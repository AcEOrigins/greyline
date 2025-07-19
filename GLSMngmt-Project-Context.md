# 🚀 Greyline Studio Management System - Comprehensive Project Context

## 1. **Project Overview**

**Greyline Studio Management System** is an Electron-based desktop application designed to manage employee data, project submissions, and administrative tasks for Greyline Studio. The app serves as a comprehensive management platform with role-based access control, real-time data management, and a modern UI.

### **Main Features:**
- **Employee Management System** - CRUD operations for employee data
- **Project Submission Tracking** - Monitor and manage client project submissions
- **Role-Based Access Control** - Different interfaces for admin vs regular employees
- **Theme Persistence** - Light/dark mode with database storage
- **Real-time Notifications** - Background monitoring for new submissions
- **Dashboard Analytics** - Statistics and overview of system activity

---

## 2. **Current File Structure**

### **Core Application Files:**
```
GLSMngmt/
├── main.js                    # Main Electron process, window management, IPC handlers
├── preload.js                 # Security bridge between main and renderer processes
├── renderer.js                # Frontend logic for main admin interface
├── index.html                 # Main application interface
├── login.html                 # Authentication page
├── login.js                   # Login logic and session management
├── admin-dashboard.html       # Employee management system interface
├── admin-dashboard.js         # Employee system logic and data management
├── package.json               # Project dependencies and scripts
├── package-lock.json          # Dependency lock file
```

### **Database & Services:**
```
GLSMngmt/
├── db/
│   ├── connect.js             # Database connection configuration
│   └── employeeService.js     # Employee data operations and business logic
```

### **Assets & Styling:**
```
GLSMngmt/
├── assests/
│   ├── GreylineICO.ico        # Application icon
│   ├── GreylineICO.png        # Application icon (PNG)
│   └── GreylineStudioLogo.png # Company logo
├── styles/
│   ├── style.css              # Main application styles
│   └── admin.css              # Employee system specific styles
```

---

## 3. **Database Integration**

### **Database Configuration:**
- **Primary Database**: `u775021278_users_manage`
- **Secondary Database**: `u775021278_Greyline` (limited access)
- **Connection Details**: MySQL via `mysql2` library
- **User**: `u775021278_userAdmin`
- **Host**: Remote MySQL server

### **Database Tables:**

#### **Users Management Database (`u775021278_users_manage`):**
```sql
-- Main users table (exists and accessible)
users (
  id INT PRIMARY KEY,
  email VARCHAR(255),
  first_name VARCHAR(255),
  last_name VARCHAR(255),
  is_active BOOLEAN,
  created_at TIMESTAMP
)

-- Employee preferences table (referenced but doesn't exist)
employee_app_preferences (
  id INT PRIMARY KEY,
  employee_id INT,
  theme VARCHAR(10),
  language VARCHAR(5),
  notifications_enabled BOOLEAN,
  -- ... other preference fields
)

-- Other referenced tables (don't exist):
- contacts
- user_notes
- projects
- employee_projects
- employee_time_logs
- employee_notes
```

#### **Greyline Database (`u775021278_Greyline`):**
```sql
-- Referenced but access denied:
contacts (
  id INT,
  name VARCHAR(255),
  email VARCHAR(255),
  project_title VARCHAR(255),
  job_number VARCHAR(255),
  status VARCHAR(50),
  submitted_at TIMESTAMP
)
```

---

## 4. **API Endpoints (IPC Handlers)**

### **Main Process IPC Handlers (`main.js`):**

#### **Window Management:**
```javascript
'open-employee-window'     // Creates/brings to front employee management window
'is-employee-window-open'  // Checks if employee window exists
```

#### **Employee Authentication & Data:**
```javascript
'authenticate-employee'    // Validates employee login credentials
'get-employee-data'        // Retrieves employee information by ID
'get-dashboard-stats'      // Gets dashboard statistics
'get-all-employees'        // Retrieves all employee records
'get-employee-projects'    // Gets projects assigned to employee
'get-employee-time-logs'   // Retrieves employee time tracking data
'add-time-log'            // Adds new time log entry
```

#### **User Preferences:**
```javascript
'save-user-preferences'    // Saves user theme and settings to database
'load-user-preferences'    // Loads user preferences from database
```

#### **Main App Data:**
```javascript
'get-submissions'          // Gets project submissions
'get-contact-details'      // Gets detailed contact information
'delete-contact'           // Deletes contact/submission
'get-dashboard-data'       // Gets main dashboard statistics
'get-job-notes'           // Gets notes for specific job
'get-auto-start-status'   // Checks if app auto-starts
'set-auto-start'          // Configures app auto-start
```

---

## 5. **Key Features Implemented**

