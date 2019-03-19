<?php

session_start();

if (isset($_SESSION['TwoColourCultra']['buttons'])) {
	if ($_SESSION['TwoColourCultra']['buttons'] == 'rightHigh') {
		echo '<button id="lowButton" onclick="pressedLowButton();">&#9679;</button><button id="highButton" onclick="pressedHighButton();">&#9679;</button>';
	} else {
		echo '<button id="highButton" onclick="pressedHighButton();">&#9679;</button><button id="lowButton" onclick="pressedLowButton();">&#9679;</button>';
	}
}
?>