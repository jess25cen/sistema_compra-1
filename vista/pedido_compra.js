function mostrarListaPedidosCompra() {
    let contenido = dameContenido("paginas/movimientos/pedido_compra/listar.php");
    $(".contenido-principal").html(contenido);
    cargarTablaPedidosCompra();
}




function mostrarAgregarPedidoCompra() {
    let contenido = dameContenido("paginas/movimientos/pedido_compra/agregar.php");
    $("#contenido-principal").html(contenido);
}

function agregarDetalle() {
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
    
    // Obtener nombre del producto
    let nombre_producto = $("#pedido_compra_producto option:selected").text();
    
    // Agregar fila a la tabla
    let fila = `<tr>`;
    fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${nombre_producto}</td>`;
    fila += `<td><input type="hidden" class="producto_cantidad" value="${cantidad}">${cantidad}</td>`;
    fila += `<td class='text-end'>`;
    fila += `<button class='btn btn-danger btn-sm eliminar-detalle-btn' type="button"><i data-feather="trash-2"></i></button>`;
    fila += `</td>`;
    fila += `</tr>`;
    
    $("#detalles_pedido_tb").append(fila);
    feather.replace();
    
    // Limpiar campos
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
    
    // Cabecera del pedido
    let cabecera = {
        fecha_compra: $("#pedido_compra_fecha").val(),        id_usuario: $("#pedido_compra_usuario").val(),
        estado: 'ACTIVO'
    };
    
    // Guardar la cabecera primero
    let respuesta_cabecera = ejecutarAjax("controladores/pedido_compra.php", "guardar=" + JSON.stringify(cabecera));

    try {
        let json_cabecera = parseJSONSafe(respuesta_cabecera);
        
        if (json_cabecera.error) {
            mensaje_dialogo_info_ERROR(json_cabecera.error, "Error al guardar pedido");
            return;
        }
        
        if (!json_cabecera.success || !json_cabecera.id_pedido) {
            mensaje_dialogo_info_ERROR("No se generó ID para el pedido", "Error");
            return;
        }
        
        let id_pedido = json_cabecera.id_pedido;
        console.log("CABECERA -> ID Pedido: " + id_pedido);
        
        // Guardar detalles uno por uno
        $("#detalles_pedido_tb tr").each(function() {
            let id_producto = $(this).find(".producto_id").val();
            let cantidad = $(this).find(".producto_cantidad").val();
            
            if (id_producto && cantidad) {
                let detalle = {
                    pedido_compra: id_pedido,
                    id_productos: id_producto,
                    cantidad: cantidad
                };
                
                let respuesta_detalle = ejecutarAjax("controladores/detalle_pedido.php", "guardar=" + JSON.stringify(detalle));
                console.log("DETALLE -> " + respuesta_detalle);

                try {
                    let json_detalle = parseJSONSafe(respuesta_detalle);
                    if (json_detalle && json_detalle.error) {
                        console.error("Error en detalle:", json_detalle.error);
                    }
                } catch (e) {
                    console.error("Error al parsear detalle:", respuesta_detalle);
                }
            }
        });
        
        mensaje_confirmacion("Pedido guardado correctamente", "Éxito");
        mostrarListaPedidosCompra();
        
    } catch (e) {
        console.error("Error al parsear cabecera:", respuesta_cabecera);
        mensaje_dialogo_info_ERROR("Error al procesar la respuesta del servidor", "Error");
    }
}

$(document).on("click", ".eliminar-detalle-btn", function () {
    $(this).closest("tr").remove();
});

function cargarTablaPedidosCompra() {
    let datos = ejecutarAjax("controladores/pedido_compra.php", "listar=1");
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='5' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = parseJSONSafe(datos);
        if (json_datos === "0") {
            fila = `<tr><td colspan='5' class='text-center'>No hay registros</td></tr>`;
        } else {
            json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.pedido_compra}</td>`;
            fila += `<td>${item.fecha_compra}</td>`;
            fila += `<td>${item.nombre_usuario ? item.nombre_usuario : ''}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-info btn-sm ver-detalles-pedido' data-id='${item.pedido_compra}'><i data-feather="eye"></i></button> `;
            fila += `<button class='btn btn-warning btn-sm imprimir-pedido' data-id='${item.pedido_compra}'><i data-feather="printer"></i></button> `;
            fila += `<button class='btn btn-primary btn-sm crear-comparador' data-id='${item.pedido_compra}'>Comparador</button> `;
            if (item.estado === "ACTIVO") {
                fila += `<button class='btn btn-danger btn-sm anular-pedido' data-id='${item.pedido_compra}'><i data-feather="x-circle"></i></button>`;
            }
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#pedidos_compra_tb").html(fila);
    feather.replace();
}

