<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if($_POST['token'] == $TOKEN_RESTAURANT_ADMIN || $_POST['token'] == $TOKEN_RESTAURANT_WAITER || $_POST['token'] == $TOKEN_RESTAURANT_KITCHEN ||$_POST['token'] == $TOKEN_RESTAURANT_COUNTER){
    include_once 'db/db.config.php';
    $conn_database_restaurant = $dbs[$_POST['englishName']];

    $englishName = mysqli_real_escape_string($conn_database_restaurant, $_POST['englishName']);
    $startDate = mysqli_real_escape_string($conn_database_restaurant, $_POST['startDate']);
    $endDate = mysqli_real_escape_string($conn_database_restaurant, $_POST['endDate']);

    // check dates are correct
    if($startDate > $endDate)
        exit(json_encode(array('statusCode'=>400)));

    $orders_list_arr = array();
    $sql_get_orders_list = "SELECT * from orders WHERE `ordered_date` BETWEEN '$startDate' AND  '$endDate' ORDER BY ordered_date DESC;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_orders_list)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $user_info = getUserInfo($conn_database_ours, $row['customer_phone']);
            $row["customer_name"]= $user_info['name'];
            array_push($orders_list_arr, $row);
        }
    }

    if(sizeof($orders_list_arr) > 0){
        exit(json_encode(array('statusCode'=>200, 'data'=>$orders_list_arr)));
    }else{
        exit(json_encode(array('statusCode'=>400)));
    }

}else{
    exit(json_encode(array('statusCode'=>401)));
}




function getUserInfo($conn, $phone){
    $user_info = array();
    $sql_get_user_info = "SELECT * from ours_customers WHERE `phone`='$phone';";
    if ($result = mysqli_query($conn, $sql_get_user_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $user_info =  $row;
        }
    }
    return $user_info;
}