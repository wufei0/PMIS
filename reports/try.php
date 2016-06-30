<?php

ob_start();
	session_start();
	$_SESSION['theme']='blue';
	$_SESSION['path'] = '..';
	
	require_once $_SESSION['path'].'/lib/classes/Conf.php';
	require_once $_SESSION['path'].'/lib/classes/MySQLClass.php';
	require_once $_SESSION['path'].'/lib/classes/fpdf/fpdf.php';

	
//Start PDF Builder
	$pdf=new FPDF();

	$Config = new Conf();
	$MySQLi = new MySQLClass($Config);
	
	

	$alpha = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	

   $pdf->AddPage();
   
	
		$pdf->SetFont('Arial','',15);
	$pdf->SetTextColor(0,0,200);
	
		$pdf->Cell(80);
	// Centered text in a framed 20*10 mm cell and line break
	$pdf->Cell(50,10,'Employee List',0,1,'C');
	$pdf->Ln();
	
	
	 $header = array('Last Name', 'First Name', 'Middle Name');
	 
	 foreach($header as $col)
	
    $pdf->Cell(63, 10, $col, 1, 0, 'C');
    $pdf->Ln();
	
	$pdf->SetFont('Arial','',10);
	$pdf->SetTextColor(0,0,200);

	for ($i=0; $i<=25; $i++) {
	
		
	 $result = $MySQLi -> sqlQuery("SELECT `EmpLName`  ,  `EmpFName`  , `EmpMName` FROM `tblemppersonalinfo` where EmpLName  like '".$alpha[$i]."%' ORDER BY `EmpLName`, `EmpFName`, `EmpMName` ASC;"); unset($sql);

  while ($row = mysql_fetch_array($result)) {
   
      	$pdf->Cell(63, 10, $row['EmpLName'], 1, 0, 'C');
		$pdf->Cell(63, 10, $row['EmpFName'], 1, 0, 'C');
		$pdf->Cell(63, 10, $row['EmpMName'], 1, 0, 'C');
		 $pdf->Ln();
		 }
  } 
 
  

  $pdf->Output();
  
  ob_end_flush();

?>
