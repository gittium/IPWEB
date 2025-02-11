// Mobile Sidebar Toggle
const menuToggle = document.querySelector('.menu-toggle');
const body = document.body;

menuToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
    body.classList.toggle('nav-open');
});

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

// Highlight Active Navigation
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

// Initialize Dropdown Toggles
function setupDropdown() {
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');

    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const dropdownMenu = toggle.nextElementSibling;
            dropdownMenu.classList.toggle('active');
        });
    });
}

// Load sidebar on DOMContentLoaded
document.addEventListener('DOMContentLoaded', loadSidebar);
