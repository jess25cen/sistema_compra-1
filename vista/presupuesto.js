function mostrarListaPresupuestos() {
    let contenido = dameContenido("paginas/movimientos/presupuesto/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaPresupuestos();
}

function mostrarAgregarPresupuesto() {
    let contenido = dameContenido("paginas/movimientos/presupuesto/agregar.php");
    $("#contenido-principal").html(contenido);
    
    // Establecer fecha de hoy
    let hoy = new Date().toISOString().split('T')[0];
    $("#presupuesto_fecha").val(hoy);
    
    cargarListaPedidosCompra();
    cargarListaProveedores();
    cargarListaProductosPresupuesto();
}

function cargarListaPedidosCompra() {
    let pedidos = ejecutarAjax("controladores/pedido_compra.php", "listar=1");
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_pedidos = typeof pedidos === 'string' ? JSON.parse(pedidos) : pedidos;
        
        if (!Array.isArray(json_pedidos)) {
            json_pedidos = [];
        }
        
        // Limpiar opciones previas excepto la opción por defecto
        $("#presupuesto_pedido_compra").find("option:not(:first)").remove();
        
        json_pedidos.forEach(function(item) {
            $("#presupuesto_pedido_compra").append(`<option value="${item.pedido_compra}">Pedido #${item.pedido_compra} - ${item.fecha_compra}</option>`);
        });
    } catch (error) {
        console.error('Error al cargar pedidos compra:', error);
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
        $("#presupuesto_proveedor").find("option:not(:first)").remove();
        
        json_proveedores.forEach(function(item) {
            $("#presupuesto_proveedor").append(`<option value="${item.id_proveedor}">${item.nombre}</option>`);
        });
    } catch (error) {
        console.error('Error al cargar proveedores:', error);
    }
}

function cargarListaProductosPresupuesto() {
    let productos = ejecutarAjax("controladores/producto.php", "listar=1");
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_productos = typeof productos === 'string' ? JSON.parse(productos) : productos;
        
        if (!Array.isArray(json_productos)) {
            json_productos = [];
        }
        
        // Limpiar opciones previas excepto la opción por defecto
        $("#presupuesto_producto").find("option:not(:first)").remove();
        
        json_productos.forEach(function(item) {
            $("#presupuesto_producto").append(`<option value="${item.id_productos}">${item.nombre_producto}</option>`);
        });
    } catch (error) {
        console.error('Error al cargar productos:', error);
    }
}

function cargarDetallesPedidoCompra() {
    let pedido_id = $("#presupuesto_pedido_compra").val();
    
    if (pedido_id === "0" || pedido_id.trim().length === 0) {
        // Limpiar tabla de detalles si no hay pedido seleccionado
        $("#detalles_presupuesto_tb").html("");
        return;
    }
    
    // Obtener detalles del pedido compra
    let detalles = ejecutarAjax("controladores/pedido_compra.php", "obtener_detalles=" + pedido_id);
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_detalles = typeof detalles === 'string' ? JSON.parse(detalles) : detalles;
        
        if (!Array.isArray(json_detalles) || json_detalles.length === 0) {
            mensaje_dialogo_info_ERROR("No hay detalles para este pedido", "Información");
            $("#detalles_presupuesto_tb").html("");
            return;
        }
        
        // Limpiar tabla y cargar detalles
        $("#detalles_presupuesto_tb").html("");
        let contador = 1;
        
        json_detalles.forEach(function(item) {
            let fila = `<tr>`;
            fila += `<td>${contador}</td>`;
            fila += `<td><input type="hidden" class="producto_id" value="${item.id_productos}">${item.nombre_producto}</td>`;
            fila += `<td><input type="hidden" class="producto_cantidad" value="${item.cantidad}">${item.cantidad}</td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-danger btn-sm eliminar-detalle-presupuesto-btn' type="button"><i data-feather="trash-2"></i></button>`;
            fila += `</td>`;
            fila += `</tr>`;
            
            $("#detalles_presupuesto_tb").append(fila);
            contador++;
        });
        
        feather.replace();
    } catch (error) {
        console.error('Error al cargar detalles:', error);
        mensaje_dialogo_info_ERROR("Error al cargar detalles del pedido", "Error");
    }
}

