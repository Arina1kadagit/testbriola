<?php
	session_start();
	header('Content-type: text/html; charset=utf-8');
	
	require_once("includes/constants.php");
	
	// Если параметр сессии имяпользователя уже установлен, значит пользователь уже авторизован, перенаправляем его на страницу для авторизованных пользователей
	if (isset($_SESSION['session_username'])) {
		
		$textBodyRequest = file_get_contents('php://input');
		
		// Составляем и отправляем запрос
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, BASE_1C_ADDRESS . '/hs/api/update');
		//echo BASE_1C_ADDRESS . '/hs/api/authorization';
		curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: ' . BASE_1C_AUTH]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		//echo 'Запрос:<br>' . $textBodyRequest . '<br>';
		curl_setopt($curl, CURLOPT_POSTFIELDS, $textBodyRequest);
		$textBodyResponse = curl_exec($curl);
		echo $textBodyResponse;
	}
?>