<?php
class dbConfig {
	var $smtphost;
	var $dbhost;
	var $dbport;
	var $dbname;
	var $dbuser;
	var $version;

	function dbConfig() {

		$this->dbhost = 'acc_server';
		$this->dbport = '3306';
		$this->dbname = 'pmis';
		$this->dbuser = 'root';
		$this->dbpass = 'P@ssW0rd';
		$this->version = '1.0';

	}
}
?>