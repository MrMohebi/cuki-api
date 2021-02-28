<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if(($_POST['token'] == $TOKEN_RESTAURANT_ADMIN) || ($_POST['token'] == $TOKEN_RESTAURANT_COUNTER) || ($_POST['token'] == $TOKEN_RESTAURANT_KITCHEN) || ($_POST['token'] == $TOKEN_RESTAURANT_WAITER) ){
    include_once 'db/db.config.php';
    $conn_database_restaurant = $dbs[$_POST['englishName']];

    $trackingId = mysqli_real_escape_string($conn_database_restaurant, $_POST['trackingId']);
    $orderStatus = mysqli_real_escape_string($conn_database_restaurant, $_POST['orderStatus']);
    $deleteReason = ($orderStatus == "deleted") ? mysqli_real_escape_string($conn_database_restaurant, $_POST['deleteReason']) : "";

    // check inputs are correct
    if(!(strlen($trackingId) == 8 && strlen($orderStatus) > 3 && strlen($_POST['englishName']) > 5)){
        exit(json_encode(array('statusCode'=>400)));
    }


    // get order info and user phone
    $orderInfo = array();
    $userPhone = "";
    $sql_get_user_info = "SELECT * FROM orders WHERE `tracking_id`='$trackingId';";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_user_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $userPhone = $row['customer_phone'];
            $orderInfo = $row;
        }
    }
    if($orderInfo['order_status'] != $orderStatus) {
        $sql_change_order_status = "UPDATE orders SET `order_status` = '$orderStatus', `delete_reason`= '$deleteReason', `modified_date` = '$nowTimestamp' WHERE `tracking_id` = '$trackingId';";
        if (mysqli_query($conn_database_restaurant, $sql_change_order_status)) {

            if (($orderStatus == "done" || $orderStatus == "deleted") && ($orderInfo['order_status'] == 'inLine')) {
                $orderInfo['order_status'] = $orderStatus;
                increaseOrderTimesOfFoods($conn_database_restaurant, $orderInfo);
                if($userPhone[0]!= "R")
                    addOrderToCustomerHistory($conn_database_restaurant, $orderInfo);
                exit(json_encode(array('statusCode' => 200)));


            } elseif ($orderStatus == "inLine") {
                if($userPhone[0]!= "R")
                    deleteOrderFromCustomerHistory($conn_database_restaurant, $orderInfo);
                exit(json_encode(array('statusCode' => 200)));

            }elseif ($orderStatus == "deleted" && ($orderInfo['order_status'] == 'done')){
                $orderInfo['order_status'] = $orderStatus;
                if($userPhone[0]!= "R")
                    changeOrderToDeletedInCustomerHistory($conn_database_restaurant, $orderInfo);
                exit(json_encode(array('statusCode' => 200)));
            }else{
                $lastOrderStatus = $orderInfo['order_status'];
                $sql_change_order_status = "UPDATE orders SET `order_status` = '$lastOrderStatus', `delete_reason`= '', `modified_date` = '$nowTimestamp' WHERE `tracking_id` = '$trackingId';";
                mysqli_query($conn_database_restaurant, $sql_change_order_status);
                exit(json_encode(array('statusCode' => 500)));
            }

        } else {
            exit(json_encode(array('statusCode' => 500)));
        }
    }else{
        exit(json_encode(array('statusCode'=>400, "info"=>"new order status is like its previous one")));
    }
}else{
    exit(json_encode(array('statusCode'=>401)));
}



function addOrderToCustomerHistory($conn_restaurant, $orderInfo){
    $nowTimestamp = time();
    $userPhone = $orderInfo['customer_phone'];

    // get customer info
    $customerInfo = array();
    $sql_get_customer_info = "SELECT * FROM restaurant_customers WHERE `phone`='$userPhone';";
    if ($result = mysqli_query($conn_restaurant, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customerInfo = $row;
        }
    }
    if(count($customerInfo) < 4){
        $customerInfo = createNewCustomer($conn_restaurant, $orderInfo['customer_phone']);
    }

    // update info
    $newOrderList = json_decode($customerInfo['order_list']);
    array_push($newOrderList, $orderInfo['tracking_id']);
    $newOrderList_str = json_encode($newOrderList);

    $newOrderTimes = $customerInfo['order_times'];
    $newScore= $customerInfo['score'];
    $newTotalPrice  = $customerInfo['total_price'];

    if($orderInfo['order_status'] == "done"){
        $newOrderTimes = $customerInfo['order_times'] + 1;
        $newScore= $customerInfo['score'] + ($orderInfo['total_price'] / 1000);
        $newTotalPrice  = $customerInfo['total_price'] + $orderInfo['total_price'];
    }

    $sql_add_order_customer_history = "UPDATE restaurant_customers SET `order_times` = '$newOrderTimes', `order_list`= '$newOrderList_str', `score`='$newScore', `total_price`='$newTotalPrice', `modified_date` = '$nowTimestamp' WHERE `phone` = '$userPhone';";
    if(mysqli_query($conn_restaurant, $sql_add_order_customer_history)){
        return true;
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }
}


