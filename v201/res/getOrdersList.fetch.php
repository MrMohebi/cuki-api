<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['token']) && isset($_POST['startDate']) && isset($_POST['endDate'])){
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


    $startDate = mysqli_real_escape_string($connRes, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($connRes, $_POST['endDate']);

    // check dates are correct
    if($startDate > $endDate)
        exit(json_encode(array('statusCode'=>400, "details"=>"input dates are incorrect")));


    $ordersList = $resAccess->select(
        "*",
        "orders",
        "`ordered_date` BETWEEN '$startDate' AND  '$endDate'",
        "`ordered_date` DESC"
    );
    exit(json_encode(array('statusCode'=>200, 'data'=>$ordersList ? $ordersList : array(), "test"=>$endDate)));


}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs!")));
}
