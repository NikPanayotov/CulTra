<?php

	session_start();
	
	echo json_encode(array(
		'cycle' => intval($_SESSION['cultra']['cycle']), 
		'items_correct' => intval($_SESSION['cultra']['items_correct']), 
		'item_total' => intval($_SESSION['cultra']['item_total']), 
		'item_order' => intval($_SESSION['cultra']['item_order']), 
		'training_score' => intval($_SESSION['cultra']['training_score']),
		'pass_requirement' => $_SESSION['cultra']['pass_requirement']
	));

?>