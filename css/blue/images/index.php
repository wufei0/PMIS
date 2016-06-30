<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');

	if ($_SESSION['fingerprint']!=md5($_SESSION['user']." ".$_SERVER['HTTP_USER_AGENT']." ".$_SERVER['REMOTE_ADDR']." ".$_SESSION['fprinttime'])){
		session_destroy();
		header('Location: ../../../login.php');
		exit();
	}
	
?>