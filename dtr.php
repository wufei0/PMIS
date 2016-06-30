<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
		
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';
	/* Check user activity within the last?minutes*/
	$Authentication=new Authentication();
	$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){header('Location: logout.php');exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD003'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	$MySQL=new MySQLClass();
	
?>

<!DOCTYPE html PUBLIC "-/*W3C/*DTD XHTML 1.0 Transitional/*EN" "http:/*www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http:/*www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
		<title>Personnel Management Information System</title>
		<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/jquery-ui-1.8.15.custom.css" rel="stylesheet"/>
		<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/common.css" rel="stylesheet"/>
		<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/chromestyle.css" rel="stylesheet"/>

		<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.15.custom.min.js"></script>
		<script type="text/javascript" src="js/jscripts.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<script type="text/javascript" src="js/chrome.js">
			/***********************************************
			* Chrome CSS Drop Down Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
			* This notice MUST stay intact for legal use
			* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
			***********************************************/
		</script>

		<script type='text/javaScript'>
		
			/* GLOBAL Variables */
			
			
			function showMessage(msg){
				var msgSection=msg.split("~");
				var msgArray=msg.split(" ");
				var iCon, content;
				if((msgArray[0]=="ERROR")||(msgArray[0]=="ERROR:")){iCon="error";$('#d_message').dialog({title:"PMIS Error"});content="<span style='font-weight:bold;text-shadow:1px 1px 0 #977;color:#CC3333;font-size:1.2em;'>"+msgSection[0]+"</span><br><span style='font-size:1.1em;font-weight:bold;'>"+msgSection[1]+"</span>";}
				else if((msgArray[0]=="WARNING")||(msgArray[0]=="WARNING:")){iCon="critical";$('#d_message').dialog({title:"PMIS Warning"});content="<span style='font-weight:bold;text-shadow:1px 1px 0 #666;color:#e17009;font-size:1.4em;'>"+msgSection[0]+"</span><span style='font-size:1.1em;font-weight:bold;'>"+msgSection[1]+"</span>";}
				else{iCon="info";$('#d_message').dialog({title:"PMIS Message"});content="<span style='font-size:1.1em;font-weight:bold;'>"+msg+"</span>";}
				$('#d_message').html("<table><tr><td style='width:50px;text-align:center;vertical-align:top;'><div class='"+iCon+"'>&nbsp;</div></td><td style='text-align:left;vertical-align:middle;'><div style='width:300px;'>"+content+"</div></td></tr></table>");
				$('#d_message').dialog('open');
			}
			
			function verifyEmployee(){
				$(function(){
					var id = $('#ID').val();
					var bdy = $('#EmpBirthDay').val();
					var bmo = $('#EmpBirthMonth').val();
					var byr = $('#EmpBirthYear').val();
					$.ajax({
						url:"lib/scripts/_verification.php",
						global:false,
						type:"GET",
						data:{id:id,bdy:bdy,bmo:bmo,byr:byr,sid:Math.random()},
						dataType:"html",
						async:false,
						beforeSend:function(){$("#emp_dtr").dialog('close');},
						success:function(data){
							var fields=new Array();
							fields=data.split('|');
							if(fields[0]=="1"){
								$("#emp_dtr").dialog('open');
								$('#EmpID').val($('#ID').val());
								getEmpDTR(document.getElementById('DTR_emp_form'));
							}
							else if(fields[0]=="0"){showMessage(fields[2]);}
							else{showMessage("ERROR 49:~User error.<p>"+data+"</p>");}
						},
						complete:function(){},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			/* GET Employee/Office DTR */
			function getEmpDTR(form){
				qParams={id:form.EmpID.value,yr:form.SelectYear.value,mo:form.SelectMonth.value,pr:form.SelectPayPeriod.value,sid:Math.random()};
				$(function(){
					$.ajax({
						url:"lib/scripts/_return_dtr.php",
						global:false,
						type:"GET",
						data:qParams,
						dataType:"html",
						async:false,
						beforeSend:function(){},
						success:function(data){
							var fields=new Array();
							fields=data.split('|');
							if(fields[0]=="1"){$('#DTR_box_emp').html(fields[2]);}
							else if(fields[0]=="0"){showMessage(fields[2]);}
							else if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
							else{showMessage("ERROR 49:~User error.<p>"+data+"</p>");}
						},
						complete:function(){},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			$(document).ready(function(){
				/* Tool tips */
				//$(function(){$(document).tooltip({track:true});});
				
				/* Button */
				$("#show_dtr").button({icons:{primary:'ui-icon-calculator'}});
				
			/* AJAX Functions */
			});
		</script>

	</head>

	<body class="main" onLoad="">
		<script type='text/javaScript'>
		<?php if($ActiveStatus[0]!=1){echo "showMessage(\"$ActiveStatus[1]\");$('#d_message').dialog({close:function(event,ui){window.location.href=\"logout.php\";}});";} ?>
		</script>

		<!-- Employee Validation -->
		<div id="emp_input" title="Validation Form" style="height:auto;">
			<div class="ui-widget-content ui-corner-all" style="width:100%px;height:auto;padding:2px 2px 2px 2px;margin-top:5px;text-align:center;">
				<table style="width:280px;"><tr>
						<td class="form_label"><label>ID NUMBER:</label></td>
						<td class="pds_form_input"><input type="text" id="ID" style="width:50px;"></td>
					</tr><tr>
						<td class="form_label"><label>DATE OF BIRTH:</label></td>
						<td class="pds_form_input">
							<select id="EmpBirthDay" name="EmpBirthday" class="text_input dtr_input">
							<?php for($d=1;$d<=31;$d++){$def=($d==date('j'))?"selected":"";echo "<option value='$d' $def>$d</option>";} ?>
							</select>
							<select id="EmpBirthMonth" name="EmpBirthmonth" class="text_input dtr_input">
							<?php for($m=1;$m<=12;$m++){$def=($m==date('n'))?"selected":"";echo "<option value='$m' $def>".$MONTHS[$m]."</option>";} ?>
							</select>
							<select id="EmpBirthYear" name="EmpBirthyear" class="text_input dtr_input">
							<?php for($y=1950;$y<=date('Y');$y++){$def=($y==date('Y'))?"selected":"";echo "<option value='$y' $def>$y</option>";} ?>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<br/>
			<hr class="form_bottom_line_window"/>
			<table style="width:100%;">
				<tr>
					<td align="right"><button id="show_dtr" onClick="verifyEmployee();">Show DTR</button></td>
				</tr>
			</table>
		</div>
		
		
		<div id="emp_dtr" title="Daily Time Record" style="height:auto;">
		<center><br/>
			<form name="DTR_emp_form" id="DTR_emp_form" onSubmit="getEmpDTR(this); return false;">
				<table class="filter_bar" style="width:600px;">
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
						<td class="pds_form_input" width="20%"><input type="submit" value="View" class="button ui-button ui-widget ui-corner-all" /><input type="button" value="Print" class="button ui-button ui-widget ui-corner-all" onClick="printDTR(document.getElementById('DTR_emp_form'),'emp');"/></td>
					</tr>
				</table>
				<input type="hidden" name="EmpID" id="EmpID" value="" />
			</form>
			<br/>
			<span name="DTR_box_emp" id="DTR_box_emp"> </span>
		</center>
		</div>
		
		
		<script type='text/javaScript'>
			//window.onbeforeunload=function(){return false;}
			window.addEventListener("beforeunload", function(e){
				 alert("close na??");return false;
			}, false);
			$(document).ready(function(){
				/* Initial positions of Personnel Information window */
				$("#d_message").dialog({modal:true,autoOpen:false,resizable:true,width:375,buttons:{"OK":function(){$(this).dialog("close");}}});
				$("#d_confirm").dialog({modal:true,autoOpen:false,width:360});
				$("#d_input").dialog({modal:true,autoOpen:false,width:360});
				$("#emp_input").dialog({modal:false,autoOpen:true,resizable:false,width:300,position:{my:"left top",at:"left top",of:window}});
				$("#emp_dtr").dialog({modal:false,autoOpen:false,resizable:true,width:800,position:{my:"right top",at:"right top",of:window}});
			});
		</script>

		<!-- MiNi Windows with Dynamic Contents -->
		<div id="d_message" title="PMIS Message"></div>
		<div id="d_confirm" title="PMIS Confirm"></div>
		<div id="d_input" title="PMIS Input Box">
			<table><tr><td style='width:50px;text-align:center;vertical-align:top;' rowspan='2'><div class='critical'>&nbsp;</div></td><td style='text-align:left;vertical-align:middle;'><div style='width:300px;'><span id="AskMsg" style='font-weight:bold;font-size:1.1em;'><span/></div></td></tr><tr><td  style='text-align:left;vertical-align:middle;'><textarea rows="2" cols="55" name="respTxt" id="respTxt" class="text_input" ></textarea></td></tr></table>
		</div>
	</body>
</html>
