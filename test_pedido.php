<?php
session_start();
require_once("conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

// Verificar si la tabla existe
echo "<h3>Probando conexión...</h3>";

try {
    // Verificar registros en pedido_compra
    $sql = "SELECT pc.pedido_compra, pc.fecha_compra, pc.estado, u.nombre AS nombre_usuario
            FROM pedido_compra pc
            JOIN usuarios u ON pc.id_usuario = u.id_usuarios
            WHERE pc.estado != 'ELIMINADO'
            ORDER BY pc.pedido_compra DESC";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total de registros: " . count($resultado) . "</p>";
    
    if (count($resultado) > 0) {
        echo "<pre>";
        echo json_encode($resultado, JSON_PRETTY_PRINT);
        echo "</pre>";
    } else {
        echo "<p>No hay registros</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}

// Verificar que existen las tablas
echo "<h3>Estructuras de tablas:</h3>";
try {
    $sql = "SHOW TABLES LIKE 'pedido_compra'";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Tabla pedido_compra existe: " . (count($tables) > 0 ? "Sí" : "No") . "</p>";
    
    $sql = "SHOW TABLES LIKE 'usuarios'";
    $stmt = $conexion->prepare($sql);
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Tabla usuarios existe: " . (count($tables) > 0 ? "Sí" : "No") . "</p>";
} catch (PDOException $e) {
    echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
}
?>
