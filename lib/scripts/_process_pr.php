 <?php
	ob_start();
	session_start();
	
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD015'));
	for($i=0;$i<=7;$i++){$Authorization[$i]=$Authorization[$i]==1?true:false;}
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:<br/>Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	

	
	$MySQLi=new MySQLClass();
	
	//Get POST Values
	$mode=isset($_POST['mode'])?strip_tags(trim($_POST['mode'])):'';
	$EmpID=isset($_POST['EmpID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['EmpID'])))):'';
	$RatingID=isset($_POST['RatingID'])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST['RatingID'])))):'';
	
	$fields = array("FirstSemesterScore"=>0,"SecondSemesterScore"=>0,"FirstSemesterRating"=>"","SecondSemesterRating"=>"","OverAllScore"=>0,"OverAllRating"=>"","RatingYear"=>"");
	$fields['EmpID']=$EmpID;
	foreach ($fields as $field => $value) {
		$fields[$field]=isset($_POST[$field])?$MySQLi->RealEscapeString(strtoupper(strip_tags(trim($_POST[$field])))):'';
	}
	
	if($mode=="0"){
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		//Get New RatingID
		$NewPRID="PR".$EmpID."01";
		$count=1;
		while($records=$MySQLi->GetArray("SELECT RatingID FROM tblempratings WHERE RatingID = '$NewPRID';")){
			$count+=1;
			$ccc=($count>9)?$count:"0".$count;
			$NewPRID="PR".$EmpID.$ccc;
		}
		$fields['RatingID']=$NewPRID;

		$sql="INSERT INTO tblempratings (RatingID, EmpID, FirstSemesterScore, SecondSemesterScore, FirstSemesterRating, SecondSemesterRating, OverAllScore, OverAllRating, RatingYear, RECORD_TIME) VALUES ('$fields[RatingID]', '$fields[EmpID]', $fields[FirstSemesterScore], $fields[SecondSemesterScore], '$fields[FirstSemesterRating]', '$fields[SecondSemesterRating]', $fields[OverAllScore], '$fields[OverAllRating]', $fields[RatingYear], NOW());";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully added.";}
	}
	else if($mode=="1") {
		if(!$Authorization[2]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$fields['RatingID'] = $RatingID;
		$sql="UPDATE tblempratings SET FirstSemesterScore = $fields[FirstSemesterScore], SecondSemesterScore = $fields[SecondSemesterScore], FirstSemesterRating = '$fields[FirstSemesterRating]', SecondSemesterRating = '$fields[SecondSemesterRating]', OverAllScore = $fields[OverAllScore], OverAllRating = '$fields[OverAllRating]', RatingYear = $fields[RatingYear], RECORD_TIME = NOW() WHERE EmpID = '$EmpID' AND RatingID = '$RatingID' LIMIT 1;";

		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully updated.";}
	}
	else if($mode=="-1") {
		if(!$Authorization[3]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
		$sql="DELETE FROM tblempratings WHERE EmpID = '$EmpID' AND RatingID = '$RatingID' LIMIT 1;";
		
		if($MySQLi->sqlQuery($sql)){echo "1|$EmpID|Membership record was successfully deleted.";}
	}
	else{echo "0|$EmpID|ERROR ???:<br/>Unkown mode.";}
	ob_end_flush();
?>