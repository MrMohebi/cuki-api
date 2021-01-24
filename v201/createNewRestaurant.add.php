<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['username']) && isset($_POST['password']) && ($_POST['englishName'] != "")){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!(
    $oursAccess->isTokenValid($_POST['token'], "admins")&&
    $oursAccess->hasTokenAccess($_POST['token'], "admins", array("admin"))
    )){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid or you dont have access in this action")));
    }

    $token = mysqli_real_escape_string($connOurs, $_POST['token']);
    $username = mysqli_real_escape_string($connOurs, $_POST['username']);
    $password = mysqli_real_escape_string($connOurs, $_POST['password']);
    $persian_name = mysqli_real_escape_string($connOurs, $_POST['persianName']);
    $english_name = mysqli_real_escape_string($connOurs, $_POST['englishName']);
    $phone = mysqli_real_escape_string($connOurs, $_POST['phone']);
    $db_name = mysqli_real_escape_string($connOurs, $_POST['dbName']);
    $payment_key = mysqli_real_escape_string($connOurs, $_POST['paymentKey']);

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // if payment key is not set generate a new one
    $payment_key = strlen($payment_key) == 2 ? $payment_key : createPaymentKey($oursAccess);

    $flag_duplicate = $oursAccess->noDuplicate(array(
        "username"=>$username,
        "persian_name"=>$persian_name,
        "english_name"=>$english_name,
        "phone"=>$phone,
        "db_name"=>$db_name,
        "payment_key"=>$payment_key,
        ), "restaurants");
    if($flag_duplicate)
        exit(json_encode(array('statusCode'=>402, "details"=>"some of info are duplicate")));


    $insertNewResParams = array(
        "username"=>$username,
        "password"=>$hashed_password,
        "persian_name"=>$persian_name,
        "english_name"=>$english_name,
        "phone"=>$phone,
        "db_name"=>$db_name,
        "payment_key"=>$payment_key,
        "position"=>"admin",
        "modified_date"=>time(),
    );

    if(
        $oursAccess->insert("restaurants", $insertNewResParams) &&
        createTables(MysqlConfig::createConn($db_name))
    ){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong during creating new restaurant on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}


function createTables($dbConn){
    $sql_food = "CREATE TABLE `foods`(
            `foods_id`      int  AUTO_INCREMENT ,
            `name`          tinytext  ,
            `group`         tinytext  ,
            `details`       text  ,                
            `price`         int  ,
            `status`        tinytext  ,
            `order_times`   bigint  ,
            `discount`      tinyint  ,             
            `delivery_time` smallint  ,
            `thumbnail`     text  ,                 
            `model3d`       text  ,                
            `photos`        mediumtext  ,
            `related_main_name`          tinytext  ,
            `related_price_range`          tinytext  , 
            `related_thumbnail`          tinytext  ,            
            `modified_date`          bigint  ,
            PRIMARY KEY (`foods_id`)
        );";

    $sql_orders ="CREATE TABLE `orders`(
            `orders_id`      int  AUTO_INCREMENT ,
            `tracking_id`    int  ,
            `customer_phone` tinytext  ,
            `order_list`     mediumtext  ,         
            `payment_status` tinytext  ,
            `delivery_price` int  ,
            `order_status`   tinytext  ,
            `address`        text  ,
            `details`        mediumtext  ,        
            `payment_id`     text  ,
            `total_price`    int  ,
            `ordered_date`   bigint  ,
            `delivery_date`  bigint  ,
            `delete_reason`  text  ,
            `offcode`   tinytext  ,
            `paid_foods` mediumtext,              
            `how_to_serve` tinytext,               
            `paid_amount` int,
            `modified_date`          bigint  ,
            `order_table` tinytext,
            PRIMARY KEY (`orders_id`)
        );";

    $sql_info = "CREATE TABLE `info`(
            `info_id`         int  AUTO_INCREMENT ,
            `persian_name`    tinytext  ,
            `english_name`    tinytext  ,
            `phone`           tinytext  ,
            `address`         text  ,
            `address_link`    text  ,
            `owner`           tinytext  ,
            `employers`       text  ,               
            `social_links`    text  ,
            `open_time`       text  ,            
            `type`            tinytext  ,        
            `rate`            tinytext  ,           
            `logo_link`       text  ,
            `min_order_price` mediumint  ,
            `off_codes`       text  ,              
            `modified_date`          bigint  ,
            PRIMARY KEY (`info_id`)
        );";

    $sql_restaurant_customers = "CREATE TABLE `restaurant_customers`(
            `restaurant_customers_id` int  AUTO_INCREMENT ,
            `phone`                   tinytext  ,
            `order_times`             mediumint  ,
            `order_list`              mediumtext  ,         
            `score`                   int  ,
            `total_price`             int  ,
            `rank`                    tinytext  ,
            `off_codes`               text  ,               
            `modified_date`          bigint  ,
            PRIMARY KEY (`restaurant_customers_id`)
        );";


    if(
        mysqli_query($dbConn, $sql_food) &&
        mysqli_query($dbConn, $sql_orders) &&
        mysqli_query($dbConn, $sql_info) &&
        mysqli_query($dbConn, $sql_restaurant_customers)
    ){
        return true;
    }else{
        return false;
    }
}


function createPaymentKey ($oursAccess){
    $paymentKey = generateRandomStringAlphaBet(2);
    while ($oursAccess->noDuplicate(array("payment_key"=>$paymentKey), "restaurants")){
        $paymentKey = generateRandomStringAlphaBet(2);
    }
    return $paymentKey;
}

function generateRandomStringAlphabet($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}