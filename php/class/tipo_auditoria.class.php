<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */
include_once "base_tipo.class.php";

class Ttipo_auditoria extends Tbase_tipo {
    protected $id_tipo_auditoria;
    protected $id_tipo_auditoria_code;

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase_tipo::__construct($clink);

        $this->table= "ttipo_auditorias";
    }

    public function Set($id = null) {
        if (empty($id))
            $id = !empty($this->id_tipo_auditoria) ? $this->id_tipo_auditoria : $this->id;

        Tbase_tipo::Set($id);

        if (is_null($this->error)) {
            $this->id_tipo_auditoria= $this->id;
            $this->id_auditoria_code= $this->id_code;
        }
        return $this->error;
    }

    public function add() {
        Tbase_tipo::add();

        if (is_null($this->error)) {
            $this->id_tipo_auditoria= $this->id;
            $this->id_tipo_auditoria_code = $this->id_code;
        }
        return $this->error;
    }

    public function update() {
        $this->id= $this->id_tipo_auditoria;
        Tbase_tipo::update();
        return $this->error;
    }

    public function listar() {
        $result = Tbase_tipo::listar();
        return $result;
    }

    public function eliminar($radio_date = null) {
        $this->id= $this->id_tipo_auditoria;
        $result = Tbase_tipo::eliminar();
        return $this->error;
    }
    
    public function get_from_other_entity($id_origen, $id_entity_origen, $id_entity_target) {
        $sql= "select t2.* from ttipo_auditorias as t1, ttipo_auditorias as t2 ";
        $sql.= "where (lower(t1.nombre) = lower(t2.nombre) and t1.id = $id_origen) ";
        $sql.= "and (t1.id_proceso = $id_entity_origen and t2.id_proceso = $id_entity_target) ";
        
        $result = $this->do_sql_show_error("get_from_other_entity", $sql);
        $row= $this->clink->fetch_array($result);
        return !empty($row['id']) ? array($row['id'], $row['id_code']) : null;
    }     
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";