<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['orders']) && isset($_POST['englishName'])){
    include_once 'db/db.config.php';

    $randomNum = rand(11111111,99999999);
    $conn_database_restaurant = $dbs[$_POST['englishName']];
    $total_price = 0;
    $offcodeUsed = false;

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $orders =  str_replace("\\","",mysqli_real_escape_string($conn_database_restaurant, $_POST['orders']));
    $englishName =  mysqli_real_escape_string($conn_database_ours, $_POST['englishName']);
    $offcode = mysqli_real_escape_string($conn_database_restaurant, $_POST['offcode']);
    $delivery_date =  mysqli_real_escape_string($conn_database_restaurant, $_POST['deliveryDate']);
    $delivery_date = $delivery_date >= $nowTimestamp ? $delivery_date : $nowTimestamp;
    $details =  mysqli_real_escape_string($conn_database_restaurant, $_POST['details']);
    $address =  mysqli_real_escape_string($conn_database_restaurant, $_POST['address']);
    $delivery_price  =  mysqli_real_escape_string($conn_database_restaurant, $_POST['deliveryPrice']);
    $payment_status  =  mysqli_real_escape_string($conn_database_restaurant, $_POST['paymentStatus']);
    $order_table  =  mysqli_real_escape_string($conn_database_restaurant, $_POST['orderTable']);

    // check user is valid and get it's phone
    $costumer_phone = false;
    $userOrdersList = array();
    $sql_get_customer_phone = "SELECT * FROM ours_customers WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_phone)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $costumer_phone = $row['phone'];
            $userOrdersList = ($row['orders'] != null && strlen($row['orders']) > 1) ? json_decode($row['orders']) : array();
        }
    }


    if(strlen($costumer_phone) == 11){
        $offcodeData = offcodeIsValid($conn_database_ours, $costumer_phone, $englishName, $offcode);
        $orders_array = json_decode($orders,true); // [{id: 6, number: 2}, {id: 42, number: 6}, ....]
        $ordersFullInfo = getFoodInfo($conn_database_restaurant,$orders_array);
        $orderPrice = TotalPriceWithDiscount($ordersFullInfo);
        if($offcodeData){
            $total_price = totalPriceAfterOffcode($offcodeData, $orderPrice);
            $offcodeUsed = ($total_price == $orderPrice) ? false : true ;
        }else{
            $total_price = $orderPrice;
        }

        $ordersFullInfo_jsonStr = json_encode($ordersFullInfo);
        $ordersFullInfo_jsonStr = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
            return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
        }, $ordersFullInfo_jsonStr);

        $sql_save_order = "INSERT INTO 
                orders(`tracking_id`, `customer_phone`,  `order_list`,              `payment_status`,  `delivery_price`,  `order_status`, `address`,  `details`,  `total_price`, `ordered_date`, `delivery_date`,  `modified_date`, `offcode`, `order_table`)   
                VALUES('$randomNum',  '$costumer_phone', '$ordersFullInfo_jsonStr', '$payment_status', '$delivery_price', 'inLine',       '$address', '$details', '$total_price','$nowTimestamp', '$delivery_date','$nowTimestamp', '$offcode', '$order_table');";

        $trackingIdForUserData = $englishName. "_" . $randomNum;
        array_push($userOrdersList,$trackingIdForUserData);
        $userOrdersList_str = json_encode($userOrdersList);
        $sql_save_order_in_user = "UPDATE ours_customers SET `orders`='$userOrdersList_str' WHERE `token`='$token';";
        if(mysqli_query($conn_database_restaurant, $sql_save_order) && mysqli_query($conn_database_ours, $sql_save_order_in_user)){
            exit(json_encode(array('statusCode'=>200, 'trackingId'=>$randomNum, 'offcodeUsed'=>$offcodeUsed, 'totalPrice'=>$total_price)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }

    }else{
        exit(json_encode(array('statusCode'=>401)));
    }

}else{
    exit(json_encode(array('statusCode'=>400)));
}


function offcodeIsValid($conn_database_ours, $phone , $english_name , $offcode){
    $offcodeData = false;
    $sql_get_customer_phone = "SELECT * FROM all_off_codes WHERE `body` = '$offcode' AND `status`= 'active'";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_phone)) {
        while ($row = mysqli_fetch_assoc($result)) {
            if(($row['place'] == NULL || $row['place'] == $english_name) && ($row['phone'] == NULL || $row['phone'] == $phone)){
                $offcodeData = $row;
            }
        }
    }

    if($offcodeData){
        return $offcodeData;
    }else{
        return false;
    }
}

function getFoodInfo($conn_database_restaurant, $foods_list){
    $orderedFood = array();

    $all_foods = array();
    $sql_get_foods = "SELECT * FROM foods;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_foods)) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($all_foods, $row);
        }
    }

    foreach ($foods_list as $eachOrderedFood){
        foreach ($all_foods as $eachFood){
            if ($eachOrderedFood['id'] == $eachFood['foods_id']) {
                $priceAfterDiscount = $eachFood['price'] * ((100 - $eachFood['discount'])/100);
                $eachOrderedFood_newArray = array(
                    'id'=>$eachOrderedFood['id'],
                    'name'=>$eachFood['name'],
                    'number'=>$eachOrderedFood['number'],
                    'price'=>$eachFood['price'],
                    'discount'=>$eachFood['discount'],
                    'priceAfterDiscount'=>$priceAfterDiscount
                );
                array_push($orderedFood, $eachOrderedFood_newArray);
            }
        }
    }
    return $orderedFood;
}


function TotalPriceWithDiscount($OrderedFoodsInfo){
    $totalPrice = 0;
    foreach ($OrderedFoodsInfo as $eachFood){
        $totalPrice += $eachFood['priceAfterDiscount'] * $eachFood['number'];
    }
    return $totalPrice;
}

function totalPriceAfterOffcode($offcodeData, $orderPrice){
    $minOffcode = ($offcodeData['min_price'] == 0 || $offcodeData['min_price'] == null) ? 0  : $offcodeData['min_price'];
    $maxOffcode = ($offcodeData['max_price'] == 0 || $offcodeData['max_price'] == null) ? 99999999999  : $offcodeData['max_price'];

    if($orderPrice >= $minOffcode && $orderPrice <= $maxOffcode){
        if($offcodeData['discount_price'] == 0 || $offcodeData['discount_price'] == null){
            $total_price =  $orderPrice * ((100-$offcodeData['discount_percentage'])/100);
        }else{
            $total_price = $orderPrice - $offcodeData['discount_price'];
        }
    }else{
        $total_price = $orderPrice;
    }
    return $total_price;
}