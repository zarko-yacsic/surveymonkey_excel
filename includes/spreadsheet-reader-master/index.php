<?php
// If you need to parse XLS files, include php-excel-reader
require('php-excel-reader/excel_reader2.php');
require('SpreadsheetReader.php');

$preguntaUno    = 13;
$idEncuesta     = 1;

$data          = array();
$pregunta      = array();
$pregunta1     = array();
$pregunta2     = array();
$a             = 0;
$b             = 0;
$Reader = new SpreadsheetReader('archivo.xlsx');
foreach ($Reader as $Row){
	$a ++;
	$b = 0;
	$totalColumnas = count($Row);
	for ($i=0;$i<count($Row);$i++) {
		$b ++;
		$data[$a][$b] = $Row[$i];
	}
}
$ancho   = $totalColumnas;
$alto    = count($data);

# ======================================
# Sacamos valores de campos
# ======================================

for ($a=1;$a<=2;$a++) {
	for ($i=$preguntaUno;$i<=$ancho;$i++) {
		if ($data[1][$i] != '') {
			$pregunta1[$i] = 1;
		}else{
			$pregunta1[$i] = 0;
		}
		if ($data[2][$i] != '') {
			$pregunta2[$i] = 1;
		}else{
			$pregunta2[$i] = 0;
		}
	}
}

$c                  = 0;
$d                  = 0;
$nomP               = '';
$preguntaX          = array();
$preguntaN          = array();
$preguntaSN         = array();
$preguntaNom        = array();
$subPreguntaNom     = array();

for ($i=$preguntaUno;$i<=$ancho;$i++) {
	$viene = 0;
	if ($pregunta1[$i]<$ancho) {
		$viene = 1;
	}

	$valor              = $data[2][$i];
	$valor              = str_replace("'", "´", $valor);
	$valor              = str_replace('"', "´", $valor);
	$valor              = utf8_decode($valor);
	$subPreguntaNom[$i] = $valor;

	$valor = $i + $viene;
	if ( ($pregunta1[$i] == 1 && $pregunta1[$valor] == 1) || $i == $ancho ) {
		$d = 0;
		$c ++;
		$preguntaX[$i]  = 'PA'.$c;
		$preguntaN[$i]  = $c;
		$preguntaSN[$i] = 0;

		$valor              = $data[1][$i];
		$valor              = str_replace("'", "´", $valor);
		$valor              = str_replace('"', "´", $valor);
		$valor              = utf8_decode($valor);
		$nomP               = $valor;
		$preguntaNom[$i]    = $nomP;

	}else if ($pregunta1[$i] == 1) {
		$d = 0;
		$c ++;
		$d ++;
		$preguntaX[$i]  = 'PA'.$c."_".$d;
		$preguntaN[$i]  = $c;
		$preguntaSN[$i] = $d;

		$valor           = $data[1][$i];
		$valor           = str_replace("'", "´", $valor);
		$valor           = str_replace('"', "´", $valor);
		$valor           = utf8_decode($valor);
		$nomP            = $valor;
		$preguntaNom[$i] = $nomP;

	}else{
		$d ++;
		$preguntaX[$i] = 'PA'.$c."_".$d;
		$preguntaN[$i] = $c;
		$preguntaSN[$i] = $d;

		$preguntaNom[$i] = $nomP;
	}
}

$e      = 0;
$nomP   = '';
for ($i=1;$i<$preguntaUno;$i++) {
	$e ++;
	$preguntaX[$i]  = 'M'.$e;
	$preguntaN[$i]  = 0;
	$preguntaSN[$i] = 0;


	$valor           = $data[1][$i];

	if ($valor=='') {
		$preguntaNom[$i] = $nomP;
	}else{
		$valor           = str_replace("'", "´", $valor);
		$valor           = str_replace('"', "´", $valor);
		$valor           = utf8_decode($valor);
		$nomP            = $valor;
		$preguntaNom[$i] = $nomP;
	}

	$valor              = $data[2][$i];
	$valor              = str_replace("'", "´", $valor);
	$valor              = str_replace('"', "´", $valor);
	$valor              = utf8_decode($valor);
	$subPreguntaNom[$i] = $valor;
}

# ==============================
# Info por columnas
# ==============================
$preguntaLargo  = array();
for ($a=3;$a<=$alto;$a++) {
	for ($i=1;$i<=$ancho;$i++) {
		if ( (strlen(utf8_decode($data[$a][$i])) > $preguntaLargo[$i]) || !isset($preguntaLargo[$i]) ) {
			$preguntaLargo[$i] = strlen(utf8_decode($data[$a][$i]));
		}

	}
}