### **✅ Fully Working:**
- **Electron App Structure** - Main and renderer processes properly configured
- **Window Management** - Single instance, proper window controls, tray functionality
- **Authentication System** - Login/logout with session management
- **Role-Based Access** - Admin vs employee permissions
- **Theme System** - Light/dark mode with localStorage persistence
- **Employee Management UI** - Complete interface for employee data
- **Database Connection** - MySQL integration with connection pooling
- **IPC Communication** - Secure communication between processes

### **⚠️ Partially Working:**
- **Theme Database Persistence** - UI works, database table missing
- **Dashboard Statistics** - Employee counts work, project stats fail
- **Employee Data** - Basic CRUD operations work, some queries fail
- **Project Management** - UI exists, database tables missing

### **❌ Not Working:**
- **Project Data** - Tables don't exist in accessible database
- **Time Tracking** - Database tables missing
- **Submission Monitoring** - Contacts table doesn't exist
- **Cross-database Queries** - Permission issues with Greyline database

---

## 6. **UI/UX Design**

### **Design System:**
- **Framework**: Custom CSS with modern design principles
- **Theme**: Light/dark mode support with CSS variables
- **Typography**: Inter font family
- **Colors**: Professional blue/gray palette
- **Layout**: Sidebar navigation with main content area

### **Screens/Pages:**

#### **Login Page (`login.html`):**
- Clean authentication form
- Email/password fields
- Remember me functionality
- Forgot password modal
- Responsive design

#### **Main Admin Interface (`index.html`):**
- Custom titlebar with window controls
- Sidebar navigation
- Dashboard with statistics cards
- Submissions table with CRUD operations
- Settings panel with theme toggle
- Employee system access button

#### **Employee Management System (`admin-dashboard.html`):**
- Professional admin dashboard
- Employee statistics overview
- Employee table with filtering
- Project management interface
- Reports and analytics section
- Modal forms for data entry

### **Key UI Components:**
- **Custom Titlebar** - Frameless window with minimize/maximize/close
- **Statistics Cards** - Dashboard metrics with trends
- **Data Tables** - Sortable, filterable employee/submission data
- **Modal Dialogs** - Forms and detail views
- **Theme Toggle** - Switch between light/dark modes
- **Notification System** - Background notifications

---

## 7. **Authentication System**

### **Login Flow:**
1. **App Starts** → Opens login page (`login.html`)
2. **User Enters Credentials** → Email/password validation
3. **Database Authentication** → Validates against `users` table
4. **Session Creation** → Stores user data in localStorage
5. **Role Check** → Determines admin vs employee access
6. **Redirect** → Main interface with role-based UI

### **Session Management:**
```javascript
// Session structure stored in localStorage
{
  employee: {
    id: 1,
    email: "admin@greylinestudio.com",
    first_name: "Admin",
    last_name: "User",
    role: "admin"
  },
  token: "generated_token",
  expiresAt: timestamp,
  created_at: timestamp
}
```

### **User Types:**
- **Admin/Manager** - Full access to employee system
- **Regular Employee** - Limited access, no employee management

### **Security Features:**
- **Session Expiration** - Automatic logout after timeout
- **Role Validation** - Backend checks user permissions
- **Secure IPC** - Context isolation between processes
- **Database Validation** - Server-side session verification

---

## 8. **Data Flow**

### **Frontend → Backend:**
1. **User Action** → Event listener triggers
2. **IPC Call** → `window.electronAPI.methodName()`
3. **Preload Bridge** → `ipcRenderer.invoke()`
4. **Main Process** → IPC handler processes request
5. **Database Query** → EmployeeService method executes
6. **Response** → Data returned through IPC chain

### **Example Flow (Theme Change):**
```
User toggles theme → renderer.js → preload.js → main.js → 
employeeService.js → database → response → UI update
```

### **Real-time Updates:**
- **Storage Events** - Theme changes sync across windows
- **Background Monitoring** - Periodic database checks
- **Live Data** - Dashboard stats update automatically

---

## 9. **Dependencies**

### **Package.json Dependencies:**
```json
{
  "dependencies": {
    "electron": "^28.0.0",
    "mysql2": "^3.6.5"
  },
  "devDependencies": {
    "electron-reload": "^1.5.0"
  }
}
```

### **Key Libraries:**
- **Electron** - Desktop application framework
- **MySQL2** - Database connectivity and querying
- **Electron-reload** - Development hot reloading (disabled in production)

### **Built-in APIs:**
- **Node.js** - File system, networking, utilities
- **Electron IPC** - Inter-process communication
- **Browser APIs** - DOM manipulation, localStorage, notifications

---

## 10. **Known Issues**

### **Database Issues:**
1. **Missing Tables** - `employee_app_preferences` table doesn't exist
2. **Permission Errors** - No access to `u775021278_Greyline.contacts`
3. **Cross-database Queries** - Limited access to secondary database
4. **Table References** - Many queries reference non-existent tables

