<?php
	require_once '../security.php';
	require_once '../db.php';
	require_once '../header.php';
		
	require_once '../permission/permission.php';
	
	$permission = new Permission();
	if( !$permission->hasPermissionForModule($link, getCurrentUserLogin(), 'BUDGET')){
		include '../access_denied.html';
		exit();
	}
	
	$hasEditRight = $permission->hasPermission($link, getCurrentUserLogin(), 'BUDGET', 'EDIT');
	
	$transactions = [];

	$sql = 'SELECT t.id, sa.name source_acc, ta.name target_acc, t.transaction_date, t.value, t.comment, t.input_date FROM budget_transaction t ' . 
		' LEFT JOIN budget_account sa ON sa.id = t.source_account ' . 
		' LEFT JOIN budget_account ta ON ta.id = t.target_account ORDER BY t.input_date DESC';
	if($stmt = mysqli_prepare($link, $sql)){
		try{
			mysqli_stmt_execute($stmt);
			$res = mysqli_stmt_get_result($stmt);
			while($transaction = mysqli_fetch_array($res)) {
				$transactions[] = $transaction;
			}
		} finally {
			mysqli_stmt_close($stmt);
		}
	}else{
		echo mysqli_error($link);
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
<div class='container-fluid budget'>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>	
			<div class='page-header'>
				<h2>Buchungen</h2>
			</div>
		</div>
	</div>
	<div class='row justify-content-center'>
		<div class='row-column col-md-12'>
			<table class="table table-striped budget-table"> 
				<thead>
					<tr>
						<th class="value-col">#</th>
						<th>Quellkonto</th>
						<th>Zielkonto</th>
						<th>Datum</th>
						<th class="value-col">Betrag</th>
						<th>Beschreibung</th>
						<th>Eingabe</th>
						<th>Aktion</th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach($transactions as $row ){
						echo '<tr>';
							echo '<td class="value-col">' . $row['id'] . '</td>';				
							echo '<td>' . $row['source_acc'] . '</td>';				
							echo '<td>' . $row['target_acc']. '</td>';				
							echo '<td>' . date_format(date_create($row['transaction_date']), "d.m.Y") . '</td>';				
							echo '<td class="value-col">' . number_format($row['value'], 2) . ' €</td>';				
							echo '<td>' . $row['comment'] . '</td>';				
							echo '<td>' . date_format(date_create($row['input_date']), "d.m.Y H:i:s") . '</td>';				
							echo '<td>';
							if( $hasEditRight){
								echo '<a href="buchung_delete.php?id=' . $row['id'] . '" title="Buchung löschen"><span class="fa fa-trash-can"></span></a>';
							}
							echo '</td>';				
						echo '</tr>';
					}
				?>				
				</tbody>
			 </table>
			 <?php 
				if( $hasEditRight){
					echo '<a id="add-button" href="entry_editor.php" class="btn btn-primary">Neuer Eintrag</a>';
				}
			?>
		</div>
	</div>
</div>

</body>
</html>