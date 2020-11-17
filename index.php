<?php
/**
 * Created by PhpStorm.
 * User: pchomeec0003
 * Date: 2019/1/16
 * Time: 2:40 PM
 */

$token = '';
$platform = 'FCM';
$type = '';
$authKey = '';
$inputJson = '';
$fcmOptions = '';

if (!empty($_POST)) {
	$token = $_POST['device-token'];
	$type = $_POST['push-notification-type'];
	$authKey = $_POST['push-notification-server-key'];
	$inputJson = !empty($_POST['push-json']) ? $_POST['push-json'] : '';
	$fcmOptions = !empty($_POST['fcm-option']) ? $_POST['fcm-option'] : '';
}
$push_result = null;
$send_data = '';

if (!empty($token)) {
	switch ($platform) {
//	  case "apns":
//		// Sandbox mode
//		$certificateFile = 'apns-20181217.pem';
//		$pushServer = 'ssl://gateway.sandbox.push.apple.com:2195';
//		$feedbackServer = 'ssl://feedback.sandbox.push.apple.com:2196';
//
//		// push notification
//		$streamContext = stream_context_create();
//		stream_context_set_option($streamContext, 'ssl', 'local_cert', $certificateFile);
//		$fp = stream_socket_client(
//		  $pushServer,
//		  $error,
//		  $errorStr,
//		  100,
//		  STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT,
//		  $streamContext
//		);
//
//		// make payload
//		$payloadObject = array(
//		  'aps' => array(
//			  'alert' => [
//				  'title' => '測試用',
//				  'body' => '測試訊息:'.date('Y-m-d H:i:s'),
//			  ],
//			  'sound' => 'default',
//			  'badge' => rand(0, 10),
//			  'show_in_foreground' => true
//		  ),
//		//			"url" => 'https://24h.m.pchome.com.tw/',
//		  "url" => 'https://www.pchomeec.tw/activity/AC00051773?_ga=2.57675754.1146241511.1545015507-1143797601.1544521111',
//		//			"url" => 'https://24h.m.pchome.com.tw/NFC/scan',
//		  "click_url" => 'https://ecvip.pchome.com.tw/emon/v1/utm.htm%3Fid%3D000000000a%26action%3Dclick%26v%3Dmobile%26desc%3D%E6%8E%A8%E6%92%AD%E9%BB%9E%E6%93%8A%E6%95%B8-%E8%A8%82%E5%96%AE%E9%80%9A%E7%9F%A5',
//		);
//		$payload = json_encode($payloadObject);
//
//		$deviceToken = $token;
//		$expire = time() + 3600;
//		$id = time();
//
//		if ($expire) {
//		  // Enhanced mode
//		  $binary  = pack('CNNnH*n', 1, $id, $expire, 32, $deviceToken, strlen($payload)).$payload;
//		} else {
//		  // Simple mode
//		  $binary  = pack('CnH*n', 0, 32, $deviceToken, strlen($payload)).$payload;
//		}
//		$push_result = fwrite($fp, $binary);
//		fclose($fp);
//
//	  	break;
	  default:
		switch ($type) {
			case 'topic':
				$registrationIds = [$token];
				$data = getPushData($type, '/topics/topic-test', $inputJson, $fcmOptions);
				// Token 加入 Topic
				$push_result["topic"] = sendToServer('https://iid.googleapis.com/iid/v1:batchAdd',
				['to'=>'/topics/topic-test', 'registration_tokens'  => $registrationIds], $authKey);
				
				$push_result["send_result"] = sendToServer('https://fcm.googleapis.com/fcm/send', $data, $authKey);
				break;
			default:
			$send_data = getPushData($type, $token, $inputJson, $fcmOptions);
			$push_result =  sendToServer('https://fcm.googleapis.com/fcm/send', $send_data,
                $authKey
			);
			break;
		}
	  	
	  	break;
	}
}

function getPushData($type, $token, $inputJson, $fcmOptions) {
	$jsonData = [
		'to' => $token,
		'notification' => array (
			"title" => "測試測試測測測",
			"body" => "推播內文"
		),
		'data' => array (
			"message" => "推播內文",
			"title" => "測試測試測測測",
			"url" => 'https://24h.m.pchome.com.tw/',
			"click_url" => 'https://ecvip.pchome.com.tw/emon/v1/utm.htm%3Fid%3D000000000a%26action%3Dclick%26v%3Dmobile%26desc%3D%E6%8E%A8%E6%92%AD%E9%BB%9E%E6%93%8A%E6%95%B8-%E8%A8%82%E5%96%AE%E9%80%9A%E7%9F%A5',
			"badge" => 99,
		),
		"priority" => 'high',
		"mutable_content" => true,
	    "content_available" => true
	];
	
	if (!empty($inputJson)) {
		$inputData = !empty($inputJson) ? json_decode($inputJson, true) : '';
		$jsonData['notification']['title'] = $inputData['title'];
		$jsonData['notification']['body'] = $inputData['message'];
		$jsonData['data'] = $inputData;
	}
	
	if (!empty($fcmOptions)) {
		$jsonData['fcm_options'] = $fcmOptions;
	}
	
	return $jsonData;
}

