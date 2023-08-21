		<?php //require_once("includes/connection.php"); ?>

<?php




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
?>
		