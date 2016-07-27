<?php 
ob_start();
session_start();
date_default_timezone_set('Asia/Taipei');
require_once $_SESSION['path'].'/lib/classes/Authentication.php';
if(isset($_SESSION['user'])||isset($_SESSION['username'])||isset($_SESSION['usergroup'])||isset($_SESSION['fingerprint'])||isset($_SESSION['fprinttime'])){
	$Authentication=new Authentication();
	$Authentication->logoutUser($_SESSION['user'],$_SESSION['fingerprint']);
	unset($_SESSION['user']);
	unset($_SESSION['username']);
	unset($_SESSION['usergroup']);
	unset($_SESSION['fingerprint']);
	unset($_SESSION['fprinttime']);
}
session_destroy();
header('Location: login.php');
?>