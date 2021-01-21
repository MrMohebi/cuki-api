<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

$allowedUsers = ["admin", "accouter"];

if(isset($_POST['token'])) {
    include_once '../db/db.config.php';

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


    $resReceipts = array();
    $sql_get_resReceipts = "SELECT * FROM res_settlement ORDER BY  `res_settlement_id` DESC  LIMIT 1000;";
    if ($result = mysqli_query($conn_database_ours, $sql_get_resReceipts)) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($resReceipts, $row);
        }
    }


    // filter and pretty names
    $result = array();
    foreach ($resReceipts as $eResReceipt){
        array_push($result,
            array(
                "resSettlementId"=> $eResReceipt['res_settlement_id'],
                "resEnglishName"=>$eResReceipt["res_english_name"],
                "paymentKey"=>$eResReceipt["payment_key"],
                "details"=>$eResReceipt["details"],
                "tOnlineIncomeTillNow"=>$eResReceipt["tonline_income_tillnow"],
                "tCashIncomeTillNow"=>$eResReceipt["tcash_income_tillnow"],
                "tOnlineIncomeFromLastSettlement"=>$eResReceipt["tonline_income_fromlastsettlement"],
                "tCashIncomeFromLastSettlement"=>$eResReceipt["tcash_income_fromlastsettlement"],
                "toPayAmount"=>$eResReceipt["to_pay_amount"],
                "status"=>$eResReceipt["status"],
                "paidDate"=>$eResReceipt["paid_date"],
                "paidAmount"=>$eResReceipt["paid_amount"],
                "paidResABankNum"=>$eResReceipt["paid_res_abank_num"],
                "paidOurABankNum"=>$eResReceipt["paid_our_abank_num"],
                "paidBankTrackingId"=>$eResReceipt["paid_bank_tracking_id"],
                "creatorSupportName"=>$eResReceipt["creator_support_name"],
                "payerSupportName"=>$eResReceipt["payer_support_name"],
                "createdDate"=>$eResReceipt["created_date"],
            )
        );
    }

    exit(json_encode(array('statusCode'=>200, "data"=>$result)));

}else{
    exit(json_encode(array('statusCode'=>400, "details"=>"wrong inputs")));
}


