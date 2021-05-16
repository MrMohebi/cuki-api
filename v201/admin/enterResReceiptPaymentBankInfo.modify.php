<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

$allowedUsers = ["admin", "accouter"];

if(isset($_POST['token']) && (isset($_POST['receiptId'])) && (isset($_POST['resEnglishName'])) && ($_POST['paidDate'] > 1000) && ($_POST['paidAmount'] > 100) && (strlen($_POST['paidResABankNum']) > 8) && (strlen($_POST['paidOurABankNum']) > 7) && (strlen($_POST['paidBankTrackingId']) > 4)){
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);


    $receiptId = mysqli_real_escape_string($connOurs, $_POST['receiptId']);
    $resEnglishName = mysqli_real_escape_string($connOurs, $_POST['resEnglishName']);
    $token = mysqli_real_escape_string($connOurs, $_POST['token']);
    $paidDate = mysqli_real_escape_string($connOurs, $_POST['paidDate']);
    $paidAmount = mysqli_real_escape_string($connOurs, $_POST['paidAmount']);
    $paidResABankNum = mysqli_real_escape_string($connOurs, $_POST['paidResABankNum']);
    $paidOurABankNum = mysqli_real_escape_string($connOurs, $_POST['paidOurABankNum']);
    $paidBankTrackingId = mysqli_real_escape_string($connOurs, $_POST['paidBankTrackingId']);

    // is token valid and has access
    if(!(
        $oursAccess->isTokenValid($token, "admins")&&
        $oursAccess->hasTokenAccess($token, "admins", array("admin"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    // get admin info
    $adminInfo = $oursAccess->select("*", 'admins', "`token`='$token'");


    $paymentKey = $oursAccess->select("payment_key", "restaurants", "`english_name`='$resEnglishName'");
    // check restaurant is correct
    if (strlen($paymentKey) < 2)
        exit(json_encode(array('statusCode' => 400, "details" => "restaurant wasn't found")));


    $lastResPaymentRecord = $oursAccess->select("*", "res_settlement", "`payment_key`= '$paymentKey'", "`res_settlement_id` DESC LIMIT 1");
    $isThereOpenReceipt = $lastResPaymentRecord['status'] == "created-notPaid";


    //check if there is any open receipt
    if(!$isThereOpenReceipt)
        exit(json_encode(array('statusCode'=>404, "details"=>"couldn't find any open receipt")));


    //check if paid amount is equal to receipt amount
    if($lastResPaymentRecord['to_pay_amount'] != $paidAmount)
        exit(json_encode(array('statusCode'=>402, "details"=>"paid amount is NOT equal to receipt amount")));



    $supportPayerName = $adminInfo["name"];
    $supportPayerId = $adminInfo["admins_id"];
    $newStatus = "paid";
    $newToPayAmount = 0;
    $lastResReceiptId = $lastResPaymentRecord['res_settlement_id'];


    $sqlUpdate_submitPaymentInfoParams = array(
        'paid_date'=>$paidDate,
        'paid_amount'=>$paidAmount ,
        'paid_res_abank_num'=>$paidResABankNum,
        'paid_our_abank_num'=>$paidOurABankNum,
        'paid_bank_tracking_id'=>$paidBankTrackingId,
        'payer_support_name'=>$supportPayerName,
        'payer_support_id'=>$supportPayerId,
        'modified_at'=>time(),
        'to_pay_amount'=>$newToPayAmount,
        'status'=>$newStatus
    );

    if($oursAccess->update("res_settlement", $sqlUpdate_submitPaymentInfoParams, "`res_settlement_id`='$lastResReceiptId'")){
        exit(json_encode(array(
            'statusCode'=>200,
            "data"=>array(
                "paidAmount"=>$paidAmount,
                "receiptId"=>$lastResReceiptId,
                "payerName"=>$supportPayerName
            ))));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"something went wrong during saving data on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, "details"=>"wrong inputs")));
}