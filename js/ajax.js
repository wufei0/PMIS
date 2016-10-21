/* SEARCH Employee load to emp_search_result */
function ajaxSearchEmp(opt,key){
	if ($('#searchPanel')[0]) {
	$(function(){
		$.ajax({
			url:"lib/scripts/_employee_search.php",
			global:false,type:"GET",
			data:{mod:"srch",opt:opt,key:key,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#search_emp_loading").show();},
			complete:function(){$("#search_emp_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){first_id=fields[0];$("#emp_search_result").html(fields[2]);}
				else{showMessage(data);}
			}
		});
	});
	}
}

/* SEARCH Office load to off_search_result */
function ajaxSearchOffice(opt,key){$(function(){$.ajax({url:"lib/scripts/_office_search.php",global:false,type:"GET",data:{mod:"srch",opt:opt,key:key,sid:Math.random()},dataType:"html",async:false,beforeSend:function(){$("#search_off_loading").show();},complete:function(){$("#search_off_loading").hide("highlight");},success:function(data){if(debugMode){alert(data);};first_id=data.substr(68,5);document.getElementById('off_search_result').innerHTML=data;}});});}

/* SEARCH Employee load to small_window_search_result */
function ajaxSmallEmpSearch(key){
	var url;
	$(function(){
		$.ajax({
			url:"lib/scripts/_small_search.php",
			global:false,
			type:"GET",
			data:{sWin:"sEmployee",key:key,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#sml_srch_win_em_loading").show();},
			complete:function(){$("#sml_srch_win_em_loading").hide("highlight");},
			success:function(data){if(debugMode){alert(data);};first_id=data.substr(68,5);document.getElementById('sml_srch_win_em_result').innerHTML=data;},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* SEARCH Office/Position load to small_window_search_result */
function ajaxSmallOPSearch(key){
	var url;
	$(function(){
		$.ajax({
			url:"lib/scripts/_small_search.php",
			global:false,
			type:"GET",
			data:{sWin:sWin,key:key,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#sml_srch_win_op_loading").show();},
			complete:function(){$("#sml_srch_win_op_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				first_id=data.substr(68,5);
				document.getElementById('sml_srch_win_op_result').innerHTML=data;
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* GET Employee load to Brief Info */
function ajaxGetEmp(id,mod){
	$(function(){
		$.ajax({
			url:"lib/scripts/_employee_search.php",
			global:false,
			type:"GET",
			data:{mod:"get",key:id,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#emp_brief_info_loading_1").show();},
			complete:function(){$("#emp_brief_info_loading_1").hide();},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){
					eid=t_eid;eName=fields[2]+", "+fields[3]+" "+fields[4].substr(0,1)+".";
					$("#brief_info_name").html(eName);
					$("#brief_info_id").html(fields[1]);
					$("#brief_info_position").html(fields[5]);
					$("#brief_info_salgrade").html(fields[6]+" - "+fields[7]);
					$("#brief_info_office").html(fields[8]);
					$("#brief_info_suboffice").html(fields[9]);
					$("#brief_info_salary").html(fields[10]);
					$("#y_emp_info_name").html(fields[2]+", "+fields[3]+" "+fields[4].substr(0,1)+".&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+fields[1]);
					$("#y_emp_info_position").html(fields[5]+"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+fields[6]+" - "+fields[7]);
					getEmpPage(epage,eid,mode);
					if(UrlExists("photos/"+eid+".jpg")){$("#emp_small_photo_1").prop("src","photos/"+eid+".jpg");}
					else{$("#emp_small_photo_1").prop("src","photos/no_photo.jpg");}
				}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* GET Office load to Brief Info */
function ajaxGetOff(id,mod){$(function(){$.ajax({url:"lib/scripts/_office_search.php",global:false,type:"GET",data:{mod:"get",key:id,sid:Math.random()},dataType:"html",async:false,beforeSend:function(){$("#off_brief_info_loading_1").show();},complete:function(){$("#off_brief_info_loading_1").hide();},success:function(data){ if(debugMode){alert(data);};var fields=new Array();fields=data.split('|');if (fields[0]!='0'){document.getElementById("brief_info_off_code").innerHTML=fields[2];document.getElementById("brief_info_off_name").innerHTML=fields[3];document.getElementById("brief_info_off_oic").innerHTML=fields[5];document.getElementById("brief_info_off_add").innerHTML=fields[4];document.getElementById("y_off_info_name").innerHTML=fields[3]+" ("+fields[2]+")";getOffPage(opage,fields[1],mod);}}});});}

/* Load Page to pinfo_main_* box emp_content_1 */
function getEmpPage(opt,id,mode){
	var url;
	$(function(){
		switch (opt){
			case("newp"):ajaxSearchEmp("EmpID",id);break;
			case("pinfo"):
				url="lib/pages/personnel_information.php";
				$("#win_personnel_information_1_title").html("Personal Information");
				break;
			case("spsi"):
				url="lib/pages/spouse_information.php";
				$("#win_personnel_information_1_title").html("Spouse Information");
				break;
			case("dpnt"):
				url="lib/pages/dependent_information.php";
				$("#win_personnel_information_1_title").html("Dependents Information");
				break;
			case("prnt"):
				url="lib/pages/parents_information.php";
				$("#win_personnel_information_1_title").html("Parent Information");
				break;
			case("educ"):
				url="lib/pages/education_background.php";
				$("#win_personnel_information_1_title").html("Educational Background");
				break;
			case("csel"):
				url="lib/pages/cs_eligibility.php";
				$("#win_personnel_information_1_title").html("Civil Service Eligibility");
				break;
			case("srec"):
				url="lib/pages/service_records.php";
				$("#win_personnel_information_1_title").html("Service Record");
				break;
			case("vwor"):
				url="lib/pages/voluntary_works.php";
				$("#win_personnel_information_1_title").html("Voluntary Jobs");
				break;
			case("trai"):
				url="lib/pages/trainings_seminars.php";
				$("#win_personnel_information_1_title").html("Trainings and Seminars");
				break;
			case("skil"):
				url="lib/pages/skills_hobbies.php";
				$("#win_personnel_information_1_title").html("Skills and Hobbies");
				break;
			case("ncad"):
				url="lib/pages/non_academic_recognitions.php";
				$("#win_personnel_information_1_title").html("Rewards/Recognitions");
				break;
			case("orgs"):
				url="lib/pages/organizations.php";
				$("#win_personnel_information_1_title").html("Organizations");
				break;
			case("qnda"):
				url="lib/pages/questions_answers.php";
				$("#win_personnel_information_1_title").html("Q & A");
				break;
			case("chrf"):
				url="lib/pages/character_references.php";
				$("#win_personnel_information_1_title").html("Character References");
				break;
			case("pdtr"):
				url="lib/pages/dtr_emp.php";
				$("#win_personnel_information_1_title").html("Daily Time Record (DTR)");
				break;
			case("trav"):
				url="lib/pages/travel_orders.php";
				$("#win_personnel_information_1_title").html("Travel Order");
				break;
			case("ppls"):
				url="lib/pages/personnel_locator.php";
				$("#win_personnel_information_1_title").html("Personnel Locator");
				break;
			case("leav"):
				url="lib/pages/leaves.php";
				$("#win_personnel_information_1_title").html("Personnel Leaves");
				break;
			case("pcoc"):
				url="lib/pages/coc.php";
				$("#win_personnel_information_1_title").html("Compensatory Time-Off");
				break;
			case("pr"):
				url="lib/pages/pr.php"; console.log(mode);
				$("#win_personnel_information_1_title").html("Performance Rating");
				break;
			case("prs"):
				url="lib/pages/prs.php"; console.log(mode);
				$("#win_personnel_information_1_title").html("PR (SyBase)");
				break;				
		}

		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{opt:opt,id:id,ids:eid_t,mode:mode,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#page_emp_loading_1").show();},
			complete:function(){$("#page_emp_loading_1").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){$("#emp_content_1").html(fields[2]);}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);return false;}
		});
	});
}


/* Load Page to office_information_* box off_content_1 */
function getOffPage(opt,id,mode){var url;if (opt=="oinfo"){url="lib/pages/office_information.php";document.getElementById('win_office_information_1_title').innerHTML="Office Information";}else if(opt=="opays"){url="lib/pages/payroll_off.php";document.getElementById('win_office_information_1_title').innerHTML="Payroll - [Grouped Per Office]";}else if(opt=="odtr"){url="lib/pages/dtr_off.php";document.getElementById('win_office_information_1_title').innerHTML="Daily Time Record - [Grouped Per Office]";}else if(opt=="ogsis"){url="lib/pages/premiums_gsis.php";document.getElementById('win_office_information_1_title').innerHTML="GSIS Premiums - [Grouped Per Office]";}else if(opt=="ohdmf"){url="lib/pages/premiums_hdmf.php";document.getElementById('win_office_information_1_title').innerHTML="Pag-ibig HDMF Premiums - [Grouped Per Office]";}else if(opt=="ophic"){url="lib/pages/premiums_phic.php";document.getElementById('win_office_information_1_title').innerHTML="PhilHealth Premiums - [Grouped Per Office]";}else if(opt=="owtax"){url="lib/pages/withholding_tax.php";document.getElementById('win_office_information_1_title').innerHTML="Withholding Tax - [Grouped Per Office]";}else if(opt=="ooded"){url="lib/pages/other_deductions.php";document.getElementById('win_office_information_1_title').innerHTML="Other Deductions";}$(function(){/* GET Employee load to Brief Info */ $.ajax({url:url,global:false,type:"POST",data:{opt:opt,id:id,mode:mode,sid:Math.random()},dataType:"html",async:false,beforeSend:function(){$("#page_off_loading_1").show();},complete:function(){$("#page_off_loading_1").hide("highlight");},success:function(data){if(debugMode){alert(data);};document.getElementById('off_content_1').innerHTML=data;},error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}});});}



/* ----------------------------------------------- FORM LOADER ----------------------------------------------- */
function showForm(opt,id,xid,mode){
	var url,frmTitle;
	switch (opt){
		case("newp"):url="lib/forms/personnel_information_form.php";frmTitle="Personnel";break;
		case("chst"):url="lib/forms/personnel_status_form.php";frmTitle="Personnel Status";id=t_eid;break;
		case("dpnt"):url="lib/forms/dependent_information_form.php";frmTitle="Dependent";break;
		case("educ"):url="lib/forms/education_background_form.php";frmTitle="Educational Background";break;
		case("csel"):url="lib/forms/cs_eligibility_form.php";frmTitle="Eligibility and Licence";break;
		case("srec"):url="lib/forms/service_record_form.php";frmTitle="Service Record";break;
		case("vwor"):url="lib/forms/voluntary_work_form.php";frmTitle="Voluntary Work";break;
		case("trai"):url="lib/forms/training_seminar_form.php";frmTitle="Training and Seminar";break;
		case("skil"):url="lib/forms/skill_hobby_form.php";frmTitle="Skill/Hobby";break;
		case("ncad"):url="lib/forms/non_academic_recognition_form.php";frmTitle="Reward/Recognition";break;
		case("orgs"):url="lib/forms/membership_form.php";frmTitle="Membership";break;
		case("chrf"):url="lib/forms/character_reference_form.php";frmTitle="Character Reference";break;
		
		case("leav"):url="lib/forms/leave_application_form.php";frmTitle="Leave";break;
		case("pliv"):url="lib/forms/confirm_document_status_form.php";frmTitle="Leave";break;
		case("pcoc"):url="lib/forms/coc_application_form.php";frmTitle="COC";break;
		case("ppls"):url="lib/forms/personnel_locator_form x.php";frmTitle="Personnel Locator";break;
		case("trav"):url="lib/forms/travel_order_form.php";frmTitle="Travel Order";break;
		
		case("usr"):url="lib/forms/user_information_form.php";frmTitle="User";break;
		case("xxx"):url="lib/forms/xxx.php";frmTitle="xxx";break;
		
		case("pr"):url="lib/forms/pr.php";frmTitle="Performance Rating";break;
	}
	
	$(function(){
	if(opt=="newp"){$("#global_loading_div").width($(window).width());$("#global_loading_div").height($(window).height());}
	if(mode==-1){$('#d_form_input').dialog({title:'DELETE '+frmTitle+' Information'});}else if(mode==0){$('#d_form_input').dialog({title:'NEW '+frmTitle+' Information'});}else if(mode==1){$('#d_form_input').dialog({title:'UPDATE '+frmTitle+' Information'});}else if(mode=='s1'){$('#d_form_input').dialog({title:'POST '+frmTitle+' Application'});}$.ajax({url:url,global:false,type:"POST",data:{id:id,xid:xid,mode:mode,sid:Math.random()},dataType:"html",async:false,
	beforeSend:function(){if(opt=="newp"){$("#global_loading_div").show();}else{$("#page_emp_loading_1").show();}},
	complete:function(){if(opt=="newp"){$("#global_loading_div").hide("highlight");}else{$("#page_emp_loading_1").hide("highlight");}},
	success:function(data){if(debugMode){alert(data);}
		var fields=new Array();
		fields=data.split('|');
		if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
		else if(fields[0]=="0"){showMessage(fields[2]);}
		else if(fields[0]=="1"){$("#d_form_input").html(fields[2]);$('#d_form_input').dialog('open');}
		else{showMessage(data);}
	},
	error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
	});
	});
}

/* ------------------------------------------ PROCESS FORM CONTENTS ------------------------------------------ */
function processForm(opt,form){
	var url,qParams={};
	$(function(){
		switch (opt){
			case("newp"):
				url="lib/scripts/_process_personal_info.php";
				qParams={mode:form.mode.value,ApptStID:form.ApptStID.value,EmpLName:form.EmpLName.value,EmpMName:form.EmpMName.value,EmpFName:form.EmpFName.value,EmpExtName:form.EmpExtName.value,EmpBirthDay:form.EmpBirthDay.value,EmpBirthMonth:form.EmpBirthMonth.value,EmpBirthYear:form.EmpBirthYear.value,sid:Math.random()};
				break;
			case("chst"):
				url="lib/scripts/_process_personal_status_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,EmpStatus:form.EmpStatus.value,sid:Math.random()};
				break;
			case("pinfo"):
				url="lib/scripts/_process_personal_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,EmpLName:form.EmpLName.value,EmpMName:form.EmpMName.value,EmpFName:form.EmpFName.value,EmpExtName:form.EmpExtName.value,EmpBirthDay:form.EmpBirthDay.value,EmpBirthMonth:form.EmpBirthMonth.value,EmpBirthYear:form.EmpBirthYear.value,EmpBirthPlace:form.EmpBirthPlace.value,EmpSex:form.EmpSex.value,EmpCivilStatus:form.EmpCivilStatus.value,EmpCitizenship:form.EmpCitizenship.value,EmpHeight:form.EmpHeight.value,EmpWeight:form.EmpWeight.value,EmpBloodType:form.EmpBloodType.value,EmpGSIS:form.EmpGSIS.value,EmpHDMF:form.EmpHDMF.value,EmpPH:form.EmpPH.value,EmpSSS:form.EmpSSS.value,EmpResAddSt:form.EmpResAddSt.value,EmpResAddBrgy:form.EmpResAddBrgy.value,EmpResAddMun:form.EmpResAddMun.value,EmpResAddProv:form.EmpResAddProv.value,EmpResZipCode:form.EmpResZipCode.value,EmpResTel:form.EmpResTel.value,EmpPerAddSt:form.EmpPerAddSt.value,EmpPerAddBrgy:form.EmpPerAddBrgy.value,EmpPerAddMun:form.EmpPerAddMun.value,EmpPerAddProv:form.EmpPerAddProv.value,EmpPerZipCode:form.EmpPerZipCode.value,EmpPerTel:form.EmpPerTel.value,EmpEMail:form.EmpEMail.value,EmpMobile:form.EmpMobile.value,EmpAgencyNo:form.EmpAgencyNo.value,EmpTIN:form.EmpTIN.value,CTCID:form.CTCID.value,CTCDateDay:form.CTCDateDay.value,CTCDateMonth:form.CTCDateMonth.value,CTCDateYear:form.CTCDateYear.value,CTCPlace:form.CTCPlace.value,sid:Math.random()};
				break;
			case("spsi"):
				url="lib/scripts/_process_spouse_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,EmpSpsLName:form.EmpSpsLName.value,EmpSpsMName:form.EmpSpsMName.value,EmpSpsFName:form.EmpSpsFName.value,EmpSpsExtName:form.EmpSpsExtName.value,EmpSpsAddSt:form.EmpSpsAddSt.value,EmpSpsAddBrgy:form.EmpSpsAddBrgy.value,EmpSpsAddMun:form.EmpSpsAddMun.value,EmpSpsAddProv:form.EmpSpsAddProv.value,EmpSpsZipCode:form.EmpSpsZipCode.value,EmpSpsTel:form.EmpSpsTel.value,EmpSpsJob:form.EmpSpsJob.value,EmpSpsBusDesc:form.EmpSpsBusDesc.value,EmpSpsBusAddSt:form.EmpSpsBusAddSt.value,EmpSpsBusAddBrgy:form.EmpSpsBusAddBrgy.value,EmpSpsBusAddMun:form.EmpSpsBusAddMun.value,EmpSpsBusAddProv:form.EmpSpsBusAddProv.value,EmpSpsBusZipCode:form.EmpSpsBusZipCode.value,EmpSpsBusTel:form.EmpSpsBusTel.value,sid:Math.random()};
				break;
			case("prnt"):
				url="lib/scripts/_process_parent_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,EmpFatherLName:form.EmpFatherLName.value,EmpFatherMName:form.EmpFatherMName.value,EmpFatherFName:form.EmpFatherFName.value,EmpFatherExtName:form.EmpFatherExtName.value,EmpMotherLName:form.EmpMotherLName.value,EmpMotherMName:form.EmpMotherMName.value,EmpMotherFName:form.EmpMotherFName.value,sid:Math.random()};
				break;
			case("dpnt"):
				url="lib/scripts/_process_dependent_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,DpntID:form.DpntID.value,DpntLName:form.DpntLName.value,DpntMName:form.DpntMName.value,DpntFName:form.DpntFName.value,DpntExtName:form.DpntExtName.value,DpntBirthMonth:form.DpntBirthMonth.value,DpntBirthDay:form.DpntBirthDay.value,DpntBirthYear:form.DpntBirthYear.value,RelID:form.RelID.value,RelDesc:form.RelDesc.value,DpntRemarks:form.DpntRemarks.value,sid:Math.random()};
				break;
			case("educ"):
				url="lib/scripts/_process_education_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,EducBgID:form.EducBgID.value,EducLvlID:form.EducLvlID.value,EducLvlDesc:form.EducLvlDesc.value,EducSchoolName:form.EducSchoolName.value,EducCourse:form.EducCourse.value,EducYrGrad:form.EducYrGrad.value,EducGradeLvlUnits:form.EducGradeLvlUnits.value,EducIncAttDateFromDay:form.EducIncAttDateFromDay.value,EducIncAttDateFromMonth:form.EducIncAttDateFromMonth.value,EducIncAttDateFromYear:form.EducIncAttDateFromYear.value,EducIncAttDateToDay:form.EducIncAttDateToDay.value,EducIncAttDateToMonth:form.EducIncAttDateToMonth.value,EducIncAttDateToYear:form.EducIncAttDateToYear.value,EducAwards:form.EducAwards.value,sid:Math.random()};
				break;
			case("csel"):
				url="lib/scripts/_process_eligibility_licence.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,CSEID:form.CSEID.value,CSEDesc:form.CSEDesc.value,CSERating:form.CSERating.value,CSEExamDay:form.CSEExamDay.value,CSEExamMonth:form.CSEExamMonth.value,CSEExamYear:form.CSEExamYear.value,CSEExamPlace:form.CSEExamPlace.value,CSELicNum:form.CSELicNum.value,CSELicReleaseDay:form.CSELicReleaseDay.value,CSELicReleaseMonth:form.CSELicReleaseMonth.value,CSELicReleaseYear:form.CSELicReleaseYear.value,sid:Math.random()};
				break;
			case("srec"):
				url="lib/scripts/_process_service_record_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,SRecID:form.SRecID.value,SRecFromDay:form.SRecFromDay.value,SRecFromMonth:form.SRecFromMonth.value,SRecFromYear:form.SRecFromYear.value,SRecToDay:form.SRecToDay.value,SRecToMonth:form.SRecToMonth.value,SRecToYear:form.SRecToYear.value,SRecEmployer:form.SRecEmployer.value,SRecIsGov:form.SRecIsGov.value,MotherOfficeID:form.MotherOfficeID.value,AssignedOfficeID:form.AssignedOfficeID.value,PosID:form.PosID.value,ApptStID:form.ApptStID.value,SRecJobDesc:form.SRecJobDesc.value,SalStep:form.SalStep.value,SRecSalary:form.SRecSalary.value,SalUnitID:form.SalUnitID.value,sid:Math.random()};
				break;
			case("vwor"):
				url="lib/scripts/_process_voluntary_work_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,VolOrgID:form.VolOrgID.value,VolOrgName:form.VolOrgName.value,VolOrgAddSt:form.VolOrgAddSt.value,VolOrgAddBrgy:form.VolOrgAddBrgy.value,VolOrgAddMun:form.VolOrgAddMun.value,VolOrgAddProv:form.VolOrgAddProv.value,VolOrgZipCode:form.VolOrgZipCode.value,VolOrgFromDay:form.VolOrgFromDay.value,VolOrgFromMonth:form.VolOrgFromMonth.value,VolOrgFromYear:form.VolOrgFromYear.value,VolOrgToDay:form.VolOrgToDay.value,VolOrgToMonth:form.VolOrgToMonth.value,VolOrgToYear:form.VolOrgToYear.value,VolOrgHours:form.VolOrgHours.value,VolOrgDetails:form.VolOrgDetails.value,sid:Math.random()};
				break;
			case("trai"):
				url="lib/scripts/_process_training_seminar_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,TrainID:form.TrainID.value,TrainDesc:form.TrainDesc.value,TrainFromDay:form.TrainFromDay.value,TrainFromMonth:form.TrainFromMonth.value,TrainFromYear:form.TrainFromYear.value,TrainToDay:form.TrainToDay.value,TrainToMonth:form.TrainToMonth.value,TrainToYear:form.TrainToYear.value,TrainHours:form.TrainHours.value,TrainSponsor:form.TrainSponsor.value,sid:Math.random()};
				break;
			case("skil"):
				url="lib/scripts/_process_skill_hobby_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,SkillID:form.SkillID.value,SkillDesc:form.SkillDesc.value,sid:Math.random()};
				break;
			case("ncad"):
				url="lib/scripts/_process_non_academic_recognition_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,NonAcadRecID:form.NonAcadRecID.value,NonAcadRecDetails:form.NonAcadRecDetails.value,sid:Math.random()};
				break;
			case("orgs"):
				url="lib/scripts/_process_organization_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,MemAssOrgID:form.MemAssOrgID.value,MemAssOrgDesc:form.MemAssOrgDesc.value,sid:Math.random()};
				break;
			case("chrf"):
				url="lib/scripts/_process_character_reference_info.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,RefID:form.RefID.value,RefLName:form.RefLName.value,RefMName:form.RefMName.value,RefFName:form.RefFName.value,RefExtName:form.RefExtName.value,RefAddSt:form.RefAddSt.value,RefAddBrgy:form.RefAddBrgy.value,RefAddMun:form.RefAddMun.value,RefAddProv:form.RefAddProv.value,RefZipCode:form.RefZipCode.value,RefTel:form.RefTel.value,sid:Math.random()};
				break;
			case("qnda"):
				var aParams={};
				url="lib/scripts/_process_qna.php";
				for(i=0;i<form.elements.length;i++){
					if(((form.elements[i].type=="radio")&&(form.elements[i].checked))||(form.elements[i].type=="text")||(form.elements[i].type=="hidden")){
						aParams[form.elements[i].name]=form.elements[i].value;
					}
				}qParams=aParams;
				break;
			case("leav"):
				url="lib/scripts/_process_leave_application_info.php";
				var d = new Date(); //alert(d.getFullYear()+"/n"+d.getMonth()+"/n"+d.getDate());return false;
				var LAFd = (typeof form.LivAppFiledDay == 'undefined')?d.getDate():form.LivAppFiledDay.value; 
				var LAFm = (typeof form.LivAppFiledMonth == 'undefined')?(d.getMonth()+1):form.LivAppFiledMonth.value;
				var LAFy = (typeof form.LivAppFiledYear == 'undefined')?d.getFullYear():form.LivAppFiledYear.value;
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,LivAppID:form.LivAppID.value,LivAppFiledDay:LAFd,LivAppFiledMonth:LAFm,LivAppFiledYear:LAFy,LivAppIncDateFrDay:form.LivAppIncDateFrDay.value,LivAppIncDateFrMonth:form.LivAppIncDateFrMonth.value,LivAppIncDateFrYear:form.LivAppIncDateFrYear.value,LivAppIncDayTimeFrom:form.LivAppIncDayTimeFrom.value,LivAppIncDateToDay:form.LivAppIncDateToDay.value,LivAppIncDateToMonth:form.LivAppIncDateToMonth.value,LivAppIncDateToYear:form.LivAppIncDateToYear.value,LivAppIncDayTimeTo:form.LivAppIncDayTimeTo.value,LeaveTypeID:form.LeaveTypeID.value,LTypeDetail:$('input[name=LTypeDetail]:checked').val(),LivAppNotes:form.LivAppNotes.value,sid:Math.random()};
				break;
			case("pcoc"):
				url="lib/scripts/_process_coc_application_info.php";
				qParams={mode:form.mode.value,COCID:form.COCID.value,EmpID:form.EmpID.value,COCEarnedDateMonth:form.COCEarnedDateMonth.value,COCEarnedDateDay:form.COCEarnedDateDay.value,COCEarnedDateYear:form.COCEarnedDateYear.value,COCEarnedHours:form.COCEarnedHours.value,COCNotes:form.COCNotes.value,sid:Math.random()};
				break;
			case("trav"):
				url="lib/scripts/_process_travel_order_info.php";
				var OutsideLU=(form.TOOutsideLU.checked)?1:0;
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,TOID:form.TOID.value,TOTo:form.ListOfID.value,TODayTimeFrom:form.TOIncDayTimeFrom.value,TODateFrDay:form.TODateFrDay.value,TODateFrMonth:form.TODateFrMonth.value,TODateFrYear:form.TODateFrYear.value,TODayTimeTo:form.TOIncDayTimeTo.value,TODateToDay:form.TODateToDay.value,TODateToMonth:form.TODateToMonth.value,TODateToYear:form.TODateToYear.value,TOOutsideLU:OutsideLU,TODestination:form.TODestination.value,TOSubject:form.TOSubject.value,TOBody:form.TOBody.value,sid:Math.random()};
				break;
				/* user administration */
			case("usra"):
				if(form.NewKey1.value.length<6){showMessage("ERROR: Invalid password length.<br/>Password must be atleast 6 characters.");return false;}
				if(form.NewKey1.value!=form.NewKey2.value){showMessage("ERROR: Passwords do not match.");return false;}
				url="lib/scripts/_process_user_info.php";
				qParams={mode:form.mode.value,UsrID:form.UsrID.value,NewKey:form.NewKey2.value,UsrGrpID:form.UsrGrpID.value,sid:Math.random()};
				break;
			case("pr"):
				if (form.RatingYear.value == '') {
					showMessage("ERROR: Please enter year.~");
					return false;
				}			
				url="lib/scripts/_process_pr.php";
				qParams={mode:form.mode.value,EmpID:form.EmpID.value,RatingID:form.RatingID.value,FirstSemesterScore:form.FirstSemesterScore.value,FirstSemesterRating:form.FirstSemesterRating.value,SecondSemesterScore:form.SecondSemesterScore.value,SecondSemesterRating:form.SecondSemesterRating.value,OverAllScore:form.OverAllScore.value,OverAllRating:form.OverAllRating.value,RatingYear:form.RatingYear.value};
				break;
		}

		if(opt=="chst"){$("#global_loading_div").width($(window).width());$("#global_loading_div").height($(window).height());}
		
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:qParams,
			dataType:"html",
			async:false,
			beforeSend:function(){if(opt=="chst"){$("#global_loading_div").show();}else{$("#page_emp_loading_1").show();}},
			complete:function(){if(opt=="chst"){$("#global_loading_div").hide("highlight");}else{$("#page_emp_loading_1").hide("highlight");}},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if((fields[0]=="-1") || (fields[0]==-1)){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				else if((fields[0]=="0") || (fields[0]==0)){showMessage(fields[2]);}
				else if((fields[0]=="1") || (fields[0]==1)){
					showMessage(fields[2]);
 					$('#d_form_input').dialog('close');
					$('#d_message').dialog({close:function(event,ui){
						
						if (opt=="leav") {
							gotoPage('leav','r_emp_info_menu_1_leav');
						} else {
						
							if (typeof form.EmpName != 'undefined'){
								showPendingDocuments("vliv",LeaveTypeID.value,form.LivAppIncDateFrYear.value,form.LivAppIncDateFrMonth.value,'X');
								viewRecordPLCT(form.EmpID.value,'L');
							} else {
								if(opt=="newp"){t_eid=fields[1];}
								else if(opt=="chst"){ajaxSearchEmp(document.getElementById('pds_search_cat').value, document.getElementById('pds_search_key').value);}
								ajaxGetEmp(fields[1],0);
							}
						
						}
						
					}});
				}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* ----------------------------------------------------------------------------------------------------------- */

/* For ADMIN/AO - Travel Orders */
function showPendingDocuments(opt,lt,yr,mo,st){
	var url,frmTitle;
	switch (opt){		
		case("vliv"):url="lib/pages/view_leave_applications.php";frmTitle="Leave Applications";break;
		case("vpls"):url="lib/pages/view_personnel_locators.php";frmTitle="Personnel Locator";break;
		case("vtra"):url="lib/pages/view_travel_orders.php";frmTitle="Travel Orders";break;
		
		case("xxx"):url="lib/pages/xxx.php";frmTitle="xxx";break;
	}
	$(function(){
		/*$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());*/
		$('#d_viewer_1').dialog({title:frmTitle});
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{Lt:lt,Yr:yr,Mo:mo,St:st,sid:Math.random()},
			dataType:"html",
			async:false,
			
			beforeSend:function(){$("#pending_loading_1").show();},
			complete:function(){$("#pending_loading_1").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){$("#d_viewer_1").html(fields[2]);$('#d_viewer_1').dialog('open');}
				else{/* Limited access user */
					showMessage(fields[2]);
					if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});/* Unauthorized/Expired session redirected to logout.php */}
				}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* ----------------------------------------------------------------------------------------------------------- */

function Messenger(gn) {
	$(function(){
		$.ajax({
			url:"lib/pages/show_notifications.php",
			global:true,
			type:"POST",
			data:{gn:gn,sid:Math.random()},
			dataType:"html",
			async:true,
			success:function(data){if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields['3']=='-1'){$("#Notifications").removeClass("ui-state-active").addClass("ui-state-default");notifier=false;}
				else if(fields['3']=='0'){$("#Notifications").removeClass("ui-state-default").addClass("ui-state-active");notifier=true;}
				else if(fields['3']=='1'){$("#Notifications").removeClass("ui-state-default").addClass("ui-state-active");notifier=true;$('#notifyAudio')[0].play();}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function showMessages() {
	//if(!notifier){return false;}
	if(usboxOn){usboxOn=false;$("#user_settings_box").hide("highlight");}
	if(noticeOn){noticeOn=false;$("#notification_box").hide("highlight");}
	if(msgboxOn){msgboxOn=false;$("#messages_box").hide("highlight");}
	else{msgboxOn=true;$("#messages_box").show("highlight");}
}

function Notifier(gn) {
	
	$(function(){
		$.ajax({
			url:"lib/pages/show_notifications.php",
			global:true,
			type:"POST",
			data:{gn:gn,sid:Math.random()},
			dataType:"html",
			async:true,
			success:function(data){if(debugMode){alert(data);}
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=='-1'){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				if(fields[3]=='-1'){$("#Notifications").removeClass("ui-state-notice").addClass("ui-state-default");notifier=true;}
				else if(fields[3]=='0'){$("#Notifications").removeClass("ui-state-default").addClass("ui-state-notice");notifier=true;}
				else if(fields[3]=='1'){$("#Notifications").removeClass("ui-state-default").addClass("ui-state-notice");notifier=true;$('#notifyAudio')[0].play();}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
	
}

function showNotifications() {
	if(!notifier){return false;}
	if(usboxOn){usboxOn=false;$("#user_settings_box").hide("highlight");}
	if(msgboxOn){msgboxOn=false;$("#messages_box").hide("highlight");}
	if(noticeOn){noticeOn=false;$("#notification_box").hide("highlight");}
	else{
		noticeOn=true;
		$(function(){
			$.ajax({
				url:"lib/pages/show_notifications.php",
				global:false,
				type:"POST",
				data:{sid:Math.random()},
				dataType:"html",
				async:true,
				beforeSend:function(){$("#notification_box").show("highlight");},
				success:function(data){if(debugMode){alert(data);};
					var fields=new Array();
					fields=data.split('|');
					if(fields[0]=='-1'){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
					else if(fields[0]=='0'){showMessage(fields[2]);}
					else if(fields[0]=='1'){$("#notification_content").html(fields[2]);}
					else{showMessage(data);}
				},
				error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
			});
		});
	}
}

function showUserSettings() {
	//if(!usopen){return false;}
	if(noticeOn){noticeOn=false;$("#notification_box").hide("highlight");}
	if(msgboxOn){msgboxOn=false;$("#messages_box").hide("highlight");}
	if(usboxOn){usboxOn=false;$("#user_settings_box").hide("highlight");}
	else{usboxOn=true;$("#user_settings_box").show("highlight");}
}

/* Show Leave Application Details and Processing options */
function showLeaveApplications(){
	var frmTitle="Leave Applications";
	$(function(){
		$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());
		$.ajax({
			url:"lib/pages/view_leave_applications.php",
			global:false,
			type:"POST",
			data:{sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){
					$('#d_viewer_1').dialog({title:frmTitle});
					$('#d_viewer_1').html(fields[2]);
					$('#d_viewer_1').dialog('open');
				}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* SHOW Personnel Leave Management to plm_window */
function showPriveleges(uid,gid,ro){
	$(function(){
		$.ajax({
			url:"lib/pages/view_priveleges.php",
			global:false,
			type:"POST",
			data:{uid:uid,gid:gid,ro:ro,uug:UpdUsrGrp,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#usr_priveleges_loading").show();},
			complete:function(){$("#usr_priveleges_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){
					$("#plm_window").html(fields[2]);
					if(ro==1){
						$("#delete_privelege").button({disabled:false});
						$("#edit_privelege").button({disabled:false});
						$("#save_privelege").button({disabled:true});
					}
					else{
						UserID=uid;GroupID=gid;
						$("#delete_plm").button({disabled:true});
						$("#edit_plm").button({disabled:true});
						$("#save_plm").button({disabled:false});
					}
				}
				else{
					showMessage(fields[2]);
					if(fields[0]=="-1"){
						$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});
					}
				}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* Confirm System Generated Leave Credits */
function processSysGenLivCred(id,yr,mo){
	var url="lib/scripts/_process_system_generated_leave_credits.php";
	$(function(){
		$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{id:id,yr:yr,mo:mo,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){$('#d_message').dialog({close:function(event,ui){viewRecordPLCT(fields[1],'L');}});showMessage(fields[2]);}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function viewRecordPLCT(id,plct,yr,ty,rr){ /* p-pls, l-leave, c-cto, t-travel */
	var opt,frmTitle,url;
	switch (plct){		
		case("P"):frmTitle="PLS History - ";url="lib/forms/pls_record_form.php";break;
		case("L"):frmTitle="Leave History and Credit Balance - ";url="lib/forms/leave_record_form.php";break;
		case("C"):frmTitle="COCs History and Time Balance - ";url="lib/forms/leave_record_form.php";break;
		case("T"):frmTitle="Travel Order History - ";url="lib/forms/to_record_form.php";break;
	}
	$(function(){
		$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{id:id,plct:plct,yr:yr,ty:ty,rr:rr,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){
					frmTitle+=fields[1];
					$('#d_viewer_2').dialog({title:frmTitle});
					$('#d_viewer_2').html(fields[2]);
					$('#d_viewer_2').dialog('open');
				}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function processDocument(id,docType,docID,docAction,remark){
	var opt,url;
	switch (docType){
		case("lv"):opt="leav";url="lib/scripts/_process_leave_applications.php";break;
		case("to"):opt="trav";url="lib/scripts/_process_travel_applications.php";break;
		case("cc"):opt="pcoc";url="lib/scripts/_process_coc_applications.php";break;
		case("pl"):opt="ppls";url="lib/scripts/_process_x_applications.php";break;
	}
	$(function(){
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{did:docID,act:docAction,rm:remark,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#page_emp_loading_1").show();},
			complete:function(){$("#page_emp_loading_1").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				$('#d_confirm').dialog('close');
				if(fields[0]=="-1"){
					showMessage(fields[2]);
					$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});
				}
				else if(fields[0]=="0"){
					showMessage(fields[2]);
				}
				else if(fields[0]=="1"){
					switch (docType){
						case("lv"):$('#d_message').dialog({close:function(event,ui){if(docAction==1){getEmpPage('leav',id,0);}else{viewRecordPLCT(id,'L');Notifier('1');}}});break;
						case("to"):$('#d_message').dialog({close:function(event,ui){if(docAction==1){getEmpPage('trav',id,0);}else{showTravelOrder(docID);Notifier('1');}}});break;
						case("cc"):$('#d_message').dialog({close:function(event,ui){getEmpPage('pcoc',id,0);}});break;
						case("pl"):$('#d_message').dialog({close:function(event,ui){if(docAction==1){getEmpPage('ppls',id,0);}else{viewRecordPLCT(id,'P');Notifier('1');}}});break;
					}
					showMessage(fields[2]);
				}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}


/* Show Travel Order Details and Processing options */
function showTravelOrder(to){
	var frmTitle="Travel Order Details - "+to;
	$(function(){
		$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());
		$.ajax({
			url:"lib/forms/travel_order_details_form.php",
			global:false,
			type:"POST",
			data:{to:to,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){
					$('#d_viewer_2').dialog({title:frmTitle});
					$('#d_viewer_2').html(fields[2]);
					$('#d_viewer_2').dialog('open');
				}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}



/* USER Administration */
function formUser(uid,mode){
	$(function(){
		$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());
		var url="lib/forms/user_information_form.php";
		if(mode==-1){$('#d_form_input').dialog({title:'DEACTIVATE User'});}
		else if(mode==0){$('#d_form_input').dialog({title:'NEW User'});}
		else if(mode==1){$('#d_form_input').dialog({title:'UPDATE User'});}
		else if(mode==2){$('#d_form_input').dialog({title:'Change User Password'});url="lib/forms/user_change_password_form.php";}
		$.ajax({
			url:url,
			global:false,type:"POST",
			data:{uid:uid,mode:mode,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){$('#d_form_input').html(fields[2]);$('#d_form_input').dialog('open');}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}


function processUserInfo(form){
	var mode=form.mode.value;
	if(mode==2){
		var UsrID=form.UsrID.value;
		var OldKey=form.OldKey.value;
		var NewKey1=form.NewKey1.value;
		var NewKey2=form.NewKey2.value;
		var UsrGrpID="";
	}
	else{
		var UsrID=form.UsrID.value;
		var OldKey="";
		var NewKey1=form.NewKey1.value;
		var NewKey2=form.NewKey2.value;
		var UsrGrpID=form.UsrGrpID.value;
	}
	
	if(NewKey1.length<6){showMessage("ERROR 406:~Invalid password length. Password must be atleast 6 characters.");return false;}
	if(NewKey1!=NewKey2){showMessage("ERROR 406:~Passwords do not match.");return false;}
	var url="lib/scripts/_process_user_info.php";
	$(function(){
	$("#global_loading_div").width($(window).width());
	$("#global_loading_div").height($(window).height());
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{mode:mode,UsrID:UsrID,OldKey:OldKey,NewKey1:NewKey1,NewKey2:NewKey2,UsrGrpID:UsrGrpID,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){showMessage(fields[2]);$('#d_form_input').dialog('close');}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}


function ajaxSearchPers(grp,uin){
	$(function(){
		$.ajax({
			url:"lib/scripts/_pers_search.php",
			global:false,
			type:"GET",
			data:{mod:"srch",grp:grp,uin:uin,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#plm_srch_win_op_loading").show();},
			complete:function(){$("#plm_srch_win_op_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){$("#plm_srch_win_op_result").html(fields[2]);}
				else if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else{showMessage(fields[2]);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* SEARCH System User load to usr_srch_win_op_result */
function ajaxSearchUser(grp,opt,uin){
	$(function(){
		$.ajax({
			url:"lib/scripts/_user_search.php",
			global:false,
			type:"GET",
			data:{mod:"srch",grp:grp,opt:opt,uin:uin,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#usr_srch_win_op_loading").show();},
			complete:function(){$("#usr_srch_win_op_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){$("#usr_srch_win_op_result").html(fields[2]);}
				else if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else{showMessage(fields[2]);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* SHOW System User Priveledges load to priveleges_window */
function showPriveleges(uid,gid,ro){
	$(function(){
		$.ajax({
			url:"lib/pages/view_priveleges.php",
			global:false,
			type:"POST",
			data:{uid:uid,gid:gid,ro:ro,uug:UpdUsrGrp,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#usr_priveleges_loading").show();},
			complete:function(){$("#usr_priveleges_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){
					$("#priveleges_window").html(fields[2]);
					if(ro==1){
						$("#delete_privelege").button({disabled:false});
						$("#edit_privelege").button({disabled:false});
						$("#save_privelege").button({disabled:true});
					}
					else{
						UserID=uid;GroupID=gid;
						$("#delete_privelege").button({disabled:true});
						$("#edit_privelege").button({disabled:true});
						$("#save_privelege").button({disabled:false});
					}
				}
				else{
					showMessage(fields[2]);
					if(fields[0]=="-1"){
						$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});
					}
				}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function formCGroup(){
	$(function(){
		$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());
		$('#d_form_input').dialog({title:'Add to Group'});
		$.ajax({
			url:"lib/forms/custom_group_form.php",
			global:false,type:"POST",
			data:{uid:t_eid,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){$("#d_form_input").html(fields[2]);$('#d_form_input').dialog('open');}
				else{
					showMessage(fields[2]);
					if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function processPersCGroup(form){
	
	var url="lib/scripts/_process_personnel_cgroup.php";
	$(function(){
	$("#global_loading_div").width($(window).width());
	$("#global_loading_div").height($(window).height());
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{CGrpID:form.CGrpID.value,eid:form.EmpID.value,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){showMessage(fields[2]);$('#d_form_input').dialog('close');}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}


/* SHOW System User Priveledges load to priveleges_window */
function showLeaveOpt(uid,gid,ro){
	//alert("showLeaveOpt("+uid+", "+gid+", "+ro+");");
	$(function(){
		$.ajax({
			url:"lib/pages/leave_credit_manager.php",
			global:false,
			type:"POST",
			data:{uid:uid,gid:gid,ro:ro,uug:UpdCGrpID,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#plm_loading").show();},
			complete:function(){$("#plm_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){
					$("#plm_window").html(fields[2]);
					if(ro==1){
						$("#delete_plm").button({disabled:false});
						$("#edit_plm").button({disabled:false});
						$("#save_plm").button({disabled:true});
					}
					else{
						UserID=uid;GroupID=gid;
						$("#delete_plm").button({disabled:true});
						$("#edit_plm").button({disabled:true});
						$("#save_plm").button({disabled:false});
						$('input[name=c_]').each(function(){$("#d_"+$(this).attr('id').substring(2)).attr("disabled",!$(this).checked);});
					}
				}
				else{
					showMessage(fields[2]);
					if(fields[0]=="-1"){
						$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});
					}
				}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function processLeaveManager(form){
	
	var cb_fl="";var dys="";var d=new Date(); var cYr=d.getFullYear();
	var cgp=form.CGrpID.value;
	if(UpdCGrpID==1){
		$("#"+form.id+" input[name=c_]").each(function(){var n=this.id.substring(2);var v=($(this).attr('checked')!="checked")?0:1;cb_fl+=n+":"+v+",";});cb_fl=cb_fl.replace(/,+$/g,'');
		$("#"+form.id+" select[name=d_]").each(function(){var n=this.id.substring(2);var v=$(this).attr('value');dys+=n+":"+v+",";});dys=dys.replace(/,+$/g,'');
	}
	else{
		cb_fl=PerID+":"+(($("#"+form.id+" input[id=flc_cb_"+cYr+"]").attr("checked")!="checked")?0:1);
		dys=PerID+":"+$("#"+form.id+" select[id=flc_dys_"+cYr+"]").attr("value");
	}
	
	var cb_pl="";
	$("#"+form.id+" input[name^=plc_]").each(function(){
		if($(this).attr('checked')=="checked"){
			var n=this.id.substring(4,9);
			var vd=$("#"+form.id+" select[id=pld_"+n+"]").val();//vd=(vd.lenght==2)?vd:"0"+vd;
			var vm=$("#"+form.id+" select[id=plm_"+n+"]").val();//vm=(vm.lenght==2)?vm:"0"+vm;
			var vy=$("#"+form.id+" input[id=ply_"+n+"]").val();
			cb_pl+=n+":"+vy+"-"+vm+"-"+vd+" 23:59:59,";
		}
	});
	cb_pl=cb_pl.replace(/,+$/g,'');
	
	//showMessage (cb_fl+"<br/>"+cb_pl);return false;
	
	$(function(){
		$.ajax({
			url:"lib/scripts/_process_leave_year_credit.php",
			global:false,
			type:"POST",
			data:{pid:PerID,cb_fl:cb_fl,cb_pl:cb_pl,dys:dys,cgp:cgp,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#usr_priveleges_loading").show();},
			complete:function(){$("#usr_priveleges_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){showMessage(fields[2]);$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else if(fields[0]=="0"){showMessage(fields[2]);showLeaveOpt(PerID,CGrpID,0);}
				else if(fields[0]=="1"){showMessage(fields[2]);$('#d_message').dialog({close:function(event,ui){showLeaveOpt(PerID,CGrpID,1);}});}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function UpdateUFL(){
	$('#pbar_process_ufl_lbl').text('Initializing...');
	$('#process_ufl_logs').append("");
	//showMessage("Bawas bassil VL...");
	UpdateUnUsedForceLeave('S',0,0,0,0,0);
}

function UpdateUnUsedForceLeave(ctr, ids, pid, cid, nid, did){
	var now_start=new Date();
	var ms_start=now_start.getTime();
	var url="lib/scripts/_update_unused_force_leave.php";

	$(function(){
		var sid=$("#prFirstID").val();
		var eid=$("#prLastID").val();
		var cgr=$("#CGrpID").val();
		$.ajax({
			url:url,
			global:false,type:"GET",
			data:{ctr:ctr, sid:sid, eid:eid, pid:pid, cid:cid, nid:nid, ids:ids, cgr:cgr, did:did},
			dataType:"html",async:true,
			beforeSend:function(){$("#start_ufl").button({disabled:true});},
			success:function(data){
				fields=data.split('|');
				if(fields[0]=='-1'){showMessage(fields[2]);$("#start_ufl").button({disabled:false});}
				else if(fields[0]=='0'){showMessage(fields[2]);$("#start_ufl").button({disabled:false});}
				else if(fields[0]=='S'){}
				else if(fields[0]=='C'){
					var pval=(fields[4]/fields[1])*100;
					pval=pval.toFixed(2); 
					$("#pbar_process_ufl").progressbar("value",parseInt(pval));
					$('#process_ufl_logs').append(fields[5]);
					var psconsole = $('#process_ufl_logs');
					psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
					var now_end=new Date();
					var ms_end=now_end.getTime();
					var ms_time=ms_end-ms_start;
					var rem_time=ms_time*(fields[4]-fields[3]);
					var sec_time=(rem_time/1000)%60; var ss=sec_time>=10?"":"0";
					var min_time=((rem_time/1000)/60)%60; var ms=min_time>=10?"":"0";
					var hr_time=((rem_time/1000)/60)/60; var hs=hr_time>=10?"":"0";
					$('#pbar_process_ufl_lbl').text('Processed: '+fields[4]+'/'+fields[1]+' ('+pval+'%) Time Remaining: '+hs+parseInt(hr_time)+':'+ms+parseInt(min_time)+':'+ss+parseInt(sec_time));
					UpdateUnUsedForceLeave(fields[0], fields[1], fields[2], fields[2], fields[3], fields[4]);
				}
				else if(fields[0]=='E'){
					var pval=(fields[4]/fields[1])*100;
					pval=pval.toFixed(2); 
					$("#pbar_process_ufl").progressbar("value",parseInt(pval));
					$('#pbar_process_ufl_lbl').text('Processed: '+fields[4]+'/'+fields[1]+' (100%) Time Remaining: 00:00:00');
					$('#process_ufl_logs').append(fields[5]);
					var psconsole = $('#process_ufl_logs');
					psconsole.scrollTop(psconsole[0].scrollHeight - psconsole.height());
					$("#start_ufl").button({disabled:false});
				}
				else{showMessage(data);$("#start_ufl").button({disabled:false});}
			},
			error:function(xhr,ajaxOptions,thrownError){$("#start_ufl").button({disabled:false});showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}


function processPriveleges(form){
	$(function(){
		var gcd=form.GroupCode.value;
		var ugn=form.GroupName.value;
		
		var cbVals="0";
		$("#"+form.id+" input:checkbox").each(function(){
			var n=this.name,v=($(this).attr('checked')!="checked")?0:1;
			cbVals+=","+n.substr(-2)+":"+v;
    });
		
		$.ajax({
			url:"lib/scripts/_process_priveleges.php",
			global:false,
			type:"POST",
			data:{uid:UserID,gid:GroupID,gcd:gcd,ugn:ugn,cbs:cbVals,uug:UpdUsrGrp,mode:mode,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#usr_priveleges_loading").show();},
			complete:function(){$("#usr_priveleges_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){showMessage(fields[2]);$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				else if(fields[0]=="0"){showMessage(fields[2]);showPriveleges(UserID,GroupID,0);}
				else if(fields[0]=="1"){showMessage(fields[2]);$('#d_message').dialog({close:function(event,ui){showPriveleges(UserID,GroupID,1);}});}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* SEARCH Personnel Group load to pgrp_srch_win_op_result */
function ajaxSearchPersGroup(ugn){
	$(function(){
		$.ajax({
			url:"lib/scripts/_pers_group_search.php",
			global:false,
			type:"GET",
			data:{mod:"srch",ugn:ugn,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#pgrp_srch_win_op_loading").show();},
			complete:function(){$("#pgrp_srch_win_op_loading").hide("highlight");},
			success:function(data){if(debugMode){alert(data);};$("#pgrp_srch_win_op_result").html(data);},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* SEARCH System User Group load to grp_srch_win_op_result */
function ajaxSearchUserGroup(ugn){
	$(function(){
		$.ajax({
			url:"lib/scripts/_user_group_search.php",
			global:false,
			type:"GET",
			data:{mod:"srch",ugn:ugn,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#grp_srch_win_op_loading").show();},
			complete:function(){$("#grp_srch_win_op_loading").hide("highlight");},
			success:function(data){if(debugMode){alert(data);};$("#grp_srch_win_op_result").html(data);},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function formUserGroup(uid,mode){
	$(function(){
		$("#global_loading_div").width($(window).width());
		$("#global_loading_div").height($(window).height());
		if(mode==-1){$('#d_form_input').dialog({title:'DELETE User Group'});}
		else if(mode==0){$('#d_form_input').dialog({title:'NEW User Group'});}
		else{$('#d_form_input').dialog({title:'UPDATE User Group'});}
		$.ajax({
			url:"lib/forms/user_group_information_form.php",
			global:false,type:"POST",
			data:{uid:uid,mode:mode,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#global_loading_div").show();},
			complete:function(){$("#global_loading_div").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){$("#d_form_input").html(fields[2]);$('#d_form_input').dialog('open');}
				else{
					showMessage(fields[2]);
					if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}


/* Process PLS */
function processPLSInfo(form){
	var url="lib/scripts/_process_personnel_locator_info.php";
	$(function(){
		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{
				mode:form.mode.value,
				EmpID:form.EmpID.value,
				PLFor:form.ListOfID.value,
				PLID:form.PLID.value,
				PLDestination:form.PLDestination.value,
				PLPurpose:form.PLPurpose.value,
				sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#page_emp_loading_1").show();},
			complete:function(){$("#page_emp_loading_1").hide("highlight");},
			success:function(data){if(debugMode){alert(data);};if(data=="1"){getEmpPage("ppls",form.EmpID.value,0);$('#d_form_input').dialog('close');}else{$("#emp_content_1").html(data);}},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function formPersonnelLocator(id,xid,mode){
	$(function(){
		if(mode==-1){$('#d_form_input').dialog({title:'DELETE Personnel Locator Slip'});}
		else if(mode==0){$('#d_form_input').dialog({title:'NEW Personnel Locator Slip'});}
		else if(mode==1){$('#d_form_input').dialog({title:'UPDATE Personnel Locator Slip'});}
		$.ajax({
			url:"lib/forms/personnel_locator_form.php",
			global:false,type:"POST",
			data:{id:id,xid:xid,mode:mode,sid:Math.random()},
			dataType:"html",async:false,
			beforeSend:function(){$("#page_emp_loading_1").show();},
			complete:function(){$("#page_emp_loading_1").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){$("#d_form_input").html(fields[2]);$('#d_form_input').dialog('open');}
				else{
					showMessage(fields[2]);
					if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
				}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* GET Employee/Office DTR */
function getDTR(form,g){
	if(g=="emp"){qParams={id:form.EmpID.value,yr:form.SelectYear.value,mo:form.SelectMonth.value,pr:form.SelectPayPeriod.value,sid:Math.random()};}
	if(g=="off"){qParams={oid:form.SubOffID.value,yr:form.SelectYear.value,mo:form.SelectMonth.value,pr:form.SelectPayPeriod.value,st:form.ApptStID.value,sid:Math.random()};}
	$(function(){
		$.ajax({
			url:"lib/scripts/_return_dtr.php",
			global:false,
			type:"GET",
			data:qParams,
			dataType:"html",
			async:false,
			beforeSend:function(){
				if(g=="emp"){$("#page_emp_loading_1").show();}
				if(g=="off"){$("#page_off_loading_1").show();}
			},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="1"){document.getElementById('DTR_box_'+g).innerHTML=fields[2];}
				else{/* Limited access user */
					showMessage(fields[2]);
					if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});/* Unauthorized/Expired session redirected to logout.php */}
				}
			},
			complete:function(){
				if(g=="emp"){$("#page_emp_loading_1").hide("highlight");}
				if(g=="off"){$("#page_off_loading_1").hide("highlight");}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* GET Employee/Office Payroll */
function ajaxGetPayroll(spo,id,yr,mo,pr,sof,aps,g){
	$(function(){
		$.ajax({
			url:"lib/scripts/_show_payroll.php",
			global:false,
			type:"GET",
			data:{spo:spo,id:id,yr:yr,mo:mo,pr:pr,sof:sof,aps:aps,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){
				if(g=="emp"){$("#page_emp_loading_1").show();}
				if(g=="off"){$("#page_off_loading_1").show();}
			},
			complete:function(){
				if(g=="emp"){$("#page_emp_loading_1").hide("highlight");}
				if(g=="off"){$("#page_off_loading_1").hide("highlight");}
			},
			success:function(data){ if(debugMode){alert(data);};
				document.getElementById('Payroll_box_'+g).innerHTML=data;
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

/* GET Premiums GSIS, HDMF, PHIC */
function ajaxShowPremiums(id,as,pr,yr,mo){ /* pr=GSIS || HDMF || PH */
	$(function(){
		$.ajax({
			url:"lib/scripts/_show_premiums.php",
			global:false,
			type:"GET",
			data:{id:id,as:as,yr:yr,mo:mo,pr:pr,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#page_emp_loading_1").show();},
			complete:function(){$("#page_emp_loading_1").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				document.getElementById('display_premiums').innerHTML=data;
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}
/* Check valid links */
function UrlExists(url){var http=new XMLHttpRequest();http.open('HEAD',url,false);http.send();return(http.status!=404)?true:false;}

function confirmPLStime(id,xid){

}

function printPLSinfo(){

}

function showSystemUsers(){$(function(){$('#win_sys_users').dialog('open');});}

function showReportManager(){
	url="lib/forms/report_manager_form.php";
	$(function(){
		$.ajax({url:url,global:false,type:"POST",data:{sid:Math.random()},dataType:"html",async:true,
		beforeSend:function(){},
		complete:function(){},
		success:function(data){if(debugMode){alert(data);}
			var fields=new Array();
			fields=data.split('|');
			if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});}
			else if(fields[0]=="0"){showMessage(fields[2]);}
			else if(fields[0]=="1"){$("#win_sys_rpt").html(fields[2]);$('#win_sys_rpt').dialog('open');}
			else{showMessage(data);}
		},
		error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);}
		});
	});
}

function showSystemPreferences(){$(function(){$('#win_sys_sysc').dialog('open');});}

/* Load Page to pinfo_main_* box emp_content_1 */
function getSysPrefDet(opt,mode){
	var url;
	$(function(){
		switch (opt){
			case("holi"):
				url="lib/pages/holidays.php";
				$("#win_sys_sysc").dialog({title: "System Preferences - Philippine Holidays"});
				break;
		}

		$.ajax({
			url:url,
			global:false,
			type:"POST",
			data:{opt:opt,mode:mode,sid:Math.random()},
			dataType:"html",
			async:false,
			beforeSend:function(){$("#sys_pref_loading").show();},
			complete:function(){$("#sys_pref_loading").hide("highlight");},
			success:function(data){ if(debugMode){alert(data);};
				var fields=new Array();
				fields=data.split('|');
				if(fields[0]=="-1"){$('#d_message').dialog({close:function(event,ui){window.location.href="logout.php";}});showMessage(fields[2]);}
				else if(fields[0]=="0"){showMessage(fields[2]);}
				else if(fields[0]=="1"){$("#sysc_details").html(fields[2]);}
				else{showMessage(data);}
			},
			error:function(xhr,ajaxOptions,thrownError){showMessage("ERROR "+xhr.status+":~"+thrownError);return false;}
		});
	});
}

function showLeaveManager(){$(function(){$('#win_sys_plm').dialog('open');});}
function showUserOptions(){$(function(){$('#win_user_set').dialog('open');});}

$("#d_confirm").dialog({buttons:{
	"YES":function(){processDocument("lv",".$records['LivAppID'].",2,1)},
	"NO":function(){processDocument(0,0,0,-1);},"Cancel":function(){processDocument(0,0,0,-1);}
}});
showConfirmation("Confirm this application?");





