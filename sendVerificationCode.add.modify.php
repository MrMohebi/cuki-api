<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(strlen($_POST['phone']) > 10 && $_POST['phone'][0] == 0){
    include_once 'db/db.config.php';
    include_once 'smsService/smsKinds.php';

    $randomNum = rand(111111,999999);
    $userToken = bin2hex(openssl_random_pseudo_bytes(32));
    $phone =  mysqli_real_escape_string($conn_database_ours, $_POST['phone']);

    // check if there is no duplicate
    $flag_duplicate = false;
    $sql_get_duplicates = "SELECT * FROM ours_customers WHERE `phone`='$phone';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_duplicates)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $flag_duplicate = true;
        }
    }
    if ($flag_duplicate) {
        $sql_save_verification_code = "UPDATE ours_customers SET `verification_code`='$randomNum' WHERE `phone`='$phone';";
        mysqli_query($conn_database_ours, $sql_save_verification_code);
    }else {
        $sql_create_new_user = "INSERT INTO ours_customers(`phone`, `verification_code`, `token`, `modified_date`) VALUES ('$phone',  '$randomNum', '$userToken', '$nowTimestamp');";
        mysqli_query($conn_database_ours, $sql_create_new_user);
    }

    include_once 'smsService/ghasedak/src/GhasedakApi.php';
    $api = new \Ghasedak\GhasedakApi( '65debc3160a00153e34b72f53a6bae08b18192663636aca99f3e942567a71d89');
    $api->Verify($phone, 1, $VERIFICATION_LOGIN, $randomNum);
    exit(json_encode(array('statusCode'=>200, 'phone'=>$phone)));

}else{
    exit(json_encode(array('statusCode'=>400)));
}