<?php
	ob_start();
	session_start();
	$_SESSION['theme']='blue';
	
	require_once $_SESSION['path'].'/lib/classes/Conf.php';
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	
	$EmpID=isset($_POST['id']) ? trim(strip_tags($_POST['id'])) : '00000';
	
	if($EmpID!='00000'){
		$Config=new Conf();
		$MySQLi=new MySQLClass($Config);
?>

<center><br/><br/>
	<table class="i_table" style="width:1000px;">
		<tr>
			<td class="i_table_header" colspan="2">Inclusive Dates of Att.</td>
			<td class="i_table_header" rowspan="2">Department/Agency/<br/>Office/Company</td>
			<td class="i_table_header" rowspan="2" width="120">Position/Title/<br/>Job Description</td>
			<td class="i_table_header" rowspan="2" width="100">Salary</td>
			<td class="i_table_header" rowspan="2" width="75">Salary Grade & Step Increment</td>
			<td class="i_table_header" rowspan="2" width="120">Status of<br/>Appointment</td>
			<td class="i_table_header" rowspan="2" width="50">Gov't Service</td>
			<td class="i_table_header" colspan="2" rowspan="2" width="40px">&nbsp;</td>
		</tr>
		<tr>
			<td class="i_table_header" width="75">From</td>
			<td class="i_table_header" width="75">To</td>
		</tr>
		
			<?php
				$Config=new Conf();
				$MySQLi=new MySQLClass($Config);
				$records=Array();
				if($EmpID!='00000') {
					$result=$MySQLi -> sqlQuery("SELECT `SRecID`, CONCAT_WS('-',`SRecFromDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `SRecFromYear`,`SRecFromMonth`,`SRecFromDay`)), 1, 3)),`SRecFromYear`) AS SRecFrom, CONCAT_WS('-',`SRecToDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `SRecToYear`,`SRecToMonth`,`SRecToDay`)), 1, 3)),`SRecToYear`) AS SRecTo, `PosID`,`SRecEmployer`,`MotherOfficeID`,`SRecJobDesc`,`SalGrdID`,`SRecSalary`,`SalUnitID`,`ApptStID`,`SRecIsGov` FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC;");
					$n=1; $lines="";
					while($records=mysql_fetch_array($result)) {
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						echo "<td class='i_table_body' align='center'>".$records['SRecFrom']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['SRecTo']."</td>";
						$offices=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `SubOffCode` FROM `tblsuboffices` WHERE `SubOffID`='".$records['MotherOfficeID']."';")); 
						$SRecEmployer=isset($offices['SubOffCode'])?$records['SRecEmployer']." - ".$offices['SubOffCode']:$records['SRecEmployer'];
						echo "<td class='i_table_body'>".$SRecEmployer."</td>";
						if($records['SRecIsGov']=="YES"){$positions=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `PosDesc` FROM `tblpositions` WHERE `PosID`='".$records['PosID']."';"));$PosTitleJD=$positions['PosDesc'];}
						else{$PosTitleJD=$records['SRecJobDesc'];}
						echo "<td class='i_table_body'>".$PosTitleJD."</td>";
						$salunit=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `SalUnitCode` FROM `tblsalaryunits` WHERE `SalUnitID`='".$records['SalUnitID']."';"));
						echo "<td class='i_table_body' align='right'>".number_format((float)$records['SRecSalary'],2,'.',',')." (".$salunit['SalUnitCode'].")</td>";
						echo "<td class='i_table_body' align='center'>".substr($records['SalGrdID'],4,2)."-".substr($records['SalGrdID'],-2,2)."</td>";
						$appstatuses=mysql_fetch_array($MySQLi -> sqlQuery("SELECT `ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$records['ApptStID']."';")); 
						echo "<td class='i_table_body' align='center'>".$appstatuses['ApptStDesc']."</td>";
						echo "<td class='i_table_body' align='center'>".$records['SRecIsGov']."</td>";
						if(1==1){
						echo "<td class='i_table_body' style='width:22px;text-align:center;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='formServiceRecord(\"$EmpID\",\"".$records['SRecID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
						echo "<td style='width:22px;text-align:center;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Remove' onClick='formServiceRecord(\"$EmpID\",\"".$records['SRecID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
					}
					else{
						echo "<td class='i_table_body' style='width:22px;text-align:center;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-disabled ui-corner-all' title='Edit' onClick=''><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
						echo "<td style='width:22px;text-align:center;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all' title='Remove' onClick=''><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
					}
					echo "</tr>";
					$n+=1; 
					} 
					while($n<=1){
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						for($col=1;$col<=10;$col+=1) {echo "<td class='i_table_body'>&nbsp;</td>";}
						echo "</tr>";
						$n+=1;
					}
				}
			?>
	</table>
	<?php if(1==1){echo "<table class='form' style='width:1000px;'><tr><td style='width:100%;text-align:left'><input type='button' value='Add' onClick='formServiceRecord(\"$EmpID\",\"\",0);'/><input type='button' value='View Statistics' onClick=''/><input type='button' value='Print' onClick=''/></td></tr></table>";} ?>
</center><br/>
<?php } ?>
