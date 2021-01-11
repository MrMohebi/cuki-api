<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

include_once 'token/tokens.php';
if(($_POST['token'] == $TOKEN_RESTAURANT_ADMIN || $_POST['token'] == $TOKEN_RESTAURANT_KITCHEN) && isset($_POST['name']) && isset($_POST['group']) && isset($_POST['details'])) {
    include_once 'db/db.config.php';

    if(isset($dbs[$_POST['english_name']])){

        $conn_database_restaurant = $dbs[$_POST['english_name']];
        $name = mysqli_real_escape_string($conn_database_restaurant, $_POST['name']);
        $group = mysqli_real_escape_string($conn_database_restaurant, $_POST['group']);
        $details = mysqli_real_escape_string($conn_database_restaurant, $_POST['details']);
        $price = ($_POST['price'] > 900) ? mysqli_real_escape_string($conn_database_restaurant, $_POST['price']) : 100000;
        $status = (strlen($_POST['status']) > 3) ? mysqli_real_escape_string($conn_database_restaurant, $_POST['status']) : 'out of stock';
        $delivery_time = ($_POST['delivery_time'] > 0) ? mysqli_real_escape_string($conn_database_restaurant, $_POST['delivery_time']) : 0;
        $thumbnail = (strlen($_POST['thumbnail']) > 0) ? mysqli_real_escape_string($conn_database_restaurant, $_POST['thumbnail']) : 'http://dl.mmmohebi.ir/sampleAssets/sampleThumbnail_96x96.png';

        $discount = 0;
        $order_times = 0;

        $details_array = array_values(array_filter(array_map('trim', explode("+", str_replace(array("\n", "\r"), '', $details)))));
        $details_array_str = json_encode($details_array);

        // check if there is no duplicate
        $flag_duplicate = false;
        $sql_get_duplicates = "SELECT * FROM foods WHERE name='$name';";
        if ($result = mysqli_query($conn_database_restaurant, $sql_get_duplicates)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $flag_duplicate = true;
            }
        }

        $sql_create_new_food = "INSERT INTO 
                                foods(`name` , `group` , `details`, `price` , `status` , `order_times`, `discount`, `delivery_time`, `thumbnail`, `modified_date`)
                                VALUES('$name', '$group', '$details_array_str', '$price', '$status', '$order_times', '$discount', '$delivery_time', '$thumbnail', '$nowTimestamp');";
        if($flag_duplicate){
            exit(json_encode(array('statusCode'=>402)));
        }elseif(mysqli_query($conn_database_restaurant, $sql_create_new_food)){
            exit(json_encode(array('statusCode'=>200)));
        }else{
            exit(json_encode(array('statusCode'=>500)));
        }

    }else{
        exit(json_encode(array('statusCode'=>400)));
    }

}else{
    exit(json_encode(array('statusCode'=>401)));
}