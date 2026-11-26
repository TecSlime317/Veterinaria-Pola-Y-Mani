<?php
// Recibe el formulario de RegistrarDueño.html y guarda un dueño nuevo en la base de datos.
require_once __DIR__ . '/funcionesDueño.php';

// Este archivo solo debe abrirse desde el formulario (método POST), no escribiendo la dirección.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    mostrarMensajeDueno('Método no permitido', 'Esta página solo recibe datos desde el formulario de registro.', 405);
}

// Lee y revisa los datos del formulario (si algo está mal, se detiene aquí con un mensaje).
$dueno = leerDatosFormularioDueno();

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = Database::getinstancia()->getConnection();

    // El ID lo genera la base de datos (AUTO_INCREMENT) y la fecha de inscripción es la de hoy (CURDATE()).
    $consulta = $conexion->prepare(
        'INSERT INTO dueno (Nombre, Apellidos, Edad, Telefono, CorreoElectronico, Direccion, Ciudad, FechaDeInscripcion)
         VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE())'
    );

    // Tipos de los siete datos en orden: s = texto, i = entero.
    $consulta->bind_param(
        'ssissss',
        $dueno['Nombre'],
        $dueno['Apellidos'],
        $dueno['Edad'],
        $dueno['Telefono'],
        $dueno['CorreoElectronico'],
        $dueno['Direccion'],
        $dueno['Ciudad']
    );
    $consulta->execute();
    $consulta->close();

    // Si todo salió bien, regresa al listado de dueños.
    header('Location: ../../html/dueño/IndexDueño.php', true, 303);
    exit;
} catch (mysqli_sql_exception $error) {
    // El detalle técnico queda en el registro del servidor y el usuario ve un mensaje sencillo.
    error_log('Error al registrar el dueño: ' . $error->getMessage());
    mostrarMensajeDueno('No se pudo registrar', 'Ocurrió un error al guardar el dueño.', 500);
}
