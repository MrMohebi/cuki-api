<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(($_POST['englishName'] != "")&&(strlen($_POST['catPersianName'])>2)&&(strlen($_POST['catEnglishName'])>2)&&(strlen($_POST['logo'])>2)){
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

    $connRes = MysqlConfig::connRes($_POST['englishName']);
    $resAccess = new MysqldbAccess($connRes);

    $catPersianName = mysqli_real_escape_string($connRes, $_POST['catPersianName']);
    $catEnglishName = mysqli_real_escape_string($connRes, $_POST['catEnglishName']);
    $logo = mysqli_real_escape_string($connRes, $_POST['logo']);
    $rank = mysqli_real_escape_string($connRes, $_POST['rank']);



    $flag_duplicate = $resAccess->noDuplicate(array(
        "persian_name"=>$catPersianName,
        "english_name"=>$catEnglishName,
    ), "food_group");
    if($flag_duplicate)
        exit(json_encode(array('statusCode'=>402, "details"=>"some of info are duplicate")));


    $insertNewCatParams = array(
        "persian_name"=>$catPersianName,
        "english_name"=>$catEnglishName,
        "logo"=>$logo,
        "status"=>"active",
    );

    if($resAccess->insert("food_group", $insertNewCatParams)){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong during creating new category on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}
