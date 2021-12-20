<html>
<?php
	require_once 'config.php';
	require_once 'security.php';
	require_once 'db.php';
	require_once 'permission/classes/permission.php';
	$permission = new Permission();
	$permittedModules = $permission->getPermittedModules($link, getCurrentUser());
?>
<head>
  <Title></Title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <?php
    echo '<link rel="stylesheet" href="' . $root_page . '/css/bootstrap.min.css">';
	echo '<link rel="stylesheet" href="' . $root_page . '/css/main.css">';
	echo '<script type="text/javascript" src="' . $root_page . '/js/main.js"></script>';
  ?> 
	
</head>
<body>
	<div class="navbar">
		<a class="home" href=<?php echo '"' . $root_page . '"'?>>Home</a>
		<div class="dropdown">
			<button class="dropbtn">Applikationen
				<i class="glyphicon glyphicon-menu-down"></i>
			</button>
			<div class="dropdown-content">
			<?php
				if( in_array('BUDGET', $permittedModules)){
					echo '<a href="' . $root_page . '/budget/overview.php">Haushaltsbuch</a>';
				}
				if( in_array('PERMISSION_ADMIN', $permittedModules)){
					echo '<a href="' . $root_page . '/permission/overview.php">Rechteverwaltung</a>';
				}
				if( in_array('FUEL', $permittedModules)){
					echo '<a href="' . $root_page . '/fuel/overview.php">Kraftstoff</a>';
				}
			?>
			</div>			
		</div>
		<div class="topnav-right">
			<div class="dropdown">
				<button class="dropbtn"><?php echo $_SESSION['userid'];?>					
					<i class="glyphicon glyphicon glyphicon-menu-down"></i>
				</button>
				<div class="dropdown-content">
					<a href="user/login.php?logout">Logout</a>
				</div>
			</div>			
		</div>
	</div>
</body>
</html>