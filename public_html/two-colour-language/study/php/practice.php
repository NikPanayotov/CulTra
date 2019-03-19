<?php

session_start();
if(!isset($_SESSION['TwoColourCultra']['exists'])) {	
	// destroy cookies?

	// read time out file
	readfile("../index.html");
	die();
}

require_once 'database_connection.php';
require_once 'tree_management.php';

refreshSession();

// Create connection
$db = new Db();

if (isset($_GET['input']) && isset($_GET['item_order']) && isset($_GET['object']) && isset($_GET['cycle'])) {

	// sanitisation
	
	// order
	$order = intval($_GET['item_order']) - 1;
	
	// cycle
	$cycle = intval($_GET['cycle']);
	
	// object
	$object = substr($db->sanitiseMySQL($_GET['object']), 0, 1);
	$regex = "/^(a|b|c|d|e|f|g|h)$/";
	if (!preg_match($regex, $object)) {
		$object = 'x';
	}
	
	// input
	$input = $db->sanitiseMySQL($_GET['input']);
	$regex = "/^(a|b)+$/";
	if (preg_match($regex, $input)) {
		
		$target = '';
		// select target from database
		$sql = "SELECT input FROM colour_transmissions WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . " AND object=". $db->quote($object) . " AND section='TESTING';";
		$result_rows = $db->select($sql);
		if ($result_rows->num_rows > 0) {
			if($row = $result_rows->fetch_assoc()) {
				$target = $row["input"];
			}
		} else {
			
		}
		// calculate edit distance and correctness
		$distance = levenshtein($input, $target);
		// Normalize edit distance
		if ((strlen($target) <= 0) || ($distance < 0)) {
			// bug cases
			$distance = 1;
		}
		else if (strlen($input) > strlen($target))
			$distance /= strlen($input);
		else
			$distance /= strlen($target);
		// Determine if completely correct
		$correct = 0;
		if ($distance == 0) {
			$correct = 1;
		}
		
		// Create a new record
		// prepare and bind
		$stmt = $db->prepare("INSERT INTO colour_transmissions (session_number, node_id, generation, section, item_order, object, target, input, correct, edit_distance, parent_id, cycle, tree) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$stmt->bind_param("iiisisssidiii", $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m);
		$a = $_SESSION['TwoColourCultra']['session_number'];
		$b = 0;
		$c = intval($_SESSION['TwoColourCultra']['generation']);
		
		// !!!
		
		$d = 'PRACTICE'; 
		
		// !!!
		
		$e = $order;
		$f = $object;
		$g = $target;
		$h = $input;
		$i = $correct;
		$j = $distance;
		$k = intval($_SESSION['TwoColourCultra']['parent_id']);
		$l = $cycle;
		$m = $_SESSION['TwoColourCultra']['tree'];
		// set parameters and execute
		$stmt->execute();

		echo json_encode(array('correct' => $correct, 'cycle' => $_SESSION['TwoColourCultra']['cycle'], 'items_correct' => intval($_SESSION['TwoColourCultra']['items_correct']), 'item_total' => intval($_SESSION['TwoColourCultra']['item_total']), 'item_order' => intval($_SESSION['TwoColourCultra']['item_order']), 'object' => $object, 'required' => $_SESSION['TwoColourCultra']['pass_requirement']));
		
	} else {
		// If preg_match() returns false, then the regex does not
		// match the string
			
	}
}

?>