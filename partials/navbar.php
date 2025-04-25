<?php
// partials/navbar.php
$navItems = [
    '../logout.php'        => ['Salir', 'fas fa-sign-out-alt']
];
?>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <!-- Logo y nombre -->
        <a class="navbar-brand d-flex align-items-center" href="dashboard.php">
            <i class="fas fa-hotel me-2"></i>
            <span>Daniya Denia</span>
        </a>

        <!-- Botón hamburguesa para móvil -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
            aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Contenido del navbar -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Links de navegación -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($navItems as $file => $info): ?>
                    <?php if ($file !== '../logout.php'): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === $file ? 'active' : ''; ?>"
                                href="<?php echo $file; ?>">
                                <i class="<?php echo $info[1]; ?> me-2"></i>
                                <?php echo $info[0]; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <!-- Área derecha del navbar -->
            <div class="d-flex align-items-center">
                <!-- Usuario actual -->
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="dropdown me-3">
                        <button class="btn btn-link nav-link dropdown-toggle" type="button" id="userDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="../logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </a></li>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Botón de tema -->
                <button class="theme-toggle" id="theme-toggle" title="Cambiar tema">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar {
        padding: 0.75rem 1rem;
        background: var(--bg-sidebar);
        box-shadow: var(--shadow-sm);
    }

    .navbar-brand {
        color: var(--text-light) !important;
        font-size: 1.5rem;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .navbar-brand i {
        font-size: 1.75rem;
        color: var(--primary-color);
    }

    .nav-link {
        color: var(--text-light) !important;
        padding: 0.5rem 1rem;
        transition: all 0.3s ease;
        border-radius: 6px;
        margin: 0 0.25rem;
    }

    .nav-link:hover,
    .nav-link.active {
        background: var(--bg-sidebar-hover);
        color: var(--primary-color) !important;
    }

    .navbar-toggler {
        color: var(--text-light);
        border: none;
        padding: 0.5rem;
    }

    .navbar-toggler:focus {
        box-shadow: none;
    }

    .dropdown-menu {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-md);
    }

    .dropdown-item {
        color: var(--text-primary);
        transition: all 0.3s ease;
    }

    .dropdown-item:hover {
        background: var(--bg-sidebar-hover);
        color: var(--primary-color);
    }

    @media (max-width: 991px) {
        .nav-link {
            padding: 0.75rem 1rem;
            margin: 0.25rem 0;
        }

        .navbar-collapse {
            padding: 1rem 0;
        }

        .theme-toggle {
            margin-top: 0.5rem;
        }
    }
</style>