$(document).on("click", ".ver-detalles-pedido", function () {
    let id = $(this).data("id");
    let datos = ejecutarAjax("controladores/pedido_compra.php", "obtener_detalles=" + id);
    
    if (datos === "0") {
        mensaje_dialogo_info_ERROR("No hay detalles para este pedido", "Información");
        return;
    }
    
    let json_datos = parseJSONSafe(datos);
    let detalles_html = "<table class='table table-sm'><thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th></tr></thead><tbody>";
    
    json_datos.forEach(function(item) {
        let subtotal = (item.cantidad * item.precio).toFixed(2);
        detalles_html += `<tr><td>${item.nombre_producto}</td><td>${item.cantidad}</td><td>${item.precio}</td></tr>`;
    });
    
    detalles_html += "</tbody></table>";
    
    Swal.fire({
        title: 'Detalles del Pedido #' + id,
        html: detalles_html,
        icon: 'info',
        confirmButtonText: 'Cerrar'
    });
});

$(document).on("click", ".anular-pedido", function () {
    let id = $(this).data("id");
    Swal.fire({
        title: 'Anular Pedido?',
        text: "¿Desea anular este pedido de compra?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Anular'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarAjax("controladores/pedido_compra.php", "anular=" + id);
            mensaje_confirmacion("Pedido anulado correctamente", "Éxito");
            cargarTablaPedidosCompra();
        }
    });
});

$(document).on("click", ".imprimir-pedido", function () {
    let id = $(this).data("id");
    imprimirPedidoCompra(id);
});

// Crear comparador a partir de un pedido
$(document).on("click", ".crear-comparador", function () {
    let id = $(this).data("id");
    crearComparadorDesdePedido(id);
});

function crearComparadorDesdePedido(id_pedido) {
    if (!id_pedido) {
        mensaje_dialogo_info_ERROR("Debe seleccionar un pedido válido", "Atención");
        return;
    }

    let datos = ejecutarAjax("controladores/pedido_compra.php", "obtener_detalles=" + id_pedido);
    if (datos === "0") {
        mensaje_dialogo_info_ERROR("No hay detalles para este pedido", "Información");
        return;
    }

    try {
        let detalles = parseJSONSafe(datos);

        // Cargar la vista del comparador
        let contenido = dameContenido("paginas/movimientos/comparador_presupuesto/agregar.php");
        $("#contenido-principal").html(contenido);

        setTimeout(function() {
            if ($('#comparador_fecha').length) {
                let hoy = new Date();
                let fecha = hoy.toISOString().split('T')[0];
                if (!$('#comparador_fecha').val()) $('#comparador_fecha').val(fecha);
            }

            if (Array.isArray(detalles) && detalles.length > 0) {
                detalles.forEach(function(item) {
                    let fila = `<tr>`;
                    fila += `<td><input type="hidden" class="producto_id" value="${item.id_productos}">${item.nombre_producto}</td>`;
                    fila += `<td><input type="hidden" class="producto_cantidad" value="${item.cantidad}">${item.cantidad}</td>`;
                    fila += `<td class='text-end'>`;
                    fila += `<button class='btn btn-danger btn-sm eliminar-detalle-comp-btn' type="button"><i data-feather="trash-2"></i></button>`;
                    fila += `</td>`;
                    fila += `</tr>`;
                    $("#detalles_comparador_tb").append(fila);
                });
                feather.replace();
            }
        }, 120);

    } catch (e) {
        console.error('Error al parsear detalles del pedido:', datos);
        mensaje_dialogo_info_ERROR("Error al procesar los detalles del pedido", "Error");
    }
}

function imprimirPedidoCompra(id_pedido) {
    if (!id_pedido) {
        mensaje_dialogo_info_ERROR("Debes seleccionar un pedido para imprimir", "Atención");
        return;
    }
    window.open("paginas/movimientos/pedido_compra/print.php?id=" + id_pedido, "_blank");
}

function cancelarPedidoCompra() {
    mostrarListaPedidosCompra();
}

$(document).on("keyup", "#b_pedido_compra", function () {
    let texto = $(this).val();
    if (texto.trim().length === 0) {
        cargarTablaPedidosCompra();
        return;
    }
    let datos = ejecutarAjax("controladores/pedido_compra.php", "buscar=" + texto);
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='5' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = parseJSONSafe(datos);
        if (json_datos === "0") {
            fila = `<tr><td colspan='5' class='text-center'>No hay registros</td></tr>`;
        } else {
            json_datos.map(function (item) {
                fila += `<tr>`;
                fila += `<td>${item.pedido_compra}</td>`;
                fila += `<td>${item.fecha_compra}</td>`;
                fila += `<td>${item.nombre_usuario ? item.nombre_usuario : ''}</td>`;
                fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
                fila += `<td class='text-end'>`;
                fila += `<button class='btn btn-info btn-sm ver-detalles-pedido' data-id='${item.pedido_compra}'><i data-feather="eye"></i></button> `;
                fila += `<button class='btn btn-warning btn-sm imprimir-pedido' data-id='${item.pedido_compra}'><i data-feather="printer"></i></button> `;
                fila += `<button class='btn btn-primary btn-sm crear-comparador' data-id='${item.pedido_compra}'>Comparador</button> `;
                if (item.estado === "ACTIVO") {
                    fila += `<button class='btn btn-danger btn-sm anular-pedido' data-id='${item.pedido_compra}'><i data-feather="x-circle"></i></button>`;
                }
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    }
    $("#pedidos_compra_tb").html(fila);
    feather.replace();
});