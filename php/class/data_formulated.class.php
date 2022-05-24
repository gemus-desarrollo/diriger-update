<?php

/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 11/7/2019
 * Time: 5:28 p.m.
 */

include_once "data.class.php";


class Tdata_formulated extends Tdata {
    public $array_indicadores;

    public function __construct($clink) {
        Tdata::__construct($clink, null);

        $this->clink= $clink;
    }

    private function get_array_indicadores($flag= true) {
        $flag= !is_null($flag) ? $flag : true;

        $obj_ind= new Tindicador($this->clink);
        $obj_ind->SetYear($this->year);
        $obj_ind->SetIdIndicador($this->id_indicador);

        $this->array_indicadores= $obj_ind->get_array_indicadores_from_ref($this->id_indicador, $flag);
    }

    private function set_array_values() {
        $obj= new Tcell($this->clink);
        $obj->SetYear($this->year);
        $obj->SetMonth($this->month);

        foreach ($this->array_indicadores as $id => $array_val) {
            $obj->SetIndicador($id);
            $obj->fix_year= true;
            $obj->fix_interval= true;
            $obj->Set();

            $array= array('alarm'=>$obj->GetAlarm(), 'arrow'=>$obj->GetFlecha(), 'alarm_cumulative'=>$obj->GetAlarm_cumulative(),
                'arrow_cumulative'=>$obj->GetFlecha_cumulative(), 'real'=>$obj->GetReal(),
                'acumulado_real'=>$obj->GetAcumuladoReal(), 'plan'=>$obj->GetPlan(), 'acumulado_plan'=>$obj->GetAcumuladoPlan(),
                'plan_cot'=>$obj->GetPlan_cot(), 'acumulado_plan_cot'=>$obj->GetAcumuladoPlan_cot());

            $this->array_indicadores[$id]['data']= $array;
        }

        $obj->SetIndicador($this->id_indicador);
        $obj->fix_year= true;
        $obj->fix_interval= true;
        $obj->Set();

        $array= array('alarm'=>$obj->GetAlarm(), 'arrow'=>$obj->GetFlecha(), 'alarm_cumulative'=>$obj->GetAlarm_cumulative(),
            'arrow_cumulative'=>$obj->GetFlecha_cumulative(), 'real'=>$obj->GetReal(),
            'acumulado_real'=>$obj->GetAcumuladoReal(), 'plan'=>$obj->GetPlan(), 'acumulado_plan'=>$obj->GetAcumuladoPlan(),
            'plan_cot'=>$obj->GetPlan_cot(), 'acumulado_plan_cot'=>$obj->GetAcumuladoPlan_cot());

        $this->array_indicadores[$this->id_indicador]['data']= $array;
    }

    public function get() {
        $this->get_array_indicadores(false);
    }

    public function get_data() {
        $this->obj_peso= new Tpeso_calculo($this->clink);
        $this->obj_peso->SetYear($this->year);
        $this->obj_peso->SetMonth($this->month);
        $this->obj_peso->SetDay($this->day);
        $this->obj_peso->SetYearMonth($this->year, $this->month);
        $this->obj_peso->set_matrix();

        foreach ($this->array_indicadores as $id => $row) {
            $this->obj_peso->SetIdIndicador($id);
            $cumulative= null;
            $this->array_data[$id]= $this->obj_peso->calcular_indicador($id, true, $cumulative, $this->date_below_cutoff);
        }
        return $this->array_data;
    }
}