<?php
	require_once '../security.php';
	require_once '../db.php';
	require_once '../header.php';
		
	require_once '../permission/permission.php';
	
	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUserLogin(), 'FUEL')){
		include '../access_denied.html';
		exit();
	}
	
	if(isset($_GET["id_vehicle"]) && $_SERVER["REQUEST_METHOD"] == "POST"){
		if(isset($_GET["vehicle_name"]) && $permission->hasPermission($link, getCurrentUserLogin(), 'FUEL', $_GET["vehicle_name"]))
		{
			$sql = "UPDATE fuel_vehicle SET current = ?, capacity = ?, buffer = ? WHERE id = ?";
			if($stmt = mysqli_prepare($link, $sql)){
				try{
					mysqli_stmt_bind_param($stmt, "dddi", $_POST['current'], $_POST['capacity'], $_POST['buffer'], $_GET["id_vehicle"]);
					mysqli_stmt_execute($stmt);
				} finally {
					mysqli_stmt_close($stmt);
				}
			} else{
				echo mysqli_error($link);
			}
		}
	}
	
	$vehicles = [];
	$selected_vehicle = null;
	$current = 0;
	$capacity = 0;
	$buffer = 0;
	$sql = "SELECT id, name, current, capacity, buffer FROM fuel_vehicle";
	
	$permitted_vehicles = $permission->getPermissions($link, getCurrentUserLogin(), 'FUEL');
	
	if($result = mysqli_query($link, $sql)){
		if(mysqli_num_rows($result) > 0){
			while($row = mysqli_fetch_array($result)){
				if(in_array($row['name'], $permitted_vehicles)){
					$vehicles[] = $row;
					if(isset($_GET["id_vehicle"]) && $_GET["id_vehicle"] == $row['id']){
						$selected_vehicle = $row;
					}
				}
			}
		}
		mysqli_free_result($result);
	} else{
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
	}
	
	$refuelings = [];
		
	if($selected_vehicle != null){
		$current = $selected_vehicle['current'];
		$capacity = $selected_vehicle['capacity'];
		$buffer = $selected_vehicle['buffer'];

		
		$sql = "SELECT id, refueling_date, odometer, refueled FROM fuel_refueling WHERE deleted = 0 AND id_vehicle = ? ORDER BY refueling_date DESC";
		if($stmt = mysqli_prepare($link, $sql)){
			try{
				mysqli_stmt_bind_param($stmt, "i", $selected_vehicle['id']);
				mysqli_stmt_execute($stmt);
				$refuel_res = mysqli_stmt_get_result($stmt);
				while($refuel = mysqli_fetch_array($refuel_res)) {
					$refuelings[] = $refuel;
				}
			} finally {
				mysqli_stmt_close($stmt);
			}
		}else{
			echo mysqli_error($link);
		}
	}
	
	
	//calculation
	$consumption_sum = 0.0;
	$avg_consumption = NAN;
	$refuel_at = NAN;
	$refuel_in = NAN;
	if($selected_vehicle != null){
		for ($i = 0; $i < count($refuelings) - 1; $i++) {
			$difference = $refuelings[$i]['odometer'] - $refuelings[$i+1]['odometer'];
			$consumption = round(100 * $refuelings[$i]['refueled'] / ($difference), 3);
			$refuelings[$i]['consumption'] = number_format($consumption, 3, ',', '.');
			$consumption_sum += $consumption;
		}
		if( count($refuelings) > 1){
			$avg_consumption = round($consumption_sum / (count($refuelings) - 1), 3);
		}
		if( count($refuelings) > 0){
			$refuelings[count($refuelings) - 1]['consumption'] = '-';
		}
		if( !is_nan($avg_consumption)){
			$refuel_at = $refuelings[0]['odometer'] + $capacity / $avg_consumption * 100 - $buffer;
			$refuel_in = $refuel_at - $current;
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
  <script>
	function vehicleChanged(){
		var id = document.getElementById("vehicle_sector").value;
		window.location.search = "?id_vehicle=" + id;
	}
  </script>
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
			<select id="vehicle_sector" onchange="vehicleChanged()">
				<?php
					if( $selected_vehicle == null){
						echo '<option value=""></option>';
					}
					foreach($vehicles as $vehicle){
						echo '<option ';
						if( $selected_vehicle != null && $vehicle['id'] == $selected_vehicle['id']){
							echo 'selected ';
						}
						echo 'value="' . $vehicle['id'] . '">' . $vehicle['name'] . '</option>';
					}
				?>
			</select>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class='row-column col-md-4'>	
			<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?id_vehicle=' . $selected_vehicle['id'] . '&vehicle_name=' . $selected_vehicle['name']; ?>" method="post">
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
					if( $selected_vehicle != null){
						echo '<hr></hr>';
						echo '<div style="text-align: center">';
						echo '	<input id="refresh-button" class="btn btn-primary" type="submit" value="Speichern" />';
						echo '	<a id="add-button" href="entry_editor.php?id_vehicle=' . $selected_vehicle['id'] . '&vehicle_name=' . $selected_vehicle['name'];
						echo '" class="btn btn-primary">Neuer Eintrag</a>';
						echo '</div>';
					}
				?>
				<hr></hr>
			</form>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class='row-column col-md-4'>
			<table class="table table-striped refueling-table"> 
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
					foreach($refuelings as $row ){
						echo "<tr>";
							echo "<td>";
							echo "<a class='fuel-edit-button' href='entry_editor.php?id=". $row['id'];
							echo "&id_vehicle=" . $selected_vehicle['id'] . '&vehicle_name=' . $selected_vehicle['name'];
							echo "' title='bearbeiten'><span class='fa fa-pen-to-square'></span></a>";
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