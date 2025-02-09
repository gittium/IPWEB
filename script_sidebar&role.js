// Function to load the sidebar dynamically
function loadSidebar() {
    fetch('sidebar.html')
        .then(response => response.text())
        .then(data => {
            document.getElementById('sidebar-container').innerHTML = data;
            setupActiveNav();
        })
        .catch(error => console.error('Error loading sidebar:', error));
}

// Function to handle active navigation highlighting
function setupActiveNav() {
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        const link = item.getAttribute('href');
        if (link && currentPath.includes(link)) {
            item.classList.add('active');
        }
    });
}
function toggleDropdown() {
    const dropdownMenu = document.querySelector('.dropdown-menu');
    dropdownMenu.classList.toggle('active');
}

document.addEventListener('DOMContentLoaded', () => {
    const currentPath = window.location.pathname;
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        const link = item.getAttribute('href');
        if (link && currentPath.includes(link)) {
            item.classList.add('active');
        }
    });
});

// Load sidebar on page load
document.addEventListener('DOMContentLoaded', loadSidebar);
