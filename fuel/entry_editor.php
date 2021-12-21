<?php
require_once '../db.php';
require_once '../security.php';
require_once '../header.php';
require_once '../permission/classes/permission.php';
	
$permission = new Permission();
if( !$permission->hasPermission($link, getCurrentUser(), 'FUEL', 'EDIT')){
	include '../access_denied.html';
	exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	if(isset($_GET["id"]) && !empty($_GET["id"])){
		$sql = "UPDATE fuel_refueling SET odometer = ?, refueling_date = ?, refueled = ?, deleted = ? WHERE id = ?";
		if($stmt = mysqli_prepare($link, $sql)){
			mysqli_stmt_bind_param($stmt, "dsddd", $param_odo, $param_date, $param_refueled, $param_deleted, $param_id);
			$param_id = $_GET['id'];
			$param_date = $_POST['date'];
			$param_odo = $_POST['odo'];
			$param_refueled = $_POST['refueled'];
			$param_deleted = $_POST['deleted'];
			if(mysqli_stmt_execute($stmt)){        
				header("location: overview.php");
				exit();
			} else{
				echo 'Something went wrong. Please try again later.';
			}
			mysqli_stmt_close($stmt);
		}
	}else{
		$sql = "INSERT INTO fuel_refueling ( odometer, refueling_date, refueled) VALUES (?, ?, ?)";
		if($stmt = mysqli_prepare($link, $sql)){
			mysqli_stmt_bind_param($stmt, "dsd", $param_odo, $param_date, $param_refueled);
			$param_date = $_POST['date'];
			$param_odo = $_POST['odo'];
			$param_refueled = $_POST['refueled'];
			if(mysqli_stmt_execute($stmt)){        
				header("location: overview.php");
				exit();
			} else{
				echo 'Something went wrong. Please try again later.';
			}
			mysqli_stmt_close($stmt);
		}
	}

}


if(isset($_GET["id"]) && !empty($_GET["id"])){
	$sql = "SELECT id, refueling_date, odometer, refueled, deleted FROM fuel_refueling WHERE id = " . $_GET["id"];
	if($result = mysqli_query($link, $sql)){
		if(mysqli_num_rows($result) > 0){
			$row = mysqli_fetch_array($result);
			$id = $row['id'];
			$date = $row['refueling_date'];
			$odo = $row['odometer'];
			$refueled = $row['refueled']; 
			$deleted = $row['deleted'];
		} else{
			echo "<p class='lead'><em>Keine Daten gefunden</em></p>";
		}
	} else{
		echo "ERROR: Could not able to execute $sql. " . mysqli_error($link);
	}
	
}else{
	$date = date('Y-m-d');
	$deleted = '0';
}
?>

<html> 
<head>
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/main.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head> 
<body>
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="row-column col-md-4">	
			<div class="page-header">
			<?php
				if( isset($_GET["id"]) && !empty($_GET["id"])){
					echo '<h2>Eintrag bearbeiten</h2>';						
				}else{
					echo '<h2>Neuer Eintrag</h2>';
				}
			?>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="row-column col-md-4">
				<?php 
				echo '<form action="';
					echo htmlspecialchars($_SERVER["PHP_SELF"]);
					if( isset($_GET["id"]) && !empty($_GET["id"])){
						echo '?id=' . $_GET["id"];
					}
				echo '"method="post">';
				?>
				
				<div class="form-group">
					<label for="date">Datum</label>
					<input type="date" name="date" class="form-control" value="<?php echo $date;?>" required="true">
				</div>
				<div class="form-group">
					<label for="odo">km-Stand</label>
					<input type="number" name="odo" class="form-control" 
						value="<?php echo $odo ?>" step=".1" required="true">
				</div>
				<div class="form-group">
					<label for="refueled">getankt</label>
					<input type="number" name="refueled" class="form-control" 
						value="<?php echo $refueled ?>" step=".01" required="true">
				</div>
				<div <?php if( !isset($_GET["id"]) || empty($_GET["id"])){ echo 'style="display: none"';} ?> class="form-group">
					<label for="deleted">Status</label>
					<select value="0" class="form-control" name="deleted" required="true">
						<option value="0" <?php if( $deleted == 0) { echo 'selected';} ?> >aktiv</option>
						<option value="1" <?php if( $deleted == 1) { echo 'selected';} ?>>gelöscht</option>					
					</select>
				</div>
				<input class="btn btn-primary" type="submit" value="Speichern">
				<a href="overview.php" class="btn btn-default">Abrechen</a>
			</form>
		</div>
	</div>
</div>
</body>
</html>
