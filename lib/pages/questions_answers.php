<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD016'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$EmpID=isset($_POST['id'])?trim(strip_tags($_POST['id'])):'00000';
	$mode=isset($_POST['mode'])?trim(strip_tags($_POST['mode'])):0;  /* 0 - VIEW, 1 - UPDATE */
	
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	if($EmpID!='00000'){
		if($mode==0){$InputState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all";$bSaveClass="button ui-button ui-widget ui-corner-all ui-state-disabled";}
		else if($mode==1){$InputState="";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$bSaveClass="button ui-button ui-widget ui-corner-all";}
?>
<center>
<br/>
	<form name="f_emp_info" onSubmit="processForm('qnda',this);return false;"><br/>
		<?php
		$MySQLi=new MySQLClass();
		
		$tblempanswers = Array();
		$qry_answers = $MySQLi -> sqlQuery("SELECT * FROM `tblempanswers` WHERE `EmpID` = '$EmpID';");
		$tblempanswers = Array("Q360"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q361"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q362"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q370"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q371"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q372"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q380"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q390"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q400"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q410"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q411"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q412"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""),"Q413"=>Array("AnsID"=>"","AnsIsYes"=>0,"AnsDetails"=>""));
		while($a_records=mysqli_fetch_array($qry_answers, MYSQLI_BOTH)) {
			$QuesID = $a_records['QuesID'];
			$tblempanswers[$QuesID]['AnsID'] = $a_records['AnsID'];
			$tblempanswers[$QuesID]['AnsIsYes'] = $a_records['AnsIsYes'];
			$tblempanswers[$QuesID]['AnsDetails'] = $a_records['AnsDetails'];
		}
		
		$qry_questions = $MySQLi -> sqlQuery("SELECT `QuesID`, `QuesNumber`, `QuesDesc` FROM `tblquestions`;");
		while($q_records=mysqli_fetch_array($qry_questions, MYSQLI_BOTH)) {
			$lines  = "";
			if ((is_numeric($q_records['QuesNumber']))&&($q_records['QuesNumber']!=38)&&($q_records['QuesNumber']!=39)&&($q_records['QuesNumber']!=40)) { $lines .= "<table class='form'><tr><td style='width:30px;text-align:right;vertical-align:text-top;'><label style='font-size:1.1em;'><b>".$q_records['QuesNumber'].".</b></label></td><td style='font-size:1.1em;text-align:justify;vertical-align:text-top;'>".$q_records['QuesDesc']."</td></tr></table>"; }
			else { 
				$lines .= "<table class='form'><tr>";
				if (!is_numeric($q_records['QuesNumber'])) { $lines .= "<td style='width:30px;text-align:right;vertical-align:text-top;'>&nbsp;</td><td style='width:15px;text-align:center;vertical-align:text-top;'>"; }
				else { $lines .="<td style='width:30px;text-align:right;vertical-align:text-top;'>"; }
				$lines .= "<label style='font-size:1.1em;'><b>".$q_records['QuesNumber'].".</b></label></td><td style='font-size:1.1em;text-align:justify;vertical-align:text-top;'>".$q_records['QuesDesc']."</td></tr></table>"; 
				$lines .= "<table class='form'><tr>"; 
				$lines .= "<td style='width:30px;font-size:1.1em;text-align:right;vertical-align:text-top;'>&nbsp;</td><td style='width:15px;font-size:1.1em;text-align:center;vertical-align:text-top;'>&nbsp;</td>"; 
				
				$QuesID = $q_records['QuesID'];
				if ($tblempanswers[$QuesID]['AnsIsYes']) {
					$lines .= "<td style='width:30px;text-align:right;vertical-align:text-top;'><input type='radio' name='".$q_records['QuesID']."' value='1' checked='checked' onClick='addDetail(this);' $InputState></td><td><label>YES</label></td>"; 
					$lines .= "<td style='width:30px;text-align:right;vertical-align:text-top;'><input type='radio' name='".$q_records['QuesID']."' value='0' onClick='removeDetail(this);' $InputState></td><td><label>NO</label></td>"; 
					$lines .= "<td class='form_label' style='width:125px;'><label>If YES, give details:</label></td><td><input type='text' style='width:300px;' name='d_".$q_records[0]."' id='d_".$q_records[0]."' value='".$tblempanswers[$QuesID]['AnsDetails']."' Readonly></td>"; 
				}
				
				else {
					$lines .= "<td style='width:30px;text-align:right;vertical-align:text-top;'><input type='radio' name='".$q_records['QuesID']."' value='1' onClick='addDetail(this);' $InputState></td><td><label>YES</label></td>"; 
					$lines .= "<td style='width:30px;text-align:right;vertical-align:text-top;'><input type='radio' name='".$q_records['QuesID']."' value='0' checked='checked' onClick='removeDetail(this);' $InputState></td><td><label>NO</label></td>"; 
					$lines .= "<td class='form_label' style='width:125px;'><label>If YES, give details:</label></td><td><input type='text' style='width:300px;' name='d_".$q_records[0]."' id='d_".$q_records[0]."' value='' Readonly></td>"; 
				}
				
				$lines .= "</tr></table><br />"; 
			}
			$lines .= "";
			echo $lines;
		}
		?>
	
		<br/><hr class="form_bottom_line"/>
		
		<?php 
			$bEditState="disabled";$bEditClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
			if($Authorization[0]&&$Authorization[2]){$bEditState="";$bEditClass="button ui-button ui-widget ui-corner-all";$onClick="getEmpPage('qnda',$EmpID,1);";}
		?>
		<table class="form">
			<tr>
				<td align="left"><input type="button" value="Help" class="button_help button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right">
					<input type="button" value="Edit" class="<?php echo $bEditClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bEditState; ?>/>&nbsp;
					<input type="submit" value="Save" class="<?php echo $bSaveClass; ?>" <?php echo $InputState; ?>/>
				</td>
			</tr>
		</table>
		<input type="hidden" name="EmpID" id="EmpID" value="<?php echo $EmpID; ?>" />
	</form>
</center><br/>

<script type='text/javaScript'>
	function addDetail(obj) {
		document.getElementById('d_' + obj.name).readOnly = false; 
		//document.getElementById('d_' + obj.name).className = "text_input_"; 
		document.getElementById('d_' + obj.name).focus();
		return true;
	}
	function removeDetail(obj) {
		document.getElementById('d_' + obj.name).readOnly = true; 
		//document.getElementById('d_' + obj.name).value = "";
		document.getElementById('d_' + obj.name).className = "text_input_"; 
		return true;
	}
</script>
<?php } ?>