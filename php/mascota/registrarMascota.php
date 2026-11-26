<?php
require_once __DIR__ . '/../conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

$idDueno = filter_input(INPUT_POST, 'ID_Dueno', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1]
]);
$nombre = trim($_POST['Nombre'] ?? '');
$genero = trim($_POST['Genero'] ?? '');
$especie = trim($_POST['Especie'] ?? '');
$raza = trim($_POST['Raza'] ?? '');
$fechaNacimiento = trim($_POST['FechaDeNacimiento'] ?? '');
$peso = filter_input(INPUT_POST, 'Peso', FILTER_VALIDATE_FLOAT);

if (
    $idDueno === false || $idDueno === null ||
    $nombre === '' || $genero === '' || $especie === '' ||
    $fechaNacimiento === '' || $peso === false || $peso === null
) {
    http_response_code(400);
    exit('Completa los campos obligatorios con valores válidos.');
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = Database::getinstancia()->getConnection();
    $consulta = $conexion->prepare(
        'INSERT INTO mascota
            (ID_Dueno, Nombre, Genero, Especie, Raza, FechaDeNacimiento, Peso)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $consulta->bind_param(
        'isssssd',
        $idDueno,
        $nombre,
        $genero,
        $especie,
        $raza,
        $fechaNacimiento,
        $peso
    );
    $consulta->execute();
    $consulta->close();

    header('Location: ../../html/mascota/IndexMascota.html', true, 303);
    exit;
} catch (mysqli_sql_exception $error) {
    error_log('Error al registrar la mascota: ' . $error->getMessage());
    http_response_code(500);
    exit('No se pudo registrar la mascota. Verifica que el ID del dueño exista.');
}
