<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD009'));
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
	<div style="width:1040px;height:auto;overflow:auto"><br/><br/>
		<table class="i_table" style="width:1000px;">
			<tr>
				<td class="i_table_header" colspan="2">Inclusive Dates of Att.</td>
				<td class="i_table_header" rowspan="2">Department/Agency/<br/>Office/Company</td>
				<td class="i_table_header" rowspan="2" width="120">Position/Title/<br/>Job Description</td>
				<td class="i_table_header" rowspan="2" width="120">Salary</td>
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
				$result=$MySQLi->sqlQuery("SELECT `SRecID`, `SRecFromYear`, CONCAT_WS('-',`SRecFromDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `SRecFromYear`,`SRecFromMonth`,`SRecFromDay`)), 1, 3)),`SRecFromYear`) AS SRecFrom, CONCAT_WS('-',`SRecToDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `SRecToYear`,`SRecToMonth`,`SRecToDay`)), 1, 3)),`SRecToYear`) AS SRecTo, `PosID`,`SRecSalGradeStep`,`SRecEmployer`,`MotherOfficeID`,`SRecOffice`,`SRecJobDesc`,`SalUnitID`,`SRecSalary`,`ApptStID`,`SRecPosition`,`SRecIsGov` FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC;");
				$n=1; $lines="";
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)) {
					if($n%2==0){echo "<tr class='i_table_row_0'>";}
					else{echo "<tr class='i_table_row_1'>";}
					echo "<td align='center' valign='top' style='padding:4px 3px 3px 0px;'>".$records['SRecFrom']."</td>";
					echo "<td class='i_table_body' align='center'>".$records['SRecTo']."</td>";
					if($records['MotherOfficeID']!="SO000"){
						$offices=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SubOffCode` FROM `tblsuboffices` WHERE `SubOffID`='".$records['MotherOfficeID']."';"), MYSQLI_BOTH);
						$SRecEmployer=isset($offices['SubOffCode'])?$records['SRecEmployer']." - ".$offices['SubOffCode']:$records['SRecEmployer'];
					}
					else{$SRecEmployer=$records['SRecOffice'];}
					echo "<td class='i_table_body'>".$SRecEmployer."</td>";
					$SRecSalary=$records['SRecSalary'];
					if($records['SRecIsGov']=="YES"){
						$positions=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `PosDesc`, `PosSalGrade` FROM `tblpositions` WHERE `PosID`='".$records['PosID']."';"), MYSQLI_BOTH);
						$PosSalGrade=$positions['PosSalGrade']>9?$positions['PosSalGrade']:"0".$positions['PosSalGrade'];
						$SRecSalGradeStep=(($records['SRecSalGradeStep']>0)&&($records['SRecSalGradeStep']<9))?trim($records['SRecSalGradeStep']):'X';
						//$SRecSalGradeStep=intval($SRecSalGradeStep);
						if($records['PosID']!="PO000"){
							$PosTitleJD=$positions['PosDesc'];
							// $salary=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SalGrdValue` FROM `tblsalgrade` WHERE `SalGrdID`='".$records['SRecFromYear'].$PosSalGrade."0".$SRecSalGradeStep."';"), MYSQLI_BOTH);
							// $SRecSalary=$salary['SalGrdValue'];
							$SalGrdID="SG".$records['SRecFromYear'].$PosSalGrade;
							$SRecSalary=$MySQLi->GetArray("SELECT `Step".$SRecSalGradeStep."` FROM `tblsalarygrade` WHERE `SGID`='".$SalGrdID."' LIMIT 1;")[0];
						}
						else{$PosTitleJD=$records['SRecPosition'];$SRecSalary=$records['SRecSalary'];$PosSalGrade="";$SRecSalGradeStep="";}
					}
					else{$PosTitleJD=$records['SRecJobDesc'];}
					echo "<td class='i_table_body'>".$PosTitleJD."</td>";
					$UnitCode=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SalUnitCode` FROM `tblsalaryunits` WHERE `SalUnitID`='".$records['SalUnitID']."';"), MYSQLI_BOTH);
					$SalUnitCode=($UnitCode['SalUnitCode']!="")?" (".$UnitCode['SalUnitCode'].")":"";
					echo "<td class='i_table_body' align='right'>".number_format($SRecSalary,2).$SalUnitCode."</td>";
					if($records['PosID']=="PO001"){$PosSalGrade="";$SRecSalGradeStep="";} // Blank salary grade/step for LABORER I
					echo "<td class='i_table_body' align='center'>".$PosSalGrade." - ".$SRecSalGradeStep."</td>";
					$appstatuses=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$records['ApptStID']."';"), MYSQLI_BOTH);
					echo "<td class='i_table_body' align='center'>".$appstatuses['ApptStDesc']."</td>";
					echo "<td class='i_table_body' align='center'>".$records['SRecIsGov']."</td>";
					
					if($Authorization[0]&&$Authorization[2]){echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='showForm(\"srec\",\"$EmpID\",\"".$records['SRecID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					else{echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					if($Authorization[0]&&$Authorization[3]){echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Delete' onClick='showForm(\"srec\",\"$EmpID\",\"".$records['SRecID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
					else{echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
					echo "</tr>";
					$n+=1; 
				} 
				while($n<=1){
					if($n%2==0){echo "<tr class='i_table_row_0'>";}
					else{echo "<tr class='i_table_row_1'>";}
					for($col=1;$col<=8;$col+=1) {echo "<td class='i_table_body'>&nbsp;</td>";}
					echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
					echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
					echo "</tr>";
					$n+=1;
				}
				
				$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
				if($Authorization[0]&&$Authorization[3]){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('srec','$EmpID','',0);";}
			?>
		</table>
		<table class="form" style="width:1000px;">
			<tr>
				<td style="width:100%;text-align:left">
					<input type="button" value="Add" class="<?php echo $bAddClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bAddState; ?>/>
					<!-- input type="button" value="View Statics" class="button_help button ui-button ui-widget ui-corner-all" onClick=""/ -->
					<?php //if($_SESSION['usergroup']=='USRGRP008'){ ?>
					<input type="button" value="Print Service Records" class="button_help button ui-button ui-widget ui-corner-all" onClick="window.open('reports/rpt_sr.php?id=<?php echo $EmpID; ?>','mywindow','width=800,height=600');"/>
					<?php //} ?>
				</td>
			</tr>
		</table>
	</div>
</center><br/> <input type="hidden" value="<?php echo $_SESSION['user']." == ".$EmpID."   ADMIN:[".$Authorization[0]."][".$Authorization[1]."][".$Authorization[2]."]"; ?>">
<?php } ?>
