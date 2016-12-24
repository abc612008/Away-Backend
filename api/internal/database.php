<?php
include_once (dirname(__FILE__) . '/config.php');
class database{

	var $m_connection; //connection

	function connect($host, $username, $password, $database, $port){
		$this->m_connection = mysqli_connect($host, $username, $password, $database, $port);
	}

	function filter($text){
		return htmlspecialchars(mysqli_real_escape_string($this->m_connection, $text));
	}

	function query($content){
		return mysqli_query($this->m_connection, $content);
	}

	function query_getline($content){
		return mysqli_fetch_array($this->query($content));
	}

	function num_rows($result){
		return mysqli_num_rows($result);
	}

	function __destruct() {
		mysqli_close($this->m_connection);
	}
}

$db=new database();
$db->connect(SQL_SERVER, SQL_SERVER_USERNAME, SQL_SERVER_PASSWORD, SQL_SERVER_DATABASE, SQL_SERVER_PORT);
?>
