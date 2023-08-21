<?php require_once("includes/connection.php"); ?>

<?php include("includes/header.php"); ?>

<?php
session_start();

$orderGUID = $_POST['orderGUID'];

$_SESSION['session_orderGUID'] = $orderGUID;


echo '<button name="all_goods" onclick="Show_matrix_goods2()" class="button button_goods_right">Весь ассортимент компании</button>';
		echo '<ul id="accordion2" class="accordion accordion2">';

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
			     echo '<div class="link link2">
			     <div id="product_pic">
			     <img src="image_group.php?guid='.$row['guid'].'" />
			     <noscript>
			     <img src="image_group.php?guid='.$row['guid'].'" />
			     </noscript>
			     </div>' . $row['fullname'] . '</div>';

					/*
					echo '<div class="product_group_row'.$addClassInOrder.'" onclick="setProductGroup(this)">';
					echo '<div id="guid" class="data">' . $row['guid'] . '</div>';
					echo '<div id="parent_guid" class="data">' . $row['parentguid'] . '</div>';
					echo '<div id="product_group_name">' . $row['fullname'] . '</div>';
					echo '</div>';
					*/

					$param = "'". $row['guid']."'";

					$mysqlQText2 = "SELECT * FROM nomenclaturegroups WHERE isdeleted = FALSE AND parentguid = $param ORDER BY fullname ";
					$mysqlQuery2 = mysqli_query($con, $mysqlQText2);
					$mysqlErrNo2 = mysqli_errno($con);
					$mysqlError2 = mysqli_error($con);
					if ($mysqlErrNo2!= 0) $message.= 'Не удалось получить данные групп номенклатуры, код ошибки: ' . $mysqlErrNo2 . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText2;
					$numrows2 = mysqli_num_rows($mysqlQuery2);

					echo '<ul class="submenu submenu2">';

					if ($numrows2 > 0) {
						while ($row2 = mysqli_fetch_array($mysqlQuery2, MYSQLI_ASSOC)) {


			       echo '<li class="elem"><div id="guid_all" class="data">'. $row2['guid'] . '</div><a href="?page=1&parentGUID='. $row2['guid'] . '&orderGUID='.$_SESSION['session_orderGUID'].'&menu=true&showall=true" >' . $row2['fullname'] . '</a>';
						echo '</li>';
						
						}
					}
					echo '</ul>';
					echo '</li>';	
					//скрипт ниже - убираем видимость верхнего уровня, если нет дочерних групп и позиций	
			}
		}

		echo '<li>';
    	echo '<div class="link">
    		 <div id="product_pic">
     		</div>
     		<a href="?page=1&parentGUID=none&orderGUID='.$_SESSION['session_orderGUID'].'&menu=true&showall=true">Весь ассортимент</a>
     		</div>';
		echo '</li>';
		//<img src="all.png" style="height:30px; width: 30px"/>


?>