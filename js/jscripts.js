
/* GLOBAL VARIABLES */
var epage="pinfo";var opage="oinfo";var t_eid="00000";var eid="00000";var eName="";var t_oid="SOOF00000";var oid="SOOF00000";var mode=0;var mod=0;var isEmpInfoCollapsed=false;var isOffInfoCollapsed=false;var curX;var curY;var sWin="sPosition";var IDtoRemoveFrList=0; var UpdUsrGrp=0; var UserID="00000"; var GroupID="USRGRP000"; var UpdCGrpID=0;  var PerID="00000"; var CGrpID="USRGRP000"; var debugMode=false; var noticeOn=false; var notifier=false; var msgboxOn=false; var messenger=false; var usboxOn=false; var usopen=false; var flchkall=false; var eid_t="0";

function showPer(uid,gid){
	//alert("showPer("+uid+", "+gid+");");
	PerID=uid;
	CGrpID=gid;
	UpdCGrpID=0;
	showLeaveOpt(uid,gid,'1');
}

/* SHOW Pers GROUP -> Load priveleges */
function showPersGroup(gid){
	CGrpID=gid;
	UpdCGrpID=1;
	showLeaveOpt('0',gid,'1');
}

/* SHOW USER -> Load priveleges */
function showUser(uid,gid){
	UserID=uid;
	GroupID=gid;
	UpdUsrGrp=0;
	showPriveleges(uid,gid,'1');
}

/* SHOW USER GROUP -> Load priveleges */
function showUserGroup(gid){
	GroupID=gid;
	UpdUsrGrp=1;
	showPriveleges('0',gid,'1');
}

/* SELECT Employee */
function selectEmployee(i){ t_eid=i;
	$("#context_menu_emp").css("left",curX-3);
	$("#context_menu_emp").css("top",curY-3);
	$("#context_menu_emp").show("highlight");
}

/* SELECT Office */
function selectOffice(i){ t_oid=i;
	$("#context_menu_off").css("left",curX-3);
	$("#context_menu_off").css("top",curY-3);
	$("#context_menu_off").show("highlight");
}

/* SELECT Customed Group */
function selectCustomGroup(i){ t_eid=i;
	$("#context_menu_grp").css("left",curX-3);
	$("#context_menu_grp").css("top",curY-3);
	$("#context_menu_grp").show("highlight");
}

/**/
function isGov(is){
	if(is=='YES'){
		document.getElementById('MotherOfficeDesc').disabled=false;
		document.getElementById('AssignedOfficeDesc').disabled=false;
		document.getElementById('PosDesc').disabled=false;
		document.getElementById('SalGrdYear').disabled=false;
		document.getElementById('SalGrade').disabled=false;
		document.getElementById('SalStep').disabled=false;
		document.getElementById('SRecSalary').disabled=true;
	}
	else{
		document.getElementById('MotherOfficeDesc').disabled=true;
		document.getElementById('AssignedOfficeDesc').disabled=true;
		document.getElementById('PosDesc').disabled=true;
		document.getElementById('SalGrdYear').disabled=true;
		document.getElementById('SalGrade').disabled=true;
		document.getElementById('SalStep').disabled=true;
		document.getElementById('SRecSalary').disabled=false;
	}
}

/* Filter Query */
function FilterQuery(form){
	var fl=form.name;
	var lt=form.flt_ltype.value;
	var yr=form.flt_year.value;
	var mo=form.flt_month.value;
	var st;
	for(i=0;i<=4;i++){if(form.flt_status[i].checked){st=form.flt_status[i].value;}}
	//showMessage("Year: "+yr+"<br/>Month: "+mo+"<br/>Status: "+st);
	if(fl=="filter_to"){showPendingDocuments("vtra",yr,mo,st);}
	else if(fl=="filter_lv"){showPendingDocuments("vliv",lt,yr,mo,st);}
}

