// scripts.js

async function loadSidebar() {
    try {
        const response = await fetch('sidebar.html');
        if (!response.ok) throw new Error('Network response was not ok');
        const sidebarHTML = await response.text();
        document.getElementById('sidebar-placeholder').innerHTML = sidebarHTML;
        
        // highlight the active nav item
        const currentPage = window.location.pathname.split('/').pop();
        document.querySelectorAll('.nav-item').forEach(item => {
            if (item.getAttribute('href') === currentPage) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    } catch (error) {
        console.error('Failed to load sidebar:', error);
    }
}

function initializeSharedInteractivity() {
    // e.g. global hover events, or notifications
}

document.addEventListener('DOMContentLoaded', () => {
    loadSidebar();
    initializeSharedInteractivity();
});
