<nav class="navbar navbar-expand-lg shadow-sm no-print">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="dashboard.php">
            <i class="bi bi-shop"></i> Mi Tienda
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Inicio</a>
                </li>
                <?php if(in_array($_SESSION['rol'], ['administrador', 'trabajador'])): ?>
                <li class="nav-item">
                    <a class="nav-link" href="ventas.php">Ventas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="productos.php">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="clientes.php">Clientes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="proveedores.php">Proveedores</a>
                </li>
                <?php endif; ?>
                <?php if($_SESSION['rol'] == 'administrador'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="usuarios.php">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reportes.php">Reportes</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>