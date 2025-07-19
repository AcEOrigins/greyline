/**
 * Employee App Preferences Helper Class
 * Manages app preferences, user data, recent activities, and bookmarks
 */
class EmployeeAppPreferencesHelper {
    constructor(baseUrl = 'https://greylinestudio.com/backend') {
        this.baseUrl = baseUrl;
        this.sessionToken = null;
    }

    /**
     * Set session token for authentication
     */
    setSessionToken(token) {
        this.sessionToken = token;
    }

    /**
     * Get headers for API requests
     */
    getHeaders() {
        const headers = {
            'Content-Type': 'application/json'
        };
        
        if (this.sessionToken) {
            headers['Authorization'] = this.sessionToken;
        }
        
        return headers;
    }

    /**
     * Make API request
     */
    async makeRequest(endpoint, options = {}) {
        try {
            const url = `${this.baseUrl}/${endpoint}`;
            const response = await fetch(url, {
                headers: this.getHeaders(),
                ...options
            });

            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'API request failed');
            }

            return data;
        } catch (error) {
            console.error('API request error:', error);
            throw error;
        }
    }

    // ===== APP PREFERENCES =====

    /**
     * Get employee app preferences
     */
    async getPreferences() {
        return this.makeRequest('employee_app_preferences_api.php?action=preferences');
    }

    /**
     * Update app preferences
     */
    async updatePreferences(preferences) {
        return this.makeRequest('employee_app_preferences_api.php?action=preferences', {
            method: 'POST',
            body: JSON.stringify(preferences)
        });
    }

    /**
     * Update specific preference
     */
    async updatePreference(key, value) {
        const update = {};
        update[key] = value;
        return this.updatePreferences(update);
    }

    // ===== APP DATA STORAGE =====

    /**
     * Store app data
     */
    async storeAppData(key, value, type = 'custom', expiresAt = null) {
        return this.makeRequest('employee_app_preferences_api.php?action=app_data', {
            method: 'POST',
            body: JSON.stringify({
                key,
                value,
                type,
                expires_at: expiresAt
            })
        });
    }

    /**
     * Get app data by key
     */
    async getAppData(key) {
        return this.makeRequest(`employee_app_preferences_api.php?action=app_data&key=${encodeURIComponent(key)}`);
    }

    /**
     * Get all app data
     */
    async getAllAppData() {
        return this.makeRequest('employee_app_preferences_api.php?action=app_data');
    }

    /**
     * Delete app data
     */
    async deleteAppData(key) {
        return this.makeRequest(`employee_app_preferences_api.php?action=app_data&key=${encodeURIComponent(key)}`, {
            method: 'DELETE'
        });
    }

    // ===== RECENT ACTIVITIES =====

    /**
     * Log recent activity
     */
    async logActivity(activityType, activityData = null) {
        return this.makeRequest('employee_app_preferences_api.php?action=recent_activity', {
            method: 'POST',
            body: JSON.stringify({
                activity_type: activityType,
                activity_data: activityData
            })
        });
    }

    /**
     * Get recent activities
     */
    async getRecentActivities(limit = 20) {
        return this.makeRequest(`employee_app_preferences_api.php?action=recent_activities&limit=${limit}`);
    }

    /**
     * Clear recent activities
     */
    async clearRecentActivities() {
        return this.makeRequest('employee_app_preferences_api.php?action=recent_activities', {
            method: 'DELETE'
        });
    }

    // ===== BOOKMARKS =====

    /**
     * Add bookmark
     */
    async addBookmark(type, name, data, sortOrder = 0) {
        return this.makeRequest('employee_app_preferences_api.php?action=bookmark', {
            method: 'POST',
            body: JSON.stringify({
                bookmark_type: type,
                bookmark_name: name,
                bookmark_data: data,
                sort_order: sortOrder
            })
        });
    }

    /**
     * Get bookmarks
     */
    async getBookmarks() {
        return this.makeRequest('employee_app_preferences_api.php?action=bookmarks');
    }

    /**
     * Delete bookmark
     */
    async deleteBookmark(id) {
        return this.makeRequest(`employee_app_preferences_api.php?action=bookmark&id=${id}`, {
            method: 'DELETE'
        });
    }

    // ===== CONVENIENCE METHODS =====

    /**
     * Initialize app with saved preferences
     */
    async initializeApp() {
        try {
            const preferences = await this.getPreferences();
            return preferences.data;
        } catch (error) {
            console.error('Failed to load preferences:', error);
            return this.getDefaultPreferences();
        }
    }

    /**
     * Get default preferences
     */
    getDefaultPreferences() {
        return {
            theme: 'dark',
            language: 'en',
            timezone: 'UTC',
            date_format: 'MM/DD/YYYY',
            time_format: '12h',
            notifications_enabled: true,
            email_notifications: true,
            push_notifications: true,
            dashboard_layout: null,
            sidebar_collapsed: false,
            auto_refresh_interval: 300,
            default_view: 'projects'
        };
    }

    /**
     * Save dashboard layout
     */
    async saveDashboardLayout(layout) {
        return this.updatePreference('dashboard_layout', layout);
    }

    /**
     * Save theme preference
     */
    async saveTheme(theme) {
        return this.updatePreference('theme', theme);
    }

    /**
     * Save sidebar state
     */
    async saveSidebarState(collapsed) {
        return this.updatePreference('sidebar_collapsed', collapsed);
    }

    /**
     * Save notification preferences
     */
    async saveNotificationPreferences(notifications) {
        return this.updatePreferences({
            notifications_enabled: notifications.enabled,
            email_notifications: notifications.email,
            push_notifications: notifications.push
        });
    }

    /**
     * Cache project data
     */
    async cacheProjectData(projectId, data, expiresInHours = 24) {
        const expiresAt = new Date();
        expiresAt.setHours(expiresAt.getHours() + expiresInHours);
        
        return this.storeAppData(`project_${projectId}`, data, 'cache', expiresAt.toISOString());
    }

    /**
     * Get cached project data
     */
    async getCachedProjectData(projectId) {
        return this.getAppData(`project_${projectId}`);
    }

    /**
     * Save form state
     */
    async saveFormState(formId, formData) {
        return this.storeAppData(`form_${formId}`, formData, 'state');
    }

    /**
     * Get saved form state
     */
    async getFormState(formId) {
        return this.getAppData(`form_${formId}`);
    }

    /**
     * Clear form state
     */
    async clearFormState(formId) {
        return this.deleteAppData(`form_${formId}`);
    }

    /**
     * Log project view
     */
    async logProjectView(projectId, projectName) {
        return this.logActivity('project_viewed', {
            project_id: projectId,
            project_name: projectName,
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Log file upload
     */
    async logFileUpload(fileName, projectId) {
        return this.logActivity('file_uploaded', {
            file_name: fileName,
            project_id: projectId,
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Log time entry
     */
    async logTimeEntry(projectId, duration, description) {
        return this.logActivity('time_logged', {
            project_id: projectId,
            duration: duration,
            description: description,
            timestamp: new Date().toISOString()
        });
    }

    /**
     * Add project bookmark
     */
    async addProjectBookmark(projectId, projectName, projectData) {
        return this.addBookmark('project', projectName, {
            project_id: projectId,
            ...projectData
        });
    }

    /**
     * Add contact bookmark
     */
    async addContactBookmark(contactId, contactName, contactData) {
        return this.addBookmark('contact', contactName, {
            contact_id: contactId,
            ...contactData
        });
    }

    /**
     * Add file bookmark
     */
    async addFileBookmark(fileId, fileName, fileData) {
        return this.addBookmark('file', fileName, {
            file_id: fileId,
            ...fileData
        });
    }

    /**
     * Add URL bookmark
     */
    async addUrlBookmark(url, title, description = '') {
        return this.addBookmark('url', title, {
            url: url,
            description: description
        });
    }

    /**
     * Export all user data
     */
    async exportUserData() {
        try {
            const [preferences, appData, activities, bookmarks] = await Promise.all([
                this.getPreferences(),
                this.getAllAppData(),
                this.getRecentActivities(1000),
                this.getBookmarks()
            ]);

            return {
                preferences: preferences.data,
                app_data: appData.data,
                recent_activities: activities.data,
                bookmarks: bookmarks.data,
                exported_at: new Date().toISOString()
            };
        } catch (error) {
            console.error('Failed to export user data:', error);
            throw error;
        }
    }

    /**
     * Clear all user data
     */
    async clearAllUserData() {
        try {
            await Promise.all([
                this.clearRecentActivities(),
                this.getAllAppData().then(data => {
                    const deletePromises = data.data.map(item => 
                        this.deleteAppData(item.data_key)
                    );
                    return Promise.all(deletePromises);
                }),
                this.getBookmarks().then(data => {
                    const deletePromises = data.data.map(bookmark => 
                        this.deleteBookmark(bookmark.id)
                    );
                    return Promise.all(deletePromises);
                })
            ]);

            return { success: true, message: 'All user data cleared successfully' };
        } catch (error) {
            console.error('Failed to clear user data:', error);
            throw error;
        }
    }
}

// Export for use in Electron app
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EmployeeAppPreferencesHelper;
} 