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
        "SELECT id_proveedor, nombre, apellido, razon_social, telefono, ruc, email, direccion, id_ciudad, estado
           FROM proveedor
       ORDER BY nombre, apellido ASC;"
    );
    $query->execute();
    if ($query->rowCount()) {
        echo json_encode($query->fetchAll(PDO::FETCH_OBJ));
    } else {
        // Devolver array vacío válido
        echo json_encode(array());
    }
    exit;
}

function guardar($lista) {
    $json_datos = json_decode($lista, true);
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "INSERT INTO proveedor (nombre, apellido, razon_social, telefono, ruc, email, direccion, id_ciudad, estado)
         VALUES (:nombre, :apellido, :razon_social, :telefono, :ruc, :email, :direccion, :id_ciudad, :estado);"
    );
    $params = [
        'nombre' => !empty($json_datos['nombre']) ? $json_datos['nombre'] : null,
        'apellido' => !empty($json_datos['apellido']) ? $json_datos['apellido'] : null,
        'razon_social' => !empty($json_datos['razon_social']) ? $json_datos['razon_social'] : null,
        'telefono' => !empty($json_datos['telefono']) ? $json_datos['telefono'] : null,
        'ruc' => !empty($json_datos['ruc']) ? $json_datos['ruc'] : null,
        'email' => !empty($json_datos['email']) ? $json_datos['email'] : null,
        'direccion' => !empty($json_datos['direccion']) ? $json_datos['direccion'] : null,
        'id_ciudad' => !empty($json_datos['id_ciudad']) ? $json_datos['id_ciudad'] : 0,
        'estado' => $json_datos['estado'],
    ];
    $query->execute($params);
}

function actualizar($lista) {
    $json_datos = json_decode($lista, true);
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "UPDATE proveedor
            SET nombre = :nombre,
                apellido = :apellido,
                razon_social = :razon_social,
                telefono = :telefono,
                ruc = :ruc,
                email = :email,
                direccion = :direccion,
                id_ciudad = :id_ciudad,
                estado = :estado
          WHERE id_proveedor = :id_proveedor;"
    );
    $params = [
        'id_proveedor' => $json_datos['id_proveedor'],
        'nombre' => !empty($json_datos['nombre']) ? $json_datos['nombre'] : null,
        'apellido' => !empty($json_datos['apellido']) ? $json_datos['apellido'] : null,
        'razon_social' => !empty($json_datos['razon_social']) ? $json_datos['razon_social'] : null,
        'telefono' => !empty($json_datos['telefono']) ? $json_datos['telefono'] : null,
        'ruc' => !empty($json_datos['ruc']) ? $json_datos['ruc'] : null,
        'email' => !empty($json_datos['email']) ? $json_datos['email'] : null,
        'direccion' => !empty($json_datos['direccion']) ? $json_datos['direccion'] : null,
        'id_ciudad' => !empty($json_datos['id_ciudad']) ? $json_datos['id_ciudad'] : 0,
        'estado' => $json_datos['estado'],
    ];
    $query->execute($params);
}

function obtener_por_id($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_proveedor, nombre, apellido, razon_social, telefono, ruc, email, direccion, id_ciudad, estado
           FROM proveedor
          WHERE id_proveedor = :id
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
        "UPDATE proveedor SET estado = 'INACTIVO' WHERE id_proveedor = :id;"
    );
    $query->execute(['id' => $id]);
}

function buscar($texto) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_proveedor, nombre, apellido, razon_social, telefono, ruc, email, direccion, id_ciudad, estado
           FROM proveedor
          WHERE CONCAT(COALESCE(nombre,''),' ',COALESCE(apellido,''),' ',COALESCE(razon_social,''),' ',COALESCE(telefono,''),' ',COALESCE(ruc,''),' ',COALESCE(email,''),' ',estado,' ',id_proveedor) LIKE :texto
       ORDER BY id_proveedor DESC
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
        "SELECT id_proveedor, CONCAT(COALESCE(nombre,''),' ',COALESCE(apellido,''), ' ', COALESCE(razon_social,'')) AS nombre_completo
           FROM proveedor
          WHERE estado = 'ACTIVO'
       ORDER BY nombre_completo;"
    );
    $query->execute();
    if ($query->rowCount()) {
        print_r(json_encode($query->fetchAll(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}
?>
