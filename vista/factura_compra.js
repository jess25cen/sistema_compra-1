function mostrarListaFacturaCompras() {
    let contenido = dameContenido("paginas/movimientos/factura_compra/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaFacturaCompras();
}

function mostrarAgregarFacturaCompra() {
    let contenido = dameContenido("paginas/movimientos/factura_compra/agregar.php");
    $("#contenido-principal").html(contenido);

    // establecer fecha actual
    let hoy = new Date().toISOString().split('T')[0];
    $("#factura_fecha").val(hoy);
    cargarListaProveedoresFactura();
    cargarListaOrdenesCompraParaFactura();
    cargarListaProductosFactura();
    cargarListaCondiciones();
}

function cargarListaProveedoresFactura() {
    let proveedores = ejecutarAjax("controladores/proveedor.php", "listar=1");
    try {
        let json = typeof proveedores === 'string' ? JSON.parse(proveedores) : proveedores;
        if (!Array.isArray(json)) json = [];
        $("#factura_proveedor").find("option:not(:first)").remove();
        json.forEach(function(item) {
            $("#factura_proveedor").append(`<option value="${item.id_proveedor}">${item.nombre} ${item.apellido || ''}</option>`);
        });
    } catch (e) {
        console.error('Error al cargar proveedores para factura:', e);
    }
}

function cargarListaOrdenesCompraParaFactura() {
    let ordenes = ejecutarAjax("controladores/orden_compra.php", "listar=1");
    try {
        let json = typeof ordenes === 'string' ? JSON.parse(ordenes) : ordenes;
        if (!Array.isArray(json)) json = [];
        $("#factura_orden").find("option:not(:first)").remove();
        json.forEach(function(it) {
            $("#factura_orden").append(`<option value="${it.orden_compra}">Orden #${it.orden_compra} - ${it.fecha_orden}</option>`);
        });
    } catch (e) { console.error(e); }
}

function cargarListaProductosFactura() {
    let productos = ejecutarAjax("controladores/producto.php", "listar=1");
    try {
        let json = typeof productos === 'string' ? JSON.parse(productos) : productos;
        if (!Array.isArray(json)) json = [];
        $("#factura_producto").find("option:not(:first)").remove();
        json.forEach(function(it) {
            $("#factura_producto").append(`<option value="${it.id_productos}">${it.nombre_producto}</option>`);
        });
    } catch (e) { console.error(e); }
}

// Cuando se selecciona una orden, cargar proveedor y productos asociados
$(document).on('change', '#factura_orden', function() {
    let idOrden = $(this).val();
    if (!idOrden) return;

    // Obtener datos de la orden (proveedor, etc.)
    let ordenRaw = ejecutarAjax('controladores/orden_compra.php', 'id=' + idOrden);
    let orden;
    try { orden = (typeof ordenRaw === 'string') ? JSON.parse(ordenRaw) : ordenRaw; } catch(e) { orden = null; }
    if (orden && orden.id_proveedor) {
        // Si el select de proveedores ya fue cargado, setear el valor
        let $prov = $('#factura_proveedor');
        if ($prov.find('option[value="'+orden.id_proveedor+'"]').length) {
            $prov.val(orden.id_proveedor);
        } else {
            // si no existe la opción, agregarla temporalmente
            $prov.append(`<option value="${orden.id_proveedor}">${orden.proveedor_nombre||'Proveedor'}</option>`);
            $prov.val(orden.id_proveedor);
        }
    }

    // Obtener detalles de la orden (productos)
    let detRaw = ejecutarAjax('controladores/orden_compra.php', 'obtener_detalles=' + idOrden);
    let detalles;
    try { detalles = (typeof detRaw === 'string') ? JSON.parse(detRaw) : detRaw; } catch(e) { detalles = null; }

    // Limpiar tabla de detalles de factura
    $('#factura_detalles_tb').empty();

    if (Array.isArray(detalles) && detalles.length) {
        detalles.forEach(function(it, idx){
            let nombre = it.nombre_producto || '';
            let cantidad = it.cantidad || 1;
            // el controlador devuelve 'precio' en la consulta
            let precio = (it.precio !== undefined && it.precio !== null) ? it.precio : 0;
            let iva = (it.iva !== undefined && it.iva !== null) ? it.iva : 0;
            let fila = `<tr data-iva="${iva}">`;
            fila += `<td>${idx+1}</td>`;
            fila += `<td><input type="hidden" class="producto_id" value="${it.id_productos}">${nombre}</td>`;
            fila += `<td><input type="number" min="0" step="1" class="form-control form-control-sm producto_cantidad" value="${cantidad}"></td>`;
            fila += `<td><input type="number" step="0.01" class="form-control form-control-sm producto_precio" value="${precio}"></td>`;
            fila += `<td class='text-end'><button class='btn btn-danger btn-sm eliminar-detalle-factura' type="button"><i data-feather="trash-2"></i></button></td>`;
            fila += `</tr>`;
            $('#factura_detalles_tb').append(fila);
        });
        feather.replace();
        // calcular totales tras cargar detalles
        calcularTotalesFactura();
    }

});

function agregarTablaFactura() {
    let id_producto = $("#factura_producto").val();
    let cantidad = $("#factura_cantidad").val();

    if (id_producto === "0" || id_producto.trim().length === 0) { mensaje_dialogo_info_ERROR("Debes seleccionar un producto","ATENCIÓN"); return; }
    if (!cantidad || cantidad <= 0) { mensaje_dialogo_info_ERROR("La cantidad debe ser mayor a 0","ATENCIÓN"); return; }

    let nombre = $("#factura_producto option:selected").text();
    let contador = $("#factura_detalles_tb tr").length + 1;

    // obtener precio e iva del producto
    let prod = ejecutarAjax("controladores/producto.php", "id=" + id_producto);
    prod = typeof prod === 'string' ? JSON.parse(prod) : prod;
    let precio = (prod && prod.costo !== undefined) ? prod.costo : (prod.precio || 0);
    let iva = (prod && prod.iva !== undefined) ? prod.iva : 0;

    let fila = `<tr data-iva="${iva}">`;
    fila += `<td>${contador}</td>`;
    fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${nombre}</td>`;
    fila += `<td><input type="number" min="0" step="1" class="form-control form-control-sm producto_cantidad" value="${cantidad}"></td>`;
    fila += `<td><input type="number" step="0.01" class="form-control form-control-sm producto_precio" value="${precio}"></td>`;
    fila += `<td class='text-end'><button class='btn btn-danger btn-sm eliminar-detalle-factura' type="button"><i data-feather="trash-2"></i></button></td>`;
    fila += `</tr>`;

    $("#factura_detalles_tb").append(fila);
    feather.replace();
    $("#factura_producto").val("0");
    $("#factura_cantidad").val("1");
    calcularTotalesFactura();
}

$(document).on('click', '.eliminar-detalle-factura', function(){
    $(this).closest('tr').remove();
    calcularTotalesFactura();
});

// recalcular totales cuando cambie precio o cantidad
$(document).on('input change', '.producto_precio, .producto_cantidad', function(){
    calcularTotalesFactura();
});

function calcularTotalesFactura(){
    let subtotal = 0.0;
    let iva5_monto = 0.0;       // Monto de IVA 5%
    let iva10_monto = 0.0;      // Monto de IVA 10%
    let exenta = 0.0;
    let total_iva = 0.0;

    $('#factura_detalles_tb tr').each(function(){
        let precio = parseFloat($(this).find('.producto_precio').val()) || 0;
        let cantidad = parseFloat($(this).find('.producto_cantidad').val()) || 0;
        let bruto = precio * cantidad;
        subtotal += bruto;

        // Obtener el IVA directamente del atributo data-iva (en porcentaje)
        let iva_porcentaje = parseFloat($(this).attr('data-iva')) || 0;
        let rate = iva_porcentaje / 100.0;  // Convertir de porcentaje a decimal

        let iva = parseFloat((bruto * rate).toFixed(2));
        total_iva += iva;

        console.log('Producto - Cantidad:', cantidad, 'Precio:', precio, 'Bruto:', bruto, 'IVA %:', iva_porcentaje, 'Tasa:', rate * 100 + '%', 'IVA $:', iva);

        if (iva_porcentaje === 5) {
            iva5_monto += iva;
        }
        else if (iva_porcentaje === 10) {
            iva10_monto += iva;
        }
        else {
            exenta += bruto;
        }
    });

    let total = subtotal + total_iva;

    console.log('=== CÁLCULO TOTALES ===');
    console.log('Subtotal:', subtotal);
    console.log('IVA 5%:', iva5_monto);
    console.log('IVA 10%:', iva10_monto);
    console.log('Exenta:', exenta);
    console.log('Total IVA:', total_iva);
    console.log('Total:', total);
    console.log('=====================');

    // actualizar campos en la vista
    $('#fc_subtotal').val(subtotal.toFixed(2));
    $('#fc_iva5').val(iva5_monto.toFixed(2));      // Mostrar el monto del IVA 5%
    $('#fc_iva10').val(iva10_monto.toFixed(2));    // Mostrar el monto del IVA 10%
    $('#fc_exenta').val(exenta.toFixed(2));
    $('#fc_total_iva').val(total_iva.toFixed(2));
    $('#fc_total').val(total.toFixed(2));
}

function cargarListaCondiciones(){
    let resp = ejecutarAjax('controladores/condicion_pago.php', 'listar=1');
    try{
        let data = typeof resp === 'string' ? JSON.parse(resp) : resp;
        if(!Array.isArray(data)) data = [];
        let $sel = $('#factura_condicion');
        if($sel.length === 0) return;
        $sel.find('option:not(:first)').remove();
        data.forEach(function(it){
            $sel.append(`<option value="${it.id_condicion}">${it.nombre}</option>`);
        });
    }catch(e){
        console.error('Error cargando condiciones de pago', e, resp);
    }
}

function guardarFacturaCompra() {
    let detalles = [];
    $("#factura_detalles_tb tr").each(function(){
        let idp = $(this).find('.producto_id').val();
        let cant = $(this).find('.producto_cantidad').val();
        let precio = $(this).find('.producto_precio').val() || 0;
        if (idp && cant) {
            detalles.push({ id_productos: idp, cantidad: cant, precio_unitario: parseFloat(precio), total: (parseFloat(precio) * parseFloat(cant)) });
        }
    });

    console.log('Detalles a guardar:', detalles);

    if (detalles.length === 0) { mensaje_dialogo_info_ERROR('Debe agregar al menos un detalle','Atención'); return; }

    let cabecera = {
        numero_factura: $("#factura_numero").val() || null,
        fecha_factura: $("#factura_fecha").val(),
        id_orden_compra: $("#factura_orden").val() || null,
        timbrado: $("#factura_timbrado").val() || null,
        fecha_vencimiento: $("#factura_fecha_vencimiento").val() || null,
        id_proveedor: $("#factura_proveedor").val() || null,
        id_condicion: $("#factura_condicion").val() || null,
        id_usuario: $("#id_usuario_factura").val() || 1,
        estado: 'ACTIVO'
    };

    console.log('Cabecera a guardar:', cabecera);

    let respRaw = ejecutarAjax('controladores/factura_compra.php', 'guardar=' + JSON.stringify(cabecera));
    console.log('Respuesta de factura_compra.php:', respRaw);
    
    let resp;
    try {
        if (typeof respRaw === 'string') {
            if (respRaw.trim().length === 0) {
                console.error('Empty response from factura_compra.php (guardar)');
                mensaje_dialogo_info_ERROR('Respuesta vacía del servidor al guardar factura','Error');
                return;
            }
            resp = JSON.parse(respRaw);
        } else {
            resp = respRaw;
        }
    } catch (e) {
        console.error('Error parsing server response for factura save:', respRaw, e);
        mensaje_dialogo_info_ERROR('Respuesta inválida del servidor al guardar factura','Error');
        return;
    }

    if (!resp || !resp.success || !resp.id_factura_compra) { mensaje_dialogo_info_ERROR((resp && resp.error) ? resp.error : 'Error al guardar factura','Error'); return; }

    let id_factura = resp.id_factura_compra;
    console.log('Factura guardada con ID:', id_factura);
    
    // guardar detalles
    for (let i=0;i<detalles.length;i++){
        let det = detalles[i];
        det.id_factura_compra = id_factura;
        console.log('Guardando detalle:', det);
        
        let rRaw = ejecutarAjax('controladores/detalle_factura.php', 'guardar=' + JSON.stringify(det));
        console.log('Respuesta de detalle_factura.php:', rRaw);
        
        try {
            let r = (typeof rRaw === 'string' && rRaw.trim().length) ? JSON.parse(rRaw) : rRaw;
            console.log('Detalle procesado:', r);
            if (r && r.error) {
                console.error('Error al guardar detalle:', r.error);
                mensaje_dialogo_info_ERROR('Error al guardar detalle: ' + r.error, 'Error');
            }
        } catch (e) {
            console.error('Error parsing detalle_factura response:', rRaw, e);
            mensaje_dialogo_info_ERROR('Error al procesar respuesta del detalle: ' + e.message, 'Error');
        }
    }

    // Guardar cuotas si la condición es Crédito
    let id_condicion = $("#factura_condicion").val();
    let condicion_texto = $("#factura_condicion option:selected").text().toLowerCase();
    if ((condicion_texto.indexOf('credito') !== -1 || id_condicion === '1') && $('#cuotas_tb tr').length > 0) {
        console.log('Intentando guardar cuotas (nota: cuenta_pagar.php aún no existe)');
        let cuotas = obtenerCuotas();
        for (let i = 0; i < cuotas.length; i++) {
            let cuota = cuotas[i];
            cuota.id_factura_compra = id_factura;
            // TODO: Crear controlador cuenta_pagar.php
            // let cRaw = ejecutarAjax('controladores/cuenta_pagar.php', 'guardar=' + JSON.stringify(cuota));
            try {
                // let c = (typeof cRaw === 'string' && cRaw.trim().length) ? JSON.parse(cRaw) : cRaw;
                console.log('Cuota a guardar (pendiente controlador):', cuota);
            } catch (e) {
                console.error('Error:', e);
            }
        }
    }

    // generar libro_compra (subtotales e IVA)
    let libroRaw = ejecutarAjax('controladores/factura_compra.php', 'generar_libro=' + id_factura);
    try{
        let libro = (typeof libroRaw === 'string' && libroRaw.trim().length) ? JSON.parse(libroRaw) : libroRaw;
        if (libro && libro.success) {
            console.log('Libro generado', libro);
        } else if (libro && libro.error) {
            console.error('Error generando libro:', libro.error);
        }
    } catch(e){ console.error('Error parseando generar_libro response', libroRaw, e); }

    mensaje_confirmacion('Factura guardada correctamente','Éxito');
    mostrarListaFacturaCompras();
}

function cargarTablaFacturaCompras() {
    let datos = ejecutarAjax('controladores/factura_compra.php', 'listar=1');
    let fila = '';
    try {
        let json;
        if (typeof datos === 'string') {
            if (datos.trim().length === 0) {
                console.warn('Empty response from factura_compra.php?listar=1');
                json = [];
            } else {
                try {
                    json = JSON.parse(datos);
                } catch (pe) {
                    console.error('Invalid JSON from factura_compra.php (listar):', datos, pe);
                    json = [];
                }
            }
        } else {
            json = datos;
        }

        if (!Array.isArray(json) || json.length === 0) {
            fila = `<tr><td colspan='7' class='text-center text-muted'>No hay registros</td></tr>`;
        } else {
            json.forEach(function(item){
                let estado_badge = `<span class="badge bg-label-${item.estado === 'ACTIVO' ? 'success' : 'danger'}">${item.estado || 'INACTIVO'}</span>`;
                fila += `<tr>`;
                fila += `<td>${item.id_factura_compra}</td>`;
                fila += `<td>${item.numero_factura || ''}</td>`;
                fila += `<td>${item.fecha_factura || ''}</td>`;
                fila += `<td>${item.proveedor_nombre || ''}</td>`;
                fila += `<td>${estado_badge}</td>`;
                fila += `<td>`;
                fila += `<button class='btn btn-sm btn-info' onclick="verDetallesFactura(${item.id_factura_compra})"><i data-feather="eye"></i></button> `;
                if (item.estado === 'ACTIVO') {
                    fila += `<button class='btn btn-sm btn-danger' onclick="anularFactura(${item.id_factura_compra})"><i data-feather="x-circle"></i></button> `;
                }
                fila += `<button class='btn btn-sm btn-primary' onclick="imprimirFactura(${item.id_factura_compra})"><i data-feather="printer"></i></button>`;
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    } catch(e) {
        console.error(e);
        fila = `<tr><td colspan='7' class='text-center text-danger'>Error al cargar registros</td></tr>`;
    }

    $('#factura_compra_tb').html(fila);
    feather.replace();
}

$(document).on('click', '.ver-detalle-factura', function(){
    let id = $(this).data('id');
    let detalles = ejecutarAjax('controladores/factura_compra.php', 'obtener_detalles=' + id);
    try {
        let json = typeof detalles === 'string' ? JSON.parse(detalles) : detalles;
        if (!Array.isArray(json) || json.length === 0) { mensaje_dialogo_info_ERROR('No hay detalles','Info'); return; }
        let html = '<table class="table table-sm"><thead><tr><th>Producto</th><th>Cantidad</th><th>Monto</th></tr></thead><tbody>';
        json.forEach(function(it){ html += `<tr><td>${it.nombre_producto||''}</td><td>${it.cantidad||''}</td><td>${it.total||''}</td></tr>`; });
        html += '</tbody></table>';
        Swal.fire({ title: 'Detalles Factura #' + id, html: html, width: '800px' });
    } catch(e){ console.error(e); mensaje_dialogo_info_ERROR('Error al obtener detalles','Error'); }
});

function verDetallesFactura(id) {
    console.log('Obteniendo detalles de factura:', id);
    let detalles = ejecutarAjax('controladores/factura_compra.php', 'obtener_detalles=' + id);
    console.log('Respuesta del servidor:', detalles);
    
    try {
        let json = typeof detalles === 'string' ? JSON.parse(detalles) : detalles;
        console.log('JSON parseado:', json);
        
        if (!Array.isArray(json)) {
            json = [];
        }
        
        if (json.length === 0) { 
            mensaje_dialogo_info_ERROR('No hay detalles registrados para esta factura','Info'); 
            return; 
        }
        
        let html = '<table class="table table-sm"><thead><tr><th>Producto</th><th>Cantidad</th><th>Monto</th></tr></thead><tbody>';
        json.forEach(function(it){ 
            html += `<tr><td>${it.nombre_producto||''}</td><td>${it.cantidad||''}</td><td>$${parseFloat(it.total).toFixed(2)}</td></tr>`; 
        });
        html += '</tbody></table>';
        Swal.fire({ title: 'Detalles Factura #' + id, html: html, width: '800px' });
    } catch(e){ 
        console.error('Error al procesar detalles:', e); 
        mensaje_dialogo_info_ERROR('Error al obtener detalles: ' + e.message,'Error'); 
    }
}

function anularFactura(id) {
    Swal.fire({
        title: '¿Anular Factura?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            let respRaw = ejecutarAjax('controladores/factura_compra.php', 'actualizar=' + JSON.stringify({id_factura_compra: id, estado: 'INACTIVO'}));
            try {
                let resp = typeof respRaw === 'string' ? JSON.parse(respRaw) : respRaw;
                if (resp && resp.success) {
                    mensaje_confirmacion('Factura anulada correctamente','Éxito');
                    cargarTablaFacturaCompras();
                } else {
                    mensaje_dialogo_info_ERROR(resp && resp.error ? resp.error : 'Error al anular','Error');
                }
            } catch(e) {
                console.error('Error:', e);
                mensaje_dialogo_info_ERROR('Error al procesar la solicitud','Error');
            }
        }
    });
}

function imprimirFactura(id) {
    if (!id || id <= 0) {
        mensaje_dialogo_info_ERROR("ID de factura inválido", "Error");
        return;
    }
    window.open(`paginas/movimientos/factura_compra/print.php?id=${id}`, '_blank', 'width=900,height=700,menubar=yes,scrollbars=yes');
}

// Manejo del cambio de Condición de Pago
$(document).on('change', '#factura_condicion', function(){
    let id_condicion = $(this).val();
    let condicion_texto = $(this).find('option:selected').text().toLowerCase();
    
    console.log('Condición seleccionada:', id_condicion, 'Texto:', condicion_texto);
    
    // Mostrar/ocultar sección de cuotas si es Crédito
    if (condicion_texto.indexOf('credito') !== -1 || id_condicion === '1') {
        $('#seccion_cuotas').slideDown();
    } else {
        $('#seccion_cuotas').slideUp();
        // Limpiar cuotas si se cambia a Contado
        $('#cuotas_tb').empty();
    }
});

function generarCuotas() {
    let total = parseFloat($('#fc_total').val()) || 0;
    let num_cuotas = parseInt($('#num_cuotas').val()) || 1;
    let dias_cuota = 30; // Valor fijo de 30 días entre cuotas
    
    if (total <= 0) {
        mensaje_dialogo_info_ERROR('El total debe ser mayor a 0 para generar cuotas','Atención');
        return;
    }
    
    if (num_cuotas <= 0) {
        mensaje_dialogo_info_ERROR('El número de cuotas debe ser mayor a 0','Atención');
        return;
    }
    
    let monto_cuota = parseFloat((total / num_cuotas).toFixed(2));
    let fecha_inicio = new Date($('#factura_fecha').val());
    
    if (isNaN(fecha_inicio.getTime())) {
        mensaje_dialogo_info_ERROR('Selecciona una fecha de factura válida','Atención');
        return;
    }
    
    // Limpiar tabla de cuotas
    $('#cuotas_tb').empty();
    
    // Generar cuotas
    let saldo_restante = total;
    for (let i = 1; i <= num_cuotas; i++) {
        let fecha_vencimiento = new Date(fecha_inicio);
        fecha_vencimiento.setDate(fecha_vencimiento.getDate() + (dias_cuota * i));
        let fecha_str = fecha_vencimiento.toISOString().split('T')[0];
        
        // Última cuota recibe el saldo restante
        let monto = (i === num_cuotas) ? saldo_restante : monto_cuota;
        saldo_restante -= monto;
        
        let fila = `<tr data-cuota="${i}" data-monto="${monto}" data-fecha="${fecha_str}">`;
        fila += `<td>${i}</td>`;
        fila += `<td>${fecha_str}</td>`;
        fila += `<td>$${parseFloat(monto).toFixed(2)}</td>`;
        fila += `<td class='text-center'><button class='btn btn-danger btn-sm eliminar-cuota' type="button"><i data-feather="trash-2"></i></button></td>`;
        fila += `</tr>`;
        
        $('#cuotas_tb').append(fila);
    }
    
    feather.replace();
    console.log('Cuotas generadas:', num_cuotas, 'Monto por cuota:', monto_cuota, 'Total:', total);
    mensaje_confirmacion('Cuotas generadas correctamente','Éxito');
}

$(document).on('click', '.eliminar-cuota', function(){
    $(this).closest('tr').remove();
    console.log('Cuota eliminada');
});

// Función auxiliar para obtener las cuotas antes de guardar
function obtenerCuotas() {
    let cuotas = [];
    $('#cuotas_tb tr').each(function(){
        let cuota = {
            cuota: parseInt($(this).attr('data-cuota')),
            monto: parseFloat($(this).attr('data-monto')),
            fecha_vencimiento: $(this).attr('data-fecha')
        };
        cuotas.push(cuota);
    });
    return cuotas;
}

