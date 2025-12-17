<?php
// Test script para verificar los detalles de factura
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion/db.php';

// Simular POST
$_POST['obtener_detalles'] = $_GET['id'] ?? 28;

error_log("Test - obtener_detalles: " . $_POST['obtener_detalles']);

if (isset($_POST['obtener_detalles'])) {
    $id_factura = $_POST['obtener_detalles'];
    $id_factura = intval($id_factura);
    
    error_log("Test - id_factura convertido: $id_factura");
    
    if ($id_factura > 0) {
        $base_datos = new DB();
        $conexion = $base_datos->conectar();
        
        $query = $conexion->prepare(
            "SELECT df.id_detalle_factura, df.cantidad, df.id_factura_compra, df.id_productos, p.nombre_producto, df.total, df.total_iva, df.costo
             FROM detalle_factura df
             LEFT JOIN productos p ON df.id_productos = p.id_productos
             WHERE df.id_factura_compra = :id"
        );
        $query->execute(['id' => $id_factura]);
        $res = $query->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Test - Registros encontrados: " . count($res));
        error_log("Test - Resultado: " . print_r($res, true));
        
        echo json_encode([
            'success' => true,
            'id_factura' => $id_factura,
            'count' => count($res),
            'data' => $res
        ]);
    } else {
        echo json_encode(['error' => 'ID inválido']);
    }
}
?>
