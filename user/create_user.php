<?php 
	include '../config.php';
	
	$show_register = true;
	$show_success = false;
	$error_message = '';
	
	if(isset($_GET['redirect'])) {
		header("location: ../index.php");
		exit();
	}
	if(isset($_GET['register'])) {
		require_once "../db.php";	
		$login = $_POST['username'];
		$name = $_POST['name'];
		$password = $_POST['password'];
		$mailaddress = $_POST['mailaddress'];
	
		try{
			$validated = true;
			$sql = "SELECT login FROM users WHERE LOWER(login) = LOWER(?) OR LOWER(mailaddress) = LOWER(?)";
			$stmt = mysqli_prepare($link, $sql);
			try{
				mysqli_stmt_bind_param($stmt, "ss", $login, $mailaddress);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_bind_result($stmt, $db_login);
				while(mysqli_stmt_fetch($stmt)) {
					$validated = false;
					$error_message = "Der Benutzername oder EMail Adresse ist bereits vergeben";
					break;
				}
			} finally {
				mysqli_stmt_close($stmt);
			}
			
			if( $validated){
				$sql = "INSERT INTO users (login, name, password, mailaddress) VALUES (?, ?, ?, ?)";
				try{
					if($stmt = mysqli_prepare($link, $sql)){
						$hashed_pw = password_hash($password, PASSWORD_BCRYPT);
						mysqli_stmt_bind_param($stmt, "ssss", $login, $name, $hashed_pw, $mailaddress);
						if(mysqli_stmt_execute($stmt)){
							$show_register = false;
							$show_success = true;
							
							require_once '../mail/mail.php';
							$helper = new MailHelper();
							$newAccountBody = 'Neuer Account</br>'
								. 'Login: <b>' . $login . '</b></br>'
								. 'Name: <b>' . $name . '</b></br>'
								. 'EMail Adresse: <b>' . $mailaddress . '</b></br>';
							$helper->sendMail($smtp_sender_address, 'Neuer Account', $newAccountBody);
						}
					}
					$error_message = mysqli_error($link);
				}
				finally{
					mysqli_stmt_close($stmt);
				}
			}
		}
		catch(Exception $e){
			$error_message = $e->getMessage();
		}	
	}
?>
<html>

<head>
  <title>Registrierung</title>    
  <link rel="stylesheet" href="../css/bootstrap.min.css">
  <link rel="stylesheet" href="../css/fontawesome.min.css">
  <link rel="stylesheet" href="../css/main.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <script>
	function check_pass() {
		if (document.getElementById('password-field').value == document.getElementById('confirm-password-field').value) {
			document.getElementById('submit-register').disabled = false;
			document.getElementById('confirm-password-error').setAttribute("hidden", "hidden");
		} else {
			document.getElementById('submit-register').disabled = true;
			document.getElementById('confirm-password-error').removeAttribute("hidden");
		}
	}
</script>
</head>
<body>

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="row-column col-md-4">	
			<div class="page-header">
					<h2>Registrierung</h2>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="row-column col-md-4">	
			<?php 
				if($error_message != ''){
					echo '<p class="error-message">Registierung fehlgeschlagen: ' . $error_message . '</p>';
				}
			?>				
			<form style="<?php if(!$show_success) echo "display: none";?>"
				action="?redirect" method="post">
				<p>Registrierung war erfolgreich.</p>
				<p>Sobald Sie freigeschaltet werden, bekommen Sie eine E-Mail.</p>
				<input class="btn btn-primary" type="submit" value="OK">
			</form> 
			
			<form style="<?php if(!$show_register) echo "display: none";?>"
				action="?register" method="post">
				<div class="form-group">
					<label for="username">Benutzername</label>
					<input type="text" name="username" class="form-control" value="" required>
				</div>
				<div class="form-group">
					<label for="name">Name</label>
					<input type="text" name="name" class="form-control" value="" required>
				</div>
				<div class="form-group">
					<label for="name">E-Mail Adresse</label>
					<input type="text" name="mailaddress" class="form-control" value="" required>
				</div>				 
				<div class="form-group">
					<label for="password">Passwort</label>
					<div>
						<input id="password-field" type="password" name="password" class="form-control" value="" onkeyup="check_pass();" required>
					</div>
				</div>
				<div class="form-group">
					<label for="confirm_password">Passwort wiederholen</label>
					<span hidden id="confirm-password-error">
					<span class="fa fa-triangle-exclamation"></span>
					</span>
					<div>
						<input id="confirm-password-field" type="password" name="confirm_password" class="form-control" value="" onkeyup="check_pass(); required">
					</div>
				</div>
				 
				<input id="submit-register" class="btn btn-primary" type="submit" value="Registrieren">
			</form> 
		</div>
	</div>
</div>

</body>
</html>