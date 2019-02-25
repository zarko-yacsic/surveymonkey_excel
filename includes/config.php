<?php
$url_base = 'https://zyacsic.desarrollo.cool/leer_bdd/'; 

// Conexion a BD...
$bd_host = 'localhost';
$bd_name = 'db_zyacsic';
$bd_user = 'dev_zyacsic';
$bd_pass = 'WfpB9@C2';


$conDB = new mysqli($bd_host, $bd_user, $bd_pass, $bd_name);
if ($conDB->connect_error) {
    die('Error en la conexion a BD : ' . $conDB->connect_error);
}

$conectaDB = mysqli_connect($bd_host, $bd_user, $bd_pass, $bd_name);
if (!$conectaDB) {
    die('Error en la conexion a BD : ' . mysqli_connect_error());
}
?>