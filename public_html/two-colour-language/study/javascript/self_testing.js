// GLOBALS
var objects = [];
var heardWord = false;
var playingSound = false;
var pattern = '';
var nextPattern = '';
var babble = '';
var max = 20;
var phase = "start";
var dialogTimer;

var playingPosition = 0;
var soundToPlay = "";

var selectionState = false;
var objectsOpacity = 1;

var mainCanvas = document.getElementById("mainCanvas");
var mainContext = mainCanvas.getContext("2d");

// condition
fadingCondition = false;

window.onbeforeunload = function(e) {
	var dialogText = 'Are you sure?';

	e.returnValue = dialogText;
	return dialogText;
}

window.onload = function() {
	$.get("php/generate_buttons.php" , function(returnedHTML) {
		document.getElementById("buttonsContainer").innerHTML = String(returnedHTML);
	});
	
	mainCanvas.width = window.innerWidth*0.7 > 940 ? window.innerWidth*0.7 : 940;
	mainCanvas.height = window.innerHeight*0.6 > 320 ? window.innerHeight*0.6 : 320;
	
	mainCanvas.addEventListener("click", clicked, false);
	
	// load images
	var loadingCounter = 0;
	const numberOfResources = 8;
	var side = mainCanvas.width > mainCanvas.height ? mainCanvas.width : mainCanvas.height;
	for (let i = 0; i < numberOfResources; i++) {
		objects[i] = {};
		objects[i].img = new Image();
		objects[i].x = i*100;
		objects[i].y = 0;
		objects[i].width = side/6;
		objects[i].height = side/6;
		objects[i].selected = false;
		objects[i].img.onload = function() {
			loadingCounter++;
			if (loadingCounter >= numberOfResources) {
				// done loading
				getCondition();
				shuffle();
				requestCurrentItem();
				window.requestAnimationFrame(mainDraw);
			}
		};
		objects[i].img.src = "images/set1/" + String.fromCharCode(97 + i) + ".png";
	}
	
	$("#dialog").slideUp();
};

window.onresize = function() {
	// resize canvas
	mainCanvas.width = window.innerWidth*0.7 > 940 ? window.innerWidth*0.7 : 940;
	mainCanvas.height = window.innerHeight*0.6 > 320 ? window.innerHeight*0.6 : 320;
	
	// resize objects
	var side = mainCanvas.width > mainCanvas.height ? mainCanvas.width : mainCanvas.height;
	for (let i = 0; i < objects.length; i++) {

		objects[i].width = side/6;
		objects[i].height = side/6;
	}
	
	shuffle();
}

function mainDraw() {
	var greenCircle = false;
	var redCircle = false;
	var centerX, cemterY, radius;
	var centerX1, cemterY1, radius1;
	
	mainContext.clearRect(0, 0, mainCanvas.width, mainCanvas.height);

	if ((selectionState) && (objectsOpacity > 0)) {
		objectsOpacity -= 0.01;
		if (objectsOpacity < 0) objectsOpacity = 0;
	}
	
	// draw objects
	for (let i = 0; i < objects.length; i++) {
		mainContext.globalAlpha = objectsOpacity;
		// is it selected in some way?
		if (objects[i].selected === true) {
			// define circle
			centerX = objects[i].x + objects[i].width/2;
			centerY = objects[i].y + objects[i].height/2;
			radius = objects[i].width/2;
			greenCircle = true;
			mainContext.globalAlpha = 1;
		} else if (objects[i].selected === 'X') {
			// define cicrle
			centerX1 = objects[i].x + objects[i].width/2;
			centerY1 = objects[i].y + objects[i].height/2;
			radius1 = objects[i].width/2;
			redCircle = true;
			//mainContext.globalAlpha = 1;	// if you want to see wrong one
		}
		
		
		mainContext.drawImage(objects[i].img, objects[i].x, objects[i].y, objects[i].width, objects[i].height);
	}
	if (greenCircle) {
		mainContext.globalAlpha = 1;
		// draw circle around selected
		mainContext.beginPath();
		mainContext.arc(centerX, centerY, radius, 0, 2 * Math.PI, false);
		mainContext.fillStyle = 'rgba(0, 0, 0, 0)';
		mainContext.fill();
		mainContext.lineWidth = 8;
		mainContext.strokeStyle = '#88ff88';
		mainContext.stroke();
	}
	
	if (redCircle) {
		mainContext.globalAlpha = objectsOpacity;
		
		// draw circle around selected
		mainContext.beginPath();
		mainContext.arc(centerX1, centerY1, radius1, 0, 2 * Math.PI, false);
		mainContext.fillStyle = 'rgba(0, 0, 0, 0)';
		mainContext.fill();
		mainContext.lineWidth = 8;
		mainContext.strokeStyle = '#ff8888';
		mainContext.stroke();
		
		mainContext.globalAlpha = 1;
	}
			
	window.requestAnimationFrame(mainDraw);
}

