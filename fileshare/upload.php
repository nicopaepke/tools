<?php
if (!$_FILES["uploadingfile"]["tmp_name"]) {
    echo "Keine Datei ausgewählt";
    exit();
} else {
	$folderPath = "uploads/";
	
	$original_file_name = $_FILES["uploadingfile"]["name"];
	$size_raw = $_FILES["uploadingfile"]["size"];        
	$size_as_mb = number_format(($size_raw / 1048576), 2);        
	if (move_uploaded_file($_FILES["uploadingfile"]["tmp_name"], "$folderPath" . $_FILES["uploadingfile"]["name"] . "")) {
		echo "$original_file_name erfolgreich hochgeladen";
	}
    else {
        echo "Datei konnte nicht kopiert werden";
        exit;
    }
}
?>
