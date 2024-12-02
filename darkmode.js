// Select the toggle switch
const toggleSwitch = document.getElementById('toggle-switch');

// Add event listener for toggling dark mode
toggleSwitch.addEventListener('click', () => {
    const bodyClassList = document.body.classList;
    
    // Toggle dark mode
    if (bodyClassList.contains('darkmode')) {
        bodyClassList.remove('darkmode');
        localStorage.setItem('theme', 'light'); // Save preference
    } else {
        bodyClassList.add('darkmode');
        localStorage.setItem('theme', 'dark'); // Save preference
    }
});

// Check localStorage for saved theme on page load
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('darkmode');
    }
});
