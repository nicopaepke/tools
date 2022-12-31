<?php
	require_once '../security.php';
	require_once '../db.php';
	require_once '../header.php';

	require_once '../permission/permission.php';

	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUserLogin(), 'FILESHARE')){
		include '../access_denied.html';
		exit();
	}
	
	$permittedRights = $permission->getPermissions($link, getCurrentUserLogin(), 'FILESHARE');
	$hasUploadRight = in_array('UPLOAD', $permittedRights);
	$hasDownloadRight = in_array('DOWNLOAD', $permittedRights);
	$hasSuperUserRight = in_array('SUPER_USER', $permittedRights);
	$hasDeletePrivateRight = in_array('DELETE_PRIVATE', $permittedRights);
	$hasDeletePublicRight = in_array('DELETE_PUBLIC', $permittedRights);
	
	$hasDeletePrivateRight = $hasDeletePrivateRight || $hasSuperUserRight;
	$hasDeletePublicRight = $hasDeletePublicRight || $hasSuperUserRight;
		
	$files = [];
	if( $hasDownloadRight || $hasSuperUserRight || $hasDeletePrivateRight || $hasDeletePublicRight){
		$sql = 'SELECT uuid, upload_time_stamp, owner, file_name, size_bytes, is_public FROM files';
		if( !$hasSuperUserRight)
		{
			$sql .= ' WHERE owner = ? OR is_public = 1';
		}
		$sql .= ' ORDER BY upload_time_stamp';
		if($stmt = mysqli_prepare($link, $sql)){
			try{
				if( !$hasSuperUserRight){
					mysqli_stmt_bind_param($stmt, "i", getCurrentUserId());
				}
				mysqli_stmt_execute($stmt);
				$res = mysqli_stmt_get_result($stmt);
				while($file = mysqli_fetch_array($res)) {
					$size = $file['size_bytes'] * 1;
					$size_unit = ' Byte';
					if( $size >= 1024){
						$size = $size / 1024;
						$size_unit = ' kB';
					}
					if( $size >= 1024){
						$size = $size / 1024;
						$size_unit = ' MB';
					}
					if( $size >= 1024){
						$size = $size / 1024;
						$size_unit = ' GB';
					}
					$file['size'] = number_format($size, 1, ',', '.') . $size_unit;
					
					$files[] = $file;
				}
			} finally {
				mysqli_stmt_close($stmt);
			}
		}else{
			echo mysqli_error($link);
		}
	}
?>
<html>
<head>
  <Title>Fileshare</Title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/main.css">
  
<!--  
	RECHTE:
		UPLOAD				darf hochladen als public oder private
		DOWNLOAD			darf private und public runterladen
		DELETE_PRIVATE		darf private löschen
		DELETE_PUBLIC		darf public löschen
		SUPER_USER			darf alles (alle privaten sehen, löschen, runterladen)
	
	Dateien werden an einem zentralen Ort im Dateisystem abgelegt mit einer UUID
	Datenbank:
		- PK UUID -> ist die selbe, wie die Datei im Dateisystem
		- UploadTimeStamp -> Zeitpunkt des hochladens
		- Owner -> Referenz auf User, der die Datei hochgeladen hat
		- FileName -> Name der Ursprünglichen Datei
	
	Sichbarkeit wird über erste Spalte angezeigt
	der Superuser bekommt zusätzlich in Spalte 1 den eigentümer angezeigt
-->
  <script>
	var upload_start_time;
    function getElement(abc) {
		return document.getElementById(abc);
	}
	function uploadFileHandler() {
		getElement('progressDiv').style.display='block';
		var file = getElement("uploadingfile").files[0];
		var isPublic = getElement("ispublicfile");
		var formdata = new FormData();
		formdata.append("uploadingfile", file);
		formdata.append("ispublic", isPublic.checked);
		var ajax = new XMLHttpRequest();
		ajax.upload.addEventListener("progress", progressHandler, false);
		ajax.addEventListener("load", completeHandler, false);
		ajax.addEventListener("error", errorHandler, false);
		ajax.addEventListener("abort", abortHandler, false);
		ajax.open("POST", "upload.php");
		ajax.send(formdata);
		upload_start_time = Date.now();
	}
	function progressHandler(event) {
		var bytesPerMSec = event.loaded / (Date.now() - upload_start_time);
		var mbPerSec = bytesPerMSec * 1000 / 1048576;
		console.log(mbPerSec.toFixed(2) + " MB/Sek");
		var loaded = event.loaded / 1048576;
		var total = event.total / 1048576;
		var percent = Math.round((event.loaded / event.total) * 100);
		getElement("progressBar").style.width = Math.round(percent) + "%";
		getElement("progressDescription").innerHTML = 
			loaded.toFixed(3) + " von " + total.toFixed(3) + " MB (" + percent + "%) mit " + mbPerSec.toFixed(2) + " MB/Sek";
	}
	function completeHandler(event) {
		getElement("progressBar").style.width = 0;
		getElement("progressDescription").innerHTML = '';
		getElement('progressDiv').style.display = 'none';
		//getElement("status").innerHTML = event.target.responseText;
		location.reload();	
	}
	function errorHandler(event) {
		getElement("status").innerHTML = "Upload Failed " + event.target.responseText;
	}
	function abortHandler(event) {
		getElement("status").innerHTML = "Upload Aborted";
	}
