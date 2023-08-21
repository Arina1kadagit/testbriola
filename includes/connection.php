<?php
	require("constants.php");
	$con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die("initial host/db connection problem");
	//if (!mysqli_set_charset($con, "utf8")) {
	if (!mysqli_set_charset($con, "utf8mb4")) {
   		//echo "Ошибка при загрузке набора символов utf8: " . mysqli_error($con);
	} else {
   		//echo "Текущий набор символов: " . mysqli_character_set_name($con);
   	}
?>
