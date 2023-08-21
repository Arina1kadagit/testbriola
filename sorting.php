
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
	
	function get_string_between($string, $start, $end, $inclusive = false) {
    
	    //global $tResp;

	    //$string = " ".$string;

	    if ($start == "") { $ini = 0; }
	    else { $startPos = strpos($string, $start) + strlen($start); }

	    //$tResp.= $startPos.PHP_EOL;

	    if ($end == "") { $len = strlen($string); }
	    else { $endPos = strpos($string, $end); }

	    //$tResp.= $endPos.PHP_EOL;

	    //if (!$inclusive) { $ini += strlen($start); }
	    //else { $len += strlen($end); }

	    return substr($string, $startPos, $endPos - $startPos);
	}

	session_start();
	header('Content-type: text/html; charset=utf-8');
	//header('Content-Type: application/json;charset=utf-8');
	header('Accept: application/json');
	
	// создать таблицу номенклатуры, если её нет
	$mysqlQText = "CREATE TABLE IF NOT EXISTS `nomenclaturesort` (
									`guid` varchar(36) NOT NULL,
									`number` integer NOT NULL, 
									PRIMARY KEY (guid)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось создать таблицу номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;

	// Получаем данные для помещения в таблицу номенклатуры
	$postData = file_get_contents('php://input');
	$data = json_decode($postData, true);

	$values = '';
	
	$array = $data['МассивТоваров'];
	foreach ($array as $d){
		$values .= "('".$d['GUID']."','".$d['N']."'),";
	}

	$values = substr($values,0,-1);

	$mysqlQText = "REPLACE INTO nomenclaturesort (`guid`, `number`) VALUES ".$values;


	// Помещаем данные в таблицу
	//$mysqlQText = "REPLACE INTO nomenclaturesort (`guid`, `number`)
										//VALUES ('".$data['GUID']."','".$data['N']."')";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось добавить или изменить номенклатуру, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;

	$tResp.= $message;


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//var_dump($data);

	//echo '<br>asfdsgfadsagfdsgdfa';
	// Если параметр сессии имяпользователя уже установлен, значит пользователь уже авторизован, перенаправляем его на страницу для авторизованных пользователей
	
	$filename = 'nomenclaturesort.txt';
    // проверка на наличия файла в последующем два раза будет использоваться
    $file_exist_bool = file_exists($filename); 
    // опредяляем метод записи
    $method = 'w';
    // в случае если файл существует, метод дозавписи в файл
    /*if ($file_exist_bool) {
        $method = 'a+'; 
    }*/
	$file = fopen($filename, $method);

    /*if (!$file_exist_bool) {
        fputcsv($file, array('firstname', 'secondname', 'eduform', 'hostel', 'text', 'courses'));
        // если файла нету, то записать header csv файла
    }*/
	//$tResp = '';
	if ($data != NULL) {
		$tResp.= '<div>';
		foreach ($array as $key => $value) {
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