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


    $customerList = $resAccess->select("*", "restaurant_customers");

    $customerList = isset($customerList['restaurant_customers_id']) ? array($customerList) : ($customerList ? $customerList : array());
    $customerListWithName = addCustomerDynamicInfo($customerList, $oursAccess);
    exit(json_encode(array('statusCode'=>200, 'data'=>$customerListWithName)));

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs!")));
}

function addCustomerDynamicInfo($customerList, $oursAccess):array {
    for ($i=0; $i < count($customerList);$i++){
        $ePhone = $customerList[$i]['phone'];
        $userInfo = $oursAccess->select('*', 'ours_customers', "`phone`='$ePhone'");
        $customerList[$i]['name'] = $userInfo['name'];
        $customerList[$i]['birthday'] = $userInfo['birthday'];
        $customerList[$i]['job'] = $userInfo['job'];
    }
    return $customerList;
}