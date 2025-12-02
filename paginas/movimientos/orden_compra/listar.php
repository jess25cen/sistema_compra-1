<div class="container-fluid card" style="padding: 30px;">
    <div class="row g-3">
        <div class="col-md-12">
            <h3>Órdenes de Compra</h3>
        </div>
        <div class="col-md-12">
            <hr>
        </div>
        <div class="col-md-6">
            <button class="btn btn-primary" onclick="mostrarAgregarOrdenCompra(); return false;">Agregar Orden</button>
        </div>
        <div class="col-md-6 text-end">
            <input type="text" id="b_orden_compra" class="form-control" placeholder="Buscar...">
        </div>
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Proveedor</th>
                            <th>Presupuesto</th>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th class="text-end">Operaciones</th>
                        </tr>
                    </thead>
                    <tbody id="ordenes_compra_tb"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>