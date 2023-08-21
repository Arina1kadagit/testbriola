<?php
    require_once("includes/connection.php");
?>
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
	
	$hasError = False;
	
	// создать таблицу номенклатуры, если её нет
	$mysqlQText = "CREATE TABLE IF NOT EXISTS `nomenclatures` (
									`guid` varchar(36) NOT NULL,
									`parentguid` varchar(36) NOT NULL, 
									`shortname` varchar(100) NOT NULL,
									`fullname` varchar(1000) NOT NULL,
									`description` varchar(1000) NOT NULL,
									`expirationDate` decimal(5, 0) NOT NULL,
									`measure` varchar(100) NOT NULL,
									`weight` decimal(15, 3) NOT NULL,
									`multiplicity` decimal(10, 3) NOT NULL,
									`picture` mediumblob NOT NULL,
									`isdeleted` boolean NOT NULL,
									`parentNomenclaturName` varchar(100) NOT NULL,
									PRIMARY KEY (guid)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) {
	    $message.= 'Не удалось создать таблицу номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	    $hasError = True;
	}

	// Получаем данные для помещения в таблицу номенклатуры
	$postData = file_get_contents('php://input');
	$data = json_decode($postData, true);
	$before = memory_get_usage();
	// Формат для вставки типа BLOB
	$picHexData = ($data['picture'] == '') ? '\'\'' : '0x'.hex_dump(base64_decode($data['picture']));
	//$picHexData = hex_dump(base64_decode($data['picture']));
	$after = memory_get_usage();
	$diff = $after - $before;
	$tResp.= 'Размер переменной составил: '.$diff.' байт';
	$tResp.= PHP_EOL;
	
	if ($diff > 10000000) {
	    //$hasError = True;
	    
//        $image = $row['picture'];
    //    $image = hex_dump(base64_decode($data['picture']));
    //    $image = hex_dump(base64_decode($data['picture']));
        $image = base64_decode($data['picture']);
    
        // Create Imagick object
        $im = new Imagick();
    
        // Convert image into Imagick
        $im->readImageBlob($image);
//        $im->resizeImage(320, 240, Imagick::FILTER_LANCZOS, 1);
//        $im->writeImage('mythumb.png');
    
        // Create thumbnail max of 200x82
//        $im->thumbnailImage(160, 0, false);
        
        // Add a subtle border
        /*$color=new ImagickPixel();
        $color->setColor("rgb(120,120,120)");
        $im->borderImage($color,1,1);*/
        
        // Output the image
        $output = $im->getImageBlob();
        $picHexData = '0x'.hex_dump($output);
	}

	// Помещаем данные в таблицу
	$_GUID = mysqli_real_escape_string($con, $data['GUID']);
	$_parentGUID = mysqli_real_escape_string($con, $data['parentGUID']);
	$_shortName = mysqli_real_escape_string($con, $data['shortName']);
	$_fullName = mysqli_real_escape_string($con, $data['fullName']);
	$_description = mysqli_real_escape_string($con, $data['description']);
	$_expirationDate = mysqli_real_escape_string($con, $data['expirationDate']);
	$_measure = mysqli_real_escape_string($con, $data['measure']);
	$_weight = mysqli_real_escape_string($con, $data['weight']);
	$_multiplicity = mysqli_real_escape_string($con, $data['multiplicity']);
	$_isDeleted = mysqli_real_escape_string($con, $data['isDeleted']);
	$_parentNomenclaturName = mysqli_real_escape_string($con, $data['parentNomenclaturName']);
	$mysqlQText = "REPLACE INTO nomenclatures (`guid`, `parentguid`, `shortname`, `fullname`, `description`, `expirationDate`, `measure`, `weight`, `multiplicity`, `picture`, `isdeleted`,  `parentNomenclaturName`)
										VALUES ('".$_GUID."','".$_parentGUID."', '".$_shortName."', '".$_fullName."', '".$_description."', '".$_expirationDate."', '".$_measure."', '".$_weight."', '".$_multiplicity."', ".$picHexData.", '".($_isDeleted ? 1 : 0)."', '".$_parentNomenclaturName."')";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) {
	    $message.= 'Не удалось добавить или изменить номенклатуру, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	    $hasError = True;
	}
    
    $tResp.= $message;

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//var_dump($data);

	//echo '<br>asfdsgfadsagfdsgdfa';
	// Если параметр сессии имяпользователя уже установлен, значит пользователь уже авторизован, перенаправляем его на страницу для авторизованных пользователей
	
	$filename = 'tes.csv';
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
	
	if ($hasError) {
    	http_response_code(500);
    	$tResp = mb_substr($tResp, 0, 1000);
    	echo $tResp;
    	fwrite($file, $tResp);
        fclose($file);
    	//header("HTTP/1.1"." 500 "."Internal Server Error");
    	exit;
	} else {
        /*echo $tResp;*/
        fwrite($file, $tResp);
        fclose($file);	    
	}
?>