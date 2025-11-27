<?php
// Obtener usuario de la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$ses_id = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
$ses_name = isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : (isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : 'Usuario');
?>

<div class="container-fluid card" style="padding: 30px; height: auto;">
    <input type="text" id="id_pedido" hidden value="0">
    <div class="row">
        <div class="col-md-12">
            <h3>Agregar un nuevo Pedido de Compra</h3>
        </div>
        <div class="col-md-12">
            <hr> 
        </div>

        <!-- Fila 1: Usuario y Fecha -->
        <div class="col-md-6">
            <label for="usuario_lst">Usuario</label>
            <input type="text" id="usuario_lst" class="form-control" readonly value="<?php echo htmlspecialchars($ses_name); ?>">
            <input type="hidden" id="id_usuario_pedido" value="<?php echo $ses_id; ?>">
        </div>
        <div class="col-md-6">
            <label for="fecha_pedido">Fecha de Pedido</label>
            <input type="date" id="fecha_pedido" class="form-control">
        </div>

        <div class="col-md-12">
            <hr> 
        </div>

        <!-- Fila 2: Producto, Cantidad, Agregar -->
        <div class="col-md-6">
            <label for="material_lst">Producto</label>
            <select id="material_lst" class="form-control">
                <option value="0">-- Seleccionar --</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="cantidad">Cantidad</label>
            <input type="number" id="cantidad" class="form-control" value="1" min="1">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="form-control btn btn-primary" onclick="agregarTablaPedidoCompra(); return false;">
                <i data-feather="plus"></i> Agregar
            </button>
        </div>

        <div class="col-md-12">
            <hr> 
        </div>

        <!-- Tabla de Detalles -->
        <div class="col-md-12">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="pedido_compra_tb">
                </tbody>
            </table>
        </div>

        <div class="col-md-12">
            <hr> 
        </div>

        <!-- Botones de Acción -->
        <div class="col-md-3">
            <button class="form-control btn btn-success" onclick="guardarPedidoCompra(); return false;">
                <i data-feather="save"></i> Guardar
            </button>
        </div>
        <div class="col-md-3">
            <button class="form-control btn btn-danger" onclick="cancelarPedidoCompra(); return false;">
                <i data-feather="x"></i> Cancelar
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Establecer fecha actual
  let hoy = new Date();
  let fecha_formateada = hoy.getFullYear() + '-' + 
                        String(hoy.getMonth() + 1).padStart(2, '0') + '-' + 
                        String(hoy.getDate()).padStart(2, '0');
  $("#fecha_pedido").val(fecha_formateada);

  // Los productos se cargan desde cargarListaProductosPedidoCompra()
  // que es llamada desde mostrarAgregarPedidoCompra() en pedido_compra.js
});

function agregarTablaPedidoCompra() {
  let id_producto = $("#material_lst").val();
  let cantidad = $("#cantidad").val();
  
  if (id_producto === "0" || id_producto.trim().length === 0) {
    mensaje_dialogo_info_ERROR("Debes seleccionar un producto", "ATENCIÓN");
    return;
  }
  
  if (!cantidad || cantidad <= 0) {
    mensaje_dialogo_info_ERROR("La cantidad debe ser mayor a 0", "ATENCIÓN");
    return;
  }

  let nombre_producto = $("#material_lst option:selected").text();
  let contador = $("#pedido_compra_tb tr").length + 1;
  
  let fila = `<tr>`;
  fila += `<td>${contador}</td>`;
  fila += `<td><input type="hidden" class="producto_id" value="${id_producto}">${nombre_producto}</td>`;
  fila += `<td><input type="hidden" class="producto_cantidad" value="${cantidad}">${cantidad}</td>`;
  fila += `<td class='text-center'>`;
  fila += `<button class='btn btn-danger btn-sm eliminar-detalle-pedido-btn' type="button"><i data-feather="trash-2"></i></button>`;
  fila += `</td>`;
  fila += `</tr>`;
  
  $("#pedido_compra_tb").append(fila);
  feather.replace();
  
  $("#material_lst").val("0");
  $("#cantidad").val("1");
}

$(document).on('click', '.eliminar-detalle-pedido-btn', function() {
  $(this).closest('tr').remove();
  // Renumerar filas
  $("#pedido_compra_tb tr").each(function(index) {
    $(this).find("td:first").text(index + 1);
  });
});

function guardarPedidoCompra() {
  let detalles = [];
  $("#pedido_compra_tb tr").each(function() {
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
    fecha_compra: $("#fecha_pedido").val(),
    id_usuario: $("#id_usuario_pedido").val(),
    estado: 'ACTIVO'
  };
  
  let respuesta_cabecera = ejecutarAjax("controladores/pedido_compra.php", "guardar=" + JSON.stringify(cabecera));
  
  try {
    // Manejar tanto strings JSON como objetos ya parseados
    let json_cabecera = typeof respuesta_cabecera === 'string' ? JSON.parse(respuesta_cabecera) : respuesta_cabecera;
    
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
      // Manejar tanto strings JSON como objetos ya parseados
      let json_det = typeof resp === 'string' ? JSON.parse(resp) : resp;
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
</script>
