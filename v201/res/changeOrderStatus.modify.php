<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && isset($_POST['trackingId']) && isset($_POST['newOrderStatus'])){
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!(
        $oursAccess->isTokenValid($_POST['token'], "restaurants")&&
        $oursAccess->hasTokenAccess($_POST['token'], "restaurants", array("admin", "counter", "waiter"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    $connRes = MysqlConfig::connRes($oursAccess->select('english_name','restaurants',"`token`='".$_POST['token']."'"));
    $resAccess = new MysqldbAccess($connRes);

    $trackingId = mysqli_real_escape_string($connRes, $_POST['trackingId']);
    $newOrderStatus = mysqli_real_escape_string($connRes, $_POST['newOrderStatus']);
    $deleteReason = ($newOrderStatus == "deleted") ? mysqli_real_escape_string($connRes, $_POST['deleteReason']) : "";
    $deliveryId = ($newOrderStatus == "delivered") ? mysqli_real_escape_string($connRes, $_POST['deliveryId']) : "";

    // get order info and user phone
    $orderInfo = $resAccess->select("*", "orders", "`tracking_id`='$trackingId'");
    $userPhone = $orderInfo["customer_phone"];

    if(strlen($userPhone) !== 11)
        exit(json_encode(array('statusCode'=>404, 'details'=>"tracking id is incorrect")));


    if($orderInfo['order_status'] != $newOrderStatus) {
        $sqlOrderInfoUpdateParams = array(
            'order_status'=>$newOrderStatus,
            'delete_reason'=>$deleteReason,
            'modified_date'=>time()
        );

        if ($resAccess->update("orders",$sqlOrderInfoUpdateParams, "`tracking_id`='$trackingId'" )) {

            // create new customer if it doesn't exist
            $customerInfo = $resAccess->select("*", "restaurant_customers", "`phone`='$userPhone'");
            if(!$customerInfo){
                $resAccess->insert("restaurant_customers", array('phone'=>$userPhone,'order_times'=>0,'order_list'=>"[]",'score'=>0,'total_price'=>0,'modified_date'=>time()));
                $customerInfo = array('phone'=>$userPhone,'order_times'=>0,'order_list'=>"[]",'score'=>0,'total_price'=>0);
            }

            // add tracking id to customer history if it doesn't exist
            $previousOrderList = json_decode($customerInfo['order_list']);
            if(!in_array($trackingId, $previousOrderList))
                $resAccess->updateAppendToList('restaurant_customers', array('order_list'=>$trackingId),"`phone`='$userPhone'" );


            // add order info to customer info
            if($newOrderStatus == "done"){
                addOrderCustomer($resAccess,$orderInfo,$customerInfo);
                increaseOrderTimesOfFoods($connRes,$orderInfo);
            }elseif ($orderInfo['order_status'] == "done"){
                removeOrderCustomer($resAccess,$orderInfo,$customerInfo);
            }
            exit(json_encode(array('statusCode' => 200)));

        } else {
            exit(json_encode(array('statusCode' => 500, "details"=>"something went wrong during change order status")));
        }
    }else{
        exit(json_encode(array('statusCode'=>400, "details"=>"new order status is like its previous one")));
    }
}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs!")));
}

function removeOrderCustomer($resAccess, $orderInfo, $customerInfo){
    $userPhone = $customerInfo['phone'];
    $sqlCustomerInfoUpdateParams = array(
        'order_times' => $customerInfo['order_times'] ? ($customerInfo['order_times'] - 1) : 0,
        'score' => $customerInfo['score'] ? ($customerInfo['score'] - ( $orderInfo['total_price']/1000)) : 0,
        'total_price'=>$customerInfo['total_price'] ? ($customerInfo['total_price'] - $orderInfo['total_price']) : 0,
        'modified_date'=>time()
    );
    return $resAccess->update("restaurant_customers", $sqlCustomerInfoUpdateParams,"`phone`='$userPhone'");
}

function addOrderCustomer($resAccess, $orderInfo, $customerInfo){
    $userPhone = $customerInfo['phone'];
    $sqlCustomerInfoUpdateParams = array(
        'order_times' => $customerInfo['order_times'] ? ($customerInfo['order_times'] + 1) : 1,
        'score' => $customerInfo['score'] ? ($customerInfo['score'] + ($orderInfo['total_price']/1000)) : ( $orderInfo['total_price']/1000),
        'total_price'=>$customerInfo['total_price'] ? ($customerInfo['total_price'] + $orderInfo['total_price']) : $orderInfo['total_price'],
        'modified_date'=>time()
    );
    return $resAccess->update("restaurant_customers", $sqlCustomerInfoUpdateParams,"`phone`='$userPhone'");
}


function increaseOrderTimesOfFoods($conn_restaurant,$orderInfo){
    $orderList = json_decode($orderInfo["order_list"], true);
    foreach ($orderList as $eachFood){
        $foodId = $eachFood["id"];
        $foodNumbers = $eachFood["number"];
        $sql_update_food_order_times = "UPDATE foods SET `order_times` = (`order_times`+'$foodNumbers') WHERE `foods_id`='$foodId';";
        mysqli_query($conn_restaurant, $sql_update_food_order_times);
    }
    return true;
}