function mostrarListaOrdenesCompra() {
    let contenido = dameContenido("paginas/movimientos/orden_compra/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaOrdenesCompra();
}

function mostrarAgregarOrdenCompra() {
    let contenido = dameContenido("paginas/movimientos/orden_compra/agregar.php");
    $("#contenido-principal").html(contenido);
    
    // Establecer fecha de hoy
    let hoy = new Date().toISOString().split('T')[0];
    $("#orden_fecha").val(hoy);
    
    cargarListaProveedores();
    cargarListaProductosOrden();
    cargarListaPresupuestos();
    // Bind cambio de presupuesto para cargar sus detalles
    $(document).off('change', '#orden_presupuesto');
    $(document).on('change', '#orden_presupuesto', function() {
        cargarDetallesPresupuestoSeleccionado();
    });
}

function cargarDetallesPresupuestoSeleccionado() {
    let id_pres = $("#orden_presupuesto").val();
    if (!id_pres || id_pres === "") {
        // limpiar tabla
        $("#detalles_orden_tb").empty();
        return;
    }

    let detalles = ejecutarAjax("controladores/presupuesto.php", "obtener_detalles=" + id_pres);
    detalles = typeof detalles === 'string' ? JSON.parse(detalles) : detalles;
    if (!Array.isArray(detalles)) detalles = [];

    // Limpiar tabla
    $("#detalles_orden_tb").empty();

    detalles.forEach(function(item, idx) {
        // item expected: id_detalle_presupuesto, id_presupuesto, id_productos, nombre_producto, cantidad
        let id_producto = item.id_productos || item.id_producto || null;
        let cantidad = item.cantidad || 1;

        // Obtener costo del producto
        let prod = ejecutarAjax("controladores/producto.php", "id=" + id_producto);
        prod = typeof prod === 'string' ? JSON.parse(prod) : prod;
        let costo = 0;
        if (prod && prod.costo !== undefined) costo = prod.costo;

        let contador = $("#detalles_orden_tb tr").length + 1;
        let fila = `<tr>`;
        fila += `<td>${contador}</td>`;
        fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${item.nombre_producto || ''}</td>`;
        fila += `<td><input type="hidden" class="producto_cantidad" value="${cantidad}">${cantidad}</td>`;
        fila += `<td><input type="number" step="0.01" class="form-control form-control-sm producto_costo" value="${costo}"></td>`;
        fila += `<td class='text-end'><button class='btn btn-danger btn-sm eliminar-detalle-orden-btn' type="button"><i data-feather="trash-2"></i></button></td>`;
        fila += `</tr>`;

        $("#detalles_orden_tb").append(fila);
    });
    feather.replace();

    // Obtener proveedor asociado al presupuesto y seleccionarlo (si aplica)
    try {
        let presRaw = ejecutarAjax("controladores/presupuesto.php", "obtener_por_id=" + id_pres);
        let pres = typeof presRaw === 'string' ? (presRaw.trim().length ? JSON.parse(presRaw) : {}) : presRaw;
        if (pres && pres.id_proveedor) {
            // seleccionar proveedor en el select si existe la opción
            let exists = $("#orden_proveedor option[value='" + pres.id_proveedor + "']").length > 0;
            if (exists) {
                $("#orden_proveedor").val(pres.id_proveedor);
            } else {
                // si no existe la opción, intentar recargar proveedores y luego seleccionar
                cargarListaProveedores();
                setTimeout(function() { $("#orden_proveedor").val(pres.id_proveedor); }, 250);
            }
        }
    } catch (e) {
        console.error('Error al obtener presupuesto por id para seleccionar proveedor:', e);
    }
}

function cargarListaPresupuestos() {
    let presupuestos = ejecutarAjax("controladores/presupuesto.php", "listar=1");
    try {
        let json_pres = typeof presupuestos === 'string' ? JSON.parse(presupuestos) : presupuestos;
        if (!Array.isArray(json_pres)) json_pres = [];
        $("#orden_presupuesto").find("option:not(:first)").remove();
        json_pres.forEach(function(item) {
            $("#orden_presupuesto").append(`<option value="${item.id_presupuesto}">Presupuesto #${item.id_presupuesto} - ${item.fecha_presupuesto}</option>`);
        });
    } catch (e) {
        console.error('Error al cargar presupuestos', e);
    }
}

