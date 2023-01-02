<html>
<head>
  <title>Rechteverwaltung</title>    
  <link rel='stylesheet' href='../css/bootstrap.min.css'>
  <link rel="stylesheet" href="../css/fontawesome.min.css">
  <link rel='stylesheet' href='../css/main.css'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <script>
    function clickAccordion(element){
		var panel = element.nextElementSibling;
		if (panel.style.display === "block") {
		  panel.style.display = "none";
		} else {
		  panel.style.display = "block";
		}
	}
  </script>
</head>
<body>
<?php
	require_once '../security.php';
	require_once '../header.php';
	require_once '../permission/permission.php';
	require_once '../db.php';
	
	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUserLogin(), 'USER_ADMIN')){
		include '../access_denied.html';
		exit();
	}
	
	$user_permissions = [];
	$module_rights = [];
	$sql = 'SELECT module, name FROM rights';
	if($result = mysqli_query($link, $sql)){
		while($row = mysqli_fetch_array($result)){
			if( !array_key_exists($row['module'], $module_rights)){
				$module_rights[$row['module']] = [];
			}
			array_push( $module_rights[$row['module']], $row['name']);
		}
		mysqli_free_result($result);
	}
	$sql = 'SELECT id, name, login, mailaddress FROM users';
	$users = [];
	if($result = mysqli_query($link, $sql)){
		while($row = mysqli_fetch_array($result)){
			$users[$row['id']] = $row;
			$user_permissions[$row['id']] = [];
			foreach($module_rights as $module=>$module_names){
				if( !array_key_exists($module, $user_permissions[$row['id']])){
					$user_permissions[$row['id']][$module] = [];
					foreach($module_names as $module_key=>$module_name){
						$user_permissions[$row['id']][$module][$module_name] = false;
					}
				}
			}
		}
		mysqli_free_result($result);
	}
	
	$sql = 'SELECT id_user, rights.module, rights.name FROM permissions LEFT JOIN rights ON rights.id=permissions.id_right';
	if($result = mysqli_query($link, $sql)){
		while($row = mysqli_fetch_array($result)){
			$user_permissions[$row['id_user']][$row['module']][$row['name']] = true;
		}
		mysqli_free_result($result);
	}

	if($_SERVER["REQUEST_METHOD"] == "POST"){
		foreach($users as $user_id=>$user){
			foreach($module_rights as $module=>$rights){
				foreach($rights as $idx=>$right){
					$inputkey = $user_id . '#*#' . $module . '#*#' . str_replace(' ', '_', $right);
					if(array_key_exists($inputkey, $_POST) && $user_permissions[$user_id][$module][$right] == false){
						$permission->createPermission($link, $user_id, $module, $right);
					}else if(!array_key_exists($inputkey, $_POST) && $user_permissions[$user_id][$module][$right] == true){
						$permission->deletePermission($link, $user_id, $module, $right);
					}
				}
			}
		}
		header("location: " . $_SERVER["PHP_SELF"]);
	}
?>
<div class='container-fluid'>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>	
			<div class='page-header'>
				<h2>Benutzerverwaltung</h2>
			</div>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<?php
			foreach($users as $user_id=>$user){
				echo '<div class="accordion" onClick="clickAccordion(this)">'
					. '<span class="fa fa-chevron-down"></span>'
					. $user['name'] . ' (' . $user['login'] . ')</div>';
				echo '<div class="panel">';
				echo '<h4>Benutzer Informationen</h4>';
				echo '<div class="row">';
				
					echo '<div class="col-md-4">';
					echo '<p><b>ID: </b>' . $user_id . '</p>';
					echo '</div>';
					echo '<div class="col-md-4">';
					echo '<p><b>Login: </b>' . $user['login'] . '</p>';
					echo '</div>';
					echo '<div class="col-md-4">';
					echo '<p><b>E-Mail: </b>' . $user['mailaddress'] . '</p>';
					echo '</div>';
					echo '<div class="col-md-4">';
					echo '<p><b>Name: </b>' . $user['name'] . '</p>';
					echo '</div>';
				
				echo '</div>';
				echo '<hr>';
				
					echo '<h4>Berechtigungen</h4>';
					foreach($module_rights as $module=>$rights){
						echo '<div class="row permission-row">';
						echo '<div class="col-xs-4">';
						echo '<p><b>' . $module . ':</b></p>';
						echo '</div>';
						echo '<div class="col-xs-5">';
							foreach($rights as $idx=>$right){
								$inputkey = $user_id . '#*#' . $module . '#*#' . $right;
								echo '<span class="permission"><input name="' .  $inputkey . '" id="' . $inputkey .'" type="checkbox" ';
								if( $user_permissions[$user_id][$module][$right] == true){
									echo 'checked';
								}
								echo '><label class="permission-label" for="' . $inputkey . '">' . $right . '</label></span>';
							}
						echo '</div>';
						echo '</div>';				
					}					
					
				echo '</br>';		
				echo '</div>';
			}
			echo '</br>';		
			echo '<div class="accordion" onClick="clickAccordion(this)">'
					. '<span class="fa fa-chevron-down"></span>'
					. 'Übersicht Brechtigungen</div>';			
			echo '<div style="overflow-x: auto;" class="panel">';
			echo '<table class="table table-bordered table-striped">';
				echo '<thead>';
					echo '<tr>';
						echo '<th>#</th>';
						echo '<th>Name</th>';
						foreach($module_rights as $module=>$rights){						
							echo '<th>' . $module . '</th>';
						}
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach($users as $user_id=>$user){
					echo '<tr>';
					echo '<td>' . $user['id'] . '</td>';
					echo '<td>' . $user['name'] . '</td>';
					
					foreach($module_rights as $module=>$rights){
						echo '<td>';
						foreach($rights as $idx=>$right){
							echo '<span class="permission-wrapper"><span class="permission fa ';
							if( $user_permissions[$user_id][$module][$right] == true){
								echo 'grant fa-check';
							}
							else{
								echo 'denied fa-xmark';
							}
							
							echo '"></span><span>' . $right . '</span></span>';
						}
						echo '</td>';
					}
					echo '</tr>';
				}
				echo '</tbody>';
			echo '</table>';

			echo '</div>';
					
			if( $permission->hasPermission($link, getCurrentUserLogin(), 'USER_ADMIN', 'EDIT')){
				echo '</br><input class="btn btn-primary" type="submit" value="Speichern" />';
			}
		?>
				
		</form>
		</div>
	</div>	
</div>
</body>

</html>