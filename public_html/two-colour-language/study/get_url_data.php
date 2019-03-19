<?php

	function getURLData() {
		
		// Get provided node id to be used as parent
		$node = 1;
		if (isset($_GET["n"])) {
			$node = intval(sanitiseString($_GET["n"]));
			if (is_int($node)) {
				if ($node > 0) {
					$_SESSION['TwoColourCultra']["link_node"] = $node;
				}
			} else {
				//echo "Bad condition input.";
				//die();
			}
		}
		
		// Get Prolific information if provided in link
		$prolific_id = "";
		if (isset($_GET["PROLIFIC_PID"])) {
			$prolific_id = sanitiseString($_GET["PROLIFIC_PID"]);
			$_SESSION['TwoColourCultra']["prolific_id"] = $prolific_id;
		}
		
		$prolific_session = "";
		if (isset($_GET["SESSION_ID"])) {
			$prolific_session= sanitiseString($_GET["SESSION_ID"]);
			$_SESSION['TwoColourCultra']["prolific_session"] = $prolific_session;
		}
		
	}

	function sanitiseString($var) {
		$var = stripslashes($var);
		$var = strip_tags($var);
		$var = htmlentities($var);
		return $var;
	}
?>