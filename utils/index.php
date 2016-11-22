<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'../lib/classes/Authentication.php';
	$Authentication=new Authentication();
	$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD002'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	
?>

<!DOCTYPE html PUBLIC "-/*W3C/*DTD XHTML 1.0 Transitional/*EN" "http:/*www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http:/*www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
		<title>PMIS @ <?php echo strtoupper($_SERVER['SERVER_NAME']); ?></title>
		<link type="text/css" href="../css/<?php echo $_SESSION['theme']; ?>/jquery-ui-1.8.15.custom.css" rel="stylesheet"/>
		<link type="text/css" href="../css/<?php echo $_SESSION['theme']; ?>/common.css" rel="stylesheet"/>
		<link type="text/css" href="../css/<?php echo $_SESSION['theme']; ?>/chromestyle.css" rel="stylesheet"/>

		<script type="text/javascript" src="../js/jquery-1.6.2.min.js"></script>
		<script type="text/javascript" src="../js/jquery-ui-1.8.15.custom.min.js"></script>
		<script type="text/javascript" src="../js/jscripts.js"></script>
		<script type="text/javascript" src="../js/ajax.js"></script>
		<script type="text/javascript" src="../js/chrome.js">
			/***********************************************
			* Chrome CSS Drop Down Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
			* This notice MUST stay intact for legal use
			* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
			***********************************************/
		</script>
		<script type="text/javascript" src="angularjs/angular.min.js"></script>		

		<script type='text/javaScript'>

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

		</script>

	</head>

	<body class="main" ng-app="utils" ng-controller="utilsCtrl">
		<script type='text/javaScript'>
		<?php if($ActiveStatus[0]!=1){echo "showMessage(\"$ActiveStatus[1]\");$('#d_message').dialog({close:function(event,ui){window.location.href=\"logout.php\";}});";} ?>
		</script>
		
		<div class="ui-widget-content ui-corner-all" style="width:100%px;height:auto;padding:2px 2px 2px 2px;margin-top:5px;text-align:center;">
			<button id="dialog-button" class="button" utils-tasks="fix-leaves">Fix Leaves</button>
		</div>
		
		<!-- Migrating Personnel DTR -->
		<div id="dialog_box" title="{{views.title}}" style="height:auto;">
			<div style="width:367px;">
				<table style="width:365px;">
					<tr>
						<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="dFirstID" id="dFirstID" class="text_input" style="width:70px;text-align:center;" ng-model="ids.start"></td>
						<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:left;"><input type="text" name="dLastID" id="dLastID" class="text_input" style="width:70px;text-align:center;" ng-model="ids.end"></td>
						<td rowspan="3" style="text-align:right;"><button class="start_button" style="padding:10px 5px 10px 5px;" start-task>Start</button></td>
					</tr>
				</table>
			</div>
			
			<textarea style="padding-left: 3px;" id="console-status" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar"><div id="pbar-label" class="pbar-label">{{views.status}}</div></div>
			
		</div>
	
		<!-- MiNi Windows with Dynamic Contents -->
		<div id="d_message" title="PMIS Message"></div>
		<div id="d_confirm" title="PMIS Confirm"></div>
		<div id="d_input" title="PMIS Input Box">
			<table><tr><td style='width:50px;text-align:center;vertical-align:top;' rowspan='2'><div class='critical'>&nbsp;</div></td><td style='text-align:left;vertical-align:middle;'><div style='width:300px;'><span id="AskMsg" style='font-weight:bold;font-size:1.1em;'><span/></div></td></tr><tr><td  style='text-align:left;vertical-align:middle;'><textarea rows="2" cols="55" name="respTxt" id="respTxt" class="text_input" ></textarea></td></tr></table>
		</div>
		<script type="text/javaScript">
		
			$(function() {
				
				$("#d_message").dialog({modal:true,autoOpen:false,resizable:true,width:375,buttons:{"OK":function(){$(this).dialog("close");}}});
				$("#d_confirm").dialog({modal:true,autoOpen:false,width:360});
				$("#d_input").dialog({modal:true,autoOpen:false,width:360});
				$("#d_form_input").dialog({modal:true,autoOpen:false,resizable:false,width:440});
				$("#d_viewer_1").dialog({modal:false,autoOpen:false,resizable:false,width:"auto"});
				$("#d_viewer_2").dialog({modal:true,autoOpen:false,resizable:false,width:"auto"});
				$("#win_sys_users").dialog({modal:false,autoOpen:false,resizable:false,width:685});
				$("#d_help").dialog({modal:true,autoOpen:false,resizable:true,width:1035,buttons:{"OK":function(){$(this).dialog("close");}}});
			 
				$("#dialog_box").dialog({modal:false,autoOpen:false,resizable:false,width:375});				
				
			});
		
		</script>
		<script src="controllers/utils.js"></script>
	</body>
</html>
