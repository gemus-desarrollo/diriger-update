<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 9/2/2015
 * Time: 5:46 p.m.
 */

$_SESSION['_DB_SYSTEM'] = !empty($_SESSION['_DB_SYSTEM']) ? $_SESSION['_DB_SYSTEM'] : "mysql";
class DBServer {
    public $dblink;
    private $db_system;

    public $clink;
    protected $query;
    public $database;
    public $host;
    public $ifnewlink;
    public $affected_rows;
    public $client_info;
    public $client_version;
    public $connect_errno;
    public $connect_error;
    public $errno;
    public $error;
    public $field_count;
    public $host_info;
    public $protocol_version;
    public $server_info;
    public $server_version;
    public $info;
    public $insert_id;
    public $sqlstate;
    public $thread_id;
    public $warning_count;
    public $result;

    private $username;
    private $password;

    public function __construct($host, $database= null, $username, $password, $ifnewlink = false) {
        $this->db_system= defined("_DB_SYSTEM") ? _DB_SYSTEM : null;
        if (is_null($this->db_system))
            $this->db_system= !is_null($_SESSION['_DB_SYSTEM']) ? $_SESSION['_DB_SYSTEM'] : "mysql";
        $this->connectLink($host, $database, $username, $password, $ifnewlink);

        if ($this->dblink) {
            $this->host= $host;
            $this->database= $database;
            $this->ifnewlink= $ifnewlink;

            $this->username= $username;
            $this->password= $password;
        }

        $this->dblink= !is_null($this->error) ? false : $this->dblink;
        $this->clink= $this->dblink;
    }

    private function connectLink($host, $database= null, $username, $password, $ifnewlink = false) {
        $host= !empty($host) ? $host : $this->host;
        $database= !empty($database) ? $database : $this->database;
        $username= !empty($username) ? $username : $this->username;
        $password= !empty($password) ? $password : $this->password;

        if ($this->db_system == 'mysql') {
            $this->dblink = new mysqli($host, $username, $password, $database);
            if ($this->dblink->connect_error) {
                $this->dblink = new mysqli("127.0.0.1", $username, $password, $database);
            }
            $this->error= mysqli_connect_errno() ? mysqli_connect_error() : null;
            $this->dblink= is_null($this->error) ? $this->dblink : false;

        } else {
            $connection_string= "host=$host dbname=$database user=$username password=$password";
            $this->dblink= !$ifnewlink ? pg_connect($connection_string) : pg_connect($connection_string, PGSQL_CONNECT_FORCE_NEW);

            if (!$this->dblink) {
                $connection_string= "host=127.0.0.1 dbname=$database user=$username password=$password";
                $this->dblink= !$ifnewlink ? pg_connect($connection_string) : pg_connect($connection_string, PGSQL_CONNECT_FORCE_NEW);
            }

            $this->error= empty($this->dblink) ? 'connection failed' : null;
        }
    }

    public function RefreshLink() {
        if (!$this->clink->ping())
            $this->connectLink(null, null, null, null, false);
    }

    public function select_db($dbname) {
        return $this->dblink->select_db($dbname);
    }

    public function close() {
        $result = ($this->db_system == 'mysql') ? $this->dblink->close() : pg_close($this->dblink);
        return $result;
    }

    public function commit() {
        $result = ($this->db_system == 'mysql') ? $this->dblink->commit() : pg_commit($this->dblink);
        return $result;
    }

    public function query($sql) {
        $this->query = $sql;
        $this->result = ($this->db_system == 'mysql') ? @$this->dblink->query($sql) : @pg_query($this->dblink, $sql);
        return $this->result;
    }

    public function multi_query($sql) {
        $this->query = $sql;
        $this->result = ($this->db_system == 'mysql') ? @$this->dblink->multi_query($sql) : @pg_query($this->dblink, $sql);
        if ($this->db_system == 'mysql') {
            $this->error= $this->dblink->error;
            if ($this->result) {
                while (@$this->dblink->next_result()) {
                    if ($l_result = $this->dblink->store_result()) {
                        $l_result->free();
        }   }   }   }
        return $this->result;
    }

    public function UsedDatabase() {
        $sql= "select database()";
        $result= $this->query($sql);
        $row= $this->fetch_array($result);
        return $row[0];
    }

    public function error() {
        $this->error= null;
        $this->error = ($this->db_system == 'mysql') ? $this->dblink->error : pg_last_error($this->dblink);
        if (empty($this->error))
            $this->error= null;
        return $this->error;
    }

