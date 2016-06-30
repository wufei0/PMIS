<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD013'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id']) ? trim(strip_tags($_POST['id'])) : '00000';
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	$Year=date('Y');
?>

<center><br/>

	<span name="display_premiums" id="display_premiums"> 
		<table width="600px">
			<tr>
				<td align="left"><input type="button" value="<<" onClick="ajaxShowPremiums('<?php echo $EmpID; ?>','0','PHIC',<?php echo $Year-1; ?>,0); return false;"/></td>
				<td align="center" id="p_year"><b><?php echo $Year; ?></b></td>
				<td align="right"><input type="button" value=">>" onClick="ajaxShowPremiums('<?php echo $EmpID; ?>','0','PHIC',<?php echo $Year+1; ?>,0); return false;"/></td>
			</tr>
		</table>

		<table class="i_table" style="width:600px;">
			<tr>
				<td class="i_table_header" rowspan="2">Month</td>
				<td class="i_table_header" colspan="3">Premiums</td>
			</tr>
			<tr>
				<td class="i_table_header" >Employer</td>
				<td class="i_table_header" >Employee</td>
				<td class="i_table_header" >TOTAL</td>
			</tr>
	<?php
		$Config=new Conf();
		$MySQLi=new MySQLClass($Config);
		$n = 1;
		while($n<=12) {
			$sql = "SELECT `pPH_ID`, `pPH_Month`, `pPH_Employer`, `pPH_Employee` FROM `tblpremiumsph` WHERE `EmpID` = '".$EmpID."' AND `pPH_Year` = '".$Year."' AND `pPH_Month` = '".$n."' ORDER BY `pPH_Month` ASC;";
			$result = $MySQLi -> sqlQuery($sql);
			$records = mysql_fetch_array($result);
			
			if ($records[1] == $n) {
				if($n%2==0){echo "<tr class='i_table_row_0'>";}
				else{echo "<tr class='i_table_row_1'>";}
				echo "	<td class='i_table_body'>".$MONTHS[$records[1]]."</td>";
				echo "	<td class='i_table_body' align='right'>".number_format($records[2],2)."</td>";
				echo "	<td class='i_table_body' align='right'>".number_format($records[3],2)."</td>";
				echo "	<td class='i_table_body' align='right'>".number_format(($records[2]+$records[3]),2)."</td>";
				echo "</tr>";
			}
			else {
				if($n%2==0){echo "<tr class='i_table_row_0'>";}
				else{echo "<tr class='i_table_row_1'>";}
				echo "	<td class='i_table_body'>".$MONTHS[$n]."</td>";
				echo "	<td class='i_table_body' align='right'></td>";
				echo "	<td class='i_table_body' align='right'></td>";
				echo "	<td class='i_table_body' align='right'></td>";
				echo "</tr>";
			}
			$n += 1; 
		}
	?>
		</table>
	</span>
</center>