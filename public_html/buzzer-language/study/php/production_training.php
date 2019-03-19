<?php

session_start();
if(!isset($_SESSION['cultra']['exists'])) {	
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

$location = '';
$target = '';

if (!isset($_SESSION['cultra']['initialised_state'])) {
	// first item
	if (intval($_SESSION['cultra']['item_order']) === 1) {
		// reshuffle random list if going to next screen
		shuffle($_SESSION['cultra']['random_list']);
		$_SESSION['cultra']['items_correct'] = 0;
		$_SESSION['cultra']['item_total'] = 8; 
		$_SESSION['cultra']['cycle']++;
		$_SESSION['cultra']['got_star'] = false;
		$_SESSION['cultra']['initialised_state'] = true;
	}
} else {
	
	if (isset($_GET['input'])) {
		// sanitisation
		$input = $db->sanitiseMySQL($_GET['input']);
		
		$regex = "/^(a|b)+$/";
		if (preg_match($regex, $input)) {

			// select target from database
			$sql = "SELECT input FROM transmissions WHERE node_id=" . $db->quote($_SESSION['cultra']['parent_id']) . " AND object=". $db->quote($_SESSION['cultra']['random_list'][$_SESSION['cultra']['item_order'] -1]) . " AND section='TESTING';";
			$result_rows = $db->select($sql);
			if ($result_rows->num_rows > 0) {
				if ($row = $result_rows->fetch_assoc()) {
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
				$_SESSION['cultra']['items_correct']++;
			}
			
			// Create a new record
			// prepare and bind
			$stmt = $db->prepare("INSERT INTO transmissions (session_number, node_id, generation, section, item_order, object, target, input, correct, edit_distance, parent_id, cycle, tree) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
			$stmt->bind_param("iiisisssidiii", $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m);
			$a = $_SESSION['cultra']['session_number'];
			$b = 0;
			$c = intval($_SESSION['cultra']['generation']);
			$d = $_SESSION['cultra']['state']; 
			$e = $_SESSION['cultra']['item_order'];
			$f = $_SESSION['cultra']['random_list'][$_SESSION['cultra']['item_order'] -1];
			$g = $target;
			$h = $input;
			$i = $correct;
			$j = $distance;
			$k = intval($_SESSION['cultra']['parent_id']);
			$l = $_SESSION['cultra']['cycle'];
			$m = $_SESSION['cultra']['tree'];
			// set parameters and execute
			$stmt->execute();
			
			/*
			$sql_insert = "INSERT INTO transmissions (session_number, node_id, object, generation, input, item_order) VALUES (" . $db->quote($_SESSION['cultra']['session_number']) . "," . '0' . ",". $db->quote($_SESSION['cultra']['random_list'][$_SESSION['cultra']['item_order'] -1]) . "," . $db->quote($_SESSION['cultra']['generation']) . "," . $db->quote($input) . "," . $db->quote($_SESSION['cultra']['item_order']) . ");";
			if ($db->query($sql_insert) === TRUE) {

			} else {
				echo "Error creating record: " . $db->error;	
			}	
			*/
			
			$_SESSION['cultra']['item_order']++;
		} else {
			// If preg_match() returns false, then the regex does not
			// match the string
				
		}
	}
}

if (($_SESSION['cultra']['items_correct'] >= $_SESSION['cultra']['pass_requirement']) && (!$_SESSION['cultra']['got_star'])) {
	$_SESSION['cultra']['training_score']++;
	$_SESSION['cultra']['got_star'] = true;
} 

if ($_SESSION['cultra']['item_order'] < $_SESSION['cultra']['item_total'] + 1) {

	// get next 
	$sql = "SELECT * FROM transmissions WHERE node_id=" . $db->quote($_SESSION['cultra']['parent_id']) . " AND object=". $db->quote($_SESSION['cultra']['random_list'][$_SESSION['cultra']['item_order'] -1]) . " AND section='TESTING';";
	$result_rows = $db->select($sql);
	if ($result_rows->num_rows > 0) {
		if($row = $result_rows->fetch_assoc()) {
			$location = 'images/set1/' . $row["object"] . '.png';
			echo json_encode(array('location' => $location, 'answer' => $target));
		}
	} else {
		
	}
} else {
	echo json_encode(array('location' => 'end', 'answer' => $target));
}

?>