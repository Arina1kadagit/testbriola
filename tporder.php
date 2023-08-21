<?php
//показываем всю матрицу

	session_start();
	header('X-Accel-Buffering: no');
	header('Content-type: text/html; charset=utf-8');
	if (!isset($_SESSION['session_username']) || $_SESSION['session_userRole'] != 'Seller') : 
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
// Инициализируем переменную сообщения
$message = '';

if (isset($_GET["orderGUID"])) {
	
	// Очищаем переменные сессии
	
	$_SESSION['session_orderGUID'] = '';
	$_SESSION['session_contractGUID'] = '';
	/*$_SESSION['session_userMatrix'] = '';*/


	echo '<div id="order_GUID" class="data">'. $_GET["orderGUID"]. '</div>';

	$_SESSION['session_orderGUID'] =  $_GET["orderGUID"];

	// ПАРАМЕТРЫ ДОКУМЕНТА:
	
	// Заголовок
	//$journalName = '';
	// Количество колонок
	$numberOfCols = 6;
	// Строк на странице
	$rowsOnPage = 50;
	// Названия колонок
	$columnsNames = '[GUID]&&&Картинка&&&Наименование&&&Цена&&&Количество на складе&&&Количество заказать';

	// Если страница не задана, будем выводить первую
	if (!isset($_GET["page"])) $_GET["page"] = 1;

	// Если родитель не задан, то родителя нет
	if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '';
	/*
	echo $_GET["parentGUID"];
	echo '</br>';

	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	echo $url;
	*/


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

				//$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
				//echo $url;

				// +++ ВЫВОД ДОКУМЕНТА +++
				echo '<div id="order_table">';
				// Выводим заголовок и кнопки
				echo '<div id="doc_title"><h2 class="doc_title_1">'.$arrayBodyResponse['orderFullName'].' 
				</h2>
				<h2>
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
						    	
								/*

						    	echo '<tr class="dolg_history_row">';
							    echo '<td>'.$elem3[0].'</td>';
								echo '<td>'.$elem3[1].'</td>';
								echo '<td>'.$elem3[2].'</td>';
								echo '<td>'.$elem3[3].'</td>';
								echo '<td>'.$elem3[4].'</td>';
								echo '<td hidden class="dolg_history_row_contactGUID">'.$key.'</td>';
								echo '</tr>';	
								*/
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

							/*
						arrHistory = arr[strIndx].arrayOfOrder;
						for (strHistoryIndx in arrHistory) {
							numberOrder = arrHistory[strHistoryIndx].numberOrder;
							dateOrder = arrHistory[strHistoryIndx].dateOrder;
							quantity = arrHistory[strHistoryIndx].quantity;
							price = arrHistory[strHistoryIndx].price;
							sum = arrHistory[strHistoryIndx].sum;
							//hString = hString + numberOrder + dateOrder + quantity + price + sum + '<br>';
							hString = hString + '<tr><td>'+dateOrder.substring(0, 10)+'</td>numberOrder<td>'++'</td><td>'+sum+'</td><td>'+price+'</td><td>'+quantity+'</td></tr>';
						}
						hString = hString + '</table></div>';
						elemHistory.innerHTML = hString;
						//console.log(stringWithProduct);
						*/

						echo '</table></div></div>';

				/////////////////////// конец сумма долга

				// Кнопка "Закрыть"
				echo '<form name="closesessionbtnform" action="logout.php" method="post" id="closesessionbtnform" class="row" onsubmit="closeOrder()">';
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

				//ВЫВОД ВСЕЙ МАТРИЦЫ

				//Выводим товары по всей матрице, первые 100 строк
				$arrayOfProducts = $arrayBodyResponse['arrayOfItems'];
				$numrowsInGroup = 0;
				foreach ($arrayOfProducts as $index1 => $arrayOfProduct) {
					$productParentGUID = $arrayOfProduct['parentGUID'];
						$numrowsInGroup++;
				}
				$maxPage = ceil($numrowsInGroup/$rowsOnPage);

				$arrayOfProducts = $arrayBodyResponse['arrayOfItems'];
				//$numrows = count($arrayOfProducts);
				$numrows = 100;
				//echo $numrows;
				$index = 0;

				/*
				$keys = array_keys($arrayOfProducts[0]);
				foreach ($keys as $key) {
					echo $key;
				}
				*/
				

				foreach ($arrayOfProducts as $index1 => $arrayOfProduct) {
					$productGUID = $arrayOfProduct['productGUID'];
					$productParentGUID = $arrayOfProduct['parentGUID'];
					$productShortName = $arrayOfProduct['productShortName'];
					$productPrice = $arrayOfProduct['productPrice'];
					//$productMeasure = $arrayOfProduct['measure'];
					$productWeight = $arrayOfProduct['productWeight'];
					$productQuantity = $arrayOfProduct['productQuantity'];
			
					/*$arrPorduct = array('productGUID' => $value, 'productQuantity' => 0);
					$_SESSION['session_MatrixWithOrder'];
					array_push($_SESSION['session_MatrixWithOrder'], $arrPorduct);*/

					$addClassInOrder = ' in_order';
					if ($productQuantity == 0) {
						$productQuantity = '';
						$addClassInOrder = '';
					}

					if ($index > $rowsOnPage - 1) {
							$addClassInOrder.= ' hide_on_page';
						}
						$index++;

					//Выводим первые 100
					if ($index <= 100){ 
						//запрос на срок годности и ед изм
						$mysqlQText = "SELECT expirationDate,measure FROM nomenclatures WHERE isdeleted = FALSE AND guid = '".$productGUID."';";
						$mysqlQuery = mysqli_query($con, $mysqlQText);
						$mysqlErrNo = mysqli_errno($con);
						$mysqlError = mysqli_error($con);
						if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
						$numrows2 = mysqli_num_rows($mysqlQuery);
						if ($numrows2 > 0) {
							//echo $mysqlQText;
							while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
								$productMeasure = $row['measure'];
								$productExpDate = $row['expirationDate'];
							}
						}

						$addClassInOrder.= ' first';

						echo '<div class="journal_row'.$addClassInOrder.'">';
						echo '<div id="guid" class="data">'.$productGUID.'</div>';
						echo '<div id="parent_guid" class="data">'.$productParentGUID.'</div>';
						echo '<div id="product_pic" onclick="openProductCard(this)"><img srс="" data-src="image.php?guid='.$productGUID.'" class="lazy"/><noscript><img src="image.php?guid='.$productGUID.'" /></noscript></div>';
						echo '<div id="product_name">'.$productShortName.'</div>';
						echo '<div id="product_balance">'.''.'</div>';
						echo '<div id="product_price">'.$productPrice.' ₽ / ед.</div>';
						echo '<div id="product_exp_date">Срок годности:'.$productExpDate.'дней</div>';
						echo '<div id="product_measure">' . $productMeasure . '</div>';
						echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
						echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)" onkeypress="if(event.keyCode == 13) return false;">';
						echo '</form>';
						echo '<div id="product_history">' . '' . '</div>';
						echo '</div>';
					}
					else { //Выводим дополнительно заказанные
						$productGUID = $arrayOfProduct['productGUID'];
						$productParentGUID = $arrayOfProduct['parentGUID'];
						$productShortName = $arrayOfProduct['productShortName'];
						$productPrice = $arrayOfProduct['productPrice'];
						//$productMeasure = $arrayOfProduct['measure'];
						$productWeight = $arrayOfProduct['productWeight'];
						$productQuantity = $arrayOfProduct['productQuantity'];
				

						$addClassInOrder = ' in_order';
						if ($productQuantity == 0) {
							$productQuantity = '';
							$addClassInOrder = '';
						}

						if ($index > $rowsOnPage - 1) {
								$addClassInOrder.= ' hide_on_page';
							}
							

						if (($addClassInOrder == ' in_order')||($addClassInOrder == ' in_order hide_on_page')){
							$index++;

							$addClassInOrder.= ' add';

							//запрос на срок годности и ед изм
							$mysqlQText = "SELECT expirationDate,measure FROM nomenclatures WHERE isdeleted = FALSE AND guid = '".$productGUID."';";
							$mysqlQuery = mysqli_query($con, $mysqlQText);
							$mysqlErrNo = mysqli_errno($con);
							$mysqlError = mysqli_error($con);
							if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
							$numrows2 = mysqli_num_rows($mysqlQuery);
							if ($numrows2 > 0) {
								//echo $mysqlQText;
								while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
									$productMeasure = $row['measure'];
									$productExpDate = $row['expirationDate'];
								}
							}

							echo '<div class="journal_row'.$addClassInOrder.'">';
							echo '<div id="guid" class="data">'.$productGUID.'</div>';
							echo '<div id="parent_guid" class="data">'.$productParentGUID.'</div>';
							echo '<div id="product_pic" onclick="openProductCard(this)"><img srс="" data-src="image.php?guid='.$productGUID.'" class="lazy"/><noscript><img src="image.php?guid='.$productGUID.'" /></noscript></div>';
							echo '<div id="product_name">'.$productShortName.'</div>';
							echo '<div id="product_balance">'.''.'</div>';
							echo '<div id="product_price">'.$productPrice.' ₽ / ед.</div>';
							echo '<div id="product_exp_date">Срок годности:'.$productExpDate.'дней</div>';
							echo '<div id="product_measure">' . $productMeasure . '</div>';
							echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
							echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)" onkeypress="if(event.keyCode == 13) return false;">';
							echo '</form>';
							echo '<div id="product_history">' . '' . '</div>';
							echo '</div>';
						}
					}	
				}
			
				// Выводим перечисление номеров страниц
				//echo '<p class="pages">Страница:';
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
							//echo '<a href="#" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
						}
					}
				}
				echo '</p>';


				echo '<div class="sendformcont">';
					echo '<input id="comment_tp" class="form_select" placeholder="Комментарий">';
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

				
				//echo '</div>';
				//echo '<div id="overall_info" class="animated">Сумма заказа:<br>0.00</div>';

				/////////////////////////////////////////

				// Помещаем данные выведенного заказа в переменные сессии
				$_SESSION['session_orderGUID'] = $_GET["orderGUID"];
				$_SESSION['session_contractGUID'] = $arrayBodyResponse['contractGUID'];
				$_SESSION['session_userMatrix'] = $arrayOfProducts; ///??? проверить тут
				//если добавляенм заказ на этой странице, отобразится ли он при выборе категории 9Открытии другой страницы)

				/*
				foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
					if ($arrProduct['productQuantity'] != 0) {
						echo $arrProduct['productQuantity'];
					}
				}*/

				/////////////////////////////////////////


				
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
} else {
	$message.= 'Ошибка при получении данных заказа из 1С: GUID заказа не указан!';
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

