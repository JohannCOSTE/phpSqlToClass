<?php

/**
 * Return true if the number of affected rows by the PDO statement is greater than 0, else otherwise
 * @param PDOStatement $req
 * @return bool
 */
function dbAffected(PDOStatement $req)
{
    return $req->rowCount() > 0;
}

/**
 * Escape HTML characters of the SQL request in order to prevent XSS
 * @param array $array
 * @return array
 */
function xssProtect(array $array)
{
    $res = array();
    foreach ($array as $key => $value) {
        if (is_array($value)){
             die("ERROR: One of the replacement element is an array.");
        }
        if (!is_null($value))
        {
            $res[$key] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
        }
    }
    return $res;
}

/**
 * Replace token in the SQL request in order to debug it easily
 * @param string $sqlRequest
 * @param array $replacements
 * @param bool $html
 * @return string|string[]
 */
function replaceTokenSql(string $sqlRequest, array $replacements = [], bool $html = true)
{
    $replacements = xssProtect($replacements);
    $string = $sqlRequest . " ";
    foreach ($replacements as $token => $value) {
        $string = str_replace(":" . $token . ",", '"' . $value . '",', $string);
        $string = str_replace(":" . $token . ")", '"' . $value . '")', $string);
        $string = str_replace(":" . $token . " ", '"' . $value . '" ', $string);
        $string = str_replace(":" . $token . "\n", '"' . $value . '"\n', $string);
    }
    if($html) return htmlspecialchars($string);
    else return $string;
}

/**
 * Help to debug SQL requests by detecting malformed tokens or array instead of valid token replacement
 * @param string $errcode
 * @param string $sqlRequest
 * @param array $replacements
 */
function debugSqlHelper(string $errcode, string $sqlRequest, array $replacements){
    if($errcode == "HY093") { // Invalid parameter number: number of bound variables does not match number of tokens
        $output_array = array();
        preg_match_all('/[:][a-zA-Z]+[_]?[a-zA-Z]*[_]?[a-zA-Z]*/', replaceTokenSql($sqlRequest, $replacements), $output_array);
        foreach ($output_array[0] as $token) {
            echo "ERROR: Token $token not replaced in $sqlRequest<br>";
        }
        if (count($output_array[0]) == 0) {
            echo "ERROR: There is one extra token in the replacement array of the request $sqlRequest<br>";
        }
    }
}

/**
 * Prepare and execute SQL requests with PDO
 * @param PDO $database
 * @param string $sqlRequest
 * @param array $replacements
 * @return PDOStatement
 */
function prepare(PDO $database, string $sqlRequest, array $replacements = array()): PDOStatement
{
    $stmt = $database->prepare($sqlRequest);
    $stmt->execute(xssProtect($replacements));
    debugSqlHelper($stmt->errorCode(), $sqlRequest, $replacements);
    return $stmt;
}

/**
 * Prepare execute, and fetch SQL requests with PDO
 * @param PDO $database
 * @param string $sqlRequest
 * @param array $remplacements
 */
function prepareAndFetch(PDO $database, string $sqlRequest, array $remplacements = array())
{
    return prepare($database, $sqlRequest, $remplacements)->fetch();
}

/**
 * Help to format date from SQL database with custom format
 * @param string $new
 * @param string $date
 * @param string $current
 * @return false|string
 */
function formatDate(string $new, string $date, string $current = "Y-m-d")
{
    if (strcmp($date, "0000-00-00") == 0) {
        if ($new == "d-m-Y") return "00-00-0000";
        elseif ($new == "d/m/Y") return "00/00/0000";
        elseif ($new == "d/m/y") return "00/00/00";
        elseif ($new == "Y-m-d") return "0000-00-00";
    }
    elseif ($date == "0000-00-00 00:00:00") {
        return "0000-00-00 00:00:00";
    }
    $dateFromFormat = date_create_from_format($current, $date);
    if ($dateFromFormat !== false) return date_format($dateFromFormat, $new);
    else if ($current == "Y-m-d H:i:s") return formatDate($new, $date, "Y-m-d");
    else return "00-00-00";
}

/**
 * Return PDO object of the database, don't forget to set correct connection information
 * @return PDO|void|null
 */
function getDatabase(){
    static $pdo = null;
    $host = null;
    $port = null;
    $username = null;
    $password = null;
    $database = null;

    if(is_null($host) && is_null($port) && is_null($username) && is_null($password) && is_null($database)){
        die("Please set database information in getDatabase() function of utils.php file.");
    }

    if(is_null($pdo)){
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database;charset=utf8", $username, $password);
    }

    return $pdo;
}