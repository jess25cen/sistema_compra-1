<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$ses_id = $_SESSION['id_usuario'] ?? 1;
?>

<div class="container-fluid card" style="padding: 30px;">
    <div class="row">
        <div class="col-md-12"><h3>Agregar Nota de Crédito Compra</h3></div>
        <div class="col-md-12"><hr></div>

        <div class="col-md-3">
            <label>Número Nota</label>
            <input type="text" id="nc_numero" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Fecha</label>
            <input type="date" id="nc_fecha" class="form-control">
        </div>
        <div class="col-md-3">
            <label>Factura Compra (Relacionada)</label>
            <select id="nc_factura_compra" class="form-control">
                <option value="">-- Seleccionar --</option>
            </select>
        </div>
        <div class="col-md-3">
            <label>Proveedor</label>
            <select id="nc_proveedor" class="form-control">
                <option value="">-- Seleccionar --</option>
            </select>
        </div>

        <div class="col-md-6 mt-3">
            <label>Motivo</label>
            <select id="nc_motivo" class="form-control">
                <option value="">-- Seleccionar --</option>
                <option value="DEVOLUCION">Devolución</option>
                <option value="DESCUENTO">Descuento</option>
                <option value="ERROR">Error en Factura</option>
                <option value="OTRO">Otro</option>
            </select>
        </div>
        <div class="col-md-6 mt-3">
            <label>Observaciones</label>
            <textarea id="nc_observaciones" class="form-control" rows="2"></textarea>
        </div>

        <div class="col-md-12 mt-3"><hr></div>
        <div class="col-md-12"><h5>Detalles de la Nota</h5></div>

        <div class="col-md-4">
            <label>Producto</label>
            <select id="nc_producto" class="form-control"><option value="">-- Seleccionar --</option></select>
        </div>
        <div class="col-md-2">
            <label>Cantidad</label>
            <input type="number" id="nc_cantidad" class="form-control" value="1" min="1" step="0.01">
        </div>
        <div class="col-md-2">
            <label>Precio Unitario</label>
            <input type="number" id="nc_precio" class="form-control" value="0" step="0.01">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100" onclick="agregarTablaNotaCredito(); return false;">Agregar</button>
        </div>

        <div class="col-md-12 mt-3">
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Subtotal</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="nc_detalles_tb"></tbody>
            </table>
        </div>

        <div class="col-md-12 mt-2">
            <div class="row g-2">
                <div class="col-md-3"><label>SUBTOTAL</label><input type="text" id="nc_subtotal" class="form-control" readonly></div>
                <div class="col-md-3"><label>TOTAL</label><input type="text" id="nc_total" class="form-control" readonly></div>
            </div>
        </div>

        <div class="col-md-3 mt-3">
            <button class="btn btn-success w-100" onclick="guardarNotaCredito(); return false;">Guardar</button>
        </div>
        <div class="col-md-3 mt-3">
            <button class="btn btn-danger w-100" onclick="mostrarListaNotasCredito(); return false;">Cancelar</button>
        </div>

        <input type="hidden" id="nc_id_usuario" value="<?php echo htmlspecialchars($ses_id); ?>">
    </div>
</div>
