<?php
// Obtener usuario de la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$ses_id = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
$ses_name = isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : (isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Usuario');
?>

<div class="container-fluid card">
  <div class="card-header">
    <h5 class="card-title">Agregar Presupuesto Compra</h5>
  </div>
  <div class="card-body">
    <form id="form_presupuesto">
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Usuario <span class="text-danger">*</span></label>
          <input type="text" id="presupuesto_usuario" class="form-control" readonly disabled value="<?php echo htmlspecialchars($ses_name); ?>">
          <input type="hidden" id="id_usuario_presupuesto" value="<?php echo $ses_id; ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Fecha <span class="text-danger">*</span></label>
          <input type="date" id="presupuesto_fecha" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Proveedor <span class="text-danger">*</span></label>
          <select id="presupuesto_proveedor" class="form-select" required>
            <option value="0">-- Seleccionar --</option>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Pedido de Compra</label>
          <select id="presupuesto_pedido_compra" class="form-select" onchange="cargarDetallesPedidoCompra()">
            <option value="0">-- Seleccionar --</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Producto <span class="text-danger">*</span></label>
          <select id="presupuesto_producto" class="form-select" required>
            <option value="0">-- Seleccionar --</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Cantidad <span class="text-danger">*</span></label>
          <input type="number" id="presupuesto_cantidad" class="form-control" value="1" min="1" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">&nbsp;</label>
          <button type="button" class="btn btn-primary w-100" onclick="agregarTablaPresupuestoCompra()">
            Agregar
          </button>
        </div>
      </div>

      <hr>

      <div class="table-responsive">
        <table class="table table-sm table-striped">
          <thead>
            <tr>
              <th style="width: 50px;">#</th>
              <th>Producto</th>
              <th style="width: 100px;">Cantidad</th>
              <th style="width: 80px;">Acción</th>
            </tr>
          </thead>
          <tbody id="detalles_presupuesto_tb">
            <!-- Los detalles se cargan aquí dinámicamente -->
          </tbody>
        </table>
      </div>

      <hr>

      <div class="row">
        <div class="col-md-12">
          <button type="button" class="btn btn-primary" onclick="guardarPresupuestoCompra()">
            <i data-feather="save"></i> Guardar
          </button>
          <button type="button" class="btn btn-secondary" onclick="cancelarPresupuestoCompra()">
            <i data-feather="x"></i> Cancelar
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Establecer fecha de hoy
  let hoy = new Date().toISOString().split('T')[0];
  document.getElementById('presupuesto_fecha').value = hoy;
});
</script>