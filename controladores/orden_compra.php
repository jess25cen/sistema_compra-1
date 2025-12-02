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
        "SELECT oc.id_orden_compra as orden_compra, oc.fecha_orden, oc.estado, oc.id_usuario, u.nombre_usuario
           FROM orden_compra oc
           LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
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
            "INSERT INTO orden_compra (fecha_orden, estado, id_usuario)
             VALUES (:fecha_orden, :estado, :id_usuario);"
        );
        $params = [
            'fecha_orden' => !empty($json_datos['fecha_orden']) ? $json_datos['fecha_orden'] : date('Y-m-d'),
            'estado' => 'ACTIVO',
            'id_usuario' => !empty($json_datos['id_usuario']) ? $json_datos['id_usuario'] : 1,
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
        "SELECT oc.id_orden_compra as orden_compra, oc.fecha_orden, oc.estado, oc.id_usuario, u.nombre_usuario
           FROM orden_compra oc
           LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
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
        "SELECT oc.id_orden_compra as orden_compra, oc.fecha_orden, oc.estado, oc.id_usuario, u.nombre_usuario
           FROM orden_compra oc
           LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
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
        "SELECT do.id_detalle_orden, do.cantidad, do.orden_compra, do.id_productos, p.nombre_producto, p.precio
         FROM detalle_orden do
         LEFT JOIN productos p ON do.id_productos = p.id_productos
         WHERE do.orden_compra = :id_orden;"
    );
    $query->execute(['id_orden' => $id_orden]);
    if ($query->rowCount()) {
        echo json_encode($query->fetchAll(PDO::FETCH_OBJ));
    } else {
        echo '0';
    }
}

?>
