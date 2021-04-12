<?php
header("Access-Control-Allow-Headers: Authorization, Content-Type");
header("Access-Control-Allow-Origin: *");
header('content-type: application/json; charset=utf-8');

if(isset($_POST['resEnglishName'])&&isset($_POST['table'])){
    include_once "DataAccess/MysqldbAccess.php";
    include_once "DataAccess/db.config.php";


    $connOurs = MysqlConfig::connOurs();
    $oursAccess = new MysqldbAccess($connOurs);

    $englishName =  mysqli_real_escape_string($connOurs, $_POST['resEnglishName']);
    $table =  mysqli_real_escape_string($connOurs, $_POST['table']);
    $customerPhone =  mysqli_real_escape_string($connOurs, $_POST['customerPhone']);

    $connRes = MysqlConfig::connRes($englishName);
    $resAccess = new MysqldbAccess($connRes);

    // get last open paging from this table in past 5 mins
    $lastPage =  $resAccess->select("*", "pager", "`date`>".(time() - 150)." AND `table`='$table'");
    if($lastPage)
        exit(json_encode(array('statusCode'=>401, 'details'=>"there is an open paging in last 5 min")));


    $sql_newPagingParams = array(
        'table'=>$table,
        'customer_phone'=>$customerPhone,
        'date'=>time(),
        'status'=>'open',
        'modified_date'=>time()
    );

    if($resAccess->insert("pager",$sql_newPagingParams)){
        exit(json_encode(array('statusCode'=>200)));
    }else{
        exit(json_encode(array('statusCode'=>500, "details"=>"something went wrong during paging")));
    }
}else{
    exit(json_encode(array('statusCode'=>400, 'details'=>"wrong inputs")));
}
