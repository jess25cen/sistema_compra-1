<?php
require_once '../../../conexion/db.php';

$id_nota = $_GET['id'] ?? 0;

if (empty($id_nota)) {
    die('Nota no especificada');
}

// Obtener datos de la nota
$sql_cabecera = "SELECT nc.*, p.nombre_proveedor, 
                        fc.numero_factura,
                        u.nombre_completo
                 FROM nota_credito nc
                 LEFT JOIN proveedor p ON nc.id_proveedor = p.id_proveedor
                 LEFT JOIN factura_compra fc ON nc.id_factura_compra = fc.id_factura_compra
                 LEFT JOIN usuario u ON nc.id_usuario = u.id_usuario
                 WHERE nc.id_nota_credito = ?";

$stmt = $pdo->prepare($sql_cabecera);
$stmt->execute([$id_nota]);
$nota = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nota) {
    die('Nota no encontrada');
}

// Obtener detalles
$sql_detalles = "SELECT dnc.*, prod.nombre_producto
                 FROM detalle_nota_credito dnc
                 LEFT JOIN productos prod ON dnc.id_productos = prod.id_productos
                 WHERE dnc.id_nota_credito = ?";

$stmt_det = $pdo->prepare($sql_detalles);
$stmt_det->execute([$id_nota]);
$detalles = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Nota de Crédito - <?php echo htmlspecialchars($nota['numero_nota']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        .info-label {
            font-weight: bold;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #999;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .totales {
            width: 100%;
            margin-top: 20px;
        }
        .totales-row {
            display: flex;
            justify-content: flex-end;
            margin: 5px 0;
        }
        .totales-label {
            font-weight: bold;
            width: 150px;
        }
        .totales-valor {
            width: 100px;
            text-align: right;
        }
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>NOTA DE CRÉDITO</h2>
        <div class="info-row">
            <div class="info-label">Número:</div>
            <div><?php echo htmlspecialchars($nota['numero_nota']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha:</div>
            <div><?php echo date('d/m/Y', strtotime($nota['fecha_nota'])); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Proveedor:</div>
            <div><?php echo htmlspecialchars($nota['nombre_proveedor']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Factura Original:</div>
            <div><?php echo htmlspecialchars($nota['numero_factura']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Motivo:</div>
            <div><?php echo htmlspecialchars($nota['motivo']); ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Observaciones:</div>
            <div><?php echo htmlspecialchars($nota['observaciones']); ?></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $num = 1;
            $subtotal = 0;
            foreach ($detalles as $d) {
                $subtotal += floatval($d['total']);
                echo '<tr>';
                echo '<td>'.$num.'</td>';
                echo '<td>'.htmlspecialchars($d['nombre_producto']).'</td>';
                echo '<td style="text-align: right;">'.floatval($d['cantidad']).'</td>';
                echo '<td style="text-align: right;">'.number_format(floatval($d['precio_unitario']), 2).'</td>';
                echo '<td style="text-align: right;">'.number_format(floatval($d['total']), 2).'</td>';
                echo '</tr>';
                $num++;
            }
            ?>
        </tbody>
    </table>

    <div class="totales">
        <div class="totales-row">
            <div class="totales-label">TOTAL:</div>
            <div class="totales-valor"><?php echo number_format(floatval($nota['monto_total']), 2); ?></div>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
