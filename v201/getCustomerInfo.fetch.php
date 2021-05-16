<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!($oursAccess->isTokenValid($_POST['token'], "users"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['englishName']);
    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);

    $connRes = MysqlConfig::connRes($englishName);
    $resAccess = new MysqldbAccess($connRes);

    $userPhone = $oursAccess->select("phone", "users", "`token`='$token'");
    $customerInfo = $resAccess->select("*", "customers","`phone`='$userPhone'");

    $ordersListInfo = $resAccess->select('*', "orders", "`user_phone`='$userPhone'","`created_at` DESC LIMIT 40");

    if(sizeof($customerInfo) > 3){
        $customerInfo_arranged = array(
            'phone'=> $customerInfo['phone'],
            'totalBought'=> $customerInfo['total_order_price'],
            'orderTimes'=> $customerInfo['order_times'],
            'score'=> $customerInfo['score'],
            'orderList'=> $ordersListInfo,
            'rank'=> $customerInfo['rank'],
            'lastOrderDate'=> $customerInfo['modified_at'],
        );
        exit(json_encode(array('statusCode'=>200, 'data'=>$customerInfo_arranged)));
    }else{
        exit(json_encode(array('statusCode'=>404, "details"=>"customer didn't find OR didn't order anything from here yet")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}


function characterFixer($str){
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
    }, $str);
}