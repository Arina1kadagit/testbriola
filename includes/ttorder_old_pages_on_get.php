<?php //require_once("includes/connection.php"); ?>

<?php
	// Переменные документа:
	// Заголовок
	$journalName = 'Товары для заказа:';
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

	// Заголовок и кнопка "Закрыть"
	echo '<div id="order_table">';
	echo '<div id="doc_title"><h2>'.$journalName.'</h2></div>';
	/*echo '<form name="closejournalbtnform" id="closejournalbtnform" action="logout.php" method="post" class="row">';
	echo '<input name="closejournalbtn" type= "submit" value="×">';
	echo '</form>';*/
	echo '<form name="closesessionbtnform" action="logout.php" method="post" id="closesessionbtnform" class="row">';
	echo '<input name="closesessionbtn" type="submit" value="×">';
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

	// Выводим строки документа
	// сколько всего строк в журнале
	// Запрос данных для вывода строк таблицы товаров для заказа (для подсчёта общего количества строк)
	$arrProductsGUIDs = array();
	foreach ($_SESSION['session_userMatrix'] as $key => $value) {
		array_push($arrProductsGUIDs, $value['productGUID']);
	}
	//var_dump($arrProductsGUIDs);
	$strProductsGUIDs = join("','", $arrProductsGUIDs);
	$strProductsGUIDs = '\'' . $strProductsGUIDs . '\'';
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.")";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	$numrows = mysqli_num_rows($mysqlQuery);
//	echo $message.$strProductsGUIDs;

	// Перечисление номеров страниц
	//echo '<p class="pages">Страница:';
	echo '<p class="pages">';
	for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
		// если номер страницы соответствует текущей
		if ($_GET["page"]==$i)
			echo '<a style="color: blue;" href="?page='.$i.'">'.$i.'</a>';
		// если номер страницы не соответствует текущей
		else
			echo '<a href="?page='.$i.'">'.$i.'</a>';
	}
	echo '</p>';

	// запрос строк для выводимой страницы
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") ORDER BY guid ASC LIMIT ".($rowsOnPage*($_GET["page"]-1)).",".$rowsOnPage;
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры для указанной страницы, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	mysqli_close($con);

	if ($numrows > 0) {
		echo '<style>';
		for ($i=1; $i <= $numberOfCols; $i++) {
			$columnContentAlign = "";
			$columnContentAlign = $arrayOfColumnsContentAlign[$i];
			if ($columnContentAlign == "r") $textAlign = "right";
			elseif ($columnContentAlign == "l") $textAlign = "left";
			elseif (($columnContentAlign == "c") || ($columnContentAlign == "")) $textAlign = "center";
			echo ".journal_row a:nth-child(".$i.") { text-align: ".$textAlign."; }\n";
		}
		echo '</style>';
	}
//$numrows = mysqli_num_rows($mysqlQuery);
//echo '3243214213   '.$numrows;
	// Вывод с ограничением по количеству строк, если источник - выборка запроса, а не массив
	if ($numrows > 0) {
		//echo $mysqlQText;
		while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {

			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productGUID'] == $row['guid']) {
					$productQuantity = $arrProduct['productQuantity'];
				}
			}
			$addClassInOrder = ' in_order';
			if ($productQuantity == 0) {
				$productQuantity = '';
				$addClassInOrder = '';
			}
			echo '<div class="journal_row'.$addClassInOrder.'">';
			echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
			echo '<div id="product_pic"><img src="image.php?guid='.$row['guid'].'" /></div>';
			echo '<div id="product_name">' . $row['shortname'] . '</div>';
			echo '<div id="product_price">' . '' . '</div>';
			echo '<div id="product_balance">' . '' . '</div>';
			echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
//			echo '<label for="journalname"><input name="journalname" type="number" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)"></label>';
			echo '<label for="journalname"></label><input name="journalname" type="text" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)">';
			//echo '<p class="submit"><input name="addjournal" class="button" type="submit" value="Добавить журнал"></p>';
			echo '</form>';
			echo '</div>';
		}
	}

	// Вывод с ограничением по количеству строк, если источник - массив, а не выборка запроса
	/*$numrows = count($_SESSION['session_userMatrix']);
	
	if($numrows!=0) {
		//$rowNumber = 0; // временно
		for ($i=0; $i < $rowsOnPage; $i++) {
		 	$ind = $rowsOnPage * ($_GET["page"] - 1) + $i;
		 	if ($ind >= $numrows) break;
		 	$key = $ind;
		 	$value = $_SESSION['session_userMatrix'][$ind];
			
			echo '<div class="journal_row">';
			
			echo '<div>' . $key. '</div>';
			echo '<div>' . $value . '</div>';
			echo '<div></div>';
			echo '<div></div>';
			echo '<div></div>';
			//echo '<form name="addjournalrowform" id="addjournalrowform" class="journal_row" action="'.$action4form.'" method="post">';
			//echo '<p><label for="col'.$col.'"><textarea name="col'.$col.'" type="text" class="input" id="col'.$col.'" size="20">'.$defaultAddValue[$col].'</textarea></label></p>';
			//echo '</form>';
			echo '<div><form name="addjournal" action="ent.php?section=1" method="post" id="addjournal" class="row">';
			//echo '<p><label for="journalname">Название журнала:<br><input name="journalname" type="text" class="input" id="journalname" size="20" value=""></label></p>';
			echo '<label for="journalname"><input name="journalname" type="text" class="input" id="journalname" size="20" value=""></label>';
			//echo '<p class="submit"><input name="addjournal" class="button" type="submit" value="Добавить журнал"></p>';
			echo '</form></div>';
			echo "</div>";
		}
	}*/
	
	
	// Перечисление номеров страниц
	//echo '<p class="pages">Страница:';
	echo '<p class="pages">';
	for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
		// если номер страницы соответствует текущей
		if ($_GET["page"]==$i)
			echo '<a style="color: blue;" href="?page='.$i.'">'.$i.'</a>';
		// если номер страницы не соответствует текущей
		else
			echo '<a href="?page='.$i.'">'.$i.'</a>';
	}
	echo '</p>';
	
	// Выводим форму отправки заказа
	$action4form = 'sendorder.php';
	echo '<div class="sendformcont"><form id="sendorderformid" class="row" name="sendorderformname" action="'.$action4form.'" method="post">';
		echo '<div class="button" onclick="sendOrderTT()">Оформить заказ</div>';
		//echo '<div class="button" onclick="addToOrderTT()">Добавить в заказ</div>';
	echo '</form></div>';
	echo "</div>";
?>