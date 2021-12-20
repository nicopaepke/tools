<html>
<?php
	require_once 'config.php';
	require_once 'security.php';
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
	<?php
		echo '<a href="' . $root_page . '">Hauptmenu</a>';
		echo '<p>' . $_SESSION['userid'] . '</p>';
	?>
</body>
</html>