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
		var formdata = new FormData();
		formdata.append("uploadingfile", file);
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
		getElement("status").innerHTML = event.target.responseText;
		getElement("progressBar").style.width = 0;
		getElement("progressDescription").innerHTML = '';
		getElement('progressDiv').style.display = 'none';
	}
	function errorHandler(event) {
		getElement("status").innerHTML = "Upload Failed";
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
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>
			<table class="table table-striped files-table">
				<colgroup>
					<col style="width: 25px" />
					<col style="" />
					<col style="" />
					<col style="width: 60px" />
				</colgroup>				
				<thead>
					<tr>
						<th></th>
						<th>Datum</th>
						<th>Dateiname</th>
						<th>Aktion</th>
					</tr>
				</thead>
				<tbody>
				
				<tr>
					<td><span class="glyphicon glyphicon-lock"></span></td>
					<td>29.09.2022 15:55:45</td>
					<td>Private Datei.txt</td>
					<td>
						<a href="file_delete.php?id=" title="Datei herunterladen"><span class="glyphicon glyphicon-download"></span></a>
						<a style="float: right" href="file_delete.php?id=" title="Datei löschen"><span class="glyphicon glyphicon-trash"></span></a>
					</td>
				</tr>
				<tr>
					<td><span class="glyphicon glyphicon-eye-open"></span></td>
					<td>29.09.2022 15:55:45</td>
					<td>öffentliche Datei.txt</td>
					<td>
						<a href="file_delete.php?id=" title="Datei herunterladen"><span class="glyphicon glyphicon-download"></span></a>
						
					</td>
				</tr>
				<tr>
					<td><span class="glyphicon glyphicon-eye-open"></span></td>
					<td>29.09.2022 15:55:45</td>
					<td>Der Dateinamen dieser Datei ist echt ganz schön lang.öffentliche Datei.txt Also so richtig lang meine ich</td>
					<td>
						<a href="./file.bin" download="datei.txt"><span class="glyphicon glyphicon-download"></span></a>
						<a style="float: right" href="file_delete.php?id=" title="Datei löschen"><span class="glyphicon glyphicon-trash"></span></a>
					</td>
				</tr>
				<!--
				
					foreach($transactions as $row ){
						echo '<tr>';
							echo '<td class="value-col">' . $row['id'] . '</td>';				
							echo '<td>' . $row['source_acc'] . '</td>';				
							echo '<td>' . $row['target_acc']. '</td>';				
							echo '<td>' . date_format(date_create($row['transaction_date']), "d.m.Y") . '</td>';				
							echo '<td class="value-col">' . number_format($row['value'], 2) . ' €</td>';				
							echo '<td>' . $row['comment'] . '</td>';				
							echo '<td>' . date_format(date_create($row['input_date']), "d.m.Y H:i:s") . '</td>';				
							echo '<td>';
							if( $hasEditRight){
								echo '<a href="buchung_delete.php?id=' . $row['id'] . '" title="Buchung löschen"><span class="glyphicon glyphicon-trash"></span></a>';
							}
							echo '</td>';				
						echo '</tr>';
					}
				-->
				</tbody>
			 </table>
		</div>
	</div>
	<hr>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>
			<form id="upload_form" enctype="multipart/form-data" method="post">
				<div class="form-group">
					<input type="file" name="uploadingfile" id="uploadingfile">
				</div>
				<div class="form-group">
					<input class="btn btn-primary" type="button" value="Hochladen" name="btnSubmit"
						   onclick="uploadFileHandler()">
					</div>
				<div class="form-group">
					<div class="progress" id="progressDiv" style="display:block" >
						<span id="progressDescription" style="color:black;position:absolute;text-align:center;width:100%">

						</span>
						<div id="progressBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
					</div>
				</div>
				<div class="form-group">
					<h3 id="status"></h3>
					<p id="uploaded_progress"></p>
				</div>
			</form>
		</div>
	</div>
</div>
</body>
</html>
