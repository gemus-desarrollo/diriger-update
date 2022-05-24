<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */
include_once "base.class.php";
include_once "code.class.php";

class Tunidad extends Tbase {
    protected $id_unidad;
    protected $id_unidad_code;
    protected $decimal;

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase::__construct($clink);

        $this->obj_code= new Tcode($clink);
    }

    public function GetUnidad() {
        return $this->nombre;
    }
    public function SetUnidad($id) {
        $this->nombre = $id;
    }
    public function GetIdUnidad() {
        return $this->id_unidad;
    }
    public function get_id_unidad_code() {
        return $this->id_unidad_code;
    }
    public function SetIdUnidad($id) {
        $this->id_unidad = $id;
    }
    public function set_id_unidad_code($id) {
        $this->id_unidad_code = $id;
    }
    public function GetDecimal() {
        return (int) $this->decimal;
    }
    public function SetDecimal($id) {
        $this->decimal = $id;
    }

    protected function find_numero($table) {
        $sql= "select max(numero) from $table where inicio <= $this->year and fin >= $this->year ";
        $sql.= "and id_proceso = $this->id_proceso ";
        $result= $this->do_sql_show_error("find_numero($table)", $sql);
        $row= $this->clink->fetch_array($result);
        return !empty($row[0]) ? ++$row[0] : 1;
    }

    public function Set($id = null) {
        if (empty($id))
            $id = $this->id_unidad;

        $this->GetUnidadById($id);
        $this->id_unidad= $this->id;
        $this->id_unidad_code= $this->id_code;
    }

    public function GetUnidadById($id) {
        $this->nombre= null;
        
        $sql = "select * from tunidades where id = $id";
        $result = $this->do_sql_show_error('GetUnidadById', $sql);
        $row = $this->clink->fetch_array($result);
        
        if ($row) {
            $this->id= $id;
            $this->id_code= $row['id_code'];
            $this->nombre = stripslashes($row['nombre']);
            $this->descripcion = stripslashes($row['descripcion']);
            $this->decimal = $row['decimales'];

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];            
        }

        return $this->nombre;
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "insert into tunidades (nombre, decimales, descripcion, id_proceso, id_proceso_code, cronos, situs) ";
        $sql.= "values ($nombre, $this->decimal, $descripcion, {$_SESSION['local_proceso_id']}, '{$_SESSION['local_proceso_id_code']}', ";
        $sql.= "'$this->cronos', '$this->location')";
        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id= $this->clink->inserted_id("tunidades");
            $this->id_unidad = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tunidades', 'id', 'id_code');

            $this->id_code = $this->obj_code->get_id_code();
            $this->id_unidad_code = $this->id_code;
        }

        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "update tunidades set nombre= $nombre, descripcion= $descripcion, decimales= $this->decimal, ";
        $sql .= "cronos= '$this->cronos' where id = $this->id_unidad ";
        $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function listar() {
        $sql = "select * from tunidades order by nombre asc";
        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar($radio_date = null) {
        $sql = "delete from tunidades where id= $this->id_unidad";
        $result = $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
    }

    public function formated_value($value) {
        return number_format($value, $this->decimal, '.', ',');
    }
}

?>