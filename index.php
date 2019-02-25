<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">
<title>Leer BDD</title>
<style type="text/css">
	body, * { font-family: Verdana;}
	body { margin: 40px;}
	h5 { font-size: 16px; text-align: center; color: #333333;}
	h6 { font-size: 14px; text-align: center; color: #333333;}
	pre { border:3px solid #CCCCCC; font-size: 12px;}
	table { width:60%; margin: 0px auto 0px auto !important;}
	th { background-color: #DEDEDE;}
	td, th { font-size: 14px; color: #333333; border:1px solid #BBBBBB;}
	p { font-size: 14px; text-align: center; margin-bottom: 25px; color: #333333;}
</style>
</head>
<body>
<?php
include('includes/config.php');
include('includes/SurveyMonkey.php');


/* ------ Obtener data desde SurveyMonkey... ------ */

function getSurveyData($sm, $idSurvey, $idCollector){
	$surveys = $sm->getParsedAnswers($idCollector, $idSurvey);
	foreach($surveys as $key){
		$output[] = $key;
	}
	return $output;
}


function insertParsedAnswersFromSM($conDB, $sm, $canal, $idSurvey, $idCollector){
	$total_inserts = 0;
	$surveys = $sm->getParsedAnswers($idCollector, $idSurvey);
	$result = array();
	foreach ($surveys as $survey){
		$status = strtolower($survey['response_status']);
			if($canal == 'web'){
				$codigo = '';
			}
			else{
				$codigo = $survey[138314706]['answers'][0];
			}
		if(!isset($survey['email'])){ $email = '';}
		else{ $email = trim(strtolower($survey['email']));}
		$sql = "INSERT INTO sm_encuestas (id_encuesta, id_respuestas, id_colector, codigo, canal, estado, correo) 
			VALUES ('" . $idSurvey . "', '" . $survey['id'] . "', '" . $idCollector . "', '" . $codigo . "', 
			'" . $canal . "', '" . $status . "', '" . $email . "');";
		if ($conDB->query($sql) === TRUE) {
		    $total_inserts++;
		}
	}
	return $total_inserts;
}


/* ------ API SurveyMonkey ------ */
$sm_apiKey = 'uYbyEgaWDPcrN.7983iPP19fmW3C96Mla5ocjZqAEwQfXPx04LOJbRjO5OkXhqViZkCB8j1KUPNDkpehNwkif-fdqbWI24.k0sJQp8ShFdqmp3Xk.utoyB61rM-qIrTF';
$sm = new \Lyfter\SurveyMonkey('', $sm_apiKey);


/* ------ Encuestas Web ------ */
$idSurvey_web = 155962747;
$idCollector_web = array(0 => 216036603, 1 => 215120815); /* ...Obtenido con $collectors = $sm->getCollectors($idSurvey_web) */


/* ------ Encuestas Call ------ */
$idSurvey_call = 157581598;
$idCollector_call = array(0 => 216372600);


/* ------ Crear tablas ------ */
$drop_table_1 = $conDB->query("DROP TABLE IF EXISTS `sm_encuestas`;");
$drop_table_2 = $conDB->query("DROP TABLE IF EXISTS `sm_encuestas_excel`;");

$create_table_1 = $conDB->query(
		"CREATE TABLE `sm_encuestas` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `id_encuesta` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `id_respuestas` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `id_colector` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `codigo` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `canal` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `estado` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `correo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
		  PRIMARY KEY (`id`) USING BTREE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT"
	);

$create_table_2 = $conDB->query(
		"CREATE TABLE `sm_encuestas_excel` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `codigo` varchar(80) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `canal` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `estado` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `propietario` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
		  `correo` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
		  PRIMARY KEY (`id`) USING BTREE
		) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ROW_FORMAT=COMPACT"
	);

$truncate_table_1 = $conDB->query("TRUNCATE TABLE `sm_encuestas`;");
$truncate_table_2 = $conDB->query("TRUNCATE TABLE `sm_encuestas_excel`;");




/* ------ Usar funcion para obtener data desde SurveyMonkey... ------ */
$inserts_web = insertParsedAnswersFromSM($conDB, $sm, 'web', $idSurvey_web, $idCollector_web[0]);
$inserts_web += insertParsedAnswersFromSM($conDB, $sm, 'web', $idSurvey_web, $idCollector_web[1]);
$inserts_call = insertParsedAnswersFromSM($conDB, $sm, 'call', $idSurvey_call, $idCollector_call[0]);




/* ------ Recorrer Excel ------ */
require('includes/spreadsheet-reader-master/php-excel-reader/excel_reader2.php');
require('includes/spreadsheet-reader-master/SpreadsheetReader.php');
$Filepath = 'excel/bdd_vista_valle.xlsx';	
date_default_timezone_set('UTC');


try{
	$Spreadsheet = new SpreadsheetReader($Filepath);
	$BaseMem = memory_get_usage();
	$Sheets = $Spreadsheet->Sheets();
	$web_and_call = array();
	$updates = array();
	$inserts_excel = 0;

	foreach ($Sheets as $Index => $Name){
	$Time = microtime(true);
	$Spreadsheet -> ChangeSheet($Index);
		foreach ($Spreadsheet as $c => $row){
			if($c > 0){
				$estado = $row[1];
				$canal = $row[1];
				if(strtolower($row[1]) == strtolower('Completa web') || strtolower($row[1]) == strtolower('Completa call')){
					$estado = 'completed';
					if(strtolower($row[1]) == strtolower('Completa web')){
						$canal = 'web';
					}
					if(strtolower($row[1]) == strtolower('Completa call')){
						$canal = 'call';
					}
				}
				if(strtolower($row[1]) == strtolower('Completa web y call')){
					$web_and_call[] = array(
						'codigo' => $row[0],
						'correo' => trim(strtolower($row[4])) 
					);
				}
				$sql = "INSERT INTO sm_encuestas_excel (codigo, canal, estado, propietario, correo) 
					VALUES ('" . $row[0] . "', '" . $canal . "', '" . $estado . "', '" . strtoupper(utf8_decode($row[3])) . "', '" . trim(strtolower($row[4])) . "');";
				if($conDB->query($sql) === TRUE){
				    $inserts_excel++;
				}
			}
		}
	}

	// Crear querys para actualizar los registros cuyo estado sea 'Completa web y call'...
	if(count($web_and_call) > 0){
		$u = 0;
		for($a=0; $a < count($web_and_call); $a++){
			$sql_e = "SELECT COUNT(id) AS total_web_call FROM sm_encuestas 
				WHERE correo='".$web_and_call[$a]['correo']."' OR codigo='".$web_and_call[$a]['codigo']."' AND estado='completed';";
			$total_web_call = 0;
			if($result_e = $conDB->query($sql_e)){
				if (mysqli_num_rows($result_e) > 0){
					while ($row_e = $result_e->fetch_assoc()){
						$total_web_call = $row_e['total_web_call'];
						$updates[$u] = "UPDATE sm_encuestas_excel SET canal='web', estado='completed' 
			    			WHERE correo='".$web_and_call[$a]['correo']."' AND codigo='".$web_and_call[$a]['codigo']."' LIMIT 1;";
			    		$u++;
					}
				}
			}
		}
	}

	// Actualizar los registros con estado 'Completa web y call'...
	for($y=0; $y < count($updates); $y++){
		$update_table_xls = $conDB->query($updates[$y]);
	}
}
catch (Exception $E){
	echo $E -> getMessage();
}



// Mostrar resultados...
$query = "SELECT * FROM sm_encuestas_excel WHERE estado='completed' ORDER BY canal DESC, codigo ASC;";
if($rs = $conDB->query($query)){
	if(mysqli_num_rows($rs) > 0){
		$f = 0; $ct_web = 0; $ct_call = 0;
		$tb_output = '<table border="1" cellspacing="0" cellpadding="5">
			<thead>
				<tr>
					<th>#</th>
					<th>Código</th>
					<th>Canal</th>
					<th>Estado</th>
					<th>Propietario</th>
					<th>Correo</th>
				</tr>
			</thead>
			<tbody>';
		
		while ($row = $rs->fetch_assoc()){
			$tb_output .=  '<tr>
				<td>' . intval($f + 1) . '</td>
				<td>' . $row['codigo'] . '</td>
				<td>' . $row['canal'] . '</td>
				<td>' . $row['estado'] . '</td>
				<td>' . utf8_encode($row['propietario']) . '</td>
				<td>' . $row['correo'] . '</td>
			</tr>';
			if($row['canal'] == 'web'){ $ct_web++;}
			if($row['canal'] == 'call'){ $ct_call++;}
			$f++;
		}
		$tb_output .=  '</tbody>
				</table>';
	
	echo '<h5>BDD : Proyecto Alto Macul - Vista Valle (Inmobiliaria Sinergia)</h5>';
	echo '<p>Completados Web : <strong>' . $ct_web . '</strong><br>';
	echo 'Completados Call : <strong>' . $ct_call . '</strong></p>';
	echo $tb_output;
	}
	
	else{
		echo '<p>No se han encontrado registros...</p>';
	}
}
?>
</body>
</html>