<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['orders']) && isset($_POST['englishName']) && isset($_POST['token'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!($oursAccess->isTokenValid($_POST['token'], "ours_customers"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['englishName']);

    $connRes = MysqlConfig::connRes($englishName);
    $resAccess = new MysqldbAccess($connRes);

    $randomNum = rand(11111111,99999999);
    $total_price = 0;
    $offcodeUsed = false;

    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);
    $orders =  str_replace("\\","",mysqli_real_escape_string($connRes, $_POST['orders']));
    $delivery_date =  mysqli_real_escape_string($connRes, $_POST['deliveryDate']);
    $delivery_date = $delivery_date >= time() ? $delivery_date : time();
    $details =  mysqli_real_escape_string($connRes, $_POST['details']);
    $address =  mysqli_real_escape_string($connRes, $_POST['address']);
    $deliveryPrice  =  mysqli_real_escape_string($connRes, $_POST['deliveryPrice']);
    $paymentStatus  =  mysqli_real_escape_string($connRes, $_POST['paymentStatus']);
    $orderTable  =  mysqli_real_escape_string($connRes, $_POST['orderTable']);

    // get user phone and user orderList
    $userInfo = $oursAccess->select("*", "ours_customers", "`token`='$token'" );
    $phone = $userInfo['phone'];
    $userOrdersList = ($userInfo['orders'] != null && strlen($userInfo['orders']) > 0) ? json_decode($userInfo['orders']) : array();



    if(strlen($phone) == 11) {
        if(($orderTable > 0 || strlen($address) > 8)) {
            $orders_array = json_decode($orders, true); // [{id: 6, number: 2}, {id: 42, number: 6}, ....]
            $ordersFullInfo = getFoodInfo($resAccess, $orders_array);
            $orderPrice = TotalPriceWithDiscount($ordersFullInfo);

            // change it when off codes added
            $total_price = $orderPrice;

            $ordersFullInfo_jsonStr = json_encode($ordersFullInfo);
            $ordersFullInfo_jsonStr = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
            }, $ordersFullInfo_jsonStr);


            $orderParams = array(
                "tracking_id" => $randomNum,
                "customer_phone" => $phone,
                "order_list" => $ordersFullInfo_jsonStr,
                "payment_status" => $paymentStatus,
                "delivery_price" => $deliveryPrice,
                "order_status" => "inLine",
                "address" => $address,
                "details" => $details,
                "total_price" => $total_price,
                "ordered_date" => time(),
                "delivery_date" => $delivery_date,
                "modified_date" => time(),
                "order_table" => $orderTable,
                "counter_app_status" => "0",
            );

            if ($resAccess->insert("orders", $orderParams)) {
                $trackingIdForUserData = $englishName . "@" . $randomNum;
                if ($oursAccess->updateAppendToList("ours_customers", array("orders" => $trackingIdForUserData), "`token`='$token'")) {
                    exit(json_encode(array(
                        'statusCode' => 200,
                        "data" => array(
                            'trackingId' => $randomNum,
                            'totalPrice' => $total_price
                        )
                    )));
                } else {
                    exit(json_encode(array('statusCode' => 500, 'details' => "order didnt saved in user history but saved for restaurant")));
                }
            } else {
                exit(json_encode(array('statusCode' => 500, 'details' => "couldn't save order")));
            }
        }else{
            exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs, table OR address should be set(address more than 8 character)")));
        }

    }else{
        exit(json_encode(array('statusCode'=>401, 'details'=>"token is not valid !")));
    }
}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}


function getFoodInfo($resAccess, $foods_list){
    $orderedFood = array();

    $all_foods = $resAccess->select("*", "foods");

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
                    'priceAfterDiscount'=>$priceAfterDiscount,
                    'counterAppFoodId'=>$eachFood['counter_app_food_id'],
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
