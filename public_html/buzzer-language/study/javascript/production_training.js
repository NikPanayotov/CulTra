var pattern = '';
var max = 20;
var heardWord = false;
var playingSound = false;
var soundToPlay = "";
var functionEnd = null;

window.onload = function() {
	// load buttons in correct order
	$.get("php/generate_start_buttons.php" , function(returnedHTML) {
		document.getElementById("startButtonsContainer").innerHTML = 
			"<p>Press the buttons below to hear the buzzer sounds before you start.</p>" + String(returnedHTML);
	});
	$.get("php/generate_buttons.php" , function(returnedHTML) {
		document.getElementById("buttonsContainer").innerHTML = String(returnedHTML);
		
		$.get("php/production_training.php" , function(json) { 
			//console.log(json);
			var data = JSON.parse(json);
			
			if (data.location == 'end') {
				window.location.reload();
			} else {
				// get new image
				document.getElementById('image').src = data.location;
				// resize image as in comprehension task
				var w = window.innerWidth*0.7 > 940 ? window.innerWidth*0.7 : 940;
				var h = window.innerHeight*0.6 > 320 ? window.innerHeight*0.6 : 320;
				var side = (w > h ? w : h)/6;
				document.getElementById("image").width = side;
				document.getElementById("image").height = side;
				// reset pattern
				pattern = '';
				// enable buttons except next
				document.getElementById('lowButton').disabled = false;
				document.getElementById('highButton').disabled = false;
				document.getElementById('nextButton').disabled = true;
				
				updateProgressDisplay();
			}
		});
	});

	$("#dialog").slideUp();
};

window.onresize = function() {
	// resize image as in comprehension task
	var w = window.innerWidth*0.7 > 940 ? window.innerWidth*0.7 : 940;
	var h = window.innerHeight*0.6 > 320 ? window.innerHeight*0.6 : 320;
	var side = (w > h ? w : h)/6;
	document.getElementById("image").width = side;
	document.getElementById("image").height = side;
}

function pressedLowButton() {
	if (pattern.length < max) {
		// disable buttons
		toggleButtons(true);
		// update pattern
		pattern += 'b';
		// enable to continue after delay
		//window.setTimeout(function() { toggleButtons(false); }, 500);
		document.getElementById('lowTone').addEventListener("ended", endedLowSound);
		// play sound
		document.getElementById('lowTone').play();
	}
}

function pressedHighButton() {
	if (pattern.length < max) {
		// disable buttons
		toggleButtons(true);
		// update pattern
		pattern += 'a';
		// enable to continue after delay
		//window.setTimeout(function() { toggleButtons(false); }, 500);
		document.getElementById('highTone').addEventListener("ended", endedHighSound);
		// play sound
		document.getElementById('highTone').play();
	}
}

function endedHighSound() {
	toggleButtons(false);
	document.getElementById('highTone').removeEventListener("ended", endedHighSound);
}

function endedLowSound() {
	toggleButtons(false);
	document.getElementById('lowTone').removeEventListener("ended", endedLowSound);
}

function pressedNextButton() {
	// send result to server
	$.get("php/production_training.php" , { input: pattern} , function(json) { 
		//console.log(json);
		var data = JSON.parse(json);
		
		/*
		if (data.duplicate) {
			document.getElementById("dialog").style.backgroundColor = "hsl(290, 100%, 90%)"
			document.getElementById("dialogText").innerHTML = "You already used this pattern before. Please try over.";
			$("#dialog").slideDown();
			setTimeout(function() { $("#dialog").slideUp(); }, 3000);
		}
		*/
		
		document.getElementById('nextButton').disabled = true;
		document.getElementById('lowButton').disabled = true;
		document.getElementById('highButton').disabled = true;
		
		if (data.answer === pattern) {
			document.getElementById("dialog").style.backgroundColor = "#cfc"
			document.getElementById("dialogText").innerHTML = "Correct!";
		} else {
			document.getElementById("dialog").style.backgroundColor = "#fcc"
			document.getElementById("dialogText").innerHTML = "Wrong!";
		}
		updateProgressDisplay(true);
		
		$("#dialog").slideDown();
		
		setTimeout(function() { 
			$("#dialog").slideUp(); 
			functionEnd = function() {
				// reset pattern
				pattern = '';
				// delay before show new word or new screen
				setTimeout(function() {
					if (data.location == 'end') {
						window.location.reload();
					} else {
						updateProgressDisplay();
						// get new image
						document.getElementById('image').src = data.location;
						// enable buttons except next
						document.getElementById('lowButton').disabled = false;
						document.getElementById('highButton').disabled = false;
					}
				}, 1000);
			}
			pressedPlayButton(data.answer);
		}, 1100);
	});
}

