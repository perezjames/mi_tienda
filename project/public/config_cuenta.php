<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';
check_login();

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_documento = sanitize($_POST['tipo_documento']);
    $numero_documento = sanitize($_POST['numero_documento']);
    $nombre = sanitize($_POST['nombre']);
    $correo = sanitize($_POST['correo']);
    $dia = sanitize($_POST['dia']);
    $mes = sanitize($_POST['mes']);
    $anio = sanitize($_POST['anio']);
    $fecha_nacimiento = "$anio-$mes-$dia";
    
    try {
        // Verificar si el nuevo número de documento ya existe (excepto el actual)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE numero_documento = ? AND id != ?");
        $stmt->execute([$numero_documento, $_SESSION['user_id']]);
        
        if ($stmt->fetch()) {
            $mensaje = 'El número de documento ya está registrado por otro usuario';
            $tipo_mensaje = 'danger';
        } else {
            // Actualizar datos del usuario
            $stmt = $pdo->prepare("UPDATE users SET tipo_documento=?, numero_documento=?, nombre=?, correo=?, fecha_nacimiento=? WHERE id=?");
            $stmt->execute([$tipo_documento, $numero_documento, $nombre, $correo, $fecha_nacimiento, $_SESSION['user_id']]);
            
            // Actualizar sesión
            $_SESSION['nombre'] = $nombre;
            $_SESSION['correo'] = $correo;
            
            registrar_historial($pdo, $_SESSION['user_id'], 'Actualizar Configuración', 'users', $_SESSION['user_id'], 'Datos personales actualizados');
            
            $mensaje = 'Datos actualizados correctamente';
            $tipo_mensaje = 'success';
        }
    } catch (Exception $e) {
        $mensaje = 'Error al actualizar los datos: ' . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
}

// Obtener datos actuales del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$usuario = $stmt->fetch();

// Separar fecha de nacimiento
$fecha_parts = explode('-', $usuario['fecha_nacimiento']);
$anio_actual = $fecha_parts[0];
$mes_actual = $fecha_parts[1];
$dia_actual = $fecha_parts[2];

include '../inc/header.php';
?>

<h2 class="mb-4">Configuración de cuenta</h2>

<?php if($mensaje): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-<?php echo $tipo_mensaje == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>-fill me-2"></i>
        <?php echo $mensaje; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card rounded-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-person-gear me-2"></i>Información personal</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre completo</label>
                        <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Correo electrónico</label>
                        <input type="email" class="form-control" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Documento de identidad</label>
                        <div class="row g-2">
                            <div class="col-5">
                                <select class="form-select" name="tipo_documento" required>
                                    <option value="CC" <?php echo $usuario['tipo_documento'] == 'CC' ? 'selected' : ''; ?>>CC</option>
                                </select>
                            </div>
                            <div class="col-7">
                                <input type="text" class="form-control" name="numero_documento" value="<?php echo htmlspecialchars($usuario['numero_documento']); ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Fecha de nacimiento</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <select class="form-select" name="dia" required>
                                    <option value="">Día</option>
                                    <?php for($d=1; $d<=31; $d++): ?>
                                        <option value="<?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?>" <?php echo $dia_actual == str_pad($d, 2, '0', STR_PAD_LEFT) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" name="mes" required>
                                    <option value="">Mes</option>
                                    <option value="01" <?php echo $mes_actual == '01' ? 'selected' : ''; ?>>Enero</option>
                                    <option value="02" <?php echo $mes_actual == '02' ? 'selected' : ''; ?>>Febrero</option>
                                    <option value="03" <?php echo $mes_actual == '03' ? 'selected' : ''; ?>>Marzo</option>
                                    <option value="04" <?php echo $mes_actual == '04' ? 'selected' : ''; ?>>Abril</option>
                                    <option value="05" <?php echo $mes_actual == '05' ? 'selected' : ''; ?>>Mayo</option>
                                    <option value="06" <?php echo $mes_actual == '06' ? 'selected' : ''; ?>>Junio</option>
                                    <option value="07" <?php echo $mes_actual == '07' ? 'selected' : ''; ?>>Julio</option>
                                    <option value="08" <?php echo $mes_actual == '08' ? 'selected' : ''; ?>>Agosto</option>
                                    <option value="09" <?php echo $mes_actual == '09' ? 'selected' : ''; ?>>Septiembre</option>
                                    <option value="10" <?php echo $mes_actual == '10' ? 'selected' : ''; ?>>Octubre</option>
                                    <option value="11" <?php echo $mes_actual == '11' ? 'selected' : ''; ?>>Noviembre</option>
                                    <option value="12" <?php echo $mes_actual == '12' ? 'selected' : ''; ?>>Diciembre</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select class="form-select" name="anio" required>
                                    <option value="">Año</option>
                                    <?php for($y=2024; $y>=1940; $y--): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $anio_actual == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-dark">
                        <i class="bi bi-save me-2"></i>Guardar cambios
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Cancelar
                    </a>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card rounded-3">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Información de cuenta</h5>
            </div>
            <div class="card-body">
                <p><strong>Rol:</strong> <span class="badge bg-secondary"><?php echo ucfirst($usuario['rol']); ?></span></p>
                <p><strong>Estado:</strong> <span class="badge bg-success"><?php echo ucfirst($usuario['estado']); ?></span></p>
                <p><strong>Registrado:</strong><br><?php echo date('d/m/Y', strtotime($usuario['created_at'])); ?></p>
            </div>
        </div>
    </div>
</div>

<?php include '../inc/footer.php'; ?>
