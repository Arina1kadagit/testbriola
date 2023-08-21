<?php
	// Получаем доступ к переменным сессии
	session_start();

	require_once("includes/connection.php");
	
	// Составляем массив данных заказа, который будем отправлять, из всей матрицы вибираем только ту номенклатуру, количество к заказу которой больше 0
	$arrOfProducts = array();
	foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
		if ($arrProduct['productQuantity'] > 0) {
			array_push($arrOfProducts, $arrProduct);
		}
	}

	//Для tp, там в строке есть orderGUID
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if (strpos($url, 'orderGUID') != false){
		if ($_SESSION['session_userNoMatrix'] != ''){
			foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productQuantity'] > 0) {
					array_push($arrOfProducts, $arrProduct);
				}
			}
		}	
	}else{ //Для tt.php
		if ($_SESSION['session_userNoMatrix'] != ''){
			//$arrOfProductsToMatrix = array(); //массив товаров, которые нужно добавить в матрицу
			foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productQuantity'] > 0) {
					array_push($arrOfProducts, $arrProduct);
					//array_push($arrOfProductsToMatrix, $arrProduct['productGUID']);
				}
			}
		}
	}

	
	// Отладка выводим получившийся массив данных заказа
	/*foreach ($arrOfProducts as $key1 => $value1) {
		$resultTxt.= 'Ключ_1: '.$key1.' Значение_1: '.$value1.PHP_EOL;
		if (gettype($value1) == 'array') {
			foreach ($value1 as $key2 => $value2) {
				$resultTxt.= 'Ключ_2: '.$key2.' Значение_2: '.$value2.PHP_EOL;
				if (gettype($value2) == 'array') {
					foreach ($value2 as $key3 => $value3) {
						$resultTxt.= 'Ключ_3: '.$key3.' Значение_3: '.$value3.PHP_EOL;
						if (gettype($value3) == 'array') {
							foreach ($value3 as $key4 => $value4) {
								$resultTxt.= 'Ключ_4: '.$key4.' Значение_4: '.$value4.PHP_EOL;
								if (gettype($value4) == 'array') {
									foreach ($value4 as $key5 => $value5) {
										$resultTxt.= 'Ключ_5: '.$key5.' Значение_5: '.$value5.PHP_EOL;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	echo $resultTxt;*/

	// Составляем и отправляем запрос с данными заказа (order)
	// Инициализация, установка заголовков запроса и других параметров запроса
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/order');
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	
	// Массив передаваемых сервису параметров
	$arrayBodyRequest = [
		//"orderGUID" => isset($_SESSION['session_orderGUID']) ? $_SESSION['session_orderGUID'] : "",
		"orderGUID" => isset($_GET['orderGUID']) ? $_GET['orderGUID'] : "",
		"comment" => isset($_GET['comment']) ? $_GET['comment'] : "",    //new
		"accountGUID" => $_SESSION['session_accountGUID'],
		"contractGUID" => $_SESSION['session_contractGUID'],
		"arrayOfPrices" => $arrOfProducts		
	];
	// Массив передаваемых параметров в JSON
	$textBodyRequest = json_encode($arrayBodyRequest);
	// JSON помещаем в тело запроса
	curl_setopt($curl, CURLOPT_POSTFIELDS, $textBodyRequest);

	// Отправляем запрос, получаем ответ
	$textBodyResponse = curl_exec($curl);

	if ($textBodyResponse === false) {
		echo 'Ошибка curl: ' . curl_error($curl);
		http_response_code(505);
		curl_close($curl);
		return;
	} else {
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($http_code = 200) {
			if (isset($_GET['orderGUID'])) {
				echo '<p>Заказ подтверждён!</p>';
			} else {
				echo '<p>Заказ успешно создан!</p>';
			}
		} else {
			http_response_code($http_code);
		}	
	}
	curl_close($curl);
	
	// После успешной отправки очищаем матрицу
	$arrNewUserMatrix = array();
	foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
		$arrNewProduct = array();
		$arrNewProduct['productGUID'] = $arrProduct['productGUID'];
		$arrNewProduct['productQuantity'] = '';
		array_push($arrNewUserMatrix, $arrNewProduct);
	}
	$_SESSION['session_userMatrix'] = $arrNewUserMatrix;

	//Добавление в матрицу нематричных товаров, чтобы они появляиьс в меню без выхода и авторизации 
	if ($_SESSION['session_userNoMatrix'] != ''){
		foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
			if ($arrProduct['productQuantity'] > 0) {
				$arrNewProduct = array();
				$arrNewProduct['productGUID'] = $arrProduct['productGUID'];
				$arrNewProduct['productQuantity'] = '';
				array_push($arrNewUserMatrix, $arrNewProduct);
			}
		}
		$_SESSION['session_userMatrix'] = $arrNewUserMatrix;
	}


	$_SESSION['session_userNoMatrix'] = '';

	// Информируем, если 1С что-то ещё и ответила
	$arrayBodyResponse = json_decode($textBodyResponse, true);
	// Если какой-либо ответ получен
	if (gettype($arrayBodyResponse) == 'array') {
	
		// Отладка выводим получившийся массив данных заказа
		/*foreach ($arrayBodyResponse as $key1 => $value1) {
			$resultTxt.= 'Ключ_1: '.$key1.' Значение_1: '.$value1.PHP_EOL;
			if (gettype($value1) == 'array') {
				foreach ($value1 as $key2 => $value2) {
					$resultTxt.= 'Ключ_2: '.$key2.' Значение_2: '.$value2.PHP_EOL;
					if (gettype($value2) == 'array') {
						foreach ($value2 as $key3 => $value3) {
							$resultTxt.= 'Ключ_3: '.$key3.' Значение_3: '.$value3.PHP_EOL;
							if (gettype($value3) == 'array') {
								foreach ($value3 as $key4 => $value4) {
									$resultTxt.= 'Ключ_4: '.$key4.' Значение_4: '.$value4.PHP_EOL;
									if (gettype($value4) == 'array') {
										foreach ($value4 as $key5 => $value5) {
											$resultTxt.= 'Ключ_5: '.$key5.' Значение_5: '.$value5.PHP_EOL;
										}
									}
								}
							}
						}
					}
				}
			}
		}
		echo $resultTxt;*/

	}
?>
