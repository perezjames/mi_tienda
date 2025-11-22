<?php
require_once '../inc/db.php';
require_once '../inc/auth.php';
require_once '../inc/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $loginResult = login($pdo, $email, $password, $role);
    
    if ($loginResult === true) {
        redirect('dashboard.php');
    } elseif ($loginResult === 'role_mismatch') {
        $error = 'Credenciales incorrectas para el rol seleccionado';
    } else {
        $error = 'Credenciales incorrectas';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f5f5f5; }
        .login-form { width: 100%; max-width: 330px; padding: 15px; margin: auto; }
    </style>
</head>
<body>
    <main class="login-form text-center">
        <form method="POST">
            <h1 class="h3 mb-3 fw-normal">Iniciar sesión</h1>
            <?php if($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <div class="form-floating mb-3">
                <select class="form-select" id="role" name="role" required>
                    <option value="admin">Administrador</option>
                    <option value="trabajador">Trabajador</option>
                    <option value="proveedor">Proveedor</option>
                    <option value="cliente">Cliente</option>
                </select>
                <label for="role">Rol</label>
            </div>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                <label for="email">Email</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password">Contraseña</label>
            </div>
            <button class="w-100 btn btn-lg btn-dark" type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>