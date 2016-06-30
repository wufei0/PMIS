<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD010'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):0;  /* 0 - VIEW, 1 - UPDATE */
	
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
?>

<center>
	<div style="width:890px;height:auto;overflow:auto"><br/><br/>
		<table class="i_table" style="width:850px;">
			<tr>
				<td class="i_table_header" rowspan="2" width="10px">#</td>
				<td class='i_table_header' rowspan='2' width='225'>Name of Organization<br /></td>
				<td class='i_table_header' rowspan='2'>Address</td>
				<td class='i_table_header' colspan='2'>Inclusive Dates of Att.</td>
				<td class='i_table_header' rowspan='2' width='50'>Number<br /> of Hours</td>
				<td class='i_table_header' rowspan='2' width='100'>Position/<br />Nature of Work</td>
				<td class="i_table_header" colspan="2" rowspan='2' width="40px">&nbsp;</td>
			</tr>
			<tr>
				<td class='i_table_header' width='70'>From</td>
				<td class='i_table_header' width='70'>To</td>
			</tr>
				
				<?php
					$records=Array();
					$result=$MySQLi -> sqlQuery("SELECT `VolOrgID`, `VolOrgName`,`VolOrgAddSt`,`VolOrgAddBrgy`, `VolOrgAddMun`, `VolOrgAddProv`,`VolOrgZipCode`,`VolOrgAddSt`, `VolOrgAddBrgy`, `VolOrgAddMun`, `VolOrgAddProv`, `VolOrgZipCode`, CONCAT_WS('\t',`VolOrgFromDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `VolOrgFromYear`, `VolOrgFromMonth`, `VolOrgFromDay`)), 1, 3)),`VolOrgFromYear`) AS VolOrgFrom, CONCAT_WS('-', `VolOrgFromYear`, `VolOrgFromMonth`, `VolOrgFromDay`) AS voFDate, CONCAT_WS('\t',`VolOrgToDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `VolOrgToYear`, `VolOrgToMonth`, `VolOrgToDay`)), 1, 3)),`VolOrgToYear`) AS VolOrgTo, CONCAT_WS('-', `VolOrgToYear`, `VolOrgToMonth`, `VolOrgToDay`) AS voTDate, `VolOrgHours`, `VolOrgDetails` FROM `tblempvoluntaryorg` WHERE `EmpID`='".$EmpID."' ORDER BY `VolOrgFromYear`, `VolOrgFromMonth` DESC;");
					$n=1;
					while($records=mysqli_fetch_array($result, MYSQLI_BOTH)) {
						$records['VolOrgAddSt']=(strlen($records['VolOrgAddSt'])>0)?$records['VolOrgAddSt']." ":"";
						$records['VolOrgAddBrgy']=(strlen($records['VolOrgAddBrgy'])>0)?$records['VolOrgAddBrgy'].", ":"";
						$records['VolOrgAddMun']=(strlen($records['VolOrgAddMun'])>0)?$records['VolOrgAddMun'].", ":"";
						$records['VolOrgAddProv']=(strlen($records['VolOrgAddProv'])>0)?$records['VolOrgAddProv']." ":"";
						$records['VolOrgZipCode']=(strlen($records['VolOrgZipCode'])>0)?$records['VolOrgZipCode']:"";
						$VolOrgAdd=$records['VolOrgAddSt'].$records['VolOrgAddBrgy'].$records['VolOrgAddMun'].$records['VolOrgAddProv'].$records['VolOrgZipCode'];
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						echo "<td align='center' valign='top' style='padding:4px 3px 3px 0px;'>".$n.".</td>";
						echo "<td class='i_table_body'>".$records['VolOrgName']."</td>";
						echo "<td class='i_table_body'>".$VolOrgAdd."</td>";
						echo "<td class='i_table_body' align='center'>".$records['VolOrgFrom']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['VolOrgTo']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['VolOrgHours']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['VolOrgDetails']."</td>";
						if($Authorization[0]&&$Authorization[2]){echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='showForm(\"vwor\",\"$EmpID\",\"".$records['VolOrgID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
						else{echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
						if($Authorization[0]&&$Authorization[3]){echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Delete' onClick='showForm(\"vwor\",\"$EmpID\",\"".$records['VolOrgID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
						else{echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
						echo "</tr>";
						$n+=1; 
					} 
					while($n<=1) {
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						for ($col=1;$col<=7;$col+=1) { echo "<td class='i_table_body'>&nbsp;</td>"; }
						echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
						echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
						echo "</tr>";
						$n += 1;
					}
					
					$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
					if($Authorization[0]&&$Authorization[2]){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('vwor','$EmpID','',0);";}
				?>
		</table>
	<table class="form" style="width:850px;">
		<tr>
			<td style="width:100%;text-align:left">
				<input type="button" value="Add" class="<?php echo $bAddClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bAddState; ?>/>
			</td>
		</tr>
	</table>
	</div>
</center>
<?php } ?>