<?php
/**
 * User: Geraudis Mustelier
 * Date: 23/01/2021
 * Time: 10:53 a.m.
 */

include_once "base.class.php";

/*
------------------  aciones -------------------
ENTRADA
SALIDA
IMPRIMIR
ADICIONAR
ELIMINAR
MODIFICAR
*/

class Ttraza extends Tbase {

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase::__construct($clink);
    } 
    
    public function add($action, $descripcion= null, $observacion= null) {
        if (empty($action))
            return null;

        $descripcion= setNull_str($descripcion);
        $observacion= setNull_str($observacion);
        
        $sql= "insert into ttrazas (action, id_usuario, id_proceso, descripcion, observacion, cronos) ";
        $sql.= "values ('$action', {$_SESSION['id_usuario']}, $this->id_proceso, $descripcion, $observacion, '$this->cronos')";

        $this->do_sql_show_error("add", $sql);    
    }

    public function listar($action= null, $id_usuario= null) {
        $this->year= !empty($this->year) ? $this->year : date('Y');

        $sql= "select action, tusuarios.nombre as _nombre, tusuarios.noIdentidad as noIdentidad, tprocesos.nombre as _proceso, ";
        $sql.= "ttrazas.descripcion as _descripcion, ttrazas.observacion as _observacion, ttrazas.cronos as _cronos ";
        $sql.= "from ttrazas, tusuarios, tprocesos where YEAR(ttrazas.cronos) = $this->year ";
        $sql.= "and tusuarios.id = ttrazas.id_usuario and tprocesos.id = ttrazas.id_proceso ";
        if (!empty($action))
            $sql.= "and action = '$action' ";
        if (!empty($id_usuario))
            $sql.= "and ttrazas.id_usuario = $id_usuario ";
        if (!empty($this->id_proceso))
            $sql.= "and ttrazas.id_proceso = $this->id_proceso ";
        if (!empty($this->fecha_inicio_plan) && !empty($this->fecha_fin_plan)) 
            $sql.= "and (date(ttrazas.cronos) >= '$this->fecha_inicio_plan' and date(ttrazas.cronos) <= '$this->fecha_fin_plan') ";

        $result= $this->do_sql_show_error("listar", $sql);
        return $result;
    }
}


?>    