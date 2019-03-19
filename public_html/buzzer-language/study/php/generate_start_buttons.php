<?php

session_start();

if (isset($_SESSION['cultra']['buttons'])) {
	if ($_SESSION['cultra']['buttons'] == 'rightHigh') {
		echo '<button id="lowStartButton" onclick="pressedLowStartButton();">&#11015;</button><button id="highStartButton" onclick="pressedHighStartButton();">&#11014;</button>';
	} else {
		echo '<button id="highStartButton" onclick="pressedHighStartButton();">&#11014;</button><button id="lowStartButton" onclick="pressedLowStartButton();">&#11015;</button>';
	}
}
?>