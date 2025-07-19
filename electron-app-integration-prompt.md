# Electron App Employee Management Integration Prompt

## Project Overview
I need to integrate a comprehensive employee management system into my existing Electron app for Greyline Studio. The system should allow employees to log in, track their active projects, log time, and manage project notes. The app now includes an enhanced job modal with extensive project management features and an admin system for managing employees.

## Current Setup
- **Website**: https://greylinestudio.com
- **Main Database**: `u775021278_Greyline` - Contains original contacts table with project submissions
- **Project Management Database**: `u775021278_project_manage` - Dedicated database for all employee and project management features
- **Existing APIs**: User registration, login, project management, notes system, enhanced job modal APIs, and admin employee management
- **Electron App**: Desktop application for employee project management

## Database Configuration
- **Project Management Database**: `u775021278_project_manage`
- **Database User**: `u775021278_userAdmin`
- **Password**: `>q}Q>']6LNp~g+7`
- **Cross-Database Linking**: Project management tables link to main contacts table via foreign keys

## Required Features

### 1. Employee Authentication
- Employee login with email/password
- Session management with secure tokens
- Role-based access (developer, designer, project_manager, admin, support)
- Automatic logout after inactivity

### 2. Admin Employee Management (NEW)
- **Admin-only access** - Only admin users can manage employees
- **Add Employees** - Create new employee accounts with auto-generated IDs
- **Edit Employees** - Update employee information and roles
- **View Employee Details** - See employee stats, projects, and time logs
- **Assign Projects** - Assign employees to specific projects
- **Reset Passwords** - Reset employee passwords
- **Deactivate Employees** - Deactivate employee accounts (soft delete)
- **Employee Statistics** - View employee performance metrics

### 3. Employee Dashboard
- **Active Projects Section**: Display all projects assigned to the employee
- **Time Tracking**: Start/stop timer for each project with activity types
- **Today's Summary**: Show hours worked today and current session status
- **Recent Notes**: Display latest notes for employee's projects
- **Weekly Overview**: Summary of work done this week

### 4. Enhanced Job Modal Features
- **Project Overview**: Status, timeline, priority, budget, assigned employee
- **Contact Information**: Client details, communication history
- **Project Details**: Requirements, specifications, deliverables
- **Notes & Comments**: Internal team communication
- **File Management**: Uploads, documents, assets with download tracking
- **GitHub Integration**: Repository links, commits, issues tracking
- **Timeline & Milestones**: Project progress tracking with due dates
- **Communication Log**: Email history, calls, meetings with follow-up tracking
- **Billing & Invoices**: Financial tracking and payment status
- **Activity Feed**: Recent actions and updates with metadata
- **Project Tags**: Categorization and filtering
- **Dependencies**: Client approvals, content, assets tracking
- **Project Templates**: Predefined project structures

### 5. Project Management
- **Project List**: Show all assigned projects with status and progress
- **Project Details**: View project information, client details, and requirements
- **Time Logging**: Track time spent on different activities (development, design, meeting, testing, etc.)
- **Notes Integration**: Add/view notes for each project
- **File Management**: Upload and manage project files
- **Milestone Tracking**: Create and monitor project milestones
- **Communication Logging**: Record all client and team communications

### 6. Time Tracking System
- **Timer Controls**: Start, pause, stop, and resume time tracking
- **Activity Types**: Development, design, meeting, testing, documentation, support, other
- **Manual Entry**: Allow manual time entry for past sessions
- **Reports**: Daily, weekly, and monthly time reports

### 7. Notes Management
- **Add Notes**: Create notes with title, content, type, and priority
- **Note Types**: General, progress, issue, milestone, feedback
- **Priority Levels**: Low, medium, high, urgent
- **Search & Filter**: Find notes by project, type, or date

## API Endpoints Available

### Authentication
- `POST /backend/employee_login_new_db.php` - Employee login (UPDATED for new database)
- Returns: session_token, employee data, active projects, today's logs

### Admin Employee Management (NEW)
- `GET /backend/admin_employee_management.php?action=list_employees` - List all employees
- `GET /backend/admin_employee_management.php?action=get_employee&employee_id=X` - Get employee details
- `GET /backend/admin_employee_management.php?action=employee_stats&employee_id=X` - Get employee statistics
- `POST /backend/admin_employee_management.php` - Add new employee
- `PUT /backend/admin_employee_management.php` - Update employee
- `PUT /backend/admin_employee_management.php` - Reset employee password
- `DELETE /backend/admin_employee_management.php` - Deactivate employee
- `POST /backend/admin_employee_management.php` - Assign project to employee

