<?php
class dbConfig {
	var $smtphost;
	var $dbhost;
	var $dbport;
	var $dbname;
	var $dbuser;
	var $version;

	function dbConfig() {

		$this->dbhost = '10.10.4.20';
		$this->dbport = '3306';
		$this->dbname = 'dbname';
		$this->dbuser = 'user';
		$this->dbpass = 'password';
		$this->version = '1.0';

	}
}
?>
