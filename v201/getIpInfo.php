<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

$ip = $_SERVER['REMOTE_ADDR'];

$requestHandler = curl_init();
curl_setopt($requestHandler, CURLOPT_URL, "https://ipinfo.io/$ip/json");
curl_setopt($requestHandler, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($requestHandler, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));

$result = json_decode(curl_exec($requestHandler), true);
curl_close($requestHandler);

exit(json_encode(array(
    'ip'=>$result['ip'],
    'city'=>$result['city'],
    'country'=>$result['country'],
    'location'=>$result['loc'],
    'timezone'=>$result['timezone']
)));