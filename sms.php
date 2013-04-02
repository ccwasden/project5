<?php

function saveJSON($data){
	file_put_contents('users.txt', base64_encode(serialize($data)));
}

function getJSON(){
	if(file_exists('users.txt'))
		return unserialize(base64_decode(file_get_contents('users.txt')));
	else return NULL;
}

function userIdOfPhone($phone){
	$phone = substr($phone, 1);
	$users = getJSON();
	foreach($users as $id => $data){
		if(isset($data['phone']) && $data['phone'] == $phone)
			return $id;
	}
	return NULL;
}

function getSTORES(){
	if(file_exists('stores.txt'))
		return unserialize(base64_decode(file_get_contents('stores.txt')));
	else return array();
}

function signalEventToUrl($eventName, $event, $url){
	// echo "ATTEMPTING: ".$url."<br>".json_encode($event);

	$event['_domain'] = "rfq";
	$event['_name'] = $eventName;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($event));
	
	$result = curl_exec($ch);
	// echo "\n\n\nSENT : ".$result;
	curl_close($ch);
}

function signalBidEventToUrl($event, $url){
	signalEventToUrl("bid_available", $event, $url);
}

function makeBid($storeId, $driverId, $driverToken, $deliveryId, $time){
	$users = getJSON();
	$user = $users[$driverId];
	$data = array(
		'driverID' => $driverId,
		'driverName' => $user['name'],
		'deliveryTime' => $time,
		'deliveryID' => $deliveryId
	);
	$stores = getSTORES();
	signalBidEventToUrl($data, $stores[$storeId]);
}


$from = $_REQUEST['From'];
$body = $_REQUEST['Body'];

if($from && $body == "bid anyway"){
	$userId = userIdOfPhone($from);
	if($userId){
		$userData = getJSON();
		$last = $userData[$userId]['last-delivery'];
		makeBid($last[0], $userId, $userData['token'], 
				$last[1], $last[2]);

		$userData[$userId]['result'] = "SENT";
		saveJSON($userData);	
	}
}
else echo "INVALID SECRET";
