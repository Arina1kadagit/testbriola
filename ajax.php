<?php 

session_start();

$name = $_POST['name'];
//echo $name;

require_once("includes/connection.php");

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

	if ($name == ''){
		//если пустой текст - выводим товары по выбранной категории
		//сейчас с ПЕРЕЗАГРУЗКОЙ
	}
	else{ 

	//Массив GUID по матрице
	$arrProductsGUIDs = array();
	foreach ($_SESSION['session_userMatrix'] as $key => $value) {
		array_push($arrProductsGUIDs, $value['productGUID']);
	}
	$strProductsGUIDs = join("','", $arrProductsGUIDs);
	$strProductsGUIDs = '\'' . $strProductsGUIDs . '\'';

	$message = '';
	$index = 0;
	$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
	$endIndex = $startIndex + $rowsOnPage - 1; 
	
	$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted = FALSE AND guid IN (".$strProductsGUIDs.") AND fullname like '%".$name."%' ORDER BY fullname LIMIT 100";
	/*
	$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted = FALSE AND fullname like '%".$name."%' ORDER BY fullname LIMIT 100";
	*/
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	$numrows = mysqli_num_rows($mysqlQuery);
	//echo $numrows;
	$maxPage = ceil($numrows/$rowsOnPage);


	$arrPrint = array(); //массив выведенных - для избежания повторений

	if ($numrows > 0) {
		while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {

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


		if (!($index >= $startIndex && $index <= $endIndex)) {
				$addClassInOrder.= ' hide_on_page';
			}
			$index++;

			array_push($arrPrint, $row['guid']);

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
			echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="0" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)">';

			echo '</form>';
			echo '<div id="product_history"><div>' . '' . '</div></div>';
			echo '</div>';
		}
	}

	ini_set('memory_limit', '-1');

	//+выводим заказанные товары

	$mysqlQText2 = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE ORDER BY parentNomenclaturName, fullname";
	$mysqlQuery2 = mysqli_query($con, $mysqlQText2);
	$mysqlErrNo2 = mysqli_errno($con);
	$mysqlError2 = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo2 . ', текст ошибки: ' . $mysqlError2 . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText2;
	$numrows2 = mysqli_num_rows($mysqlQuery2);

	mysqli_close($con);

	if ($numrows2 > 0) {
	//echo $mysqlQText;
		while ($row2 = mysqli_fetch_array($mysqlQuery2, MYSQLI_ASSOC)) {

			$addClassInOrder = ' '; 
			$productQuantity = 0;
			if ($productQuantity == 0) {
				$productQuantity = '';
				$addClassInOrder = '';
			}

		//товары по матрице - выделяем цветом
		foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productGUID'] == $row2['guid']) {
					$productQuantity = $arrProduct['productQuantity'];
					if ($productQuantity != 0) {
						$addClassInOrder = ' in_order';
					}
				}
			}

			
			if (!($index >= $startIndex && $index <= $endIndex)) {
					$addClassInOrder.= ' hide_on_page';
				}
				$index++;

			//если товар из заказа выведен выше, то его не выводим - для избежания повторений
			foreach ($arrPrint as $printGUID){
				if ($row2['guid'] == $printGUID){
					$addClassInOrder.= ' no_print';
				}
			}
				
			//если товар в заказе и не повторяется, то выводим его по прячем в корзину
			if (($addClassInOrder == ' in_order')||($addClassInOrder == ' in_order hide_on_page')){

			$addClassInOrder.= ' hidden_row';

			echo '<div class="journal_row order'.$addClassInOrder.'" onclick="showHistory(this)">';
			echo '<div id="guid" class="data">' . $row2['guid'] . '</div>';
			echo '<div id="parent_guid" class="data">' . $row2['parentguid'] . '</div>';
			echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row2['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row2['guid'].'" /></noscript></div>';
			echo '<div id="product_name">' . $row2['shortname'] . '</div>';
			echo '<div id="product_balance">' . '' . '</div>';
			echo '<div id="product_price">' . '' . '</div>';
			echo '<div id="product_exp_date">Срок годности: ' . $row2['expirationDate'] . ' дней</div>';
			echo '<div id="product_measure">' . $row2['measure'] . '</div>';
			echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
			echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onChange="hasOrder()" onkeydown="onlyNumber(this)">';
			echo '</form>';
			echo '<div id="product_history"><div>' . '' . '</div></div>';
			echo '</div>';
			}
		}
	}


$numrows.= $numrows2;

// Выводим перечисление номеров страниц
echo '<p class="pages">';
for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
	// если номер страницы соответствует текущей
	if ($_GET["page"]==$i && $maxPage > 1) {
		echo '<a style="color: black; background: linear-gradient(
				91.65deg, #AFFC38 2.43%, #F6FD41 100%); font-weight: 600;" href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
	} else {
		if ($i <= $maxPage && $maxPage > 1) {
			echo '<a href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
		} else {
			echo '<a href="?page='.$i.'" style="display: none" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
		}
	}
}
echo '</p>';

// Выводим форму отправки заказа
$action4form = 'sendorder.php';

echo '<div class="sendformcont"><form id="sendorderformid" class="row" name="sendorderformname" action="'.$action4form.'" method="post">';
	echo '<div class="button" onclick="sendOrderTT()">Заказать</div>';
echo '</form></div>';
echo "</div>";

};




?>