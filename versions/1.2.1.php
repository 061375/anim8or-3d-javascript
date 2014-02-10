<?php
require_once('php/classes/an8.class.php');
define("MAX_BYTES",100000);
$points = 0;
$faces = 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$displayModel = false;
$an8 = new An8();
if ("upload_an8" == $action) {
    $result = $an8->uploadAn8();
    if (false !== $result) {
	$data = $an8->openAn8($result['file']);
	//echo '<pre> data = ';print_r($data);exit();
	if (0 !== $data) {
	    $result = $an8->prepareAn8($data);
	    if (0 !== $result) {
		$points = $an8->get3Dpoints($result);
		$points = $an8->make3Dpoints($points);
		$faces = $an8->get3Dfaces($result);
		$faces = $an8->make3Dfaces($faces);
		$displayModel = true;
	    } else {
		$an8->display_errors();
	    }
	} else {
	    $an8->display_errors();
	}
    }  else {
	$an8->display_errors();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>HTML5</title>
  <script src="js/modernizr.min.js"></script>
  <script src="js/html5_check.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script> 
    <script>
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
	/* Anim8or Points
	    points {
      (-26.955 -52.911 -26.955) (-26.955 -52.911 26.955) 
      (-26.955 52.911 -26.955) (-26.955 52.911 26.955) (26.955 -52.911 -26.955) 
      (26.955 -52.911 26.955) (26.955 52.911 -26.955) (26.955 52.911 26.955)
    }*/
	<?=$points?>
	/* Anim8or face
	 faces {
      4 4 0 -1 ( (0 0) (4 4) (6 6) (2 2) )
      4 4 0 -1 ( (1 1) (3 3) (7 7) (5 5) )
      4 4 0 -1 ( (0 0) (2 2) (3 3) (1 1) )
      4 4 0 -1 ( (4 4) (5 5) (7 7) (6 6) )
      4 4 0 -1 ( (2 2) (6 6) (7 7) (3 3) )
      4 4 0 -1 ( (0 0) (1 1) (5 5) (4 4) )
    }*/
	<?=$faces?>
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
    </script>
    <style>
	.fltleft
	{
	    float: left;
	}
	.fltright
	{
	    float: right;
	}
	.clear
	{
	    clear: both;
	}
    </style>
</head>
<body style="background:blue">
	<div id="container" style="background:#fff;height:500px;width:1010px;">
	    <div style="width:500px;height:500px;padding:5px" class="fltleft">
		<div>
		    <?php
		    if(true == $displayModel)
		    {
		    ?>
		    <canvas class="clear" id="canvas" style="border:1px dotted;float:left" height="500" width="500"></canvas>
		    <?php
		    }
		    ?>
		</div>
	    </div>
	    
	    <div class="fltleft">
		<div>
		    <img src="/thelab/legacy/Anim8or/images/original angry birds.jpg" alt="The original Angry Birds"/>
		</div>
		<form action="#" method="post" enctype="multipart/form-data">
		    <h2></h2>
		    <input type="file" name="file" id="file" />
		    <input type="hidden" name="action" value="upload_an8" />
		    <div>
			<input type="submit" name="submit" value="Upload Anim8or File" />
		    </div>
		</form>
	    </div>
	    <div class="fltleft">
		<h3>This only works for Anim8or v0.95 presently...</h3>
		<p>It's generally what I use and so...It's what I designed it to use. :p</p>
		<p>It actually works with later versions. But to a lesser degree.<br />
		It also does not yet work with sub divisions.</p>
	    </div>
	</div>
</body>
</html>
