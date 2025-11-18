<?php
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
        "SELECT pc.pedido_compra, pc.fecha_compra, pc.estado, pc.id_usuario, u.nombre_usuario
           FROM pedido_compra pc
           LEFT JOIN usuarios u ON pc.id_usuario = u.id_usuario
       ORDER BY pc.pedido_compra DESC;"
    );
    $query->execute();
    if ($query->rowCount()) {
        print_r(json_encode($query->fetchAll(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}

function guardar($lista) {
    $json_datos = json_decode($lista, true);
    $base_datos = new DB();
    
    // Insertar pedido_compra
    $query = $base_datos->conectar()->prepare(
        "INSERT INTO pedido_compra (fecha_compra, estado, id_usuario)
         VALUES (:fecha_compra, :estado, :id_usuario);"
    );
    $params = [
        'fecha_compra' => !empty($json_datos['fecha_compra']) ? $json_datos['fecha_compra'] : date('Y-m-d'),
        'estado' => 'ACTIVO',
        'id_usuario' => !empty($json_datos['id_usuario']) ? $json_datos['id_usuario'] : 1,
    ];
    $query->execute($params);
    
    // Obtener el ID del pedido insertado
    $id_pedido = $base_datos->conectar()->lastInsertId();
    
    // Insertar detalles
    if (!empty($json_datos['detalles']) && is_array($json_datos['detalles'])) {
        foreach ($json_datos['detalles'] as $detalle) {
            $query_detalle = $base_datos->conectar()->prepare(
                "INSERT INTO detalle_pedido (cantidad, pedido_compra, id_productos)
                 VALUES (:cantidad, :pedido_compra, :id_productos);"
            );
            $query_detalle->execute([
                'cantidad' => $detalle['cantidad'],
                'pedido_compra' => $id_pedido,
                'id_productos' => $detalle['id_productos'],
            ]);
        }
    }
}

function anular($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "UPDATE pedido_compra SET estado = 'ANULADO' WHERE pedido_compra = :id;"
    );
    $query->execute(['id' => $id]);
}

function obtener_por_id($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT pc.pedido_compra, pc.fecha_compra, pc.estado, pc.id_usuario, u.nombre_usuario
           FROM pedido_compra pc
           LEFT JOIN usuarios u ON pc.id_usuario = u.id_usuario
          WHERE pc.pedido_compra = :id
          LIMIT 1;"
    );
    $query->execute(['id' => $id]);
    if ($query->rowCount()) {
        print_r(json_encode($query->fetch(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}

function buscar($texto) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT pc.pedido_compra, pc.fecha_compra, pc.estado, pc.id_usuario, u.nombre_usuario
           FROM pedido_compra pc
           LEFT JOIN usuarios u ON pc.id_usuario = u.id_usuario
          WHERE CONCAT(pc.pedido_compra, ' ', pc.fecha_compra, ' ', pc.estado, ' ', u.nombre_usuario) LIKE :texto
       ORDER BY pc.pedido_compra DESC
          LIMIT 50;"
    );
    $query->execute(['texto' => "%$texto%"]);
    if ($query->rowCount()) {
        print_r(json_encode($query->fetchAll(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}

function obtener_detalles($id_pedido) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT dp.id_detalle_pedido, dp.cantidad, dp.id_productos, pr.nombre_producto, pr.precio
           FROM detalle_pedido dp
           LEFT JOIN productos pr ON dp.id_productos = pr.id_productos
          WHERE dp.pedido_compra = :id_pedido;"
    );
    $query->execute(['id_pedido' => $id_pedido]);
    if ($query->rowCount()) {
        print_r(json_encode($query->fetchAll(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}
?>
