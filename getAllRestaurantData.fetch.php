<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['english_name'])){
    include_once 'db/db.config.php';
    $english_name =  mysqli_real_escape_string($conn_database_ours, $_POST['english_name']);

    $conn_database_restaurant = $dbs[$english_name];

    $allTableList = array();
    $sql_get_all_table_list = "SELECT * FROM all_tables WHERE `status` <> 'deleted' ORDER BY all_tables_id DESC;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_all_table_list)) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($allTableList, $row);
        }
    }

    $reservedTableList = array();
    $sql_get_reserved_tables_list = "SELECT * FROM reserved_tables ORDER BY reserved_tables_id DESC;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_reserved_tables_list)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['phone'] = "";
            $row['reserved_id'] = "";
            array_push($reservedTableList, $row);
        }
    }

    $assets = array();
    $sql_get_assets = "SELECT * FROM assets ORDER BY assets_id DESC;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_assets)) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($assets, $row);
        }
    }

    $comments = array();
    $sql_get_comments = "SELECT * FROM comments ORDER BY comments_id DESC;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_comments)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $row['phone'] = "";
            array_push($comments, $row);
        }
    }

    $foods = array();
    $sql_get_foods = "SELECT * FROM foods ORDER BY foods_id DESC;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_foods)) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($foods, $row);
        }
    }

    $restaurantInfo = array();
    $sql_get_restaurant_info = "SELECT * FROM info ORDER BY info_id DESC;";
    if ($result = mysqli_query($conn_database_restaurant, $sql_get_restaurant_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $restaurantInfo =  $row;
        }
    }

    if(sizeof($foods) > 1){
        exit(json_encode(array(
            'statusCode'=>200,
            'data'=>array(
                'allTableList'=> $allTableList,
                'reservedTableList'=>$reservedTableList,
                'assets'=>$assets,
                'comments'=>$comments,
                'foods'=>$foods,
                'restaurantInfo'=>$restaurantInfo
            )
        )));
    }else{
        exit(json_encode(array('statusCode'=>500)));

    }

}else{
    exit(json_encode(array('statusCode'=>400)));
}