document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toast').forEach((toast) => {
        bootstrap.Toast.getOrCreateInstance(toast, { delay: 4200 }).show();
    });

    const overlay = document.createElement('div');
    overlay.className = 'loading-overlay';
    overlay.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading</span></div>';
    document.body.appendChild(overlay);

    document.querySelectorAll('[data-loading]').forEach((element) => {
        element.addEventListener('click', () => overlay.classList.add('show'));
    });

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => overlay.classList.add('show'));
    });

    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
    }
});
