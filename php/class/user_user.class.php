<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 30/11/18
 * Time: 7:53 a.m.
 */

include_once "base.class.php";

if (!class_exists('Tasistencia'))
    include_once "asistencia.class.php";
if (!class_exists('Tregister_planning'))
    include_once "register_planning.class.php";
if (!class_exists('Tevento'))
    include_once "evento.class.php";


class Tuser_user extends Tbase {
    protected $id_user_source;
    protected $id_user_target;
    public $copy_user;


    public function __construct($clink= null) {
        Tbase::__construct($clink);
        $this->clink= $clink;
    }

    private function search_treg_evento($id, $id_usuario, $year_init, $year_end, $if_teventos= true) {
        $sql= null;
        for ($year= $year_init; $year <= $year_end; $year++) {
            $sql.= $year > $year_init ? "union " : "";
            $sql.= "select * from treg_evento_$year where 1 ";
            $sql.= $if_teventos ? "and id_evento = $id " : "and id_tarea = $id ";
            $sql.= "and id_usuario = $id_usuario ";
        }
        $result= $this->do_sql_show_error('search_treg_evento', $sql);
        return $this->cant > 0 ? true : false;
    }

    private function get_array(&$array= array(), $row, $flag) {
        $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_tarea'=>$row['id_tarea'],
                        'id_tarea_code'=>$row['id_tarea_code'], 'id_auditoria'=>$row['id_auditoria'],
                        'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tematica'=>$row['id_tematica'],
                        'id_tematica_code'=>$row['id_tematica_code'], 'flag_responsable'=>$flag,
                        'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'], 'id_tipo_reunion'=>$row['id_tipo_reunion'],
                        'id_tipo_reunion_code'=>$row['id_tipo_reunion_code'], 'id_evento_accords'=>$row['id_evento_accords'],
                        'id_evento_accords_code'=>$row['id_evento_accords_code'],
                        'year_init'=>$row['year_init'], 'year_end'=>$row['year_end']);
        return $array;
    }

    protected function get_teventos() {
        $sql= "select *, year(fecha_inicio_plan) as year_init, year(fecha_fin_plan) as year_end ";
        $sql.= "from teventos where fecha_inicio_plan >= '$this->fecha'";
        $result= $this->do_sql_show_error('get_eventos', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if ($row['id_responsable'] == $this->id_user_source) {
                ++$i;
                $this->get_array($array, $row, true);
                $this->array_eventos[$row['id']]= $array;

            } else {
                if ($this->search_treg_evento($row['id'], $this->id_user_source, $row['year_init'], $row['year_end'], true)) {
                    ++$i;
                    $this->get_array($array, $row, false);
                    $this->array_eventos[$row['id']]= $array;
        }   }   }
        return $i;
    }

    protected function get_ttareas() {
        $sql= "select *, year(fecha_inicio_plan) as year_init, year(fecha_fin_plan) as year_end ";
        $sql.= "from ttareas where (fecha_inicio_plan >= '$this->fecha' or fecha_fin_plan >= '$this->fecha') ";
        $result= $this->do_sql_show_error('get_tareas', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if ($row['id_responsable'] == $this->id_user_source) {
                ++$i;
                $this->get_array($array, $row, true);
                $this->array_tareas[$row['id']]= $array;

            } else {
                if ($this->search_treg_evento($row['id'], $this->id_user_source, $row['year_init'], $row['year_end'], false)) {
                    ++$i;
                    $this->get_array($array, $row, false);
                    $this->array_tareas[$row['id']]= $array;
        }   }   }
        return $i;
    }

    protected function get_tauditorias() {
        $sql= "select *, year(fecha_inicio_plan) as year_init, year(fecha_fin_plan) as year_end ";
        $sql.= "from tauditorias where fecha_inicio_plan >= '$this->fecha' and id_responsable = $this->id_user_source";
        $result= $this->do_sql_show_error('get_auditorias', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $this->get_array($array, $row, true);
            $this->array_auditorias[$row['id']]= $array;
        }
        return $i;
    }

    private function search_tusuario_eventos($id_tematica, $id_usuario, $year) {
        $sql= "select tusuario_eventos_$year.id_usuario from tusuario_eventos_$year ";
        $sql.= "where id_tematica = $id_tematica and tusuario_eventos_$year.id_usuario = $id_usuario and id_grupo is null ";
        $sql.= "union ";
        $sql.= "select tusuario_grupos.id_usuario from tusuario_eventos_$year, tusuario_grupos ";
        $sql.= "where (tusuario_eventos_$year.id_tematica = $id_tematica and tusuario_eventos_$year.id_usuario is null) ";
        $sql.= "and (tusuario_eventos_$year.id_grupo = tusuario_grupos.id_grupo and tusuario_grupos.id_usuario = $id_usuario) ";
        $result= $this->do_sql_show_error('search_treg_evento', $sql);
        return $this->cant > 0 ? true : false;
    }

    protected function get_ttematicas() {
        $sql= "select *, year(fecha_inicio_plan) as year_init from ttematicas where fecha_inicio_plan >= '$this->fecha' ";
        $result= $this->do_sql_show_error('get_ttematicas', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if ($row['id_responsable'] == $this->id_user_source) {
                ++$i;
                $this->get_array($array, $row, true);
                $this->array_tematicas[$row['id']]= $array;

            } else {
                if (!empty($row['id_evento_accords']))
                    continue;
                if ($this->search_tusuario_eventos($row['id'], $this->id_user_source, $row['year_init'])) {
                    ++$i;
                    $this->get_array($array, $row, false);
                    $this->array_tematicas[$row['id']]= $array;
        }   }   }
        return $i;
    }

    protected function set_responsable($table, $id, $field= 'id') {
        $sql= "update $table set id_responsable = $this->id_user_target ";
        if ($table == 'teventos' || $table == 'ttareas' || $table == 'tauditorias') {
            $sql.= ", id_responsable_2= $this->id_user_source, responsable_2_reg_date = '$this->fecha', ";
            $sql.= "cronos= '$this->cronos', situs= '$this->location' ";
        }
        $sql.= "where id_responsable = $this->id_user_source and $field = $id";

        $result= $this->do_sql_show_error('set_responsable', $sql);
    }

    protected function insert_tusuario_eventos($row, $year_init, $year_end= null) {
        $id_evento= setNULL($row['id']);
        $id_evento_code= setNULL_str($row['id_code']);
        $id_tarea= setNULL($row['id_tarea']);
        $id_tarea_code= setNULL_str($row['id_tarea_code']);
        $id_auditoria= setNULL($row['id_auditoria']);
        $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
        $id_tematica= setNULL($row['id_tematica']);
        $id_tematica_code= setNULL_str($row['id_tematica_code']);

        $year_end= !empty($year_end) ? $year_end : $year_init;
        for ($year= $year_init; $year <= $year_end; $year++) {
            $sql= "insert into tusuario_eventos_$year (id_usuario, id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
            $sql.= "id_auditoria_code, id_tematica, id_tematica_code, cronos, situs) values ($this->id_user_target, $id_evento, ";
            $sql.= "$id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, $id_tematica, $id_tematica_code, ";
            $sql.= "'$this->cronos', '$this->location')";
            $result= $this->do_sql_show_error('insert_usuario', $sql);
        }
    }

    protected function insert_tasistencias($row) {
        $obj_assist= new Tasistencia($this->clink);
        $row_source= $obj_assist->get_asistencias($row['id'], $this->id_user_source);
        $row_target= $obj_assist->get_asistencias($row['id'], $this->id_user_target);

        if (!empty($row_target))
            return false;
        $id_asistencia= current($row_source)['id'];
        if (empty($id_asistencia))
            return false;

        $obj_assist->Set($id_asistencia);
        $obj_assist->SetIdUsuario($this->id_user_target);
        $obj_assist->add();
        return true;
    }

    protected function insert_treg_evento($id_evento, $year) {
        $obj_reg= new Tregister_planning($this->clink);
        $obj_reg->SetYear($year);
        $row_source= $obj_reg->get_last_reg($id_evento, $this->id_user_source);
        $row_target= $obj_reg->get_last_reg($id_evento, $this->id_user_target);

        if (!empty($row_target)) return true;

        if (!empty($row_source)) {
            $obj_reg->setEvento_reg($row_source);
            $obj_reg->SetIdUsuario($this->id_user_target);
            $obj_reg->SetIdEvento($id_evento);
            $obj_reg->set_id_evento_code($row_source['id_evento_code']);

            $obj_reg->add_cump($_SESSION['id_usuario']);
        }

        return false;
    }

    protected function set_indicadores() {
        $sql= "update tindicadores set id_usuario_plan= $this->id_user_target where id_usuario_plan= $this->id_user_source; ";
        $this->do_sql_show_error('set_indicadores', $sql);

        $sql= "update tindicadores set id_usuario_real= $this->id_user_target where id_usuario_real= $this->id_user_source; ";
        $this->do_sql_show_error('set_indicadores', $sql);
    }
}
