<?php
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function registrar_historial($pdo, $usuario_id, $accion, $tabla, $registro_id, $detalles = '') {
    $stmt = $pdo->prepare("INSERT INTO historial (usuario_id, accion, tabla_afectada, registro_id, detalles) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $accion, $tabla, $registro_id, $detalles]);
}
?>