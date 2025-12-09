<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../conexion/db.php';

if (isset($_POST['listar'])) {
    listar();
}

if (isset($_POST['guardar'])) {
    guardar($_POST['guardar']);
}

if (isset($_POST['anular'])) {
    anular($_POST['anular']);
}

if (isset($_POST['id'])) {
    obtener_por_id($_POST['id']);
}

if (isset($_POST['buscar'])) {
    buscar($_POST['buscar']);
}

if (isset($_POST['obtener_detalles'])) {
    obtener_detalles($_POST['obtener_detalles']);
}

function listar() {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT oc.id_orden_compra as orden_compra, oc.fecha_orden, oc.estado, oc.condiciones_pago, oc.id_usuario, u.nombre_usuario,
               oc.id_proveedor, prov.nombre AS proveedor_nombre,
               pr.id_presupuesto AS presupuesto_id, pr.fecha_presupuesto
           FROM orden_compra oc
           LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
           LEFT JOIN presupuesto pr ON oc.id_presupuesto = pr.id_presupuesto
           LEFT JOIN proveedor prov ON oc.id_proveedor = prov.id_proveedor
       ORDER BY oc.id_orden_compra DESC;"
    );
    $query->execute();
    if ($query->rowCount()) {
        echo json_encode($query->fetchAll(PDO::FETCH_OBJ));
    } else {
        echo '0';
    }
}

function guardar($lista) {
    $json_datos = json_decode($lista, true);
    $base_datos = new DB();
    $conexion = $base_datos->conectar();

    try {
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = $conexion->prepare(
            "INSERT INTO orden_compra (fecha_orden, estado, condiciones_pago, id_usuario, id_proveedor, id_presupuesto)
             VALUES (:fecha_orden, :estado, :condiciones_pago, :id_usuario, :id_proveedor, :id_presupuesto);"
        );

        // Validaciones mínimas: estos campos son NOT NULL en la nueva estructura
        if (empty($json_datos['id_usuario']) || empty($json_datos['id_proveedor_orden']) || empty($json_datos['id_presupuesto'])) {
            echo json_encode(['error' => 'Faltan datos requeridos: id_usuario, id_proveedor o id_presupuesto']);
            return;
        }

        $params = [
            'fecha_orden' => !empty($json_datos['fecha_orden']) ? $json_datos['fecha_orden'] : date('Y-m-d'),
            'estado' => $json_datos['estado'] ?? 'ACTIVO',
            'condiciones_pago' => $json_datos['condiciones_pago'] ?? '',
            'id_usuario' => $json_datos['id_usuario'],
            'id_proveedor' => $json_datos['id_proveedor_orden'],
            'id_presupuesto' => $json_datos['id_presupuesto']
        ];

        if (!$query->execute($params)) {
            echo json_encode(['error' => 'No se pudo insertar la orden']);
            return;
        }

        $id_orden = $conexion->lastInsertId();

        if (!$id_orden) {
            echo json_encode(['error' => 'No se generó ID para la orden']);
            return;
        }

        echo json_encode(['success' => 'Orden guardada correctamente', 'id_orden' => $id_orden]);

    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error PDO: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
}

function anular($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "UPDATE orden_compra SET estado = 'ANULADO' WHERE id_orden_compra = :id;"
    );
    $query->execute(['id' => $id]);
}

function obtener_por_id($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT oc.id_orden_compra as orden_compra, oc.fecha_orden, oc.estado, oc.condiciones_pago, oc.id_usuario, u.nombre_usuario,
               oc.id_proveedor, prov.nombre AS proveedor_nombre,
               pr.id_presupuesto AS presupuesto_id, pr.fecha_presupuesto
           FROM orden_compra oc
           LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
           LEFT JOIN presupuesto pr ON oc.id_presupuesto = pr.id_presupuesto
           LEFT JOIN proveedor prov ON oc.id_proveedor = prov.id_proveedor
          WHERE oc.id_orden_compra = :id
          LIMIT 1;"
    );
    $query->execute(['id' => $id]);
    if ($query->rowCount()) {
        echo json_encode($query->fetch(PDO::FETCH_OBJ));
    } else {
        echo '0';
    }
}

function buscar($texto) {
    $base_datos = new DB();
     $query = $base_datos->conectar()->prepare(
          "SELECT oc.id_orden_compra as orden_compra, oc.fecha_orden, oc.estado, oc.condiciones_pago, oc.id_usuario, u.nombre_usuario,
                    prov.nombre AS proveedor_nombre, pr.id_presupuesto AS presupuesto_id
              FROM orden_compra oc
              LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
              LEFT JOIN presupuesto pr ON oc.id_presupuesto = pr.id_presupuesto
              LEFT JOIN proveedor prov ON oc.id_proveedor = prov.id_proveedor
             WHERE CONCAT(oc.id_orden_compra, ' ', oc.fecha_orden, ' ', oc.estado, ' ', u.nombre_usuario) LIKE :texto
        ORDER BY oc.id_orden_compra DESC
            LIMIT 50;"
     );
    $query->execute(['texto' => "%$texto%"]);
    if ($query->rowCount()) {
        echo json_encode($query->fetchAll(PDO::FETCH_OBJ));
    } else {
        echo '0';
    }
}

function obtener_detalles($id_orden) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
           "SELECT do.id_detalle_orden, do.cantidad, do.id_orden_compra AS orden_compra, do.id_producto AS id_productos,
               p.nombre_producto, p.precio, p.costo, p.iva
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

?>
