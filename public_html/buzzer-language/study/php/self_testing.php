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

$answer = '';
$correct = '';

// Create connection
$db = new Db();

if (!isset($_SESSION['cultra']['initialised_state'])) {
	// first item
	if (intval($_SESSION['cultra']['item_order']) === 1) {
		// reshuffle random list if going to next screen
		shuffle($_SESSION['cultra']['random_list']);
		$_SESSION['cultra']['item_total'] = 4; 
		$_SESSION['cultra']['initialised_state'] = true;	
	}
} else {
	if (isset($_SESSION['cultra']['answer'])) {
		$answer = $_SESSION['cultra']['answer'];
	}
	
	$correct = false;
	// sanitisation!!!
	if (isset($_GET['input'])) {
		if ($_GET['input'] == $answer) {
			$correct = true;
		}
	}
}

$pattern = '';
if (isset($_GET['input'])) { 
	$input = $db->sanitiseMySQL($_GET['input']);	
	$target = $answer;

	// calculate hamming edit distance
	$properties = array(	'a' => array('shape' => 'fluffy', 'size' => 'big', 'brightness' => 'light'),
							'b' => array('shape' => 'spiky', 'size' => 'big', 'brightness' => 'light'),
							'c' => array('shape' => 'fluffy', 'size' => 'small', 'brightness' => 'light'),
							'd' => array('shape' => 'spiky', 'size' => 'small', 'brightness' => 'light'),
							'e' => array('shape' => 'fluffy', 'size' => 'big', 'brightness' => 'dark'),
							'f' => array('shape' => 'spiky', 'size' => 'big', 'brightness' => 'dark'),
							'g' => array('shape' => 'fluffy', 'size' => 'small', 'brightness' => 'dark'),
							'h' => array('shape' => 'spiky', 'size' => 'small', 'brightness' => 'dark')
	);
	
	$distance = 3;
	if ($properties[$input]['shape'] === $properties[$target]['shape']) {
		$distance -= 1;
	}
	if ($properties[$input]['size'] === $properties[$target]['size']) {
		$distance -= 1;
	}
	if ($properties[$input]['brightness'] === $properties[$target]['brightness']) {
		$distance -= 1;
	}
	
	// Create a new record
	// prepare and bind
	$stmt = $db->prepare("INSERT INTO transmissions (session_number, node_id, generation, section, item_order, object, target, input, correct, edit_distance, parent_id, tree) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
	$stmt->bind_param("iiisisssidii", $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l);
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
	$l = $_SESSION['cultra']['tree'];
	// set parameters and execute
	$stmt->execute();

	$_SESSION['cultra']['item_order']++; 
}

if ($_SESSION['cultra']['item_order'] < $_SESSION['cultra']['item_total'] + 1) {

	// Attempt query
	$sql = "SELECT * FROM transmissions WHERE session_number=" . $db->quote($_SESSION['cultra']['session_number']) . " AND generation=" . $db->quote($_SESSION['cultra']['generation']) . " AND object=". $db->quote($_SESSION['cultra']['random_list'][$_SESSION['cultra']['item_order'] -1]) . " AND section='TESTING';";
	$result_rows = $db->select($sql);

	if ($result_rows->num_rows > 0) {
		if($row = $result_rows->fetch_assoc()) {
			$_SESSION['cultra']['answer'] = $row["object"];
			$pattern = $row["input"];
			echo json_encode(array('answer' => $answer, 'correct' => $correct, 'pattern' => $pattern));
		}
	} else {
		echo "no transmissions";
	}
} else {
	echo json_encode(array('answer' => $answer, 'correct' => $correct, 'pattern' => 'end'));
}


?>