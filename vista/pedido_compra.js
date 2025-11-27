function mostrarListaPedidosCompra() {
    let contenido = dameContenido("paginas/movimientos/pedido_compra/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaPedidosCompra();
}

function mostrarAgregarPedidoCompra() {
    let contenido = dameContenido("paginas/movimientos/pedido_compra/agregar.php");
    $("#contenido-principal").html(contenido);
    cargarListaProductosPedidoCompra();
}

function cargarListaProductosPedidoCompra() {
    let productos = ejecutarAjax("controladores/producto.php", "listar=1");
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_productos = typeof productos === 'string' ? JSON.parse(productos) : productos;
        
        if (!Array.isArray(json_productos)) {
            json_productos = [];
        }
        
        // Limpiar opciones previas excepto la opción por defecto
        $("#material_lst").find("option:not(:first)").remove();
        
        json_productos.forEach(function(item) {
            $("#material_lst").append(`<option value="${item.id_productos}">${item.nombre_producto}</option>`);
        });
    } catch (error) {
        console.error('Error al cargar productos:', error);
        mensaje_dialogo_info_ERROR("Error al cargar la lista de productos", "Error");
    }
}

function agregarDetallePedidoCompra() {
    let id_producto = $("#pedido_compra_producto").val();
    let cantidad = $("#pedido_compra_cantidad").val();
    
    if (id_producto === "0" || id_producto.trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes seleccionar un producto", "ATENCIÓN");
        return;
    }
    
    if (!cantidad || cantidad <= 0) {
        mensaje_dialogo_info_ERROR("La cantidad debe ser mayor a 0", "ATENCIÓN");
        return;
    }

    let nombre_producto = $("#pedido_compra_producto option:selected").text();
    
    let fila = `<tr>`;
    fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${nombre_producto}</td>`;
    fila += `<td><input type="hidden" class="producto_cantidad" value="${cantidad}">${cantidad}</td>`;
    fila += `<td class='text-end'>`;
    fila += `<button class='btn btn-danger btn-sm eliminar-detalle-pedido-btn' type="button"><i data-feather="trash-2"></i></button>`;
    fila += `</td>`;
    fila += `</tr>`;
    
    $("#detalles_pedido_tb").append(fila);
    feather.replace();
    
    $("#pedido_compra_producto").val("0");
    $("#pedido_compra_cantidad").val("1");
}

function guardarPedidoCompra() {
    if ($("#pedido_compra_usuario").val() === "0") {
        mensaje_dialogo_info_ERROR("Debes seleccionar un usuario", "ATENCIÓN");
        return;
    }
    
    let detalles = [];
    $("#detalles_pedido_tb tr").each(function() {
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
        mensaje_dialogo_info_ERROR("Debes agregar al menos un producto", "ATENCIÓN");
        return;
    }
    
    let cabecera = {
        fecha_compra: $("#pedido_compra_fecha").val(),
        id_usuario: $("#pedido_compra_usuario").val(),
        estado: 'ACTIVO'
    };
    
    let respuesta_cabecera = ejecutarAjax("controladores/pedido_compra.php", "guardar=" + JSON.stringify(cabecera));
    
    try {
        let json_cabecera = JSON.parse(respuesta_cabecera);
        
        if (json_cabecera.error) {
            mensaje_dialogo_info_ERROR(json_cabecera.error, "Error al guardar pedido");
            return;
        }
        
        if (!json_cabecera.success || !json_cabecera.id_pedido_compra) {
            mensaje_dialogo_info_ERROR("No se generó ID para el pedido", "Error");
            return;
        }
        
        let id_pedido = json_cabecera.id_pedido_compra;
        
        // Guardar detalles
        for (let i = 0; i < detalles.length; i++) {
            let det = detalles[i];
            let payload = { pedido_compra: id_pedido, id_productos: det.id_productos, cantidad: det.cantidad };
            let resp = ejecutarAjax("controladores/detalle_pedido.php", "guardar=" + JSON.stringify(payload));
            let json_det = JSON.parse(resp);
            if (json_det.error) {
                console.error('Error guardando detalle:', json_det);
            }
        }
        
        mensaje_confirmacion("Pedido guardado correctamente", "Éxito");
        mostrarListaPedidosCompra();
    } catch (error) {
        mensaje_dialogo_info_ERROR("Error al guardar pedido: " + error.message, "Error");
    }
}

function cancelarPedidoCompra() {
    mostrarListaPedidosCompra();
}

$(document).on('click', '.eliminar-detalle-pedido-btn', function() {
    $(this).closest('tr').remove();
});

function cargarTablaPedidosCompra() {
    let datos = ejecutarAjax("controladores/pedido_compra.php", "listar=1");
    let fila = "";
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
        
        // Validar que sea un array y que tenga datos
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            fila = '<tr><td colspan="5" class="text-center text-muted">No hay registros</td></tr>';
        } else {
            json_datos.forEach(function(item) {
                fila += `<tr>`;
                fila += `<td>${item.pedido_compra}</td>`;
                fila += `<td>${item.fecha_compra}</td>`;
                fila += `<td>${item.nombre_usuario || ''}</td>`;
                fila += `<td><span class="badge bg-label-${item.estado === 'ACTIVO' ? 'success' : 'danger'}">${item.estado}</span></td>`;
                fila += `<td>`;
                fila += `<button class='btn btn-sm btn-info' onclick="verDetallesPedido(${item.pedido_compra})"><i data-feather="eye"></i></button> `;
                if (item.estado === 'ACTIVO') {
                    fila += `<button class='btn btn-sm btn-danger' onclick="anularPedido(${item.pedido_compra})"><i data-feather="x-circle"></i></button> `;
                }
                fila += `<button class='btn btn-sm btn-primary' onclick="imprimirPedido(${item.pedido_compra})"><i data-feather="printer"></i></button>`;
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    } catch (error) {
        console.error('Error al cargar tabla:', error);
        fila = '<tr><td colspan="5" class="text-center text-danger">Error al cargar los registros</td></tr>';
    }
    
    $("#pedidos_compra_tb").html(fila);
    feather.replace();
}

function verDetallesPedido(id) {
    let respuesta = ejecutarAjax("controladores/pedido_compra.php", "obtener_detalles=" + id);
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_detalles = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;
        
        if (json_detalles === "0" || !Array.isArray(json_detalles)) {
            json_detalles = [];
        }
        
        let detalles_html = `<table class='table table-sm'><thead><tr><th>Producto</th><th>Cantidad</th></tr></thead><tbody>`;
        
        json_detalles.forEach(function(detalle) {
            detalles_html += `<tr><td>${detalle.nombre_producto || ''}</td><td>${detalle.cantidad}</td></tr>`;
        });
        
        detalles_html += `</tbody></table>`;
        
        Swal.fire({
            title: `Detalles Pedido #${id}`,
            html: detalles_html,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    } catch (error) {
        mensaje_dialogo_info_ERROR("Error al obtener detalles: " + error.message, "Error");
    }
}

