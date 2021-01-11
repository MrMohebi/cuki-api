<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if(($_POST['token'] == $TOKEN_RESTAURANT_ADMIN) || ($_POST['token'] == $TOKEN_RESTAURANT_COUNTER) ||($_POST['token'] == $TOKEN_RESTAURANT_KITCHEN)){
    include_once 'db/db.config.php';
    $conn_database_restaurant = $dbs[$_POST['englishName']];

    $englishName = mysqli_real_escape_string($conn_database_ours, $_POST['englishName']);
    $foods_id = mysqli_real_escape_string($conn_database_restaurant, $_POST['foodId']);
    $persian_name = mysqli_real_escape_string($conn_database_restaurant, $_POST['persianName']);
    $group = mysqli_real_escape_string($conn_database_restaurant, $_POST['group']);
    $details = mysqli_real_escape_string($conn_database_restaurant, $_POST['details']);
    $price = mysqli_real_escape_string($conn_database_restaurant, $_POST['price']);
    $status = mysqli_real_escape_string($conn_database_restaurant, $_POST['status']);
    $discount = mysqli_real_escape_string($conn_database_restaurant, $_POST['discount']);
    $delivery_time = mysqli_real_escape_string($conn_database_restaurant, $_POST['deliveryTime']);
    $thumbnail = mysqli_real_escape_string($conn_database_restaurant, $_POST['thumbnail']);
    $model3d= mysqli_real_escape_string($conn_database_restaurant, $_POST['model3d']);
    $photos = mysqli_real_escape_string($conn_database_restaurant, $_POST['photos']);

    $details_array = array_values(array_filter(array_map('trim', explode("+", str_replace(array("\n", "\r"), '', $details)))));
    $details_array_str = json_encode($details_array);
    $details_array_str = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
    }, $details_array_str);




    if(isset($_POST['persianNameChange']) && (strlen($persian_name) >2)){
        $sql_change_name = "UPDATE foods SET `name`='$persian_name', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_name)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['groupChange'])){
        $sql_change_group = "UPDATE foods SET `group`='$group', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_group)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['detailsChange']) && (strlen($details) >2)){
        $sql_change_details = "UPDATE foods SET `details`='$details_array_str', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_details)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['priceChange']) && ($price >= 1000)){
        $sql_change_price = "UPDATE foods SET `price`='$price', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_price)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }


    if(isset($_POST['statusChange']) && (strlen($status) > 2)){
        $sql_change_status = "UPDATE foods SET `status`='$status', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_status)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }


    if(isset($_POST['discountChange']) && (strlen($discount) >2)){
        $sql_change_discount = "UPDATE foods SET `discount`='$discount', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_discount)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }


    if(isset($_POST['deliveryTimeChange']) && (strlen($delivery_time) >0)){
        $sql_change_delivery_time = "UPDATE foods SET `delivery_time`='$delivery_time', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_delivery_time)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['thumbnailChange']) && (strlen($thumbnail) >2)){
        $sql_change_thumbnail = "UPDATE foods SET `thumbnail`='$thumbnail', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_thumbnail)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['model3dChange']) && (strlen($model3d) >2)){
        $sql_change_model3d = "UPDATE foods SET `model3d`='$model3d', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_model3d)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    if(isset($_POST['photosChange']) && (strlen($photos) >2)){
        $sql_change_model3d = "UPDATE foods SET `photos`='$photos', `modified_date`='$nowTimestamp' WHERE `foods_id`='$foods_id';";
        if(mysqli_query($conn_database_restaurant, $sql_change_model3d)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }
    }

    exit(json_encode(array('statusCode'=>400)));

}else{
    exit(json_encode(array('statusCode'=>400)));
}