function cargarListaProveedores() {
    let proveedores = ejecutarAjax("controladores/proveedor.php", "listar=1");
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_proveedores = typeof proveedores === 'string' ? JSON.parse(proveedores) : proveedores;
        
        if (!Array.isArray(json_proveedores)) {
            json_proveedores = [];
        }
        
        // Limpiar opciones previas excepto la opción por defecto
        $("#orden_proveedor").find("option:not(:first)").remove();
        
        json_proveedores.forEach(function(item) {
            $("#orden_proveedor").append(`<option value="${item.id_proveedor}">${item.nombre} ${item.apellido || ''}</option>`);
        });
    } catch (error) {
        console.error('Error al cargar proveedores:', error);
    }
}

function cargarListaProductosOrden() {
    let productos = ejecutarAjax("controladores/producto.php", "listar=1");
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_productos = typeof productos === 'string' ? JSON.parse(productos) : productos;
        
        if (!Array.isArray(json_productos)) {
            json_productos = [];
        }
        
        // Limpiar opciones previas excepto la opción por defecto
        $("#orden_producto").find("option:not(:first)").remove();
        
        json_productos.forEach(function(item) {
            $("#orden_producto").append(`<option value="${item.id_productos}">${item.nombre_producto}</option>`);
        });
    } catch (error) {
        console.error('Error al cargar productos:', error);
    }
}

function agregarTablaOrdenCompra() {
    let id_producto = $("#orden_producto").val();
    let cantidad = $("#orden_cantidad").val();
    
    if (id_producto === "0" || id_producto.trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes seleccionar un producto", "ATENCIÓN");
        return;
    }
    
    if (!cantidad || cantidad <= 0) {
        mensaje_dialogo_info_ERROR("La cantidad debe ser mayor a 0", "ATENCIÓN");
        return;
    }

    let nombre_producto = $("#orden_producto option:selected").text();
    let contador = $("#detalles_orden_tb tr").length + 1;

    // obtener costo del producto
    let prod = ejecutarAjax("controladores/producto.php", "id=" + id_producto);
    prod = typeof prod === 'string' ? JSON.parse(prod) : prod;
    let costo = (prod && prod.costo !== undefined) ? prod.costo : 0;

    let fila = `<tr>`;
    fila += `<td>${contador}</td>`;
    fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${nombre_producto}</td>`;
    fila += `<td><input type="hidden" class="producto_cantidad" value="${cantidad}">${cantidad}</td>`;
    fila += `<td><input type="number" step="0.01" class="form-control form-control-sm producto_costo" value="${costo}"></td>`;
    fila += `<td class='text-end'>`;
    fila += `<button class='btn btn-danger btn-sm eliminar-detalle-orden-btn' type="button"><i data-feather="trash-2"></i></button>`;
    fila += `</td>`;
    fila += `</tr>`;
    
    $("#detalles_orden_tb").append(fila);
    feather.replace();
    
    // Limpiar campos
    $("#orden_producto").val("0");
    $("#orden_cantidad").val("1");
}

function agregarDetalleOrden() {
    let id_producto = $("#orden_compra_producto").val();
    let cantidad = $("#orden_compra_cantidad").val();
    
    if (id_producto === "0" || id_producto.trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes seleccionar un producto", "ATENCIÓN");
        return;
    }
    
    if (!cantidad || cantidad <= 0) {
        mensaje_dialogo_info_ERROR("La cantidad debe ser mayor a 0", "ATENCIÓN");
        return;
    }
    
    let nombre_producto = $("#orden_compra_producto option:selected").text();
    let fila = `<tr>`;
    fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${nombre_producto}</td>`;
    fila += `<td><input type="hidden" class="producto_cantidad" value="${cantidad}">${cantidad}</td>`;
    fila += `<td class='text-end'>`;
    fila += `<button class='btn btn-danger btn-sm eliminar-detalle-orden-btn' type="button"><i data-feather="trash-2"></i></button>`;
    fila += `</td>`;
    fila += `</tr>`;
    
    $("#detalles_orden_tb").append(fila);
    feather.replace();
    
    $("#orden_compra_producto").val("0");
    $("#orden_compra_cantidad").val("1");
}

