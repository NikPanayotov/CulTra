<?php

	session_start();
	
	echo json_encode(array(
		'cycle' => intval($_SESSION['TwoColourCultra']['cycle']), 
		'items_correct' => intval($_SESSION['TwoColourCultra']['items_correct']), 
		'item_total' => intval($_SESSION['TwoColourCultra']['item_total']), 
		'item_order' => intval($_SESSION['TwoColourCultra']['item_order']), 
		'training_score' => intval($_SESSION['TwoColourCultra']['training_score']),
		'pass_requirement' => $_SESSION['TwoColourCultra']['pass_requirement'],
		'tree' => $_SESSION['TwoColourCultra']['tree']
	));

?>