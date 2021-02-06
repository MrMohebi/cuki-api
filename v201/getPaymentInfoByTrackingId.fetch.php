<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token'])) {
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!($oursAccess->isTokenValid($_POST['token'], "ours_customers"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }


    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);
    $trackingId =  mysqli_real_escape_string($connOurs, $_POST['trackingId']);

    $userPhone = $oursAccess->select("phone", "ours_customers", "`token`='$token'");



    $paymentsInfo = $oursAccess->select("*", "payments", "`tracking_id`='$trackingId' AND `payer_phone`='$userPhone'");


    if($paymentsInfo){
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
        exit(json_encode(array('statusCode'=>404, "details"=>"no payment was found!")));
    }


}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}