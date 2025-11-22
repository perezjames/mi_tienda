<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $nombre = sanitize($_POST['nombre']);
    $codigo = sanitize($_POST['codigo']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria_id = $_POST['categoria_id'];

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO productos (nombre, codigo, precio, stock, categoria_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $codigo, $precio, $stock, $categoria_id]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Crear Producto', 'productos', $pdo->lastInsertId(), "Nombre: $nombre");
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        
        // Get old data for history
        $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $old = $stmt->fetch();
        
        $changes = [];
        if ($old['nombre'] != $nombre) $changes[] = "Nombre: {$old['nombre']} -> $nombre";
        if ($old['codigo'] != $codigo) $changes[] = "Código: {$old['codigo']} -> $codigo";
        if ($old['precio'] != $precio) $changes[] = "Precio: {$old['precio']} -> $precio";
        if ($old['stock'] != $stock) $changes[] = "Stock: {$old['stock']} -> $stock";
        if ($old['categoria_id'] != $categoria_id) $changes[] = "Cat ID: {$old['categoria_id']} -> $categoria_id";
        
        $detalles = empty($changes) ? "Sin cambios" : implode(", ", $changes);

        $stmt = $pdo->prepare("UPDATE productos SET nombre=?, codigo=?, precio=?, stock=?, categoria_id=? WHERE id=?");
        $stmt->execute([$nombre, $codigo, $precio, $stock, $categoria_id, $id]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Editar Producto', 'productos', $id, $detalles);
    } elseif ($action === 'delete') {
        // Handled via GET usually, but let's support POST delete for security if needed, 
        // but for simplicity I'll use GET for delete link in table with modal confirmation that submits a form or links to a delete script.
        // Actually, the modal script sets href on a link. Let's handle GET delete below.
    }
    redirect('productos.php');
}

// Handle Delete via GET
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    registrar_historial($pdo, $_SESSION['user_id'], 'Eliminar Producto', 'productos', $id, '');
    redirect('productos.php');
}

// Fetch Data
$productos = $pdo->query("SELECT p.*, c.nombre as categoria FROM productos p LEFT JOIN categorias c ON p.categoria_id = c.id ORDER BY p.id DESC")->fetchAll();
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll();

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Productos</h1>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#productoModal" onclick="resetForm()">
        Nuevo Producto
    </button>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover table-sm">
        <thead>
            <tr>
                <th>ID</th>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><?php echo htmlspecialchars($p['codigo']); ?></td>
                <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                <td><?php echo htmlspecialchars($p['categoria'] ?? 'Sin Cat'); ?></td>
                <td>$<?php echo number_format($p['precio'], 2); ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td>
                    <button class="btn btn-sm btn-outline-secondary" 
                            data-bs-toggle="modal" 
                            data-bs-target="#productoModal"
                            data-id="<?php echo $p['id']; ?>"
                            data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>"
                            data-codigo="<?php echo htmlspecialchars($p['codigo']); ?>"
                            data-precio="<?php echo $p['precio']; ?>"
                            data-stock="<?php echo $p['stock']; ?>"
                            data-categoria="<?php echo $p['categoria_id']; ?>"
                            onclick="editProducto(this)">
                        Editar
                    </button>
                    <button class="btn btn-sm btn-outline-danger" 
                            data-bs-toggle="modal" 
                            data-bs-target="#deleteModal" 
                            data-url="productos.php?delete=<?php echo $p['id']; ?>">
                        Eliminar
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Producto -->
<div class="modal fade" id="productoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo Producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" id="action" value="create">
        <input type="hidden" name="id" id="id">
        
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Código</label>
            <input type="text" class="form-control" name="codigo" id="codigo">
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Precio</label>
                <input type="number" step="0.01" class="form-control" name="precio" id="precio" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Stock</label>
                <input type="number" class="form-control" name="stock" id="stock" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select class="form-select" name="categoria_id" id="categoria_id">
                <option value="">Seleccione...</option>
                <?php foreach ($categorias as $c): ?>
                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-dark">Guardar</button>
      </div>
    </form>
  </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerText = 'Nuevo Producto';
    document.getElementById('action').value = 'create';
    document.getElementById('id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('codigo').value = '';
    document.getElementById('precio').value = '';
    document.getElementById('stock').value = '';
    document.getElementById('categoria_id').value = '';
}

function editProducto(btn) {
    document.getElementById('modalTitle').innerText = 'Editar Producto';
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = btn.getAttribute('data-id');
    document.getElementById('nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('codigo').value = btn.getAttribute('data-codigo');
    document.getElementById('precio').value = btn.getAttribute('data-precio');
    document.getElementById('stock').value = btn.getAttribute('data-stock');
    document.getElementById('categoria_id').value = btn.getAttribute('data-categoria');
}
</script>

<?php include '../inc/modals.php'; ?>
<?php include '../inc/footer.php'; ?>
