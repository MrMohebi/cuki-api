<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['token'])) > 20) {
    include_once 'db/db.config.php';

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $trackingId =  mysqli_real_escape_string($conn_database_ours, $_POST['trackingId']);


    $userPhone = "";
    $sql_get_customer_info = "SELECT * FROM ours_customers WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $userPhone = $row['phone'];
        }
    }
    if(strlen($userPhone) != 11)
        exit(json_encode(array('statusCode'=>401, "data"=>array("massage"=>"token is not valid!"))));

    $paymentsInfo = array();
    $sql_get_payments_info = "SELECT * FROM payments WHERE `tracking_id`= '$trackingId' AND `payer_phone`='$userPhone';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_payments_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($paymentsInfo, $row);
        }
    }

    if(sizeof($paymentsInfo) > 0){
        $result = array();
        foreach ($paymentsInfo as $eachPay){
            array_push($result,array(
                "trackingId"=>$eachPay['tracking_id'],
                'paymentId'=>$eachPay['payment_id'],
                "payerPhone"=>$eachPay['payer_phone'],
                "paidDate"=>$eachPay['verified_date'],
                "isPaid"=> $eachPay['verified_date'] > 1000,
                "amount"=>$eachPay['amount'],
                "itemType"=>$eachPay['item_type'],
                "item"=>$eachPay['item'],
                "status"=>$eachPay['status'],
            ));
        }
        exit(json_encode(array('statusCode'=>200, "data"=>$result)));

    }else{
        exit(json_encode(array('statusCode'=>404,"test3"=>$trackingId,  "test"=>$paymentsInfo, "test2"=>$userPhone, "data"=>array("massage"=>"no payment was found!"))));
    }


}else{
    exit(json_encode(array('statusCode'=>402, "data"=>array("massage"=>"bad inputs"))));
}