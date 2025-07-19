# Employee App Preferences System - Usage Guide

## Overview
This system allows employees to save and manage their app preferences, user data, recent activities, and bookmarks. All data is stored in the `u775021278_project_manage` database and can be accessed through the provided API endpoints.

## Database Tables Created

### 1. `employee_app_preferences`
Stores core app preferences like theme, notifications, language, etc.

### 2. `employee_app_data`
Flexible storage for any app data (form states, cached data, custom data)

### 3. `employee_recent_activities`
Tracks user activities for history and analytics

### 4. `employee_bookmarks`
Stores user bookmarks for quick access to projects, contacts, files, etc.

## API Endpoints

### Base URL: `https://greylinestudio.com/backend/employee_app_preferences_api.php`

## Usage Examples

### 1. Initialize Preferences Helper in Electron App

```javascript
// In your main Electron app file
const EmployeeAppPreferencesHelper = require('./electron-app-preferences-helper.js');

// Initialize the helper
const preferencesHelper = new EmployeeAppPreferencesHelper();

// Set session token after login
preferencesHelper.setSessionToken(employeeSessionToken);

// Load saved preferences on app startup
async function initializeApp() {
    try {
        const preferences = await preferencesHelper.initializeApp();
        applyPreferencesToUI(preferences);
    } catch (error) {
        console.error('Failed to load preferences:', error);
        // Use default preferences
        const defaultPrefs = preferencesHelper.getDefaultPreferences();
        applyPreferencesToUI(defaultPrefs);
    }
}
```

### 2. Theme Settings (Dark/Light Mode)

```javascript
// Save theme preference
async function saveThemePreference(theme) {
    try {
        await preferencesHelper.saveTheme(theme);
        
        // Apply theme to UI
        if (theme === 'dark') {
            document.body.classList.add('dark-theme');
            document.body.classList.remove('light-theme');
        } else {
            document.body.classList.add('light-theme');
            document.body.classList.remove('dark-theme');
        }
        
        // Log activity
        await preferencesHelper.logActivity('theme_changed', { theme });
        
    } catch (error) {
        console.error('Failed to save theme:', error);
    }
}

// Usage
document.getElementById('darkModeToggle').addEventListener('change', (e) => {
    const theme = e.target.checked ? 'dark' : 'light';
    saveThemePreference(theme);
});
```

### 3. Notification Settings

```javascript
// Save notification preferences
async function saveNotificationPreferences() {
    const notifications = {
        enabled: document.getElementById('notificationsToggle').checked,
        email: document.getElementById('emailNotificationsToggle').checked,
        push: document.getElementById('pushNotificationsToggle').checked
    };
    
    try {
        await preferencesHelper.saveNotificationPreferences(notifications);
        await preferencesHelper.logActivity('notifications_updated', notifications);
    } catch (error) {
        console.error('Failed to save notification preferences:', error);
    }
}

// Usage
document.getElementById('notificationsToggle').addEventListener('change', saveNotificationPreferences);
```

### 4. Font Size Settings

```javascript
// Save font size preference
async function saveFontSize(fontSize) {
    try {
        await preferencesHelper.updatePreference('font_size', fontSize);
        
        // Apply font size to UI
        document.documentElement.style.fontSize = fontSize;
        
        await preferencesHelper.logActivity('font_size_changed', { fontSize });
    } catch (error) {
        console.error('Failed to save font size:', error);
    }
}

// Usage
document.getElementById('fontSizeSelect').addEventListener('change', (e) => {
    saveFontSize(e.target.value);
});
```

### 5. Language Settings

```javascript
// Save language preference
async function saveLanguage(language) {
    try {
        await preferencesHelper.updatePreference('language', language);
        
        // Apply language to UI (you'll need to implement i18n)
        changeLanguage(language);
        
        await preferencesHelper.logActivity('language_changed', { language });
    } catch (error) {
        console.error('Failed to save language:', error);
    }
}

// Usage
document.getElementById('languageSelect').addEventListener('change', (e) => {
    saveLanguage(e.target.value);
});
```

