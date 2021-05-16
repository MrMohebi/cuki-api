<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['englishName']) && isset($_POST['token'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['englishName']);

    $connRes = MysqlConfig::connRes($englishName);
    $resAccess = new MysqldbAccess($connRes);


    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);
    $foodId =  mysqli_real_escape_string($connRes, $_POST['foodId']);
    $lastDate = mysqli_real_escape_string($connRes, $_POST['lastDate']);
    $number = mysqli_real_escape_string($connRes, $_POST['number']);


    // get user phone and name
    $userInfo = $oursAccess->select("*", "users", "`token`='$token'" );
    $phone = $userInfo['phone'];

    $trackingIdAndOrders = getOrdersAndLastTrackingIdBaseOnFoodId($resAccess, $phone, $foodId, time()-(8400*3), time());



    $commentsList = $resAccess->select("*", "comments", "`food_id`='$foodId' AND `status`='confirmed' AND `commented_date` < '$lastDate'", "`commented_date` DESC LIMIT $number");

    if(isset($commentsList['id']))
        $commentsList = array($commentsList);


    if(count($commentsList) > 0 && $commentsList){
        // remove privet info
        $finalCommentsList = array();
        foreach ($commentsList as $eComment){
            $eComment['phone'] = ":)";
            array_push($finalCommentsList, $eComment);
        }

        exit(json_encode(array(
            'statusCode'=>200,
            'data'=>array(
                'comments'=>$finalCommentsList,
                'isAllowedLeaveComment'=> $trackingIdAndOrders[0] > 100
            )
        )));
    }else{
        exit(json_encode(array('statusCode'=>404, 'details'=>"nothing found", "data"=>array('isAllowedLeaveComment'=>false))));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs", "data"=>array('isAllowedLeaveComment'=>false))));
}

function getOrdersAndLastTrackingIdBaseOnFoodId($resAccess,$userPhone, $foodId, $startTime, $endTime){
    $ordersList = $resAccess->select("*", "orders", " `user_phone`='$userPhone' AND `created_at` BETWEEN '$startTime' AND '$endTime'", "`created_at` DESC");

    if(!($ordersList))
        return array(0,array(),array());

    $trackingId = 0;
    $selectedOrder = array();
    if(isset($ordersList['tracking_id']))
        $ordersList = array($ordersList);
    foreach ($ordersList as $eOrder){
        $foodsList = json_decode($eOrder['items'], true);
        foreach ($foodsList as $eFood){
            if ($eFood['id'] == $foodId)
                $trackingId = $eOrder['tracking_id'];
            $selectedOrder = $eOrder;
        }
    }
    return array($trackingId, $selectedOrder, $ordersList);
}