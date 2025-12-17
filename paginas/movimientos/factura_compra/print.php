<?php
require_once('../../../conexion/db.php');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">No se especificó una factura de compra</p></div>';
    exit;
}

$db = new DB();
$c = $db->conectar();

// Validar que la factura exista
$validar = $c->prepare("SELECT id_factura_compra FROM factura_compra WHERE id_factura_compra = :id");
$validar->execute([':id' => $id]);
if (!$validar->fetch(PDO::FETCH_ASSOC)) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">La factura de compra con ID ' . htmlspecialchars($id) . ' no existe.</p></div>';
    exit;
}

// Obtener datos de la factura
$stmt = $c->prepare("SELECT f.id_factura_compra, f.numero_factura, f.fecha_factura, f.timbrado, prov.nombre AS proveedor_nombre, u.nombre_usuario FROM factura_compra f LEFT JOIN proveedor prov ON f.id_proveedor = prov.id_proveedor LEFT JOIN usuarios u ON f.id_usuario = u.id_usuario WHERE f.id_factura_compra = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$factura = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$factura) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">No se pudo obtener la información de la factura.</p></div>';
    exit;
}

// Obtener detalles
$det = $c->prepare("SELECT df.cantidad, df.total, p.nombre_producto FROM detalle_factura df LEFT JOIN productos p ON df.id_productos = p.id_productos WHERE df.id_factura_compra = :id");
$det->execute([':id' => $id]);
$detalles = $det->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura #<?php echo htmlspecialchars($id); ?></title>
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
        <h1>FACTURA DE COMPRA #<?php echo htmlspecialchars($factura['numero_factura'] ?? $id); ?></h1>
    </div>

    <div class="info-row">
        <div><strong>Fecha:</strong> <?php echo htmlspecialchars($factura['fecha_factura'] ?? ''); ?></div>
        <div><strong>Timbrado:</strong> <?php echo htmlspecialchars($factura['timbrado'] ?? ''); ?></div>
    </div>

    <div class="info-row">
        <div><strong>Proveedor:</strong> <?php echo htmlspecialchars($factura['proveedor_nombre'] ?? ''); ?></div>
        <div><strong>Usuario:</strong> <?php echo htmlspecialchars($factura['nombre_usuario'] ?? ''); ?></div>
    </div>

    <h3>Detalles</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Monto Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            foreach ($detalles as $d) {
                $total += $d['total'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['nombre_producto'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($d['cantidad']); ?></td>
                    <td>$<?php echo number_format($d['total'], 2, ',', '.'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2">TOTAL</td>
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
