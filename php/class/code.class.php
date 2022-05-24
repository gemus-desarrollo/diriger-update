<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */
include_once "base.class.php";

function build_code($id = null, $situs = null) {
    if (empty($id))
        $id = $_SESSION['local_proceso_id'];
    if (empty($situs))
        $situs = $_SESSION['location'];

    $code = $situs . str_pad($id, 10, '0', STR_PAD_LEFT);
    return $code;
}

function get_location($id_code) {
    return substr($id_code, 0, 2);
}

function get_code_from_table($table, $id, $uplink = null) {
    global $clink;
    $clink = !is_null($clink) ? $clink : $uplink;

    if (empty($id) || $id == -1)
        return null;
        
    $sql = "select id_code from $table where id = $id ";
    $result = $clink->query($sql);
    $row = $clink->fetch_array($result);
    $id_code = !empty($row['id_code']) ? $row['id_code'] : null;
    return $id_code;
}

function get_id_from_id_code($id_code, $table, $uplink = null) {
    global $clink;
    $clink = !is_null($clink) ? $clink : $uplink;

    $sql = "select id from $table where id_code = '$id_code'";
    $result = $clink->query($sql);
    $id = $clink->fetch_result($result, 0, 0);
    return $id;
}

function get_last_id($table, $id_proceso = null, $uplink = null) {
    global $clink;
    $clink = !is_null($clink) ? $clink : $uplink;

    $sql = "select id from $table ";
    if (!empty($id_proceso))
        $sql .= "where id_proceso = $id_proceso ";
    $sql .= "order by cronos desc limit 1";

    $result = $clink->query($sql);
    $id = $clink->fetch_result($result, 0, 0);
    return !empty($id) ? $id : 0;
}

/**
 * Todo lo referentre a la generaion de los codigos
 */
class Tcode extends Tbase {

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase::__construct($clink);
    }

    public function getCode($id, $situs) {
        $code = !empty($id) ? build_code($id, $situs) : NULL;
        return $code;
    }

    public function set_code($table, $field, $code, $id = null, $situs = null) {
        $id = !empty($id) ? $id : $this->id;
        $this->id_code = $this->getCode($id, $situs);

        $sql= "update `$table` set `$code` = '$this->id_code' where `$field`= $id; ";
        $result= $this->do_sql_show_error('set_code', $sql);
        return $result ? $this->id_code : null;
    }

    public function update_code($table, $field, $code, $id, $id_code) {
        $sql = "update $table set $code = '$id_code' where $field = $id";
        $this->do_sql_show_error('update_code', $sql);
    }

    public function reg_delete($table, $field1, $id1, $field2 = NULL, $id2 = NULL, $field3 = NULL, $id3 = NULL, $multi_query = false) {
        if (empty($table))
            die("ERROR: FUNCTION:reg_delete ===> La variable tabla no puede estar vacia");

        $multi_query = !is_null($multi_query) ? $multi_query : false;

        if (is_null($id2) && !is_null($id3)) {
            $id2= $id3;
            $field2= $field3;
        }
        if (is_null($id1) && !is_null($id2)) {
            $id1= $id2;
            $field1= $field2;
        }

        $field2 = setNULL_str($field2);
        $id2 = setNULL_str($id2);
        $field3 = setNULL_str($field3);
        $id3 = setNULL_str($id3);
        $observacion= setNULL_str($this->observacion);

        $sql = "insert into tdeletes (tabla, campo1, valor1, campo2, valor2, campo3, valor3, observacion, ";
        $sql.= "id_usuario, cronos, situs) values ('$table', '$field1', '$id1', $field2, $id2, $field3, $id3, ";
        $sql.= "$observacion, {$_SESSION['id_usuario']}, '$this->cronos', '$this->location'); ";

        if ($multi_query)
            return $sql;
        $this->do_sql_show_error('reg_delete', $sql);
    }

    public function get_code_from_table($table, $id) {
        $sql = "select id_code from $table where id = $id ";
        $result = $this->do_sql_show_error('get_code_from_table', $sql);
        $row = $this->clink->fetch_array($result);
        $id_code = $row['id_code'];
        return $id_code;
    }

    public function get_proceso_by_id_code($id_code) {
        $code = substr($id_code, 0, 2);
        $array = $this->get_proceso_by_code($code);
        return $array;
    }

    public function get_proceso_by_code($code) {
        $array = null;

        $sql = "select id, id_code, nombre, codigo, tipo from tprocesos where codigo = '$code' ";
        $result = $this->do_sql_show_error('get_proceso_by_code', $sql);
        $row = $this->clink->fetch_array($result);
        if (is_null($row) || empty($this->cant))
            return null;

        $array = array('id' => $row['id'], 'id_code' => $row['id_code'], 'code' => $row['codigo'],
                        'id_proceso' => $row['id_proceso'], 'id_proceso_code' => $row['id_proceso_code'],
                        'nombre' => $row['nombre'], 'tipo' => $row['tipo']);
        return $array;
    }

    public function get_id_from_id_code($id_code, $table) {
        $sql = "select id from $table where id_code = '$id_code'";
        $result = $this->do_sql_show_error('get_id_from_id_code', $sql);
        $id = $this->clink->fetch_result($result, 0, 0);
        return $id;
    }

    public function listar($array_fileds= null, $flag= false) {
        $list= !is_null($array_fileds) ? implode(",", $array_fileds) : null;
        $flag= !is_null($flag) ? $flag : false;

        $sql= "select * from tdeletes ";
        $sql.= "where (".date2pg("cronos")." >= '$this->fecha_inicio_plan' and ".date2pg("cronos")." <= '$this->fecha_fin_plan') ";
        if (!is_null($list))
            $sql.= "and tabla in ($list)";
        $result= $this->do_sql_show_error('listar', $sql);
        if ($flag)
            return $result;

        $array_cells[8]= 'id_responsable';
        $array_cells[9]= 'origen_data';
        $array_cells[10]= 'observacion';
        $array_cells[11]= 'cronos';
        $array_cells[12]= 'cronos_syn';
        $array_cells[13]= 'situs';

        $i= 0;
        $array= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array[$i]['tabla']= $row[1];
            for ($j= 2; $j < 14; $j++) {
                if (array_key_exists($j, $array_cells))
                    $array[$i][$array_cells[$j]]= $row[$j];
                else
                    $array[$i][$row[$j]]= $row[++$j];
            }
            ++$i;
        }

        return $array;
    }
}

?>