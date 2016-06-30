<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD020'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$COCID=isset($_POST['ids'])?trim(strip_tags($_POST['ids'])):'0';
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	$MySQLi=new MySQLClass();
	
	echo "1|$EmpID|";
	
	if($EmpID!='00000'){
?>

<center>
<div style="width:640px;height:auto;overflow:auto"><br/><br/>
	<table class="i_table" style="width:600px;">
		<tr>
			<td class="i_table_header" width="75px">Date Earned</td>
			<td class="i_table_header" width="60px">Hours Earned</td>
			<td class="i_table_header" width="60px">Hours Remaining</td>
			<td class="i_table_header" width="75px">Date Expired</td>
			<td class="i_table_header" width="75px">Status</td>
			<td class="i_table_header">Remarks</td>
			<td class="i_table_header" width="40px"colspan="3">&nbsp;</td>
		</tr>

			<?php
				$records=Array();
				$n=1;
				$result=$MySQLi->sqlQuery("SELECT `tblempcocs`.`COCID`, `tblempcocs`.`EmpID`, `tblempcocs`.`COCEarnedDate` AS COCEarnedTime, DATE_FORMAT(`tblempcocs`.`COCEarnedDate`,'%b %d, %Y') AS COCEarnedDate, `tblempcocs`.`COCEarnedHours`, `tblempcocs`.`COCRemainingHours`, DATE_FORMAT(`tblempcocs`.`COCExpireDate`,'%b %d, %Y') AS COCExpireDate, `tblempcocs`.`COCStatus`, `tblempcocs`.`COCNotes`, `tblempcocs`.`RECORD_TIME` FROM `tblempcocs` WHERE `tblempcocs`.`EmpID`='".$EmpID."' ORDER BY `COCEarnedTime`;");
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					// if($n%2==0){echo "<tr class='i_table_row_0'>";}
					// else{echo "<tr class='i_table_row_1'>";}
					echo "<tr class='i_table_row_".(($n%2==0)?"0":"1")." ".(($records['COCID']==$COCID)?"hlight":"")."'>";
					echo "<td align='center' valign='top' style='padding:4px 3px 3px 0px;'>".$records['COCEarnedDate']."</td>";
					echo "<td class='i_table_body' align='right'>".number_format($records['COCEarnedHours'],3)."</td>";
					echo "<td class='i_table_body' align='right'>".number_format($records['COCRemainingHours'],3)."</td>";
					echo "<td class='i_table_body' align='center'>".$records['COCExpireDate']."</td>";
					switch ($records['COCStatus']){case 0:$COCStatus="NEW";break;case 1:$COCStatus="POSTED";break;case 2:$COCStatus="FILED";break;case 3:$COCStatus="CONFIRMED";break;case 4:$COCStatus="APPROVED";break;case -1:$COCStatus="DISAPPROVED";break;default: $COCStatus="<font color='#CC3333'>EXPIRED</font>";break;}
					echo "<td class='i_table_body' align='center'>".$COCStatus."</td>";
					echo "<td class='i_table_body' align='left'>".$records['COCNotes']."</td>";
					
					
					
					if($Authorization[4]&&($records['COCStatus']==0)){echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Post' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"".$EmpID."\",\"cc\",\"".$records['COCID']."\",1,\"\");},\"NO\":function(){closeDialogWindow(\"d_confirm\");}}});showConfirmation(\"Confirm to post this COC computation.<br/>Modifying this record after posting will be impossible.<br/>Continue?\");'><span class='ui-icon ui-icon-notice'></span></li></ul></td>";}
					else if($Authorization[5]&&($records['COCStatus']==1)){echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Note' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"".$EmpID."\",\"cc\",\"".$records['COCID']."\",2,\"\");},\"NO\":function(){closeDialogWindow(\"d_confirm\");}}});showConfirmation(\"Note this COC computation?\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";}
					else if($Authorization[6]&&($records['COCStatus']==2)){echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Check' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"".$EmpID."\",\"cc\",\"".$records['COCID']."\",3,\"\");},\"NO\":function(){closeDialogWindow(\"d_confirm\");}}});showConfirmation(\"Confirm this COC computation?\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";}
					else if($Authorization[7]&&($records['COCStatus']==3)){echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Approve' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"".$EmpID."\",\"cc\",\"".$records['COCID']."\",4,\"\");},\"NO\":function(){closeDialogWindow(\"d_confirm\");}}});showConfirmation(\"Approve this COC computation?\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";}
					else{echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-notice'></span></li></ul></td>";}
					
					if($Authorization[0]&&$Authorization[2]){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='showForm(\"pcoc\",\"$EmpID\",\"".$records['COCID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					
					if($Authorization[0]&&$Authorization[3]){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Delete' onClick='showForm(\"pcoc\",\"$EmpID\",\"".$records['COCID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
					else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}

					echo "</tr>";
					$n+=1; 
				}
				
				while($n<=1){
					if($n%2==0){echo "<tr class='i_table_row_0'>";}
					else{echo "<tr class='i_table_row_1'>";}
					for($col=1;$col<=9;$col+=1){echo "<td class='i_table_body'>&nbsp;</td>";}
					$n+=1;
					echo "</tr>";
				}
				$EarnedHrs=number_format($MySQLi->GetArray("SELECT SUM(`COCEarnedHours`) AS EarnedHrs FROM `tblempcocs` WHERE `EmpID`='$EmpID' AND (`COCStatus`='4' OR `COCStatus`='-2');")['EarnedHrs'],3);
				$RemainingHrs=number_format($MySQLi->GetArray("SELECT SUM(`COCRemainingHours`) AS RemainingHrs FROM `tblempcocs` WHERE `EmpID`='$EmpID' AND `COCStatus`='4';")['RemainingHrs'],3);
				$AvailableHrs=number_format($MySQLi->GetArray("SELECT SUM(`COCRemainingHours`) AS AvailableHrs FROM `tblempcocs` WHERE `EmpID`='$EmpID' AND `COCExpireDate`>NOW() AND `COCStatus`='4';")['AvailableHrs'],3);
				echo "<tr><td align='right' style='border-top:1px solid #6D84B4;'>TOTAL:</td><td align='right' style='border-top:1px solid #6D84B4;'>$EarnedHrs</td><td align='right' style='border-top:1px solid #6D84B4;'>$RemainingHrs</td><td align='right' colspan='2' style='border-top:1px solid #6D84B4;'>AVAILABLE:</td><td colspan='4' style='border-top:1px solid #6D84B4;'>$AvailableHrs</td></tr>";
				$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
				if(($Authorization[0]&&$Authorization[2])||(($_SESSION['user']==$EmpID)&&$Authorization[2])||($_SESSION['usergroup']=="USRGRP006")){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('pcoc','$EmpID','',0);";}
			?>
	</table>
	<table class="form" style="width:600px;">
		<tr>
			<td style="width:100%;text-align:left">
				<input type="button" value="New COC" class="<?php echo $bAddClass; ?>" onClick="showForm('pcoc','<?php echo $EmpID; ?>','',0);" <?php echo $bAddState; ?>/>
				<input type="button" value="COCs" class="<?php echo $bAddClass; ?>" onClick="viewRecordPLCT('<?php echo $EmpID; ?>','C','','LT08'); return false;" <?php echo $bAddState; ?>/>
			</td>
		</tr>
	</table>
</div>
</center>
<?php } ?> 


