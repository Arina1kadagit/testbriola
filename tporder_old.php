<?php
	session_start();
	header('X-Accel-Buffering: no');
	header('Content-type: text/html; charset=utf-8');
	if (!isset($_SESSION['session_username'])) : 
		header("Location:login.php");
	else :
?>

<?php require_once("includes/connection.php"); ?>

<?php
	if (!isset($_GET["onlyBody"]) || !$_GET["onlyBody"]) {
		include("includes/header.php"); 
		echo '<body onload="complete()">';
	}
?>


<?php
	if (isset($_GET["orderguid"])) {
		// Переменные документа:
		// Заголовок
		//$journalName = '';
		// Количество колонок
		$numberOfCols = 6;
		// Строк на странице
		$rowsOnPage = 40;
		// Названия колонок
		$columnsNames = '[GUID]&&&Картинка&&&Наименование&&&Цена&&&Количество на складе&&&Количество заказать';
		// Инициализируем переменную сообщения
		$message = '';

		// Если страница не задана, будем выводить первую
		if (!isset($_GET["page"])) $_GET["page"] = 1;

		// Вывод документа

		// Составляем и отправляем запрос данных заказа (getOrder)
		// Инициализация, установка заголовков запроса и других параметров запроса
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/getOrder');
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		// Массив передаваемых сервису параметров
		$arrayBodyRequest = [
			"idOrder" => $_GET["orderguid"]
		];
		// Массив передаваемых параметров в JSON
		$textBodyRequest = json_encode($arrayBodyRequest);
		// JSON помещаем в тело запроса
		curl_setopt($curl, CURLOPT_POSTFIELDS, $textBodyRequest);
		// Отправляем запрос, получаем ответ
		$textBodyResponse = curl_exec($curl);

		// Отладка
		//echo 'Ответ:<br>' . $textBodyResponse . '<br>';
		
		// Если от сервера не получен ответ
		if ($textBodyResponse === false) {
			echo 'Ошибка curl: ' . curl_error($curl);
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
					
					// Выводим заголовок и кнопку "Закрыть"
					echo '<div id="order_tp_table">';
					echo '<h2>Заказ контрагента <span style="color: red">«'.$arrayBodyResponse['counterpartyName'].', '.$arrayBodyResponse['contractFullName'].'»</span></h2>';
					echo '<form name="closesessionbtnform" action="logout.php" method="post" id="closesessionbtnform" class="row">';
					echo '<input name="closesessionbtn" type="submit" value="×" onclick="closeOrder()">';
					echo '</form>';

					// Выводим название колонок
					// Название колонок в массив
					$arrayOfColumnsNames = explode("&&&", $columnsNames);
					echo '<div class="journal_row columns_names data">';
					for ($i=1; $i <=$numberOfCols; $i++) {
						$colName = $arrayOfColumnsNames[$i-1];
						if ($colName[0] == '[' && $colName[strlen($colName)-1] == ']') {
							echo '<div class="data">'.$colName.'</div>';
						} else {
							echo '<div>'.$colName.'</div>';
						}
					}
					echo '</div>';

					echo '<div id="document_rows">';
					// Выводим строки документа
					$arrayOfProducts = $arrayBodyResponse['arrayOfItems'];
					foreach ($arrayOfProducts as $index1 => $arrayOfProduct) {
						$productGUID = $arrayOfProduct['idNomenclature'];
						$productPrice = $arrayOfProduct['price'];
						$productWeight = $arrayOfProduct['weight'];
						$productQuantity = 0; //$arrayOfProduct[''];
						echo '<div class="journal_row">';
						echo '<div id="product_GUID"">'.$productGUID.'</div>';
						echo '<div>'.$productPrice.'</div>';
						echo '<div>'.$productWeight.'</div>';
						echo '<div>'.$productQuantity.'</div>';
						echo '</div>';
					}
					echo '</div>';
				} else {
					$message.= 'Ошибка при получении данных заказа из 1С: ' . $arrayBodyResponse['errors'];
				}
			// Если код ответа от 1С - не ОК, устанавливаем код ответа сайта таким же
			} else {
				$message.= 'Ошибка при получении данных заказа из 1С: ' . $arrayBodyResponse['errors'];
				http_response_code($http_code);
			}	
		}
		curl_close($curl);
		echo '</div>';
		if ($message) {
			echo '<div id="error">'.$message.'</div>';
		}
	}
?>

<?php 
	if (!isset($_GET["onlyBody"]) || !$_GET["onlyBody"]) {
		include("includes/footer.php"); 
	}
?>

<?php endif; ?>