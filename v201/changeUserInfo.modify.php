<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['token'])) > 20){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);
    $name = mysqli_real_escape_string($connOurs, $_POST['name']);
    $birthday = mysqli_real_escape_string($connOurs, $_POST['birthday']);
    $job = mysqli_real_escape_string($connOurs, $_POST['job']);


    // is token valid
    if(!($oursAccess->isTokenValid($_POST['token'], "users"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $userInfo = $oursAccess->select("*", "users", "`token`='$token'");

    $newUserInfoUpdateParams = array(
        'name'=>strlen($name) > 2 ? $name : $userInfo['name'],
        'birthday'=>$birthday > 100 ? $birthday : $userInfo['birthday'],
        'job'=>strlen($job) > 2 ?  $job :  $userInfo['job'],
    );

    if($oursAccess->update('users', $newUserInfoUpdateParams, "`token`='$token'")){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong! try again")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}