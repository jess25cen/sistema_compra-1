<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$ses_id = $_SESSION['id_usuario'] ?? 1;
$ses_name = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre_usuario'] ?? 'Usuario');
?>

<div class="container-fluid card" style="padding: 30px;">
    <div class="row">
        <div class="col-md-12"><h3>Agregar Factura Compra</h3></div>
        <div class="col-md-12"><hr></div>

        <div class="col-md-4">
            <label>Número Factura</label>
            <input type="text" id="factura_numero" class="form-control">
        </div>
        <div class="col-md-4">
            <label>Fecha</label>
            <input type="date" id="factura_fecha" class="form-control">
        </div>
        <div class="col-md-4">
            <label>Proveedor</label>
            <select id="factura_proveedor" class="form-control">
                <option value="0">-- Seleccionar --</option>
            </select>
        </div>

        <div class="col-md-4 mt-3">
            <label>Orden de Compra (opcional)</label>
            <select id="factura_orden" class="form-control"><option value="">-- Sin Orden --</option></select>
        </div>
        <div class="col-md-4 mt-3">
            <label>Timbrado</label>
            <input type="text" id="factura_timbrado" class="form-control">
        </div>
        <div class="col-md-4 mt-3">
            <label>Fecha Vencimiento</label>
            <input type="date" id="factura_fecha_vencimiento" class="form-control">
        </div>

        <div class="col-md-4 mt-3">
            <label>Condición de Pago</label>
            <select id="factura_condicion" class="form-control">
                <option value="">-- Seleccionar --</option>
            </select>
        </div>

        <div class="col-md-12"><hr></div>

        <div class="col-md-4">
            <label>Producto</label>
            <select id="factura_producto" class="form-control"><option value="0">-- Seleccionar --</option></select>
        </div>
        <div class="col-md-2">
            <label>Cantidad</label>
            <input type="number" id="factura_cantidad" class="form-control" value="1" min="1">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" onclick="agregarTablaFactura(); return false;">Agregar</button>
        </div>

        <div class="col-md-12 mt-3">
            <table class="table table-sm table-bordered">
                <thead><tr><th>#</th><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Acción</th></tr></thead>
                <tbody id="factura_detalles_tb"></tbody>
            </table>
        </div>

        <div class="col-md-12 mt-2">
            <div class="row g-2">
                <div class="col-md-2"><label>SUB TOTAL</label><input type="text" id="fc_subtotal" class="form-control" readonly></div>
                <div class="col-md-2"><label>IVA 5%</label><input type="text" id="fc_iva5" class="form-control" readonly></div>
                <div class="col-md-2"><label>IVA 10%</label><input type="text" id="fc_iva10" class="form-control" readonly></div>
                <div class="col-md-2"><label>EXENTA</label><input type="text" id="fc_exenta" class="form-control" readonly></div>
                <div class="col-md-2"><label>TOTAL IVA</label><input type="text" id="fc_total_iva" class="form-control" readonly></div>
                <div class="col-md-2"><label>TOTAL</label><input type="text" id="fc_total" class="form-control" readonly></div>
            </div>
        </div>

        <div class="col-md-3 mt-3">
            <button class="btn btn-success w-100" onclick="guardarFacturaCompra(); return false;">Guardar</button>
        </div>
        <div class="col-md-3 mt-3">
            <button class="btn btn-danger w-100" onclick="mostrarListaFacturaCompras(); return false;">Cancelar</button>
        </div>

        <input type="hidden" id="id_usuario_factura" value="<?php echo htmlspecialchars($ses_id); ?>">
    </div>
</div>
