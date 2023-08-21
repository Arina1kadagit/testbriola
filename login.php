<?php
	session_start();
	header('Content-type: text/html; charset=utf-8');
	// Если параметр сессии имяпользователя уже установлен, значит пользователь уже авторизован, перенаправляем его на страницу для авторизованных пользователей
	if (isset($_SESSION['session_username'])) {
		if ($_SESSION['session_userRole'] == 'Shop') {
			// И перенаправлеям на страницу для торговой точки.
			header("Location: tt.php");
		} elseif ($_SESSION['session_userRole'] == 'Seller') {
			// И перенаправлеям на страницу для торговой точки.
			header("Location: tp.php");
		} elseif ($_SESSION['session_userRole'] == 'Administrator') {
			// И перенаправлеям на страницу для торговой точки.
			header("Location: pri.php");
		}
	}
?>
<?php require_once("includes/connection.php"); ?>
<?php
	$message = '';
	//$arrayBodyResponse = array('' => '');
	// Если была нажата кнопка "Вход" на форме авторизации
	if (isset($_POST["login"])) {
		if (!empty($_POST['username']) && !empty($_POST['password'])) {
			// Получение значения имя пользователя из переданных полей формы авторизации.
  			$username = $_POST['username'];
  			// Получение значения пароль из переданных полей формы авторизации.
  			$password = $_POST['password'];
			// Составляем и отправляем запрос
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/authorization');
			//echo BASE_1C_ADDRESS . '/hs/api/authorization';
			curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			
			$arrayBodyRequest = [
				"login" => $username,
				"password" => $password,
			];
			$textBodyRequest = json_encode($arrayBodyRequest);
			//echo 'Запрос:<br>' . $textBodyRequest . '<br>';
			curl_setopt($curl, CURLOPT_POSTFIELDS, $textBodyRequest);
			$textBodyResponse = curl_exec($curl);
			//$info = curl_getinfo($curl);
			//echo var_dump($info);
			//echo 'Ответ:<br>' . $textBodyResponse . '<br>';
			curl_close($curl);

			$arrayBodyResponse = json_decode($textBodyResponse, true);
			// Если какой-либо ответ получен
			if (gettype($arrayBodyResponse) == 'array') {
				/*echo '<div>';
				foreach ($arrayBodyResponse as $key => $value) {
					echo $key . ": ";
					echo '<br>';
					if (gettype($value) == 'array') {
						foreach ($value as $k => $val) {
							echo $k . ": " . $val;
							echo '<br>';
						}							
					} else {
						echo $key . ": " . $value;
						echo '<br>';
					}
				}
				echo '</div';*/
				// Если всё норм
				if ($arrayBodyResponse['statusСode'] == 200) {
					// Так как авторизация прошла, устанавливаем переменные сессии.
					$_SESSION['session_username'] = $username;
	 				$_SESSION['session_userDisplayName'] = $arrayBodyResponse['userDisplayName'];
	 				$_SESSION['session_accountGUID'] = $arrayBodyResponse['accountGUID'];
	 				$_SESSION['session_userGUID'] = $arrayBodyResponse['userGUID'];
	 				$_SESSION['session_userRole'] = $arrayBodyResponse['role'];
	 				$_SESSION['session_userMatrix'] = array();
	 				foreach ($arrayBodyResponse['matrix'] as $key => $value) {
	 					$arrPorduct = array('productGUID' => $value, 'productQuantity' => 0);
	 					//var_dump($arrPorduct);
	 					array_push($_SESSION['session_userMatrix'], $arrPorduct);
	 				}
	 				//Массив для товаров не из матрицы
					$_SESSION['session_userNoMatrix']='';
	 				//var_dump($_SESSION['session_userMatrix']);

					//долги
					$_SESSION['session_ArrayOfDebts'] = array();
					foreach ($arrayBodyResponse['ArrayOfDebts'] as $arr) {

						$arrPorduct = array();
						$arrPorduct['ContractGUID'] = $arr['ContractGUID'];
						$arrPorduct['NumberOrder'] = $arr['NumberOrder'];
						$arrPorduct['DateOrder'] = $arr['DateOrder'];
						$arrPorduct['sum'] = $arr['sum'];
						$arrPorduct['AmountOfDebt'] = $arr['AmountOfDebt'];
						$arrPorduct['DaysOverdue'] = $arr['DaysOverdue'];
						/*
						array_push($arrPorduct, $arr['ContractGUID']);
						array_push($arrPorduct, $arr['NumberOrder']);
						array_push($arrPorduct, $arr['DateOrder']);
						array_push($arrPorduct, $arr['sum']);
						array_push($arrPorduct, $arr['AmountOfDebt']);
						array_push($arrPorduct, $arr['DaysOverdue']);	
						*/
	 					//var_dump($arrPorduct);
	 					//echo '</br>';
	 					array_push($_SESSION['session_ArrayOfDebts'], $arrPorduct);
	 					
	 				}




// временно договор прописан тут
	 				// Междуреченск г, Октябрьская ул, дом № 24
	 				//$_SESSION['session_contractGUID'] = 'c4523f4a-32b0-11eb-85ab-b42e99611d38';
					if ($_SESSION['session_userRole'] == 'Shop') {
						// И перенаправлеям на страницу для торговой точки.
						header("Location: tt.php");
					} elseif ($_SESSION['session_userRole'] == 'Seller') {
						// И перенаправлеям на страницу для торговой точки.
						header("Location: tp.php");
					} elseif ($_SESSION['session_userRole'] == 'Administrator') {
						// И перенаправлеям на страницу для торговой точки.
						header("Location: pri.php");
					}
				}
				// Возвращена ошибка аутентификации от сервиса
				elseif ($arrayBodyResponse['statusСode'] == 401) {
					$message.= 'Авторизация не удалась. ' . $arrayBodyResponse['errors'];
				// Непредвиденный ответ от сервиса
				} else {
					$message.= 'Авторизация не удалась, повторите попытку позже.';
				}
			// Если вообще нет никакого ответа от сервиса
			} else {
				$message.= "Нет ответа от сервиса аутентификации!". $textBodyResponse;
			}
		} else {
			$message.= "Заполните оба поля «Имя пользователя» и «Пароль»!";
		}
	}
