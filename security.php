<?php 
session_start();
include 'config.php';

if(!isset($_SESSION['user'])) {
	header("location: user/login.php");
	exit();
}

function getCurrentUserLogin(){
	return $_SESSION['user']['login'];
}

function getCurrentUserId(){
	return $_SESSION['user']['id'];
}

?>