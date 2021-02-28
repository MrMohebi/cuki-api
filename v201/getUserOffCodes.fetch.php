<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!($oursAccess->isTokenValid($_POST['token'], "ours_customers"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['resEnglishName']);
    $token =  mysqli_real_escape_string($connOurs, $_POST['token']);

    $userPhone = $oursAccess->select("phone", "ours_customers", "`token`='$token'");


    $offCodes = $oursAccess->select("*", "off_codes","`target`='$userPhone' AND (`place`='$englishName' OR `place`='general')");

    if(isset($offCodes['id'])){
        $offCodes = array($offCodes);
    }

    if(sizeof($offCodes) > 0){
        $result = array();
        foreach ($offCodes as $eOffCode){
            array_push($result, array(
                'target'=>$eOffCode['target'],
                'place'=>$eOffCode['place'],
                'times'=>$eOffCode['times'],
                'used'=>$eOffCode['used'],
                'maxPrice'=>$eOffCode['max_price'],
                'minPrice'=>$eOffCode['min_price'],
                'discountPercentage'=>$eOffCode['discount_percentage'],
                'discountPrice'=>$eOffCode['discount_price'],
                'name'=>$eOffCode['name'],
                'code'=>$eOffCode['body'],
                'from'=>$eOffCode['from_date'],
                'to'=>$eOffCode['to_date'],
                'status'=>$eOffCode['status'],
                'lastUsedDate'=>$eOffCode['modified_date'],
            ));
        }
        exit(json_encode(array('statusCode'=>200, 'data'=>$result)));
    }else{
        exit(json_encode(array('statusCode'=>404, "details"=>"customer didn't find any off code")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}
