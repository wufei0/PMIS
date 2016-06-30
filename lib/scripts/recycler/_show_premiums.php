<?php
	ob_start();
	session_start();
	$_SESSION['theme']='blue';
	
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
	
	function microtime_float(){list($usec, $sec)=explode(" ", microtime());return ((float)$usec + (float)$sec);}
	$start_time=microtime_float();
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	//Get GET Values
	$ID=isset($_GET['id'])?mysql_escape_string(trim(strip_tags($_GET['id']))):'00000';
	$Status=isset($_GET['as'])? mysql_escape_string(trim(strip_tags($_GET['as']))):'AS003';
	$Premium=isset($_GET['pr'])?mysql_escape_string(trim(strip_tags($_GET['pr']))):'pgsis';
	$Year=isset($_GET['yr'])?mysql_escape_string(trim(strip_tags($_GET['yr']))):date('Y');
	$Month=isset($_GET['mo'])?mysql_escape_string(trim(strip_tags($_GET['mo']))):date('n');
	
	$Config=new Conf();
	$MySQLi=new MySQLClass($Config);
	$records=Array();
	
	if($Premium=="pgsis"){$PType="PT01";}
	if($Premium=="phdmf"){$PType="PT02";}
	if($Premium=="pphic"){$PType="PT03";}
	
	echo "ID - ".$ID.", Status - ".$Status.", Premium - ".$Premium.", Year - ".$Year.", Month - ".$Month.", PType - ".$PType;
	
	if ($Status=='0'){
?>
		<table width="600px">
			<tr>
				<td align="left"><input type="button" value="<<" onClick="ajaxShowPremiums('<?php echo $ID; ?>','0','<?php echo $Premium; ?>',<?php echo $Year-1; ?>,0); return false;"/></td>
				<td align="center" id="p_year"><b><?php echo $Year; ?></b></td>
				<td align="right"><input type="button" value=">>" onClick="ajaxShowPremiums('<?php echo $ID; ?>','0','<?php echo $Premium; ?>',<?php echo $Year+1; ?>,0); return false;"/></td>
			</tr>
		</table>

		<table class="i_table" style="width:600px;">
			<tr>
				<td class="i_table_header" rowspan="2" width="10px">#</td>
				<td class="i_table_header" rowspan="2">Month</td>
				<td class="i_table_header" colspan="3">Premiums</td>
			</tr>
			<tr>
				<td class="i_table_header" >Employer</td>
				<td class="i_table_header" >Employee</td>
				<td class="i_table_header" >TOTAL</td>
			</tr>
<?php
		$EmpPremEeS=Array("00"=>"0","01"=>"0","02"=>"0","03"=>"0","04"=>"0","05"=>"0","06"=>"0","07"=>"0","08"=>"0","09"=>"0","10"=>"0","11"=>"0","12"=>"0");
		$EmpPremErS=Array("00"=>"0","01"=>"0","02"=>"0","03"=>"0","04"=>"0","05"=>"0","06"=>"0","07"=>"0","08"=>"0","09"=>"0","10"=>"0","11"=>"0","12"=>"0");
		$result=$MySQLi->sqlQuery("SELECT `EmpPremPayMonth`, `EmpPremEeS`, `EmpPremErS` FROM `tblemppremiums` WHERE `EmpID`='".$ID."' AND `EmpPremPayYear`='".$Year."' AND `PremiumID`='".$PType."' ORDER BY `EmpPremPayMonth` ASC;");
		while($records=mysql_fetch_array($result)){
			$Mo=(strlen($records['EmpPremPayMonth'])>1)?$records['EmpPremPayMonth']:"0".$records['EmpPremPayMonth'];
			$EmpPremEeS[$Mo]=$records['EmpPremEeS'];
			$EmpPremErS[$Mo]=$records['EmpPremErS'];
		}
		
		for($n=1;$n<=12;$n++){
			$i=($n>9)?$n:"0".$n;
			if($n%2==0){echo "<tr class='i_table_row_0'>";}
			else{echo "<tr class='i_table_row_1'>";}
			echo "<td align='right'>".$n.".</td>";
			echo "<td class='i_table_body'>".$MONTHS[$n]."</td>";
			echo "<td class='i_table_body' align='right'>".number_format($EmpPremEeS[$i],2)."</td>";
			echo "<td class='i_table_body' align='right'>".number_format($EmpPremErS[$i],2)."</td>";
			echo "<td class='i_table_body' align='right'>".number_format(($EmpPremEeS[$i]+$EmpPremErS[$i]),2)."</td>";
			echo "</tr>";
		}
		echo "</table>";
	}

	else{
?>
		<table width='600px'>
		<tr>
		 <td class="i_table_header" rowspan="2"  width="10px">#</td>
		 <td class="i_table_header" rowspan="2">Employee Name</td>
		 <td class="i_table_header" colspan="3">Premiums</td>
		</tr>
		<tr>
		 <td class="i_table_header" >Employer</td>
		 <td class="i_table_header" >Employee</td>
		<td class="i_table_header" >TOTAL</td>
		</tr>
<?php
		if($Month<1){$Year=$Year-1;$Month=12;}
		else if($Month>12){$Year=$Year+1;$Month=1;}
		$result=$MySQLi->sqlQuery("SELECT `tblemppremiums`.`EmpPremID`, `tblemppremiums`.`EmpPremErS`, `tblemppremiums`.`EmpPremEeS`, CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppremiums` JOIN `tblemppersonalinfo` ON `tblemppremiums`.`EmpID`=`tblemppersonalinfo`.`EmpID` WHERE (`tblemppremiums`.`EmpID` IN (SELECT `EmpID` FROM `tblappointments` WHERE `SubOffID`='".$ID."' AND `ApptStID`='".$Status."')) AND (`tblemppremiums`.`EmpPremPayMonth`='".$Month."' AND `tblemppremiums`.`EmpPremPayYear`='".$Year."') ORDER BY EmpName");
		if (!($records=mysql_fetch_array($result))){
			echo "<tr><td class='i_table_body' colspan='4'>No records found.<br/>Please click PROCESS PREMIUMS.</td></tr>";
		}
		else{
			$n=1;
			while($records=mysql_fetch_array($result)){
				echo "<tr>";
				echo "<td align='right'>".$n."</td>";
				echo "<td class='i_table_body'>".$records['EmpName']."</td>";
				echo "<td class='i_table_body' align='right'>".number_format($records['EmpPremErS'],2)."</td>";
				echo "<td class='i_table_body' align='right'>".number_format($records['EmpPremEeS'],2)."</td>";
				echo "<td class='i_table_body' align='right'>".number_format(($records['EmpPremErS']+$records['EmpPremEeS']),2)."</td>";
				echo "</tr>";
				$n+=1;
			}
		}
		echo "</table>";
	}
	
	//$end_time=microtime_float();
	//$consumed_time=$end_time - $start_time;
	//echo"$ID, $Status, $Year, $Month, $Premium</br> $consumed_time ";
?>