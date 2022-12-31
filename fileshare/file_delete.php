<?php
require_once '../config.php';
require_once '../db.php';
require_once '../security.php';

if (!array_key_exists("id", $_GET)){
    exit();
}
$uuid = $_GET["id"];
$sql = "DELETE FROM files WHERE uuid = ?";
try{
	unlink($file_share_directory . $uuid);
	if($stmt = mysqli_prepare($link, $sql)){
		mysqli_stmt_bind_param($stmt, "s", $uuid);
		if(!mysqli_stmt_execute($stmt)){		
			echo mysqli_error($link);
		}
	}
}
finally{
	mysqli_stmt_close($stmt);
}
header("location: overview.php");
?>
