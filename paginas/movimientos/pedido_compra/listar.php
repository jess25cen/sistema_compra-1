<div class="card">
  <div class="card-header pb-0">
    <h5 class="card-title">Listado de Pedidos de Compra</h5>
  </div>
  <div class="card-body">
    <div class="row mb-3">
      <div class="col-md-9">
        <input type="text" class="form-control" id="buscar_pedido_compra" placeholder="Buscar por ID o usuario..." onkeyup="buscarPedidoCompra()">
      </div>
      <div class="col-md-3">
        <button type="button" class="btn btn-primary w-100" onclick="mostrarAgregarPedidoCompra()">
          <i data-feather="plus"></i> Nuevo Pedido
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="pedidos_compra_tb">
        </tbody>
      </table>
    </div>
  </div>
</div>
