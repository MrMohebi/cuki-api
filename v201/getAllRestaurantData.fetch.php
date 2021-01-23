<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(strlen($_POST['englishName']) > 2){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connRes = MysqlConfig::connRes($_POST['englishName']);
    $resAccess = new MysqldbAccess($connRes);


    $foodListArr = getFoodList($resAccess);
    $infoListArr = getResInfo($resAccess);


    if(sizeof($foodListArr) > 1){
        exit(json_encode(array(
            'statusCode'=>200,
            'data'=>array(
                'foods'=>$foodListArr,
                'restaurantInfo'=>$infoListArr
            )
        )));
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}



function getFoodList($resAccess){
    $foodsList = $resAccess->select("*", "foods", false,"`foods_id` DESC");
    for($i = 0; $i < count($foodsList) ; $i++){
        foreach ($foodsList[$i] as $key => $val){
            $foodsList[$i][$key] = is_numeric($val) ? $val+0: $val;
        }
        $foodsList[$i]['group'] = json_decode($foodsList[$i]['group']);
        $foodsList[$i]['details'] = json_decode($foodsList[$i]['details']);
        $foodsList[$i]['photos'] = json_decode($foodsList[$i]['photos']);
        $foodsList[$i]['related_price_range'] = json_decode($foodsList[$i]['related_price_range']);
    }
    return $foodsList;
}


function getResInfo($resAccess){
    $infoList = $resAccess->select("*", "info");
    foreach ($infoList as $key => $val){
        $infoList[$key] = is_numeric($val) ? $val+0: $val;
    }
    return $infoList;
}
