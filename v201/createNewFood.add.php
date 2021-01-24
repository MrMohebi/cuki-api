<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['name']) && isset($_POST['group']) && isset($_POST['details'])) {
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

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


    $name = mysqli_real_escape_string($connRes, $_POST['name']);
    $group = mysqli_real_escape_string($connRes, $_POST['group']);
    $details = mysqli_real_escape_string($connRes, $_POST['details']);
    $price = ($_POST['price'] > 900) ? mysqli_real_escape_string($connRes, $_POST['price']) : 100000;
    $status = (strlen($_POST['status']) > 3) ? mysqli_real_escape_string($connRes, $_POST['status']) : 'out of stock';
    $delivery_time = ($_POST['delivery_time'] > 0) ? mysqli_real_escape_string($connRes, $_POST['deliveryTime']) : 0;
    $thumbnail = (strlen($_POST['thumbnail']) > 0) ? mysqli_real_escape_string($connRes, $_POST['thumbnail']) : 'http://dl.mmmohebi.ir/sampleAssets/sampleThumbnail_96x96.png';


    $details_array = array_values(array_filter(array_map('trim', explode("+", str_replace(array("\n", "\r"), '', $details)))));
    $details_array_str = characterFixer(json_encode($details_array));




    $groupTableFullInfo = $oursAccess->select("*", "food_group", "`english_name`='$group'");
    if(count($groupTableFullInfo)<1)
        exit(json_encode(array('statusCode'=>400, "details"=>"group name is not available")));



    $groupInfo = array(
        "englishName"=>$groupTableFullInfo['english_name'],
        "persianName"=>$groupTableFullInfo['persian_name'],
        "logo"=>$groupTableFullInfo['logo'],
        "status"=>$groupTableFullInfo['status'],
        "rank"=>$groupTableFullInfo['rank'],
    );
    $groupInfoStr = characterFixer(json_encode($groupInfo));

    $flag_duplicate = $resAccess->noDuplicate(array(
        "name"=>$name,
        ), "foods");
    if($flag_duplicate)
        exit(json_encode(array('statusCode'=>402, "details"=>"food is duplicate")));


    $insertNewFoodParams = array(
        "name"=>$name,
        "group"=>$groupInfoStr,
        "details"=>$details_array_str,
        "price"=>$price,
        "status"=>$status,
        "order_times"=>0,
        "discount"=>0,
        "delivery_time"=>$delivery_time,
        "thumbnail"=>$thumbnail,
        "modified_date"=>time(),
    );


    if($resAccess->insert("foods", $insertNewFoodParams)){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong during creating new food on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>401)));
}

function characterFixer($str){
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
    }, $str);
}