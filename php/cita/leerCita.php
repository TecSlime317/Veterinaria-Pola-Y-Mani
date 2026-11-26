<?php // diegoCar09: Valida el ID y solicita al módulo compartido el detalle de una cita.
// Importa las funciones compartidas para consultar la base de datos e imprimir las tablas.
require_once __DIR__ . '/funcionesCita.php';

// Extrae y valida que el parámetro 'id' enviado en la URL sea un número entero positivo.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// Si el ID falta o contiene caracteres no válidos, responde con código HTTP 400.
if ($id === false || $id === null) 
{
    http_response_code(400);
    echo '<p class="fila-mensaje" role="alert">Se requiere un ID de cita válido.</p>';
} 

else 
{

    try 
    {
        // Llama a la función que imprime las tablas de la cita, su mascota y su dueño.
        imprimirDetalleCita($id);
    } 
    
    catch (mysqli_sql_exception $error) 
    {
        // Registra el fallo técnico en el servidor y entrega un mensaje seguro al usuario.
        error_log('Error al leer la cita: ' . $error->getMessage());
        http_response_code(500);
        echo '<p class="fila-mensaje" role="alert">Ocurrió un error al consultar la cita.</p>';
    }
}
?>