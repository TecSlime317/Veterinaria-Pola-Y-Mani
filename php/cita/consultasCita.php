<?php
// tecprime: Carga la clase compartida para obtener la conexión con la base de datos.
require_once __DIR__ . '/../conexionejemplo.php';

// tecprime: Recupera todos los dueños con los campos que se muestran en la tabla de administración.
function obtenerCitas() {
    $db = Database::getinstancia()->getConnection();

    // tecprime: Selecciona los datos requeridos de la tabla cita.
    $sql = 'SELECT ID_Cita, ID_Mascota, ID_Dueno, Fecha, Hora, Estado
            FROM cita';

    $stmt = $db->prepare($sql);

    // tecprime: Inicializa la colección que almacenará las filas devueltas por la consulta.
    $citas = array();

    // tecprime: Si la consulta pudo prepararse, la ejecuta y recorre el resultado fila por fila.
    if ($stmt) {
        $stmt->execute();
        $resultado = $stmt->get_result();

        // tecprime: Agrega cada registro asociativo a la colección de citas.
        while ($fila = $resultado->fetch_assoc()) {
            $citas[] = $fila;
        }

        // tecprime: Libera los recursos asociados a la consulta preparada.
        $stmt->close();
    }

    // tecprime: Devuelve la colección de registros obtenidos.
    return $citas;
}

// tecprime: Obtiene las citas y genera una fila HTML por cada registro para la página.
$citas = obtenerCitas();

foreach ($citas as $d) {
    // tecprime: Imprime las celdas con los datos del dueño y los controles disponibles en la fila.
    echo '<tr>';
    echo '<td>' . $d['ID_Cita'] . '</td>';
    echo '<td>' . $d['ID_Mascota'] . '</td>';
    echo '<td>' . $d['ID_Dueno'] . '</td>';
    echo '<td>' . $d['Fecha'] . '</td>';
    echo '<td>' . $d['Hora'] . '</td>';
    echo '<td>' . $d['Estado'] . '</td>';
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