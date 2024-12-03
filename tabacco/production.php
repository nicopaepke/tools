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

if($_SERVER["REQUEST_METHOD"] == "POST"){

	/*
	$sql = "INSERT INTO tabacco_box (brand, contents, expected_cigarettes, price, started_at) VALUES (?, ?, ?, ?, ?)";
	if($stmt = mysqli_prepare($link, $sql)){
		mysqli_stmt_bind_param($stmt, "siids", $param_brand, $param_contents, $param_expected, $param_price, $param_started);
		$param_brand = $_POST['brand'];
		$param_contents = $_POST['contents'];
		$param_expected = $_POST['quantity'];
		$param_price = $_POST['price'];
		$param_started = $_POST['begin'];
		if(mysqli_stmt_execute($stmt)){        
			//header("location: overview.php");
			//exit();
		} else{
			echo 'Something went wrong. Please try again later.';
		}
		mysqli_stmt_close($stmt);
	}	
	*/
}

?>

<html> 
<head>
	<link rel="stylesheet" href="../css/bootstrap.min.css">
	<link rel="stylesheet" href="../css/main.css">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <script>
	function start() {
		document.getElementById('start-input').value = new Date(Date.now()).toISOString();
		document.getElementById('start-button').disabled = true;
		document.getElementById('quantity-input').disabled = true;
		document.getElementById('stop-button').disabled = false;
		setInterval(function() {
			var startValue = document.getElementById('start-input').value;
			var endValue = document.getElementById('end-input').value;
			
			var end;
			if( endValue){
				end = new Date(endValue);
			} else {
				end = new Date(Date.now());
			}			
			var hours = 0;
			var minutes = 0;
			var seconds = 0;
			if( startValue){
				const diff = ((end - new Date(startValue)) / 1000 / 60);
				hours = Math.floor(diff / 60);
				minutes = Math.floor((diff - (60 * hours)));
				seconds = Math.round(60 * (diff - Math.floor(diff)));
			}
			
			if( hours < 10){
				hours = "0" + hours;
			}
			if( minutes < 10){
				minutes = "0" + minutes;
			}
			if( seconds < 10){
				seconds = "0" + seconds;
			}
			document.getElementById('duration').innerHTML = hours + ":" + minutes + ":" + seconds;
			//console.log(hours + ":" + minutes + ":" + seconds);
		}, 500);
	}
	function stop() {
		document.getElementById('end-input').value = new Date(Date.now()).toISOString();
		document.getElementById('stop-button').disabled = true;
	}
	function onChangeQuantity(){
		document.getElementById('start-button').disabled = !(document.getElementById('quantity-input').value > 0);
	}
	</script>
  
</head> 
<body>
<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="row-column col-md-4">	
			<div class="page-header">
				<h2>Produktion</h2>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="row-column col-md-4">
			<form action="?create" method="post">
				<div class="form-group">
					<label for="quantity">Zigaretten</label>
					<input id="quantity-input" onkeyup="onChangeQuantity();" type="number" name="quantity" class="form-control" value="" required>
				</div>
				
				
				<div class="form-group">
					<label for="begin">Start</label>
					<input id="start-input" type="text" name="begin" class="form-control" value="" disabled>	
				</div>
				<div class="form-group">
					<label for="end">Ende</label>
					<input id="end-input" type="text" name="end" class="form-control" value="" disabled>
				</div>
				
				<div class="form-group row" style="margin-top: 10px; text-align: center">
					<div class="col-3">
						<input id="start-button" onclick="start();" class="btn btn-primary" type="button" value="Start" disabled>
					</div>
					<div class="col-6">
						<p id="duration" style="font-size: 24px">00:00:00</p>
					</div>
					<div class="col-3">
						<input id="stop-button" onclick="stop();" class="btn btn-primary" type="button" value="Stop" disabled>
					</div>
                </div>
				
				<hr />
				
				<div class="form-group" style="margin-top: 10px;" align="center">
                    <input class="btn btn-primary" type="submit" value="Speichern">
                    <a href="overview.php" class="btn btn-default">Abbrechen</a>
                </div>
			</form> 
		</div>
	</div>
</div>
</body>
</html>
