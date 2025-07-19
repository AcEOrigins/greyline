-- Add preference tables to the correct database: u775021278_users_manage

USE u775021278_users_manage;

-- Employee app preferences
CREATE TABLE u775021278_users_manage.employee_app_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL UNIQUE,
    theme VARCHAR(20) DEFAULT 'dark',
    language VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(50) DEFAULT 'UTC',
    date_format VARCHAR(20) DEFAULT 'MM/DD/YYYY',
    time_format VARCHAR(10) DEFAULT '12h',
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    dashboard_layout JSON,
    sidebar_collapsed BOOLEAN DEFAULT FALSE,
    auto_refresh_interval INT DEFAULT 300,
    default_view VARCHAR(50) DEFAULT 'projects',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_employee_app_preferences_employee_id (employee_id)
);

-- Employee app data storage
CREATE TABLE u775021278_users_manage.employee_app_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    data_key VARCHAR(255) NOT NULL,
    data_value JSON,
    data_type ENUM('preference', 'cache', 'state', 'history', 'custom') DEFAULT 'custom',
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_employee_data_key (employee_id, data_key),
    INDEX idx_employee_app_data_employee_id (employee_id),
    INDEX idx_employee_app_data_type (data_type),
    INDEX idx_employee_app_data_expires (expires_at)
);

-- Employee recent activities
CREATE TABLE u775021278_users_manage.employee_recent_activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    activity_type VARCHAR(100) NOT NULL,
    activity_data JSON,
    activity_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_employee_recent_activities_employee_id (employee_id),
    INDEX idx_employee_recent_activities_timestamp (activity_timestamp)
);

-- Employee bookmarks
CREATE TABLE u775021278_users_manage.employee_bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    bookmark_type ENUM('project', 'contact', 'file', 'url', 'custom') NOT NULL,
    bookmark_name VARCHAR(255) NOT NULL,
    bookmark_data JSON NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_employee_bookmarks_employee_id (employee_id),
    INDEX idx_employee_bookmarks_type (bookmark_type)
);

-- Verify the tables were created
SHOW TABLES FROM u775021278_users_manage; 