    public function errno() {
        $this->errno = ($this->db_system == 'mysql') ? $this->dblink->errno : null;
        return $this->errno;
    }

    public function fetch_assoc($result = null) {
        if (is_null($result) || empty($result))
            return null;
        if ($this->db_system == 'mysql' && !is_object($result))
            return null;

        $row = ($this->db_system == 'mysql') ? $result->fetch_assoc(): pg_fetch_assoc($result);
        return $row;
    }

    public function fetch_array($result, $resulttype = MYSQLI_BOTH) {
        if (is_null($result) || empty($result))
            return null;
        if ($this->db_system == 'mysql' && !is_object($result))
            return null;
        $resulttype= (int)$resulttype;

        if (($this->db_system == 'mysql')) {
            $row= $result->fetch_array((int)$resulttype);
        } else {
            if ($resulttype == MYSQLI_BOTH)
                $resulttype= PGSQL_BOTH;
            if ($resulttype == MYSQLI_NUM)
                $resulttype= PGSQL_NUM;
            if ($resulttype == MYSQLI_ASSOC)
                $resulttype= PGSQL_ASSOC;

            $row = pg_fetch_array($result, NULL, (int)$resulttype);
        }

        return $row;
    }

    public function fetch_row($result) {
        if (is_null($result) || empty($result))
            return null;
        if ($this->db_system == 'mysql' && !is_object($result))
            return null;

        $row = ($this->db_system == 'mysql') ? $result->fetch_row() : pg_fetch_array($result);
        return $row;
    }

    public function data_seek($resultlink, $pos = 0) {
        if (is_null($pos))
            $pos = 0;
        if ($this->db_system == 'mysql' && !is_object($resultlink))
            return null;

        $result = ($this->db_system == 'mysql') ? $resultlink->data_seek($pos) : pg_result_seek($resultlink, $pos);
        return $result;
    }

    public function queryAsArray($sql) {
        $this->result= $this->query($sql);

        $array= array();
        if ($this->db_system == 'mysql')
            while ($row = mysqli_fetch_array($this->result)) $array[] = $row;
        else
            $array = pg_fetch_all($this->result);

        return $array;
    }

    public function toArray($result= null) {
        if (empty($result))
            $result= $this->result;

        $array= array();
        if ($this->db_system == 'mysql')
            while ($row = mysqli_fetch_array($result, MYSQLI_NUM))
                $array[] = $row;
        else
            while ($row = pg_fetch_array($result, NULL, PGSQL_NUM ))
                $array[] = $row;

        $this->data_seek($result);
        return $array;
    }

    public function field_name($resultlink = null, $pos = 0) {
        if (is_null($pos))
            $pos = 0;
        if (is_null($resultlink))
            $resultlink = $this->result;

        if (is_null($resultlink) || empty($resultlink))
            return null;
        if ($this->db_system == 'mysql' && !is_object($resultlink))
            return null;

        if ($this->db_system == 'mysql') {
            $resultlink->field_seek($pos);
            $finfo = $resultlink->fetch_field();
            $result= $finfo->name;

            $resultlink->data_seek(0);
        } else {
            $result= pg_field_name($resultlink, $pos);
        }
        return $result;
    }

    public function field_exists($table, $field) {
        if ($this->db_system == 'mysql') {
            $sql= "SHOW COLUMNS FROM $table LIKE '$field'";
            $result= $this->query($sql);
            if ($this->num_rows($result) == 1)
                return true;
        } else {
            $sql= "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table' AND COLUMN_NAME = '$field'";
            $result= $this->query($sql);
            if ($this->num_rows($result) == 1)
                return true;
        }
        return false;
    }

    public function fetch_result($resultlink, $nrow = 0, $field= 0) {
        if (is_null($field))
            $field = 0;
        if (is_null($nrow))
            $nrow = 0;

        if (is_null($resultlink) || empty($resultlink))
            return null;
        if ($this->db_system == 'mysql' && !is_object($resultlink))
            return null;

        if ($this->db_system == 'mysql') {
            $resultlink->data_seek($nrow);
            $finfo = !is_string($field) ? $resultlink->fetch_row() : $resultlink->fetch_assoc();
         //   $finfo = $resultlink->fetch_fields();
            $result= $finfo[$field];

            $resultlink->data_seek(0);
        } else {
            $result= pg_fetch_result($resultlink, $nrow, $field);
        }
        return $result;
    }

