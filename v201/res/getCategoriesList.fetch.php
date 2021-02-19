<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['token'])){
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

    $resEnglishName = $oursAccess->select('english_name','restaurants',"`token`='".$_POST['token']."'");

    $catsList = $oursAccess->select("*", "food_group", "`res_english_name`='$resEnglishName' OR `res_english_name`='general'");
    exit(json_encode(array('statusCode'=>200, 'data'=>$catsList ? $catsList : array())));

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs!")));
}
