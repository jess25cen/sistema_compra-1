<?php
header('Content-Type: application/json');
require_once '../conexion/db.php';

$db = new DB();
$conexion = $db->conectar();

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        listar();
        break;
    case 'guardar':
        guardar();
        break;
    case 'obtener_detalles':
        obtener_detalles();
        break;
    case 'actualizar':
        actualizar();
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Acción no válida']);
}

function listar() {
    global $conexion;
    try {
        $buscar = $_POST['buscar'] ?? '';
        
        $sql = "SELECT nc.id_nota_credito, nc.numero_nota, nc.fecha_nota, nc.monto_total, nc.estado,
                        p.nombre AS proveedor_nombre, fc.numero_factura
                FROM nota_credito nc
                LEFT JOIN proveedor p ON nc.id_proveedor = p.id_proveedor
                LEFT JOIN factura_compra fc ON nc.id_factura_compra = fc.id_factura_compra
                WHERE nc.estado != 'ELIMINADO'";
        
        if (!empty($buscar)) {
            $sql .= " AND (nc.numero_nota LIKE :buscar OR p.nombre LIKE :buscar OR fc.numero_factura LIKE :buscar)";
        }
        
        $sql .= " ORDER BY nc.fecha_nota DESC";
        
        $stmt = $conexion->prepare($sql);
        if (!empty($buscar)) {
            $stmt->execute([':buscar' => '%' . $buscar . '%']);
        } else {
            $stmt->execute();
        }
        
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultado);
        
    } catch (Exception $e) {
        error_log('nota_credito.php - listar(): ' . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function guardar() {
    global $conexion;
    try {
        $numero_nota = $_POST['numero_nota'] ?? '';
        $fecha_nota = $_POST['fecha_nota'] ?? date('Y-m-d');
        $id_factura_compra = $_POST['id_factura_compra'] ?? 0;
        $id_proveedor = $_POST['id_proveedor'] ?? 0;
        $motivo = $_POST['motivo'] ?? '';
        $observaciones = $_POST['observaciones'] ?? '';
        $monto_total = floatval($_POST['monto_total'] ?? 0);
        $detalles = json_decode($_POST['detalles'] ?? '[]', true);
        $id_usuario = $_POST['id_usuario'] ?? 1;
        
        error_log("nota_credito.php - guardar(): numero=$numero_nota, fecha=$fecha_nota, id_factura=$id_factura_compra, detalles=".count($detalles));
        
        $conexion->beginTransaction();
        
        // Insertar cabecera
        $sql_nc = "INSERT INTO nota_credito 
                   (numero_nota, fecha_nota, id_factura_compra, id_proveedor, motivo, observaciones, monto_total, estado, id_usuario, fecha_creacion)
                   VALUES (?, ?, ?, ?, ?, ?, ?, 'ACTIVO', ?, NOW())";
        
        $stmt = $conexion->prepare($sql_nc);
        $stmt->execute([$numero_nota, $fecha_nota, $id_factura_compra, $id_proveedor, $motivo, $observaciones, $monto_total, $id_usuario]);
        
        $id_nota_credito = $conexion->lastInsertId();
        error_log("nota_credito.php - Nota creada con ID: $id_nota_credito");
        
        // Insertar detalles
        $sql_det = "INSERT INTO detalle_nota_credito 
                    (id_nota_credito, id_productos, cantidad, precio_unitario, total)
                    VALUES (?, ?, ?, ?, ?)";
        
        $stmt_det = $conexion->prepare($sql_det);
        foreach ($detalles as $d) {
            $id_prod = $d['id_productos'] ?? 0;
            $cantidad = floatval($d['cantidad'] ?? 0);
            $precio_unit = floatval($d['precio_unitario'] ?? 0);
            $total = floatval($d['total'] ?? 0);
            
            error_log("nota_credito.php - Insertando detalle: prod=$id_prod, cant=$cantidad, precio=$precio_unit");
            
            $stmt_det->execute([$id_nota_credito, $id_prod, $cantidad, $precio_unit, $total]);
        }
        
        $conexion->commit();
        
        echo json_encode(['success' => true, 'id_nota_credito' => $id_nota_credito]);
        
    } catch (Exception $e) {
        $conexion->rollBack();
        error_log('nota_credito.php - guardar(): ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function obtener_detalles() {
    global $conexion;
    try {
        $id_nota = $_POST['id_nota_credito'] ?? 0;
        
        // Obtener cabecera
        $sql_cabecera = "SELECT nc.*, p.nombre AS proveedor_nombre, fc.numero_factura
                         FROM nota_credito nc
                         LEFT JOIN proveedor p ON nc.id_proveedor = p.id_proveedor
                         LEFT JOIN factura_compra fc ON nc.id_factura_compra = fc.id_factura_compra
                         WHERE nc.id_nota_credito = ?";
        
        $stmt = $conexion->prepare($sql_cabecera);
        $stmt->execute([$id_nota]);
        $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Obtener detalles
        $sql_detalles = "SELECT dnc.*, prod.nombre_producto
                         FROM detalle_nota_credito dnc
                         LEFT JOIN productos prod ON dnc.id_productos = prod.id_productos
                         WHERE dnc.id_nota_credito = ?";
        
        $stmt_det = $conexion->prepare($sql_detalles);
        $stmt_det->execute([$id_nota]);
        $detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['cabecera' => $cabecera, 'detalles' => $detalles]);
        
    } catch (Exception $e) {
        error_log('nota_credito.php - obtener_detalles(): ' . $e->getMessage());
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function actualizar() {
    global $conexion;
    try {
        $id_nota = $_POST['id_nota_credito'] ?? 0;
        $estado = $_POST['estado'] ?? 'ACTIVO';
        
        $sql = "UPDATE nota_credito SET estado = ? WHERE id_nota_credito = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$estado, $id_nota]);
        
        error_log("nota_credito.php - actualizar(): id_nota=$id_nota, estado=$estado");
        
        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        error_log('nota_credito.php - actualizar(): ' . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
