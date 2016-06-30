<?php
	session_start();
	define('ROOT_PATH', $_SESSION['path']);
	
	
	
		
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	


	/* Get GET Values */
	$mod=isset($_GET['mod'])?trim(strip_tags($_GET['mod'])):"srch";
	$UserGroupName=isset($_GET['ugn'])?trim(strip_tags($_GET['ugn'])):"%";

	$MySQLi=new MySQLClass();
	
	if ($mod=="srch"){
		$sql ="SELECT * FROM `tblsystemusergroups` WHERE `UserGroupName` LIKE '$UserGroupName%'"; 
		
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql)>0) {
			$lines ="<table class='usr_search_body'>";
			$lines.="<tbody>";
			$ison=true;
			while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				if ($ison){$lines.="<tr id='r_".$records[0]."' class='search_result_row_0' onClick='showUserGroup(\"".$records[0]."\"); return false'>";}
				else{$lines.="<tr id='r_".$records[0]."' class='search_result_row_1' onClick='showUserGroup(\"".$records[0]."\"); return false'>";}
				$lines.="<td class='search_result' align='center'  width='40px'>".$records[1]."</td>";
				$lines.="<td class='search_result'>".$records[2]."</td>";
				$lines.="</tr>";
				$ison=!$ison;
			}
			$lines.="</tbody>";
			$lines.="</table><span id='r_00000'></span>";
			echo $lines;
		}
		else{
			echo "<table width='100%' height='100%'><tr valign='center'><td align='center'><i>No record found.</i></td></tr></table>";
		}
	}
?>