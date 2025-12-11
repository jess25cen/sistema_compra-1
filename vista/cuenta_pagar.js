function mostrarListaCuentasPagar() {
    let contenido = dameContenido("paginas/movimientos/cuenta_pagar/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaCuentasPagar();
}

function cargarTablaCuentasPagar() {
    let datos = ejecutarAjax('controladores/cuenta_pagar.php', 'listar=1');
    let fila = '';
    try {
        let json;
        if (typeof datos === 'string') {
            if (datos.trim().length === 0) {
                console.warn('Empty response from cuenta_pagar.php?listar=1');
                json = [];
            } else {
                try {
                    json = JSON.parse(datos);
                } catch (pe) {
                    console.error('Invalid JSON from cuenta_pagar.php (listar):', datos, pe);
                    json = [];
                }
            }
        } else {
            json = datos;
        }

        if (!Array.isArray(json) || json.length === 0) {
            fila = `<tr><td colspan='7' class='text-center'>No hay registros</td></tr>`;
        } else {
            json.forEach(function(item){
                let estado_pago = parseFloat(item.saldo) <= 0 ? '<span class="badge bg-success">Pagado</span>' : '<span class="badge bg-warning">Pendiente</span>';
                fila += `<tr>`;
                fila += `<td>${item.numero_factura || '-'}</td>`;
                fila += `<td>${item.proveedor_nombre || '-'}</td>`;
                fila += `<td>${item.cuota}</td>`;
                fila += `<td>$${parseFloat(item.monto_cuota).toFixed(2)}</td>`;
                fila += `<td>${item.fechavencimiento || '-'}</td>`;
                fila += `<td>$${parseFloat(item.saldo).toFixed(2)}</td>`;
                fila += `<td>${estado_pago}</td>`;
                fila += `</tr>`;
            });
        }
    } catch(e) {
        console.error(e);
        fila = `<tr><td colspan='7' class='text-center text-danger'>Error al cargar registros</td></tr>`;
    }

    $('#cuenta_pagar_tb').html(fila);
    feather.replace();
}
