<?php
header('Content-Type: application/json');

require_once("../conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

if (isset($_GET['listar']) || isset($_POST['listar'])) {
    try {
        $sql = "SELECT f.id_factura_compra, f.numero_factura, f.fecha_factura, f.estado, u.nombre_usuario, prov.nombre AS proveedor_nombre
            FROM factura_compra f
            LEFT JOIN usuarios u ON f.id_usuario = u.id_usuario
            LEFT JOIN proveedor prov ON f.id_proveedor = prov.id_proveedor
            WHERE f.estado != 'ELIMINADO'
            ORDER BY f.id_factura_compra DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultado ?: array());
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['guardar']) || isset($_POST['guardar'])) {
    try {
        $datos = json_decode($_POST['guardar'] ?? $_GET['guardar'], true);

        $sql = "INSERT INTO factura_compra (numero_factura, fecha_factura, id_orden_compra, timbrado, fecha_vencimiento, id_proveedor, id_usuario, estado)
                VALUES (:numero_factura, :fecha_factura, :id_orden_compra, :timbrado, :fecha_vencimiento, :id_proveedor, :id_usuario, :estado)";

        $stmt = $conexion->prepare($sql);
        $stmt->execute([
            ':numero_factura' => $datos['numero_factura'] ?? null,
            ':fecha_factura' => $datos['fecha_factura'] ?? date('Y-m-d'),
            ':id_orden_compra' => $datos['id_orden_compra'] ?? null,
            ':timbrado' => $datos['timbrado'] ?? null,
            ':fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
            ':id_proveedor' => $datos['id_proveedor'] ?? null,
            ':id_usuario' => $datos['id_usuario'] ?? 1,
            ':estado' => $datos['estado'] ?? 'ACTIVO'
        ]);

        $id = $conexion->lastInsertId();
        echo json_encode(['success' => true, 'id_factura_compra' => $id]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['anular']) || isset($_POST['anular'])) {
    try {
        $id = $_POST['anular'] ?? $_GET['anular'];
        $sql = "UPDATE factura_compra SET estado = 'ANULADO' WHERE id_factura_compra = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['obtener_por_id']) || isset($_POST['obtener_por_id'])) {
    try {
        $id = $_POST['obtener_por_id'] ?? $_GET['obtener_por_id'];
        $sql = "SELECT * FROM factura_compra WHERE id_factura_compra = :id LIMIT 1";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        echo $res ? json_encode($res) : '0';
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

if (isset($_GET['obtener_detalles']) || isset($_POST['obtener_detalles'])) {
    try {
        $id = $_POST['obtener_detalles'] ?? $_GET['obtener_detalles'];
        $sql = "SELECT df.id_detalle_factura, df.cantidad, df.total_bruto, df.total_iva, df.total_neto, df.monto_total, df.id_productos, p.nombre_producto
                FROM detalle_factura df
                LEFT JOIN productos p ON df.id_productos = p.id_productos
                WHERE df.id_factura_compra = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($res ?: array());
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
}

?>