function anularPedido(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: '¿Deseas anular este pedido?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, anular',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            let respuesta = ejecutarAjax("controladores/pedido_compra.php", "anular=" + id);
            
            try {
                let json_respuesta = JSON.parse(respuesta);
                
                if (json_respuesta.success) {
                    mensaje_confirmacion("Pedido anulado correctamente", "Éxito");
                    cargarTablaPedidosCompra();
                } else {
                    mensaje_dialogo_info_ERROR(json_respuesta.error || "Error al anular", "Error");
                }
            } catch (error) {
                mensaje_dialogo_info_ERROR("Error: " + error.message, "Error");
            }
        }
    });
}

function imprimirPedido(id) {
    window.open(`paginas/movimientos/pedido_compra/print.php?id=${id}`, '_blank');
}

function buscarPedidoCompra() {
    let texto = $("#buscar_pedido_compra").val();
    
    if (texto.trim().length === 0) {
        cargarTablaPedidosCompra();
        return;
    }
    
    let respuesta = ejecutarAjax("controladores/pedido_compra.php", "buscar=" + encodeURIComponent(texto));
    let fila = "";
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_datos = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;
        
        // Validar que sea un array y que tenga datos
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            fila = '<tr><td colspan="5" class="text-center text-muted">No se encontraron resultados</td></tr>';
        } else {
            json_datos.forEach(function(item) {
                fila += `<tr>`;
                fila += `<td>${item.pedido_compra}</td>`;
                fila += `<td>${item.fecha_compra}</td>`;
                fila += `<td>${item.nombre_usuario || ''}</td>`;
                fila += `<td><span class="badge bg-label-${item.estado === 'ACTIVO' ? 'success' : 'danger'}">${item.estado}</span></td>`;
                fila += `<td>`;
                fila += `<button class='btn btn-sm btn-info' onclick="verDetallesPedido(${item.pedido_compra})"><i data-feather="eye"></i></button> `;
                if (item.estado === 'ACTIVO') {
                    fila += `<button class='btn btn-sm btn-danger' onclick="anularPedido(${item.pedido_compra})"><i data-feather="x-circle"></i></button> `;
                }
                fila += `<button class='btn btn-sm btn-primary' onclick="imprimirPedido(${item.pedido_compra})"><i data-feather="printer"></i></button>`;
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    } catch (error) {
        console.error('Error al buscar:', error);
        fila = '<tr><td colspan="5" class="text-center text-danger">Error al buscar registros</td></tr>';
    }
    
    $("#pedidos_compra_tb").html(fila);
    feather.replace();
}
