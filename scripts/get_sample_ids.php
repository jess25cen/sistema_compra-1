<?php
require_once __DIR__ . '/../conexion/db.php';
$db = new DB();
$pdo = $db->conectar();
$sample = [];
$stmt = $pdo->query("SELECT id_productos FROM productos LIMIT 1");
$prod = $stmt->fetch(PDO::FETCH_ASSOC);
$sample['id_productos'] = $prod['id_productos'] ?? null;
$stmt2 = $pdo->query("SELECT id_orden_compra FROM orden_compra LIMIT 1");
$ord = $stmt2->fetch(PDO::FETCH_ASSOC);
$sample['id_orden_compra'] = $ord['id_orden_compra'] ?? null;
echo json_encode($sample);
