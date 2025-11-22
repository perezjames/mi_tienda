<nav class="navbar navbar-expand-lg navbar-dark custom-navbar fixed-top">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
        <i class="bi bi-box-seam-fill text-info"></i> Mi Tienda
    </a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto mb-2 mb-lg-0 fw-semibold">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        
        <?php if(in_array($_SESSION['role'], ['admin', 'trabajador'])): ?>
        <li class="nav-item"><a class="nav-link" href="ventas.php"><i class="bi bi-cart3"></i> Ventas</a></li>
        <li class="nav-item"><a class="nav-link" href="productos.php"><i class="bi bi-box"></i> Productos</a></li>
        <li class="nav-item"><a class="nav-link" href="clientes.php"><i class="bi bi-people"></i> Clientes</a></li>
        <li class="nav-item"><a class="nav-link" href="proveedores.php"><i class="bi bi-truck"></i> Proveedores</a></li>
        <?php endif; ?>

        <?php if($_SESSION['role'] == 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="usuarios.php"><i class="bi bi-person-badge"></i> Usuarios</a></li>
        <li class="nav-item"><a class="nav-link" href="reportes.php"><i class="bi bi-file-earmark-bar-graph"></i> Reportes</a></li>
        <?php endif; ?>
      </ul>
      
      <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="bg-white text-dark rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                <i class="bi bi-person-fill"></i>
            </div>
            <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong>
          </a>
          <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
            <li><span class="dropdown-item-text text-muted small"><?php echo ucfirst($_SESSION['role']); ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
          </ul>
      </div>
    </div>
  </div>
</nav>