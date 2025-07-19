-- Employee Management Database Setup
-- Run this in your u775021278_users_manage database

-- Create employees table
CREATE TABLE employees (
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

-- Create employee login sessions table
CREATE TABLE employee_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    session_token VARCHAR(255) UNIQUE NOT NULL,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee_sessions_employee_id (employee_id),
    INDEX idx_employee_sessions_token (session_token),
    INDEX idx_employee_sessions_active (is_active)
);

-- Create employee project assignments table
CREATE TABLE employee_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    contact_id INT NOT NULL,
    role_in_project ENUM('lead', 'developer', 'designer', 'reviewer', 'support') NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_date TIMESTAMP NULL,
    status ENUM('active', 'completed', 'on_hold', 'transferred') DEFAULT 'active',
    hours_logged DECIMAL(8,2) DEFAULT 0.00,
    notes TEXT,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_contact (employee_id, contact_id),
    INDEX idx_employee_projects_employee_id (employee_id),
    INDEX idx_employee_projects_contact_id (contact_id),
    INDEX idx_employee_projects_status (status)
);

-- Create employee time tracking table
CREATE TABLE employee_time_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    contact_id INT NOT NULL,
    start_time TIMESTAMP NOT NULL,
    end_time TIMESTAMP NULL,
    duration_minutes INT DEFAULT 0,
    activity_type ENUM('development', 'design', 'meeting', 'testing', 'documentation', 'support', 'other') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    INDEX idx_employee_time_logs_employee_id (employee_id),
    INDEX idx_employee_time_logs_contact_id (contact_id),
    INDEX idx_employee_time_logs_date (start_time)
);

-- Create employee performance metrics table
CREATE TABLE employee_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    metric_date DATE NOT NULL,
    projects_completed INT DEFAULT 0,
    hours_worked DECIMAL(8,2) DEFAULT 0.00,
    tasks_completed INT DEFAULT 0,
    client_satisfaction_rating DECIMAL(3,2) DEFAULT 0.00,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_employee_date (employee_id, metric_date),
    INDEX idx_employee_metrics_employee_id (employee_id),
    INDEX idx_employee_metrics_date (metric_date)
);

-- Insert sample admin employee
INSERT INTO employees (employee_id, email, password_hash, first_name, last_name, role, department, hire_date) VALUES
('EMP001', 'admin@greylinestudio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'Management', CURDATE());

-- Verify tables were created
SHOW TABLES;
DESCRIBE employees;
DESCRIBE employee_sessions;
DESCRIBE employee_projects;
DESCRIBE employee_time_logs;
DESCRIBE employee_metrics; 