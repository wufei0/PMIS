<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD015'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$RatingID=isset($_POST['xid'])?trim(strip_tags($_POST['xid'])):'';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):'0';
	echo "1|$EmpID|";
	
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$records=Array();
		$InputState="";
		if($mode==-1){
			$result=$MySQLi->sqlQuery("SELECT * FROM tblempratings WHERE EmpID = '".$EmpID."' AND RatingID = '".$RatingID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
			$InputState="disabled";
		}
		else if($mode==0){
			$records['FirstSemesterScore']=$records['SecondSemesterScore']=$records['OverAllScore']="";
			$records['FirstSemesterRating']=$records['SecondSemesterRating']=$records['OverAllRating']="";
			$records['RatingYear']="";
		}
		else if($mode==1){
			$result=$MySQLi->sqlQuery("SELECT * FROM tblempratings WHERE EmpID = '".$EmpID."' AND RatingID = '".$RatingID."' LIMIT 1;");
			$records=mysqli_fetch_array($result, MYSQLI_BOTH);
		}
?>
<center>
	<form name="f_PR_info" onSubmit="processForm('pr',this);return false;"><br/>
		<table class="form_window">
			<tr>
				<td class="form_label" style="width:100px;"><label>JAN TO JUN SCORE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['FirstSemesterScore']; ?>" type="text" name="FirstSemesterScore" id="FirstSemesterScore" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:100px;" onKeyup="evalScore(this);" /></td>
				<td class="form_label" style="width:100px;"><label>JAN TO JUN RATING: </label></td>
				<td class="pds_form_input">
					<select name="FirstSemesterRating" id="FirstSemesterRating">
						<option value="">-</option>					
						<option value="O" <?php if ($records['FirstSemesterRating'] == "O") echo 'selected="selected"'; ?>>Outstanding</option>
						<option value="VS" <?php if ($records['FirstSemesterRating'] == "VS") echo 'selected="selected"'; ?>>Very Satisfactory</option>
						<option value="S" <?php if ($records['FirstSemesterRating'] == "S") echo 'selected="selected"'; ?>>Satisfactory</option>
						<option value="U" <?php if ($records['FirstSemesterRating'] == "U") echo 'selected="selected"'; ?>>Unsatisfactory</option>
						<option value="P" <?php if ($records['FirstSemesterRating'] == "P") echo 'selected="selected"'; ?>>Poor</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="form_label"><label>JUL TO DEC SCORE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['SecondSemesterScore']; ?>" type="text" name="SecondSemesterScore" id="SecondSemesterScore" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:100px;" onKeyup="evalScore(this);" /></td>
				<td class="form_label" style="width:100px;"><label>JUL TO DEC RATING: </label></td>
				<td class="pds_form_input">
					<select name="SecondSemesterRating" id="SecondSemesterRating">
						<option value="">-</option>					
						<option value="O" <?php if ($records['SecondSemesterRating'] == "O") echo 'selected="selected"'; ?>>Outstanding</option>
						<option value="VS" <?php if ($records['SecondSemesterRating'] == "VS") echo 'selected="selected"'; ?>>Very Satisfactory</option>
						<option value="S" <?php if ($records['SecondSemesterRating'] == "S") echo 'selected="selected"'; ?>>Satisfactory</option>
						<option value="U" <?php if ($records['SecondSemesterRating'] == "U") echo 'selected="selected"'; ?>>Unsatisfactory</option>
						<option value="P" <?php if ($records['SecondSemesterRating'] == "P") echo 'selected="selected"'; ?>>Poor</option>
					</select>
				</td>
			</tr>
			<tr>
				<td class="form_label"><label>OVERALL SCORE: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['OverAllScore']; ?>" type="text" name="OverAllScore" id="OverAllScore" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:100px;" /></td>
				<td class="form_label" style="width:100px;"><label>OVERALL RATING: </label></td>
				<td class="pds_form_input">
					<select name="OverAllRating" id="OverAllRating">
						<option value="">-</option>					
						<option value="O" <?php if ($records['OverAllRating'] == "O") echo 'selected="selected"'; ?>>Outstanding</option>
						<option value="VS" <?php if ($records['OverAllRating'] == "VS") echo 'selected="selected"'; ?>>Very Satisfactory</option>
						<option value="S" <?php if ($records['OverAllRating'] == "S") echo 'selected="selected"'; ?>>Satisfactory</option>
						<option value="U" <?php if ($records['OverAllRating'] == "U") echo 'selected="selected"'; ?>>Unsatisfactory</option>
						<option value="P" <?php if ($records['OverAllRating'] == "P") echo 'selected="selected"'; ?>>Poor</option>
					</select>
				</td>
			</tr>			
			<tr>
				<td class="form_label" style="width:100px;"><label>YEAR: </label></td>
				<td class="pds_form_input"><input value="<?php echo $records['RatingYear']; ?>" type="text" name="RatingYear" id="RatingYear" class="text_input sml_frm_fld" <?php echo $InputState; ?> style="width:100px;" /></td>
				<td colspan="2">&nbsp;</td>
			</tr>
		</table>
		<br/>
		<hr class="form_bottom_line_window"/>
		<table class="form_window">
			<tr>
				<td align="left"><input type="button" value="Help" class="button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right"><input type="submit" value="<?php if($mode==-1){echo'Confirm Delete';}else{echo'Save';} ?>" class="button ui-button ui-widget ui-corner-all"/>&nbsp;<input type="button" value="Cancel" class="button ui-button ui-widget ui-corner-all" onClick="closeDialogWindow('d_form_input');return false;" /></td>
			</tr>
		</table>
		<input type="hidden" name="mode" id="mode" value="<?php echo $mode; ?>" />
		<input type="hidden" name="RatingID" id="RatingID" value="<?php echo $RatingID; ?>" />
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center>
<script type="text/javascript">
	
	var ratings = {
		FirstSemesterScore: "FirstSemesterRating",
		SecondSemesterScore: "SecondSemesterRating",
		OverAllScore: "OverAllRating"
	};
	
 	function rating(score) {
		if (score == '') return "-";
		if ((score >= 0) && (score <= 1.99)) return "P";
		if ((score >= 2) && (score <= 2.99)) return "U";
		if ((score >= 3) && (score <= 3.99)) return "S";
		if ((score >= 4) && (score <= 4.99)) return "VS";
		if (score >= 5) return "O";
	}
	
	function evalScore(e) {

		var id = ratings[e.name];
		var score = $('#'+e.id).val();
		$('#'+id).val(rating(score));

		// OverAllScore OverAllRating
		var fs = ($('#FirstSemesterScore').val() == '') ? 0 : $('#FirstSemesterScore').val()
		var ss = ($('#SecondSemesterScore').val() == '') ? 0 : $('#SecondSemesterScore').val()

		var os = (parseFloat(fs)+parseFloat(ss))/2;
		if (ss == 0) os = parseFloat(fs);
		if (fs == 0) os = parseFloat(ss);
		// $('#OverAllScore').val(os.toFixed(2));
		$('#OverAllScore').val(os);
		$('#OverAllRating').val(rating(os));

	}
	
</script>
<?php } ?>