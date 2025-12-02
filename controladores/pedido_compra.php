<?php
header('Content-Type: application/json');

require_once("../conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

if (isset($_GET['listar']) || isset($_POST['listar'])) {
    try {
        $sql = "SELECT 
                pc.pedido_compra, 
                pc.fecha_compra, 
                pc.estado,
                u.nombre_usuario
            FROM pedido_compra pc
            JOIN usuarios u ON pc.id_usuario = u.id_usuario
            WHERE pc.estado != 'ELIMINADO'
            ORDER BY pc.pedido_compra DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($resultado) > 0) {
            echo json_encode($resultado);
        } else {
            echo json_encode(array());
        }
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['guardar']) || isset($_POST['guardar'])) {
    try {
        $datos = json_decode($_POST['guardar'] ?? $_GET['guardar'], true);
        
        $sql = "INSERT INTO pedido_compra (fecha_compra, id_usuario, estado) 
                VALUES (:fecha_compra, :id_usuario, :estado)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':fecha_compra' => $datos['fecha_compra'],
            ':id_usuario' => $datos['id_usuario'],
            ':estado' => $datos['estado'] ?? 'ACTIVO'
        ]);
        
        $id_pedido = $conexion->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'id_pedido_compra' => $id_pedido
        ]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['anular']) || isset($_POST['anular'])) {
    try {
        $id = $_POST['anular'] ?? $_GET['anular'];
        
        $sql = "UPDATE pedido_compra SET estado = 'ANULADO' WHERE pedido_compra = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Pedido anulado correctamente'
        ]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['obtener_por_id']) || isset($_POST['obtener_por_id'])) {
    try {
        $id = $_POST['obtener_por_id'] ?? $_GET['obtener_por_id'];
        
        $sql = "SELECT * FROM pedido_compra WHERE pedido_compra = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado) {
            echo json_encode($resultado);
        } else {
            echo "0";
        }
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['buscar']) || isset($_POST['buscar'])) {
    try {
        $texto = $_POST['buscar'] ?? $_GET['buscar'];
        
        $sql = "SELECT 
                pc.pedido_compra, 
                pc.fecha_compra, 
                pc.estado,
                u.nombre_usuario
            FROM pedido_compra pc
            JOIN usuarios u ON pc.id_usuario = u.id_usuario
            WHERE pc.estado != 'ELIMINADO' 
            AND (pc.pedido_compra LIKE :texto OR u.nombre_usuario LIKE :texto)
            ORDER BY pc.pedido_compra DESC";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':texto' => '%' . $texto . '%']);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($resultado) > 0) {
            echo json_encode($resultado);
        } else {
            echo json_encode(array());
        }
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['obtener_detalles']) || isset($_POST['obtener_detalles'])) {
    try {
        $id = $_POST['obtener_detalles'] ?? $_GET['obtener_detalles'];
        
        $sql = "SELECT dp.*, p.nombre_producto, p.precio 
                FROM detalle_pedido dp 
                LEFT JOIN productos p ON dp.id_productos = p.id_productos 
                WHERE dp.pedido_compra = :id";
        
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($resultado) > 0) {
            echo json_encode($resultado);
        } else {
            echo json_encode(array());
        }
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

?>
