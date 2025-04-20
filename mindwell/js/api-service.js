const API_BASE_URL = '/api';

class ApiService {
    // User related methods
    static async getUser() {
        try {
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            if (!response.ok) throw new Error('Failed to fetch user data');
            return await response.json();
        } catch (error) {
            console.error('Error fetching user:', error);
            return null;
        }
    }

    static async updateUser(userData) {
        try {
            const response = await fetch(`${API_BASE_URL}/login.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(userData)
            });
            if (!response.ok) throw new Error('Failed to update user data');
            return await response.json();
        } catch (error) {
            console.error('Error updating user:', error);
            return null;
        }
    }

    // Mood tracking methods
    static async getMoodHistory() {
        try {
            const response = await fetch(`${API_BASE_URL}/mood.php`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            if (!response.ok) throw new Error('Failed to fetch mood history');
            return await response.json();
        } catch (error) {
            console.error('Error fetching mood history:', error);
            return [];
        }
    }

    static async saveMood(moodData) {
        try {
            const response = await fetch(`${API_BASE_URL}/mood.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(moodData)
            });
            if (!response.ok) throw new Error('Failed to save mood');
            return await response.json();
        } catch (error) {
            console.error('Error saving mood:', error);
            return null;
        }
    }

    // Assessment methods
    static async getAssessmentHistory() {
        try {
            const response = await fetch(`${API_BASE_URL}/assessment.php`, {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                }
            });
            if (!response.ok) throw new Error('Failed to fetch assessment history');
            return await response.json();
        } catch (error) {
            console.error('Error fetching assessment history:', error);
            return [];
        }
    }

    static async saveAssessment(assessmentData) {
        try {
            const response = await fetch(`${API_BASE_URL}/assessment.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify(assessmentData)
            });
            if (!response.ok) throw new Error('Failed to save assessment');
            return await response.json();
        } catch (error) {
            console.error('Error saving assessment:', error);
            return null;
        }
    }
}

// Export the service
window.ApiService = ApiService; 