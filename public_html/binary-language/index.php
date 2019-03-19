<?php

	// Redirect randomly to one of the two experiments
	if (rand(0, 1) < 1) {
		header('Location: https://' . $_SERVER['HTTP_HOST'] . '/buzzer-language');
	} else {
		header('Location: https://' . $_SERVER['HTTP_HOST'] . '/two-colour-language');
	}
	exit;
	
?>