<html>
<?php
	require_once 'config.php';
	require_once 'security.php';
	require_once 'db.php';
	require_once 'permission/permission.php';
	$permission = new Permission();
	$permittedModules = $permission->getPermittedModules($link, getCurrentUserLogin());
?>
<head>
  <Title></Title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <?php
    echo '<link rel="stylesheet" href="' . $root_page . '/css/bootstrap.min.css">';
    echo '<link rel="stylesheet" href="' . $root_page . '/css/fontawesome.min.css">';
	echo '<link rel="stylesheet" href="' . $root_page . '/css/main.css">';
	echo '<script type="text/javascript" src="' . $root_page . '/js/main.js"></script>';
  ?> 
	
</head>
<body>
	<div class="navbar">
		<a class="home" href=<?php echo '"' . $root_page . '"'?>>Home</i></a>
		<div class="topnav-right">
			<div class="dropdown">
				<button class="dropbtn"><?php echo getCurrentUserLogin();?>
					<i class="fa fa-chevron-down"></i>
				</button>
				<div class="dropdown-content">
					<?php
					echo '<a href="' . $root_page . '/user/login.php?logout">Logout</a>';
					?>
				</div>
			</div>			
		</div>
	</div>
</body>
</html>