/* Message BOX modal alert */
function showMessage(msg){
	var msgSection=msg.split("~");
	var msgArray=msg.split(" ");
	var iCon, content;
	var str = msgArray[1].toUpperCase();
	if((msgArray[0]=="ERROR")||(msgArray[0]=="ERROR:")){iCon="error";$('#d_message').dialog({title:"PMIS Error"});content="<span style='font-weight:bold;text-shadow:1px 1px 0 #977;color:#CC3333;font-size:1.2em;'>"+msgSection[0]+"</span><br/><span style='font-size:1.1em;font-weight:bold;'>"+msgSection[1]+"</span>";}
	else if((str.search("WARNING")>-1)){iCon="critical";$('#d_message').dialog({title:"PMIS Warning"});content="<span style='font-size:1.4em;'>"+msgSection[0]+"</span><br/><span style='font-size:1.1em;font-weight:bold;'>"+msgSection[1]+"</span>";}
	else{iCon="info";$('#d_message').dialog({title:"PMIS Message"});content="<span style='font-size:1.1em;font-weight:bold;'>"+msg+"</span>";}
	$('#d_message').html("<table><tr><td style='width:50px;text-align:center;vertical-align:top;'><div class='"+iCon+"'>&nbsp;</div></td><td style='text-align:left;vertical-align:middle;'><div style='width:300px;'>"+content+"</div></td></tr></table>");
	$('#d_message').dialog('open');
}//"<span style='font-weight:bold;text-shadow:1px 1px 0 #666;color:#e17009;font-size:1.4em;'>"+  </span>

/* Message BOX modal confirm */
function showConfirmation(qsn){
	var iCon = "critical";
	$('#d_confirm').dialog({title:"PMIS Warning"});
	$('#d_confirm').html("<table><tr><td style='width:50px;text-align:center;vertical-align:top;'><div class='"+iCon+"'>&nbsp;</div></td><td style='text-align:left;vertical-align:middle;'><div style='width:300px;'><span style='font-weight:bold;font-size:1.1em;'>"+qsn+"<span/></div></td></tr></table>");
	$('#d_confirm').dialog('open');
}

/* Message BOX modal input box */
function getInformation(qsn){
	var iCon = "critical";
	$('#d_input').dialog({title:"PMIS Input Box"});
	$('#AskMsg').html(qsn);
	$('#d_input').dialog('open');
}

/* HELP Window */
function showHelp(h,p,title){
	var nh,pp,nx=""; // H0181
	pp = parseInt(h.substring(4,5))+1;
	if(pp<=p){nh = h.substring(0,4)+pp;}
	else {nh = h.substring(0,4)+'1';}
	nx = "showHelp(&#39;"+nh+"&#39;,&#39;"+p+"&#39;,&#39;"+title+"&#39;);"
	$("#d_help").dialog({title:"PMIS Help - "+title+" "+(pp-1)+"/"+p});
	$("#d_help").html("<a href='#' onClick='"+nx+"'><img src='img/hlp/"+h+".jpg' width='1024px' hieght='768px' title='Click to view more...'></a>");
	$("#d_help").dialog('open');
}


/* Window Hide with Effects */
function hideWindow(win){$("#"+win).hide("drop");}

/* Process Personnel DTR */
function GetDTR(form,g) {
	if(g=="emp"){var id=form.EmpID.value;var yr=form.SelectYear.value;var mo=form.SelectMonth.value;var pr=form.SelectPayPeriod.value;ajaxGetDTR(0,id,yr,mo,pr,0,0,g);}
	if(g=="off"){var oid=form.SubOffID.value;var yr=form.SelectYear.value;var mo=form.SelectMonth.value;var pr=form.SelectPayPeriod.value;var st=form.ApptStID.value;ajaxGetDTR(1,0,yr,mo,pr,oid,st,g);}
	/*if(g=="grp"){
		var id=form.EmpID.value;
		var yr=form.SelectYear.value;
		var mo=form.SelectMonth.value;
		var pr=form.SelectPayPeriod.value;
		ajaxGetDTR(0,id,yr,mo,pr,0,0,g);
	}*/
}

/* Print Personnel DTR */
function printDTR(form,g) {
	if(g=="emp"){var id=form.EmpID.value;var yr=form.SelectYear.value;var mo=form.SelectMonth.value;var pr=form.SelectPayPeriod.value;window.open('reports/rpt_dtr.php?id='+id+'&yr='+yr+'&mo='+mo+'&pr='+pr,'mywindow','width=800,height=600');}
	if(g=="off"){var sof=form.SubOffID.value;var yr=form.SelectYear.value;var mo=form.SelectMonth.value;var pr=form.SelectPayPeriod.value;var aps=form.ApptStID.value;window.open('reports/rpt_dtr.php?yr='+yr+'&mo='+mo+'&pr='+pr+'&spo=1&sof='+sof+'&aps='+aps,'mywindow','width=800,height=600');}
	/*if(g=="grp"){
		var id=form.EmpID.value;
		var yr=form.SelectYear.value;
		var mo=form.SelectMonth.value;
		var pr=form.SelectPayPeriod.value;
		ajaxGetDTR(0,id,yr,mo,pr,0,0,g);
	}*/
}

