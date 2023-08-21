<?php
	session_start();
	header('X-Accel-Buffering: no');
	header('Content-type: text/html; charset=utf-8');

	// Активация лога последней операции
	$filename = 'add2orderlog.txt';
    // проверка на наличия файла в последующем два раза будет использоваться
    $file_exist_bool = file_exists($filename); 
    // опредяляем метод записи
    $method = 'w';
    // в случае если файл существует, метод дозавписи в файл
    if ($file_exist_bool) {
        $method = 'a+'; 
    }
	$file = fopen($filename, $method);
	
	$resultTxt.= PHP_EOL;
	/*$resultTxt.= date(DATE_ATOM).PHP_EOL;
	$resultTxt.= date("Y-m-d H:i:s").PHP_EOL;
	$resultTxt.= microtime(true).PHP_EOL;*/
	$date_array = explode(" ", microtime());
	$resultTxt.= date("Y-m-d H:i:s", $date_array[1]).substr((string)$date_array[0], 1, 4).PHP_EOL;
	$resultTxt.= $postData.PHP_EOL;

	// Получаем данные для помещения в таблицу номенклатуры
	$postData = file_get_contents('php://input');
	$data = json_decode($postData, true);
	foreach ($data as $key => $value) {
		if (gettype($value) == 'array') {
			foreach ($value as $k1 => $val1) {
				if (gettype($val1) == 'array') {
					foreach ($val1 as $k2 => $val2) {
						$resultTxt.= 'Ключ2: '.$k2.' Значение: '.$val2.PHP_EOL;
					}
				} else {
					$resultTxt.= 'Ключ1: '.$k1.' Значение: '.$val1.PHP_EOL;
				}
			}
		} else {
			$resultTxt.= 'Ключ: '.$key.' Значение: '.$value.PHP_EOL;
		}
	}

	//Поиск по матрице
	//Для каждого товара из матрицы находим соттветвующий элемент из массива добавленных товаров $data['userMatrix'] - обновляем кол-во
	
	$arrNewUserMatrix = array();
	
	foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
		$arrNewProduct = array();
		$arrNewProduct['productGUID'] = $arrProduct['productGUID'];
		$arrNewProduct['productQuantity'] = $arrProduct['productQuantity'];
		foreach ($data['userMatrix'] as $key => $arrProductToOrder) {
			//Ищем соответвие по матрице
			if ($arrProductToOrder['productGUID'] == $arrProduct['productGUID']) {
				$arrNewProduct['productQuantity'] = $arrProductToOrder['productQuantity'];
			}	
		}
		array_push($arrNewUserMatrix, $arrNewProduct);
	}
	$_SESSION['session_userMatrix'] = $arrNewUserMatrix;

	//Выводим толкьо те, которых >0
	foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
		if ($arrProduct['productQuantity'] > 0){
			$resultTxt.=$arrProduct['productGUID'].' ';
			$resultTxt.=$arrProduct['productQuantity'].PHP_EOL;
		}
	}

	//////////
	//Добавленные товары не из матрицы

	$resultTxt.= 'count session_noMatrix - '.count($_SESSION['session_userNoMatrix']).PHP_EOL;
	$i = 0;
	foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
		//if ($arrProduct['productQuantity'] > 0){
		$i = $i + 1;
		$resultTxt.= $i.' ';
			$resultTxt.=$arrProduct['productGUID'].' ';
			$resultTxt.=$arrProduct['productQuantity'].PHP_EOL;
		//}
	}

	$flagMatrix = 0;
	$arrayNoMatrix = $_SESSION['session_userNoMatrix']; //новое

	//для каждого товара из корзины
	foreach ($data['userMatrix'] as $key => $arrProductToOrder) { //приходит сюда один товар	
		//Ищем соответсвие в матрице
		foreach ($_SESSION['session_userMatrix'] as $key => $arrProductMatrix) {
			$flag = 0;
			//ищем по матрице
			if ($arrProductToOrder['productGUID'] == $arrProductMatrix['productGUID']) {
				$flag = 1; //нашли
				$flagMatrix =1;
				$resultTxt.= 'Find matrix '.$arrProductToOrder['productGUID'].PHP_EOL;
				break;
				}
		}
		//если не нашлось в матрице
		if ($flag == 0) {
			$arrayNoMatrix = array(); //новое			
			//проверяем есть ли измененный элемент в корзине
			$flag2 = 0;
			foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct){
				//ищем по корзине не матрицы
				if ($arrProductToOrder['productGUID'] == $arrProduct['productGUID']) {
					$flag2 = 1;
					$arrNewProductAll = array();
					if ($arrProductToOrder['productQuantity'] == 0){
						$arrNewProductAll['productQuantity'] = ''; //не проставляются в карточках нули
					} else{
						$arrNewProductAll['productQuantity'] = $arrProductToOrder['productQuantity'];
					}
					$arrNewProductAll['productGUID'] = $arrProductToOrder['productGUID'];
					$resultTxt.= 'Find of '.$arrProductToOrder['productGUID'].PHP_EOL;
					array_push($arrayNoMatrix, $arrNewProductAll);
				}
				//сохраняем товар из корзины в новый мссив для избежания потерь
				else{
					$arrNewProductAll = array();
					$arrNewProductAll['productQuantity'] = $arrProduct['productQuantity'];
					$arrNewProductAll['productGUID'] = $arrProduct['productGUID'];
					array_push($arrayNoMatrix, $arrNewProductAll);
				}
			}
			//если товар не найден в не матрице
			if ($flag2 == 0){
				//новый товар
				$arrNewProductAll = array();
				$arrNewProductAll['productQuantity'] = $arrProductToOrder['productQuantity'];
				$arrNewProductAll['productGUID'] = $arrProductToOrder['productGUID'];

				$resultTxt.= 'Find new '.$arrNewProductAll['productGUID'].PHP_EOL;
				array_push($arrayNoMatrix, $arrNewProductAll);		
			}
		
		}
	}