    function inserted_id($table= null) {
        $this->insert_id = ($this->db_system == 'mysql') ? $this->dblink->insert_id : $this->pg_inserted_id($this->dblink, $table);
        return $this->insert_id;
    }

    private function pg_inserted_id($dblink, $table= null) {
        if (is_null($table)) {
            $query= strtolower($this->query);
            $ipos = stripos($query, "(") - 2 - stripos($query, "into") - 4;
            $table = substr($query, stripos($query, "into") + 5, $ipos);
        }

        $sequence = trim($table) . '_id_seq';
        $select = "SELECT currval('$sequence')";
        $result = pg_query($this->dblink, $select);
        $id = pg_fetch_array($result, null, PGSQL_NUM);

        return $id[0];
    }

    public function num_rows($result) {
        if (is_null($result) || empty($result))
            return null;
        if ($this->db_system == 'mysql' && !is_object($result))
            return null;

        $num_rows = ($this->db_system == 'mysql') ? $result->num_rows : pg_num_rows($result);
        return $num_rows;
    }

    public function num_fields($result = null) {
        if (is_null($result))
            $result = $this->result;
        if (is_null($result) || empty($result))
            return null;
        if ($this->db_system == 'mysql' && !is_object($result))
            return null;

        $num_fields = ($this->db_system == 'mysql') ? $result->field_count : pg_num_fields($result);
        return $num_fields;
    }

    public function affected_rows($result = null) {
        $this->affected_rows= null;
        if (is_null($result))
            $result = $this->result;
        if ((is_null($result) || (empty($result)) && $this->db_system != 'mysql'))
            return null;

        $this->affected_rows= ($this->db_system == 'mysql') ? $this->dblink->affected_rows : pg_affected_rows($result);
        return $this->affected_rows;
    }

    public function free_result($result = null) {
        if (is_null($result))
            $result= $this->result;
        if ($this->db_system == 'mysql' && !is_object($result)) {
            $this->result = null;
            return null;
        }

        $bool = ($this->db_system == 'mysql') ? @mysqli_free_result($result) : @pg_free_result($result);
        $this->result= null;
        return $bool;
    }

    public function store_result($result) {
        if (is_null($result))
            $result= $this->result;
        if ($this->db_system == 'mysql' && !is_object($result)) {
            $this->result = null;
            return null;
        }

        $bool = ($this->db_system == 'mysql') ? @$this->dblink->store_result() : true;
        return $bool;
    }

    public function fields($table, $type_without_length= true) {
        $sql= null;
        $rows= null;

        if ($this->db_system == 'mysql') {
            $sql= "show fields from ".stringMYSQL($_SESSION['db_name']).".".stringMYSQL($table);
        } else {
            $sql= "SELECT DISTINCT c.ordinal_position AS position, c.column_name AS name, c.data_type AS type, tm.key as key, ";
            $sql.= "c.is_nullable AS null, c.column_default AS default, character_maximum_length AS char_length, character_octet_length AS oct_length ";
            $sql.= "FROM information_schema.columns as c, ";

            $sql.= "(SELECT a.attnum as position, a.attname as column_name, ";
            $sql.= "	CASE ";
            $sql.= "    WHEN cc.contype='p' THEN 'PRI' ";
            $sql.= "    WHEN cc.contype='u' THEN 'UNI' ";
            $sql.= "    WHEN cc.contype='f' THEN 'FK' ";
            $sql.= "    ELSE '' END AS key ";

            $sql.= "FROM pg_catalog.pg_attribute a ";
            $sql.= "LEFT JOIN pg_catalog.pg_class c ON c.oid = a.attrelid ";
            $sql.= "LEFT JOIN pg_catalog.pg_constraint cc ON cc.conrelid = c.oid AND cc.conkey[1] = a.attnum ";
            $sql.= "WHERE c.relname = '$table' AND a.attnum > 0) as tm ";
            $sql.= "WHERE table_name ='$table' and tm.position = c.ordinal_position ";
            $sql.= "ORDER BY position ";
        }
        $result= $this->query($sql);
        $i= 0;

        $length= null;
        $type= null;
        $type_name= null;

        while ($row= $this->fetch_array($result)) {
            if ($this->db_system == 'mysql') {
                $mysql= $row['Type'];

                $ip= strpos($mysql, "(");
                $jp= strpos($mysql, ")");
                if ($ip !== false) {
                    $type_name= substr($mysql,0,$ip);
                    $type= !$type_without_length ? $type_name : $mysql;
                    $length = substr($mysql, $ip + 1, $jp - $ip - 1);
                } else {
                    $length= NULL;
                    $type= $mysql;
                    $type_name= $type;
                }
            }

            $rows[$i]['Field']= $this->db_system == 'mysql' ? $row['Field'] : $row['name'];
            $rows[$i]['Type']= $this->db_system == 'mysql' ? $type : $row['type'];
            $rows[$i]['type_name']= $type_name;
            $rows[$i]['Null']= $this->db_system == 'mysql' ? $row['Null'] : $row['null'];
            $rows[$i]['Default']= $this->db_system == 'mysql' ? $row['Default'] : $row['default'];
            $rows[$i]['Key']= $this->db_system == 'mysql' ? $row['Key'] : $row['key'];
            $rows[$i]['char_length']= $this->db_system == 'mysql' ? $length : $row['char_length'];
            ++$i;
        }

        return $rows;
    }

