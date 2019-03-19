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
var finalTrial = false;

var playingPosition = 0;
var soundToPlay = "";

var selectionState = false;
var objectsOpacity = 1;

var itemOrder = 0;
var lastObject = 'x';
var lastCycle = 0;

var mainCanvas = document.getElementById("mainCanvas");
var mainContext = mainCanvas.getContext("2d");

var functionEnd = null;

// condition
var fadingCondition = false;

// -----------------------------------------------------------------------------------------------
// WINDOW EVENTS AND INTiALISATION
// -----------------------------------------------------------------------------------------------
window.onbeforeunload = function(e) {
	var dialogText = 'Are you sure?';

	e.returnValue = dialogText;
	return dialogText;
}

window.onload = function() {
	$.get("php/generate_buttons.php" , function(returnedHTML) {
		document.getElementById("buttonsContainer").innerHTML = String(returnedHTML);
	});
	
	// set up the canvas
	mainCanvas.width = window.innerWidth*0.7 > 940 ? window.innerWidth*0.7 : 940;
	mainCanvas.height = window.innerHeight*0.6 > 320 ? window.innerHeight*0.6 : 320;
	
	mainCanvas.addEventListener("click", clickedCanvas, false);
	
	// hide dialog box
	$("#dialog").slideUp();
	
	// load images and initiate objects
	loadObjects();
};

window.onresize = function() {
	// resize canvas
	mainCanvas.width = window.innerWidth*0.7 > 940 ? window.innerWidth*0.7 : 940;
	mainCanvas.height = window.innerHeight*0.6 > 320 ? window.innerHeight*0.6 : 320;
	
	// resize objects
	var side = (mainCanvas.width > mainCanvas.height ? mainCanvas.width : mainCanvas.height)/6;
	for (let i = 0; i < objects.length; i++) {
		objects[i].width = side;
		objects[i].height = side;
	}
	
	shuffleObjects();
}

// Load images and initiate objects
function loadObjects() {
	var loadingCounter = 0;
	const numberOfResources = 8;
	var side = (mainCanvas.width > mainCanvas.height ? mainCanvas.width : mainCanvas.height)/6;
	for (let i = 0; i < numberOfResources; i++) {
		objects[i] = {};
		objects[i].img = new Image();
		objects[i].x = i*100;
		objects[i].y = 0;
		objects[i].width = side;
		objects[i].height = side;
		objects[i].selected = false;
		objects[i].img.onload = function() {
			loadingCounter++;
			if (loadingCounter >= numberOfResources) {
				// done loading
				// get current item
				$.get("php/comprehension_training.php" , function(json) {
					//console.log(json);
					var data = JSON.parse(json);
					// get new pattern
					pattern = data.pattern;
					
					// update display values
					updateProgressDisplay();
					
					// reset values
					document.getElementById('nextButton').disabled = true;
					heardWord = false;
					deselectAll();	// unselect all
					shuffleObjects();

					// start animating the canvas
					window.requestAnimationFrame(mainDraw);
				});
			}
		};
		objects[i].img.src = "images/set1/" + String.fromCharCode(97 + i) + ".png";
	}
}

// Shuffle position of objects on canvas
function shuffleObjects() {
	// shuffle objects
	//console.log("start-shuffle");
	for (let i = 0; i < objects.length; i++) {
		do {
			objects[i].x = Math.random()*(mainCanvas.width - objects[i].width);
			objects[i].y = Math.random()*(mainCanvas.height - objects[i].height);
		} while (allCollision(objects[i]));
	}
}


// -----------------------------------------------------------------------------------------------
// CANVAS FUNCTIONS
// -----------------------------------------------------------------------------------------------

