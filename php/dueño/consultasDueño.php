<?php
// Gael: Carga la clase compartida que abre y entrega la conexión con MySQL.
require_once __DIR__ . '/../conexion.php';

// Gael: Convierte los valores de la base de datos en texto seguro para insertarlo en HTML.
// También muestra un guion cuando un campo opcional está vacío o contiene NULL.
function escaparDato($valor)
{
    if ($valor === null || $valor === '') {
        return '—';
    }

    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Gael: Consulta todos los dueños para llenar la tabla del listado.
// El modo estricto hace que los errores de conexión o SQL puedan detectarse y reportarse.
function obtenerDuenos()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = Database::getinstancia()->getConnection();

    // Gael: Recupera los campos que aparecen como columnas en IndexDueño.php.
    $sql = 'SELECT ID_Dueno, Nombre, Apellido, Telefono, CorreoElectronico
            FROM dueno';
    $consulta = $conexion->prepare($sql);
    $duenos = [];
    $consulta->execute();
    $resultado = $consulta->get_result();

    // Gael: Guarda cada fila asociativa para que la vista pueda recorrerla y mostrarla.
    while ($dueno = $resultado->fetch_assoc()) {
        $duenos[] = $dueno;
    }

    $consulta->close();
    // Gael: Devuelve una lista vacía si no hay registros, o todas las filas encontradas.
    return $duenos;
}

// Gael: Imprime una tabla HTML reutilizable a partir de encabezados y filas.
function imprimirTabla(array $encabezados, array $filas, string $mensajeVacio)
{
    echo '<div class="tabla-contenedor"><table class="tabla-datos"><thead><tr>';

    // Gael: Genera los títulos de columna escapados para evitar interpretar texto como HTML.
    foreach ($encabezados as $encabezado) {
        echo '<th>' . escaparDato($encabezado) . '</th>';
    }
    echo '</tr></thead><tbody>';

    // Gael: Si no hay datos, muestra un mensaje en una celda que abarca todas las columnas.
    if (count($filas) === 0) {
        echo '<tr class="fila-mensaje"><td colspan="' . count($encabezados) . '">'
            . escaparDato($mensajeVacio) . '</td></tr>';
    } else {
        // Gael: En otro caso crea una fila por registro y escapa cada valor antes de imprimirlo.
        foreach ($filas as $fila) {
            echo '<tr>';
            foreach ($fila as $valor) {
                echo '<td>' . escaparDato($valor) . '</td>';
            }
            echo '</tr>';
        }
    }

    echo '</tbody></table></div>';
}

// Gael: Busca un dueño por su ID y genera debajo las tablas de sus mascotas y citas.
function imprimirDetalleDueno(int $id)
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = Database::getinstancia()->getConnection();

    // Gael: El parámetro ? se reemplaza con el ID validado, evitando concatenarlo en la consulta SQL.
    $consulta = $conexion->prepare(
        'SELECT ID_Dueno, Nombre, Apellidos, Edad, Telefono, CorreoElectronico, Direccion, Ciudad, FechaDeInscripcion
         FROM dueno
         WHERE ID_Dueno = ?'
    );
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $dueno = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if (!$dueno) {
        // Gael: Devuelve un estado 404 y un mensaje claro cuando ese ID no existe.
        http_response_code(404);
        echo '<p class="fila-mensaje" role="alert">No se encontró un dueño con ese ID.</p>';
        return;
    }

    // Gael: Obtiene solo las mascotas cuya llave foránea corresponde al dueño solicitado.
    $consulta = $conexion->prepare(
        'SELECT ID_Mascota, Nombre, Genero, Especie, Raza, Peso, FechaDeNacimiento, Estado
         FROM mascota
         WHERE ID_Dueno = ?
         ORDER BY ID_Mascota'
    );
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $mascotas = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    $consulta->close();

    // Gael: Busca las citas asociadas directamente al dueño e incluye el nombre de la mascota.
    // LEFT JOIN conserva la cita aunque no hubiera un nombre de mascota disponible para mostrar.
    $consulta = $conexion->prepare(
        'SELECT C.ID_Cita, C.ID_Mascota, M.Nombre AS Mascota, C.Fecha, C.Hora, C.Estado
         FROM cita AS C
         LEFT JOIN mascota AS M ON M.ID_Mascota = C.ID_Mascota
         WHERE C.ID_Dueno = ?
         ORDER BY C.Fecha, C.Hora, C.ID_Cita'
    );
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $citas = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    $consulta->close();

    // Gael: Prepara los pares etiqueta/valor del dueño y los entrega a la tabla compartida.
    echo '<h2>Información del dueño</h2>';
    $filasDueno = [
        ['ID', $dueno['ID_Dueno']],
        ['Nombre', $dueno['Nombre']],
        ['Apellidos', $dueno['Apellidos']],
        ['Edad', $dueno['Edad']],
        ['Teléfono', $dueno['Telefono']],
        ['Correo electrónico', $dueno['CorreoElectronico']],
        ['Dirección', $dueno['Direccion']],
        ['Ciudad', $dueno['Ciudad']],
        ['Fecha de inscripción', $dueno['FechaDeInscripcion']]
    ];
    imprimirTabla(['Dato', 'Información'], $filasDueno, '');

    // Gael: Reordena los campos de cada mascota para que coincidan con los encabezados de la tabla.
    echo '<h2>Mascotas asociadas</h2>';
    $filasMascotas = [];
    foreach ($mascotas as $mascota) {
        $filasMascotas[] = [
            $mascota['ID_Mascota'],
            $mascota['Nombre'],
            $mascota['Genero'],
            $mascota['Especie'],
            $mascota['Raza'],
            $mascota['Peso'],
            $mascota['FechaDeNacimiento'],
            $mascota['Estado']
        ];
    }
    imprimirTabla(
        ['ID', 'Nombre', 'Género', 'Especie', 'Raza', 'Peso', 'Fecha de nacimiento', 'Estado'],
        $filasMascotas,
        'Este dueño no tiene mascotas registradas.'
    );

    // Gael: Reordena las citas, incluyendo el nombre de la mascota, antes de imprimirlas.
    echo '<h2>Citas asociadas</h2>';
    $filasCitas = [];
    foreach ($citas as $cita) {
        $filasCitas[] = [
            $cita['ID_Cita'],
            $cita['ID_Mascota'],
            $cita['Mascota'],
            $cita['Fecha'],
            $cita['Hora'],
            $cita['Estado']
        ];
    }
    imprimirTabla(
        ['ID de cita', 'ID de mascota', 'Mascota', 'Fecha', 'Hora', 'Estado'],
        $filasCitas,
        'Este dueño no tiene citas registradas.'
    );
}

