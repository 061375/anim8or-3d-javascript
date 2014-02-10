$(document).ready(function() {
	var canvas;    
	var context;   
 	var imageLoaded = 0;
  	var depth = new Array();
	
	var origin = new Object();
 	origin.x = 250;
 	origin.y = 250;
	
 	var focalLength = 200;

	var timer = 0;
	MakeA3DPoint = function(x,y,z){
		var point = new Object();
		point.x = x;
		point.y = y;
		point.z = z;
		return point;
	};
	ConvertPointIn3DToPointIn2D = function(pointIn3D){
		var pointIn2D = new Object();
		var scaleRatio = focalLength / (focalLength + pointIn3D.z);
		pointIn2D.x = pointIn3D.x * scaleRatio;
		pointIn2D.y = pointIn3D.y * scaleRatio;
		return pointIn2D;
	};
	
	//console.log(facesArray);

	// creeate box object here
	var box = new Object();
	direction = "left";
	speed = 5;
	
	backAndForthAndSideToSide = function(){
		var screenPoints = new Array();
		for (var i=0; i < pointsArray.length; i++){
			var thisPoint = pointsArray[i];		
			if (direction == "left"){
				thisPoint.x -= speed;
				if (i == pointsArray.length-1 && thisPoint.x <= -100) direction = "backward";
			}else if (direction == "backward"){
				thisPoint.z += speed;
				if (i == pointsArray.length-1 && thisPoint.z >= 200) direction = "right";
			}else if (direction == "right"){
				thisPoint.x += speed;
				if (i == pointsArray.length-1 && thisPoint.x >= 60) direction = "forward";
			}else if (direction == "forward"){
				thisPoint.z -= speed;
				if (i == pointsArray.length-1 && thisPoint.z <= -100) direction = "left";
			}
			screenPoints[i] = ConvertPointIn3DToPointIn2D(thisPoint);
			screenPoints[i].x += origin.x;
			screenPoints[i].y += origin.y;
			//console.log(direction);
	}
	

    	canvas.width = canvas.width;		
	context.beginPath();
	context.lineWidth = 0.5;
    	context.strokeStyle = "#000";
	var firstPoint = new Object();
	firstPoint.i = 0;
	firstPoint.x = 0;
	firstPoint.y = 0;
	$(facesArray).each(function(){
	  $(this).each(function(){
	    if(firstPoint.i == 0)
	    {
		if(undefined !== screenPoints[this].x && undefined !== screenPoints[this].y)
		{
		    context.moveTo(screenPoints[this].x, screenPoints[this].y);
		    firstPoint.x = screenPoints[this].x;
		    firstPoint.y = screenPoints[this].y;
		}
	    }
	    if(typeof undefined !== screenPoints[this].x && typeof undefined !== screenPoints[this].y)
	    context.lineTo(screenPoints[this].x, screenPoints[this].y);
	    firstPoint.i++;
	    if(firstPoint.i == faceKeys.length) {
		firstPoint.i = 0;
		if (undefined !== firstPoint.x && undefined !== firstPoint.y) {
		    context.lineTo(firstPoint.x, firstPoint.y);
		}
	    }
	  });
	});
	context.stroke();
    	context.closePath();
      };
	canvas = document.getElementById("canvas");     // get the canvas from the DOM
    	if (null !== canvas) {
	    context = canvas.getContext("2d");              // we are using a 2D canvas
	    setInterval(backAndForthAndSideToSide, 35);
	    //backAndForthAndSideToSide();
	}
});