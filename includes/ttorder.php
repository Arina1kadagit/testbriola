<?php //require_once("includes/connection.php"); ?>

<?php
	// Переменные документа:
	// Заголовок
	$journalName = 'Товары для заказа:';
	// Количество колонок
	$numberOfCols = 6;
	// Строк на странице
	$rowsOnPage = 50;
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

		//Массив GUID по матрице
		$arrProductsGUIDs = array();
		foreach ($_SESSION['session_userMatrix'] as $key => $value) {
			array_push($arrProductsGUIDs, $value['productGUID']);
		};
		if (count($arrProductsGUIDs) == 0){
			echo '<p class="no_matrix_text">Матрица не задана, обратитесь к вашему ТП</p>';
		}
		$strProductsGUIDs = join("','", $arrProductsGUIDs);
		$strProductsGUIDs = '\'' . $strProductsGUIDs . '\'';

		/*
		if ($_SESSION['session_userNoMatrix'] != ''){
			echo 'count session_noMatrix - '.count($_SESSION['session_userNoMatrix']);
		}
		echo '</br>';
		echo 'count session_Matrix - '.count($_SESSION['session_userMatrix']);
		*/

		/*
		//Вывод заказанных товаров не из матрицы
		//echo 'count session_noMatrix - '.count($_SESSION['session_userNoMatrix']);
		if ($_SESSION['session_userNoMatrix'] != ''){
			foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
				echo $arrProduct['productGUID'].' - '.$arrProduct['productQuantity'];
				echo '</br>';
			}
		}
		*/

		/*
		foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
			echo $arrProduct['productGUID'];
			echo '</br>';
		}
		*/
		

		if ($_GET["parentGUID"] == 'none') { //общий код для двух меню для второй части - вывода товаров по матрице, не выведенных в первой части вывода
			//общее для двух меню
			$index = 0;
			$countPrint = 0;
			$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
			$endIndex = $startIndex + $rowsOnPage - 1;

			//выбор меню
			if (isset($_GET["showall"])){ //весь ассортимент
				//Выводить будем
				//1) товары все - заказанные + не заказанные (500)
				//2) товары по матрице - заказанные, которые не попали в этот список
				//3) товары не из матрицы заказанные, которые не попали в это  список
                
				//Для вывода всех записей
				$arrayInOrder_2 = array(); //Массив заказанных	

				//Позиции по всей номенклатуре (100) 
				$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted =FALSE ORDER BY parentNomenclaturName, fullname LIMIT 50000";
				$mysqlQuery = mysqli_query($con, $mysqlQText);
				$mysqlErrNo = mysqli_errno($con);
				$mysqlError = mysqli_error($con);
				if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
				$numrows = mysqli_num_rows($mysqlQuery);

				//$maxPage = ceil($numrows/$rowsOnPage);

				if ($numrows > 0) {
					//echo $mysqlQText;
					while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
						
						//все товары, которые не по матрице зануляем (чтобы они не выделялись цветом)
						$addClassInOrder = ' '; 
						$productQuantity = 0;
						if ($productQuantity == 0) {
							$productQuantity = '';
							$addClassInOrder = '';
						}

						//находим соответствие в матрице
						foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
							if ($arrProduct['productGUID'] == $row['guid']) {
								$addClassInOrder = ' matrix';
								$productQuantity = $arrProduct['productQuantity'];
								if ($productQuantity != 0) {
									$addClassInOrder .= ' in_order';
									array_push($arrayInOrder_2, $arrProduct['productGUID']);
									break;
								}
							}
						}
						//находим соответствие в НЕматрице
						if ($_SESSION['session_userNoMatrix'] != ''){
							foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
								if ($arrProduct['productGUID'] == $row['guid']) {
									//echo '1 - '.$arrProduct['productGUID'].' -'.$row['guid'];
									$addClassInOrder = ' no_matrix';
									$productQuantity = $arrProduct['productQuantity'];
									if ($productQuantity != 0) {
										$addClassInOrder .= ' in_order';
										array_push($arrayInOrder_2, $arrProduct['productGUID']);
										break;
									}
								}
							}
						}
						

						if (!($index >= $startIndex && $index <= $endIndex)) {
							$addClassInOrder.= ' hide_on_page';
						} 
						$index++;

					$guid = "'".$row['guid']."'";

					echo '<div class="journal_row global'.$addClassInOrder.'" onclick="showHistory(this)">';
					echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
					echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
					//echo '<div id="product_pic"><img src="image.php?guid='.$row['guid'].'" /></div>';
					echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
					echo '<div id="product_name">' . $row['shortname'] . '</div>';
					echo '<div id="product_balance">' . '' . '</div>';
					echo '<div id="product_price">' . '' . '</div>';
					echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
					echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
					echo '<div id="product_measure">' . $row['measure'] . '</div>';
					echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
					//если товар по матрице будет, то неверно, что readonly
					if(strpos($addClassInOrder, 'matrix') != false){
						//матричный товар
						echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
					} else{
						//нематричный товар
						//особенность - всплывающее окно
						echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onclick="input_readonly()">';
					}
					echo '</form>';
					echo '<div id="product_history" style="display:none;"><div>' . '' . '</div></div>'; 
					echo '</div>';		
					}
				}

				//Дописать 2) и 3)

				/*
				$numrows = $index;
				$maxPage = ceil($numrows/$rowsOnPage);
				*/
				//echo ' 1 - '.$index;
				
			}
			else { //вся матрица
				//Выводить будем
				//1) товары по матрице - заказанные + не заказанные (всего 100)
				//2) товары по матрице - заказанные, которые не попали в этот список
				//3) товары не из матрицы закаказанные, которые не попали в этот список
		
				$arrayInOrder_2 = array(); //массив заказанных для избежания повторений

				//Позиции по всей номенклатуре по матрице(100) 
				$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE ORDER BY parentNomenclaturName, fullname LIMIT 10000";
				$mysqlQuery = mysqli_query($con, $mysqlQText);
				$mysqlErrNo = mysqli_errno($con);
				$mysqlError = mysqli_error($con);
				if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
				$numrows = mysqli_num_rows($mysqlQuery);

				$index = 0;
				$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
				$endIndex = $startIndex + $rowsOnPage - 1;

				if ($numrows > 0) {
					//echo $mysqlQText;
					while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
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
								array_push($arrayInOrder_2, $arrProduct['productGUID']);
							}
						}
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
					echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
					echo '<div id="product_measure">' . $row['measure'] . '</div>';
					echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
					echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="0" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
					echo '</form>';
					echo '<div id="product_history"><div>' . '' . '</div></div>';
					echo '</div>';
					}
				}
				//echo ' 1 - '.$index;		
			}

			//Общее для всего ассортимента и для матрицы

			//2) товары по матрице - заказанные, которые не попали в этот список
			//Заказанные товары по матрице
			$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE";
			$mysqlQuery = mysqli_query($con, $mysqlQText);
			$mysqlErrNo = mysqli_errno($con);
			$mysqlError = mysqli_error($con);
			if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;

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

					//Избежание повторений - не выводим, если находится совпадение в массиве
					foreach ($arrayInOrder_2 as $productInOrder){
						if ($row['guid'] == $productInOrder){
							$addClassInOrder.= ' no print';
						}
					}

					if ($addClassInOrder == ' in_order'){

						if (!($index >= $startIndex && $index <= $endIndex)) {
							$addClassInOrder.= ' hide_on_page';
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
						echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
						echo '<div id="product_measure">' . $row['measure'] . '</div>';
						echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
						echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
						echo '</form>';
						echo '<div id="product_history"><div>' . '' . '</div></div>';
						echo '</div>';
						$index++;
					}
				}
			}
			//echo ' 2 - '.$index;

			//3) Товары не из матрицы закаказанные, которые не попали в этот список
			if ($_SESSION['session_userNoMatrix'] != ''){
				foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {

					$addClassInOrder = '';

					//Избежание повторений - не выводим, если находится совпадение в массиве
					foreach ($arrayInOrder_2 as $productInOrder){
						if ($arrProduct['productGUID'] == $productInOrder){
							$addClassInOrder = 'no print';
						}
					}
					//если не встретился в массиве выведенных
					if ($addClassInOrder == ''){
						$addClassInOrder = ' no_matrix';
						$productGUID = $arrProduct['productGUID'];
						$productQuantity = $arrProduct['productQuantity'];
						if ($productQuantity != 0) {
							$addClassInOrder .= ' in_order';
							array_push($arrayInOrder_2, $arrProduct['productGUID']);
						}
						//echo '2 - '.$arrProduct['productGUID'].' -'.$productQuantity;
					}


					if ($addClassInOrder == ' no_matrix in_order'){ //по факту, все члены этого массива - с ненулевым ко-вом, поэтому можно оставить только no_matrix

						if (!($index >= $startIndex && $index <= $endIndex)) {
							$addClassInOrder.= ' hide_on_page';
						}

						//запрос на характеристики номенклатуры
						$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted = FALSE AND guid = '".$productGUID."';";
						$mysqlQuery = mysqli_query($con, $mysqlQText);
						$mysqlErrNo = mysqli_errno($con);
						$mysqlError = mysqli_error($con);
						if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
						$numrows2 = mysqli_num_rows($mysqlQuery);
						if ($numrows2 > 0) {
							//echo $mysqlQText;
							while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
								$productQuantity = $arrProduct['productQuantity'];
								echo '<div class="journal_row'.$addClassInOrder.'" onclick="showHistory(this)">';
								echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
								echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
								//echo '<div id="product_pic"><img src="image.php?guid='.$row['guid'].'" /></div>';
								echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
								echo '<div id="product_name">' . $row['shortname'] . '</div>';
								echo '<div id="product_balance">' . '' . '</div>';
								echo '<div id="product_price">' . '' . '</div>';
								echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
								echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
								echo '<div id="product_measure">' . $row['measure'] . '</div>';
								echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
								echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="0" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
								echo '</form>';
								echo '<div id="product_history"><div>' . '' . '</div></div>';
								echo '</div>';
								$index++;
							}
						}
					}	
				}
			}

			$numrows = $index;
			$maxPage = ceil($numrows/$rowsOnPage);
			//echo ' maxPage - '.$maxPage;
			
		}
		else { //если показывается категория по меню

		if (isset($_GET["showall"])){ //если в режиме всего ассортимента

		//Вывод
		//1)позиции по всей номенклатуре по выбранной группе
		//2)товары из матрицы - заказанные
		//3)товары не из матрицы - заказанные

		//Для вывода всех записей
		$numrowsAll = 0;
		$index = 0;
		$arrayInOrder_2 = array(); //Массив заказанных

		//Позиции по всей номенклатуре по выбранной группе
		$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted =FALSE AND parentguid = '".$_GET["parentGUID"]."' ORDER BY parentNomenclaturName, fullname";
		$mysqlQuery = mysqli_query($con, $mysqlQText);
		$mysqlErrNo = mysqli_errno($con);
		$mysqlError = mysqli_error($con);
		if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
		$numrows = mysqli_num_rows($mysqlQuery);

		
		$startIndex = ($_GET["page"] - 1) * $rowsOnPage;
		$endIndex = $startIndex + $rowsOnPage - 1;

		$numrowsAll = $numrowsAll + $numrows; 

		if ($numrows > 0) {
			//echo $mysqlQText;
			while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
				
			//все товары, которые не по матрице зануляем (чтобы они не выделялись цветом)
			$addClassInOrder = ' '; 
			$productQuantity = 0;
			if ($productQuantity == 0) {
				$productQuantity = '';
				$addClassInOrder = '';
			}

			//товары по матрице - выделяем цветом
			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct){
				if ($arrProduct['productGUID'] == $row['guid']) {
					$addClassInOrder = ' matrix';
					$productQuantity = $arrProduct['productQuantity'];
					if ($productQuantity != 0) {
						$addClassInOrder .= ' in_order';
						array_push($arrayInOrder_2, $arrProduct['productGUID']);
					}
				}
			}

			//находим соответствие в НЕматрице
			if ($_SESSION['session_userNoMatrix'] != ''){
				foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {
					if ($arrProduct['productGUID'] == $row['guid']) {
						$addClassInOrder = ' no_matrix';
						$productQuantity = $arrProduct['productQuantity'];
						if ($productQuantity != 0) {
							$addClassInOrder = ' in_order';
							array_push($arrayInOrder_2, $arrProduct['productGUID']);
							break;
						}
					}
				}
			}			

			if (!($index >= $startIndex && $index <= $endIndex)) {
				$addClassInOrder.= ' hide_on_page';
			}
			$index++; 

			echo '<div class="journal_row'.$addClassInOrder.'" onclick="showHistory(this)">';
			echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
			echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
			echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
			echo '<div id="product_name">' . $row['shortname'] . '</div>';
			echo '<div id="product_balance">' . '' . '</div>';
			echo '<div id="product_price">' . '' . '</div>';
			echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
			echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
			echo '<div id="product_measure">' . $row['measure'] . '</div>';
			echo '<form name="product_quantity" id="product_quantity" action="" method="post">';

			//если товар по матрице будет, то неверно, что readonly
			if(strpos($addClassInOrder, 'matrix') != false){
				//матричный товар
				echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
			} else{
				//нематричный товар
				//особенность - всплывающее окно
				echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onclick="input_readonly()" onchange="onChangeQuantity(this)">';
			}
			echo '</form>';
			echo '<div id="product_history" style="display:none;"><div>' . '' . '</div></div>'; 
			echo '</div>';		
			}
		}

		$numrowsInGroup = $numrowsAll;
		$maxPage = ceil($numrowsInGroup/$rowsOnPage); //показываем толкьо кол-во страниц выбранной группы

		/*
		echo ' numrows1 - '.$numrowsAll;
		echo ' $index1 - '.$index;
		*/

		//2) Дополнительно выводим товары по матрице (310) и из них выводитм заказанные
		$mysqlQText2 = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE ORDER BY parentNomenclaturName, fullname";
		$mysqlQuery2 = mysqli_query($con, $mysqlQText2);
		$mysqlErrNo2 = mysqli_errno($con);
		$mysqlError2 = mysqli_error($con);
		if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo2 . ', текст ошибки: ' . $mysqlError2 . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText2;
		$numrows2 = mysqli_num_rows($mysqlQuery2);

		ini_set('memory_limit', '-1');

		if ($numrows2 > 0) {
		//echo $mysqlQText;
		while ($row2 = mysqli_fetch_array($mysqlQuery2, MYSQLI_ASSOC)) {

			//если в сессии изменено кол-во
			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				if ($arrProduct['productGUID'] == $row2['guid']) {
					$productQuantity = $arrProduct['productQuantity'];
				}
			}

			$addClassInOrder = ' in_order';
			if ($productQuantity == 0) {
				$productQuantity = '';
				$addClassInOrder = '';
			}

			//Избежание повторений - не выводим, если находится совпадение в массиве
			foreach ($arrayInOrder_2 as $productInOrder){
				if ($row2['guid'] == $productInOrder){
					$addClassInOrder.= ' no print';
				}
			}

			if ($addClassInOrder == ' in_order'){
				if (!($index >= $startIndex && $index <= $endIndex)) {
					$addClassInOrder.= ' hide_on_page';
				} else{
					$addClassInOrder.= ' hidden_row';
				}

				$index++;
				$numrowsAll++;

			echo '<div class="journal_row class'.$addClassInOrder.'" onclick="showHistory(this)">';
			echo '<div id="guid" class="data">' . $row2['guid'] . '</div>';
			echo '<div id="parent_guid" class="data">' . $row2['parentguid'] . '</div>';
			echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row2['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row2['guid'].'" /></noscript></div>';
			echo '<div id="product_name">' . $row2['shortname'] . '</div>';
			echo '<div id="product_balance">' . '' . '</div>';
			echo '<div id="product_price">' . '' . '</div>';
			echo '<div id="product_exp_date">Срок годности: ' . $row2['expirationDate'] . ' дней</div>';
			echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
			echo '<div id="product_measure">' . $row2['measure'] . '</div>';
			echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
			echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
			echo '</form>';
			echo '<div id="product_history"><div>' . '' . '</div></div>';
			echo '</div>';
			}
		}
	}

	/*
	echo ' numrows2 - '.$numrowsAll;
	echo ' $index2 - '.$index;
	*/

	//3) Товары не из матрицы закаказанные, которые не попали в этот список
	if ($_SESSION['session_userNoMatrix'] != ''){
		foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {

			$addClassInOrder = '';

			//Избежание повторений - не выводим, если находится совпадение в массиве
			foreach ($arrayInOrder_2 as $productInOrder){
				if ($arrProduct['productGUID'] == $productInOrder){
					$addClassInOrder = 'no print';
				}
			}
			//если не встретился в массиве выведенных
			if ($addClassInOrder == ''){
				$addClassInOrder = ' no_matrix';
				$productGUID = $arrProduct['productGUID'];
				$productQuantity = $arrProduct['productQuantity'];
				if ($productQuantity != 0) {
					$addClassInOrder .= ' in_order';
					array_push($arrayInOrder_2, $arrProduct['productGUID']);
				}
				//echo '2 - '.$arrProduct['productGUID'].' -'.$productQuantity;
			}


			if ($addClassInOrder == ' no_matrix in_order'){ //по факту, все члены этого массива - с ненулевым ко-вом, поэтому можно оставить только no_matrix

				/*
				if (!($index >= $startIndex && $index <= $endIndex)) {
					$addClassInOrder.= ' hide_on_page';
				}
				$index++;
				*/

				if (!($index >= $startIndex && $index <= $endIndex)) {
					$addClassInOrder.= ' hide_on_page';
				} else{
					$addClassInOrder.= ' hidden_row';
				}
				$index++;

				//запрос на характеристики номенклатуры
				$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted = FALSE AND guid = '".$productGUID."';";
				$mysqlQuery = mysqli_query($con, $mysqlQText);
				$mysqlErrNo = mysqli_errno($con);
				$mysqlError = mysqli_error($con);
				if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
				$numrows2 = mysqli_num_rows($mysqlQuery);
				if ($numrows2 > 0) {
					//echo $mysqlQText;
					while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
						$productQuantity = $arrProduct['productQuantity'];
						echo '<div class="journal_row'.$addClassInOrder.'" onclick="showHistory(this)">';
						echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
						echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
						//echo '<div id="product_pic"><img src="image.php?guid='.$row['guid'].'" /></div>';
						echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
						echo '<div id="product_name">' . $row['shortname'] . '</div>';
						echo '<div id="product_balance">' . '' . '</div>';
						echo '<div id="product_price">' . '' . '</div>';
						echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
						echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
						echo '<div id="product_measure">' . $row['measure'] . '</div>';
						echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
						echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="0" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
						echo '</form>';
						echo '<div id="product_history"><div>' . '' . '</div></div>';
						echo '</div>';
						$numrowsAll++;
					}
				}
			}	
		}
	}

	//$maxPage = $numrowsAll;
	$numrows = $numrowsAll;

