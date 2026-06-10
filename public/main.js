// Toggle the sidebar
const sidebarToggleButton = document.getElementById('sidebarToggle');
const mainContent = document.getElementById('mainContent');

sidebarToggleButton.addEventListener('click', function () {
    mainContent.classList.toggle('sidebar-open');
});