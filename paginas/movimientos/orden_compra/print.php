<?php
require_once '../../../conexion/db.php';

$id = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id <= 0) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">No se especificó una orden de compra</p></div>';
    exit;
}

$db = new DB();

// Validar que la orden exista
$validar = $db->conectar()->prepare(
    "SELECT id_orden_compra FROM orden_compra WHERE id_orden_compra = :id"
);
$validar->execute(['id' => $id]);
if (!$validar->fetch(PDO::FETCH_ASSOC)) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">La orden de compra con ID ' . htmlspecialchars($id) . ' no existe.</p></div>';
    exit;
}

$detalle = $db->conectar()->prepare(
    "SELECT do.id_detalle_orden, do.cantidad, p.nombre_producto, p.precio
     FROM detalle_orden do
     LEFT JOIN productos p ON do.id_producto = p.id_productos
     WHERE do.id_orden_compra = :id"
);
$detalle->execute(['id' => $id]);

// Obtener cabecera de la orden
$cab = $db->conectar()->prepare(
    "SELECT oc.id_orden_compra, oc.fecha_orden, u.nombre_usuario, oc.condiciones_pago
     FROM orden_compra oc
     LEFT JOIN usuarios u ON oc.id_usuario = u.id_usuario
     WHERE oc.id_orden_compra = :id LIMIT 1"
);
$cab->execute(['id' => $id]);
$cabecera = $cab->fetch(PDO::FETCH_ASSOC);

if (!$cabecera) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">No se pudo obtener la información de la orden de compra.</p></div>';
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Orden de Compra #<?php echo $id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { margin-bottom: 30px; }
        .info-row { display: flex; justify-content: space-between; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total-row { font-weight: bold; background-color: #f9f9f9; }
        .button-container { margin-top: 20px; }
        button { margin-right: 10px; padding: 8px 15px; cursor: pointer; }
        @media print {
            button { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ORDEN DE COMPRA #<?php echo htmlspecialchars($id); ?></h1>
    </div>

    <div class="info-row">
        <div><strong>Fecha:</strong> <?php echo htmlspecialchars($cabecera['fecha_orden'] ?? ''); ?></div>
        <div><strong>Usuario:</strong> <?php echo htmlspecialchars($cabecera['nombre_usuario'] ?? ''); ?></div>
    </div>

    <div class="info-row">
        <div><strong>Condiciones de Pago:</strong> <?php echo htmlspecialchars($cabecera['condiciones_pago'] ?? ''); ?></div>
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
            <?php
            $total = 0;
            while ($fila = $detalle->fetch(PDO::FETCH_ASSOC)) {
                $subtotal = (is_numeric($fila['cantidad']) && is_numeric($fila['precio'])) ? ($fila['cantidad'] * $fila['precio']) : 0;
                $total += $subtotal;
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nombre_producto'] ?? 'N/A'); ?></td>
                    <td><?php echo number_format($fila['cantidad'], 0, ',', '.'); ?></td>
                    <td>$<?php echo number_format($fila['precio'], 2, ',', '.'); ?></td>
                    <td>$<?php echo number_format($subtotal, 2, ',', '.'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td>$<?php echo number_format($total, 2, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="button-container">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>