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
    <title>Iniciar sesión - Mi Tienda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">
                <div class="text-center mb-4">
                    <i class="bi bi-shop" style="font-size: 4rem; color: #212529;"></i>
                    <h1 class="h3 mt-3 fw-bold">Mi Tienda</h1>
                    <p class="text-muted">Sistema de gestión</p>
                </div>
                
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="h5 mb-4 text-center fw-semibold">Iniciar sesión</h2>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Documento</label>
                                <div class="row g-2">
                                    <div class="col-5">
                                        <select class="form-select" name="tipo_documento" required>
                                            <option value="">Tipo</option>
                                            <option value="DNI">DNI</option>
                                            <option value="CE">CE</option>
                                            <option value="Pasaporte">Pasaporte</option>
                                            <option value="RUC">RUC</option>
                                        </select>
                                    </div>
                                    <div class="col-7">
                                        <input type="text" class="form-control" name="numero_documento" placeholder="Número" required>
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
                                                <option value="<?php echo str_pad($d, 2, '0', STR_PAD_LEFT); ?>"><?php echo $d; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-4">
                                        <select class="form-select" name="mes" required>
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
                                        <select class="form-select" name="anio" required>
                                            <option value="">Año</option>
                                            <?php for($y=2024; $y>=1940; $y--): ?>
                                                <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-dark btn-lg w-100 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Continuar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>