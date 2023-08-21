<?php
	// Получаем доступ к переменным сессии
	session_start();

	require_once("includes/connection.php");


	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

	$_SESSION['session_userMatrix'] = '';
	$_SESSION['session_contractGUID'] = '';

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/getMatrixWithOrder');
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

	// Массив передаваемых сервису параметров
	$arrayBodyRequest = [
		"orderGUID" => $_GET["orderGUID"]
	];
	// Массив передаваемых параметров в JSON
	$textBodyRequest = json_encode($arrayBodyRequest);
	// Отладка
	//echo 'Запрос:<br>' . $textBodyRequest . '<br>';

	// JSON помещаем в тело запроса
	curl_setopt($curl, CURLOPT_POSTFIELDS, $textBodyRequest);
	// Отправляем запрос, получаем ответ
	$textBodyResponse = curl_exec($curl);

	// Отладка
	//echo 'Ответ:<br>' . $textBodyResponse . '<br>';

	// Если от сервера не получен ответ
	if ($textBodyResponse === false) {
		$message.= 'Ошибка curl: ' . curl_error($curl);
		echo $message;
		http_response_code(500);
		curl_close($curl);
		return;
	// Если получен любой ответ, даже с ошибкой
	} else {
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$arrayBodyResponse = json_decode($textBodyResponse, true);
		// Если код ответа от 1С - ОК, выводим строки заказа
		if ($http_code == 200) {
			if (gettype($arrayBodyResponse) == 'array') {
				$arrayOfProducts = $arrayBodyResponse['arrayOfItems'];
				$_SESSION['session_userMatrix'] = $arrayOfProducts;
				//echo 'hello!';

			}
			else {
				$message.= 'Ошибка при получении данных заказа из 1С: ' . $arrayBodyResponse['errors'];
			}
		}
		else {
			$message.= 'Ошибка при получении данных заказа из 1С: ' . $arrayBodyResponse['errors'];
			http_response_code($http_code);
		}	
		curl_close($curl);
	}

	$_SESSION['session_contractGUID'] = $arrayBodyResponse['contractGUID'];

	////////// Получили массив по orderGUID и contractGUID

	// Составляем массив данных заказа, который будем отправлять, из всей матрицы вибираем только ту номенклатуру, количество к заказу которой больше 0
		$arrOfProducts = array();
		foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
			if ($arrProduct['productQuantity'] > 0) {
				array_push($arrOfProducts, $arrProduct);
			}
		}

		if (strpos($url, 'orderGUID') != false){
			if ($_SESSION['session_userNoMatrix'] != ''){
				foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productQuantity'] > 0) {
					array_push($arrOfProducts, $arrProduct);
				}
				}
			}	
		}

		//echo count($arrOfProducts);

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

		// Информируем, если 1С что-то ещё и ответила
		$arrayBodyResponse = json_decode($textBodyResponse, true);
		// Если какой-либо ответ получен
		if (gettype($arrayBodyResponse) == 'array') {

		}


?>
