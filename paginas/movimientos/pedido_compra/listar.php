<div class="container-fluid card" style="padding: 30px;">
    <div class="row">
        <div class="col-md-8">
            <h3>Pedidos de Compra</h3>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" onclick="mostrarAgregarPedidoCompra(); return false;"><i class="fa fa-plus"></i> Agregar</button>
            <button class="btn btn-warning" onclick="imprimirPedidoCompra(); return false;"><i class="fa fa-print"></i> Imprimir</button>
        </div>
        <div class="col-md-12">
            <hr>
        </div>
        <div class="col-md-12">
            <label for="b_pedido_compra">Búsqueda</label>
            <input type="text" class="form-control" id="b_pedido_compra" placeholder="Ingrese número de pedido, fecha o usuario">
        </div>
        <div class="col-md-12" style="margin-top: 30px;">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#Pedido</th>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th class="text-end">Operaciones</th>
                        </tr>
                    </thead>
                    <tbody id="pedidos_compra_tb"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
