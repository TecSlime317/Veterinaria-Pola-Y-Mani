<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Peñaloza: Configura la página y carga la hoja de estilos compartida por el sistema. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leer registro - Veterinaria Pola y Maní</title>
    <link rel="stylesheet" href="../../css/index.css">
</head>
<body>
    <main class="contenedor">
        <!-- Peñaloza: Presenta el título de la vista de lectura del dueño seleccionado. -->
        <header class="encabezado">
            <h1>
                <span class="huella" aria-hidden="true">🐾</span>
                Leer registro
                <span class="huella" aria-hidden="true">🐾</span>
            </h1>
        </header>

        <!-- Peñaloza: Carga las consultas y la salida HTML del detalle; el ID llega por la URL y se procesa en PHP. -->
        <?php require_once __DIR__ . '/../../php/dueño/consultasDueño.php'; ?>

        <!-- Peñaloza: Regresa al listado, que ahora también se procesa como una página PHP. -->
        <div class="acciones-formulario">
            <button class="btn btn-primary">
            <a class="boton boton-secundario" href="IndexDueño.php">Volver</a>
        </div>
    </main>
</body>
</html>
