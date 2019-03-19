<?php

// AT EACH INTERACTION
function refreshSession() {
	require_once 'database_connection.php';

	// Create connection
	$db = new Db();
	
	// update expires
	$sql_update = "UPDATE colour_nodes SET expires = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . ";";
	if ($db->query($sql_update) === TRUE) {
		//
	} else {
		//echo "Error updating record: " . $db->error();	
	}
	
	// update status to taken again
	$sql_update = "UPDATE colour_nodes SET status = 'taken' WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . ";";
	if ($db->query($sql_update) === TRUE) {
		//
	} else {
		//echo "Error updating record: " . $db->error();	
	}
	
	// free up expired nodes
	$sql_update = "UPDATE colour_nodes SET status = 'free' WHERE expires < NOW() AND status = 'taken' AND node_id <>" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . ";";
	if ($db->query($sql_update) === TRUE) {
		//
	} else {
		//echo "Error updating record: " . $db->error();	
	}
}

?>