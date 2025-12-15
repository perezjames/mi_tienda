<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();
check_role(['administrador']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $nombre = sanitize($_POST['nombre']);
    $correo = sanitize($_POST['correo']);
    $rol = $_POST['rol'];
    $tipo_documento = sanitize($_POST['tipo_documento']);
    $numero_documento = sanitize($_POST['numero_documento']);
    $dia = sanitize($_POST['dia']);
    $mes = sanitize($_POST['mes']);
    $anio = sanitize($_POST['anio']);
    $fecha_nacimiento = "$anio-$mes-$dia";

    if ($action === 'create') {
        $stmt = $pdo->prepare("INSERT INTO users (tipo_documento, numero_documento, fecha_nacimiento, nombre, correo, password, rol, estado) VALUES (?, ?, ?, ?, ?, 'sin_password', ?, 'activo')");
        $stmt->execute([$tipo_documento, $numero_documento, $fecha_nacimiento, $nombre, $correo, $rol]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Crear Usuario', 'users', $pdo->lastInsertId(), "Correo: $correo");
    } elseif ($action === 'edit') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("UPDATE users SET tipo_documento=?, numero_documento=?, fecha_nacimiento=?, nombre=?, correo=?, rol=? WHERE id=?");
        $stmt->execute([$tipo_documento, $numero_documento, $fecha_nacimiento, $nombre, $correo, $rol, $id]);
        registrar_historial($pdo, $_SESSION['user_id'], 'Editar Usuario', 'users', $id, "Correo: $correo");
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

<div class="d-flex mb-4 border-bottom">
    <h2 class="h2"></h2>
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
                <th>Documento</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Estado</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?php echo $u['id']; ?></td>
                <td><span class="fw-bold"><?php echo htmlspecialchars($u['nombre']); ?></span></td>
                <td><?php echo $u['tipo_documento'] . ' ' . $u['numero_documento']; ?></td>
                <td><?php echo htmlspecialchars($u['correo']); ?></td>
                <td>
                    <span class="badge bg-secondary"><?php echo ucfirst($u['rol']); ?></span>
                </td>
                <td>
                    <span class="badge bg-<?php echo $u['estado'] == 'activo' ? 'success' : 'danger'; ?>"><?php echo ucfirst($u['estado']); ?></span>
                </td>
                <td class="text-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#usuarioModal"
                                data-id="<?php echo $u['id']; ?>"
                                data-nombre="<?php echo htmlspecialchars($u['nombre']); ?>"
                                data-correo="<?php echo htmlspecialchars($u['correo']); ?>"
                                data-rol="<?php echo $u['rol']; ?>"
                                data-tipo-documento="<?php echo $u['tipo_documento']; ?>"
                                data-numero-documento="<?php echo $u['numero_documento']; ?>"
                                data-fecha-nacimiento="<?php echo $u['fecha_nacimiento']; ?>"
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
            <label class="form-label">Nombre completo</label>
            <input type="text" class="form-control" name="nombre" id="nombre" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" name="correo" id="correo" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Documento</label>
            <div class="row g-2">
                <div class="col-5">
                    <select class="form-select" name="tipo_documento" id="tipo_documento" required>
                        <option value="DNI">DNI</option>
                        <option value="CE">CE</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="RUC">RUC</option>
                    </select>
                </div>
                <div class="col-7">
                    <input type="text" class="form-control" name="numero_documento" id="numero_documento" placeholder="Número" required>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Fecha de nacimiento</label>
            <div class="row g-2">
                <div class="col-4">
                    <select class="form-select" name="dia" id="dia" required>
                        <option value="">Día</option>
                        <?php for($d=1; $d<=31; $d++): ?>
                            <option value="<?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?>"><?php echo $d; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-4">
                    <select class="form-select" name="mes" id="mes" required>
                        <option value="">Mes</option>
                        <option value="01">Enero</option>
                        <option value="02">Febrero</option>
                        <option value="03">Marzo</option>
                        <option value="04">Abril</option>
                        <option value="05">Mayo</option>
                        <option value="06">Junio</option>
                        <option value="07">Julio</option>
                        <option value="08">Agosto</option>
                        <option value="09">Septiembre</option>
                        <option value="10">Octubre</option>
                        <option value="11">Noviembre</option>
                        <option value="12">Diciembre</option>
                    </select>
                </div>
                <div class="col-4">
                    <select class="form-select" name="anio" id="anio" required>
                        <option value="">Año</option>
                        <?php for($y=2024; $y>=1940; $y--): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Rol</label>
            <select class="form-select" name="rol" id="rol" required>
                <option value="trabajador">Trabajador</option>
                <option value="administrador">Administrador</option>
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
    document.getElementById('correo').value = '';
    document.getElementById('tipo_documento').value = 'DNI';
    document.getElementById('numero_documento').value = '';
    document.getElementById('dia').value = '';
    document.getElementById('mes').value = '';
    document.getElementById('anio').value = '';
    document.getElementById('rol').value = 'trabajador';
}

function editUsuario(btn) {
    document.getElementById('modalTitle').innerText = 'Editar Usuario';
    document.getElementById('action').value = 'edit';
    document.getElementById('id').value = btn.getAttribute('data-id');
    document.getElementById('nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('correo').value = btn.getAttribute('data-correo');
    document.getElementById('tipo_documento').value = btn.getAttribute('data-tipo-documento');
    document.getElementById('numero_documento').value = btn.getAttribute('data-numero-documento');
    
    const fecha = btn.getAttribute('data-fecha-nacimiento').split('-');
    document.getElementById('anio').value = fecha[0];
    document.getElementById('mes').value = fecha[1];
    document.getElementById('dia').value = fecha[2];
    
    document.getElementById('rol').value = btn.getAttribute('data-rol');
}
</script>

<?php include '../inc/modals.php'; ?>
<?php include '../inc/footer.php'; ?>