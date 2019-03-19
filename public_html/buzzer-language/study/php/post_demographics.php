<?php

	// Post input from demographics questionnaire to server database

	session_start();
	if(!isset($_SESSION['cultra']['exists'])) {
		die();	
	}

	require_once 'database_connection.php';
	$db = new Db();

	// Gether Prolific ID explicitly
	if(isset($_POST["prolific_id"])) {
		$prolific_id = $db->sanitiseMySQL($_POST["prolific_id"]);
		
		if ($prolific_id != '') {
			$sql = "UPDATE transmission_sessions SET prolific_id=" .  $db->quote($prolific_id) . " WHERE session_number=" . $db->quote($_SESSION['cultra']["session_number"]) . ";";
			if ($db->query($sql) === TRUE) {

			} else {
				//echo "Error creating record: " . $db->error;	
			}
		}
	}
	
	// go to next page
	$_SESSION['cultra']['item_order']++;

	header("Location: ../");

?>