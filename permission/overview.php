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
	
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		foreach($_POST as $key=>$value){
			echo $key . '=>' . $value;
		}
	}
	
	
	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUser(), 'PERMISSION_ADMIN')){
		include '../access_denied.html';
		exit();
	}
				
	$module_rights = [];
	$sql = 'SELECT module, name FROM rights';
	if($result = mysqli_query($link, $sql)){
		while($row = mysqli_fetch_array($result)){
			if( !array_key_exists($row['module'], $module_rights)){
				$module_rights[$row['module']] = [];
			}
			array_push( $module_rights[$row['module']], $row['name']);
		}
		/*foreach($module_rights as $key=>$value){
			echo $key . '-1</br>';
			foreach($value as $k=>$v){
				echo $k . '-2</br>';
				echo $v . '-3</br>';
			}
		}*/
	}
	$user_permissions = [];
	$sql = 'SELECT users.id, rights.module, rights.name FROM users INNER JOIN permissions ON permissions.id_user = users.id INNER JOIN rights ON permissions.id_right = rights.id';
	if($result = mysqli_query($link, $sql)){
		while($row = mysqli_fetch_array($result)){
			
			if( !array_key_exists($row['id'], $user_permissions)){
				$user_permissions[$row['id']] = [];
			}
	
			if( !array_key_exists($row['module'], $user_permissions[$row['id']])){
				$user_permissions[$row['id']][$row['module']] = [];
			}
			array_push( $user_permissions[$row['id']][$row['module']], $row['name']);
		}
		/*foreach($user_permissions as $key=>$value){
			echo $key . '-1</br>';
			foreach($value as $k=>$v){
				echo $k . '-2</br>';
				foreach($v as $x=>$y){
					//echo $x . '-3</br>';
					echo $y . '-4</br>';
				}
			}
		}*/
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
		
		/*
		
			Um die Häckchen wieder zu speichern, könnte man ein Formular submitten, in dem alle gesetzten und alle nicht gesetzten felder mitgegeben werden und dann wird ein delete bzw. ein insert aufgerufen (select vorher nicht vergessen, oder alle löschen)
		
		*/
			
			$sql = 'SELECT id, name, login FROM users';
			if($result = mysqli_query($link, $sql)){
				echo '<table class="table table-bordered table-striped">';
				echo '<thead>';
					echo '<tr>';
						echo '<th rowspan="2">#</th>';
						echo '<th rowspan="2">Name</th>';
						echo '<th rowspan="2">Login</th>';
						foreach($module_rights as $module=>$rights){
							echo '<th colspan="' . sizeof($rights) . '">' . $module . '</th>';
						}
					echo '</tr>';
					echo '<tr>';
						foreach($module_rights as $module=>$rights){
							foreach( $rights as $idx=>$right){
								echo '<th>' . $right . '</th>';
							}
						}
					echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				while($row = mysqli_fetch_array($result)){
					echo '<tr>';
						echo '<td>' . $row['id'] . '</td>';
						echo '<td>' . $row['name'] . '</td>';
						echo '<td>' . $row['login'] . '</td>';
						foreach($module_rights as $module=>$rights){
							foreach( $rights as $idx=>$right){
								$hasRight = 'nein';
								if( array_key_exists($row['id'], $user_permissions)){
									if( array_key_exists($module, $user_permissions[$row['id']])){
										if( in_array($right, $user_permissions[$row['id']][$module])){
											$hasRight = 'ja';
										}
									}
								}
								//echo '<td>' . $hasRight . '</td>';
								echo '<td>' . '<input name="' .  $row['id'] . '" type="text" value="' . $hasRight . '">' . '</td>';
							}
						}
						
					echo '</tr>';
				}
				echo '</tbody>';                            
				echo '</table>';
				mysqli_free_result($result);
			}
		?>
		<input class="btn btn-primary" type="submit" value="Save" />
		</form>
		</div>
	</div>
	
</div>
</body>
</html>