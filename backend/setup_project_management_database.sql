-- Project Management Database Setup
-- Database: u775021278_project_manage
-- This will contain all employee and project management features

-- 1. Employee Management Tables
CREATE TABLE u775021278_project_manage.employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id VARCHAR(20) UNIQUE NOT NULL, -- e.g., EMP001, EMP002
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('developer', 'designer', 'project_manager', 'admin', 'support') NOT NULL,
    department VARCHAR(100),
    phone VARCHAR(20),
    hire_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE u775021278_project_manage.employee_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (employee_id) REFERENCES u775021278_project_manage.employees(id) ON DELETE CASCADE,
    INDEX idx_employee_sessions_employee_id (employee_id),
    INDEX idx_employee_sessions_token (session_token),
    INDEX idx_employee_sessions_active (is_active)
);

CREATE TABLE u775021278_project_manage.employee_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    contact_id INT NOT NULL,
    role_in_project ENUM('lead', 'developer', 'designer', 'reviewer', 'support') NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_date TIMESTAMP NULL,
    status ENUM('active', 'completed', 'on_hold', 'transferred') DEFAULT 'active',
    hours_logged DECIMAL(8,2) DEFAULT 0.00,
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES u775021278_project_manage.employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_contact (employee_id, contact_id),
    INDEX idx_employee_projects_employee_id (employee_id),
    INDEX idx_employee_projects_contact_id (contact_id),
    INDEX idx_employee_projects_status (status)
);

CREATE TABLE u775021278_project_manage.employee_time_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    contact_id INT NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NULL,
    duration_minutes INT DEFAULT 0,
    activity_type ENUM('development', 'design', 'meeting', 'testing', 'documentation', 'support', 'other') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES u775021278_project_manage.employees(id) ON DELETE CASCADE,
    INDEX idx_employee_time_logs_employee_id (employee_id),
    INDEX idx_employee_time_logs_contact_id (contact_id),
    INDEX idx_employee_time_logs_date (start_time)
);

CREATE TABLE u775021278_project_manage.employee_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    metric_date DATE NOT NULL,
    projects_completed INT DEFAULT 0,
    hours_worked DECIMAL(8,2) DEFAULT 0.00,
    tasks_completed INT DEFAULT 0,
    client_satisfaction_rating DECIMAL(3,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES u775021278_project_manage.employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, metric_date),
    INDEX idx_employee_metrics_employee_id (employee_id),
    INDEX idx_employee_metrics_date (metric_date)
);

-- 2. Project Management Tables
CREATE TABLE u775021278_project_manage.project_files (
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
    FOREIGN KEY (uploaded_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_project_files_contact_id (contact_id),
    INDEX idx_project_files_uploaded_by (uploaded_by),
    INDEX idx_project_files_type (file_type)
);

CREATE TABLE u775021278_project_manage.project_github (
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
    FOREIGN KEY (created_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    UNIQUE KEY unique_project_github (contact_id),
    INDEX idx_project_github_contact_id (contact_id)
);

CREATE TABLE u775021278_project_manage.project_milestones (
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
    FOREIGN KEY (assigned_to) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_project_milestones_contact_id (contact_id),
    INDEX idx_project_milestones_assigned_to (assigned_to),
    INDEX idx_project_milestones_status (status),
    INDEX idx_project_milestones_due_date (due_date)
);

CREATE TABLE u775021278_project_manage.project_communications (
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
    FOREIGN KEY (created_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_project_communications_contact_id (contact_id),
    INDEX idx_project_communications_type (communication_type),
    INDEX idx_project_communications_date (communication_date),
    INDEX idx_project_communications_important (is_important)
);

CREATE TABLE u775021278_project_manage.project_billing (
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
    FOREIGN KEY (created_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_project_billing_contact_id (contact_id),
    INDEX idx_project_billing_status (status),
    INDEX idx_project_billing_date (invoice_date)
);

CREATE TABLE u775021278_project_manage.project_activities (
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
    FOREIGN KEY (performed_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_project_activities_contact_id (contact_id),
    INDEX idx_project_activities_type (activity_type),
    INDEX idx_project_activities_date (activity_date),
    INDEX idx_project_activities_performed_by (performed_by)
);

CREATE TABLE u775021278_project_manage.project_tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_name VARCHAR(100) UNIQUE NOT NULL,
    tag_color VARCHAR(7) DEFAULT '#6366f1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE u775021278_project_manage.project_tag_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    tag_id INT NOT NULL,
    assigned_by INT NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tag_id) REFERENCES u775021278_project_manage.project_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    UNIQUE KEY unique_project_tag (contact_id, tag_id),
    INDEX idx_project_tag_assignments_contact_id (contact_id),
    INDEX idx_project_tag_assignments_tag_id (tag_id)
);

CREATE TABLE u775021278_project_manage.project_dependencies (
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
    FOREIGN KEY (created_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_project_dependencies_contact_id (contact_id),
    INDEX idx_project_dependencies_status (status),
    INDEX idx_project_dependencies_type (dependency_type)
);

CREATE TABLE u775021278_project_manage.project_templates (
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
    FOREIGN KEY (created_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_project_templates_active (is_active)
);

-- 3. Notes System
CREATE TABLE u775021278_project_manage.user_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- Can be NULL for employee notes
    contact_id INT NOT NULL,
    note_title VARCHAR(255) NOT NULL,
    note_content TEXT NOT NULL,
    note_type ENUM('general', 'progress', 'issue', 'milestone', 'feedback', 'internal') DEFAULT 'general',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    is_private BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (created_by) REFERENCES u775021278_project_manage.employees(id) ON DELETE SET NULL,
    INDEX idx_user_notes_contact_id (contact_id),
    INDEX idx_user_notes_created_by (created_by),
    INDEX idx_user_notes_type (note_type),
    INDEX idx_user_notes_priority (priority)
);

-- 4. Insert Default Data
INSERT INTO u775021278_project_manage.employees (employee_id, email, password_hash, first_name, last_name, role, department, hire_date) VALUES
('EMP001', 'admin@greylinestudio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'Management', CURDATE());

INSERT INTO u775021278_project_manage.project_tags (tag_name, tag_color) VALUES
('Website', '#6366f1'),
('Mobile App', '#10b981'),
('E-commerce', '#f59e0b'),
('Logo Design', '#ef4444'),
('Branding', '#8b5cf6'),
('Maintenance', '#6b7280'),
('Urgent', '#dc2626'),
('High Priority', '#ea580c');

INSERT INTO u775021278_project_manage.project_templates (template_name, template_description, estimated_hours, default_milestones, default_tags, created_by) VALUES
('Basic Website', 'Standard 5-page website with contact form', 40.00, 
 '["Initial Design", "Development", "Content Integration", "Testing", "Launch"]',
 '[1, 7]', 1),
('E-commerce Site', 'Full e-commerce website with payment processing', 80.00,
 '["Design Phase", "Development", "Payment Integration", "Product Setup", "Testing", "Launch"]',
 '[3, 7]', 1),
('Logo Design', 'Professional logo design with brand guidelines', 15.00,
 '["Research", "Concept Development", "Client Review", "Finalization", "Delivery"]',
 '[4, 5]', 1);

-- 5. Verify all tables were created
SHOW TABLES FROM u775021278_project_manage;
SELECT COUNT(*) as total_tables FROM information_schema.tables WHERE table_schema = 'u775021278_project_manage'; 