<?php
	session_start();
	define('ROOT_PATH', $_SESSION['path']);
	
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';
	
	if ($_SESSION['fingerprint']==md5($_SESSION['user']." ".$_SERVER['HTTP_USER_AGENT']." ".$_SERVER['REMOTE_ADDR']." ".$_SESSION['fprinttime'])){
		/* Check user activity within the last ? minutes*/
		$Authentication=new Authentication();
		$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));
		if($ActiveStatus[0]==1){$Authentication->setUserActiveTime($_SESSION['user'],$_SESSION['fingerprint']);}
		else{echo "-1|00000|".$ActiveStatus[1];exit();}
		/* Check user access to this module */
		
		
	}
	else{
		echo "-1|00000|ERROR 401:<br/>You are not authorized to access this section.<br/>Please login.";
		exit();
	}

	//Get GET Values
	$mod=isset($_GET['mod'])?mysql_escape_string(trim(strip_tags($_GET['mod']))):"x";
	$opt=isset($_GET['opt'])?mysql_escape_string(trim(strip_tags($_GET['opt']))):"SubOffCode";
	$key=isset($_GET['key'])?strtoupper(mysql_escape_string(trim(strip_tags($_GET['key'])))):"A";
	
	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);
	
	if ($mod=="srch") {
		$sql = "SELECT `SubOffID`, `SubOffCode`, `SubOffName` FROM `tblsuboffices` WHERE `".$opt."` 	LIKE '".$key."%' ORDER BY `".$opt."` ASC;";
		
		$result = $MySQLi -> sqlQuery($sql);
		if(mysql_num_rows($result)>0) {
			$lines = "<table class='search_body' id='ajax_search_result'>";
			$lines .= "<tbody>";
			$ison = true;
			while($records = mysql_fetch_row($result)) {
				if ($ison) {
					$lines .= "<tr id='r_".$records[0]."' class='search_result_row_0' onClick='selectOffice(\"".$records[0]."\"); return false'>";
					$lines .= "<td class='search_result' width='40px'>".$records[1]."</td>";
					$lines .= "<td class='search_result'>".$records[2]."</td>";
				}
				else	{
					$lines .= "<tr id='r_".$records[0]."' class='search_result_row_1' onClick='selectOffice(\"".$records[0]."\"); return false'>";	
					$lines .= "<td class='search_result' width='40px'>".$records[1]."</td>";
					$lines .= "<td class='search_result'>".$records[2]."</td>";
				}
				
				$lines .= "</tr>";
				$ison = !$ison;
			}
			$lines .= "</tbody>";
			$lines .= "</table><span id='r_00000'></span>";
			echo $lines;
		}
		else {
			echo "<table width='100%' height='100%'><tr valign='center'>
					<td align='center'><i>No record found.</i></td>
					</tr></table>";
		}
	}
	
	if ($mod=="get") {
		$lines = "1";
		$records = Array("SOOF00000","","","","","");
		$sql = "SELECT `SubOffID`, `SubOffCode`, `SubOffName`, `SubOffAddSt`, `SubOffAddBrgy`, `SubOffAddMun`, `SubOffAddProv`, `SubOffHead` FROM `tblsuboffices` WHERE `SubOffID` = '$key';"; 
		$result = $MySQLi -> sqlQuery($sql);
		$records = mysql_fetch_row($result);
		$records[1] = (!isset($records[1])) ? "SOOF00000" : $records[1];
		$records[2] = ($records[2]=="") ? "" : $records[2];
		$records[3] = ($records[3]=="") ? "" : $records[3]." ";
		$records[4] = ($records[4]=="") ? "" : $records[4].", ";
		$records[5] = ($records[5]=="") ? "" : $records[5].", ";
		$records[6] = ($records[6]=="") ? "" : $records[6];
		$OICID = ($records[7]=="") ? "" : $records[7];
		$SubOffAdd = $records[3].$records[4].$records[5].$records[6];
		$lines .= "|".$records[0]."|".$records[1]."|".$records[2]."|".$SubOffAdd;
		
		$records = Array("");
		$sql = "SELECT `tblemppersonalinfo`.`EmpLName`, `tblemppersonalinfo`.`EmpFName`, `tblemppersonalinfo`.`EmpMName` FROM (`tblsuboffices` JOIN `tblemppersonalinfo` ON `tblsuboffices`.`SubOffHead` = `tblemppersonalinfo`.`EmpID`) WHERE `tblsuboffices`.`SubOffHead` = '$OICID' LIMIT 1;";
		$result = $MySQLi -> sqlQuery($sql);
		$records = mysql_fetch_row($result);
		$records[0] = (!isset($records[0])) ? "" : $records[0].", ";
		$records[1] = (!isset($records[1])) ? "" : $records[1]." ";
		$records[2] = (!isset($records[2])) ? "" : substr($records[2], 0, 1).".";
		$OICName = $records[0].$records[1].$records[2];
		
		$records = Array("");
		$sql = "SELECT `tblpositions`.`PosCode` FROM (`tblappointments` JOIN `tblpositions` ON `tblappointments`.`PosID` = `tblpositions`.`PosID`) WHERE `tblappointments`.`EmpID` = '$OICID' LIMIT 1;";
		$result = $MySQLi -> sqlQuery($sql);
		$records = mysql_fetch_row($result);
		$OICName = ($records[0]=="") ? $OICName : $OICName." (".$records[0].")";
		$lines .= "|".$OICName;
		
		echo $lines;
	}
?>