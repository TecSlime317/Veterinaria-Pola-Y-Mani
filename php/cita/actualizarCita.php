<?php //Por: Diego Carballo
// tecprime: Importa las funciones compartidas de conexión y consulta de citas.
require_once __DIR__ . '/funcionesCita.php';

// tecprime: Indica al navegador que todas las respuestas de este archivo serán en formato JSON.
header('Content-Type: application/json; charset=UTF-8');

// tecprime: Valida que el parámetro 'id' recibido en la URL sea un entero positivo.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// tecprime: Si el ID falta o es inválido, devuelve un error HTTP 400 y detiene la ejecución.
if ($id === false || $id === null) 
{
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => 'Se requiere un ID de cita válido.']);
    exit;
}

try
{
    // tecprime: Si la petición es POST, actualiza únicamente Fecha, Hora y Estado (protegiendo los IDs).
    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        $fecha  = trim($_POST['Fecha'] ?? '');
        $hora   = trim($_POST['Hora'] ?? '');
        $estado = trim($_POST['Estado'] ?? '');

    
        if ($fecha === '' || $hora === '' || $estado === '') 
        {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Los campos Fecha, Hora y Estado son obligatorios.']);
            exit;
        }
        
        $conexion = obtenerConexion();
        $sql = 'UPDATE Cita SET Fecha = ?, Hora = ?, Estado = ? WHERE ID_Cita = ?';
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param('sssi', $fecha, $hora, $estado, $id);
        $consulta->execute();
        $consulta->close();
        $conexion->close();
        
        echo json_encode(['exito' => true, 'mensaje' => 'Cita actualizada correctamente.']);
        exit;
    }

    // tecprime: Si la petición es GET, consulta los datos actuales de la cita (incluyendo mascota y dueño) y los envía en JSON.
    $cita = obtenerCitaPorId($id);

    if (!$cita) 
    {
        http_response_code(404);
        echo json_encode(['exito' => false, 'mensaje' => 'No se encontró la cita solicitada.']);
    } 
    
    else 
    {
        echo json_encode(['exito' => true, 'cita' => $cita]);
    }
} 

catch (mysqli_sql_exception $error)
{
    // tecprime: Registra el detalle técnico en el servidor y devuelve un mensaje seguro en JSON.
    error_log('Error en actualizarCita.php: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error de base de datos al procesar la cita.']);
}
?>