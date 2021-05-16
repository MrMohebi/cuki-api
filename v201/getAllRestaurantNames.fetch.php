<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once "DataAccess/MysqldbAccess.php";
include_once "DataAccess/db.config.php";

$connOur = MysqlConfig::connOurs();
$ourAccess = new MysqldbAccess($connOur);

$restaurants = $ourAccess->select("*", "restaurants", "`position`='admin'");
$restaurants = isset($restaurants['id']) ? array($restaurants) : $restaurants;

$result = array();
foreach ($restaurants as $eachRes) {
    array_push($result, array(
        "restaurantId"=>$eachRes['id'],
        "englishName" => $eachRes['english_name'],
        "persianName" => $eachRes['persian_name'],
    ));
}

exit(json_encode(array('statusCode'=>200, "data"=>$result)));