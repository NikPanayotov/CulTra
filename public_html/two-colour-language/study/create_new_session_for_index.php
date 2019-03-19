<?php

	// Save the POST-ed file to the server
	require_once 'database_connection_for_index.php';
	require_once 'php/tree_management.php';

	// Create New Session in Database
	function createNewSession() {
		$db = new Db();
		
		// Get IP address, referer and browser info; from: http://daipratt.co.uk/mysql-store-ip-address/
		$ip = "''";
		// test if it is a shared client
		/*
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){
			$ip = $db->quote($_SERVER['HTTP_CLIENT_IP']);
		// is it a proxy address
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$ip = $db->quote($_SERVER['HTTP_X_FORWARDED_FOR']);
		} else {
			$ip = $db->quote($_SERVER['REMOTE_ADDR']);
		}
		*/
		// get browser and referer
		$agent = $db->quote($_SERVER['HTTP_USER_AGENT']);
		$referer = "''";
		/*
		if (isset($_SERVER['HTTP_REFERER'])) {
			$referer = $db->quote($_SERVER['HTTP_REFERER']);
		}
		*/
	
		// free up expired colour_nodes
		$sql_update = "UPDATE colour_nodes SET status = 'free' WHERE expires < NOW() AND status = 'taken';";
		if ($db->query($sql_update) === TRUE) {
			//
		} else {
			//echo "Error updating record: " . $db->error();	
		}
		
		$preset_node_sql = "";
		// if node preset check if it exists
		if (isset($_SESSION['TwoColourCultra']["link_node"])) {	

			// and if node id exists
			$sql = "SELECT * FROM colour_nodes WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']["link_node"]) . " ;";
			$result_rows = $db->query($sql);
			if ($result_rows->num_rows > 0) {
				$preset_node_sql = $sql;
			}
		}
		
		// if node preset
		if ($preset_node_sql !== "") {
			// use that node
			getNode($db, $preset_node_sql);
		} else {		
			// Get node based on status and type
			$sql = '';
			$condition = '';
			if (rand(1, 100) <= 0) {
				$condition = 'nonterminal';
				$sql = "SELECT * FROM colour_nodes WHERE status=" . $db->quote('free') . 
												" AND node_type=" . $db->quote('nonterminal') . 
												" ORDER BY generation LIMIT 1 FOR UPDATE;";
			} else {
				$condition = 'terminal';
				$sql = "SELECT * FROM colour_nodes WHERE status=" . $db->quote('free') . 
												" AND node_type=" . $db->quote('terminal') . 
												" ORDER BY generation LIMIT 1 FOR UPDATE;";	
			}
			/*
			if (!getNode($db, $sql)) {
				// nothing free
				if ($condition === 'nonterminal') {	
					$sql = "SELECT * FROM colour_nodes WHERE node_type=" . $db->quote('nonterminal') . 
												" ORDER BY generation LIMIT 1 FOR UPDATE;";
				} else {
					$sql = "SELECT * FROM colour_nodes WHERE node_type=" . $db->quote('terminal') . 
													" ORDER BY generation LIMIT 1 FOR UPDATE;";	
				}
			} else {
				if (!getNode($db, $sql)) {
				// no terminal or non-terminal
				//$sql = "SELECT * FROM colour_nodes WHERE 1=1 ORDER BY generation LIMIT 1 FOR UPDATE;";
				// any terminal
				$sql = "SELECT * FROM colour_nodes WHERE node_type=" . $db->quote('terminal') . " ORDER BY generation LIMIT 1 FOR UPDATE;";
				getNode($db, $sql);
			}
				
			}
			*/
			if (!getNode($db, $sql)) {
				// any terminal
				//$sql = "SELECT * FROM colour_nodes WHERE node_type=" . $db->quote('terminal') . " ORDER BY generation LIMIT 1 FOR UPDATE;";
				//getNode($db, $sql);
				// show message
				// ...
				echo "Server busy or there are no available places left. Please refresh page in a few minutes to try again.";
				die();
			}
		}
		
		// Create a new record of the session
		// insert the values into the database
		$result = $db -> query("INSERT INTO colour_transmission_sessions (session_number, node_id, parent_id, generation, progress, browser, tree)" . 
								"VALUES (NULL," . '0' . "," . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . "," . $db->quote($_SESSION['TwoColourCultra']['generation']) . "," . $db->quote($_SESSION['TwoColourCultra']['state']) . "," . $agent . "," . $_SESSION['TwoColourCultra']['tree'] . ");");
		
		if ($result === TRUE) {
			// get the new session's ID		
			$sql_id = $db->getInsertId();

			// set participant ID to database session ID
			$_SESSION['TwoColourCultra']['session_number'] = $sql_id;
			
			// counterbalance button position
			$_SESSION['TwoColourCultra']['buttons'] = 'rightHigh';
			if ($_SESSION['TwoColourCultra']['session_number']%2 == 0) {
				$_SESSION['TwoColourCultra']['buttons'] = 'leftHigh';
			}
			
			// include this in session
			$buttons = $_SESSION['TwoColourCultra']['buttons'];
			$sql_update = "UPDATE colour_transmission_sessions SET buttons = '$buttons' WHERE session_number=" . $db->quote($_SESSION['TwoColourCultra']['session_number']) . ";";
			if ($db->query($sql_update) === TRUE) {
				//
			} else {
				$db->query("ROLLBACK;");
				echo "Interrupted.";
				die();
			}
			
			// Prolific ID
			if (isset($_SESSION['TwoColourCultra']["prolific_id"])) {
				$sql_update = "UPDATE colour_transmission_sessions SET prolific_id=" . $db->quote($_SESSION['TwoColourCultra']["prolific_id"]) . " WHERE session_number=" . strval($_SESSION['TwoColourCultra']['session_number']) . ";";
				if ($db->query($sql_update) === TRUE) {
					// Successfully updated
				} else {
					$db->query("ROLLBACK;");
					echo "Interrupted.";
					die();
				}
			}
			// Prolific Session
			if (isset($_SESSION['TwoColourCultra']["prolific_session"])) {
				$sql_update = "UPDATE colour_transmission_sessions SET prolific_session=" . $db->quote($_SESSION['TwoColourCultra']["prolific_session"]) . " WHERE session_number=" . strval($_SESSION['TwoColourCultra']['session_number']) . ";";
				if ($db->query($sql_update) === TRUE) {
					// Successfully updated
				} else {
					$db->query("ROLLBACK;");
					echo "Interrupted.";
					die();	
				}
			}
			
			// start session item from 1
			$_SESSION['TwoColourCultra']['item_order'] = 1;
			$_SESSION['TwoColourCultra']['item_total'] = 1;
			
			$_SESSION['TwoColourCultra']['items_correct'] = 0;
			
			$_SESSION['TwoColourCultra']['cycle'] = 0;
			
			// random list
			$_SESSION['TwoColourCultra']['random_list'] = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h');
			shuffle($_SESSION['TwoColourCultra']['random_list']);
			
			// session created so it exists now
			$_SESSION['TwoColourCultra']['exists'] = true;
			
			$db->query("COMMIT;");
		} else {
			$db->query("ROLLBACK;");
			echo "Interrupted.";
			die();
		}

	}
	
	function getNode($db, $sql) {
		$db->query("BEGIN WORK;");

		// get info from node
		$result_rows = $db->query($sql);
		if ($result_rows->num_rows > 0) {
			if ($row = $result_rows->fetch_assoc()) {
				// set initial state
				$_SESSION['TwoColourCultra']['state'] = 'START';
				
				// get properties
				$_SESSION['TwoColourCultra']['tree'] = $row["tree"];
				$_SESSION['TwoColourCultra']['parent_id'] = $row["node_id"];
				$_SESSION['TwoColourCultra']['generation'] = $row["generation"] + 1;
				
				// determine conditions based on tree
				$_SESSION['TwoColourCultra']['pass_requirement'] = 5;
				if ($_SESSION['TwoColourCultra']['tree'] <= 6) {
					$_SESSION['TwoColourCultra']['pass_requirement'] = 3;
				} else {
					$_SESSION['TwoColourCultra']['pass_requirement'] = 3;
				}
				$_SESSION['TwoColourCultra']['training_score'] = 0;
				$_SESSION['TwoColourCultra']['training_length_condition'] = 2;

				// update expires
				$sql_update = "UPDATE colour_nodes SET expires = DATE_ADD(NOW(), INTERVAL 5 MINUTE) WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . ";";
				if ($db->query($sql_update) === TRUE) {
					//
				} else {
					$db->query("ROLLBACK;");
					echo "Interrupted.";
					die();
				}
				
				// update to taken
				$sql_update = "UPDATE colour_nodes SET status = 'taken' WHERE node_id=" . $db->quote($_SESSION['TwoColourCultra']['parent_id']) . ";";
				if ($db->query($sql_update) === TRUE) {
					//
				} else {
					$db->query("ROLLBACK;");
					echo "Interrupted.";
					die();
				}
				
				return true;
			}
		} else {
			$db->query("ROLLBACK;");
			// nothing free
			return false;
			/*
			echo 'No free colour_nodes are available.';
			$db->query("ROLLBACK;");
			echo "Interrupted.";
			die();
			*/
		}
	}

?>