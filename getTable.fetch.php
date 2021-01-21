<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if(($_POST['token'] == $TOKEN_RESTAURANT_ADMIN || $_POST['token'] == $TOKEN_RESTAURANT_WAITER)) {
    include_once 'db/db.config.php';

    $conn_database_restaurant = $dbs[$_POST['englishName']];


    if(isset($conn_database_restaurant)) {
        $tableList = array();
        $sql_get_duplicates = "SELECT * FROM all_tables WHERE `status`<>'deleted';";
        if ($result = mysqli_query($conn_database_restaurant, $sql_get_duplicates)) {
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($tableList, $row);
            }
        }
        if (count($tableList) > 0) {
            exit(json_encode(array('statusCode' => 200, "data" => $tableList)));
        } else {
            exit(json_encode(array('statusCode' => 400)));
        }
    }else{
        exit(json_encode(array('statusCode'=>400)));
    }

}else{
    exit(json_encode(array('statusCode'=>401)));
}