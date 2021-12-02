<?php
	require_once 'security.php';
	//require_once "db.php";
?>
<html>

<head>
  <title>Übersicht</title>    
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/main.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="row-column col-md-12">	
			<div class="page-header">
				<h2>Applikation auswählen</h2>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="col-md-4 col-sm-12">
			<a href="haushaltsbuch">
				<div class="tile">Haushaltsbuch</div>
			</a>
		</div>
		<div class="col-md-4 col-sm-12">
			<a href="login.php">
				<div class="tile">Login</div>
			</a>
		</div>
		<div class="col-md-4 col-sm-12">
			<a href="create_user.php">
				<div class="tile">Registrierung</div>
			</a>
		</div>
	</div>
</div>
</body>
</html>