function agregarTablaPresupuestoCompra() {
    let id_producto = $("#presupuesto_producto").val();
    let cantidad = $("#presupuesto_cantidad").val();
    
    if (id_producto === "0" || id_producto.trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes seleccionar un producto", "ATENCIÓN");
        return;
    }
    
    if (!cantidad || cantidad <= 0) {
        mensaje_dialogo_info_ERROR("La cantidad debe ser mayor a 0", "ATENCIÓN");
        return;
    }

    let nombre_producto = $("#presupuesto_producto option:selected").text();
    let contador = $("#detalles_presupuesto_tb tr").length + 1;
    
    let fila = `<tr>`;
    fila += `<td>${contador}</td>`;
    fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${nombre_producto}</td>`;
    fila += `<td><input type="hidden" class="producto_cantidad" value="${cantidad}">${cantidad}</td>`;
    fila += `<td class='text-end'>`;
    fila += `<button class='btn btn-danger btn-sm eliminar-detalle-presupuesto-btn' type="button"><i data-feather="trash-2"></i></button>`;
    fila += `</td>`;
    fila += `</tr>`;
    
    $("#detalles_presupuesto_tb").append(fila);
    feather.replace();
    
    // Limpiar campos
    $("#presupuesto_producto").val("0");
    $("#presupuesto_cantidad").val("1");
}

function guardarPresupuestoCompra() {
    if (!$("#id_usuario_presupuesto").val()) {
        mensaje_dialogo_info_ERROR("Usuario no cargado", "ATENCIÓN");
        return;
    }
    
    if ($("#presupuesto_proveedor").val() === "0") {
        mensaje_dialogo_info_ERROR("Debes seleccionar un proveedor", "ATENCIÓN");
        return;
    }
    
    let detalles = [];
    $("#detalles_presupuesto_tb tr").each(function() {
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
        fecha_presupuesto: $("#presupuesto_fecha").val(),
        id_usuario: $("#id_usuario_presupuesto").val(),
        id_proveedor: $("#presupuesto_proveedor").val(),
        estado: 'ACTIVO',
        pedido_compra: $("#presupuesto_pedido_compra").val() || null
    };
    
    let respuesta_cabecera = ejecutarAjax("controladores/presupuesto.php", "guardar=" + JSON.stringify(cabecera));
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_cabecera = typeof respuesta_cabecera === 'string' ? JSON.parse(respuesta_cabecera) : respuesta_cabecera;
        
        if (json_cabecera.error) {
            mensaje_dialogo_info_ERROR(json_cabecera.error, "Error al guardar presupuesto");
            return;
        }
        
        if (!json_cabecera.success || !json_cabecera.id_presupuesto) {
            mensaje_dialogo_info_ERROR("No se generó ID para el presupuesto", "Error");
            return;
        }
        
        let id_presupuesto = json_cabecera.id_presupuesto;
        console.log("CABECERA -> ID Presupuesto: " + id_presupuesto);
        
        $("#detalles_presupuesto_tb tr").each(function() {
            let id_producto = $(this).find(".producto_id").val();
            let cantidad = $(this).find(".producto_cantidad").val();
            
            if (id_producto && cantidad) {
                let detalle = {
                    id_presupuesto: id_presupuesto,
                    id_productos: id_producto,
                    cantidad: cantidad
                };
                
                let respuesta_detalle = ejecutarAjax("controladores/detalle_presupuesto.php", "guardar=" + JSON.stringify(detalle));
                console.log("DETALLE -> " + respuesta_detalle);
                
                try {
                    // Manejar tanto strings JSON como objetos ya parseados
                    let json_detalle = typeof respuesta_detalle === 'string' ? JSON.parse(respuesta_detalle) : respuesta_detalle;
                    if (json_detalle.error) {
                        console.error("Error en detalle:", json_detalle.error);
                    }
                } catch (e) {
                    console.error("Error al parsear detalle:", respuesta_detalle);
                }
            }
        });
        
        mensaje_confirmacion("Presupuesto guardado correctamente", "Éxito");
        mostrarListaPresupuestos();
        
    } catch (e) {
        console.error("Error al parsear cabecera:", respuesta_cabecera);
        mensaje_dialogo_info_ERROR("Error al procesar la respuesta del servidor", "Error");
    }
}

$(document).on("click", ".eliminar-detalle-presupuesto-btn", function () {
    $(this).closest("tr").remove();
});

function cargarTablaPresupuestos() {
    let datos = ejecutarAjax("controladores/presupuesto.php", "listar=1");
    let fila = "";
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
        
        // Validar que sea un array y que tenga datos
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            fila = `<tr><td colspan='7' class='text-center text-muted'>No hay registros</td></tr>`;
        } else {
            json_datos.forEach(function(item) {
                fila += `<tr>`;
                fila += `<td>${item.id_presupuesto}</td>`;
                fila += `<td>${item.fecha_presupuesto}</td>`;
                fila += `<td>${item.nombre_usuario || ''}</td>`;
                fila += `<td>${item.nombre_proveedor || ''}</td>`;
                fila += `<td>${item.pedido_compra || '-'}</td>`;
                fila += `<td><span class="badge bg-label-${item.estado === 'ACTIVO' ? 'success' : 'danger'}">${item.estado}</span></td>`;
                fila += `<td>`;
                fila += `<button class='btn btn-sm btn-info' onclick="verDetallesPresupuesto(${item.id_presupuesto})"><i data-feather="eye"></i></button> `;
                if (item.estado === 'ACTIVO') {
                    fila += `<button class='btn btn-sm btn-danger' onclick="anularPresupuesto(${item.id_presupuesto})"><i data-feather="x-circle"></i></button> `;
                }
                fila += `<button class='btn btn-sm btn-primary' onclick="imprimirPresupuesto(${item.id_presupuesto})"><i data-feather="printer"></i></button>`;
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    } catch (error) {
        console.error('Error al cargar tabla:', error);
        fila = '<tr><td colspan="7" class="text-center text-danger">Error al cargar los registros</td></tr>';
    }
    
    $("#presupuestos_tb").html(fila);
    feather.replace();
}

function verDetallesPresupuesto(id) {
    let datos = ejecutarAjax("controladores/presupuesto.php", "obtener_detalles=" + id);
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
        
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            mensaje_dialogo_info_ERROR("No hay detalles para este presupuesto", "Información");
            return;
        }
        
        let detalles_html = "<table class='table table-sm'><thead><tr><th>Producto</th><th>Cantidad</th></tr></thead><tbody>";
        
        json_datos.forEach(function(item) {
            detalles_html += `<tr><td>${item.nombre_producto}</td><td>${item.cantidad}</td></tr>`;
        });
        
        detalles_html += "</tbody></table>";
        
        Swal.fire({
            title: 'Detalles del Presupuesto #' + id,
            html: detalles_html,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    } catch (error) {
        console.error('Error al obtener detalles:', error);
        mensaje_dialogo_info_ERROR("Error al obtener los detalles", "Error");
    }
}

function anularPresupuesto(id) {
    Swal.fire({
        title: 'Anular Presupuesto?',
        text: "¿Desea anular este presupuesto?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Anular'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarAjax("controladores/presupuesto.php", "anular=" + id);
            mensaje_confirmacion("Presupuesto anulado correctamente", "Éxito");
            cargarTablaPresupuestos();
        }
    });
}

function imprimirPresupuesto(id) {
    if (!id) {
        mensaje_dialogo_info_ERROR("Debes seleccionar un presupuesto para imprimir", "Atención");
        return;
    }
    window.open("paginas/movimientos/presupuesto/print.php?id=" + id, "_blank");
}

function cancelarPresupuestoCompra() {
    mostrarListaPresupuestos();
}

$(document).on("keyup", "#b_presupuesto", function () {
    let texto = $(this).val();
    if (texto.trim().length === 0) {
        cargarTablaPresupuestos();
        return;
    }
    let datos = ejecutarAjax("controladores/presupuesto.php", "buscar=" + texto);
    let fila = "";
    
    try {
        // Manejar tanto strings JSON como objetos ya parseados
        let json_datos = typeof datos === 'string' ? JSON.parse(datos) : datos;
        
        if (!Array.isArray(json_datos) || json_datos.length === 0) {
            fila = `<tr><td colspan='7' class='text-center'>No hay registros</td></tr>`;
        } else {
            json_datos.forEach(function (item) {
                fila += `<tr>`;
                fila += `<td>${item.id_presupuesto}</td>`;
                fila += `<td>${item.fecha_presupuesto || item.fecha || ''}</td>`;
                fila += `<td>${item.nombre_usuario ? item.nombre_usuario : ''}</td>`;
                fila += `<td>${item.nombre_proveedor ? item.nombre_proveedor : ''}</td>`;
                fila += `<td>${item.pedido_compra || '-'}</td>`;
                fila += `<td><span class="badge bg-label-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
                fila += `<td class='text-end'>`;
                fila += `<button class='btn btn-info btn-sm' onclick="verDetallesPresupuesto(${item.id_presupuesto})"><i data-feather="eye"></i></button> `;
                fila += `<button class='btn btn-primary btn-sm' onclick="imprimirPresupuesto(${item.id_presupuesto})"><i data-feather="printer"></i></button> `;
                if (item.estado === "ACTIVO") {
                    fila += `<button class='btn btn-danger btn-sm' onclick="anularPresupuesto(${item.id_presupuesto})"><i data-feather="x-circle"></i></button>`;
                }
                fila += `</td>`;
                fila += `</tr>`;
            });
        }
    } catch (error) {
        console.error('Error al buscar presupuestos:', error);
        fila = '<tr><td colspan="7" class="text-center text-danger">Error al procesar búsqueda</td></tr>';
    }
    
    $("#presupuestos_tb").html(fila);
    feather.replace();
});