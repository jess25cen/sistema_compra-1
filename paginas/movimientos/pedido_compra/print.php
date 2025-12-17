<?php
require_once("../../../conexion/db.php");

$db = new DB();
$conexion = $db->conectar();
$id_pedido = isset($_GET['id']) ? $_GET['id'] : 0;

$sql = "SELECT 
        pc.pedido_compra, 
        pc.fecha_compra, 
        pc.estado,
        u.nombre_usuario
    FROM pedido_compra pc
    JOIN usuarios u ON pc.id_usuario = u.id_usuario
    WHERE pc.pedido_compra = :id";

$stmt = $conexion->prepare($sql);
$stmt->execute([':id' => $id_pedido]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el pedido existe
if (!$pedido) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">El pedido con ID ' . htmlspecialchars($id_pedido) . ' no existe.</p></div>';
    exit;
}

$sql_detalles = "SELECT dp.*, p.nombre_producto, p.precio 
                FROM detalle_pedido dp 
                LEFT JOIN productos p ON dp.id_productos = p.id_productos 
                WHERE dp.pedido_compra = :id";

$stmt_detalles = $conexion->prepare($sql_detalles);
$stmt_detalles->execute([':id' => $id_pedido]);
$detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pedido Compra #<?php echo $id_pedido; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { margin-bottom: 30px; }
        .info-row { display: flex; justify-content: space-between; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { text-align: right; font-weight: bold; margin-top: 20px; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PEDIDO DE COMPRA #<?php echo $pedido['pedido_compra']; ?></h1>
    </div>

    <div class="info-row">
        <div><strong>Fecha:</strong> <?php echo $pedido['fecha_compra']; ?></div>
        <div><strong>Estado:</strong> <?php echo $pedido['estado']; ?></div>
    </div>

    <div class="info-row">
        <div><strong>Usuario:</strong> <?php echo $pedido['nombre_usuario']; ?></div>
    </div>

    <h3>Detalles</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?php echo $detalle['nombre_producto']; ?></td>
                    <td><?php echo $detalle['cantidad']; ?></td>
                    <td><?php echo $detalle['precio']; ?></td>
                    <td><?php echo $detalle['cantidad'] * $detalle['precio']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <button onclick="window.print()">Imprimir</button>
    <button onclick="window.close()">Cerrar</button>
</body>
</html>
