<?php require_once("includes/connection.php"); ?>
<?php
session_start();

		// ПАРАМЕТРЫ ДОКУМЕНТА:
		
		// Заголовок
		//$journalName = '';
		// Количество колонок
		$numberOfCols = 6;
		// Строк на странице
		$rowsOnPage = 10;
		// Названия колонок
		$columnsNames = '[GUID]&&&Картинка&&&Наименование&&&Цена&&&Количество на складе&&&Количество заказать';

		// Если страница не задана, будем выводить первую
		if (!isset($_GET["page"])) $_GET["page"] = 1;

		// Если родитель не задан, то родителя нет
		if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '00000000-0000-0000-0000-000000000000';

		echo '<div id="order_GUID" class="data">'. $_GET["orderGUID"]. '</div>';

		//$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		//echo $url;	

		$orderGUID = $_GET["orderGUID"];
		$parentGUID = $_GET["parentGUID"];

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

					// Если страница не задана, будем выводить первую
					if (!isset($_GET["page"])) $_GET["page"] = 1;

					// Если родитель не задан, то родителя нет
					if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '00000000-0000-0000-0000-000000000000';

					//хлебные крошки
							
					echo '<div class="breadcrumbs breadcrumbs_tp">';
					 $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

					$guid = strstr($url, '&parentGUID'); //находит первое нахождение подстроки, знак &
					$guid = trim($guid, '&parentGUID='); //удаляет из строки, знак &
					$menu = 'Товары по вашей матрице >';


					if (isset($_GET["showall"])){
						$guid = trim($guid, '&showall=true'); //удаляет из строки
						$menu = 'Ассортимент компании >';
					}

				///echo $guid;
					

					//Вывод категории второго уровня
					$mysqlQText = "SELECT fullname,parentguid FROM nomenclaturegroups WHERE guid='" . $guid . "'";
					$mysqlQuery = mysqli_query($con, $mysqlQText);
					$mysqlErrNo = mysqli_errno($con);
					$mysqlError = mysqli_error($con);
					if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
					$numrows = mysqli_num_rows($mysqlQuery);
					//echo $numrows;

				// Вывод строк групп номенклатуры
					if ($numrows > 0) {
						$category_name;
					//echo $mysqlQText;
					while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
						$parent = $row['parentguid'];
						

						//Вывод категории первого уровня
						$mysqlQText = "SELECT fullname FROM nomenclaturegroups WHERE guid='" . $parent . "'";
						$mysqlQuery = mysqli_query($con, $mysqlQText);
						$mysqlErrNo = mysqli_errno($con);
						$mysqlError = mysqli_error($con);
						if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
							$numrows = mysqli_num_rows($mysqlQuery);

						// Вывод строк групп номенклатуры
						if ($numrows > 0) {
						//echo $mysqlQText;
							while ($row2 = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
								echo '<div>' . $menu . '&nbsp </div>';
								echo '<div id="second_of_breadcrumbs">' . $row2['fullname'] . '&nbsp>&nbsp</div>';
							}
						};

						//echo ' > ';
						echo '<div>' . $row['fullname'] . '</div>';
						$category_name = $row['fullname'];
						//echo '<div class="category_name">' . $row['fullname'] . '</div>';
					};
				};
				echo '</div>';
				if (isset($category_name)){
					echo '<div class="category_name category_name_tp">' . $category_name. '</div>';
				}

				//Конец хлебных крошек

					// +++ ВЫВОД ДОКУМЕНТА +++
					echo '<div id="order_table">';
					// Выводим заголовок и кнопки
					echo '<div id="doc_title"><h2>'.$arrayBodyResponse['orderFullName'].' 
					<br/>
					<span style="color: #3D3A3A">«'.$arrayBodyResponse['counterpartyName'].', '.$arrayBodyResponse['contractFullName'].'»</span></h2></div>';

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
					
		
					// Запрос только тех строк которые будут видны (чтобы предварительно знать их количество для вывода номеров страниц)
					//или ВСЕ
					$arrayOfProducts = $arrayBodyResponse['arrayOfItems'];
					$numrowsInGroup = 0;
					foreach ($arrayOfProducts as $index1 => $arrayOfProduct) {
						$productParentGUID = $arrayOfProduct['parentGUID'];
						if ($productParentGUID == $parentGUID) { //если все товары выводятся то == $_GET["parentGUID"]
							$numrowsInGroup++;
						}
					}
					$maxPage = ceil($numrowsInGroup/$rowsOnPage);
					//echo 'numrowsInGroup - '.$numrowsInGroup;
					//echo ' rowsOnPage - '.$rowsOnPage;		
					//echo ' maxPage - '.$maxPage;	

					$index = 0;
					$numrows = 0;

					$orderGUID = "'". $orderGUID. "'";

					$matrixOrders = array();

					foreach ($arrayOfProducts as $index1 => $arrayOfProduct) {
						$productGUID = $arrayOfProduct['productGUID'];
						$productParentGUID = $arrayOfProduct['parentGUID'];
						$productShortName = $arrayOfProduct['productShortName'];
						$productPrice = $arrayOfProduct['productPrice'];
						$productWeight = $arrayOfProduct['productWeight'];
						$productQuantity = $arrayOfProduct['productQuantity'];
						
						$addClassInOrder = ' in_order';
						if ($productQuantity == 0) {
							$productQuantity = '';
							$addClassInOrder = '';
						}

						if ($productParentGUID != $parentGUID) {
							$addClassInOrder.= ' hide_on_page';
						} else {
							$numrows++;
							if ($index > $rowsOnPage - 1) {
								$addClassInOrder.= ' hide_on_page';
							}
							$index++;
						}


						echo '<div class="journal_row'.$addClassInOrder.'">';
						echo '<div id="guid" class="data">'.$productGUID.'</div>';
						echo '<div id="parent_guid" class="data">'.$productParentGUID.'</div>';
						//echo '<div id="product_pic"><img src="image.php?guid='.$productGUID.'" /></div>';
						echo '<div id="product_pic" onclick="openProductCard(this)"><img srс="" data-src="image.php?guid='.$productGUID.'" class="lazy"/><noscript><img src="image.php?guid='.$productGUID.'" /></noscript></div>';
						echo '<div id="product_name">'.$productShortName.'</div>';
						echo '<div id="product_balance">'.''.'</div>';
						echo '<div id="product_price">'.$productPrice.' ₽ / ед.</div>';
						echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
						echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this)" onkeydown="onlyNumber(this)">';
						echo '</form>';
						echo '<div id="product_history">' . '' . '</div>';
						echo '</div>';

					}
					//echo 'numrows'.$numrows;

					/*
					if (isset($_SESSION['session_userMatrix'])){
						echo 'yes!';
					}else{
						echo 'no!';
					}
					*/
			



					// Выводим перечисление номеров страниц
					//echo '<p class="pages">Страница:';
					echo '<p class="pages">';
					for ($i = 1; $i<= (ceil($numrows/$rowsOnPage)); $i++) {
						// если номер страницы соответствует текущей
						if ($_GET["page"]==$i && $maxPage > 1) {
							echo '<a style="color: black; background: linear-gradient(
						91.65deg, #AFFC38 2.43%, #F6FD41 100%); font-weight: 600;" href="?page='.$i.'" onclick="setPage(\''.$i.'\')">'.$i.'</a>';
	
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
					echo '<div class="sendformcont"><form name="sendorderformname" action="'.$action4form.'" method="post" id="sendorderformid" class="row">';
						echo '<div class="button" onclick="sendOrderTP(\''.$_GET["orderGUID"].'\')">Подтвердить заказ</div>';
						//echo '<div class="button" onclick="addToOrderTT()">Добавить в заказ</div>';
					echo '</form></div>';
					//echo '</div>';
					//echo '<div id="overall_info" class="animated">Сумма заказа:<br>0.00</div>';

					// Помещаем данные выведенного заказа в переменные сессии
					$_SESSION['session_orderGUID'] = $_GET["orderGUID"];
					$_SESSION['session_contractGUID'] = $arrayBodyResponse['contractGUID'];
					$_SESSION['session_userMatrix'] = $arrayOfProducts;
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

			

?>