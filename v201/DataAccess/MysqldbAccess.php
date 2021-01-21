<?php

namespace DBs;

class MysqldbAccess{

    private $dbConn;
    function __construct($dbConn){
        $this->dbConn = $dbConn;
    }

    public function isTokenValid ($token, $tableName){
        $sqlCommand = "SELECT * FROM `$tableName` WHERE `token`='$token';";
        $isValid = false;
        if ($result = mysqli_query($this->dbConn, $sqlCommand)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $isValid = true;
            }
        }else{
            return false;
        }
        return $isValid;
    }


    public function noDuplicate($filedArr, $tableName){
        $sqlCommand = "SELECT * FROM `$tableName` WHERE ";
        foreach ($filedArr as $key=>$value){
            $sqlCommand .= " `$key`='$value' OR";
        }
        substr($sqlCommand,0, -2);
        $sqlCommand .= ";";

        $duplicate = false;
        if ($result = mysqli_query($this->dbConn, $sqlCommand)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $duplicate = true;
            }
        }else{
            return null;
        }
        return $duplicate;
    }

    public function select($selector, $tableName, $condition = false , $orderedBy = false ){
        $sqlCommand = "SELECT $selector FROM `$tableName` ";
        if($condition)
            $sqlCommand .= " WHERE $condition ";
        if ($orderedBy)
            $sqlCommand .= " ORDER BY $condition ";
        $sqlCommand .= ";";

        $queryResult = array();
        if ($result = mysqli_query($this->dbConn, $sqlCommand)) {
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($queryResult, $row);
            }
        }else{
            return false;
        }

        return count($queryResult) > 0 ? $queryResult :  false ;
    }

    public function insert($tableName, $keyValObject){
        if(!(count($keyValObject) > 0))
            return false;

        $sqlCommand = "INSERT INTO `$tableName`  ";
        $keys = "(";
        $values = " VALUES ( ";
        foreach ($keyValObject as $key=>$val){
            $keys .= "`$key`,";
            $values .= "'$val',";
        }
        $keys =  rtrim($keys, ", ") . ") ";
        $values = rtrim($values, ", ") . ") ;";

        $sqlCommand .= $keys . $values;

        return mysqli_query($this->dbConn, $sqlCommand) ? true: false;
    }
}




