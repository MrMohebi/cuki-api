<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['englishName']) && isset($_POST['token'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!($oursAccess->isTokenValid($_POST['token'], "users"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['englishName']);

    $connRes = MysqlConfig::connRes($englishName);
    $resAccess = new MysqldbAccess($connRes);


    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);
    $foodId =  mysqli_real_escape_string($connRes, $_POST['foodId']);
    $title =  mysqli_real_escape_string($connRes, $_POST['title']);
    $body  =  mysqli_real_escape_string($connRes, $_POST['body']);
    $rate  =  mysqli_real_escape_string($connRes, $_POST['rate']);
    $prosCons  =  json_decode(str_replace("\\","",mysqli_real_escape_string($connRes, $_POST['prosCons'])),true);
    if(!(isset($prosCons['pros']) && isset($prosCons['cons'])))
        $prosCons = array('pros'=>array(), 'cons'=>array());



    // get user phone and name
    $userInfo = $oursAccess->select("*", "users", "`token`='$token'" );
    $phone = $userInfo['phone'];
    $name = $userInfo['name'];

    $trackingIdAndOrders = getOrdersAndLastTrackingIdBaseOnFoodId($resAccess, $phone, $foodId, time()-(8400*3), time());

    if($trackingIdAndOrders[0] < 100)
        exit(json_encode(array('statusCode'=>403, "details"=>"your not allowed to leave comment")));

    $addCommentParams = array(
        "phone"=>$phone,
        "name"=>$name,
        "tracking_id"=>$trackingIdAndOrders[0],
        "food_id"=>$foodId,
        "title"=>$title,
        "body"=>$body,
        "rate"=>$rate,
        "order_type"=>$trackingIdAndOrders['table'] > 0 ? "inRes" : "outRes",
        "pros_cons"=>json_encode($prosCons),
        "status"=>"notConfirmed",
    );

    if($resAccess->insert("comments", $addCommentParams)){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode' => 500, 'details' => "couldn't save comment")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
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