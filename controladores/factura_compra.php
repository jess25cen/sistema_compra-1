<?php
header('Content-Type: application/json');

require_once("../conexion/db.php");

$db = new DB();
$conexion = $db->conectar();

function has_column($pdo, $table, $column) {
    $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table AND COLUMN_NAME = :col LIMIT 1");
    $stmt->execute([':table' => $table, ':col' => $column]);
    return (bool) $stmt->fetchColumn();
}

$hasCond = has_column($conexion, 'factura_compra', 'id_condicion');

if (isset($_POST['listar']) || isset($_GET['listar'])) {
    try {
        $select = "SELECT f.id_factura_compra, f.numero_factura, f.fecha_factura, f.estado, u.nombre_usuario, prov.nombre AS proveedor_nombre";
        if ($hasCond) $select .= ", cp.nombre AS condicion_nombre";
        $select .= " FROM factura_compra f
            LEFT JOIN usuarios u ON f.id_usuario = u.id_usuario
            LEFT JOIN proveedor prov ON f.id_proveedor = prov.id_proveedor";
        if ($hasCond) $select .= " LEFT JOIN condicion_pago cp ON f.id_condicion = cp.id_condicion";
        $select .= " WHERE f.estado != 'ELIMINADO' ORDER BY f.id_factura_compra DESC";

        $stmt = $conexion->prepare($select);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($resultado ?: array());
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
    exit;
}

if (isset($_POST['guardar']) || isset($_GET['guardar'])) {
    try {
        $datos = json_decode($_POST['guardar'] ?? $_GET['guardar'], true);

        // asegurar numero_factura no nulo (DB lo define NOT NULL)
        $numero = (isset($datos['numero_factura']) && $datos['numero_factura'] !== null && $datos['numero_factura'] !== '') ? $datos['numero_factura'] : 0;

        $fields = ['numero_factura', 'fecha_factura', 'id_orden_compra', 'timbrado', 'fecha_vencimiento', 'id_proveedor', 'id_usuario', 'estado'];
        $params = [
            ':numero_factura' => $numero,
            ':fecha_factura' => $datos['fecha_factura'] ?? date('Y-m-d'),
            ':id_orden_compra' => $datos['id_orden_compra'] ?? null,
            ':timbrado' => $datos['timbrado'] ?? null,
            ':fecha_vencimiento' => $datos['fecha_vencimiento'] ?? null,
            ':id_proveedor' => $datos['id_proveedor'] ?? null,
            ':id_usuario' => $datos['id_usuario'] ?? 1,
            ':estado' => $datos['estado'] ?? 'ACTIVO'
        ];

        if ($hasCond) {
            $fields[] = 'id_condicion';
            $params[':id_condicion'] = $datos['id_condicion'] ?? null;
        }

        $cols = implode(', ', $fields);
        $placeholders = implode(', ', array_map(function($f){ return ':' . $f; }, $fields));

        $sql = "INSERT INTO factura_compra ({$cols}) VALUES ({$placeholders})";
        $stmt = $conexion->prepare($sql);
        $stmt->execute($params);

        $id = $conexion->lastInsertId();
        echo json_encode(['success' => true, 'id_factura_compra' => $id]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
    exit;
}

if (isset($_POST['anular']) || isset($_GET['anular'])) {
    try {
        $id = $_POST['anular'] ?? $_GET['anular'];
        $sql = "UPDATE factura_compra SET estado = 'ANULADO' WHERE id_factura_compra = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(array('error' => $e->getMessage()));
    }
    exit;
}

if (isset($_POST['obtener_por_id']) || isset($_GET['obtener_por_id'])) {
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
    exit;
}

if (isset($_POST['obtener_detalles']) || isset($_GET['obtener_detalles'])) {
    try {
        $id = $_POST['obtener_detalles'] ?? $_GET['obtener_detalles'];
        $sql = "SELECT df.id_detalle_factura, df.cantidad, df.monto_total, df.id_productos, p.nombre_producto
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
    exit;
}

// Generar y guardar registro en libro_compra (subtotales por IVA)
if (isset($_POST['generar_libro']) || isset($_GET['generar_libro'])) {
    try {
        $id_factura = $_POST['generar_libro'] ?? $_GET['generar_libro'];
        // calcular subtotales por tasa usando tipos de producto
        $sql = "SELECT p.id_tipo_producto, tp.nombre_tipo,
                       SUM(df.total_bruto) AS suma_bruto,
                       SUM(df.total_iva) AS suma_iva
                  FROM detalle_factura df
                  LEFT JOIN productos p ON df.id_productos = p.id_productos
                  LEFT JOIN tipo_producto tp ON p.id_tipo_producto = tp.id_tipo_producto
                 WHERE df.id_factura_compra = :id
              GROUP BY p.id_tipo_producto, tp.nombre_tipo";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([':id' => $id_factura]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $iva_5 = 0.0; $iva_10 = 0.0; $exenta = 0.0; $total_iva = 0.0; $subtotal = 0.0;
        foreach ($rows as $r) {
            $nt = strtolower($r['nombre_tipo'] ?? '');
            $bruto = floatval($r['suma_bruto'] ?? 0);
            $iva = floatval($r['suma_iva'] ?? 0);
            $total_iva += $iva;
            $subtotal += $bruto;
            if (strpos($nt, '10') !== false) {
                $iva_10 += $bruto;
            } else if (strpos($nt, '5') !== false) {
                $iva_5 += $bruto;
            } else if (strpos($nt, 'ex') !== false || strpos($nt, 'exenta') !== false) {
                $exenta += $bruto;
            } else {
                // unknown: treat as exenta
                $exenta += $bruto;
            }
        }

        // insertar o actualizar libro_compra
        // verificar si ya existe registro para la factura
        $check = $conexion->prepare("SELECT id_libro_compra FROM libro_compra WHERE id_factura_compra = :id LIMIT 1");
        $check->execute([':id' => $id_factura]);
        if ($row = $check->fetch(PDO::FETCH_ASSOC)) {
            $upd = $conexion->prepare("UPDATE libro_compra SET iva_5 = :iva5, exenta = :ex, iva_10 = :iva10 WHERE id_factura_compra = :id");
            $upd->execute([':iva5' => $iva_5, ':ex' => $exenta, ':iva10' => $iva_10, ':id' => $id_factura]);
        } else {
            $ins = $conexion->prepare("INSERT INTO libro_compra (iva_5, exenta, iva_10, id_factura_compra) VALUES (:iva5, :ex, :iva10, :id)");
            $ins->execute([':iva5' => $iva_5, ':ex' => $exenta, ':iva10' => $iva_10, ':id' => $id_factura]);
        }

        echo json_encode(['success' => true, 'subtotal' => $subtotal, 'total_iva' => $total_iva, 'iva_5' => $iva_5, 'iva_10' => $iva_10, 'exenta' => $exenta]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

?>
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
