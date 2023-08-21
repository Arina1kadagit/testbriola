<?php
	session_start();
	header('X-Accel-Buffering: no');
	header('Content-type: text/html; charset=utf-8');
	if (!isset($_SESSION['session_username']) || $_SESSION['session_userRole'] != 'Shop') : 
		header("Location:login.php");
	else :
?>

<?php require_once("includes/connection.php"); ?>

<?php include("includes/header.php"); ?>

<head>
	  <link rel='stylesheet' href='https://netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css'>
	  <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.min.css'> 
</head>
<body id="body" onload="complete(); hasOrder(); lazyLoad()">


<header class="header">
	<div class="header_burger">
		<span></span>
	</div>
<div id="header_tt" class="header_menu">
	<div class="logo">
		<img src="favicon.png" alt="">
	</div>
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
									echo '<li class="elem"><a href="?page1?page=1&parentGUID='. $row2['guid'] . '" >' . $row2['fullname'] . '</a>';
									echo '</li>';
							}
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
     		<a href="?page=1&parentGUID=none">Вся матрица</a>
     		</div>';
		echo '</li>';

		//<img src="allMatrix.png" style="height:30px; width: 30px"/>

			  
	echo '</div>';
?>



<!-- 547-554 -->
<!-- form: action="logout.php" -->
	<div id="sum_order" class="sum_order"> <!--scr.js 40-41-->
		<form name="hiderowsbtnform" method="post" id="hiderowsbtnform" class="row" onsubmit="hideRows(this)">
		<button name="hiderowsbtn" class="hiderowsbtn2" type="submit" value="-">
			<div id="sum_sum">Сумма заказа: 0.00</div>
		</button>
		</form>
		
	</div>

<?php
			
		echo '<div class="history_orders">';
		echo '<button name="history_orders" onclick="history_orders()" class="button">История заказов</button>';
		echo '</div>';

		//////////////
		// Если торговая точка выбрана, то сохраняем её договор в сессии и очищаем корзину сессии					
		if (isset($_POST['select_shop'])) {

			// После смены торговой точки устанавливаем договор
			$_SESSION['session_contractGUID'] = $_POST['select_shop'];

			$mysqlQText = "SELECT * FROM contracts WHERE guid IN ('".$_SESSION['session_contractGUID']."')";
			$mysqlQuery = mysqli_query($con, $mysqlQText);
			$mysqlErrNo = mysqli_errno($con);
			$mysqlError = mysqli_error($con);
			if ($mysqlErrNo != 0) $message.= 'Не удалось получить список торговых точек, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
			while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
				$_SESSION['session_seeOnlyYourMatrix'] = $row['seeonlyyourmatrix'];
			}

			// После смены торговой точки очищаем корзину
			$arrNewUserMatrix = array();

			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				$arrNewProduct = array();
				$arrNewProduct['productGUID'] = $arrProduct['productGUID'];
				$arrNewProduct['productQuantity'] = '';
				array_push($arrNewUserMatrix, $arrNewProduct);
			}
			$_SESSION['session_userMatrix'] = $arrNewUserMatrix;
			$_SESSION['session_userNoMatrix']='';

			foreach ($_SESSION['session_userMatrix'] as $key => $arrProduct) {
				
				if ($arrProduct['productGUID'] == '2784e0d0-7b10-11eb-85b6-b42e99611d38') {
					echo $arrProduct['productGUID'];
					echo '</br>';
					echo 'hello!';
				}				
			}

			$_POST = array();
			//header("Location:tt.php");
		}
		
		if ($_SESSION['session_seeOnlyYourMatrix'] != 1) {
		echo '<div class="all_goods">';
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

			       echo '<li class="elem"><a href="?page1?page=1&parentGUID='. $row2['guid'] . '&showall=true">' . $row2['fullname'] . '</a>';

			       	/*
							echo '<div class="product_group_row'.$addClassInOrder.'" onclick="setProductGroup(this)">';
							echo '<div id="guid" class="data">' . $row2['guid'] . '</div>';
							echo '<div id="parent_guid" class="data">' . $row2['parentguid'] . '</div>';
							echo '<div id="product_group_name">' . $row2['fullname'] . '</div>';
							echo '</div>';*/
							echo '</li>';
							
						}
					}
					echo '</ul>';
					echo '</li>';		
			}
		}

	echo '<li>';
    	echo '<div class="link">
    		 <div id="product_pic">
    		 		
     		</div>
     		<a href="?page=1&parentGUID=none&showall=true">Весь ассортимент</a>
     		</div>';
		echo '</li>';

		//<img src="allMatrix.png" style="height:30px; width: 30px"/>

 
	echo '</div>';
	}
