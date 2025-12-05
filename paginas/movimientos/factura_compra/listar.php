<div class="card">
  <div class="card-header pb-0">
    <h5 class="card-title">Listado de Facturas de Compra</h5>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-9">
        <input type="text" class="form-control" id="buscar_factura_compra" placeholder="Buscar por número o proveedor..." onkeyup="cargarTablaFacturaCompras()">
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-primary w-100" onclick="mostrarAgregarFacturaCompra()">
          <i data-feather="plus"></i> Nueva Factura
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Número</th>
            <th>Fecha</th>
            <th>Proveedor</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="factura_compra_tb">
        </tbody>
      </table>
    </div>
  </div>
</div>
