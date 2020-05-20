<?php
/**
* Notification Manager
*/
//TODO: Working timestamp to body or $title
class NotificationManager
{
	function addSubscriber($userID = '', $token = ''){
		$notificationSubscriber = $subDeviceId = Db::loadOne('SELECT id FROM notifications WHERE token = ?;', array($token));
		if ($notificationSubscriber == ''){
			$notification = array (
				'user_id' => $userID,
				'token' => $token,
			);
			Db::add ('notifications', $notification);
		}
	}

	function getSubscription () {
		return Db::loadAll ("SELECT * FROM notifications");
	}

	function sendSimpleNotification(string $serverKey, string $to, array $data, bool $timeStamp = false){
		$dataTemplate = [
			'title' => '',
			'body' => '',
			'icon' => '',
		];

		if (array_diff_key ($dataTemplate , $data)){
			return;
		}

		if ($timeStamp) {
			$data['title'] = $data['title'] . date();
		}

		$notification = new Notification($serverKey);
		$notification->to($to);
		$notification->notification($data['title'], date("h:i") . " - " . $data['body'], $data['icon'], '');
		$answer = $notification->send();
		$notification = null;

		return $answer;
	}
}

class Notification
{
	public $server_key = '';
	public $jsonPayload = [
		"to" => '',
		"data" => [
			"notification" => [
				"body" => '',
				"title" => '',
				"icon" => '',
				"click_action" => '',
			]
		]
	];

	function __construct($serverKey = '')
	{
		$this->server_key = $serverKey;
	}

	function to($to = ''){
		$this->jsonPayload["to"] = $to;
	}

	function notification($title = '', $body = '', $icon = '', $action = '', bool $timeStamp = false)
	{
		if ($timeStamp) {
			$data['title'] = $data['title'] . date();
		}

		$this->jsonPayload["data"]["notification"]["title"] = $title;
		$this->jsonPayload["data"]["notification"]["body"] = date("h:i") . " - " . $body;
		$this->jsonPayload["data"]["notification"]["icon"] = $icon;
		$this->jsonPayload["data"]["notification"]["click_action"] = $action;
	}

	function send(){
		$data = json_encode($this->jsonPayload);
		$url = 'https://fcm.googleapis.com/fcm/send';
		$headers = array(
			'Content-Type:application/json',
			'Authorization:key='.$this->server_key,
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		if ($result === FALSE) {
			die('Oops! FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);
		return $result;
	}
}
