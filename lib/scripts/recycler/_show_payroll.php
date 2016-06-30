<?php
	ob_start();
	session_start();
	$_SESSION['theme']='blue';
	
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';
	require_once $_SESSION['path'].'/lib/classes/Payroll_Proccess.php';
	require_once $_SESSION['path'].'/lib/classes/Payroll_Sections.php';
	
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
	
	//Get GET Values for Individual DTR Processing
	$EmpID=isset($_GET['id'])?mysql_escape_string(trim(strip_tags($_GET['id']))):'00000';
	$Year=isset($_GET['yr'])?mysql_escape_string(trim(strip_tags($_GET['yr']))):date('Y');
	$Month=isset($_GET['mo']) ?mysql_escape_string(trim(strip_tags($_GET['mo']))):date('m');
	$PayPeriod=isset($_GET['pr']) ?mysql_escape_string(trim(strip_tags($_GET['pr']))):0;
	//Get Values for per Office/SubOffice DTR Processing
	$isPerOff=isset($_GET['spo'])?mysql_escape_string(trim(strip_tags($_GET['spo']))):'0';
	$SubOffID=isset($_GET['sof'])?mysql_escape_string(trim(strip_tags($_GET['sof']))):'SOOF00101';
	$ApptStID=isset($_GET['aps'])?mysql_escape_string(trim(strip_tags($_GET['aps']))):'AS001';
	
	if($EmpID!="00000"){
		$Month=($Month>9)?$Month:"0".$Month;
		
		$LeaveNoPay=0;
		$GrossPay=0;

		$Config=new Conf();
		$MySQLi=new MySQLClass($Config);
		$result=$MySQLi->sqlQuery("SELECT `SalGrdValue` FROM `tblappointments` JOIN `tblsalgrade` ON `tblappointments`.`SalGrdID`=`tblsalgrade`.`SalGrdID` WHERE `tblappointments`.`EmpID`='".$EmpID."';");
		$records=mysql_fetch_array($result); 
		$SalGrdValue=isset($records['SalGrdValue'])?$records['SalGrdValue']:"0.00";
		unset($result);unset($records);
		$OtherProcess=new OtherProcess();
		$NumberOfDays=$OtherProcess->getNumberOfDays($EmpID,$Year,$Month,$PayPeriod);
		$LeaveNoPay=$OtherProcess->getLeaveWithOutPay($EmpID,$Year,$Month,$PayPeriod);
		$ComputedSalary=$OtherProcess->getSalary($EmpID,$Year,$Month,$PayPeriod);
	
		$Payroll=new Payroll();
?>
	
	<!-- BASIC SALARY -->
	<table class="i_table" style="width:600px;">
		<tr>
			<td class="i_table_header" colspan="3" style="text-align:left;">BASIC SALARY</td>
			<td class="i_table_header">&nbsp;</td>
		</tr>
		<tr class="i_table_row_1">
			<td style="padding-left:15px;">Basic Salary</td>
			<td class="i_table_body" style="width:82px;text-align:right;"><?php echo number_format($SalGrdValue,2); ?></td>
			<td style="width:22px;text-align:center;"><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="Salary" class="ui-state-default ui-corner-all" title="Re-proccess" onClick="reProccess(this,<?php echo "$EmpID,$Year,$Month,$PayPeriod"; ?>);"><span class="ui-icon ui-icon-refresh"></span></li></ul></td>
			<td style="width:22px;text-align:center;"><ul class="ui-widget ui-helper-clearfix ul-icons"><li class="ui-state-default ui-corner-all" title="Remove"><span class="ui-icon ui-icon-trash"></span></li></ul></td>
		</tr>
		<tr class="i_table_row_1">
			<td style="padding-left:15px;">Number of Days</td>
			<td class="i_table_body" style="text-align:right;"><?php echo number_format($NumberOfDays,2); ?></td>
			<td style="width:22px;text-align:center;"><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="NumberOfDays"class="ui-state-default ui-corner-all" title="Re-proccess" onClick="reProccess(this,<?php echo "$EmpID,$Year,$Month,$PayPeriod"; ?>);"><span class="ui-icon ui-icon-refresh"></span></li></ul></td>
			<td style="width:22px;text-align:center;"><ul class="ui-widget ui-helper-clearfix ul-icons"><li class="ui-state-default ui-corner-all" title="Remove"><span class="ui-icon ui-icon-trash"></span></li></ul></td>
		</tr>
		<tr class="i_table_row_1">
			<td style="padding-left:15px;">Leave without Pay</td>
			<td class="i_table_body" style="text-align:right;"><?php echo number_format($LeaveNoPay,2); ?></td>
			<td style="width:22px;text-align:center;"><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="NumberOfDays"class="ui-state-default ui-corner-all" title="Re-proccess" onClick="reProccess(this,<?php echo "$EmpID,$Year,$Month,$PayPeriod"; ?>);"><span class="ui-icon ui-icon-refresh"></span></li></ul></td>
			<td style="width:22px;text-align:center;"><ul class="ui-widget ui-helper-clearfix ul-icons"><li class="ui-state-default ui-corner-all" title="Remove"><span class="ui-icon ui-icon-trash"></span></li></ul></td>
		</tr>
	</table>
	
	<!-- COMPUTED SALARY --><table style="width:600px;border-spacing:0px;"><tr><td style="text-align:right;font-size:1.1em;font-weight:bold;border-top:1px solid #6D84B4;">COMPUTED SALARY:</td><td style="text-align:right;font-size:1.1em;font-weight:bold;border-top:1px solid #6D84B4;border-bottom:1px solid #6D84B4;width:82px;"><?php echo number_format($ComputedSalary,2); ?></td><td style="width:24px;border-top:1px solid #6D84B4;">&nbsp;</td><td style="width:24px;border-top:1px solid #6D84B4;">&nbsp;</td></tr></table>
	
<?php
		$Incentives=$Payroll->Incentives($EmpID,$Year,$Month,$PayPeriod);
		$GrossPay=$ComputedSalary+$Incentives;
?>

	<!-- GROSS PAY --><table style="width:600px;border-spacing:0px;"><tr><td style="text-align:right;font-size:1.1em;font-weight:bold;">GROSS PAY:</td><td style="text-align:right;font-size:1.1em;font-weight:bold;border-bottom:1px solid #6D84B4;width:82px;"><?php echo number_format($GrossPay,2); ?></td><td style="width:24px;">&nbsp;</td><td style="width:24px;">&nbsp;</td></tr></table>
	
<?php

		$Pay=new Premiums();
		$Pay->processGSIS_premium($EmpID,$Year,$Month,$PayPeriod);
		$Pay->processHDMF_premium($EmpID,$Year,$Month,$PayPeriod);
		$Pay->processPH_premium($EmpID,$Year,$Month,$PayPeriod);
		//$Pay->ProcessWTax($EmpID,$Year,$Month,$PayPeriod);
		$Premiums=$Payroll->Premiums($EmpID,$Year,$Month,$PayPeriod);
		$Loans=$Payroll->Loans($EmpID,$Year,$Month,$PayPeriod);
		$OtherDeductions=$Payroll->OtherDeductions($EmpID,$Year,$Month,$PayPeriod);
		$NetPay=$GrossPay-($Premiums+$Loans+$OtherDeductions);
	
?>

	<!-- NET PAY --><table style="width:600px;border-spacing:0px;"><tr><td style="text-align:right;font-size:1.4em;font-weight:bold;">NET PAY:</td><td style="text-align:right;font-size:1.4em;font-weight:bold;border-bottom:1px double #6D84B4;border-width:3px;width:82px;"><?php echo number_format($NetPay,2); ?></td><td style="width:24px;">&nbsp;</td><td style="width:24px;">&nbsp;</td></tr></table>

<?php
	}
?>
