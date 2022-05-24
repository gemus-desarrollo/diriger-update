<?php
/**
* @author Geraudis Mustelier Portuondo
* @copyright 2019
*/

include_once "base.class.php";

class Ttabla_anno extends Tbase {

    public function __construct($clink, $year= null) {
        $this->clink = $clink;
        Tbase::__construct($clink);

        $this->className = 'Ttabla_anno';
        $this->year= !empty($year) ? $year : date('Y');

        if (!empty($this->year)) {
            if (!$this->clink->if_table_exist("treg_evento_$this->year"))
                $this->create_tables();
            if (!$this->clink->if_table_exist("tusuario_eventos_$this->year"))
                $this->create_tables();
            if (!$this->clink->if_table_exist("tproceso_eventos_$this->year"))
                $this->create_tables();
        }
    }

    public function Set($year) {
        $this->year= !empty($year) ? $year : date('Y');
        
        if (!$this->clink->if_table_exist("treg_evento_$this->year"))
            $this->create_tables();
        if (!$this->clink->if_table_exist("tusuario_eventos_$this->year"))
            $this->create_tables();
        if (!$this->clink->if_table_exist("tproceso_eventos_$this->year"))
            $this->create_tables();
    }

    private function create_tables() {
        $this->treg_evento();
        $this->tusuario_eventos();
        $this->tproceso_eventos();
    }

    private function treg_evento() {
        $_year= !empty($this->year) ? $this->year : date('Y');
        $year= (int)$this->year-1;
        $sql= "show create table treg_evento_$year";
        $result = $this->do_sql_show_error('treg_evento', $sql);
        $sql_table= $this->clink->fetch_array($result)[1];
        
        $sql_table= str_replace("_$year", "_$this->year", $sql_table);
        $this->do_sql_show_error('treg_evento', $sql_table);
    }

    private function tusuario_eventos() {
        $_year= !empty($this->year) ? $this->year : date('Y');
        $year= (int)$this->year-1;
        $sql= "show create table tusuario_eventos_$year";
        $result = $this->do_sql_show_error('tusuario_eventos', $sql);
        $sql_table= $this->clink->fetch_array($result)[1];
        
        $sql_table= str_replace("_$year", "_$this->year", $sql_table);
        $this->do_sql_show_error('tusuario_eventos', $sql_table);
    }

    private function tproceso_eventos() {
        $_year= !empty($this->year) ? $this->year : date('Y');
        $year= (int)$this->year-1;
        $sql= "show create table tproceso_eventos_$year";
        $result = $this->do_sql_show_error('tproceso_eventos', $sql);
        $sql_table= $this->clink->fetch_array($result)[1];
        
        $sql_table= str_replace("_$year", "_$this->year", $sql_table);
        $this->do_sql_show_error('tproceso_eventos', $sql_table);
    }
}
