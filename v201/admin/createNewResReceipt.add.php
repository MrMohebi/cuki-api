<?php

header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if (isset($_POST['token'])) {
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $resEnglishName = mysqli_real_escape_string($connOurs, $_POST['resEnglishName']);
    $details = mysqli_real_escape_string($connOurs, $_POST['details']);
    $token = mysqli_real_escape_string($connOurs, $_POST['token']);

    $connRes = MysqlConfig::connRes($resEnglishName);
    $resAccess = new MysqldbAccess($connRes);


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


    // check if there is no receipt
    if ($isThereOpenReceipt) {
        exit(json_encode(array(
            'statusCode' => 402,
            "details" => "there is an open restaurant receipt. First pay it!",
            "data" => array(
                'receiptId' => $lastResPaymentRecord["res_settlement_id"],
                'resEnglishName' => $resEnglishName,
                'toPayAmount' => $lastResPaymentRecord["to_pay_amount"],
                'status' => $lastResPaymentRecord["status"],
                'totalOnlineIncomeAllTime' => $lastResPaymentRecord["tonline_income_tillnow"],
                'totalCashIncomeAllTime' => $lastResPaymentRecord["tcash_income_tillnow"],
                'totalOnlineIncomeFromLastSettlementTillNow' => $lastResPaymentRecord["tonline_income_fromlastsettlement"],
                'totalCashIncomeFromLastSettlementTillNow' => $lastResPaymentRecord["tcash_income_fromlastsettlement"],
            )
        )));
    }


    $totalOnlineIncomeAllTime = calculateTOnlineIncomeTillNow($lastResPaymentRecord, $connOurs);
    $totalCashIncomeAllTime = calculateTcashFoodIncomeTillNow($lastResPaymentRecord, $connRes);
    $totalOnlineIncomeFromLastSettlement = calculateTOnlineIncomeFromLastSettlement($lastResPaymentRecord, $connOurs);
    $totalCashIncomeFromLastSettlement = calculateTcashFoodIncomeFromLastSettlement($lastResPaymentRecord, $connRes);

    // check if receipt online(not pay) is not zero
    if ($totalOnlineIncomeFromLastSettlement <= 100)
        exit(json_encode(array(
            'statusCode' => 408,
            "details" => "there is no unpaid bill",
            "data" => array(
                'resEnglishName' => $resEnglishName,
                'totalOnlineIncomeAllTime' => $totalOnlineIncomeAllTime,
                'totalCashIncomeAllTime' => $totalCashIncomeAllTime,
                'totalOnlineIncomeFromLastSettlementTillNow' => $totalOnlineIncomeFromLastSettlement,
                'totalCashIncomeFromLastSettlementTillNow' => $totalCashIncomeFromLastSettlement,
            )
        )));


    $status = "created-notPaid";
    $creatorSupportName = $adminInfo["name"];
    $creatorSupportId = $adminInfo["admins_id"];
    $toPayAmount = $totalOnlineIncomeFromLastSettlement;

    $sql_saveNewResReceiptRecordParams = array(
        'res_english_name'=>$resEnglishName,
        'payment_key'=>$paymentKey,
        'details'=>$details,
        'to_pay_amount'=>$toPayAmount,
        'tonline_income_tillnow'=>$totalOnlineIncomeAllTime,
        'tcash_income_tillnow'=>$totalCashIncomeAllTime,
        'tonline_income_fromlastsettlement'=>$totalOnlineIncomeFromLastSettlement,
        'tcash_income_fromlastsettlement'=>$totalCashIncomeFromLastSettlement,
        'status'=>$status,
        'creator_support_name'=>$creatorSupportName,
        'creator_support_id'=>$creatorSupportId,
        'created_date'=>time(),
        'modified_date'=>time(),
    );


    if ($oursAccess->insert("res_settlement", $sql_saveNewResReceiptRecordParams)) {
        exit(json_encode(array('statusCode' => 200, "data" => array(
            'resEnglishName' => $resEnglishName,
            'status' => $status,
            'toPay' => $toPayAmount,
            'totalOnlineIncomeAllTime' => $totalOnlineIncomeAllTime,
            'totalCashIncomeAllTime' => $totalCashIncomeAllTime,
            'totalOnlineIncomeFromLastSettlementTillNow' => $totalOnlineIncomeFromLastSettlement,
            'totalCashIncomeFromLastSettlementTillNow' => $totalCashIncomeFromLastSettlement,
        ))));
    } else
        exit(json_encode(array('statusCode' => 500, "details" => "some thing went wrong during saving record")));

} else {
    exit(json_encode(array('statusCode' => 400, "details" => "wrong inputs")));
}


function calculateTOnlineIncomeTillNow($lastRPRecord, $ourConn)
{
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newOnlinePaymentsFromLastRecord = "SELECT * FROM payments WHERE `verified_date` >= '$lastRDate'";
    if ($result = mysqli_query($ourConn, $sql_get_newOnlinePaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum += $row["amount"];
        }
    }

    return $lastRPRecord['tonline_income_tillnow'] + $sum;
}

function calculateTcashFoodIncomeTillNow($lastRPRecord, $resConn)
{
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newCashPaymentsFromLastRecord = "SELECT * FROM orders WHERE `ordered_date` >= '$lastRDate' AND `order_status` = 'done';";
    if ($result = mysqli_query($resConn, $sql_get_newCashPaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum += ($row["total_price"] - $row["paid_amount"]);
        }
    }

    return $lastRPRecord['tcash_income_tillnow'] + $sum;
}

function calculateTOnlineIncomeFromLastSettlement($lastRPRecord, $ourConn)
{
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newOnlinePaymentsFromLastRecord = "SELECT * FROM payments WHERE `verified_date` >= '$lastRDate'";
    if ($result = mysqli_query($ourConn, $sql_get_newOnlinePaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum += $row["amount"];
        }
    }

    return $sum;
}

function calculateTcashFoodIncomeFromLastSettlement($lastRPRecord, $resConn)
{
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newCashPaymentsFromLastRecord = "SELECT * FROM orders WHERE `ordered_date` >= '$lastRDate' AND `order_status` = 'done'";
    if ($result = mysqli_query($resConn, $sql_get_newCashPaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum += ($row["total_price"] - $row["paid_amount"]);
        }
    }

    return $sum;
}