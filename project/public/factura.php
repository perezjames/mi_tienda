<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
check_login();

$venta_id = $_GET['id'] ?? 0;

// Obtener datos de la venta
$stmt = $pdo->prepare("SELECT v.*, c.nombre as contacto_nombre, c.contacto, c.telefono, u.nombre as vendedor 
                       FROM ventas v 
                       LEFT JOIN contactos c ON v.contacto_id = c.id 
                       LEFT JOIN users u ON v.usuario_id = u.id 
                       WHERE v.id = ?");
$stmt->execute([$venta_id]);
$venta = $stmt->fetch();

if (!$venta) {
    die("Venta no encontrada");
}

// Obtener items de la venta
$stmt = $pdo->prepare("SELECT vi.*, p.nombre as producto 
                       FROM venta_items vi 
                       LEFT JOIN productos p ON vi.producto_id = p.id 
                       WHERE vi.venta_id = ?");
$stmt->execute([$venta_id]);
$items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo $venta_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 20px; }
        }
        .factura-header {
            border-bottom: 3px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-4">
        <div class="card rounded-3">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="factura-header">
                    <div class="row">
                        <div class="col-6">
                            <h1 class="mb-0">Mi Tienda</h1>
                            <p class="text-muted mb-0">Sistema de gestión</p>
                        </div>
                        <div class="col-6 text-end">
                            <h2 class="mb-0">FACTURA</h2>
                            <p class="mb-0"><strong>No. <?php echo str_pad($venta['id'], 6, '0', STR_PAD_LEFT); ?></strong></p>
                            <p class="text-muted mb-0"><?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Datos del cliente -->
                <div class="row mb-4">
                    <div class="col-6">
                        <h5 class="mb-3">Contacto:</h5>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($venta['contacto_nombre'] ?? 'Cliente General'); ?></strong></p>
                        <?php if($venta['contacto']): ?>
                        <p class="mb-1"><?php echo htmlspecialchars($venta['contacto']); ?></p>
                        <?php endif; ?>
                        <?php if($venta['telefono']): ?>
                        <p class="mb-1">Tel: <?php echo htmlspecialchars($venta['telefono']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-3">Vendedor:</h5>
                        <p class="mb-1"><strong><?php echo htmlspecialchars($venta['vendedor']); ?></strong></p>
                    </div>
                </div>

                <!-- Tabla de productos -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>Cant.</th>
                                <th>Descripción</th>
                                <th class="text-end">Precio Unit.</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td><?php echo htmlspecialchars($item['producto']); ?></td>
                                <td class="text-end">$<?php echo number_format($item['precio_unit'], 2); ?></td>
                                <td class="text-end">$<?php echo number_format($item['cantidad'] * $item['precio_unit'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Totales -->
                <div class="row">
                    <div class="col-7"></div>
                    <div class="col-5">
                        <table class="table table-sm">
                            <tr>
                                <td class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end">$<?php echo number_format($venta['subtotal'], 2); ?></td>
                            </tr>
                            <tr>
                                <td class="text-end"><strong>IVA (19%):</strong></td>
                                <td class="text-end">$<?php echo number_format($venta['iva'], 2); ?></td>
                            </tr>
                            <tr class="table-dark">
                                <td class="text-end"><strong>TOTAL:</strong></td>
                                <td class="text-end"><strong>$<?php echo number_format($venta['total'], 2); ?></strong></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Footer -->
                <div class="text-center mt-5 pt-4 border-top">
                    <p class="text-muted mb-0">Gracias por su compra</p>
                </div>

                <!-- Botones -->
                <div class="text-center mt-4 no-print">
                    <button onclick="window.print()" class="btn btn-dark">
                        <i class="bi bi-printer me-2"></i>Imprimir Factura
                    </button>
                    <a href="ventas.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Volver a Ventas
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
