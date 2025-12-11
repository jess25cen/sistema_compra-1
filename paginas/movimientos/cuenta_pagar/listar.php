<div class="card">
    <h5 class="card-title">Listado de Cuentas por Pagar</h5>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="buscar_cuenta_pagar" placeholder="Buscar por factura o proveedor..." onkeyup="cargarTablaCuentasPagar()">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Factura</th>
                        <th>Proveedor</th>
                        <th>Cuota #</th>
                        <th>Monto</th>
                        <th>Fecha Vencimiento</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="cuenta_pagar_tb">
                    <tr>
                        <td colspan="7" class="text-center">Cargando...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
