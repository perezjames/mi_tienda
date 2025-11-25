<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

// Estadísticas 
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

<h2 class="mb-1">Dashboard</h2>
<p class="text-muted mb-4">Hola, <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?></strong></p>

<!-- Vistazos rápidos -->
<div class="row g-3 mb-4">
    <?php if(in_array($_SESSION['rol'], ['administrador', 'trabajador'])): ?>
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Productos</h6>
                    <i class="bi bi-box-seam fs-3"></i>
                </div>
                <h2 class="mb-0"><?php echo $total_productos; ?></h2>
                <small>Total en inventario</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Ventas Hoy</h6>
                    <i class="bi bi-cart-check fs-3"></i>
                </div>
                <h2 class="mb-0"><?php echo $ventas_hoy; ?></h2>
                <small>Transacciones</small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($_SESSION['rol'] == 'administrador'): ?>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Ingresos Hoy</h6>
                    <i class="bi bi-currency-dollar fs-3"></i>
                </div>
                <h2 class="mb-0">$<?php echo number_format($ingresos_hoy, 2); ?></h2>
                <small>Total vendido</small>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(in_array($_SESSION['rol'], ['administrador', 'trabajador'])): ?>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Clientes</h6>
                    <i class="bi bi-people fs-3"></i>
                </div>
                <h2 class="mb-0"><?php echo $total_clientes; ?></h2>
                <small>Registrados</small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Tabla de Movimientos -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-activity me-2"></i>Movimientos Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>Detalles</th>
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
    </div>
</div>

<!-- Tablas: Productos más vendidos y Últimas ventas -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-trophy me-2"></i>Productos más vendidos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
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
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Últimas ventas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead class="table-light">
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
    </div>
</div>

<!-- Productos añadidos recientemente -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Productos añadidos recientemente</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
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
</div>

<?php include '../inc/footer.php'; ?>