<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['englishName']);

    $connRes = MysqlConfig::connRes($englishName);
    $resAccess = new MysqldbAccess($connRes);

    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);
    $trackingIds =  json_decode(mysqli_real_escape_string($connRes, $_POST['trackingId']));

    $trackingIds = is_array($trackingIds) ? $trackingIds : array($trackingIds);

    $userInfo = $oursAccess->select("*", "ours_customers", "`token`='$token'" );

    $ordersInfo = array();
    foreach ($trackingIds as $eachTrackingId){
        $orderInfo = $resAccess->select('*', 'orders', "`tracking_id`='$eachTrackingId'");
        if($orderInfo['customer_phone'] == $userInfo['phone'])
            array_push($ordersInfo, $orderInfo);
    }


    if (sizeof($ordersInfo) == 1) {
        exit(json_encode(array('statusCode'=>200, 'data'=>$ordersInfo[0])));
    }else if(sizeof($ordersInfo) > 1){
        exit(json_encode(array('statusCode'=>200, 'data'=>$ordersInfo)));
    }else{
        exit(json_encode(array('statusCode'=>402, "details"=>"nothing found!")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}