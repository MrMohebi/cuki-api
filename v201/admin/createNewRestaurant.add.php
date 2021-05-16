<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


if(isset($_POST['username']) && isset($_POST['password']) && ($_POST['englishName'] != "")){
    include_once "../DataAccess/MysqldbAccess.php";
    include_once "../DataAccess/db.config.php";

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
        'token'=>bin2hex(openssl_random_pseudo_bytes(32)),
        "payment_key"=>$payment_key,
        "position"=>"admin",
        "modified_at"=>time(),
    );

    if(
        $oursAccess->insert("restaurants", $insertNewResParams) &&
        createTables(MysqlConfig::createConn($db_name))&&
        addResInfoToTable(MysqlConfig::connRes($english_name), $english_name)&&
        setResCode($oursAccess, $english_name)
    ){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"some thing went wrong during creating new restaurant on server")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}


function addResInfoToTable($connRes, $englishName){
    $time = time();
    $sqlCommandAddInfo = "INSERT INTO info(`english_name`,`modified_at`) VALUES ('$englishName', '$time')";
    if(mysqli_query($connRes, $sqlCommandAddInfo)){
        return true;
    }else{
        return false;
    }
}


function createTables($dbConn){
    $sql_food = "CREATE TABLE `foods`(
            `id`      int  AUTO_INCREMENT ,
            `counter_app_food_id`              tinytext  ,
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
            `modified_at`          bigint  ,
            PRIMARY KEY (`id`)
        );";

    $sql_orders ="CREATE TABLE `orders`(
            `id`      int  AUTO_INCREMENT ,
            `tracking_id`    int  ,
            `user_phone` tinytext  ,
            `items`     mediumtext  ,         
            `payment_status` tinytext  ,
            `delivery_price` int  ,
            `order_status`   tinytext  ,
            `counter_app_status`              tinytext  ,
            `address`        text  ,
            `details`        mediumtext  ,        
            `payment_id`     text  ,
            `total_price`    int  ,
            `created_at`   bigint  ,
            `delivery_at`  bigint  ,
            `delete_reason`  text  ,
            `offcode`   tinytext  ,
            `paid_foods` mediumtext,              
            `how_to_serve` tinytext,               
            `paid_amount` int,
            `modified_at`          bigint  ,
            `table` tinytext,
            PRIMARY KEY (`id`)
        );";

    $sql_info = "CREATE TABLE `info`(
            `info_id`         int  AUTO_INCREMENT ,
            `status`    tinytext,
            `persian_name`    tinytext  ,
            `english_name`    tinytext  ,
            `counter_phone`           tinytext  ,
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
            `favicon_link`       text  ,
            `min_order_price` mediumint  ,
            `off_codes`       text  ,              
            `modified_at`          bigint  ,
            PRIMARY KEY (`info_id`)
        );";

    $sql_customers = "CREATE TABLE `customers`(
            `customers_id` int  AUTO_INCREMENT ,
            `phone`                   tinytext  ,
            `order_times`             mediumint  ,
            `items`              mediumtext  ,         
            `score`                   int  ,
            `total_price`             int  ,
            `rank`                    tinytext  ,
            `off_codes`               text  ,               
            `modified_at`          bigint  ,
            PRIMARY KEY (`customers_id`)
        );";

    $sql_comment = "CREATE TABLE `comments`(
            `id`    int  AUTO_INCREMENT ,
            `phone`          tinytext  ,
            `name`           tinytext  ,
            `tracking_id`    int  ,
            `food_id`        int ,
            `title`          tinyint  ,
            `body`           text  ,
            `rate`           tinyint  ,
            `order_type`     tinytext  ,
            `pros_cons`      text  ,              
            `status`         tinytext  ,
            `commented_date` bigint  ,
            `modified_at`          bigint  ,
            PRIMARY KEY (`id`)
        );";

    $sql_pager = "CREATE TABLE `pager`(
            `id` int  AUTO_INCREMENT ,
            `table`              tinytext  ,
            `date`         bigint  ,
            `status`        tinytext  ,
            `user_phone`        tinytext  ,
            `modified_at`          bigint  ,
            PRIMARY KEY (`id`)
        );";

    $sql_infoFRow = "INSERT INTO info(`status`) VALUES ('active');";
    if(
        mysqli_query($dbConn, $sql_food) &&
        mysqli_query($dbConn, $sql_orders) &&
        mysqli_query($dbConn, $sql_info) &&
        mysqli_query($dbConn, $sql_customers) &&
        mysqli_query($dbConn, $sql_comment)&&
        mysqli_query($dbConn, $sql_infoFRow)&&
        mysqli_query($dbConn, $sql_pager)
    ){
        return true;
    }else{
        return false;
    }
}


function createPaymentKey ($oursAccess): string{
    $paymentKey = generateRandomStringAlphaBet(2);
    while ($oursAccess->noDuplicate(array("payment_key"=>$paymentKey), "restaurants")){
        $paymentKey = generateRandomStringAlphaBet(2);
    }
    return $paymentKey;
}

function generateRandomStringAlphabet($length = 10): string{
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function setResCode($oursAccess, $resEnglishName):bool{
    $resId = $oursAccess->select("id", "restaurants", "`english_name`='$resEnglishName'");
    return $oursAccess->update("restaurants", array("res_code"=>($resId+10)), "`english_name`='$resEnglishName'");
}