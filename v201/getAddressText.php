<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['lat']) && isset($_POST['lon']) && isset($_POST['token'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";

    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    // is token valid and has access
    if(!($oursAccess->isTokenValid($_POST['token'], "ours_customers"))){
        exit(json_encode(array('statusCode'=>401, "details"=>"token is not valid")));
    }

    $apiKey="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6IjViYWFiYjc2MWUyNjIzZjUxNjJlYjM4OGJjNDI0YjgzY2MyMzBlMGYxOTFmZmQ5YjNkMzU1NTI2NWE4N2UyNDE2YWQ2YmE5YTM2ZjgyMWUxIn0.eyJhdWQiOiIxMTE5MiIsImp0aSI6IjViYWFiYjc2MWUyNjIzZjUxNjJlYjM4OGJjNDI0YjgzY2MyMzBlMGYxOTFmZmQ5YjNkMzU1NTI2NWE4N2UyNDE2YWQ2YmE5YTM2ZjgyMWUxIiwiaWF0IjoxNjEwODg4NDI1LCJuYmYiOjE2MTA4ODg0MjUsImV4cCI6MTYxMzMwNzYyNSwic3ViIjoiIiwic2NvcGVzIjpbImJhc2ljIl19.bbHkFAqQc_uPWAgoFuXeIFOJk3P6qH1S0FGGRNrn9r6jcJ0PTM4qMHo4OLakVXm8gWps39-HHD1Mq5XZ-hWzeInR653aRW_vKbWDJNg3OQjoR0EYjz7BXBjRNFWlJZxCjLHEpVT8bXf3F_c1XxjDkN7Pd40KEPK5TlIZXFQNn43iSCsFVk2e1oWyYl0fWIuxzf168bczzeNTIJsVTrLWVAnB3uAXvGk7ffcLy7kfyptxCXj1Z3dtJxFeWxq13WV3FwLDyqsjJ25J_YqIJWva4JiCAyjLU-WCRBR7oXOoaw4VnMB0osoq4DD5Fmnx7oByRFQcxh8lcmTK8ovkScC9rQ";

    $url = 'https://map.ir/fast-reverse?lat=' . $_POST['lat'] . "&lon=" . $_POST['lon'];

    $requestHandler = curl_init();
    curl_setopt($requestHandler, CURLOPT_URL, $url);
    curl_setopt($requestHandler, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($requestHandler, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'Content-Type: application/json',
        "x-api-key: $apiKey",
    ));

    $result = json_decode(curl_exec($requestHandler), true);
    curl_close($requestHandler);

    exit(json_encode(array(
        'statusCode'=>200,
        'data'=>array(
            "addressCompact"=>$result['address_compact']
        )
    )));

}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}