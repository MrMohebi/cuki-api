<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(strlen($_POST['phone']) > 10 && $_POST['phone'][0] == 0 && strlen($_POST['verification_code']) == 6){
    include_once 'db/db.config.php';

    $phone =  mysqli_real_escape_string($conn_database_ours, $_POST['phone']);
    $verification_code =  mysqli_real_escape_string($conn_database_ours, $_POST['verification_code']);
    $userToken = "";

    // check if user entered its necessary info
    $verification_status = false;
    $flag_necessary_info_was_entered = true;
    $sql_get_customer_info = "SELECT * FROM ours_customers WHERE `phone`='$phone';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            if($verification_code == $row['verification_code']){
                $verification_status = true;
                $userToken = $row["token"];
                if($row["name"] != null){
                    $flag_necessary_info_was_entered = false;
                }
            }
        }
    }

    if ($verification_status) {
        if(strlen($userToken) > 30){
            $sql_set_verification_code_null = "UPDATE ours_customers SET `verification_code` = null, `modified_date`= '$nowTimestamp' WHERE `phone`='$phone';";
            if(mysqli_query($conn_database_ours, $sql_set_verification_code_null)){
                exit(json_encode(array('statusCode'=>200, 'missingUserInfo'=> $flag_necessary_info_was_entered, 'token'=>$userToken)));
            }else{
                exit(json_encode(array('statusCode'=>500)));
            }
        }else{
            exit(json_encode(array('statusCode'=>401)));
        }
    }else{
        exit(json_encode(array('statusCode'=>300)));
    }

}else{
    exit(json_encode(array('statusCode'=>400)));
}