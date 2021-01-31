<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['name'])) > 2){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);
    $name = mysqli_real_escape_string($connOurs, $_POST['name']);
    $birthday = ($_POST['birthday'] > 1) ? mysqli_real_escape_string($connOurs, $_POST['birthday']) : null ;
    $job = (strlen($_POST['job']) > 2) ? mysqli_real_escape_string($connOurs, $_POST['job']) : null ;

    // is token valid
    if(!($oursAccess->isTokenValid($_POST['token'], "ours_customers"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $setUserInfoUpdateParams = array(
        'name'=>$name,
        'birthday'=>$birthday,
        'job'=>$job,
        'status'=>'active',
        'amount'=>0,
        'off_codes'=>'[]',
        'favorite_places'=>'[]',
        'modified_date'=>time(),
    );

    if($oursAccess->update('ours_customers', $setUserInfoUpdateParams, "`token`='$token'")){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong! try again")));
    }
}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}