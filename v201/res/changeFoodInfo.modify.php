<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['token']) && isset($_POST['foodId'])){
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!(
        $oursAccess->isTokenValid($_POST['token'], "restaurants")&&
        $oursAccess->hasTokenAccess($_POST['token'], "restaurants", array("admin"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    $connRes = MysqlConfig::connRes($oursAccess->select('english_name','restaurants',"`token`='".$_POST['token']."'"));
    $resAccess = new MysqldbAccess($connRes);


    $foodId = mysqli_real_escape_string($connOurs, $_POST['foodId']);
    $persianName = trim(mysqli_real_escape_string($connOurs, $_POST['persianName']));
    $group = trim(mysqli_real_escape_string($connOurs, $_POST['group']));
    $details = mysqli_real_escape_string($connOurs, $_POST['details']);
    $price = mysqli_real_escape_string($connOurs, $_POST['price']);
    $status = mysqli_real_escape_string($connOurs, $_POST['status']);
    $discount = mysqli_real_escape_string($connOurs, $_POST['discount']);
    $deliveryTime = mysqli_real_escape_string($connOurs, $_POST['deliveryTime']);
    $counterAppFoodId = mysqli_real_escape_string($connOurs, $_POST['counterAppFoodId']);


    // validate and translate inputs
    if(strlen($group) > 2){
        $groupTableFullInfo = $oursAccess->select("*", "food_group", "`english_name`='$group'");
        if(count($groupTableFullInfo)<2)
            exit(json_encode(array('statusCode'=>400, "details"=>"group name is not available")));
    }

    if(strlen($details) > 2){
        $details_array = array_values(array_filter(array_map('trim', explode("+", str_replace(array("\n", "\r"), '', $details)))));
        $details_array_str = characterFixer(json_encode($details_array));
    }





    $previousFoodInfo = $resAccess->select("*", "foods", "`foods_id`='$foodId'");

    if(count($previousFoodInfo) < 3)
        exit(json_encode(array('statusCode'=>404, "details"=>"couldn't find food, foodId maybe incorrect")));



    $sqlUpdateFoodInfoParams = array(
        "name"=> strlen($persianName) > 2 ? $persianName : $previousFoodInfo["name"],
        "group"=> strlen($group) > 2 ? $group : $previousFoodInfo["group"],
        "details"=> strlen($details) > 2 ? $details_array_str : $previousFoodInfo["details"],
        "price"=> $price > 999 ? $price : $previousFoodInfo["price"],
        "status"=> in_array($status, array("in stock", "out of stock", "deleted")) ? $status : $previousFoodInfo["status"],
        "discount"=> $discount > 0 ? $discount : $previousFoodInfo["discount"],
        "counter_app_food_id"=> $counterAppFoodId > 0 ? $counterAppFoodId : $previousFoodInfo["counter_app_food_id"],
        "delivery_time"=> $deliveryTime > 0 ? $deliveryTime : $previousFoodInfo["delivery_time"],
        "modified_date"=> time(),
    );

    if(isset($_FILES['foodThumbnail'])){
        if(!changeFoodThumbnail($_POST['token'], $foodId, $_FILES['foodThumbnail']))
            exit(json_encode(array('statusCode'=>500, "details"=>"couldn't upload thumbnail")));
    }




    if($resAccess->update("foods", $sqlUpdateFoodInfoParams, "`foods_id`='$foodId'")){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"something went wrong during change food info on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}

function characterFixer($str){
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
    }, $str);
}

function changeFoodThumbnail($token, $foodId, $thumbnail){
    $image_upload_val = array(
        'token'=>$token,
        'foodId'=>$foodId,
        'foodThumbnail'=> new CurlFile($thumbnail['tmp_name'], $thumbnail['type'], $thumbnail['name']),
    );
    $ch = curl_init();
    $curl_url = "https://dl.cuki.ir/api/uploadfoodthumbnail.modify.php";
    curl_setopt($ch, CURLOPT_URL,$curl_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$image_upload_val);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_result = json_decode(curl_exec($ch),true);
    curl_close($ch);
    if($server_result['statusCode'] == 200)
        return true;
    else
        return false;
}



//$requestHandler = curl_init();
//curl_setopt($requestHandler, CURLOPT_URL, 'https://api.payping.ir/v2/pay/verify');
//curl_setopt($requestHandler, CURLOPT_POSTFIELDS, json_encode($info_params));
//curl_setopt($requestHandler, CURLOPT_RETURNTRANSFER, TRUE);
//curl_setopt($requestHandler, CURLOPT_HTTPHEADER, array(
//    'Accept: application/json',
//    'Content-Type: application/json',
//    "Authorization: bearer $api_key",
//));
//
//$result = json_decode(curl_exec($requestHandler), true);
//curl_close($requestHandler);
//$verifyCardNumber = $result['cardNumber'];