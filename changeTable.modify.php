<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if(($_POST['token'] == $TOKEN_RESTAURANT_ADMIN || $_POST['token'] == $TOKEN_RESTAURANT_WAITER)) {
    include_once 'db/db.config.php';

    $conn_database_restaurant = $dbs[$_POST['englishName']];
    $tableName = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableName']);
    $tableName = strlen($tableName) > 0 ? $tableName : false;

    $tableCapacity = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableCapacity']);
    $tableCapacity = strlen($tableCapacity) > 0 ? $tableCapacity : false;

    $tableStatus = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableStatus']);
    $tableStatus = strlen($tableStatus)> 3 ? $tableStatus : false;

    $tableId = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableId']);

    $tablePhoto = null;
    $tableType = null;

    if(isset($conn_database_restaurant) && ($tableId > 0) ){

        // check if there is no duplicate
        $flag_duplicate = false;
        $sql_get_duplicates = "SELECT * FROM all_tables WHERE `all_tables_id`='$tableId';";
        if ($result = mysqli_query($conn_database_restaurant, $sql_get_duplicates)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $flag_duplicate = true;

                if(!$tableName)
                    $tableName = $row['table_name'];
                if(!$tableCapacity)
                    $tableCapacity = $row['capacity'];
                if(!$tableStatus)
                    $tableStatus = $row['status'];
            }
        }

        $sql_create_new_table = "UPDATE all_tables SET
                                `table_name` = '$tableName', `capacity` ='$tableCapacity',
                                `photo_link` ='$tablePhoto', `status` ='$tableStatus',
                                `type` = '$tableType', `modified_date` ='$nowTimestamp'
                                WHERE `all_tables_id`='$tableId';";
        if(!$flag_duplicate){
            exit(json_encode(array('statusCode'=>402)));
        }elseif(mysqli_query($conn_database_restaurant, $sql_create_new_table)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }

    }else{
        exit(json_encode(array('statusCode'=>400)));
    }

}else{
    exit(json_encode(array('statusCode'=>401)));
}