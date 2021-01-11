<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

$allowedUsers = ["admin", "accouter"];

if(isset($_POST['token'])) {
    include_once '../db/db.config.php';
    $resConn = $dbs[$_POST['resEnglishName']];

    $resEnglishName = mysqli_real_escape_string($conn_database_ours, $_POST['resEnglishName']);
    $details = mysqli_real_escape_string($conn_database_ours, $_POST['details']);
    $token = mysqli_real_escape_string($conn_database_ours, $_POST['token']);

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

    // check if there is no receipt
    if($isThereOpenReceipt){
        exit(json_encode(array(
            'statusCode'=>402,
            "details"=>"there is an open restaurant receipt. First pay it!",
            "data"=>array(
                'receiptId'=>$lastResPaymentRecord["res_settlement_id"],
                'resEnglishName'=>$resEnglishName,
                'toPayAmount'=>$lastResPaymentRecord["to_pay_amount"],
                'status'=>$lastResPaymentRecord["status"],
                'totalOnlineIncomeAllTime'=>$lastResPaymentRecord["tonline_income_tillnow"],
                'totalCashIncomeAllTime'=>$lastResPaymentRecord["tcash_income_tillnow"],
                'totalOnlineIncomeFromLastSettlementTillNow'=>$lastResPaymentRecord["tonline_income_fromlastsettlement"],
                'totalCashIncomeFromLastSettlementTillNow'=>$lastResPaymentRecord["tcash_income_fromlastsettlement"],
            )
        )));
    }




    $totalOnlineIncomeAllTime = calculateTOnlineIncomeTillNow($lastResPaymentRecord, $conn_database_ours);
    $totalCashIncomeAllTime = calculateTcashFoodIncomeTillNow($lastResPaymentRecord, $resConn);
    $totalOnlineIncomeFromLastSettlement = calculateTOnlineIncomeFromLastSettlement($lastResPaymentRecord, $conn_database_ours);
    $totalCashIncomeFromLastSettlement = calculateTcashFoodIncomeFromLastSettlement($lastResPaymentRecord, $resConn);

    // check if receipt online(not pay) is not zero
    if($totalOnlineIncomeFromLastSettlement <= 100)
        exit(json_encode(array(
            'statusCode'=>408,
            "details"=>"there is no unpaid bill",
            "data"=>array(
                'resEnglishName'=>$resEnglishName,
                'totalOnlineIncomeAllTime'=>$totalOnlineIncomeAllTime,
                'totalCashIncomeAllTime'=>$totalCashIncomeAllTime,
                'totalOnlineIncomeFromLastSettlementTillNow'=>$totalOnlineIncomeFromLastSettlement,
                'totalCashIncomeFromLastSettlementTillNow'=>$totalCashIncomeFromLastSettlement,
            )
        )));


    $status = "created-notPaid";
    $creatorSupportName = $adminInfo["name"];
    $creatorSupportId = $adminInfo["admins_id"];
    $toPayAmount = $totalOnlineIncomeFromLastSettlement;

    $sql_save_newResReceiptRecord = "INSERT INTO res_settlement(`res_english_name`,`payment_key`,`details`,`to_pay_amount`, `tonline_income_tillnow`,`tcash_income_tillnow`,`tonline_income_fromlastsettlement`,`tcash_income_fromlastsettlement`,`status`,creator_support_name,creator_support_id,`created_date`,`modified_date`) 
                                                        VALUES ('$resEnglishName','$paymentKey','$details','$toPayAmount','$totalOnlineIncomeAllTime','$totalCashIncomeAllTime','$totalOnlineIncomeFromLastSettlement','$totalCashIncomeFromLastSettlement','$status','$creatorSupportName','$creatorSupportId','$nowTimestamp','$nowTimestamp');";

    if(mysqli_query($conn_database_ours, $sql_save_newResReceiptRecord)){
        exit(json_encode(array('statusCode'=>200, "data"=>array(
            'resEnglishName'=>$resEnglishName,
            'status'=>$status,
            'toPay'=>$toPayAmount,
            'totalOnlineIncomeAllTime'=>$totalOnlineIncomeAllTime,
            'totalCashIncomeAllTime'=>$totalCashIncomeAllTime,
            'totalOnlineIncomeFromLastSettlementTillNow'=>$totalOnlineIncomeFromLastSettlement,
            'totalCashIncomeFromLastSettlementTillNow'=>$totalCashIncomeFromLastSettlement,
        ))));
    }else
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong during saving record")));

}else{
    exit(json_encode(array('statusCode'=>400, "details"=>"wrong inputs")));
}



function calculateTOnlineIncomeTillNow($lastRPRecord, $ourConn){
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newOnlinePaymentsFromLastRecord = "SELECT * FROM payments WHERE `verified_date` >= '$lastRDate'";
    if ($result = mysqli_query($ourConn, $sql_get_newOnlinePaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum  += $row["amount"];
        }
    }

   return $lastRPRecord['tonline_income_tillnow'] + $sum;
}

function calculateTcashFoodIncomeTillNow($lastRPRecord, $resConn){
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newCashPaymentsFromLastRecord = "SELECT * FROM orders WHERE `ordered_date` >= '$lastRDate' AND `order_status` = 'done';" ;
    if ($result = mysqli_query($resConn, $sql_get_newCashPaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum  += ($row["total_price"] - $row["paid_amount"]);
        }
    }

    return $lastRPRecord['tcash_income_tillnow'] + $sum;
}

function calculateTOnlineIncomeFromLastSettlement($lastRPRecord, $ourConn){
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newOnlinePaymentsFromLastRecord = "SELECT * FROM payments WHERE `verified_date` >= '$lastRDate'";
    if ($result = mysqli_query($ourConn, $sql_get_newOnlinePaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum  += $row["amount"];
        }
    }

    return $sum;
}

function calculateTcashFoodIncomeFromLastSettlement($lastRPRecord, $resConn){
    $lastRDate = $lastRPRecord['created_date'] > 100 ? $lastRPRecord['created_date'] : 0;

    $sum = 0;
    $sql_get_newCashPaymentsFromLastRecord = "SELECT * FROM orders WHERE `ordered_date` >= '$lastRDate' AND `order_status` = 'done'";
    if ($result = mysqli_query($resConn, $sql_get_newCashPaymentsFromLastRecord)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $sum  += ($row["total_price"] - $row["paid_amount"]);
        }
    }

    return $sum;
}