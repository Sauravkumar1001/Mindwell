const express = require('express');
const cors = require('cors');
const bodyParser = require('body-parser');
const path = require('path');
const fs = require('fs');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(express.static(path.join(__dirname)));

// Data storage
const DATA_DIR = path.join(__dirname, 'data');
if (!fs.existsSync(DATA_DIR)) {
    fs.mkdirSync(DATA_DIR);
}

// Routes
app.get('/api/user', (req, res) => {
    const userData = JSON.parse(fs.readFileSync(path.join(DATA_DIR, 'users.json'), 'utf8') || '{}');
    res.json(userData);
});

app.post('/api/user', (req, res) => {
    const userData = req.body;
    fs.writeFileSync(path.join(DATA_DIR, 'users.json'), JSON.stringify(userData));
    res.json({ success: true });
});

app.get('/api/mood-history', (req, res) => {
    const moodHistory = JSON.parse(fs.readFileSync(path.join(DATA_DIR, 'mood-history.json'), 'utf8') || '[]');
    res.json(moodHistory);
});

app.post('/api/mood-history', (req, res) => {
    const moodData = req.body;
    const moodHistory = JSON.parse(fs.readFileSync(path.join(DATA_DIR, 'mood-history.json'), 'utf8') || '[]');
    moodHistory.push(moodData);
    fs.writeFileSync(path.join(DATA_DIR, 'mood-history.json'), JSON.stringify(moodHistory));
    res.json({ success: true });
});

app.get('/api/assessment-history', (req, res) => {
    const assessmentHistory = JSON.parse(fs.readFileSync(path.join(DATA_DIR, 'assessment-history.json'), 'utf8') || '[]');
    res.json(assessmentHistory);
});

app.post('/api/assessment-history', (req, res) => {
    const assessmentData = req.body;
    const assessmentHistory = JSON.parse(fs.readFileSync(path.join(DATA_DIR, 'assessment-history.json'), 'utf8') || '[]');
    assessmentHistory.push(assessmentData);
    fs.writeFileSync(path.join(DATA_DIR, 'assessment-history.json'), JSON.stringify(assessmentHistory));
    res.json({ success: true });
});

// Start server
app.listen(PORT, () => {
    console.log(`Server running on port ${PORT}`);
}); 