<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['token'])) > 20){
    include_once 'db/db.config.php';

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $name = mysqli_real_escape_string($conn_database_ours, $_POST['name']);
    $birthday = mysqli_real_escape_string($conn_database_ours, $_POST['birthday']);
    $job = mysqli_real_escape_string($conn_database_ours, $_POST['job']);

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);

    // check user token is valid
    $user_phone = "";
    $sql_get_customer_info = "SELECT * FROM ours_customers WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $user_phone = $row["phone"];
        }
    }
    if($user_phone[0] != "0" ){
        exit(json_encode(array('statusCode'=>401)));
    }


    if(isset($_POST['nameChange']) && (strlen($name) >2)){
        $sql_change_name = "UPDATE ours_customers SET `name`='$name', `modified_date`='$nowTimestamp' WHERE `token`='$token';";
        if(mysqli_query($conn_database_ours, $sql_change_name)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['birthdayChange'])){
        $sql_change_birthday = "UPDATE ours_customers SET `birthday`='$birthday', `modified_date`='$nowTimestamp' WHERE `token`='$token';";
        if(mysqli_query($conn_database_ours, $sql_change_birthday)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['jobChange']) && (strlen($job) >2)){
        $sql_change_job = "UPDATE ours_customers SET `job`='$job', `modified_date`='$nowTimestamp' WHERE `token`='$token';";
        if(mysqli_query($conn_database_ours, $sql_change_job)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    exit(json_encode(array('statusCode'=>400)));

}else{
    exit(json_encode(array('statusCode'=>400)));
}