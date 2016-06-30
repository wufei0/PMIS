<?php
	ob_start();
	session_start();
	date_default_timezone_set('Asia/Taipei');
		
	
	
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */
	require_once $_SESSION['path'].'/lib/classes/Authentication.php';$Authentication=new Authentication();$ActiveStatus=explode("|",$Authentication->isUserActive($_SESSION['user'],$_SESSION['fingerprint']));if($ActiveStatus[0]!=1){echo "-1|".$_SESSION['user']."|".$ActiveStatus[1];exit();}
	/* Check user access to this module */
	$Authorization=str_split($Authentication->getAuthorization($_SESSION['user'],'MOD002'));
	if(!$Authorization[1]){echo "0|".$_SESSION['user']."|ERROR 401:~Access denied!!!";exit();}
	/* - - - - - - - - - -  A U T H E N T I C A T I O N - - - - - - - - - - */

	
	
	$cnx=odbc_pconnect('pmisdb','sa','') or die("Error Connect to Database");
	if(!$cnx){echo "-1|0|0|0|0|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
						
	/* Migrate SG of current ID at current given Date */
	$sql="SELECT * FROM salary_grade ORDER BY salary_grade";
	$cur=odbc_exec($cnx, $sql);
	if(!$cur){echo "-1|0|0|0|0|ERROR 49:~Error in odbc_exec(no cursor returned) $sql";odbc_close($cnx);exit();}
	
	echo "Migrating Salary grades...<br><br>";
	
	while($SG=odbc_fetch_array($cur)){
		$YR="2013";
		$SalGrade=($SG['salary_grade']<10?"0".$SG['salary_grade']:$SG['salary_grade']);
		echo "SALARY GRADE: ".$SalGrade." - ";
		$MySQLi=new MySQLClass();
		for($S=1;$S<=10;$S+=1){
			$SalStep=($S<10?"0".$S:$S);
			$SalGrdID=$YR.$SalGrade.$SalStep;
			$SalGrdValue=$SG['step'.$S];
			echo $SalGrdID.": ".$SG['step'.$S].", ";
			$sql ="INSERT INTO `tblsalgrade`(`SalGrdID`, `SalGrade`, `SalStep`, `SalGrdValue`, `RECORD_TIME`) VALUES('".$SalGrdID."', '".$SalGrade."','".$SalStep."', '".$SalGrdValue."', NOW());";
			$MySQLi->sqlQuery($sql,false);
		}
		echo "<br><br>";
	}
	
  odbc_close($cnx);
?>