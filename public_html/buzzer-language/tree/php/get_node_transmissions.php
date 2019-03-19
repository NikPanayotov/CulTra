<?php

if (isset($_GET["node_id"])) {

	require_once 'database_connection.php';

	// Create connection
	$db = new Db();

	// Get input data
	$node_id = intval($db->sanitiseMySQL($_GET["node_id"]));

	if ($node_id < 1) {
		echo "Bad input.";
		die();
	}

	// Attempt query
	$sql = "SELECT * FROM transmissions WHERE node_id = $node_id ;";
	$result_rows = $db->select($sql);
	$transmissions = array();	
	if ($result_rows->num_rows > 0) {
		
		
		while ($row = $result_rows->fetch_assoc()) {
			
			$transmissions[] = array(	'object' => $row["object"], 
										'section' => $row["section"], 
										'item_order' => $row["item_order"], 
										'target' => $row["target"], 
										'input' => $row["input"],
										'correct' => $row["correct"], 
										'edit_distance' => $row["edit_distance"],
										'timestamp' => strtotime($row["timestamp"])
									);
		}
		
		echo json_encode($transmissions);
	} else {
		// no results
		echo json_encode($transmissions);
	}

} else {
	echo 'No input provided.';	
}

?>