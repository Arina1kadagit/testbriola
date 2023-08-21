<?php require_once("includes/connection.php"); ?>
<?php
	// Переменные документа:
	// Заголовок
	$journalName = 'Товары для заказа:';
	// Количество колонок
	$numberOfCols = 6;
	// Строк на странице
	$rowsOnPage = 10;
	// Названия колонок
	$columnsNames = '[GUID]&&&Картинка&&&Наименование&&&Цена&&&Количество на складе&&&Количество заказать';
	// Инициализируем переменную сообщения
	$message = '';

	// Если страница не задана, будем выводить первую
	if (!isset($_GET["page"])) $_GET["page"] = 1;

	// Если родитель не задан, то родителя нет
	if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '00000000-0000-0000-0000-000000000000';

	// +++ ВЫВОД ДОКУМЕНТА +++
	echo '<div id="order_table">';
		
		// Выводим заголовок и кнопки
		// Заголовок
		echo '<div id="doc_title"><h2>'.$journalName.'</h2></div>';
		// Кнопка "Закрыть"
		echo '<form name="closesessionbtnform" action="logout.php" method="post" id="closesessionbtnform" class="row">';
			echo '<input name="closesessionbtn" type="submit" value="×">';
		echo '</form>';
		// Кнопка "Свернуть/развернуть"
		echo '<form name="hiderowsbtnform" action="logout.php" method="post" id="hiderowsbtnform" class="row" onsubmit="hideRows(this)">';
		echo '<input name="hiderowsbtn" type="submit" value="-">';
		echo '</form>';


		// Выводим название колонок (СКРЫТЫ, НЕ ИСПОЛЬЗУЮТСЯ)
		// Название колонок в массив
		$arrayOfColumnsNames = explode("&&&", $columnsNames);
		echo '<div class="columns_names data">';
		for ($i=1; $i <=$numberOfCols; $i++) {
			$colName = $arrayOfColumnsNames[$i-1];
			if ($colName[0] == '[' && $colName[strlen($colName)-1] == ']') {
				echo '<div class="data">'.$colName.'</div>';
			} else {
				echo '<div>'.$colName.'</div>';
			}
		}

		echo '</div>';

	if (!isset($_SESSION['session_userMatrix'])){
		echo 'yes!';
	}
	else{
		echo 'no!';
	}

	// Составляем и отправляем запрос данных заказа для вывода (getOrder)
		// Инициализация, установка заголовков запроса и других параметров запроса
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
	echo 'Запрос:<br>' . $textBodyRequest . '<br>';

		// JSON помещаем в тело запроса
		curl_setopt($curl, CURLOPT_POSTFIELDS, $textBodyRequest);
		// Отправляем запрос, получаем ответ
		$textBodyResponse = curl_exec($curl);

		// Отладка
		echo 'Ответ:<br>' . $textBodyResponse . '<br>';


	//Массив GUID по матрице
	$arrProductsGUIDs = array();
		foreach ($_SESSION['session_userMatrix'] as $key => $value) {
		array_push($arrProductsGUIDs, $value['productGUID']);
		}
		$strProductsGUIDs = join("','", $arrProductsGUIDs);
		$strProductsGUIDs = '\'' . $strProductsGUIDs . '\'';	

	if (isset($_GET["showall"])){ //если в режиме всего ассортимента

		//Позиции по номенклатуре по выбранной группе
		$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted =FALSE AND parentguid = '".$_GET["parentGUID"]."' ORDER BY guid";
		$mysqlQuery = mysqli_query($con, $mysqlQText);
		$mysqlErrNo = mysqli_errno($con);
		$mysqlError = mysqli_error($con);
		if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
		$numrows = mysqli_num_rows($mysqlQuery);
		$maxPage = ceil($numrows/$rowsOnPage);

	//mysqli_close($con);


	// Вывод с ограничением по количеству строк, если источник - выборка запроса, а не массив
	$index = 0;
	$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
	$endIndex = $startIndex + $rowsOnPage - 1;
	if ($numrows > 0) {
		//echo $mysqlQText;
		while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {

			/*
			$addClassInOrder = ' in_order';
			if (!isset($productQuantity)){
				$productQuantity = '';
				$addClassInOrder = '';
			} 
			else if($productQuantity == 0){
				$productQuantity = '';
				$addClassInOrder = '';
			}
			else{
				$addClassInOrder = ' in_order';
			}*/
			
			//все товары, которые не по матрице зануляем (чтобы они не выделялись цветом)
			$addClassInOrder = ' '; 
			$productQuantity = 0;
			if ($productQuantity == 0) {
				$productQuantity = '';
				$addClassInOrder = '';
			}

			//товары по матрице - выделяем цветом
			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productGUID'] == $row['guid']) {
					$productQuantity = $arrProduct['productQuantity'];
					if ($productQuantity != 0) {
						$addClassInOrder = ' in_order';
					}
				}
			}			

		//выводим только товары по выбранной категории
		if ($row['parentguid'] != $_GET["parentGUID"]) {
			$addClassInOrder.= ' hide_on_page';
		} else {
			if (!($index >= $startIndex && $index <= $endIndex)) {
				$addClassInOrder.= ' hide_on_page';
			}
			$index++;
		}
		echo '<div class="journal_row'.$addClassInOrder.'" onclick="showHistory(this)">';
		echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
		echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
		//echo '<div id="product_pic"><img src="image.php?guid='.$row['guid'].'" /></div>';
		echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
		echo '<div id="product_name">' . $row['shortname'] . '</div>';
		echo '<div id="product_balance">' . '' . '</div>';
		echo '<div id="product_price">' . '' . '</div>';
		echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
		echo '<div id="product_measure">' . $row['measure'] . '</div>';
		echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
		echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="0" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)" readonly>';

		echo '</form>';
		echo '<div id="product_history"><div>' . '' . '</div></div>';
		echo '</div>';
		}
	}

		//Дополнительно выводим товары по матрице (310) и из них выводитм заказанные
	//Альтернатива была - выводить все товары, но это занимает слишком много памяти (более 5000 строк)

		$mysqlQText2 = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE";
		$mysqlQuery2 = mysqli_query($con, $mysqlQText2);
		$mysqlErrNo2 = mysqli_errno($con);
		$mysqlError2 = mysqli_error($con);
		if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo2 . ', текст ошибки: ' . $mysqlError2 . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText2;
		$numrows2 = mysqli_num_rows($mysqlQuery2);

		mysqli_close($con);
		ini_set('memory_limit', '-1');

		if ($numrows2 > 0) {
		//echo $mysqlQText;
		while ($row2 = mysqli_fetch_array($mysqlQuery2, MYSQLI_ASSOC)) {

			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productGUID'] == $row2['guid']) {
					$productQuantity = $arrProduct['productQuantity'];
				}
			}

			//выводим толкьо заказанные продукты
			//order - класс-идентификатор для этой группы строк
			if ($productQuantity != 0) {
				$addClassInOrder = ' in_order hidden_row order'; //класс order - для избежания повторения в выводе

			echo '<div class="journal_row'.$addClassInOrder.'" onclick="showHistory(this)">';
			echo '<div id="guid" class="data">' . $row2['guid'] . '</div>';
			echo '<div id="parent_guid" class="data">' . $row2['parentguid'] . '</div>';
			echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row2['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row2['guid'].'" /></noscript></div>';
			echo '<div id="product_name">' . $row2['shortname'] . '</div>';
			echo '<div id="product_balance">' . '' . '</div>';
			echo '<div id="product_price">' . '' . '</div>';
			echo '<div id="product_exp_date">Срок годности: ' . $row2['expirationDate'] . ' дней</div>';
			echo '<div id="product_measure">' . $row2['measure'] . '</div>';
			echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
			echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)">';
			echo '</form>';
			echo '<div id="product_history"><div>' . '' . '</div></div>';
			echo '</div>';
			}
		}
	}
	
	//если режим по матрице
	} else{

	// Выводим строки документа
	// сколько всего строк в журнале
	// Запрос данных для вывода строк таблицы товаров для заказа (для подсчёта общего количества строк)

	//echo $strProductsGUIDs;
	//$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE AND parentguid = '".$_GET["parentGUID"]."'";
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	$numrows = mysqli_num_rows($mysqlQuery);
//	echo $message.$strProductsGUIDs;

	// Запрос только тех строк которые будут видны (чтобы предварительно знать их количество для вывода номеров страниц)
	//$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") ORDER BY guid ASC LIMIT ".($rowsOnPage*($_GET["page"]-1)).",".$rowsOnPage;
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted =FALSE AND parentguid = '".$_GET["parentGUID"]."' ORDER BY guid";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры для указанной группы, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	$numrowsInGroup = mysqli_num_rows($mysqlQuery);
	$maxPage = ceil($numrowsInGroup/$rowsOnPage);


	// запрос строк для выводимой страницы
	//$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") ORDER BY guid ASC LIMIT ".($rowsOnPage*($_GET["page"]-1)).",".$rowsOnPage;
	//$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND parentguid = '".$_GET["parentGUID"]."' ORDER BY guid";
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE ORDER BY guid";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры для указанной страницы, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	mysqli_close($con);

	//$numrows = mysqli_num_rows($mysqlQuery);

	// Вывод с ограничением по количеству строк, если источник - выборка запроса, а не массив
	$index = 0;
	$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
	$endIndex = $startIndex + $rowsOnPage - 1;
	//echo $numrows;
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
			if ($row['parentguid'] != $_GET["parentGUID"]) {
				$addClassInOrder.= ' hide_on_page';
			} else {
				if (!($index >= $startIndex && $index <= $endIndex)) {
					$addClassInOrder.= ' hide_on_page';
				}
				$index++;
			}
			echo '<div class="journal_row'.$addClassInOrder.'" onclick="showHistory(this)">';
			echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
			echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
			//echo '<div id="product_pic"><img src="image.php?guid='.$row['guid'].'" /></div>';
			echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
			echo '<div id="product_name">' . $row['shortname'] . '</div>';
			echo '<div id="product_balance">' . '' . '</div>';
			echo '<div id="product_price">' . '' . '</div>';
			echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
			echo '<div id="product_measure">' . $row['measure'] . '</div>';
			echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
//			echo '<label for="journalname"><input name="journalname" type="number" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)"></label>';
			echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)">';
			//echo '<p class="submit"><input name="addjournal" class="button" type="submit" value="Добавить журнал"></p>';
			echo '</form>';
			echo '<div id="product_history"><div>' . '' . '</div></div>';
			echo '</div>';
		}
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
	

	// Выводим перечисление номеров страниц
	//echo '<p class="pages">Страница:';
	echo '<p class="pages">';
	for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
		// если номер страницы соответствует текущей
		if ($_GET["page"]==$i && $maxPage > 1) {
			echo '<a style="color: black; background: linear-gradient(
91.65deg, #AFFC38 2.43%, #F6FD41 100%); font-weight: 600;" href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
			//echo '<a style="color: blue;" href="#" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
		// если номер страницы не соответствует текущей
		} else {
			if ($i <= $maxPage && $maxPage > 1) {
				echo '<a href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
			} else {
				echo '<a href="?page='.$i.'" style="display: none" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
				//echo '<a href="#" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
			}
		}
	}
	echo '</p>';
	
	// Выводим форму отправки заказа
	$action4form = 'sendorder.php';
	echo '<div class="sendformcont"><form id="sendorderformid" class="row" name="sendorderformname" action="'.$action4form.'" method="post">';
		echo '<div class="button" onclick="sendOrderTT()">Оформить заказ</div>';
		//echo '<div class="button" onclick="addToOrderTT()">Добавить в заказ</div>';
	echo '</form></div>';
	/*
	echo '<div id="overall_info" class="animated">Сумма заказа:<br>0.00</div>';
	*/
	echo "</div>";
?>