<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['token'])) > 20) {
    include_once 'db/db.config.php';
    include_once 'token/tokens.php';

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $paymentId =  json_decode(mysqli_real_escape_string($conn_database_ours, $_POST['paymentId']));


    $isRestaurant = ($token == $TOKEN_RESTAURANT_ADMIN || $token == $TOKEN_RESTAURANT_WAITER || $token == $TOKEN_RESTAURANT_COUNTER);

    $paymentsInfo = array();
    if(is_array($paymentId)){
        foreach ($paymentId as $eachPaymentId){
            $sql_get_payments_info = "SELECT * FROM payments WHERE `payment_id`= '$eachPaymentId';";
            if ($result = mysqli_query($conn_database_ours, $sql_get_payments_info)) {
                while ($row = mysqli_fetch_assoc($result)) {
                    array_push($paymentsInfo, array(
                        "trackingId"=>$row['tracking_id'],
                        'paymentId'=>$row['payment_id'],
                        "payerPhone"=>$row['payer_phone'],
                        "paidDate"=>$row['verified_date'],
                        "isPaid"=> $row['verified_date'] > 1000,
                        "amount"=>$row['amount'],
                        "itemType"=>$row['item_type'],
                        "item"=>$row['item'],
                        "status"=>$row['status'],
                    ));
                }
            }
        }
    }else{
        $sql_get_payments_info = "SELECT * FROM payments WHERE `payment_id`= '$paymentId';";
        if ($result = mysqli_query($conn_database_ours, $sql_get_payments_info)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $paymentsInfo = array(
                    "trackingId"=>$row['tracking_id'],
                    'paymentId'=>$row['payment_id'],
                    "payerPhone"=>$row['payer_phone'],
                    "paidDate"=>$row['verified_date'],
                    "isPaid"=> $row['verified_date'] > 1000,
                    "amount"=>$row['amount'],
                    "itemType"=>$row['item_type'],
                    "item"=>$row['item'],
                    "status"=>$row['status'],
                );
            }
        }
    }



    if(!$isRestaurant) {
        $userPhone = "";
        $sql_get_customer_info = "SELECT * FROM ours_customers WHERE `token`='$token';";
        if ($result = mysqli_query($conn_database_ours, $sql_get_customer_info)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $userPhone = $row['phone'];
            }
        }
        if (strlen($userPhone) != 11)
            exit(json_encode(array('statusCode' => 401, "data" => array("massage" => "token is not valid!"))));

        exit(json_encode(array('statusCode'=>200, "data"=>$paymentsInfo)));
    }else{
        exit(json_encode(array('statusCode'=>200, "data"=>$paymentsInfo)));
    }
}