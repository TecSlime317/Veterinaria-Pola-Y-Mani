<?php // diegoCar09: Genera el listado de citas o el detalle solicitado mediante su ID.
// Importa las funciones compartidas de conexión, escape y consulta de citas.
require_once __DIR__ . '/funcionesCita.php';

// Si se recibe un parámetro id por GET, entrega el detalle individual; de lo contrario, genera el listado general.
if (isset($_GET['id'])) 
{
    // Valida que el parámetro id sea un número entero mayor o igual a 1.
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($id === false || $id === null) 
    {
        http_response_code(400);
        echo '<p class="fila-mensaje" role="alert">Se requiere un ID de cita válido.</p>';
    } 

    else 
    {

        try 
        {
            imprimirDetalleCita($id);
        } 

        catch (mysqli_sql_exception $error) 
        {
            error_log('Error al consultar el registro de la cita: ' . $error->getMessage());
            http_response_code(500);
            echo '<p class="fila-mensaje" role="alert">Ocurrió un error al consultar la información de la cita.</p>';
        }
    }
} 

else 
{
    // Consulta todas las citas y construye las 7 columnas esperadas por la tabla en IndexCita.html.
    try 
    {
        $citas = obtenerCitas();

        // Si no hay registros, imprime una sola fila centrada abarcando las 7 columnas.
        if (count($citas) === 0) 
        {
            echo '<tr class="fila-mensaje"><td colspan="7">No hay citas registradas.</td></tr>';
        }

        // Recorre cada cita e imprime sus 6 campos de datos más la columna de acciones.
        foreach ($citas as $cita) 
        {
            echo '<tr>';
            echo '<td>' . escaparDato($cita['ID_Cita']) . '</td>';
            echo '<td>' . escaparDato($cita['ID_Mascota']) . '</td>';
            echo '<td>' . escaparDato($cita['ID_Dueno']) . '</td>';
            echo '<td>' . escaparDato($cita['Fecha']) . '</td>';
            echo '<td>' . escaparDato($cita['Hora']) . '</td>';
            echo '<td>' . escaparDato($cita['Estado']) . '</td>';
            echo '<td><details><summary>Opciones</summary>';
            // Codifica el ID para pasarlo de forma segura en la URL.
            $id = rawurlencode((string) $cita['ID_Cita']);
            // Enlaces hacia las vistas HTML de lectura y edición individual.
            echo '<a class="boton boton-secundario" href="LeerCita.html?id=' . $id . '">Ver</a>';
            echo '<a class="boton boton-secundario" href="ActualizarCita.html?id=' . $id . '">Editar</a>';
            // Botón con clase y atributo data-id para activar el mensaje emergente de confirmación en JS.
            echo '<button type="button" class="boton-eliminar" data-id="' . $id . '">Eliminar</button>';
            echo '</details></td>';
            echo '</tr>';
        }

    } 
    
    catch (mysqli_sql_exception $error) 
    {
        // Guarda el error técnico en el log del servidor y muestra un mensaje seguro en la tabla.
        error_log('Error al consultar el listado de citas: ' . $error->getMessage());
        http_response_code(500);
        echo '<tr class="fila-mensaje"><td colspan="7">No se pudo cargar el listado de citas.</td></tr>';
    }
}
?>