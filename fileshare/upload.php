<?php
require_once '../config.php';

if (!array_key_exists("uploadingfile", $_FILES)){
    exit();
}
if (!$_FILES["uploadingfile"]["tmp_name"]) {
    echo "Keine Datei ausgewählt";
    exit();
} else {
    require_once '../db.php';
    require_once '../security.php';

    $uuid = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4));

	$original_file_name = $_FILES["uploadingfile"]["name"];
	$owner = getCurrentUserId();
	$size_raw = $_FILES["uploadingfile"]["size"];
	#$size_as_mb = number_format(($size_raw / 1048576), 2);
	$is_public = 0;
	if( $_POST["ispublic"] == "true"){
		$is_public = 1;
	}
	
	if (move_uploaded_file($_FILES["uploadingfile"]["tmp_name"], $file_share_directory . $uuid)) {
		echo "Datei erfolgreich hochgeladen";
	}
    else {
        echo "Datei konnte nicht kopiert werden";
    }

    $sql = "INSERT INTO files (uuid, owner, file_name, size_bytes, is_public) VALUES (?, ?, ?, ?, ?)";
    try{
        if($stmt = mysqli_prepare($link, $sql)){
            mysqli_stmt_bind_param($stmt, "sssii", $uuid, $owner, $original_file_name, $size_raw, $is_public);
            if(mysqli_stmt_execute($stmt)){
                exit();
            }
        }
        unlink($file_share_directory . $uuid);
        echo mysqli_error($link);
    }
    finally{
        mysqli_stmt_close($stmt);
    }
}
?>
