<?php

	session_start();
	if(!isset($_SESSION['cultra']['exists'])) {	
		// destroy cookies?

		// read time out file
		readfile("../index.html");
		die();
	}

	require_once 'database_connection.php';
	$db = new Db();
	
	$outreach = '';
	$enjoy = 0;
	$comment = '';
	
	if (isset($_POST["outreach"])) { $outreach = $db->sanitiseMySQL($_POST["outreach"]); }
	if (isset($_POST["enjoy"])) { $enjoy = intval($db->sanitiseMySQL($_POST["enjoy"])); }
	if (isset($_POST["comment"])) { $comment = $db->sanitiseMySQL($_POST["comment"]); }

	// do some more sanitisation
	$outreach = substr($outreach, 0, 30);
	$comment = substr($comment, 0, 500);
	if (($enjoy < 1) || ($enjoy > 5)) {
		$enjoy = 0;
	}

	// upload to database
	$sql = "UPDATE transmission_sessions SET enjoy=$enjoy, outreach=" . $db->quote($outreach) . 
	", comment=" . $db->quote($comment) . 
	" WHERE session_number=" . $db->quote($_SESSION['cultra']["session_number"]) . ";";
	if ($db->query($sql) === TRUE) {

	} else {
		// echo "Error creating record: " . $db->error();	
	}

	// if successful go to next page
	$_SESSION['cultra']['item_order']++;
		
	header("Location: ../");

?>