//если измененный товар из матрицы - не изменять корзину не по матрице
	if($flagMatrix == 0){
		$_SESSION['session_userNoMatrix'] = $arrayNoMatrix;
	}

	//Удаляем из массива нулевые и пустые эелементы
	//МОЖЕТ НЕ РАБОТАТЬ ИЗ ЗА ДВУХ ЗАККОМЕНТИРОВАННЫХ СТРОК НИЖЕ, но вроде норм
	//$arrayNoMatrix = array();
	//$arrayNoMatrix = $_SESSION['session_userNoMatrix'];
	foreach ($arrayNoMatrix as $key => $arrProduct){
		//$resultTxt.=$arrProduct['productQuantity'].PHP_EOL;
		if (($arrProduct['productQuantity'] == '')||($arrProduct['productQuantity'] == 0)) {
			unset($arrayNoMatrix[$key]);
		}
	}
	$_SESSION['session_userNoMatrix'] = $arrayNoMatrix;

	

//Вывод для проверки
	$resultTxt.= 'count session_noMatrix - '.count($_SESSION['session_userNoMatrix']).PHP_EOL;
	foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
		//if ($arrProduct['productQuantity'] > 0){
			$resultTxt.=$arrProduct['productGUID'].' ';
			$resultTxt.=$arrProduct['productQuantity'].PHP_EOL;
		//}
	}
	
	/*foreach ($_SESSION['session_userMatrix'] as $key => $value) {
		if (gettype($value) == 'array') {
			foreach ($value as $k1 => $val1) {
				if (gettype($val1) == 'array') {
					foreach ($val1 as $k2 => $val2) {
						$resultTxt.= 'Ключ: '.$k2.' Значение: '.$val2.PHP_EOL;
					}
				} else {
					$resultTxt.= 'Ключ: '.$k1.' Значение: '.$val1.PHP_EOL;
				}
			}
		} else {
			$resultTxt.= 'Ключ: '.$key.' Значение: '.$value.PHP_EOL;
		}
	}*/

	//echo $resultTxt;

	/*if (!isset($_SESSION['session_userorder']) {

	}*/

	// Запись лога
	fwrite($file, $resultTxt);
	fclose($file);

?>