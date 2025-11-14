function mostrarListaProveedores() {
    let contenido = dameContenido("paginas/referenciales/proveedor/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaProveedores();
}

function mostrarAgregarProveedor() {
    let contenido = dameContenido("paginas/referenciales/proveedor/agregar.php");
    $("#contenido-principal").html(contenido);
    cargarListaCiudadesActivos('#proveedor_id_ciudad');
}

function guardarProveedor() {
    // validar: al menos razon social o nombre
    if ($("#proveedor_razon_social").val().trim().length === 0 && $("#proveedor_nombre").val().trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes ingresar al menos Nombre o Razón Social", "ATENCIÓN");
        return;
    }
    let cabecera = {
        nombre: $("#proveedor_nombre").val().trim(),
        apellido: $("#proveedor_apellido").val().trim(),
        razon_social: $("#proveedor_razon_social").val().trim(),
        telefono: $("#proveedor_telefono").val().trim(),
        ruc: $("#proveedor_ruc").val().trim(),
        email: $("#proveedor_email").val().trim(),
        direccion: $("#proveedor_direccion").val().trim(),
        id_ciudad: $("#proveedor_id_ciudad").val(),
        estado: $("#proveedor_estado").val(),
    };
    if ($("#id_proveedor").val() === "0") {
        ejecutarAjax("controladores/proveedor.php", "guardar=" + JSON.stringify(cabecera));
        mensaje_confirmacion("Guardado correctamente", "Éxito");
    } else {
        cabecera = { ...cabecera, id_proveedor: $("#id_proveedor").val() };
        ejecutarAjax("controladores/proveedor.php", "actualizar=" + JSON.stringify(cabecera));
        mensaje_confirmacion("Actualizado correctamente", "Éxito");
    }
    mostrarListaProveedores();
}

function cargarTablaProveedores() {
    let datos = ejecutarAjax("controladores/proveedor.php", "listar=1");
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='8' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.id_proveedor}</td>`;
            fila += `<td>${(item.nombre?item.nombre+' ':'') + (item.apellido?item.apellido:'')}</td>`;
            fila += `<td>${item.razon_social ? item.razon_social : ''}</td>`;
            fila += `<td>${item.telefono ? item.telefono : ''}</td>`;
            fila += `<td>${item.ruc ? item.ruc : ''}</td>`;
            fila += `<td>${item.id_ciudad ? item.id_ciudad : ''}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-warning editar-proveedor'><i data-feather="edit"></i></button> `;
            fila += `<button class='btn btn-danger eliminar-proveedor'><i data-feather="trash"></i></button>`;
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#proveedores_tb").html(fila);
    feather.replace();
}

$(document).on("click", ".eliminar-proveedor", function () {
    let id = $(this).closest("tr").find("td:eq(0)").text();
    Swal.fire({
        title: 'Estas seguro?',
        text: "Desea eliminar esta registro?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'No',
        confirmButtonText: 'Si'
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarAjax("controladores/proveedor.php", "eliminar=" + id);
            mensaje_confirmacion("Eliminado correctamente", "Éxito");
            cargarTablaProveedores();
        }
    });
});

$(document).on("click", ".editar-proveedor", function () {
    let id = $(this).closest("tr").find("td:eq(0)").text();
    let response = ejecutarAjax("controladores/proveedor.php", "id=" + id);
    if (response === "0") {
        mensaje_dialogo_info_ERROR("No se pudo obtener el registro", "Error");
        return;
    }
    let json_registro = JSON.parse(response);
    let contenido = dameContenido("paginas/referenciales/proveedor/agregar.php");
    $("#contenido-principal").html(contenido);
    $("#proveedor_form_titulo").text("Editar Proveedor");
    $("#id_proveedor").val(json_registro.id_proveedor);
    $("#proveedor_nombre").val(json_registro.nombre);
    $("#proveedor_apellido").val(json_registro.apellido);
    $("#proveedor_razon_social").val(json_registro.razon_social);
    $("#proveedor_telefono").val(json_registro.telefono);
    $("#proveedor_ruc").val(json_registro.ruc);
    $("#proveedor_email").val(json_registro.email);
    $("#proveedor_direccion").val(json_registro.direccion);
    // cargar ciudades y seleccionar la correspondiente
    cargarListaCiudadesActivos('#proveedor_id_ciudad');
    setTimeout(function(){
        $("#proveedor_id_ciudad").val(json_registro.id_ciudad);
    }, 300);
    $("#proveedor_estado").val(json_registro.estado);
});

function cancelarProveedor() {
    mostrarListaProveedores();
}

$(document).on("keyup", "#b_proveedor", function () {
    let texto = $(this).val();
    if (texto.trim().length === 0) {
        cargarTablaProveedores();
        return;
    }
    let datos = ejecutarAjax("controladores/proveedor.php", "buscar=" + texto);
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='8' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.id_proveedor}</td>`;
            fila += `<td>${(item.nombre?item.nombre+' ':'') + (item.apellido?item.apellido:'')}</td>`;
            fila += `<td>${item.razon_social ? item.razon_social : ''}</td>`;
            fila += `<td>${item.telefono ? item.telefono : ''}</td>`;
            fila += `<td>${item.ruc ? item.ruc : ''}</td>`;
            fila += `<td>${item.id_ciudad ? item.id_ciudad : ''}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-warning editar-proveedor'><i data-feather="edit"></i></button> `;
            fila += `<button class='btn btn-danger eliminar-proveedor'><i data-feather="trash"></i></button>`;
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#proveedores_tb").html(fila);
    feather.replace();
});
