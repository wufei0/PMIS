<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD010'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID = isset($_POST['id']) ? trim(strip_tags($_POST['id'])) : '00000';
	
	$MONTHS = Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	
	$Config=new Conf();
	$MySQLi=new MySQLClass($Config);
	
?>
<center><br/>
	<form onSubmit="GetPayroll(this,'emp'); return false;">
		<table class="form" style="width:600px;">
			<tr>
				<td class="form_label" style="width:50px;"><label>MONTH: </label></td>
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
				<td class="form_label" style="width:50px;"><label>YEAR: </label></td>
				<td class="pds_form_input"><input type="text" id="SelectYear" name="SelectYear" class="text_input" value="<?php echo date('Y'); ?>"></td>
				<td class="form_label" style="width:50px;"><label>PERIOD: </label></td>
				<td class="pds_form_input">
					<select id="SelectPayPeriod" name="SelectPayPeriod" class="text_input">
						<option value="0">Whole Month</option>
						<option value="1">First Half</option>
						<option value="2">Second Half</option>
					</select>
				</td>
				<td class="pds_form_input" width="20%"><input type="submit" value="View" /><input type="button" value="Print" /></td>
			</tr>
		</table>
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
	<br/>
	<span name="Payroll_box_emp" id="Payroll_box_emp"> </span>
</center>
