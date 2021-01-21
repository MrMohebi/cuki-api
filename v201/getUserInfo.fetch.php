<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['token']) && (strlen($_POST['token'])) > 20){
    include_once 'db/db.config.php';

    $token =  mysqli_real_escape_string($conn_database_ours, $_POST['token']);

    $customerInfo = array();
    $sql_get_customer_info = "SELECT * FROM ours_customers WHERE `token`='$token';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            $customerInfo = $row;
        }
    }

    $phone = $customerInfo['phone'];
    $customerAvailableOffCodes = array();
    $sql_get_customer_info = "SELECT * FROM all_off_codes WHERE `phone`=null OR `phone`='$phone';";
    if ($result = mysqli_query($conn_database_ours, $sql_get_customer_info)) {
        while ($row = mysqli_fetch_assoc($result)) {
            if($row['status'] == 'active')
                array_push($customerAvailableOffCodes, $row);
        }
    }



    if(sizeof($customerInfo) > 3){
        $customerInfo_arranged = array(
            'name'=> $customerInfo['name'],
            'phone'=> $customerInfo['phone'],
            'birthday'=> $customerInfo['birthday'],
            'job'=> $customerInfo['job'],
            'totalBought'=> $customerInfo['amount'],
            'lastLogin'=> $customerInfo['modified_date'],
            'availableOffCodes'=> $customerAvailableOffCodes,
            'userOffCodeHistory'=> json_decode($customerInfo['off_codes']),
            'favoritePlaces' => json_decode($customerInfo['favorite_places']),
        );

        exit(json_encode(array('statusCode'=>200, 'data'=>$customerInfo_arranged)));
    }else{
        exit(json_encode(array('statusCode'=>500)));
    }

}else{
    exit(json_encode(array('statusCode'=>400)));
}