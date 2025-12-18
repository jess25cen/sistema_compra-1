<?php
header('Content-Type: application/json');

require_once("../conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

if (isset($_GET['accion']) || isset($_POST['accion'])) {
    $accion = $_GET['accion'] ?? $_POST['accion'];
    
    if ($accion === 'generar') {
        try {
            $movimiento = $_GET['movimiento'] ?? $_POST['movimiento'] ?? '';
            $especificacion = $_GET['especificacion'] ?? $_POST['especificacion'] ?? '';
            $fecha_desde = $_GET['fecha_desde'] ?? $_POST['fecha_desde'] ?? '';
            $fecha_hasta = $_GET['fecha_hasta'] ?? $_POST['fecha_hasta'] ?? '';
            
            $resultado = array();
            
            // Construir consulta según el tipo de movimiento
            switch ($movimiento) {
                case 'pedido_compra':
                    $sql = "SELECT 
                            pc.pedido_compra as id_registro, 
                            pc.pedido_compra as numero,
                            pc.fecha_compra as fecha, 
                            pc.estado,
                            u.nombre_usuario
                        FROM pedido_compra pc
                        JOIN usuarios u ON pc.id_usuario = u.id_usuario
                        WHERE pc.estado != 'ELIMINADO'";
                    
                    if (!empty($especificacion)) {
                        $sql .= " AND pc.pedido_compra LIKE :especificacion";
                    }
                    if (!empty($fecha_desde)) {
                        $sql .= " AND pc.fecha_compra >= :fecha_desde";
                    }
                    if (!empty($fecha_hasta)) {
                        $sql .= " AND pc.fecha_compra <= :fecha_hasta";
                    }
                    
                    $sql .= " ORDER BY pc.pedido_compra DESC";
                    break;
                    
                case 'orden_compra':
                    $sql = "SELECT 
                            oc.id_orden_compra as id_registro, 
                            oc.id_orden_compra as numero,
                            oc.fecha_orden as fecha, 
                            oc.estado,
                            CONCAT(prov.nombre, ' ', prov.apellido) as nombre_proveedor,
                            u.nombre_usuario
                        FROM orden_compra oc
                        JOIN proveedor prov ON oc.id_proveedor = prov.id_proveedor
                        JOIN usuarios u ON oc.id_usuario = u.id_usuario
                        WHERE oc.estado != 'ELIMINADO'";
                    
                    if (!empty($especificacion)) {
                        $sql .= " AND oc.id_orden_compra LIKE :especificacion";
                    }
                    if (!empty($fecha_desde)) {
                        $sql .= " AND oc.fecha_orden >= :fecha_desde";
                    }
                    if (!empty($fecha_hasta)) {
                        $sql .= " AND oc.fecha_orden <= :fecha_hasta";
                    }
                    
                    $sql .= " ORDER BY oc.id_orden_compra DESC";
                    break;
                    
                case 'factura_compra':
                    $sql = "SELECT 
                            fc.id_factura_compra as id_registro, 
                            fc.numero_factura as numero,
                            fc.fecha_factura as fecha, 
                            fc.estado,
                            CONCAT(prov.nombre, ' ', prov.apellido) as nombre_proveedor,
                            u.nombre_usuario
                        FROM factura_compra fc
                        JOIN proveedor prov ON fc.id_proveedor = prov.id_proveedor
                        JOIN usuarios u ON fc.id_usuario = u.id_usuario
                        WHERE fc.estado != 'ELIMINADO'";
                    
                    if (!empty($especificacion)) {
                        $sql .= " AND fc.numero_factura LIKE :especificacion";
                    }
                    if (!empty($fecha_desde)) {
                        $sql .= " AND fc.fecha_factura >= :fecha_desde";
                    }
                    if (!empty($fecha_hasta)) {
                        $sql .= " AND fc.fecha_factura <= :fecha_hasta";
                    }
                    
                    $sql .= " ORDER BY fc.numero_factura DESC";
                    break;
                    
                case 'presupuesto_compra':
                    $sql = "SELECT 
                            p.id_presupuesto as id_registro, 
                            p.id_presupuesto as numero,
                            p.fecha_presupuesto as fecha, 
                            p.estado,
                            CONCAT(prov.nombre, ' ', prov.apellido) as nombre_proveedor,
                            u.nombre_usuario
                        FROM presupuesto p
                        JOIN proveedor prov ON p.id_proveedor = prov.id_proveedor
                        JOIN usuarios u ON p.id_usuario = u.id_usuario
                        WHERE p.estado != 'ELIMINADO'";
                    
                    if (!empty($especificacion)) {
                        $sql .= " AND p.id_presupuesto LIKE :especificacion";
                    }
                    if (!empty($fecha_desde)) {
                        $sql .= " AND p.fecha_presupuesto >= :fecha_desde";
                    }
                    if (!empty($fecha_hasta)) {
                        $sql .= " AND p.fecha_presupuesto <= :fecha_hasta";
                    }
                    
                    $sql .= " ORDER BY p.id_presupuesto DESC";
                    break;
                    
                case 'nota_credito_compra':
                    $sql = "SELECT 
                            nc.id_nota_credito as id_registro, 
                            nc.numero_nota as numero,
                            nc.fecha_nota as fecha, 
                            nc.estado,
                            CONCAT(prov.nombre, ' ', prov.apellido) as nombre_proveedor,
                            u.nombre_usuario
                        FROM nota_credito nc
                        JOIN proveedor prov ON nc.id_proveedor = prov.id_proveedor
                        JOIN usuarios u ON nc.id_usuario = u.id_usuario
                        WHERE nc.estado != 'ELIMINADO'";
                    
                    if (!empty($especificacion)) {
                        $sql .= " AND nc.numero_nota LIKE :especificacion";
                    }
                    if (!empty($fecha_desde)) {
                        $sql .= " AND nc.fecha_nota >= :fecha_desde";
                    }
                    if (!empty($fecha_hasta)) {
                        $sql .= " AND nc.fecha_nota <= :fecha_hasta";
                    }
                    
                    $sql .= " ORDER BY nc.numero_nota DESC";
                    break;
                    
                default:
                    echo json_encode(array(
                        'success' => false,
                        'mensaje' => 'Tipo de movimiento no válido'
                    ));
                    exit;
            }
            
            $stmt = $conexion->prepare($sql);
            
            $params = array();
            if (!empty($especificacion)) {
                $params[':especificacion'] = '%' . $especificacion . '%';
            }
            if (!empty($fecha_desde)) {
                $params[':fecha_desde'] = $fecha_desde;
            }
            if (!empty($fecha_hasta)) {
                $params[':fecha_hasta'] = $fecha_hasta;
            }
            
            $stmt->execute($params);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(array(
                'success' => true,
                'datos' => $resultado,
                'total' => count($resultado)
            ));
            
        } catch (PDOException $e) {
            echo json_encode(array(
                'success' => false,
                'mensaje' => 'Error en la base de datos: ' . $e->getMessage()
            ));
        }
    }
}
?>
