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
	echo '<script type="text/javascript" src="' . $root_page . '/js/bootstrap.min.js"></script>';
	echo '<script type="text/javascript" src="' . $root_page . '/js/main.js"></script>';
  ?>
	
</head>
<body>
<nav class="navbar navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $root_page?>">Home</a>
        <form class="d-flex">
            <ul class="navbar-nav">
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown"
                aria-expanded="true">
                  <?php echo getCurrentUserLogin();?>
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                  <li><a class="dropdown-item" href="<?php echo $root_page . '/user/login.php?logout';?>">Logout</a></li>
                </ul>
              </li>
            </ul>
        </form>
    </div>
</nav>


	<!--<div class="navbar navbar-light bg-light">
		<a class="navbar-brand" href=<?php echo '"' . $root_page . '"'?>>Home</i></a>
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
	</div>-->
</body>
</html>