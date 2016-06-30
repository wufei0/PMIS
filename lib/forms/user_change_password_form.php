<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD001'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	
	echo "1|".$_SESSION['user']."|";

?>

<center>
	<form name="f_user_info" onSubmit="processUserInfo(this);return false;"><br/>
		<table class="form_window">
			<tr>
				<td class="form_label" style="width:200px;"><label>ID NUMBER: </label></td>
				<td class="pds_form_input" colspan="2"><input value="<?php echo $_SESSION['user']; ?>" type="text" name="UsrID" id="UsrID" class="text_input" style="width:40px" readonly/></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>OLD PASSWORD: </label></td>
				<td class="pds_form_input"><input value="" type="password" name="OldKey" id="OldKey" class="text_input sml_frm_fld"/></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="" class="ui-state-default ui-corner-all" title="Enter your old password here." style="cursor:help;" onClick="showMessage(this.title);"><span class="ui-icon ui-icon-help"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>NEW PASSWORD: </label></td>
				<td class="pds_form_input"><input value="" type="password" name="NewKey1" id="NewKey1" class="text_input sml_frm_fld"/></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="" class="ui-state-default ui-corner-all" title="Password must be atleast 6 characters." style="cursor:help;" onClick="showMessage(this.title);"><span class="ui-icon ui-icon-help"></span></li></ul></td>
			</tr>
			<tr>
				<td class="form_label" style="width:200px;"><label>CONFIRM NEW PASSWORD: </label></td>
				<td class="pds_form_input"><input value="" type="password" name="NewKey2" id="NewKey2" class="text_input sml_frm_fld"/></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li id="" class="ui-state-default ui-corner-all" title="Retype new password." style="cursor:help;" onClick="showMessage(this.title);"><span class="ui-icon ui-icon-help"></span></li></ul></td>
			</tr>
		</table>
		<br/>
		<hr class="form_bottom_line_window"/>
		<table class="form_window">
			<tr>
				<td align="left"><input type="button" value="Help" class="button ui-button ui-widget ui-corner-all" onClick="showHelp('001');return false;" /></td>
				<td align="right"><input type="submit" value="Save" class="button ui-button ui-widget ui-corner-all"/>&nbsp;<input type="button" value="Cancel" class="button ui-button ui-widget ui-corner-all" onClick="closeDialogWindow('d_form_input');return false;" /></td>
			</tr>
		</table>
		<input type="hidden" name="mode" id="mode" value="2" />
	</form>
</center>
