<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$bt=isset($_POST['bt'])?trim(strip_tags($_POST['bt'])):1;
	$tc=isset($_POST['tc'])?trim(strip_tags($_POST['tc'])):1;
  
	$cnx=odbc_connect('BIOMETRICS', 'root', '123456');
	if(!$cnx){Error_handler("Error in odbc_connect",$cnx );}
	
	$respTxt="|ERROR 49!!!";
	
	$NumberOfRecords=0;
	switch ($st){
		case -1	: 	// send a simple odbc query. returns an odbc cursor
					$sql="SELECT * FROM `NGAC_LOG` ORDER BY `logtime` ASC;";
					$cur=odbc_exec($cnx, $sql);
					if(!$cur){Error_handler("Error in odbc_exec(no cursor returned) ",$cnx);}
					while(odbc_fetch_row($cur)){$NumberOfRecords+=1;} //Count the number of records.
					$bt=ceil($NumberOfRecords/20);
					$respTxt="0|$bt|1|0|Total records to upload $NumberOfRecords";
					break;
		case 0	: 	
					if($tc<=$bt){
						$MySQLi=new MySQLClass();
						//Get Data from MS Access database (raw logs)
						$sql="SELECT TOP 20 * FROM `NGAC_LOG` ORDER BY `logindex` ASC;";
						$cur=odbc_exec($cnx, $sql);
						if(!$cur){Error_handler("Error in odbc_exec(no cursor returned) ",$cnx);}
						
						while(odbc_fetch_row($cur)){  
							$BioLogID=odbc_result($cur,1);
							$EmpID=substr(odbc_result($cur,4),0,5);
							$BioLogTime=odbc_result($cur,3);
							$BioSLogTime=odbc_result($cur,9);
							$sql="INSERT INTO `pmis`.`tblbiometrics` (`BioLogID`, `EmpID`, `BioLogTime`, `BioSLogTime`, `REMARKS`) VALUES ('$BioLogID', '$EmpID', '$BioLogTime', '$BioSLogTime', '');";
							$MySQLi->sqlQuery($sql);
						}
						//Delete uploaded data from MS Access table
						$sql="DELETE * FROM (SELECT TOP 20 * FROM `NGAC_LOG` ORDER BY `logindex` ASC) AS `Top20` WHERE `NGAC_LOG`.`logindex`=`Top20`.`logindex`;";
						$cur=odbc_exec($cnx, $sql);
						if(!$cur){Error_handler("Error in odbc_exec(no cursor returned) ",$cnx);}
						
						$tc += 1;
						$respTxt="0|$bt|$tc|".number_format((($tc/$bt)*100),2)."|Uploading... ";
					}
					else{$respTxt="1|$bt|$tc|".number_format(100,2)."|Upload Complete... ";}
					break;
		default	:	$respTxt="999|ERROR 49!!!|||";
	}
    odbc_close($cnx);
	echo $respTxt;
	
	function Error_Handler($msg,$cnx){
		echo "$msg \n";
		odbc_close($cnx);
		exit();
	}

?>