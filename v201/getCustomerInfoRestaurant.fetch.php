<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['token'])) > 20){
    include_once 'db/db.config.php';

    $conn_database_restaurant = $dbs[$_POST['englishName']];
    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);

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


    $customerInfo= array();
    $sql_get_customer_info = "SELECT * FROM restaurant_customers WHERE `phone`='$userPhone';";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customerInfo = $row;
        }
    }



    if(sizeof($customerInfo) > 3){
        $customerInfo_arranged = array(
            'phone'=> $customerInfo['phone'],
            'totalBought'=> $customerInfo['total_price'],
            'orderTimes'=> $customerInfo['order_times'],
            'score'=> $customerInfo['score'],
            'orderList'=> $customerInfo['order_list'],
            'rank'=> $customerInfo['rank'],
            'lastOrderDate'=> $customerInfo['modified_date'],
        );

        exit(json_encode(array('statusCode'=>200, 'data'=>$customerInfo_arranged)));
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }

}else{
    exit(json_encode(array('statusCode'=>400)));
}