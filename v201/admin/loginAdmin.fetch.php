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

    $adminInfo = $oursAccess->select("*", "admins", "`username`='$username'");

    if($adminInfo){
        if(password_verify($password, $adminInfo['password'])){
            $oursAccess->update("admins",array("last_login"=>time()),"`username`='$username'");
            exit(json_encode(array(
                'statusCode'=>200,
                'data'=>array(
                    "token"=>$adminInfo['token'],
                    "position"=>$adminInfo['position'],
                    "username"=>$username,
                    "name"=>$adminInfo['name'],
                    "phone"=>$adminInfo['phone'],
                    "lastLogin"=>$adminInfo['last_login'],
                    "promotedBy"=>$adminInfo['promoted_by'],
                    "createdDate"=>$adminInfo['created_date'],
                )
            )));
        }else{
            exit(json_encode(array('statusCode'=>401, 'details'=>"username or password are wrong")));
        }
    }else{
        exit(json_encode(array('statusCode'=>401, 'details'=>"username or password are wrong")));
    }


}else{
    exit(json_encode(array('statusCode'=>400, "details"=>"wrong inputs")));
}
