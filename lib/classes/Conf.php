<?php
class dbConfig {
	var $smtphost;
	var $dbhost;
	var $dbport;
	var $dbname;
	var $dbuser;
	var $version;

	function dbConfig() {

		$this->dbhost = 'ubuntu';
		$this->dbport = '3306';
		$this->dbname = 'pmis';
		$this->dbuser = 'pmis';
		$this->dbpass = 'pm1s';
		$this->version = '1.0';

	}
}
?>