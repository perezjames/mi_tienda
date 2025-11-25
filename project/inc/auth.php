<?php
session_start();

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function check_role($allowed_roles) {
    if (!in_array($_SESSION['rol'], $allowed_roles)) {
        die("Acceso denegado. Rol requerido: " . implode(', ', $allowed_roles));
    }
}

function login($pdo, $tipo_documento, $numero_documento, $fecha_nacimiento) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE tipo_documento = ? AND numero_documento = ? AND estado = 'activo'");
    $stmt->execute([$tipo_documento, $numero_documento]);
    $user = $stmt->fetch();

    if ($user) {
        // Verificar fecha de nacimiento
        if ($user['fecha_nacimiento'] === $fecha_nacimiento) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['correo'] = $user['correo'];
            $_SESSION['rol'] = $user['rol'];
            return true;
        }
    }
    return false;
}

function logout() {
    session_destroy();
    redirect('login.php');
}
?>