<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'smsService/smsKinds.php';

if(strlen($_POST['phone']) > 10 && $_POST['phone'][0] == 0){
    include_once "smsService/smsKinds.php";
    include_once "smsService/ghasedak/src/GhasedakApi.php";
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);


    $randomNum = rand(1111,9999);
    $userToken = bin2hex(openssl_random_pseudo_bytes(32));
    $phone =  mysqli_real_escape_string($connOurs, $_POST['phone']);


    // check if user registered before
    $savedPhone = $oursAccess->select('phone', "ours_customers", "`phone`='$phone'");

    if (strlen($savedPhone) == 11) {
        $sendVcodeUpdateParams = array(
            'verification_code'=>$randomNum,
            'verification_code_tries'=>0,
            'modified_date'=>time()
        );
        $oursAccess->update("ours_customers", $sendVcodeUpdateParams, "`phone`='$phone'");
    }else if(strlen($phone) == 11) {
        $createNewUserParams = array(
            'phone'=>$phone,
            'verification_code'=>$randomNum,
            'verification_code_tries'=>0,
            'modified_date'=>time()
        );
        $oursAccess->insert("ours_customers", $createNewUserParams);
    }

    $api = new \Ghasedak\GhasedakApi( '65debc3160a00153e34b72f53a6bae08b18192663636aca99f3e942567a71d89');
    $api->Verify($phone, 1, SMSKinds::VERIFICATION_LOGIN, $randomNum);

    exit(json_encode(array(
        'statusCode'=>200,
        'data'=>array(
            'phone'=>$phone
        )
    )));

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}

