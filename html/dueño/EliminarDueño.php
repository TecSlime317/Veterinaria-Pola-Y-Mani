<?php
// Carga las funciones compartidas del módulo de dueños.
require_once __DIR__ . '/../../php/dueño/funcionesDueño.php';

// El ID llega por la URL (por ejemplo EliminarDueño.php?id=10000001). Debe ser un entero mayor o igual a 1.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$dueno = null;
$mensajeError = '';

if ($id === false || $id === null) {
    http_response_code(400);
    $mensajeError = 'Se requiere un ID de dueño válido.';
} else {
    try {
        // Busca al dueño para mostrar su nombre en la pantalla de confirmación.
        $dueno = obtenerDuenoPorId($id);

        if ($dueno === null) {
            http_response_code(404);
            $mensajeError = 'No se encontró un dueño con ese ID.';
        }
    } catch (mysqli_sql_exception $error) {
        // El detalle técnico queda en el registro del servidor y el usuario ve un mensaje sencillo.
        error_log('Error al consultar el dueño para eliminarlo: ' . $error->getMessage());
        http_response_code(500);
        $mensajeError = 'Ocurrió un error al consultar la información del dueño.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar dueño - Veterinaria Pola y Maní</title>
    <link rel="stylesheet" href="../../css/index.css">
</head>
<body>
    <main class="contenedor contenedor-angosto">

        <header class="encabezado">
            <h1>Eliminar dueño</h1>
        </header>

        <?php if ($mensajeError !== '') : ?>

            <!-- Si no hay un dueño que eliminar, solo se muestra el aviso y el botón para volver. -->
            <p class="fila-mensaje" role="alert"><?= valorCampo($mensajeError) ?></p>

            <div class="acciones-formulario">
                <a class="boton boton-secundario" href="IndexDueño.php">Volver</a>
            </div>

        <?php else : ?>

            <!-- Pantalla de confirmación: sin JavaScript, la pregunta se hace en una página aparte.
                 El botón "Eliminar" envía el ID a eliminarDueño.php, que es quien borra el registro. -->
            <p>
                ¿Seguro que deseas eliminar al dueño
                <strong><?= valorCampo($dueno['Nombre'] . ' ' . $dueno['Apellidos']) ?></strong>
                (ID <?= valorCampo($dueno['ID_Dueno']) ?>)? Esta acción no se puede deshacer.
            </p>
            <p class="nota">Si el dueño tiene mascotas o citas registradas, no podrá eliminarse hasta borrarlas.</p>

            <form class="formulario" action="../../php/dueño/eliminarDueño.php" method="post">
                <!-- Campo oculto: lleva el ID del dueño al archivo PHP que lo elimina. -->
                <input type="hidden" name="ID_Dueno" value="<?= valorCampo($dueno['ID_Dueno']) ?>">

                <div class="acciones-formulario">
                    <a class="boton boton-secundario" href="IndexDueño.php">Cancelar</a>
                    <button class="boton" type="submit">Eliminar</button>
                </div>
            </form>

        <?php endif; ?>

    </main>
</body>
</html>