?>

<?php include("includes/header.php"); ?>


<head>
  <meta charset="UTF-8">
  <title>CodePen - Constellation</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>

</head>

<body>
	<div id="firstwindow">
		<div class="radio_tab">
			<div class="container mlogin ">
				<div id="login">
					<h1>Вход</h1>
					<form name="loginform" action="" method="post" id="loginform">
						<p>
							<input name="username" type="text" class="input" id="username" size="20" value="" placeholder="Имя">
						</p>
						<p class="password">
							<input name="password" type="password" class="input" id="password" size="20" value=""
							placeholder="Пароль">
							<img id="view_password" src="visibility_2.png">
						</p>
						<?php if (!empty($message)) {echo '<p class="error error_login" style="color: red; text-align: center;">' . $message . '</p>';} ?>
						<p class="submit "><input name="login" class="button button_login" type= "submit" value="ОК"></p>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!--
	<div class="block_animation">
  		<canvas></canvas>
	</div >


	<div class="grapfic_mobile">
	</div>
	-->

	<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/zepto/1.0/zepto.min.js'></script>
	<script src='./js/Stats.js'></script>
	<script  src="./js/animation.js"></script>
	<script>
		window.onload = function(event) {
  		document.getElementsByTagName('html')[0].style.overflow = 'hidden';
  		console.log('hidden');
  		
		}
	</script>
<script>
//отслеживание username
$("#username").focus(function() {
	var flag = 1;
	$("#password").focus(function(){  flag = flag + 1;  });
	console.log('flag = ' + flag); 

	if (flag != 0){
		$('.grapfic_mobile').addClass('data');  
		console.log('focus'); 
	} else{
		$('.grapfic_mobile').removeClass('data');  console.log('blur');
	}
});

$("#username").blur( function(){  
	var flag = 0;
	$("#password").focus(function(){ flag++;  });
	console.log('flag = ' + flag); 
	if (flag != 0){
		$('.grapfic_mobile').addClass('data');  
		console.log('focus'); 
	} else{
		$('.grapfic_mobile').removeClass('data');  console.log('blur');
	}
});

//отслеживание password
$("#password").focus(function() {
	var flag = 1;
	$("#username").focus(function(){  flag = flag + 1;  });
	console.log('flag = ' + flag); 
	if (flag != 0){
		$('.grapfic_mobile').addClass('data');  
		console.log('focus'); 
	} else{
		$('.grapfic_mobile').removeClass('data');  console.log('blur');
	}
});

$("#password").blur( function(){  
	var flag = 0;
	$("#username").focus(function(){ flag++;  });
	console.log('flag = ' + flag); 

	if (flag != 0){
		$('.grapfic_mobile').addClass('data');  
		console.log('focus'); 
	} else{
		$('.grapfic_mobile').removeClass('data');  console.log('blur');
	}
});

// конец скрытия блока

$('body').on('click', '#view_password', function(){  
	if ($('#password').attr('type') == 'password'){
		$(this).addClass('view');
		$('#password').attr('type', 'text');
	$("#view_password").attr("src","visibility.png");
	} else {
		$(this).removeClass('view');
		$('#password').attr('type', 'password');
		$('#view_password').attr("src","visibility_2.png");

	}
});

/*
$(document).keyup(function(e) {
	if (e.key === "Escape" || e.keyCode === 27) {
		$('.grapfic_mobile').removeClass('data');
		alert('Нажата клавиша Escape');
	}
	if (e.key = e.KEYCODE_BACK){
		alert('Нажата клавиша KEYCODE_BACK');
	}
	if (e.keyCode == 4){
		alert('Нажата клавиша BACK');
	}
	if (e.keyCode == 34){
		alert('page down');
	}
	if (e.keyCode == 35){
		alert('end');
	}
});
*/

//начальные параметры экрана
window.sumedges = $(window).width() + $(window).height();

$(window).resize(function(){
	//alert('1 - '+window.sumedges);
		console.log('1 -' + sumedges);
		var sumedges2 = $(window).width() + $(window).height()
		console.log('2 -' + sumedges2);
		//alert('2 - '+sumedges2);
    if(sumedges2 < sumedges) {
	    $('.grapfic_mobile').addClass('data');
	    console.log('focus');
	  } else {
	    $('.grapfic_mobile').removeClass('data'); 
	    console.log('blur');
	  }
});
</script>



</body>

<?php include("includes/footer.php"); ?>
