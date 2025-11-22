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
        $stmt = $pdo->prepare("INSERT INTO clientes (nombre, contacto, telefono) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $contacto, $telefono]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Crear Cliente', 'clientes', $pdo->lastInsertId(), "Nombre: $nombre");
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE clientes SET nombre=?, contacto=?, telefono=? WHERE id=?");
        $stmt->execute([$nombre, $contacto, $telefono, $id]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Editar Cliente', 'clientes', $id, "Nombre: $nombre");
    }
    redirect('clientes.php');
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    registrar_historial($pdo, $_SESSION['user_id'], 'Eliminar Cliente', 'clientes', $id, '');
    redirect('clientes.php');
}

$clientes = $pdo->query("SELECT * FROM clientes ORDER BY id DESC")->fetchAll();

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Clientes</h1>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#clienteModal" onclick="resetForm()">
        Nuevo cliente
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
            <?php foreach ($clientes as $c): ?>
            <tr>
                <td><?php echo $c['id']; ?></td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($c['nombre']); ?></span></td>
                <td><?php echo htmlspecialchars($c['contacto']); ?></td>
                <td><?php echo htmlspecialchars($c['telefono']); ?></td>
                <td class="text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#clienteModal"
                                data-id="<?php echo $c['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($c['nombre']); ?>"
                                data-contacto="<?php echo htmlspecialchars($c['contacto']); ?>"
                                data-telefono="<?php echo htmlspecialchars($c['telefono']); ?>"
                                onclick="editCliente(this)"
                                title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal" 
                                data-url="clientes.php?delete=<?php echo $c['id']; ?>"
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

<!-- Modal Cliente -->
<div class="modal fade" id="clienteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo Cliente</h5>
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
    document.getElementById('modalTitle').innerText = 'Nuevo Cliente';
    document.getElementById('action').value = 'create';
    document.getElementById('id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('contacto').value = '';
    document.getElementById('telefono').value = '';
}

function editCliente(btn) {
    document.getElementById('modalTitle').innerText = 'Editar Cliente';
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = btn.getAttribute('data-id');
    document.getElementById('nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('contacto').value = btn.getAttribute('data-contacto');
    document.getElementById('telefono').value = btn.getAttribute('data-telefono');
}
</script>

<?php include '../inc/modals.php'; ?>
<?php include '../inc/footer.php'; ?>
