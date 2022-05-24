<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 7/28/15
 * Time: 11:35 a.m.
 */

include_once "proceso.class.php";
include_once "config.class.php";

class Tconfig_synchro extends Tproceso {
    public $manner;
    public $day_prs;
    public $hour_prs;
    public $min_prs;
    public $time_synchro;
    public $mcrypt;
    public $mcrypt_key;

    public $cant_smtp_prs;

    public function __construct($clink= null) {
        Tproceso::__construct($clink);
        $this->clink= $clink;
        $this->cant_smtp_prs= 0;
        $this->className= "Tconfig_synchro";
    }

    public function Set($id) {
        $sql= "select * from _config_synchro where id_proceso = $id ";
        $result= $this->do_sql_show_error('Set', $sql);
        $row= $this->clink->fetch_array($result);

        $this->id_proceso= $id;
        $this->id_proceso_code= !empty($row['id_proceso_code']) ? $row['id_proceso_code'] : get_code_from_table('tprocesos', $id);

        $this->manner= $row['manner'];
        $this->time_synchro= $row['time_synchro'];

        $array= split_time_seconds($this->time_synchro);
        $this->day_prs= $array['d'];
        $this->hour_prs= $array['h'];
        $this->min_prs= $array['i'];
        $this->mcrypt= boolean($array['mcrypt']);
        $this->mcrypt_key= $array['mcrypt_key'];
    }

    public function update() {
        $manner= setZero($this->manner);
        $time_synchro= (!empty($this->day_prs) || !empty($this->hour_prs) || !empty($this->min_prs)) ? $this->day_prs*86400 + $this->hour_prs*3600 + $this->min_prs*60 : NULL;
        $time_synchro= setNULL_empty($time_synchro);
        $mcrypt_key= setNULL_empty($this->mcrypt_key);

        $sql= "update _config_synchro set manner= $manner, time_synchro= $time_synchro, mcrypt_key= $mcrypt_key, mcrypt=". boolean2pg($this->mcrypt)." ";
        $sql.= "where id_proceso = $this->id_proceso";
        $this->do_sql_show_error('update', $sql);
        $cant= $this->clink->affected_rows();

        if (empty($cant)) {
            $sql= "insert into _config_synchro (id_proceso, id_proceso_code, manner, time_synchro, mcrypt, mcrypt_key) values (";
            $sql.= "$this->id_proceso, '$this->id_proceso_code', $manner, $time_synchro, ". boolean2pg($this->mcrypt).", $mcrypt_key)";
            $this->do_sql_show_error('update', $sql, false);
        }
    }

    public function listar() {
        if (isset($this->array_procesos)) unset($this->array_procesos);
        $this->array_procesos= null;
        $this->cant_smtp_prs= 0;

        $sql= "select * from _config_synchro";
        $result= $this->do_sql_show_error('listar', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if ($row['manner'] == _SYNCHRO_AUTOMATIC_EMAIL)
                ++$this->cant_smtp_prs;

            $array= split_time_seconds($row['time_synchro']);
            $day= $array['d'];
            $hour= $array['h'];
            $min= $array['i'];
            $mcrypt= boolean($row['mcrypt']);

            $array= array('id'=>$row['id_proceso'], 'id_code'=>$row['id_proceso_code'], 'manner'=>$row['manner'],
                    'time_synchro'=>$row['time_synchro'], 'd'=>$day, 'h'=>$hour, 'i'=>$min,
                    'mcrypt'=>$mcrypt, 'key'=>$row['mcrypt_key']);

            $this->array_procesos[$row['id_proceso']]= $array;
        }
    }

    public function set_conectado() {
        $sql= "update _config_synchro, tprocesos set _config_synchro.conectado= tprocesos.conectado ";
        $sql.= "where _config_synchro.id_proceso = tprocesos.id";
        $this->do_sql_show_error('set_conectado', $sql, false);
    }

    public function post() {
        $cant_prs= $_POST['cant_prs'];
        for ($i= 1; $i <= $cant_prs; $i++) {
            $this->id_proceso= $_POST['tab_prs'.$i];

            $this->manner= $_POST['select_manner'.$i];
            if (is_null($this->manner))
                continue;

            $this->id_proceso_code= get_code_from_table('tprocesos', $this->id_proceso);
            $this->day_prs= $_POST['day_prs'.$i];
            $this->hour_prs= $_POST['hour_prs'.$i];
            $this->min_prs= $_POST['min_prs'.$i];
            $this->mcrypt= !empty($_POST['mcrypt'.$i]) ? 1 : 0;
            // $this->mcrypt_key= $_POST['mcrypt_key'];

            $this->update();
            $this->set_conectado();
        }

        $config= new _Tconfig($this->clink);

        $config->type_synchro= $_POST['type_synchro'];
        $config->time_synchro= $_POST['time_synchro'];
        $config->timesynchro= $_POST['timesynchro'];

        $config->save_param(true);
    }
}