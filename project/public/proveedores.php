<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $nombre = sanitize($_POST['nombre']);
    $contacto = sanitize($_POST['contacto']);
    $telefono = sanitize($_POST['telefono']);

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO proveedores (nombre, contacto, telefono) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $contacto, $telefono]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Crear Proveedor', 'proveedores', $pdo->lastInsertId(), "Nombre: $nombre");
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE proveedores SET nombre=?, contacto=?, telefono=? WHERE id=?");
        $stmt->execute([$nombre, $contacto, $telefono, $id]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Editar Proveedor', 'proveedores', $id, "Nombre: $nombre");
    }
    redirect('proveedores.php');
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM proveedores WHERE id = ?");
    $stmt->execute([$id]);
    registrar_historial($pdo, $_SESSION['user_id'], 'Eliminar Proveedor', 'proveedores', $id, '');
    redirect('proveedores.php');
}

$proveedores = $pdo->query("SELECT * FROM proveedores ORDER BY id DESC")->fetchAll();

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Proveedores</h1>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#proveedorModal" onclick="resetForm()">
        Nuevo Proveedor
    </button>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Contacto</th>
                <th>Teléfono</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proveedores as $p): ?>
            <tr>
                <td><?php echo $p['id']; ?></td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($p['nombre']); ?></span></td>
                <td><?php echo htmlspecialchars($p['contacto']); ?></td>
                <td><?php echo htmlspecialchars($p['telefono']); ?></td>
                <td class="text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#proveedorModal"
                                data-id="<?php echo $p['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($p['nombre']); ?>"
                                data-contacto="<?php echo htmlspecialchars($p['contacto']); ?>"
                                data-telefono="<?php echo htmlspecialchars($p['telefono']); ?>"
                                onclick="editProveedor(this)"
                                title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal" 
                                data-url="proveedores.php?delete=<?php echo $p['id']; ?>"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Proveedor -->
<div class="modal fade" id="proveedorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo Proveedor</h5>
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
            <label class="form-label">Contacto</label>
            <input type="text" class="form-control" name="contacto" id="contacto">
        </div>
        <div class="mb-3">
            <label class="form-label">Teléfono</label>
            <input type="text" class="form-control" name="telefono" id="telefono">
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
    document.getElementById('modalTitle').innerText = 'Nuevo Proveedor';
    document.getElementById('action').value = 'create';
    document.getElementById('id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('contacto').value = '';
    document.getElementById('telefono').value = '';
}

function editProveedor(btn) {
    document.getElementById('modalTitle').innerText = 'Editar Proveedor';
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = btn.getAttribute('data-id');
    document.getElementById('nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('contacto').value = btn.getAttribute('data-contacto');
    document.getElementById('telefono').value = btn.getAttribute('data-telefono');
}
</script>

<?php include '../inc/modals.php'; ?>
<?php include '../inc/footer.php'; ?>
