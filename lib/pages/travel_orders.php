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
	if(!(($Authorization[0])||($_SESSION['user']==$EmpID))){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	
	echo "1|$EmpID|";
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
?>

<center>
	<div style="width:840px;height:auto;overflow:auto"><br/><br/>
		<table class="i_table" style="width:800px;">
			<tr>
				<td class="i_table_header_1st" rowspan="2" width="80px">Travel Order Number</td>
				<td class="i_table_header" colspan="2">Inclusive Dates of Travel</td>
				<!--td class="i_table_header" rowspan="2" width="140px">Subject<br /></td-->
				<td class="i_table_header" rowspan="2" width="130px">Destination</td>
				<td class="i_table_header" rowspan="2">Details</td>
				<td class="i_table_header" rowspan="2" width="35px">Status</td>
				<td class="i_table_header" rowspan="2" colspan="4" width="60px">&nbsp;</td>
			</tr>
			<tr>
				<td class="i_table_header" width="70px">From</td>
				<td class="i_table_header" width="70px">To</td>
			</tr>

				<?php
					$records=Array();
					$n=1;
					$EmpTO=$MySQLi->sqlQuery("SELECT `TOID` FROM `tblemptravelorders` WHERE `EmpID`='$EmpID';");
					while($travels=mysqli_fetch_array($EmpTO, MYSQLI_BOTH)){
						$result=$MySQLi->sqlQuery("SELECT `TOID`, DATE_FORMAT(`TOIncDateFrom`,'%b %d, %Y') AS TODateFr, DATE_FORMAT(`TOIncDateTo`,'%b %d, %Y') AS TODateTo, `TOSubject`, `TODestination`, `TOBody`, `TOStatus` FROM `tbltravelorders` WHERE `TOID` = '".$travels['TOID']."' ORDER BY DATE_FORMAT(`TOIncDateFrom`,'%Y%m%d') DESC;");
						while($records=mysqli_fetch_array($result, MYSQLI_BOTH)){
							if($n%2==0){echo "<tr class='i_table_row_0'>";}
							else{echo "<tr class='i_table_row_1'>";}
							echo "<td valign='top'align='center' style='padding:4px 3px 3px 0px;'>".$records['TOID']."</td>";
							echo "<td class='i_table_body' align='center'>".$records['TODateFr']."</td>";
							echo "<td class='i_table_body' align='center'>".$records['TODateTo']."</td>";
							//echo "<td class='i_table_body'>".$records['TOSubject']."</td>";
							echo "<td class='i_table_body'>".$records['TODestination']."</td>";
							echo "<td class='i_table_body'>".$records['TOBody']."</td>";
							switch ($records['TOStatus']){case '0':$TOStatus="NEW";break;case '1':$TOStatus="POSTED";break;case '2':$TOStatus="NOTED";break;case '3':$TOStatus="CHECKED";break;case '4':$TOStatus="APPROVED";break;default: $TOStatus="DISAPPROVED";break;}
							echo "<td class='i_table_body' align='center'>$TOStatus</td>";
							
							if($Authorization[0]&&$Authorization[2]){echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Post' onClick='$(\"#d_confirm\").dialog({buttons:{\"YES\":function(){processDocument(\"".$EmpID."\",\"to\",\"".$records['TOID']."\",1,\"\");},\"NO\":function(){closeDialogWindow(\"d_confirm\");}}});showConfirmation(\"Confirm to post this application.<br/>Modifying this record after posting will be impossible.<br/>Continue?\");'><span class='ui-icon ui-icon-notice'></span></li></ul></td>";}
							else{echo "<td valign='top' style='width:20px;text-align:center;border-left:1px dotted #6D84B4;padding:2px 0px 1px 3px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-notice'></span></li></ul></td>";}
							
							if($Authorization[0]&&$Authorization[2]){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li id=''class='ui-state-default ui-corner-all' title='Edit' onClick='showForm(\"trav\",\"$EmpID\",\"".$records['TOID']."\",\"1\");'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
							else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-pencil'></span></li></ul></td>";}
							
							if($Authorization[0]&&$Authorization[3]){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Delete' onClick='showForm(\"trav\",\"$EmpID\",\"".$records['TOID']."\",\"-1\");'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
							else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-trash'></span></li></ul></td>";}
							
							if((($Authorization[0]&&$Authorization[1])||(($_SESSION['user']==$EmpID)&&$Authorization[1]))&&($records['TOStatus']=='4')){echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-default ui-corner-all' title='Print' onClick='window.open(\"reports/rpt_to.php?id=".$records['TOID']."\",\"mywindow\",\"width=800,height=600\");'><span class='ui-icon ui-icon-print'></span></li></ul></td>";}
							else{echo "<td valign='top' style='width:20px;text-align:center;padding:2px 0px 1px 0px;'><ul class='ui-widget ui-helper-clearfix ul-icons'><li class='ui-state-disabled ui-corner-all'><span class='ui-icon ui-icon-print'></span></li></ul></td>";}
							
							echo "</tr>";
							$n+=1; 
						}
					}
					while($n<=1){
						if($n%2==0){echo "<tr class='i_table_row_0'>";}
						else{echo "<tr class='i_table_row_1'>";}
						for($col=1;$col<=9;$col+=1){echo "<td class='i_table_body'>&nbsp;</td>";}
						$n+=1;
						echo "</tr>";
					}
					
					$bAddState="disabled";$bAddClass="button ui-button ui-widget ui-corner-all ui-state-disabled";$onClick="";
					if(($Authorization[0]&&$Authorization[2])||(($_SESSION['user']==$EmpID)&&$Authorization[2])){$bAddState="";$bAddClass="button ui-button ui-widget ui-corner-all";$onClick="showForm('trav','$EmpID','',0);";}
				?>
		</table>
		<table class="form" style="width:800px;">
			<tr>
				<td style="width:100%;text-align:left">
					<input type="button" value="New Travel Order" class="<?php echo $bAddClass; ?>" onClick="<?php echo $onClick; ?>" <?php echo $bAddState; ?>/>
				</td>
			</tr>
		</table>
	</div>
</center>
<?php } ?>