function deleteOrderFromCustomerHistory($conn_restaurant, $orderInfo){
    $nowTimestamp = time();
    $userPhone = $orderInfo['customer_phone'];

    // get customer info
    $customerInfo = array();
    $sql_get_customer_info = "SELECT * FROM restaurant_customers WHERE `phone`='$userPhone';";
    if ($result = mysqli_query($conn_restaurant, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customerInfo = $row;
        }
    }
    if(count($customerInfo) < 4){
        $customerInfo = createNewCustomer($conn_restaurant, $orderInfo['customer_phone']);
    }

    // update info
    $newOrderList = array_diff(json_decode($customerInfo['order_list']), array($orderInfo['tracking_id']));

    $newOrderTimes = $customerInfo['order_times'];
    $newOrderList_str = json_encode($newOrderList);
    $newScore = $customerInfo['score'];
    $newTotalPrice = $customerInfo['total_price'];
    if($orderInfo['order_status'] == 'done') {
        $newOrderTimes = $customerInfo['order_times'] - 1;
        $newScore = $customerInfo['score'] - ($orderInfo['total_price'] / 1000);
        $newTotalPrice = $customerInfo['total_price'] - $orderInfo['total_price'];
    }

    $sql_delete_order_customer_history = "UPDATE restaurant_customers SET `order_times` = '$newOrderTimes', `order_list`= '$newOrderList_str', `score`='$newScore', `total_price`='$newTotalPrice', `modified_date` = '$nowTimestamp' WHERE `phone` = '$userPhone';";
    if(mysqli_query($conn_restaurant, $sql_delete_order_customer_history)){
        return true;
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }
}


function changeOrderToDeletedInCustomerHistory($conn_restaurant, $orderInfo){
    $nowTimestamp = time();
    $userPhone = $orderInfo['customer_phone'];

    // get customer info
    $customerInfo = array();
    $sql_get_customer_info = "SELECT * FROM restaurant_customers WHERE `phone`='$userPhone';";
    if ($result = mysqli_query($conn_restaurant, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customerInfo = $row;
        }
    }
    if(count($customerInfo) < 4){
        $customerInfo = createNewCustomer($conn_restaurant, $orderInfo['customer_phone']);
    }

    $newOrderTimes = $customerInfo['order_times'] - 1;
    $newScore = $customerInfo['score'] - ($orderInfo['total_price'] / 1000);
    $newTotalPrice = $customerInfo['total_price'] - $orderInfo['total_price'];

    $sql_delete_order_customer_history = "UPDATE restaurant_customers SET `order_times` = '$newOrderTimes', `score`='$newScore', `total_price`='$newTotalPrice', `modified_date` = '$nowTimestamp' WHERE `phone` = '$userPhone';";
    if(mysqli_query($conn_restaurant, $sql_delete_order_customer_history)){
        return true;
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }
}







function createNewCustomer($conn_restaurant, $userPhone){
    $nowTimestamp = time();

    $sql_add_customer = "INSERT INTO restaurant_customers(`phone`, `order_times`, `order_list`,`score`,`total_price`, `modified_date`) VALUES('$userPhone', '0', '[]', '0', '0', '$nowTimestamp') ;";
    if(mysqli_query($conn_restaurant, $sql_add_customer)){
        $customerInfo = array();
        $sql_get_customer_info = "SELECT * FROM restaurant_customers WHERE `phone`='$userPhone';";
        if ($result = mysqli_query($conn_restaurant, $sql_get_customer_info)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $customerInfo = $row;
            }
        }
        return $customerInfo;
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }
}



function increaseOrderTimesOfFoods($conn_restaurant,$orderInfo){
    $orderList = json_decode($orderInfo["order_list"], true);
    foreach ($orderList as $eachFood){
        $foodId = $eachFood["id"];
        $foodNumbers = $eachFood["number"];
        $sql_update_food_order_times = "UPDATE foods SET `order_times` = (`order_times`+'$foodNumbers') WHERE `foods_id`='$foodId';";
        mysqli_query($conn_restaurant, $sql_update_food_order_times);
    }
}