// Gael: La presencia del parámetro id distingue la vista de detalle del listado general.
if (isset($_GET['id'])) {
    // Gael: Acepta únicamente un entero positivo para usarlo en las consultas preparadas.
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1]
    ]);

    if ($id === false || $id === null) {
        // Gael: Rechaza IDs ausentes o mal formados antes de consultar la base de datos.
        http_response_code(400);
        echo '<p class="fila-mensaje" role="alert">Se requiere un ID de dueño válido.</p>';
    } else {
        try {
            imprimirDetalleDueno($id);
        } catch (mysqli_sql_exception $error) {
            // Gael: Registra el detalle técnico en el servidor y muestra al usuario un error comprensible.
            error_log('Error al consultar el registro del dueño: ' . $error->getMessage());
            http_response_code(500);
            echo '<p class="fila-mensaje" role="alert">Ocurrió un error al consultar la información del dueño.</p>';
        }
    }
} else {
    // Gael: Sin ID, este mismo módulo genera las filas que se incluyen en el listado de dueños.
    try {
        $duenos = obtenerDuenos();

        if (count($duenos) === 0) {
            // Gael: Mantiene la tabla alineada cuando la consulta no encuentra registros.
            echo '<tr class="fila-mensaje"><td colspan="10">No hay dueños registrados.</td></tr>';
        }

        // Gael: Imprime cada dueño y crea enlaces que conservan su ID en la URL.
        foreach ($duenos as $dueno) {
            echo '<tr>';
            echo '<td>' . escaparDato($dueno['ID_Dueno']) . '</td>';
            echo '<td>' . escaparDato($dueno['Nombre']) . '</td>';
            echo '<td>' . escaparDato($dueno['Apellidos']) . '</td>';
            echo '<td>' . escaparDato($dueno['Edad']) . '</td>';
            echo '<td>' . escaparDato($dueno['Telefono']) . '</td>';
            echo '<td>' . escaparDato($dueno['CorreoElectronico']) . '</td>';
            echo '<td>' . escaparDato($dueno['Direccion']) . '</td>';
            echo '<td>' . escaparDato($dueno['Ciudad']) . '</td>';
            echo '<td>' . escaparDato($dueno['FechaDeInscripcion']) . '</td>';
            echo '<td><details><summary>Opciones</summary>';
            $id = rawurlencode((string) $dueno['ID_Dueno']);
            echo '<a class="boton boton-secundario" href="LeerDueño.php?id=' . $id . '">Ver</a>';
            echo '<a class="boton boton-secundario" href="ActualizarDueño.html?id=' . $id . '">Editar</a>';
            // Gael: La eliminación está pendiente de backend, por eso el control se muestra deshabilitado.
            echo '<button type="button" disabled title="La eliminación todavía no está conectada al servidor">Eliminar</button>';
            echo '</details></td>';
            echo '</tr>';
        }
    } catch (mysqli_sql_exception $error) {
        // Gael: Informa el fallo sin ocultar el error técnico, que queda en el registro del servidor.
        error_log('Error al consultar el listado de dueños: ' . $error->getMessage());
        http_response_code(500);
        echo '<tr class="fila-mensaje"><td colspan="10">No se pudo cargar el listado de dueños.</td></tr>';
    }
}
