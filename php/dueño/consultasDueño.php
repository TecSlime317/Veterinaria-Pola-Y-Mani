<?php
// Gael: Carga la clase compartida para obtener la conexión con la base de datos.
require_once __DIR__ . '/../conexion.php';

// Gael: Recupera todos los dueños con los campos que se muestran en la tabla de administración.
function obtenerDuenos() {
    $db = Database::getinstancia()->getConnection();

    // Gael: Selecciona los datos requeridos de la tabla Dueno.
    $sql = 'SELECT ID_Dueno, Nombre, Apellidos, Edad, Telefono, CorreoElectronico, Direccion, Ciudad, FechaDeInscripcion
            FROM Dueno';

    $stmt = $db->prepare($sql);

    // Gael: Inicializa la colección que almacenará las filas devueltas por la consulta.
    $duenos = array();

    // Gael: Si la consulta pudo prepararse, la ejecuta y recorre el resultado fila por fila.
    if ($stmt) {
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Gael: Agrega cada registro asociativo a la colección de dueños.
        while ($fila = $resultado->fetch_assoc()) {
            $duenos[] = $fila;
        }

        // Gael: Libera los recursos asociados a la consulta preparada.
        $stmt->close();
    }

    // Gael: Devuelve la colección de registros obtenidos.
    return $duenos;
}

// Gael: Obtiene los dueños y genera una fila HTML por cada registro para la página.
$duenos = obtenerDuenos();

foreach ($duenos as $d) {
    // Gael: Imprime las celdas con los datos del dueño y los controles disponibles en la fila.
    echo '<tr>';
    echo '<td>' . $d['ID_Dueno'] . '</td>';
    echo '<td>' . $d['Nombre'] . '</td>';
    echo '<td>' . $d['Apellidos'] . '</td>';
    echo '<td>' . $d['Edad'] . '</td>';
    echo '<td>' . $d['Telefono'] . '</td>';
    echo '<td>' . $d['CorreoElectronico'] . '</td>';
    echo '<td>' . $d['Direccion'] . '</td>';
    echo '<td>' . $d['Ciudad'] . '</td>';
    echo '<td>' . $d['FechaDeInscripcion'] . '</td>';
    echo '<td>';
    echo '<details>';
    echo '<summary>Opciones</summary>';
    echo '<button type="button">Ver</button>';
    echo '<button type="button">Editar</button>';
    echo '<button type="button">Eliminar</button>';
    echo '</details>';
    echo '</td>';
    echo '</tr>';
}