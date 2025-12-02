<?php
header('Content-Type: application/json');

session_start();

if (isset($_GET['obtener']) || isset($_POST['obtener'])) {
    if (isset($_SESSION['id_usuario'])) {
        echo json_encode([
            'id_usuario' => $_SESSION['id_usuario'],
            'nombre_usuario' => $_SESSION['nombre_completo'] ?? 'Usuario',
            'nombre_completo' => $_SESSION['nombre_completo'] ?? 'Usuario'
        ]);
    } else {
        echo json_encode(['error' => 'No hay sesión activa']);
    }
} else {
    echo json_encode(['error' => 'Parámetro obtener no encontrado']);
}
?>
