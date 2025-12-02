<div class="card">
  <div class="card-header pb-0">
    <div class="row">
      <div class="col-sm-7">
        <h5 class="card-title">Listado de Presupuestos</h5>
      </div>
      <div class="col-sm-5">
        <button class="btn btn-primary btn-sm float-end" onclick="mostrarAgregarPresupuesto()">
          <i data-feather="plus"></i> Agregar Presupuesto
        </button>
      </div>
    </div>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-6">
        <input type="text" id="b_presupuesto" class="form-control" placeholder="Buscar presupuesto...">
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Proveedor</th>
            <th>Pedido Compra</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="presupuestos_tb">
        </tbody>
      </table>
    </div>
  </div>
</div>