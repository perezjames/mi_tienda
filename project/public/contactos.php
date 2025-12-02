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
    $tipo = sanitize($_POST['tipo']);

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO contactos (nombre, contacto, telefono, tipo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $contacto, $telefono, $tipo]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Crear contacto', 'contactos', $pdo->lastInsertId(), "Nombre: $nombre, Tipo: $tipo");
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE contactos SET nombre=?, contacto=?, telefono=?, tipo=? WHERE id=?");
        $stmt->execute([$nombre, $contacto, $telefono, $tipo, $id]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Editar contacto', 'contactos', $id, "Nombre: $nombre, Tipo: $tipo");
    }
    redirect('contactos.php');
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contactos WHERE id = ?");
    $stmt->execute([$id]);
    registrar_historial($pdo, $_SESSION['user_id'], 'Eliminar contacto', 'contactos', $id, '');
    redirect('contactos.php');
}

$contactos = $pdo->query("SELECT * FROM contactos ORDER BY id DESC")->fetchAll();

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Contactos</h1>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#contactoModal" onclick="resetForm()">
        Nuevo contacto
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
                <th>Tipo</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contactos as $c): ?>
            <tr>
                <td><?php echo $c['id']; ?></td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($c['nombre']); ?></span></td>
                <td><?php echo htmlspecialchars($c['contacto']); ?></td>
                <td><?php echo htmlspecialchars($c['telefono']); ?></td>
                <td><span class="badge bg-<?php echo $c['tipo'] === 'cliente' ? 'info' : 'warning'; ?>"><?php echo ucfirst(htmlspecialchars($c['tipo'])); ?></span></td>
                <td class="text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#contactoModal"
                                data-id="<?php echo $c['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($c['nombre']); ?>"
                                data-contacto="<?php echo htmlspecialchars($c['contacto']); ?>"
                                data-telefono="<?php echo htmlspecialchars($c['telefono']); ?>"
                                data-tipo="<?php echo htmlspecialchars($c['tipo']); ?>"
                                onclick="editContacto(this)"
                                title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal" 
                                data-url="contactos.php?delete=<?php echo $c['id']; ?>"
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

<!-- Modal Contacto -->
<div class="modal fade" id="contactoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo contacto</h5>
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
        <div class="mb-3">
            <label class="form-label">Tipo</label>
            <select class="form-select" name="tipo" id="tipo" required>
                <option value="cliente">Cliente</option>
                <option value="proveedor">Proveedor</option>
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
    document.getElementById('modalTitle').innerText = 'Nuevo Contacto';
    document.getElementById('action').value = 'create';
    document.getElementById('id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('contacto').value = '';
    document.getElementById('telefono').value = '';
    document.getElementById('tipo').value = 'cliente';
}

function editContacto(btn) {
    document.getElementById('modalTitle').innerText = 'Editar contacto';
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = btn.getAttribute('data-id');
    document.getElementById('nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('contacto').value = btn.getAttribute('data-contacto');
    document.getElementById('telefono').value = btn.getAttribute('data-telefono');
    document.getElementById('tipo').value = btn.getAttribute('data-tipo');
}
</script>

<?php include '../inc/modals.php'; ?>
<?php include '../inc/footer.php'; ?>