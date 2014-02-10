<?php
require_once('php/classes/an8.1.0.0.class.php');
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
  <title>Anim8or HTML5 Experiment - Ver <?=$version?></title>
  <script src="js/modernizr.min.js"></script>
  <script src="js/html5_check.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script> 
    <script src="js/anim8or.1.0.1.js"></script>
    <script>
	$(document).ready(function(){
	    jQuery.logThis = function( text ){
	       if( (window['console'] !== undefined) ){
		       console.log( text );
	       }
	    }
	    /* Anim8or Points
		points {
		(-26.955 -52.911 -26.955) (-26.955 -52.911 26.955) 
		(-26.955 52.911 -26.955) (-26.955 52.911 26.955) (26.955 -52.911 -26.955) 
		(26.955 -52.911 26.955) (26.955 52.911 -26.955) (26.955 52.911 26.955)
		}
	    */
	    <?=$points?>
		/* Anim8or face
		    faces {
		    4 4 0 -1 ( (0 0) (4 4) (6 6) (2 2) )
		    4 4 0 -1 ( (1 1) (3 3) (7 7) (5 5) )
		    4 4 0 -1 ( (0 0) (2 2) (3 3) (1 1) )
		    4 4 0 -1 ( (4 4) (5 5) (7 7) (6 6) )
		    4 4 0 -1 ( (2 2) (6 6) (7 7) (3 3) )
		    4 4 0 -1 ( (0 0) (1 1) (5 5) (4 4) )
		}
		*/
	    <?=$faces?>
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
