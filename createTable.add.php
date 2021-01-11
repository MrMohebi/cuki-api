<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if(($_POST['token'] == $TOKEN_RESTAURANT_ADMIN || $_POST['token'] == $TOKEN_RESTAURANT_WAITER)) {
    include_once 'db/db.config.php';

    $conn_database_restaurant = $dbs[$_POST['englishName']];
    $tableName = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableName']);
    $tableCapacity = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableCapacity']);
    $tableStatus = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableStatus']);

    $tablePhoto = null;
    $tableType = null;

    if(isset($conn_database_restaurant) && ($tableCapacity > 0) && (strlen($tableName) > 0) && (strlen($tableStatus) > 3)){

        // check if there is no duplicate
        $flag_duplicate = false;
        $sql_get_duplicates = "SELECT * FROM all_tables WHERE `table_name`='$tableName';";
        if ($result = mysqli_query($conn_database_restaurant, $sql_get_duplicates)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $flag_duplicate = true;
            }
        }

        $sql_create_new_table = "INSERT INTO 
                                all_tables(`table_name`, `capacity`,`photo_link`,`status`, `type`, `modified_date`)
                                VALUES('$tableName', '$tableCapacity', '$tablePhoto', '$tableStatus', '$tableType', '$nowTimestamp');";
        if($flag_duplicate){
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