function clicked(e) {
	var bounds = mainCanvas.getBoundingClientRect();
	//alert("h " + (e.clientX - bounds.left) + " " + (e.clientY - bounds.top));
	
	var clickX = e.clientX - bounds.left;
	var clickY = e.clientY - bounds.top;
	
	// Clicking  collision detection
	if (phase == "start") {
		if (heardWord) {
			clearTimeout(dialogTimer);
			$("#dialog").slideUp();
			clickCircleCollision(clickX, clickY);
		} else {
			document.getElementById("dialog").style.backgroundColor = "hsl(290, 100%, 90%)"
			document.getElementById("dialogText").innerHTML = "Too soon! Please see the word and wait for it to fade first.";
			$("#dialog").slideDown();
			clearTimeout(dialogTimer);
			dialogTimer = setTimeout(function() { $("#dialog").slideUp(); }, 3000);
		}
	}
}

// TODO: not so good - could improve
function shuffle() {
	// shuffle objects
	//console.log("start-shuffle");
	for (let i = 0; i < objects.length; i++) {
		do {
			objects[i].x = Math.random()*(mainCanvas.width - objects[i].width);
			objects[i].y = Math.random()*(mainCanvas.height - objects[i].height);

			//console.log("shuffle-repeat");
		} while (allCollision(objects[i]));
	}
}

function clickBoxCollision(x, y) {
	for (let i = 0; i < objects.length; i++) {
		if ((x < objects[i].x) || (x > objects[i].x + objects[i].width) ||
			(y < objects[i].y) || (y > objects[i].y + objects[i].height)) {
			continue;
		} else {
			
		}
	}
}

function clickCircleCollision(x, y) {
	for (let i = 0; i < objects.length; i++) {
		var Ix = objects[i].x + objects[i].width/2;
		var Iy = objects[i].y + objects[i].height/2;
		var distance = Math.sqrt(((Ix - x)*(Ix - x)) + ((Iy - y)*(Iy - y)));
		if (distance < (objects[i].width/3)) {
			// collision
			document.getElementById('nextButton').disabled = false;
			deselectAll();
			select(objects[i]);
			//setTimeout(pressedNextButton, 1000);
			//heardWord = false;
		} else {
			continue;
		}
	}
}

function allCollision(object) {
	for (let i = 0; i < objects.length; i++) {
		if (object !== objects[i]) {
			if (circleCollision(object, objects[i])) {
				return true;
			}
		}
	}
	return false;
}

function boxCollision(objectA, objectB) {
	// ??? - is it really correct?
	if 	((objectA.x > objectB.x + objectB.width) ||
		(objectA.x + objectA.width < objectB.x) ||
		(objectA.y > objectB.y + objectB.height) ||
		(objectA.y + objectA.height < objectB.y) ) {
		return false;
	} else {
		return true;
	}
}


function circleCollision(objectA, objectB) {
	var Ax = objectA.x + objectA.width/2;
	var Bx = objectB.x + objectB.width/2;
	var Ay = objectA.y + objectA.height/2;
	var By = objectB.y + objectB.height/2;
	var distance = Math.sqrt(((Ax - Bx)*(Ax - Bx)) + ((Ay - By)*(Ay - By)));
	if (distance < 4*(objectA.width/10 + objectB.width/10)) {
		// collision
		return true;
	} else {
		return false;
	}
}

function select(object) {
	object.selected = true;
}

function deselectAll() {
	for (let i = 0; i < objects.length; i++) {
		objects[i].selected = false;
	}
}

function selectBad(object) {
	if (arguments.length > 0)
		object.selected = 'X';
}

function getSelected() {
	for (let i = 0; i < objects.length; i++) {
		if (objects[i].selected === true) {
			return objects[i];
		}
	}
	// return empty object
}