function guardarOrdenCompra() {
    if ($("#orden_compra_usuario").val() === "0") {
        mensaje_dialogo_info_ERROR("Debes seleccionar un usuario", "ATENCIÓN");
        return;
    }
    
    let detalles = [];
    $("#detalles_orden_tb tr").each(function() {
        let id_producto = $(this).find(".producto_id").val();
        let cantidad = $(this).find(".producto_cantidad").val();
        if (id_producto && cantidad) {
            detalles.push({
                id_productos: id_producto,
                cantidad: cantidad
            });
        }
    });
    
    if (detalles.length === 0) {
        mensaje_dialogo_info_ERROR("Debes agregar al menos un detalle", "ATENCIÓN");
        return;
    }
    
    let cabecera = {
        fecha_orden: $("#orden_compra_fecha").val(),
        id_usuario: $("#orden_compra_usuario").val(),
        estado: 'ACTIVO'
    };
    
    let respuesta_cabecera = ejecutarAjax("controladores/orden_compra.php", "guardar=" + JSON.stringify(cabecera));
    
    try {
        let json_cabecera = typeof respuesta_cabecera === 'string' ? JSON.parse(respuesta_cabecera) : respuesta_cabecera;
        
        if (json_cabecera.error) {
            mensaje_dialogo_info_ERROR(json_cabecera.error, "Error al guardar orden de compra");
            return;
        }
        
        if (!json_cabecera.success || !json_cabecera.id_orden_compra) {
            mensaje_dialogo_info_ERROR("No se generó ID para la orden de compra", "Error");
            return;
        }
        
        let id_orden_compra = json_cabecera.id_orden_compra;
        console.log("CABECERA -> ID Orden Compra: " + id_orden_compra);
        
        $("#detalles_orden_tb tr").each(function() {
            let id_producto = $(this).find(".producto_id").val();
            let cantidad = $(this).find(".producto_cantidad").val();
            
            if (id_producto && cantidad) {
                let detalle = {
                    orden_compra: id_orden_compra,
                    id_productos: id_producto,
                    cantidad: cantidad
                };
                
                let respuesta_detalle = ejecutarAjax("controladores/detalle_orden.php", "guardar=" + JSON.stringify(detalle));
                console.log("DETALLE -> " + respuesta_detalle);

                try {
                    let json_detalle = typeof respuesta_detalle === 'string' ? JSON.parse(respuesta_detalle) : respuesta_detalle;
                    if (json_detalle && json_detalle.error) {
                        console.error("Error en detalle:", json_detalle.error);
                    }
                } catch (e) {
                    console.error("Error al parsear detalle:", respuesta_detalle);
                }
            }
        });
        
        mensaje_confirmacion("Orden de compra guardada correctamente", "Éxito");
        mostrarListaOrdenesCompra();
        
    } catch (e) {
        console.error("Error al parsear cabecera:", respuesta_cabecera);
        mensaje_dialogo_info_ERROR("Error al procesar la respuesta del servidor", "Error");
    }
}

$(document).on("click", ".eliminar-detalle-orden-btn", function () {
    $(this).closest("tr").remove();
});

