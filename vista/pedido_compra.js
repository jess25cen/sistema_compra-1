let detalles_temporales = [];

function mostrarListaPedidosCompra() {
    let contenido = dameContenido("paginas/movimientos/pedido_compra/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaPedidosCompra();
}

function mostrarAgregarPedidoCompra() {
    detalles_temporales = [];
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
    
    // Agregar a detalles temporales
    detalles_temporales.push({
        id_productos: id_producto,
        nombre_producto: nombre_producto,
        cantidad: cantidad
    });
    
    // Limpiar campos
    $("#pedido_compra_producto").val("0");
    $("#pedido_compra_cantidad").val("1");
    
    // Actualizar tabla
    renderizarDetallesPedido();
}

function renderizarDetallesPedido() {
    let fila = "";
    if (detalles_temporales.length === 0) {
        fila = `<tr><td colspan='3' class='text-center'>No hay productos agregados</td></tr>`;
    } else {
        detalles_temporales.forEach(function(item, index) {
            fila += `<tr>`;
            fila += `<td>${item.nombre_producto}</td>`;
            fila += `<td>${item.cantidad}</td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-danger btn-sm' onclick="eliminarDetalle(${index}); return false;">Eliminar</button>`;
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#detalles_pedido_tb").html(fila);
}

function eliminarDetalle(index) {
    detalles_temporales.splice(index, 1);
    renderizarDetallesPedido();
}

function guardarPedidoCompra() {
    if ($("#pedido_compra_usuario").val() === "0") {
        mensaje_dialogo_info_ERROR("Debes seleccionar un usuario", "ATENCIÓN");
        return;
    }
    
    if (detalles_temporales.length === 0) {
        mensaje_dialogo_info_ERROR("Debes agregar al menos un producto", "ATENCIÓN");
        return;
    }
    
    let cabecera = {
        fecha_compra: $("#pedido_compra_fecha").val(),
        id_usuario: $("#pedido_compra_usuario").val(),
        detalles: detalles_temporales
    };
    
    ejecutarAjax("controladores/pedido_compra.php", "guardar=" + JSON.stringify(cabecera));
    mensaje_confirmacion("Pedido guardado correctamente", "Éxito");
    mostrarListaPedidosCompra();
}

function cargarTablaPedidosCompra() {
    let datos = ejecutarAjax("controladores/pedido_compra.php", "listar=1");
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='5' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.pedido_compra}</td>`;
            fila += `<td>${item.fecha_compra}</td>`;
            fila += `<td>${item.nombre_usuario ? item.nombre_usuario : ''}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-info btn-sm ver-detalles-pedido' data-id='${item.pedido_compra}'><i data-feather="eye"></i></button> `;
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
    
    let json_datos = JSON.parse(datos);
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

function imprimirPedidoCompra() {
    mensaje_dialogo_info_ERROR("Función de impresión en desarrollo", "Información");
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
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.pedido_compra}</td>`;
            fila += `<td>${item.fecha_compra}</td>`;
            fila += `<td>${item.nombre_usuario ? item.nombre_usuario : ''}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-info btn-sm ver-detalles-pedido' data-id='${item.pedido_compra}'><i data-feather="eye"></i></button> `;
            if (item.estado === "ACTIVO") {
                fila += `<button class='btn btn-danger btn-sm anular-pedido' data-id='${item.pedido_compra}'><i data-feather="x-circle"></i></button>`;
            }
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#pedidos_compra_tb").html(fila);
    feather.replace();
});
