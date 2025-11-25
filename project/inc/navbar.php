<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-shop"></i> Mi Tienda
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-1"></i>Panel
                    </a>
                </li>
                
                <?php if(in_array($_SESSION['rol'], ['administrador', 'trabajador'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="ventas.php">
                        <i class="bi bi-cart3 me-1"></i>Ventas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="productos.php">
                        <i class="bi bi-box me-1"></i>Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clientes.php">
                        <i class="bi bi-people me-1"></i>Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="proveedores.php">
                        <i class="bi bi-truck me-1"></i>Proveedores
                    </a>
                </li>
                <?php endif; ?>

                <?php if($_SESSION['rol'] == 'administrador'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">
                        <i class="bi bi-person-badge me-1"></i>Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportes.php">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>Reportes
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-4 me-2"></i>
                    <span><?php echo htmlspecialchars($_SESSION['nombre']); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text small text-muted"><?php echo htmlspecialchars($_SESSION['correo']); ?></span></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="config_cuenta.php"><i class="bi bi-gear me-2"></i>Configuración</a></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>