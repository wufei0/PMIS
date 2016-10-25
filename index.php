<?php
	ob_start();
	define('ROOT_PATH', dirname(__FILE__));
	
	session_start();
	$_SESSION['path']=ROOT_PATH;
	$_SESSION['theme']='blue';

	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';
	
	if ($_SESSION['fingerprint']!=md5($_SESSION['user'].$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'].$_SESSION['fprinttime'])){
		header('Location: logout.php');
		exit();
	}


	/* Check user activity within the last ? minutes */
	$Authentication=new Authentication();
	$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){header('Location: logout.php');exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD003'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	$LeaveAuth=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD018'));
	for($i=0;$i<=7;$i++){$LeaveAuth[$i]=$LeaveAuth[$i]==1?true:false;}
	$COCAuth=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD020'));
	for($i=0;$i<=7;$i++){$COCAuth[$i]=$COCAuth[$i]==1?true:false;}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	
	$MySQLi=new MySQLClass();
	
?>

<!DOCTYPE html PUBLIC "-/*W3C/*DTD XHTML 1.0 Transitional/*EN" "http:/*www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http:/*www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8"/>
		<title>PMIS @ <?=strtoupper($_SERVER['SERVER_NAME']); ?></title>
		<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/jquery-ui-1.8.15.custom.css" rel="stylesheet"/>
		<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/common.css" rel="stylesheet"/>
		<link type="text/css" href="css/<?php echo $_SESSION['theme']; ?>/chromestyle.css" rel="stylesheet"/>
		<link type="text/css" href="css/shortcut-menu.css" rel="stylesheet"/>
		<link rel="icon" href="favicon.ico"/>

		<script type="text/javascript" src="js/jquery-1.6.2.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui-1.8.15.custom.min.js"></script>
		<script type="text/javascript" src="js/jquery.easing.1.3.js"></script>
		<script type="text/javascript" src="js/jscripts.js"></script>
		<script type="text/javascript" src="js/ajax.js"></script>
		<script type="text/javascript" src="js/chrome.js">
			// Chrome CSS Drop Down Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
			// This notice MUST stay intact for legal use
			// Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
		</script>
		<script type='text/javaScript'>
			$(document).ready(function(){
				/* Tool tips */
				// $(function(){$(document).tooltip({track:true});});
				
				
				//$("#miPersonnel").button({icons:{primary:'ui-icon-person'}});
				//$("#miPersonnel").click(function(){$("#win_process_ufl").dialog('open');});
				/* Progress Bar */
				$("#pbar_process_ufl").progressbar();
				$(".start_button").button({icons:{primary:'ui-icon-play'}});
				
				
				/* Window Personnel Information 1 */
				$("#win_personnel_information_1").draggable({handle:"#win_personnel_information_1_handle",containment:"#main_box",scroll:false});
				
				/* Personnel Information Menu Buttons */
				$("#e_menu_1,#e_menu_2,#e_menu_3,#e_menu_4,#e_menu_5").buttonset();
				$(".emp_info_menu_1_more").button({icons:{secondary:'ui-icon-triangle-1-e'}});
				$(".help_button").button({icons:{primary:'ui-icon-help'}});
				$("#new_personnel").button({icons:{primary:'ui-icon-person'}});
				$("#search_personnel").button({icons:{primary:'ui-icon-search'}});
				$("#dtr_config").button({icons:{primary:'ui-icon-pencil'}});
				
				$("#srch_users, #srch_plm").button({icons:{primary:'ui-icon-person'}});
				$("#srch_groups, #srch_pgroups").button({icons:{primary:'ui-icon-contact'}});
				$("#user_new").button({icons:{primary:'ui-icon-person'}});
				$("#search_user, #search_p").button({icons:{primary:'ui-icon-search'}});
				$("#group_new, #pgroup_new").button({icons:{primary:'ui-icon-person'}});
				$("#search_group, #search_pgroup").button({icons:{primary:'ui-icon-contact'}});
				$("#help_privelege, #help_plm").button({icons:{primary:'ui-icon-info'}});
				$("#delete_privelege, #delete_plm").button({icons:{primary:'ui-icon-trash'}});$("#delete_privelege").button({disabled:true});
				$("#edit_privelege, #edit_plm").button({icons:{primary:'ui-icon-pencil'}});$("#edit_privelege").button({disabled:true});
				$("#save_privelege, #save_plm").button({icons:{primary:'ui-icon-disk'}});$("#save_privelege").button({disabled:true});
				$("#close_privelege, #close_plm, #close_sysc").button({icons:{primary:'ui-icon-circle-close'}});
				/* Personnel Information tabs */
				
				/* Progress Bar */
				$("#pbar1").progressbar();
				$("#upload").button({icons:{primary:'ui-icon-play'}});
				
				$("#pbar2").progressbar();
				$("#migrate").button({icons:{primary:'ui-icon-play'}});

				//$("main_box").css("width",$(window).width());

			/*------------------------- */
			});
		</script>
		
	</head>

	<body class="main" onLoad="">
		<script type='text/javaScript'>
		<?php if($ActiveStatus[0]!=1){echo "showMessage(\"$ActiveStatus[1]\");$('#d_message').dialog({close:function(event,ui){window.location.href=\"logout.php\";}});";} ?>
		</script>
		<?php include_once 'shortcut-menu.php'; ?>
		<!-- Header and Logo -->
		<div class='header'>
			<div class='header_logo' title="Logo here later..."></div>
		</div>
		<br/><br/>
		<!-- Notification Box -->
		<div id="notification_box" class="notification_window ui-dialog-content ui-widget-content ui-corner-all">
			<span style="font-size:1.2em;font-weight:bold;color:orange;">Notifications</span><hr/>
			<div id="notification_content" style="width:auto;height:auto;">
				<table style="width:280px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;">
					<tr valign="center">
						<td style="padding:0px 0px 0px 5px;"><i>Loading...</i></td>
					</tr>
				</table>
			</div>
		</div>
		<!-- Notification_Messages Box -->
		<div id="messages_box" class="notification_window ui-dialog-content ui-widget-content ui-corner-all">
			<span style="font-size:1.2em;font-weight:bold;color:orange;">Private Messages</span><hr/>
			<div id="messages_content" style="width:auto;height:auto;">
				<a href="#" class="notification_item" onClick=""><table class="ui-widget-content ui-corner-all" style="width:150px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td>Compose Mail</td></tr></table></a>
				<a href="#" class="notification_item" onClick=""><table class="ui-widget-content ui-corner-all" style="width:150px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td>Inbox</td></tr></table></a>
			</div>
		</div>
		<!-- Notification_User Settings Box -->
		<div id="user_settings_box" class="notification_window ui-dialog-content ui-widget-content ui-corner-all">
			<span style="font-size:1.2em;font-weight:bold;color:orange;">User Settings</span><hr/>
			<div id="user_settings_content" style="width:auto;height:auto;">
				<a href="#" class="notification_item" onClick="formUser('',2); showUserSettings(); return false;"><table class="ui-widget-content ui-corner-all" style="width:150px;padding:0px;border-spacing:0px;margin:3px 0px 0px 0px;"><tr valign="center"><td width="20px" align="center"><span class='ui-icon ui-icon-calendar'></span></td><td>Change Password</td></tr></table></a>
			</div>
		</div>
		
		<!-- MENU BAR -->
		<div style="border:0px;">
			<!-- Control Panel Notification|Messages|User Settings|Logout ICONS -->
			<div id="cPanel" class='ui-state-default ui-corner-all' style="float:right;padding: 2px 1px 1px 0px;margin: -6.5px -1.5px 0px 0px;">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr><td style="padding: 0px 5px 0px 5px;"><?php echo "[".$_SESSION['usergcode']."] ".$_SESSION['username']; ?></td><td>
						<ul class='ui-widget ui-helper-clearfix ul-icons'>
							<li id='Notifications' class='ui-state-default ui-corner-all' title='Notifications' onClick="showNotifications(); return false;"><a href="#"><span class='ui-icon ui-icon-notice'></span></a></li>
							<li class='ui-state-default ui-corner-all' title='Mail' onClick="showMessages(); return false;"><a href="#"><span class='ui-icon ui-icon-mail-closed'></span></a></li>
							<li class='ui-state-default ui-corner-all' title='Settings' onClick="showUserSettings(); return false;"><a href="#"><span class='ui-icon ui-icon-wrench'></span></a></li>
							<li class='ui-state-default ui-corner-all' title='Logout'><a href="logout.php"><span class='ui-icon ui-icon-key'></span></a></li>
						</ul>
					</td></tr>
				</table>
			</div>
			<!-- MAIN MENU -->
			<div class="chromestyle" id="chromemenu">
				<ul>
				<li><a href="#">Home</a></li>
				<?php if(($_SESSION['usergroup']=='USRGRP001')||($_SESSION['usergroup']=='USRGRP008')){ ?>
				<li><a href="#" id="e_menu_6_hr" class="e_menu" rel="dropmenu6">HR</a></li>
				<?php } ?>
				<?php if(($_SESSION['usergroup']=='USRGRP001')||($_SESSION['usergroup']=='USRGRP002')||($_SESSION['usergroup']=='USRGRP004')){ ?>
				<li><a href="#" id="e_menu_7_admin" class="e_menu" rel="dropmenu7">Admin</a></li>
				<?php } ?>
				<li><a href="#" id="e_menu_8_help" class="e_menu" rel="dropmenu8">HELP</a></li>
				<li><a href="#" style="color:gray;" onClick="if(debugMode){debugMode=false;this.style.color='gray';}else{debugMode=true;this.style.color='#EE6666';}">Debug Mode</a></li>
				</ul>
			</div>
		</div>
		<!--5th drop down menu -->
		<div id="dropmenu5" class="dropmenudiv" style="width: 150px;">
			<a href="#" id="e_menu_6_vpls" class="e_menu_" onClick='showPendingDocuments("vtra",<?php echo date('Y'); ?>,<?php echo date('m'); ?>,"X");'>Travel Orders</a>
			<a href="#" id="e_menu_6_vpls" class="e_menu_" onClick='showPendingDocuments("vliv",<?php echo date('Y'); ?>,<?php echo date('m'); ?>,"X");'>Leave Applications</a>
			<a href="#" id="e_menu_6_vpls" class="e_menu_" onClick='showPendingDocuments("vpls",<?php echo date('Y'); ?>,<?php echo date('m'); ?>,"X");'>Personnel Locator Slip</a>
			<a href="#" id="e_menu_6_vpls" class="e_menu_" onClick='showTrails();'>Logs/Trails</a>
		</div>
		<!--6th drop down menu -->
		<div id="dropmenu6" class="dropmenudiv" style="width: 150px;">
			<?php if($LeaveAuth[6]){ ?>
			<a href="#" id="e_menu_6_pliv" class="e_menu_" onClick='showLeaveManager();'>Personnel Leaves</a>
			<a href="#" id="e_menu_6_pufl" class="e_menu_" onClick="$('#win_process_ufl').dialog('open');">Process UFL</a>
			<?php } ?>
			<a href="#" id="e_menu_6_rpts" class="e_menu_" onClick='showReportManager();'>HR Reports</a>
		</div>
		<!--7th drop down menu -->
		<div id="dropmenu7" class="dropmenudiv" style="width: 150px;">
			<!-- a href="#" id="e_menu_7_syss" class="e_menu_" onClick='showSystemUsers();'>System Settings</a -->
			<a href="#" id="e_menu_7_usrm" class="e_menu_" onClick='showSystemUsers();'>User Management</a>
			<a href="#" id="e_menu_7_sysc" class="e_menu_" onClick='showSystemPreferences();'>Preferences</a>
		</div>	
		<!--8th drop down menu -->
		<div id="dropmenu8" class="dropmenudiv" style="width: 150px;">
			<a href="#" id="e_menu_8_hdtr" class="e_menu_" onClick="showHelp('H1221','2','Adding New User');">Adding New User</a>
			<a href="#" id="e_menu_8_hdtr" class="e_menu_" onClick="showHelp('H0221','2','Changing User Password');">Changing User Password</a>
			<a href="#" id="e_menu_8_hdtr" class="e_menu_" onClick="showHelp('H0171','2','Viewing/Printing of DTR');">Viewing/Printing of DTR</a>
			<a href="#" id="e_menu_8_hdtr" class="e_menu_" onClick="showHelp('H0181','5','Filing of Leave Application');">Filing Leave Application</a>
			<a href="#" id="e_menu_8_hdtr" class="e_menu_" onClick="showHelp('H0201','5','Filing of COC');">Filing of COC</a>
		</div>

		<!-- [ MAIN BOX -->
		<div id="main_box" style="height:600px;display:block;" class="ui-dialog-content ui-widget-content ui-corner-all">
			<!-- Personnel Information Window 1 -->
			<div id="win_personnel_information_1" style="width:auto;height:auto;" class="windows ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable ui-resizable" >
				<div id="win_personnel_information_1_handle" class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix">
					<span id="win_personnel_information_1_title" class="ui-dialog-title">Personnel Data Sheet</span>
					<a id="hide_personnel_information_1_window" class="ui-dialog-titlebar-close ui-corner-all" href="#"><span class="ui-icon ui-icon-minusthick">close</span></a>
				</div>
				
				<table border=0 cellpadding=0 cellspacing=0>
					<tr>
						<?php // if($Authorization[0]){ ?>
						<td <?php if($Authorization[0]) echo 'id="searchPanel"'; ?> style="<?php if($Authorization[0]) echo "display: visible;"; else echo "display: none;"; ?>">
							<!-- Search Personnel tab -->
							<div style="height:auto;width:auto;margin-top:-5px;" class="ui-dialog-content ui-widget-content">
								
								<table	class='search_header' style='width:220px;'>
									<tr>
										<td class="search_header" align="center" width="40px">ID</td>
										<td class="search_header" align="center">Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
									</tr>
								</table>

								<!-- Search Result box -->
								<div class="search_result" style='width:218px;height:402px;'>
									<!-- Animated Loading Interface -->
									<div id="search_emp_loading" class="loading_div" style="left:7px;width:216px;height:400px;">
										<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
									</div>
									<!-- Search Result Loads Here -->
									<span id="emp_search_result"></span>
								</div><br/>
								<!-- Form Search Personnel -->
								<form name="pds_search" id="pds_search" onSubmit="ajaxSearchEmp(this.pds_search_cat.value, this.pds_search_key.value); return false;">
									<table	class="search_form">
										<tr>
											<td class="form_label"><label>Search By:</label></td>
											<td align="left">
												<select name="pds_search_cat" id="pds_search_cat" class="text_input search_select" style="width:160px;">
													<option value="EmpID">ID Number</option>
													<option value="EmpLName" selected>Last Name</option>
													<option value="EmpFName">First Name</option>
													<option value="EmpMName">Middle Name</option>
													<?php if($Authorization[0]&&($_SESSION['usergroup']!="USRGRP004")){ ?><option value="SubOffCode">Office/Department</option><?php } ?>
												</select>
											</td>
										</tr>
										<tr>
											<td class="form_label"><label>Keyword:</label></td>
											<td align="left">
												<input type="text" name="pds_search_key" id="pds_search_key" class="text_input" style="width:157px;"/>
											</td>
										</tr>	
									</table>
								</form>
								<!-- Search Buttons -->
								<table	class="search_form">
									<tr>
										<td align="left" style="padding:5px 3px 2px 3px;">
											<?php if($Authorization[2]){ ?><button id="new_personnel" style="width:110px;text-align:left;" onClick="showForm('newp',0,0,0);">New Personnel</button><?php } ?></td>
										<td align="right" style="padding:5px 3px 2px 3px;">
											<button id="search_personnel" style="width:70px;text-align:center;" onClick="ajaxSearchEmp(document.getElementById('pds_search_cat').value, document.getElementById('pds_search_key').value); return false;" >Search</button></td>
									</tr>
								</table>
								
							</div>

						</td>
						<?php // } ?>
						<td>
						<!---------------------------------------------------------------------->
							<!-- HEADER Box Personnel Basic Information -->
							<div id="brief_info_emp_1" class="ui-dialog-content ui-widget-content">
								<!-- Maximized Personnel Basic Information -->
								<div style="width:880px;height:120px;" id="emp_brief_info_x_1" class="ui-widget ui-widget-content ui-corner-all" >
									<!-- Personnel Basic Information Box -->
									<div style="position:absolute;left:125px;width:755px;height:96px;margin-right:5px;margin-top:1px;">
										<!-- Personnel Basic Information ID|Salary Grade|Salary -->
										<table class="brief_info_emp_1" style="width:755px;padding-top:0px;">
											<tr valign="baseline">
												<td class="form_label" style="width:50px;"><label>ID:</label></td>
												<td class="brief_info_data" id="brief_info_id"></td>
												<td class="form_label" style="width:110px;"><label>SALARY GRADE STEP:</label></td>
												<td class="brief_info_data" style="width:60px;text-align:center;" id="brief_info_salgrade">00-00</td>
												<td class="form_label" style="width:100px;"><label>MONTHLY SALARY:</label></td>
												<td class="brief_info_data" style="width:80px;text-align:right;" id="brief_info_salary">0.00</td>
											</tr>
										</table>
										<!-- Personnel Basic Information Name|Position|Office -->
										<table class="brief_info_emp_1" style="width:755px;">
											<tr valign="baseline">
												<td class="form_label" style="width:65px;"><label>NAME:</label></td>
												<td class="brief_info_data" id="brief_info_name">&nbsp;</td>
											</tr>
											<tr valign="baseline">
												<td class="form_label"><label>POSITION:</label></td>
												<td class="brief_info_data" id="brief_info_position">&nbsp;</td>
											</tr>
											<tr valign="baseline">
												<td class="form_label"><label>MO. OFFICE:</label></td>
												<td class="brief_info_data" id="brief_info_office">&nbsp;</td>
											</tr>
											<tr valign="baseline">
												<td class="form_label"><label>AS. OFFICE:</label></td>
												<td class="brief_info_data" id="brief_info_suboffice">&nbsp;</td>
											</tr>
										</table>
									</div>
									<!-- Animated Loading Interface -->
									<div id="emp_brief_info_loading_1" class="loading_div brief_info_emp_1" style="left:127px;width:758px;height:118px;">
										<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
									</div>
									<!-- Personnel Photo -->
									<div style="border:1px dotted #6D84B4;width:114px;height:114px;margin-left:2px;margin-top:2px;background-image:url(photos/no_photo.jpg) no-repeat topleft;">
										<img id="emp_small_photo_1" src="photos/no_photo.jpg" style="width:114px;height:114px;" onClick="return false;"/>
									</div>
								
								</div>
							
							</div>
							<!-- Sub-menus -->
							<div id="emp_info_menu_1" style="padding-top:5px;padding-right:5px;float:right;height:360px;">
								<!-- PDS Sub-menus -->
								<div id="e_menu_1">
									<input type="radio" id="r_emp_info_menu_1_pinfo" name="emp_menu" checked="checked"/><label for="r_emp_info_menu_1_pinfo" class="r_emp_info_menu">Basic Information</label><br/>
									<input type="radio" id="r_emp_info_menu_1_spsi" name="emp_menu"/><label for="r_emp_info_menu_1_spsi" class="r_emp_info_menu">Spouse</label><br/>
									<input type="radio" id="r_emp_info_menu_1_dpnt" name="emp_menu"/><label for="r_emp_info_menu_1_dpnt" class="r_emp_info_menu">Dependents</label><br/>
									<input type="radio" id="r_emp_info_menu_1_prnt" name="emp_menu"/><label for="r_emp_info_menu_1_prnt" class="r_emp_info_menu">Parents</label><br/>
									<input type="radio" id="r_emp_info_menu_1_educ" name="emp_menu"/><label for="r_emp_info_menu_1_educ" class="r_emp_info_menu">Education</label><br/>
									<input type="radio" id="r_emp_info_menu_1_csel" name="emp_menu"/><label for="r_emp_info_menu_1_csel" class="r_emp_info_menu">Eligibility/License</label><br/>
									<input type="radio" id="r_emp_info_menu_1_srec" name="emp_menu"/><label for="r_emp_info_menu_1_srec" class="r_emp_info_menu">Service Records</label><br/>
									<input type="radio" id="r_emp_info_menu_1_vwor" name="emp_menu"/><label for="r_emp_info_menu_1_vwor" class="r_emp_info_menu">Voluntary Works</label><br/>
									<input type="radio" id="r_emp_info_menu_1_trai" name="emp_menu"/><label for="r_emp_info_menu_1_trai" class="r_emp_info_menu">Trainings/Seminars</label><br/>
									<input type="radio" id="r_emp_info_menu_1_skil" name="emp_menu"/><label for="r_emp_info_menu_1_skil" class="r_emp_info_menu">Skills/Hobbies</label><br/>
									<input type="radio" id="r_emp_info_menu_1_ncad" name="emp_menu"/><label for="r_emp_info_menu_1_ncad" class="r_emp_info_menu">Rewards//<br/>Recognitions</label><br/>
									<input type="radio" id="r_emp_info_menu_1_orgs" name="emp_menu"/><label for="r_emp_info_menu_1_orgs" class="r_emp_info_menu">Memberships</label><br/>
									<input type="radio" id="r_emp_info_menu_1_chrf" name="emp_menu"/><label for="r_emp_info_menu_1_chrf" class="r_emp_info_menu">Character References</label><br/>
									<input type="radio" id="r_emp_info_menu_1_qnda" name="emp_menu"/><label for="r_emp_info_menu_1_qnda" class="r_emp_info_menu">Q & A</label><br/>
								</div>
								<!-- Attendance Related Sub-menus -->
								<div id="e_menu_2" style="display:none;">
									<input type="radio" id="r_emp_info_menu_1_pdtr" name="emp_menu"/><label for="r_emp_info_menu_1_pdtr" class="r_emp_info_menu">DTR</label><br/>
									<!--input type="radio" id="r_emp_info_menu_1_cert" name="emp_menu"/><label for="r_emp_info_menu_1_cert" class="r_emp_info_menu">Certification</label><br/-->
									<!--input type="radio" id="r_emp_info_menu_1_trav" name="emp_menu"/><label for="r_emp_info_menu_1_trav" class="r_emp_info_menu">Travel Orders</label><br/-->
									<input type="radio" id="r_emp_info_menu_1_pcoc" name="emp_menu"/><label for="r_emp_info_menu_1_pcoc" class="r_emp_info_menu">COCs</label><br/>
									<input type="radio" id="r_emp_info_menu_1_leav" name="emp_menu"/><label for="r_emp_info_menu_1_leav" class="r_emp_info_menu">Leave Applications</label><br/>
									<!--input type="radio" id="r_emp_info_menu_1_ppls" name="emp_menu"/><label for="r_emp_info_menu_1_ppls" class="r_emp_info_menu">PLS</label><br/-->
									<input type="radio" id="r_emp_info_menu_1_pr" name="emp_menu"/><label for="r_emp_info_menu_1_pr" class="r_emp_info_menu">Performance Rating</label><br/>									
									<!--<input type="radio" id="r_emp_info_menu_1_prs" name="emp_menu"/><label for="r_emp_info_menu_1_prs" class="r_emp_info_menu">PR (SyBase)</label><br/>-->
								</div>
								<ul class='ui-widget ui-helper-clearfix ul-icons' style="margin-top:3px;margin-left:93px;">
									<li id="e_menu_next" class='ui-state-default ui-corner-all' title='Next' onClick=''><span class='ui-icon ui-icon-carat-1-e'></span></li>
								</ul>
							</div>
							<!-- Dynamic Contents Box -->
							<div class="ui-dialog-content ui-widget-content">
								<div style="width:760px;height:385px;" class="emp_content_1 ui-widget ui-widget-content ui-corner-all" >
									<!-- Animated Loading Interface -->
									<div id="page_emp_loading_1" class="loading_div emp_content_1" style="left:6px;width:760px;height:385px;">
										<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
									</div>
									<!-- Contents Loads here -->
									<div id="emp_content_1" class="emp_content_1" style="width:760px;height:385px;overflow:auto;background-color:#E6EFFF;">&nbsp;</div>
								</div>
							</div>

						<!---------------------------------------------------------------------->
						</td>
					</tr>
				</table>
				
			</div>
			
		</div>

		<script type='text/javaScript'>
			window.onbeforeunload=function(){window.location.href = 'logout.php';}
			$(document).ready(function(){
				/* Initial positions of Personnel Information window */
				
				$("#win_personnel_information_1").position({of:$("#main_box"),my:"left top",at:"left top",offset:"5 5"});
				$("#notification_box").position({of:$("#cPanel"),my:"right top",at:"right botom",offset:"0 14"});
				$("#messages_box").position({of:$("#cPanel"),my:"right top",at:"right botom",offset:"0 14"});
				$("#user_settings_box").position({of:$("#cPanel"),my:"right top",at:"right botom",offset:"0 14"});
				
				$("#d_message").dialog({modal:true,autoOpen:false,resizable:true,width:375,buttons:{"OK":function(){$(this).dialog("close");}}});
				$("#d_confirm").dialog({modal:true,autoOpen:false,width:360});
				$("#d_input").dialog({modal:true,autoOpen:false,width:360});
				$("#d_form_input").dialog({modal:true,autoOpen:false,resizable:false,width:440});
				$("#win_sys_rpt").dialog({modal:false,autoOpen:false,resizable:false,width:380});
				$("#d_form_process").dialog({modal:true,autoOpen:false,resizable:false,width:440});
				$("#d_viewer_1").dialog({modal:false,autoOpen:false,resizable:false,width:"auto"});
				$("#d_viewer_2").dialog({modal:true,autoOpen:false,resizable:false,width:"auto"});
				$("#win_sys_sysc").dialog({modal:false,autoOpen:false,resizable:false,width:685});
				$("#win_sys_users").dialog({modal:false,autoOpen:false,resizable:false,width:685});
				$("#win_sys_plm").dialog({modal:false,autoOpen:false,resizable:false,width:685});
				$("#win_user_set").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				$("#d_help").dialog({modal:true,autoOpen:false,resizable:true,width:1035,buttons:{"OK":function(){$(this).dialog("close");}}});
				
				$("#win_process_ufl").dialog({modal:false,autoOpen:false,resizable:false,width:375});
				 
				$('<audio id="notifyAudio"><source src="notify.ogg" type="audio/ogg"><source src="notify.mp3" type="audio/mpeg"><source src="notify.wav" type="audio/wav"></audio>').appendTo('body');
				
				<?php
				//if($_SESSION['usergroup']=="USRGRP004"){echo "showMessage('Kung meron po kayong mga empleyado sa inyong opisina, na hindi nyo makita sa SEARCH, pakilista na lang po ung ID number at ipadala sa MIS-Raymond.<br/>Salamat po...');";}
				?>
				//showMessage("<?php echo $_SESSION['user']."-".$_SESSION['fingerprint']; ?>");
				
				$("#pl_vdf").datepicker("option","dateFormat","yy-mm-dd");
				$("#pl_vdt").datepicker("option","dateFormat","yy-mm-dd");
			});
									
			if(("<?php echo $_SESSION['user'];?>">"00000")&&("<?php echo $_SESSION['user'];?>"<"99999")){t_eid="<?php echo $_SESSION['user'];?>"; ajaxGetEmp(t_eid,0);}
			ajaxSearchEmp('EmpLName','');
		</script>

		<!-- MiNi Windows with Dynamic Contents -->
		<div id="d_message" title="PMIS Message"></div>
		<div id="d_confirm" title="PMIS Confirm"></div>
		<div id="d_input" title="PMIS Input Box">
			<table>
				<tr>
					<td style='width:50px;text-align:center;vertical-align:top;' rowspan='2'><div class='critical'>&nbsp;</div></td>
					<td style='text-align:left;vertical-align:middle;'><div style='width:300px;'><span id="AskMsg" style='font-weight:bold;font-size:1.1em;'></span></div></td>
				</tr>
				<tr>
					<td  style='text-align:left;vertical-align:middle;'><textarea rows="2" cols="42" name="respTxt" id="respTxt" class="text_input" ></textarea></td>
				</tr>
			</table>
		</div>
		<div id="d_form_input" title="Form" style="height:auto;"></div>
		<div id="d_form_process" title="Form" style="height:auto;"></div>
		<div id="win_sys_rpt" title="HR Reports Manager" style="height:auto;"></div>
		<div id="d_viewer_1" title="Viewer" style="height:auto;"><i>Loading... Please wait...</i></div>
		<div id="d_viewer_2" title="Viewer" style="height:auto;"><i>Loading... Please wait...</i></div>
		<div id="d_help" title="PMIS Help"></div>

		<!-- System User Administration Window -->
		<div id="win_sys_users" title="User Administration" style="height:auto;">
			<div style="float:right;width:460px;height:455px;" class="ui-widget">
				<div style="width:460px;height:418px;" class="ui-widget">
					<span id="priveleges_window">
						<!-- User small info -->
							<div style="background:#E6EFFF;width:auto;margin-bottom:4px;" class="ui-widget ui-widget-content ui-corner-all" >
								<table style="border-spacing:0px;border:0px solid #6D84B4;width:450px;">
									<tr>
										<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>USER NAME:</label></td>	
										<td colspan="3" style="padding:3px 0px 0px 0px;"><input value="" name="UserName" id="UserName" class="text_input" style="width:375px" readonly type="text"></td>
									</tr>
									<tr>
										<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>USER ID:</label></td>	
										<td style="padding:3px 0px 3px 0px;"><input value="" name="UserID" id="UserID" class="text_input" style="width:40px" readonly type="text"></td>
										
										<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:70px"><label>USER GROUP:</label></td>
										<td style="padding:3px 0px 3px 0px;width:200px">
											<select name="UserGroup" id="UserGroup" class="text_input search_select" style="width:200px" disabled>
												<option>&nbsp;</option><option>XXX</option>
											</select>
										</td>
									</tr>
								</table>
							</div>
							
							<table style="border-spacing:0px;border:1px solid #6D84B4;width:460px;">
								<tr><td class="search_header" style="text-align:center;">MODULE</td><td class="search_header" style="text-align:center;width:180px;">PRIVILEGE</td></tr>
							</table>
							<table style="border-spacing:0px;border:0px solid #6D84B4;width:460px;">
								<tr><td class="search_result" style="text-align:center;border-left:1px solid #6D84B4;"></td>
									<td class="search_result chkbox" title="All Record">O</td>
									<td class="search_result chkbox" title="Read">R</td>
									<td class="search_result chkbox" title="Write">W</td>
									<td class="search_result chkbox" title="Delete">D</td>
									<td class="search_result chkbox" title="Post">P</td>
									<td class="search_result chkbox" title="Note">N</td>
									<td class="search_result chkbox" title="Check">C</td>
									<td class="search_result chkbox" title="Approve">A</td>
									<td class="search_result chkbox" style="border-right:1px solid #6D84B4;">&nbsp;</td>
								</tr>
							</table>
					</span>
				</div>
				<div id="usr_priveleges_loading" class="loading_div" style="left:220px;top:5px;width:460px;height:420px;">
					<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
				</div>
				
				<div style="width:458px;margin-top:7px;" id="" class="ui-widget ui-widget-content ui-corner-all" >
					<table style="border-spacing:0px;border:0px solid #6D84B4;width:458px;">
						<tr>
							<td style="border-left:0px solid #6D84B4;text-align:left;padding:3px 3px 3px 3px;">
								<button id="help_privelege" style="z-index:999;">Help</button>
							</td>
							<td style="border-left:0px solid #6D84B4;text-align:right;padding:3px 3px 3px 3px;">
								<button id="delete_privelege">Delete</button>
								<button id="edit_privelege">Edit</button>
								<button id="save_privelege">Save</button>
								<button id="close_privelege">Close</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- User Admin Tab (Search User) -->
			<div style="width:204px;height:455px;padding-left:3px;" class="ui-widget ui-widget-content ui-corner-all" >
				<div id="usr_srch_tab" style="padding:3px 1px 3px 1px;">
					<input type="radio" id="srch_users" name="usr_srch_menu" checked="yes"/><label for="srch_users" style="text-align:left;width:95px;">Users</label>
					<input type="radio" id="srch_groups" name="usr_srch_menu"/><label for="srch_groups" style="text-align:left;width:95px;">User Groups</label>
				</div>
				
				<!-- User Tab (Search User) -->
				<div id="srch_users_tab">
					<table class="search_user_header">
						<tr><td class="search_header" style="text-align:center;width:40px;">ID</td>
						<td class="search_header" style="text-align:center;">Username&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
					</table>
					<div class="usr_srch_result" style="height:237px;">
						<div id="usr_srch_win_op_loading" class="loading_div" style="border-spacing:0px;left:10px;width:196px;height:237px;">
							<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
						</div>
						<span id="usr_srch_win_op_result"></span>
					</div>

					<table width='100%'>
						<tr>
							<td style="width:100px;padding:10px 0px 0px 0px;" colspan="2">User Group:</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<select name="usr_search_grp" id="usr_search_grp" class="text_input search_select" style="width:190px;" onchange="document.getElementById('usrgrp').value=this.value;">
									<option value="0" selected>All Users</option>
									<?php
										$result=$MySQLi->sqlQuery("SELECT `UserGroupID`,`UserGroupName` FROM `tblsystemusergroups` WHERE `UserGroupID` <> 'USRGRP000'ORDER BY `UserGroupName`;");
										while($usrgrps=mysqli_fetch_array($result, MYSQLI_BOTH)) {
											echo "<option value='".$usrgrps['UserGroupID']."'>".$usrgrps['UserGroupName']."</option>";
										} unset($result);
									?>
								</select><input type="hidden" id="usrgrp" value="0"/>
							</td>
						</tr>
						<tr>
							<td colspan="2">Search By:</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<select name="usr_search_opt" id="usr_search_opt" class="text_input search_select" style="width:190px;">
									<option value="EmpID">ID Number</option>
									<option value="EmpLName" selected>Last Name</option>
									<option value="EmpFName">First Name</option>
									<option value="EmpMName">Middle Name</option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">Search Text:</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<input name="usr_search_key" id="usr_search_key" class="text_input search_input" type="text" style="width:184px;">
							</td>
						</tr>
						<tr>
							<td align="left" style="padding:5px 3px 2px 3px;"><button id="user_new" style="width:92px;text-align:left;" onClick="formUser('',0);">New User</button></td>
							<td align="right" style="padding:5px 3px 2px 0px;"><button id="search_user" style="width:92px;text-align:left;" onClick="ajaxSearchUser(document.getElementById('usrgrp').value,document.getElementById('usr_search_opt').value,document.getElementById('usr_search_key').value); return false;">Search</button></td>
						</tr>
					</table>
				</div>
				<!-- User Group Tab (Search Group) -->
				<div id="srch_groups_tab" style="display:none;">
					<table class="search_user_header">
						<tr><td class="search_header" style="text-align:center;width:40px;">Code</td><td class="search_header" style="text-align:center;">Group Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
					</table>
					<div class="grp_srch_result">
						<div id="grp_srch_win_op_loading" class="loading_div" style="left:10px;width:196px;height:328px;">
							<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
						</div>
						<span id="grp_srch_win_op_result"></span>
					</div>

					<table width='100%'>
						<tr>
							<td colspan="2">Search:</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<input name="usr_search_key" id="grp_search_key" class="text_input search_input" type="text" style="width:184px;">
							</td>
						</tr>
						<tr>
							<td align="left" style="padding:5px 3px 2px 3px;"><button id="group_new" style="width:92px;text-align:left;" onClick="formUserGroup('',1);">New Group</button></td>
							<td align="right" style="padding:5px 3px 2px 0px;"><button id="search_group" style="width:92px;text-align:left;" onClick="ajaxSearchUserGroup(document.getElementById('grp_search_key').value); return false;">Search</button></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		
		<!-- Personnel Leave Management Window -->
		<div id="win_sys_plm" title="Leave Credit Manager" style="height:auto;">
			<div style="float:right;width:460px;height:455px;" class="ui-widget">
				<div style="width:460px;height:418px;" class="ui-widget">
					<span id="plm_window">
						<!-- Personnel small info -->
							<div style="background:#E6EFFF;width:auto;margin-bottom:4px;" class="ui-widget ui-widget-content ui-corner-all" >
								<table style="border-spacing:0px;border:0px solid #6D84B4;width:450px;">
									<tr>
										<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>PERSONNEL:</label></td>	
										<td colspan="3" style="padding:3px 0px 0px 0px;"><input value="" class="text_input" style="width:375px" readonly type="text"></td>
									</tr>
									<tr>
										<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:65px"><label>USER ID:</label></td>	
										<td style="padding:3px 0px 3px 0px;"><input value="" class="text_input" style="width:40px" readonly type="text"></td>
										
										<td class="form_label" style="border-left:0px solid #6D84B4;padding:3px 3px 3px 3px;width:70px"><label>OFFICE/GROUP:</label></td>
										<td style="padding:3px 0px 3px 0px;width:200px">
											<select class="text_input search_select" style="width:200px" disabled>
												<option>&nbsp;</option><option>XXX</option>
											</select>
										</td>
									</tr>
								</table>
							</div>
							
							<table style="border-spacing:0px;border:1px solid #6D84B4;width:460px;">
								<tr><td class="search_header" style="text-align:center;width:80px;">ID</td><td class="search_header" style="text-align:center;">PERSONNEL</td><td class="search_header" style="text-align:center;width:100px;">CERTIFICATION</td></tr>
							</table>
							<div class="" style="border:1px solid #6D84B4;width:458px;height:330px;overflow:auto;">			
								<table style="border-spacing:0px;border:0px solid #6D84B4;width:438px;">

								</table>
							</div>
					</span>
				</div>
				<div id="plm_loading" class="loading_div" style="left:220px;top:5px;width:460px;height:420px;">
					<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
				</div>
				
				<div style="width:458px;margin-top:7px;" id="" class="ui-widget ui-widget-content ui-corner-all" >
					<table style="border-spacing:0px;border:0px solid #6D84B4;width:458px;">
						<tr>
							<td style="border-left:0px solid #6D84B4;text-align:left;padding:3px 3px 3px 3px;">
								<button id="help_plm" style="z-index:999;">Help</button>
							</td>
							<td style="border-left:0px solid #6D84B4;text-align:right;padding:3px 3px 3px 3px;">
								<button id="delete_plm">Delete</button>
								<button id="edit_plm">Edit</button>
								<button id="save_plm">Save</button>
								<button id="close_plm">Close</button>
							</td>
						</tr>
					</table>
				</div>
			</div>
			
			<!-- User Admin Tab (Search User) -->
			<div style="width:204px;height:455px;padding-left:3px;" class="ui-widget ui-widget-content ui-corner-all" >
				<div id="plm_srch_tab" style="padding:3px 1px 3px 1px;">
					<input type="radio" id="srch_plm" name="plm_srch_menu" checked="yes"/><label for="srch_plm" style="text-align:left;width:95px;">Personnel</label>
					<input type="radio" id="srch_pgroups" name="plm_srch_menu"/><label for="srch_pgroups" style="text-align:left;width:95px;">Office/Group</label>
				</div>
				
				<!-- User Tab (Search User) -->
				<div id="srch_plm_tab">
					<table class="search_user_header">
						<tr><td class="search_header" style="text-align:center;width:40px;">ID</td>
						<td class="search_header" style="text-align:center;">Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
					</table>
					<div class="usr_srch_result" style="height:277px;">
						<div id="plm_srch_win_op_loading" class="loading_div" style="border-spacing:0px;left:10px;width:196px;height:277px;">
							<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
						</div>
						<span id="plm_srch_win_op_result"></span>
					</div>

					<table width='100%'>
						<tr>
							<td style="width:100px;padding:10px 0px 0px 0px;" colspan="2">Office/Group:</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<select name="plm_search_grp" id="plm_search_grp" class="text_input search_select" style="width:190px;" onchange="document.getElementById('usrpgrp').value=this.value;">
									<option value="%" selected>All Office/Group</option>
									<?php
										$result=$MySQLi->sqlQuery("SELECT `CGrpID`,`CGrpCode` FROM `tblempcgroups` WHERE `CGrpID` <> 'CG00' ORDER BY `CGrpCode`;");
										while($usrgrps=mysqli_fetch_array($result, MYSQLI_BOTH)) {
											echo "<option value='".$usrgrps['CGrpID']."'>".$usrgrps['CGrpCode']."</option>";
										} unset($result);
									?>
								</select><input type="hidden" id="usrpgrp" value="0"/>
							</td>
						</tr>
						<tr>
							<td colspan="2">Search Text:</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<input name="plm_search_key" id="plm_search_key" class="text_input search_input" type="text" style="width:184px;">
							</td>
						</tr>
						<tr>
							<td align="right" style="padding:5px 3px 2px 0px;"><button id="search_p" style="width:92px;text-align:left;" onClick="ajaxSearchPers(document.getElementById('usrpgrp').value,document.getElementById('plm_search_key').value); return false;">Search</button></td>
						</tr>
					</table>
				</div>
				
				<!-- Personnel Group Tab (Search Group) -->
				<div id="srch_pgroups_tab" style="display:none;">
					<table class="search_user_header">
						<tr><td class="search_header" style="text-align:center;width:40px;">Code</td><td class="search_header" style="text-align:center;">Office/Group&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
					</table>
					<div class="grp_srch_result">
						<div id="pgrp_srch_win_op_loading" class="loading_div" style="left:10px;width:196px;height:328px;">
							<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
						</div>
						<span id="pgrp_srch_win_op_result"></span>
					</div>

					<table width='100%'>
						<tr>
							<td colspan="2">Search:</td>
						</tr>
						<tr>
							<td align="center" colspan="2">
								<input id="pgrp_search_key" class="text_input search_input" type="text" style="width:184px;">
							</td>
						</tr>
						<tr>
							<td align="left" style="padding:5px 3px 2px 3px;"><button id="pgroup_new" style="width:92px;text-align:left;" onClick="formUserGroup('',1);">New Group</button></td>
							<td align="right" style="padding:5px 3px 2px 0px;"><button id="search_group" style="width:92px;text-align:left;" onClick="ajaxSearchPersGroup(document.getElementById('pgrp_search_key').value); return false;">Search</button></td>
						</tr>
					</table>
				</div>
			</div>
		</div>
		
		<!-- System Preferences Window -->
		<div id="win_sys_sysc" title="Preferences" style="height:auto;">
			<div style="float:right;width:520px;height:455px;" class="ui-widget">
				<div style="width:520px;height:418px;" class="ui-widget ui-widget-content ui-corner-all">
					<span id="sysc_details">
						
					</span>
				</div>
				
				<div id="sys_pref_loading" class="loading_div" style="left:220px;top:5px;width:520px;height:420px;">
					<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
				</div>
				
				<div style="width:520px;margin-top:5px;" id="" class="ui-widget ui-widget-content ui-corner-all" >
					<table style="border-spacing:0px;border:0px solid #6D84B4;width:520px;">
						<tr>
							<td style="border-left:0px solid #6D84B4;text-align:left;padding:3px 3px 3px 3px;">
								<button id="help_plm" style="z-index:999;">Help</button>
							</td>
							<td style="border-left:0px solid #6D84B4;text-align:right;padding:3px 3px 3px 3px;">
								<button id="close_sysc">Close</button>
							</td>
						</tr>
					</table>
				</div>
				
			</div>
			
			<!-- Preference Menu -->
			<div style="width:150px;height:455px;padding-left:0px;" class="ui-widget" >
				<div id="e_menu_1">
					<input type="radio" id="pref_menu_holidays" name="pref_menu" checked="checked"/><label for="pref_menu_holidays" class="pref_menu">Holidays</label><br/>
					<input type="radio" id="pref_menu_1_spsi" name="pref_menu"/><label for="pref_menu_1_spsi" class="pref_menu">...</label><br/>
					<input type="radio" id="pref_menu_1_dpnt" name="pref_menu"/><label for="pref_menu_1_dpnt" class="pref_menu">...</label><br/>
				</div>
			</div>
		</div>
		
		<!-- Unused Force Leave Updating Window -->
		<div id="win_process_ufl" title="Process Unused Force Leave" style="height:auto;">
			<div id="personnel" style="width:367px;">
				<table style="width:365px;">
					<tr>
						<td class="form_label"><label>ID Number From:</label></td>
							<td class="pds_form_input"><input type="text" name="prFirstID" id="prFirstID" class="text_input" value="0" style="width:50px;text-align:center;"></td>
						<td class="form_label"><label>To:</label></td>
							<td class="pds_form_input" style="text-align:left;"><input type="text" name="prLastID" id="prLastID" class="text_input" value="99999" style="width:50px;text-align:center;"></td>
						<td style="text-align:right;width:105px;" rowspan="2"><button id="start_ufl" class="start_button" onClick="UpdateUFL();" style="padding:10px 5px 10px 5px;">Start</button></td>
					</tr>
					<tr>
						<td class="form_label"><label>Group/Office:</label></td>
						<td class="pds_form_input" colspan="3">
							<select name="CGrpID" id="CGrpID" class="text_input search_select" style="width:150px" <?php echo $ReadOnly; ?>>
								<?php
									$result=$MySQLi->sqlQuery("SELECT `CGrpID`,`CGrpCode` FROM `tblempcgroups` WHERE `CGrpID` <> 'CG00' ORDER BY `CGrpCode`;");
									while($CGrp=mysqli_fetch_array($result, MYSQLI_BOTH)){
										if($CGrpID==$CGrp['CGrpID']){echo "<option value='".$CGrp['CGrpID']."' selected>".$CGrp['CGrpCode']."</option>";}
										else{echo "<option value='".$CGrp['CGrpID']."'>".$CGrp['CGrpCode']."</option>";}
									} unset($result);
								?>
							</select>
						</td>
					</tr>
				</table>
			</div>
			<textarea id="process_ufl_logs" rows='15' cols='68' readonly></textarea>
			<div id="pbar_process_ufl"><div id="pbar_process_ufl_lbl" class="pbar-label"></div></div>
		</div>
		
		<!-- User Options Window -->
		<div id="win_user_set" title="User Options" style="height:auto;">
		</div>
		
		<!-- Small Search Window for Employee -->
		<div id="d_form_select_em" title="Select" class="select_window ui-corner-all">
			<table>
				<tr>
					<td class="form_label" style="width:30px;"><label>Search: </label></td><td><input type="text" name="sml_srch_win_em" id="sml_srch_win_em" class="text_input" style="width:153px" onKeyPress="if(event.keyCode==13){ajaxSmallEmpSearch(this.value);}"></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li class="ui-state-default ui-corner-all" title="Search" onClick="ajaxSmallEmpSearch(document.getElementById('sml_srch_win_em').val());"><span class="ui-icon ui-icon-search"></span></li></ul></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li class="ui-state-default ui-corner-all" title="Close" onClick='$("#d_form_select_em").hide("blind");'><span class="ui-icon ui-icon-close"></span></li></ul></td>
				</tr>
			</table>
			<table class="search_header" style="width:250px;">
				<tr><td class="search_header" style="text-align:center;width:60px;">ID</td><td class="search_header" style="text-align:center;">Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
			</table>
			<div class="sml_srch_result">
				<div id="sml_srch_win_em_loading" class="loading_div" style="left:7px;width:246px;height:157px;">
					<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
				</div>
				<span id="sml_srch_win_em_result"></span>
			</div>
		</div>
		
		<!-- Small Search Window for Position and Office -->
		<div id="d_form_select_op" title="Select" class="select_window ui-corner-all">
			<table>
				<tr>
					<td class="form_label" style="width:30px;"><label>Search: </label></td><td><input type="text" name="sml_srch_win_op" id="sml_srch_win_op" class="text_input" style="width:153px" onKeyPress="if(event.keyCode==13){ajaxSmallOPSearch(this.value);}" /></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li class="ui-state-default ui-corner-all" title="Search" onClick="ajaxSmallOPSearch($('#sml_srch_win_op').val());"><span class="ui-icon ui-icon-search"></span></li></ul></td><td><ul class="ui-widget ui-helper-clearfix ul-icons"><li class="ui-state-default ui-corner-all" title="Close" onClick='$("#d_form_select_op").hide("blind");'><span class="ui-icon ui-icon-close"></span></li></ul></td>
				</tr>
			</table>
			<table class="search_header" style="width:250px;">
				<tr><td class="search_header" style="text-align:center;width:60px;">ID</td><td class="search_header" style="text-align:center;">Description&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr>
			</table>
			<div class="sml_srch_result">
				<div id="sml_srch_win_op_loading" class="loading_div" style="left:7px;width:246px;height:157px;">
					<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
				</div>
				<span id="sml_srch_win_op_result"></span>
			</div>
		</div>

		<!-- Context Menu Personnel -->
		<ul id="context_menu_emp" class="contextMenu">
			<li id="ctm_emp_open" class="contextMenu_item"><span class="ui-icon ui-icon-folder-open contextIcon"></span><a href="#Open"><b>Open</b></a></li>
			<?php if($Authorization[2]){ ?><li id="ctm_emp_chsts" class="contextMenu_item separator"><span class="ui-icon ui-icon-flag contextIcon"></span><a href="#ChangeStatus"><b>Change Status</b></a></li><?php } ?>
			<li id="ctm_emp_add2grp" class="contextMenu_item separator"><span class="ui-icon ui-icon-cart contextIcon"></span><a href="#AddToGroup"><b>Add to Group</b></a></li>
			<li id="ctm_emp_print" class="contextMenu_item"><span class="ui-icon ui-icon-print contextIcon"></span><a href="#Print"><b>Print PDS</b></a></li>
		</ul>
		
		<div id="global_loading_div" class="loading_div" style="border-spacing:0px;left:0px;top:0px;">
			<table width='100%' height='100%'><tr valign='center'><td align='center'><img src="css/<?php echo $_SESSION['theme']; ?>/images/loader.gif"/><br/><span class="loading_text">Loading...</span></td></tr></table>
		</div>
						
		<script type="text/javaScript">
		
			//config
			$float_speed=1500; //milliseconds
			$float_easing="easeOutQuint";
			$menu_fade_speed=500; //milliseconds
			$closed_menu_opacity=0.75;

			//cache vars
			$fl_menu=$("#fl_menu");
			$fl_menu_menu=$("#fl_menu .menu");
			$fl_menu_label=$("#fl_menu .label");

			$(window).load(function() {
				menuPosition=$('#fl_menu').position().top;
				FloatMenu();
				$fl_menu.hover(
					function(){ //mouse over
						$fl_menu_label.fadeTo($menu_fade_speed, 1);
						$fl_menu_menu.fadeIn($menu_fade_speed);
					},
					function(){ //mouse out
						$fl_menu_label.fadeTo($menu_fade_speed, $closed_menu_opacity);
						$fl_menu_menu.fadeOut($menu_fade_speed);
					}
				);
			});

			$(window).scroll(function () { 
				FloatMenu();
			});

			function FloatMenu(){
				var scrollAmount=$(document).scrollTop();
				var newPosition=menuPosition+scrollAmount;
				if($(window).height()<$fl_menu.height()+$fl_menu_menu.height()){
					$fl_menu.css("top",menuPosition);
				} else {
					$fl_menu.stop().animate({top: newPosition}, $float_speed, $float_easing);
				}
			}		
		
			cssdropdown.startchrome("chromemenu");
			Notifier('1');
			// $(function() { setInterval(function(){Notifier('1')},1000); });
			$(function() { setInterval(function(){Notifier('1')},30000); });
			
		</script>
	</body>
</html>
