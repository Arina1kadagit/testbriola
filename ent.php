<?php
	header('X-Accel-Buffering: no');
	header('Content-type: text/html; charset=utf-8');
// уязвимость, если у пользователя нет ни одной группы и не установлена ни одна группа в правах журнала, считается что права равны и они есть
?>

<?php
	session_start();
	
	if(!isset($_SESSION["session_username"])):
		header("location:login.php");
	else:
?>
<?php require_once("includes/connection.php"); ?>
<!--<_?php
	require("constants.php");
	$con = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME) or die(mysql_error());
?>-->
<?php include("includes/header.php"); ?>
<!--<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Тестирование доменной авторизации</title>
	<link href="css/style.css" media="screen" rel="stylesheet">
</head>

<body>-->

<?php
	$NUMBER_OF_SYSTEM_COLUMNS_IN_JORNALS = 5;
	$message= "У нас всё хорошо!";
?>

<?php
	// Если нажата кнопка "Добавить журнал"
	if(isset($_POST["addjournal"])) include("includes/addjournal.php");
	// Если нажата кнопка "Создать журнал из файла"
	if(isset($_POST["addjournalfromfile"])) include("includes/addjournalfromfile.php");
	// Если нажата кнопка "Добавить строку в журнал"
	if(isset($_POST["addjournalrow"])) include("includes/addjournalrow.php");
	// Если нажата кнопка "Просмотр истории"
	if(isset($_POST["viewhistoryrow"])) include("includes/viewhistoryrow.php");
	// Если нажата кнопка "Дубль"
	if(isset($_POST["copyvaluestoaddrow"])) include("includes/copyvaluestoaddrow.php");
	// Если нажата кнопка "Просмотр истории"
	if(isset($_POST["sizesreset"])) include("includes/sizesreset.php");
	// Если нажата кнопка "Сохранить строку журнала"
	if(isset($_POST["savejournalrow"])) include("includes/savejournalrow.php");
	// Если выбрано поле редактирования количества колонок
	if(isset($_GET["selectedJournal"])&&(isset($_GET["editNumberOfCols"]))) include("includes/editnumberofcols.php");
	// Если выбрано поле редактирования количества строк
	if(isset($_GET["selectedJournal"])&&(isset($_GET["editRowsOnPage"]))) include("includes/editrowsonpage.php");
	// Если нажата кнопка сохранить в форме редактирования количества колонок
	if(isset($_POST["savenumberofcol"])) include("includes/savenumberofcol.php");
	// Если нажата кнопка сохранить в форме редактирования количества строк
	if(isset($_POST["saverowsonpage"])) include("includes/saverowsonpage.php");
	// Если выбрано поле редактирования рзрешений на редактирование строк или на редактирование разрешений
	//if(isset($_GET["selectedJournal"])&&(isset($_GET["editPermissionEditRows"])||isset($_GET["editPermissionEditSettings"]))) include("includes/editpermissioneditget.php");
	if((isset($_POST["editpermissioneditrows"])||isset($_POST["editpermissioneditsettings"]))) include("includes/editpermissioneditpost.php");
	// Если нажата кнопка "Подтвердить выбор" в формах редактирования групп разрешений
	if(isset($_POST["savelistofgroups"])) include("includes/savelistofgroups.php");
	// Если выбрано "Редактирование названия колонки"
	if(isset($_GET["selectedJournal"])&&isset($_GET["editHeadOfColumn"])) include("includes/editheadofcolumn.php");
	// Если нажата кнопка "Сохранить значение названия колонки"
	if(isset($_POST["savecolumnname"])) include("includes/savecolumnname.php");
	// Если выбрана ячейка журнала для редактирования
	if(isset($_GET["selectedJournal"])&&isset($_GET["x"])&&isset($_GET["y"])&&($_GET["editMode"] == "cell")) include("includes/editjournalcell.php");
	// Если выбрана строка журнала для редактирования
	if(isset($_GET["selectedJournal"])&&isset($_GET["x"])&&isset($_GET["y"])&&($_GET["editMode"] == "row")) include("includes/editjournalrow.php");
	// Если нажата кнопка "Сохранить значение ячейки журнала"
	if(isset($_POST["savejournalcell"])) include("includes/savejournalcell.php");
	// Если нажата кнопка сохранить на форме "Настройки", сохраняем настройки
	if (isset($_POST["savesettings"])) include("includes/savesettings.php");

	// Если передано значение размер колонки из JS, сохраняем новый размер колонки
	if(isset($_GET["sizeOfColumn"])) include("includes/savesizeofcolumn.php");

	// Если нажата кнопка "Удалить журнал"
	if (isset($_POST["deletejournalconfirm"]) || (isset($_POST["deletejournalfirst"]))) include("includes/deletejournalconfirm.php");
	// Если получено последнее подтверждение удаления журнала
	if (isset($_POST["deletejournallast"])) include("includes/deletejournallast.php");
?>

