<?php
// Recibe el formulario de ActualizarDueño.php y guarda los cambios del dueño en la base de datos.
require_once __DIR__ . '/funcionesDueño.php';

// Este archivo solo debe abrirse desde el formulario (método POST), no escribiendo la dirección.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarMensajeDueno('Método no permitido', 'Esta página solo recibe datos desde el formulario de actualización.', 405);
}

// El ID llega en el campo de solo lectura del formulario. Debe ser un entero mayor o igual a 1.
$id = filter_input(INPUT_POST, 'ID_Dueno', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    mostrarMensajeDueno('ID no válido', 'Se requiere un ID de dueño válido.', 400);
}

// Lee y revisa los datos del formulario (si algo está mal, se detiene aquí con un mensaje).
$dueno = leerDatosFormularioDueno();

try {
    // Primero confirma que el dueño exista.
    if (obtenerDuenoPorId($id) === null) {
        mostrarMensajeDueno('No encontrado', 'No se encontró un dueño con ese ID.', 404);
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = Database::getinstancia()->getConnection();

    // El ID y la fecha de inscripción no se modifican.
    $consulta = $conexion->prepare(
        'UPDATE dueno
         SET Nombre = ?, Apellidos = ?, Edad = ?, Telefono = ?, CorreoElectronico = ?, Direccion = ?, Ciudad = ?
         WHERE ID_Dueno = ?'
    );

    // Tipos en orden: siete datos (s = texto, i = entero) y al final el ID (i).
    $consulta->bind_param(
        'ssissssi',
        $dueno['Nombre'],
        $dueno['Apellidos'],
        $dueno['Edad'],
        $dueno['Telefono'],
        $dueno['CorreoElectronico'],
        $dueno['Direccion'],
        $dueno['Ciudad'],
        $id
    );
    $consulta->execute();
    $consulta->close();

    // Si todo salió bien, regresa al listado de dueños.
    header('Location: ../../html/dueño/IndexDueño.php', true, 303);
    exit;
} catch (mysqli_sql_exception $error) {
    // El detalle técnico queda en el registro del servidor y el usuario ve un mensaje sencillo.
    error_log('Error al actualizar el dueño: ' . $error->getMessage());
    mostrarMensajeDueno('No se pudo actualizar', 'Ocurrió un error al guardar los cambios del dueño.', 500);
}
