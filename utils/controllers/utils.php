<?php

require_once '../db.php';

$_POST = json_decode(file_get_contents('php://input'), true);

switch ($_GET['r']) {

case "fixLeavesStart":
	
	$con = new pdo_db();
	$sql = "SELECT tblemppersonalinfo.EmpID FROM tblemppersonalinfo WHERE EmpID BETWEEN $_POST[start] AND $_POST[end] AND ((SELECT COUNT(*) FROM tblempleavecredits WHERE tblempleavecredits.EmpID = tblemppersonalinfo.EmpID) > 0) ORDER BY tblemppersonalinfo.EmpID ASC";
	$ids = $con->getData($sql);
	
	echo json_encode($ids);
	
break;

case "fixLeavesProcess":

	$con = new pdo_db("tblempleavecredits");

	$sql = "SELECT LivCredID, EmpID, LivCredDateFrom, LivCredDateTo, LivCredAddTo, LivCredDeductTo, LivCredBalance, LivCredReference, LivCredRemarks FROM tblempleavecredits WHERE EmpID = '$_POST[id]' AND LeaveTypeID = 'LT01' ORDER BY LivCredDateTo ASC";
	$VL = $con->getData($sql);

	foreach ($VL as $key => $value) {
		
		if ($key == 0) continue; // skip beginning balance
		
		// compute row balance
		$LivCredAddTo = 0;
		$LivCredDeductTo = 0;
		$LivCredBalance = 0;
		for ($i=$key; $i>=0; $i--) {
			$LivCredAddTo += $VL[$i]['LivCredAddTo'];
			$LivCredDeductTo += $VL[$i]['LivCredDeductTo'];
		}
		$LivCredBalance = $LivCredAddTo - $LivCredDeductTo;
		$update = $con->updateData(array("LivCredID"=>$VL[$key]['LivCredID'],"LivCredBalance"=>$LivCredBalance),'LivCredID');

	}

	$sql = "SELECT LivCredID, EmpID, LivCredDateFrom, LivCredDateTo, LivCredAddTo, LivCredDeductTo, LivCredBalance, LivCredReference, LivCredRemarks FROM tblempleavecredits WHERE EmpID = '$_POST[id]' AND LeaveTypeID = 'LT02' ORDER BY LivCredDateTo ASC";
	$SL = $con->getData($sql);

	foreach ($SL as $key => $value) {
		
		if ($key == 0) continue; // skip beginning balance
		
		// compute row balance
		$LivCredAddTo = 0;
		$LivCredDeductTo = 0;
		$LivCredBalance = 0;		
		for ($i=$key; $i>=0; $i--) {
			$LivCredAddTo += $SL[$i]['LivCredAddTo'];
			$LivCredDeductTo += $SL[$i]['LivCredDeductTo'];
		}
		$LivCredBalance = $LivCredAddTo - $LivCredDeductTo;
		$update = $con->updateData(array("LivCredID"=>$SL[$key]['LivCredID'],"LivCredBalance"=>$LivCredBalance),'LivCredID');

	}

	$response = array("status"=>1,"content"=>"DONE\n");

	echo json_encode($response);

break;
	
}

?>