<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
$response = array();
$response['id_usuario'] = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : (isset($_SESSION['cod_usuario']) ? $_SESSION['cod_usuario'] : null);
$response['nombre'] = isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : (isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : null);
echo json_encode($response);
?>