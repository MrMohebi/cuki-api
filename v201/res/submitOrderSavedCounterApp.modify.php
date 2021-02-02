<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['token']) && isset($_POST['trackingId'])){
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

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

    $trackingId = mysqli_real_escape_string($connRes, $_POST['trackingId']);


    if($resAccess->update('orders',array('counter_app_status'=>1), "`tracking_id`='$trackingId'")){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"something went wrong! try again")));
    }




}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs!")));
}
