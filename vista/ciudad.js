function mostrarListaCiudades() {
    let contenido = dameContenido("paginas/referenciales/ciudad/listar.php");
    // el contenedor principal usa id="contenido-principal" en la plantilla
    $("#contenido-principal").html(contenido);
    cargarTablaCiudades();
}

function mostrarAgregarCiudad() {
    let contenido = dameContenido("paginas/referenciales/ciudad/agregar.php");
    $("#contenido-principal").html(contenido);
}

function guardarCiudad() {
    if ($("#ciudad_nombre").val().trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes ingresar el nombre de la ciudad", "ATENCIÓN");
        return;
    }
    let cabecera = {
        nombre: $("#ciudad_nombre").val().trim(),
        departamento: $("#ciudad_departamento").val().trim(),
        pais: $("#ciudad_pais").val().trim(),
        direccion: $("#ciudad_direccion").val().trim(),
        estado: $("#ciudad_estado").val(),
    };
    if ($("#id_ciudad").val() === "0") {
        ejecutarAjax("controladores/ciudad.php", "guardar=" + JSON.stringify(cabecera));
        mensaje_confirmacion("Guardado correctamente", "Éxito");
    } else {
        cabecera = { ...cabecera, id_ciudad: $("#id_ciudad").val() };
        ejecutarAjax("controladores/ciudad.php", "actualizar=" + JSON.stringify(cabecera));
        mensaje_confirmacion("Actualizado correctamente", "Éxito");
    }
    mostrarListaCiudades();
}

function cargarTablaCiudades() {
    let datos = ejecutarAjax("controladores/ciudad.php", "listar=1");
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='6' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.id_ciudad}</td>`;
            fila += `<td>${item.nombre}</td>`;
            fila += `<td>${item.departamento}</td>`;
            fila += `<td>${item.pais}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-warning editar-ciudad'><i data-feather="edit"></i></button> `;
            fila += `<button class='btn btn-danger eliminar-ciudad'><i data-feather="trash"></i></button>`;
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#ciudades_tb").html(fila);
    feather.replace();
}

$(document).on("click", ".eliminar-ciudad", function () {
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
            ejecutarAjax("controladores/ciudad.php", "eliminar=" + id);
            mensaje_confirmacion("Eliminado correctamente", "Éxito");
            cargarTablaCiudades();
        }
    });
});

$(document).on("click", ".editar-ciudad", function () {
    let id = $(this).closest("tr").find("td:eq(0)").text();
    let response = ejecutarAjax("controladores/ciudad.php", "id=" + id);
    if (response === "0") {
        mensaje_dialogo_info_ERROR("No se pudo obtener el registro", "Error");
        return;
    }
    let json_registro = JSON.parse(response);
    let contenido = dameContenido("paginas/referenciales/ciudad/agregar.php");
    $("#contenido-principal").html(contenido);
    $("#ciudad_form_titulo").text("Editar Ciudad");
    $("#id_ciudad").val(json_registro.id_ciudad);
    $("#ciudad_nombre").val(json_registro.nombre);
    $("#ciudad_departamento").val(json_registro.departamento);
    $("#ciudad_pais").val(json_registro.pais);
    $("#ciudad_direccion").val(json_registro.direccion);
    $("#ciudad_estado").val(json_registro.estado);
});

function cancelarCiudad() {
    mostrarListaCiudades();
}

$(document).on("keyup", "#b_ciudad", function () {
    let texto = $(this).val();
    if (texto.trim().length === 0) {
        cargarTablaCiudades();
        return;
    }
    let datos = ejecutarAjax("controladores/ciudad.php", "buscar=" + texto);
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='6' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.id_ciudad}</td>`;
            fila += `<td>${item.nombre}</td>`;
            fila += `<td>${item.departamento}</td>`;
            fila += `<td>${item.pais}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-warning editar-ciudad'><i data-feather="edit"></i></button> `;
            fila += `<button class='btn btn-danger eliminar-ciudad'><i data-feather="trash"></i></button>`;
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#ciudades_tb").html(fila);
    feather.replace();
});

function cargarListaCiudadesActivos(componente) {
    let datos = ejecutarAjax("controladores/ciudad.php", "leer_activos=1");
    let option = "<option value='0'>Selecciona una Ciudad</option>";
    if (datos !== "0") {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            option += `<option value='${item.id_ciudad}'>${item.nombre}</option>`;
        });
    }
    $(componente).html(option);
}
