<?php 
session_start();
include 'config.php';

if(!isset($_SESSION['userid'])) {
	header("location: login.php");
	exit();
}

function getCurrentUser(){
	return $_SESSION['userid'];
}

?>