<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

$allowedUsers = ["admin", "accouter"];

if(isset($_POST['token']) && (isset($_POST['receiptId'])) && (isset($_POST['resEnglishName'])) && ($_POST['paidDate'] > 1000) && ($_POST['paidAmount'] > 100) && (strlen($_POST['paidResABankNum']) > 8) && (strlen($_POST['paidOurABankNum']) > 7) && (strlen($_POST['paidBankTrackingId']) > 4)){
    include_once '../db/db.config.php';

    $receiptId = mysqli_real_escape_string($conn_database_ours, $_POST['receiptId']);
    $resEnglishName = mysqli_real_escape_string($conn_database_ours, $_POST['resEnglishName']);
    $token = mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $paidDate = mysqli_real_escape_string($conn_database_ours, $_POST['paidDate']);
    $paidAmount = mysqli_real_escape_string($conn_database_ours, $_POST['paidAmount']);
    $paidResABankNum = mysqli_real_escape_string($conn_database_ours, $_POST['paidResABankNum']);
    $paidOurABankNum = mysqli_real_escape_string($conn_database_ours, $_POST['paidOurABankNum']);
    $paidBankTrackingId = mysqli_real_escape_string($conn_database_ours, $_POST['paidBankTrackingId']);


    // get admin info
    $adminInfo = array();
    $sql_get_adminInfo = "SELECT * FROM admins WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_adminInfo)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $adminInfo = $row;
        }
    }

    // check if admin allowed to use this api
    if(!in_array(strtolower($adminInfo["position"]), $allowedUsers))
        exit(json_encode(array('statusCode'=>403, "details"=>"your not allowed to access this action")));


    $paymentKey = "";
    $sql_get_payment_key = "SELECT * FROM restaurants WHERE `english_name`='$resEnglishName';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_payment_key)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $paymentKey = $row["payment_key"];
        }
    }

    // check restaurant is correct
    if(strlen($paymentKey) < 2)
        exit(json_encode(array('statusCode'=>400, "details"=>"restaurant wasn't found")));


    $isThereOpenReceipt = false;
    $lastResPaymentRecord = array();
    $sql_get_last_res_pay_record = "select * from res_settlement WHERE `payment_key`= '$paymentKey' ORDER BY res_settlement_id DESC LIMIT 1;";
    if ($result = mysqli_query($conn_database_ours, $sql_get_last_res_pay_record)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $lastResPaymentRecord = $row;
            if($row["status"] == "created-notPaid"){
                $isThereOpenReceipt = true;
            }
        }
    }


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

    $sql_update_submit_paymentInfo = "UPDATE res_settlement SET 
                                        `paid_date`='$paidDate',
                                        `paid_amount`='$paidAmount' ,
                                        `paid_res_abank_num`='$paidResABankNum',
                                        `paid_our_abank_num`='$paidOurABankNum',
                                        `paid_bank_tracking_id`='$paidBankTrackingId',
                                        `payer_support_name`='$supportPayerName',
                                        `payer_support_id`='$supportPayerId',
                                        `modified_date`='$nowTimestamp',
                                        `to_pay_amount`='$newToPayAmount',
                                        `status`='$newStatus'
                                                    WHERE `res_settlement_id`='$lastResReceiptId';";

    if(mysqli_query($conn_database_ours, $sql_update_submit_paymentInfo)){
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