/*
	echo ' $numrows3 - '.$numrows;
	echo ' $index3 - '.$index;
	*/
	
	//если режим по матрице - показ категории
	} else {

	//Выводим:
	//1)все товары по матрице, показываем по категории, остальные скрываем
	//2)выводим заказанные товары не из матрицы
	//*нет проверки на выведенные

	// Выводим строки документа
	// сколько всего строк в журнале
	// Запрос данных для вывода строк таблицы товаров для заказа (для подсчёта общего количества строк)
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE ORDER BY parentNomenclaturName, fullname";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	$numrows = mysqli_num_rows($mysqlQuery);

	// Запрос только тех строк которые будут видны (чтобы предварительно знать их количество для вывода номеров страниц)
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted =FALSE AND parentguid = '".$_GET["parentGUID"]."' ORDER BY guid";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры для указанной группы, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
	$numrowsInGroup = mysqli_num_rows($mysqlQuery);
	$maxPage = ceil($numrowsInGroup/$rowsOnPage);

	// запрос строк для выводимой страницы
	//Дублирует первый запрос
	$mysqlQText = "SELECT * FROM nomenclatures WHERE guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE ORDER BY parentNomenclaturName, fullname";
	$mysqlQuery = mysqli_query($con, $mysqlQText);
	$mysqlErrNo = mysqli_errno($con);
	$mysqlError = mysqli_error($con);
	if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры для указанной страницы, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;

	//$numrows = mysqli_num_rows($mysqlQuery);

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
			echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
			echo '<div id="product_name">' . $row['shortname'] . '</div>';
			echo '<div id="product_balance">' . '' . '</div>';
			echo '<div id="product_price">' . '' . '</div>';
			echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
			echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
			echo '<div id="product_measure">' . $row['measure'] . '</div>';
			echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
			echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="20" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onChange="hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
			echo '</form>';
			echo '<div id="product_history"><div>' . '' . '</div></div>';
			echo '</div>';
		}
	}