function sendToServer($url, $fields, $authKey) {
	$fields = json_encode ( $fields );
	
	$headers = array (
		'Authorization: key=' . $authKey,
		'Content-Type: application/json'
	);
	
	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL, $url );
	curl_setopt ( $ch, CURLOPT_POST, true );
	curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
	
	$result = curl_exec ( $ch );
	curl_close ( $ch );
	
	return $result;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Title</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js" integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
	<div class="text-center p-2 border-bottom">
		<span class="h1">測試專區</span>
	</div>
	<div class="container pt-4" id="accordionExample">
		<!-- 推播!-->
		<div class="card">
			<div class="card-header" id="headingThree">
				<h5 class="mb-0">
					<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
						推播測試專區
					</button>
				</h5>
			</div>
			<div id="collapseThree" class="collapse <?php echo !empty($push_result) ? 'show' : '';?>" aria-labelledby="headingThree" data-parent="#accordionExample">
				<div class="card-body">
					<form class="container text-center" action="index.php" method="post">
						<div class="form-group row">
							<label class="col-sm-2 col-form-label" for="device-token">推播 Token：</label>
							<div class="col-sm-10">
								<input type="text" class="form-control" name="device-token" placeholder="Token" value="<?php echo !empty($token) ? $token : '';?>">
							</div>
						</div>
						<div class="form-row border-top pt-3">
							<div class="col-12 form-group row">
								<label class="col-sm-2 col-form-label" for="push-notification-type">推播類型：</label>
								<div class="col-sm-10">
									<select class="form-control" name="push-notification-type">
										<option value="general" <?php echo !empty($type) && $type === 'general' ? 'selected' : '';?>>一般</option>
										<option value="topic" <?php echo !empty($type) && $type === 'topic' ? 'selected' : '';?>>Topic</option>
									</select>
								</div>
							</div>
                            <div class="col-12 form-group row">
                                <label class="col-sm-2 col-form-label" for="push-notification-type">SERVER-KEY：</label>
                                <div class="col-sm-10">
                                    <input class="form-control" name="push-notification-server-key" type="text" placeholder="請輸入FCM ServerKey" value="<?php echo !empty($authKey) ? $authKey : '';?>"/>
                                </div>
                            </div>
							<div class="col-12 form-group row">
								<label class="col-sm-2 col-form-label" for="push-notification-type">推播內容：</label>
								<div class="col-sm-10">
									<textarea class="form-control" name="push-json" placeholder="請輸入推播內容(請輸入物件，內容一定要有 title, message 兩個 key 值)" rows="4" cols="50"><?php echo !empty($inputJson) ? $inputJson : '';?></textarea>
								</div>
							</div>
							<div class="col-12 form-group row">
								<label class="col-sm-2 col-form-label" for="push-notification-type">FCM OPTIONS：</label>
								<div class="col-sm-10">
									<textarea class="form-control" name="fcm-option" placeholder="Fcm Options" rows="4" cols="50"><?php echo !empty($fcmOptions) ? $fcmOptions : '';?></textarea>
								</div>
							</div>
						</div>
						<div class="form-group text-right">
							<button type="submit" class="btn btn-primary">送出</button>
						</div>
					</form>
						<?php
							if (!empty($push_result)) {
						?>
							<div class="card">
								<div class="card-header" id="headingThree">
									<h6 class="mb-0">傳送結果</h6>
								</div>
								<div class="card-body">
									<div>
										<?php print_r($push_result);?>
									</div>
									<hr>
									<div>
										<?php
											!empty($send_data) && print_r($send_data);
										?>
									</div>
								</div>
							</div>
						<?php
							}
						?>
				</div>
			</div>
		</div>
		<!-- Scheme!-->
		<div class="card mt-2">
			<div class="card-header" id="headingScheme">
				<h5 class="mb-0">
					<button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseScheme" aria-expanded="false" aria-controls="collapseScheme">
						Scheme專區
					</button>
				</h5>
			</div>
			<div id="collapseScheme" class="collapse" aria-labelledby="headingScheme" data-parent="#accordionExample">
				<div class="card-body text-center">
					<div class="form-group row">
						<label class="col-sm-2 col-form-label" for="device-token">Scheme Url：</label>
						<div class="col-sm-10 row">
							<div class="col-10">
								<input type="text" class="form-control" id="scheme-url" placeholder="Scheme Url"/>
							</div>
							<div class="col-2 text-right">
								<button type="button" class="btn btn-primary" onclick="schemeTo();">送出</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html>

<script>
	$(document).ready(function () {
	  // location.href="mitch://navigate?to=https://google.com.tw";
	});
	
	function schemeTo () {
	  var schemeUrl = $("#scheme-url").val();
	  
	  if (schemeUrl) {
	    location.href=schemeUrl;
	  }
    }
</script>
