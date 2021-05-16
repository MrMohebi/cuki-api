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
        $oursAccess->hasTokenAccess($_POST['token'], "restaurants", array("admin"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    $token = trim(mysqli_real_escape_string($connOurs, $_POST['token']));
    $previousPass = trim(mysqli_real_escape_string($connOurs, $_POST['previousPass']));
    $newPass = trim(mysqli_real_escape_string($connOurs, $_POST['newPass']));

    if(strlen($newPass) < 8 )
        exit(json_encode(array('statusCode'=>400, "details"=>"password is too short, at least 8 character")));

    $resUserInfo = $oursAccess->select("*", "restaurants", "`token`='$token'");

    if(password_verify($previousPass, $resUserInfo['password'])){
        if($oursAccess->update("restaurants", array('password'=>password_hash($newPass, PASSWORD_DEFAULT), 'modified_at'=>time()), "`token`='$token'")){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500, "details"=>"something went wrong during changing password")));
        }
    }else{
        exit(json_encode(array('statusCode'=>401, "details"=>"previous password is wrong")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}
