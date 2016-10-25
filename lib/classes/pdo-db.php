<?php

class pdo_db {

	var $db;
	var $prepare;
	var $table;
	var $sql;
	var $rows;
	var $insertId;
	
	function __construct($table = "") {
		
		$server = "10.10.4.20";
		$db_name = "pmis";
		$dsn = "mysql:host=$server;dbname=$db_name;charset=utf8";
		$username = "pmis";
		$password = "p61upm15";

		$this->db = new PDO($dsn, $username, $password, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
		$this->table = $table;

	}

	function getData($sql) {

		$stmt = $this->db->query($sql);
		$this->rows = $stmt->rowCount();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);		
		return $results;

	}	

	function auto_increment_one() {
		
		$this->sql = "ALTER TABLE " . $this->table . " AUTO_INCREMENT = 1";
		$this->db->query($this->sql);
		
	}
	
	function insertData($data) {
		
		$this->auto_increment_one();
		
		$this->prepare = "INSERT INTO ".$this->table." (";
		$prepare = "VALUES (";		
		$insert = [];
		
		foreach ($data as $key => $value) {
			$this->prepare .= $key . ",";
			if ($value == 'CURRENT_TIMESTAMP') $prepare .= "$value,";
			else $prepare .= ":$key,";
			if ($value == 'CURRENT_TIMESTAMP') continue;
			$insert[":$key"] = $value;
		}
		
		$prepare = substr($prepare,0,strlen($prepare)-1);
		$prepare .= ")";
		
		$this->prepare = substr($this->prepare,0,strlen($this->prepare)-1);
		$this->prepare .= ") ";
		$this->prepare .= $prepare;
		
		$stmt = $this->db->prepare($this->prepare);
		$stmt->execute($insert);
		$this->insertId = $this->db->lastInsertId();

	}
	
	function insertDataMulti($data) {
		
		$this->auto_increment_one();		
		
		$inserts = [];
		$this->prepare = "INSERT INTO ".$this->table." (";	
		$prepare = "VALUES (";		
		
		foreach ($data as $row) { // construct Prepared Statement
			foreach ($row as $key => $value) {
				$this->prepare .= $key . ",";
				if ($value == 'CURRENT_TIMESTAMP') $prepare .= "$value,";
				else $prepare .= ":$key,";
				if ($value == 'CURRENT_TIMESTAMP') continue;
			}
			break;
		}

		foreach ($data as $row) { // strip item with CURRENT_TIMESTAMP value
		
			$insert = [];		
			foreach ($row as $key => $value) {
				if ($value == 'CURRENT_TIMESTAMP') continue;
				$insert[$key] = $value;
			}
			$inserts[] = $insert;
			
		}

		$prepare = substr($prepare,0,strlen($prepare)-1);
		$prepare .= ")";
		
		$this->prepare = substr($this->prepare,0,strlen($this->prepare)-1);
		$this->prepare .= ") ";
		$this->prepare .= $prepare;

		$this->db->beginTransaction();
		foreach ($inserts as $insert) {
			$stmt = $this->db->prepare($this->prepare);
			$stmt->execute($insert);
		}	 
		$this->db->commit();

	}
	
	function updateData($data,$pk) {
		
		$insert = [];
		
		$this->prepare = "UPDATE ".$this->table;
		$prepare = " SET ";

		foreach ($data as $key => $value) {
			
			if ($key == $pk) {
				$pk_value = $value;
				continue;
			}
			
			if ($value == "CURRENT_TIMESTAMP") {
				$prepare .= $key."=CURRENT_TIMESTAMP,";
				continue;
			} else {
				$prepare .= $key."=?,";
			}
			$insert[] = $value;	
		}
		
		$prepare = substr($prepare,0,strlen($prepare)-1);
		
		$this->prepare .= $prepare;
		$this->prepare .= " WHERE $pk=?";
		$insert[] = $pk_value;
		
		$stmt = $this->db->prepare($this->prepare);
		$stmt->execute($insert);		
		
	}
	
	function updateDataMulti($data,$pk) {
		
		$updates = [];		
						
		$this->prepare = "UPDATE ".$this->table;
		$prepare = " SET ";

		foreach ($data as $row) { // construct Prepared Statement
		
			foreach ($row as $key => $value) {
				
				if ($key == $pk) {
					continue;
				}
				
				if ($value == "CURRENT_TIMESTAMP") {
					$prepare .= $key."=CURRENT_TIMESTAMP,";
					continue;
				} else {
					$prepare .= $key."=?,";
				}
			}
			break;
		
		}
		
		$prepare = substr($prepare,0,strlen($prepare)-1);		
		$this->prepare .= $prepare;
		$this->prepare .= " WHERE $pk=?"; print_r($this->prepare);

		foreach ($data as $row) {
			
			$update = [];
			foreach ($row as $key => $value) {
				
				if ($key == $pk) {
					$pk_value = $value;
					continue;
				}				
				$update[] = $value;	
			}		
			$update[] = $pk_value;
			$updates[] = $update;
			
		}

		$this->db->beginTransaction();		 
		foreach ($updates as $update) {
			$stmt = $this->db->prepare($this->prepare);
			$stmt->execute($update);
		}		 
		$this->db->commit();		
		
	}
	
	function deleteData($data) {
		
		$qMarks = str_repeat('?,', count(explode(",",array_values($data)[0])) - 1) . '?';
		$prepare = "DELETE FROM ".$this->table." WHERE ".array_keys($data)[0]." IN ($qMarks)"; echo $prepare;
		$insert = explode(",",array_values($data)[0]);
		$stmt = $this->db->prepare($prepare);
		$stmt->execute($insert);

	}

}

?>