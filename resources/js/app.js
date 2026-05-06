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
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');

    const updateSidebarAria = () => {
        if (sidebarToggle) {
            sidebarToggle.setAttribute('aria-expanded', sidebar?.classList.contains('show') ? 'true' : 'false');
        }
    };

    const toggleSidebar = () => {
        sidebar.classList.toggle('show');
        sidebarBackdrop?.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
        updateSidebarAria();
    };

    if (sidebarToggle && sidebar) {
        updateSidebarAria();
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', toggleSidebar);
    }

    document.querySelectorAll('#sidebar .nav-link').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 768 && sidebar.classList.contains('show')) {
                toggleSidebar();
            }
        });
    });
});
