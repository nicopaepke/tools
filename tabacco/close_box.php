<?php
require_once '../db.php';
require_once '../security.php';
require_once '../header.php';
require_once '../permission/permission.php';
	
$permission = new Permission();
if( !$permission->hasPermissionForModule($link, getCurrentUserLogin(), 'TABACCO')){
	include '../access_denied.html';
	exit();
}

$date = date('Y-m-d');
$box = [];
if($_SERVER["REQUEST_METHOD"] == "GET"){
	$sql = "SELECT brand, started_at FROM tabacco_box WHERE id = ?";
	
	if($stmt = mysqli_prepare($link, $sql)){
		mysqli_stmt_bind_param($stmt, "i", $param_tabacco_box);
		$param_tabacco_box = $_GET["id_box"];
		try{
			mysqli_stmt_execute($stmt);
			$res = mysqli_stmt_get_result($stmt);
			$box = mysqli_fetch_array($res);
		} finally {
			mysqli_stmt_close($stmt);
		}
	}else{
		echo mysqli_error($link);
	}
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	$sql = "UPDATE tabacco_box SET finished_at = ? WHERE id = ?";
	if($stmt = mysqli_prepare($link, $sql)){
		mysqli_stmt_bind_param($stmt, "si", $param_finished, $param_id);
		$param_finished = $_POST['end'];
		$param_id = $_GET["id_box"];
		if(mysqli_stmt_execute($stmt)){        
			header("location: overview.php");
			exit();			
		} else{
			echo 'Something went wrong. Please try again later.';
		}
		mysqli_stmt_close($stmt);
	}
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
				<h2>
				<?php 
				echo "Box '" . $box['brand'] . "' vom " . date_format(date_create($box['started_at']), 'd.m.Y') . " beenden?";
				?></h2>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="row-column col-md-4">
			<?php
				echo '<form action="';
				echo htmlspecialchars($_SERVER["PHP_SELF"]);
				echo '?id_box=' . $_GET["id_box"];
				echo '" method="post">';
			?>
				<div class="form-group">
					<label for="end">Beendet</label>
					<input type="date" name="end" class="form-control" value="<?php echo $date;?>" required>
				</div>				
				<div class="form-group" style="margin-top: 10px;">
                    <input class="btn btn-primary" type="submit" value="Ja, beenden">
                    <a href="overview.php" class="btn btn-default">Abbrechen</a>
                </div>
			</form> 
		</div>
	</div>
</div>
</body>
</html>
