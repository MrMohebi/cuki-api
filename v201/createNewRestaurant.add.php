<?php

use BDsConfig\MysqlConfig;
use DBs\MysqldbAccess;

header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if( isset($_POST['username']) && isset($_POST['password']) && ($_POST['english_name'] != "")){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $username = mysqli_real_escape_string($connOurs, $_POST['username']);
    $password = mysqli_real_escape_string($connOurs, $_POST['password']);
    $persian_name = mysqli_real_escape_string($connOurs, $_POST['persian_name']);
    $english_name = mysqli_real_escape_string($connOurs, $_POST['english_name']);
    $phone = mysqli_real_escape_string($connOurs, $_POST['phone']);
    $db_name = mysqli_real_escape_string($connOurs, $_POST['db_name']);
    $payment_key = mysqli_real_escape_string($connOurs, $_POST['payment_key']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);


    $flag_duplicate = $oursAccess->noDuplicate(array(
        "username"=>$username,
        "persian_name"=>$persian_name,
        "english_name"=>$english_name,
        "phone"=>$phone,
        "db_name"=>$db_name
        ), "restaurants");
    if($flag_duplicate)
        exit(json_encode(array('statusCode'=>402, "details"=>"some of info are duplicate")));


    $insertNewResParams = array(
        "username"=>$username,
        "password"=>$hashed_password,
        "persian_name"=>$persian_name,
        "english_name"=>$english_name,
        "phone"=>$phone,
        "db_name"=>$db_name,
        "payment_key"=>$payment_key,
        "position"=>"admin",
        "modified_date"=>time(),
    );

    if($oursAccess->insert("restaurants", $insertNewResParams)){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong during creating new restaurant on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}