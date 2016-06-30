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

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$LivAppID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	
	$MONTHS=Array('','JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC');
	echo "1|$EmpID|";
	
	$MySQLi=new MySQLClass();

?>
	
<center>
	<form id="gen_rpt_form" name="gen_rpt_form" onSubmit="generateReport(); return false;"><br/>
		<div class="ui-widget-content ui-corner-all" style="padding:5px;margin:0px 5px 5px 5px;">	
			<table class="">
				<tr>
					<td class="form_label" style="width:70px;"><label>REPORT TYPE: </label></td>
					<td class="pds_form_input">
						<select id="rtype" name="rtype" class="text_input sml_frm_fld" onChange="showDiv(this.value);">
							<?php
								$rType=[
									"rpt_el"=>"Eligibilities and Licences",
									"rpt_sr"=>"Service Records",
									"rpt_ed"=>"Educational Attainments",
									"rpt_el"=>"Eligibilities and Licences",
									"rpt_hp"=>"HR Profile",
								];
								foreach($rType as $value => $text){echo "<option value='".$value."'>".$text."</option>";}
							?>
						</select>
					</td>
				</tr>
			</table>
		</div>
		
		<!-- Eligibilities and Licences Form -->
		<div id="rpt_el" name="rpt_form" class="ui-widget-content ui-corner-all" style="padding:5px;margin:0px 5px 5px 5px;">	
			<table class="">
				
				<tr>
					<td class="form_label" style="width:70px;"><label>OFFICE: </label></td>
					<td class="pds_form_input">
						<select id="r_off" name="off" class="text_input sml_frm_fld" onChange="">
							<?php
								echo "<option value='0'>ALL</option>";
								$result=$MySQLi->sqlQuery("SELECT `SubOffID`, `SubOffCode` FROM `tblsuboffices` WHERE `SubOffID` <> 'SO000' ORDER BY `SubOffCode`;");
								while($offinfo=mysqli_fetch_array($result, MYSQLI_BOTH)) {
									echo "<option value='".$offinfo[0]."'>".$offinfo[1]."</option>";
								} unset($result);
							?>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="form_label" style="width:70px;"><label>DEVISION: </label></td>
					<td class="pds_form_input">
						<select id="r_dev" name="dev" class="text_input sml_frm_fld" onChange="" disabled>
							<option value=""></option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="form_label" style="width:70px;"><label>SEX: </label></td>
					<td class="pds_form_input">
						<select id="r_sex" name="sex" class="text_input sml_frm_fld" onChange="" >
							<option value="0">ALL</option><option value="MALE">MALE</option><option value="FEMALE">FEMALE</option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="form_label" style="width:70px;"><label>APPT. STATUS: </label></td>
					<td class="pds_form_input">
						<select id="r_sta" name="sta" class="text_input sml_frm_fld" onChange="">
							<?php
								echo "<option value='0'>ALL</option>";
								$result=$MySQLi->sqlQuery("SELECT `ApptStID`, `ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID` <> 'AS000' ORDER BY `ApptStDesc`;");
								while($offinfo=mysqli_fetch_array($result, MYSQLI_BOTH)) {
									echo "<option value='".$offinfo[0]."'>".$offinfo[1]."</option>";
								} unset($result);
							?>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="form_label" style="width:70px;"><label>EMP. STATUS: </label></td>
					<td class="pds_form_input">
						<select id="r_est" name="est" class="text_input sml_frm_fld" onChange="" >
							<option value="0">ALL</option>
							<option value="ACTIVE">ACTIVE</option>
							<option value="INACTIVE">INACTIVE</option>
							<option value="DEAD FILE">DEAD FILE</option>
							<option value="ON LEAVE">ON LEAVE</option>
						</select>
					</td>
				</tr>
				
			</table>
		</div>
		
		<!-- Service Records Form -->
		<div id="rpt_sr" name="rpt_form" class="ui-widget-content ui-corner-all" style="padding:5px;margin:0px 5px 5px 5px;">	
			<table class="">
				
				<tr>
					<td class="form_label" style="width:70px;"><label>EMPLOYEE ID: </label></td>
					<td class="pds_form_input">
						<input id="r_id" name="id" class="text_input sml_frm_fld" />
					</td>
				</tr>
				
			</table>
		</div>
		
		<!-- HR Profiles Report Form -->
		<div id="rpt_hp" name="rpt_form" class="ui-widget-content ui-corner-all" style="padding:5px;margin:0px 5px 5px 5px;">	
			<table class="">
				
				<tr>
					<td class="form_label"><label>EMPLOYMENT STATUS: </label></td>
					<td class="pds_form_input" style="width:80px;">
						<input type="checkbox" id="r_empSt" name="empst" class="text_input">
					</td>
				</tr>
				
				<tr>
					<td class="form_label"><label>LEVEL OF POSITION: </label></td>
					<td class="pds_form_input" style="width:80px;">
						<input type="checkbox" id="r_lvlPos" name="lvlpos" class="text_input">
					</td>
				</tr>
				
				<tr>
					<td class="form_label"><label>SALARY GRADE: </label></td>
					<td class="pds_form_input" style="width:80px;">
						<input type="checkbox" id="r_salGrd" name="salgrd" class="text_input">
					</td>
				</tr>
				
				<tr>
					<td class="form_label"><label>AGE RANGE: </label></td>
					<td class="pds_form_input" style="width:80px;">
						<input type="checkbox" id="r_agern" name="agern" class="text_input">
					</td>
				</tr>
				
			</table>
		</div>
		
		<br/>
		<hr class="form_bottom_line_window"/>
		
		<table style="width:360px;">
			<tr>
				<td align="left"><input type="button" value="Help" class="button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right"><input type="submit" value="Generate" class="button ui-button ui-widget ui-corner-all"/>&nbsp;<input type="button" value="Cancel" class="button ui-button ui-widget ui-corner-all" onClick="closeDialogWindow('win_sys_rpt');return false;" /></td>
			</tr>
		</table>

	</form>
</center>

<script type='text/javaScript'>

	function generateReport(){
		var url = "reports/"+$('#rtype').val()+".php?";
		$('#gen_rpt_form [id^=r_]').each(function(){url+=$(this).prop("name")+"="+$(this).val()+"&";});
		window.open(url,'mywindow','width=800,height=600');
		return false;
	}
	
	function showDiv(id){
		$('[name=rpt_form]').each(function(){$(this).hide();});
		$('#'+id).show();
	}
	
	showDiv("rpt_el");
	//
</script>