/* DIALOG WINDOW - close*/
function closeDialogWindow(fName){$(function(){$('#'+fName).dialog("close");});}

$(document).ready(function(){
	/* TESTING SECTION */


	/* Bring to Front Selected Window */
	var z=1;$('.windows').each(function(){$(this).css('zIndex',z);z++;});
	$(".windows").mousedown(function(){winToFront(this);});
	function winToFront(obj){var zi=0;$(".windows").each(function(){var cur=$(this).css('zIndex');$(this).css('zIndex',1);zi=cur>zi?cur:zi;});$(obj).css('zIndex',parseInt(zi)+1);}
	
	/* Page Loader PDS Menu onClick */
	$(".r_emp_info_menu").click(function(){
		if(epage!=$(this).prop("for").substring(18)){
			epage=$(this).prop("for").substring(18);
			getEmpPage(epage,eid,mode);
		}
	});
	
	$("#e_menu_next").click(function(){
		if("none"==$("#e_menu_1").css("display")){
			$("#e_menu_1").show("drop");
			$("#e_menu_2").hide();
		}
		else{
			$("#e_menu_2").show("drop");
			$("#e_menu_1").hide();
		}
	});
	
	
	/* Get Current Mouse Position */
	$(document).mousemove(function(e){curX=e.pageX;curY=e.pageY;});
	
	/* Context Menu */
	$(".contextMenu").mouseover(function(){$(this).show();});
	$(".contextMenu").mouseout(function(){$(this).hide();});
	$(".contextMenu_item").click(function(){
		// Personnel
		if($(this).prop("id")=="ctm_emp_open"){
			mode=0;$("#context_menu_emp").hide();
			ajaxGetEmp(t_eid,mod);
			$("#edit_personnel").button({disabled:false});
		}
		else if($(this).prop("id")=="ctm_emp_chsts"){showForm('chst',0,0,1);}
		else if($(this).prop("id")=="ctm_emp_add2grp"){formCGroup();}
		else if($(this).prop("id")=="ctm_emp_print"){window.open('reports/rpt_pds.php?id='+t_eid,'mywindow','width=800,height=600');}
	});
	
	$("#edit_personnel").click(function(){if(eid=="00000"){return false;}mode=1;getEmpPage(epage,eid,mode);$(this).button({disabled:true});});
	
	
	/* SYSTEM USER MANAGEMENT BUTTONS */
	$("#srch_users").click(function(){
		$("#srch_users_tab").show();
		$("#srch_groups_tab").hide();
		//$("#edit_privelege,#save_privelege").button({disabled:true});
		UpdUsrGrp=0;
		showPriveleges('','USRGRP000',1);
	});
	$("#srch_groups").click(function(){
		$("#srch_groups_tab").show();
		$("#srch_users_tab").hide();
		//$("#edit_privelege,#save_privelege").button({disabled:true});
		UpdUsrGrp=1;
		showPriveleges('','USRGRP000',1);
	});
	$("#edit_privelege").click(function(){$("#save_privelege").button({disabled:false});showPriveleges(UserID,GroupID,0);});
	$("#save_privelege").click(function(){$("#user_priveleges").submit();});
	$("#close_privelege").click(function(){$("#win_sys_users").dialog("close");});
	$("#close_sysc").click(function(){$("#win_sys_sysc").dialog("close");});
	
	/* PLM MANAGEMENT BUTTONS */
	$("#srch_plm").click(function(){
		$("#srch_plm_tab").show();
		$("#srch_pgroups_tab").hide();
		//$("#edit_privelege,#save_privelege").button({disabled:true});
		//UpdUsrGrp=0;
		//showLeaveOpt('','CG00',1);
	});
	$("#srch_pgroups").click(function(){
		$("#srch_pgroups_tab").show();
		$("#srch_plm_tab").hide();
		//$("#edit_privelege,#save_privelege").button({disabled:true});
		//UpdUsrGrp=1;
		//showLeaveOpt('','CG00',1);
	});
	$("#edit_plm").click(function(){$("#save_plm").button({disabled:false});showLeaveOpt(PerID,CGrpID,0);});
	$("#save_plm").click(function(){$("#pers_flm").submit();});
	$("#close_plm").click(function(){$("#win_sys_plm").dialog("close");});
	
});

