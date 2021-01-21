<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');


include_once 'token/tokens.php';
if(($_POST['token'] == $TOKEN_RESTAURANT_ADMIN)){
    include_once 'db/db.config.php';
    $conn_database_restaurant = $dbs[$_POST['englishName']];

    $persian_name = mysqli_real_escape_string($conn_database_ours, $_POST['persianName']);
    $english_name = mysqli_real_escape_string($conn_database_ours, $_POST['englishName']);
    $phone = mysqli_real_escape_string($conn_database_ours, $_POST['phone']);
    $address = mysqli_real_escape_string($conn_database_ours, $_POST['address']);
    $address_link = mysqli_real_escape_string($conn_database_ours, $_POST['addressLink']);
    $owner = mysqli_real_escape_string($conn_database_ours, $_POST['owner']);
    $employers = mysqli_real_escape_string($conn_database_ours, $_POST['employers']);
    $social_links = mysqli_real_escape_string($conn_database_ours, $_POST['socialLinks']);
    $open_time = mysqli_real_escape_string($conn_database_ours, $_POST['openTime']);
    $type = mysqli_real_escape_string($conn_database_ours, $_POST['type']);
    $logo_link = mysqli_real_escape_string($conn_database_ours, $_POST['logoLink']);
    $min_order_price = mysqli_real_escape_string($conn_database_ours, $_POST['minOrderPrice']);


    if(isset($_POST['persianNameChange']) && (strlen($persian_name) > 3)){
        $sql_change_persianName = "UPDATE info SET `persian_name` = '$persian_name', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_persianName)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['englishNameChange']) && (strlen($english_name) > 3)){
        $sql_change_englishName = "UPDATE info SET `english_name` = '$english_name', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_englishName)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['phoneChange']) && (strlen($phone) > 11)){
        $sql_change_phone = "UPDATE info SET `phone` = '$phone', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_phone)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['addressChange']) && (strlen($address) > 10)){
        $sql_change_address = "UPDATE info SET `address` = '$address', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_address)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['addressLinkChange']) && (strlen($address_link) > 10)){
        $sql_change_addressLink = "UPDATE info SET `address_link` = '$address_link', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_addressLink)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['ownerChange']) && (strlen($owner) > 3)){
        $sql_change_owner = "UPDATE info SET `owner` = '$owner', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_owner)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['employersChange']) && (strlen($employers) > 3)){
        $sql_change_employers = "UPDATE info SET `employers` = '$employers', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_employers)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['socialLinksChange']) && (strlen($social_links) > 4)){
        $sql_change_socialLinks = "UPDATE info SET `social_links` = '$social_links', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_socialLinks)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['openTimeChange']) && (strlen($open_time) > 3)){
        $sql_change_openTime = "UPDATE info SET `open_time` = '$open_time', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_openTime)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['typeChange']) && (strlen($type) > 3)){
        $sql_change_openTime = "UPDATE info SET `type` = '$type', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_openTime)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['logoLinkChange']) && (strlen($logo_link) > 10)){
        $sql_change_logoLink = "UPDATE info SET `logo_link` = '$logo_link', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_logoLink)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['minOrderPriceChange']) && (strlen($min_order_price) > 2)){
        $sql_change_minOrderPrice = "UPDATE info SET `min_order_price` = '$min_order_price', `modified_date`='$nowTimestamp';";
        if(mysqli_query($conn_database_restaurant, $sql_change_minOrderPrice)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    exit(json_encode(array('statusCode'=>400)));

}else{
    exit(json_encode(array('statusCode'=>401)));
}






    // check if there is no duplicate
    $flag_duplicate = false;
    $sql_get_duplicates = "SELECT * FROM restaurants WHERE username='$username' OR persian_name='$persian_name' OR english_name='$english_name' OR phone='$phone' OR db_name='$db_name';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_duplicates)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $flag_duplicate = true;
        }
    }
