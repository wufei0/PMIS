<?php

require_once $_SESSION['path'].'/lib/classes/Conf.php';

class MySQLClass {

	var $myHost; // server name
	var $myHostPort;
	var $userName; //db user
	var $userPassword; // db user password
	var $db_name; // database name
	var $conn; // database connection
	var $result;

/* Class Constructor for MySQLClass */
	function MySQLClass() {
		$Config=new dbConfig();
		$this->myHost=$Config->dbhost; //reference for the Host
		$this->myHostPort=$Config->dbport;
		$this->userName=$Config->dbuser; //reference for the Username
		$this->userPassword=$Config->dbpass; //reference for the Password
		$this->db_name=$Config->dbname; //reference for the DatabaseName
		$this->conn="";
		$this->dbConnect();
	}

	/* 	
		DBConnection Function
		uses mysql_connect function to connect the Database
		gets myhost, username, userpassword
		Returns true if Connected to the Database
	*/
	function dbConnect(){
		// Create Connection
		$this->conn = new mysqli($this->myHost, $this->userName, $this->userPassword, $this->db_name);
		// Check Connection
		if (mysqli_connect_errno()) {
			echo "ERROR 00:~Connect failed: ".mysqli_connect_error();
			exit();
		}
		mysqli_query($this->conn,"SET CHARACTER_SET_CLIENT='utf8';");
		mysqli_query($this->conn,"SET CHARACTER_SET_RESULTS='utf8';");
		mysqli_query($this->conn,"SET CHARACTER_SET_CONNECTION='utf8';");
	}

	/* 	
		dbDisconnect Function
		uses mysql_close function
		to disconnect from the Database
		
	*/
	function dbDisconnect($result=NULL){
		if ($this->conn != null){
			if($result){$this->conn->freeMem($result);}
			return $this->conn->dbClose();
		}
	}

	/* 	
		LogTrail Function
		records All user queries
		to the database
		
	*/
	function LogTrail($sql,$status,$remarks){
		$UserID=$_SESSION['user'];
		/* Get New TrailID */
		$NewTrailID=date('YmdH').$UserID."001";
		$count=1;
		while($records=$this->GetArray("SELECT `TrailID` FROM `tbltrails` WHERE `TrailID`='$NewTrailID';")) {
			$count+=1;
			$ccc=($count>99)?$count:(($count>9)?"0".$count:"00".$count);
			$NewTrailID=date('YmdH').$UserID.$ccc;
		} $TrailID=$NewTrailID;
		$q="INSERT INTO `tbltrails` (`TrailID`,`UserID`,`TrailTime`,`TrailQuery`,`TrailQueryStatus`,`TrailRemarks`,`RECORD_TIME`) VALUES ('$TrailID','$UserID',NOW(),'".$this->RealEscapeString($sql)."','$status','$remarks',NOW());";
		mysqli_query($this->conn,$q);
	}
	
	/*
		SQLQUERY Function
		Input Parameter is the Query String
		Uses mysqli_query(), mysqli_error() functions
		returns the result set $this->result

	*/
	function sqlQuery($sql,$logit=true){
		if((isset($this->conn))&&($sql!='')){
			$this->result=mysqli_query($this->conn,$sql);
			if(!($this->result)){
				if(mysqli_errno($this->conn)==1062){
					$this->LogTrail($sql,"FAILED","ERROR ".mysqli_errno($this->conn).": ".mysqli_error($this->conn));
					echo "ERROR ".mysqli_errno($this->conn).":~".mysqli_error($this->conn)."<br/><b>".$sql."</b><br/><br/>";
					return false;
				}
				$this->LogTrail($sql,"FAILED","ERROR ".mysqli_errno($this->conn).": ".mysqli_error($this->conn));
				echo "ERROR ".mysqli_errno($this->conn).":~".mysqli_error($this->conn)."<br/><b>".$sql."</b><br/><br/>";
				return false;
			}
			if((substr($sql,0,6)!="SELECT") && $logit){$this->LogTrail($sql,"SUCCESSFUL","");}
			return $this->result;
	 } else{echo "ERROR 49:~No SQL query found...";return false;}
	}

	function GetArray($sql){
		mysqli_query($this->conn,"SET CHARACTER_SET_RESULTS='utf8';");
		return mysqli_fetch_array(mysqli_query($this->conn,$sql), MYSQLI_BOTH);
	}

	function NumberOfRows($sql){return mysqli_num_rows(mysqli_query($this->conn,$sql));}
	
	function RealEscapeString($str){return mysqli_real_escape_string($this->conn,$str);}
}
?>
