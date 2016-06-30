<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
		
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD002'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	function FixDateLog($ThatDate,$DateLog){$LogDate=explode(" ",$DateLog);return $ThatDate." ".$LogDate[1];}
	function isOK($DateLog){$iS=(substr($DateLog,0,4)!="2000");return $iS;}
	
	$EmploymentStatus=isset($_POST['es'])?trim(strip_tags($_POST['es'])):"A";
	$StartID=isset($_POST['iid'])?trim(strip_tags($_POST['iid'])):'0';
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'99999';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneIDs=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalOffices=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalOffices FROM department WHERE dept_code >= '".$StartID."' AND dept_code <= '".$EndID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Office=odbc_fetch_array($cur);
					$TotalOffices=$Office['TotalOffices'];
					
					$cur=odbc_exec($cnx,"SELECT dept_code FROM department WHERE dept_code >= '".$StartID."' AND dept_code <= '".$EndID."' ORDER BY dept_code ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Office=odbc_fetch_array($cur);
					$curID=$nextID=trim($Office['dept_code']);
					$DoneIDs=0;
					
					$MSG="\nMigrating of all USED OFFICE Information from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneIDs|$TotalOffices|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$MySQLi=new MySQLClass();
					
					$OF001=array('0','004','007','058','008','009','029','005','818','37','557','98','980','40','02','555','99','36','03','33','446','551','131','371','372');
					$OF002=array('0','50','449','501','342');
					$OF003=array('0','01','04','100','20','201','21','222','29','32','35','333','338','340');
					$OF004=array('0','002','032','033','035','121','13','18','211','439');
					$OF005=array('0','074','09','277');
					$OF006=array('0','08');
					$OF007=array('0','17','443');
					$OF008=array('0','15','218');
					$OF009=array('0','102','12');
					$OF010=array('0','001','07','888');
					$OF011=array('0',);
					$OF012=array('0','019','05','554');
					$OF013=array('0','043','06','324','520');
					$OF014=array('0','23','789','22','27','343','25','488','195','989','47','28','196','197','050','26','045','24','19','027','038','022','023','026','047','024','044','068','025','14','15','344');
					$OF015=array('0','14','777');
					$OF016=array('0','010','011','10','105','106','38','39','45');
					$OF017=array('0','021','16','788');
					$OF018=array('0','017','31');
					$OF019=array('0','052','43','434','435','436','437','438','444','62');
					
					$SubOffID="SO000";
					if($key=array_search($curID,$OF001,true)){
						switch ($key){case 1:$SubOffID="SO001";break;case 2:$SubOffID="SO004";break;case 3:$SubOffID="SO004";break;case 4:$SubOffID="SO003";break;case 5:$SubOffID="SO002";break;case 6:$SubOffID="SO002";break;case 7:$SubOffID="SO001";break;case 8:$SubOffID="SO001";break;case 9:$SubOffID="SO007";break;case 10:$SubOffID="SO001";break;case 11:$SubOffID="SO003";break;case 12:$SubOffID="SO003";break;case 13:$SubOffID="SO001";break;case 14:$SubOffID="SO001";break;case 15:$SubOffID="SO001";break;case 16:$SubOffID="SO001";break;case 17:$SubOffID="SO004";break;case 18:$SubOffID="SO004";break;case 19:$SubOffID="SO002";break;case 20:$SubOffID="SO002";break;case 21:$SubOffID="SO002";break;case 22:$SubOffID="SO002";break;case 23:$SubOffID="SO005";break;case 24:$SubOffID="SO005";break;default:$SubOffID="SO000";break;}
					}
					else if($key=array_search($curID,$OF002,true)){$SubOffID="SO008";}
					else if($key=array_search($curID,$OF003,true)){$SubOffID="SO009";}
					else if($key=array_search($curID,$OF004,true)){$SubOffID="SO010";}
					else if($key=array_search($curID,$OF005,true)){$SubOffID="SO011";}
					else if($key=array_search($curID,$OF006,true)){$SubOffID="SO012";}
					else if($key=array_search($curID,$OF007,true)){$SubOffID="SO013";}
					else if($key=array_search($curID,$OF008,true)){$SubOffID="SO014";}
					else if($key=array_search($curID,$OF009,true)){$SubOffID="SO015";}
					else if($key=array_search($curID,$OF010,true)){$SubOffID="SO016";}
					else if($key=array_search($curID,$OF011,true)){$SubOffID="SO017";}
					else if($key=array_search($curID,$OF012,true)){$SubOffID="SO018";}
					else if($key=array_search($curID,$OF013,true)){$SubOffID="SO008";}
					else if($key=array_search($curID,$OF014,true)){
						switch ($key){case 1:$SubOffID="SO023";break;case 2:$SubOffID="SO023";break;case 3:$SubOffID="SO025";break;case 4:$SubOffID="SO027";break;case 5:$SubOffID="SO027";break;case 6:$SubOffID="SO022";break;case 7:$SubOffID="SO022";break;case 8:$SubOffID="SO020";break;case 9:$SubOffID="SO021";break;case 10:$SubOffID="SO020";break;case 11:$SubOffID="SO020";break;case 12:$SubOffID="SO020";break;case 13:$SubOffID="SO020";break;case 14:$SubOffID="SO020";break;case 15:$SubOffID="SO026";break;case 16:$SubOffID="SO026";break;case 17:$SubOffID="SO024";break;case 18:$SubOffID="SO024";break;case 19:$SubOffID="SO025";break;case 20:$SubOffID="SO022";break;case 21:$SubOffID="SO023";break;case 22:$SubOffID="SO023";break;case 23:$SubOffID="SO025";break;case 24:$SubOffID="SO025";break;case 25:$SubOffID="SO027";break;case 26:$SubOffID="SO026";break;case 27:$SubOffID="SO026";break;case 28:$SubOffID="SO024";break;case 29:$SubOffID="SO020";break;case 30:$SubOffID="SO021";break;case 31:$SubOffID="SO021";break;default:$SubOffID="SO000";break;}
					}
					else if($key=array_search($curID,$OF015,true)){$SubOffID="SO029";}
					else if($key=array_search($curID,$OF016,true)){$SubOffID="SO030";}
					else if($key=array_search($curID,$OF017,true)){$SubOffID="SO031";}
					else if($key=array_search($curID,$OF018,true)){$SubOffID="SO032";}
					else if($key=array_search($curID,$OF019,true)){$SubOffID="SO033";}
					
					//echo "KEY: [$key] SO: [$SubOffID] - - - ";
					/* Update All Employee to this office information */ 
					$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE employment_status = 'A' AND dept_code = '".$curID."' ORDER BY pers_id ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					while($OffInfo=odbc_fetch_array($cur)){
						$EmpID=(strlen($OffInfo['pers_id'])==4)?"0".$OffInfo['pers_id']:$OffInfo['pers_id'];
						$SRecID="SR".$EmpID."001";
						
						$MySQLi->sqlQuery("UPDATE `pmis`.`tblempservicerecords` SET `MotherOfficeID`='".$SubOffID."', `AssignedOfficeID`='".$SubOffID."' WHERE `SRecID`='".$SRecID."' AND `EmpID`='".$EmpID."';",false);
					}
					
					
					$DoneIDs+=1;
					/* Check ID */
					if($DoneIDs>=$TotalOffices){
						$MSG="DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneIDs|$TotalOffices|$MSG";
					}
					else{
						/* Get Next ID */ 
						$cur=odbc_exec($cnx,"SELECT dept_code FROM department WHERE dept_code > '".$curID."' AND dept_code >= '".$StartID."' AND dept_code <= '".$EndID."' ORDER BY dept_code ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						$Office=odbc_fetch_array($cur);
						$nextID=trim($Office['dept_code']);
						$respTxt="0|$curID|$nextID|$DoneIDs|$TotalOffices|DONE\nProcessing $nextID . . . ";
					}
					
					break;
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>