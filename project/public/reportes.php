<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

$tipo_reporte = $_GET['tipo'] ?? 'ventas';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Generar datos según tipo de reporte
$datos = [];
$titulo = '';
$columnas = [];

switch ($tipo_reporte) {
    case 'ventas':
        $titulo = 'Reporte de Ventas';
        $columnas = ['ID', 'Fecha', 'Contacto', 'Vendedor', 'Subtotal', 'IVA', 'Total'];
        $sql = "SELECT v.id, v.fecha, c.nombre as contacto, u.nombre as usuario, v.subtotal, v.iva, v.total 
                FROM ventas v 
                LEFT JOIN contactos c ON v.contacto_id = c.id 
                LEFT JOIN users u ON v.usuario_id = u.id 
                WHERE DATE(v.fecha) BETWEEN ? AND ? 
                ORDER BY v.fecha DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        $datos = $stmt->fetchAll();
        break;

    case 'productos':
        $titulo = 'Reporte de Productos';
        $columnas = ['ID', 'Nombre', 'Código', 'Categoría', 'Precio', 'Stock', 'Fecha Registro'];
        $sql = "SELECT p.id, p.nombre, p.codigo, c.nombre as categoria, p.precio, p.stock, p.created_at 
                FROM productos p 
                LEFT JOIN categorias c ON p.categoria_id = c.id 
                ORDER BY p.nombre";
        $datos = $pdo->query($sql)->fetchAll();
        break;

    case 'inventario':
        $titulo = 'Reporte de Inventario (Stock)';
        $columnas = ['ID', 'Producto', 'Stock Actual', 'Precio Unit.', 'Valor Total', 'Estado'];
        $sql = "SELECT p.id, p.nombre, p.stock, p.precio, (p.stock * p.precio) as valor_total 
                FROM productos p 
                ORDER BY p.stock ASC";
        $stmt = $pdo->query($sql);
        while ($row = $stmt->fetch()) {
            $row['estado'] = $row['stock'] < 10 ? 'Bajo stock' : ($row['stock'] < 50 ? 'Normal' : 'Alto stock');
            $datos[] = $row;
        }
        break;

    case 'contactos':
        $titulo = 'Reporte de Contactos';
        $columnas = ['ID', 'Nombre', 'Contacto', 'Teléfono', 'Tipo', 'Total Compras', 'Fecha Registro'];
        $sql = "SELECT c.id, c.nombre, c.contacto, c.telefono, c.tipo,
                COALESCE(SUM(v.total), 0) as total_compras, c.created_at 
                FROM contactos c 
                LEFT JOIN ventas v ON c.id = v.contacto_id AND c.tipo = 'cliente'
                GROUP BY c.id 
                ORDER BY total_compras DESC";
        $datos = $pdo->query($sql)->fetchAll();
        break;

    case 'usuarios':
        $titulo = 'Reporte de Usuarios';
        $columnas = ['ID', 'Nombre', 'Correo', 'Rol', 'Estado', 'Fecha Registro'];
        $sql = "SELECT id, nombre, correo, rol, estado, created_at FROM users ORDER BY nombre";
        $datos = $pdo->query($sql)->fetchAll();
        break;

    case 'productos_vendidos':
        $titulo = 'Productos Más Vendidos';
        $columnas = ['Producto', 'Cantidad Total', 'Veces Vendido', 'Ingresos Generados'];
        $sql = "SELECT p.nombre, SUM(vi.cantidad) as total_cantidad, 
                COUNT(DISTINCT vi.venta_id) as veces_vendido, 
                SUM(vi.cantidad * vi.precio_unit) as total_ingresos 
                FROM venta_items vi 
                INNER JOIN productos p ON vi.producto_id = p.id 
                INNER JOIN ventas v ON vi.venta_id = v.id 
                WHERE DATE(v.fecha) BETWEEN ? AND ? 
                GROUP BY vi.producto_id 
                ORDER BY total_cantidad DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        $datos = $stmt->fetchAll();
        break;
}

// Exportar CSV
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="reporte_' . $tipo_reporte . '_' . date('Y-m-d') . '.csv"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    fputcsv($output, $columnas);
    foreach ($datos as $row) {
        fputcsv($output, array_values((array)$row));
    }
    fclose($output);
    exit;
}

include '../inc/header.php';
?>

<h2 class="mb-4 no-print">Reportes</h2>