/*
	echo ' $numrows1 - '.$numrows;
	echo ' $index1 - '.$index;
	*/
	

	//2) дополняем заказанными товарами не из матрицы
	if ($_SESSION['session_userNoMatrix'] != ''){
		foreach ($_SESSION['session_userNoMatrix'] as $key => $arrProduct) {

			$addClassInOrder = ' no_matrix';
			$productGUID = $arrProduct['productGUID'];
			$productQuantity = $arrProduct['productQuantity'];
			if ($productQuantity != 0) {
				$addClassInOrder .= ' in_order';
				//array_push($arrayInOrder_2, $arrProduct['productGUID']);
			}
			
			if ($addClassInOrder == ' no_matrix in_order'){ //по факту, все члены этого массива - с ненулевым ко-вом, поэтому можно оставить только no_matrix

/*
				if ($row['parentguid'] != $_GET["parentGUID"]) {
					$addClassInOrder.= ' hide_on_page';
				} else {
					if (!($index >= $startIndex && $index <= $endIndex)) {
						$addClassInOrder.= ' hide_on_page';
					}
					$index++;
				}
				*/

				//запрос на характеристики номенклатуры
				$mysqlQText = "SELECT * FROM nomenclatures WHERE isdeleted = FALSE AND guid = '".$productGUID."';";
				$mysqlQuery = mysqli_query($con, $mysqlQText);
				$mysqlErrNo = mysqli_errno($con);
				$mysqlError = mysqli_error($con);
				if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные матрицы номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
				$numrows2 = mysqli_num_rows($mysqlQuery);
				if ($numrows2 > 0) {
					//echo $mysqlQText;
					while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
						$productQuantity = $arrProduct['productQuantity'];

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
						echo '<div id="product_pic" onclick="openProductCard(this)"><img src="" data-src="image.php?guid='.$row['guid'].'" class="lazy"/><noscript><img src="image.php?guid='.$row['guid'].'" /></noscript></div>';
						echo '<div id="product_name">' . $row['shortname'] . '</div>';
						echo '<div id="product_balance">' . '' . '</div>';
						echo '<div id="product_price">' . '' . '</div>';
						echo '<div id="product_exp_date">Срок годности: ' . $row['expirationDate'] . ' дней</div>';
						echo '<div id="product_multiplicity" class="data">' . $row['multiplicity'] . '</div>';
						echo '<div id="product_measure">' . $row['measure'] . '</div>';
						echo '<form name="product_quantity" id="product_quantity" action="" method="post">';
						echo '<label for="journalname"></label><input name="journalname" type="text" placeholder="введите количество" min="0" max="'.'0'.'" class="input" id="journalname" size="0" value="'.$productQuantity.'" oninput="sincMatrix(this); hasOrder()" onkeydown="onlyNumber(this)" onchange="onChangeQuantity(this)">';
						echo '</form>';
						echo '<div id="product_history"><div>' . '' . '</div></div>';
						echo '</div>';
					}
				}
			}	
		}
	}

	/*
	echo ' $numrows2 - '.$numrows;
	echo ' $index2 - '.$index;
	*/

	} //END else

	}	
		
	mysqli_close($con);

/*
	echo '</br>';
	echo ' All numrows - '.$numrows;
	echo ' maxPage - '.$maxPage;
	*/

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

	// Выводим форму отправки заказа
	$action4form = 'sendorder.php';
	
	echo '<div class="sendformcont"><form id="sendorderformid" class="row" name="sendorderformname" action="'.$action4form.'" method="post">';
		echo '<div class="button animated" onclick="sendOrderTT()">Заказать</div>';
	echo '</form></div>';
	/*
	echo '<div id="overall_info" class="animated">Сумма заказа:<br>0.00</div>';
	*/
	echo "</div>";

?>

