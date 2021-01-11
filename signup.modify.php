<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['name'])) > 2){
    include_once 'db/db.config.php';

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $name = mysqli_real_escape_string($conn_database_ours, $_POST['name']);
    $birthday = ($_POST['birthday'] > 1) ? mysqli_real_escape_string($conn_database_ours, $_POST['birthday']) : null ;
    $job = (strlen($_POST['job']) > 2) ? mysqli_real_escape_string($conn_database_ours, $_POST['job']) : null ;

    // check token
    $user_phone = false;
    $sql_get_reserved_tables_list = "SELECT * FROM ours_customers WHERE `token` = '$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_reserved_tables_list)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $user_phone = $row['phone'];
        }
    }

    $sql_set_user_info = "UPDATE ours_customers SET
                            `name` = '$name',
                            `birthday` = '$birthday',
                            `job` = '$job',
                            `status` = 'active',
                            `amount` = '0',
                            `off_codes` = '[]',
                            `favorite_places`  = '[]',
                            `modified_date`= '$nowTimestamp'
                        WHERE `token`='$token';";

    if($user_phone){
        if(mysqli_query($conn_database_ours, $sql_set_user_info)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }else{
        exit(json_encode(array('statusCode'=>401)));
    }

}else{
    exit(json_encode(array('statusCode'=>400)));
}