</script>
</head>
<body>
<div class='container-fluid fileshare'>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>	
			<div class='page-header'>
				<h2>Dateien</h2>
			</div>
		</div>
	</div>
	<div class="row justify-content-center" style="max-height: calc(100% - 280px); overflow-y: auto;">
		<div class='row-column col-md-12'>
			<table class="table table-striped files-table">
				<colgroup>
					<col style="width: 25px" />
					<col style="" />
					<col style="" />
					<col style="width: 120px" />
					<col style="width: 60px" />
				</colgroup>				
				<thead>
					<tr>
						<th></th>
						<th>Datum</th>
						<th>Dateiname</th>
						<th style="text-align: right">Größe</th>
						<th>Aktion</th>
					</tr>
				</thead>
				<tbody>
				<?php
				
					foreach($files as $row ){
					echo '<tr>';
					if($row['is_public']){
						echo '	<td><span class="glyphicon glyphicon-globe"></span></td>';
					}else{
						echo '	<td><span class="glyphicon glyphicon-lock"></span></td>';
					}
					echo '	<td>' . date_format(date_create($row['upload_time_stamp']), "d.m.Y H:i:s") . '</td>';
					echo '	<td>' . $row['file_name'] . '</td>';
					echo '	<td style="text-align: right">' . $row['size'] . '</td>';
					echo '	<td>';
					if( $hasDownloadRight){
						echo '		<a href="/_files/' . $row['uuid'] . '"' . ' download="' . $row['file_name'] . '" title="Datei herunterladen"><span class="glyphicon glyphicon-download"></span></a>';
					}
					if( (!$row['is_public'] && $hasDeletePrivateRight) || ($row['is_public'] && $hasDeletePublicRight)){
						echo '		<a style="float: right" href="file_delete.php?id=' . $row['uuid'] . '" title="Datei löschen"><span class="glyphicon glyphicon-trash"></span></a>';
					}
					echo '	</td>';
					echo '</tr>';
					}					
				?>
				
				</tbody>
			 </table>
		</div>
	</div>
	<hr>
	
	<?php 
	if( $hasUploadRight)
	{
		echo '<div class="row justify-content-center">';
		echo '	<div class="row-column col-md-12">';
		echo '		<form id="upload_form" enctype="multipart/form-data" method="post">';
		echo '			<div class="row">';
		echo '			<div class="form-group col-md-2">';
		echo '				<input type="checkbox" name="ispublicfile" id="ispublicfile"/>';
		echo '				<label for="ispublicfile">öffentliche Datei</label>';
		echo '			</div>';
		echo '			<div class="form-group col-md-8">';
		echo '				<input type="file" name="uploadingfile" id="uploadingfile"/>';
		echo '			</div>';
		echo '			<div class="form-group col-md-2">';
		echo '				<input class="btn btn-primary" type="button" value="Hochladen" name="btnSubmit"';
		echo '					   onclick="uploadFileHandler()">';
		echo '			</div>';
		echo '			</div>';
		echo '			<div class="form-group">';
		echo '				<div class="progress" id="progressDiv" style="display:block" >';
		echo '					<span id="progressDescription" style="color:black;position:absolute;text-align:center;width:100%">';
		echo '					</span>';
		echo '					<div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>';
		echo '				</div>';
		echo '			</div>';
		echo '			<div class="form-group">';
		echo '				<h3 id="status"></h3>';
		echo '				<p id="uploaded_progress"></p>';
		echo '			</div>';
		echo '		</form>';
		echo '	</div>';
		echo '</div>';
	}
	?>
</div>
</body>
</html>
