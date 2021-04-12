<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['resCode'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $resCode =  mysqli_real_escape_string($connOurs, $_POST['resCode']);

    $resEnglishName = $oursAccess->select("english_name", "restaurants", "`res_code`='$resCode'");

    if(strlen($resEnglishName) > 2){
        exit(json_encode(
            array(
                'statusCode'=>200,
                'data'=>array(
                    'resEnglishName'=> $resEnglishName
                )
            )
        ));
    }else{
        exit(json_encode(array('statusCode'=>404, 'details'=>"restaurant not found")));
    }

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}
