<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token'])){
    include_once 'db/db.config.php';

    $conn_database_restaurant = $dbs[$_POST['englishName']];
    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $tracking_id =  json_decode(mysqli_real_escape_string($conn_database_restaurant, $_POST['trackingId']));

    $userPhone = "";
    $sql_get_customer_info = "SELECT * FROM ours_customers WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $userPhone = $row['phone'];
        }
    }
    if(strlen($userPhone) != 11){
        exit(json_encode(array('statusCode'=>401)));
    }

    $orderInfo = array();
    foreach ($tracking_id as $eachTrackingId){
        $sql_get_order_info = "SELECT * FROM orders WHERE `tracking_id`='$eachTrackingId';";
        if ($result = mysqli_query($conn_database_restaurant, $sql_get_order_info)) {
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($orderInfo,$row);
            }
        }
    }


    if (sizeof($orderInfo) == 1) {
        exit(json_encode(array('statusCode'=>200, 'data'=>$orderInfo[0])));
    }else if(sizeof($orderInfo) > 1){
        exit(json_encode(array('statusCode'=>200, 'data'=>$orderInfo)));
    }else{
        exit(json_encode(array('statusCode'=>402)));
    }

}else{
    exit(json_encode(array('statusCode'=>400, "test"=>$_POST)));
}