/* Small Search Window */
function selectWindow(f){ 
	sWin=f.id;
	if((sWin=="sEmployee")||(sWin=="sPersonnel")){
		$("#d_form_select_em").css("left",curX-245);
		$("#d_form_select_em").css("top",curY);
		$("#d_form_select_em").show("blind");
		//$("#sml_srch_win_em").focus();
		/*ajaxSmallEmpSearch("00000");*/
	}
	else{
		$("#d_form_select_op").css("left",curX-245);
		$("#d_form_select_op").css("top",curY);
		$("#d_form_select_op").show("blind");
		$("#sml_srch_win_op").focus();
		/*ajaxSmallOPSearch("%");*/
	}
}

/* Small Search Window Select it */
function selectThis(id,desc){
	if(sWin=="sEmployee"){
		if(document.getElementById("ListOfID").value.indexOf(id)!=-1){showMessage("Selected Employee is already in the list.");return}
		document.getElementById("ListOfID").value=document.getElementById("ListOfID").value+","+id;
		var optn=document.createElement("OPTION");
		optn.text=desc;
		optn.value=id;
		document.getElementById("ListedIDs").options.add(optn);
		$("#d_form_select_em").hide();
	}
	else if(sWin=="sPersonnel"){document.getElementById("ID").value=id;document.getElementById("EmpName").value=desc;$("#d_form_select_em").hide();}
	else{
		if(sWin=="sPosition"){document.getElementById("PosID").value=id;document.getElementById("PosDesc").value=desc;}
		if(sWin=="sMotherOffice"){document.getElementById("MotherOfficeID").value=id;document.getElementById("MotherOfficeDesc").value=desc;}
		if(sWin=="sAssignedOffice"){document.getElementById("AssignedOfficeID").value=id;document.getElementById("AssignedOfficeDesc").value=desc;}
		$("#d_form_select_op").hide();
	}
}

function RemoveIDfrList(lst,selectbox){
	var iR=0,i;
	var IDs=new Array();
	IDs=lst.value.split(",");
	for (i=IDs.length-1;i>0;--i){if(IDs[i]==IDtoRemoveFrList){iR=i;}}
	if(iR!=0){IDs.splice(iR,1);}
	IDtoRemoveFrList=0;
	lst.value=IDs.valueOf();
	for(i=selectbox.options.length-1;i>=0;i--){if(selectbox.options[i].selected){selectbox.remove(i);}}
}

function AddIDtoList(selectbox,text,value){
	var optn=document.createElement("OPTION");
	optn.text=text;
	optn.value=value;
	selectbox.options.add(optn);
	lst.value=lst.value+","+value;
}

function gotoPage(opt,radio) {

	/*
	** remove active state
	*/
	
	var radios = ["r_emp_info_menu_1_pinfo","r_emp_info_menu_1_spsi","r_emp_info_menu_1_dpnt","r_emp_info_menu_1_prnt","r_emp_info_menu_1_educ","r_emp_info_menu_1_csel","r_emp_info_menu_1_srec","r_emp_info_menu_1_vwor","r_emp_info_menu_1_trai","r_emp_info_menu_1_skil","r_emp_info_menu_1_ncad","r_emp_info_menu_1_orgs","r_emp_info_menu_1_chrf","r_emp_info_menu_1_qnda","r_emp_info_menu_1_pdtr","r_emp_info_menu_1_pcoc","r_emp_info_menu_1_leav","r_emp_info_menu_1_pr"]
	radios.forEach(function(radio,index) { $('#'+radio + '+label').removeClass('ui-state-active'); });

	$("#e_menu_2").show("drop");
	$("#e_menu_1").hide();
	
	getEmpPage(opt,eid,mode);
	$('#'+radio + '+label').addClass('ui-state-active');
	
}