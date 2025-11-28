<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar que todos los campos estén presentes
    if (empty($_POST['tipo_documento']) || empty($_POST['numero_documento']) || 
        empty($_POST['dia']) || empty($_POST['mes']) || empty($_POST['anio'])) {
        $error = 'Todos los campos son requeridos';
    } else {
        $tipo_documento = sanitize($_POST['tipo_documento']);
        $numero_documento = sanitize($_POST['numero_documento']);
        $dia = sanitize($_POST['dia']);
        $mes = sanitize($_POST['mes']);
        $anio = sanitize($_POST['anio']);
        
        // Construir fecha de nacimiento
        $fecha_nacimiento = "$anio-$mes-$dia";
        
        $loginResult = login($pdo, $tipo_documento, $numero_documento, $fecha_nacimiento);
        
        if ($loginResult === true) {
            redirect('dashboard.php');
        } elseif ($loginResult === 'role_mismatch') {
            $error = 'El rol no coincide con las credenciales proporcionadas';
        } else {
            $error = 'Tipo de documento, número de documento o fecha de nacimiento incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="col-md-7 col-lg-6 col-xl-5">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <?php if($error): ?>
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <div><?php echo $error; ?></div>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="row g-3">
                        <div class="col-12">
                            <label for="numero_documento" class="form-label fw-bold">Documento de identidad</label>
                            <div class="input-group">
                                <select class="form-select" name="tipo_documento" required style="max-width: 80px;">
                                    <option value="">Tipo</option>
                                    <option value="CC">CC</option>
                                </select>
                                <input type="text" class="form-control" id="numero_documento" name="numero_documento" placeholder="Número" required>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Fecha de nacimiento</label>
                            <div class="row g-2">
                                <div class="col">
                                    <select class="form-select" name="dia" required>
                                        <option value="">Día</option>
                                        <?php for($d=1; $d<=31; $d++): ?>
                                            <option value="<?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?>"><?php echo $d; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <div class="col">
                                    <select class="form-select" name="mes" required>
                                        <option value="">Mes</option>
                                        <option value="01">Ene.</option>
                                        <option value="02">Feb.</option>
                                        <option value="03">Mar.</option>
                                        <option value="04">Abr.</option>
                                        <option value="05">May.</option>
                                        <option value="06">Jun.</option>
                                        <option value="07">Jul.</option>
                                        <option value="08">Ago.</option>
                                        <option value="09">Sep.</option>
                                        <option value="10">Oct.</option>
                                        <option value="11">Nov.</option>
                                        <option value="12">Dic.</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <select class="form-select" name="anio" required>
                                        <option value="">Año</option>
                                        <?php for($y=date('Y')-15; $y>=2000; $y--): ?>
                                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Continuar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>