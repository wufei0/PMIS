<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD003'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	
	$MySQLi=new MySQLClass();
	
	/* Get GET Values */
	$mod=isset($_GET['mod'])?trim(strip_tags($_GET['mod'])):'x';
	$opt=isset($_GET['opt'])?trim(strip_tags($_GET['opt'])):'EmpLName';
	$key=isset($_GET['key'])?strtoupper(trim(strip_tags($_GET['key']))):"A";
	
	$thisOffice="";
	if($_SESSION['usergroup']=="USRGRP004"){ /* For Administrative officer user, get Office */
		$sql="SELECT `AssignedOfficeID` FROM `tblempservicerecords` WHERE `EmpID`='".$_SESSION['user']."' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment` = 1;";
		$SRecOff=$MySQLi->GetArray($sql);
		$AssignedOfficeID=$SRecOff['AssignedOfficeID'];
		$thisOffice=" AND `EmpID` IN (SELECT `EmpID` FROM `tblempservicerecords` WHERE `AssignedOfficeID`='$AssignedOfficeID' AND `SRecIsGov`='YES' AND `SRecCurrentAppointment`=1)";
	}
	
	if ($mod=="srch"){
		$sql ="SELECT `tblemppersonalinfo`.`EmpID`, `tblemppersonalinfo`.`UserGroupID`, `tbluserlogs`.`UsrLogTimeIN`, `tbluserlogs`.`UsrLogTimeActive`, `tbluserlogs`.`UsrLogTimeOUT`, `tbluserlogs`.`UsrLogIPAddress`, `tbluserlogs`.`UsrLogHostName`,"; 
		$sql.="CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` JOIN `tbluserlogs` ON `tblemppersonalinfo`.`EmpID`=`tbluserlogs`.`UserID`";
		$sql.="GROUP BY `tblemppersonalinfo`.`EmpID` ORDER BY `UsrLogTimeActive` DESC;"; 
		
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql)>0){
			$lines="<table style='width:420px;border-spacing:0px;border:0px solid #6D84B4;'";
			$lines.="<tbody>";
			$ison=true;
			while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				if($ison){$lines.="<tr id='r_".$records['EmpID']."' class='search_result_row_0'>";}
				else{$lines.="<tr class='search_result_row_1'>";}
				$lines.="<td class='search_result' align='center' width='50px'>".$records['EmpID']."</td>";
				$lines.="<td class='search_result' align='center' width='80px'>".$records['UserGroupID']."</td>";
				$lines.="<td class='search_result'>".$records['EmpName']."</td>";
				$iSecs=floatval(time())-floatval($records['UsrLogTimeActive']);
				$iMins=floor($iSecs/60);
				$iHrs=floor($iMins/60);
				if($records['UsrLogTimeActive']==0){$Status="OFFLINE";}
				else if($iSecs>(60*10)){$Status="IDLE ".$iHrs." hrs ".($iMins%60)." mins";}
				else{$Status="ONLINE";}
				$lines.="<td class='search_result' align='center' width='80px'>".$Status."</td>";
				
				$lines.="</tr>";
				$ison=!$ison;
			}
			$lines.="</tbody>";
			$lines.="</table>";
			echo "1|".$_SESSION['user']."|".$lines;
		}
		else{
			echo "1|00000|<table width='100%' height='100%'><tr valign='center'><td align='center'><i>No record found.</i></td></tr></table>";
		}
	}

?>