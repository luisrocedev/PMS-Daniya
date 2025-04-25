// Gestión del sidebar responsivo
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (sidebarToggle && sidebar) {
        // Toggle sidebar en móvil
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });

        // Cerrar sidebar al hacer clic fuera en móviles
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }

    // Marcar página actual en el sidebar
    const currentPage = window.location.pathname.split('/').pop();
    const currentLink = sidebar?.querySelector(`a[href="${currentPage}"]`);
    if (currentLink) {
        currentLink.classList.add('active');
    }
});