<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(strlen($_POST['phone']) > 10 && $_POST['phone'][0] == 0 && strlen($_POST['vCode']) >=3){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $phone =  mysqli_real_escape_string($connOurs, $_POST['phone']);
    $vCode =  mysqli_real_escape_string($connOurs, $_POST['vCode']);

    // generate new token
    $userToken = bin2hex(openssl_random_pseudo_bytes(32));

    $userInfo = $oursAccess->select("*", "ours_customers", "`phone`='$phone'");

    // check user attempts times
    $oursAccess->update("ours_customers", array('verification_code_tries'=>$userInfo['verification_code_tries']+1), "`phone`='$phone'");
    if($userInfo['verification_code_tries'] > 15){
        exit(json_encode(array('statusCode'=>429, "details"=>"too many attempts! please ask for new VCode")));
    }

    // check if VCode is correct and user entered its necessary info
    $isUserInfoSaved = true;
    if(!($vCode == $userInfo['verification_code']))
        exit(json_encode(array('statusCode'=>300, "details"=>"verification code is not correct!!!")));
    if($userInfo["name"] == null || strlen($userInfo["name"]) < 1){
        $isUserInfoSaved = false;
    }



    $userLoginUpdateParams = array(
        'token'=>$userToken,
        'verification_code'=>null,
        'verification_code_tries'=>null,
        'modified_date'=>time()
    );

    if($oursAccess->update("ours_customers", $userLoginUpdateParams, "`phone`='$phone'")){
        exit(json_encode(array(
            'statusCode'=>200,
            'data'=>array(
                'isUserInfoSaved'=> $isUserInfoSaved,
                'token'=>$userToken,
                'phone'=>$phone,
            ),
        )));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong! try again")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}