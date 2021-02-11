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


    $openOrdersList = $resAccess->select("*", "orders", "`customer_phone`='$userPhone' AND `order_status`!='deleted' AND `order_status`!='done'");

    if(sizeof($openOrdersList) > 0){
        exit(json_encode(array('statusCode'=>200, 'data'=>$openOrdersList)));
    }else{
        exit(json_encode(array('statusCode'=>404, "details"=>"nothing found!")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}