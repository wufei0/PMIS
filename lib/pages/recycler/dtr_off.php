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

	
	
	$SubOffID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	echo "1|$SubOffID|";
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');

?>
<center><br/>
	<form name="DTR_emp_form" id="DTR_emp_form" onSubmit="GetDTR(this,'off'); return false;">
		<table class="form" style="width:600px;">
			<tr>
				<td class="form_label" style="width:45px;"><label>MONTH: </label></td>
				<td class="pds_form_input">
					<select id="SelectMonth" name="SelectMonth" class="text_input">
					<?php
					for($m=1;$m<=12;$m+=1) { 
						if($m==date('n')) { echo "<option value='$m' selected>".$MONTHS[$m]."</option>"; }
						else { echo "<option value='$m'>".$MONTHS[$m]."</option>"; }
					}
					?>
					</select>
				</td>
				<td class="form_label" style="width:45px;"><label>YEAR: </label></td>
				<td class="pds_form_input"><input type="text" id="SelectYear" name="SelectYear" class="text_input" value="<?php echo date('Y'); ?>"></td>
				<td class="form_label" style="width:45px;"><label>PERIOD: </label></td>
				<td class="pds_form_input">
					<select id="SelectPayPeriod" name="SelectPayPeriod" class="text_input">
						<option value="0">Whole Month</option>
						<option value="1">First Half</option>
						<option value="2">Second Half</option>
					</select>
				</td>
				<td class="form_label"><label>APPOINTMENT: </label></td>
				<td class="pds_form_input">
					<select type="text" name="ApptStID" id="ApptStID" class="text_input">
					<?php
						$Config=new Conf();
						$MySQLi=new MySQLClass($Config);
						$result=$MySQLi->sqlQuery("SELECT * FROM `tblapptstatus` ORDER BY `ApptStDesc`;");
						while($appstatuses=mysql_fetch_array($result)) {
							echo "<option value='".$appstatuses['ApptStID']."'>".$appstatuses['ApptStDesc']."</option>";
						} unset($result);
					?>
					</select>
				</td>
				<td class="pds_form_input"><input type="submit" value="View" /><input type="button" value="Print" onClick="printDTR(document.getElementById('DTR_emp_form'),'off');"/></td>
			</tr>
		</table>
		<input type="hidden" name="SubOffID" id="SubOffID" value="<?php echo $SubOffID; ?>" />
	</form>
	<br/>
	<span name="DTR_box_off" id="DTR_box_off"> </span>
</center>