$campos = '';
for ($i=1;$i<=$ancho;$i++) {

	$campoNom      = $preguntaX[$i];
	$largoCampo    = $preguntaLargo[$i];
	if ($largoCampo <= 250) {
		$campos = $campos."`$campoNom` varchar($largoCampo) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,";
	}else{
		$campos = $campos."`$campoNom` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,";
	}
}


# =================================================
# Crear tabla
# =================================================
$conDB = new mysqli($bd_host, $bd_user, $bd_pass, $bd_name);

// Check connection
if ($conDB->connect_error) {
    die('No pudo conectarse: ' . $conDB->connect_error);
}

$sql = "DROP TABLE IF EXISTS `at_maestra`";
$conDB->query($sql);
$sql = "DROP TABLE IF EXISTS `at_maestro_preguntas`";
$conDB->query($sql);

$sql = "CREATE TABLE `at_maestro_preguntas`  (
					  `idPreguntas` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					  `nomCampo` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
					  `numCampo` int(11) UNSIGNED NULL DEFAULT NULL,
					  `numpregunta` int(11) UNSIGNED NULL DEFAULT 0,
					  `numSubPregunta` int(11) UNSIGNED NULL DEFAULT 0,
					  `pregunta` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
					  `subPregunta` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
					  `idPregunta` int(11) UNSIGNED NULL DEFAULT 0,
					  `idSubPregunta` int(11) UNSIGNED NULL DEFAULT 0,
					  `categoria` int(11) UNSIGNED NULL DEFAULT 0,
					  PRIMARY KEY (`idPreguntas`) USING BTREE,
					  UNIQUE INDEX `nomCampo`(`nomCampo`) USING BTREE,
					  INDEX `numCampo`(`numCampo`) USING BTREE,
					  INDEX `numpregunta`(`numpregunta`) USING BTREE,
					  INDEX `numSubPregunta`(`numSubPregunta`) USING BTREE,
					  INDEX `pregunta`(`pregunta`) USING BTREE,
					  INDEX `subPregunta`(`subPregunta`) USING BTREE,
					  INDEX `categoria`(`categoria`) USING BTREE,
					  INDEX `idPregunta`(`idPregunta`) USING BTREE,
					  INDEX `idSubPregunta`(`idSubPregunta`) USING BTREE
					) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;";

if ($conDB->query($sql) !== TRUE) {
    echo "Error creating table at_maestro_preguntas<br>" . $conDB->error;
    exit;
}

$sql = "CREATE TABLE `at_maestra`  (
  `idArmado` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `idEncuesta` int(11) UNSIGNED NULL DEFAULT NULL,
  $campos
  PRIMARY KEY (`idArmado`) USING BTREE,
  INDEX `idEncuesta`(`idEncuesta`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Compact;";

if ($conDB->query($sql) === TRUE) {
    echo "Table creada...<br>";
} else {
    echo "Error creating table at_maestra<br>" . $conDB->error;
    exit;
}

# =================================================
# Insertar campos en la tabla
# =================================================

$totalInsert = 0;
$f           = 0;
for ($a=3;$a<=$alto;$a++) {
	$f ++;
	$campito = '';
	for ($i=1;$i<=$ancho;$i++) {
		$valor = utf8_decode($data[$a][$i]);
		$valor = str_replace("'", "´", $valor);
		$valor = str_replace('"', "´", $valor);
		$campito = $campito.",'".$valor."'";
	}
	$sql = "INSERT INTO at_maestra VALUES ($f, $idEncuesta $campito); ";
	if ($conDB->query($sql) === TRUE) {
	    $totalInsert ++;
	}
}

if ($totalInsert == ($alto-2) ) {
	echo "Todas insertadas<br>";
}else{
	echo "Error al insertar";
	exit;
}

# ==============================
# Valor de los campos
# ==============================

for ($i=1;$i<=$ancho;$i++) {

	$sql = "INSERT INTO at_maestro_preguntas
	                  (numCampo, nomCampo, numpregunta, numSubPregunta, pregunta, subPregunta)
	           VALUES ($i,'".$preguntaX[$i]."', ".$preguntaN[$i].", ".$preguntaSN[$i].", '".$preguntaNom[$i]."', '".$subPreguntaNom[$i]."'); ";

	if ($conDB->query($sql) !== TRUE) {
		echo "falla insert";
		exit;
	}

}


echo "<br>avance...";

?>
