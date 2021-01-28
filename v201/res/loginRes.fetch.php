<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['username']) && (strlen($_POST['password']) > 7)){
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $username = mysqli_real_escape_string($connOurs, $_POST['username']);
    $password = mysqli_real_escape_string($connOurs, $_POST['password']);

    $userInfo = $oursAccess->select("*", "restaurants", "`username`='$username'");

    if($userInfo){
        if(password_verify($password, $userInfo['password'])){
            exit(json_encode(array(
                'statusCode'=>200,
                'data'=>array(
                    "token"=>$userInfo['token'],
                    "position"=>$userInfo['position'],
                    "username"=>$username,
                    "resPersianName"=>$userInfo['persian_name'],
                    "resEnglishName"=>$userInfo['english_name']
                )
            )));
        }else{
            exit(json_encode(array('statusCode'=>401, 'details'=>"username or password are wrong")));
        }
    }else{
        exit(json_encode(array('statusCode'=>401, 'details'=>"username or password are wrong")));
    }


}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs!")));
}