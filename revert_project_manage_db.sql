-- Revert u775021278_project_manage database to original state
-- Remove the preference tables that were accidentally added

USE u775021278_project_manage;

-- Drop the preference tables that were added by mistake
DROP TABLE IF EXISTS u775021278_project_manage.employee_app_preferences;
DROP TABLE IF EXISTS u775021278_project_manage.employee_app_data;
DROP TABLE IF EXISTS u775021278_project_manage.employee_recent_activities;
DROP TABLE IF EXISTS u775021278_project_manage.employee_bookmarks;

-- Verify the database is back to original state
SHOW TABLES FROM u775021278_project_manage;

-- Should only show these tables:
-- employees
-- employee_metrics
-- employee_projects
-- employee_sessions
-- employee_time_logs
-- (and any other original project management tables) 