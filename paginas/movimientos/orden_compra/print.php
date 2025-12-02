<?php
require_once '../../../conexion/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;
$db = new DB();
$detalle = $db->conectar()->prepare(
    "SELECT do.id_detalle_orden, do.cantidad, p.nombre_producto, p.precio
     FROM detalle_orden do
     LEFT JOIN productos p ON do.id_productos = p.id_productos
     WHERE do.id_orden_compra = :id"
);
$detalle->execute(['id' => $id]);
// Obtener cabecera de la orden (proveedor, fecha, condiciones)
$cab = $db->conectar()->prepare(
    "SELECT oc.id_orden_compra, oc.fecha_orden, oc.condiciones_pago, u.nombre_usuario, prov.nombre AS proveedor_nombre, pr.id_presupuesto
     FROM orden_compra oc
     LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
     LEFT JOIN proveedor prov ON oc.id_proveedor = prov.id_proveedor
     LEFT JOIN presupuesto pr ON oc.id_presupuesto = pr.id_presupuesto
     WHERE oc.id_orden_compra = :id LIMIT 1"
);
$cab->execute(['id' => $id]);
$cabecera = $cab->fetch(PDO::FETCH_ASSOC);

?>
<html>
<head>
    <meta charset="utf-8" />
    <title>Orden #<?= htmlspecialchars($id) ?></title>
    <link rel="stylesheet" href="/assets/vendor/bootstrap/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h3>Orden de Compra #<?= htmlspecialchars($id) ?></h3>
    <?php if (!empty($cabecera)): ?>
        <p><strong>Proveedor:</strong> <?= htmlspecialchars($cabecera['proveedor_nombre'] ?? '') ?></p>
        <p><strong>Presupuesto:</strong> <?= htmlspecialchars($cabecera['id_presupuesto'] ?? '') ?></p>
        <p><strong>Fecha:</strong> <?= htmlspecialchars($cabecera['fecha_orden'] ?? '') ?></p>
        <p><strong>Condiciones:</strong> <?= htmlspecialchars($cabecera['condiciones_pago'] ?? '') ?></p>
    <?php endif; ?>
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Precio Unitario</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            while ($fila = $detalle->fetch(PDO::FETCH_ASSOC)) {
                $subtotal = (is_numeric($fila['cantidad']) && is_numeric($fila['precio'])) ? ($fila['cantidad'] * $fila['precio']) : 0;
                $total += $subtotal;
                ?>
                <tr>
                    <td><?= htmlspecialchars($fila['nombre_producto'] ?? 'N/A') ?></td>
                    <td class="text-center"><?= number_format($fila['cantidad'], 2, ',', '.') ?></td>
                    <td class="text-right">$<?= number_format($fila['precio'], 2, ',', '.') ?></td>
                    <td class="text-right">$<?= number_format($subtotal, 2, ',', '.') ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-end"><strong>Total</strong></td>
                <td class="text-right">$<?= number_format($total, 2, ',', '.') ?></td>
            </tr>
        </tfoot>
    </table>
</div>
</body>
</html>