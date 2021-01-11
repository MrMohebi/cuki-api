<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['username']) && (strlen($_POST['password']) > 7)){
    include_once '../db/db.config.php';

    $username = mysqli_real_escape_string($conn_database_ours, $_POST['username']);
    $password = mysqli_real_escape_string($conn_database_ours, $_POST['password']);


    $admin_info = array();
    $sql_get_adminInfo = "SELECT * FROM admins WHERE username='$username';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_adminInfo)) {
        while ($row = mysqli_fetch_assoc($result)) {
            if((sizeof($row) > 3) && password_verify($password, $row['password'])){
                if($row["status"] == "active")
                    $admin_info = $row;
                else
                    exit(json_encode(array('statusCode'=>403, "details"=>"your account is restricted or deleted")));
            }else{
                exit(json_encode(array('statusCode'=>401, "details"=>"password or username in not correct")));
            }
        }
    }

    

    $result = array(
        "token"=>$admin_info['token'],
        "position"=>$admin_info['position'],
        "username"=>$admin_info['username'],
        "name"=>$admin_info['name'],
        "phone"=>$admin_info['phone'],
        "lastLogin"=>$admin_info['last_login'],
        "promotedBy"=>$admin_info['promoted_by'],
    );

    exit(json_encode(array('statusCode'=>200, 'data'=>$result)));
}else{
    exit(json_encode(array('statusCode'=>400, "details"=>"wrong inputs")));
}