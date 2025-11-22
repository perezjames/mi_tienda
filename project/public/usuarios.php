<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();
check_role(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $nombre = sanitize($_POST['nombre']);
    $email = sanitize($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    if ($action === 'create') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nombre, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $hash, $role]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Crear Usuario', 'users', $pdo->lastInsertId(), "Email: $email");
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nombre=?, email=?, password=?, role=? WHERE id=?");
            $stmt->execute([$nombre, $email, $hash, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nombre=?, email=?, role=? WHERE id=?");
            $stmt->execute([$nombre, $email, $role, $id]);
        }
        registrar_historial($pdo, $_SESSION['user_id'], 'Editar Usuario', 'users', $id, "Email: $email");
    }
    redirect('usuarios.php');
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if ($id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Eliminar Usuario', 'users', $id, '');
    }
    redirect('usuarios.php');
}

$usuarios = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();

include '../inc/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Usuarios</h1>
    <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#usuarioModal" onclick="resetForm()">
        Nuevo usuario
    </button>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($u['nombre']); ?></span></td>
                <td><?php echo htmlspecialchars($u['email']); ?></td>
                <td>
                    <span class="badge bg-secondary"><?php echo ucfirst($u['role']); ?></span>
                </td>
                <td class="text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#usuarioModal"
                                data-id="<?php echo $u['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($u['nombre']); ?>"
                                data-email="<?php echo htmlspecialchars($u['email']); ?>"
                                data-role="<?php echo $u['role']; ?>"
                                onclick="editUsuario(this)"
                                title="Editar">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                        <?php if($u['id'] != $_SESSION['user_id']): ?>
                        <button class="btn btn-sm btn-outline-danger" 
                                data-bs-toggle="modal" 
                                data-bs-target="#deleteModal" 
                                data-url="usuarios.php?delete=<?php echo $u['id']; ?>"
                                title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Usuario -->
<div class="modal fade" id="usuarioModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Nuevo usuario</h5>
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
            <label class="form-label">Email</label>
            <input type="text" class="form-control" name="email" id="email" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contraseña <small class="text-muted">(Dejar en blanco para no cambiar)</small></label>
            <input type="password" class="form-control" name="password" id="password">
        </div>
        <div class="mb-3">
            <label class="form-label">Rol</label>
            <select class="form-select" name="role" id="role" required>
                <option value="trabajador">Trabajador</option>
                <option value="admin">Admin</option>
                <option value="proveedor">Proveedor</option>
                <option value="cliente">Cliente</option>
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
    document.getElementById('modalTitle').innerText = 'Nuevo Usuario';
    document.getElementById('action').value = 'create';
    document.getElementById('id').value = '';
    document.getElementById('nombre').value = '';
    document.getElementById('email').value = '';
    document.getElementById('password').value = '';
    document.getElementById('role').value = 'trabajador';
}

function editUsuario(btn) {
    document.getElementById('modalTitle').innerText = 'Editar Usuario';
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = btn.getAttribute('data-id');
    document.getElementById('nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('email').value = btn.getAttribute('data-email');
    document.getElementById('role').value = btn.getAttribute('data-role');
    document.getElementById('password').value = '';
}
</script>

<?php include '../inc/modals.php'; ?>
<?php include '../inc/footer.php'; ?>