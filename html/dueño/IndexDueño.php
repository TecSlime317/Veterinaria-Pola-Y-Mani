<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Peñaloza: Define la codificación, el diseño adaptable y los estilos compartidos del módulo. -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dueños - Veterinaria Pola y Maní</title>
    <link rel="stylesheet" href="../../css/index.css">
</head>
<body>
    <main class="contenedor">
        <!-- Peñaloza: Encabezado con navegación al inicio, nombre de la sección y acceso al registro. -->
        <header class="barra-superior">
            <a class="boton boton-secundario izquierda" href="../../index.html">Volver</a>
            <h1>
                <span class="huella" aria-hidden="true">🐾</span>
                Dueños
                <span class="huella" aria-hidden="true">🐾</span>
            </h1>
            <a class="boton derecha" href="RegistrarDueño.html">Registrar</a>
        </header>

        <!-- Peñaloza: Tabla del listado; sus diez encabezados coinciden con las columnas generadas por consultasDueño.php. -->
        <div class="tabla-contenedor">
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Edad</th>
                        <th>Teléfono</th>
                        <th>Correo electrónico</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Fecha de inscripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Peñaloza: Incluye el módulo PHP que consulta los dueños e imprime las filas del listado en el servidor. -->
                    <?php require_once __DIR__ . '/../../php/dueño/consultasDueño.php'; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
