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
	$Premium=isset($_POST['opt'])?mysql_escape_string(trim(strip_tags($_POST['opt']))):'pgsis';
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	$Year=date('Y');
	
	if($Premium=="pgsis"){$PType="PT01";}
	if($Premium=="phdmf"){$PType="PT02";}
	if($Premium=="pphic"){$PType="PT03";}
?>

<center><br/>

	<span name="display_premiums" id="display_premiums"> 
		<table width="600px">
			<tr>
				<td align="left"><input type="button" value="<<" onClick="ajaxShowPremiums('<?php echo $EmpID; ?>','0','<?php echo $Premium; ?>',<?php echo $Year-1; ?>,0); return false;"/></td>
				<td align="center" id="p_year"><b><?php echo $Year; ?></b></td>
				<td align="right"><input type="button" value=">>" onClick="ajaxShowPremiums('<?php echo $EmpID; ?>','0','<?php echo $Premium; ?>',<?php echo $Year+1; ?>,0); return false;"/></td>
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
		$Config=new Conf();
		$MySQLi=new MySQLClass($Config);
		$EmpPremEeS=Array("00"=>"0","01"=>"0","02"=>"0","03"=>"0","04"=>"0","05"=>"0","06"=>"0","07"=>"0","08"=>"0","09"=>"0","10"=>"0","11"=>"0","12"=>"0");
		$EmpPremErS=Array("00"=>"0","01"=>"0","02"=>"0","03"=>"0","04"=>"0","05"=>"0","06"=>"0","07"=>"0","08"=>"0","09"=>"0","10"=>"0","11"=>"0","12"=>"0");
		$result = $MySQLi -> sqlQuery("SELECT `EmpPremPayMonth`, `EmpPremEeS`, `EmpPremErS` FROM `tblemppremiums` WHERE `EmpID` = '".$EmpID."' AND `EmpPremPayYear` = '".$Year."' AND `PremiumID` = '".$PType."' ORDER BY `EmpPremPayMonth` ASC;");
		while($records = mysql_fetch_array($result)){
			$Month=(strlen($records['EmpPremPayMonth'])>1)?$records['EmpPremPayMonth']:"0".$records['EmpPremPayMonth'];
			$EmpPremEeS[$Month]=$records['EmpPremEeS'];
			$EmpPremErS[$Month]=$records['EmpPremErS'];
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
	?>
		</table>
	</span>
</center>