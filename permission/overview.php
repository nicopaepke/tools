<html>
<head>
  <title>Rechteverwaltung</title>    
  <link rel='stylesheet' href='../css/bootstrap.min.css'>
  <link rel='stylesheet' href='../css/main.css'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
</head>
<body>
<?php
	require_once '../security.php';
	require_once 'classes/permissions.php';
	require_once '../db.php';
	
	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUser(), 'PERMISSION_ADMIN')){
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
	$sql = 'SELECT id, name, login FROM users';
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
					$inputkey = $user_id . '#*#' . $module . '#*#' . $right;
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
				<h2>Berechtigungen</h2>
			</div>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>	
		<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
		<?php

			echo '<table class="table table-bordered table-striped">';
			echo '<thead>';
				echo '<tr>';
					echo '<th>#</th>';
					echo '<th>Name</th>';
					echo '<th>Login</th>';
					foreach($module_rights as $module=>$rights){						
						echo '<th >' . $module . '</th>';
					}
				echo '</tr>';

			echo '</thead>';
			echo '<tbody>';
			
			foreach($users as $user_id=>$user){
				echo '<tr>';
					echo '<td>' . $user['id'] . '</td>';
					echo '<td>' . $user['name'] . '</td>';
					echo '<td>' . $user['login'] . '</td>';
					foreach($module_rights as $module=>$rights){
						echo '<td>';
						foreach($rights as $idx=>$right){
							$inputkey = $user_id . '#*#' . $module . '#*#' . $right;
							echo '<input class="permission-checkbox" name="' .  $inputkey . '" id="' . $inputkey .'" type="checkbox" ';
							if( $user_permissions[$user_id][$module][$right] == true){
								echo 'checked';
							}
							echo'><label class="permission-checkbox-label" for="' . $inputkey . '">' . $right . '</label>';
						}
						echo '</td>';
					}
					
				echo '</tr>';
			}
			echo '</tbody>';                            
			echo '</table>';
			if( $permission->hasPermission($link, getCurrentUser(), 'PERMISSION_ADMIN', 'EDIT')){
				echo '<input class="btn btn-primary" type="submit" value="Save" />';
			}
		?>
		</form>
		</div>
	</div>
	
</div>
</body>
</html>