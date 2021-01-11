<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['tableName']) && isset($_POST['englishName'])){
    include_once 'db/db.config.php';
    $conn_database_restaurant = $dbs[$_POST['englishName']];
    if(!$conn_database_restaurant){
        exit(json_encode(array('statusCode'=>400)));
    }

    $randomNum = rand(1111,9999);

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);
    $englishName =  mysqli_real_escape_string($conn_database_ours, $_POST['englishName']);
    $tableName = mysqli_real_escape_string($conn_database_restaurant, $_POST['tableName']);
    $reserveDate = mysqli_real_escape_string($conn_database_restaurant, $_POST['reserveDate']);
    $reserveHours = mysqli_real_escape_string($conn_database_restaurant, $_POST['reserveHours']);

    // check user is valid and get it's phone
    $costumer_phone = false;
    $sql_get_customer_phone = "SELECT * FROM ours_customers WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_phone)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $costumer_phone = $row['phone'];
        }
    }


    if(strlen($costumer_phone) == 11){
        if(!TableIsReserved($conn_database_restaurant, $reserveDate, $reserveHours, $tableName)){
            $sql_save_reserve_table = "INSERT INTO 
                `reserved_tables`(`phone`, `table_name`, `reserved_date`, `reserved_hours`, `reserved_id`, `status`, `modified_date`)   
                VALUES('$costumer_phone', '$tableName', '$reserveDate', '$reserveHours', '$randomNum', 'reserved', '$nowTimestamp');";

            if(mysqli_query($conn_database_restaurant, $sql_save_reserve_table)){
                exit(json_encode(array(
                    'statusCode'=>200,
                    'data'=>array(
                        'reserveId'=>$randomNum,
                        'reserveDate'=>$reserveDate,
                        'tableName'=>$tableName,
                        'reserveHours'=>$reserveHours,
                        ),
                )));
            }else{
                exit(json_encode(array('statusCode'=>500)));
            }
        }else{
            exit(json_encode(array('statusCode'=>402)));
        }
    }else{
        exit(json_encode(array('statusCode'=>401)));
    }
}else{
    exit(json_encode(array('statusCode'=>400)));
}



function TableIsReserved($connRes, $reserveDate, $reserveHours, $tableName){
    $nowTimestamp = time();
    $oneDayBeforeNow = $nowTimestamp - 86400;

    $reservedTables = array();
    $sql_get_reserves = "SELECT * FROM `reserved_tables` WHERE `reserved_date` > '$oneDayBeforeNow' AND `status` <> 'deleted';";
    if ($result = mysqli_query($connRes, $sql_get_reserves)) {
        while ($row = mysqli_fetch_assoc($result)) {
            array_push($reservedTables, $row);
        }
    }

    foreach ($reservedTables as $eachReserve){
        if(count(array_intersect(json_decode($reserveHours),json_decode($eachReserve['reserved_hours']))) > 0){
            if(date('d/m', $eachReserve['reserved_date']) == date('d/m', $reserveDate)){
                if($tableName == $eachReserve['table_name']){
                    return $eachReserve['reserved_id'];
                }
            }
        }
    };
    return false;
}

