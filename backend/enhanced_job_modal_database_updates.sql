-- Enhanced Job Modal Database Updates
-- Run this in your u775021278_users_manage database to support new job modal features

-- 1. Project Files Management Table
CREATE TABLE project_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    uploaded_by INT NOT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    download_count INT DEFAULT 0,
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_project_files_contact_id (contact_id),
    INDEX idx_project_files_uploaded_by (uploaded_by),
    INDEX idx_project_files_type (file_type)
);

-- 2. GitHub Integration Table
CREATE TABLE project_github (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    repository_url VARCHAR(500) NOT NULL,
    repository_name VARCHAR(255),
    branch_name VARCHAR(100) DEFAULT 'main',
    last_commit_hash VARCHAR(100),
    last_commit_message TEXT,
    last_commit_date TIMESTAMP NULL,
    issues_count INT DEFAULT 0,
    pull_requests_count INT DEFAULT 0,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL,
    UNIQUE KEY unique_project_github (contact_id),
    INDEX idx_project_github_contact_id (contact_id)
);

-- 3. Project Timeline & Milestones Table
CREATE TABLE project_milestones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    milestone_name VARCHAR(255) NOT NULL,
    milestone_description TEXT,
    due_date DATE NOT NULL,
    completed_date DATE NULL,
    status ENUM('pending', 'in_progress', 'completed', 'overdue', 'cancelled') DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    assigned_to INT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_project_milestones_contact_id (contact_id),
    INDEX idx_project_milestones_assigned_to (assigned_to),
    INDEX idx_project_milestones_status (status),
    INDEX idx_project_milestones_due_date (due_date)
);

-- 4. Communication Log Table
CREATE TABLE project_communications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    communication_type ENUM('email', 'phone', 'meeting', 'message', 'note') NOT NULL,
    subject VARCHAR(255),
    content TEXT NOT NULL,
    direction ENUM('inbound', 'outbound', 'internal') NOT NULL,
    sender_email VARCHAR(255),
    recipient_email VARCHAR(255),
    duration_minutes INT NULL,
    communication_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    is_important BOOLEAN DEFAULT FALSE,
    follow_up_required BOOLEAN DEFAULT FALSE,
    follow_up_date DATE NULL,
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_project_communications_contact_id (contact_id),
    INDEX idx_project_communications_type (communication_type),
    INDEX idx_project_communications_date (communication_date),
    INDEX idx_project_communications_important (is_important)
);

-- 5. Billing & Invoices Table
CREATE TABLE project_billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    invoice_number VARCHAR(50) UNIQUE,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled') DEFAULT 'draft',
    payment_date DATE NULL,
    payment_method VARCHAR(100),
    notes TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_project_billing_contact_id (contact_id),
    INDEX idx_project_billing_status (status),
    INDEX idx_project_billing_date (invoice_date)
);

-- 6. Project Activity Feed Table
CREATE TABLE project_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    activity_type ENUM('note_added', 'file_uploaded', 'milestone_created', 'milestone_completed', 
                       'communication_logged', 'invoice_created', 'invoice_paid', 'github_updated',
                       'status_changed', 'assigned', 'time_logged') NOT NULL,
    activity_description TEXT NOT NULL,
    related_id INT NULL, -- ID of related record (note_id, file_id, etc.)
    related_table VARCHAR(50) NULL, -- Table name of related record
    performed_by INT NOT NULL,
    activity_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    metadata JSON NULL, -- Additional data in JSON format
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_project_activities_contact_id (contact_id),
    INDEX idx_project_activities_type (activity_type),
    INDEX idx_project_activities_date (activity_date),
    INDEX idx_project_activities_performed_by (performed_by)
);

