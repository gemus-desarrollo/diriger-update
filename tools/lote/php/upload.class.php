<?php

/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

include_once "bond.class.php";


class Tupload extends Tbond {

    public function __construct($dblink) {
        Tbond::__construct($dblink);
        $this->dblink= $dblink;
    }

// CTUALIZANDO LAS REFERENCIAS EN LAS TABLAS REALES ///////////////////////////////////////////////////////
    public function _i_tprocesos() {
        $this->table= "tprocesos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tprocesos', 'id_entity');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
         */
    }
    public function _i_tusuarios() {
        $this->table= "tusuarios";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_tauditorias() {
        $this->table= "tauditorias";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tauditorias', 'id_auditoria');
        $this->update_real_table($this->table, 'ttipo_auditorias', 'id_tipo_auditoria');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_responsable_2');
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tnotas() {
        $this->table= "tnotas";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tauditorias', 'id_auditoria');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_usuario');
        */
    }
    public function _i_tproyectos() {
        $this->table= "tproyectos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_usuario');
        */
    }
    public function _i_ttareas() {
        $this->table= "ttareas";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tproyectos', 'id_proyecto');
        $this->update_real_table($this->table, 'ttareas', 'id_tarea');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_responsable_2');
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_ttipo_eventos() {
        $this->table= "ttipo_eventos";
        $this->update_real_table($this->table, 'ttipo_eventos', 'id_subcapitulo');
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_ttipo_reuniones() {
        $this->table= "ttipo_reuniones";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_ttipo_auditorias() {
        $this->table= "ttipo_auditorias";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_teventos() {
        $this->table= "teventos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'teventos', 'id_evento');
        $this->update_real_table($this->table, 'teventos', 'id_copyfrom', 'id_copyfrom');
        $this->update_real_table($this->table, 'ttipo_eventos', 'id_tipo_evento');
        $this->update_real_table($this->table, 'ttipo_reuniones', 'id_tipo_reunion');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_responsable_2');
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_ttematicas() {
        $this->table= "ttematicas";
        $this->update_real_table($this->table, 'tasistencias', 'id_asistencia_resp');
        $this->update_real_table($this->table, 'teventos', 'id_evento');
        $this->update_real_table($this->table, 'teventos', 'id_evento_accords');
        $this->update_real_table($this->table, 'ttematicas', 'id_tematica');
        $this->update_real_table($this->table, 'ttematicas', 'id_copyfrom', 'id_copyfrom');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_responsable_eval');
         */
    }
    public function _i_tasistencias() {
        $this->table= "tasistencias";
        $this->update_real_table($this->table, 'teventos', 'id_evento');
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_tdebates() {
        $this->table= "tdebates";
        $this->update_real_table($this->table, 'ttematicas', 'id_tematica');
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tescenarios() {
        $this->table= "tescenarios";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_treg_evento() {
        $this->table= "treg_evento";
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tusuario_eventos() {
        $this->table= "tusuario_eventos";
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tproceso_eventos() {
        $this->table= "tproceso_eventos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'ttipo_eventos', 'id_tipo_evento');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_responsable_aprb');
         */
    }
    public function _i_tusuario_proyectos() {
        $this->table= "tusuario_proyectos";
        $this->update_real_table($this->table, 'tproyectos', 'id_proyecto');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tplanes() {
        $this->table= "tplanes";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
        $this->update_real_usuario($this->table, 'id_responsable');
        $this->update_real_usuario($this->table, 'id_responsable_eval');
        $this->update_real_usuario($this->table, 'id_responsable_auto_eval');
        $this->update_real_usuario($this->table, 'id_responsable_aprb');
         */
    }
    public function _i_treg_plantrab() {
        $this->table= "treg_plantrab";
        $this->update_real_table($this->table, 'tplanes', 'id_plan');
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tpoliticas() {
        $this->table= "tpoliticas";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_treg_politica() {
        $this->table= "treg_politica";
    }
    public function _i_tprogramas() {
        $this->table= "tprogramas";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_treg_programa() {
        $this->table= "treg_programa";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tref_programas() {
        $this->table= "tref_programas";
    }
    public function _i_tobjetivos() {
        $this->table= "tobjetivos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_treg_objetivo() {
        $this->table= "treg_objetivo";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tobjetivo_tareas() {
        $this->table= "tobjetivo_tareas";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_tpolitica_objetivos() {
        $this->table= "tpolitica_objetivos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_ttarea_tarea() {
        $this->table= "tarea_tareas";
        $this->update_real_table($this->table, 'ttareas', 'id_target');
        $this->update_real_table($this->table, 'ttareas', 'id_source');
    }
    public function _i_tperspectivas() {
        $this->table= "tperspectivas";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_treg_perspectiva() {
        $this->table= "treg_perspectiva";
        $this->update_real_table($this->table, 'tperspectivas', 'id_perspectiva');
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tinductores() {
        $this->table= "tinductores";
        $this->update_real_table($this->table, 'tperspectivas', 'id_perspectiva');
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_treg_inductor() {
        $this->table= "treg_inductor";
        $this->update_real_usuario($this->table, 'id_usuario');
    }
    public function _i_tobjetivo_inductores() {
        $this->table= "tobjetivo_inductores";
    }
    public function _i_tinductor_eventos() {
        $this->table= "tinductor_eventos";
        $this->update_real_table($this->table, 'tinductores', 'id_inductor');
        $this->update_real_table($this->table, 'teventos', 'id_evento');
    }
    public function _i_tunidades() {
        $this->table= "tunidades";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_tindicadores() {
        $this->table= "tindicadores";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tunidades', 'id_unidad');
        /*
        $this->update_real_usuario($this->table, 'id_usuario_real');
        $this->update_real_usuario($this->table, 'id_usuario_plan');
         */

        if ($this->if_mysql) {
            $sql= "update tindicadores, _tidx set inicio= inicio_origen ";
        } else {
            $sql= "update tindicadores set inicio= inicio_origen from _tidx ";
        }
        $sql.= "where inicio is null or (inicio is not null and inicio > inicio_origen) ";
        $sql.= "and (".convert_to("tindicadores.id_code", "utf8")." = ".convert_to("_tidx.id_code", "utf8")." and _tidx._table = '_tindicadores') ";
        $this->db_sql_show_error('_i_tindicadores', $sql);

        if ($this->if_mysql) {
            $sql= "update tindicadores, _tidx set fin= fin_origen ";
        } else {
            $sql= "update tindicadores set fin= fin_origen from _tidx ";
        }
        $sql.= "where (fin is null or (fin is not null and fin > fin_origen)) ";
        $sql.= "and (".convert_to("tindicadores.id_code", "utf8")." = ".convert_to("_tidx.id_code", "utf8")." and _tidx._table = '_tindicadores') ";
        $this->db_sql_show_error('_i_tindicadores', $sql);

        // Insertandolos en el tablero integral
        $sql= "select tindicadores.id as _id, tindicadores.id_code as _id_code from tindicadores, _tindicadores ";
        $sql.= "where ".convert_to("tindicadores.id_code", "utf8")." = ".convert_to("_tindicadores.id_code", "utf8")." ";
        $result= $this->db_sql_show_error('_i_tindicadores', $sql);

        while ($row= $this->dblink->fetch_array($result)) {
            $id= $row['_id'];
            $id_code= $row['_id_code'];

            $sql= "insert into tindicador_tableros (id_indicador, id_indicador_code, id_tablero, cronos, situs) ";
            $sql.= "values ($id, '$id_code', 1, '$this->cronos', '$this->origen_code') ";
            $this->db_sql_show_error('_i_tindicadores', $sql);
        }
    }
    public function _i_tproceso_indicadores() {
        $this->table= "tproceso_indicadores";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tindicdores', 'id_indicador');
    }
    public function _i_tref_indicadores() {
        $this->table= "tref_indicadores";
    }
    public function _i_proceso_criterio() {
        $this->table= "tproceso_criterio";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }    
    public function _i_tindicador_criterio() {
        $this->table= "tindicador_criterio";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
    }
    public function _i_treg_plan() {
        $this->table= "treg_plan";
        $this->update_real_table($this->table, 'tindicadores', 'id_indicador');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_treg_real() {
        $this->table= "treg_real";
        $this->update_real_table($this->table, 'tindicadores', 'id_indicador');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tregistro() {
        $this->table= "tregistro";
        $this->update_real_table($this->table, 'tindicadores', 'id_indicador');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
        $this->update_real_usuario($this->table, 'id_usuario_plan');
        $this->update_real_usuario($this->table, 'id_usuario_real');
         */
    }
    public function _i_triesgos() {
        $this->table= "triesgos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_treg_riesgo() {
        $this->table= "treg_riesgo";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_triesgo_tareas() {
        $this->table= "triesgo_tareas";
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tinductor_riesgos() {
        $this->table= "tinductor_riesgos";
    }
    public function _i_tproceso_objetivos() {
        $this->table= "tproceso_objetivos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tobjetivos', 'id_objetivo');
    }
    public function _i_treg_proceso() {
        $this->table= "treg_proceso";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tnota_causas() {
        $this->table= "tnota_causas";
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tproceso_proyectos() {
        $this->table= "tproceso_proyectos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        $this->update_real_table($this->table, 'tprogramas', 'id_programa');
        $this->update_real_table($this->table, 'tproyectos', 'id_proyecto');
    }
    public function _i_tdocumentos() {
        $this->table= "tdocumentos";
        $this->update_real_table($this->table, 'tprocesos', 'id_proceso');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
        $this->update_real_usuario($this->table, 'id_responsable');
         */
    }
    public function _i_tref_documentos() {
        $this->table= "tref_documentos";
        $this->update_real_table($this->table, 'tdocumentos', 'id_documento');
    }
    public function if_valid_documento($name) {
        $sql= "select distinct * from _tdocumentos where ".convert_to("'$name'", "utf8")." = ".convert_to("nombre", "utf8");
        $result= $this->db_sql_show_error('if_valid_documento', $sql);
        $cant= $this->dblink->num_rows($result);

        return $cant;
    }
    public function _i_treg_tarea() {
        $this->table= "treg_tarea";
        $this->update_real_table($this->table, 'ttareas', 'id_tarea');
        $this->update_real_table($this->table, 'tkanban_columns', 'id_kanban_column');
        /*
        $this->update_real_usuario($this->table, 'id_usuario');
         */
    }
    public function _i_tkanban_columns() {
        $this->table= "tkanban_columns";
        $this->update_real_table($this->table, 'tproyectos', 'id_proyecto');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        */
    }
    public function _i_tkanban_column_tareas() {
        $this->table= "tkanban_column_tareas";
        $this->update_real_table($this->table, 'tkanban_columns', 'id_kanban_column');
        $this->update_real_table($this->table, 'ttareas', 'id_tarea');
        /*
        $this->update_real_usuario($this->table, 'id_responsable');
        */
    }    
    public function _i_tdeletes() {
        $this->table= "tdeletes";
    }
}

?>