<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD011'));
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
				<td class='i_table_header' rowspan='2' width='10'>#</td>
				<td class='i_table_header' rowspan='2' width='225'>Title of Seminar/Conference/Workshop/Short Courses</td>
				<td class='i_table_header' colspan='2'>Inclusive Dates of Att.</td>
				<td class='i_table_header' rowspan='2' width='75'>Number<br /> of Hours</td>
				<td class='i_table_header' rowspan='2' width='175'>Conducted/Sponsored By</td>
				<td class="i_table_header" colspan="2" rowspan='2' width="40px">&nbsp;</td>
			</tr>
			<tr>
				<td class='i_table_header' width='75'>From</td>
				<td class='i_table_header' width='75'>To</td>
			</tr>
				
				<?php
					$records=Array();
					$result=$MySQLi -> sqlQuery("SELECT `TrainID`, `TrainDesc`, CONCAT_WS('-',`TrainFromDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `TrainFromYear`, `TrainFromMonth`, `TrainFromDay`)), 1, 3)),`TrainFromYear`) AS TrainFrom, CONCAT_WS('-', `TrainFromYear`, `TrainFromMonth`, `TrainFromDay`) AS trFDate, CONCAT_WS('-',`TrainToDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `TrainToYear`, `TrainToMonth`, `TrainToDay`)), 1, 3)),`TrainToYear`) AS TrainTo, CONCAT_WS('-', `TrainToYear`, `TrainToMonth`, `TrainToDay`) AS trTDate, `TrainHours`, `TrainSponsor` FROM `tblemptrainings` WHERE `EmpID`='".$EmpID."' ORDER BY `TrainFromYear` DESC, `TrainFromMonth` DESC;");
					$n=1;$td=6;
					while($records=mysqli_fetch_array($result, MYSQLI_BOTH)) {
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						echo "<td align='center' valign='top' style='padding:4px 3px 3px 0px;'>".$n.".</td>";
						echo "<td class='i_table_body'>".$records['TrainDesc']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['TrainFrom']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['TrainTo']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['TrainHours']."</td>";
						echo "<td class='i_table_body'>".$records['TrainSponsor']."</td>";
						if($Authorization[0]&&$Authorization[2]){echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='showForm(\"trai\",\"$EmpID\",\"".$records['TrainID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
						else{echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
						if($Authorization[0]&&$Authorization[3]){echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Delete' onClick='showForm(\"trai\",\"$EmpID\",\"".$records['TrainID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
						else{echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
						echo "</tr>";
						$n += 1; 
					} 
					while($n<=1){
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						for($col=1;$col<=6;$col+=1) { echo "<td class='i_table_body'>&nbsp;</td>"; }
						echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
						echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
						echo "</tr>";
						$n+=1;
					}
					
					$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
					if($Authorization[0]&&$Authorization[2]){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('trai','$EmpID','',0);";}
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