### Dashboard
- `GET /backend/employee_dashboard.php` - Get dashboard data
- Headers: Authorization: [session_token]
- Returns: active projects, time logs, weekly summary, recent notes

### Time Tracking
- `POST /backend/employee_dashboard.php` - Time tracking actions
- Actions: start_timer, stop_timer
- Data: contact_id, activity_type, description, log_id

### Enhanced Job Modal API (UPDATED for new database)
- `GET /backend/enhanced_job_modal_api_new_db.php?action=project_overview&contact_id=X` - Complete project overview
- `GET /backend/enhanced_job_modal_api_new_db.php?action=files&contact_id=X` - Project files
- `GET /backend/enhanced_job_modal_api_new_db.php?action=github&contact_id=X` - GitHub integration
- `GET /backend/enhanced_job_modal_api_new_db.php?action=billing&contact_id=X` - Billing information
- `GET /backend/enhanced_job_modal_api_new_db.php?action=dependencies&contact_id=X` - Project dependencies
- `GET /backend/enhanced_job_modal_api_new_db.php?action=templates` - Project templates

### Job Modal Actions (UPDATED for new database)
- `POST /backend/enhanced_job_modal_api_new_db.php` - Update project details
- `POST /backend/enhanced_job_modal_api_new_db.php` - Add milestones
- `POST /backend/enhanced_job_modal_api_new_db.php` - Log communications
- `POST /backend/enhanced_job_modal_api_new_db.php` - Add dependencies
- `POST /backend/enhanced_job_modal_api_new_db.php` - Link GitHub repositories
- `PUT /backend/enhanced_job_modal_api_new_db.php` - Update milestones

### Notes Management
- `GET /backend/notes_api.php?user_id=X&contact_id=Y` - Get project notes
- `POST /backend/notes_api.php` - Create note
- `PUT /backend/notes_api.php` - Update note
- `DELETE /backend/notes_api.php` - Delete note

### Project Data
- `GET /backend/get_all_projects.php` - Get all projects with notes

## Database Schema

### Main Website Database (`u775021278_Greyline`)
```sql
contacts (id, job_number, project_title, name, email, message, submitted_at, project_status, project_priority, estimated_hours, actual_hours, start_date, target_completion_date, actual_completion_date, budget_amount, assigned_employee_id)
```

### Project Management Database (`u775021278_project_manage`)

#### Employee Management Tables
```sql
employees (id, employee_id, email, password_hash, first_name, last_name, role, department, phone, hire_date, is_active, created_at, updated_at)
employee_sessions (id, employee_id, session_token, login_time, logout_time, ip_address, user_agent, is_active)
employee_projects (id, employee_id, contact_id, role_in_project, assigned_date, completion_date, status, hours_logged, notes)
employee_time_logs (id, employee_id, contact_id, start_time, end_time, duration_minutes, activity_type, description, created_at)
employee_metrics (id, employee_id, metric_date, projects_completed, hours_worked, tasks_completed, client_satisfaction_rating, notes, created_at)
```

#### Enhanced Project Management Tables
```sql
project_files (id, contact_id, file_name, file_path, file_type, file_size, uploaded_by, upload_date, description, is_public, download_count)
project_github (id, contact_id, repository_url, repository_name, branch_name, last_commit_hash, last_commit_message, last_commit_date, issues_count, pull_requests_count, created_by, created_at, updated_at)
project_milestones (id, contact_id, milestone_name, milestone_description, due_date, completed_date, status, priority, assigned_to, created_by, created_at, updated_at)
project_communications (id, contact_id, communication_type, subject, content, direction, sender_email, recipient_email, duration_minutes, communication_date, created_by, is_important, follow_up_required, follow_up_date)
project_billing (id, contact_id, invoice_number, invoice_date, due_date, amount, tax_amount, total_amount, currency, status, payment_date, payment_method, notes, created_by, created_at, updated_at)
project_activities (id, contact_id, activity_type, activity_description, related_id, related_table, performed_by, activity_date, metadata)
project_tags (id, tag_name, tag_color, created_at)
project_tag_assignments (id, contact_id, tag_id, assigned_by, assigned_at)
project_dependencies (id, contact_id, dependency_name, dependency_type, status, due_date, received_date, description, created_by, created_at, updated_at)
project_templates (id, template_name, template_description, estimated_hours, default_milestones, default_tags, created_by, is_active, created_at, updated_at)
```

#### Notes System
```sql
user_notes (id, user_id, contact_id, note_title, note_content, note_type, priority, created_at, updated_at, created_by, is_private)
```

## Database Setup Instructions

