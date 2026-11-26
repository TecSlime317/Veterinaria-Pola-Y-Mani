<?php // diegoCar09: Define consultas y funciones de salida para el módulo de citas.
require_once __DIR__ . '/../conexion.php';

// tecslime: Devuelve la conexión compartida configurada para el resto del sistema.
function obtenerConexion()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    return Database::getinstancia()->getConnection();
}

// diegoCar09: Convierte los valores de la base de datos en texto seguro para insertarlo en HTML (previene ataques XSS).
// Muestra un guion largo cuando un campo opcional está vacío o contiene NULL.
function escaparDato(mixed $valor)
{
    if ($valor === null || $valor === '') 
    {
        return '—';
    }

    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// diegoCar09: Consulta todas las citas registradas ordenadas por fecha, hora e ID para llenar la tabla principal.
function obtenerCitas()
{
    $conexion = obtenerConexion();

    $sql = 'SELECT ID_Cita, ID_Mascota, ID_Dueno, Fecha, Hora, Estado FROM cita ORDER BY Fecha, Hora, ID_Cita';
    $consulta = $conexion->prepare($sql);
    $citas = [];
    $consulta->execute();
    $resultado = $consulta->get_result();

    // Recorre cada fila obtenida y la guarda en un arreglo asociativo.
    while ($cita = $resultado->fetch_assoc()) 
    {
        $citas[] = $cita;
    }

    $consulta->close();

    return $citas;
}

// diegoCar09: Obtiene una sola cita por su ID junto con los nombres de su mascota y dueño mediante LEFT JOIN.
// Se utiliza para rellenar los campos en ActualizarCita.html.
function obtenerCitaPorId(int $id): ?array
{
    $conexion = obtenerConexion();

    $sql = 'SELECT C.ID_Cita, C.ID_Mascota, C.ID_Dueno, C.Fecha, C.Hora, C.Estado, M.Nombre AS NombreMascota, M.Especie, M.Raza,
                   D.Nombre AS NombreDueno, D.Apellidos AS ApellidosDueno, D.Telefono
            FROM cita AS C
            LEFT JOIN mascota AS M ON M.ID_Mascota = C.ID_Mascota
            LEFT JOIN dueno AS D ON D.ID_Dueno = C.ID_Dueno
            WHERE C.ID_Cita = ?';

    $consulta = $conexion->prepare($sql);
    // Vincula el ID como entero ('i') para evitar inyecciones SQL.
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $cita = $consulta->get_result()->fetch_assoc();

    $consulta->close();

    return $cita ?: null;
}

// diegoCar09: Genera e imprime una tabla HTML reutilizable a partir de un arreglo de encabezados y filas.
function imprimirTabla(array $encabezados, array $filas, string $mensajeVacio)
{
    echo '<div class="tabla-contenedor"><table class="tabla-datos"><thead><tr>';

    // Imprime los encabezados de columna escapados.
    foreach ($encabezados as $encabezado) 
    {
        echo '<th>' . escaparDato($encabezado) . '</th>';
    }
    echo '</tr></thead><tbody>';

    // Si no existen registros, muestra una fila única con el mensaje informativo.
    if (count($filas) === 0) 
    {
        echo '<tr class="fila-mensaje"><td colspan="' . count($encabezados) . '">' . escaparDato($mensajeVacio) . '</td></tr>';
    } 
    
    else 
    {
        // Crea una fila por cada elemento y escapa cada celda antes de imprimirla.
        foreach ($filas as $fila) 
        {
            echo '<tr>';
            foreach ($fila as $valor) 
            {
                echo '<td>' . escaparDato($valor) . '</td>';
            }
            echo '</tr>';
        }
    }

    echo '</tbody></table></div>';
}

// diegoCar09: Busca una cita por su ID e imprime tres tablas: detalle de la cita, mascota asociada y dueño asociado.
function imprimirDetalleCita(int $id)
{
    $conexion = obtenerConexion();

    // 1. Consulta los datos principales de la cita solicitada.
    $consulta = $conexion->prepare('SELECT ID_Cita, ID_Mascota, ID_Dueno, Fecha, Hora, Estado FROM Cita WHERE ID_Cita = ?');
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $cita = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    // Si el ID no existe en la base de datos, devuelve código 404 y detiene la ejecución.
    if (!$cita) 
    {
        http_response_code(404);
        echo '<p class="fila-mensaje" role="alert">No se encontró una cita con ese ID.</p>';
        return;
    }

    // 2. Consulta los datos completos de la mascota vinculada a la cita.
    $consulta = $conexion->prepare('SELECT ID_Mascota, Nombre, Genero, Especie, Raza, Peso, FechaDeNacimiento, Estado FROM Mascota
     WHERE ID_Mascota = ?');
    $consulta->bind_param('i', $cita['ID_Mascota']);
    $consulta->execute();
    $mascotas = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    $consulta->close();

    // 3. Consulta los datos completos del dueño vinculado a la cita.
    $consulta = $conexion->prepare(
        'SELECT ID_Dueno, Nombre, Apellidos, Telefono, CorreoElectronico, Direccion, Ciudad FROM Dueno WHERE ID_Dueno = ?'
    );
    $consulta->bind_param('i', $cita['ID_Dueno']);
    $consulta->execute();
    $duenos = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    $consulta->close();

    // Construye e imprime la tabla vertical con la información de la cita.
    echo '<h2>Información de la cita</h2>';
    $filasCita = [
        ['ID de cita', $cita['ID_Cita']],
        ['ID de mascota', $cita['ID_Mascota']],
        ['ID de dueño', $cita['ID_Dueno']],
        ['Fecha', $cita['Fecha']],
        ['Hora', $cita['Hora']],
        ['Estado', $cita['Estado']]
    ];
    imprimirTabla(['Dato', 'Información'], $filasCita, '');

    // Construye e imprime la tabla con los datos de la mascota asociada.
    echo '<h2>Mascota asociada</h2>';
    $filasMascotas = [];
    foreach ($mascotas as $mascota) 
    {
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
        'No se encontró la mascota asociada a esta cita.'
    );

    // Construye e imprime la tabla con los datos del dueño asociado.
    echo '<h2>Dueño asociado</h2>';
    $filasDuenos = [];
    foreach ($duenos as $dueno) 
    {
        $filasDuenos[] = [
            $dueno['ID_Dueno'],
            $dueno['Nombre'],
            $dueno['Apellidos'],
            $dueno['Telefono'],
            $dueno['CorreoElectronico'],
            $dueno['Direccion'],
            $dueno['Ciudad']
        ];
    }
    imprimirTabla(
        ['ID', 'Nombre', 'Apellidos', 'Teléfono', 'Correo electrónico', 'Dirección', 'Ciudad'],
        $filasDuenos,
        'No se encontró el dueño asociado a esta cita.'
    );
}

// diegoCar09: Endpoint POST que elimina una cita después de la confirmación del usuario en IndexCita.html.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar') 
{
    header('Content-Type: application/json; charset=UTF-8');

    // Valida que el ID recibido sea un número entero positivo.
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    if ($id === false || $id === null) 
    {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'ID de cita no válido.']);
        exit;
    }

    try 
    {
        $conexion = obtenerConexion();
        // Ejecuta el borrado seguro mediante consulta preparada.
        $consulta = $conexion->prepare('DELETE FROM Cita WHERE ID_Cita = ?');
        $consulta->bind_param('i', $id);
        $consulta->execute();
        // Verifica si efectivamente se borró al menos una fila.
        $eliminado = $consulta->affected_rows > 0;
        $consulta->close();

        if ($eliminado) 
        {
            echo json_encode(['exito' => true, 'mensaje' => 'Cita eliminada correctamente.']);
        } 
        
        else 
        {
            http_response_code(404);
            echo json_encode(['exito' => false, 'mensaje' => 'No se encontró la cita para eliminar.']);
        }
    } 
    
    catch (mysqli_sql_exception $error) 
    {
        error_log('Error al eliminar la cita: ' . $error->getMessage());
        http_response_code(500);
        echo json_encode(['exito' => false, 'mensaje' => 'Error de base de datos al eliminar la cita.']);
    }
    exit;
}
?>