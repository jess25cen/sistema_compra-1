<?php
header('Content-Type: application/json');
session_start();
require_once("conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

// Debug: Mostrar estructura de tablas
$debug = array();

// Verificar tabla pedido_compra
try {
    $sql = "DESCRIBE pedido_compra";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $debug['pedido_compra_estructura'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $debug['pedido_compra_error'] = $e->getMessage();
}

// Verificar tabla usuarios
try {
    $sql = "DESCRIBE usuarios";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $debug['usuarios_estructura'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $debug['usuarios_error'] = $e->getMessage();
}

// Contar registros en pedido_compra
try {
    $sql = "SELECT COUNT(*) as total FROM pedido_compra";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $debug['pedido_compra_count'] = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $debug['count_error'] = $e->getMessage();
}

// Obtener todos los registros de pedido_compra sin JOIN
try {
    $sql = "SELECT * FROM pedido_compra";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $debug['pedido_compra_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $debug['data_error'] = $e->getMessage();
}

// Intentar la consulta con JOIN
try {
    $sql = "SELECT 
            pc.pedido_compra, 
            pc.fecha_compra, 
            pc.estado,
            u.nombre AS nombre_usuario
        FROM pedido_compra pc
        JOIN usuarios u ON pc.id_usuario = u.id_usuarios
        WHERE pc.estado != 'ELIMINADO'
        ORDER BY pc.pedido_compra DESC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $debug['join_result'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $debug['join_error'] = $e->getMessage();
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>
