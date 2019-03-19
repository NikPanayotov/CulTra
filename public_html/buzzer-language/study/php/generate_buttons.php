<?php

session_start();

if (isset($_SESSION['cultra']['buttons'])) {
	if ($_SESSION['cultra']['buttons'] == 'rightHigh') {
		echo '<button id="lowButton" onclick="pressedLowButton();">&#11015;</button><button id="highButton" onclick="pressedHighButton();">&#11014;</button>';
	} else {
		echo '<button id="highButton" onclick="pressedHighButton();">&#11014;</button><button id="lowButton" onclick="pressedLowButton();">&#11015;</button>';
	}
}
?>