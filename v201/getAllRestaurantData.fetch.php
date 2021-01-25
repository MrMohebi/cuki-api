<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(strlen($_POST['englishName']) > 2){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOur = MysqlConfig::connOurs();
    $ourAccess = new MysqldbAccess($connOur);

    $connRes = MysqlConfig::connRes($_POST['englishName']);
    $resAccess = new MysqldbAccess($connRes);


    $foodListArr = getFoodList($resAccess,$connOur);
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



function getFoodList($resAccess, $ourAccess){
    $foodsList = $resAccess->select("*", "foods", false,"`foods_id` DESC");
    $groupsInfo = $ourAccess->select("*", "food_group");
    for($i = 0; $i < count($foodsList) ; $i++){
        foreach ($foodsList[$i] as $key => $val){
            $foodsList[$i][$key] = is_numeric($val) ? $val+0: $val;
        }
        $group=array();
        foreach ($groupsInfo as $eGroup){
            if($eGroup["englishName"] == $foodsList[$i]['group'])
                $group = $eGroup;
        }

        $groupInfo = array(
            "englishName"=>$group['english_name'],
            "persianName"=>$group['persian_name'],
            "logo"=>$group['logo'],
            "status"=>$group['status'],
            "rank"=>$group['rank'],
            "averageColor"=>$group['average_color'],
            "type"=>$group['type'],
        );
        $foodsList[$i]['group'] = $groupInfo;
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
