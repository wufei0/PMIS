<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
		
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';
	$Authentication=new Authentication();
	$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD002'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$MONTHS=Array('','JANUARY','FEBRUARY','MARCH','APRIL','MAY','JUNE','JULY','AUGUST','SEPTEMBER','OCTOBER','NOVEMBER','DECEMBER');
	$MySQLi=new MySQLClass();
	
?>

<!DOCTYPE html PUBLIC "-/*W3C/*DTD XHTML 1.0 Transitional/*EN" "http:/*www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http:/*www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
		<title>PMIS @ <?php echo strtoupper($_SERVER['SERVER_NAME']); ?></title>
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
			var RecordSet, EmpID=0, DpntID=0, CSEID=0, TrainID=0, PosID=0, OffID=0, mLTime=0;
			
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
			
			$(document).ready(function(){
				/* Tool tips */
				//$(function(){$(document).tooltip({track:true});});
				
				/* Button */
				$("#miPersonnel").button({icons:{primary:'ui-icon-person'}});
				$("#miDTR").button({icons:{primary:'ui-icon-calculator'}});
				$("#miDependent").button({icons:{primary:'ui-icon-heart'}});
				$("#miEligibility").button({icons:{primary:'ui-icon-flag'}});
				$("#miTraining").button({icons:{primary:'ui-icon-script'}});
				$("#miPosition").button({icons:{primary:'ui-icon-contact'}});
				$("#miOffice").button({icons:{primary:'ui-icon-home'}});
				$("#miService").button({icons:{primary:'ui-icon-script'}});
				$("#miLeaves").button({icons:{primary:'ui-icon-calendar'}});
				$("#miUserMan").button({icons:{primary:'ui-icon-person'}});
				$("#miShell").button({icons:{primary:'ui-icon-calculator'}});
				
				$("#miPersonnel").click(function(){$("#win_migrate_personnel").dialog('open');});
				$("#miDTR").click(function(){$("#win_migrate_dtr").dialog('open');});
				$("#miDependent").click(function(){$("#win_migrate_dependent").dialog('open');});
				$("#miEligibility").click(function(){$("#win_migrate_eligibility").dialog('open');});
				$("#miTraining").click(function(){$("#win_migrate_training").dialog('open');});
				$("#miPosition").click(function(){$("#win_migrate_position").dialog('open');});
				$("#miOffice").click(function(){$("#win_migrate_office").dialog('open');});
				$("#miService").click(function(){$("#win_migrate_service").dialog('open');});
				$("#miLeaves").click(function(){$("#win_migrate_leave_credit").dialog('open');});
				$("#miShell").click(function(){$("#win_shell").dialog('open');});
				$("#miUserMan").click(function(){$("#win_user_management").dialog('open');searchOnlineUsers('','')});;
				
				/* Progress Bar */
				$("#pbar1").progressbar();
				$("#upload").button({icons:{primary:'ui-icon-play'}});
				
				$("#pbar_migrate_dtr_emp").progressbar();
				$("#pbar_migrate_personal").progressbar();
				$("#pbar_migrate_dependent").progressbar();
				$("#pbar_migrate_eligibility").progressbar();
				$("#pbar_migrate_training").progressbar();
				$("#pbar_migrate_service").progressbar();
				$("#pbar_migrate_position").progressbar();
				$("#pbar_migrate_office").progressbar();
				$("#pbar_migrate_leave_credit").progressbar();
				
				$(".start_button").button({icons:{primary:'ui-icon-play'}});
				
			});
			
			/* AJAX Functions */
			function migratePersonnelDTR(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_dtr_express.php";

				$(function(){
					var fyr=$("#dtr_fyear").val();
					var fmo=$("#dtr_fmonth").val();
					var fdy=$("#dtr_fday").val();
					var tyr=$("#dtr_tyear").val();
					var tmo=$("#dtr_tmonth").val();
					var tdy=$("#dtr_tday").val();
					var iid=$("#dFirstID").val();
					var lid=$("#dLastID").val();
					
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{es:'I',st:st,cid:cid,nid:nid,did:did,tid:tid,fyr:fyr,fmo:fmo,fdy:fdy,tyr:tyr,tmo:tmo,tdy:tdy,iid:iid,lid:lid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_dtr").button({disabled:true});$(".dtr_input").prop('disabled','disabled');},
						success:function(data){//alert(data);
							fields=data.split('|');
							if(fields[0]=='-1'){
								showMessage(fields[9]);
							}
							else if(fields[0]=='0'){
								var pval_emp=(fields[3]/fields[4])*100;
								pval_emp=pval_emp.toFixed(2);
								
								$("#pbar_migrate_dtr_emp").progressbar("value",parseInt(pval_emp));
								
								$('#migrate_dtr_logs').append(fields[5]);
								var psconsole = $('#migrate_dtr_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								
								$('#pbar_migrate_dtr_emp_lbl').text('Processed ID: '+fields[3]+'/'+fields[4]+' ('+pval_emp+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migratePersonnelDTR('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval_emp=(fields[3]/fields[4])*100;
								pval_emp=pval_emp.toFixed(2); 
								
								$("#pbar_migrate_dtr_emp").progressbar("value",parseInt(pval_emp));
								
								$('#pbar_migrate_dtr_emp_lbl').text('Processed ID: '+fields[3]+'/'+fields[4]+' ('+pval_emp+'%) Time Remaining: 00:00:00');

								$('#migrate_dtr_logs').append(fields[5]);
								var psconsole = $('#migrate_dtr_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_dtr").button({disabled:false});
								$(".dtr_input").prop('disabled',false);
							}
							else{$("#start_dtr").button({disabled:false});$(".dtr_input").prop('disabled',false);showMessage(data);}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			function migratePersonnelInformation(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_personnel_information.php";

				$(function(){
					var iid=$("#prFirstID").val();
					var lid=$("#prLastID").val();
					var dh=$("#dh_year").val()+"-"+$("#dh_month").val()+"-"+$("#dh_day").val()+" 00:00:00";
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,iid:iid,lid:lid,dh:dh,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_personal").button({disabled:true});$("#PersonnelStatus").prop('disabled','disabled');},
						success:function(data){//alert(data);
							fields=data.split('|');
							EmpID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);$("#start_personal").button({disabled:false});
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_personal").progressbar("value",parseInt(pval));
								$('#migrate_personal_logs').append(fields[5]);
								var psconsole = $('#migrate_personal_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_personal_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migratePersonnelInformation('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_personal").progressbar("value",parseInt(pval));
								$('#pbar_migrate_personal_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_personal_logs').append(fields[5]);
								var psconsole = $('#migrate_personal_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_personal").button({disabled:false});
								$("#PersonnelStatus").prop('disabled',false);
							}
							else{showMessage(data);$("#start_personal").button({disabled:false});}
							
						},
						error:function(xhr,ajaxOptions,thrownError){$("#start_personal").button({disabled:false});showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			function migrateDependentInformation(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_dependents_information.php";

				$(function(){
					var iid=$("#dnFirstID").val();
					var lid=$("#dnLastID").val();
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,iid:iid,lid:lid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_dependent").button({disabled:true});},
						success:function(data){//alert(data);
							fields=data.split('|');
							DpntID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);$("#start_dependent").button({disabled:false});
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_dependent").progressbar("value",parseInt(pval));
								$('#migrate_dependent_logs').append(fields[5]);
								var psconsole = $('#migrate_dependent_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_dependent_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migrateDependentInformation('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_dependent").progressbar("value",parseInt(pval));
								$('#pbar_migrate_dependent_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_dependent_logs').append(fields[5]);
								var psconsole = $('#migrate_dependent_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_dependent").button({disabled:false});
							}
							else{showMessage(data);$("#start_dependent").button({disabled:false});}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			function migrateEligibilityInformation(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_eligibility_information.php";

				$(function(){
					var iid=$("#elFirstID").val();
					var lid=$("#elLastID").val();
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,iid:iid,lid:lid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_eligibility").button({disabled:true});},
						success:function(data){
							fields=data.split('|');
							CSEID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_eligibility").progressbar("value",parseInt(pval));
								$('#migrate_eligibility_logs').append(fields[5]);
								var psconsole = $('#migrate_eligibility_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_eligibility_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migrateEligibilityInformation('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_eligibility").progressbar("value",parseInt(pval));
								$('#pbar_migrate_eligibility_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_eligibility_logs').append(fields[5]);
								var psconsole = $('#migrate_eligibility_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_eligibility").button({disabled:false});
							}
							else{showMessage(data);$("#start_eligibility").button({disabled:false});}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			function migrateTrainingInformation(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_trainings_information.php";

				$(function(){
					var iid=$("#trFirstID").val();
					var lid=$("#trLastID").val();
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,iid:iid,lid:lid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_training").button({disabled:true});},
						success:function(data){
							fields=data.split('|');
							TrainID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_training").progressbar("value",parseInt(pval));
								$('#migrate_training_logs').append(fields[5]);
								var psconsole = $('#migrate_training_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_training_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migrateTrainingInformation('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_training").progressbar("value",parseInt(pval));
								$('#pbar_migrate_training_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_training_logs').append(fields[5]);
								var psconsole = $('#migrate_training_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_training").button({disabled:false});
							}
							else{showMessage(data);$("#start_training").button({disabled:false});}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			function migrateServiceInformation(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_service_records_information.php";

				$(function(){
					var iid=$("#srFirstID").val();
					var lid=$("#srLastID").val();
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,iid:iid,lid:lid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_service").button({disabled:true});},
						success:function(data){
							fields=data.split('|');
							TrainID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_service").progressbar("value",parseInt(pval));
								$('#migrate_service_logs').append(fields[5]);
								var psconsole = $('#migrate_service_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_service_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migrateServiceInformation('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_service").progressbar("value",parseInt(pval));
								$('#pbar_migrate_service_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_service_logs').append(fields[5]);
								var psconsole = $('#migrate_service_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_service").button({disabled:false});
							}
							else{showMessage(data);$("#start_service").button({disabled:false});}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			function migratePositionInformation(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_position_information.php";

				$(function(){
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_position").button({disabled:true});},
						success:function(data){//alert(data);
							fields=data.split('|');
							PosID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_position").progressbar("value",parseInt(pval));
								$('#migrate_position_logs').append(fields[5]);
								var psconsole = $('#migrate_position_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_position_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migratePositionInformation('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_position").progressbar("value",parseInt(pval));
								$('#pbar_migrate_position_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_position_logs').append(fields[5]);
								var psconsole = $('#migrate_position_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_position").button({disabled:false});
							}
							else{showMessage(data);}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			function migrateOfficeInformation(st,cid,nid,did,tid){
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_office_information.php";

				$(function(){
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_office").button({disabled:true});},
						success:function(data){//alert(data);
							fields=data.split('|');
							OffID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_office").progressbar("value",parseInt(pval));
								$('#migrate_office_logs').append(fields[5]);
								var psconsole = $('#migrate_office_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var now_end=new Date();
								var ms_end=now_end.getTime();
								var ms_time=ms_end-ms_start;
								var rem_time=ms_time*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_office_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migrateOfficeInformation('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_office").progressbar("value",parseInt(pval));
								$('#pbar_migrate_office_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_office_logs').append(fields[5]+"\n\n");
								var psconsole = $('#migrate_office_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_office").button({disabled:false});
							}
							else{showMessage(data);$("#start_office").button({disabled:false});}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
		
			function migrateLeaveCredits(st,cid,nid,did,tid){
				if(st==-1){mLTime=0;}
				var now_start=new Date();
				var ms_start=now_start.getTime();
				var url="lib/scripts/_migrate_sybase_leave_credits.php";

				$(function(){
					var lt=$("#LeaveType").val();
					var iid=$("#lFirstID").val();
					var lid=$("#lLastID").val();
					$.ajax({
						url:url,
						global:false,type:"POST",
						data:{st:st,cid:cid,nid:nid,did:did,tid:tid,lt:lt,iid:iid,lid:lid,sid:Math.random()},
						dataType:"html",async:true,
						beforeSend:function(){$("#start_leave_credit").button({disabled:true});$("#LeaveType").prop('disabled','disabled');},
						success:function(data){
							fields=data.split('|');
							EmpID=fields[2];
							if(fields[0]=='-1'){
								showMessage(fields[5]);
							}
							else if(fields[0]=='0'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2);
								var log=fields[5];
								$("#pbar_migrate_leave_credit").progressbar("value",parseInt(pval));
								$('#migrate_leave_credit_logs').append(fields[5]);
								var psconsole = $('#migrate_leave_credit_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								var d_m=log.split(' ');
								if(d_m[0]>0){
									var now_end=new Date();
									var ms_end=now_end.getTime();
									var do_time=ms_end-ms_start;
									var per_rec=do_time/d_m[0];
									mLTime=per_rec;//alert(mLTime);
								}
								rem_time=mLTime*(fields[4]-fields[3]);
								var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
								var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
								var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
								$('#pbar_migrate_leave_credit_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
								migrateLeaveCredits('0',fields[1],fields[2],fields[3],fields[4]);
							}
							else if(fields[0]=='1'){
								var pval=(fields[3]/fields[4])*100;
								pval=pval.toFixed(2); 
								$("#pbar_migrate_leave_credit").progressbar("value",parseInt(pval));
								$('#pbar_migrate_leave_credit_lbl').text('Processed: '+fields[3]+'/'+fields[4]+' (100%) Time Remaining: 00:00:00');
								$('#migrate_leave_credit_logs').append(fields[5]);
								var psconsole = $('#migrate_leave_credit_logs');
								psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
								$("#start_leave_credit").button({disabled:false});
								$("#LeaveType").prop('disabled',false);
							}
							else{showMessage(data);}
							
						},
						error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
					});
				});
			}
			
			/* SEARCH Employee load to emp_search_result */
		function searchOnlineUsers(opt,key){
			$(function(){
				$.ajax({
					url:"lib/scripts/_online_user_search.php",
					global:false,type:"GET",
					data:{mod:"srch",opt:opt,key:key,sid:Math.random()},
					dataType:"html",
					async:false,
					beforeSend:function(){$("#search_ol_loading").show();},
					complete:function(){$("#search_ol_loading").hide("highlight");},
					success:function(data){ if(debugMode){alert(data);};
						var fields=new Array();
						fields=data.split('|');
						if(fields[0]==-1){showMessage(fields[2]);$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
						else if(fields[0]==0){showMessage(fields[2]);}
						else if(fields[0]==1){first_id=fields[0];$("#ol_search_result").html(fields[2]);}
						else{showMessage(data);}
					}
				});
			});
		}
		</script>

	</head>

	<body class="main" onLoad="">
		<script type='text/javaScript'>
		<?php if($ActiveStatus[0]!=1){echo "showMessage(\"$ActiveStatus[1]\");$('#d_message').dialog({close:function(event,ui){window.location.href=\"logout.php\";}});";} ?>
		</script>
		
		<div class="ui-widget-content ui-corner-all" style="width:100%px;height:auto;padding:2px 2px 2px 2px;margin-top:5px;text-align:center;">
			<button id="miPersonnel" class="button">Personnel</button>
			<button id="miDTR" class="button">DTR</button>
			<button id="miDependent" class="button">Dependents</button>
			<button id="miEligibility" class="button">Eligibility</button>
			<button id="miTraining" class="button">Trainings & Seminars</button>
			<button id="miPosition" class="button">Positions</button>
			<button id="miOffice" class="button">Offices</button>
			<button id="miService" class="button">Service Records</button>
			<button id="miLeaves" class="button">Leaves</button>
			<button id="miUserMan" class="button">User Management</button>
			<button id="miShell" class="button">Shell</button>
		</div>
		
		<!-- Migrating Personnel DTR -->
		<div id="win_migrate_dtr" title="Migrating Daily Time Record" style="height:auto;">
			<div id="dtr" style="width:367px;">
				<table style="width:365px;"><tr>
					<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="dFirstID" id="dFirstID" class="text_input" value="0" style="width:70px;text-align:center;"></td>
					<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:left;"><input type="text" name="dLastID" id="dLastID" class="text_input" value="99999" style="width:70px;text-align:center;"></td>
						<td rowspan="3" style="text-align:right;"><button id="start_dtr" class="start_button" style="padding:10px 5px 10px 5px;" onClick="$('#pbar_migrate_dtr_emp_lbl').text('Loading...'); migratePersonnelDTR('-1','','','','');">Start</button></td>
				</tr><tr>
					<td class="form_label"><label>DATE FROM:</label></td>
						<td class="pds_form_input" colspan="3">
							<select id="dtr_fyear" name="dtr_fyear" class="text_input dtr_input">
							<?php for($y=2010;$y<=date('Y');$y++){$def=($y==date('Y'))?"selected":"";echo "<option value='$y' $def>$y</option>";} ?>
							</select>
							<select id="dtr_fmonth" name="dtr_fmonth" class="text_input dtr_input">
							<?php for($m=1;$m<=12;$m++){$def=($m==date('n'))?"selected":"";echo "<option value='$m' $def>".$MONTHS[$m]."</option>";} ?>
							</select>
							<select id="dtr_fday" name="dtr_fday" class="text_input dtr_input">
							<?php for($d=1;$d<=31;$d++){$def=($d==date('j'))?"selected":"";echo "<option value='$d' $def>$d</option>";} ?>
							</select>
						</td>
				</tr><tr>
					<td class="form_label"><label>DATE TO:</label></td>
						<td class="pds_form_input" colspan="3">
							<select id="dtr_tyear" name="dtr_tyear" class="text_input dtr_input">
							<?php for($y=2010;$y<=date('Y');$y++){$def=($y==date('Y'))?"selected":"";echo "<option value='$y' $def>$y</option>";} ?>
							</select>
							<select id="dtr_tmonth" name="dtr_tmonth" class="text_input dtr_input">
							<?php for($m=1;$m<=12;$m++){$def=($m==date('n'))?"selected":"";echo "<option value='$m' $def>".$MONTHS[$m]."</option>";} ?>
							</select>
							<select id="dtr_tday" name="dtr_tday" class="text_input dtr_input">
							<?php for($d=1;$d<=31;$d++){$def=($d==date('j'))?"selected":"";echo "<option value='$d' $def>$d</option>";} ?>
							</select>
						</td>
				</tr></table>
			</div>
			
			<textarea id="migrate_dtr_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_dtr_emp"><div id="pbar_migrate_dtr_emp_lbl" class="pbar-label"></div></div>
			
		</div>
		
		<!-- Migrating Personnel Information -->
		<div id="win_migrate_personnel" title="Migrating Personnel Information" style="height:auto;">
			
			<div id="personnel" style="width:367px;">
				<table style="width:365px;">
				<tr>
					<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="prFirstID" id="prFirstID" class="text_input" value="0" style="width:67px;text-align:center;"></td>
					<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:left;"><input type="text" name="prLastID" id="prLastID" class="text_input" value="99999" style="width:67px;text-align:center;"></td>
					<td style="text-align:right;" rowspan='2'><button id="start_personal" class="start_button" style="padding:9px 9px 9px 9px;" onClick="$('#pbar_migrate_personal_lbl').text('Loading...'); migratePersonnelInformation('-1','','','','')">Start</button></td>
				</tr>
				<tr>
					<td class="form_label"><label>Date Hired:</label></td>
						<td class="pds_form_input" colspan="3">
							<input type="text" id="dh_year" name="dh_year" class="text_input dtr_input" value="<?php echo date('Y'); ?>" style="width:45px;text-align:center;" />
							<select id="dh_month" name="dh_month" class="text_input dtr_input" style="height:19px;">
							<?php for($m=1;$m<=12;$m++){$def=($m==date('n'))?"selected":"";echo "<option value='".(($m>9)?$m:'0'.$m)."' $def>".$MONTHS[$m]."</option>";} ?>
							</select>
							<select id="dh_day" name="dh_day" class="text_input dtr_input" style="height:19px;">
							<?php for($d=1;$d<=31;$d++){$def=($d==date('j'))?"selected":"";echo "<option value='".(($d>9)?$d:'0'.$d)."' $def>$d</option>";} ?>
							</select>
						</td>
				</tr>
				</table>
			</div>

			<textarea id="migrate_personal_logs" rows='30' cols='69' readonly></textarea>
			
			<div id="pbar_migrate_personal"><div id="pbar_migrate_personal_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- Migrating Dependent Information -->
		<div id="win_migrate_dependent" title="Migrating Dependent Information" style="height:auto;">
			
			<div id="dependent" style="width:367px;">
				<table style="width:365px;"><tr>
					<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="dnFirstID" id="dnFirstID" class="text_input" value="0" style="width:70px;text-align:center;"></td>
					<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:left;"><input type="text" name="dnLastID" id="dnLastID" class="text_input" value="99999" style="width:70px;text-align:center;"></td>
					<td style="text-align:right;"><button id="start_dependent" class="start_button" onClick="migrateDependentInformation('-1','','','','')">Start</button></td>
				</tr></table>
			</div>

			<textarea id="migrate_dependent_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_dependent"><div id="pbar_migrate_dependent_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- Migrating Eligibility Information -->
		<div id="win_migrate_eligibility" title="Migrating Eligibility Information" style="height:auto;">
			
			<div id="eligibility" style="width:367px;">
				<table style="width:365px;"><tr>
					<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="elFirstID" id="elFirstID" class="text_input" value="0" style="width:70px;text-align:center;"></td>
					<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:left;"><input type="text" name="elLastID" id="elLastID" class="text_input" value="99999" style="width:70px;text-align:center;"></td>
					<td style="text-align:right;"><button id="start_eligibility" class="start_button" onClick="migrateEligibilityInformation('-1','','','','')">Start</button></td>
				</tr></table>
			</div>

			<textarea id="migrate_eligibility_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_eligibility"><div id="pbar_migrate_eligibility_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- Migrating Trainings and Seminars Information -->
		<div id="win_migrate_training" title="Migrating Trainings and Seminars Information" style="height:auto;">
			
			<div id="training" style="width:367px;">
				<table style="width:365px;"><tr>
					<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="trFirstID" id="trFirstID" class="text_input" value="0" style="width:70px;text-align:center;"></td>
					<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:left;"><input type="text" name="trLastID" id="trLastID" class="text_input" value="99999" style="width:70px;text-align:center;"></td>
					<td style="text-align:right;"><button id="start_training" class="start_button" onClick="migrateTrainingInformation('-1','','','','')">Start</button></td>
				</tr></table>
			</div>

			<textarea id="migrate_training_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_training"><div id="pbar_migrate_training_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- Migrating Service Records Information -->
		<div id="win_migrate_service" title="Migrating Service Records Information" style="height:auto;">
			
			<div id="service" style="width:367px;">
				<table style="width:365px;"><tr>
					<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="srFirstID" id="srFirstID" class="text_input" value="0" style="width:70px;text-align:center;"></td>
					<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:left;"><input type="text" name="srLastID" id="srLastID" class="text_input" value="99999" style="width:70px;text-align:center;"></td>
					<td style="text-align:right;"><button id="start_service" class="start_button" onClick="migrateServiceInformation('-1','','','','')">Start</button></td>
				</tr></table>
			</div>

			<textarea id="migrate_service_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_service"><div id="pbar_migrate_service_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- Migrating Positions Update Current Service Record -->
		<div id="win_migrate_position" title="Migrating Position and Update Current Employee Position" style="height:auto;">
			
			<div id="position" style="width:367px;">
				<table style="width:365px;"><tr>
				<td style="text-align:right;"><button id="start_position" class="start_button" onClick="migratePositionInformation('-1','','','','')">Start</button></td>
				</tr></table>
			</div>

			<textarea id="migrate_position_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_position"><div id="pbar_migrate_position_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- Migrating Offices Update Current Service Record -->
		<div id="win_migrate_office" title="Migrating Offices and Update Current Employee Office" style="height:auto;">
			
			<div id="office" style="width:367px;">
				<table style="width:365px;"><tr>
				<td style="text-align:right;"><button id="start_office" class="start_button" onClick="migrateOfficeInformation('-1','','','','')">Start</button></td>
				</tr></table>
			</div>

			<textarea id="migrate_office_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_office"><div id="pbar_migrate_office_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- Migrating Leave Credits -->
		<div id="win_migrate_leave_credit" title="Migrating Leave Credits" style="height:auto;">
			
			<div id="leave_credit" style="width:367px;">
				<table style="width:365px;"><tr>
					<td class="form_label"><label>ID Number From:</label></td>
						<td class="pds_form_input"><input type="text" name="lFirstID" id="lFirstID" class="text_input" value="0" style="width:70px;text-align:center;"></td>
					<td class="form_label"><label>To:</label></td>
						<td class="pds_form_input" style="text-align:right;"><input type="text" name="lLastID" id="lLastID" class="text_input" value="99999" style="width:70px;text-align:center;"></td>
						<td rowspan="3" style="text-align:right;"><button id="start_leave" class="start_button" style="padding:20px 7px 20px 7px;" onClick="$('#pbar_migrate_leave_credit_lbl').text('Loading...');migrateLeaveCredits('-1','','','','');">Start</button></td>
				</tr><tr>
					<td class="form_label"><label>Personnel Status:</label></td>
						<td class="pds_form_input" colspan="3">
							<select name="EmpStatusOnLeaveCr" id="EmpStatusOnLeaveCr" class="text_input" style="width:200px;">
								<option value="A" selected>Active Employees</option>
								<option value="D">Dead Files</option>
								<option value="I">Inactive Employees</option>
								<option value="O">On Leave</option>
							</select>
						</td>
				</tr><tr>
					<td class="form_label"><label>Leave Type:</label></td>
						<td class="pds_form_input" colspan="3">
							<select name="LeaveType" id="LeaveType" class="text_input" style="width:200px;">
								<option value="VL" selected>Vacation Leave</option>
								<option value="SL">Sick Leave</option>
								<option value="XL">Privilege Leave</option>
								<option value="UL">Under Time</option>
								<option value="FL">Force Leaves</option>
								<option value="WL">Monetization</option>
							</select>
						</td>
				</tr></table>
			</div>

			<textarea id="migrate_leave_credit_logs" rows='30' cols='66' readonly></textarea>
			
			<div id="pbar_migrate_leave_credit"><div id="pbar_migrate_leave_credit_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- User Administration -->
		<div id="win_user_management" title="User Administration and Management" style="height:auto;">
			<table	style="width:440px;border-spacing:0px;border:1px solid #6D84B4;">
				<tr>
					<td class="i_table_header" align="center" width="50px">ID</td>
					<td class="i_table_header" align="center" width="80px">Access</td>
					<td class="i_table_header" align="center">Name</td>
					<td class="i_table_header" align="center" width="80px">Status</td>
					<td class="i_table_header" align="center" width="15px">X</td>
				</tr>
			</table>
			<div style="width:438px;height:250px;border:1px dotted #6D84B4;padding:0px;overflow-x:hidden;overflow-y:scroll;">
				<div id="search_ol_loading" class="loading_div" style="left:7px;width:440px;height:250px;">
					<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
				</div>
				<span id="ol_search_result"></span>
			</div>
		</div>
		
		<!-- Shell (command prompt) -->
		<div id="win_shell" title="Shell" style="height:auto;">
			<textarea id="shell_area" rows='20' cols='66' onfocus="this.value = this.value;">></textarea>
		</div>
		
		<script type='text/javaScript'>
			//window.onbeforeunload=function(){return false;}
			$(document).ready(function(){
				/* Initial positions of Personnel Information window */
				$("#d_message").dialog({modal:true,autoOpen:false,resizable:true,width:375,buttons:{"OK":function(){$(this).dialog("close");}}});
				$("#d_confirm").dialog({modal:true,autoOpen:false,width:360});
				$("#d_input").dialog({modal:true,autoOpen:false,width:360});
				$("#d_form_input").dialog({modal:true,autoOpen:false,resizable:false,width:440});
				$("#d_viewer_1").dialog({modal:false,autoOpen:false,resizable:false,width:"auto"});
				$("#d_viewer_2").dialog({modal:true,autoOpen:false,resizable:false,width:"auto"});
				$("#win_sys_users").dialog({modal:false,autoOpen:false,resizable:false,width:685});
				$("#d_help").dialog({modal:true,autoOpen:false,resizable:true,width:1035,buttons:{"OK":function(){$(this).dialog("close");}}});

				$("#win_migrate_personnel").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_dtr").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_dependent").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_eligibility").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_training").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_position").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_office").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_leave_credit").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_migrate_service").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_shell").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#win_user_management").dialog({modal:false,autoOpen:false,resizable:false,width:500});
			});
			
			if(t_eid=="00000"){t_eid="<?php echo $_SESSION['user'];?>"; ajaxGetEmp(t_eid,0);}
		</script>

		<!-- MiNi Windows with Dynamic Contents -->
		<div id="d_message" title="PMIS Message"></div>
		<div id="d_confirm" title="PMIS Confirm"></div>
		<div id="d_input" title="PMIS Input Box">
			<table><tr><td style='width:50px;text-align:center;vertical-align:top;' rowspan='2'><div class='critical'>&nbsp;</div></td><td style='text-align:left;vertical-align:middle;'><div style='width:300px;'><span id="AskMsg" style='font-weight:bold;font-size:1.1em;'><span/></div></td></tr><tr><td  style='text-align:left;vertical-align:middle;'><textarea rows="2" cols="55" name="respTxt" id="respTxt" class="text_input" ></textarea></td></tr></table>
		</div>
		
		<script type='text/javaScript'> $(document).ready(function(){ $('.pbar-label').text('Ready...'); });</script>
	</body>
</html>