function cargarTablaOrdenesCompra() {
    let datos = ejecutarAjax("controladores/orden_compra.php", "listar=1");
    let fila = "";
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
        
        // Validar que sea un array y que tenga datos
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            fila = `<tr><td colspan='6' class='text-center'>No hay registros</td></tr>`;
        } else {
            json_datos.forEach(function (item) {
                fila += `<tr>`;
                fila += `<td>${item.orden_compra}</td>`;
                fila += `<td>${item.fecha_orden}</td>`;
                fila += `<td>${item.proveedor_nombre ? item.proveedor_nombre : ''}</td>`;
                fila += `<td>${item.nombre_usuario ? item.nombre_usuario : ''}</td>`;
                fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
                fila += `<td class='text-end'>`;
                fila += `<button class='btn btn-info btn-sm ver-detalles-orden' data-id='${item.orden_compra}'><i data-feather="eye"></i></button> `;
                fila += `<button class='btn btn-warning btn-sm imprimir-orden' data-id='${item.orden_compra}'><i data-feather="printer"></i></button> `;
                if (item.estado === "ACTIVO") {
                    fila += `<button class='btn btn-danger btn-sm anular-orden' data-id='${item.orden_compra}'><i data-feather="x-circle"></i></button>`;
                }
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    } catch (error) {
        console.error('Error al cargar tabla:', error);
        fila = '<tr><td colspan="5" class="text-center text-danger">Error al cargar los registros</td></tr>';
    }
    
    $("#ordenes_compra_tb").html(fila);
    feather.replace();
}

$(document).on("click", ".ver-detalles-orden", function () {
    let id = $(this).data("id");
    let datos = ejecutarAjax("controladores/orden_compra.php", "obtener_detalles=" + id);
    
    try {
        let json_datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
        
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            mensaje_dialogo_info_ERROR("No hay detalles para esta orden de compra", "Información");
            return;
        }
        
        let detalles_html = "<table class='table table-sm'><thead><tr><th>Producto</th><th>Cantidad</th></tr></thead><tbody>";
        
        json_datos.forEach(function(item) {
            detalles_html += `<tr><td>${item.nombre_producto}</td><td>${item.cantidad}</td></tr>`;
        });
        
        detalles_html += "</tbody></table>";
        
        Swal.fire({
            title: 'Detalles de la Orden #' + id,
            html: detalles_html,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    } catch (error) {
        console.error('Error al obtener detalles:', error);
        mensaje_dialogo_info_ERROR("Error al obtener los detalles", "Error");
    }
});

$(document).on("click", ".anular-orden", function () {
    let id = $(this).data("id");
    Swal.fire({
        title: 'Anular Orden de Compra?',
        text: "¿Desea anular esta orden de compra?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Anular'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarAjax("controladores/orden_compra.php", "anular=" + id);
            mensaje_confirmacion("Orden de compra anulada correctamente", "Éxito");
            cargarTablaOrdenesCompra();
        }
    });
});

$(document).on("click", ".imprimir-orden", function () {
    let id = $(this).data("id");
    if (!id || id <= 0) {
        mensaje_dialogo_info_ERROR("ID de orden inválido", "Error");
        return;
    }
    window.open(`paginas/movimientos/orden_compra/print.php?id=${id}`, '_blank', 'width=900,height=700,menubar=yes,scrollbars=yes');
});

function cancelarOrdenCompra() {
    mostrarListaOrdenesCompra();
}

function guardarOrdenCompraNew() {
    let id_usuario_orden = $("#id_usuario_orden").val();
    let fecha_orden = $("#orden_fecha").val();
    let id_proveedor_orden = $("#orden_proveedor").val() || null;
    let id_presupuesto = $("#orden_presupuesto").val() || null;
    let condiciones_pago = $("#orden_condiciones_pago").val() || '';
    let detalles = [];
    
    // Recolectar detalles del formulario
    $("#detalles_orden_tb tr").each(function() {
        let id_producto = $(this).find("input.producto_id").val();
        let cantidad = $(this).find("input.producto_cantidad").val();
        let costo = $(this).find("input.producto_costo").val() || 0;
        detalles.push({
            id_producto: id_producto,
            cantidad: cantidad
            , precio_unitario: costo
        });
    });
    
    // Validar
    if (!fecha_orden || fecha_orden.trim() === "") {
        alert("La fecha es requerida");
        return;
    }
    if (detalles.length === 0) {
        alert("Debe agregar al menos un producto");
        return;
    }
    if (!id_proveedor_orden || id_proveedor_orden === "0") {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe seleccionar un proveedor' });
        return;
    }
    if (!id_presupuesto || id_presupuesto === "") {
        Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe seleccionar un presupuesto' });
        return;
    }
    
    // Preparar JSON para envío de orden
    let json_datos_orden = JSON.stringify({
        id_usuario: id_usuario_orden,
        fecha_orden: fecha_orden,
        id_proveedor_orden: id_proveedor_orden,
        id_presupuesto: id_presupuesto,
        condiciones_pago: condiciones_pago
    });
    
    // Guardar orden primero
    let datos = ejecutarAjax("controladores/orden_compra.php", "guardar=" + json_datos_orden);
    datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
    
    if (!datos.success) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: datos.error || 'Error al guardar la orden de compra'
        });
        return;
    }
    
    // Si la orden se guardó exitosamente, obtener el ID
    let id_orden = datos.id_orden;
    
    // Guardar detalles
    let error_detalle = false;
    let detalle_error_msg = "";
    
    for (let i = 0; i < detalles.length; i++) {
        let json_detalle = JSON.stringify({
            cantidad: detalles[i].cantidad,
            orden_compra: id_orden,
            id_productos: detalles[i].id_producto,
            precio_unitario: detalles[i].precio_unitario || 0
        });
        
        let respuesta_detalle = ejecutarAjax("controladores/detalle_orden.php", "guardar=" + json_detalle);
        respuesta_detalle = typeof respuesta_detalle === 'string' ? JSON.parse(respuesta_detalle) : respuesta_detalle;
        
        if (!respuesta_detalle.success) {
            error_detalle = true;
            detalle_error_msg = respuesta_detalle.error || "Error al guardar detalles";
            break;
        }
    }
    
    if (error_detalle) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Orden guardada pero: ' + detalle_error_msg
        });
    } else {
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: 'Orden de compra guardada correctamente'
        });
    }
    
    // Volver a la lista
    mostrarListaOrdenesCompra();
}


