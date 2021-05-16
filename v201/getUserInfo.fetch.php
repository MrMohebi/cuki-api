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


    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);

    $userInfo = $oursAccess->select("*", "users", "`token`='$token'");



    if(sizeof($userInfo) > 3){
        $customerInfo_arranged = array(
            'name'=> $userInfo['name'],
            'phone'=> $userInfo['phone'],
            'birthday'=> $userInfo['birthday'],
            'job'=> $userInfo['job'],
            'allTotalBought'=> $userInfo['amount'],
            'favoritePlaces' => json_decode($userInfo['favorite_places']),
        );
        exit(json_encode(array('statusCode'=>200, 'data'=>$customerInfo_arranged)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"something went wrong, couldn't fetch user info")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}