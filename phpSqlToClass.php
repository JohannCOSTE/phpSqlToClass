<head>
    <title>phpSqlToClass</title>
</head>

<?php
include_once "utils.php";

if (!isset($_GET["database"]) || !isset($_GET["table"]) || !isset($_GET["output"])) {
    echo "<h1 style='text-align: center; font-family: sans-serif; margin-top: 50px'>phpSqlToClass</h1>";
    echo "<form action='' style='text-align: center'>";

    echo "<div style='margin: 15px'>";
    echo '<label for="database">Database<br>';
    echo "<input type='text' name='database' id='database' required value='".(isset($_POST["database"])?$_POST["database"]:"")."'><br>";
    echo "</div>";

    echo "<div style='margin: 15px'>";
    echo '<label for="table">Table</label><br>';
    echo "<input type='text' name='table' id='table' required/><br>";
    echo "</div>";

    echo "<div style='margin: 15px'>";
    echo '<input type="radio" id="browser" name="output" value="browser" checked>';
    echo '<label for="browser">Show in browser</label><br>';
    echo "</div>";

    echo "<div style='margin: 15px'>";
    echo '<input type="radio" id="file" name="output" value="file">';
    echo '<label for="file">Save in file</label><br>';
    echo "</div>";

    echo "<input type='submit' value='Generate'/>";
    echo "</form>";

} else {
    $table = $_GET["table"];
    $output_type = $_GET["output"];
    $database = $_GET["database"];

    if($output_type == "browser"){
        $RET_LINE = "<br>";
    }
    else{
        $RET_LINE = "\n";
    }

    $arguments_method_only_primary_fields = "";
    $arguments_string_method_only_primary_fields = "";
    $arguments_method_all_fields = "";
    $arguments_method_all_fields_except_primary = "";

    $db_request_all_fields = "";
    $db_request_all_fields_token = "";
    $db_request_update = "";
    $db_request_where_primary_fields = "";

    $db_array_all_files_arguments = "";
    $db_array_all_fields_except_primary_arguments = "";
    $db_array_only_primary_fields_this = "";
    $db_array_only_primary_field_arguments = "";
    $db_wrapper_arguments = "";

    $output = "";

    $columns = getColumnNames($database, $table);
    if(is_null($columns)){
        die("<div style='text-align: center; margin-top: 50px;'>The table $database.$table doesn't exist.<br><br><button onClick='history.go(-1);'>Back</button></div>");
    }

    $classname = ucfirst($table);
    if($output_type == "browser") print_line("<pre>&lt;?php");
    print_line("require_once \"../utils.php\";");

    print_line();
    print_line("class " . $classname);
    print_line("{");
    print_line("public static \$DB;");
    print_line("/* ==================================================== FIELDS ==================================================== */");
    foreach ($columns as $column => $primary_type) {
        print_line("private $" . $column . ";");
    }

    print_line();
    print_line("/* ==================================================== CONSTRUCTOR & CIE ==================================================== */");

    print_line("private function __construct(" . $arguments_method_only_primary_fields . "){");
    print_line("\$this->db = getDatabase();");
    print_line('$req = prepareAndFetch('.$classname.'::$DB, "SELECT * FROM ' . $table . ' WHERE ' . $db_request_where_primary_fields . '", array(' . $db_array_only_primary_field_arguments . '));');
    print_line();
    foreach ($columns as $column => $primary_type) {
        print_line('$this->' . $column . ' = $req["' . $column . '"];');
    }
    print_line("}");
    print_line();

    print_line("public static function wrapper(" . $arguments_string_method_only_primary_fields . "){");
    print_line('$information = prepareAndFetch('.$classname.'::$DB, "SELECT * FROM ' . $table . ' WHERE ' . $db_request_where_primary_fields . '", array(' . $db_array_only_primary_field_arguments . '));');
    print_line('if(!is_array($information)) return null;');
    print_line('return new self(' . $arguments_method_only_primary_fields . ');');
    print_line("}");
    print_line();

    print_line('public static function bddWrapper(array $req){');
    print_line('return self::wrapper(' . $db_wrapper_arguments . ');');
    print_line("}");
    print_line();

    print_line("public static function create(" . $arguments_method_all_fields . "){");
    print_line('$req = "INSERT INTO ' . $table . ' ('. $db_request_all_fields .') VALUES (' . $db_request_all_fields_token . ')";');
    print_line('$tab = array(' . $db_array_all_files_arguments . ');');
    print_line('$execution = prepare('.$classname.'::$DB, $req, $tab);');
    print_line('if(dbAffected($execution)) return self::wrapper('.$arguments_method_only_primary_fields.');');
    print_line('else return null;');
    print_line("}");
    print_line();

    print_line("public function update(" . $arguments_method_all_fields_except_primary . "){");
    print_line('$req = "UPDATE ' . $table . ' SET ' . $db_request_update . ' WHERE ' . $db_request_where_primary_fields . '";');
    print_line('$tab = array(' . $db_array_all_fields_except_primary_arguments .", ".$db_array_only_primary_fields_this . ');');
    print_line('$execution = prepare('.$classname.'::$DB, $req, $tab);');
    print_line('if(dbAffected($execution)){');
    foreach ($columns as $column => $primary_type) {
        if($primary_type[0]) continue;
        print_line('$this->' . $column . ' = $' . $column . ';');
    }
    print_line("return true;");
    print_line("}");
    print_line("return false;");
    print_line("}");
    print_line();

    print_line("public function delete(){");
    print_line('$req = "DELETE FROM ' . $table . ' WHERE ' . $db_request_where_primary_fields . '";');
    print_line('$tab = array(' . $db_array_only_primary_fields_this . ');');
    print_line('$execution = prepare('.$classname.'::$DB, $req, $tab);');
    print_line('return dbAffected($execution);');
    print_line("}");
    print_line();


    print_line("public function __toString(){");
    print_line('$string = "";');
    foreach ($columns as $column => $primary_type) {
        $name_array = explode("_", $column);
        $clean_name_array = array();
        foreach ($name_array as $name) {
            $clean_name_array[] = ucfirst($name);
        }
        if($primary_type[1] == "bool") {
            print_line('$string .= "'.$column.' = " . $this->is'.implode("", $clean_name_array).'()."&lt;br&gt;";');
        }
        else{
            print_line('$string .= "'.$column.' = " . $this->get'.implode("", $clean_name_array).'()."&lt;br&gt;";');
        }
    }
    print_line('return $string;');
    print_line("}");
    print_line();
    print_line("/* ==================================================== GETTERS ==================================================== */");

    foreach ($columns as $column => $primary_type) {
        $name_array = explode("_", $column);
        $clean_name_array = array();
        foreach ($name_array as $name) {
            $clean_name_array[] = ucfirst($name);
        }

        if($primary_type[1] == "date"){
            print_line("public function get" . implode("", $clean_name_array) . "(\$format = 'Y-m-d'){");
            print_line('return formatDate($format, $this->' . $column . ");");
            print_line("}");
            print_line();
        }
        elseif($primary_type[1] == "timestamp"){
            print_line("public function get" . implode("", $clean_name_array) ."(\$format = 'Y-m-d H:i:s'){");
                       print_line('return formatDate($format, $this->' . $column . ", 'Y-m-d H:i:s');");
            print_line("}");
            print_line();
        }
        elseif($primary_type[1] == "bool"){
            print_line("public function is" . implode("", $clean_name_array) . "(){");
            print_line('return $this->' . $column . ";");
            print_line("}");
            print_line();
        }
        else{
            print_line("public function get" . implode("", $clean_name_array) . "(){");
            print_line('return $this->' . $column . ";");
            print_line("}");
            print_line();

        }

    }

    print_line();
    print_line("/* ==================================================== SETTERS ==================================================== */");


    foreach ($columns as $column => $primary_type) {
        if ($primary_type[0]) continue;
        $name_array = explode("_", $column);
        $clean_name_array = array();
        foreach ($name_array as $name) {
            $clean_name_array[] = ucfirst($name);
        }
        print_line("public function set" . implode("", $clean_name_array) . "($" . $column . "){");
        print_line('$req = prepare('.$classname.'::$DB, "UPDATE ' . $table . ' SET ' . $column . ' = :' . $column . ' WHERE ' . $db_request_where_primary_fields . '", array(' . $db_array_only_primary_fields_this . ', "' . $column . '" => $' . $column . '));');
        print_line('$updated = dbAffected($req);');
        print_line('if($updated) $this->' . $column . ' = $' . $column . ';');
        print_line('return $updated;');
        print_line("}");
        print_line();
    }

    print_line();

    $camelCaseClassNameArray = explode("_", $table);
    for($i = 0; $i < count($camelCaseClassNameArray) ; $i++) $camelCaseClassNameArray[$i] = ucfirst(strtolower($camelCaseClassNameArray[$i]));
    $camelCaseClassName = implode("", $camelCaseClassNameArray);
    print_line("/** @return self[] */");
    print_line("public static function getList".$camelCaseClassName."(){");
    print_line('$recherche = prepare('.$classname.'::$DB, "SELECT * FROM ' . $table . '");');
    print_line('$array = array();');
    print_line('while($element = $recherche->fetch()){');
    print_line('$array[]= self::bddWrapper($element);');
    print_line("}");
    print_line('return $array;');
    print_line("}");
    print_line();

    print_line("}");

    if($output_type == "browser") print_line("</pre>");


    if($output_type == "browser"){
        echo $output;
    }
    else{
        $path = "./";
        $filename = $path.$classname.".php";
        $myfile = fopen($filename, "w") or die("Unable to open file!");
        $phpHeader = "<?php\n\n";
        fwrite($myfile, $phpHeader.$output);
        fclose($myfile);
        echo("<div style='text-align: center; margin-top: 50px;'>File $filename generated! <br><br><button onClick='history.go(-1);'>Back</button></div>");
    }
}

