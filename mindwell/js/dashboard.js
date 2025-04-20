// Check authentication status
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    
    if (!token) {
        window.location.href = 'login.html';
        return;
    }

    // Update user info
    document.getElementById('user-name').textContent = user.name || 'User';
    document.getElementById('user-email').textContent = user.email || '';
    
    // Load dashboard data
    loadDashboardData();
    
    // Setup event listeners
    setupEventListeners();
});

// Load dashboard data
async function loadDashboardData() {
    try {
        const response = await fetch('api/dashboard.php', {
            headers: {
                'Authorization': `Bearer ${localStorage.getItem('token')}`
            }
        });
        
        if (!response.ok) {
            throw new Error('Failed to load dashboard data');
        }
        
        const data = await response.json();
        
        // Update mood chart
        updateMoodChart(data.moodData);
        
        // Update activity feed
        updateActivityFeed(data.activities);
        
        // Update statistics
        updateStatistics(data.stats);
        
    } catch (error) {
        console.error('Error loading dashboard data:', error);
        showError('Failed to load dashboard data. Please try again later.');
    }
}

// Update mood chart
function updateMoodChart(moodData) {
    const ctx = document.getElementById('moodChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: moodData.labels,
            datasets: [{
                label: 'Mood Score',
                data: moodData.scores,
                borderColor: '#0d948c',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(13, 148, 140, 0.1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10
                }
            }
        }
    });
}

// Update activity feed
function updateActivityFeed(activities) {
    const feedContainer = document.getElementById('activity-feed');
    feedContainer.innerHTML = activities.map(activity => `
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm mb-4">
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900 flex items-center justify-center">
                    <i class="fas ${getActivityIcon(activity.type)} text-teal-600 dark:text-teal-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-gray-800 dark:text-white">${activity.description}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">${formatDate(activity.timestamp)}</p>
                </div>
            </div>
        </div>
    `).join('');
}

// Update statistics
function updateStatistics(stats) {
    document.getElementById('mood-average').textContent = stats.moodAverage.toFixed(1);
    document.getElementById('journal-entries').textContent = stats.journalEntries;
    document.getElementById('completed-assessments').textContent = stats.completedAssessments;
}

// Setup event listeners
function setupEventListeners() {
    // Logout button
    document.getElementById('logout-btn').addEventListener('click', function() {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        window.location.href = 'login.html';
    });
    
    // Mood tracking form
    document.getElementById('mood-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const moodScore = document.getElementById('mood-score').value;
        const notes = document.getElementById('mood-notes').value;
        
        try {
            const response = await fetch('api/mood.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: JSON.stringify({ score: moodScore, notes })
            });
            
            if (!response.ok) {
                throw new Error('Failed to save mood entry');
            }
            
            this.reset();
            loadDashboardData(); // Reload dashboard data
            showSuccess('Mood entry saved successfully!');
            
        } catch (error) {
            console.error('Error saving mood entry:', error);
            showError('Failed to save mood entry. Please try again.');
        }
    });
}

// Helper functions
function getActivityIcon(type) {
    const icons = {
        'mood': 'fa-smile',
        'journal': 'fa-book',
        'assessment': 'fa-clipboard-check',
        'login': 'fa-sign-in-alt'
    };
    return icons[type] || 'fa-circle';
}

function formatDate(timestamp) {
    return new Date(timestamp).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function showError(message) {
    const alert = document.createElement('div');
    alert.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded';
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
}

function showSuccess(message) {
    const alert = document.createElement('div');
    alert.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded';
    alert.textContent = message;
    document.body.appendChild(alert);
    setTimeout(() => alert.remove(), 3000);
} 