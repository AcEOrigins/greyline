-- Add Admin User to Project Management Database
-- Run this in the u775021278_project_manage database

USE u775021278_project_manage;

-- Insert admin user
INSERT INTO employees (employee_id, email, password_hash, first_name, last_name, role, department, phone, hire_date, is_active) VALUES
('EMP001', 'admin@greylinestudio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', 'Management', '', CURDATE(), TRUE);

-- Verify admin user was created
SELECT id, employee_id, email, first_name, last_name, role, is_active FROM employees WHERE role = 'admin'; 