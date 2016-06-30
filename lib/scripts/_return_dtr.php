<?php
	ob_start();
	session_start();
	
	require_once $_SESSION['path'].'/lib/classes/DTR_Generator.php';
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD017'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	//function microtime_float(){list($usec,$sec) =explode(" ",microtime());return ((float)$usec + (float)$sec);}
	
	//Get GET Values for Individual DTR Processing
	$id=isset($_GET['id'])?trim(strip_tags($_GET['id'])):'00000';
	$Year=isset($_GET['yr'])?trim(strip_tags($_GET['yr'])):date('Y');
	$Month=isset($_GET['mo'])?trim(strip_tags($_GET['mo'])):date('m');
	$PayPeriod=isset($_GET['pr'])?trim(strip_tags($_GET['pr'])) :0;
	//Get Values for per Office/SubOffice DTR Processing
	$isPerOff	=isset($_GET['spo'])?trim(strip_tags($_GET['spo'])):'0';
	$SubOffID=isset($_GET['sof'])?trim(strip_tags($_GET['sof'])):'SOOF00101';
	$ApptStID=isset($_GET['aps'])?trim(strip_tags($_GET['aps'])):'AS001';
	
	if(!(($Authorization[0])||($_SESSION['user']==$id))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	echo "1|$id|";
	//$start_time =microtime_float();
	$gDTR=new DTR();
	if ($isPerOff=='1'){$gDTR->showOffDTR($SubOffID,$ApptStID,$Year,$Month,$PayPeriod);}
	else {$gDTR->showEmpDTR($id,$Year,$Month,$PayPeriod); }
	//$end_time =microtime_float();
	//$consumed_time =$end_time - $start_time;
	//echo"$SubOffID,$ApptStID,$Year,$Month,$PayPeriod</br> $consumed_time ";
	
?>