### **Functionality Issues:**
1. **Theme Database Storage** - Falls back to localStorage due to missing table
2. **Dashboard Stats** - Project statistics fail due to database access
3. **Employee Projects** - No project data available
4. **Time Tracking** - Database tables missing
5. **Submission Monitoring** - Contacts table doesn't exist

### **Error Messages:**
```
Error: Table 'u775021278_users_manage.employee_app_preferences' doesn't exist
Error: SELECT command denied to user for table `u775021278_Greyline`.`contacts`
Error: Table 'u775021278_users_manage.contacts' doesn't exist
```

---

## 11. **Next Steps**

### **Immediate Fixes:**
1. **Create Missing Tables** - Set up `employee_app_preferences` table
2. **Database Permissions** - Resolve access to Greyline database
3. **Error Handling** - Implement graceful fallbacks for missing data
4. **Data Migration** - Set up proper database schema

### **Feature Enhancements:**
1. **Complete Project Management** - Full CRUD for projects
2. **Time Tracking System** - Employee time logging
3. **Reporting System** - Analytics and data export
4. **Notification System** - Real-time alerts and updates
5. **User Management** - Admin user creation and management

### **Technical Improvements:**
1. **Database Schema** - Proper table relationships and constraints
2. **API Documentation** - Complete endpoint documentation
3. **Error Logging** - Comprehensive error tracking
4. **Performance Optimization** - Query optimization and caching
5. **Security Hardening** - Input validation and sanitization

---

## 12. **Technical Architecture**

### **Architecture Pattern:**
```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Renderer      │    │   Preload       │    │   Main          │
│   Process       │◄──►│   Bridge        │◄──►│   Process       │
│   (Frontend)    │    │   (Security)    │    │   (Backend)     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   HTML/CSS/JS   │    │   IPC Security  │    │   Database      │
│   (UI Layer)    │    │   (API Layer)   │    │   (Data Layer)  │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

### **Process Structure:**
- **Main Process** - Window management, IPC handlers, database operations
- **Renderer Process** - UI rendering, user interactions, local state
- **Preload Script** - Secure API bridge, context isolation

### **Data Layers:**
- **Presentation Layer** - HTML/CSS/JavaScript UI
- **Business Logic Layer** - EmployeeService, authentication logic
- **Data Access Layer** - MySQL queries, connection management
- **Infrastructure Layer** - Electron IPC, file system, networking

### **Security Model:**
- **Context Isolation** - Renderer process isolated from Node.js
- **IPC Security** - All API calls go through preload bridge
- **Input Validation** - Server-side validation of all inputs
- **Session Management** - Secure token-based authentication

---

## **Project Status Summary**

**Current State**: Functional Electron app with authentication, role-based access, and theme system. Database integration partially working with some missing tables and permission issues.

**Core Functionality**: ✅ Working
**Database Integration**: ⚠️ Partially Working  
**UI/UX**: ✅ Complete
**Authentication**: ✅ Working
**Theme System**: ✅ Working (localStorage fallback)

**Ready for**: User testing, database schema completion, and feature expansion.

---

## **Current Error Log (Latest Run)**

```
Error saving user preferences: Error: Table 'u775021278_users_manage.employee_app_preferences' doesn't exist
Error loading user preferences: Error: Table 'u775021278_users_manage.employee_app_preferences' doesn't exist
Error getting dashboard stats: Error: SELECT command denied to user for table `u775021278_Greyline`.`contacts`
Error checking for new submissions: Error: Table 'u775021278_users_manage.contacts' doesn't exist
```

**Last Updated**: December 2024
**Project Version**: 1.0.0
**Electron Version**: 28.0.0

---

## **Quick Reference Commands**

### **Development:**
```bash
npm start          # Start the Electron app
npm run dev        # Start with hot reload (if configured)
```

### **Database Connection:**
- **Host**: Remote MySQL server
- **User**: `u775021278_userAdmin`
- **Primary DB**: `u775021278_users_manage`
- **Secondary DB**: `u775021278_Greyline` (limited access)

### **Key Files:**
- **Main Process**: `main.js`
- **Renderer**: `renderer.js`
- **Database**: `db/connect.js`, `db/employeeService.js`
- **UI**: `index.html`, `admin-dashboard.html`
- **Styles**: `styles/style.css`, `styles/admin.css`

---

## **Notes for Future Development**

1. **Database Schema**: Need to create missing tables for full functionality
2. **Permissions**: Resolve database access issues
3. **Error Handling**: Implement better fallbacks for missing data
4. **Testing**: Add comprehensive testing for all features
5. **Documentation**: Complete API documentation
6. **Performance**: Optimize database queries and UI rendering 