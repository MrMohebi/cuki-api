<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['resEnglishName'])&&isset($_POST['ip'])&&isset($_POST['userAgent'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $resEnglishName =  mysqli_real_escape_string($connOurs, $_POST['resEnglishName']);
    $ip =  mysqli_real_escape_string($connOurs, $_POST['ip']);
    $isp =  mysqli_real_escape_string($connOurs, $_POST['isp']);
    $city =  mysqli_real_escape_string($connOurs, $_POST['city']);
    $userAgent =  mysqli_real_escape_string($connOurs, $_POST['userAgent']);

    $newUserInfo_str = json_encode(array(
        'resEnglishName'=>$resEnglishName,
        'ip'=>$ip,
        'isp'=>$isp,
        'city'=>$city,
        'userAgent'=>$userAgent,
    ));

    // generate new temp token
    $userToken = "TEMPUSER_".bin2hex(openssl_random_pseudo_bytes(32));

    $insertUserParams = array(
        'type'=>"temp",
        'token'=>$userToken,
        'info'=>$newUserInfo_str,
        'phone'=>'RAN'.rand(11111111,99999999),
        "modified_date"=>time(),
    );



    if($oursAccess->insert('ours_customers', $insertUserParams)){
        exit(json_encode(
            array(
                'statusCode'=>200,
                'data'=>array(
                    'token'=> $userToken
                )
            )
        ));
    }else{
        exit(json_encode(array('statusCode'=>500, 'details'=>"some thing went wrong! try again")));
    }


}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}