?>

	<div class="combo">
		<input id="inputName" type="text" id="state" size="20" placeholder="Введите наименование" oninput="load_options(this)" >
		<img src="search.png" alt="" onclick="closeMenu()">
	</div>


	<div id="goodbye" class="mobile_goodbye">
			<p><a href="logout.php">Выйти</a></p>
		
	</div>
	</div>
	<div id="graphic" class="grapfic_mobile grapfic_in_burger">
	</div>		
</header> 

<div class='window_readonly'>
	<p>
	Вы добавили товар, которого
	<br>
	<b>нет</b>
	в вашей матрице.
	</p>
	<button onclick="close_window_readonly()">Закрыть</button>
</div>

<div class="container container_tt"> 
	<div id="welcome">
 		<h2>Добро пожаловать, <span class="span_welcome"><?php echo $_SESSION['session_userDisplayName'];?>!</span></h2>
		<form style="margin-top: -10px;" action="" method="post">
			<p><select class="form_select" size="1" name="select_shop" onchange="this.form.submit()">
			<?php
                
				//echo 'hello!';
				// Если договор не установлен, устанавливае выбранное поле по умолчанию "Выберите торговую точку"
				if (!isset($_SESSION['session_contractGUID'])) {
					echo '<option selected="selected" disabled="disabled">Выберите торговую точку</option>';
				}


				// Получаем список торговых точек контрагента и заполняем ими список выбора
				$mysqlQText = "SELECT * FROM contracts WHERE counterpartyguid IN ('".$_SESSION['session_userGUID']."') AND isdeleted = FALSE";
				$mysqlQuery = mysqli_query($con, $mysqlQText);
				$mysqlErrNo = mysqli_errno($con);
				$mysqlError = mysqli_error($con);
				if ($mysqlErrNo != 0) $message.= 'Не удалось получить список торговых точек, код ошибки: ' . $mysqlErrNo . ', текст ошибки: ' . $mysqlError . PHP_EOL . 'Тект запроса: ' . PHP_EOL . $mysqlQText;
				while ($row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC)) {
					// Если договор установлен в сессии, то выбранное поле по умолчанию это этот договор, для остальных - просто добавляем в список
					if ($_SESSION['session_contractGUID'] === $row['guid']) {
						echo '<option selected="selected" value="'.$row['guid'].'">'.$row['fullname'].'</option>';
					} else {
						echo '<option value="'.$row['guid'].'">'.$row['fullname'].'</option>';
					}
				}
			?>
			</select></p>
		</form>
		<div id="contract_GUID" class="data"><?php echo $_SESSION['session_contractGUID'];?></div>
		<div id="user_GUID" class="data"><?php echo $_SESSION['session_userGUID'];?></div>
		<div id="account_GUID" class="data"><?php echo $_SESSION['session_accountGUID'];?></div>
		<div id="contact_seller" class="data"></div>
		<?php
			// Если страница не задана, будем выводить первую
			if (!isset($_GET["page"])) $_GET["page"] = 1;

			/*
			// Если родитель не задан, то родителя нет
			if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '00000000-0000-0000-0000-000000000000';
			*/

			// Если родитель не задан, то родителя нет
			//просмотр всей матрицы
			if (!isset($_GET["parentGUID"])) {
			$_GET["parentGUID"] = 'none'; 
				//меняем адрес для понятности где мы
				echo '<script type="text/javascript">
			           window.location = "tt.php?page=1&parentGUID=none"
				      </script>';
				      
		 	 }
		?>
		<div id="current_parent_GUID" class="data"><?php echo $_GET["parentGUID"];?></div>
	</div>

		<?php

		//////////////////// сумма долга
		if (isset($_SESSION['session_contractGUID'])) {

				echo '<div class="contragent_dolg_container" id="contragent_dolg_container_tp" >';
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

		$arrOfContract = $_SESSION['session_ArrayOfDebts'];


		if (gettype($arrOfContract) == 'array') {
				foreach ($arrOfContract as $arr) {   //0-6
									
					if (gettype($arr) == 'array'){        		
						echo '<tr class="dolg_history_row">';
							
						$keys2 = array_keys($arr);
					    foreach ($keys2 as $key2){
					    	
		    				if ($key2 != 'ContractGUID'){
		    					echo '<td>'.$arr[$key2].'</td>';	
		    				}	
		    				
		    				//echo '<td>'.$arr[$key2].'</td>';
					    }

						echo '<td hidden class="dolg_history_row_contactGUID">'.$arr['ContractGUID'].'</td>';
								echo '</tr>';	
					}	

				}
			}

			echo '</table></div></div>';

		}

				/////////////////////// конец сумма долга
			

		//хлебные крошки

			echo '<div class="breadcrumbs">';

			if ($_GET["parentGUID"] == 'none'){
				if (isset($_GET["showall"])){
					$menu = 'Ассортимент компании >';
					$category_name = 'Весь ассортимент';
				}
				else{
					$menu = 'Товары по вашей матрице >';
					$category_name = 'Вся матрица';
				}

				echo '<div>' . $menu . '&nbsp </div>';
				echo '<div id="second_of_breadcrumbs"></div>';
				echo '<div>'.$category_name.'</div>';

				echo '</div>';

				echo '<div class="category_name">'.$category_name.'</div>';

			} else{
				$guid = $_GET["parentGUID"];
				$menu = 'Товары по вашей матрице >';

				if (isset($_GET["showall"])){
					$guid = trim($guid, '&showall=true'); //удаляет из строки
					$menu = 'Ассортимент компании >';
				}

				$guid = $_GET["parentGUID"];
				//echo $guid;

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
				echo '<div class="category_name">' . $category_name. '</div>';
			}
		}

		//Конец хлебных крошек	

		?>

	<div id="veil"><div id="message_window">
		<div></div>
		<div><form id="sendorderformid" class="row" name="sendorderformname" action="" method="post">
			<input class="button" name="sendorderbutton" type="submit" value="ОК">
			<!--<div class="button" onclick="sendOrderTT()">Оформить заказ</div>-->
		</form></div>
 	</div></div>
 	<div id="window"></div>
 	<div id="float_window"></div>

