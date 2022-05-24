<?php

/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */
include_once "base.class.php";

class Tbase_tipo extends Tbase {
    protected $table;

    protected $empresarial;

    protected $id_capitulo;
    protected $id_capitulo_code;
    protected $id_subcapitulo;
    protected $id_subcapitulo_code;
    protected $subcapitulo0;
    protected $subcapitulo1;
    protected $numero;

    protected $id_tipo_lista;
    protected $id_tipo_lista_code;

    protected $id_lista;
    protected $id_lista_code;

    protected $id_proceso_asigna;
    protected $id_proceso_asigna_code;

    public $array_tipo_eventos;

    protected $capitulo;
    protected $subcapitulo;

    public function GetCapitulo() {
        return $this->capitulo;
    }
    public function SetCapitulo($id) {
        $this->capitulo = $id;
    }
    public function GetSubcapitulo() {
        return $this->subcapitulo;
    }
    public function SetSubcapitulo($id) {
        $this->subcapitulo = $id;
    }
    
    public function SetIdTipo_evento($id) {
        $this->id_tipo_evento= $id;
    }
    public function GetidTipo_evento() {
        return $this->id_tipo_evento;
    }
    public function set_id_tipo_evento_code($id) {
        $this->id_tipo_evento_code= $id;
    }
    public function get_id_tipo_evento_code() {
        return $this->id_tipo_evento_code;
    }
    public function SetIdTipo_auditoria($id) {
        $this->id_tipo_auditoria= $id;
    }
    public function GetidTipo_auditoria() {
        return $this->id_tipo_auditoria;
    }
    public function set_id_tipo_auditoria_code($id) {
        $this->id_tipo_auditoria_code= $id;
    }
    public function get_id_tipo_auditoria_code() {
        return $this->id_tipo_auditoria_code;
    }
    public function SetIfEmpresarial($id) {
        $this->empresarial= $id;
    }
    public function GetIfEmpresarial() {
        return $this->empresarial;
    }
    public function GetIdCapitulo() {
        return $this->id_capitulo;
    }
    public function get_id_capitulo_code() {
        return $this->id_capitulo_code;
    }   
    public function SetIdCapitulo($id) {
        $this->id_capitulo = $id;
    }
    public function set_id_capitulo_code($id) {
        $this->id_capitulo_code = $id;
    }
    public function GetIdSubcapitulo() {
        return $this->id_subcapitulo;
    }
    public function get_id_subcapitulo_code() {
        return $this->id_subcapitulo_code;
    }
    public function SetIdSubcapitulo($id) {
        $this->id_subcapitulo = $id;
    }
    public function set_id_subcapitulo_code($id) {
        $this->id_subcapitulo_code = $id;
    }
    public function GetSubcapitulo0() {
        return $this->subcapitulo0;
    }
    public function SetSubcapitulo0($id) {
        $this->subcapitulo0 = $id;
    }
    public function GetSubcapitulo1() {
        return $this->subcapitulo1;
    }
    public function SetSubcapitulo1($id) {
        $this->subcapitulo1 = $id;
    }
    public function SetNumero($id) {
        $this->numero= $id;
    }
    public function GetNumero() {
        return $this->numero;
    }
    public function SetIdLista($id) {
        $this->id_lista= $id;
    }
    public function GetIdLista() {
        return $this->id_lista;
    }
    public function get_id_lista_code() {
        return $this->id_tipo_lista_code;
    }
    public function set_id_lista_code($id) {
        $this->id_lista_code= $id;
    }
    public function GetIdTipo_lista() {
        return $this->id_tipo_lista;
    }
    public function SetIdTipo_lista($id) {
        $this->id_tipo_lista= $id;
    }
    public function get_id_tipo_lista_code() {
        return $this->id_tipo_lista_code;
    }
    public function set_id_tipo_lista_code($id) {
        $this->id_tipo_lista_code= $id;
    }
    public function get_id_proceso_asigna() {
        return $this->id_proceso_asigna;
    }
    public function set_id_proceso_asigna($id) {
        $this->id_proceso_asigna= $id;
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase::__construct($clink);

        $this->obj_code= new Tcode($clink);
    }

    public function find_numero() {
        $sql= "select max(numero) from $this->table where id_proceso = $this->id_proceso ";
        $result= $this->do_sql_show_error('find_numero ($table)', $sql);
        $row= $this->clink->fetch_array($result);
        return !empty($row[0]) ? ++$row[0] : 1;
    }

    public function Set($id = null) {
        if (empty($id))
            $id = $this->id;

        $sql= "select * from $this->table where id = $id";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->id= $row['id'];
            $this->id_code= $row['id_code'];

            $this->numero= $row['numero'];
            $this->nombre= stripslashes($row['nombre']);
            $this->descripcion= stripslashes($row['descripcion']);

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];

             $this->kronos = $row['cronos'];
        }
        return $this->error;
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "insert into $this->table (numero, nombre, descripcion, id_proceso, id_proceso_code, cronos, situs) values ";
        $sql.= "($this->numero, $nombre, $descripcion, $this->id_proceso, '$this->id_proceso_code', '$this->cronos', '$this->location')";
        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id($this->table);
            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code($this->table, 'id', 'id_code');
            $this->id_code = $this->obj_code->get_id_code();
        }
        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "update $this->table set nombre= $nombre, descripcion= $descripcion, numero= $this->numero, ";
        $sql .= "cronos= '$this->cronos' where id = $this->id ";
        $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function listar() {
        global $array_procesos_entity;

        $id_proceso= null;
        if (!empty($this->id_proceso)) {
            $id_proceso= $array_procesos_entity[$this->id_proceso]['if_entity'] ? $this->id_proceso : $array_procesos_entity[$this->id_proceso]['id_entity'];
        }

        $sql = "select * from $this->table ";
        if (!empty($this->id_proceso))
            $sql.= "where id_proceso = $id_proceso ";
        $sql.= "order by numero, nombre asc";
        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar($radio_date = null) {
        $sql = "delete from $this->table where id= $this->id";
        $result = $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
    }

     public function copy($id_origen, $id_target, $id_target_code) {
        $sql= "select * from $this->table where id_proceso = $id_origen";
        $result = $this->do_sql_show_error("copy --- $this->table", $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->numero= $row['numero'];
            $this->nombre= $row['nombre'];
            $this->descripcion= $row['descripcion'];

            $this->id_proceso= $id_target;
            $this->id_proceso_code= $id_target_code;

            $this->add();
        }
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";

if (!class_exists('Tproceso'))
    include_once "proceso.class.php.class.php";