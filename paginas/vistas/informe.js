function mostrarInformeMovimientos() {
    let contenido = dameContenido("paginas/informes/informe_movimientos.html");
    $("#contenido-principal").html(contenido);
}

function generarInforme() {

    const movimiento = document.getElementById("movimiento").value;
    const especificacion = document.getElementById("especificacion").value;
    const desde = document.getElementById("desde").value;
    const hasta = document.getElementById("hasta").value;

    if (movimiento === "") {
        alert("Debe seleccionar un movimiento");
        return;
    }

    if (desde !== "" && hasta !== "" && desde > hasta) {
        alert("La fecha 'Desde' no puede ser mayor que 'Hasta'");
        return;
    }

    // Datos del informe (ejemplo)
    const datos = {
        movimiento: movimiento,
        especificacion: especificacion,
        fecha_desde: desde,
        fecha_hasta: hasta
    };

    console.log("Generando informe con los siguientes datos:");
    console.log(datos);

    // Aquí luego puedes:
    // - Redirigir a un reporte específico
    // - Llamar a una API
    // - Generar PDF o tabla
}
