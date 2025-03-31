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

	$sql = "INSERT INTO cigarette_production (id_tabacco_box, quantity, started_at, finished_at) VALUES (?, ?, ?, ?)";
	if($stmt = mysqli_prepare($link, $sql)){
		mysqli_stmt_bind_param($stmt, "iiss", $param_tabacco_box, $param_quantity, $param_started, $param_finished);
		$param_tabacco_box = $_GET["id_box"];
		$param_quantity = $_POST['quantity'];
		$param_started = $_POST['begin'];
		$param_finished = $_POST['end'];
		if(mysqli_stmt_execute($stmt)){        
			header("location: overview.php");
			exit();
		} else{
			echo 'Something went wrong. Please try again later.' . mysqli_error($link);
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
  
  <script>
	var interval;
	function start() {
		const now = new Date(Date.now());
		document.getElementById('start-input').value = formatDateTimeForDB(now);
		document.getElementById('start-display').value = formatDateTimeForView(now);
		document.getElementById('start-button').disabled = true;
		//document.getElementById('quantity-input').readOnly = true;
		document.getElementById('stop-button').disabled = false;
		
		interval = setInterval(function() {
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
		}, 500);
	}
	function formatDateTimeForView( value){
		const localTime = new Date(value.getTime());
		const day = String(localTime.getDate()).padStart(2, '0');
		const month = String(localTime.getMonth() + 1).padStart(2, '0');
		const year = localTime.getFullYear();
		const hours = String(localTime.getHours()).padStart(2, '0');
		const minutes = String(localTime.getMinutes()).padStart(2, '0');
		const seconds = String(localTime.getSeconds()).padStart(2, '0');

		return `${day}.${month}.${year} ${hours}:${minutes}:${seconds}`;
	}
	function formatDateTimeForDB( value){
		const localTime = new Date(value.getTime());
		const day = String(localTime.getDate()).padStart(2, '0');
		const month = String(localTime.getMonth() + 1).padStart(2, '0');
		const year = localTime.getFullYear();
		const hours = String(localTime.getHours()).padStart(2, '0');
		const minutes = String(localTime.getMinutes()).padStart(2, '0');
		const seconds = String(localTime.getSeconds()).padStart(2, '0');

		return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
	}
	function stop() {
		const now = new Date(Date.now());
		document.getElementById('end-input').value = formatDateTimeForDB(now);
		document.getElementById('end-display').value = formatDateTimeForView(now);
		document.getElementById('stop-button').disabled = true;
		document.getElementById('save-button').disabled = false;
		clearInterval(interval);
	}
	//function onChangeQuantity(){
	//document.getElementById('start-button').disabled = !(document.getElementById('quantity-input').value > 0);
	//}
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
			
			<?php
				echo '<form action="';
				echo htmlspecialchars($_SERVER["PHP_SELF"]);
				echo '?id_box=' . $_GET["id_box"];
				echo '" method="post">';
			?>
				<div class="form-group">
					<label for="quantity">Zigaretten</label>
					<input id="quantity-input" onkeyup="onChangeQuantity();" type="number" name="quantity" class="form-control" value="" required>
				</div>				
				
				<div class="form-group">
					<label for="begin-display">Start</label>
					<input id="start-display" type="text" name="begin-display" class="form-control" value="" readOnly>
				</div>
				<div class="form-group">
					<label for="end-display">Ende</label>
					<input id="end-display" type="text" name="end-display" class="form-control" value="" readOnly>
				</div>
				
				<div class="form-group" style="display: none">
					<input id="start-input" type="text" name="begin" class="form-control" value="" readOnly>
					<input id="end-input" type="text" name="end" class="form-control" value="" readOnly>
				</div>
				
				<div class="form-group row" style="margin-top: 10px; text-align: center">
					<div class="col-3">
						<input id="start-button" onclick="start();" class="btn btn-primary" type="button" value="Start">
					</div>
					<div id="duration" class="col-6">
						00:00:00
					</div>
					<div class="col-3">
						<input id="stop-button" onclick="stop();" class="btn btn-primary" type="button" value="Stop" disabled>
					</div>
                </div>
				
				<hr />
				
				<div class="form-group" style="margin-top: 10px;" align="center">
                    <input id="save-button" class="btn btn-primary" type="submit" value="Speichern" disabled>
                    <a href="overview.php" class="btn btn-default" onclick="return confirm('Produktion wirklich abbrechen?')">Abbrechen</a>
                </div>
			</form> 
		</div>
	</div>
</div>
</body>
</html>
