<?php 
session_start();
include 'config.php';

if(!isset($_SESSION['userid'])) {
	header("location: user/login.php");
	exit();
}

function getCurrentUser(){
	return $_SESSION['userid'];
}

?>