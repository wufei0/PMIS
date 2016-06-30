<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD017'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	if((!$Authorization[0])&&($_SESSION['user']!=$EmpID)){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	$MONTHS = Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	
?>
<center><br/>
	<form name="DTR_emp_form" id="DTR_emp_form" onSubmit="getDTR(this,'emp'); return false;">
		<table class="filter_bar" style="width:650px;">
			<tr>
				<td class="form_label filter_bar" style="width:50px;"><label>MONTH: </label></td>
				<td class="pds_form_input">
					<select id="SelectMonth" name="SelectMonth" class="text_input">
					<?php
					for($m=1;$m<=12;$m+=1) { 
						if($m==date('n')) { echo "<option value='$m' selected>".$MONTHS[$m]."</option>"; }
						else { echo "<option value='$m'>".$MONTHS[$m]."</option>"; }
					}
					?>
					</select>
				</td>
				<td class="form_label filter_bar" style="width:50px;"><label>YEAR: </label></td>
				<td class="pds_form_input"><input type="text" id="SelectYear" name="SelectYear" class="text_input" value="<?php echo date('Y'); ?>"></td>
				<td class="form_label filter_bar" style="width:50px;"><label>PERIOD: </label></td>
				<td class="pds_form_input">
					<select id="SelectPayPeriod" name="SelectPayPeriod" class="text_input">
						<option value="0">Whole Month</option>
						<option value="1">First Half</option>
						<option value="2">Second Half</option>
					</select> 
				</td>
				<td class="pds_form_input" style="text-align:right;"><input type="submit" value="View" class="button ui-button ui-widget ui-corner-all" /><input type="button" value="Save/Print" class="button ui-button ui-widget ui-corner-all" onClick="printDTR(document.getElementById('DTR_emp_form'),'emp');"/></td>
			</tr>
		</table>
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
	<br/>
	<span name="DTR_box_emp" id="DTR_box_emp"> </span>
	
	
</center>

	<div id="d_input_time" title="Alter DTR">
		<table>
			<tr>
				<td class="form_label" style="width:100px;"><label>DATE:</label></td>
				<td class="pds_form_input" colspan='2'>
				<select id="LivAppFiledMonth" name="LivAppFiledMonth" class="text_input" disabled>
					<?php for($m=1;$m<=12;$m++){if($m==date('m')){echo "<option value='$m' selected>".$MONTHS[$m]."</option>";}else{echo "<option value='$m'>".$MONTHS[$m]."</option>";}} ?>
				</select>
				<select id="LivAppFiledDay" name="LivAppFiledDay" class="text_input" disabled>
					<?php for($d=1;$d<=31;$d++){if($d==date('d')){echo "<option value='$d' selected>$d</option>";}else{echo "<option value='$d'>$d</option>";}} ?>
				</select>
				<input type="text" name="LivAppFiledYear" id="LivAppFiledYear" class="text_input" style="width:30px;" value="<?= date('Y')?>" disabled/>
				</td>
			</tr>
		</table>
	</div>
	
	<script type='text/javaScript'>
		$("#d_input_time").dialog({modal:true,autoOpen:false,width:360});
		function alterThis(td,i){
			var tx="<input type='text' id='"+i+"' class='dtr_input_box' onBlur='_fixTimeText(this);'>";
			td.innerHTML=tx;
			$('#'+i).focus();
			// td.innerHTML=(td.innerHTML=='-')?tx:'-';
		}
		function _fixTimeText(t){
			var v=t.value.match(new RegExp('.{1,2}', 'g'));
			t.value=v[0]+":"+v[1]+":"+v[2];
		}
	</script>
	
	<script type='text/css'>
		
	</script>
	