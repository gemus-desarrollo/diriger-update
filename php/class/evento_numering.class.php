<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */

include_once "base_evento.class.php";
include_once "evento.class.php";
include_once "tipo_evento.class.php";
include_once "plantrab.class.php";


class Tevento_numering extends Tplantrab {
    public $ktotal;
    private $obj_event;
    public $signal;
    protected $id_plan;

    protected $id_proceso,
            $id_proceso_asigna;

    protected $year;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tplantrab::__construct($clink);
    }

    private function init_update_numering() {
        $this->obj_event= new Tevento($this->clink);
        $this->obj_event->if_teventos= $this->if_teventos;
        $this->obj_event->if_treg_evento= $this->if_treg_evento;
        $this->obj_event->if_tidx= false;
        $this->obj_event->create_temporary_treg_evento_table= false;

        $this->obj_event->SetIdProceso($this->id_proceso);
        $this->obj_event->set_id_proceso_asigna($this->id_proceso_asigna);
        $this->obj_event->SetIfNumering($this->if_numering);
        $this->obj_event->SetIfEmpresarial($this->empresarial);
        $this->obj_event->toshow= $this->toshow;
    }

    public function build_numering() {
        if ($this->if_numering == _ENUMERACION_MANUAL)
            return null;

        $this->month= $this->signal == 'anual_plan' ? null : $this->month;
        $this->day= null;

        $this->toshow= $this->signal == 'anual_plan' ? 2 : 1;
        $this->create_temporary_treg_evento_table= $this->signal == 'anual_plan' ? false : true;
        $this->set_create_tmp_table_tidx(false);

        $this->automatic_event_status($this->toshow, false);
        $this->init_update_numering();
        $this->update_numering();

        $this->update_if_numering();
    }

    public function update_numering() {
        if ($this->if_numering == _ENUMERACION_MANUAL)
            return null;

        $obj_tipo1= new Ttipo_evento($this->clink);
        // $obj_tipo1->SetYear($this->year);
        $obj_tipo1->SetIdProceso($this->id_proceso);
        $obj_tipo1->set_id_proceso_asigna($this->id_proceso_asigna);

        $obj_tipo2= new Ttipo_evento($this->clink);
        // $obj_tipo2->SetYear($this->year);
        $obj_tipo2->SetIdProceso($this->id_proceso);
        $obj_tipo2->set_id_proceso_asigna($this->id_proceso_asigna);

        $this->ktotal= 0;
        for ($i= 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) {
            $num_rows= $this->numering($i, 0);

            $result1= $obj_tipo1->listar($i, null);
            while ($row1= $this->clink->fetch_array($result1)) {
                $num_rows1= $this->numering($i, $row1['id']);

                $result2= $obj_tipo2->listar($i, $row1['id']);
                while ($row2= $this->clink->fetch_array($result2)) {
                    $num_rows2= $this->numering($i, $row2['id']);
        }   }   }
    }

    private function numering($empresarial, $id_tipo_evento) {
        if ($this->signal == 'anual_plan')
            $this->obj_event->listyear($empresarial, $id_tipo_evento, false);
        else
            $this->obj_event->listmonth($empresarial, $id_tipo_evento, null);

        $cant= $this->obj_event->GetCantidad();
        if (empty($cant)) return null;

        $j= 0;
        $i= 0;
        $sql= null;

        $this->obj_event->sort_eventos();
        $array_ids= array();
        foreach ($this->obj_event->array_eventos as $evento) {
            if ($array_ids[$evento['id']]) continue;
            $array_ids[$evento['id']]= 1;

            ++$j;
            ++$this->ktotal;
            $k= $this->if_numering == _ENUMERACION_CONTINUA ? $this->ktotal : $j;

            if ($this->id_proceso == $_SESSION['local_proceso_id'] && $this->signal == "anual_plan") {
                $sql.= "update teventos set numero= $k ";
            } else {
                $sql.= "update _teventos set numero_tmp= $k ";
            }
            if ($this->signal == 'anual_plan')
                $sql.= "where id = {$evento['id']} or id_evento = {$evento['id']}; ";
            else
                $sql.= "where id = {$evento['id']}; ";

            ++$i;
            if ($i >= 500) {
                $this->do_multi_sql_show_error('numering', $sql);
                $sql= null;
                $i= 0;
            }
        }

        if (!empty($sql)) {
            $this->do_multi_sql_show_error('numering', $sql);
        }

        return is_null($this->error) ? $j : $this->error;
    }

    public function update_if_numering($if_numering= null) {
        $if_numering= !empty($if_numering) ? $if_numering : $this->if_numering;
        $this->if_numering= $if_numering;
        $if_numering= setNULL_empty($if_numering);

        $sql= "update tplanes set if_numering = $if_numering, cronos= '$this->cronos' where id = $this->id_plan";
        $this->do_sql_show_error('update_enumeracion', $sql);
    }

}
