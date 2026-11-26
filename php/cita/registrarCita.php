<?php // diegoCar09: Endpoint PHP puro que entrega listas (GET) y registra una nueva cita (POST) para los procesos fetch de RegistrarCita.html.
require_once __DIR__ . '/funcionesCita.php';

header('Content-Type: application/json; charset=UTF-8');

try 
{
    $conexion = obtenerConexion();

    // diegoCar09: Petición GET: Devuelve los dueños y mascotas existentes para llenar los select en HTML.
    if ($_SERVER['REQUEST_METHOD'] === 'GET') 
    {
        $duenos = $conexion->query('SELECT ID_Dueno, Nombre, Apellidos FROM dueno ORDER BY Nombre, Apellidos')->fetch_all(MYSQLI_ASSOC);
        $mascotas = $conexion->query('SELECT ID_Mascota, ID_Dueno, Nombre, Especie FROM mascota ORDER BY Nombre')->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            'exito' => true,
            'duenos' => $duenos,
            'mascotas' => $mascotas
        ]);
        exit;
    }

    // diegoCar09: Petición POST: Valida los datos recibidos e inserta la nueva cita con ID_Cita autoincremental.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    {
        $idMascota = filter_input(INPUT_POST, 'ID_Mascota', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $idDueno   = filter_input(INPUT_POST, 'ID_Dueno', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $fecha     = trim($_POST['Fecha'] ?? '');
        $hora      = trim($_POST['Hora'] ?? '');
        $estado    = trim($_POST['Estado'] ?? '');

        if (!$idMascota || !$idDueno || $fecha === '' || $hora === '' || $estado === '') 
        {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos son obligatorios.']);
            exit;
        }

        // diegoCar09: Verifica que la mascota seleccionada realmente pertenezca al dueño elegido.
        $verificar = $conexion->prepare('SELECT ID_Mascota FROM mascota WHERE ID_Mascota = ? AND ID_Dueno = ?');
        $verificar->bind_param('ii', $idMascota, $idDueno);
        $verificar->execute();
        $relacionValida = $verificar->get_result()->fetch_assoc();
        $verificar->close();

        if (!$relacionValida) 
        {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'La mascota seleccionada no pertenece al dueño indicado.']);
            exit;
        }

        // diegoCar09: Inserta la nueva cita omitiendo ID_Cita para que MySQL lo genere automáticamente.
        $sql = 'INSERT INTO cita (ID_Mascota, ID_Dueno, Fecha, Hora, Estado) VALUES (?, ?, ?, ?, ?)';
        $consulta = $conexion->prepare($sql);
        $consulta->bind_param('iisss', $idMascota, $idDueno, $fecha, $hora, $estado);
        $consulta->execute();

        $nuevoId = $conexion->insert_id;
        $consulta->close();

        echo json_encode([
            'exito' => true,
            'mensaje' => 'Cita #' . $nuevoId . ' registrada correctamente.'
        ]);
        exit;
    }

    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
} 
catch (mysqli_sql_exception $error) 
{
    error_log('Error en registrarCita.php: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error de base de datos al procesar la cita.']);
}
?>