function toggleButtons(setting) {
	if (pattern.length < max) {
		document.getElementById('lowButton').disabled = setting;
		document.getElementById('highButton').disabled = setting;
	} else {
		document.getElementById('lowButton').disabled = true;
		document.getElementById('highButton').disabled = true;
	}
	document.getElementById('nextButton').disabled = setting;
}

function pressedPlayButton(patternToPlay/*, functionEnd*/) {
	heardWord = false;
	if (typeof patternToPlay === "undefined") {
		patternToPlay = pattern;
	}
	
	if (!playingSound) {
		playingSound = true;

		$("#playButton").prop("disabled", true);
		$("#dialog").slideUp();
		
		playingPosition = 0;
		soundToPlay = patternToPlay;
		
		playNextSound();
	}
}

function lowToneEnd() {
	//console.log("LOW");
	document.getElementById('lowTone').removeEventListener("ended", lowToneEnd, false);	// CAN YOU DO THIS?????????
	window.setTimeout(function() { playNextSound(); }, 500);
}

function highToneEnd() {
	//console.log("HIGH");
	document.getElementById('highTone').removeEventListener("ended", highToneEnd, false);	// CAN YOU DO THIS?????????
	window.setTimeout(function() { playNextSound(); }, 500);
}

function playNextSound() {
	//console.log("next");
	if (playingPosition < soundToPlay.length) {
		if (soundToPlay[playingPosition] === 'b') {
			playingPosition++;
			document.getElementById('lowTone').addEventListener("ended", lowToneEnd, false);
			document.getElementById('lowTone').play();
		} else if (soundToPlay[playingPosition] === 'a') {
			playingPosition++;
			document.getElementById('highTone').addEventListener("ended", highToneEnd, false);
			document.getElementById('highTone').play();
		}
	} else {
		heardWord = true;
		if (typeof functionEnd !== "function") {	// condition for second playthrough, do not play again
			$("#playButton").prop("disabled", false);
		} else {
			functionEnd();
			functionEnd = null;
		}
		playingSound = false;
	}
}

// update display values
function updateProgressDisplay(onlyScoreAndStars) {
	$.get("php/get_progress.php" , function(json) {
		//console.log(json);
		var data = JSON.parse(json);
		
		if (onlyScoreAndStars !== true) {
			document.getElementById('itemOrder').innerHTML = data.item_order;
		}
		document.getElementById('itemsTotal').innerHTML = data.item_total;
		document.getElementById('itemsCorrect').innerHTML = data.items_correct;
		document.getElementById('itemsRequired').innerHTML = data.pass_requirement;
		document.getElementById('scoreProgress').value = data.items_correct;
		document.getElementById('scoreProgress').max = data.pass_requirement;
		
		if (data.training_score > 0) {
			document.getElementById('starOne').src = "images/golden_star.png";
		}
		if (data.training_score > 1) {
			document.getElementById('starTwo').src = "images/golden_star.png";
		}
		
		/*if (data.cycle > 1) {
			document.getElementById('scoreView').hidden = false;
		}*/
	});
}

// -----------------------------------------------------------------------------------------------
// START BUTTONS
// -----------------------------------------------------------------------------------------------

function lowStartToneEnd() {
	document.getElementById('lowTone').removeEventListener("ended", lowStartToneEnd, false);	// CAN YOU DO THIS?????????

	if (document.getElementById('highStartButton').disabled && document.getElementById('lowStartButton').disabled) {
		setTimeout(function() {
			document.getElementById('startButtonsContainer').hidden = true;
			document.getElementById('mainSection').hidden = false;
		}, 600);
	}
}

function highStartToneEnd() {
	document.getElementById('highTone').removeEventListener("ended", highStartToneEnd, false);	// CAN YOU DO THIS?????????

	if (document.getElementById('highStartButton').disabled && document.getElementById('lowStartButton').disabled) {
		setTimeout(function() {
			document.getElementById('startButtonsContainer').hidden = true;
			document.getElementById('mainSection').hidden = false;
		}, 600);
	}
}

function pressedLowStartButton() {
	document.getElementById('lowTone').addEventListener("ended", lowStartToneEnd, false);
	document.getElementById('lowTone').play();
	document.getElementById('lowStartButton').disabled = true;
}

function pressedHighStartButton() {
	document.getElementById('highTone').addEventListener("ended", highStartToneEnd, false);
	document.getElementById('highTone').play();
	document.getElementById('highStartButton').disabled = true;
}