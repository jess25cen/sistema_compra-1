<div class="container-fluid card" style="padding: 30px;">
    <div class="row g-3">
        <div class="col-md-12">
            <h3 id="pedido_compra_form_titulo">Nuevo Pedido de Compra</h3>
        </div>
        <div class="col-md-12">
            <hr>
        </div>
        <input type="hidden" id="id_pedido_compra" value="0">
        <div class="col-md-6">
            <label for="pedido_compra_fecha" class="form-label">Fecha *</label>
            <input type="date" class="form-control" id="pedido_compra_fecha">
        </div>
        <div class="col-md-6">
            <label for="pedido_compra_usuario" class="form-label">Usuario *</label>
            <select id="pedido_compra_usuario" class="form-select">
                <option value="0">Selecciona un Usuario</option>
            </select>
        </div>
        <div class="col-md-12">
            <hr>
            <h5>Detalles del Pedido</h5>
        </div>
        <div class="col-md-6">
            <label for="pedido_compra_producto" class="form-label">Producto</label>
            <select id="pedido_compra_producto" class="form-select">
                <option value="0">Selecciona un Producto</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="pedido_compra_cantidad" class="form-label">Cantidad</label>
            <input type="number" class="form-control" id="pedido_compra_cantidad" placeholder="Cantidad" value="1" min="1">
        </div>
        <div class="col-md-3 align-self-end">
            <button class="btn btn-success w-100" onclick="agregarDetalle(); return false;">Agregar Producto</button>
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th class="text-end">Operaciones</th>
                        </tr>
                    </thead>
                    <tbody id="detalles_pedido_tb"></tbody>
                </table>
            </div>
        </div>
        <div class="col-md-12 text-end">
            <button class="btn btn-secondary" onclick="cancelarPedidoCompra(); return false;">Cancelar</button>
            <button class="btn btn-primary" onclick="guardarPedidoCompra(); return false;">Guardar</button>
        </div>
    </div>
</div>

<script>
// Variable global para almacenar detalles temporales
let detalles_temporales = [];

$(document).ready(function() {
    // Establecer fecha actual
    let hoy = new Date();
    let fecha = hoy.toISOString().split('T')[0];
    $("#pedido_compra_fecha").val(fecha);
    
    // Cargar usuarios
    cargarListaUsuariosActivos('#pedido_compra_usuario');
    
    // Cargar productos
    cargarListaProductosActivos('#pedido_compra_producto');
});
</script>
