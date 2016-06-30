<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD019'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$Yr=isset($_POST['Yr'])?trim(strip_tags($_POST['Yr'])):date('Y');
	$Mo=isset($_POST['Mo'])?trim(strip_tags($_POST['Mo'])):date('m');
	$St=isset($_POST['St'])?trim(strip_tags($_POST['St'])):'XX';
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	echo "1|".$_SESSION['user']."|";
	$MySQLi=new MySQLClass();
	
	$StStr="";
?>

<center><br/>
	<form name="filter_to" id="filter" onSubmit="FilterQuery(this);return false;">
	<table class="filter_bar" cellspacing="0" cellpadding="0" style="width:900px;">
		<tr>
			<td class="form_label_l filter_bar" style="width:55px;"><label>FILTER:</label></td>
			<td class="form_label filter_bar" style="width:35px;"><label>Year:</label></td>
				<td class="pds_form_input filter_bar" style="width:53px;">
					<select id="flt_year" name="flt_year" class="text_input" style="width:53px;" onChange="FilterQuery(document.getElementById('filter'));" >
					<?php for($y=2010;$y<=date('Y');$y++){if($y==$Yr){echo "<option value='$y' selected>".$y."</option>";}else{echo "<option value='$y'>".$y."</option>";}} ?>
					</select>
				</td>
			<td class="form_label filter_bar" style="width:40px;"><label>Month:</label></td>
				<td class="pds_form_input filter_bar" style="width:90px;">
					<select id="flt_month" name="flt_month" class="text_input" style="width:90px;" onChange="FilterQuery(document.getElementById('filter'));" >
					<?php for($m=1;$m<=12;$m++){if($m==$Mo){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
					</select>
				</td>
			<td class="form_label filter_bar" style="width:55px;"><label>Status:</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_show_all" name="flt_status" value="XX" <?php if($St=="XX"){echo "checked='checked'";$StStr="";}?> onClick="FilterQuery(document.getElementById('filter'));"/>
				</td>
				<td class="form_label_l" style="width:55px;"><label>SHOW ALL</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_posted" name="flt_status" value="PO" <?php if($St=="PO"){echo "checked='checked'";$StStr=" AND `TOStatus` LIKE 'PO%'";}?> onClick="FilterQuery(document.getElementById('filter'));"/>
				</td>
				<td class="form_label_l" style="width:40px;"><label>POSTED</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_checked" name="flt_status" value="CH" <?php if($St=="CH"){echo "checked='checked'";$StStr=" AND `TOStatus` LIKE 'CH%'";}?> onClick="FilterQuery(document.getElementById('filter'));"/>
				</td>
				<td class="form_label_l" style="width:50px;"><label>CHECKED</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_approved" name="flt_status" value="AP" <?php if($St=="AP"){echo "checked='checked'";$StStr=" AND `TOStatus` LIKE 'AP%'";}?> onClick="FilterQuery(document.getElementById('filter'));"/>
				</td>
				<td class="form_label_l" style="width:55px;"><label>APPROVED</label></td>
				<?php
				
				$records=Array();
				$total_records=$MySQLi->sqlQuery("SELECT `TOID` FROM `tbltravelorders`;");
				$result=$MySQLi->sqlQuery("SELECT `TOID`, CONCAT_WS(' ',`TODateFrDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-',`TODateFrYear`,`TODateFrMonth`,`TODateFrDay`)), 1, 3)),`TODateFrYear`) AS TODateFr, CONCAT_WS(' ',`TODateToDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-',`TODateToYear`,`TODateToMonth`,`TODateToDay`)), 1, 3)),`TODateToYear`) AS TODateTo, `TOSubject`, `TODestination`, `TOStatus`, `TORemarks` FROM `tbltravelorders` WHERE `TODateFrYear`='$Yr' AND `TODateFrMonth`='$Mo' ".$StStr." ORDER BY `TODateFrYear` DESC, `TODateFrMonth` DESC,`TODateFrDay` DESC ;");
				
				?>
			<td class="form_label filter_bar"><?php echo "<label>Returned </label>".mysql_num_rows($result)."<label> of </label>".mysql_num_rows($total_records)."<label> records.</label>"; ?></td>
		</tr>
	</table>
	</form>
	<table class="i_table" style="width:900px;" cellspacing="0">
		<tr>
			<td class="i_table_header_1st" rowspan="2" width="80px">Travel Order Number</td>
			<td class="i_table_header" colspan="2" width="140px">Inclusive Dates</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="202px">TO</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="150px">Destination</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="85px">Status</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" rowspan="2" width="130px">Remarks</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" colspan="2" rowspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="70px">From</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="70px">To</td>
		</tr>
	</table>
	<div id="pending_loading_1" class="loading_div brief_info_emp_1" style="left:127px;width:898px;height:270px;">
		<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
	</div>
	<div style="height:270px;width:898px;border:1px dotted #6D84B4;margin-left:20px;margin-right:20px;padding:0px;overflow-x:hidden;overflow-y:scroll;">
		<table style="width:881px;" cellspacing="0">
			<?php
				$n=1;
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					if($n%2==0){echo "<tr class='i_table_row_1'>";}
					else{echo "<tr class='i_table_row_0'>";}
					echo "<td align='center' width='80px' valign='top' style='padding:4px 3px 3px 0px;'>".$records['TOID']."</td>";
					echo "<td class='i_table_body' align='center' width='70px'>".$records['TODateFr']."</td>";
					echo "<td class='i_table_body' align='center' width='70px'>".$records['TODateTo']."</td>";
					$TOTo=$MySQLi->sqlQuery("SELECT CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName FROM `tblemppersonalinfo` JOIN `tblemptravelorders` ON `tblemppersonalinfo`.`EmpID`=`tblemptravelorders`.`EmpID` WHERE `tblemptravelorders`.`TOID`='".$records['TOID']."';");
					$EmpName="";
					while($TOs=mysql_fetch_array($TOTo)){$EmpName=$EmpName.$TOs['EmpName']."<br/>";}
					echo "<td class='i_table_body'>".$EmpName."</td>";
					echo "<td class='i_table_body' width='150px'>".$records['TODestination']."</td>";
					echo "<td class='i_table_body' width='85px'>".$records['TOStatus']."</td>";
					echo "<td class='i_table_body' width='130px'>".$records['TORemarks']."</td>";
					echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Confirm' onClick='confirmPLStime(\"".$records['TOSubject']."\",\"".$records['TOID']."\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";
					echo "<td style='width:20px;text-align:center;border-left:0px dotted #6D84B4;padding:2px 3px 1px 0px;' valign='top'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Print' onClick='printPLSinfo(\"".$records['TOSubject']."\",\"".$records['TOID']."\");'><span class='ui-icon ui-icon-print'></span></li></ul></td>";
					echo "</tr>";
					$n+=1;
				}
				$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
				if(($Authorization[2])&&(($_SESSION['user']==$EmpID)||($Authorization[0]))){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('trav','','',0);";}
			?>
		</table>
	</div>
	<table style="width:900px;">
		<tr>
			<td style="width:100%;text-align:left">
				<input type="button" value="New Travel Order" class="<?php echo $bAddClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bAddState; ?>/>
			</td>
		</tr>
	</table>
</center>


