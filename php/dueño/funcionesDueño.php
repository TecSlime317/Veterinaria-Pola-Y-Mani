<?php
// Funciones compartidas por registrarDueño.php, actualizarDueño.php, eliminarDueño.php
// y por las páginas ActualizarDueño.php y EliminarDueño.php.
// Este archivo solo define funciones: al incluirlo no imprime nada.

// Carga la clase compartida que abre y entrega la conexión con MySQL.
require_once __DIR__ . '/../conexion.php';

// Busca un dueño por su ID. Devuelve sus datos en un arreglo, o null si el ID no existe.
function obtenerDuenoPorId(int $id): ?array
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = Database::getinstancia()->getConnection();

    // El signo ? se reemplaza con el ID que llega como parámetro.
    $consulta = $conexion->prepare(
        'SELECT ID_Dueno, Nombre, Apellidos, Edad, Telefono, CorreoElectronico, Direccion, Ciudad, FechaDeInscripcion
         FROM dueno
         WHERE ID_Dueno = ?'
    );
    $consulta->bind_param('i', $id);
    $consulta->execute();
    $dueno = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    // fetch_assoc devuelve null cuando no encuentra el registro.
    return $dueno;
}

// Convierte un valor de la base de datos en texto seguro para ponerlo dentro de un campo del formulario.
// Si el valor es NULL devuelve texto vacío, para que el campo aparezca en blanco.
function valorCampo($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Lee y revisa los datos que llegan del formulario de registrar o de actualizar.
// Si falta algo obligatorio o algo no es válido, muestra un mensaje y detiene el programa.
// Si todo está bien, devuelve los datos limpios en un arreglo.
function leerDatosFormularioDueno(): array
{
    // trim quita los espacios sobrantes al principio y al final.
    $nombre = trim($_POST['Nombre'] ?? '');
    $apellidos = trim($_POST['Apellidos'] ?? '');
    $edadTexto = trim($_POST['Edad'] ?? '');
    $telefono = trim($_POST['Telefono'] ?? '');
    $correo = trim($_POST['CorreoElectronico'] ?? '');
    $direccion = trim($_POST['Direccion'] ?? '');
    $ciudad = trim($_POST['Ciudad'] ?? '');

    // Estos tres campos son obligatorios en el formulario.
    if ($nombre === '' || $apellidos === '' || $correo === '') {
        mostrarMensajeDueno('Faltan datos', 'El nombre, los apellidos y el correo son obligatorios.', 400);
    }

    // La edad es opcional. Si se escribió, debe ser un número entero entre 0 y 120.
    $edad = null;
    if ($edadTexto !== '') {
        $edad = filter_var($edadTexto, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 120]]);
        if ($edad === false) {
            mostrarMensajeDueno('Edad no válida', 'La edad debe ser un número entero entre 0 y 120.', 400);
        }
    }

    // Los campos opcionales que quedaron vacíos se guardan como NULL en la base de datos.
    return [
        'Nombre' => $nombre,
        'Apellidos' => $apellidos,
        'Edad' => $edad,
        'Telefono' => ($telefono === '' ? null : $telefono),
        'CorreoElectronico' => $correo,
        'Direccion' => ($direccion === '' ? null : $direccion),
        'Ciudad' => ($ciudad === '' ? null : $ciudad)
    ];
}

// Muestra una página completa con un mensaje y un botón para volver al listado, y detiene el programa.
// Se usa desde los archivos de php/dueño/, por eso las rutas suben dos niveles (../../).
function mostrarMensajeDueno(string $titulo, string $mensaje, int $codigoHttp)
{
    http_response_code($codigoHttp);
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= valorCampo($titulo) ?> - Veterinaria Pola y Maní</title>
    <link rel="stylesheet" href="../../css/index.css">
</head>
<body>
    <main class="contenedor contenedor-angosto">
        <header class="encabezado">
            <h1><?= valorCampo($titulo) ?></h1>
        </header>

        <p class="fila-mensaje" role="alert"><?= valorCampo($mensaje) ?></p>

        <div class="acciones-formulario">
            <a class="boton boton-secundario" href="../../html/dueño/IndexDueño.php">Volver al listado</a>
        </div>
    </main>
</body>
</html>
    <?php
    exit;
}
