<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 8/6/15
 * Time: 7:53 a.m.
 */

if (!class_exists('Ttematica'))
    include_once "tematica.class.php";

class Tdebate extends Ttematica {
    private $id_debate;
    private $id_debate_code;
    private $hora;

    private $tematica;
    private $no_tematica;

    private $id_asistencia;
    private $id_asistencia_code;

    public function SetIdDebate($id) {
        $this->id_debate = $id;
    }
    public function GetIdDebate() {
        return $this->id_debate;
    }
    public function set_id_debate_code($id) {
        $this->id_debate_code = $id;
    }
    public function get_id_debate_code() {
        return $this->id_debate_code;
    }
    public function SetHora($id) {
        $this->hora = $id;
    }
    public function GetHora() {
        return $this->hora;
    }
    public function GetTematica() {
        return $this->tematica;
    }
    public function GetNoTematica() {
        return $this->no_tematica;
    }
    public function SetIdAsistencia($id) {
        $this->id_asistencia = $id;
    }
    public function GetIdAsitencia() {
        return $this->id_asistencia;
    }
    public function set_id_asistencia_code($id) {
        $this->id_asistencia_code = $id;
    }
    public function get_id_asistencia_code() {
        return $this->id_asistencia_code;
    }

    public function __construct($clink= null) {
        Ttematica::__construct($clink);
        $this->clink= $clink;
    }

    public function Set($id= null) {
        $id= !empty($id) ? $id : $this->id_debate;

        $sql= "select tdebates.*, ttematicas.numero as no_tematica, ttematicas.descripcion as tematica ";
        $sql.= "from tdebates, ttematicas where tdebates.id_tematica = ttematicas.id and tdebates.id = $id";
        $result= $this->do_sql_show_error('Set', $sql);

        $row= $this->clink->fetch_array($result);
        $this->id_debate= $row['id'];
        $this->id_code= $row['id_code'];
        $this->id_debate_code= $this->id_code;

        $this->observacion= $row['observacion'];
        $this->id_usuario= $row['id_usuario'];
        $this->origen_data= $row['origen_data'];

        $this->id_tematica= $row['id_tematica'];
        $this->id_tematica_code= $row['id_tematica_code'];

        $this->id_asistencia= $row['id_asistencia'];
        $this->id_asistencia_code= $row['id_asistencia_code'];

        $this->id_proceso= $row['id_proceso'];
        $this->id_proceso_code= $row['id_proceso_code'];

        $this->hora= $row['hora'];

        $this->tematica= $row['tematica'];
        $this->no_tematica= $row['no_tematica'];
    }

    public function add() {
        $observacion= setNULL_str($this->observacion);
        $id_responsable= setNULL($this->id_responsable);

        $sql= "insert into tdebates (observacion, id_responsable, id_asistencia, id_asistencia_code, hora, id_tematica, ";
        $sql.= "id_tematica_code, id_usuario, id_proceso, id_proceso_code, cronos, situs) values ($observacion, $id_responsable, ";
        $sql.= "$this->id_asistencia, '$this->id_asistencia_code', '$this->hora', $this->id_tematica, '$this->id_tematica_code', ";
        $sql.= "$this->id_usuario, $this->id_proceso, '$this->id_proceso_code', '$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
             $this->id= $this->clink->inserted_id("tdebates");
             $this->id_debate= $this->id;

             $this->obj_code->SetId($this->id);
             $this->obj_code->set_code('tdebates','id','id_code');
             $this->id_code= $this->obj_code->get_id_code();
             $this->id_debate_code= $this->id_code;
        } else {
            return $this->error;
        }
    }

    public function update() {
        $observacion= setNULL_str($this->observacion);
        $id_responsable= setNULL($this->id_responsable);

        $sql= "update tdebates set observacion= $observacion, ";
        $sql.= "id_responsable= $id_responsable, hora= '$this->hora', id_usuario= '$this->id_usuario',  ";
        $sql.= "id_asistencia= $this->id_asistencia, id_asistencia_code= '$this->id_asistencia_code' ";

        if (empty($this->id_debate) && (!empty($this->id_proceso) || !empty($this->id_tematica))) {
            $sql.= "where 1 ";
            if (!empty($this->id_tematica))
                $sql.= "and id_tematica = $this->id_tematica ";
            if (!empty($this->id_proceso))
                $sql.= "and id_proceso = $this->id_proceso ";

        } else {
            $sql.= "where id = $this->id_debate ";
        }

        $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function eliminar($id= null) {
        $id= !empty($id) ? $id : $this->id_debate;

        $sql= "delete from tdebates where 1 ";
        if (!empty($id))
            $sql.= "and id = $id ";
        if (!empty($this->id_asistencia))
            $sql.= "and id_asistencia = $this->id_asistencia ";
        if (!empty($this->id_tematica))
            $sql.= "and id_tematica = $this->id_tematica ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";

        $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
    }

    public function listar() {
        $sql= "select distinct tdebates.*, tdebates.id as _id, tdebates.id_code as _id_code, tdebates.observacion as _observacion, ";
        $sql.= "tdebates.id_usuario as _id_usuario, ttematicas.numero as _numero, ttematicas.fecha_inicio_plan as _fecha_inicio_plan, ";
        $sql.= "ttematicas.descripcion as tematica, tdebates.id_tematica as _id_tematica ";
        $sql.= "from tdebates, ttematicas where tdebates.id_tematica = ttematicas.id ";
        if (!empty($this->id_asistencia))
            $sql.= "and tdebates.id_asistencia = $this->id_asistencia ";
        if (!empty($this->id_tematica))
            $sql.= "and tdebates.id_tematica = $this->id_tematica ";
        if (!empty($this->id_evento))
            $sql.= "and ttematicas.id_evento = $this->id_evento ";
        if (!empty($this->id_responsable))
            $sql.= "and tdebates.id_responsable= $this->id_responsable ";
        if (!empty($this->id_proceso))
            $sql.= "and tdebates.id_proceso= $this->id_proceso ";
        $sql.= "order by ttematicas.numero asc, tdebates.hora asc, tdebates.cronos asc";

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";
if (!class_exists('Tasistencia'))
    include_once "asistencia.class.php";