<?php
	session_start();
	header('X-Accel-Buffering: no');
	header('Content-type: text/html; charset=utf-8');
	if (!isset($_SESSION['session_username'])) : 
		if (!isset($_GET["onlyBody"]) || !$_GET["onlyBody"]) {
			header("Location:login.php");
		} else {
			http_response_code(401);
		}
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
	// Инициализируем переменную сообщения
	$message = '';
	
	if (isset($_GET["productGUID"])) {
		
		// Очищаем переменные сессии
		//$_SESSION['session_orderGUID'] = '';
		//$_SESSION['session_contractGUID'] = '';
		//$_SESSION['session_userMatrix'] = '';

		// ПАРАМЕТРЫ ДОКУМЕНТА:
		
		// Заголовок
		//$journalName = '';
		// Количество колонок
		//$numberOfCols = 6;
		// Строк на странице
		//$rowsOnPage = 40;
		// Названия колонок
		//$columnsNames = '[GUID]&&&Картинка&&&Наименование&&&Цена&&&Количество на складе&&&Количество заказать';

		// Если страница не задана, будем выводить первую
		//if (!isset($_GET["page"])) $_GET["page"] = 1;

		// Параметры запроса
		$strProductsGUIDs = "'" . $_GET["productGUID"] . "'";
		
		// Запрос указанной номенклатуры
		$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.")";
		$mysqlQuery = mysqli_query($con, $mysqlQText);
		$mysqlErrNo = mysqli_errno($con);
		$mysqlError = mysqli_error($con);
		if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные указанной номенклатуры из БД, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
		mysqli_close($con);

		$numrows = mysqli_num_rows($mysqlQuery);
		if ($numrows > 0) {
			$row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC);
			
			// Выводим заголовок и кнопки
			echo '<div id="product_card" onclick= "doNothing()">';
			echo '<div id="product_name">'.$row['shortname'].'</div>';
			echo '<div id="product_pic"><img src="image.php?guid='.$_GET["productGUID"].'&fullSize=true"/></div>';
			//echo '<div id="product_pic"><img srс="'.$row['picture'].'"/></div>';
			echo '</div>';
			//var_dump($row);
		}
	} else {
		$message.= 'Ошибка при получении данных продукта: GUID продукта не указан!';
	}
	if ($message) {
		echo '<div id="error">'.$message.'</div>';
	}
?>
<?php 
	if (!isset($_GET["onlyBody"]) || !$_GET["onlyBody"]) {
		include("includes/footer.php"); 
	}
?>
<?php endif; ?>