//
function mainDraw() {
	var greenCircle = false;
	var redCircle = false;
	var centerX, cemterY, radius;
	var centerX1, cemterY1, radius1;
	
	mainContext.clearRect(0, 0, mainCanvas.width, mainCanvas.height);

	// fade objects 
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

function clickedCanvas(e) {
	var bounds = mainCanvas.getBoundingClientRect();
	//alert("h " + (e.clientX - bounds.left) + " " + (e.clientY - bounds.top));
	
	var clickX = e.clientX - bounds.left;
	var clickY = e.clientY - bounds.top;
	
	// Clicking  collision detection
	if (phase == "start") {
		if ((heardWord) && (!((selectionState) && (objectsOpacity > 0)))) {
			clearTimeout(dialogTimer);
			$("#dialog").slideUp();
			clickCircleCollision(clickX, clickY);
		} else {
			// show dialog
			document.getElementById("dialog").style.backgroundColor = "hsl(290, 100%, 90%)"
			document.getElementById("dialogText").innerHTML = "Too soon! Please see the word and wait for it to fade first.";
			$("#dialog").slideDown();
			clearTimeout(dialogTimer);
			dialogTimer = setTimeout(function() { $("#dialog").slideUp(); }, 3000);
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
			pressedNextButton();
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


// -----------------------------------------------------------------------------------------------
// OBJECT FUNCTIONS
// -----------------------------------------------------------------------------------------------

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


// -----------------------------------------------------------------------------------------------
// EXPERIMENT LOGIC
// -----------------------------------------------------------------------------------------------

function pressedPlayButton(patternToPlay/*, functionEnd*/) {
	heardWord = false;
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
	clearTimeout(dialogTimer);
	$("#dialog").slideUp();
	selectionState = false;
	switch (phase) {
		case "start":
			$("#nextButton").prop("disabled", true);
			$("#playButton").prop("disabled", true);
			// record choice
			var inputItem = String(getSelected().img.src).slice(-5, -4);
			$.get("php/comprehension_training.php" , { input: inputItem} , function(json) {
				//console.log(json);
				var data = JSON.parse(json);

				itemOrder = data.item_order;
				lastObject = data.object;

				// show if correct
				if (data.correct) {
					document.getElementById("dialog").style.backgroundColor = "#cfc"
					document.getElementById("dialogText").innerHTML = "Correct!";
					
				} else {
					selectBad(getSelected());
					select(objects[data.answer.charCodeAt(0) - 97]);
					document.getElementById("dialog").style.backgroundColor = "#fcc"
					document.getElementById("dialogText").innerHTML = "Wrong!";
				}
				updateProgressDisplay(true);
				
				document.getElementById('itemsCorrect').innerHTML = data.items_correct;
				
				$("#dialog").slideDown();
				dialogTimer = setTimeout(function() { $("#dialog").slideUp(); }, 1100);

				// no repeat phase at all
				heardWord = false;			
				setTimeout(function() { 
				
					functionEnd = function() {
						setTimeout(function() {
							heardWord = false;
							
							$("#playButton").prop("disabled", true);
							$("#nextButton").prop("disabled", false);
							
							if (data.pattern == 'end') {
								finalTrial = true;
								phase = "ending";
								lastCycle = data.cycle;
								setTimeout(function() { pressedNextButton(); $("#nextButton").prop("disabled", false); }, 1000);
							} else {
								$("#dialog").slideUp(); 
								// reset values
								pattern = data.pattern;
								document.getElementById('nextButton').disabled = true;
								document.getElementById('playButton').disabled = false;
								
								phase = "start";
								updateProgressDisplay();
								deselectAll();
								
								selectionState = false;
								objectsOpacity = 1;
								
								shuffleObjects();
								
								if (data.item_total < data.item_order) {
									alert("Nice try but you are not quite there yet. Here is another chance to go through the set. Just keep at it, you will get better!");	
								}
								lastCycle = data.cycle;
							}
						}, 1000);
					};
					pressedPlayButton(pattern);	
				}, 1800);

				selectionState = true;
				objectsOpacity = 1;
			});
			break;
		case "practice":
			$("#nextButton").prop("disabled", true);
			$("#lowButton").prop("disabled", false);
			$("#highButton").prop("disabled", false);		
			$("#listenPhase").hide();
			$("#babblePhase").show();

			phase = "next";
			break;
		case "next":
			$("#nextButton").prop("disabled", true);
			$("#lowButton").prop("disabled", true);
			$("#highButton").prop("disabled", true);
			// send babble to server
			$.get("php/practice.php" , { input: babble, item_order: itemOrder, object: lastObject, cycle: lastCycle} , function(json) { 
				//console.log(json);
				var data = JSON.parse(json);
				
				// show if correct
				if (data.correct) {
					document.getElementById("dialog").style.backgroundColor = "#cfc"
					document.getElementById("dialogText").innerHTML = "Correct!";
					
				} else {
					document.getElementById("dialog").style.backgroundColor = "#fcc"
					document.getElementById("dialogText").innerHTML = "Wrong!";
				}
				
				babble = '';
				
				$("#dialog").slideDown();
				dialogTimer = setTimeout(function() { 
					$("#dialog").slideUp(); 
					// reset values
					pattern = nextPattern;
					document.getElementById('nextButton').disabled = true;
					document.getElementById('playButton').disabled = false;
								
					if (lastCycle > 1) {	
						if (finalTrial) {
							lastCycle = data.cycle;
							phase = "ending";
							$("#nextButton").prop("disabled", false);
							setTimeout(pressedNextButton, 1000);
						} else {
							phase = "start";
							updateProgressDisplay();
							heardWord = false;
							deselectAll();
							$("#listenPhase").show();
							$("#babblePhase").hide();
							shuffleObjects();
							objectsOpacity = 1;
							lastCycle = data.cycle;
							if (data.item_order === 1) {
								if (!finalTrial)
									alert("Nice try but you are not quite there yet. Here is another chance to go through the set. Just keep at it, you will get better!");	
							}
						}
					} else {
						if (!finalTrial) {
							phase = "start";
							updateProgressDisplay();
							heardWord = false;
							deselectAll();
							$("#listenPhase").show();
							$("#babblePhase").hide();
							shuffleObjects();
							objectsOpacity = 1;
							// new instructions
							if ((lastCycle == 1) && (data.cycle > 1)) {
								alert("Good job! Your training will continue until you get at least " + String(document.getElementById('itemsRequired').innerHTML) + " out of " + String(document.getElementById('itemsTotal').innerHTML) + " right in a single round. The score is reset at the end of each round.");
								document.getElementById('scoreView').hidden = false;
							}	
							lastCycle = data.cycle;
						} else {
							lastCycle = data.cycle;
							phase = "ending";
							$("#nextButton").prop("disabled", false);
							setTimeout(pressedNextButton, 1000);
						}
					}
					
				}, 1100);
			});
			break;
		case 'ending':
			window.onbeforeunload = null;
			window.location.reload();
			break;
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
		
		// fading condition
		if (data.tree > 6) {
			fadingCondition = true;
		}
		
		/*if (data.cycle > 1) {
			document.getElementById('scoreView').hidden = false;
		}*/
	});
}