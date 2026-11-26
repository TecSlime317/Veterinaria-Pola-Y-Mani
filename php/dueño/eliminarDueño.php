<?php
// Recibe la confirmación de EliminarDueño.php y borra al dueño de la base de datos.
require_once __DIR__ . '/funcionesDueño.php';

// Este archivo solo debe abrirse desde el botón de confirmación (método POST), no escribiendo la dirección.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarMensajeDueno('Método no permitido', 'Esta página solo recibe datos desde la pantalla de confirmación.', 405);
}

// El ID llega en un campo oculto del formulario. Debe ser un entero mayor o igual a 1.
$id = filter_input(INPUT_POST, 'ID_Dueno', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

if ($id === false || $id === null) {
    mostrarMensajeDueno('ID no válido', 'Se requiere un ID de dueño válido.', 400);
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = Database::getinstancia()->getConnection();

    $consulta = $conexion->prepare('DELETE FROM dueno WHERE ID_Dueno = ?');
    $consulta->bind_param('i', $id);
    $consulta->execute();

    // affected_rows indica cuántas filas se borraron. Si fue 0, ese ID no existía.
    $seBorro = $consulta->affected_rows > 0;
    $consulta->close();

    if (!$seBorro) {
        mostrarMensajeDueno('No encontrado', 'No se encontró un dueño con ese ID.', 404);
    }

    // Si todo salió bien, regresa al listado de dueños.
    header('Location: ../../html/dueño/IndexDueño.php', true, 303);
    exit;
} catch (mysqli_sql_exception $error) {
    // El código 1451 de MySQL significa que otra tabla (Mascota o Cita) todavía usa a este dueño.
    if ($error->getCode() === 1451) {
        mostrarMensajeDueno(
            'No se puede eliminar',
            'Este dueño tiene mascotas o citas registradas. Elimínalas primero y vuelve a intentarlo.',
            409
        );
    }

    // Cualquier otro error: el detalle técnico queda en el registro del servidor.
    error_log('Error al eliminar el dueño: ' . $error->getMessage());
    mostrarMensajeDueno('No se pudo eliminar', 'Ocurrió un error al eliminar el dueño.', 500);
}
