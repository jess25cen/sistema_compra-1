// Nota de Crédito Compra - Vista
let nc_detalle_temp = [];

function mostrarListaNotasCredito() {
    let contenido = dameContenido("paginas/movimientos/nota_credito/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaNotasCredito();
    feather.replace();
}

function mostrarAgregarNotaCredito() {
    let contenido = dameContenido("paginas/movimientos/nota_credito/agregar.php");
    $("#contenido-principal").html(contenido);
    
    // Cargar dropdowns
    cargarFacturasNotaCredito();
    cargarProductosNotaCredito();
    
    // Establecer fecha actual
    document.getElementById('nc_fecha').valueAsDate = new Date();
    
    // Evento cuando se selecciona factura
    $('#nc_factura_compra').change(function() {
        if (this.value) {
            cargarProveedorDesdeFactura(this.value);
        }
    });
    
    feather.replace();
}

function cargarProductosNotaCredito() {
    ejecutarAjax('POST', 'controladores/producto.php', { 'accion': 'listar' }, (respuesta) => {
        let options = '<option value="">-- Seleccionar --</option>';
        let data = JSON.parse(respuesta);
        data.forEach(p => {
            options += `<option value="${p.id_productos}" data-precio="${p.precio}" data-iva="${p.iva}">${p.nombre_producto}</option>`;
        });
        document.getElementById('nc_producto').innerHTML = options;
        
        $('#nc_producto').change(function() {
            if (this.value) {
                let option = $(this).find('option:selected');
                $('#nc_precio').val(parseFloat(option.data('precio')).toFixed(2));
            }
        });
    });
}

function cargarFacturasNotaCredito() {
    ejecutarAjax('POST', 'controladores/factura_compra.php', { 'accion': 'listar' }, (respuesta) => {
        let options = '<option value="">-- Seleccionar --</option>';
        let data = JSON.parse(respuesta);
        data.forEach(f => {
            options += `<option value="${f.id_factura_compra}" data-proveedor="${f.id_proveedor}">${f.numero_factura}</option>`;
        });
        document.getElementById('nc_factura_compra').innerHTML = options;
    });
}

function cargarProveedorDesdeFactura(id_factura) {
    ejecutarAjax('POST', 'controladores/factura_compra.php', 
        { 'accion': 'obtener_por_id', 'id_factura_compra': id_factura }, 
        (respuesta) => {
            let data = JSON.parse(respuesta);
            if (data && data.id_proveedor) {
                $('#nc_proveedor').val(data.id_proveedor);
            }
        }
    );
}

function agregarTablaNotaCredito() {
    let id_prod = $('#nc_producto').val();
    let cantidad = parseFloat($('#nc_cantidad').val()) || 0;
    let precio_unit = parseFloat($('#nc_precio').val()) || 0;
    
    if (!id_prod || cantidad <= 0 || precio_unit <= 0) {
        Swal.fire('Error', 'Seleccione producto y complete cantidad y precio', 'error');
        return;
    }
    
    let option = $('#nc_producto').find('option:selected');
    let nombre_prod = option.text();
    let iva = parseFloat(option.data('iva')) || 0;
    let subtotal = cantidad * precio_unit;
    let iva_monto = subtotal * (iva / 100);
    let total = subtotal + iva_monto;
    
    let existe = nc_detalle_temp.findIndex(d => d.id_productos == id_prod);
    
    if (existe >= 0) {
        nc_detalle_temp[existe].cantidad += cantidad;
        nc_detalle_temp[existe].subtotal = nc_detalle_temp[existe].cantidad * nc_detalle_temp[existe].precio_unitario;
        nc_detalle_temp[existe].iva_monto = nc_detalle_temp[existe].subtotal * (nc_detalle_temp[existe].iva / 100);
        nc_detalle_temp[existe].total = nc_detalle_temp[existe].subtotal + nc_detalle_temp[existe].iva_monto;
    } else {
        nc_detalle_temp.push({
            'id_productos': id_prod,
            'nombre_producto': nombre_prod,
            'cantidad': cantidad,
            'precio_unitario': precio_unit,
            'iva': iva,
            'subtotal': subtotal,
            'iva_monto': iva_monto,
            'total': total
        });
    }
    
    $('#nc_cantidad').val('1');
    $('#nc_producto').val('');
    $('#nc_precio').val('0');
    
    refrescarTablaNotaCredito();
}

function refrescarTablaNotaCredito() {
    let html = '';
    nc_detalle_temp.forEach((d, idx) => {
        html += `<tr>
                    <td>${idx+1}</td>
                    <td>${d.nombre_producto}</td>
                    <td>${d.cantidad}</td>
                    <td>${parseFloat(d.precio_unitario).toFixed(2)}</td>
                    <td>${parseFloat(d.total).toFixed(2)}</td>
                    <td><button class="btn btn-sm btn-danger" onclick="eliminarDetalleNotaCredito(${idx})"><i data-feather="trash-2"></i></button></td>
                </tr>`;
    });
    document.getElementById('nc_detalles_tb').innerHTML = html;
    calcularTotalesNotaCredito();
    feather.replace();
}

function eliminarDetalleNotaCredito(idx) {
    nc_detalle_temp.splice(idx, 1);
    refrescarTablaNotaCredito();
}

function calcularTotalesNotaCredito() {
    let subtotal = 0, iva5 = 0, iva10 = 0, exenta = 0, total_iva = 0, total = 0;
    
    nc_detalle_temp.forEach(d => {
        subtotal += d.subtotal;
        total_iva += d.iva_monto;
        total += d.total;
        
        if (d.iva == 5) {
            iva5 += d.iva_monto;
        } else if (d.iva == 10) {
            iva10 += d.iva_monto;
        } else if (d.iva == 0) {
            exenta += d.subtotal;
        }
    });
    
    $('#nc_subtotal').val(parseFloat(subtotal).toFixed(2));
    $('#nc_iva5').val(parseFloat(iva5).toFixed(2));
    $('#nc_iva10').val(parseFloat(iva10).toFixed(2));
    $('#nc_exenta').val(parseFloat(exenta).toFixed(2));
    $('#nc_total_iva').val(parseFloat(total_iva).toFixed(2));
    $('#nc_total').val(parseFloat(total).toFixed(2));
}

function guardarNotaCredito() {
    let numero = $('#nc_numero').val().trim();
    let fecha = $('#nc_fecha').val();
    let id_factura = $('#nc_factura_compra').val();
    let id_proveedor = $('#nc_proveedor').val();
    let motivo = $('#nc_motivo').val();
    let observaciones = $('#nc_observaciones').val().trim();
    let id_usuario = $('#nc_id_usuario').val();
    let monto_total = parseFloat($('#nc_total').val()) || 0;
    
    if (!numero || !fecha || !id_factura || !id_proveedor || nc_detalle_temp.length === 0) {
        Swal.fire('Error', 'Complete todos los campos requeridos', 'error');
        return;
    }
    
    console.log('Guardando nota:', {numero, fecha, id_factura, monto_total, detalles: nc_detalle_temp.length});
    
    ejecutarAjax('POST', 'controladores/nota_credito.php', {
        'accion': 'guardar',
        'numero_nota': numero,
        'fecha_nota': fecha,
        'id_factura_compra': id_factura,
        'id_proveedor': id_proveedor,
        'motivo': motivo,
        'observaciones': observaciones,
        'monto_total': monto_total,
        'detalles': JSON.stringify(nc_detalle_temp),
        'id_usuario': id_usuario
    }, (respuesta) => {
        console.log('Respuesta:', respuesta);
        let data = JSON.parse(respuesta);
        if (data.success) {
            Swal.fire('Éxito', 'Nota de Crédito guardada', 'success').then(() => {
                nc_detalle_temp = [];
                mostrarListaNotasCredito();
            });
        } else {
            Swal.fire('Error', data.error || 'Error al guardar', 'error');
        }
    });
}

function cargarTablaNotasCredito() {
    let buscar = $('#search_nota_credito').val() || '';
    
    ejecutarAjax('POST', 'controladores/nota_credito.php', 
        { 'accion': 'listar', 'buscar': buscar }, 
        (respuesta) => {
            let data = JSON.parse(respuesta);
            let html = '';
            
            if (!data || data.length === 0) {
                html = '<tr><td colspan="8" class="text-center">No hay registros</td></tr>';
            } else {
                data.forEach(nc => {
                    let badge = nc.estado === 'ACTIVO' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                    html += `<tr>
                                <td>${nc.id_nota_credito}</td>
                                <td>${nc.numero_nota}</td>
                                <td>${nc.fecha_nota}</td>
                                <td>${nc.nombre_proveedor || '-'}</td>
                                <td>${nc.numero_factura || '-'}</td>
                                <td>${parseFloat(nc.monto_total).toFixed(2)}</td>
                                <td>${badge}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verDetallesNotaCredito(${nc.id_nota_credito})"><i data-feather="eye"></i></button>
                                    <button class="btn btn-sm btn-danger" onclick="anularNotaCredito(${nc.id_nota_credito})"><i data-feather="x-circle"></i></button>
                                    <button class="btn btn-sm btn-warning" onclick="imprimirNotaCredito(${nc.id_nota_credito})"><i data-feather="printer"></i></button>
                                </td>
                            </tr>`;
                });
            }
            
            $('#nota_credito_tb').html(html);
            feather.replace();
        }
    );
}

function verDetallesNotaCredito(id_nota) {
    ejecutarAjax('POST', 'controladores/nota_credito.php', 
        { 'accion': 'obtener_detalles', 'id_nota_credito': id_nota }, 
        (respuesta) => {
            let data = JSON.parse(respuesta);
            
            let html = '<div style="max-height: 400px; overflow-y: auto;">';
            
            if (data.detalles && data.detalles.length > 0) {
                html += '<table class="table table-sm table-bordered"><thead><tr><th>#</th><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Total</th></tr></thead><tbody>';
                data.detalles.forEach((d, idx) => {
                    html += `<tr><td>${idx+1}</td><td>${d.nombre_producto}</td><td>${d.cantidad}</td><td>${parseFloat(d.precio_unitario).toFixed(2)}</td><td>${parseFloat(d.total).toFixed(2)}</td></tr>`;
                });
                html += '</tbody></table>';
                html += '<strong>Monto Total: ' + parseFloat(data.cabecera.monto_total).toFixed(2) + '</strong>';
            } else {
                html += '<p>No hay detalles</p>';
            }
            
            html += '</div>';
            
            Swal.fire({
                title: 'Detalles - ' + data.cabecera.numero_nota,
                html: html,
                icon: 'info'
            });
        }
    );
}

function anularNotaCredito(id_nota) {
    Swal.fire({
        title: '¿Anular Nota de Crédito?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Anular'
    }).then(result => {
        if (result.isConfirmed) {
            ejecutarAjax('POST', 'controladores/nota_credito.php', 
                { 'accion': 'actualizar', 'id_nota_credito': id_nota, 'estado': 'INACTIVO' }, 
                (respuesta) => {
                    let data = JSON.parse(respuesta);
                    if (data.success) {
                        Swal.fire('Éxito', 'Nota anulada', 'success').then(() => cargarTablaNotasCredito());
                    } else {
                        Swal.fire('Error', 'Error al anular', 'error');
                    }
                }
            );
        }
    });
}

function imprimirNotaCredito(id_nota) {
    let ventana = window.open('paginas/movimientos/nota_credito/print.php?id=' + id_nota, 'print_nc', 'width=1000,height=700');
    ventana.focus();
}
