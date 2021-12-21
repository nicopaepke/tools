<?php
	require_once 'security.php';
	require_once 'permission/classes/permission.php';
	require_once 'db.php';
	$permission = new Permission();
	$permittedModules = $permission->getPermittedModules($link, getCurrentUser());
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
	<?php
	if( in_array('BUDGET', $permittedModules)){
		echo '<div class="row justify-content-center">';
		echo '	<div class="col-md-4">';
		echo '		<a href="budget/overview.php">';
		echo '			<div class="tile">Haushaltsbuch</div>';
		echo '		</a>';
		echo '	</div>';
	}
	if( in_array('PERMISSION_ADMIN', $permittedModules)){
		echo '	<div class="col-md-4">';
		echo '		<a href="permission/overview.php">';
		echo '			<div class="tile">Rechteverwaltung</div>';
		echo '		</a>';
		echo '	</div>';
	}
	if( in_array('FUEL', $permittedModules)){
		echo '	<div class="col-md-4">';
		echo '		<a href="fuel/overview.php">';
		echo '			<div class="tile">Kraftstoff</div>';
		echo '		</a>';
		echo '	</div>';
	}
	
 	#echo '	<div class="col-md-4">';
	#echo '		<a href="user/login.php?logout">';
	#echo '			<div class="tile">Login</div>';
	#echo '		</a>';
	#echo '	</div>'; 


	#echo '	<div class="col-md-4">';
	#echo '		<a href="user/create_user.php">';
	#echo '			<div class="tile">Registrierung</div>';
	#echo '		</a>';
	#echo '	</div>';
	#echo '</div>';
	
	?>
</div>
</body>
</html>