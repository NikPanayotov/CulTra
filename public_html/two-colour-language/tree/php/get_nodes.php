<?php

require_once 'database_connection.php';

// Create connection
$db = new Db();

// Attempt query
$sql = "SELECT * FROM colour_nodes ORDER BY generation, node_id;";
$result_rows = $db->select($sql);

if ($result_rows->num_rows > 0) {
	$nodes = array();
	
	while ($row = $result_rows->fetch_assoc()) {
		
		$nodes[] = array(	'node_id' => intval($row["node_id"]), 
							'parent' => intval($row["parent_id"]), 
							'generation' => intval($row["generation"]), 
							'sessionId' => intval($row["session_number"]), 
							'status' => $row["status"],
							'type' => $row["node_type"]);

	}
	
	echo json_encode($nodes);
} else {
	// no results
}

?>