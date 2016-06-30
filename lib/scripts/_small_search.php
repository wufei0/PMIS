<?php
	session_start();
		
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD003'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	//Get GET Values
	$sWin=isset($_GET['sWin'])?trim(strip_tags($_GET['sWin'])):"sPosition";
	$key=isset($_GET['key'])?strtoupper(trim(strip_tags($_GET['key']))):"%";
	
	$MySQLi=new MySQLClass();
	
	if(($sWin=="sEmployee")||($sWin=="sPersonnel")){
		$sql="SELECT `s_personnel`.`EmpID`, `s_personnel`.`EmpName` AS EmpName, `s_personnel`.`EmpID` FROM `s_personnel` WHERE `s_personnel`.`EmpID` > '1000' AND `s_personnel`.`EmpID` < '99999' AND `FindMe` LIKE '%".$key."%'";
		if(is_int($key)){ $sql.=" ORDER BY `EmpID` ASC;";}
		else{$sql.=" ORDER BY `EmpName` ASC;";}
	}
	else if($sWin=="sPosition"){$sql="SELECT `PosID`,`PosDesc`,`PosID` FROM `tblpositions` WHERE `PosDesc` LIKE '%".$key."%' ORDER BY 'PosDesc' ASC;";}
	else if(($sWin=="sMotherOffice")||($sWin=="sAssignedOffice")){$sql="SELECT `SubOffID`, `SubOffName`, `SubOffCode` FROM `tblsuboffices` WHERE `SubOffName` LIKE '%".$key."%' ORDER BY `SubOffName` ASC;";}

	$result=$MySQLi->sqlQuery($sql);
	if($MySQLi->NumberOfRows($sql)>0) {
		$lines="<table class='search_body' id='ajax_search_result'>";
		$lines.="<tbody>";
		$ison=true;
		while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
			if ($ison) {
				$lines.="<tr id='r_".$records[0]."' class='search_result_row_0' onClick='selectThis(\"".$records[0]."\",\"".$records[1]."\"); return false'>";
				$lines.="<td class='search_result' width='40px'>".$records[2]."</td>";
				$lines.="<td class='search_result'>".$records[1]."</td>";
			}
			else	{
				$lines.="<tr id='r_".$records[0]."' class='search_result_row_1' onClick='selectThis(\"".$records[0]."\",\"".$records[1]."\"); return false'>";	
				$lines.="<td class='search_result' width='40px'>".$records[2]."</td>";
				$lines.="<td class='search_result'>".$records[1]."</td>";
			}
			
			$lines.="</tr>";
			$ison=!$ison;
		}
		// $lines.="<tr><td class='search_result_row_0' colspan='2'>".$sql." --- ".$_GET['key']."</td></tr>";
		$lines.="</tbody>";
		$lines.="</table><span id='r_00000'></span>";
		echo $lines;
	}
	else {
		echo "<table width='100%' height='100%'><tr valign='center'>
				<td align='center'><i>No record found.</i></td>
				</tr></table>";
	}
	
	
?>