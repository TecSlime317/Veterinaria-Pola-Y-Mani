<?php
// awa123691: Carga la conexión común del sistema y prepara las consultas de mascotas.
require_once __DIR__ . '/../conexion.php';

// awa123691: Recupera todas las mascotas con los campos que se muestran en la tabla de administración.
function obtenermascota() {
    $db = Database::getinstancia()->getConnection();

    // awa123691: Selecciona los datos requeridos de la tabla mascota.
    $sql = 'SELECT ID_Mascota,ID_Dueno,Nombre,Genero,Especie,Raza,Peso,FechaDeNacimiento,Estado
            FROM mascota';

    $stmt = $db->prepare($sql);

    // awa123691: Inicializa la colección que almacenará las filas devueltas por la consulta.
    $mascota = array();

    // awa123691: Si la consulta pudo prepararse, la ejecuta y recorre el resultado fila por fila.
    if ($stmt) {
        $stmt->execute();
        $resultado = $stmt->get_result();

        // awa123691: Agrega cada registro asociativo a la colección de mascotas.
        while ($fila = $resultado->fetch_assoc()) {
            $mascota[] = $fila;
        }

        // awa123691: Libera los recursos asociados a la consulta preparada.
        $stmt->close();
    }

    // awa123691: Devuelve la colección de registros obtenidos.
    return $mascota;
}

// awa123691: Obtiene las mascotas consultadas y genera una fila HTML por registro para el listado.
$mascota = obtenermascota();

foreach ($mascota as $m) {
    // awa123691: Imprime las celdas con los datos dela mascota y los controles disponibles en la fila.
    echo '<tr>';
    echo '<td>' . $m['ID_Mascota'] . '</td>';
    echo '<td>' . $m['ID_Dueno'] . '</td>';
    echo '<td>' . $m['Nombre'] . '</td>';
    echo '<td>' . $m['Genero'] . '</td>';
    echo '<td>' . $m['Especie'] . '</td>';
    echo '<td>' . $m['Raza'] . '</td>';
    echo '<td>' . $m['Peso'] . '</td>';
    echo '<td>' . $m['FechaDeNacimiento'] . '</td>';
    echo '<td>' . $m['Estado'] . '</td>';
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