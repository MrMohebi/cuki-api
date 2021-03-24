<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['token'])){
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


    $persianName = trim(mysqli_real_escape_string($connOurs, $_POST['persianName']));
    $englishName = trim(mysqli_real_escape_string($connOurs, $_POST['englishName']));
    $counterPhone = mysqli_real_escape_string($connOurs, $_POST['counterPhone']);
    $phone = json_decode(str_replace("\\","",mysqli_real_escape_string($connOurs, $_POST['phone'])));
    $addressText = mysqli_real_escape_string($connOurs, $_POST['addressText']);
    $addressLink = mysqli_real_escape_string($connOurs, $_POST['addressLink']);
    $owner = mysqli_real_escape_string($connOurs, $_POST['owner']);
    $employers = json_decode(str_replace("\\","",mysqli_real_escape_string($connOurs, $_POST['employers'])));
    $socialLinks = json_decode(str_replace("\\","",mysqli_real_escape_string($connOurs, $_POST['socialLinks'])), true);
    $openTime = json_decode(str_replace("\\","",mysqli_real_escape_string($connOurs, $_POST['openTime'])), true);
    $type = json_decode(str_replace("\\","",mysqli_real_escape_string($connOurs, $_POST['type'])));
    $minOrderPrice = mysqli_real_escape_string($connOurs, $_POST['minOrderPrice']);


    $previousResInfo = $resAccess->select("*", "info", false, "`info_id` DESC LIMIT 1");
    $rowId = $previousResInfo['info_id'];

    $sqlUpdateResInfoParams = array(
        "persian_name"=> strlen($persianName) > 2 ? $persianName : $previousResInfo["persian_name"],
        "english_name"=> strlen($englishName) > 2 ? $englishName : $previousResInfo["english_name"],
        "counter_phone"=> strlen($counterPhone) == 11 ? $counterPhone : $previousResInfo["counter_phone"],
        "phone"=> (is_array($phone)) ? json_encode($phone) : (strlen($phone) > 4 ? json_encode(array($phone)) : $previousResInfo["phone"]),
        "address"=> strlen($addressText) > 3 ? $addressText : $previousResInfo["address"],
        "address_link"=> strlen($addressLink) > 10 ? $addressLink : $previousResInfo["address_link"],
        "owner"=> strlen($addressLink) > 10 ? $addressLink : $previousResInfo["owner"],
        "employers"=> count($employers) > 0 ? json_encode($employers) : $previousResInfo["employers"],
        "social_links"=> count($socialLinks) > 0  ? json_encode($socialLinks) : $previousResInfo["social_links"],
        "open_time"=> count($openTime) == 7  ? json_encode($openTime) : $previousResInfo["open_time"],
        "type"=> count($type) > 0  ? json_encode($type) : $previousResInfo["type"],
        "min_order_price"=> $minOrderPrice > 100  ? $minOrderPrice : $previousResInfo["min_order_price"],
        "modified_date"=> time(),
    );



    if($resAccess->update("info", $sqlUpdateResInfoParams, "`info_id`='$rowId'")){
        exit(json_encode(array('statusCode'=>200, "test"=>$openTime)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"something went wrong during change food info on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}