function str_contains($string, $needle){
    return strpos($string, $needle) !== false;
}

function getSoftTypeFromSql($sqltype){
    if(str_contains($sqltype, "varchar") || str_contains($sqltype, "text")){
        return "string";
    }
    elseif ((str_contains($sqltype, "date") && !str_contains($sqltype, "time"))){
        return "date";
    }
    elseif (str_contains($sqltype, "tinyint")){
        return "bool";
    }
    elseif (str_contains($sqltype, "int") || str_contains($sqltype, "float")){
        return "int";
    }
    elseif (str_contains($sqltype, "enum")){
        return "enum";
    }
    elseif (str_contains($sqltype, "timestamp") || str_contains($sqltype, "datetime")){
        return "timestamp";
    }
}

function getColumnNames($database, $table)
{
    $pdo = getDatabase();
    $sql = prepare($pdo, "SELECT COLUMN_NAME, COLUMN_KEY, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table", array("database" => $database, "table" => $table));

    if($sql->rowCount() == 0){
        return null;
    }

    $output = [];
    global $arguments_method_only_primary_fields;
    global $arguments_string_method_only_primary_fields;
    global $arguments_method_all_fields;
    global $arguments_method_all_fields_except_primary;

    global $db_request_all_fields;
    global $db_request_all_fields_token;
    global $db_request_update;
    global $db_request_where_primary_fields;

    global $db_array_all_files_arguments;
    global $db_array_all_fields_except_primary_arguments;
    global $db_array_only_primary_fields_this;
    global $db_array_only_primary_field_arguments;
    global $db_wrapper_arguments;

    while ($row = $sql->fetch()) {
        $output[$row['COLUMN_NAME']] = array(($row['COLUMN_KEY'] == "PRI"), getSoftTypeFromSql($row['COLUMN_TYPE']));
        if ($row['COLUMN_KEY'] == "PRI") {
            $arguments_method_only_primary_fields .= "$" . $row['COLUMN_NAME'] . ", ";
            $arguments_string_method_only_primary_fields .= "string $" . $row['COLUMN_NAME'] . ", ";
            $db_request_where_primary_fields .= '' . $row['COLUMN_NAME'] . ' = :' . $row['COLUMN_NAME'] . ' AND ';
            $db_array_only_primary_fields_this .= '"' . $row['COLUMN_NAME'] . '" => $this->' . $row['COLUMN_NAME'] . ', ';
            $db_array_only_primary_field_arguments .= '"' . $row['COLUMN_NAME'] . '" => $' . $row['COLUMN_NAME'] . ', ';
            $db_wrapper_arguments .= '$req["' . $row['COLUMN_NAME'] . '"], ';
        } else {
            $arguments_method_all_fields_except_primary .= "$" . $row['COLUMN_NAME'] . ", ";
            $db_request_update .= '' . $row['COLUMN_NAME'] . ' = :' . $row['COLUMN_NAME'] . ', ';
            $db_array_all_fields_except_primary_arguments .= '"' . $row['COLUMN_NAME'] . '" => $' . $row['COLUMN_NAME'] . ', ';
        }
        $arguments_method_all_fields .= "$" . $row['COLUMN_NAME'] . ", ";
        $db_request_all_fields_token .= ":" . $row['COLUMN_NAME'] . ", ";
        $db_request_all_fields .=  $row['COLUMN_NAME'] . ", ";
        $db_array_all_files_arguments .= '"' . $row['COLUMN_NAME'] . '" => $' . $row['COLUMN_NAME'] . ', ';
    }

    $arguments_method_only_primary_fields = removeLastTwoChar($arguments_method_only_primary_fields);
    $arguments_string_method_only_primary_fields = removeLastTwoChar($arguments_string_method_only_primary_fields);
    $arguments_method_all_fields = removeLastTwoChar($arguments_method_all_fields);
    $arguments_method_all_fields_except_primary = removeLastTwoChar($arguments_method_all_fields_except_primary);
    $db_request_all_fields_token = removeLastTwoChar($db_request_all_fields_token);
    $db_request_all_fields = removeLastTwoChar($db_request_all_fields);
    $db_array_all_files_arguments = removeLastTwoChar($db_array_all_files_arguments);
    $db_request_update = removeLastTwoChar($db_request_update);
    $db_request_where_primary_fields = removeLastNChar($db_request_where_primary_fields, 5);
    $db_array_all_fields_except_primary_arguments = removeLastTwoChar($db_array_all_fields_except_primary_arguments);
    $db_array_only_primary_fields_this = removeLastTwoChar($db_array_only_primary_fields_this);
    $db_array_only_primary_field_arguments = removeLastTwoChar($db_array_only_primary_field_arguments);
    $db_wrapper_arguments = removeLastTwoChar($db_wrapper_arguments);

    return $output;
}

function print_line($line = "")
{
    global $output;
    global $RET_LINE;
    $output .= $line . $RET_LINE;
}

function removeLastNChar($string, $n)
{
   return substr($string, 0, -$n);
}

function removeLastTwoChar($string)
{
    return removeLastNChar($string, 2);
}

