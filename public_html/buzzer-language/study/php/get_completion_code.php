<?php

	session_start();
	if(!isset($_SESSION['cultra']['exists'])) {	
		// destroy cookies?

		// read time out file
		readfile("../index.html");
		die();
	}

	require_once 'database_connection.php';
    // Create connection
    $db = new Db();
	
	// get completion code
	$code = "";
	$sql = "SELECT completion_code FROM transmission_sessions WHERE session_number=" . $db->quote($_SESSION['cultra']['session_number']) . ";";
	$result_rows = $db->select($sql);
	if ($result_rows->num_rows > 0) {
		while ($row = $result_rows->fetch_assoc()) {
			$code = $row["completion_code"];
		}
	} else {

	}

	printf("Completion Code: " . $code . ""); 
?>