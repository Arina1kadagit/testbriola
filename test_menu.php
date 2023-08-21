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

<body >
	<div class="combo">
		<input id="inputName" type="text" name="school" id="state" size="20" placeholder="Введите наименование" oninput="load_options(this)">
		<ul id="listName" class="listName">
		</ul>
	</div>

<script>
function load_options(elem, text){
	input = document.getElementById("inputName");
	value =  input.value;

    $.ajax({
    	type : 'POST',
        url: "ajax.php",
        data: {name: value},
        success: function(data){
        	listName = document.getElementById("listName");
        	//li = listName.querySelectorAll('.listName_li')[0];
        	//li.innerHTML = data;
        	listName.innerHTML = data;
	    }
    })
}


</script>


<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
 <script src="https://kit.fontawesome.com/cd9a557ac3.js" crossorigin="anonymous"></script>	
</body>



<?php endif; ?>