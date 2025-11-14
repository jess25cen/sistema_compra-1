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
        "SELECT id_usuario, nombre_usuario, nickname, id_rol, intentos, limite_intentos, estado
           FROM usuarios
       ORDER BY id_usuario DESC;"
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
    $password_hash = md5($json_datos['password']);
    $query = $base_datos->conectar()->prepare(
        "INSERT INTO usuarios (nombre_usuario, nickname, password, id_rol, intentos, limite_intentos, estado)
         VALUES (:nombre_usuario, :nickname, :password, :id_rol, :intentos, :limite_intentos, :estado);"
    );
    $params = [
        'nombre_usuario' => $json_datos['nombre_usuario'],
        'nickname' => $json_datos['nickname'],
        'password' => $password_hash,
        'id_rol' => !empty($json_datos['id_rol']) ? $json_datos['id_rol'] : 2,
        'intentos' => 0,
        'limite_intentos' => !empty($json_datos['limite_intentos']) ? $json_datos['limite_intentos'] : 3,
        'estado' => $json_datos['estado'],
    ];
    $query->execute($params);
}

function actualizar($lista) {
    $json_datos = json_decode($lista, true);
    $base_datos = new DB();
    
    $update_password = '';
    $params = [
        'id_usuario' => $json_datos['id_usuario'],
        'nombre_usuario' => $json_datos['nombre_usuario'],
        'nickname' => $json_datos['nickname'],
        'id_rol' => !empty($json_datos['id_rol']) ? $json_datos['id_rol'] : 2,
        'limite_intentos' => !empty($json_datos['limite_intentos']) ? $json_datos['limite_intentos'] : 3,
        'estado' => $json_datos['estado'],
    ];
    
    if (!empty($json_datos['password']) && $json_datos['password'] !== '') {
        $update_password = ", password = :password";
        $params['password'] = md5($json_datos['password']);
    }
    
    $query = $base_datos->conectar()->prepare(
        "UPDATE usuarios
            SET nombre_usuario = :nombre_usuario,
                nickname = :nickname,
                id_rol = :id_rol,
                limite_intentos = :limite_intentos,
                estado = :estado
                {$update_password}
          WHERE id_usuario = :id_usuario;"
    );
    $query->execute($params);
}

function obtener_por_id($id) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_usuario, nombre_usuario, nickname, id_rol, intentos, limite_intentos, estado
           FROM usuarios
          WHERE id_usuario = :id
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
        "UPDATE usuarios SET estado = 'INACTIVO' WHERE id_usuario = :id;"
    );
    $query->execute(['id' => $id]);
}

function buscar($texto) {
    $base_datos = new DB();
    $query = $base_datos->conectar()->prepare(
        "SELECT id_usuario, nombre_usuario, nickname, id_rol, intentos, limite_intentos, estado
           FROM usuarios
          WHERE CONCAT(nombre_usuario, ' ', nickname, ' ', id_rol, ' ', estado, ' ', id_usuario) LIKE :texto
       ORDER BY id_usuario DESC
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
        "SELECT id_usuario, nombre_usuario
           FROM usuarios
          WHERE estado = 'ACTIVO'
       ORDER BY nombre_usuario;"
    );
    $query->execute();
    if ($query->rowCount()) {
        print_r(json_encode($query->fetchAll(PDO::FETCH_OBJ)));
    } else {
        echo '0';
    }
}
?>
