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
	$EndID=isset($_POST['lid'])?trim(strip_tags($_POST['lid'])):'ZZZZZ';
	$st=isset($_POST['st'])?trim(strip_tags($_POST['st'])):-1;
	$curID=isset($_POST['cid'])?trim(strip_tags($_POST['cid'])):"00000";
	$nextID=isset($_POST['nid'])?trim(strip_tags($_POST['nid'])):"00000";
	$DoneEduc=isset($_POST['did'])?trim(strip_tags($_POST['did'])):0;
	$TotalRecords=isset($_POST['tid'])?trim(strip_tags($_POST['tid'])):0;
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
	
	switch ($st){
		case -1	:
					$cur=odbc_exec($cnx,"SELECT count(*) AS TotalRecords FROM employee_educational_backgrnd WHERE pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Education=odbc_fetch_array($cur);
					$TotalRecords=$Education['TotalRecords'];
					
					$sql="SELECT pers_id FROM personal WHERE employment_status = '".$EmploymentStatus."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC";
					$cur=odbc_exec($cnx,$sql);
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
					$Personal=odbc_fetch_array($cur);
					$curID=$nextID=$Personal['pers_id'];
					$DoneEduc=0;
					
					$MSG="\nMigrating of all EDUCATIONAL BACKGROUND from Sybase database to MySQL.\n\nProcessing $curID . . . ";
					$respTxt="0|$curID|$nextID|$DoneEduc|$TotalRecords|$MSG";
					break;
					
		case 0	:
					$curID=$nextID;
					$MySQLi=new MySQLClass();
					$cur=odbc_exec($cnx,"SELECT count(*) AS IDsOnThisEducation FROM employee_educational_backgrnd WHERE pers_id = '".$curID."'");
					if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
					$Education=odbc_fetch_array($cur);
					$IDsOnThisEducation=$Education['IDsOnThisEducation'];
					
					if($IDsOnThisEducation>0){
						/* Migrate Education Information */
						$cur=odbc_exec($cnx,"SELECT * FROM employee_educational_backgrnd WHERE pers_id = '".$curID."'");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						
						while($EducBgInfo=odbc_fetch_array($cur)){
							$EmpID=$curID;
							/* Get New EducBgID */
							$NewEducBgID="ED".$EmpID."01";
							$count=1;
							while($records=$MySQLi->GetArray("SELECT `EducBgID` FROM `tblempeducbg` WHERE `EducBgID`='$NewEducBgID';")) {
								$count+=1;
								$ccc=($count>9)?$count:"0".$count;
								$NewEducBgID="ED".$EmpID.$ccc;
							} $EducBgID=$NewEducBgID;
							/* Get Educational Level Info */
							$edu=odbc_exec($cnx,"SELECT * FROM educational_level WHERE educ_lvl_code = ".$EducBgInfo['educ_lvl_code']." ");
							if(!$edu){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
							$EducLvl=odbc_fetch_array($edu);
							
							$EducLvl['educational_level']
							
							$EducLvlID="";
							$EducSchoolName="";
							$EducCourse="";
							$EducYrGrad="";
							$EducGradeLvlUnits="";
							$EducIncAttDateFromDay="";
							$EducIncAttDateFromMonth="";
							$EducIncAttDateFromYear="";
							$EducIncAttDateToDay="";
							$EducIncAttDateToMonth="";
							$EducIncAttDateToYear="";
							$EducAwards="";
							
							$EducBgDesc=strtoupper(mysql_escape_string(trim($EducInfo['edugibility'])));
							$EducBgRating=($EducBgInfo['grade']<1)?$EducBgInfo['grade']*100:$EducBgInfo['grade'];
							$EducBgExamDay="";
							$EducBgExamMonth="";
							$EducBgExamYear="";
							if(strpbrk($EducBgInfo['date_exam'],' ')){
								$EDate=explode(" ",$EducBgInfo['date_exam']);
								if(strpbrk($EDate[0],'-')){
									$Examdate=explode("-",$EDate[0]);
									$EducBgExamDay=$Examdate[2];
									$EducBgExamMonth=$Examdate[1];
									$EducBgExamYear=$Examdate[0];
								}
							}
							$EducBgExamPlace=strtoupper(mysql_escape_string(trim($EducBgInfo['place'])));
							$EducBgLicNum="";
							$EducBgLicReleaseDay="";
							$EducBgLicReleaseMonth="";
							$EducBgLicReleaseYear="";
							$EducBgHighest=(strtoupper(mysql_escape_string(trim($EducBgInfo['highest_edug'])))=='Y')?'1':'0';
							
							$MySQLi->sqlQuery("DELETE FROM `tblempeducbg` WHERE `EmpID`='".$EmpID."'",false);
							$sql="INSERT INTO `tblempeducbg` (`EducBgID`,`EmpID`,`EducLvlID`,`EducSchoolName`,`EducCourse`,`EducYrGrad`,`EducGradeLvlUnits`,`EducIncAttDateFromDay`,`EducIncAttDateFromMonth`,`EducIncAttDateFromYear`,`EducIncAttDateToDay`,`EducIncAttDateToMonth`,`EducIncAttDateToYear`,`EducAwards`,`RECORD_TIME`) VALUES ('".$EducBgID."','".$EmpID."','".$EducLvlID."','".$EducSchoolName."','".$EducCourse."','".$EducYrGrad."','".$EducGradeLvlUnits."','".$EducIncAttDateFromDay."','".$EducIncAttDateFromMonth."','".$EducIncAttDateFromYear."','".$EducIncAttDateToDay."','".$EducIncAttDateToMonth."','".$EducIncAttDateToYear."','".$EducAwards."',NOW());";
							$MySQLi->sqlQuery($sql,false);
						}
						$DoneEduc+=1;
					}
					
					/* Check ID */
					if($DoneEduc>=$TotalRecords){
						$MSG="DONE\n\nMigration Complete.";
						$respTxt="1|$curID|$nextID|$DoneEduc|$TotalRecords|$MSG";
					}
					else{
						/* Get Next ID */ 
						$cur=odbc_exec($cnx,"SELECT pers_id FROM personal WHERE pers_id > '".$curID."' AND pers_id >= '".$StartID."' AND pers_id <= '".$EndID."' ORDER BY pers_id ASC");
						if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned).";odbc_close($cnx);exit();}
						if(odbc_fetch_row($cur)){$nextID=odbc_result($cur,1);}
						else{$nextID="";}
						
						if($nextID!=""){
							$respTxt="0|$curID|$nextID|$DoneEduc|$TotalRecords|DONE\nProcessing $nextID . . . ";
						}
						else{
							$MSG="DONE\n\nThere was an error getting the next ID.";
							$respTxt="1|$curID|$nextID|$DoneEduc|$TotalRecords|$MSG";
						}
					}
					
					break;
					
		default	:	echo "-1|0|0|0|0|ERROR 49:~!!!";exit();
	}
	
  odbc_close($cnx);
	echo $respTxt;


?>