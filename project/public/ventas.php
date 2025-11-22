<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'];
    $items = json_decode($_POST['items'], true);
    $total = 0;

    if (!empty($items)) {
        try {
            $pdo->beginTransaction();

            // Calculate total and validate stock
            foreach ($items as $item) {
                $stmt = $pdo->prepare("SELECT precio, stock FROM productos WHERE id = ?");
                $stmt->execute([$item['id']]);
                $prod = $stmt->fetch();
                
                if ($prod['stock'] < $item['qty']) {
                    throw new Exception("Stock insuficiente para producto ID: " . $item['id']);
                }
                $total += $prod['precio'] * $item['qty'];
            }

            // Create Sale
            $stmt = $pdo->prepare("INSERT INTO ventas (cliente_id, usuario_id, total) VALUES (?, ?, ?)");
            $stmt->execute([$cliente_id, $_SESSION['user_id'], $total]);
            $venta_id = $pdo->lastInsertId();

            // Create Sale Items and Update Stock
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

$clientes = $pdo->query("SELECT * FROM clientes")->fetchAll();
$productos = $pdo->query("SELECT * FROM productos WHERE stock > 0")->fetchAll();
$ventas = $pdo->query("SELECT v.*, c.nombre as cliente, u.nombre as usuario FROM ventas v LEFT JOIN clientes c ON v.cliente_id = c.id LEFT JOIN users u ON v.usuario_id = u.id ORDER BY v.fecha DESC LIMIT 20")->fetchAll();

include '../inc/header.php';
?>

<h2 class="mb-3">Nueva Venta</h2>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<?php if(isset($_GET['success'])): ?>
    <div class="alert alert-success">Venta registrada correctamente.</div>
<?php endif; ?>

<form method="POST" id="ventaForm">
    <div class="row mb-3">
        <div class="col-md-4">
            <label class="form-label">Cliente</label>
            <select class="form-select" name="cliente_id" required>
                <option value="">Seleccione Cliente...</option>
                <?php foreach ($clientes as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">Agregar Productos</div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Producto</label>
                    <select class="form-select" id="prodSelect">
                        <option value="">Seleccione...</option>
                        <?php foreach ($productos as $p): ?>
                        <option value="<?php echo $p['id']; ?>" data-price="<?php echo $p['precio']; ?>" data-name="<?php echo htmlspecialchars($p['nombre']); ?>">
                            <?php echo htmlspecialchars($p['nombre']); ?> - $<?php echo $p['precio']; ?> (Stock: <?php echo $p['stock']; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control" id="prodQty" value="1" min="1">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-secondary w-100" onclick="addItem()">Agregar</button>
                </div>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-hover" id="itemsTable">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio Unit.</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <!-- Items go here -->
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th id="totalAmount">$0.00</th>
                <th></th>
            </tr>
        </tfoot>
    </table>

    <input type="hidden" name="items" id="itemsInput">
    <button type="submit" class="btn btn-dark btn-lg">Registrar Venta</button>
</form>

<hr class="my-5">

<h3 class="mb-3">Últimas Ventas</h3>
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th>Total</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $v): ?>
            <tr>
                <td><?php echo $v['id']; ?></td>
                <td><?php echo $v['fecha']; ?></td>
                <td><?php echo htmlspecialchars($v['cliente'] ?? 'General'); ?></td>
                <td><?php echo htmlspecialchars($v['usuario']); ?></td>
                <td><span class="fw-bold text-success">$<?php echo number_format($v['total'], 2); ?></span></td>
                <td class="text-end">
                    <a href="#" class="btn btn-sm btn-outline-info" title="Ver Detalles">
                        <i class="bi bi-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
let items = [];

function addItem() {
    const select = document.getElementById('prodSelect');
    const qtyInput = document.getElementById('prodQty');
    const id = select.value;
    const qty = parseInt(qtyInput.value);
    
    if (!id || qty < 1) return;

    const option = select.options[select.selectedIndex];
    const name = option.getAttribute('data-name');
    const price = parseFloat(option.getAttribute('data-price'));

    // Check if exists
    const existing = items.find(i => i.id === id);
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
    let total = 0;

    items.forEach((item, index) => {
        const subtotal = item.price * item.qty;
        total += subtotal;
        tbody.innerHTML += `
            <tr>
                <td>${item.name}</td>
                <td>$${item.price.toFixed(2)}</td>
                <td>${item.qty}</td>
                <td>$${subtotal.toFixed(2)}</td>
                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${index})">X</button></td>
            </tr>
        `;
    });

    document.getElementById('totalAmount').innerText = '$' + total.toFixed(2);
    document.getElementById('itemsInput').value = JSON.stringify(items);
}

document.getElementById('ventaForm').addEventListener('submit', function(e) {
    if (items.length === 0) {
        e.preventDefault();
        alert('Agrega al menos un producto.');
    }
});
</script>

<?php include '../inc/footer.php'; ?>