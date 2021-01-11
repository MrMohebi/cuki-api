<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['username']) && (strlen($_POST['password']) > 7)){
    include_once 'token/tokens.php';
    include_once 'db/db.config.php';

    $username = mysqli_real_escape_string($conn_database_ours, $_POST['username']);
    $password = mysqli_real_escape_string($conn_database_ours, $_POST['password']);


    $user_info = array();
    $sql_get_userInfo = "SELECT * FROM restaurants WHERE username='$username';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_userInfo)) {
        while ($row = mysqli_fetch_assoc($result)) {
            if((sizeof($row) > 3) && password_verify($password, $row['password'])){
                $user_info = $row;
            }else{
                exit(json_encode(array('statusCode'=>401)));
            }
        }
    }

    if($user_info["position"] == "admin"){
        $token = $TOKEN_RESTAURANT_ADMIN;
    }else if($user_info["position"] == "waiter"){
        $token = $TOKEN_RESTAURANT_WAITER;
    }else if($user_info["position"] == "kitchen"){
        $token = $TOKEN_RESTAURANT_WAITER;
    }else if($user_info["position"] == "counter"){
        $token = $TOKEN_RESTAURANT_COUNTER;
    }else{
        exit(json_encode(array('statusCode'=>401)));
    }

    $result = array(
        "token"=>$token,
        "position"=>$user_info['position'],
        "username"=>$username,
        "persianName"=>$user_info['persian_name'],
        "englishName"=>$user_info['english_name']
    );

    exit(json_encode(array('statusCode'=>200, 'data'=>$result)));
}else{
    exit(json_encode(array('statusCode'=>400)));
}