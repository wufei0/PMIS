<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD021'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
?>

<center><br/><br/>
	<table class="i_table" style="width:800px;">
		<tr>
			<td class="i_table_header" width="90px">Locator Slip Number</td>
			<td class="i_table_header" width="75px">Date</td>
			<td class="i_table_header">Destination<br /></td>
			<td class="i_table_header">Purpose<br /></td>
			<td class="i_table_header" width="50px">Time<br/>Out<br /></td>
			<td class="i_table_header" width="50px">Time<br/>In<br /></td>
			<td class="i_table_header" width="85px">Status<br /></td>
			<td class="i_table_header" width="90px">Remarks</td>
			<td class="i_table_header" colspan="2" width="40px">&nbsp;</td>
		</tr>

			<?php
				$records=Array();
				$n=1;
				$EmpPL=$MySQLi->sqlQuery("SELECT `PLID` FROM `tblemplocator` WHERE `EmpID`='$EmpID';");
				while($travels=mysqli_fetch_array($EmpPL, MYSQLI_BOTH)){
					$result=$MySQLi->sqlQuery("SELECT `PLID`, CONCAT_WS(' ',`PLDateDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-',`PLDateYear`,`PLDateMonth`,`PLDateDay`)), 1, 3)),`PLDateYear`) AS PLDate, `PLDestination`, `PLPurpose`, `PLTimeOUT`, `PLTimeIN`, `PLApprovedBy`, `PLStatus`, `PLRemarks` FROM `tblpersonnellocators` WHERE `PLID` = '".$travels['PLID']."' ORDER BY `PLDateYear` DESC, `PLDateMonth` DESC,`PLDateDay` DESC ;");
					while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						echo "<td align='center' style='padding:4px 3px 3px 0px;'>".$records['PLID']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['PLDate']."</td>";
						echo "<td class='i_table_body'>".$records['PLDestination']."</td>";
						echo "<td class='i_table_body'>".$records['PLPurpose']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['PLTimeOUT']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['PLTimeIN']."</td>";
						echo "<td class='i_table_body'>".$records['PLStatus']."</td>";
						echo "<td class='i_table_body'>".$records['PLRemarks']."</td>";
						echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='formPersonnelLocator(\"$EmpID\",\"".$records['PLID']."\",\"1\");'><span class='ui-icon ui-icon-print'></span></li></ul></td>";
						echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='formPersonnelLocator(\"$EmpID\",\"".$records['PLID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
						echo "</tr>";
						$n+=1; 
					}
				}
				while($n<=1){
					if($n%2==0){echo "<tr class='i_table_row_0'>";}
					else{echo "<tr class='i_table_row_1'>";}
					for($col=1;$col<=8;$col+=1){echo "<td class='i_table_body'>&nbsp;</td>";}
					$n+=1;
					echo "</tr>";
				}
				
				$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
				if(($Authorization[0]&&$Authorization[2])||(($_SESSION['user']==$EmpID)&&$Authorization[2])||($_SESSION['usergroup']=="USRGRP006")){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('educ','$EmpID','',0);";}
				
			?>
	</table>
	<table class="form" style="width:800px;">
		<tr>
			<td style="width:100%;text-align:left">
				<input type="button" value="New PLS" class="<?php echo $bAddClass; ?>" onClick="showForm('ppls','<?php echo $EmpID; ?>','',0);" <?php echo $bAddState; ?>/>
			</td>
		</tr>
	</table>
</center>
<?php } ?>


