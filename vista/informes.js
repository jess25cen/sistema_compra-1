function mostrarInformeMovimientos() {
    let contenido = dameContenido("paginas/informes/informe_movimientos.html");
    $("#contenido-principal").html(contenido);
    
    // Adjuntar evento al botón después de que se cargue el contenido
    $(document).off("click", "#btnGenerarInforme").on("click", "#btnGenerarInforme", function(e) {
        e.preventDefault();
        generarInforme();
    });
}

function generarInforme() {
    const movimiento = document.getElementById("movimiento").value;
    const especificacion = document.getElementById("especificacion").value;
    const desde = document.getElementById("desde").value;
    const hasta = document.getElementById("hasta").value;

    if (movimiento === "") {
        mensaje_dialogo_info_ERROR("Debe seleccionar un movimiento", "ATENCIÓN");
        return;
    }

    if (desde !== "" && hasta !== "" && desde > hasta) {
        mensaje_dialogo_info_ERROR("La fecha 'Desde' no puede ser mayor que 'Hasta'", "ATENCIÓN");
        return;
    }

    // Datos del informe
    const datos = {
        movimiento: movimiento,
        especificacion: especificacion,
        fecha_desde: desde,
        fecha_hasta: hasta
    };

    console.log("Generando informe con los siguientes datos:");
    console.log(datos);

    // Llamar al controlador para obtener los datos del informe
    let respuesta = ejecutarAjax("controladores/informes.php", "accion=generar&" + $.param(datos));
    
    try {
        let resultado = typeof respuesta === 'string' ? JSON.parse(respuesta) : respuesta;
        
        if (resultado.success) {
            // Limpiar resultados anteriores
            $("#resultados-informe").html("");
            
            if (resultado.total > 0) {
                // Mostrar tabla de resultados
                mostrarResultadosInforme(resultado.datos);
                mensaje_dialogo_info("Se encontraron " + resultado.total + " registros", "Éxito");
            } else {
                $("#resultados-informe").html("<p class='alert alert-info'>No hay datos para mostrar con los criterios seleccionados</p>");
                mensaje_dialogo_info("No hay registros que coincidan con los criterios", "Información");
            }
        } else {
            mensaje_dialogo_info_ERROR(resultado.mensaje || "Error al generar el informe", "Error");
        }
    } catch (error) {
        console.error('Error al procesar respuesta:', error);
        console.error('Respuesta recibida:', respuesta);
        mensaje_dialogo_info_ERROR("Error al generar el informe. Revisa la consola para más detalles", "Error");
    }
}

function mostrarResultadosInforme(datos) {
    if (!datos || datos.length === 0) {
        $("#resultados-informe").html("<p>No hay datos para mostrar</p>");
        return;
    }
    
    // Crear tabla con los resultados
    let tabla = '<table class="table table-striped table-bordered"><thead><tr style="background-color: #f8f9fa;">';
    
    // Encabezados
    Object.keys(datos[0]).forEach(key => {
        tabla += '<th style="background-color: #f8f9fa;">' + key.replace(/_/g, ' ').toUpperCase() + '</th>';
    });
    tabla += '</tr></thead><tbody>';
    
    // Datos
    datos.forEach((fila, index) => {
        tabla += '<tr>';
        Object.values(fila).forEach(valor => {
            tabla += '<td>' + (valor !== null ? valor : '-') + '</td>';
        });
        tabla += '</tr>';
    });
    
    tabla += '</tbody></table>';
    
    $("#resultados-informe").html(tabla);
}
