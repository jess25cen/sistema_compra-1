<?php
if (!isset($_GET['id'])) { echo 'ID faltante'; exit; }
$id = intval($_GET['id']);
require_once('../../../conexion/db.php');
$db = new DB();
$c = $db->conectar();

$stmt = $c->prepare("SELECT f.*, prov.nombre AS proveedor_nombre FROM factura_compra f LEFT JOIN proveedor prov ON f.id_proveedor = prov.id_proveedor WHERE f.id_factura_compra = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$f = $stmt->fetch(PDO::FETCH_ASSOC);

$det = $c->prepare("SELECT df.*, p.nombre_producto FROM detalle_factura df LEFT JOIN productos p ON df.id_productos = p.id_productos WHERE df.id_factura_compra = :id");
$det->execute([':id' => $id]);
$detalles = $det->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="container">
    <h3>Factura Compra #<?php echo $id; ?></h3>
    <p>Proveedor: <?php echo htmlspecialchars($f['proveedor_nombre'] ?? ''); ?></p>
    <p>Fecha: <?php echo htmlspecialchars($f['fecha_factura'] ?? ''); ?></p>
    <hr>
    <table class="table table-sm">
        <thead><tr><th>Producto</th><th>Cantidad</th><th>Monto</th></tr></thead>
        <tbody>
        <?php foreach ($detalles as $d) { ?>
            <tr>
                <td><?php echo htmlspecialchars($d['nombre_producto'] ?? ''); ?></td>
                <td><?php echo htmlspecialchars($d['cantidad']); ?></td>
                <td><?php echo htmlspecialchars($d['monto_total']); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
