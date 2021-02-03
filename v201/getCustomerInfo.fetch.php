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
    if(!($oursAccess->isTokenValid($_POST['token'], "ours_customers"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['englishName']);
    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);

    $connRes = MysqlConfig::connRes($englishName);
    $resAccess = new MysqldbAccess($connRes);

    $userPhone = $oursAccess->select("phone", "ours_customers", "`token`='$token'");
    $customerInfo = $resAccess->select("*", "restaurant_customers","`phone`='$userPhone'");

    if(sizeof($customerInfo) > 3){
        $customerInfo_arranged = array(
            'phone'=> $customerInfo['phone'],
            'totalBought'=> $customerInfo['total_price'],
            'orderTimes'=> $customerInfo['order_times'],
            'score'=> $customerInfo['score'],
            'orderList'=> $customerInfo['order_list'],
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