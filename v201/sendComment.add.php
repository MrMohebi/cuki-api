<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(strlen($_POST['token']) > 10 && isset($_POST['trackingId'])){
    include_once 'db/db.config.php';
    $conn_database_restaurant = $dbs[$_POST['englishName']];

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $order_tracking_id = mysqli_real_escape_string($conn_database_restaurant, $_POST['trackingId']);
    $body = mysqli_real_escape_string($conn_database_restaurant, $_POST['commentText']);
    $order_type = mysqli_real_escape_string($conn_database_restaurant, $_POST['orderType']);
    $pros_cons =  str_replace("\\","",mysqli_real_escape_string($conn_database_restaurant, $_POST['prosCons']));
    $rate = mysqli_real_escape_string($conn_database_restaurant, $_POST['rate']);

    // get user info
    $phone = false;
    $name = false;
    $sql_get_user_info = "SELECT * FROM ours_customers WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_user_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $phone = $row['phone'];
            $name = $row['name'];
        }
    }

    // get order list
    $order_list = false;
    $sql_get_order_info = "SELECT * FROM orders WHERE `tracking_id`='$order_tracking_id';";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_order_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $order_list = $row['order_list'];
        }
    }


    if($phone){
        $sql_save_comment = "INSERT INTO 
            comments(`phone`, `name`, `order_list`, `body`, `rate`, `order_type`, `pros_cons`, `status`,        `commented_date`, `modified_date`, `tracking_id`) 
            VALUES  ('$phone','$name','$order_list','$body','$rate','$order_type', '$pros_cons','notConfirmed', '$nowTimestamp',  '$nowTimestamp', '$order_tracking_id');";

        if(mysqli_query($conn_database_restaurant, $sql_save_comment)){
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