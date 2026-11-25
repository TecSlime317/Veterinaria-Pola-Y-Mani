<?php
require_once __DIR__ . '/../conexion.php';
$_Nombre = $_POST['Nombre'];
$_Genero = $_POST['Genero'];
$_Especie = $_POST['Especie'];
$_Raza = $_POST['Raza'];
$_Fecha =$_POST["FechaDeNacimiento"];
$_Peso =$_POST["Peso"];
 

$db = Database::getinstancia()->getConnection();

$sql ="INSERT INTO mascota
(Nombre,Genero,Especie,Raza,FechaDeNacimiento,Peso)
 VALUES (?, ?, ?, ?, ?, ?)";

?>