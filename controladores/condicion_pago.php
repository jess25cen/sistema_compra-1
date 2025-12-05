<?php
header('Content-Type: application/json');
require_once('../conexion/db.php');
$db = new DB();
$c = $db->conectar();

try {
    // Intentar leer desde la tabla, si existe
    $stmt = $c->prepare("SELECT id_condicion, nombre FROM condicion_pago ORDER BY id_condicion");
    $stmt->execute();
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($res ?: array());
} catch (PDOException $e) {
    // Si la tabla no existe o hay error, devolver fallback con Credito y Contado
    $fallback = [
        ['id_condicion' => 1, 'nombre' => 'Credito'],
        ['id_condicion' => 2, 'nombre' => 'Contado']
    ];
    echo json_encode($fallback);
}

?>
