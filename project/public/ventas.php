<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

$IVA_PORCENTAJE = 0.19; // 19%

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contacto_id = $_POST['contacto_id'];
    $items = json_decode($_POST['items'], true);
    
    if (!empty($items)) {
        try {
            $pdo->beginTransaction();

            $subtotal = 0;
            
            // calcular subtotal y validar stock
            foreach ($items as $item) {
                $stmt = $pdo->prepare("SELECT precio, stock FROM productos WHERE id = ?");
                $stmt->execute([$item['id']]);
                $prod = $stmt->fetch();
                
                if ($prod['stock'] < $item['qty']) {
                    throw new Exception("Stock insuficiente para producto ID: " . $item['id']);
                }
                $subtotal += $prod['precio'] * $item['qty'];
            }
            
            // Calcular IVA y total
            $iva = $subtotal * $IVA_PORCENTAJE;
            $total = $subtotal + $iva;

            // crear venta
            $stmt = $pdo->prepare("INSERT INTO ventas (contacto_id, usuario_id, subtotal, iva, total) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$contacto_id, $_SESSION['user_id'], $subtotal, $iva, $total]);
            $venta_id = $pdo->lastInsertId();

            // crear items de venta y actualizar stock
            foreach ($items as $item) {
                $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
                $stmt->execute([$item['id']]);
                $prod = $stmt->fetch();

                $stmt = $pdo->prepare("INSERT INTO venta_items (venta_id, producto_id, cantidad, precio_unit) VALUES (?, ?, ?, ?)");
                $stmt->execute([$venta_id, $item['id'], $item['qty'], $prod['precio']]);

                $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['qty'], $item['id']]);
            }

            registrar_historial($pdo, $_SESSION['user_id'], 'Nueva Venta', 'ventas', $venta_id, "Total: $total");
            $pdo->commit();
            redirect('ventas.php?success=1');

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$contactos = $pdo->query("SELECT * FROM contactos WHERE tipo = 'cliente' ORDER BY nombre")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE stock > 0 ORDER BY nombre")->fetchAll();
$ventas = $pdo->query("SELECT v.*, c.nombre as contacto, u.nombre as usuario FROM ventas v LEFT JOIN contactos c ON v.contacto_id = c.id LEFT JOIN users u ON v.usuario_id = u.id ORDER BY v.fecha DESC LIMIT 15")->fetchAll();

include '../inc/header.php';
?>

<h2 class="mb-4">Registrar Venta</h2>

<?php if(isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>Venta registrada correctamente
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Registro de venta -->
    <div class="col-md-6">
        <div class="card rounded-3 mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Nueva venta</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="ventaForm">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cliente</label>
                        <select class="form-select" name="contacto_id" required>
                            <option value="">Seleccione cliente...</option>
                            <?php foreach ($contactos as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agregar productos</label>
                        <div class="row g-2 mb-2">
                            <div class="col-8">
                                <select class="form-select" id="prodSelect">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($productos as $p): ?>
                                    <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['precio']; ?>" data-name="<?php echo htmlspecialchars($p['nombre']); ?>" data-stock="<?php echo $p['stock']; ?>">
                                        <?php echo htmlspecialchars($p['nombre']); ?> - $<?php echo number_format($p['precio'], 2); ?> (Stock: <?php echo $p['stock']; ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-2">
                                <input type="number" class="form-control" id="prodQty" value="1" min="1">
                            </div>
                            <div class="col-2">
                                <button type="button" class="btn btn-secondary w-100" onclick="addItem()"><i class="bi bi-plus"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cant.</th>
                                    <th>Precio</th>
                                    <th>Subt.</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="bg-light p-3 rounded mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Subtotal:</span>
                            <strong id="subtotalAmount">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>IVA (19%):</span>
                            <strong id="ivaAmount">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="h5 mb-0">Total:</span>
                            <strong class="h5 mb-0 text-success" id="totalAmount">$0.00</strong>
                        </div>
                    </div>

                    <input type="hidden" name="items" id="itemsInput">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="bi bi-check-circle me-2"></i>Registrar venta
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Últimas ventas -->
    <div class="col-md-6">
        <div class="card rounded-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Últimas Ventas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Contacto</th>
                                <th>Total</th>
                                <th>Fact.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $v): ?>
                            <tr>
                                <td><?php echo $v['id']; ?></td>
                                <td><small><?php echo date('d/m H:i', strtotime($v['fecha'])); ?></small></td>
                                <td><small><?php echo htmlspecialchars(substr($v['contacto'] ?? 'General', 0, 15)); ?></small></td>
                                <td><strong class="text-success">$<?php echo number_format($v['total'], 2); ?></strong></td>
                                <td>
                                    <a href="factura.php?id=<?php echo $v['id']; ?>" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver Factura">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let items = [];
const IVA = 0.19;

function addItem() {
    const select = document.getElementById('prodSelect');
    const qtyInput = document.getElementById('prodQty');
    const id = select.value;
    const qty = parseInt(qtyInput.value);
    
    if (!id || qty < 1) {
        alert('Seleccione un producto y cantidad válida');
        return;
    }

    const option = select.options[select.selectedIndex];
    const name = option.getAttribute('data-name');
    const price = parseFloat(option.getAttribute('data-price'));
    const stock = parseInt(option.getAttribute('data-stock'));

    // verificar stock disponible
    const existing = items.find(i => i.id === id);
    const totalQty = existing ? existing.qty + qty : qty;
    
    if (totalQty > stock) {
        alert('Stock insuficiente. Disponible: ' + stock);
        return;
    }

    if (existing) {
        existing.qty += qty;
    } else {
        items.push({ id, name, price, qty });
    }

    renderItems();
    select.value = '';
    qtyInput.value = 1;
}

function removeItem(index) {
    items.splice(index, 1);
    renderItems();
}

function renderItems() {
    const tbody = document.querySelector('#itemsTable tbody');
    tbody.innerHTML = '';
    let subtotal = 0;

    items.forEach((item, index) => {
        const itemSubtotal = item.price * item.qty;
        subtotal += itemSubtotal;
        tbody.innerHTML += `
            <tr>
                <td><small>${item.name}</small></td>
                <td>${item.qty}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>$${itemSubtotal.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})"><i class="bi bi-x"></i></button></td>
            </tr>
        `;
    });

    const iva = subtotal * IVA;
    const total = subtotal + iva;

    document.getElementById('subtotalAmount').innerText = '$' + subtotal.toFixed(2);
    document.getElementById('ivaAmount').innerText = '$' + iva.toFixed(2);
    document.getElementById('totalAmount').innerText = '$' + total.toFixed(2);
    document.getElementById('itemsInput').value = JSON.stringify(items);
}

document.getElementById('ventaForm').addEventListener('submit', function(e) {
    if (items.length === 0) {
        e.preventDefault();
        alert('Agregue al menos un producto a la venta');
    }
});
</script>

<?php include '../inc/footer.php'; ?>
