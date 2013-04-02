<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class App extends CI_Controller {

	public $test = TRUE;
	public $driver = NULL;

	public function __construct()
	{
		date_default_timezone_set('America/Denver');
	    parent::__construct();
	    $this->load->helper('url'); 
	    $this->load->library('session');
	    $this->load->library('template');
		$this->load->helper("crud_helper");
		$this->driver = $this->user();
	}

	public function index()
	{
		if($this->authenticate()){
			$this->template->page("driver_view",array(
				"user"=>$this->driver
			));
		}
	}

	public function esls()
	{
		if($this->authenticate()){
			$this->template->page("driver_store_view",array(
				"user"=>$this->driver
			));
		}
	}

	public function texts()
	{
		if($this->authenticate()){
			$this->template->page("texts_view",array(
				"user"=>$this->driver
			));
		}
	}

	public function bids()
	{
		if($this->authenticate()){
			$this->template->page("bids_view",array(
				"user"=>$this->driver
			));
		}
	}





	private function getCheckins($id, $token){
		$this->foursquare->set_current_token($token);
		$checks = $this->foursquare->api('users/'.$id.'/checkins');
		return $checks['checkins'];
	}



	public function clear($userId, $field){
		$user = $this->retrieve($userId);
		$user[$field] = array();
		$this->persist($user);
	}

	public function textIn(){
		// echo "FROM: ".$this->input->post("From");
		$phone = $this->input->post("From");
		$user = $this->where("phone",$phone);
		if(!$user) {
			log_message('error', "Couldnt find user of number: ".$phone);
			echo "ERROR";
		}
		else {
			$message = $this->input->post("Body");
			array_push($user['textsIn'],array("date"=>date('n/j g:i a'),"message"=>$message));
			$this->persist($user);
			if ($message == "bid anyway") {
				$delivery = end($user['bidsIn']);
				reset($user['bidsIn']);
				$this->sendBidForDelivery($user['id'],$delivery);
			}
			else if($message == "complete"){
				$delivery = end($user['bidsIn']);
				$this->signalEventToUrl("complete",array("deliveryId"=>$delivery['id']),$delivery['esl']);
				$this->signalEventToUrl("complete",array(),$delivery['esl1']);
			}
		}
		echo "OK";
	}

	private function where($key, $value, $arr = NULL){
		if($arr == NULL) $arr = $this->allUsers();
		foreach ($arr as $obj) {
	        if ($obj[$key] == $value) {
	        	return $obj;
	        }
		}
		return NULL;
	}

	private function getIndex($key, $value, $arr = NULL){
		if(!$arr) $arr = $this->allUsers();
		$i = 0;
		foreach ($arr as $obj) {
	        if ($obj && isset($obj[$key]) && $obj[$key] == $value) 
	        	return $i;
			$i++;
		}
		return NULL;
	}

	private function allUsers(){
		$users = array();
		$directory = 'drivers';
		if ( ! is_dir($directory)) exit('Invalid diretory path');
		foreach (scandir($directory) as $file) {
		    if (strrpos($file, ".") > -1) continue;
		    array_push($users, $this->retrieve($file));
		}
		return $users;
	}

	public function newESL(){
		$eslName = $this->input->post('esl');
		$eslId = uuid();
		$esl = array("id"=>$eslId,"name"=>$eslName,"url"=>base_url()."app/esl/".$this->driver['id']."/".$eslId);
		array_push($this->driver['esls'], $esl);
		$this->persist();
		redirect('/app/esls');
	}

	public function esl($userId, $storeId){
		$eventName = $this->input->post('_name');
		$user = $this->retrieve($userId);
		$source = $this->where("id",$storeId,$user['esls']);
		if($user && $source){
			if($eventName == "delivery_ready"){
				
				$delivery_ready = json_decode($this->input->post('delivery'),true);
				$delivery_ready['dateIn'] = date('n/j g:i a');
				array_push($user['bidsIn'], $delivery_ready);
				$this->persist($user);
				$this->onDeliveryReady($userId, $delivery_ready);
				
			}
			else if($eventName == "bid_awarded"){
				$this->textUser($userId, "Bid awarded. Pickup at 200 E. 500 N. Provo. Text 'complete' when delivered.");
			}
			else echo "EVENT NOT FOUND: ".$eventName;
		}
		else {
			echo "Error finding esl";
			log_message("error", "Error finding esl");
		}

	}

	private function onDeliveryReady($userId, $delivery){
		$user = $this->retrieve($userId);
		$userLat = $user['last_checkin']['venue']['location']['lat'];
		$userLng = $user['last_checkin']['venue']['location']['lng'];
		$storeLat = $delivery['shop_lat'];
		$storeLng = $delivery['shop_lng'];
		$distance = distance($userLat, $userLng, $storeLat, $storeLng);
		if($distance < 5){ // less than 5 miles
			echo "IN RANGE";
			$this->sendBidForDelivery($userId, $delivery);
			$this->textUser($userId, "Delivery ready ".round($distance,2)." miles away. Address: '"
				.$delivery['delivery_address']."' *** your bid has been sent.");
		}
		else {
			echo "OUT OF RANGE";
			$this->textUser($userId, "Delivery ready ".round($distance,2)." miles away. Address: '"
				.$delivery['delivery_address']."' *** respond 'bid anyway' to bid.");
		}
	}

	private function sendBidForDelivery($userId, $delivery){
		$user = $this->retrieve($userId);
		$data = array(
			'driverID' => $delivery['driverId'],
			'driverName' => $user['firstName'].' '.$user['lastName'],
			'deliveryTime' => date("Y-m-d H:i:s"),
			'deliveryID' => $delivery['id']
		);
		$response = $this->signalBidEventToUrl($data, $delivery['esl']);
		array_push($user['bidsOut'], array(
			"deliveryID"=>$delivery['id'],
			// "delivery"=>json_encode($delivery),
			"url"=>$delivery['esl'],
			"result"=>$response));
		$this->persist($user);
	}

	private function textUser($userId, $message){
		$user = $this->retrieve($userId);

		$this->load->library('twilio');

		$from = '+1 208-608-5376';
		$response = $this->twilio->sms($from, $user['phone'], $message);

		if($response->IsError) {
			echo $response->ErrorMessage;
			log_message('error', $response->ErrorMessage);
		}
		else {
			array_push($user['textsOut'], array("date"=> date('n/j g:i a'),"message"=>$message));
			$this->persist($user);
		}
	}

	public function delete($type, $i, $redirect){
		array_splice($this->driver[$type], $i, 1);
		$this->persist();
		redirect('/app/'.$redirect);
	}

	public function savePhone(){
		$phoneNum = $this->input->post('phone');
		$this->driver['phone'] = $phoneNum;
		$this->persist();
		redirect('/app');
	}

	private function signalBidEventToUrl($event, $url){
		return $this->signalEventToUrl("bid_available", $event, $url);
	}

	private function signalEventToUrl($eventName, $event, $url){
		// echo "ATTEMPTING: ".$url."<br>";
		// log_message('error', "ATTEMPTING... ".$url);
		$event['_domain'] = "rfq";
		$event['_name'] = $eventName;

		echo json_encode($event);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($event));

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		// curl_setopt($ch, CURLOPT_FAILONERROR, 0);

		$result = curl_exec($ch);
    	curl_close($ch);

    	return $result;
	}

	private function fsquareUser($id, $token){
		if($id == NULL) return NULL;
		$this->foursquare->set_current_token($token);
		$user = $this->foursquare->api('users/'.$id);
		if(!$user) return NULL;
		return $user['user'];
	}

	private function authenticate(){
		$loggedIn = $this->driver != NULL;
		if(!$loggedIn){
			redirect("/app/login");
		}
		return $loggedIn;
	}

	public function logout(){
		$this->session->unset_userdata('driver');
		redirect("/app");
		// echo "YOU ARE LOGGED OUT";
	}

	public function login($id = NULL, $token = NULL){
		if($id == NULL) {
			$this->template->page("login_view");
			// echo "LOG IN HERE";
			// HERE IS THE HOME VIEW SHOWING AN ARRAY OF ALL USERS.......
			// else {
			// 	$json = $this->getJSON();
			// 	$users = array();
			// 	if($json != NULL){
			// 		$userToken = $this->getLoggedInToken();
			// 		foreach($json as $id => $user){
			// 			// $aToken = $userToken != NULL ? $userToken : $token;
			// 			if(isset($user['token']))
			// 				array_push($users, $this->getUser($id, $user['token']));
			// 		}
			// 	}
			// 	$userData = $this->getUserData();

			// 	$this->template->page("home_view", array("userData"=>$userData, "title"=>"Driver Portal", "users"=>$users));
			// }
		}
		else {
			$driver = $this->retrieve($id);
			if($driver == NULL){
				$fsquare = $this->fsquareUser($id, $token);
				$lastCheckin = $fsquare['checkins']['count']>0 ? $fsquare['checkins']['items'][0] : array();
				$this->persist(array(
						"id"=>$id,
						"token"=>$token,
						"phone"=>"",
						"photoUrl"=>$fsquare['photo'],
						"firstName"=>$fsquare['firstName'],
						"lastName"=>$fsquare['lastName'],
						"last_checkin"=>$lastCheckin,
						"esls"=>array(),
						"textsIn"=>array(),
						"textsOut"=>array(),
						"bidsIn"=>array(),
						"bidsOut"=>array(),
					));
			}
			$this->session->set_userdata("driver", $id);
		}
	}

	public function fsCheckin(){
		// $user = $this->retrieve("47236005");
		$checkin = json_decode($this->input->post("checkin"), true);
		$id = $checkin['user']['id'];
		$user = $this->retrieve($id);
		$user['last_checkin'] = $checkin;
		$this->persist($user);
	}

	public function sampleData(){
		$id = "47236005";
		$token = "XPVQQZFYR2TUS2DV4F1QQBGOXJX1MRJ3GFGX05QL44FLWJIB";
		if(file_exists("drivers/".$id))
			unlink("drivers/".$id);
		$this->login($id, $token);
		echo "YOU ARE LOGGED IN";
	}

	public function user($driverId = NULL){
		if($driverId != NULL) echo json_encode($this->retrieve($driverId));
		else {
			$id = $this->session->userdata('driver');
			if($id == NULL) return NULL;
			return $this->retrieve($id);
		}
	}

	private function persist($driver = NULL){
		if(!$driver) $driver = $this->driver;
		file_put_contents('drivers/'.$driver['id'], base64_encode(serialize($driver)));
	}

	private function retrieve($id){
		if(file_exists('drivers/'.$id))
			return unserialize(base64_decode(file_get_contents('drivers/'.$id)));
		else return NULL;
	}
}

/* End of file app.php */
/* Location: ./application/controllers/app.php */