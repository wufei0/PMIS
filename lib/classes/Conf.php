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
		$this->dbname = 'pmis';
		$this->dbuser = 'pmis';
		$this->dbpass = 'p61upm15';
		$this->version = '1.0';

	}
}
?>