<?php 
	ob_start();
	session_start();
	
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD019'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	
	
	$TOID=isset($_GET['id'])?trim(strip_tags($_GET['id'])):'00000';
	
	class PDF extends FPDF{
	// Page header
		function Header(){
			$this->Image('png/TO_form_001.png',0,0,215.9,279.4,'PNG');
		}

	// Page footer
		function Footer(){
			// Position at 1.8 cm from bottom
			$this->SetY(-18);
			$this->SetFont('Times','BI',10);$this->SetTextColor(0,0,0);
			$this->MultiCell(0,3.5,'"Help Make Tomorrow Better Than Today"',0,'C',false);
			$this->SetFont('Helvetica','',8.5);
			$this->MultiCell(0,3,'2nd Floor, Provincial Capitol, City of San Fernando 2500, La Union',0,'C',false);
			$this->MultiCell(0,3,'Tel. Nos. (072) 242-55-50 loc 251, 219; Telefax (072) 888-31-71; Email: gtilan@launion.gov.ph',0,'C',false);
		}
	}


	$pdf = new PDF();
	$pdf->SetMargins(25.4,25.4,25.4);
	$pdf->AddPage('P','letter');
	
	$MySQLi=new MySQLClass();
	$TO=$MySQLi->GetArray("SELECT `TOID`, DATE_FORMAT(`TOIncDateFrom`,'%b %d, %Y') AS TODateFr, `TOIncDayTimeFrom`, DATE_FORMAT(`TOIncDateTo`,'%b %d, %Y') AS TODateTo, `TOIncDayTimeTo`, `TODays`, `TOSubject`, `TODestination`, `TOBody`, `TOStatus`, DATE_FORMAT(`TOPreparedTime`,'%b %d, %Y') AS TOPreparedDate FROM `tbltravelorders` WHERE `TOID` = '".$TOID."';");
	
	$TOs=Array();
	$result=$MySQLi->sqlQuery("SELECT CONCAT_WS(', ',`tblemppersonalinfo`.`EmpLName`, CONCAT_WS(' ',`tblemppersonalinfo`.`EmpFName`, CONCAT_WS('.', SUBSTRING(`tblemppersonalinfo`.`EmpMName`, 1, 1), ''))) AS EmpName, `tblpositions`.`PosDesc` FROM (`tblemppersonalinfo` JOIN `tblempservicerecords` ON `tblemppersonalinfo`.`EmpID` = `tblempservicerecords`.`EmpID`) JOIN `tblpositions` ON `tblempservicerecords`.`PosID` = `tblpositions`.`PosID` WHERE `tblempservicerecords`.`SRecCurrentAppointment`='1' AND `tblemppersonalinfo`.`EmpID` IN (SELECT `EmpID` FROM `tblemptravelorders` WHERE `TOID` = '".$TOID."');");
	$i=0;while($Emp=mysqli_fetch_array($result, MYSQLI_BOTH)){$TOs[$i]=Array("EmpName"=>$Emp['EmpName'],"PosDesc"=>$Emp['PosDesc']);$i+=1;}
	
	$SubOffName="";
	$TOPreparedDate="";
	
	$TOSubject="";
	$TODetails="You are hereby directed to proceed to the following Municipality: ".$TO['TODestination']." on ".$TO['TODateFr']." ".$TO['TOIncDayTimeFrom']." to ".$TO['TODateTo']." ".$TO['TOIncDayTimeFrom'].".".$TO['TOBody']."\n\nPer approved Itinerary of Travel, your travel expenses for ".(($TO['TODays']>1)?$TO['TODays']." days":$TO['TODays']." day")." relative thereto are hereby authorized subject to availability of funds and the usual accounting and auditing rules and regulations chargeable against the funds of the Provincial Governor.\n\nThe usual Certificate of Travel Completed and Travel Accomplishment Report shall be submitted to the Office within one (1) day after completion of travel.";
	$TOBOdy="";
	
	$initY=50;
	$pdf->SetFont('Arial','B',14);
	$pdf->SetXY(47,33);$pdf->MultiCell(120,5,$SubOffName,0,'C',false);
	
	$pdf->SetY($initY+30);
	
	$pdf->SetFont('Arial','',11);$pdf->Cell(135.1,4,'TRAVEL ORDER NO.:',0,0,'R',false);$pdf->SetFont('Arial','B',11);$pdf->Cell(30,4,$TOID,0,1,'L',false);
	$pdf->SetFont('Arial','',11);$pdf->Cell(135.1,4,'DATE:',0,0,'R',false);$pdf->SetFont('Arial','B',11);$pdf->Cell(30,4,$TO['TOPreparedDate'],0,1,'L',false);
	
	$pdf->Cell(165.1,13.5,'',0,1,'L',false);/* spacer */
	$pdf->SetFont('Arial','',11);$pdf->Cell(20,4.5,'TO:',0,0,'L',false);$pdf->SetFont('Arial','B',11);$pdf->Cell(145.1,4.5,$TOs[0]['EmpName'],0,1,'L',false);
	$pdf->SetFont('Arial','',10);$pdf->Cell(20,3,'',0,0,'R',false);$pdf->SetFont('Arial','I',10);$pdf->Cell(145.1,3,$TOs[0]['PosDesc'],0,1,'L',false);
	if(count($TOs)>1){
		for($i=1;$i<count($TOs);$i+=1){
			$pdf->Cell(165.1,2,'',0,1,'L',false);/* spacer */
			$pdf->SetFont('Arial','',11);$pdf->Cell(20,4.5,'',0,0,'R',false);$pdf->SetFont('Arial','B',11);$pdf->Cell(145.1,4.5,$TOs[$i]['EmpName'],0,1,'L',false);
			$pdf->SetFont('Arial','',10);$pdf->Cell(20,3,'',0,0,'R',false);$pdf->SetFont('Arial','I',10);$pdf->Cell(145.1,3,$TOs[$i]['PosDesc'],0,1,'L',false);
		}
	}
	$pdf->Cell(165.1,9,'',0,1,'L',false);/* spacer */
	$pdf->SetFont('Arial','',11);$pdf->Cell(20,4.5,'SUBJECT:',0,0,'L',false);$pdf->SetFont('Arial','B',11);$pdf->Cell(145.1,4.5,$TO['TOSubject'],0,1,'L',false);
	$pdf->Cell(165.1,9,'',0,1,'L',false);/* spacer */
	$pdf->SetFont('Arial','',11);$pdf->MultiCell(0,4.5,iconv('UTF-8', 'windows-1252', $TODetails),0,'J',false);
	$pdf->Cell(165.1,13.5,'',0,1,'L',false);/* spacer */
	$pdf->SetFont('Arial','B',11);$pdf->Cell(145.1,4.5,'GEOFREY TILAN',0,1,'L',false);
	$pdf->SetFont('Arial','I',10);$pdf->Cell(145.1,3,'PROVINCIAL ADMINISTRATOR',0,1,'L',false);
	$pdf->Output();
	
	ob_end_flush();
?>