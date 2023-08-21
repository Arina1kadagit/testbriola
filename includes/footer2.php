<?php

echo '<footer>
	Разработка сайта "Агит-Плюс"
</footer>

<div id="call" class="mobile_call">
	<p>Контакты торгового представителя</p>';

	echo '<span id="call_name">';

	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	if (strstr($url,'tt.php') !== false){
		if (isset($_SESSION['session_contactSeller'])){
			$name = $_SESSION['session_contactSeller'];
		} else{
			$name = "";
		}
	}
	else{
		$name = $_SESSION['session_userDisplayName'];
	}

	//echo 'name - '.$name.' - ';

   	$name2 = preg_replace('/[0-9]/', '', $name);
    	$name2 = preg_replace('/[-]/', '', $name2);
    	echo $name2;
    	echo '</span>';
	
	echo '<a id="call_telephone" href="tel:';

	$phone_number = preg_replace('~\D+~','', $name);
	echo $phone_number.'">';
	echo $phone_number;

	echo '</a>';

echo   '</div>
	</body>
</html>';

?>