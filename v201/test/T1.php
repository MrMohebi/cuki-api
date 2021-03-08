<?php

include_once "../DataAccess/MysqldbAccess.php";
include_once "../DataAccess/db.config.php";
$connOurs = MysqlConfig::connOurs();
$oursAccess = new MysqldbAccess($connOurs);

$connRes = MysqlConfig::connRes("cuki");
$resAccess = new MysqldbAccess($connRes);


