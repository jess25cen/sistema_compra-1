<div class="card">
  <div class="card-header pb-0">
    <div class="row">
      <div class="col-sm-7">
        <h5 class="card-title">Listado de Notas de Crédito Compra</h5>
      </div>
      <div class="col-sm-5">
        <button class="btn btn-primary btn-sm float-end" onclick="mostrarAgregarNotaCredito()">
          <i data-feather="plus"></i> Nueva Nota
        </button>
      </div>
    </div>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-6">
        <input type="text" class="form-control" id="search_nota_credito" placeholder="Buscar por número o proveedor..." onkeyup="cargarTablaNotasCredito()">
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Número</th>
            <th>Fecha</th>
            <th>Proveedor</th>
            <th>Factura</th>
            <th>Monto</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="nota_credito_tb">
        </tbody>
      </table>
    </div>
  </div>
</div>