$(document).on("keyup", "#b_orden_compra", function () {
    let texto = $(this).val();
    if (texto.trim().length === 0) {
        cargarTablaOrdenesCompra();
        return;
    }
    let datos = ejecutarAjax("controladores/orden_compra.php", "buscar=" + texto);
    let fila = "";
    
    try {
        let json_datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
        
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            fila = `<tr><td colspan='6' class='text-center'>No hay registros</td></tr>`;
        } else {
            json_datos.forEach(function (item) {
                fila += `<tr>`;
                fila += `<td>${item.orden_compra}</td>`;
                fila += `<td>${item.fecha_orden}</td>`;
                fila += `<td>${item.proveedor_nombre ? item.proveedor_nombre : ''}</td>`;
                fila += `<td>${item.nombre_usuario ? item.nombre_usuario : ''}</td>`;
                fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
                fila += `<td class='text-end'>`;
                fila += `<button class='btn btn-info btn-sm ver-detalles-orden' data-id='${item.orden_compra}'><i data-feather="eye"></i></button> `;
                fila += `<button class='btn btn-warning btn-sm imprimir-orden' data-id='${item.orden_compra}'><i data-feather="printer"></i></button> `;
                if (item.estado === "ACTIVO") {
                    fila += `<button class='btn btn-danger btn-sm anular-orden' data-id='${item.orden_compra}'><i data-feather="x-circle"></i></button>`;
                }
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    } catch (error) {
        console.error('Error al buscar órdenes:', error);
        fila = '<tr><td colspan="5" class="text-center text-danger">Error al procesar búsqueda</td></tr>';
    }
    
    $("#ordenes_compra_tb").html(fila);
    feather.replace();
});