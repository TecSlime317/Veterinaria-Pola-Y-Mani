<?php // diegoCar09: Valida los datos recibidos desde RegistrarCita.html y registra la nueva cita en la base de datos.
// diegoCar09: Importa las funciones compartidas de conexión con MySQL desde funcionesCita.php.
require_once __DIR__ . '/funcionesCita.php';

// diegoCar09: Indica al navegador que todas las respuestas de este endpoint serán en formato JSON.
header('Content-Type: application/json; charset=UTF-8');

try 
{
    // diegoCar09: Abre la conexión con la base de datos utilizando la función compartida.
    $conexion = obtenerConexion();

    // diegoCar09: Petición GET: Consulta y entrega las listas de dueños y mascotas existentes para llenar los select.
    if ($_SERVER['REQUEST_METHOD'] === 'GET') 
    {
        // diegoCar09: Obtiene todos los dueños ordenados alfabéticamente por nombre y apellidos.
        $sqlDuenos = 'SELECT ID_Dueno, Nombre, Apellidos FROM Dueno ORDER BY Nombre, Apellidos';
        $consultaDuenos = $conexion->prepare($sqlDuenos);
        $consultaDuenos->execute();
        $duenos = $consultaDuenos->get_result()->fetch_all(MYSQLI_ASSOC);
        $consultaDuenos->close();

        // diegoCar09: Obtiene todas las mascotas junto con su llave foránea ID_Dueno para permitir el filtrado en HTML.
        $sqlMascotas = 'SELECT ID_Mascota, ID_Dueno, Nombre, Especie FROM Mascota ORDER BY Nombre';
        $consultaMascotas = $conexion->prepare($sqlMascotas);
        $consultaMascotas->execute();
        $mascotas = $consultaMascotas->get_result()->fetch_all(MYSQLI_ASSOC);
        $consultaMascotas->close();

        $conexion->close();

        // diegoCar09: Devuelve ambos listados en formato JSON a RegistrarCita.html.
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
        // diegoCar09: Filtra y sanea cada campo recibido desde el formulario.
        $idMascota = filter_input(INPUT_POST, 'ID_Mascota', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $idDueno   = filter_input(INPUT_POST, 'ID_Dueno', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $fecha     = trim($_POST['Fecha'] ?? '');
        $hora      = trim($_POST['Hora'] ?? '');
        $estado    = trim($_POST['Estado'] ?? '');

        // diegoCar09: Verifica que todos los campos obligatorios vengan completos y sean válidos.
        if (!$idMascota || !$idDueno || $fecha === '' || $hora === '' || $estado === '') 
        {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos son obligatorios.']);
            $conexion->close();
            exit;
        }

        // diegoCar09: Comprueba en la base de datos que la mascota seleccionada realmente pertenezca al dueño indicado.
        $verificar = $conexion->prepare('SELECT ID_Mascota FROM Mascota WHERE ID_Mascota = ? AND ID_Dueno = ?');
        $verificar->bind_param('ii', $idMascota, $idDueno);
        $verificar->execute();
        $relacionValida = $verificar->get_result()->fetch_assoc();
        $verificar->close();

        // diegoCar09: Si la mascota no corresponde al dueño, rechaza el registro antes de intentar el INSERT.
        if (!$relacionValida) 
        {
            http_response_code(400);
            echo json_encode(['exito' => false, 'mensaje' => 'La mascota seleccionada no pertenece al dueño indicado.']);
            $conexion->close();
            exit;
        }

        // diegoCar09: Prepara la inserción omitiendo ID_Cita para que MySQL lo genere automáticamente.
        $sql = 'INSERT INTO Cita (ID_Mascota, ID_Dueno, Fecha, Hora, Estado) VALUES (?, ?, ?, ?, ?)';
        $consulta = $conexion->prepare($sql);
        // diegoCar09: Vincula los parámetros según su tipo (i = entero, s = cadena de texto).
        $consulta->bind_param('iisss', $idMascota, $idDueno, $fecha, $hora, $estado);
        $consulta->execute();

        // diegoCar09: Recupera el ID autogenerado asignado por MySQL a la nueva cita.
        $nuevoId = $conexion->insert_id;

        $consulta->close();
        $conexion->close();

        // diegoCar09: Responde en formato JSON confirmando el alta y el número de cita asignado.
        echo json_encode([
            'exito' => true,
            'mensaje' => 'Cita #' . $nuevoId . ' registrada correctamente.'
        ]);
        exit;
    }

    // diegoCar09: Rechaza cualquier método HTTP que no sea GET o POST.
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido.']);
    $conexion->close();
} 

catch (mysqli_sql_exception $error) 
{
    // diegoCar09: Registra el error técnico en el log del servidor y devuelve un mensaje seguro al navegador.
    error_log('Error en registrarCita.php: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error de base de datos al procesar la cita.']);
}
?>