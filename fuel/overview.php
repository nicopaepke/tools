<?php
	require_once '../security.php';
	require_once '../db.php';
	require_once '../header.php';
		
	require_once '../permission/classes/permission.php';
	
	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUser(), 'FUEL')){
		include '../access_denied.html';
		exit();
	}
	
	$hasEditRight = $permission->hasPermission($link, getCurrentUser(), 'FUEL', 'EDIT');
	
	if($_SERVER["REQUEST_METHOD"] == "POST" && $permission->hasPermission($link, getCurrentUser(), 'FUEL', 'EDIT')){
		$sql = "UPDATE fuel_key_values SET v = ? WHERE k = ?";
		if($stmt = mysqli_prepare($link, $sql)){
			mysqli_stmt_bind_param($stmt, "ss", $param_value, $param_key);
			$param_value = $_POST['current'];
			$param_key = 'current';
			mysqli_stmt_execute($stmt);
			
			$param_value = $_POST['capacity'];
			$param_key = 'capacity';
			mysqli_stmt_execute($stmt);
			
			$param_value = $_POST['buffer'];
			$param_key = 'buffer';
			mysqli_stmt_execute($stmt);
			
			mysqli_stmt_close($stmt);
		}
	}
		
	
	$rows = [];
	$sql = "SELECT id, refueling_date, odometer, refueled FROM fuel_refueling WHERE deleted = 0 ORDER BY refueling_date DESC";
	if($result = mysqli_query($link, $sql)){
		if(mysqli_num_rows($result) > 0){
			while($row = mysqli_fetch_array($result)){
				$rows[] = $row;
			}
		} else{
			#echo "<p class='lead'><em>Keine Daten gefunden</em></p>";
		}
	} else{
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
	}
	
	$sql = "SELECT k, v FROM fuel_key_values WHERE k = 'current'";
	if($result = mysqli_query($link, $sql)){
		if(mysqli_num_rows($result) > 0){
			$current = mysqli_fetch_array($result)['v'];
		}else{
			#echo 'insert';
		}
	}else{
		echo 'failed ' . $sql;
	}

	$sql = "SELECT k, v FROM fuel_key_values WHERE k = 'capacity'";
	if($result = mysqli_query($link, $sql)){
		if(mysqli_num_rows($result) > 0){
			$capacity = mysqli_fetch_array($result)['v'];
		}else{
			#echo 'insert';
		}
	}else{
		echo 'failed ' . $sql;
	}
	
	$sql = "SELECT k, v FROM fuel_key_values WHERE k = 'buffer'";
	if($result = mysqli_query($link, $sql)){
		if(mysqli_num_rows($result) > 0){
			$buffer = mysqli_fetch_array($result)['v'];
		}else{
			#echo 'insert';
		}
	}else{
		echo 'failed ' . $sql;
	}

	mysqli_close($link);
	
	
	//calculation
	$consumption_sum = 0.0;
	for ($i = 0; $i < count($rows) - 1; $i++) {
		$difference = $rows[$i]['odometer'] - $rows[$i+1]['odometer'];
		$consumption = round(100 * $rows[$i]['refueled'] / ($difference), 3);
		$rows[$i]['consumption'] = number_format($consumption, 3, ',', '.');
		$consumption_sum += $consumption;
	}
	$avg_consumption = NAN;
	if( count($rows) > 1){
		$avg_consumption = round($consumption_sum / (count($rows) - 1), 3);
	}
	if( count($rows) > 0){
		$rows[count($rows) - 1]['consumption'] = '-';
	}
	$refuel_at = NAN;
	$refuel_in = NAN;
	if( !is_nan($avg_consumption)){
		$refuel_at = $rows[0]['odometer'] + $capacity / $avg_consumption * 100 - $buffer;
		$refuel_in = $refuel_at - $current;
	}
	
?>
<html>

<head>
  <title>Login</title>    
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/main.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>
<div class='container-fluid fuel'>
	<div class='row justify-content-center'>
		<div class='row-column col-md-4'>	
			<div class='page-header'>
				<h2>Kraftstoff</h2>
			</div>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class='row-column col-md-4'>	
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
				<table class="table table-striped"> 
					<tbody>
						<tr>
							<td class="fuel-label-col">Aktuell</td>
							<td class="fuel-value-col">
								<?php 
									echo '<input class="fuel-input" step=".1" type="number" name="current" value="' . $current . '">';
								?>					
							</td>
							<td class="fuel-unti-col">km</td>
						</tr>
						<tr>
							<td class="fuel-label-col">tanken bei</td>
							<td class="fuel-value-col">
							<?php 
								echo number_format($refuel_at, 0, ',', '.');
							?>
							</td>
							<td class="fuel-unti-col">km</td>
						</tr>
						<tr>
							<td class="fuel-label-col">tanken in</td>
							<td class="fuel-value-col">
							<?php 
								echo number_format($refuel_in, 0, ',', '.');
							?>
							</td>
							<td class="fuel-unti-col">km</td>
						</tr>
						<tr>
							<td class="fuel-label-col">Tankinhalt</td>
							<td class="fuel-value-col">
								<?php 
									echo '<input class="fuel-input" step=".1" name="capacity" type="number" value="' . $capacity . '">';
								?>	
							</td>
							<td class="fuel-unti-col">l</td>
						</tr>
						<tr>
							<td class="fuel-label-col">Puffer</td>
							<td class="fuel-value-col">
								<?php 
									echo '<input class="fuel-input" type="number" name="buffer" value="' . $buffer . '">';
								?>	
							</td>
							<td class="fuel-unti-col">km</td>
						</tr>
						<tr>
							<td class="fuel-label-col">Verbrauch</td>
							<td class="fuel-value-col">
							<?php
								echo number_format($avg_consumption, 3, ',', '.');;
							?>
							</td>
							<td class="fuel-unti-col">l/100km</td>
						</tr>
					</tbody>
				 </table>
				<?php
					if( $hasEditRight){
						echo '<hr></hr>';
						echo '<div style="text-align: center">';
						echo '	<input id="refresh-button" class="btn btn-primary" type="submit" value="Refresh" />';
						echo '	<a id="add-button" href="entry_editor.php" class="btn btn-primary">Neuer Eintrag</a>';
						echo '</div>';
					}
				?>
				<hr></hr>
			</form>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class='row-column col-md-4'>
			<table class="table table-striped"> 
				<thead>
					<tr>
						<th>Datum</th>
						<th>km-Stand</th>
						<th>getankt</th>
						<th>Verbrauch</th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach( $rows as $row ){
						echo "<tr>";
							echo "<td>";
							echo "<a class='fuel-edit-button' href='entry_editor.php?id=". $row['id'] ."' title='bearbeiten'><span class='glyphicon glyphicon-edit'></span></a>";
							echo date_format(date_create($row['refueling_date']), "d.m.Y") . "</td>";
							echo "<td>" . number_format($row['odometer'], 1, ',', '.') . "</td>";
							echo "<td>" . number_format($row['refueled'], 2, ',', '.') . "</td>";
							echo "<td>" . $row['consumption'] . "</td>";
						echo "</tr>";
					}
				?>		
				</tbody>
			 </table>
		</div>
	</div>
</div>
			
	
</body>
</html>