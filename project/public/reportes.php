<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Fetch Sales Report
$sql = "SELECT v.id, v.fecha, c.nombre as cliente, u.nombre as usuario, v.total 
        FROM ventas v 
        LEFT JOIN clientes c ON v.cliente_id = c.id 
        LEFT JOIN users u ON v.usuario_id = u.id 
        WHERE DATE(v.fecha) BETWEEN ? AND ? 
        ORDER BY v.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$start_date, $end_date]);
$ventas = $stmt->fetchAll();

// Export CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reporte_ventas.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Fecha', 'Cliente', 'Vendedor', 'Total']);
    foreach ($ventas as $v) {
        fputcsv($output, [$v['id'], $v['fecha'], $v['cliente'], $v['usuario'], $v['total']]);
    }
    fclose($output);
    exit;
}

include '../inc/header.php';
?>

<h2 class="mb-4 no-print">Reportes de Ventas</h2>

<form class="row g-3 mb-4 no-print">
    <div class="col-auto">
        <label for="start_date" class="col-form-label">Desde</label>
    </div>
    <div class="col-auto">
        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
    </div>
    <div class="col-auto">
        <label for="end_date" class="col-form-label">Hasta</label>
    </div>
    <div class="col-auto">
        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">Filtrar</button>
    </div>
    <div class="col-auto">
        <button type="submit" name="export" value="csv" class="btn btn-outline-secondary">Exportar CSV</button>
    </div>
    <div class="col-auto">
        <button type="button" onclick="window.print()" class="btn btn-outline-dark">Imprimir</button>
    </div>
</form>

<div class="card">
    <div class="card-header">
        Resultados del <?php echo $start_date; ?> al <?php echo $end_date; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_periodo = 0;
                    foreach ($ventas as $v): 
                        $total_periodo += $v['total'];
                    ?>
                    <tr>
                        <td><?php echo $v['id']; ?></td>
                        <td><?php echo $v['fecha']; ?></td>
                        <td><?php echo htmlspecialchars($v['cliente'] ?? 'General'); ?></td>
                        <td><?php echo htmlspecialchars($v['usuario']); ?></td>
                        <td>$<?php echo number_format($v['total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="4" class="text-end">Total Periodo</th>
                        <th>$<?php echo number_format($total_periodo, 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