<!-- Selector de tipo de reporte -->
<div class="row no-print mb-4">
    <div class="col-12">
        <div class="btn-group flex-wrap" role="group">
            <a href="?tipo=ventas" class="btn btn-<?php echo $tipo_reporte == 'ventas' ? 'dark' : 'outline-dark'; ?>">
                <i class="bi bi-cart-check me-1"></i>Ventas
            </a>
            <a href="?tipo=productos_vendidos" class="btn btn-<?php echo $tipo_reporte == 'productos_vendidos' ? 'dark' : 'outline-dark'; ?>">
                <i class="bi bi-graph-up me-1"></i>Productos vendidos
            </a>
            <a href="?tipo=productos" class="btn btn-<?php echo $tipo_reporte == 'productos' ? 'dark' : 'outline-dark'; ?>">
                <i class="bi bi-box me-1"></i>Productos
            </a>
            <a href="?tipo=inventario" class="btn btn-<?php echo $tipo_reporte == 'inventario' ? 'dark' : 'outline-dark'; ?>">
                <i class="bi bi-boxes me-1"></i>Inventario
            </a>
            <a href="?tipo=contactos" class="btn btn-<?php echo $tipo_reporte == 'contactos' ? 'dark' : 'outline-dark'; ?>">
                <i class="bi bi-people me-1"></i>Contactos
            </a>
            <?php if($_SESSION['rol'] == 'administrador'): ?>
            <a href="?tipo=usuarios" class="btn btn-<?php echo $tipo_reporte == 'usuarios' ? 'dark' : 'outline-dark'; ?>">
                <i class="bi bi-person-badge me-1"></i>Usuarios
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filtros de fecha (solo para reportes con fechas) -->
<?php if(in_array($tipo_reporte, ['ventas', 'productos_vendidos'])): ?>
<form class="row g-3 mb-4 no-print">
    <input type="hidden" name="tipo" value="<?php echo $tipo_reporte; ?>">
    <div class="col-auto">
        <label class="col-form-label fw-semibold">Desde:</label>
    </div>
    <div class="col-auto">
        <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
    </div>
    <div class="col-auto">
        <label class="col-form-label fw-semibold">Hasta:</label>
    </div>
    <div class="col-auto">
        <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-dark">
            <i class="bi bi-funnel me-2"></i>Filtrar
        </button>
    </div>
    <div class="col-auto">
        <button type="submit" name="export" value="csv" class="btn btn-outline-secondary">
            <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar CSV
        </button>
    </div>
    <div class="col-auto">
        <button type="button" onclick="window.print()" class="btn btn-outline-dark">
            <i class="bi bi-printer me-2"></i>Imprimir
        </button>
    </div>
</form>
<?php else: ?>
<div class="mb-4 no-print">
    <button onclick="window.location.href='?tipo=<?php echo $tipo_reporte; ?>&export=csv'" class="btn btn-outline-secondary">
        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar CSV
    </button>
    <button type="button" onclick="window.print()" class="btn btn-outline-dark">
        <i class="bi bi-printer me-2"></i>Imprimir
    </button>
</div>
<?php endif; ?>

<!-- Título del reporte -->
<div class="card rounded-3">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><?php echo $titulo; ?></h5>
        <?php if(in_array($tipo_reporte, ['ventas', 'productos_vendidos'])): ?>
        <small>Periodo: <?php echo date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)); ?></small>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if(empty($datos)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>No hay datos para mostrar en este reporte.
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <?php foreach($columnas as $col): ?>
                        <th><?php echo $col; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $totales = [];
                    foreach ($datos as $row): 
                    ?>
                    <tr>
                        <?php 
                        $valores = array_values((array)$row);
                        for($i = 0; $i < count($columnas); $i++): 
                            $valor = $valores[$i] ?? '';
                            
                            // Formatear valores monetarios
                            if(in_array($columnas[$i], ['Total', 'Subtotal', 'IVA', 'Precio', 'Precio Unit.', 'Valor Total', 'Total Compras', 'Ingresos Generados'])) {
                                echo '<td>$' . number_format($valor, 2) . '</td>';
                                if(!isset($totales[$i])) $totales[$i] = 0;
                                $totales[$i] += $valor;
                            }
                            // Formatear fechas
                            elseif(in_array($columnas[$i], ['Fecha', 'Fecha Registro']) && strtotime($valor)) {
                                echo '<td>' . date('d/m/Y H:i', strtotime($valor)) . '</td>';
                            }
                            // Badges para estados
                            elseif($columnas[$i] == 'Estado') {
                                $badge_class = $valor == 'activo' ? 'success' : ($valor == 'Bajo stock' ? 'danger' : 'secondary');
                                echo '<td><span class="badge bg-' . $badge_class . '">' . ucfirst($valor) . '</span></td>';
                            }
                            elseif($columnas[$i] == 'Rol' || $columnas[$i] == 'Tipo') {
                                $badge_class = 'secondary';
                                if ($valor === 'cliente') $badge_class = 'info';
                                if ($valor === 'proveedor') $badge_class = 'warning';
                                echo '<td><span class="badge bg-' . $badge_class . '">' . ucfirst($valor) . '</span></td>';
                            }
                            else {
                                echo '<td>' . htmlspecialchars($valor) . '</td>';
                            }
                        endfor;
                        ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                
                <?php if(!empty($totales)): ?>
                <tfoot class="table-light">
                    <tr>
                        <?php for($i = 0; $i < count($columnas); $i++): ?>
                            <?php if(isset($totales[$i])): ?>
                                <th class="text-end">
                                    <?php if($i == array_key_first($totales)): ?>TOTALES:<?php endif; ?>
                                    $<?php echo number_format($totales[$i], 2); ?>
                                </th>
                            <?php else: ?>
                                <th></th>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
        
        <p class="text-muted mb-0 mt-3">
            <i class="bi bi-info-circle me-1"></i>
            Total de registros: <strong><?php echo count($datos); ?></strong>
        </p>
        <?php endif; ?>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