### 6. Auto-start Settings

```javascript
// Save auto-start preference
async function saveAutoStart(enabled) {
    try {
        await preferencesHelper.updatePreference('auto_start', enabled);
        
        // Configure app auto-start (Electron specific)
        if (enabled) {
            app.setLoginItemSettings({
                openAtLogin: true,
                path: app.getPath('exe')
            });
        } else {
            app.setLoginItemSettings({
                openAtLogin: false
            });
        }
        
        await preferencesHelper.logActivity('auto_start_changed', { enabled });
    } catch (error) {
        console.error('Failed to save auto-start setting:', error);
    }
}

// Usage
document.getElementById('autoStartToggle').addEventListener('change', (e) => {
    saveAutoStart(e.target.checked);
});
```

### 7. Reset Settings

```javascript
// Reset all settings to default
async function resetAllSettings() {
    if (confirm('Are you sure you want to reset all settings to default?')) {
        try {
            // Clear all user data
            await preferencesHelper.clearAllUserData();
            
            // Reset to default preferences
            const defaultPrefs = preferencesHelper.getDefaultPreferences();
            await preferencesHelper.updatePreferences(defaultPrefs);
            
            // Apply default preferences to UI
            applyPreferencesToUI(defaultPrefs);
            
            // Reset auto-start
            app.setLoginItemSettings({ openAtLogin: false });
            
            await preferencesHelper.logActivity('settings_reset');
            
            alert('Settings have been reset to default values.');
        } catch (error) {
            console.error('Failed to reset settings:', error);
            alert('Failed to reset settings. Please try again.');
        }
    }
}

// Usage
document.getElementById('resetButton').addEventListener('click', resetAllSettings);
```

### 8. Form State Persistence

```javascript
// Save form state as user types
async function saveFormState(formId, formData) {
    try {
        await preferencesHelper.saveFormState(formId, formData);
    } catch (error) {
        console.error('Failed to save form state:', error);
    }
}

// Restore form state
async function restoreFormState(formId) {
    try {
        const formState = await preferencesHelper.getFormState(formId);
        if (formState.data) {
            // Populate form fields with saved data
            populateForm(formId, JSON.parse(formState.data.data_value));
        }
    } catch (error) {
        console.error('Failed to restore form state:', error);
    }
}

// Usage - Auto-save form every 5 seconds
let formSaveTimer;
document.getElementById('projectForm').addEventListener('input', () => {
    clearTimeout(formSaveTimer);
    formSaveTimer = setTimeout(() => {
        const formData = getFormData('projectForm');
        saveFormState('projectForm', formData);
    }, 5000);
});

// Restore form on page load
document.addEventListener('DOMContentLoaded', () => {
    restoreFormState('projectForm');
});
```

### 9. Project Bookmarks

```javascript
// Add project to bookmarks
async function addProjectToBookmarks(projectId, projectName, projectData) {
    try {
        await preferencesHelper.addProjectBookmark(projectId, projectName, projectData);
        await preferencesHelper.logActivity('project_bookmarked', { projectId, projectName });
        
        // Update UI to show bookmark is added
        updateBookmarkUI(projectId, true);
    } catch (error) {
        console.error('Failed to add bookmark:', error);
    }
}

// Remove project from bookmarks
async function removeProjectBookmark(bookmarkId) {
    try {
        await preferencesHelper.deleteBookmark(bookmarkId);
        await preferencesHelper.logActivity('bookmark_removed', { bookmarkId });
        
        // Update UI
        updateBookmarkUI(bookmarkId, false);
    } catch (error) {
        console.error('Failed to remove bookmark:', error);
    }
}

// Load bookmarks
async function loadBookmarks() {
    try {
        const bookmarks = await preferencesHelper.getBookmarks();
        displayBookmarks(bookmarks.data);
    } catch (error) {
        console.error('Failed to load bookmarks:', error);
    }
}
```

### 10. Recent Activities

