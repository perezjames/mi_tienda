<?php
session_start();

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }
}

function check_role($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        die("Acceso denegado. Rol requerido: " . implode(', ', $allowed_roles));
    }
}

function login($pdo, $email, $password, $role_selected) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['role'] !== $role_selected) {
            return 'role_mismatch';
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    return false;
}

function logout() {
    session_destroy();
    redirect('login.php');
}
?>