<?php

function saveJSON($data){
	file_put_contents('users.txt', base64_encode(serialize($data)));
}

function getJSON(){
	if(file_exists('users.txt'))
		return unserialize(base64_decode(file_get_contents('users.txt')));
	else return NULL;
}


$secret = $_REQUEST['secret'];

if($secret == "GVO5Q32QJVUMESM4QOOXQG5FTS0WT5HCBTGBLLFAKI5JWM3T"){
	$userData = getJSON();
	$checkin = json_decode(stripslashes($_REQUEST['checkin']));
	$userId = $checkin->user->id;
	if(!isset($userData[$userId]))
			$userData[$userId] = array();
	if(isset($checkin->venue) && isset($checkin->venue->location)) {
		$userData[$userId]['last-checkin'] = $checkin->venue->location;
		saveJSON($userData);
	}
	else {
		$userData[$userId]['last-checkin'] = NULL;
		saveJSON($userData);	
	}
	// echo json_encode($userData);	
}
else echo "INVALID SECRET";
