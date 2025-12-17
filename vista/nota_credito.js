// Nota de Crédito Compra - Vista
let nc_detalle_temp = [];

function mostrarListaNotasCredito() {
    let contenido = dameContenido("paginas/movimientos/nota_credito/listar.php");
    $("#contenido-principal").html(contenido);
    
    // Esperar a que el DOM esté listo
    setTimeout(function() {
        cargarTablaNotasCredito();
        feather.replace();
    }, 100);
}

function mostrarAgregarNotaCredito() {
    let contenido = dameContenido("paginas/movimientos/nota_credito/agregar.php");
    $("#contenido-principal").html(contenido);
    
    // Esperar a que el DOM esté listo
    setTimeout(function() {
        // Cargar dropdowns
        cargarFacturasNotaCredito();
        cargarProductosNotaCredito();
        cargarProveedoresNotaCredito();
        
        // Establecer fecha actual
        document.getElementById('nc_fecha').valueAsDate = new Date();
        
        // Evento cuando se selecciona factura
        $('#nc_factura_compra').change(function() {
            if (this.value) {
                console.log('Factura seleccionada:', this.value);
                cargarProveedorDesdeFactura(this.value);
                cargarDetallesFacturaNC(this.value);
            } else {
                // Limpiar detalles si no hay factura seleccionada
                nc_detalle_temp = [];
                $('#nc_proveedor').val('');
                refrescarTablaNotaCredito();
            }
        });
        
        feather.replace();
    }, 100);
}

function cargarProductosNotaCredito() {
    let productos = ejecutarAjax("controladores/producto.php", "listar=1");
    
    try {
        let json_productos = typeof productos === 'string' ? JSON.parse(productos) : productos;
        
        if (!Array.isArray(json_productos)) {
            json_productos = [];
        }
        
        let options = '<option value="">-- Seleccionar --</option>';
        json_productos.forEach(p => {
            options += `<option value="${p.id_productos}" data-precio="${p.precio}" data-iva="${p.iva}">${p.nombre_producto}</option>`;
        });
        document.getElementById('nc_producto').innerHTML = options;
        
        $('#nc_producto').change(function() {
            if (this.value) {
                let option = $(this).find('option:selected');
                $('#nc_precio').val(parseFloat(option.data('precio')).toFixed(2));
            }
        });
    } catch (error) {
        console.error('Error al cargar productos:', error);
    }
}

function cargarFacturasNotaCredito() {
    let facturas = ejecutarAjax("controladores/factura_compra.php", "listar=1");
    
    try {
        let json_facturas = typeof facturas === 'string' ? JSON.parse(facturas) : facturas;
        
        if (!Array.isArray(json_facturas)) {
            json_facturas = [];
        }
        
        let options = '<option value="">-- Seleccionar --</option>';
        json_facturas.forEach(f => {
            let proveedor = f.proveedor_nombre || f.nombre || '';
            let total = parseFloat(f.total || f.monto_total || 0).toFixed(2);
            options += `<option value="${f.id_factura_compra}" data-proveedor="${f.id_proveedor}" data-total="${total}">${f.numero_factura} - ${proveedor} - Total: ${total}</option>`;
        });
        document.getElementById('nc_factura_compra').innerHTML = options;
    } catch (error) {
        console.error('Error al cargar facturas:', error);
    }
}

function cargarProveedoresNotaCredito() {
    let proveedores = ejecutarAjax("controladores/proveedor.php", "listar=1");
    
    try {
        let json_proveedores = typeof proveedores === 'string' ? JSON.parse(proveedores) : proveedores;
        
        if (!Array.isArray(json_proveedores)) {
            json_proveedores = [];
        }
        
        let options = '<option value="">-- Seleccionar --</option>';
        json_proveedores.forEach(p => {
            options += `<option value="${p.id_proveedor}">${p.nombre}</option>`;
        });
        document.getElementById('nc_proveedor').innerHTML = options;
    } catch (error) {
        console.error('Error al cargar proveedores:', error);
    }
}

function cargarProveedorDesdeFactura(id_factura) {
    let factura = ejecutarAjax("controladores/factura_compra.php", "obtener_por_id=" + id_factura);
    
    try {
        let json_factura = typeof factura === 'string' ? JSON.parse(factura) : factura;
        console.log('Factura obtenida:', json_factura);
        
        if (json_factura && json_factura.id_proveedor) {
            $('#nc_proveedor').val(json_factura.id_proveedor);
            console.log('Proveedor asignado:', json_factura.id_proveedor);
        }
    } catch (error) {
        console.error('Error al cargar proveedor:', error);
    }
}

