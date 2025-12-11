<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../conexion/db.php';

if (isset($_POST['guardar'])) {
    guardar($_POST['guardar']);
}

if (isset($_POST['obtener_detalles'])) {
    obtener_detalles($_POST['obtener_detalles']);
}

if (isset($_POST['eliminar'])) {
    eliminar($_POST['eliminar']);
}

function guardar($lista) {
    $json_datos = json_decode($lista, true);
    $base_datos = new DB();

    // Logging
    error_log('detalle_factura.php guardar - Datos recibidos: ' . print_r($json_datos, true));

    try {
        $conexion = $base_datos->conectar();
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $idProd = $json_datos['id_productos'] ?? null;
        $cantidad = $json_datos['cantidad'] ?? 0;
        $monto_total = isset($json_datos['monto_total']) ? $json_datos['monto_total'] : null;
        $precio_unitario = isset($json_datos['precio_unitario']) ? $json_datos['precio_unitario'] : null;
        $id_factura = $json_datos['id_factura_compra'] ?? null;

        error_log("detalle_factura.php - id_productos: $idProd, cantidad: $cantidad, id_factura: $id_factura");

        if (!$idProd) {
            echo json_encode(['error' => 'Falta id_productos en detalle']);
            return;
        }

        if (!$id_factura) {
            echo json_encode(['error' => 'Falta id_factura_compra en detalle']);
            return;
        }

        // Obtener precio unitario si no fue enviado
        $stmtP = $conexion->prepare("SELECT precio, costo, iva FROM productos WHERE id_productos = :id LIMIT 1");
        $stmtP->execute(['id' => $idProd]);
        $prod = $stmtP->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            echo json_encode(['error' => 'Producto no encontrado: ' . $idProd]);
            return;
        }

        $unit = $precio_unitario !== null ? $precio_unitario : ($prod['costo'] ?? $prod['precio']);
        // total neto sin IVA
        $total_bruto = $unit * $cantidad;

        // Obtener tasa IVA directamente del producto
        $iva_valor = floatval($prod['iva'] ?? 0);
        $rate = $iva_valor / 100.0;  // Convertir de porcentaje a decimal (ej: 10 -> 0.10)

        $total_iva = round($total_bruto * $rate, 2);
        $total_neto = round($total_bruto + $total_iva, 2);

        error_log("detalle_factura.php - Cálculos: bruto=$total_bruto, iva=$total_iva, neto=$total_neto");

        $query = $conexion->prepare(
            "INSERT INTO detalle_factura (cantidad, total_bruto, total_iva, total_neto, tipo_pago, id_factura_compra, monto_total, id_productos)
             VALUES (:cantidad, :total_bruto, :total_iva, :total_neto, :tipo_pago, :id_factura_compra, :monto_total, :id_productos);"
        );

        $resultado = $query->execute([
            'cantidad' => $cantidad,
            'total_bruto' => $total_bruto,
            'total_iva' => $total_iva,
            'total_neto' => $total_neto,
            'tipo_pago' => $json_datos['tipo_pago'] ?? null,
            'id_factura_compra' => $id_factura,
            'monto_total' => $total_neto,
            'id_productos' => $idProd,
        ]);

        if ($resultado) {
            error_log("detalle_factura.php - Detalle guardado exitosamente");
            echo json_encode(['success' => true, 'message' => 'Detalle guardado correctamente']);
        } else {
            error_log("detalle_factura.php - Error al insertar el detalle");
            echo json_encode(['error' => 'Error al insertar el detalle']);
        }

    } catch (PDOException $e) {
        error_log("detalle_factura.php - Error PDO: " . $e->getMessage());
        echo json_encode(['error' => 'Error PDO: ' . $e->getMessage()]);
    } catch (Exception $e) {
        error_log("detalle_factura.php - Error: " . $e->getMessage());
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}

function obtener_detalles($id_factura) {
    $base_datos = new DB();
    
    error_log("detalle_factura.php obtener_detalles - id_factura: $id_factura");
    
    $query = $base_datos->conectar()->prepare(
        "SELECT df.id_detalle_factura, df.cantidad, df.id_factura_compra, df.id_productos, p.nombre_producto, df.monto_total
         FROM detalle_factura df
         LEFT JOIN productos p ON df.id_productos = p.id_productos
         WHERE df.id_factura_compra = :id;"
    );
    $query->execute(['id' => $id_factura]);
    $res = $query->fetchAll(PDO::FETCH_ASSOC);
    
    error_log("detalle_factura.php obtener_detalles - Registros encontrados: " . count($res));
    error_log("detalle_factura.php obtener_detalles - Resultado: " . print_r($res, true));
    
    echo json_encode($res ?: array());
}
?>function eliminar($id_detalle) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "DELETE FROM detalle_factura WHERE id_detalle_factura = :id;"
    );
    $query->execute(['id' => $id_detalle]);
    echo json_encode(['success' => 'Detalle eliminado correctamente']);
}
?>
