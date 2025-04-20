// Theme Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    // Check for saved theme preference or use user's system preference
    const userTheme = localStorage.getItem('theme');
    const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    
    // Set initial theme
    if (userTheme === 'dark' || (!userTheme && systemTheme === 'dark')) {
      document.documentElement.classList.add('dark');
      updateThemeIcons(true);
    } else {
      updateThemeIcons(false);
    }
    
    // Toggle theme when button is clicked
    const themeToggleBtn = document.getElementById('theme-toggle');
    if (themeToggleBtn) {
      themeToggleBtn.addEventListener('click', function() {
        // Check current theme
        const isDarkMode = document.documentElement.classList.contains('dark');
        
        // Toggle theme
        if (isDarkMode) {
          document.documentElement.classList.remove('dark');
          localStorage.setItem('theme', 'light');
        } else {
          document.documentElement.classList.add('dark');
          localStorage.setItem('theme', 'dark');
        }
        
        // Update icons
        updateThemeIcons(!isDarkMode);
      });
    }
    
    // Function to update the theme toggle icons
    function updateThemeIcons(isDarkMode) {
      const darkIcon = document.getElementById('theme-toggle-dark-icon');
      const lightIcon = document.getElementById('theme-toggle-light-icon');
      
      if (isDarkMode) {
        darkIcon.classList.remove('hidden');
        lightIcon.classList.add('hidden');
      } else {
        darkIcon.classList.add('hidden');
        lightIcon.classList.remove('hidden');
      }
    }
  });