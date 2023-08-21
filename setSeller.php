<?php
session_start();


$name = $_POST['name'];

$_SESSION['session_contactSeller'] = $name;
echo $_SESSION['session_contactSeller'];


?>