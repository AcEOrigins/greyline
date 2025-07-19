-- Add notes table to u775021278_users_manage database
-- Run this in your users database

-- Create notes table for storing employee notes
CREATE TABLE user_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    contact_id INT NOT NULL,
    note_title VARCHAR(255) NOT NULL,
    note_content TEXT NOT NULL,
    note_type ENUM('general', 'progress', 'issue', 'milestone', 'feedback') DEFAULT 'general',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by VARCHAR(100) DEFAULT 'employee',
    is_private BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_notes_user_id (user_id),
    INDEX idx_user_notes_contact_id (contact_id),
    INDEX idx_user_notes_created_at (created_at)
);

-- Verify table was created
DESCRIBE user_notes;
SELECT COUNT(*) FROM user_notes; 