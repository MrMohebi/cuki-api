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
    $address =  json_decode(mysqli_real_escape_string($connRes, $_POST['address']), true);
    $deliveryPrice  =  mysqli_real_escape_string($connRes, $_POST['deliveryPrice']);
    $paymentStatus  =  mysqli_real_escape_string($connRes, $_POST['paymentStatus']);
    $orderTable  =  mysqli_real_escape_string($connRes, $_POST['orderTable']);

    // get user phone and user orderList
    $userInfo = $oursAccess->select("*", "ours_customers", "`token`='$token'" );
    $phone = $userInfo['phone'];
    $userOrdersList = ($userInfo['orders'] != null && strlen($userInfo['orders']) > 0) ? json_decode($userInfo['orders']) : array();



    if(strlen($phone) == 11) {
        if(($orderTable > 0 || count($address) > 1)) {
            $address["addressText"] = getAddressText($address["coordinates"][0], $address["coordinates"][1]);
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
                "address" => json_encode($address),
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


function getAddressText ($lat, $lon){
    $apiKey="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjViYWFiYjc2MWUyNjIzZjUxNjJlYjM4OGJjNDI0YjgzY2MyMzBlMGYxOTFmZmQ5YjNkMzU1NTI2NWE4N2UyNDE2YWQ2YmE5YTM2ZjgyMWUxIn0.eyJhdWQiOiIxMTE5MiIsImp0aSI6IjViYWFiYjc2MWUyNjIzZjUxNjJlYjM4OGJjNDI0YjgzY2MyMzBlMGYxOTFmZmQ5YjNkMzU1NTI2NWE4N2UyNDE2YWQ2YmE5YTM2ZjgyMWUxIiwiaWF0IjoxNjEwODg4NDI1LCJuYmYiOjE2MTA4ODg0MjUsImV4cCI6MTYxMzMwNzYyNSwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.bbHkFAqQc_uPWAgoFuXeIFOJk3P6qH1S0FGGRNrn9r6jcJ0PTM4qMHo4OLakVXm8gWps39-HHD1Mq5XZ-hWzeInR653aRW_vKbWDJNg3OQjoR0EYjz7BXBjRNFWlJZxCjLHEpVT8bXf3F_c1XxjDkN7Pd40KEPK5TlIZXFQNn43iSCsFVk2e1oWyYl0fWIuxzf168bczzeNTIJsVTrLWVAnB3uAXvGk7ffcLy7kfyptxCXj1Z3dtJxFeWxq13WV3FwLDyqsjJ25J_YqIJWva4JiCAyjLU-WCRBR7oXOoaw4VnMB0osoq4DD5Fmnx7oByRFQcxh8lcmTK8ovkScC9rQ";

    $url = "https://map.ir/fast-reverse?lat=$lat&lon=$lon";

    $requestHandler = curl_init();
    curl_setopt($requestHandler, CURLOPT_URL, $url);
    curl_setopt($requestHandler, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($requestHandler, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json',
        "x-api-key: $apiKey",
    ));
    $result = json_decode(curl_exec($requestHandler), true);
    curl_close($requestHandler);
    return $result['address_compact'];
}