function cargarDetallesFacturaNC(id_factura) {
    console.log('Cargando detalles de factura:', id_factura, 'Tipo:', typeof id_factura);
    
    if (!id_factura) {
        console.error('ID de factura vacío');
        return;
    }
    
    // Usar método POST directo para mejor control
    $.ajax({
        type: 'POST',
        url: 'controladores/detalle_factura.php',
        data: { obtener_detalles: id_factura },
        dataType: 'json',
        success: function(response) {
            console.log('Respuesta del servidor:', response);
            
            if (response && response.error) {
                console.error('Error en la respuesta:', response.error);
                Swal.fire('Error', 'Error: ' + response.error, 'error');
                return;
            }
            
            if (Array.isArray(response) && response.length > 0) {
                nc_detalle_temp = [];
                
                response.forEach(d => {
                    let precio_unitario = parseFloat(d.costo || d.precio_unitario || 0);
                    let cantidad = parseFloat(d.cantidad || 0);
                    let subtotal = cantidad > 0 ? precio_unitario * cantidad : parseFloat(d.total || 0);
                    
                    nc_detalle_temp.push({
                        'id_productos': d.id_productos,
                        'nombre_producto': d.nombre_producto || 'Producto',
                        'cantidad': cantidad,
                        'precio_unitario': precio_unitario,
                        'iva': 0,
                        'subtotal': subtotal,
                        'iva_monto': 0,
                        'total': subtotal
                    });
                });
                
                console.log('Detalles cargados:', nc_detalle_temp);
                refrescarTablaNotaCredito();
            } else {
                console.warn('No hay detalles para esta factura');
                nc_detalle_temp = [];
                refrescarTablaNotaCredito();
                Swal.fire('Información', 'No hay detalles para esta factura', 'info');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', status, error);
            console.error('Response:', xhr.responseText);
            Swal.fire('Error', 'Error al cargar detalles: ' + error, 'error');
        }
    });
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
    let subtotal = cantidad * precio_unit;
    let total = subtotal;
    
    let existe = nc_detalle_temp.findIndex(d => d.id_productos == id_prod);
    
    if (existe >= 0) {
        nc_detalle_temp[existe].cantidad += cantidad;
        nc_detalle_temp[existe].subtotal = nc_detalle_temp[existe].cantidad * nc_detalle_temp[existe].precio_unitario;
        nc_detalle_temp[existe].total = nc_detalle_temp[existe].subtotal;
    } else {
        nc_detalle_temp.push({
            'id_productos': id_prod,
            'nombre_producto': nombre_prod,
            'cantidad': cantidad,
            'precio_unitario': precio_unit,
            'iva': 0,
            'subtotal': subtotal,
            'iva_monto': 0,
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
    let subtotal = 0, total = 0;
    
    nc_detalle_temp.forEach(d => {
        subtotal += d.subtotal;
        total += d.total;
    });
    
    $('#nc_subtotal').val(parseFloat(subtotal).toFixed(2));
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
    
    let respuesta = ejecutarAjax('controladores/nota_credito.php', 
        'accion=guardar&numero_nota=' + encodeURIComponent(numero) + 
        '&fecha_nota=' + fecha +
        '&id_factura_compra=' + id_factura +
        '&id_proveedor=' + id_proveedor +
        '&motivo=' + encodeURIComponent(motivo) +
        '&observaciones=' + encodeURIComponent(observaciones) +
        '&monto_total=' + monto_total +
        '&detalles=' + encodeURIComponent(JSON.stringify(nc_detalle_temp)) +
        '&id_usuario=' + id_usuario);
    
    try {
        let data = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;
        if (data.success) {
            Swal.fire('Éxito', 'Nota de Crédito guardada', 'success').then(() => {
                nc_detalle_temp = [];
                mostrarListaNotasCredito();
            });
        } else {
            Swal.fire('Error', data.error || 'Error al guardar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        Swal.fire('Error', 'Error al procesar respuesta', 'error');
    }
}

function cargarTablaNotasCredito() {
    let buscar = $('#search_nota_credito').val() || '';
    
    let respuesta = ejecutarAjax('controladores/nota_credito.php', 
        'accion=listar&buscar=' + encodeURIComponent(buscar));
    
    try {
        let data = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;
        let html = '';
        
        // Validar si hay error en la respuesta
        if (data && data.error) {
            console.error('Error en respuesta:', data.error);
            html = '<tr><td colspan="8" class="text-center text-danger">Error: ' + data.error + '</td></tr>';
            $('#nota_credito_tb').html(html);
            return;
        }
        
        // Validar que data sea un array
        if (!Array.isArray(data)) {
            console.warn('Respuesta no es un array:', data);
            data = [];
        }
        
        if (data.length === 0) {
            html = '<tr><td colspan="8" class="text-center">No hay registros</td></tr>';
        } else {
            data.forEach(nc => {
                let badge = nc.estado === 'ACTIVO' ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                html += `<tr>
                            <td>${nc.id_nota_credito}</td>
                            <td>${nc.numero_nota}</td>
                            <td>${nc.fecha_nota}</td>
                            <td>${nc.proveedor_nombre || '-'}</td>
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
    } catch (error) {
        console.error('Error al cargar tabla:', error, 'Respuesta:', respuesta);
        $('#nota_credito_tb').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar los registros: ' + error.message + '</td></tr>');
    }
}

function verDetallesNotaCredito(id_nota) {
    let respuesta = ejecutarAjax('controladores/nota_credito.php', 
        'accion=obtener_detalles&id_nota_credito=' + id_nota);
    
    try {
        let data = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;
        
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
    } catch (error) {
        console.error('Error:', error);
    }
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
            let respuesta = ejecutarAjax('controladores/nota_credito.php', 
                'accion=actualizar&id_nota_credito=' + id_nota + '&estado=INACTIVO');
            
            try {
                let data = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;
                if (data.success) {
                    Swal.fire('Éxito', 'Nota anulada', 'success').then(() => cargarTablaNotasCredito());
                } else {
                    Swal.fire('Error', 'Error al anular', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
            }
        }
    });
}

function imprimirNotaCredito(id_nota) {
    if (!id_nota || id_nota <= 0) {
        mensaje_dialogo_info_ERROR("ID de nota de crédito inválido", "Error");
        return;
    }
    window.open(`paginas/movimientos/nota_credito/print.php?id=${id_nota}`, '_blank', 'width=900,height=700,menubar=yes,scrollbars=yes');
}
