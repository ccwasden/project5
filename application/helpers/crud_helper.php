<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('puke'))
{
    function puke($map, $title, $skip = array(), $redirect = NULL)
    {
    	if($redirect == NULL) $redirect = $title;
    	if(count($map) > 0){
	    	echo "<table cellpadding='0' cellspacing='0'>";
	    	for($i = 0; $i < count($map); $i++){
	    		$row = $map[$i];
	    		if($i == 0){
	    			echo "<tr>";
	    			foreach($row as $key => $value){
	    				if(!in_array($key, $skip))
	    					echo "<th>".$key."</th>";
	    			}
	    			echo "<th></th></tr>";
	    		}
	    		echo "<tr>";
	    		
	    		foreach($row as $key => $value){
	    			if(!in_array($key, $skip))
	    				echo "<td>".$value."</td>";
	    		}
	    		echo "<td><a href='".base_url()."app/delete/".$title."/".$i."/".$redirect."'>delete</a></td>";
	    		echo "</tr>";
	    	}
	    	echo "</table>";
	    }
	    else echo "<h3>No ".$title."</h3>";
    }   
}

function single_field_form($label, $name, $url, $val = ""){
	echo '<form action="'.$url.'" method="post"> 
		'.$label.' <input type="text" name="'.$name.'" value="'.$val.'"> 
		<button type="submit">Save</button> 
	</form>';
}

function uuid(){
	    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
	        // 32 bits for "time_low"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

	        // 16 bits for "time_mid"
	        mt_rand( 0, 0xffff ),

	        // 16 bits for "time_hi_and_version",
	        // four most significant bits holds version number 4
	        mt_rand( 0, 0x0fff ) | 0x4000,

	        // 16 bits, 8 bits for "clk_seq_hi_res",
	        // 8 bits for "clk_seq_low",
	        // two most significant bits holds zero and one for variant DCE1.1
	        mt_rand( 0, 0x3fff ) | 0x8000,

	        // 48 bits for "node"
	        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
	    );
	}

function distance($lat1, $lng1, $lat2, $lng2, $miles = true)
{
	$pi80 = M_PI / 180;
	$lat1 *= $pi80;
	$lng1 *= $pi80;
	$lat2 *= $pi80;
	$lng2 *= $pi80;

	$r = 6372.797; // mean radius of Earth in km
	$dlat = $lat2 - $lat1;
	$dlng = $lng2 - $lng1;
	$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	$km = $r * $c;

	return ($miles ? ($km * 0.621371192) : $km);
}