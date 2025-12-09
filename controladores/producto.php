<?php
require_once '../conexion/db.php';

if (isset($_POST['listar'])) {
    listar();
}

if (isset($_POST['guardar'])) {
    guardar($_POST['guardar']);
}

if (isset($_POST['actualizar'])) {
    actualizar($_POST['actualizar']);
}

if (isset($_POST['id'])) {
    obtener_por_id($_POST['id']);
}

if (isset($_POST['eliminar'])) {
    eliminar($_POST['eliminar']);
}

if (isset($_POST['buscar'])) {
    buscar($_POST['buscar']);
}

if (isset($_POST['leer_activos'])) {
    leer_activos();
}

function listar() {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_productos, nombre_producto, costo, precio, estado, id_categoria, id_tipo_producto, iva
           FROM productos
       ORDER BY id_productos DESC;"
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
    $query = $base_datos->conectar()->prepare(
        "INSERT INTO productos (nombre_producto, costo, precio, estado, id_categoria, id_tipo_producto, iva)
         VALUES (:nombre_producto, :costo, :precio, :estado, :id_categoria, :id_tipo_producto, :iva);"
    );
    $params = [
        'nombre_producto' => $json_datos['nombre_producto'],
        'costo' => !empty($json_datos['costo']) ? $json_datos['costo'] : 0,
        'precio' => !empty($json_datos['precio']) ? $json_datos['precio'] : 0,
        'estado' => $json_datos['estado'],
        'id_categoria' => !empty($json_datos['id_categoria']) ? $json_datos['id_categoria'] : 0,
        'id_tipo_producto' => !empty($json_datos['id_tipo_producto']) ? $json_datos['id_tipo_producto'] : 0,
        'iva' => !empty($json_datos['iva']) ? $json_datos['iva'] : 0,
    ];
    $query->execute($params);
}

function actualizar($lista) {
    $json_datos = json_decode($lista, true);
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "UPDATE productos
            SET nombre_producto = :nombre_producto,
                costo = :costo,
                precio = :precio,
                estado = :estado,
                id_categoria = :id_categoria,
                id_tipo_producto = :id_tipo_producto,
                iva = :iva
          WHERE id_productos = :id_productos;"
    );
    $params = [
        'id_productos' => $json_datos['id_productos'],
        'nombre_producto' => $json_datos['nombre_producto'],
        'costo' => !empty($json_datos['costo']) ? $json_datos['costo'] : 0,
        'precio' => !empty($json_datos['precio']) ? $json_datos['precio'] : 0,
        'estado' => $json_datos['estado'],
        'id_categoria' => !empty($json_datos['id_categoria']) ? $json_datos['id_categoria'] : 0,
        'id_tipo_producto' => !empty($json_datos['id_tipo_producto']) ? $json_datos['id_tipo_producto'] : 0,
        'iva' => !empty($json_datos['iva']) ? $json_datos['iva'] : 0,
    ];
    $query->execute($params);
}

function obtener_por_id($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_productos, nombre_producto, costo, precio, estado, id_categoria, id_tipo_producto, iva
           FROM productos
          WHERE id_productos = :id
          LIMIT 1;"
    );
    $query->execute(['id' => $id]);
    if ($query->rowCount()) {
        print_r(json_encode($query->fetch(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}

function eliminar($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "UPDATE productos SET estado = 'INACTIVO' WHERE id_productos = :id;"
    );
    $query->execute(['id' => $id]);
}

function buscar($texto) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_productos, nombre_producto, costo, precio, estado, id_categoria, id_tipo_producto, iva
           FROM productos
          WHERE CONCAT(nombre_producto, ' ', id_categoria, ' ', id_tipo_producto, ' ', estado, ' ', id_productos) LIKE :texto
       ORDER BY id_productos DESC
          LIMIT 50;"
    );
    $query->execute(['texto' => "%$texto%"]);
    if ($query->rowCount()) {
        print_r(json_encode($query->fetchAll(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}

function leer_activos() {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_productos, nombre_producto
           FROM productos
          WHERE estado = 'ACTIVO'
       ORDER BY nombre_producto;"
    );
    $query->execute();
    if ($query->rowCount()) {
        print_r(json_encode($query->fetchAll(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}
?>
