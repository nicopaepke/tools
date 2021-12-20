<html>
<head>
  <Title></Title>
  
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/main.css">
  
  <script type="text/javascript" src="js/main.js"></script>
	
</head>
<body>
	<?php
		require_once 'config.php';
		require_once 'security.php';
		echo '<a href="' . $root_page . '">Hauptmenu</a>';
		echo '<p>' . $_SESSION['userid'] . '</p>';
	?>
</body>
</html>