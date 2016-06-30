<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	require_once $_SESSION['path'].'/lib/classes/Functions.php';
	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	$MySQLi=new MySQLClass();
	$fLeave=new LeaveFunctions();
	$fCOC=new COCFunctions();

	
	echo "1|$EmpID|";
	
	if($EmpID!='00000'){
?>

<center>
<div style="width:840px;height:auto;overflow:auto"><br/><br/>
	<table class="i_table" style="width:800px;">
		<tr>
			<td class="i_table_header" colspan="2">Inclusive Date</td>
			<td class="i_table_header" rowspan="2">LeaveType</td>
			<td class="i_table_header" width="50px" rowspan="2">Number<br />of Days</td>
			<td class="i_table_header" width="75px" rowspan="2">Date Filed</td>
			<td class="i_table_header" width="90px" rowspan="2">Status<br /></td>
			<td class="i_table_header" width="160px" rowspan="2">Remarks</td>
			<td class="i_table_header" width="40px" rowspan="2" colspan="4">&nbsp;</td>
		</tr>
		<tr>
			<td class="i_table_header" width="90">From</td>
			<td class="i_table_header" width="90">To</td>
		</tr>

			<?php
				$records=Array();
				$n=1;
				$result=$MySQLi->sqlQuery("SELECT `LivAppID`, `LeaveTypeID`, `LivAppDays`, DATE_FORMAT(`LivAppFiledDate`,'%b %d, %Y') AS LivAppFiledDate, DATE_FORMAT(`LivAppIncDateFrom`,'%b %d, %Y') AS LivAppIncDateFrom,`LivAppIncDayTimeFrom`,DATE_FORMAT(`LivAppIncDateTo`,'%b %d, %Y') AS LivAppIncDateTo, `LivAppIncDayTimeTo`,`LivAppStatus`, `LivAppNotedRemarks`, `LivAppCheckedRemarks`, `LivAppApprovedRemarks` FROM `tblempleaveapplications` WHERE `EmpID` = '".$EmpID."' ORDER BY DATE_FORMAT(`LivAppIncDateFrom`,'%Y%m%d') DESC;");
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$AvailableCredit=$fLeave->AvailableLeaveCredit($EmpID,$records['LeaveTypeID'],substr($records['LivAppIncDateFrom'], -4, 4));
					$LeaveNoPay=($records['LivAppDays']-$AvailableCredit)<0?0:($records['LivAppDays']-$AvailableCredit);
					$LeaveNoPay=$LeaveNoPay>0?"<font color=red>".number_format($LeaveNoPay,3)."</font>":number_format($LeaveNoPay,3);
					
					if($n%2==0){echo "<tr class='i_table_row_0'>";}
					else{echo "<tr class='i_table_row_1'>";}
					echo "<td align='center' valign='top' style='padding:4px 3px 3px 0px;'>".$records['LivAppIncDateFrom']." ".$records['LivAppIncDayTimeFrom']."</td>";
					echo "<td class='i_table_body' align='center'>".$records['LivAppIncDateTo']." ".$records['LivAppIncDayTimeTo']."</td>";
					$leavetype=$MySQLi->GetArray("SELECT `LeaveTypeDesc` FROM `tblleavetypes` WHERE `LeaveTypeID`='".$records['LeaveTypeID']."';");
					if($records['LeaveTypeID']=="LT08"){$leavetype['LeaveTypeDesc']="CTO";}
					echo "<td class='i_table_body'>".$leavetype['LeaveTypeDesc']."</td>";
					echo "<td class='i_table_body' align='right'>".number_format($records['LivAppDays'],2)."</td>";
					echo "<td class='i_table_body' align='center'>".$records['LivAppFiledDate']."</td>";
					switch ($records['LivAppStatus']){case '0':$leavestatus="NEW";break;case '1':$leavestatus="POSTED";break;case '2':$leavestatus="NOTED";break;case '3':$leavestatus="CHECKED";break;case '4':$leavestatus="APPROVED";break;default: $leavestatus="DISAPPROVED";break;}
					echo "<td class='i_table_body' align='center'>$leavestatus</td>";
					
					$LivAppRemarks=strlen($records['LivAppNotedRemarks'])>0?$records['LivAppNotedRemarks']."<br/>":"";
					$LivAppRemarks.=strlen($records['LivAppCheckedRemarks'])>0?$records['LivAppCheckedRemarks']."<br/>":"";
					$LivAppRemarks.=strlen($records['LivAppApprovedRemarks'])>0?$records['LivAppApprovedRemarks']:"";
					
					echo "<td class='i_table_body'>".$LivAppRemarks."</td>";
					
					if((($Authorization[0]&&$Authorization[4])||(($_SESSION['user']==$EmpID)&&$Authorization[4])||($_SESSION['usergroup']=="USRGRP006"))&&($records['LivAppStatus']=='0')){echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Post' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"".$EmpID."\",\"lv\",\"".$records['LivAppID']."\",1,\"\");},\"NO\":function(){closeDialogWindow(\"d_confirm\");}}});showConfirmation(\"Confirm to post this application.<br/>Modifying this application later will not be allowed.<br/><br/>Leave Days Applied: ".number_format($records['LivAppDays'],3)."<br/>Available Leave Credits: ".number_format($AvailableCredit,3)."<br/>Leave without pay: ".$LeaveNoPay."<br/><br/>Continue?\");'><span class='ui-icon ui-icon-notice'></span></li></ul></td>";}
					else{echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-notice'></span></li></ul></td>";}
					
					if((($Authorization[0]&&$Authorization[2])||(($_SESSION['user']==$EmpID)&&$Authorization[2])||($_SESSION['usergroup']=="USRGRP006"))&&($records['LivAppStatus']=='0')){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='showForm(\"leav\",\"$EmpID\",\"".$records['LivAppID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					
					if((($Authorization[0]&&$Authorization[3])||(($_SESSION['user']==$EmpID)&&$Authorization[3])||($_SESSION['usergroup']=="USRGRP006"))&&($records['LivAppStatus']=='0')){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Delete' onClick='showForm(\"leav\",\"$EmpID\",\"".$records['LivAppID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
					else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
					
					if((($Authorization[0]&&$Authorization[1])||(($_SESSION['user']==$EmpID)&&$Authorization[1]))&&($records['LivAppStatus']=='4')){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Print' onClick='window.open(\"reports/rpt_lv.php?id=".$records['LivAppID']."\",\"mywindow\",\"width=800,height=600\");'><span class='ui-icon ui-icon-print'></span></li></ul></td>";}
					else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-print'></span></li></ul></td>";}
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
				
				$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
				if(($Authorization[0]&&$Authorization[2])||(($_SESSION['user']==$EmpID)&&$Authorization[2])||($_SESSION['usergroup']=="USRGRP006")){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('leav','$EmpID','',0);";}
			?>
	</table>
	<table class="form" style="width:800px;">
		<tr>
			<td style="width:100%;text-align:left">
				<input type="button" value="New Leave Application" class="<?php echo $bAddClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bAddState; ?>/>
				<input type="button" value="Leave Credits" class="<?php echo $bAddClass; ?>" onClick="viewRecordPLCT('<?php echo $EmpID; ?>','L'); return false;" <?php echo $bAddState; ?>/>
			</td>
		</tr>
	</table>
</div>
</center>
<?php } ?> 


