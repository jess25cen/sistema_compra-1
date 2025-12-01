<?php
header('Content-Type: application/json');

require_once("../conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

if (isset($_GET['guardar']) || isset($_POST['guardar'])) {
    try {
        $datos = json_decode($_POST['guardar'] ?? $_GET['guardar'], true);
        
        $sql = "INSERT INTO detalle_presupuesto (cantidad, id_presupuesto, id_productos) 
                VALUES (:cantidad, :id_presupuesto, :id_productos)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':cantidad' => $datos['cantidad'],
            ':id_presupuesto' => $datos['id_presupuesto'],
            ':id_productos' => $datos['id_productos']
        ]);
        
        $id_detalle = $conexion->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'id_detalle_presupuesto' => $id_detalle
        ]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['obtener']) || isset($_POST['obtener'])) {
    try {
        $id = $_POST['obtener'] ?? $_GET['obtener'];
        
        $sql = "SELECT dp.*, p.nombre_producto FROM detalle_presupuesto dp 
                LEFT JOIN productos p ON dp.id_productos = p.id_productos 
                WHERE dp.id_detalle_presupuesto = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            echo json_encode($resultado);
        } else {
            echo json_encode(array());
        }
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}
if (isset($_GET['eliminar']) || isset($_POST['eliminar'])) {
    try {
        $id = $_POST['eliminar'] ?? $_GET['eliminar'];
        
        $sql = "DELETE FROM detalle_presupuesto WHERE id_detalle_presupuesto = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Detalle eliminado correctamente'
        ]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

?>