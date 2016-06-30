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
	
	
	//if(date('m'!='12')){echo "0|".$_SESSION['user']."|ERROR 49:~Processing Force Leave Certification will only be allowed on the last month of the year (December 1 to 31).";exit();}
	require_once $_SESSION['path'].'/lib/classes/Functions.php';
	

	$PerID=isset($_POST['uid'])?trim(strip_tags($_POST['uid'])):"0";
	$UpdCGrpID=isset($_POST['uug'])?trim(strip_tags($_POST['uug'])):"0";
	$CGrpID=isset($_POST['gid'])?trim(strip_tags($_POST['gid'])):"CG00";
	$isReadOnly=isset($_POST['ro'])?trim(strip_tags($_POST['ro'])):'1';
	
	$isReadOnly=($isReadOnly==1)?true:false;
	$ReadOnly=($isReadOnly)?"disabled":"";
	
	//$UpdCGrpID=($UpdCGrpID==1)?true:false;
	
	$FLCYear="2014";
	$cYear=date('Y');
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	
	echo "1|$PerID|";
	?> 
<form name="pers_flm" id="pers_flm" onSubmit="processLeaveManager(this); return false;">
	<?php
	
	$MySQLi=new MySQLClass();
	$fLeave=new LeaveFunctions();
	
	if(!$UpdCGrpID){
		$sql="SELECT `EmpID`, `EmpName`, `CGrpID` FROM `s_personnel` WHERE `EmpID`='$PerID' LIMIT 1;";
		if(!$PersInfo=$MySQLi->GetArray($sql)){$PersInfo['EmpID']=$PersInfo['EmpName']=$PersInfo['CGrpID']="";}
		if($isReadOnly){$CGrpID=$PersInfo['CGrpID'];}
	}
	?>
	
	<!-- User small info -->
	<div style="background:#E6EFFF;width:auto;margin-bottom:4px;<?php if($UpdCGrpID){echo"display:none;";} ?>" id="user_div" class="ui-widget ui-widget-content ui-corner-all" >
		<table style="border-spacing:0px;border:0px solid #6D84B4;width:450px;">
			<tr>
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>PERSONNEL:</label></td>	
				<td colspan="3" style="padding:3px 0px 0px 0px;"><input value="<?php echo $PersInfo['EmpName']; ?>" name="UserName" id="UserName" class="text_input" style="width:375px" disabled type="text"></td>
			</tr>
			<tr>
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>ID:</label></td>	
				<td style="padding:3px 0px 3px 0px;"><input value="<?php echo $PersInfo['EmpID']; ?>" name="PerID" id="PerID" class="text_input" style="width:40px" disabled type="text"></td>
				
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:70px"><label>OFFICE/GROUP:</label></td>
				<td style="padding:3px 0px 3px 0px;width:200px">
					<select name="CGrpID" id="CGrpID" class="text_input search_select" style="width:200px" <?php echo $ReadOnly; ?>>
						<?php
							$result=$MySQLi->sqlQuery("SELECT `CGrpID`,`CGrpCode` FROM `tblempcgroups` WHERE `CGrpID` <> 'CG00' ORDER BY `CGrpCode`;");
							while($CGrp=mysqli_fetch_array($result, MYSQLI_BOTH)){
								if($CGrpID==$CGrp['CGrpID']){echo "<option value='".$CGrp['CGrpID']."' selected>".$CGrp['CGrpCode']."</option>";}
								else{echo "<option value='".$CGrp['CGrpID']."'>".$CGrp['CGrpCode']."</option>";}
							} unset($result);
						?>
					</select>
				</td>
			</tr>
		</table>
	</div>
	<?php

	if(!$GrpInfo=$MySQLi->GetArray("SELECT `CGrpCode`, `CGrpName` FROM `tblempcgroups` WHERE `CGrpID` = '$CGrpID';")){$GrpInfo['CGrpCode']=$GrpInfo['CGrpName']="";}
	
	?>
	<!-- Group small info -->
	<div style="background:#E6EFFF;width:auto;margin-bottom:4px;<?php if(!$UpdCGrpID){echo"display:none;";} ?>" id="user_group_div" class="ui-widget ui-widget-content ui-corner-all" >
		<table style="border-spacing:0px;border:0px solid #6D84B4;width:450px;">
			<tr>
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:80px"><label>GROUP ID:</label></td>	
				<td style="padding:3px 0px 0px 0px;"><input value="<?php echo $CGrpID; ?>" name="CGrpID" id="CGrpID" class="text_input" style="width:150px" disabled type="text"/></td>
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:80px"><label>CODE:</label></td>
				<td style="padding:3px 0px 0px 0px;width:100px"><input value="<?php echo $GrpInfo['CGrpCode']; ?>" name="GroupCode" id="GroupCode" class="text_input" style="width:100px" disabled type="text"/></td>
			</tr>
			<tr>
				<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:100px"><label>GROUP NAME:</label></td>	
				<td colspan="3" style="padding:3px 0px 3px 0px;"><input value="<?php echo $GrpInfo['CGrpName']; ?>" name="GroupName" id="GroupName" class="text_input" style="width:360px" disabled type="text"/></td>
			</tr>
		</table>
	</div>
	
	<!-- Leave Manager window -->
	<?php if(!$UpdCGrpID){ // Single Personnel ?>
	
	
	<!-- Privilege Leave Manager -->
	<table style="border-spacing:0px;border-bottom:1px solid #FFF;width:460px;">
		<tr><td class="search_header" style="text-align:center;">PRIVELEGE LEAVES</td></tr>
	</table>
	<table style="border-spacing:0px;border:1px solid #6D84B4;width:460px;">
		<tr><td class="search_header" style="text-align:center;width:30px;">&nbsp;</td><td class="search_header" style="text-align:center;">DESCRIPTION</td><td class="search_header" style="text-align:center;width:80px;">Number of Days</td><td class="search_header" style="text-align:center;width:150px;">VALID UNTIL</td><td class="search_header" style="text-align:center;width:20px;">&nbsp;</td></tr>
	</table>
	<div class="" style="border:1px solid #6D84B4;width:458px;height:178px;overflow-x:hidden;overflow-y:scroll;">			
		<table style="border-spacing:0px;border:0px solid #6D84B4;width:438px;">
		<?php
			$PL = Array();
			$sql="SELECT `PLID`, `ValidFrom`, `ValidTo` FROM `tblempprivilegeleaves` WHERE `EmpID` = '".$PerID."';";
			$EPL_res=$MySQLi->sqlQuery($sql);
			while($EPLs=mysqli_fetch_array($EPL_res, MYSQLI_BOTH)){$PL[$EPLs['PLID']]=["ValidFrom"=>$EPLs['ValidFrom'],"ValidTo"=>$EPLs['ValidTo'],];}
			//print_r($PL);
			$sql="SELECT * FROM `tblprivilegeleaves`;";
			$result=$MySQLi->sqlQuery($sql);
			$row_="0";
			while($PLs=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$Checked="";$ValidFrom=$ValidTo=date('Y-m-d H:i:s');
				if(array_key_exists($PLs['PLID'], $PL)){
					if(($PL[$PLs['PLID']]['ValidFrom']<=date('Y-m-d H:i:s'))&&($PL[$PLs['PLID']]['ValidTo']>=date('Y-m-d H:i:s'))){
						$Checked="checked";
						$ValidFrom=$PL[$PLs['PLID']]['ValidFrom'];$ValidTo=$PL[$PLs['PLID']]['ValidTo'];
					}
				}
				//echo "PLID: ".$PLs['PLID']." ValidFrom: ".$ValidFrom." ValidTo: ".$ValidTo."<br/>";
		?>
			<tr class='search_result_row_<?php echo $row_; ?>'>
				<td class='search_result' align='center' width='30px'>
					<input type='checkbox' id='plc_<?php echo $PLs['PLID']; ?>' name='plc_<?php echo $PLs['PLID']; ?>' <?php echo (($PLs['PLID']=='PL001')?"disabled":$ReadOnly)." ".$Checked; ?> onClick="var sRow=this.checked; $('[name=<?php echo $PLs['PLID']; ?>]').each(function(){$(this).prop('disabled',!sRow);});" />
				</td>
				<td class='search_result' title="<?php echo $PLs['PLDescription']; ?>">
					<?php echo $PLs['PLName']; ?>
				</td>
				<td class='search_result' align='center' width='50px'>
					<input type="text" id='pln_<?php echo $PLs['PLID']; ?>' name='<?php echo $PLs['PLID']; ?>' value="<?php echo number_format($PLs['PLNumberOfDays'],1); ?>" style='width:35px;text-align:right;' disabled />
				</td>
				<td class='search_result' align='center' style='width:170px;' >
					<select id='plm_<?php echo $PLs['PLID']; ?>' name='<?php echo $PLs['PLID']; ?>' disabled style="height:19px;">
					<?php
						for($m=1;$m<=12;$m+=1){
							echo "<option value='".(($m>9)?$m:"0".$m)."' ".(($m==substr($ValidTo,5,2))?"selected":(($m==intval(date('m')))?"selected":"")).">".$MONTHS[$m]."</option>";
						}
					?>
					</select>
					<select id='pld_<?php echo $PLs['PLID']; ?>' name='<?php echo $PLs['PLID']; ?>' disabled style="height:19px;">
					<?php
						for($d=1;$d<=31;$d+=1){
							echo "<option value='".(($d>9)?$d:"0".$d)."' ".(($d==substr($ValidTo,8,2))?"selected":(($d==intval(date('d')))?"selected":"")).">".$d."</option>";
						}
					?>
					</select>
					<input id='ply_<?php echo $PLs['PLID']; ?>' name='<?php echo $PLs['PLID']; ?>' value='<?php echo substr($ValidTo,0,4); ?>' style='width:30px;' disabled />
				</td>
			</tr>
		<?php
				$row_=($row_=="1")?"0":"1";
			}
		?>
		</table>
	</div>
	
	<!-- CERTIFICATION FOR UNUSED FORCE LEAVE -->
	<table style="border-spacing:0px;border-bottom:1px solid #FFF;margin-top:3px;width:460px;">
		<tr><td class="search_header" style="text-align:center;">CERTIFICATION FOR UNUSED FORCE LEAVE</td></tr>
	</table>
	<table style="border-spacing:0px;border:1px solid #6D84B4;width:460px;">
		<tr><td class="search_header" style="text-align:center;width:30px;">&nbsp;</td><td class="search_header" style="text-align:center;width:80px;">YEAR</td><td class="search_header" style="text-align:center;width:100px;">Number of Days</td><td class="search_header" style="text-align:center;">Date Submitted</td><td class="search_header" style="text-align:center;width:20px;">&nbsp;</td></tr>
	</table>
	<div class="" style="border:1px solid #6D84B4;width:458px;height:100px;overflow-x:hidden;overflow-y:scroll;">			
		<table style="border-spacing:0px;border:0px solid #6D84B4;width:438px;">
			<?php
				//echo $fLeave->UnusedForceLeave($PerID,2014);
				$sql="SELECT `FLCYear`, `NumberOfDays`, `DateSubmitted` FROM tblempforceleavecert WHERE `EmpID`='".$PerID."';";
				if($MySQLi->NumberOfRows($sql)>0){
					$row_="0";
					$result=$MySQLi->sqlQuery($sql);
					while($Pers=mysqli_fetch_array($result, MYSQLI_BOTH)){
						//$Pers['NumberOfDays']=$fLeave->UnusedForceLeave($PerID,2014);
						$cbSt=($Pers['FLCYear']==$FLCYear)?"checked":"";
						echo "
							<tr class='search_result_row_".$row_."'>
								<td class='search_result' align='center' width='30px'>
									<input type='checkbox' id='flc_cb_".$Pers['FLCYear']."' name='flc_c".$Pers['FLCYear']."' ".$cbSt." ".(($Pers['FLCYear']==date('Y')?$ReadOnly:"disabled"))." 
									onClick='var sRow=this.checked;
										$(\"[name=flc_".$Pers['FLCYear']."]\").each(function(){
											$(this).prop(\"disabled\",!sRow);
										});
									' />
								</td>
								<td class='search_result' align='center' width='80px'>
									<select id='flc_yr' name='flc_".$Pers['FLCYear']."' disabled>
										<option>".$Pers['FLCYear']."</option>
									</select>
								</td>
								<td class='search_result' align='center' width='100px'>
									<select id='flc_dys_".$Pers['FLCYear']."' name='flc_".$Pers['FLCYear']."' disabled>
						";
										for($o=0.0;$o<=5.0;$o+=0.5){
											echo "<option value='".number_format($o,1)."' ".(($o==$Pers['NumberOfDays'])?"selected":"")." max='2'>".number_format($o,1)."</option>";
										}
						echo "
									</select>
								</td>
								<td class='search_result' align='center'>
									<select id='flc_dsm_".$Pers['FLCYear']."' name='flc_".$Pers['FLCYear']."' disabled>
						";
										for($m=1;$m<=12;$m+=1){
											echo "<option value='".$m."' ".(($m==intval(substr($Pers['DateSubmitted'],5,2)))?"selected":"").">".$MONTHS[$m]."</option>";
										}
						echo "
									</select>
									<select id='flc_dsd_".$Pers['FLCYear']."' name='flc_".$Pers['FLCYear']."' disabled>
						";
										for($d=1;$d<=31;$d+=1){
											echo "<option value='".$d."' ".(($d==intval(substr($Pers['DateSubmitted'],8,2)))?"selected":"").">".$d."</option>";
										}
						echo "
									</select>
									<input id='flc_dsy_".$Pers['FLCYear']."' name='flc_".$Pers['FLCYear']."' value='".$Pers['FLCYear']."' style='width:30px;' disabled>
								</td>
							</tr>";
						$row_=($row_=="1")?"0":"1";
					}
				}
				else{
					echo "
						<tr class='search_result_row_0'>
							<td class='search_result' align='center' width='30px'>
								<input type='checkbox' id='flc_cb_".$cYear."' name='flc_c".$cYear."' ".(((date('m')=='12'))?$ReadOnly:"disabled")." 
								onClick='var sRow=this.checked;
									$(\"[name=flc_".$cYear."]\").each(function(){
										$(this).prop(\"disabled\",!sRow);
									});
								' />
							</td>
							<td class='search_result' align='center' width='80px'>
								<select id='flc_yr' name='flc_".$cYear."' disabled>
									<option>".$cYear."</option>
								</select>
							</td>
							<td class='search_result' align='center' width='100px'>
								<select id='flc_dys_".$cYear."' name='flc_".$cYear."' disabled>
					";
									for($o=0.0;$o<=5.0;$o+=0.5){
										echo "<option value='".number_format($o,1)."' ".(($o==floatval($fLeave->UnusedForceLeave($PerID,$cYear)))?"selected":"").">".number_format($o,1)."</option>";
									}
					echo "
								</select>
							</td>
							<td class='search_result' align='center'>
								<select id='flc_dsm_".$cYear."' name='flc_x".$cYear."' disabled>
					";
									for($m=1;$m<=12;$m+=1){
										echo "<option value='".$m."' ".(($m==intval(date('m')))?"selected":"").">".$MONTHS[$m]."</option>";
									}
					echo "
								</select>
								<select id='flc_dsd_".$cYear."' name='flc_x".$cYear."' disabled>
					";
									for($d=1;$d<=31;$d+=1){
										echo "<option value='".$d."' ".(($d==intval(date('d')))?"selected":"").">".$d."</option>";
									}
					echo "
								</select>
								<input id='flc_dsy_".$cYear."' name='flc_x".$cYear."' value='".$cYear."' style='width:30px;' disabled>
							</td>
						</tr>";
				}
			?>
		</table>
	</div>
	
	<?php } else { // Grouped per Office/Group Personnel ?>
	<table style="border-spacing:0px;border:1px solid #6D84B4;width:460px;<?php if(!$UpdCGrpID){echo"display:none;";} ?>">
		<tr><td class="search_header" style="text-align:center;width:75px;">ID</td><td class="search_header" style="text-align:left;">PERSONNEL</td><td class="search_header" style="width:80px;">CERTIFICATION</td><td class="search_header" style="width:45px;">DAYS</td><td class="search_header" width="15px">&nbsp;</td></tr>
	</table>
	
	<div class="" style="border:1px solid #6D84B4;width:458px;height:318px;overflow:auto;">			
		<table style="border-spacing:0px;border:0px solid #6D84B4;width:438px;">
			<?php
				//$result=$MySQLi->sqlQuery("SELECT `s_personnel`.`EmpID`, `s_personnel`.`CGrpID`, `s_personnel`.`EmpName`, `tblempforceleavecert`.* FROM (`s_personnel` JOIN `tblempforceleavecert` ON `s_personnel`.`EmpID` = `tblempforceleavecert`.`EmpID`) WHERE `s_personnel`.`CGrpID` = '".$CGrpID."' AND `tblempforceleavecert`.`FLCYear` = '2014' ORDER BY `s_personnel`.`EmpName`;");
				$sql="SELECT `s_personnel`.`EmpID`, `s_personnel`.`CGrpID`, `s_personnel`.`EmpName` FROM `s_personnel` WHERE `s_personnel`.`CGrpID` = '".$CGrpID."' AND `EmpStatus` = 'ACTIVE' ORDER BY `s_personnel`.`EmpName`;";
				$result=$MySQLi->sqlQuery($sql);
				$row_=0;
				while($Pers=mysqli_fetch_array($result, MYSQLI_BOTH)){
					$fDays=$fLeave->UnusedForceLeave($Pers['EmpID'],$FLCYear);
					if($FLCinfo=$MySQLi->GetArray("SELECT * FROM `tblempforceleavecert` WHERE `FLCYear` = '".$FLCYear."' AND `EmpID` = '".$Pers['EmpID']."';")){$cbSt="checked";$fDays=$FLCinfo['NumberOfDays'];}
					else{$cbSt="";}
					
					echo "
						<tr class='search_result_row_".($row_%2)."'>
							<td class='search_result' align='center' width='80px'>".$Pers['EmpID']."</td>
							<td class='search_result' align='left'>".$Pers['EmpName']."</td>
							<td class='search_result' align='left' width='55px'><input id='c_".$Pers['EmpID']."' name='c_' type='checkbox' ".$cbSt." ".$ReadOnly." onClick='&#36;(\"#ca_\").prop(\"checked\",false);&#36;(\"#d_0\").prop(\"disabled\",true);'/></td>
							<td class='search_result' align='left' width='30px'>";
							
						echo "
								<select id='d_".$Pers['EmpID']."' name='d_' disabled>";
									for($o=0.0;$o<=5.0;$o+=0.5){
										echo "<option value='".number_format($o,1)."' ".(($o==floatval($fDays))?"selected":"").">".number_format($o,1)."</option>";
									}
						echo "
								</select>";
							
					echo "</td>
						</tr>";
					$row_++;//=($row_=="1")?"0":"1";
				}
			?>
		</table>
	</div>
	<div style="border:1px solid #6D84B4;width:458px;height:auto;margin-top:-1px;">
		<table style="border-spacing:0px;border:0px solid #6D84B4;width:438px;">
			<tr class='search_result_row_1'>
				<td class='search_result' align='right'>SELECT ALL (<?php echo $row_; ?> records)</td>
				<td class='search_result' align='left' width='55px'>
					<input id='ca_' name='ca_' type='checkbox' <?php echo $ReadOnly; ?> onClick="
						flchkall=this.checked;
						$('input[name=c_]').each(function(){
							$(this).prop('checked',flchkall);
						});
						$('select[name=d_0]').each(function(){
							$(this).prop('disabled',!flchkall);
							if(!flchkall){$(this).val('0.0');}
							else{$(this).val('5.0');}
						});
					"/>
				</td>
				<td class='search_result' align='left' width='30px'>
					<select id='d_0' name='d_0' disabled onChange="var d_val=this.value; $('select[name^=d_]').each(function(){$(this).val(d_val);});">
						<option value='0' selected>0.0</option>
						<option value='1.0'>1.0</option>
						<option value='1.5'>1.5</option>
						<option value='2.0'>2.0</option>
						<option value='2.5'>2.5</option>
						<option value='3.0'>3.0</option>
						<option value='3.5'>3.5</option>
						<option value='4.0'>4.0</option>
						<option value='4.5'>4.5</option>
						<option value='5.0'>5.0</option>
					</select></td>
			</tr>
		</table>
	</div>
	<?php  } ?>
</form>
