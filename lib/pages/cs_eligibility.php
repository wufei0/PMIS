<?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD008'));
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
	<div style="width:840px;height:auto;overflow:auto"><br/><br/>
		<table class="i_table" style="width:800px;">
			<tr>
				<td class="i_table_header" >License Description</td>
				<td class="i_table_header" width="40">Rating</td>
				<td class="i_table_header" width="80">Date of<br />Examination</td>
				<td class="i_table_header" width="120">Place of<br />Examination</td>
				<td class="i_table_header" width="100">Licence Number</td>
				<td class="i_table_header" width="80">Date<br />Released</td>
				<td class="i_table_header" width="50">Highest<br />Eligibility</td>
				<td class="i_table_header" width="40px" colspan="2">&nbsp;</td>
			</tr>
			
			<?php
				$result=$MySQLi->sqlQuery("SELECT `CSEID`, `CSEDesc`, `CSERating`, CONCAT_WS('\t',`CSEExamDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `CSEExamYear`, `CSEExamMonth`, `CSEExamDay`)), 1, 3)),`CSEExamYear`) AS CSEExamDate, CONCAT_WS('-', `CSEExamYear`, `CSEExamMonth`, `CSEExamDay`) AS csEDate, `CSEExamPlace`, `CSELicNum`, CONCAT_WS('\t',`CSELicReleaseDay`, UPPER(MID(MONTHNAME(CONCAT_WS('-', `CSELicReleaseYear`, `CSELicReleaseMonth`, `CSELicReleaseDay`)), 1, 3)),`CSELicReleaseYear`) AS CSELicReleaseDate, CONCAT_WS('-', `CSELicReleaseYear`, `CSELicReleaseMonth`, `CSELicReleaseDay`) AS csRDate, `CSEHighest` FROM `tblempcse` WHERE `EmpID`='".$EmpID."' ORDER BY `CSELicReleaseYear`, `CSELicReleaseMonth` DESC;");
				$n=1;
				while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
					if($n%2==0){echo "<tr class='i_table_row_0'>";}
					else{echo "<tr class='i_table_row_1'>";}
					echo "<td valign='top' style='padding:4px 3px 3px 3px;'>".$records['CSEDesc']."</td>";
					echo "<td class='i_table_body' align='center'>".$records['CSERating']."</td>";
					echo "<td class='i_table_body' align='center'>".$records['CSEExamDate']."</td>";
					echo "<td class='i_table_body'>".$records['CSEExamPlace']."</td>";
					echo "<td class='i_table_body' align='center'>".$records['CSELicNum']."</td>";
					echo "<td class='i_table_body' align='center'>".$records['CSELicReleaseDate']."</td>";	
					$isHighest=($records['CSEHighest']==1)?"YES":"NO";
					echo "<td class='i_table_body' align='center'>$isHighest</td>";	
					if($Authorization[0]&&$Authorization[2]){echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='showForm(\"csel\",\"$EmpID\",\"".$records['CSEID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					else{echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
					if($Authorization[0]&&$Authorization[3]){echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Delete' onClick='showForm(\"csel\",\"$EmpID\",\"".$records['CSEID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
					else{echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
					echo "</tr>";
					$n+=1; 
				} 
				while($n <= 1) {
					if($n%2==0){echo "<tr class='i_table_row_0'>";}
					else{echo "<tr class='i_table_row_1'>";}
					for ($col=1;$col<=7;$col+=1){echo "<td class='i_table_body'>&nbsp;</td>";}
					echo "<td style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";
					echo "<td style='width:20px;text-align:center;padding:2px 3px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";
					echo "</tr>";
					$n += 1;
				}
				
				$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
				if(($Authorization[2])&&(($_SESSION['user']==$EmpID)||($Authorization[0]))){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('csel','$EmpID','',0);";}
			?>
		</table>
		<table class="form" style="width:800px;">
		<tr>
			<td style="width:100%;text-align:left">
				<input type="button" value="Add" class="<?php echo $bAddClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bAddState; ?>/>
			</td>
		</tr>
	</table>
	</div>
</center>
<?php } ?>