### 1. Create New Database
- Database Name: `u775021278_project_manage`
- Database User: `u775021278_userAdmin`
- Password: `>q}Q>']6LNp~g+7`

### 2. Run SQL Setup Script
Execute the SQL script: `backend/setup_project_management_database.sql`
This will create all 16 tables with proper foreign key relationships and sample data.

### 3. Add Admin User
Execute the SQL script: `backend/add_admin_user.sql`
This will add the admin user with login credentials:
- Email: `admin@greylinestudio.com`
- Password: `password`

### 4. Cross-Database Relationships
- Project management tables link to main contacts table via `contact_id` foreign keys
- Employee assignments link to main contacts table via `assigned_employee_id`
- All project data is stored in the dedicated project management database

## UI/UX Requirements

### Design Style
- **Theme**: Dark mode with blue/purple accent colors
- **Colors**: Primary #6366f1, Secondary #10b981, Background #0f172a
- **Typography**: Inter font family
- **Layout**: Clean, modern interface with cards and sections

### Main Dashboard Layout
```
┌─────────────────────────────────────────────────────────┐
│ Header: Employee Name, Role, Logout Button              │
├─────────────────────────────────────────────────────────┤
│ Active Projects | Today's Summary | Recent Notes        │
│ [Project Cards] | [Time Stats]    | [Note List]         │
├─────────────────────────────────────────────────────────┤
│ Time Tracking | Weekly Overview                          │
│ [Timer]        | [Charts/Stats]                          │
└─────────────────────────────────────────────────────────┘
```

### Admin Dashboard Layout (NEW)
```
┌─────────────────────────────────────────────────────────┐
│ Header: Admin Name, Role, Logout Button                 │
├─────────────────────────────────────────────────────────┤
│ Employee Management | Project Overview | System Stats   │
│ [Employee List]     | [Project List]   | [Analytics]    │
├─────────────────────────────────────────────────────────┤
│ Quick Actions | Recent Activity                         │
│ [Add Employee] | [Activity Feed]                        │
└─────────────────────────────────────────────────────────┘
```

### Enhanced Job Modal Layout
```
┌─────────────────────────────────────────────────────────┐
│ Project Title | Status | Priority | Assigned To         │
├─────────────────────────────────────────────────────────┤
│ Tabs: Overview | Files | GitHub | Timeline | Billing    │
├─────────────────────────────────────────────────────────┤
│ Overview Tab:                                            │
│ ┌─────────────┬─────────────┬─────────────┐             │
│ │ Project     │ Contact     │ Timeline    │             │
│ │ Details     │ Info        │ & Budget    │             │
│ └─────────────┴─────────────┴─────────────┘             │
│ ┌─────────────┬─────────────┬─────────────┐             │
│ │ Milestones  │ Dependencies│ Activity    │             │
│ │ & Progress  │ & Tasks     │ Feed        │             │
│ └─────────────┴─────────────┴─────────────┘             │
├─────────────────────────────────────────────────────────┤
│ Files Tab: File upload, management, GitHub integration  │
│ GitHub Tab: Repository info, commits, issues            │
│ Timeline Tab: Milestones, deadlines, progress tracking  │
│ Billing Tab: Invoices, payments, financial tracking     │
└─────────────────────────────────────────────────────────┘
```

### Key Components Needed
1. **Login Screen**: Email/password with validation
2. **Admin Dashboard**: Employee management interface (admin only)
3. **Employee Dashboard**: Main overview with all sections
4. **Project Cards**: Show project info with timer controls
5. **Timer Component**: Start/stop with activity selection
6. **Enhanced Job Modal**: Comprehensive project management interface
7. **File Manager**: Upload, download, and manage project files
8. **GitHub Integration**: Repository linking and status
9. **Milestone Tracker**: Create and monitor project milestones
10. **Communication Logger**: Record and track communications
11. **Notes Panel**: Add/view notes for projects
12. **Time Reports**: Charts and summaries
13. **Settings**: Employee profile and preferences
14. **Employee Management**: Add, edit, view, and manage employees (admin only)

## Technical Requirements

### Electron App Structure
- **Main Process**: Handle authentication and data management
- **Renderer Process**: UI components and user interactions
- **IPC Communication**: Between main and renderer processes
- **Local Storage**: Cache session data and preferences
- **File System Access**: For file uploads and downloads

### Data Management
- **API Integration**: Connect to all backend endpoints
- **Real-time Updates**: Refresh data periodically
- **Offline Support**: Cache data for offline viewing
- **Error Handling**: Graceful error messages and retry logic
- **File Upload**: Handle file uploads to server
- **GitHub API**: Integrate with GitHub for repository data

