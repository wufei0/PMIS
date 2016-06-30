<?php

require_once $_SESSION['path'].'/lib/classes/Conf.php';

class SybaseClass {

	var $myHost; // server name
	var $myHostPort;
	var $userName; //db user
	var $userPassword; // db user password
	var $db_name; // database name
	var $conn; // database connection
	var $result;

/* Class Constructor for SybaseClass */
	function SybaseClass() {
		$this->myHost="10.10.5.12"; //reference for the Host
		$this->myHostPort="5000";
		$this->userName="sa"; //reference for the Username
		$this->userPassword=""; //reference for the Password
		$this->db_name="pmis"; //reference for the DatabaseName
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
		$this->conn=sybase_pconnect($this->myHost,$this->userName,$this->userPassword,"utf8");
		// Check Connection
		if(!$this->conn){echo "Couldn't make a connection!";exit;}
		$db = sybase_select_db($this->db_name,$this->conn); 
		if(!$db){echo "Couldn't select database!";exit;}
	}

	/* 	
		dbDisconnect Function
		uses mysql_close function
		to disconnect from the Database
		
	*/
	function dbDisconnect($result=NULL){
		if($this->conn!=null){
			if($result){$this->conn->freeMem($result);}
			return $this->conn->dbClose();
		}
	}
	
	/*
		SQLQUERY Function
		Input Parameter is the Query String
		Uses sybase_query(), mysqli_error() functions
		returns the result set $this->result

	*/
	function sqlQuery($sql){
		if((isset($this->conn))&&($sql!='')){
			sybase_query("SET CHARACTER_SET_CLIENT='utf8';",$this->conn);
			sybase_query("SET CHARACTER_SET_RESULTS='utf8';",$this->conn);
			sybase_query("SET CHARACTER_SET_CONNECTION='utf8';",$this->conn);
			$this->result=sybase_query($sql,$this->conn);
			if(!($this->result)){
				if(mysqli_errno($this->conn)==1062){
					echo "ERROR ".mysqli_errno($this->conn).":~".mysqli_error($this->conn)."<br/><b>".$sql."</b><br/><br/>";
					return false;
				}
				echo "ERROR ".mysqli_errno($this->conn).":~".mysqli_error($this->conn)."<br/><b>".$sql."</b><br/><br/>";
				return false;
			}
			return $this->result;
	 } else{echo "ERROR 49:~No SQL query found...";return false;}
	}

	function GetArray($sql){
		sybase_query("SET CHARACTER_SET_RESULTS='utf8';",$this->conn);
		return sybase_fetch_array(sybase_query($sql,$this->conn));
	}

	function NumberOfRows($sql){return sybase_num_rows(sybase_query($sql,$this->conn));}
	
	function RealEscapeString($str){return mysqli_real_escape_string($this->conn,$str);}
}
?>
