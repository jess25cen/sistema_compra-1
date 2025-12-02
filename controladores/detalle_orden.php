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

    try {
        $conexion = $base_datos->conectar();
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Obtener datos del producto para completar precio_unitario e id_tipo_producto
        $idProd = $json_datos['id_productos'] ?? null;
        $precioUnitario = null;
        $idTipoProducto = null;
        if ($idProd) {
            $stmtP = $conexion->prepare("SELECT precio, costo, id_tipo_producto FROM productos WHERE id_productos = :id LIMIT 1");
            $stmtP->execute(['id' => $idProd]);
            $prod = $stmtP->fetch(PDO::FETCH_ASSOC);
            if ($prod) {
                // Preferir precio_unitario enviado desde el cliente si existe
                if (isset($json_datos['precio_unitario']) && is_numeric($json_datos['precio_unitario'])) {
                    $precioUnitario = $json_datos['precio_unitario'];
                } else {
                    // fallback: usar costo del producto si existe, sino precio
                    $precioUnitario = isset($prod['costo']) ? $prod['costo'] : $prod['precio'];
                }
                $idTipoProducto = $prod['id_tipo_producto'];
            } else {
                echo json_encode(['error' => 'Producto no encontrado: ' . $idProd]);
                return;
            }
        } else {
            echo json_encode(['error' => 'Falta id_productos en detalle']);
            return;
        }

        if (empty($idTipoProducto)) {
            // intentar obtener un tipo de producto por defecto
            $stmtT = $conexion->prepare("SELECT id_tipo_producto FROM tipo_producto LIMIT 1");
            $stmtT->execute();
            $t = $stmtT->fetch(PDO::FETCH_ASSOC);
            if ($t && !empty($t['id_tipo_producto'])) {
                $idTipoProducto = $t['id_tipo_producto'];
            } else {
                echo json_encode(['error' => 'No hay tipos de producto definidos. Cree al menos un tipo de producto antes de agregar detalles.']);
                return;
            }
        }

        $query = $conexion->prepare(
            "INSERT INTO detalle_orden (cantidad, id_orden_compra, id_producto, precio_unitario, id_tipo_producto)
             VALUES (:cantidad, :id_orden_compra, :id_producto, :precio_unitario, :id_tipo_producto);"
        );

        $resultado = $query->execute([
            'cantidad' => $json_datos['cantidad'],
            'id_orden_compra' => $json_datos['orden_compra'],
            'id_producto' => $idProd,
            'precio_unitario' => $precioUnitario ?? 0,
            'id_tipo_producto' => $idTipoProducto,
        ]);

        if ($resultado) {
            echo json_encode(['success' => 'Detalle guardado correctamente']);
        } else {
            echo json_encode(['error' => 'Error al insertar el detalle']);
        }

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error PDO: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}

function obtener_detalles($id_orden) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT do.id_detalle_orden, do.cantidad, do.id_orden_compra AS orden_compra, do.id_producto AS id_productos, p.nombre_producto, p.precio
         FROM detalle_orden do
         LEFT JOIN productos p ON do.id_producto = p.id_productos
         WHERE do.id_orden_compra = :id_orden;"
    );
    $query->execute(['id_orden' => $id_orden]);
    if ($query->rowCount()) {
        echo json_encode($query->fetchAll(PDO::FETCH_OBJ));
    } else {
        echo '0';
    }
}

function eliminar($id_detalle) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "DELETE FROM detalle_orden WHERE id_detalle_orden = :id;"
    );
    $query->execute(['id' => $id_detalle]);
    echo json_encode(['success' => 'Detalle eliminado correctamente']);
}
?>