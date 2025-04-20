// Mood Tracker Module
const MoodTracker = {
    chart: null,
    
    // Initialize the mood tracker
    init: function() {
        this.setupMoodChart();
        this.loadMoodData();
        this.setupEventListeners();
    },
    
    // Set up the mood chart using Chart.js
    setupMoodChart: function() {
        const ctx = document.getElementById('moodChart').getContext('2d');
        this.chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Mood Score',
                    data: [],
                    borderColor: '#0d9488',
                    backgroundColor: 'rgba(13, 148, 136, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Mood: ${context.parsed.y}`;
                            }
                        }
                    }
                }
            }
        });
    },
    
    // Load mood data from the API
    loadMoodData: async function() {
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = 'login.html';
                return;
            }
            
            const response = await fetch('api/mood.php', {
                headers: {
                    'Authorization': `Bearer ${token}`
                }
            });
            
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }
            
            this.updateChart(data.data);
            
        } catch (error) {
            console.error('Error loading mood data:', error);
            this.showError('Failed to load mood data. Please try again later.');
        }
    },
    
    // Update the chart with new data
    updateChart: function(entries) {
        const labels = [];
        const scores = [];
        
        entries.forEach(entry => {
            labels.push(new Date(entry.created_at).toLocaleDateString());
            scores.push(entry.mood_score);
        });
        
        this.chart.data.labels = labels;
        this.chart.data.datasets[0].data = scores;
        this.chart.update();
    },
    
    // Set up event listeners
    setupEventListeners: function() {
        const form = document.getElementById('mood-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                await this.submitMoodEntry(e.target);
            });
        }
        
        // Mood score buttons
        document.querySelectorAll('.mood-score-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.mood-score-btn').forEach(b => b.classList.remove('selected'));
                btn.classList.add('selected');
                document.getElementById('mood-score').value = btn.dataset.score;
            });
        });
    },
    
    // Submit new mood entry
    submitMoodEntry: async function(form) {
        try {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = 'login.html';
                return;
            }
            
            const moodScore = document.getElementById('mood-score').value;
            const notes = document.getElementById('mood-notes').value;
            
            if (!moodScore) {
                this.showError('Please select a mood score');
                return;
            }
            
            const response = await fetch('api/mood.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    mood_score: parseInt(moodScore),
                    notes: notes
                })
            });
            
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }
            
            // Show success message
            this.showSuccess('Mood entry recorded successfully!');
            
            // Reset form
            form.reset();
            document.querySelectorAll('.mood-score-btn').forEach(btn => btn.classList.remove('selected'));
            
            // Reload mood data
            await this.loadMoodData();
            
        } catch (error) {
            console.error('Error submitting mood entry:', error);
            this.showError('Failed to submit mood entry. Please try again.');
        }
    },
    
    // Show error message
    showError: function(message) {
        const alert = document.getElementById('mood-alert');
        if (alert) {
            alert.textContent = message;
            alert.classList.remove('hidden', 'bg-green-50', 'text-green-800', 'border-green-500');
            alert.classList.add('bg-red-50', 'text-red-800', 'border-red-500');
            setTimeout(() => alert.classList.add('hidden'), 5000);
        }
    },
    
    // Show success message
    showSuccess: function(message) {
        const alert = document.getElementById('mood-alert');
        if (alert) {
            alert.textContent = message;
            alert.classList.remove('hidden', 'bg-red-50', 'text-red-800', 'border-red-500');
            alert.classList.add('bg-green-50', 'text-green-800', 'border-green-500');
            setTimeout(() => alert.classList.add('hidden'), 5000);
        }
    }
};

// Initialize mood tracker when the document is ready
document.addEventListener('DOMContentLoaded', () => {
    MoodTracker.init();
}); 