// Electron App API Configuration
// This file contains all the API endpoints and configuration for your electron app

const API_CONFIG = {
    // Base URL for your website
    BASE_URL: 'https://greylinestudio.com',
    
    // API Endpoints
    ENDPOINTS: {
        // Get all projects with notes (for employee dashboard)
        GET_ALL_PROJECTS: '/backend/get_all_projects.php',
        
        // Notes management
        NOTES_API: '/backend/notes_api.php',
        
        // User management
        LOGIN: '/backend/login_user.php',
        REGISTER: '/backend/register_user.php',
        GET_USER_PROJECTS: '/backend/get_user_projects.php'
    },
    
    // API Headers
    HEADERS: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

// API Helper Functions
class APIHelper {
    constructor() {
        this.baseURL = API_CONFIG.BASE_URL;
        this.headers = API_CONFIG.HEADERS;
    }

    // Generic API request function
    async makeRequest(endpoint, method = 'GET', data = null) {
        try {
            const url = `${this.baseURL}${endpoint}`;
            const options = {
                method: method,
                headers: this.headers,
                body: data ? JSON.stringify(data) : null
            };

            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'API request failed');
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // Get all projects with notes (for employee dashboard)
    async getAllProjects() {
        return await this.makeRequest(API_CONFIG.ENDPOINTS.GET_ALL_PROJECTS);
    }

    // Notes API functions
    async getNotes(userId, contactId) {
        return await this.makeRequest(
            `${API_CONFIG.ENDPOINTS.NOTES_API}?user_id=${userId}&contact_id=${contactId}`
        );
    }

    async createNote(noteData) {
        return await this.makeRequest(API_CONFIG.ENDPOINTS.NOTES_API, 'POST', noteData);
    }

    async updateNote(noteData) {
        return await this.makeRequest(API_CONFIG.ENDPOINTS.NOTES_API, 'PUT', noteData);
    }

    async deleteNote(noteId) {
        return await this.makeRequest(API_CONFIG.ENDPOINTS.NOTES_API, 'DELETE', { id: noteId });
    }

    // User authentication
    async loginUser(credentials) {
        return await this.makeRequest(API_CONFIG.ENDPOINTS.LOGIN, 'POST', credentials);
    }

    async getUserProjects(userId) {
        return await this.makeRequest(API_CONFIG.ENDPOINTS.GET_USER_PROJECTS, 'POST', { user_id: userId });
    }
}

// Example usage for Electron app
class ElectronNotesManager {
    constructor() {
        this.api = new APIHelper();
    }

    // Load all projects for employee dashboard
    async loadAllProjects() {
        try {
            const result = await this.api.getAllProjects();
            return result.data;
        } catch (error) {
            console.error('Failed to load projects:', error);
            throw error;
        }
    }

    // Add a note to a project
    async addNote(userId, contactId, noteTitle, noteContent, noteType = 'general', priority = 'medium') {
        try {
            const noteData = {
                user_id: userId,
                contact_id: contactId,
                note_title: noteTitle,
                note_content: noteContent,
                note_type: noteType,
                priority: priority,
                created_by: 'employee'
            };

            const result = await this.api.createNote(noteData);
            return result.note;
        } catch (error) {
            console.error('Failed to add note:', error);
            throw error;
        }
    }

    // Update a note
    async updateNote(noteId, noteTitle, noteContent, noteType, priority) {
        try {
            const noteData = {
                id: noteId,
                note_title: noteTitle,
                note_content: noteContent,
                note_type: noteType,
                priority: priority
            };

            const result = await this.api.updateNote(noteData);
            return result.note;
        } catch (error) {
            console.error('Failed to update note:', error);
            throw error;
        }
    }

    // Delete a note
    async deleteNote(noteId) {
        try {
            await this.api.deleteNote(noteId);
            return true;
        } catch (error) {
            console.error('Failed to delete note:', error);
            throw error;
        }
    }

    // Get notes for a specific project
    async getProjectNotes(userId, contactId) {
        try {
            const result = await this.api.getNotes(userId, contactId);
            return result.notes;
        } catch (error) {
            console.error('Failed to get project notes:', error);
            throw error;
        }
    }
}

// Export for use in Electron app
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        API_CONFIG,
        APIHelper,
        ElectronNotesManager
    };
}

// For browser usage
if (typeof window !== 'undefined') {
    window.API_CONFIG = API_CONFIG;
    window.APIHelper = APIHelper;
    window.ElectronNotesManager = ElectronNotesManager;
} 