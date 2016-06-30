<?php 
	ob_start();
	session_start();
	
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD009'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	

//Get GET Values
	$EmpID=isset($_GET['id'])?strtoupper(strip_tags(trim($_GET['id']))):'00000';
//Start PDF Builder
	$pdf=new FPDF();
		
//GET Employee Information from database
	$personalInfo=Array();
	if($EmpID!='00000'){
		$MySQLi=new MySQLClass();
		$sql="SELECT * FROM `tblemppersonalinfo` WHERE `EmpID`='".$EmpID."' LIMIT 1;";
		$result=$MySQLi->sqlQuery($sql);
		$personalInfo=mysqli_fetch_array($result, MYSQLI_BOTH);
		unset($sql); unset($result);
	//Fix Birth Date
		$EmpBirthDate=(($personalInfo['EmpBirthDay']!="")&&($personalInfo['EmpBirthMonth']!="")&&($personalInfo['EmpBirthYear']!="")) ? $personalInfo['EmpBirthMonth'].' / '.$personalInfo['EmpBirthDay'].' / '.$personalInfo['EmpBirthYear']:"";
		
	//Fix Address
		$ResAddSt=($personalInfo['EmpResAddSt']!="") ? $personalInfo['EmpResAddSt'].",":"";
		$ResAddBrgy=($personalInfo['EmpResAddBrgy']!="") ? $personalInfo['EmpResAddBrgy'].",":"";
		$ResAddMun=($personalInfo['EmpResAddMun']!="") ? $personalInfo['EmpResAddMun'].",":"";
		$ResAddProv=$personalInfo['EmpResAddProv'];
		$ResAdd=$ResAddSt.$ResAddBrgy.$ResAddMun.$ResAddProv;
		
		$PerAddSt=($personalInfo['EmpPerAddSt']!="") ? $personalInfo['EmpPerAddSt'].",":"";
		$PerAddBrgy=($personalInfo['EmpPerAddBrgy']!="") ? $personalInfo['EmpPerAddBrgy'].",":"";
		$PerAddMun=($personalInfo['EmpPerAddMun']!="") ? $personalInfo['EmpPerAddMun'].",":"";
		$PerAddProv=$personalInfo['EmpPerAddProv'];
		$PerAdd=$PerAddSt.$PerAddBrgy.$PerAddMun.$PerAddProv;

		$isAddPage=Array(0=>false,'EmpSpsBus'=>false,'DpntInfo'=>false,'EducBg'=>false,'CSE'=>false,'SRec'=>false,'VolOrg'=>false,'TrainInfo'=>false,'OtherInfo'=>false);
		$pdf->SetTextColor(0,0,0);
	/* PDS Page 1 -------------------------------------------------------- */
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_001.png',3,3,210,325,'PNG');

		$pdf->SetFont('Arial','',10);
		
	/* I. Personal Information (1-23) */
		$pdf->Text(38,44.3,$personalInfo['EmpLName']);
		$pdf->Text(38,50.5,$personalInfo['EmpFName']);
		$pdf->Text(38,56.7,$personalInfo['EmpMName']); $pdf->Text(188,56.7,$personalInfo['EmpExtName']);
		$pdf->Text(66,62.8,$EmpBirthDate);
		$pdf->Text(38,68.6,$personalInfo['EmpBirthPlace']);
		
		$posX=($personalInfo['EmpSex']=='MALE')?38.4:52.4; $pdf->Text($posX,73.5,'x');;
		//$personalInfo['EmpCivilStatus']='OTHERS';
		$posX=(($personalInfo['EmpCivilStatus']=='SINGLE')||($personalInfo['EmpCivilStatus']=='MARRIED')||($personalInfo['EmpCivilStatus']=='ANNULLED'))?38.2:56.2;
		$posY=(($personalInfo['EmpCivilStatus']=='SINGLE')||($personalInfo['EmpCivilStatus']=='WIDOWED'))?79.5: ((($personalInfo['EmpCivilStatus']=='MARRIED')||($personalInfo['EmpCivilStatus']=='SEPARATED'))?84.6:89.6);
			$pdf->Text($posX,$posY,'x');;
		if (($personalInfo['EmpCivilStatus']!='SINGLE')&&($personalInfo['EmpCivilStatus']!='WEDOWED')&&($personalInfo['EmpCivilStatus']!='MARRIED')&&($personalInfo['EmpCivilStatus']!='SEPARATED')&&($personalInfo['EmpCivilStatus']!='ANNULLED')){ $pdf->SetFont('Arial','',7); $pdf->Text(77.5,90.0,$personalInfo['EmpCivilStatus']); }
		
		$pdf->SetFont('Arial','',10);
		$pdf->Text(38,97.6,$personalInfo['EmpCitizenship']);
		$pdf->Text(38,103.5,$personalInfo['EmpHeight']);
		$pdf->Text(38,109.3,$personalInfo['EmpWeight']);
		$pdf->Text(38,115.0,$personalInfo['EmpBloodType']);
		$pdf->Text(38,120.6,$personalInfo['EmpGSIS']);
		$pdf->Text(38,126.5,$personalInfo['EmpHDMF']);
		$pdf->Text(38,132.5,$personalInfo['EmpPH']);
		$pdf->Text(38,138.0,$personalInfo['EmpSSS']);
		
		$pdf->Text(131,62.8,$ResAddSt.$ResAddBrgy);
		$pdf->Text(131,68.2,$ResAddMun);
		$pdf->Text(131,73.8,$ResAddProv);
		$pdf->Text(131,80.0,$personalInfo['EmpResZipCode']);
		$pdf->Text(131,86.0,$personalInfo['EmpResTel']);
		
		$pdf->Text(131,91.7,$PerAddSt.$PerAddBrgy);
		$pdf->Text(131,97.1,$PerAddMun);
		$pdf->Text(131,102.7,$PerAddProv);
		$pdf->Text(131,108.9,$personalInfo['EmpPerZipCode']);
		$pdf->Text(131,114.9,$personalInfo['EmpPerTel']);
		
		$pdf->Text(131,120.6,$personalInfo['EmpEMail']);
		$pdf->Text(131,126.5,$personalInfo['EmpMobile']);
		$pdf->Text(131,132.5,$personalInfo['EmpAgencyNo']);
		$pdf->Text(131,138.0,$personalInfo['EmpTIN']);
		
	// II. Family Background (24-27)
		$pdf->Text(38,149.5,$personalInfo['EmpSpsLName']);
		$pdf->Text(38,155.5,$personalInfo['EmpSpsFName']);
		$pdf->Text(38,161.3,$personalInfo['EmpSpsMName']);
		
		$pdf->Text(41,195.9,$personalInfo['EmpFatherLName']);
		$pdf->Text(41,201.7,$personalInfo['EmpFatherFName']);
		$pdf->Text(41,207.5,$personalInfo['EmpFatherMName']);
		$pdf->Text(41,219.2,$personalInfo['EmpMotherLName']);
		$pdf->Text(41,225.0,$personalInfo['EmpMotherFName']);
		$pdf->Text(41,231.1,$personalInfo['EmpMotherMName']);
		
		//Spouse Business
		$EmpSpsBusAddSt=($personalInfo['EmpSpsBusAddSt']!="") ? $personalInfo['EmpSpsBusAddSt'].", ":"";
		$EmpSpsBusAddBrgy=($personalInfo['EmpSpsBusAddBrgy']!="") ? $personalInfo['EmpSpsBusAddBrgy'].", ":"";
		$EmpSpsBusAddMun=($personalInfo['EmpSpsBusAddMun']!="") ? $personalInfo['EmpSpsBusAddMun'].", ":"";
		$EmpSpsBusAddProv=$personalInfo['EmpSpsBusAddProv'];
		
		$pdf->Text(38,166.8,$personalInfo['EmpSpsJob']);
		$pdf->Text(38,172.8,$personalInfo['EmpSpsBusDesc']);
		$pdf->Text(38,178.6,$EmpSpsBusAddSt.$EmpSpsBusAddBrgy.$EmpSpsBusAddMun.$EmpSpsBusAddProv);
		$pdf->Text(38,184.4,$personalInfo['EmpSpsBusTel']);

		
		/* Dependent Information */
		$posY=155.5;
		$sql="SELECT * FROM `tblempdependents` WHERE `EmpID`='".$EmpID."' ORDER BY `DpntBirthYear` ASC,`DpntBirthMonth` ASC;";
		$result=$MySQLi->sqlQuery($sql);
		if(($MySQLi->NumberOfRows($sql)) > 0){
			while($DpntInfo=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$DpntLName=($DpntInfo['DpntLName']!="") ? $DpntInfo['DpntLName'].", ":"";
				$DpntFName=($DpntInfo['DpntFName']!="") ? $DpntInfo['DpntFName']." ":"";
				$DpntMName=($DpntInfo['DpntMName']!="")?substr($DpntInfo['DpntMName'],0,1).".":"";
				$DpntName=$DpntLName.$DpntFName.$DpntMName;
				//Fix Birth Date
				$DpntBirthDate=(($DpntInfo['DpntBirthDay']!="")&&($DpntInfo['DpntBirthMonth']!="")&&($DpntInfo['DpntBirthYear']!="")) ? $DpntInfo['DpntBirthMonth'].' / '.$DpntInfo['DpntBirthDay'].' / '.$DpntInfo['DpntBirthYear']:"";
				$pdf->Text(116,$posY,$DpntName); $pdf->Text(178,$posY,$DpntBirthDate);
				$posY=$posY + 5.8;
			}
		} unset($sql); unset($result);
		
	/* III. Educational Background (28) */
		$EducLvlIDs=Array('ELEMENTARY','HIGH SCHOOL','VOCATIONAL/TRADE COURSE','COLLEGE','GRADUATE STUDIES');
		foreach ($EducLvlIDs as $level){
			$sql="SELECT * FROM `tblempeducbg` WHERE `EmpID`='".$EmpID."' AND `EducLvlID`='$level';";
			$result=$MySQLi->sqlQuery($sql);
			if($MySQLi->NumberOfRows($sql) > 1){
				$isAddPage[0]=true;
				$isAddPage['EducBg']=true;
			} unset($sql); unset($result);
		}
		if(!$isAddPage['EducBg']){
			$sql="SELECT * FROM `tblempeducbg` WHERE `EmpID`='".$EmpID."';";
			$result=$MySQLi->sqlQuery($sql);
			$pdf->SetFont('Helvetica','',7);
			while($educBg=mysqli_fetch_array($result, MYSQLI_BOTH)){
				/* Fix Birth Date */
				$EducIncAttDateFrom=(($educBg['EducIncAttDateFromDay']!="")&&($educBg['EducIncAttDateFromMonth']!="")&&($educBg['EducIncAttDateFromYear']!="")) ? $educBg['EducIncAttDateFromMonth'].'/'.$educBg['EducIncAttDateFromDay'].'/'.$educBg['EducIncAttDateFromYear']:"";
				$EducIncAttDateTo=(($educBg['EducIncAttDateToDay']!="")&&($educBg['EducIncAttDateToMonth']!="")&&($educBg['EducIncAttDateToYear']!="")) ? $educBg['EducIncAttDateToMonth'].'/'.$educBg['EducIncAttDateToDay'].'/'.$educBg['EducIncAttDateToYear']:"";
				
				if($educBg['EducLvlID']=='L01'){
					$posY=(strlen($educBg['EducSchoolName'])>=60)?253.7:((strlen($educBg['EducSchoolName'])>=35)?254.7:256.0);
						$pdf->SetXY(36.2,$posY); $pdf->MultiCell(53.5,2.5,$educBg['EducSchoolName'],0,'L');
					$posY=(strlen($educBg['EducCourse'])>=34)?253.7:((strlen($educBg['EducCourse'])>=17)?254.7:256.0);
						$pdf->SetXY(89.7,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducCourse'],0,'L');
					$pdf->SetXY(114.0,256.0); $pdf->MultiCell(15.4,2.5,$educBg['EducYrGrad'],0,'C');
					$pdf->SetXY(129.5,256.0); $pdf->MultiCell(22.0,2.5,$educBg['EducGradeLvlUnits'],0,'C');
					$pdf->SetXY(151.5,256.0); $pdf->MultiCell(17.0,2.5,$EducIncAttDateFrom,0,'C');
					$pdf->SetXY(168.5,256.0); $pdf->MultiCell(17.0,2.5,$EducIncAttDateTo,0,'C');
					$posY=(strlen($educBg['EducAwards'])>=34)?253.7:((strlen($educBg['EducAwards'])>=17)?254.7:256.0);
						$pdf->SetXY(185.5,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducAwards'],0,'L');
				}
				if($educBg['EducLvlID']=='L02'){
					$posY=(strlen($educBg['EducSchoolName'])>=60)?262.6:((strlen($educBg['EducSchoolName'])>=35)?263.6:264.9);
						$pdf->SetXY(36.2,$posY); $pdf->MultiCell(53.5,2.5,$educBg['EducSchoolName'],0,'L');
					$posY=(strlen($educBg['EducCourse'])>=34)?262.6:((strlen($educBg['EducCourse'])>=17)?263.6:264.9);
						$pdf->SetXY(89.7,$posY); $pdf->MultiCell(24.0,3,$educBg['EducCourse'],0,'L');
					$pdf->SetXY(114.0,264.9); $pdf->MultiCell(15.4,2.5,$educBg['EducYrGrad'],0,'C');
					$pdf->SetXY(129.5,264.9); $pdf->MultiCell(22.0,2.5,$educBg['EducGradeLvlUnits'],0,'C');
					$pdf->SetXY(151.5,264.9); $pdf->MultiCell(17.0,2.5,$EducIncAttDateFrom,0,'C');
					$pdf->SetXY(168.5,264.9); $pdf->MultiCell(17.0,2.5,$EducIncAttDateTo,0,'C');
					$posY=(strlen($educBg['EducAwards'])>=34)?262.6:((strlen($educBg['EducAwards'])>=17)?263.6:264.9);
						$pdf->SetXY(185.5,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducAwards'],0,'L');
				}
				if($educBg['EducLvlID']=='L07'){
					$posY=(strlen($educBg['EducSchoolName'])>=60)?271.6:((strlen($educBg['EducSchoolName'])>=35)?272.6:274.2);
						$pdf->SetXY(36.2,$posY); $pdf->MultiCell(53.5,2.5,$educBg['EducSchoolName'],0,'L');
					$posY=(strlen($educBg['EducCourse'])>=34)?271.6:((strlen($educBg['EducCourse'])>=17)?272.6:274.2);
						$pdf->SetXY(89.7,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducCourse'],0,'L');
					$pdf->SetXY(114.0,274.2); $pdf->MultiCell(15.4,2.5,$educBg['EducYrGrad'],0,'C');
					$pdf->SetXY(129.5,274.2); $pdf->MultiCell(22.0,2.5,$educBg['EducGradeLvlUnits'],0,'C');
					$pdf->SetXY(151.5,274.2); $pdf->MultiCell(17.0,2.5,$EducIncAttDateFrom,0,'C');
					$pdf->SetXY(168.5,274.2); $pdf->MultiCell(17.0,2.5,$EducIncAttDateTo,0,'C');
					$posY=(strlen($educBg['EducAwards'])>=34)?271.6:((strlen($educBg['EducAwards'])>=17)?272.6:274.2);
						$pdf->SetXY(185.5,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducAwards'],0,'L');
				}
				if($educBg['EducLvlID']=='L03'){
					$posY=(strlen($educBg['EducSchoolName'])>=60)?280.9:((strlen($educBg['EducSchoolName'])>=35)?281.9:283.2);
						$pdf->SetXY(36.2,$posY); $pdf->MultiCell(53.5,2.5,$educBg['EducSchoolName'],0,'L');
					$posY=(strlen($educBg['EducCourse'])>=34)?280.9:((strlen($educBg['EducCourse'])>=17)?281.9:283.2);
						$pdf->SetXY(89.7,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducCourse'],0,'L');
					$pdf->SetXY(114.0,283.2); $pdf->MultiCell(15.4,2.5,$educBg['EducYrGrad'],0,'C');
					$pdf->SetXY(129.5,283.2); $pdf->MultiCell(22.0,2.5,$educBg['EducGradeLvlUnits'],0,'C');
					$pdf->SetXY(151.5,283.2); $pdf->MultiCell(17.0,2.5,$EducIncAttDateFrom,0,'C');
					$pdf->SetXY(168.5,283.2); $pdf->MultiCell(17.0,2.5,$EducIncAttDateTo,0,'C');
					$posY=(strlen($educBg['EducAwards'])>=34)?280.9:((strlen($educBg['EducAwards'])>=17)?281.9:283.2);
						$pdf->SetXY(185.5,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducAwards'],0,'L');
				}
				if($educBg['EducLvlID']=='L06'){
					$posY=(strlen($educBg['EducSchoolName'])>=60)?299.3:((strlen($educBg['EducSchoolName'])>=35)?300.3:301.3);
						$pdf->SetXY(36.2,$posY); $pdf->MultiCell(53.5,2.5,$educBg['EducSchoolName'],0,'L');
					$posY=(strlen($educBg['EducCourse'])>=34)?299.3:((strlen($educBg['EducCourse'])>=17)?300.3:301.3);
						$pdf->SetXY(89.7,299.3); $pdf->MultiCell(24.0,2.5,$educBg['EducCourse'],0,'L');
					$pdf->SetXY(114.0,301.3); $pdf->MultiCell(15.4,2.5,$educBg['EducYrGrad'],0,'C');
					$pdf->SetXY(129.5,301.3); $pdf->MultiCell(22.0,2.5,$educBg['EducGradeLvlUnits'],0,'C');
					$pdf->SetXY(151.5,301.3); $pdf->MultiCell(17.0,2.5,$EducIncAttDateFrom,0,'C');
					$pdf->SetXY(168.5,301.3); $pdf->MultiCell(17.0,2.5,$EducIncAttDateTo,0,'C');
					$posY=(strlen($educBg['EducAwards'])>=34)?299.3:((strlen($educBg['EducAwards'])>=17)?300.3:301.3);
						$pdf->SetXY(185.5,$posY); $pdf->MultiCell(24.0,2.5,$educBg['EducAwards'],0,'L');
				}
			} $pdf->SetFont('Helvetica','',9);
		}

	/* PDS Page 2 -------------------------------------------------------- */
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_002.png',3,3,210,325,'PNG');
		
		/* CS & Licsenses */
		$sql="SELECT * FROM `tblempcse` WHERE `EmpID`='".$EmpID."' ORDER BY `CSELicReleaseYear` DESC,`CSELicReleaseMonth` DESC;";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql) > 7){
			$isAddPage[0]=true;
			$isAddPage['CSE']=true;
		}
		else{
			$pdf->SetFont('Helvetica','',7);
			$defY=30.7;
			while($empCSE=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$CSEExam=(($empCSE['CSEExamDay']!="")&&($empCSE['CSEExamMonth']!="")&&($empCSE['CSEExamYear']!="")) ? $empCSE['CSEExamMonth'].'/'.$empCSE['CSEExamDay'].'/'.$empCSE['CSEExamYear']:"";
				$CSELicRelease=(($empCSE['CSELicReleaseDay']!="")&&($empCSE['CSELicReleaseMonth']!="")&&($empCSE['CSELicReleaseYear']!="")) ? $empCSE['CSELicReleaseMonth'].'/'.$empCSE['CSELicReleaseDay'].'/'.$empCSE['CSELicReleaseYear']:"";
				
				$posY=(strlen($empCSE['CSEDesc'])>=85) ? $defY-2.2:((strlen($empCSE['CSEDesc'])>=40) ? $defY-1.3:$defY);
					$pdf->SetXY(5.2,$posY); $pdf->MultiCell(63.5,2.5,$empCSE['CSEDesc'],0,'L');
				$pdf->SetXY(68.5,$defY); $pdf->MultiCell(19.7,2.5,$empCSE['CSERating'],0,'C');
				$pdf->SetXY(88.2,$defY); $pdf->MultiCell(20.7,2.5,$CSEExam,0,'C');
				$posY=(strlen($empCSE['CSEExamPlace'])>=85) ? $defY-2.2:((strlen($empCSE['CSEExamPlace'])>=40) ? $defY-1.3:$defY);	
					$pdf->SetXY(108.9,$posY); $pdf->MultiCell(67.2,2.5,$empCSE['CSEExamPlace'],0,'L');
				$pdf->SetXY(176.1,$defY); $pdf->MultiCell(18.4,2.5,$empCSE['CSELicNum'],0,'C');
				$pdf->SetXY(194.5,$defY); $pdf->MultiCell(15.7,2.5,$CSELicRelease,0,'C');
				$defY=$defY + 9.65;
			}
		} unset($sql); unset($result);
		
		/* Work Experiences */
		$sql="SELECT * FROM `tblempservicerecords` WHERE `EmpID`='".$EmpID."' ORDER BY `SRecFromYear` DESC, `SRecFromMonth` DESC;";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql)>20){
			$isAddPage[0]=true;
			$isAddPage['SRec']=true;
		}
		else{
			$pdf->SetFont('Helvetica','',7);
			$defY=126.0;
			while($SrvRec=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$SrvRecFrom=(($SrvRec['SRecFromDay']!="")&&($SrvRec['SRecFromMonth']!="")&&($SrvRec['SRecFromYear']!="")) ? $SrvRec['SRecFromMonth'].'/'.$SrvRec['SRecFromDay'].'/'.$SrvRec['SRecFromYear']:"";
				$SrvRecTo=(($SrvRec['SRecToDay']!="")&&($SrvRec['SRecToMonth']!="")&&($SrvRec['SRecToYear']!="")) ? $SrvRec['SRecToMonth'].'/'.$SrvRec['SRecToDay'].'/'.$SrvRec['SRecToYear']:"";
				
				$pdf->SetXY(5.2,$defY); $pdf->MultiCell(18.8,2.5,$SrvRecFrom,0,'C');
				$pdf->SetXY(24.0,$defY); $pdf->MultiCell(19.1,2.5,$SrvRecTo,0,'C');
				$SRecSalGradeStep=intval($SrvRec['SRecSalGradeStep']);
				$SRecSalary=$SrvRec['SRecSalary'];
				if($SrvRec['SRecIsGov']=="YES"){
					$positions=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `PosDesc`, `PosSalGrade` FROM `tblpositions` WHERE `PosID`='".$SrvRec['PosID']."';"), MYSQLI_BOTH);
					$PosSalGrade=$positions['PosSalGrade']>9?$positions['PosSalGrade']:"0".$positions['PosSalGrade'];
					if($SrvRec['PosID']!="PO000"){
						$PosTitleJD=$positions['PosDesc'];
						$salary=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SalGrdValue` FROM `tblsalgrade` WHERE `SalGrdID`='".$SrvRec['SRecFromYear'].$PosSalGrade."0".$SRecSalGradeStep."';"), MYSQLI_BOTH);
						$SRecSalary=$salary['SalGrdValue'];
					}
					else{$PosTitleJD=$SrvRec['SRecPosition'];}
				} 
				$posY=(strlen($PosTitleJD)>=60) ? $defY-2.2:((strlen($PosTitleJD)>=30) ? $defY-1.3:$defY);
					$pdf->SetXY(43.1,$posY); $pdf->MultiCell(45.0,2.5,$PosTitleJD,0,'L');
				$posY=(strlen($SrvRec['SRecEmployer'])>=70) ? $defY-2.2:((strlen($SrvRec['SRecEmployer'])>=35) ? $defY-1.3:$defY);
					$pdf->SetXY(88.1,$posY); $pdf->MultiCell(55.6,2.5,$SrvRec['SRecEmployer'],0,'L');
				$UnitCode=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `SalUnitCode` FROM `tblsalaryunits` WHERE `SalUnitID`='".$SrvRec['SalUnitID']."';"), MYSQLI_BOTH);
				$SalUnitCode=($UnitCode['SalUnitCode']!="")?" (".$UnitCode['SalUnitCode'].")":"";
				$pdf->SetXY(143.7,$defY); $pdf->MultiCell(16.5,2.5,number_format((float)$SRecSalary,2,'.',',').$SalUnitCode,0,'R');
				if((intval($PosSalGrade)<=30)&&(intval($PosSalGrade)>=1)){$pdf->SetXY(160.2,$defY); $pdf->MultiCell(16.0,2.5,$PosSalGrade."-".$SRecSalGradeStep,0,'C');}
				$appstatuses=mysqli_fetch_array($MySQLi->sqlQuery("SELECT `ApptStDesc` FROM `tblapptstatus` WHERE `ApptStID`='".$SrvRec['ApptStID']."';"), MYSQLI_BOTH);
				$posY=(strlen($appstatuses['ApptStDesc'])>=20) ? $defY-2.2:((strlen($appstatuses['ApptStDesc'])>=10) ? $defY-1.0:$defY);
					$pdf->SetXY(176.2,$posY); $pdf->MultiCell(18.4,2.5,$appstatuses['ApptStDesc'],0,'C');
				$pdf->SetXY(194.6,$defY); $pdf->MultiCell(16.0,2.5,$SrvRec['SRecIsGov'],0,'C');
				
				$defY=$defY+9.65;
			}
		}unset($sql);unset($result);

	/* PDS Page 3 -------------------------------------------------------- */
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_003.png',3,3,210,325,'PNG');
		
		/* Voluntary Work or Involvement tin Civic/Non-Government/People/Voluntary Organizations */
		$sql="SELECT * FROM `tblempvoluntaryorg` WHERE `EmpID`='".$EmpID."' ORDER BY `VolOrgFromYear` DESC,`VolOrgFromMonth` DESC;";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql) > 5){
			$isAddPage[0]=true;
			$isAddPage['VolOrg']=true;
		}
		else{
			$pdf->SetFont('Helvetica','',7);
			$defY=31.5;
			while($volOrg=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$VolOrgAddSt=($volOrg['VolOrgAddSt']!="") ? $volOrg['VolOrgAddSt'].",":"";
				$VolOrgAddBrgy=($volOrg['VolOrgAddBrgy']!="") ? $volOrg['VolOrgAddBrgy'].",":"";
				$VolOrgAddMun=($volOrg['VolOrgAddMun']!="") ? $volOrg['VolOrgAddMun'].",":"";
				$VolOrgAddProv=$volOrg['VolOrgAddProv'];
				$VolOrgAdd=$VolOrgAddSt.$VolOrgAddBrgy.$VolOrgAddMun.$VolOrgAddProv;
				$VolOrgFrom=(($volOrg['VolOrgFromDay']!="")&&($volOrg['VolOrgFromMonth']!="")&&($volOrg['VolOrgFromYear']!="")) ? $volOrg['VolOrgFromMonth'].'/'.$volOrg['VolOrgFromDay'].'/'.$volOrg['VolOrgFromYear']:"";
				$VolOrgTo=(($volOrg['VolOrgToDay']!="")&&($volOrg['VolOrgToMonth']!="")&&($volOrg['VolOrgToYear']!="")) ? $volOrg['VolOrgToMonth'].'/'.$volOrg['VolOrgToDay'].'/'.$volOrg['VolOrgToYear']:"";
				
				if(!(strlen($volOrg['VolOrgName'])>=50)&&!(strlen($VolOrgAdd)>=50)){
					$posY=$defY-1.2;
					$pdf->SetXY(5.7,$posY); $pdf->MultiCell(89.5,2.5,$volOrg['VolOrgName'],0,'L');
					$pdf->SetXY(5.7,$posY+2.5); $pdf->MultiCell(89.5,2.5,$VolOrgAdd,0,'L');
				}
				
				if(!(strlen($volOrg['VolOrgName'])>=50)&&(strlen($VolOrgAdd)>=50)){
					$posY=$defY-2.3;
					$pdf->SetXY(5.7,$posY); $pdf->MultiCell(89.5,2.5,$volOrg['VolOrgName'],0,'L');
					$pdf->SetXY(5.7,$posY+2.5); $pdf->MultiCell(89.5,2.5,$VolOrgAdd,0,'L');
				}
				
				if((strlen($volOrg['VolOrgName'])>=50)&&!(strlen($VolOrgAdd)>=50)){
					$posY=$defY-2.3;
					$pdf->SetXY(5.7,$posY); $pdf->MultiCell(89.5,2.5,$volOrg['VolOrgName'],0,'L');
					$pdf->SetXY(5.7,$posY+5); $pdf->MultiCell(89.5,2.5,$VolOrgAdd,0,'L');
				}
				
				if((strlen($volOrg['VolOrgName'])>=50)&&(strlen($VolOrgAdd)>=50)){
					$posY=$defY-3.0;
					$pdf->SetFont('Helvetica','',6.5);
					$pdf->SetXY(5.7,$posY); $pdf->MultiCell(89.5,2.2,$volOrg['VolOrgName'],0,'L');
					$pdf->SetXY(5.7,$posY+4.4); $pdf->MultiCell(89.5,2.2,$VolOrgAdd,0,'L');
					$pdf->SetFont('Helvetica','',7);
				}
				
				$pdf->SetXY(95.20,$defY); $pdf->MultiCell(23.3,2.5,$VolOrgFrom,0,'C');
				$pdf->SetXY(118.50,$defY); $pdf->MultiCell(22.2,2.5,$VolOrgTo,0,'C');
				$pdf->SetXY(140.70,$defY); $pdf->MultiCell(17.8,2.5,$volOrg['VolOrgHours'],0,'C');
				$posY=(strlen($volOrg['VolOrgDetails'])>=60) ? $defY-2.3:((strlen($volOrg['VolOrgDetails'])>=30) ? $defY-1.2:$defY);
					$pdf->SetXY(158.50,$posY); $pdf->MultiCell(51.3,2.5,$volOrg['VolOrgDetails'],0,'L');
				$defY=$defY + 9.65;
			}
		} unset($sql); unset($result);
		
		/* Training Programs */
		$sql="SELECT * FROM `tblemptrainings` WHERE `EmpID`='".$EmpID."' ORDER BY `TrainFromYear` DESC,`TrainFromMonth` DESC;";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql) > 5){
			$isAddPage[0]=true;
			$isAddPage['Train']=true;
		}
		else{
			$pdf->SetFont('Helvetica','',7);
			$defY=106.0;
			while($train=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$TrainFrom=(($train['TrainFromDay']!="")&&($train['TrainFromMonth']!="")&&($train['TrainFromYear']!="")) ? $train['TrainFromMonth'].'/'.$train['TrainFromDay'].'/'.$train['TrainFromYear']:"";
				$TrainTo=(($train['TrainToDay']!="")&&($train['TrainToMonth']!="")&&($train['TrainToYear']!="")) ? $train['TrainToMonth'].'/'.$train['TrainToDay'].'/'.$train['TrainToYear']:"";
				
				$posY=(strlen($train['TrainDesc'])>=120) ? $defY-2.3:((strlen($train['TrainDesc'])>=60) ? $defY-1.2:$defY);
					$pdf->SetXY(5.7,$posY); $pdf->MultiCell(89.5,2.5,$train['TrainDesc'],0,'L');
				$pdf->SetXY(95.20,$defY); $pdf->MultiCell(23.3,2.5,$TrainFrom,0,'C');
				$pdf->SetXY(118.50,$defY); $pdf->MultiCell(22.2,2.5,$TrainTo,0,'C');
				$pdf->SetXY(140.70,$defY); $pdf->MultiCell(17.8,2.5,$train['TrainHours'],0,'C');
				$posY=(strlen($train['TrainSponsor'])>=60) ? $defY-2.3:((strlen($train['TrainSponsor'])>=30) ? $defY-1.2:$defY);
					$pdf->SetXY(158.50,$posY); $pdf->MultiCell(51.3,2.5,$train['TrainSponsor'],0,'L');
				$defY=$defY + 9.65;
			}
		} unset($sql); unset($result);
		
		/* Special Skills -------------------------------------------------------- */
		$sql="SELECT * FROM `tblempskills` WHERE `EmpID`='".$EmpID."' ORDER BY `SkillDesc` ASC;";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql) > 5){
			$isAddPage[0]=true;
			$isAddPage['OtherInfo']=true;
		}
		else{
			$pdf->SetFont('Helvetica','',7);
			$defY=270.5;
			while($skill=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$posY=(strlen($skill['SkillDesc'])>=70) ? $defY-2.3:((strlen($skill['SkillDesc'])>=35) ? $defY-1.2:$defY);
					$pdf->SetXY(5.7,$posY); $pdf->MultiCell(56.5,2.5,$skill['SkillDesc'],0,'L');
				$defY=$defY + 9.65;
			}
		} unset($sql); unset($result);
		
		/* Non-academic Distinction/Recognition */
		$sql="SELECT * FROM `tblempnonacadrecognitions` WHERE `EmpID`='".$EmpID."' ORDER BY `NonAcadRecDetails` ASC;";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql) > 5){
			$isAddPage[0]=true;
			$isAddPage['OtherInfo']=true;
		}
		else{
			$pdf->SetFont('Helvetica','',7);
			$defY=270.5;
			while($naRec=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$posY=(strlen($naRec['NonAcadRecDetails'])>=90) ? $defY-2.3:((strlen($naRec['NonAcadRecDetails'])>=45) ? $defY-1.2:$defY);
					$pdf->SetXY(62.20,$posY); $pdf->MultiCell(74.0,2.5,$naRec['NonAcadRecDetails'],0,'L');
				$defY=$defY + 9.65;
			}
		} unset($sql); unset($result);
		
		/* Membership in association/Organization */
		$sql="SELECT * FROM `tblempassorgmembership` WHERE `EmpID`='".$EmpID."' ORDER BY `MemAssOrgDesc` ASC;";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql) > 5){
			$isAddPage[0]=true;
			$isAddPage['OtherInfo']=true;
		}
		else{
			$pdf->SetFont('Helvetica','',7);
			$defY=270.5;
			while($mao=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$posY=(strlen($mao['MemAssOrgDesc'])>=90) ? $defY-2.3:((strlen($mao['MemAssOrgDesc'])>=45) ? $defY-1.2:$defY);
					$pdf->SetXY(136.2,$posY); $pdf->MultiCell(73.8,2.5,$mao['MemAssOrgDesc'],0,'L');
				$defY=$defY + 9.65;
			}
		} unset($sql); unset($result);

	/* PDS Page 4 -------------------------------------------------------- */
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_004.png',3,3,210,325,'PNG');
		
		$tblempanswers=Array();
		$sql="SELECT * FROM `tblempanswers` WHERE `EmpID`='".$EmpID."';";
		$result=$MySQLi->sqlQuery($sql);
		if($MySQLi->NumberOfRows($sql)){
			while($a_records=mysqli_fetch_array($result, MYSQLI_BOTH)){
				$QuesID=$a_records['QuesID'];
				$tblempanswers[$QuesID]['AnsID']=$a_records['AnsID'];
				$tblempanswers[$QuesID]['AnsIsYes']=$a_records['AnsIsYes'];
				$tblempanswers[$QuesID]['AnsDetails']=$a_records['AnsDetails'];
			} unset($sql); unset($result);
		
			$pdf->SetFont('Helvetica','B',12);
			if($tblempanswers['Q361']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(144.1,19.7,'x');
				$pdf->SetXY(142.3,24.6); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(64.8,4.4,$tblempanswers['Q361']['AnsDetails'],0,'L');
			} 
			else{ $pdf->Text(156.6,19.7,'x');; }
		
			if($tblempanswers['Q362']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(143.8,45.9,'x');;
				$pdf->SetXY(142.3,51.4); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(64.8,4.4,$tblempanswers['Q362']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.3,45.9,'x');; } 
		
			if($tblempanswers['Q371']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(143.8,70.0,'x');;
				$pdf->SetXY(142.3,75.6); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(64.8,4.4,$tblempanswers['Q371']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.3,70.0,'x');; } 
			
			if($tblempanswers['Q372']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(143.5,89.6,'x');;
				$pdf->SetXY(142.3,95.1); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(64.8,4.4,$tblempanswers['Q372']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.0,89.6,'x');; } 
			
			if($tblempanswers['Q380']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(144.3,109.3,'x');;
				$pdf->SetXY(142.3,114.3); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(64.8,4.4,$tblempanswers['Q380']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.8,109.3,'x');; } 
			
			if($tblempanswers['Q390']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(144.1,128.4,'x');;
				$pdf->SetXY(142.3,137.6); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(64.8,4.4,$tblempanswers['Q390']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.6,128.4,'x');; } 
			
			if($tblempanswers['Q400']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(144.3,154.7,'x');;
				$pdf->SetXY(142.3,160.7); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(64.8,4.4,$tblempanswers['Q400']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.8,154.7,'x');; } 
		
			if($tblempanswers['Q411']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(143.5,187.7,'x');;
				$defY=190; $posY=(strlen($tblempanswers['Q411']['AnsDetails'])>=36) ? $defY-6.4:((strlen($tblempanswers['Q411']['AnsDetails'])>=18) ? $defY-3.2:$defY);
				$pdf->SetXY(172.3,$posY); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(35.8,3.2,$tblempanswers['Q411']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.0,187.7,'x');; } 
			
			if($tblempanswers['Q412']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(143.5,198.6,'x');;
				$defY=200; $posY=(strlen($tblempanswers['Q412']['AnsDetails'])>=36) ? $defY-6.4:((strlen($tblempanswers['Q412']['AnsDetails'])>=18) ? $defY-3.2:$defY);
				$pdf->SetXY(172.3,$posY); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(35.8,3.2,$tblempanswers['Q412']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(156.0,198.6,'x');; } 
			
			if($tblempanswers['Q413']['AnsIsYes']){
				$pdf->SetFont('Helvetica','B',12); $pdf->Text(143.2,207.7,'x');;
				$defY=209.6; $posY=(strlen($tblempanswers['Q413']['AnsDetails'])>=36) ? $defY-6.4:((strlen($tblempanswers['Q413']['AnsDetails'])>=18) ? $defY-3.2:$defY);
				$pdf->SetXY(172.3,$posY); $pdf->SetFont('Helvetica','B',9); $pdf->MultiCell(35.8,3.2,$tblempanswers['Q413']['AnsDetails'],0,'L');
			} 
			else{ $pdf->SetFont('Helvetica','B',12); $pdf->Text(155.7,207.7,'x');; } 
		}
		
		/* Character References */
		$sql="SELECT * FROM `tblempreferences` WHERE `EmpID`='".$EmpID."' ORDER BY `RefLName` ASC,`RefFName` ASC LIMIT 3;";
		$result=$MySQLi->sqlQuery($sql);

		$pdf->SetFont('Helvetica','',8);
		$defY=230.2;
		while($cref=mysqli_fetch_array($result, MYSQLI_BOTH)){
			$RefLName=($cref['RefLName']!="") ? $cref['RefLName'].", ":"";
			$RefFName=($cref['RefFName']!="") ? $cref['RefFName']." ":"";
			$RefMName=($cref['RefMName']!="")?substr($cref['RefMName'],0,1).". ":"";
			$RefExtName=($cref['RefExtName']!="") ? $cref['RefExtName'].".":"";
			$RefName=$RefLName.$RefFName.$RefMName.$RefExtName;
			
			$RefAddSt=($cref['RefAddSt']!="") ? $cref['RefAddSt'].", ":"";
			$RefAddBrgy=($cref['RefAddBrgy']!="") ? $cref['RefAddBrgy'].", ":"";
			$RefAddMun=($cref['RefAddMun']!="") ? $cref['RefAddMun'].", ":"";
			$RefAddProv=$cref['RefAddProv'];
			$RefAdd=$RefAddSt.$RefAddBrgy.$RefAddMun.$RefAddProv;
			
			$posY=(strlen($RefName)>=30) ? $defY-1.5:$defY;
				$pdf->SetXY(5.5,$posY); $pdf->MultiCell(53.0,2.5,$RefName,0,'L');
			$posY=(strlen($RefAdd)>=42) ? $defY-1.5:$defY;
				$pdf->SetXY(58.5,$posY); $pdf->MultiCell(74.4,2.5,$RefAdd,0,'L');
			$posY=(strlen($cref['RefTel'])>=20) ? $defY-1.5:$defY;
				$pdf->SetXY(132.9,$posY); $pdf->MultiCell(31.6,2.5,$cref['RefTel'],0,'C');
				
			$defY=$defY + 6.95;
		} unset($sql); unset($result);
		
		$sql="SELECT * FROM `tblempctc` WHERE `EmpID`='".$EmpID."' ORDER BY `CTCDateYear` DESC, `CTCDateMonth` DESC LIMIT 1;";
		if($result=$MySQLi->sqlQuery($sql)){
			$ctc=mysqli_fetch_array($result, MYSQLI_BOTH);
			$pdf->SetFont('Helvetica','',10);
			$pdf->SetXY(11.4,277.8); $pdf->MultiCell(67.0,3.5,$ctc['CTCID'],0,'C');
			$pdf->SetXY(11.4,290.9); $pdf->MultiCell(67.0,3.5,$ctc['CTCPlace'],0,'C');
			$CTCDate=(($ctc['CTCDateDay']!="")&&($ctc['CTCDateMonth']!="")&&($ctc['CTCDateYear']!="")) ? $ctc['CTCDateMonth'].' / '.$ctc['CTCDateDay'].' / '.$ctc['CTCDateYear']:"";
			$pdf->SetXY(11.4,303.0); $pdf->MultiCell(67.0,3.5,$CTCDate,0,'C');
		}
		$pdf->SetXY(81.4,303.0); $pdf->MultiCell(82.4,3.5,strtoupper(date('F d,Y',time())),0,'C');
	}
	else{ 
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_001.png',3,3,210,325,'PNG');
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_002.png',3,3,210,325,'PNG');
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_003.png',3,3,210,325,'PNG');
		$pdf->AddPage('P','long');
		$pdf->Image('png/PDS_csform_004.png',3,3,210,325,'PNG');
	}
	
	$pdf->Output();

ob_end_flush();
?>