-- 7. Enhanced Project Status Tracking
ALTER TABLE u775021278_Greyline.contacts 
ADD COLUMN project_status ENUM('new', 'in_progress', 'review', 'client_review', 'completed', 'on_hold', 'cancelled') DEFAULT 'new' AFTER message,
ADD COLUMN project_priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' AFTER project_status,
ADD COLUMN estimated_hours DECIMAL(8,2) DEFAULT 0.00 AFTER project_priority,
ADD COLUMN actual_hours DECIMAL(8,2) DEFAULT 0.00 AFTER estimated_hours,
ADD COLUMN start_date DATE NULL AFTER actual_hours,
ADD COLUMN target_completion_date DATE NULL AFTER start_date,
ADD COLUMN actual_completion_date DATE NULL AFTER target_completion_date,
ADD COLUMN budget_amount DECIMAL(10,2) DEFAULT 0.00 AFTER actual_completion_date,
ADD COLUMN assigned_employee_id INT NULL AFTER budget_amount,
ADD INDEX idx_contacts_project_status (project_status),
ADD INDEX idx_contacts_project_priority (project_priority),
ADD INDEX idx_contacts_assigned_employee (assigned_employee_id);

-- 8. Project Tags/Categories
CREATE TABLE project_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(100) UNIQUE NOT NULL,
    tag_color VARCHAR(7) DEFAULT '#6366f1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE project_tag_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    tag_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES project_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES employees(id) ON DELETE SET NULL,
    UNIQUE KEY unique_project_tag (contact_id, tag_id),
    INDEX idx_project_tag_assignments_contact_id (contact_id),
    INDEX idx_project_tag_assignments_tag_id (tag_id)
);

-- Insert some default tags
INSERT INTO project_tags (tag_name, tag_color) VALUES
('Website', '#6366f1'),
('Mobile App', '#10b981'),
('E-commerce', '#f59e0b'),
('Logo Design', '#ef4444'),
('Branding', '#8b5cf6'),
('Maintenance', '#6b7280'),
('Urgent', '#dc2626'),
('High Priority', '#ea580c');

-- 9. Project Dependencies
CREATE TABLE project_dependencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    dependency_name VARCHAR(255) NOT NULL,
    dependency_type ENUM('client_approval', 'content', 'assets', 'third_party', 'technical', 'other') NOT NULL,
    status ENUM('pending', 'received', 'in_progress', 'completed') DEFAULT 'pending',
    due_date DATE NULL,
    received_date DATE NULL,
    description TEXT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contact_id) REFERENCES u775021278_Greyline.contacts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_project_dependencies_contact_id (contact_id),
    INDEX idx_project_dependencies_status (status),
    INDEX idx_project_dependencies_type (dependency_type)
);

-- 10. Project Templates
CREATE TABLE project_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_name VARCHAR(255) NOT NULL,
    template_description TEXT,
    estimated_hours DECIMAL(8,2) DEFAULT 0.00,
    default_milestones JSON, -- JSON array of default milestones
    default_tags JSON, -- JSON array of default tag IDs
    created_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_project_templates_active (is_active)
);

-- Insert some default templates
INSERT INTO project_templates (template_name, template_description, estimated_hours, default_milestones, default_tags, created_by) VALUES
('Basic Website', 'Standard 5-page website with contact form', 40.00, 
 '["Initial Design", "Development", "Content Integration", "Testing", "Launch"]',
 '[1, 7]', 1),
('E-commerce Site', 'Full e-commerce website with payment processing', 80.00,
 '["Design Phase", "Development", "Payment Integration", "Product Setup", "Testing", "Launch"]',
 '[3, 7]', 1),
('Logo Design', 'Professional logo design with brand guidelines', 15.00,
 '["Research", "Concept Development", "Client Review", "Finalization", "Delivery"]',
 '[4, 5]', 1);

-- Verify all tables were created
SHOW TABLES LIKE '%project%';
SHOW TABLES LIKE '%github%';
SHOW TABLES LIKE '%milestone%';
SHOW TABLES LIKE '%communication%';
SHOW TABLES LIKE '%billing%';
SHOW TABLES LIKE '%activity%';
SHOW TABLES LIKE '%tag%';
SHOW TABLES LIKE '%dependency%';
SHOW TABLES LIKE '%template%'; 