<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

$stmt = $pdo->query("SELECT COUNT(*) FROM productos");
$total_productos = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM ventas WHERE DATE(fecha) = CURDATE()");
$ventas_hoy = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(total) FROM ventas WHERE DATE(fecha) = CURDATE()");
$ingresos_hoy = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->query("SELECT COUNT(*) FROM clientes");
$total_clientes = $stmt->fetchColumn();

include '../inc/header.php';
?>

<!-- Saludo y opciones de cuenta -->
<div class="d-flex justify-content-between align-items-center mb-4 border border-dark rounded p-3">
    <h2 class="mb-0">Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h2>
    <div>
        <a href="config_cuenta.php" class="text-decoration-none me-3"><i class="bi bi-gear me-1"></i>Configuración</a>
        <a href="logout.php" class="text-decoration-none"><i class="bi bi-box-arrow-right me-1"></i>Cerrar sesión</a>
    </div>
</div>

<!-- Tarjetas -->
<div class="card-group mb-4">
    <!-- Mostrar tarjeta de productos y ventas solo para administradores y trabajadores -->
    <?php if(in_array($_SESSION['rol'], ['administrador', 'trabajador'])): ?>

        <!-- Total de productos -->
        <div class="card">
            <div class="card-header">Productos</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h2 class="mb-0"><?php echo $total_productos; ?></h2>
                    <small>Total en inventario</small>
                </li>
        </div>
   
        <!-- Ventas hoy -->
        <div class="card">
            <div class="card-header">Ventas hoy</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h2 class="mb-0"><?php echo $ventas_hoy; ?></h2>
                    <small>Transacciones</small>
                </li>
        </div>

    <?php endif; ?>
    <!-- Mostrar tarjeta de ingresos solo para administradores -->
    <?php if($_SESSION['rol'] == 'administrador'): ?>

        <!-- Ingresos hoy -->
        <div class="card">
            <div class="card-header">Ingresos hoy</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h2 class="mb-0">$<?php echo number_format($ingresos_hoy, 2); ?></h2>
                    <small>Total vendido</small>
                </li>
        </div>

    <?php endif; ?>
    <!-- Mostrar tarjeta de clientes para administradores y trabajadores -->
    <?php if(in_array($_SESSION['rol'], ['administrador', 'trabajador'])): ?>
        
        <!-- Total de clientes -->
        <div class="card">
            <div class="card-header">Clientes</div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <h2 class="mb-0"><?php echo $total_clientes; ?></h2>
                    <small>Registrados</small>
                </li>
        </div>

    <?php endif; ?>
</div>

<!-- Tablas -->
<hr class="mb-4">
<div class="row">
    <!-- Historial de actividades -->    
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white font-weight-bold">
                Historial
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Fecha</th>
                            <th scope="col">Usuario</th>
                            <th scope="col">Acción</th>
                            <th scope="col">Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT h.*, u.nombre as usuario FROM historial h LEFT JOIN users u ON h.usuario_id = u.id";
                        if ($_SESSION['rol'] !== 'administrador') {
                            $sql .= " WHERE h.usuario_id = " . $_SESSION['user_id'];
                        }
                        $sql .= " ORDER BY h.fecha_hora DESC LIMIT 8";
                                            
                        $stmt = $pdo->query($sql);
                        while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha_hora'])); ?></td>
                            <td><?php echo htmlspecialchars($row['usuario'] ?? 'Sistema'); ?></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['accion']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['detalles']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Productos más vendidos -->
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white font-weight-bold">
                Productos más vendidos
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT p.nombre, SUM(vi.cantidad) as total_cantidad, SUM(vi.cantidad * vi.precio_unit) as total_monto
                                FROM venta_items vi 
                                INNER JOIN productos p ON vi.producto_id = p.id
                                GROUP BY vi.producto_id
                                ORDER BY total_cantidad DESC
                                LIMIT 5";
                        $stmt = $pdo->query($sql);
                        while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo $row['total_cantidad']; ?></td>
                            <td>$<?php echo number_format($row['total_monto'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Últimas ventas -->
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white font-weight-bold">
                Últimas ventas
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT v.fecha, v.total, c.nombre as cliente
                                FROM ventas v 
                                LEFT JOIN clientes c ON v.cliente_id = c.id
                                ORDER BY v.fecha DESC
                                LIMIT 5";
                        $stmt = $pdo->query($sql);
                        while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($row['cliente'] ?? 'Sin cliente'); ?></td>
                            <td>$<?php echo number_format($row['total'], 2); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Productos añadidos recientemente -->
    <div class="col-md-3 col-sm-6 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-header bg-white font-weight-bold">
                Productos añadidos recientemente
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Fecha de registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM productos ORDER BY created_at DESC LIMIT 5";
                        $stmt = $pdo->query($sql);
                        while ($row = $stmt->fetch()):
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['codigo']); ?></td>
                            <td>$<?php echo number_format($row['precio'], 2); ?></td>
                            <td><?php echo $row['stock']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>