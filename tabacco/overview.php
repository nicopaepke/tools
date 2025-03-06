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
	$boxes = [];
	$productions = [];
		
	$sql = "SELECT started_at, finished_at, quantity, id_tabacco_box FROM cigarette_production ORDER BY started_at DESC";
		
	if($stmt = mysqli_prepare($link, $sql)){
		try{
			mysqli_stmt_execute($stmt);
			$res = mysqli_stmt_get_result($stmt);
			while($production = mysqli_fetch_array($res)) {
				$duration = date_create($production['finished_at'])->diff(date_create($production['started_at']));
				$production['duration'] = str_pad($duration->h * 60 + $duration->i, 2, '0', STR_PAD_LEFT) . ':' . str_pad($duration->s, 2, '0', STR_PAD_LEFT);
				$production['performance'] = ($duration->h * 60 * 60 + $duration->i * 60 + $duration->s) / $production['quantity'];
				if( !array_key_exists($production['id_tabacco_box'], $productions)){
				$productions[$production['id_tabacco_box']] = [];
			}
				array_push($productions[$production['id_tabacco_box']], $production);
			}
		} finally {
			mysqli_stmt_close($stmt);
		}
	}else{
		echo mysqli_error($link);
	}

	$sql = "SELECT tabacco_box.id, brand, contents, expected_cigarettes, price, tabacco_box.started_at, tabacco_box.finished_at, SUM(quantity) AS produced FROM tabacco_box LEFT JOIN cigarette_production ON cigarette_production.id_tabacco_box = tabacco_box.id GROUP BY tabacco_box.id ORDER BY started_at DESC";
		
	if($stmt = mysqli_prepare($link, $sql)){
		try{
			mysqli_stmt_execute($stmt);
			$res = mysqli_stmt_get_result($stmt);
			while($box = mysqli_fetch_array($res)) {
				$box['percentage'] = 0;
				$box['cig_price'] = '-';
				if( $box['expected_cigarettes'] != 0){
					$box['percentage'] = round($box['produced'] / $box['expected_cigarettes'] * 100, 2);
				}
				if( $box['produced'] != 0){
					$box['cig_price'] = round($box['price'] / $box['produced'] * 100, 1);
				}
				$box['productions'] = [];
				$boxes[] = $box;
			}
		} finally {
			mysqli_stmt_close($stmt);
		}
	}else{
		echo mysqli_error($link);
	}
	
?>
<html>

<head>
  <title>Login</title>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/fontawesome.min.css">
  <link rel="stylesheet" href="../css/main.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <script>
	function collapseExpand( box_id){
		if( document.getElementById('production-' + box_id).style.display == 'none'){
			document.getElementById('production-' + box_id).style.display = null;
		}else{
			document.getElementById('production-' + box_id).style.display = 'none';
		}
	}
  </script>
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
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Hersteller:</p>';
		echo '</div><div class="col-6"><p>' . $box['brand'] . '</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Preis:</p>';
		echo '</div><div class="col-6"><p>' . $box['price'] . ' €</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Inhalt:</p>';
		echo '</div><div class="col-6"><p>' . $box['contents'] . ' g</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Bis zu:</p>';
		echo '</div><div class="col-6"><p>' . $box['expected_cigarettes'] . ' Zig.</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Begonnen:</p>';
		echo '</div><div class="col-6"><p>' . date_format(date_create($box['started_at']), 'd.m.y') . '</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Beendet:</p>';
		if( $box['finished_at'] != null){
			echo '</div><div class="col-6"><p>' . date_format(date_create($box['finished_at']), 'd.m.y') . '</p></div></div></div>';
		} else {
			echo '</div><div class="col-6"><p>-</p></div></div></div>';
		}		
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Produziert:</p>';
		echo '</div><div class="col-6"><p>' . $box['produced'] . '</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Ausbeute:</p>';
		echo '</div><div class="col-6"><p>' . $box['percentage'] . ' %</p></div></div></div>';
		
		echo '<div class="col-md-4 col-6"><div class="row"><div class="col-6"><p>Zig. Preis:</p>';
		echo '</div><div class="col-6"><p>' . $box['cig_price'] . ' Cent</p></div></div></div>';
		
		
		echo '</div><hr class="separator"/><div class="row button-list" align="right"><div class="col-12">';
		echo '<a style="float: left;" id="detail-button-' . $box['id'] . '" onClick="collapseExpand(' . $box['id'] . ');" class="btn btn-primary">Details</a>';
		if( $box['finished_at'] == null){			
			echo '<a id="add-production-button" href="production.php?id_box=' . $box['id'] . '" class="btn btn-primary">+ Produktion</a>';
			echo '<a id="close-button" href="close_box.php?id_box=' . $box['id'] . '" class="btn btn-primary">Beenden</a>';
		}
		echo '</div></div>';
		
		echo '<div style="display: none" id="production-' . $box['id'] . '" class="row"><div class="col-12">';
		echo '<table class="table table-striped production-table"><thead><tr>';
		echo '<th style="text-align: left; width: 50%;">Zeitraum</th><th>Dauer</th><th>Anzahl</th><th>&#8960</th></tr></thead><tbody>';

		foreach($productions[$box['id']] as $production){
			echo "<tr>";
				echo '<td style="text-align: left;">' . date_format(date_create($production['started_at']), "d.m.y H:i:s") . " - " . date_format(date_create($production['finished_at']), "d.m.y H:i:s") . "</td>";
				echo '<td>' . $production['duration'] . "</td>";
				echo '<td>' . $production['quantity'] . "</td>";
				echo '<td>' . number_format($production['performance'],1) . "</td>";
			echo "</tr>";
		}	
		echo '</tbody></table></div></div>';
		echo '</div>';
	}
?>	
	<a id="add-button" href="create_box.php" class="btn btn-primary">Neue Box</a>

</body>
</html>