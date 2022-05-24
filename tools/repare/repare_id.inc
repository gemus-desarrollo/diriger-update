<?php
/**
 * Created by Visual Studio Code.
 * User: Geraudis Mustelier
 * Date: 14/04/2020
 * Time: 7:16
 */

global $num_tables;
global $array_tables;
global $array_table_list;

$_MAX_IDS= 10000;

function init_tables() {
    global $clink;
    global $num_tables;
    global $array_tables;
    global $array_tables_list;

    $sql= "SET FOREIGN_KEY_CHECKS=0";
    $result= $clink->query($sql);

    $sql= "show tables";
    $result= $clink->query($sql);

    $array_tables= array();
    $num_tables= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$num_tables;
        $array_tables_list[]= $row[0];
        $array_tables[$row[0]]= array('table'=>$row[0], 'Fields'=>null);
    }
}

function execute_rebuild() {
    global $clink;
    global $num_tables;
    global $array_tables;
    global $array_only_table_set_97;

    init_tables();

    $i= 0;
    foreach ($array_tables as $table) {
        ++$i;
        $r= (float)$i/$num_tables;
        $_r= number_format($r*100, 3);
        bar_progressCSS(0, "set_97 .... Procesando tablas $_r%", $r);
        $nums= 0;
        
        if (isset($array_only_table_set_97) && is_array($array_only_table_set_97)) {
            if (array_search($table['table'], $array_only_table_set_97) === false)
                continue;
        }
        
        if ($table['table'] != "tdeletes")
            $nums= rebuild_table($table);
        if ($nums > 0 || $table['table'] == "tdeletes")
            rebuild_code($table);
    }

    $sql= "SET FOREIGN_KEY_CHECKS=1";
    $result= $clink->query($sql);
    bar_progressCSS(0, "set_97... Procesando tablas 100%", 1);
}

function rebuild_table(&$table) {
    global $clink;

    $sql= "describe {$table['table']}";
    $result= $clink->query($sql);

    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        if (stripos($row['Field'],'id_') !== false && stripos($row['Field'], '_code') !== false) {
            ++$i;
            $table['Fields'][]= $row['Field'];
            $sql.= "alter table {$table['table']} change {$row['Field']} {$row['Field']} char(12);";
    }   }
    if ($sql)
        $result= $clink->multi_query($sql);

    return $i;
}

function rebuild_code($table) {
    global $clink;
    global $_MAX_IDS;

    $name= $table['table'];

    if ($name == "tdeletes") {
        rebuild_code_tdeletes();
        return;
    }

    foreach ($table['Fields'] as $field) {
        $sql= "select * from $name";
        $result= $clink->query($sql);

        $i= 0;
        $j= 0;
        $array_ids= array();
        while ($row= $clink->fetch_array($result)) {
            ++$i;
            $array_ids[$row['id']]= array($row[$field], $row['situs']);
            ++$j;
            if ($j >= $_MAX_IDS) {
                _rebuild_code($name, $field, $i, $array_ids);
                $j= 0;
                $array_ids= array();
            }
        }
        if ($j > 0) {
            _rebuild_code($name, $field, $i, $array_ids);
        }
        $clink->free_result($result);
    }
}

function _rebuild_code($table, $field, $num_register, $array_ids) {
    global $clink;

    $i= 0;
    $j= 0;
    foreach ($array_ids as $id => $register) {
        ++$i;
        $id_code= $register[0];
        $situs= $register[1];

        if (strlen($id_code) == 12)
            continue;
        if ($table != "tdeletes" && ($field == "id_code" && strlen($id_code) == 0))
            $id_code= build_code($id, $situs);
        if (empty($id_code))
            continue;

        $new_code= "XX0000000000";
        $new_code= substr_replace($new_code, substr($id_code,0,2), 0,2);
        $new_code= substr_replace($new_code, substr($id_code, strlen($id_code)-8), 4,12);

        $sql.= "update $table set $field = '$new_code' where id = $id; ";

        ++$j;
        if ($j >= 5000) {
            $result= $clink->multi_query($sql);
            $r= (float)$i/$num_register;
            $_r= number_format($r*100, 3);
            bar_progressCSS(1, "set_97... Procesando tabla:$table ==> campo:$field $_r%", $r);
            $sql= null;
            $j= 0;
        }
    }
    if ($sql)
        $result= $clink->multi_query($sql);
        bar_progressCSS(1, "set_97... Procesando tabla:$table ==> campo:$field 100%", 1);
}

function _rebuild_code_tdeletes($index) {
    global $clink;
    global $_MAX_IDS;

    $sql= "select id, valor$index from tdeletes";
    $result= $clink->query($sql);

    $i= 0;
    $j= 0;
    $array_ids= array();
    while ($row= $clink->fetch_array($result)) {
        if (empty($row['valor'.$index]))
            continue;
        if (!haveletter($row['valor'.$index]))
            continue;
        ++$i;
        $array_ids[$row['id']]= array($row['valor'.$index], null);
        ++$j;
        if ($j >= $_MAX_IDS) {
            _rebuild_code("tdeletes", "valor$index", $i, $array_ids);
            $j= 0;
            $array_ids= array();
        }
    }
    if ($j) {
        _rebuild_code("tdeletes", "valor$index", $i, $array_ids);
    }
    $clink->free_result($result);
}

function rebuild_code_tdeletes() {
    _rebuild_code_tdeletes(1);
    _rebuild_code_tdeletes(2);
    _rebuild_code_tdeletes(3);
}

function haveletter($string) {
    for ($i= 0; $i <= strlen($string); $i++) {
        if ((ord($string[$i]) >= 65 && ord($string[$i]) <= 90) || (ord($string[$i]) >= 97 && ord($string[$i]) <= 122))
            return true;
    }
    return false;
}
?>
