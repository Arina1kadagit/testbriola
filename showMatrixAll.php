<?php

$orderGUID = $_GET['orderGUID'];

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/getMatrixWithOrder');
curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

// Массив передаваемых сервису параметров
$arrayBodyRequest = [
	"orderGUID" => $orderGUID
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

	// Если страница не задана, будем выводить первую
	if (!isset($_GET["page"])) $_GET["page"] = 1;

	// Если родитель не задан, то родителя нет
	if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '00000000-0000-0000-0000-000000000000';

	//хлебные крошки
			
	echo '<div class="breadcrumbs breadcrumbs_tp">';

	$menu = 'Товары по вашей матрице >';
	$category_name = 'Вся матрица';
	 
	echo '<div>' . $menu . '&nbsp </div>';
	echo '<div id="second_of_breadcrumbs">'.$category_name.'</div>';
	echo '<div></div>';

	echo '</div>';

	echo '<div class="category_name category_name_tp">'.$category_name.'</div>';

//Конец хлебных крошек

	// +++ ВЫВОД ДОКУМЕНТА +++
	echo '<div id="order_table">';
	// Выводим заголовок и кнопки
	echo '<div id="doc_title"><h2>'.$arrayBodyResponse['orderFullName'].' 
	<br/>
	<span style="color: #3D3A3A">«'.$arrayBodyResponse['counterpartyName'].', '.$arrayBodyResponse['contractFullName'].'»</span></h2></div>';


	//////////////////// сумма долга

	echo '<div class="contragent_dolg_container">';
	echo '<button class="button" id="contragent_dolg" onclick = "displayDebtHistory()">Сумма долга: 0.00</button>';
	echo '<div id="dolg_history">

	<table id="dolg_history_table" width = "100%" border = "1">
		<caption>Дебиторская задолженность</caption>
		<tr>
			<td>Номер</th>
			<td>Дата</th>
			<td>Сумма документа</th>
			<td>Сумма долга</th>
			<td>Дней просрочки</th>
		</tr>';


		//echo $_SESSION['arrContract'];
	$keys = array_keys($_SESSION['arrContract']);
	foreach ($keys as $key){
	    
	    $elem = $_SESSION['arrContract'];
	    $elem2 = $elem[$key];

		/*
		$keys2 = array_keys($elem2);
	    foreach ($keys2 as $key2){
	    	 echo 'key - '.$key2;
			echo '<br>';
	    }
	    */

	    //echo $elem2[0]; //сумма долга
	    //echo $elem2[1];

	    $elem3 = $elem2[2];
	    if (count($elem3) != 0) { //если есть хоть один договор
	    	foreach ($elem3 as $elem4) {
	    		echo '<tr class="dolg_history_row">';
				foreach ($elem4 as $elem5) {
				    echo '<td>'.$elem5.'</td>';			
				}
				echo '<td hidden class="dolg_history_row_contactGUID">'.$key.'</td>';
				echo '</tr>';	
			} 
		} else{
			echo '<tr class="dolg_history_row">';
		    echo '<td>Номер</td>';
			echo '<td>Дата</td>';
			echo '<td>Сумма документа</td>';
			echo '<td>Сумма долга</td>';
			echo '<td>Дней просрочки</td>';
			echo '<td hidden class="dolg_history_row_contactGUID">0000</td>';
			echo '</tr>';	
		}
	}

	echo '</table></div></div>';

	/////////////////////// конец сумма долга

	// Кнопка "Закрыть"
	echo '<form name="closesessionbtnform" action="logout.php" method="post" id="closesessionbtnform" class="row">';
		echo '<input name="closesessionbtn" type="submit" value="×">';
	echo '</form>';
	// Кнопка "Свернуть/развернуть"
	echo '<form name="hiderowsbtnform" action="logout.php" method="post" id="hiderowsbtnform" class="row" onsubmit="hideRows(this)">';
	echo '<input name="hiderowsbtn" type="submit" value="-">';
	echo '</form>';

	// Данные ГУИД договора
	echo '<div id="contract_GUID" class="data">'.$arrayBodyResponse['contractGUID'].'</div>';


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


	//Массив GUID по матрице
	$arrProductsGUIDs = array();
	foreach ($_SESSION['session_userMatrix'] as $key => $value) {
		array_push($arrProductsGUIDs, $value['productGUID']);
	}
	//echo ' count array  - '.count($arrProductsGUIDs);
	//Добавляем в этот массив товары не из матрицы
	if($_SESSION['session_userNoMatrix'] != ""){
		foreach ($_SESSION['session_userNoMatrix'] as $key => $value) {
		array_push($arrProductsGUIDs, $value['productGUID']);
		}
	}
	//echo ' count array  - '.count($arrProductsGUIDs);

	$strProductsGUIDs = join("','", $arrProductsGUIDs);
	$strProductsGUIDs = '\'' . $strProductsGUIDs . '\'';

	//echo 'count session - '.count($_SESSION['session_userMatrix']);
	//echo ' count array  - '.count($arrProductsGUIDs);


	//Выводить будем:
	//1) выводятся товары по матрице + заказанные не по матрице 
	//2) не по категории - скрываются
					
	// запрос строк для выводимой страницы
	//выводим всю матрицу, показываем по группе (идентично запросу 1)
	$mysqlQText = "SELECT n.guid, parentguid, shortname, expirationDate, measure, number FROM nomenclatures n INNER JOIN nomenclaturesort s ON n.guid = s.guid WHERE n.guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE ORDER BY number";
	//$mysqlQText = "SELECT guid, parentguid, shortname, expirationDate, measure FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры для указанной страницы, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	$numrows = mysqli_num_rows($mysqlQuery);

	//echo '</br>';
	//echo '3 - '.$numrows;

	// Вывод с ограничением по количеству строк, если источник - выборка запроса, а не массив
	$index = 0;
	$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
	$endIndex = $startIndex + $rowsOnPage - 1;
	//echo ' numrows - '.$numrows;
	$maxPage = ceil($numrows/$rowsOnPage);

	if ($numrows > 0) {
		//echo $mysqlQText;
		while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {

			$addClassInOrder = '';

			//Для товара в заказе изменяем кол-во - товар из матрицы
			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productGUID'] == $row['guid']) {
					$productQuantity = $arrProduct['productQuantity'];
					if ($arrProduct['productQuantity'] != 0){
						$productQuantity = $arrProduct['productQuantity'];
						break; //новое - иначе может не показываться
					}
				}
			}

			//Для товара в заказе изменяем кол-во - товар НЕ из матрицы
			if($_SESSION['session_userNoMatrix'] != ""){
				foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
					if ($arrProduct['productGUID'] == $row['guid']) {
						$productQuantity = $arrProduct['productQuantity'];
						if ($arrProduct['productQuantity'] != 0){
							$addClassInOrder .= ' all_in_order'; 
						}
					}	
				}
			}
			

			$addClassInOrder .= ' in_order';
			if ($productQuantity == 0) {
				$productQuantity = '';
				$addClassInOrder = '';
			} 


			if (!($index >= $startIndex && $index <= $endIndex)) {
				$addClassInOrder.= ' hide_on_page';
			}
				$index++;

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
			echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)" onkeypress="if(event.keyCode == 13) return false;">';
			echo '</form>';
			echo '<div id="product_history"><div>' . '' . '</div></div>';
			echo '</div>';
		}
	}
	

	mysqli_close($con);


	//////////

	// Выводим перечисление номеров страниц
	//echo '<p class="pages">Страница:';
	echo '<p class="pages">';

	echo '<a onclick="previous_block_page()"><</a>';

	for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
		// если номер страницы соответствует текущей
		if ($_GET["page"]==$i && $maxPage > 1) {
			echo '<a class="page_display" style="color: black; background: linear-gradient(
		91.65deg, #AFFC38 2.43%, #F6FD41 100%); font-weight: 600;" href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';

		// если номер страницы не соответствует текущей
		} else {
			
			if ($i <= $maxPage && $maxPage > 1 && $i<=10) {
				echo '<a class="page_display" href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
			} else {
				if($i <= $maxPage){
					echo '<a class="page_display_none" href="?page='.$i.'" style="display: none" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
				}
				else{
					echo '<a  href="?page='.$i.'" style="display: none" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
				}
				
				//echo '<a href="#" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
			}
		}
	}

	echo '<a onclick="next_block_page()">></a>';
	echo '</p>';

	echo '<div class="sendformcont">';
					echo '<input id="comment_tp" class="form_select" placeholder="Комментарий">';
					//disabled
					//echo '<div class="button" onclick="addToOrderTT()">Добавить в заказ</div>';
	echo '</div>';


	if ($_SESSION['blacklist'] == '1'){ //клиент в черном списке
		echo '<div class="sendformcont">
			<h2 class="doc_title_1">Клиент в черном списке!
			</h2>
		</div>';
	} else{
		// Выводим форму подтверждения заказа
	$action4form = 'sendorder.php';
	echo '<div class="sendformcont"><form name="sendorderformname" action="'.$action4form.'" method="post" id="sendorderformid" class="row">';
		echo '<div class="button" onclick="sendOrderTP(\''.$_GET["orderGUID"].'\')">Подтвердить заказ</div>';
		//echo '<div class="button" onclick="addToOrderTT()">Добавить в заказ</div>';
	echo '</form></div>';
	}

	// Помещаем данные выведенного заказа в переменные сессии
	$_SESSION['session_orderGUID'] = $_GET["orderGUID"];
	$_SESSION['session_contractGUID'] = $arrayBodyResponse['contractGUID'];
	/*$_SESSION['session_userMatrix'] = $arrayOfProducts;*/
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
echo '</div>'; // .order_table

echo '<div id="goodbye">
	<p><a href="logout.php">Выйти</a> из системы.</p>
</div>
</div>';

			

?>