<div class="container"> 
	<div id="welcome">
 		<h2>Добро пожаловать, <span><?php echo $_SESSION['FIOofUser'];?>!</span></h2>
 		<!--<div class="closed_part">
 			<div class="before_cp">!!!</div>
 			<div><p>Это закрытая часть сайта, доступна только после авторизации</p></div>
 			<div class="after_cp">!!!</div>
 		</div>-->
 	</div>

<!--<div> id="error">MESSAGE: У нас всё хорошо!</div>-->
<?php
	// Получаем настройки из таблицы настроек
	$mysqlQuery = mysqli_query($con, "SELECT * FROM journals_settings WHERE id = 1");
	$numrows = mysqli_num_rows($mysqlQuery);
	if($numrows!=0) { $row = mysqli_fetch_array($mysqlQuery, MYSQLI_ASSOC); }
	// Показывать сообщение?
	$showMessage = $row["show_message"];
	if ($showMessage) echo "<div id=\"error\">MESSAGE: ". $message . "</div>";
?>

<!--<div id="settingsInternal">
		<h2>Настройки</h2>
		<form id="savesettingsform" name="savesettingsform" action="" method="post">
			<p>
				<label for="servername">Имя администратора:<br>
				<input name="username" type="text" class="input" id="username" size="20" value="<?php echo $adminname ?>"></label>
			</p>
			<p>
				<label for="servername">Пароль администратора:<br>
				<input name="password" type="password" class="input" id="password" size="20" value="<?php echo $adminpass ?>"></label>
			</p>
			<p>
				<label for="servername">Имя сервера:<br>
				<input name="servername" type="text" class="input" id="servername" size="20" value="<?php echo $ldaphost ?>"></label>
			</p>
			<p>
				<label for="serverport">Порт сервера:<br>
				<input name="serverport" type="text" class="input" id="serverport" size="20" value="<?php echo $ldapport ?>"></label>
			</p>
			<p>
				<label for="domain">Имя домена:<br>
				<input name="serverdomain" type="text" class="input" id="serverdomain" size="20" value="<?php echo $domain ?>"></label>
			</p>
			<p class="submit"><input name="savesettings" class="button" type= "submit" value="Сохранить"></p>
		</form>
	</div>-->

<?php
	// Вывод блока настроек
	if ($_SESSION["groupsOfUser"][0] == "Local-Admin") {
		include("includes/editsettings.php");
	}
?>

<!--<div id="listOfJournals">
		<h2>Список журналов</h2>
		<div class="row"><div>Id</div><div>Имя таблицы</div><div>Название журнала</div><div>Количество колонок</div></div>
		<div class="row"><a href="ent.php?selectedJournal=journal_0001_data"></a><div>1</div><div>journal_0001_data</div><div>Список сотрудников</div><div>4</div></div>"
		<form name="addjournal" action="ent.php" method="post" id="addjournal" class="row">
		<p></p>
		<p></p>
		<p>
			<label for="journalname">Название журнала:<br>
			<input name="journalname" type="text" class="input" id="journalname" size="20" value=""></label>
		</p>
		<p>
			<label for="numberofcol">Количество колонок:<br>
			<input name="numberofcol" type="text" class="input" id="numberofcol" size="20" value=""></label>
		</p>
		<p class="submit"><input name="addjournal" class="button" type= "submit" value="Добавить журнал"></p>
   		</form>
   	</div>-->
<?php
	// Вывод списка разделов
	include("includes/listofsections.php");
	// Вывод списка отчётов
	if ($_SESSION["groupsOfUser"][0] == "Local-Admin") include("includes/listofreports.php");
	// Вывод списка журналов
	if (!isset($_GET["report"])) {
		include("includes/listofjournals.php");
	} else {
		include("includes/reportofjournals.php");
	}
?>

<?php
	$time1 = microtime(true);
	// Если выбран журнал, вывод журнала
	if(isset($_GET["selectedJournal"])&&(!isset($_GET["editNumberOfCols"]))&&(!isset($_GET["editRowsOnPage"]))&&(!isset($_GET["editPermissionEditRows"]))&&(!isset($_GET["editPermissionEditSettings"]))) {
		include("includes/selectedjournal.php");
	}
	//usleep(1000000);
	$time2 = microtime(true);
	$time3 = $time2 - $time1;
	if ($showMessage) {
		//echo "<div style=\"grid-row: 1 / span 1; grid-column: 2 / span 1;\">".$time3."<br />";
		for( $i = 0 ; $i < 10 ; $i++ ) {
			//echo $i . ' ';
			flush();
			ob_flush();
			//sleep(1);
		}
		// echo "<br />";
		// foreach ($_SESSION['groupsOfUserDN'] as $key => $value) {
		// 	echo $value."<br />";
		// }
		// echo "</div>";
	}
?>

	<div id="goodbye">
		<p><a href="logout.php">Выйти</a> из системы.</p>
	</div>
</div>
<?php include("includes/footer.php"); ?>
<!--<footer>
	© 2018 <a href="http://inet-nk.ru/ent.php">Вход</a> zsuk
</footer>
</body>
</html>-->

<?php endif; ?>