### Security
- **Session Management**: Secure token storage and validation
- **Data Encryption**: Encrypt sensitive data in local storage
- **Input Validation**: Validate all user inputs
- **Auto Logout**: Inactive session timeout
- **File Security**: Secure file upload and download
- **Role-based Access**: Admin-only features for employee management

## Sample API Responses

### Admin Employee List Response
```json
{
  "success": true,
  "employees": [
    {
      "id": 1,
      "employee_id": "EMP001",
      "email": "admin@greylinestudio.com",
      "first_name": "Admin",
      "last_name": "User",
      "role": "admin",
      "department": "Management",
      "is_active": true,
      "created_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": 2,
      "employee_id": "EMP002",
      "email": "john@greylinestudio.com",
      "first_name": "John",
      "last_name": "Doe",
      "role": "developer",
      "department": "Development",
      "is_active": true,
      "created_at": "2024-01-16T14:20:00Z"
    }
  ]
}
```

### Add Employee Response
```json
{
  "success": true,
  "message": "Employee added successfully",
  "employee_id": 3,
  "employee_code": "EMP003",
  "default_password": "password"
}
```

### Enhanced Project Overview Response
```json
{
  "success": true,
  "project": {
    "id": 1,
    "job_number": "JOB-00001",
    "project_title": "E-commerce Website",
    "name": "Client Name",
    "email": "client@example.com",
    "message": "Project requirements...",
    "project_status": "in_progress",
    "project_priority": "high",
    "estimated_hours": 80.00,
    "actual_hours": 45.50,
    "start_date": "2024-01-15",
    "target_completion_date": "2024-03-15",
    "budget_amount": 5000.00,
    "assigned_employee_id": 1,
    "assigned_employee_name": "John",
    "assigned_employee_lastname": "Doe"
  },
  "tags": [
    {"tag_name": "Website", "tag_color": "#6366f1"},
    {"tag_name": "E-commerce", "tag_color": "#f59e0b"},
    {"tag_name": "High Priority", "tag_color": "#ea580c"}
  ],
  "milestones": [
    {
      "id": 1,
      "milestone_name": "Design Phase",
      "due_date": "2024-02-01",
      "status": "completed",
      "priority": "high",
      "assigned_employee_name": "Jane"
    }
  ],
  "communications": [
    {
      "id": 1,
      "communication_type": "email",
      "subject": "Design Approval",
      "direction": "outbound",
      "communication_date": "2024-01-20T10:30:00Z"
    }
  ],
  "activities": [
    {
      "id": 1,
      "activity_type": "milestone_completed",
      "activity_description": "Design Phase completed",
      "activity_date": "2024-01-25T14:20:00Z",
      "performed_by_name": "John"
    }
  ]
}
```

### File Management Response
```json
{
  "success": true,
  "files": [
    {
      "id": 1,
      "file_name": "design-mockup.psd",
      "file_type": "image/photoshop",
      "file_size": 5242880,
      "upload_date": "2024-01-20T09:15:00Z",
      "uploaded_by_name": "Jane",
      "download_count": 3
    }
  ]
}
```

### GitHub Integration Response
```json
{
  "success": true,
  "github": {
    "repository_url": "https://github.com/client/ecommerce-site",
    "repository_name": "ecommerce-site",
    "branch_name": "main",
    "last_commit_hash": "abc123...",
    "last_commit_message": "Add payment integration",
    "last_commit_date": "2024-01-25T16:45:00Z",
    "issues_count": 2,
    "pull_requests_count": 1
  }
}
```

## Implementation Priority
1. **Phase 1**: Admin login and employee management system
2. **Phase 2**: Employee login and basic dashboard
3. **Phase 3**: Enhanced job modal with project overview
4. **Phase 4**: File management and GitHub integration
5. **Phase 5**: Time tracking and milestone management
6. **Phase 6**: Communication logging and billing
7. **Phase 7**: Advanced features and analytics

## Additional Notes
- The app should work offline with cached data
- Implement proper error handling and user feedback
- Add keyboard shortcuts for common actions
- Include a help/tutorial system for new employees
- Consider adding notifications for project updates
- Support drag-and-drop file uploads
- Implement real-time updates for collaborative features
- Add export functionality for reports and data
- Use the dedicated project management database for all employee and project data
- Cross-database queries link project management data to main website contacts
- Admin features should only be accessible to users with admin role
- Employee management should include proper validation and error handling

Please help me implement this comprehensive employee management system in my Electron app with a clean, professional interface that matches the Greyline Studio brand and includes all the enhanced project management features using the dedicated project management database. 