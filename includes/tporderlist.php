<?php require_once("includes/connection.php"); ?>
<?php
//родной 


	// Переменные документа:
	// Заголовок
	$journalName = 'Список заказов торговых точек:';
	// Количество колонок
	$numberOfCols = 6;
	// Строк на странице
	//$rowsOnPage = 40;
	// Названия колонок
	$columnsNames = 'Контрагент&&&Торговая точка&&&[contract_GUID]&&&[order_GUID]&&&Заказ&&&Сумма';
	// Инициализируем переменную сообщения
	//$message = '';
	
	// Если страница не задана, будем выводить первую
	//if (!isset($_GET["page"])) $_GET["page"] = 1;

	// Вывод документа
	
	// Заголовок и кнопка "Закрыть"
	echo '<div id="orders_list">';
	/*echo '<h2>'.$journalName.'</h2>';*/
	echo '<form name="closesessionbtnform" id="closesessionbtnform" action="logout.php" method="post" class="row">';
	echo '<input name="closesessionbtn" type= "submit" value="×">';
	echo '</form>';

	// Выводим название колонок
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

	// Составляем и отправляем запрос списка заказов (getOrders)
	// Инициализация, установка заголовков запроса и других параметров запроса
	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/getOrders');
	curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	
	// Массив передаваемых сервису параметров
	$arrayBodyRequest = [
		"accountGUID" => $_SESSION['session_accountGUID']
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
	
	curl_close($curl);

	$arrayBodyResponse = json_decode($textBodyResponse, true);
	// Если какой-либо ответ получен
	if (gettype($arrayBodyResponse) == 'array') {
		
		// Отладка
		/*foreach ($arrayBodyResponse as $key1 => $value1) {
			$resultTxt.= 'Ключ_1: '.$key1.' Значение_1: '.$value1.PHP_EOL;
			if (gettype($value1) == 'array') {  // Это массив контрактов с числовым индексом
				foreach ($value1 as $key2 => $value2) {
					$resultTxt.= 'Ключ_2: '.$key2.' Значение_2: '.$value2.PHP_EOL;
					if (gettype($value2) == 'array') { // Это массив с элементами: ГУИД контракта и массив заказов по этому контракту
						foreach ($value2 as $key3 => $value3) {
							$resultTxt.= 'Ключ_3: '.$key3.' Значение_3: '.$value3.PHP_EOL;
							if (gettype($value3) == 'array') { // Это массив заказов с числовым индексом
								foreach ($value3 as $key4 => $value4) {
									$resultTxt.= 'Ключ_4: '.$key4.' Значение_4: '.$value4.PHP_EOL;
									if (gettype($value4) == 'array') { // Это массив с элементами: ГУИД заказа и полное наименование заказа
										foreach ($value4 as $key5 => $value5) {
											$resultTxt.= 'Ключ_5: '.$key5.' Значение_5: '.$value5.PHP_EOL;
										}
									}
								}
							} 
						}
					}
				}
			}
		}
		echo $resultTxt;*/
		

		$arrOfContracts = $arrayBodyResponse['arrayRetailOutlet'];
/*

$arrOfContracts2 = [];

foreach ($arrOfContracts as $index1 => $arrOfContract) {
	$arrOfContracts2[$index1] = $arrOfContract;
};

foreach ($arrOfContracts2 as $index2 => $arrOfContract2) {
			$counterpartyName = $arrOfContract2['counterpartyName'];
			$contractFullName = $arrOfContract2['contractFullName'];
			$contractGUID = $arrOfContract2['contractGUID'];
			$overallSum = $arrOfContract2['overallSum'];

			$strEcho = '';
			$numApproved = 0;
			$arrOfOrders = $arrOfContract2['arrayOfOrder'];
			foreach ($arrOfOrders as $index3 => $arrOfOrder) {
				$addClassInOrder = '';
				$orderGUID = $arrOfOrder['orderGUID'];
				$orderFullName = $arrOfOrder['orderFullName'];
				$orderSum = $arrOfOrder['orderSum'];
				$orderApproved = $arrOfOrder['orderApproved'];
				if ($orderApproved) {
					$addClassInOrder = ' order_approved';
					$numApproved++; //количество подтвержденных
				}

			}
			$numNotApproved = count($arrOfOrders) - $numApproved; //общее количество - подтвержденные = неподтвержденные
			

			$arrOfContract2['numNotApproved'] = $numNotApproved;
			$arrOfContract2 += ['numNotApproved'=>$numNotApproved];
			 //array_push($arrOfContract2, $numNotApproved);

			$keys = array_keys($arrOfContract2);
			foreach ($keys as $key){
			    echo 'key - '.$key;
			    echo '<br>';
			}
			echo '<br>';

	}


	foreach ($arrOfContracts2 as $index2 => $arrOfContract2) {
			$keys = array_keys($arrOfContract2);
			foreach ($keys as $key){
			    echo 'key - '.$key;
			    echo '<br>';
			}
			echo '<br>';
	}

	*/

function array_sort_by_column(&$arr, $col, $dir = SORT_DESC) {
    $sort_col = array();
    foreach ($arr as $key=> $row) {
        $sort_col[$key] = $row[$col];
    }

    array_multisort($sort_col, $dir, $arr);
}

array_sort_by_column($arrOfContracts, 'sumNoApproved');


$arrContract = array();
	
	foreach ($arrOfContracts as $index1 => $arrOfContract) {

		//создаем массив с элементами долга по каждой тт
		$arrContractDebt = array();

		$counterpartyName = $arrOfContract['counterpartyName'];
		$contractFullName = $arrOfContract['contractFullName'];
		$contractGUID = $arrOfContract['contractGUID'];
		$overallSum = $arrOfContract['overallSum'];


		array_push($arrContractDebt, $arrOfContract['AmountOfDebt']);
		array_push($arrContractDebt, $arrOfContract['OverdueDebt']);


		//Признак черного списка для всех тт - одинаковый
		$_SESSION['blacklist'] = $arrOfContract['blacklist'];

		
		//массив документов для таблицы
		$arrayDocDebts = array();
		if (gettype($arrOfContract['ArrayOfDebts']) == 'array') {
			$elem = $arrOfContract['ArrayOfDebts'];	
			foreach ($elem as $arr) {
				$arrayDocDebts_ = array();
				array_push($arrayDocDebts_, $arr['NumberOrder']);
				array_push($arrayDocDebts_, $arr['DateOrder']);
				array_push($arrayDocDebts_, $arr['sum']);
				array_push($arrayDocDebts_, $arr['AmountOfDebt']);
				array_push($arrayDocDebts_, $arr['DaysOverdue']);
				array_push($arrayDocDebts, $arrayDocDebts_);			
			}
		}

		array_push($arrContractDebt, $arrayDocDebts);

		$arrContract[$contractGUID] = $arrContractDebt;

		/*
			$keys = array_keys($arrOfContract);
			foreach ($keys as $key){
			    echo 'key - '.$key;
			    echo '<br>';
			}
			echo '<br>';

			echo 'AmountOfDebt - '.$arrOfContract['AmountOfDebt'];
			echo '<br>';

			echo 'OverdueDebt - '.$arrOfContract['OverdueDebt'];
			echo '<br>';


			if (gettype($arrOfContract['ArrayOfDebts']) == 'array') {
				$elem = $arrOfContract['ArrayOfDebts'];	
				foreach ($elem as $arr) {
					echo '<br>';
					echo $arr['NumberOrder'];
					echo '<br>';
					echo $arr['DateOrder'];
					echo '<br>';
					echo $arr['sum'];
					echo '<br>';
					echo $arr['AmountOfDebt'];
					echo '<br>';
					echo $arr['DaysOverdue'];
					echo '<br>';			
				}
			}
			*/

		
			
			
			
			// Сначала выводим строки закзов по этой торговой точке в буфер и заодно подсчитываем количество подтверждённых и количество не подтверждённых
			$strEcho = '';
			$numApproved = 0;
			$arrOfOrders = $arrOfContract['arrayOfOrder'];

			foreach ($arrOfOrders as $index2 => $arrOfOrder) {
				$addClassInOrder = '';
				$orderGUID = $arrOfOrder['orderGUID'];
				$orderFullName = $arrOfOrder['orderFullName'];
				$orderSum = $arrOfOrder['orderSum'];
				$orderApproved = $arrOfOrder['orderApproved'];
				//здесь будет комментарий
				if ($orderApproved) {
					$addClassInOrder = ' order_approved';
					$numApproved++; //количество подтвержденных
				}

				//$strEcho.= '<div class="list_row panel hidden_row'.$addClassInOrder.'" onclick="openOrder(this)">';
				$strEcho.= '<div class="list_row hidden_row'.$addClassInOrder.'" >';
				$strEcho.= '<div class="list_row_2" onclick="openOrder(this)">';
				$strEcho.= '<div id="contract_GUID" class="data">'.$contractGUID.'</div>';
				$strEcho.= '<div id="order_GUID" class="data">'.$orderGUID.'</div>';
				$strEcho.= '<div id="orderFullName">'.$orderFullName.'</div>';

				
				$strEcho.= '<div id="orderSum">'.$orderSum.'</div>';
				$strEcho.= '<div class="order_ok_container"><a>0</a>';
				$strEcho.= '<div class="order_ok_pic"><i class="fas fa-check"></i></div>';
				$strEcho.= '</div>';
				$strEcho.= '</div>';
				//$strEcho.= '<div class="second_conatainer">';
				// Выводим форму отправки заказа
				$action4form = 'sendorder.php';
				
				$strEcho.=  '<div id="button_approved" class="sendformcont"><form name="sendorderformname" action="'.$action4form.'" method="post" id="sendorderformid" class="row">';
					$strEcho.=  '<div id="button_approved_2" class="button" onclick="sendOrderTP2(\''.$orderGUID.'\')">Подтвердить заказ</div>';
					//onclick="sendOrderTP(\''.$orderGUID.'\')"
					//echo '<div class="button" onclick="addToOrderTT()">Добавить в заказ</div>';
				$strEcho.=  '</form></div>';

				$strEcho.= '</div>';

			}
			
			$numNotApproved = count($arrOfOrders) - $numApproved; //общее количество - подтвержденные = неподтвержденные
			$arrOfContract += ['numNotApproved'=>$numNotApproved];
			//echo $arrOfContract['numNotApproved'];

			if ($numNotApproved > 0){
					echo'<div class="panel panel_approved" onclick="toggleOrderList(this)">';
			}else{
					echo'<div class="panel" onclick="toggleOrderList(this)">';
			}
			
			echo '<div class="data">'.$numNotApproved.'</div>';
			echo'<div>'.$counterpartyName.'</div>
					<div>'.$contractFullName.'</div>
					<div id="contract_GUID" class="data">'.$contractGUID.'</div>
					<div class="panel_two">
							<div id="order_GUID" class="data"></div>
							<div>'.$numApproved.'/'.$numNotApproved.'</div>
							<div>'.$overallSum.'</div>
					</div>';
			echo '</div>';

			
			/*// Выводим суммарную строку торговой точки
			echo '<div class="list_row" onclick="toggleOrderList(this)">';
			echo '<div>'.$counterpartyName.'</div>';
			echo '<div>'.$contractFullName.'</div>';
			echo '<div id="contract_GUID" class="data">'.$contractGUID.'</div>';
			echo '<div id="order_GUID" class="data"></div>';
			echo '<div>'.$numApproved.'/'.$numNotApproved.'</div>';
			echo '<div>'.$overallSum.'</div>';
			echo '</div>';
			*/

			// Выводим строки с заказами торговой точки, которая ранее была подготовлена
			echo $strEcho;

		}

		$_SESSION['arrContract'] = $arrContract; //передаем данные о задолженности всех торговых точек
	} else {
		$message.= 'Ошибка при получении списка заказов из 1С:<br>' . $textBodyResponse ;
	}
	echo "</div>";
	if ($message) {
		echo '<div id="error">'.$message.'</div>';
	}
?>