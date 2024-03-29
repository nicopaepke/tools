<?php 
	include '../config.php';
	session_start();
	if(isset($_GET['login'])) {
		require_once "../db.php";
		
		$username = $_POST['username'];
		$password = $_POST['password'];    
			
		$sql = "SELECT login, password, id FROM users WHERE LOWER(login) = LOWER(?) OR LOWER(mailaddress) = LOWER(?)";
		$stmt = mysqli_prepare($link, $sql);
		try{
			mysqli_stmt_bind_param($stmt, "ss", $username, $username);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_bind_result($stmt, $db_login, $db_pass, $db_id);
			while(mysqli_stmt_fetch($stmt)) {
			   if( password_verify($password, $db_pass)){
					$_SESSION['user']['login'] = $db_login;
					$_SESSION['user']['id'] = $db_id;
					header("location: ../index.php");
					exit();
			   }
			}
		} finally {
			mysqli_stmt_close($stmt);
		}
		$errorMessage = "Benutzername oder Passwort war ungültig<br>";
	}
	
	if(isset($_GET['logout'])) {
		if (isset($_SESSION['user'])){
			unset($_SESSION['user']);
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


<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="row-column col-md-4">	
			<div class="page-header">
					<h2>Anmeldung</h2>
			</div>
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="row-column col-md-4">	
			<?php 
			if(isset($errorMessage)) {
				echo '<p class="error-message">' . $errorMessage . '</p>';
			}
			?>
			<form action="?login" method="post">
				<div class="form-group">
					<label for="username">Benutzername oder E-Mail Adresse</label>
					<input type="text" name="username" class="form-control" 
						value="">
				</div>
				 
				<div class="form-group">
					<label for="password">Passwort</label>
					<div>
						<input id="password-field" style="display: inline-block" type="password" name="password" class="form-control" value="">
						<i id="password-toggle" style="cursor: pointer; margin-left: -30px; margin-top: 11px; position: absolute;" class="fa fa-eye-slash"></i>
					</div>
				</div>
				 
				<div class="form-group" style="margin-top: 10px;">
					<input class="btn btn-primary" type="submit" value="Login">
                </div>
			</form> 
		</div>
	</div>
	<div class="row justify-content-center">
		<div class="row-column col-md-4">
			<a href="create_user.php">Noch keinen Zugang? Hier registrieren</a>
		</div>
	</div>
</div>

</body>

<script>
const togglePassword = document.querySelector('#password-toggle');
const password = document.querySelector('#password-field');
togglePassword.addEventListener('click', function () {
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);

    this.classList.toggle('fa-eye-slash');
	this.classList.toggle('fa-eye');
});
</script>

</html>