    public function tables() {
        if ($this->db_system == 'mysql') {
            $sql= "show tables";
        } else {
            $sql= "SELECT DISTINCT table_name FROM information_schema.tables where table_schema = 'public' order by table_name asc";
        }
        $result= $this->query($sql);
        return $result;
    }

    public function if_table_exist($table) {
        if ($this->db_system == 'mysql') {
            $sql= "show create table ".stringMYSQL($table);
        } else {
            $sql= "SELECT * FROM information_schema.columns WHERE table_name = '$table'; ";
        }
        $result= $this->query($sql);
        return $this->num_rows($result) > 0 ? true : false;
    }

    public function table_size($table) {
       $sql= "select count(*) from $table";
       $result= $this->query($sql);
       $row= $this->fetch_array($result);
       return !empty($row[0]) ? (int)$row[0] : 0;
    }
}

// librerias de funciones independientes
function isStringType($type, $strict= false) {
    global $PG_texttypes;
    global $PG_timetypes;

    $isString= false;

    $i= strpos($type, "(");
    $_type= $i !== false ? substr($type,0,$i) : $type;
    if (array_search($_type,$PG_texttypes) !== false)
        $isString= true;
    if ($strict && array_search($_type,$PG_timetypes) !== false)
        $isString= true;
    return $isString;
}

function stringMYSQL($table){
    return '`' . $table . '`';
}

function stringPG($table){
    return '"' . $table . '"';
}

function stringSQL($table) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? stringMYSQL($table) : stringPG($table);
}

function field2pg($mysql, $extra= null) {
    if ($_SESSION['_DB_SYSTEM'] == "mysql")
        return $mysql;

    $mysql= fullUpper($mysql);
    $extra= fullUpper($extra);
    $i= strpos($mysql, "(");
    $j= strpos($mysql, ")");
    $length= 0;
    $type= null;

    if ($i !== false) {
        $type= substr($mysql,0,$i);
        $length= substr($mysql,$i+1,$j-$i-1);
    }
    if ($i === false)
        $type= strtoupper ($mysql);

    if ($type == "TINYINT" && $length == 1)
        $type= "BOOLEAN";
    elseif ($type == "TINYINT" && $length <= 4)
        $type= "INT2";
    elseif ($type == "TINYINT" && $length > 4)
        $type= "INT8";

    if ($type == "SMALLINT")
        $type= "INT2";

    if ($type == "MEDIUMINT")
        $type= "INT4";

    if (($type == "INTEGER" || $type == "INT") && $extra == "AUTO_INCREMENT")
        $type= "BIGSERIAL";
    elseif ($type == "MEDIUMINT" && $extra == "AUTO_INCREMENT")
        $type= "SERIAL";
    elseif (($type == "INTEGER" || $type == "INT") && $length >= 11)
        $type= "BIGINT";
    elseif (($type == "INTEGER" || $type == "INT") && $length > 0)
        $type= "INTEGER($length)";

    if ($type == "FLOAT") $type= "REAL";
    if ($type == "DOUBLE") $type= "DOUBLE PRECISION";

    if ($type == "TINYTEXT" || $type == "TEXT" || $type == "LONGTEXT" || $type == "MEDIUMTEXT")
        $type= "TEXT";
    if ($type == "CHAR")
        $type= "CHAR($length)";
    if ($type == "VARCHAR")
        $type= "VARCHAR($length)";

    if ($type == "DATETIME")
        $type= "TIMESTAMP(0) WITHOUT TIME ZONE";
    if ($mysql == "TIME")
        $type= "TIME WITHOUT TIME ZONE";
    if ($type == "DATE")
        $type= "DATE";

    if ($type == "BLOB" || $type == "LONGBLOB")
        $type= "BYTEA";

    return $type;
}

