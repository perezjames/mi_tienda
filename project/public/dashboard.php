<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

// Stats
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

<h2 class="mb-4">Dashboard</h2>

<div class="row mb-4">
    <?php if(in_array($_SESSION['role'], ['admin', 'trabajador'])): ?>
    <div class="col-md-3">
        <div class="card bg-dark mb-3">
            <div class="card-header">Productos</div>
            <div class="card-body text-white">
                <h5 class="card-title"><?php echo $total_productos; ?></h5>
                <p class="card-text">Total en inventario</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-dark bg-light mb-3">
            <div class="card-header">Ventas hoy</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $ventas_hoy; ?></h5>
                <p class="card-text">Transacciones</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($_SESSION['role'] == 'admin'): ?>
    <div class="col-md-3">
        <div class="card bg-dark mb-3">
            <div class="card-header">Ingresos hoy</div>
            <div class="card-body text-white">
                <h5 class="card-title">$<?php echo number_format($ingresos_hoy, 2); ?></h5>
                <p class="card-text">Total vendido</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if(in_array($_SESSION['role'], ['admin', 'trabajador'])): ?>
    <div class="col-md-3">
        <div class="card text-dark bg-light mb-3">
            <div class="card-header">Clientes</div>
            <div class="card-body">
                <h5 class="card-title"><?php echo $total_clientes; ?></h5>
                <p class="card-text">Registrados</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if(!in_array($_SESSION['role'], ['admin', 'trabajador'])): ?>
    <div class="col-12">
        <div class="alert alert-info">Bienvenido al sistema. Su rol actual (<?php echo ucfirst($_SESSION['role']); ?>) tiene acceso limitado.</div>
    </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">Últimos movimientos (Historial)</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
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
                            if ($_SESSION['role'] !== 'admin') {
                                $sql .= " WHERE h.usuario_id = " . $_SESSION['user_id'];
                            }
                            $sql .= " ORDER BY h.fecha_hora DESC LIMIT 10";
                            
                            $stmt = $pdo->query($sql);
                            while ($row = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?php echo $row['fecha_hora']; ?></td>
                                <td><span class="fw-bold"><?php echo htmlspecialchars($row['usuario'] ?? 'Sistema'); ?></span></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['accion']); ?></span></td>
                                <td class="text-muted small"><?php echo htmlspecialchars($row['detalles']); ?></td>
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