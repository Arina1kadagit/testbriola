<?php require_once("includes/connection.php"); ?>

<?php

	function charToHex($matches) {
		//print_r($matches);
		//printf("%02x",ord($matches[0]));
		//echo "</br>";
		return sprintf("%02x",ord($matches[0]));
	}
	// вспомогательная функция, возвращает шестнадцатеричный дамп строки
	// входящая строка посимвольно отправляется в функцию temp, которая возвращает шестнадцатиричное значение символа и заменяет им символ в исходной строке, формируя конечную возвращаемую строку
	function hex_dump($str) {
		//echo(preg_replace_callback('{.}s', "charToHex", $str));
		return preg_replace_callback('{.}s', "charToHex", $str);
	}
	


	session_start();
	header('Content-type: text/html; charset=utf-8');
	//header('Content-Type: application/json;charset=utf-8');
	header('Accept: application/json');

	$mysqlQText = "CREATE TABLE IF NOT EXISTS `nomenclaturegroups` (
									`guid` varchar(36) NOT NULL,
									`fullname` varchar(100) NOT NULL,
									`parentguid` varchar(36),
									`isdeleted` boolean NOT NULL,
									`picture` mediumblob NOT NULL,
									PRIMARY KEY (guid)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось создать таблицу групп номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;

	// Получаем данные для помещения в таблицу номенклатуры
	$postData = file_get_contents('php://input');
	$data = json_decode($postData, true);
	// Формат для вставки типа BLOB
	$picHexData = ($data['picture'] == '') ? '\'\'' : '0x'.hex_dump(base64_decode($data['picture']));

	$mysqlQText = "REPLACE INTO nomenclaturegroups (`guid`, `fullname`, `parentguid`, `isdeleted`, `picture`) VALUES ('".$data['GUID']."', '".$data['fullName']."', '".$data['parentGUID']."', '".($data['isDeleted'] ? 1 : 0)."', ".$picHexData.")";

	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось добавить или изменить группу номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;

	$tResp.= $message;

	// Если параметр сессии имяпользователя уже установлен, значит пользователь уже авторизован, перенаправляем его на страницу для авторизованных пользователей
	
	$filename = 'nomenclaturegroups.txt';
    // проверка на наличия файла в последующем два раза будет использоваться
    $file_exist_bool = file_exists($filename); 
    // опредяляем метод записи
    $method = 'w';
    // в случае если файл существует, метод дозавписи в файл
    if ($file_exist_bool) {
        $method = 'a+'; 
    }
	$file = fopen($filename, $method);

    /*if (!$file_exist_bool) {
        fputcsv($file, array('firstname', 'secondname', 'eduform', 'hostel', 'text', 'courses'));
        // если файла нету, то записать header csv файла
    }*/
	$tResp = $picHexData;
	if ($data != NULL) {
		$tResp.= '<div>';
		foreach ($data as $key => $value) {
			$tResp.= $key . ": " . gettype($value) . PHP_EOL;
			if (gettype($value) == 'array') {
				foreach ($value as $k => $val) {
					$tResp.= $k . ": " . gettype($val) . PHP_EOL;
					$tResp.= $k . ": " . $val . PHP_EOL;
				}							
			} else {
				$tResp.= $key . ": " . $value . PHP_EOL;
			}
		}
		$tResp.= '</div';
	} else {
		$tResp.= "Тело пустое";
	}
	$tResp.= PHP_EOL;
	echo $tResp;
	fwrite($file, $tResp);
	fclose($file);		
	
?>