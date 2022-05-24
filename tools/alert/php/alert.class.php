<?php

/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/7/2017
 * Time: 11:33
 */

defined('_TIME_ALARM') or define('_TIME_ALARM', 15);

if (!class_exists('Tbase_alert'))
    include_once "base_alert.class.php";

class Talert extends Tbase_alert {

    public function __construct($clink) {
        $this->clink= $clink;
        Tbase_alert::__construct($clink);
    }   

    public function listar() {
        $sql= "select * from talerts where id_usuario = $this->id_usuario ";
        $sql.= "and (fecha_inicio_plan >= '$this->fecha_inicio_plan' and fecha_inicio_plan <= '$this->fecha_fin_plan') ";
        $sql.= "order by fecha_inicio_plan asc limit 1";
        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function SetSound($id) {
        $sql= "update talerts set sound = false where id_usuario = $this->id_usuario and id_evento = $id";
        $this->do_sql_show_error('SetSound', $sql);
    }

    public function SetActive($id) {
        $sql= "update talerts set active = false where id_usuario = $this->id_usuario and id_evento = $id";
        $this->do_sql_show_error('SetActive', $sql);
    }
}