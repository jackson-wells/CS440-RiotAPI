<?php
	if(!isset($_SESSION)) { 
   		session_start();
	}
	session_unset();
    	unset($_SESSION);
	session_destroy();
	$_SESSION = array();
    	$_SESSION = [];
	header("Location: index.php");
?>
