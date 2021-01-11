<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'db/db.config.php';

$restaurants = array();
$sql_get_all_restaurants_names = "SELECT * FROM restaurants WHERE `position`='admin';";
if ($result = mysqli_query($conn_database_ours, $sql_get_all_restaurants_names)) {
    while ($row = mysqli_fetch_assoc($result)) {
        array_push($restaurants, $row);
    }
}

$result = array();
foreach ($restaurants as $eachRes) {
    array_push($result, array(
        "restaurantId"=>$eachRes['restaurants_id'],
        "englishName" => $eachRes['english_name'],
        "persianName" => $eachRes['persian_name'],
    ));
}

exit(json_encode(array('statusCode'=>200, "data"=>$result)));