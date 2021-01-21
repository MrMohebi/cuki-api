<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if( ($_POST['token'] == $TOKEN_ADMIN) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['english_name']) && ($_POST['english_name'] != "")){
    include_once 'db/db.config.php';

    $username = mysqli_real_escape_string($conn_database_ours, $_POST['username']);
    $password = mysqli_real_escape_string($conn_database_ours, $_POST['password']);
    $persian_name = mysqli_real_escape_string($conn_database_ours, $_POST['persian_name']);
    $english_name = mysqli_real_escape_string($conn_database_ours, $_POST['english_name']);
    $phone = mysqli_real_escape_string($conn_database_ours, $_POST['phone']);
    $db_name = mysqli_real_escape_string($conn_database_ours, $_POST['db_name']);
    $payment_key = mysqli_real_escape_string($conn_database_ours, $_POST['payment_key']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // check if there is no duplicate
    $flag_duplicate = false;
    $sql_get_duplicates = "SELECT * FROM restaurants WHERE username='$username' OR persian_name='$persian_name' OR english_name='$english_name' OR phone='$phone' OR db_name='$db_name';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_duplicates)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $flag_duplicate = true;
        }
    }


    $sql_create_new_restaurant = "INSERT INTO 
                                restaurants(`username`, `password`, `persian_name`, `english_name` ,`phone`, `db_name`, `payment_key`, `modified_date`, `position` )
                                VALUES('$username', '$hashed_password', '$persian_name', '$english_name', '$phone', '$db_name', '$payment_key', '$nowTimestamp', 'admin');";

    if($flag_duplicate){
        exit(json_encode(array('statusCode'=>402)));
    }elseif(mysqli_query($conn_database_ours, $sql_create_new_restaurant)){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }
}else{
    exit(json_encode(array('statusCode'=>400)));
}