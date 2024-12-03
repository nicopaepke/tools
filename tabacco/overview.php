<?php
	require_once '../security.php';
	require_once '../db.php';
	require_once '../header.php';
		
	require_once '../permission/permission.php';
	
	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUserLogin(), 'TABACCO')){
		include '../access_denied.html';
		exit();
	}
	
	$sql = "SELECT tabacco_box.id, brand, contents, expected_cigarettes, price, tabacco_box.started_at, tabacco_box.finished_at, SUM(quantity) AS produced FROM tabacco_box LEFT JOIN cigarette_production ON cigarette_production.id_tabacco_box = tabacco_box.id ORDER BY started_at DESC";
	$boxes = [];
	
	if($result = mysqli_query($link, $sql)){
		if(mysqli_num_rows($result) > 0){
			while($row = mysqli_fetch_array($result)){
					$boxes[] = $row;
			}
		}
		mysqli_free_result($result);
	} else{
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
	}
	foreach($boxes as &$box){
		$box['percentage'] = 0;
		$box['cig_price'] = '-';
		if( $box['expected_cigarettes'] != 0){
			$box['percentage'] = round($box['produced'] / $box['expected_cigarettes'] * 100, 2);
		}
		if( $box['produced'] != 0){
			$box['cig_price'] = round($box['price'] / $box['produced'] * 100, 1);
		}
	}
?>
<html>

<head>
  <title>Login</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/fontawesome.min.css">
  <link rel="stylesheet" href="../css/main.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
<div class='container-fluid tabacco'>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>	
			<div class='page-header'>
				<h2>Tabak</h2>
			</div>
		</div>
	</div>
	
<?php
	foreach($boxes as $box){
		echo '<div class="tabacco-box"><div class="row">';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Hersteller:</b></p>';
		echo '</div><div class="col-6"><p>' . $box['brand'] . '</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Preis:</b></p>';
		echo '</div><div class="col-6"><p>' . $box['price'] . ' €</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Inhalt:</b></p>';
		echo '</div><div class="col-6"><p>' . $box['contents'] . ' g</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Bis zu:</b></p>';
		echo '</div><div class="col-6"><p>' . $box['expected_cigarettes'] . ' Zig.</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Begonnen:</b></p>';
		echo '</div><div class="col-6"><p>' . date_format(date_create($box['started_at']), 'd.m.Y') . '</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Beendet:</b></p>';
		if( $box['finished_at'] != null){
			echo '</div><div class="col-6"><p>' . date_format(date_create($box['finished_at']), 'd.m.Y') . '</p></div></div></div>';
		} else {
			echo '</div><div class="col-6"><p>-</p></div></div></div>';
		}		
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Produziert:</b></p>';
		echo '</div><div class="col-6"><p>' . $box['produced'] . '</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Ausbeute:</b></p>';
		echo '</div><div class="col-6"><p>' . $box['percentage'] . ' %</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p><b>Zig. Preis:</b></p>';
		echo '</div><div class="col-6"><p>' . $box['cig_price'] . ' Cent</p></div></div></div>';
		
		
		echo '</div><hr class="separator"/><div class="row button-list" align="right"><div class="col-12">';
		echo '<a id="add-production-button" href="production.php?id_box=' . $box['id'] . '" class="btn btn-primary">+ Produktion</a>';
		echo '<a id="close-button" href="close_box.php?id_box=' . $box['id'] . '" class="btn btn-primary">Beenden</a>';
		echo '</div></div></div>';
	}
?>
	
</div>

	
</body>
</html>