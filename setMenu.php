<?php require_once("includes/connection.php"); ?>

<?php include("includes/header.php"); ?>

<?php
session_start();

$orderGUID = $_POST['orderGUID'];

$_SESSION['session_orderGUID'] = $orderGUID;


echo '<button onclick="Show_matrix_goods()" class="button button_goods_left">Товары по вашей матрице</button>';
		echo '<ul id="accordion" class="accordion">';

		$mysqlQText = "SELECT * FROM nomenclaturegroups WHERE isdeleted = FALSE AND parentguid = '00000000-0000-0000-0000-000000000000' ORDER BY fullname ";
		$mysqlQuery = mysqli_query($con, $mysqlQText);
		$mysqlErrNo = mysqli_errno($con);
		$mysqlError = mysqli_error($con);
		if ($mysqlErrNo != 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
		$numrows = mysqli_num_rows($mysqlQuery);


		// Вывод строк групп номенклатуры
		if ($numrows > 0) {
			//echo $mysqlQText;
			while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {

				$addClassInOrder = '';

				echo '<li>';
			     echo '<div class="link">
			     <div id="product_pic">
			     <img src="image_group.php?guid='.$row['guid'].'" />
			     <noscript>
			     <img src="image_group.php?guid='.$row['guid'].'" />
			     </noscript>
			     </div>' . $row['fullname'] . '</div>';

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

							//Определение количества позиций в каждой группе номенклатуры
							$parentguid = "'". $row2['guid']."'";

							$arrProductsGUIDs = array();
							foreach ($_SESSION['session_userMatrix'] as $key => $value) {
								array_push($arrProductsGUIDs, $value['productGUID']);
							}

							$strProductsGUIDs = join("','", $arrProductsGUIDs);
							$strProductsGUIDs = '\'' . $strProductsGUIDs . '\'';

							$mysqlQText3 = "SELECT * FROM nomenclatures WHERE parentguid = $parentguid AND guid IN (".$strProductsGUIDs.") AND isdeleted = FALSE";		
							$mysqlQuery3 = mysqli_query($con, $mysqlQText3);
							$mysqlErrNo3 = mysqli_errno($con);
							$mysqlError3 = mysqli_error($con);
							if ($mysqlErrNo3!= 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo3 . ', текст ошибки: ' . $mysqlError3 . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText3;
							
							$numrows3 = mysqli_num_rows($mysqlQuery3);

							//если нет позиций - не выводим категорию второго уровня
							if ($numrows3 > 0){
									echo '<li class="elem"><div id="guid" class="data">'. $row2['guid'] . '</div><a href="?page=1&parentGUID='. $row2['guid'] . '&orderGUID='.$_SESSION['session_orderGUID'].'&menu=true" >' . $row2['fullname']. '</a>';

									echo '</li>';

							}
						}
					}
					echo '</ul>';
					echo '</li>';	
					//script.js - убираем видимость верхнего уровня, если нет дочерних групп и позиций	
			}
		}
		echo '<li>';
    	echo '<div class="link">
    		 <div id="product_pic">
    		 		
     		</div>
     		<a href="?page=1&parentGUID=none&orderGUID='.$_SESSION['session_orderGUID'].'&menu=true">Вся матрица</a>
     		</div>';
		echo '</li>';
		//<img src="allMatrix.png" style="height:30px; width: 30px

		/*include("./menu.js");*/
?>
