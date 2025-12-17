<?php
require_once '../../../conexion/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">No se especificó una nota de crédito</p></div>';
    exit;
}

$db = new DB();
$c = $db->conectar();

// Validar que la nota exista
$validar = $c->prepare("SELECT id_nota_credito FROM nota_credito WHERE id_nota_credito = :id");
$validar->execute([':id' => $id]);
if (!$validar->fetch(PDO::FETCH_ASSOC)) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">La nota de crédito con ID ' . htmlspecialchars($id) . ' no existe.</p></div>';
    exit;
}

// Obtener datos de la nota
$sql_cabecera = "SELECT nc.id_nota_credito, nc.numero_nota, nc.fecha_nota, nc.motivo, nc.observaciones, nc.monto_total,
                        p.nombre AS nombre_proveedor, 
                        fc.numero_factura,
                        u.nombre_usuario
                 FROM nota_credito nc
                 LEFT JOIN proveedor p ON nc.id_proveedor = p.id_proveedor
                 LEFT JOIN factura_compra fc ON nc.id_factura_compra = fc.id_factura_compra
                 LEFT JOIN usuarios u ON nc.id_usuario = u.id_usuario
                 WHERE nc.id_nota_credito = :id";

$stmt = $c->prepare($sql_cabecera);
$stmt->execute([':id' => $id]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nota) {
    echo '<div style="padding: 20px; text-align: center;"><p style="color: red; font-size: 18px;">No se pudo obtener la información de la nota de crédito.</p></div>';
    exit;
}

// Obtener detalles
$sql_detalles = "SELECT dnc.cantidad, dnc.precio_unitario, dnc.total, prod.nombre_producto
                 FROM detalle_nota_credito dnc
                 LEFT JOIN productos prod ON dnc.id_productos = prod.id_productos
                 WHERE dnc.id_nota_credito = :id";

$stmt_det = $c->prepare($sql_detalles);
$stmt_det->execute([':id' => $id]);
$detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota de Crédito #<?php echo htmlspecialchars($id); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .button-container {
            margin-top: 20px;
        }
        button {
            margin-right: 10px;
            padding: 8px 15px;
            cursor: pointer;
        }
        @media print {
            button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>NOTA DE CRÉDITO #<?php echo htmlspecialchars($nota['numero_nota'] ?? $id); ?></h1>
    </div>

    <div class="info-row">
        <div><strong>Fecha:</strong> <?php echo htmlspecialchars($nota['fecha_nota'] ?? ''); ?></div>
        <div><strong>Proveedor:</strong> <?php echo htmlspecialchars($nota['nombre_proveedor'] ?? ''); ?></div>
    </div>

    <div class="info-row">
        <div><strong>Factura Original:</strong> <?php echo htmlspecialchars($nota['numero_factura'] ?? ''); ?></div>
        <div><strong>Usuario:</strong> <?php echo htmlspecialchars($nota['nombre_usuario'] ?? ''); ?></div>
    </div>

    <div class="info-row">
        <div><strong>Motivo:</strong> <?php echo htmlspecialchars($nota['motivo'] ?? ''); ?></div>
    </div>

    <div class="info-row">
        <div><strong>Observaciones:</strong> <?php echo htmlspecialchars($nota['observaciones'] ?? ''); ?></div>
    </div>

    <h3>Detalles</h3>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $subtotal = 0;
            foreach ($detalles as $d) {
                $subtotal += floatval($d['total'] ?? 0);
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($d['nombre_producto'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($d['cantidad']); ?></td>
                    <td>$<?php echo number_format(floatval($d['precio_unitario'] ?? 0), 2, ',', '.'); ?></td>
                    <td>$<?php echo number_format(floatval($d['total'] ?? 0), 2, ',', '.'); ?></td>
                </tr>
            <?php } ?>
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3">TOTAL</td>
                <td>$<?php echo number_format($subtotal, 2, ',', '.'); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="button-container">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </div>
</body>
</html>