```javascript
// Log various activities
async function logUserActivity(activityType, activityData = null) {
    try {
        await preferencesHelper.logActivity(activityType, activityData);
    } catch (error) {
        console.error('Failed to log activity:', error);
    }
}

// Usage examples
logUserActivity('project_viewed', { projectId: 123, projectName: 'Website Redesign' });
logUserActivity('file_uploaded', { fileName: 'design-mockup.psd', projectId: 123 });
logUserActivity('time_logged', { projectId: 123, duration: 120, description: 'Design work' });
logUserActivity('note_added', { projectId: 123, noteType: 'progress' });

// Display recent activities
async function displayRecentActivities() {
    try {
        const activities = await preferencesHelper.getRecentActivities(10);
        renderActivityFeed(activities.data);
    } catch (error) {
        console.error('Failed to load recent activities:', error);
    }
}
```

### 11. Data Export/Import

```javascript
// Export all user data
async function exportUserData() {
    try {
        const userData = await preferencesHelper.exportUserData();
        
        // Create download link
        const blob = new Blob([JSON.stringify(userData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `user-data-${new Date().toISOString().split('T')[0]}.json`;
        a.click();
        URL.revokeObjectURL(url);
        
    } catch (error) {
        console.error('Failed to export user data:', error);
    }
}
```

## Integration with Electron App

### Main Process (main.js)
```javascript
const { app, BrowserWindow } = require('electron');
const EmployeeAppPreferencesHelper = require('./electron-app-preferences-helper.js');

let preferencesHelper;

app.whenReady().then(() => {
    preferencesHelper = new EmployeeAppPreferencesHelper();
    
    // Load saved preferences
    loadSavedPreferences();
    
    createWindow();
});

async function loadSavedPreferences() {
    try {
        const preferences = await preferencesHelper.initializeApp();
        
        // Apply preferences to app
        if (preferences.theme === 'dark') {
            // Set dark theme
        }
        
        if (preferences.auto_start) {
            app.setLoginItemSettings({ openAtLogin: true });
        }
        
    } catch (error) {
        console.error('Failed to load preferences:', error);
    }
}
```

### Renderer Process (renderer.js)
```javascript
// Make preferences helper available to renderer
window.preferencesHelper = preferencesHelper;

// Listen for preference changes from renderer
ipcRenderer.on('save-preference', async (event, { key, value }) => {
    try {
        await preferencesHelper.updatePreference(key, value);
        event.reply('preference-saved', { success: true });
    } catch (error) {
        event.reply('preference-saved', { success: false, error: error.message });
    }
});
```

## Error Handling

```javascript
// Global error handler for preferences
window.addEventListener('unhandledrejection', (event) => {
    if (event.reason.message.includes('preferences')) {
        console.error('Preferences error:', event.reason);
        // Show user-friendly error message
        showNotification('Failed to save preferences. Please try again.', 'error');
    }
});

// Retry mechanism for failed preference saves
async function savePreferenceWithRetry(key, value, maxRetries = 3) {
    for (let i = 0; i < maxRetries; i++) {
        try {
            await preferencesHelper.updatePreference(key, value);
            return true;
        } catch (error) {
            if (i === maxRetries - 1) {
                throw error;
            }
            // Wait before retry
            await new Promise(resolve => setTimeout(resolve, 1000 * (i + 1)));
        }
    }
}
```

## Security Considerations

1. **Session Validation**: All API calls require a valid session token
2. **Data Encryption**: Sensitive data should be encrypted before storage
3. **Input Validation**: Validate all user inputs before saving
4. **Rate Limiting**: Implement rate limiting on API endpoints
5. **Data Expiration**: Set appropriate expiration times for cached data

## Performance Tips

1. **Batch Updates**: Group multiple preference updates together
2. **Debounce Input**: Use debouncing for real-time preference saves
3. **Cache Locally**: Cache frequently accessed preferences in localStorage
4. **Lazy Loading**: Load preferences only when needed
5. **Background Sync**: Sync preferences in the background

This system provides a comprehensive solution for managing employee app preferences and user data in your Electron application! 