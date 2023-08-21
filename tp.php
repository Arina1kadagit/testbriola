<?php
	session_start();
	header('X-Accel-Buffering: no');
	header('Content-type: text/html; charset=utf-8');
	if (!isset($_SESSION['session_username']) || $_SESSION['session_userRole'] != 'Seller'): 
		header("Location:login.php");
	else:

	// Блокировка повторной отправки формы, если форма уже отправлена, то редиректим сюда же без данных POST
	if (isset($_POST["sendorderbutton"])) {
		header("Location:tp.php");
	}
?>

<?php require_once("includes/connection.php"); ?>

<?php include("includes/header.php"); ?>

<?php
	$message = '';
?>

<!--<body onload="complete()">-->
<body onload="complete()">
<div id="header_container_tp" class="header_container">
	<div class="header_burger">
		<span></span>
	</div>

<?php
 
 //показывается или скрывается меню - при перезагрузке
 if (isset($_GET['orderGUID'])){
	echo '<div id="header_tp" class="header header_menu" style="display: grid">';
} else{
	echo '<div id="header_tp" class="header header_menu" style="display: none">';
}

?>

		<div class="logo">
		<img src="favicon.png" alt="">
	</div>
	<?php 

		//МЕНЮ

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
		//<img src="allMatrix.png" style="height:30px; width: 30px"/>

			  
	echo '</div>';
?>
<!--<div class="logo">
		<img src="favicon.png" alt="">
	</div>-->

<div id="sum_order" class="sum_order"> <!--scr.js 40-41-->
	<form name="hiderowsbtnform" method="post" id="hiderowsbtnform" class="row" onsubmit="hideRows(this)">
	<button name="hiderowsbtn" class="hiderowsbtn2" type="submit" value="-">
		<div id="sum_sum">Сумма заказа: 0.00</div>
	</button>
	</form>
	
	</div>

<?php
			// Запрос строк групп номенклатуры

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

						/*			
			       echo '<li class="elem"><a href="?page1?page=1&parentGUID='. $row2['guid'] . '&showall=true" onclick="setProductGroup(this)">' . $row2['fullname'] . '</a>';
			       */

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

	echo '</div>';
?>

<div class="combo">
		<input id="inputName" type="text" id="state" size="20" placeholder="Введите наименование" oninput="load_options(this)">
		<img src="search.png" alt="" onclick="closeMenu()">
</div>


<div id="goodbye" class="mobile_goodbye">
			<p><a href="logout.php">Выйти</a></p>
</div>


</div> <!--id="header_tp" class="header header_menu-->

	<div id="welcome" class="welcome_tp">
		<?php
			$name = $_SESSION['session_userDisplayName'];
	    $name2 = preg_replace('/[0-9]/', '', $name);
	    $name = preg_replace('/[-]/', '', $name2);
		?>

 		<h2>Добро пожаловать, <span class="span_welcome"><?php echo $name;?>!</span>

 		</h2>
 		<div id="contract_GUID2" class="data"></div>
 		<div id="user_GUID" class="data"><?php echo $_SESSION['session_userGUID'];?></div>
 		<div id="account_GUID" class="data"><?php echo $_SESSION['session_accountGUID'];?></div>
 	</div>
	<header class="header_tp">
		<div class="labelListOrder" onclick="returnToListOrders()">Список заказов</div>
	</header>

	<div id="graphic" class="grapfic_mobile grapfic_in_burger">
	</div>	


</div> <!--header_container-->

