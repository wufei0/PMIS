<?php
class dbConfig {
	var $smtphost;
	var $dbhost;
	var $dbport;
	var $dbname;
	var $dbuser;
	var $version;

	function dbConfig() {

		$this->dbhost = 'localhost';
		$this->dbport = '3306';
		$this->dbname = 'pmis';
		$this->dbuser = 'root';
		$this->dbpass = 'sly';
		$this->version = '1.0';

	}
}
?>