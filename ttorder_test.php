<?php //require_once("includes/connection.php"); ?>

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
/*
		echo '<form name="closesessionbtnform" action="logout.php" method="post" id="closesessionbtnform" class="row">';
			echo '<input name="closesessionbtn" type="submit" value="×">';
		echo '</form>';
		// Кнопка "Свернуть/развернуть"
		echo '<form name="hiderowsbtnform" action="logout.php" method="post" id="hiderowsbtnform" class="row" onsubmit="hideRows(this)">';
		echo '<input name="hiderowsbtn" type="submit" value="-">';
		echo '</form>';
		*/

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

		/*
		// Запрос строк групп номенклатуры

		echo '<div class="matrix_goods">';
		echo '<button onclick="Show_matrix_goods()" class="button button_goods_left">Товары по вашей матрице</button>';
		echo '<ul id="accordion" class="accordion">';

		$mysqlQText = "SELECT * FROM nomenclaturegroups WHERE isdeleted = FALSE AND parentguid = '00000000-0000-0000-0000-000000000000' ORDER BY fullname ";
		$mysqlQuery = mysqli_query($con, $mysqlQText);
		$mysqlErrNo = mysqli_errno($con);
		$mysqlError = mysqli_error($con);
		if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
		$numrows = mysqli_num_rows($mysqlQuery);

		/*
		// Вывод строк групп номенклатуры
		if ($numrows > 0) {
			//echo $mysqlQText;
			while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {

				$addClassInOrder = '';

				echo '<li>';
			     echo '<div class="link"><i class="fa fa-drumstick-bite"></i>' . $row['fullname'] . '</div>';
/*
					echo '<div class="product_group_row'.$addClassInOrder.'" onclick="setProductGroup(this)">';
					echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
					echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
					echo '<div id="product_group_name">' . $row['fullname'] . '</div>';
					echo '</div>';
					*/

					/*
					$param = "'". $row['guid']."'";

					$mysqlQText2 = "SELECT * FROM nomenclaturegroups WHERE isdeleted = FALSE AND parentguid = $param ORDER BY fullname ";
					$mysqlQuery2 = mysqli_query($con, $mysqlQText2);
					$mysqlErrNo2 = mysqli_errno($con);
					$mysqlError2 = mysqli_error($con);
					if ($mysqlErrNo2!= 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo2 . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText2;
					$numrows2 = mysqli_num_rows($mysqlQuery2);

					echo '<ul class="submenu">';

					if ($numrows2 > 0) {
						while ($row2 = mysqli_fetch_array($mysqlQuery2, MYSQLI_ASSOC)) {


			       echo '<li class="elem"><a href="#" onclick="setProductGroup(this)">' . $row2['fullname'] . '</a>';
			       	
							echo '<div class="product_group_row'.$addClassInOrder.'" onclick="setProductGroup(this)">';
							echo '<div id="guid" class="data">' . $row2['guid'] . '</div>';
							echo '<div id="parent_guid" class="data">' . $row2['parentguid'] . '</div>';
							echo '<div id="product_group_name">' . $row2['fullname'] . '</div>';
							echo '</div>';
							echo '</li>';
							
						}
					}
					echo '</ul>';
					echo '</li>';		
			}
		}

			  
	echo '</div>';
	*/
	
		

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

	// Выводим перечисление номеров страниц
	//echo '<p class="pages">Страница:';
	echo '<p class="pages">';
	for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
		// если номер страницы соответствует текущей
		if ($_GET["page"]==$i && $maxPage > 1) {
			echo '<a style="color: blue;" href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
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


	
	// Выводим перечисление номеров страниц
	//echo '<p class="pages">Страница:';
	echo '<p class="pages">';
	for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
		// если номер страницы соответствует текущей
		if ($_GET["page"]==$i && $maxPage > 1) {
			echo '<a style="color: blue;" href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
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
	echo '<div id="overall_info" class="animated">Сумма заказа:<br>0.00</div>';
	echo "</div>";
?>