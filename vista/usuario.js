function mostrarListaUsuarios() {
    let contenido = dameContenido("paginas/referenciales/usuario/listar.php");
    $("#contenido-principal").html(contenido);
    cargarTablaUsuarios();
}

function mostrarAgregarUsuario() {
    let contenido = dameContenido("paginas/referenciales/usuario/agregar.php");
    $("#contenido-principal").html(contenido);
}

function guardarUsuario() {
    if ($("#usuario_nombre_usuario").val().trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes ingresar el nombre del usuario", "ATENCIÓN");
        return;
    }
    if ($("#usuario_nickname").val().trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes ingresar el nickname", "ATENCIÓN");
        return;
    }
    if ($("#id_usuario").val() === "0" && $("#usuario_password").val().trim().length === 0) {
        mensaje_dialogo_info_ERROR("Debes ingresar una contraseña para nuevo usuario", "ATENCIÓN");
        return;
    }
    let cabecera = {
        nombre_usuario: $("#usuario_nombre_usuario").val().trim(),
        nickname: $("#usuario_nickname").val().trim(),
        password: $("#usuario_password").val().trim(),
        id_rol: $("#usuario_id_rol").val(),
        limite_intentos: $("#usuario_limite_intentos").val(),
        estado: $("#usuario_estado").val(),
    };
    if ($("#id_usuario").val() === "0") {
        ejecutarAjax("controladores/usuario.php", "guardar=" + JSON.stringify(cabecera));
        mensaje_confirmacion("Guardado correctamente", "Éxito");
    } else {
        cabecera = { ...cabecera, id_usuario: $("#id_usuario").val() };
        ejecutarAjax("controladores/usuario.php", "actualizar=" + JSON.stringify(cabecera));
        mensaje_confirmacion("Actualizado correctamente", "Éxito");
    }
    mostrarListaUsuarios();
}

function cargarTablaUsuarios() {
    let datos = ejecutarAjax("controladores/usuario.php", "listar=1");
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='7' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.id_usuario}</td>`;
            fila += `<td>${item.nombre_usuario}</td>`;
            fila += `<td>${item.nickname}</td>`;
            fila += `<td>${item.id_rol}</td>`;
            fila += `<td>${item.intentos}/${item.limite_intentos}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-warning editar-usuario'><i data-feather="edit"></i></button> `;
            fila += `<button class='btn btn-danger eliminar-usuario'><i data-feather="trash"></i></button>`;
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#usuarios_tb").html(fila);
    feather.replace();
}

$(document).on("click", ".eliminar-usuario", function () {
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
            ejecutarAjax("controladores/usuario.php", "eliminar=" + id);
            mensaje_confirmacion("Eliminado correctamente", "Éxito");
            cargarTablaUsuarios();
        }
    });
});

$(document).on("click", ".editar-usuario", function () {
    let id = $(this).closest("tr").find("td:eq(0)").text();
    let response = ejecutarAjax("controladores/usuario.php", "id=" + id);
    if (response === "0") {
        mensaje_dialogo_info_ERROR("No se pudo obtener el registro", "Error");
        return;
    }
    let json_registro = JSON.parse(response);
    let contenido = dameContenido("paginas/referenciales/usuario/agregar.php");
    $("#contenido-principal").html(contenido);
    $("#usuario_form_titulo").text("Editar Usuario");
    $("#id_usuario").val(json_registro.id_usuario);
    $("#usuario_nombre_usuario").val(json_registro.nombre_usuario);
    $("#usuario_nickname").val(json_registro.nickname);
    $("#usuario_id_rol").val(json_registro.id_rol);
    $("#usuario_limite_intentos").val(json_registro.limite_intentos);
    $("#usuario_estado").val(json_registro.estado);
});

function cancelarUsuario() {
    mostrarListaUsuarios();
}

$(document).on("keyup", "#b_usuario", function () {
    let texto = $(this).val();
    if (texto.trim().length === 0) {
        cargarTablaUsuarios();
        return;
    }
    let datos = ejecutarAjax("controladores/usuario.php", "buscar=" + texto);
    let fila = "";
    if (datos === "0") {
        fila = `<tr><td colspan='7' class='text-center'>No hay registros</td></tr>`;
    } else {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            fila += `<tr>`;
            fila += `<td>${item.id_usuario}</td>`;
            fila += `<td>${item.nombre_usuario}</td>`;
            fila += `<td>${item.nickname}</td>`;
            fila += `<td>${item.id_rol}</td>`;
            fila += `<td>${item.intentos}/${item.limite_intentos}</td>`;
            fila += `<td><span class="badge bg-${item.estado === "ACTIVO" ? "success" : "danger"}">${item.estado}</span></td>`;
            fila += `<td class='text-end'>`;
            fila += `<button class='btn btn-warning editar-usuario'><i data-feather="edit"></i></button> `;
            fila += `<button class='btn btn-danger eliminar-usuario'><i data-feather="trash"></i></button>`;
            fila += `</td>`;
            fila += `</tr>`;
        });
    }
    $("#usuarios_tb").html(fila);
    feather.replace();
});

function cargarListaUsuariosActivos(componente) {
    let datos = ejecutarAjax("controladores/usuario.php", "leer_activos=1");
    let option = "<option value='0'>Selecciona un Usuario</option>";
    if (datos !== "0") {
        let json_datos = JSON.parse(datos);
        json_datos.map(function (item) {
            option += `<option value='${item.id_usuario}'>${item.nombre_usuario}</option>`;
        });
    }
    $(componente).html(option);
}