<?php include("includes/ttorder.php"); ?>

	<div id="goodbye">
		<p><a href="logout.php">Выйти</a></p>
	</div>
</div>


<!-- Test -->
<script>
function Show_matrix_goods() {
    document.getElementById("accordion").classList.toggle("show");
    //скрыть картинку в меню снизу
    if ($('#graphic').hasClass('active') ) { 
			$('#graphic').toggleClass('hide');
			}
}
function Show_matrix_goods2() {
    document.getElementById("accordion2").classList.toggle("show");
    //скрыть картинку в меню снизу
    if ($('#graphic').hasClass('active') ) { 
			$('#graphic').toggleClass('hide');
			}
}


// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.button_goods_left')) {

    var dropdowns = document.getElementById("accordion");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
    }
  }
    if (!event.target.matches('.button_goods_right')) {

    var dropdowns = document.getElementById("accordion2");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
    }
  }
}

//мобильное меню закрывается при окончании ввода в строке поиска
var inputName = document.getElementById("inputName");
//alert(inputName);
inputName.addEventListener("keyup", function(event) {
  if (event.keyCode === 13) {
   //event.preventDefault();
    //document.querySelector('.header_burger').click();
  }
});

var input = '';

//Закрыть мобильное меню по клике на крестик 
function closeMenu(){
	document.querySelector('.header_burger').click();
	document.getElementById("name").disabled = false;
}


//функция скррытия категорий первого уровня, если нет подкатегорий
document.addEventListener('DOMContentLoaded', function(){ 
	var submenu = document.querySelectorAll('.submenu');
  for (i=0; i<submenu.length; i++){
  	if (submenu[i].childNodes.length > 0){
	   //console.log('yeah!');
		}else{
			//console.log('no!');
			submenu[i].parentElement.style.display = 'none';
		}
  }	

  //////

  window.addEventListener('click', e => { // при клике в любом месте окна браузера
    const target = e.target // находим элемент, на котором был клик
    console.log(target.tagName);
    if (target.tagName == 'INPUT'){
    	input = target;
    }
    if (target.tagName == 'BUTTON'){
    	console.log('value - '+input.value);
    	if (input != ''){
    		if (input.value != ''){
    			console.log('no!');
    		} else{
    			input.focus();
    			console.log('yes!');
    		}
    	}
    }
    /*
    if (!target.closest('.nav') && !target.closest('.header__button')) { // если этот элемент или его родительские элементы не окно навигации и не кнопка
      nav.classList.remove('nav_active') // то закрываем окно навигации, удаляя активный класс
    }
    */
  })

});

