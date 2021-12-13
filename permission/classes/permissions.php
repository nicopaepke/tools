<?php
	class Permission {
		
		function createPermission($link, $user, $module, $right){
			$sql = 'INSERT INTO permissions (id_user, id_right) SELECT ?, id FROM rights WHERE module = ? AND name = ?';
			if($stmt = mysqli_prepare($link, $sql)){
				try{
					mysqli_stmt_bind_param($stmt, "sss", $user, $module, $right);
					mysqli_stmt_execute($stmt);
				} finally {
					mysqli_stmt_close($stmt);
				}
			} else{
				echo mysqli_error($link);
			}
		}
		
		function deletePermission($link, $user, $module, $right){
			$sql = 'DELETE FROM permissions WHERE id_user = ? AND id_right = (SELECT id FROM rights WHERE module = ? AND name = ?)';
			if($stmt = mysqli_prepare($link, $sql)){
				try{
					mysqli_stmt_bind_param($stmt, "sss", $user, $module, $right);
					mysqli_stmt_execute($stmt);
				} finally {
					mysqli_stmt_close($stmt);
				}
			} else{
				echo mysqli_error($link);
			}
		}
		
		function hasPermission($link, $user, $module, $right){
			$sql = "SELECT count(*) FROM users LEFT JOIN permissions ON permissions.id_user = users.id LEFT JOIN rights ON permissions.id_right = rights.id WHERE users.login = ? AND rights.module = ? and rights.name = ?";
			if($stmt = mysqli_prepare($link, $sql)){
				try{
					mysqli_stmt_bind_param($stmt, "sss", $user, $module, $right);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_bind_result($stmt, $count);
					if(mysqli_stmt_fetch($stmt)) {
						if ($count > 0){
							return true;
						}
					}
				} finally {
					mysqli_stmt_close($stmt);
				}
			}else{
				echo mysqli_error($link);
			}
			return false;	
		}
		
		function getPermissions($link, $user, $module){
			$result = [];
			$sql = "SELECT DISTINCT(rights.name) FROM users LEFT JOIN permissions ON permissions.id_user = users.id LEFT JOIN rights ON permissions.id_right = rights.id WHERE users.login = ? AND rights.module = ?";
			if($stmt = mysqli_prepare($link, $sql)){
				try{
					mysqli_stmt_bind_param($stmt, "ss", $user, $module);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_bind_result($stmt, $right_name);
					while(mysqli_stmt_fetch($stmt)){
						array_push($result, $right_name);
					}
				} finally {
					mysqli_stmt_close($stmt);
				}
			}else{
				echo mysqli_error($link);
			}
			return $result;
		}
		
		function getPermittedModules($link, $user){
			$result = [];
			$sql = "SELECT DISTINCT(rights.module) FROM users LEFT JOIN permissions ON permissions.id_user = users.id LEFT JOIN rights ON permissions.id_right = rights.id WHERE users.login = ?";
			if($stmt = mysqli_prepare($link, $sql)){
				try{
					mysqli_stmt_bind_param($stmt, "s", $user);
					mysqli_stmt_execute($stmt);
					mysqli_stmt_bind_result($stmt, $module);
					while(mysqli_stmt_fetch($stmt)){
						array_push($result, $module);
					}
				} finally {
					mysqli_stmt_close($stmt);
				}
			}else{
				echo mysqli_error($link);
			}
			return $result;
		}
		
		function hasPermissionForModule($link, $user, $module){
			$result = $this->getPermittedModules($link, $user);
			return in_array($module, $result);
		}
	}
	
?>
