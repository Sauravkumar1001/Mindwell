// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Get references to sections and buttons
    const introSection = document.getElementById('assessment-intro');
    const questionsSection = document.getElementById('assessment-questions');
    const resultsSection = document.getElementById('assessment-results');
    
    const startButton = document.getElementById('start-assessment');
    const prevButton = document.getElementById('prev-question');
    const nextButton = document.getElementById('next-question');
    const viewResourcesButton = document.getElementById('view-resources');
    const retakeButton = document.getElementById('retake-assessment');
    
    // Question container and progress elements
    const questionContainer = document.getElementById('question-container');
    const currentQuestionSpan = document.getElementById('current-question');
    const totalQuestionsSpan = document.getElementById('total-questions');
    const progressBar = document.getElementById('progress-bar');
    const currentCategorySpan = document.getElementById('current-category');
    
    // Sample questions (you would replace these with your actual questions)
    const questions = [
        {
            category: "Mood & Emotions",
            text: "Over the past two weeks, how often have you felt down, depressed, or hopeless?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Mood & Emotions",
            text: "Over the past two weeks, how often have you had little interest or pleasure in doing things?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Anxiety",
            text: "Over the past two weeks, how often have you been feeling nervous, anxious, or on edge?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Anxiety",
            text: "Over the past two weeks, how often have you felt afraid as if something awful might happen?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Stress",
            text: "Over the past two weeks, how often have you found it difficult to relax?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Stress",
            text: "Over the past two weeks, how often have you become easily annoyed or irritable?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Energy",
            text: "Over the past two weeks, how often have you felt tired or had little energy?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Energy",
            text: "Over the past two weeks, how often have you had trouble falling or staying asleep, or sleeping too much?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Social",
            text: "Over the past two weeks, how often have you avoided social situations or interactions?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Social",
            text: "Over the past two weeks, how often have you felt lonely or isolated?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Mood & Emotions",
            text: "Over the past two weeks, how often have you felt bad about yourself or that you are a failure?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Anxiety",
            text: "Over the past two weeks, how often have you been unable to stop or control worrying?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Stress",
            text: "Over the past two weeks, how often have you felt that difficulties were piling up so high that you could not overcome them?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Energy",
            text: "Over the past two weeks, how often have you had trouble concentrating on things?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        },
        {
            category: "Social",
            text: "Over the past two weeks, how often have you experienced difficulty engaging in activities with others?",
            options: ["Not at all", "Several days", "More than half the days", "Nearly every day"]
        }
    ];
    
    // Variables to track progress
    let currentQuestionIndex = 0;
    const answers = new Array(questions.length).fill(null);
    
    // Initialize total questions display
    totalQuestionsSpan.textContent = questions.length;
    
    // Function to show a specific section and hide others
    function showSection(sectionToShow) {
        introSection.classList.remove('active-section');
        questionsSection.classList.remove('active-section');
        resultsSection.classList.remove('active-section');
        
        sectionToShow.classList.add('active-section');
    }
    
    // Function to display the current question
    function displayCurrentQuestion() {
        const question = questions[currentQuestionIndex];
        currentQuestionSpan.textContent = currentQuestionIndex + 1;
        currentCategorySpan.textContent = question.category;
        
        // Update progress bar
        const progressPercentage = ((currentQuestionIndex + 1) / questions.length) * 100;
        progressBar.style.width = `${progressPercentage}%`;
        
        // Create question HTML
        questionContainer.innerHTML = `
            <h4 class="text-lg font-medium mb-4 text-gray-800">${question.text}</h4>
            <div class="space-y-3">
                ${question.options.map((option, index) => `
                    <div class="flex items-center">
                        <input type="radio" id="option-${index}" name="question-option" value="${index}" 
                               ${answers[currentQuestionIndex] === index ? 'checked' : ''} class="h-4 w-4 text-teal-600">
                        <label for="option-${index}" class="ml-2 text-gray-700">${option}</label>
                    </div>
                `).join('')}
            </div>
        `;
        
        // Enable/disable nav buttons as needed
        prevButton.disabled = currentQuestionIndex === 0;
        nextButton.textContent = currentQuestionIndex === questions.length - 1 ? "Finish" : "Next";
        
        // Add event listeners for radio buttons
        document.querySelectorAll('input[name="question-option"]').forEach((radio, index) => {
            radio.addEventListener('change', () => {
                answers[currentQuestionIndex] = index;
            });
        });
    }
    
    // Function to calculate and display results
    function showResults() {
        // Calculate scores by category
        const categories = ["Mood & Emotions", "Anxiety", "Stress", "Energy", "Social"];
        const scores = {};
        
        categories.forEach(category => {
            const categoryQuestions = questions.filter(q => q.category === category);
            const categoryAnswers = [];
            
            categoryQuestions.forEach(q => {
                const index = questions.indexOf(q);
                if (answers[index] !== null) {
                    categoryAnswers.push(answers[index]);
                }
            });
            
            // Calculate score (assuming higher values mean more severe symptoms)
            if (categoryAnswers.length > 0) {
                const categoryScore = categoryAnswers.reduce((sum, val) => sum + val, 0) / categoryAnswers.length;
                scores[category] = (categoryScore / 3) * 100; // Normalize to percentage (0-3 scale to 0-100%)
            } else {
                scores[category] = 0;
            }
        });
        
        // Create bars for chart
        const resultsChart = document.getElementById('results-chart');
        resultsChart.innerHTML = '';
        
        categories.forEach(category => {
            const score = scores[category];
            
            // Determine bar color based on score
            let barColor;
            if (score < 33) {
                barColor = 'bg-green-500';
            } else if (score < 66) {
                barColor = 'bg-yellow-500';
            } else {
                barColor = 'bg-red-500';
            }
            
            const bar = document.createElement('div');
            bar.className = `${barColor} relative w-12 rounded-t-lg`;
            bar.style.height = `${score}%`;
            
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100';
            tooltip.textContent = `${Math.round(score)}%`;
            
            bar.appendChild(tooltip);
            resultsChart.appendChild(bar);
        });
        
        // Create results summary
        const resultsSummary = document.getElementById('results-summary');
        
        // Calculate overall mental health status
        const avgScore = Object.values(scores).reduce((sum, score) => sum + score, 0) / categories.length;
        let overallStatus = "Healthy";
        
        if (avgScore >= 66) {
            overallStatus = "Significantly Elevated";
        } else if (avgScore >= 33) {
            overallStatus = "Moderately Elevated";
        }
        
        // Create suitable advice based on overall status
        let advice = "";
        if (overallStatus === "Healthy") {
            advice = "Your responses suggest you're generally doing well. Continue practicing good self-care and monitoring your mental health.";
        } else if (overallStatus === "Moderately Elevated") {
            advice = "Your responses suggest some areas of concern. Consider implementing self-care strategies and reaching out to a mental health professional if symptoms persist.";
        } else {
            advice = "Your responses suggest significant concerns in multiple areas. We strongly recommend consulting with a mental health professional for proper evaluation and support.";
        }
        
        resultsSummary.innerHTML = `
            <h4 class="text-lg font-semibold mb-2 text-gray-800">Overall Status: <span class="text-teal-600">${overallStatus}</span></h4>
            <p class="text-gray-600 mb-4">${advice}</p>
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <p class="text-sm text-yellow-700">
                    <strong>Remember:</strong> This assessment is not a diagnosis. It's meant to help you understand potential areas of concern.
                </p>
            </div>
        `;
        
        // Show results section
        showSection(resultsSection);
    }
    
    // Add click event for the start button
    startButton.addEventListener('click', function() {
        showSection(questionsSection);
        displayCurrentQuestion();
    });
    
    // Add click event for the previous button
    prevButton.addEventListener('click', function() {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            displayCurrentQuestion();
        }
    });
    
    // Add click event for the next button
    nextButton.addEventListener('click', function() {
        // Check if an option is selected
        const selectedOption = document.querySelector('input[name="question-option"]:checked');
        
        if (!selectedOption && answers[currentQuestionIndex] === null) {
            alert("Please select an option before proceeding.");
            return;
        }
        
        if (selectedOption) {
            answers[currentQuestionIndex] = parseInt(selectedOption.value);
        }
        
        if (currentQuestionIndex < questions.length - 1) {
            currentQuestionIndex++;
            displayCurrentQuestion();
        } else {
            showResults();
        }
    });
    
    // Add click event for the view resources button
    viewResourcesButton.addEventListener('click', function() {
        alert("This feature would connect to your resource database. For now, we recommend consulting with a mental health professional for personalized resources.");
    });
    
    // Add click event for the retake button
    retakeButton.addEventListener('click', function() {
        // Reset answers and question index
        currentQuestionIndex = 0;
        answers.fill(null);
        
        // Show the questions section again
        showSection(questionsSection);
        displayCurrentQuestion();
    });
    
    // Add CSS to hide non-active sections
    const style = document.createElement('style');
    style.textContent = `
        .assessment-section {
            display: none;
        }
        .active-section {
            display: block;
        }
    `;
    document.head.appendChild(style);
});