//скрытие бургера при списке заказов tporderlist
window.addEventListener('load', function(){
	var burger = document.querySelector('.header_burger');
	var orders_list = document.querySelector('#orders_list');
	if (orders_list != null){
	if (orders_list.style.display != 'none'){
		burger.style.display = 'none';
	};
	}
});

//поиск
function load_options(elem){
	input = document.getElementById("inputName");
	value =  input.value;

	history_list_orders = document.querySelector("#history_list_orders");
	if (history_list_orders != null){
		console.log('Особый поиск!');
		if (value != ''){
			nameNom = document.querySelectorAll("#nameNom");

			arrOfChoice = new Array();

			for (var i = 1; i <= nameNom.length; i++) {
				elem = nameNom[i-1];
				parentElem = elem.parentNode; //list_row
				//вернуть на исходную
				parentElem.classList.remove("list_rows_choice");
				parentElem.classList.add("hidden_list_row");
				///

				str = elem.textContent.toLowerCase();
				value = value.toLowerCase();
				if (str.includes(value)) {
					console.log(value+' - '+str);
					parentElem.classList.add("list_rows_choice");
					console.log(parentElem);
					/*
					if(!parentElem.classList.contains("hidden_list_row")){
						parentElem.classList.remove("hidden_list_row");
						console.log('1!');
					}
					*/
					hide_list_orders(parentElem);

					parentElem_number = parentElem.querySelector('#numberOrder').textContent;
					arrOfChoice.push(parentElem_number);//масив номеров заказов, которые соответсвуют поиску
				}
			}

			//открываем все товары с тем же номеров заказа
			listRows = document.querySelectorAll(".list_row");
			//console.log('listRows.length - '+listRows.length);
			for (var j = 1; j <= listRows.length; j++) {
				row = listRows[j-1];
				row_number = '';
				if (row != null){
					//console.log(row);
					row_number = row.querySelector('#numberOrder').textContent;
				}
									
				for (var i = 1; i <= arrOfChoice.length; i++) {
					elemOfArr = arrOfChoice[i-1];
					//console.log(elemOfArr);
					if(elemOfArr == row_number){
						row.classList.remove("hidden_list_row");
					} 
				}
			}


		} else{
			//location.href = location.toString();
			//снять выбор
			listRows = document.querySelectorAll(".list_row");
			for (var i = 1; i <= listRows.length; i++) {
				elem = listRows[i-1];
				elem.classList.remove("list_rows_choice");
				elem.classList.add("hidden_list_row");
			}
		} 
		document.getElementsByTagName('html')[0].style.overflow = 'scroll';
	} else{

		contractGUID = document.querySelector('#contract_GUID').value;

		console.log('Поиск по товарам');
			if (value != ''){
			$.ajax({
	    	type : 'POST',
	        url: "ajax2.php?tt.php",
	        data: {name: value},
	        success: function(data){
	        	setPage(1);
	        	strLoc = location.toString();
	        	i = strLoc.indexOf('&search');
						if (i<0) { strLoc = strLoc + '&search=true';};
						history.pushState(null, null, strLoc);
						document.getElementById("order_table").innerHTML = '';
	        	document.getElementById("order_table").innerHTML = data;
	        	setLazy();
	        	lazyLoad();
	        	complete();
	        	hasOrder();
	        	//hideRowsTableDebt(contractGUID);
	        	sum_ajax2_tt();
		    },
		    timeout: 15000
	    })
		} else{
			location.href = location.toString();
		} 
	}


}

//вспывающее сообщение 
function input_readonly(){
	//document.querySelector('.window_readonly').classList.add('b-show');
}

//закрыть всплывающее сообщение
function close_window_readonly(){
	//document.querySelector('.window_readonly').style.display = 'none';
	document.querySelector('.window_readonly').classList.remove('b-show');
}


</script> 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mobile-detect/1.4.4/mobile-detect.min.js"></script>
  <script  src="js/script.js"></script>
  <script src="https://kit.fontawesome.com/cd9a557ac3.js" crossorigin="anonymous"></script>

</body>

<?php include("includes/footer2.php"); ?>

<?php endif; ?>