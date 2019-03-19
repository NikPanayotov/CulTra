<?php

	require_once 'create_new_session_for_index.php';
	require_once 'get_url_data.php';
	
	// Check if there is an existing session 
	session_start();
	if(!isset($_SESSION['TwoColourCultra']['exists'])) {
		getURLData();
		createNewSession();
	}
	
	// connect to db
	$db = new Db();

	// Check progress in database
	$sql_select = "SELECT * FROM colour_transmission_sessions WHERE session_number=" . strval($_SESSION['TwoColourCultra']['session_number']) . ";";
	$result_rows = $db->select($sql_select);
	if ($result_rows->num_rows > 0) {
		if ($row = $result_rows->fetch_assoc()) {
			$_SESSION['TwoColourCultra']['state'] = $row['progress'];
			stateMachine();
		}
	} else {
		echo "Error checking progress: " . $db->error();	
	}

	function stateTransition($current_state, $next_state) {
		if ($_SESSION['TwoColourCultra']['item_order'] > $_SESSION['TwoColourCultra']['item_total']) {
			updateState($next_state);
		} else {
			readfile($current_state);
		}	
	}
	
	function stateMachine() {
		// redirect to appropriate page based on session info
		switch($_SESSION['TwoColourCultra']['state']) {
			case "START":
				stateTransition('pages/before_training.html', 'COMPREHENSION_TRAINING');
				break;
			case "COMPREHENSION_TRAINING":
				if ($_SESSION['TwoColourCultra']['training_score'] >= $_SESSION['TwoColourCultra']['training_length_condition']) {
					stateTransition('pages/comprehension_training.html', 'AFTER_TRAINING');
				} else {
					stateTransition('pages/comprehension_training.html', 'BEFORE_PRODUCTION_TRAINING');
				}
				break;
			case "BEFORE_PRODUCTION_TRAINING":
				stateTransition('pages/before_production_training.html', 'PRODUCTION_TRAINING');
				break;
			case "PRODUCTION_TRAINING":
				if ($_SESSION['TwoColourCultra']['training_score'] >= $_SESSION['TwoColourCultra']['training_length_condition']) {
					stateTransition('pages/production_training.html', 'AFTER_TRAINING');
				} else {
					stateTransition('pages/production_training.html', 'BEFORE_COMPREHENSION_TRAINING');
				}
				break;
			case "BEFORE_COMPREHENSION_TRAINING":
				stateTransition('pages/before_comprehension_training.html', 'COMPREHENSION_TRAINING');
				break;
			case "AFTER_TRAINING":
				stateTransition('pages/after_training.html', 'TESTING');
				break;
			case "TESTING":
				stateTransition('pages/testing.html', 'BEFORE_SELF_TESTING');
				break;
			case "BEFORE_SELF_TESTING":
				stateTransition('pages/before_self_testing.html', 'SELF_TESTING');
				break;
			case "SELF_TESTING":
				stateTransition('pages/self_testing.html', 'QUESTIONNAIRE');
				break;
			case "QUESTIONNAIRE":
				stateTransition('pages/questionnaire.html', 'END');
				break;
			case "END":
				stateTransition('pages/end.html', 'END');
				break;
			default:
				break;
		}
	}
	
	function updateState($state) {
		$_SESSION['TwoColourCultra']['item_order'] = 1;
		$_SESSION['TwoColourCultra']['item_total'] = 1;
		
		if (isset($_SESSION['TwoColourCultra']['initialised_state'])) {
			unset($_SESSION['TwoColourCultra']['initialised_state']);
		}
		
		// connect to db
		$db = new Db();
		
		$_SESSION['TwoColourCultra']['state'] = $state;
		// Update progress
		$sql_update = "UPDATE colour_transmission_sessions SET progress=" . $db->quote($_SESSION['TwoColourCultra']['state']) . " WHERE session_number=" . strval($_SESSION['TwoColourCultra']['session_number']) . ";";
		if ($db->query($sql_update) === TRUE) {
			//
			stateMachine();
		} else {
			echo "Error updating record: " . $db->error;	
		}
		
		if ($_SESSION['TwoColourCultra']['state'] === 'END') {
			ending();
		}

	}
	
	function ending() {
		// Create connection
		$db = new Db();

		// Start transaction
		$db->query("BEGIN WORK;");
		
		// Include end timestamp
		$sql_update = "UPDATE colour_transmission_sessions SET end_timestamp = current_timestamp WHERE session_number=" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . ";";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			$db->query("ROLLBACK;");
			echo "Interrupted.";
			die();
		}

		// Update to nonterminal
		$sql_update = "UPDATE colour_nodes SET node_type = 'nonterminal' WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . ";";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			$db->query("ROLLBACK;");
			echo "Interrupted.";
			die();
		}
		
		// Free the node
		$sql_update = "UPDATE colour_nodes SET status = 'free' WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . ";";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			$db->query("ROLLBACK;");
			echo "Interrupted.";
			die();
		}
	
		// Create a new node
		// if above a certain limit
		/*if ($_SESSION['cultra']['generation'] > 10) {
			// last node in chain
			$result = $db -> query("INSERT INTO colour_nodes (session_number, parent_id, generation, status, node_type, tree) VALUES (" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . "," . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . "," . $db->quote($_SESSION['TwoColourCultra']['generation']) . ","  . "'free', 'endbranch'"  . "," . $_SESSION['TwoColourCultra']['tree'] . ")");
		} else {*/
			// or continue the chain
			$result = $db -> query("INSERT INTO colour_nodes (session_number, parent_id, generation, status, node_type, tree) VALUES (" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . "," . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . "," . $db->quote($_SESSION['TwoColourCultra']['generation']) . ","  . "'free', 'terminal'"  . "," . $_SESSION['TwoColourCultra']['tree'] . ")");
		//}

		$sql_id = $db->getInsertId();

		// Update session to say correct node id
		$sql_update = "UPDATE colour_transmission_sessions SET node_id = ". $db->quote($sql_id) . "WHERE session_number=" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . ";";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			$db->query("ROLLBACK;");
			echo "Interrupted.";
			die();
		}
		
		// Update transmissions to say correct node id
		$sql_update = "UPDATE colour_transmissions SET node_id = ". $db->quote($sql_id) . "WHERE session_number=" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . ";";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			$db->query("ROLLBACK;");
			echo "Interrupted.";
			die();
		}
		
		// Commit final changes to tree
		$db->query("COMMIT;");
		
		// Free up expired nodes
		$sql_update = "UPDATE colour_nodes SET status = 'free' WHERE expires < NOW() AND status = 'taken';";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			//
		}
		
		// create completion code
		$code = str_pad($_SESSION['TwoColourCultra']['session_number'], 5, "0", STR_PAD_LEFT);
		$seed = str_split('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
		shuffle($seed); // probably optional since array_is randomized; this may be redundant
		foreach (array_rand($seed, 5) as $k) {
			$code .= $seed[$k];
		}
		
		$sql_update = "UPDATE colour_transmission_sessions SET completion_code = '$code' WHERE session_number=" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . ";";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			echo "Error updating record: " . $db->error();	
		}
	}

?>