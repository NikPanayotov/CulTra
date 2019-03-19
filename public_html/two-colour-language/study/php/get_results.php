<?php

	session_start();
	if(!isset($_SESSION['TwoColourCultra']['exists'])) {	
		// destroy cookies?

		// read time out file
		readfile("../index.html");
		die();
	}

	require_once 'database_connection.php';
    // Create connection
    $db = new Db();
	
	// Get results
	$results = "";
	$score = 0;
	// Attempt query
	$sql = "SELECT correct FROM colour_transmissions WHERE session_number=" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . " AND section='SELF_TESTING';";
	$result_rows = $db->select($sql);
	if ($result_rows->num_rows > 0) {
		while ($row = $result_rows->fetch_assoc()) {
			$score += $row["correct"];
		}
		$results = ( ($score/$result_rows->num_rows) )*100;
	} else {
		$results = 'unknown';	
	}

	if ($results < 60) {
		printf("You got %.0f%% right on the last task!", $results); 
	} else {
		printf("You got %.0f%% right on the last task!", $results); 
	}
?>