function pressedPlayButton(patternToPlay/*, functionEnd*/) {
	heardWord = false;
	deselectAll();
	document.getElementById('nextButton').disabled = true;
	
	if (typeof patternToPlay === "undefined") {
		patternToPlay = pattern;
	}
	
	if (!playingSound) {
		playingSound = true;

		$("#playButton").prop("disabled", true);
		clearTimeout(dialogTimer);
		$("#dialog").slideUp();
		
		playingPosition = 0;
		soundToPlay = patternToPlay;
		
		playNextSound();
	}
}

function playNextSound() {
	if (playingPosition < soundToPlay.length) {
				
		if (fadingCondition) {
			if (soundToPlay[playingPosition] === 'b') {
				playingPosition++;
		
				// fade away previous dot
				$("#wordField span").animate({"opacity": 0});
				
				// write new dot
				$("#wordField").append("<span style='color:hsl(40, 100%, 50%)'>&#9679;</span>");
				window.setTimeout(function() { playNextSound(); }, 500 + 500);
			} else if (soundToPlay[playingPosition] === 'a') {
				playingPosition++;
				
				// fade away previous dot
				$("#wordField span").animate({"opacity": 0});
				
				// fade in new dot
				$("#wordField").append("<span style='color:hsl(220, 100%, 50%)'>&#9679;</span>");
				window.setTimeout(function() { playNextSound(); }, 500 + 500);
			}	
		} else {
			// show all at once
			for (playingPosition = 0; playingPosition < soundToPlay.length; playingPosition++) {
				
				// add dot
				if (soundToPlay[playingPosition] === 'b') {
					$("#wordField").append("<span style='color:hsl(40, 100%, 50%)'>&#9679;</span>");
				} else if (soundToPlay[playingPosition] === 'a') {
					$("#wordField").append("<span style='color:hsl(220, 100%, 50%)'>&#9679;</span>");
				}
			}
			// set time to fade away
			window.setTimeout(function() { 
				$("#wordField span").animate({"opacity": 0}); 
				// to reach end function
				playNextSound();
			}, soundToPlay.length*500);
		}
	} else {
		heardWord = true;
		if (typeof functionEnd !== "function") {
			//$("#playButton").prop("disabled", false);
		} else {
			functionEnd();
			functionEnd = null;
		}
		// remove word from screen
		document.getElementById("wordField").innerHTML = "-";
		playingSound = false;
	}
}

function pressedNextButton() {
	heardWord = false;
	clearTimeout(dialogTimer);
	$("#dialog").slideUp();
	selectionState = false;
	switch (phase) {
		case "start":
			$("#nextButton").prop("disabled", true);
			$("#playButton").prop("disabled", true);
			// record choice
			var inputItem = String(getSelected().img.src).slice(-5, -4);
			$.get("php/self_testing.php" , { input: inputItem} , function(json) {
				//console.log(json);
				var data = JSON.parse(json);
				
				// show if correct
				/*
				if (data.correct) {
					document.getElementById("dialog").style.backgroundColor = "#cfc"
					document.getElementById("dialogText").innerHTML = "Correct!";
					
				} else {
					selectBad(getSelected());
					select(objects[data.answer.charCodeAt(0) - 97]);
					document.getElementById("dialog").style.backgroundColor = "#fcc"
					document.getElementById("dialogText").innerHTML = "Wrong!";
				}
				*/
				
				//$("#dialog").slideDown();
				//dialogTimer = setTimeout(function() { $("#dialog").slideUp(); }, 1100);

				if (data.pattern == 'end') {
					window.onbeforeunload = null;
					window.location.reload();
				} else {
					$("#nextButton").prop("disabled", true);
					document.getElementById('playButton').disabled = false;
					// get new pattern
					nextPattern = data.pattern;
					
					// reset values
					pattern = nextPattern;
					//phase = "start";
					heardWord = false;
					deselectAll();
					shuffle();
				}
			});
			break;
	}
}

function requestCurrentItem() {
	$.get("php/self_testing.php" , function(json) {
		//console.log(json);
		var data = JSON.parse(json);

		// get new pattern
		pattern = data.pattern;
		
		// reset values
		document.getElementById('nextButton').disabled = true;
		heardWord = false;
		deselectAll();	// unselect all
		shuffle();
	});
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