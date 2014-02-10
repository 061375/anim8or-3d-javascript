<?php
/*****************************************************************
 * @class An8
 * @description 
 * @copyright Copyright (c) 2010-Present, 061375
 * @author Jeremy Heminger <j.heminger@061375.com>
 * @bindings
 * @deprecated = false
 *
 * */
class An8
{
    private $errors = array();
    function __construct()
    {
	
    }
    public function uploadAn8()
    {
	$action_result = array();
	if(isset($_FILES))
	{
	    $files = $_FILES;
	}
	else
	{
	    $action_result['result'][] .= "console.log('Error: No File');";
	}
	if ($files["file"]["error"] > 0) // catch initial errors
	{
	    $action_result['result'][] .= "console.log('Error: " . $files["file"]["error"] . "');";
	}
	else
	{
	    if($files['file']['size'] < MAX_BYTES) // make sure the file isn't too big
	    {
		$path = getcwd().'/temp/';
	
		if(false == is_dir($path))
		{
		    mkdir($path);	// mk
		    chmod($path,777);
		}   
		if('application/octet-stream' !== $files['file']['type']) // deal with the compression type
		{
			$action_result['result'][] .= "console.log(' ... an unrecognized file type was selected ');";
		}
		else
		{
		    $file = $files['file']['tmp_name'];
		    $newfile = $path.$files['file']['name'];
		    if (!copy($file, $newfile))
		    {
			$action_result['result'][] = "console.log('failed to copy $file to $newfile');";
		    }
		    else
		    {
			$action_result['result'] = 1;
			$action_result['file'] = $newfile;
		    }
		}
		
	    }
	}
	return $action_result;
    }
    public function openAn8($filename)
    {
	$result = array();
	if (filesize($filename) > MAX_BYTES) {
	    // file too big
	    $this->set_error_message('File too big');
	}
	if (false == $this->has_error()) {
	    $handle = @fopen($filename, "r");
	    if (false !== $handle) {
		while (($buffer = fgets($handle, 4096)) !== false) {
                    if (true !== $this->fileIsClean($buffer)) {
                        $this->set_error_message('File contains potentially dangerous code!');
                        return false;
                    } else {
                        $result['data'][] = $buffer;
                    }
		}
		if (!feof($handle)) {
		    $this->set_error_message('Error: unexpected fgets() fail_');
		}
		fclose($handle);
	    }
	    if (false == isset($result['data'])) {
		$this->set_error_message('Error: unexpected fgets() fail_');
	    }
	    
	    if (false == $this->has_error()) {
		$result['result'] = 1;
		return $result;
	    }
	}
	return 0;
    }
    public function prepareAn8($data,$object="object01",$mesh="mesh01")
    {
	$start = 0;
	$points = array();
	$faces = array();
	if (false == isset($data['data'])) {
	    $this->set_error_message('data has no value IN An8 :: prepareAn8');    
	}
	if (false == $this->has_error()) {
	    foreach ($data['data'] as $row) {
		if (strpos($row,"object") !== false && strpos($row,$object) !== false) {
		    $start = 1;
		}
		if (1 == $start) {
		    if(strpos($row,"mesh") !== false) {
			$start = 2;
		    }
		}
		if (2 == $start) {
		    if (strpos($row,"name") !== false && strpos($row,$mesh) !== false) {
			$start = 3;
		    }
		}
		if (3 == $start) {
		    if (strpos($row,"points {") !== false) {
			$start = 4;
		    }
		}
		if (4 == $start) {
		    if(strpos($row,"}") !== false) {
			$start = 5;
		    } else {
			$points[] = $row;
		    }
		}
		if (5 == $start) {
		    if (strpos($row,"faces") !== false) {
			$start = 6;
		    }
		}
		if (6 == $start) {
		    if (strpos($row,"}") !== false) {
			$start = 7;
		    } else {
			$faces[] = $row;
		    }
		}
	    }
	    $model = array(
			    'result' => 1,
			    'faces' => $faces,
			    'points' => $points
			    );
	    return $model;
	}
	return 0;
    }
    public function get3Dpoints($data)
    {
	$result = array();;
	$newdata = array();
	if (false == isset($data['points'])) {
	    $this->set_error_message('Error IN An8::get3Dpoints = points not set');
	}
	if (false == $this->has_error()) {
	    foreach ($data['points'] as $key => $value) {
		if (0 != $key) {
		    $string=$value;
		    $face_length = substr_count($value,"(");
		    $regex=',(?x)
		    (?(DEFINE)(?<Cap>\((?>[^)]+\))))
		    ';
		    for ($i=0; $i<$face_length; $i++) {
			    $regex.='((?&Cap))\s';
		    }
		    $regex.=',';
		    if (preg_match($regex,$string,$match)) {
			$result[] = $match;
		    }
		}
	    }
	    if (count($result) < 1) {
		$this->set_error_message('Error IN An8::get3Dpoints = No Results');
	    } else {
		return $result;
	    }
	}
	return 0;
    }
    public function make3Dpoints($data)
    {
	/*
	 pointsArray = [
		    MakeA3DPoint(-26.955,-52.911,-26.955),
		    MakeA3DPoint(-26.955,-52.911,26.955),
		    MakeA3DPoint(-26.955,52.911,-26.955),
		    MakeA3DPoint(-26.955,52.911,26.955),
		    MakeA3DPoint(26.955,-52.911,-26.955),
		    MakeA3DPoint(26.955,-52.911,26.955),
		    MakeA3DPoint(26.955,52.911,-26.955),
		    MakeA3DPoint(26.955,52.911,26.955)
	    ];
	    
	    
	*/
	$result = "pointsArray = [\n\t\t";
	$count_row = 0;
	foreach ($data as $row) {
	    $_row = '';
	    $_value = '';
	    unset($row[0]);
	    unset($row[1]);
	    unset($row['Cap']);
	    $count = 0;
	    foreach ($row as $key => $value) {
		ltrim($value);
		rtrim($value);
		$value = str_replace('(','',$value);
		$value = str_replace(')','',$value);
		$points = explode(' ',$value);
		$_value = '';
		//echo '<pre>';print_r($value);
		foreach ($points as $point) {
		    $point = (float)$point;
		    
		    if ($point < 0) {
			$point = $point + ($point * -2);
		    } else {
			$point = $point - ($point * 2);
		    }
		    $_value .= round($point,2).',';
		}
		//echo '<pre>';print_r($_value);exit();
		$_value = substr($_value,0,strlen($_value) -1);
		//$_value = str_replace(" ",',',$value);
		$result.="MakeA3DPoint(".$_value.")";
		if ($count < count($row)-1) {
		    $result.=",\n\t\t";
		}
		$count++;
	    }
    
	    if ($count_row < count($data)-1) {
		$result.=",\n\t\t";
	    }
	    $count_row++;
	}
	$result .="\n\t];\n";
	//exit();
	return $result;
    }
    public function get3Dfaces($data)
    {
	$result = array();
	foreach ($data['faces'] as $key => $value) {
	    if (0 != $key) {
		$value = ltrim($value);
		$value = rtrim($value);
		$string=$value;
		$face_length = substr($string,0,strpos($string,' '));
		$regex=',(?x)
		(?(DEFINE)(?<Cap>\((?>[^)]+\))))
		(?>(?:-?\d\s){4}\([ ])
		';
		for ($i=0; $i<$face_length; $i++) {
			$regex.='((?&Cap))\s';
		}
		$regex.=',';
		if (preg_match($regex,$string,$match)) {
		    $result[] = $match;
		}
	    }
	}
	return $result;
    }
    public function make3Dfaces($data)
    {
	/*
	 facesArray = [
		  faceKeys = [0,4,6,2],
		  faceKeys = [1,3,7,5],
		  faceKeys = [0,2,3,1],
		  faceKeys = [4,5,7,6],
		  faceKeys = [2,6,7,3],
		  faceKeys = [0,1,5,4]		  
	    ];
	*/
	$result = "facesArray = [\n\t\t";
	$count_row = 0;
	foreach ($data as $row) {
	    $_row = '';
	    $_value = '';
	    unset($row[0]);
	    unset($row[1]);
	    unset($row['Cap']);
	    $count = 0;
	    foreach ($row as $key => $value) {
		if (strpos($value," ") !== false) {
		    $_value = substr($value,strpos($value,'('),strpos($value,' '));
		} else {
		    $_value = str_replace(")",'',$value);
		}
		    $_value = str_replace("(",'',$_value);
		    $_row.= $_value;
		    if ($count < count($row)-1) {
			$_row.=",";
		    }
		    $count++;
	    }
	    $result .= "faceKeys = [".$_row."]";
	    if ($count_row < count($data)-1 ) {
		$result.=",\n\t\t";
	    }
	    $count_row++;
	}
	$result .="\n];\n";
	return $result;
    }
    private function fileIsClean($string)
    {
        if (strpos($string,'eval') !== false) {
            return false;
        }
        if (strpos($string,'base64decode') !== false) {
            return false;
        }
        if (strpos($string,'<?') !== false) {
            return false;
        }
        if (strpos($string,'<?php') !== false) {
            return false;
        }
        if (strpos($string,'?>') !== false) {
            return false;
        }
        return true;
    }
    public function getAllFiles($dir)
    {
        $return = array();
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                $return[] = $entry;
            }
            closedir($handle);
        }
        return $return;
    }
    // --------------------------------------------------------------------

    /**
     * Get Error messages
     *
     * @return array
     */
    public function get_error_message()
    {
        if (count($this->errors) > 0)
        {
            $tmp = $this->errors;
            $this->errors = array();
            return $tmp;
        }
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Set Error messages
     *
     * @return array
     */
    private function set_error_message($message)
    {
        if ($message != '')
        {
            $this->errors[] = $message;
        }
    }
	
    // --------------------------------------------------------------------
	
    /**
     * Has Error
     *
     * @return array
     */
    private function has_error()
    {
        if (count($this->errors) > 0)
        {
            return true;
        }
        return false;
    }

    // --------------------------------------------------------------------

    /**
     * Clear Error
     *
     * @return array
     */
    private function clear_error()
    {
        $this->errors = array();
    }
    /**
     * Gathers errors and converts them to XML to be returned to the user
     *
     * @return void
     */
    public function display_errors()
    {
        $errors = array();
        $errors = $this->get_error_message();
	echo '<script>';
        foreach ($errors as $row) {
            echo "console.log('Error :: ".$row."');";
        }
	echo '</script>';
    }
}
?>