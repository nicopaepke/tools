<?php
	include 'config.php';
	$link = new mysqli($db_servername, $db_username, $db_password, $db_name);
		
	if ($link->connect_error) {
	  die("Connection failed: " . $link->connect_error);
	}
?>