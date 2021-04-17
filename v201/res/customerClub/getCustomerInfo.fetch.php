<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token'])){
    include_once "../../DataAccess/MysqldbAccess.php";
    include_once "../../DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!(
        $oursAccess->isTokenValid($_POST['token'], "restaurants")&&
        $oursAccess->hasTokenAccess($_POST['token'], "restaurants", array("admin", "counter", "waiter"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    $connRes = MysqlConfig::connRes($oursAccess->select('english_name','restaurants',"`token`='".$_POST['token']."'"));
    $resAccess = new MysqldbAccess($connRes);



    $userPhone = trim(mysqli_real_escape_string($connRes, $_POST['phone']));
    $customerInfo = $resAccess->select("*", "restaurant_customers","`phone`='$userPhone'");

    $ordersListInfo = $resAccess->select('*', "orders", "`customer_phone`='$userPhone'","`ordered_date` DESC LIMIT 300");

    if(sizeof($customerInfo) > 3){
        $customerInfo_arranged = array(
            'phone'=> $customerInfo['phone'],
            'totalBought'=> $customerInfo['total_price'],
            'orderTimes'=> $customerInfo['order_times'],
            'score'=> $customerInfo['score'],
            'orderList'=> $ordersListInfo,
            'rank'=> $customerInfo['rank'],
            'lastOrderDate'=> $customerInfo['modified_date'],
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