function showFieldSQL($field) {
    $name= $field['Field'];
    $type= strtolower($field['Type']);
    $value= $field['Default'];
    $length= $field['char_length'];

    $content= null;

    if ($_SESSION["_DB_SYSTEM"] == "mysql") {
        if ($field['Null'] == "NO")
            $content= "NOT NULL";
        if ($field['Null'] == "YES" && is_null($value))
            $content= "DEFAULT NULL";

        if (!is_null($value)) {
            if (stripos($type, "char") !== false || stripos($type, "text") !== false)
                $value= "'$value'";
            $content.= " DEFAULT $value";
        }

        return stringMYSQL($name)." $type $content";
    }

    //  PostgreSQL
    if (stripos($value, "nextval(") !== false)
            $value= NULL;
    if ($field['Null'] == 'YES' && is_null($value))
        $content.= "DEFAULT NULL";
    if (!is_null($value))
        $content.= "DEFAULT ".defaultTypePG($value,$type);
    if ($field['Null'] == 'NO' && !is_null($value))
        $content.= " NOT NULL";

    if ($type == "character varying")
        $type= "varchar({$length})";
    elseif ($type == "character")
        $type= "char({$length})";
    else
        $type= !is_null($length) ? "$type({$length})" : $type;

    return stringPG($name)." $type $content";
}

function defaultTypePG($value,$type) {
    $default= "";
    $type= strtolower($type);
    $i= stripos($type, "(");
    if ($i !== false)
        $type= substr($type, 0, $i);

    if ($type == "bigint")
        $default= "::bigint";
    if ($type == "text")
        $default= "::text";
    if ($type == "varchar")
        $default= "::character varying";
    if ($type == "char")
        $default= "::bpchar";
    if ($type == "boolean")
        $default= "::boolean";
    if ($type == "time")
        $default= "::time without time zone";
    if ($type == "datetime")
        $default= "::timestamp without time zone";

    if (is_null($value))
        $value= "NULL";
    elseif (!is_null($value) && strlen($value) == 0)
        $value= "''";
    elseif ($type == "varchar" || $type == "text" || $type == "char")
        $value= "'$value'";
    elseif ($type == "boolean")
        $value= $value ? 'true' : 'false';
    elseif ($type == "time" || $type == "datetime")
        $value= "'$value'";

    return $value.$default;
}

function day2pg($field) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "day($field)" : "date_part('day',$field)";
}

function month2pg($field) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "month($field)" : "date_part('month',$field)";
}

function year2pg($field) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "year($field)" : "date_part('year',$field)";
}

function date2pg($field) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "date($field)" : "date($field)";
}

function time2pg($field) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "time($field)" : "($field-date($field))";
}

function literal2pg($field) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "$field" : "format('%s',$field)";
}

function str_to_date2pg($text) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "str_to_date($text, '%Y-%m-%d')" : "to_date(format('%s',$text), 'YYYY-MM-DD')";
}

function str_to_datetime2pg($text) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "str_to_date($text, '%Y-%m-%d %H:%i:%s')" : "to_timestamp(format('%s',$text), 'YYYY-MM-DD HH24:MI:SS')";
}

function instr2pg($str, $substr) {
    return $_SESSION['_DB_SYSTEM'] == "mysql" ? "instr($str, $substr)" : "strpos($str, $substr)";
}

function boolean2pg($value= null) {
    if (is_null($value))
        return 'null';
    if ($_SESSION['_DB_SYSTEM'] == "mysql")
        return empty($value) ? 0 : 1;
    else
        return empty($value) ? "'0'" : "'1'";
}

function boolean($v) {
    $false= array(' ','0',false,'false','FALSE','f','F','n','N','no','NO','off','OFF');
    return (empty($v) || is_null($v) || array_search($v, $false) !== false) ? 0 : 1;
}

function decodeBlob2pg($data) {
    if (empty($data))
        return 'NULL';
    else {
        return ($_SESSION['_DB_SYSTEM'] == "mysql") ? "'$data'" : "decode('$data','hex')";
    }
}

function encodeBlob2pg($field) {
    return ($_SESSION['_DB_SYSTEM'] == "mysql") ? $field : "encode($field, 'base64')";
}

function convert_to($text, $coding) {
    return ($_SESSION['_DB_SYSTEM'] == "mysql") ? "convert($text using $coding)" : "convert_to($text,'$coding')";
}