<div class="container container_tp"> 
<?php

 	echo '<div id="veil"><div id="message_window">
		<div></div>
		<div><form id="sendorderformid" class="row" name="sendorderformname" action="" method="post">
			<input class="button" name="sendorderbutton" type="submit" value="ОК">
			<!--<div class="button" onclick="sendOrderTT()">Оформить заказ</div>-->
		</form></div>
 	</div></div>
	<div id="window"></div>
	<div id="float_window"></div>';

	// Если страница не задана, будем выводить первую
		if (!isset($_GET["page"])) $_GET["page"] = 1;

		// Если родитель не задан, то родителя нет
		if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '';

		/*
		echo $_GET["parentGUID"];
		echo '</br>';

		$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		echo $url;

		echo '<div id="current_parent_GUID" class="data">';
		echo $_GET["parentGUID"];
		echo '</div>';
		*/

 if (isset($_GET['orderGUID'])){ //если клик в заказе
 	//echo ' orderGUID ';

 	/*
 	echo $_SESSION['session_orderGUID'];

 	echo '</br>';
 	$keys = array_keys($_SESSION);
	foreach ($keys as $key) {
		echo $key;
		echo '</br>';
	}

	foreach ($_SESSION['session_userMatrix'] as $key => $value) {
		echo $value['productGUID'];
		echo '</br>';

	}
	*/

 	if (isset($_GET['showAllMatrix'])){ //режим всего ассортимента
 		//старая задумка  - по ней ничего нет

		/*
 		$orderGUID = $_GET['orderGUID'];
 		//header('tporder.php?orderGUID='.$orderGUID.'&onlyBody=true');
 		header("Location:tporder.php?orderGUID='.$orderGUID.'&onlyBody=true");
 		*/

 		//echo ' showAllMatrix';

 		$orderGUID = $_GET['orderGUID'];
 	}
 	else{ 
 		//echo ' NoShowAllMatrix';

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
		if (!isset($_GET["parentGUID"])) $_GET["parentGUID"] = '00000000-0000-0000-0000-000000000000';

		echo '<div id="order_GUID" class="data">'. $_GET["orderGUID"]. '</div>';
		echo '<div id="current_parent_GUID" class="data">';
		echo $_GET["parentGUID"];
		echo '</div>';


		//$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		//echo $url;	
		//echo '<br/>';
		
		$orderGUID = $_GET["orderGUID"];
		$parentGUID = $_GET["parentGUID"];

		///////////////////////////
		//конец общей части

	 	if($_GET['parentGUID'] == 'none'){ //просмотр всего товара по меню
	 		if (isset($_GET['showall'])){
	 			//echo ' showAllALL';
				include("./showAllALL.php"); //режим всего просмотра не по матрице 
	 		} else{
	 			//echo ' showMatrixAll';
	 			include("./showMatrixAll.php"); //режим просмотра матрицы
	 		} 		
	 	}
	 	else{ //просмотр категории
	 		if (!isset($_GET["showall"])){ // просмотр категории по матрице
	 			//echo ' категория по матрице';
	 			include("./showMatrix.php");
		 		if(!isset($_GET["menu"])){
		 			//echo ' обнуление матрицы';
		 			$_SESSION['session_userNoMatrix'] = ''; ///???
		 		}
	 		}
		 	else{
		 		include("./showAll2.php"); //товар по категории не по матрице
		 		//echo ' категория не по матрице';
		 		if(!isset($_GET["menu"])){
		 			//echo ' обнуление матрицы2';
		 			$_SESSION['session_userNoMatrix'] = ''; ///???
		 		}
		 	}
	 	}
 	}
 }
 else { //если не клик в заказе (по меню)

 		//echo '+++tporderlist';

 		$_SESSION['session_userNoMatrix'] = '';
		include("includes/tporderlist.php");

		echo '<div id="goodbye">
					<p><a href="logout.php">Выйти</a></p>
				</div>
			</div>';

 }

 echo '</div>';
 ?>

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

// Закрыть меню при клике вне
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
   event.preventDefault();
    document.querySelector('.header_burger').click();
    document.getElementsByTagName('html')[0].style.overflow = 'scroll';
  }
  
});

//по клике на лупу - закрывается меню
function closeMenu(){
	document.querySelector('.header_burger').click();
}

//функция скррытия категорий первого уровня, если нет подкатегорий
document.addEventListener('DOMContentLoaded', function(){ 
	var submenu = document.querySelectorAll('.submenu');
  for (i=0; i<submenu.length; i++){
  	if (submenu[i].childNodes.length > 0){
		}else{
			submenu[i].parentElement.style.display = 'none';
		}
  }	

  //скрываем стрелочки после загрузки, если страниц <=10
	var allRows = document.querySelectorAll('#order_table .all_in_category');
	console.log(allRows.length);
	let arrOfRows = new Array(); //массив не срытых кнопкой отбора строк
	for (var i = 1; i <= allRows.length; i++) {
		elem = allRows[i-1];
		if (!elem.classList.contains('hidden_row')) {
			arrOfRows.push(elem);
		}
	}
	maxPage = Math.ceil(arrOfRows.length/ROWS_ON_PAGE);
	console.log(arrOfRows.length);
	console.log(maxPage);

	if(maxPage <= 10){
		var allPages = document.querySelectorAll('.pages > a');
		//console.log('READY - кол-во элементов в стр' + allPages.length);
		for (var i = 1; i <= allPages.length; i++) {
			elemPage = allPages[i-1];
			if(elemPage.textContent == '>' || elemPage.textContent=='<'){
				elemPage.style.display='none';
			}
		}	
	}

	///
	//скрываем стрелочки у крайних страниц
	//при перехагузке
	//последняя страница не может открыться автоматически
	var allPages = document.querySelectorAll('.pages > a');
	one = allPages[1];
	last = allPages[allPages.length-2];
	cursor_left = allPages[0];
	cursor_right = allPages[allPages.length-1];
	if(one.classList.contains("page_display")){
		cursor_left.style.display='none';
		console.log('cursor_left hide global!');
	}
	/*
	if(last.classList.contains("page_display")){
		cursor_right.style.display='none';
		console.log('cursor_rigth hide!');
	}
	*/

});


