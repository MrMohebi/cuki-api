<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['token'])) {
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);


    $token = mysqli_real_escape_string($connOurs, $_POST['token']);

    // is token valid and has access
    if(!(
        $oursAccess->isTokenValid($token, "admins")&&
        $oursAccess->hasTokenAccess($token, "admins", array("admin"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    // get admin info
    $adminInfo = $oursAccess->select("*", 'admins', "`token`='$token'");


    $resReceipts = $oursAccess->select("*", "res_settlement", false,"`res_settlement_id` DESC  LIMIT 1000");
    $resReceipts = isset($resReceipts['res_settlement_id']) ? array($resReceipts) : $resReceipts;

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


