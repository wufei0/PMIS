<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD021'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$Yr=isset($_POST['Yr'])?trim(strip_tags($_POST['Yr'])):date('Y');
	$Mo=isset($_POST['Mo'])?trim(strip_tags($_POST['Mo'])):date('m');
	$St=isset($_POST['St'])?trim(strip_tags($_POST['St'])):'XX';
	
	$PLDateDay=date('d');
	$PLDateMonth=date('m');
	$PLDateYear=date('Y');

	$Config=new Conf();
	$MySQLi=new MySQLClass($Config);
?>

<center><br/>
	<form name="to_filter" id="to_filter" onSubmit="FilterQuery(this);return false;">
	<table class="filter_bar" cellspacing="0" cellpadding="0" style="width:900px;">
		<tr>
			<td class="form_label_l filter_bar" style="width:55px;"><label>FILTER:</label></td>
			<td class="form_label filter_bar" style="width:35px;"><label>Year:</label></td>
				<td class="pds_form_input filter_bar" style="width:53px;">
					<select id="flt_year" name="flt_year" class="text_input" style="width:53px;" onChange="FilterQuery(document.getElementById('to_filter'));" >
					<?php for($y=2010;$y<=date('Y');$y++){if($y==$Yr){echo "<option value='$y' selected>".$y."</option>";}else{echo "<option value='$y'>".$y."</option>";}} ?>
					</select>
				</td>
			<td class="form_label filter_bar" style="width:40px;"><label>Month:</label></td>
				<td class="pds_form_input filter_bar" style="width:90px;">
					<select id="flt_month" name="flt_month" class="text_input" style="width:90px;" onChange="FilterQuery(document.getElementById('to_filter'));" >
					<?php for($m=1;$m<=12;$m++){if($m==$Mo){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
					</select>
				</td>
			<td class="form_label filter_bar" style="width:55px;"><label>Status:</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_show_all" name="flt_status" value="XX" <?php if($St=="XX"){echo "checked='checked'";$StStr="";}?> onClick="FilterQuery(document.getElementById('to_filter'));"/>
				</td>
				<td class="form_label_l" style="width:55px;"><label>SHOW ALL</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_posted" name="flt_status" value="PO" <?php if($St=="PO"){echo "checked='checked'";$StStr=" AND `TOStatus` LIKE 'PO%'";}?> onClick="FilterQuery(document.getElementById('to_filter'));"/>
				</td>
				<td class="form_label_l" style="width:40px;"><label>POSTED</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_checked" name="flt_status" value="CH" <?php if($St=="CH"){echo "checked='checked'";$StStr=" AND `TOStatus` LIKE 'CH%'";}?> onClick="FilterQuery(document.getElementById('to_filter'));"/>
				</td>
				<td class="form_label_l" style="width:50px;"><label>CHECKED</label></td>
				<td align="right" style="width:40px;">
					<input type="radio" id="st_approved" name="flt_status" value="AP" <?php if($St=="AP"){echo "checked='checked'";$StStr=" AND `TOStatus` LIKE 'AP%'";}?> onClick="FilterQuery(document.getElementById('to_filter'));"/>
				</td>
				<td class="form_label_l" style="width:55px;"><label>APPROVED</label></td>
				<?php
				
				$records=Array();
				$total_records=$MySQLi->sqlQuery("SELECT `PLID` FROM `tblemppersonnellocator`;");
				$result=$MySQLi->sqlQuery("SELECT `tblemppersonnellocator`.`EmpID`,CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName,`tblemppersonnellocator`.`PLID`, CONCAT_WS(' ',`tblemppersonnellocator`.`PLDateDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-',`tblemppersonnellocator`.`PLDateYear`,`tblemppersonnellocator`.`PLDateMonth`,`tblemppersonnellocator`.`PLDateDay`)), 1, 3)),`tblemppersonnellocator`.`PLDateYear`) AS PLDate, `tblemppersonnellocator`.`PLDestination`, `tblemppersonnellocator`.`PLPurpose`, `tblemppersonnellocator`.`PLTimeOut`, `tblemppersonnellocator`.`PLTimeIn`, `tblemppersonnellocator`.`PLNotedBy`, `tblemppersonnellocator`.`PLRemarks` FROM `tblemppersonnellocator` JOIN `tblemppersonalinfo` ON `tblemppersonnellocator`.`EmpID`=`tblemppersonalinfo`.`EmpID` ORDER BY `PLDateYear` ASC, `PLDateMonth` ASC,`PLDateDay` ASC ;"); //WHERE `PLDateYear` = '".$PLDateYear."' AND `PLDateMonth` = '".$PLDateMonth."' AND `PLDateDay` = '".$PLDateDay."'
				
				?>
			<td class="form_label filter_bar"><?php echo "<label>Returned </label>".mysql_num_rows($result)."<label> of </label>".mysql_num_rows($total_records)."<label> records.</label>"; ?></td>
		</tr>
	</table>
	</form>
	<table class="i_table" style="width:898px;">
		<tr>
			<td class="i_table_header_1st" style="padding:0px 3px 0px 3px;" width="100">Locator Slip Number</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="70px">From</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="150">Name</td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;">Destination<br /></td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;">Purpose<br /></td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="45">Time<br/>Out<br /></td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="45">Time<br/>In<br /></td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;">Noted By<br /></td>
			<td class="i_table_header" style="padding:0px 3px 0px 3px;" width="90">Remarks</td>
			<td class="i_table_header" colspan="40" width="22px">&nbsp;</td>
		</tr>
	</table>
	<div style="height:270px;width:898px;border:1px dotted #6D84B4;margin-left:20px;margin-right:20px;padding:0px;overflow-x:hidden;overflow-y:scroll;">
		<table style="width:881px;" cellspacing="0">
		<?php
			$n=1;
			while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				if($n%2==0){echo "<tr class='i_table_row_0'>";}
				else{echo "<tr class='i_table_row_1'>";}
				echo "<td width='100px' valign='top' align='center' style='padding:4px 3px 3px 0px;'>".$records['PLID']."</td>";
				echo "<td class='i_table_body'>".$records['EmpName']."</td>";
				echo "<td class='i_table_body'>".$records['PLDestination']."</td>";
				echo "<td class='i_table_body'>".$records['PLPurpose']."</td>";
				echo "<td class='i_table_body' align='center'>".$records['PLTimeOut']."</td>";
				echo "<td class='i_table_body' align='center'>".$records['PLTimeIn']."</td>";
				echo "<td class='i_table_body'>".$records['PLNotedBy']."</td>";
				echo "<td class='i_table_body'>".$records['PLRemarks']."</td>";
				echo "<td class='i_table_body' style='width:22px;text-align:center;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Confirm' onClick='confirmPLStime(\"".$records['EmpID']."\",\"".$records['PLID']."\");'><span class='ui-icon ui-icon-check'></span></li></ul></td>";
				echo "<td class='i_table_body' style='width:22px;text-align:center;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Print' onClick='printPLSinfo(\"".$records['EmpID']."\",\"".$records['PLID']."\");'><span class='ui-icon ui-icon-print'></span></li></ul></td>";
				echo "</tr>";
				$n+=1;
			}
		?>
		</table>
	</div>
	<?php if(1==1){echo "<table style='width:900px;'><tr><td style='width:100%;text-align:left'><input type='button' value='New PLS' onClick='formPersonnelLocator(\"$EmpID\",\"\",0);'/></td></tr></table>";} ?>
</center>