//отображение блока с галочкой, если галочка есть - текста нет
let pic = document.querySelectorAll(".order_ok_pic");
for (let i = 0; i < pic.length; i++) {
	if (getComputedStyle(pic[i]).display == 'block'){
		pic[i].previousElementSibling.textContent = '';
		console.log('ok!')
	}
}

//поиск по таймеру - ожидание ввода текста в течении 500, потом поиск с этим текстом
let typingTimer;                
let doneTypingInterval = 500;  
let input = document.getElementById("inputName");

input.addEventListener('keyup', () => {
    clearTimeout(typingTimer);
    if (input.value) {
        typingTimer = setTimeout(doneTyping, doneTypingInterval);
    }
});

function doneTyping () {
	console.log('value = '+ input.value);
	if (input.value != ''){
		orderGUID=document.querySelector("#order_GUID").textContent;
		$.ajax({
		type : 'POST',
	    url: "ajax2.php?orderGUID="+orderGUID,
	    data: {name: input.value},
	    success: function(data){
		    	setPage(1);
		    	
		    	strLoc = location.toString();
		    	console.log(strLoc);
		    	strLoc = strLoc + '&search=true';
		    	history.pushState(null, null, strLoc);

		    	/*
		    	j = strLoc.indexOf('?page');
				subStr = strLoc.substring(j); 
		    	newstr = strLoc.replace(subStr, '');
		    	//i = strLoc.indexOf('&search');
				//if (i<0) { strLoc = strLoc + '&search=true';};

				history.pushState(null, null, newstr);
				*/

				//когда начинаю вводить в строке поиска, то contract_GUID очищается внутри orderTable, поэтому перезаписываем зачение в верхний уровень
				if (document.querySelector('#contract_GUID') != null){
					contractGUID = document.querySelector('#contract_GUID').textContent;
					console.log('contractGUID' + contractGUID);
					document.getElementById("contract_GUID2").textContent = contractGUID;
				}

					document.getElementById("order_table").innerHTML = '';
		    	document.getElementById("order_table").innerHTML = data;
		    	setLazy();
		    	lazyLoad();
		    	complete2();
		    	hasOrder();
		    	hideRowsTableDebt(contractGUID);

	    	}
		});
	} 
}

//обработчик пустого поля ввода
function load_options(elem){
	input = document.getElementById("inputName");
	value =  input.value;
	//strLoc = location.toString();
	//console.log(strLoc);
	if (value == ''){
		strLoc = location.toString();
		i = strLoc.indexOf('orderGUID=');
		orderGUID = strLoc.slice(i+10, i+46);
		console.log('orderGUID - '+orderGUID);
     //window.location = 'tt.php?page=1&parentGUID=none&orderGUID='+orderGUID+'&menu=true&showall=true';
	}
} 

//Клик по "Списко заказов"
function returnToListOrders(){
	window.location = "tp.php";
}

</script> 
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.12.15/dist/sweetalert2.all.min.js"></script>

<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
  <script  src="js/script.js"></script>
  <script src="https://kit.fontawesome.com/cd9a557ac3.js" crossorigin="anonymous">


  </script> 

<?php 
//показываем футер с контактами, если меню скрыто -то не показываем блок с контактами
include("includes/footer.php");
/*
echo '<script type="text/javascript">
	  	header = document.querySelector("#header_tp");
       if(header.style.display == "none"){
       		document.querySelector("#call").style.display = "none";
       } else{
       		document.querySelector("#call").style.display = "block";
       }
  </script>';
 */

?>

<?php endif; ?>