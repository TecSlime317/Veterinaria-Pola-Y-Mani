<?php
// Carga las funciones compartidas del módulo de dueños.
require_once __DIR__ . '/../../php/dueño/funcionesDueño.php';

// El ID llega por la URL (por ejemplo ActualizarDueño.php?id=10000001). Debe ser un entero mayor o igual a 1.
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$dueno = null;
$mensajeError = '';

if ($id === false || $id === null) {
    http_response_code(400);
    $mensajeError = 'Se requiere un ID de dueño válido.';
} else {
    try {
        // Busca los datos actuales del dueño para llenar el formulario.
        $dueno = obtenerDuenoPorId($id);

        if ($dueno === null) {
            http_response_code(404);
            $mensajeError = 'No se encontró un dueño con ese ID.';
        }
    } catch (mysqli_sql_exception $error) {
        // El detalle técnico queda en el registro del servidor y el usuario ve un mensaje sencillo.
        error_log('Error al consultar el dueño para actualizarlo: ' . $error->getMessage());
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
    <title>Actualizar dueño - Veterinaria Pola y Maní</title>
    <link rel="stylesheet" href="../../css/index.css">
</head>
<body>
    <main class="contenedor contenedor-angosto">

        <header class="encabezado">
            <h1>Actualizar dueño</h1>
        </header>

        <?php if ($mensajeError !== '') : ?>

            <!-- Si no hay un dueño que editar, solo se muestra el aviso y el botón para volver. -->
            <p class="fila-mensaje" role="alert"><?= valorCampo($mensajeError) ?></p>

            <div class="acciones-formulario">
                <a class="boton boton-secundario" href="IndexDueño.php">Volver</a>
            </div>

        <?php else : ?>

            <!-- Formulario de actualización. Cada campo se llena con el valor actual del dueño
                 usando PHP (valorCampo). Los nombres (atributo name) son los que recibe actualizarDueño.php en $_POST. -->
            <form class="formulario" action="../../php/dueño/actualizarDueño.php" method="post">

                <!-- ID del dueño. Es de solo lectura (readonly) y se envía con el formulario
                     para que PHP sepa qué registro actualizar. -->
                <div class="campo">
                    <label for="idDueno">ID *</label>
                    <input type="text" id="idDueno" name="ID_Dueno" value="<?= valorCampo($dueno['ID_Dueno']) ?>" readonly>
                </div>

                <div class="campo">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="Nombre" value="<?= valorCampo($dueno['Nombre']) ?>" maxlength="100" required>
                </div>

                <div class="campo">
                    <label for="apellidos">Apellidos</label>
                    <input type="text" id="apellidos" name="Apellidos" value="<?= valorCampo($dueno['Apellidos']) ?>" maxlength="150" required>
                </div>

                <div class="campo">
                    <label for="edad">Edad</label>
                    <input type="number" id="edad" name="Edad" value="<?= valorCampo($dueno['Edad']) ?>" min="0" max="120">
                </div>

                <div class="campo">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="Telefono" value="<?= valorCampo($dueno['Telefono']) ?>" maxlength="20">
                </div>

                <div class="campo">
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="CorreoElectronico" value="<?= valorCampo($dueno['CorreoElectronico']) ?>" maxlength="254" required>
                </div>

                <div class="campo">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="Direccion" value="<?= valorCampo($dueno['Direccion']) ?>" maxlength="255">
                </div>

                <div class="campo">
                    <label for="ciudad">Ciudad</label>
                    <input type="text" id="ciudad" name="Ciudad" value="<?= valorCampo($dueno['Ciudad']) ?>" maxlength="100">
                </div>

                <!-- Nota que explica el asterisco del campo ID. -->
                <p class="nota">* El ID se muestra para saber qué dueño se está modificando, pero no puede cambiarse.</p>

                <!-- "Cancelar" regresa al listado sin guardar; "Confirmar" envía los datos al servidor. -->
                <div class="acciones-formulario">
                    <a class="boton boton-secundario" href="IndexDueño.php">Cancelar</a>
                    <button class="boton" type="submit">Confirmar</button>
                </div>
            </form>

        <?php endif; ?>

    </main>
</body>
</html>
