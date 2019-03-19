var pattern = '';
var max = 20;

// condition
fadingCondition = false;

window.onload = function() {
	getCondition();
	$.get("php/generate_buttons.php" , function(returnedHTML) {
		document.getElementById("buttonsContainer").innerHTML = String(returnedHTML);
		
		$.get("php/testing.php" , function(json) { 
			//console.log(json);
			if (json == 'end') {
				window.location.reload();
			} else {
				var data = JSON.parse(json);
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
		
		// fade in new dot
		var dot = $("<span>").css("color", "hsl(40, 100%, 50%)").html("&#9679;");
		$("#wordField").append(dot);
		
		if (fadingCondition) {
			// fade away dot
			setTimeout(function() { dot.animate({"opacity": 0});}, 500);
		}
		
		toggleButtons(false);
	}
}

function pressedHighButton() {
	if (pattern.length < max) {
		// disable buttons
		toggleButtons(true);
		// update pattern
		pattern += 'a';
		
		// fade in new dot
		var dot = $("<span>").css("color", "hsl(220, 100%, 50%)").html("&#9679;");
		$("#wordField").append(dot);
		
		if (fadingCondition) {
			// fade away dot
			setTimeout(function() { dot.animate({"opacity": 0});}, 500);
		}
		
		toggleButtons(false);
	}
}

function pressedNextButton() {
	// remove word from screen
	document.getElementById("wordField").innerHTML = "-";
	// send result to server
	$.get("php/testing.php" , { input: pattern} , function(json) { 
		//console.log(json);
		if (json == 'end') {
			window.location.reload();
		} else {
			var data = JSON.parse(json);
			
			if (data.duplicate) {
				document.getElementById("dialog").style.backgroundColor = "hsl(290, 100%, 90%)"
				document.getElementById("dialogText").innerHTML = "You already used this pattern before. Please try again.";
				$("#dialog").slideDown();
				setTimeout(function() { $("#dialog").slideUp(); }, 3000);
			}
			// get new image
			document.getElementById('image').src = data.location;
			// reset pattern
			pattern = '';
			// enable buttons except next
			document.getElementById('lowButton').disabled = false;
			document.getElementById('highButton').disabled = false;
			document.getElementById('nextButton').disabled = true;
		}
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

// get condition
function getCondition() {
	$.get("php/get_progress.php" , function(json) {
		//console.log(json);
		var data = JSON.parse(json);

		// fading condition
		if (data.tree > 6) {
			fadingCondition = true;
		}
	});
}