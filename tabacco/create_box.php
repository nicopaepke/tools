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

if($_SERVER["REQUEST_METHOD"] == "POST"){

	$sql = "INSERT INTO tabacco_box (brand, contents, expected_cigarettes, price, started_at) VALUES (?, ?, ?, ?, ?)";
	if($stmt = mysqli_prepare($link, $sql)){
		mysqli_stmt_bind_param($stmt, "siids", $param_brand, $param_contents, $param_expected, $param_price, $param_started);
		$param_brand = $_POST['brand'];
		$param_contents = $_POST['contents'];
		$param_expected = $_POST['quantity'];
		$param_price = $_POST['price'];
		$param_started = $_POST['begin'];
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
				<h2>Neue Box</h2>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="row-column col-md-4">
			<form action="?create" method="post">
				<div class="form-group">
					<label for="brand">Hersteller</label>
					<input type="text" name="brand" class="form-control" value="" required>
				</div>
				<div class="form-group">
					<label for="price">Preis</label>
					<input type="number" step="0.01" name="price" class="form-control" value="" required>
				</div>
				<div class="form-group">
					<label for="contents">Inhalt (g)</label>
					<input type="number" name="contents" class="form-control" value="" required>
				</div>				
				<div class="form-group">
					<label for="quantity">Zigaretten</label>
					<input type="number" name="quantity" class="form-control" value="" required>
				</div>
				<div class="form-group">
					<label for="begin">Beginn</label>
					<input type="date" name="begin" class="form-control" value="<?php echo $date;?>" required>
				</div>
				
				<div class="form-group" style="margin-top: 10px;">
                    <input class="btn btn-primary" type="submit" value="Speichern">
                    <a href="overview.php" class="btn btn-default">Abbrechen</a>
                </div>
			</form> 
		</div>
	</div>
</div>
</body>
</html>
