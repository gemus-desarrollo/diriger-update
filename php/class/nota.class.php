<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */


if (!class_exists('Triesgo'))
    include_once "riesgo.class.php";

class Tnota extends Triesgo {
    private $show_all_note; // mustra todas las notas no cerradas aunque no correspondan al anno

    private $observacion_ma, $observacion_sst;
    private $if_req_leg, $if_req_proc, $if_req_reg;
    private $requisito, $norma;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Triesgo::__construct($clink);

        $this->if_req_leg= NULL;
        $this->if_req_proc= NULL;
        $this->if_req_reg= NULL;
        $this->show_all_note= false;

        $this->className= "Tnota";
    }

    public function set_show_all_notes($id= true) {
        $this->show_all_note= $id;
    }
    public function get_show_all_note() {
        return $this->show_all_note;
    }
    public function GetObservacion_sst() {
        return $this->observacion_sst;
    }
    public function SetObservacion_sst($id) {
        $this->observacion_sst = $id;
    }
    public function GetObservacion_ma() {
        return $this->observacion_ma;
    }
    public function SetObservacion_ma($id) {
        $this->observacion_ma = $id;
    }
    public function SetIfRequisito_leg($id = 1) {
        $this->if_req_leg = $id;
    }
    public function SetIfRequisito_reg($id = 1) {
        $this->if_req_reg = $id;
    }
    public function SetIfRequisito_proc($id = 1) {
        $this->if_req_proc = $id;
    }
    public function GetIfRequisito_leg() {
        return $this->if_req_leg;
    }
    public function GetIfRequisito_reg() {
        return $this->if_req_reg;
    }
    public function GetIfRequisito_proc() {
        return $this->if_req_proc;
    }
    public function SetRequisito($id) {
        $this->requisito = $id;
    }
    public function GetRequisito() {
        return $this->requisito;
    }
    public function SetNorma($id) {
        $this->norma = $id;
    }
    public function GetNorma() {
        return $this->norma;
    }

    public function Set($id_nota= null) {
        $id_nota= empty($id_nota) ? $this->id_nota : $id_nota;

        $sql= "select * from tnotas where 1 ";
        if (!empty($id_nota))
            $sql.= "and tnotas.id = $id_nota ";
        else {
            if (!empty($this->id_auditoria) && !empty($this->id_requisito)) 
                $sql.= "and id_auditoria = $this->id_auditoria and id_requisito = $this->id_requisito ";
        }    
        $result= $this->do_sql_show_error('Set', $sql);

        if (!$result) 
            return $this->error;

        $row= $this->clink->fetch_array($result);

        $this->id= $row['id'];
        $this->id_nota= $this->id;
        $this->id_code= $row['id_code'];
        $this->id_nota_code= $this->id_code;
        $this->descripcion= stripslashes($row['descripcion']);

        $this->id_usuario= $row['id_usuario'];

        $this->lugar= stripslashes($row['lugar']);
        $this->id_proceso= $row['id_proceso'];
        $this->id_proceso_code= $row['id_proceso_code'];

        $this->id_auditoria= $row['id_auditoria'];
        $this->id_auditoria_code= $row['id_auditoria_code'];

        $this->observacion= stripslashes($row['observacion']);
        $this->observacion_sst= stripslashes($row['observacion_sst']);
        $this->observacion_ma= stripslashes($row['observacion_ma']);

        $this->if_req_leg= $row['if_req_leg'];
        $this->if_req_proc= $row['if_req_proc'];
        $this->if_req_reg= $row['if_req_reg'];

        $this->requisito= $row['requisito'];
        $this->norma= $row['norma'];

        $this->tipo= $row['tipo'];
        $this->origen= $row['origen'];

        $this->fecha_inicio_real= $row['fecha_inicio_real'];
        $this->fecha_fin_plan= $row['fecha_fin_plan'];

        $this->id_lista= $row['id_lista'];
        $this->id_lista_code= $row['id_lista_code'];
        $this->id_requisito= $row['id_requisito'];
        $this->id_requisito_code= $row['id_requisito_code'];

        $this->origen_data= $row['origen_data'];
        $this->kronos= $row['cronos'];

        $row= Tregister_nota::getNota_reg();
        $this->cumplimiento= $row['cumplimiento'];
        $this->estado= $row['estado'];
    }

    public function add() {
        $observacion= setNULL_str($this->observacion);
        $observacion_sst= setNULL_str($this->observacion_sst);
        $observacion_ma= setNULL_str($this->observacion_ma);

        $requisito= setNULL_str($this->requisito);
        $norma= setNULL_str($this->norma);

        $if_req_leg= boolean2pg($this->if_req_leg);
        $if_req_reg= boolean2pg($this->if_req_reg);
        $if_req_proc= boolean2pg($this->if_req_proc);
        $descripcion= setNULL_str($this->descripcion);

        $id_auditoria= setNULL_empty($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);

        $origen= setNULL($this->origen);
        $tipo= setNULL_empty($this->tipo);

        $id_lista= setNULL_empty($this->id_lista);
        $id_lista_code= setNULL_str($this->id_lista_code);
        $id_requisito= setNULL_empty($this->id_requisito);
        $id_requisito_code= setNULL_str($this->id_requisito_code);

        $cumplimiento= setNULL($this->cumplimiento);

        $lugar= setNULL_str($this->lugar);

        $sql= "insert into tnotas (id_auditoria, id_auditoria_code, lugar, descripcion, id_usuario, fecha_inicio_real, ";
        $sql.= "fecha_fin_plan, id_proceso, id_proceso_code, tipo, origen, cumplimiento, observacion_sst, ";
        $sql.= "observacion_ma, if_req_leg, if_req_reg, if_req_proc, requisito, norma, observacion, id_lista, id_lista_code, ";
        $sql.= "id_requisito, id_requisito_code, cronos, situs) values ($id_auditoria, $id_auditoria_code, $lugar, ";
        $sql.= "$descripcion, $this->id_usuario, '$this->fecha_inicio_real', '$this->fecha_fin_plan', $this->id_proceso, ";
        $sql.= "'$this->id_proceso_code', $tipo, $origen, $cumplimiento, $observacion_sst,  ";
        $sql.= "$observacion_ma, $if_req_leg, $if_req_reg, $if_req_proc, $requisito, $norma, $observacion, $id_lista, ";
        $sql.= "$id_lista_code, $id_requisito, $id_requisito_code, '$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('add', $sql);

        if (is_null($this->error)) {
            $this->id_nota= $this->clink->inserted_id("tnotas");
            $this->id= $this->id_nota;

             $this->obj_code->SetId($this->id);
             $this->obj_code->set_code('tnotas', 'id', 'id_code');

             $this->id_code= $this->obj_code->get_id_code();
             $this->id_nota_code= $this->id_code;
        }

    	return $this->error;
    }

    public function update() {
        $observacion= setNULL_str($this->observacion);
        $observacion_sst= setNULL_str($this->observacion_sst);
        $observacion_ma= setNULL_str($this->observacion_ma);

        $requisito= setNULL_str($this->requisito);
        $norma= setNULL_str($this->norma);

        $if_req_leg= boolean2pg($this->if_req_leg);
        $if_req_reg= boolean2pg($this->if_req_reg);
        $if_req_proc= boolean2pg($this->if_req_proc);
        $descripcion= setNULL_str($this->descripcion);

        $cumplimiento= setNULL($this->cumplimiento);

        $id_auditoria= setNULL_empty($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);

        $origen= setNULL($this->origen);
        $tipo= setNULL_empty($this->tipo);

        $id_lista= setNULL_empty($this->id_lista);
        $id_lista_code= setNULL_str($this->id_lista_code);
        $id_requisito= setNULL_empty($this->id_requisito);
        $id_requisito_code= setNULL_str($this->id_requisito_code);

        $sql= "update tnotas set id_auditoria= $id_auditoria, id_auditoria_code= $id_auditoria_code, descripcion= $descripcion, ";
        $sql.= "id_usuario= $this->id_usuario, fecha_inicio_real= '$this->fecha_inicio_real',observacion= $observacion, ";
        $sql.= "fecha_fin_plan= '$this->fecha_fin_plan', id_proceso= $this->id_proceso, id_proceso_code= '$this->id_proceso_code', ";
        $sql.= "lugar= '$this->lugar', tipo= $tipo, origen= $origen, cumplimiento= $cumplimiento, observacion_sst= $observacion_sst, ";
        $sql.= "observacion_ma= $observacion_ma, if_req_leg= $if_req_leg, if_req_reg= $if_req_reg, if_req_proc= $if_req_proc, ";
        $sql.= "requisito= $requisito, norma= $norma, id_lista= $id_lista, id_lista_code= $id_lista_code, ";
        $sql.= "id_requisito= $id_requisito, id_requisito_code= $id_requisito_code, ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' where id = $this->id_nota ";

        $result= $this->do_sql_show_error('update', $sql);
    	return $this->error;
    }

    private function _create_tmp_tnotas() {
        $sql= "drop table if exists ".stringSQL("_tnotas");
        $this->do_sql_show_error('_create_tmp_tnotas', $sql);
// 
        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_tnotas")." ( ";
          $sql.= "id ".field2pg("INTEGER(11)").", ";
          $sql.= "id_code ".field2pg("CHAR(12)").", ";
          $sql.= "id_auditoria ".field2pg("INTEGER(11)").", ";
          $sql.= "id_auditoria_code ".field2pg("CHAR(12)").", ";
          $sql.= "id_lista ".field2pg("INTEGER(11)").", ";
          $sql.= "id_lista_code ".field2pg("CHAR(12)").", ";
          $sql.= "id_requisito ".field2pg("INTEGER(11)").", ";
          $sql.= "id_requisito_code ".field2pg("CHAR(12)").", ";
          $sql.= "lugar ".field2pg("varchar(120)").", ";

          $sql.= "origen_data ".field2pg("text").", ";
          $sql.= "descripcion ".field2pg("longtext").", ";
          $sql.= "tipo ".field2pg("smallint(6)").", ";
          $sql.= "origen ".field2pg("smallint(6)").", ";

          $sql.= "fecha_inicio_real ".field2pg("datetime").", ";
          $sql.= "fecha_fin_plan ".field2pg("datetime").", ";
          $sql.= "id_usuario ".field2pg("INTEGER(11)").", ";

          $sql.= "cronos ".field2pg("datetime").", ";

          $sql.= "id_proceso ".field2pg("INTEGER(11)").", ";
          $sql.= "id_proceso_code ".field2pg("CHAR(12)");
        $sql.= ") ";

        $this->do_sql_show_error('_create_tmp_tnotas', $sql);
    }

    private function create_tmp_tnotas($array_arg, $if_all_year, $user_trea_nota= null) {
        $this->_create_tmp_tnotas();

        $time= new TTime;
        $time->SetYear($this->year);
        $time->SetMonth($this->month);
        $lastday= $time->longmonth();
                
        $fecha_inicio= !$if_all_year ? $this->year.'-'.str_pad($this->month,2,'0',STR_PAD_LEFT).'-01' : "$this->year-01-01";
        $fecha_fin= !$if_all_year ? $this->year.'-'.str_pad($this->month,2,'0',STR_PAD_LEFT).'-'.$lastday : "$this->year-12-31";
        $_fecha_inicio= strtotime($fecha_inicio);
        $_fecha_fin= strtotime($fecha_fin);

        $sql= $this->sql_tnotas_init($array_arg, $user_trea_nota);
        $result= $this->do_sql_show_error('create_tmp_tnotas', $sql);

        $j= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            $continue= false;
            if (strtotime($row['fecha_inicio_real']) >= $_fecha_inicio && strtotime($row['fecha_inicio_real']) <= $_fecha_fin)
                $continue= true;
            if (strtotime($row['fecha_fin_plan']) >= $_fecha_inicio && strtotime($row['fecha_fin_plan']) <= $_fecha_fin)
                $continue= true;
            if ($this->show_all_note && (!$continue && strtotime($row['fecha_inicio_real']) <= $_fecha_inicio)) {
                $array= $this->getNota_reg($row['_id'], false, 'desc', $row['_id_proceso']);
                if ($array['estado'] != _CERRADA)
                    $continue= true;
            }

            if (!$continue)
                continue;

            $id_auditoria= setNULL($row['id_auditoria']);
            $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
            $id_lista= setNULL($row['id_lista']);
            $id_lista_code= setNULL_str($row['id_lista_code']);
            $id_requisito= setNULL($row['id_requisito']);
            $id_requisito_code= setNULL_str($row['id_requisito_code']);

            $lugar= setNULL_str($row['lugar']);
            $origen_data= setNULL_str($row['origen_data']);
            $descripcion= setNULL_str($row['descripcion']);

            $tipo= setNULL($row['tipo']);
            $origen= setNULL($row['origen']);

            $fecha_inicio_real= setNULL_str($row['fecha_inicio_real']);
            $fecha_fin_plan= setNULL_str($row['fecha_fin_plan']);
            $id_usuario= setNULL($row['id_usuario']);

            $sql.= "insert into _tnotas ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "values ({$row['_id']}, '{$row['_id_code']}', $id_auditoria, $id_auditoria_code, $id_lista, $id_lista_code, ";
            $sql.= "$id_requisito, $id_requisito_code, $lugar, $origen_data, $descripcion, $tipo, $origen, $fecha_inicio_real, ";
            $sql.= "$fecha_fin_plan, $id_usuario, '{$row['cronos']}', {$row['_id_proceso']}, '{$row['_id_proceso_code']}'); ";

            ++$j;
            if ($j > 1000) {
                $this->do_multi_sql_show_error('create_tmp_tnotas', $sql);
                $j= 0;
                $sql= null;
            }
        }
        if (!is_null($sql) && is_null($this->error))
            $this->do_multi_sql_show_error('create_tmp_tnotas', $sql);

        $this->if_tnotas= is_null($this->error) ? true : false;
    }

    private function sql_tnotas_init($array_arg, $user_treg_nota= false) {
        $if_noconformidad= $array_arg[0];
        $if_mejora= $array_arg[1];
        $if_observacion= $array_arg[2];
        $list= !empty($array_arg[3]) ? $array_arg[3] : null;
        $user_treg_nota= !is_null($user_treg_nota) ? $user_treg_nota : false;
        $table= $user_treg_nota ? "treg_nota" : "tproceso_riesgos";

        $sql= "select distinct tnotas.*, tnotas.id as _id, tnotas.id_code as _id_code, $table.id_proceso as _id_proceso, ";
        $sql.= "$table.id_proceso_code as _id_proceso_code from tnotas, $table where tnotas.id = $table.id_nota ";
        if (!empty($this->id_usuario))
            $sql.= "and id_usuario = $this->id_usuario ";
        if (!empty($this->tipo))
            $sql.= "and tipo = $this->tipo ";
        if (!empty($this->origen))
            $sql.= "and origen = $this->origen ";
        if (!empty($this->id_auditoria))
            $sql.= "and tnotas.id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_proceso) && empty($list))
            $sql.= "and $table.id_proceso = $this->id_proceso ";
        if (!empty($list)) 
            $sql.= "and $table.id_proceso in ($list) ";

        $plus= null;
        if ($if_noconformidad || $if_mejora || $if_observacion) {
            $sql.= "and (";
            if ($if_noconformidad)
                $plus= "tipo = "._NO_CONFORMIDAD;
            if ($if_mejora)
                $plus.= is_null($plus) ? "tipo = "._OPORTUNIDAD : " or tipo = "._OPORTUNIDAD;
            if ($if_observacion)
                $plus.= is_null($plus) ? "tipo = "._OBSERVACION : " or tipo = "._OBSERVACION;
            $sql.= $plus.") ";
        }

        return $sql;
    }

    public function listar($if_noconformidad= true, $if_mejora= true, $if_observacion= true, 
                                                            $if_all_year= false, $array= null) {
        $if_noconformidad= !is_null($if_noconformidad) ? $if_noconformidad : true;
        $if_mejora= !is_null($if_mejora) ? $if_mejora : true;
        $if_observacion= !is_null($if_observacion) ? $if_observacion : true;
        $if_all_year= !is_null($if_all_year) ? $if_all_year : false;

        if (empty($array) || count($array) == 0)
            $array= null;
        $list= !empty($array) ? implode(",", $array) : null;
        $array_arg= array($if_noconformidad, $if_mejora, $if_observacion, $list);

        $this->create_tmp_tnotas($array_arg, $if_all_year);

        $tnotas= $this->if_tnotas ? "_tnotas" : "tnotas";
        $sql= "select $tnotas.*, $tnotas.id as _id, $tnotas.id_code as _id_code, $tnotas.id_proceso as _id_proceso, ";
        $sql.= "$tnotas.id_proceso_code as _id_proceso_code, $tnotas.lugar as _lugar from $tnotas where 1 ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        /*
        if (!empty($this->id_entity))
            $sql.= "and id_proceso = $this->id_entity ";
        */    
        else {
            if ($list)
                $sql.= "and id_proceso in ($list) ";
        }

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar() {
        $array_tareas= array();
        $sql= "select triesgo_tareas.id_tarea as _id, id_proyecto from triesgo_tareas, ttareas ";
        $sql.= "where triesgo_tareas.id_tarea = ttareas.id and id_nota = $this->id_nota";
        $result= $this->do_sql_show_error('eliminar', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result))
            $array_tareas[$i++]= array('id'=>$row['_id'], 'id_proyecto'=>$row['id_proyeco']);
        
        $sql= "delete from tnotas where id = $this->id_nota ";
        $this->do_sql_show_error('eliminar', $sql);

        if ($i == 0)
            return;

        foreach ($array_tareas as $tarea) {
            if (!empty($tarea['id_proyecto']))
                continue;
            $sql= "select * from triesgo_tareas where id_tarea = {$tarea['id']}";
            $result= $this->do_sql_show_error('eliminar', $sql);

            if (empty($result->num_rows)) {
                $sql= "delete from ttareas where id = {$tarea['id']}";
                $this->do_sql_show_error('eliminar', $sql);
            }
        }
    }

    private function get_array_reg_tareas() {
        $array= $this->get_inicio_fin();
        if (!is_null($array))
            $this->listar_tareas($this->id_nota, null, true, $array[0], $array[1]);
    }

    public function compute_status($id= null) {
        $this->id_nota= !empty($id) ? $id : $this->id_nota;
        $this->setClass($this->id_nota);
        $this->reg_fecha= null;
        $this->get_array_reg_tareas();

        $i= 0;
        $j= 0;
        $max_cronos= null;
        $ncumplimiento= null;
        $n_outdate= null;
        foreach ($this->array_tareas as $id_tarea => $array) {
            ++$i;
            $rowcmp= $this->get_tarea_reg($id_tarea, date('Y', strtotime($array['fecha_inicio'])), date('Y', strtotime($array['fecha_fin'])));
            $ncumplimiento+= $rowcmp[0];
            $n_outdate+= $rowcmp[1];
            $max_cronos= $rowcmp[2];
            
            if ($rowcmp[3]['cumplimiento'] == _COMPLETADO) 
                ++$j;
        }

        $this->estado= _IDENTIFICADO;
        
        if ($j > 0 || $ncumplimiento > 0) {
            if ($i == $j)
                $this->estado= _CERRADA;
            elseif ($ncumplimiento > 0)
                $this->estado= _GESTIONANDOSE;
            else
                $this->estado= _IDENTIFICADO;            
        } 
             
        $array= array('id'=>$id, 'estado'=> $this->estado, 'cronos'=>$max_cronos, 'ntareas'=>$i, 'ncumplidas'=>$j);
        return !empty($max_cronos) ? $array : null;
    }

    public function list_ranking($result, $automatic_note= false) {
        if ($this->clink->num_rows($result) == 0)
            return null;

        $i= 0;
        $array= null;
        $this->id_usuario= null;
        $this->estado= null;

        $id_nota= $this->id_nota;
        $id_nota_code= $this->id_nota_code;
        $id_proceso= $this->id_proceso;

        $id_auditoria= $this->id_auditoria;
        $id_auditoria_code= $this->id_auditoria_code;
        
        while ($row= $this->clink->fetch_array($result)) {
            $this->id_usuario= null;
            $this->estado= null;
            $this->id_auditoria= $row['id_auditoria'];
            $this->id_auditoria_code= $row['id_auditoria_code'];
            
            $this->id_lista= $row['id_lista'];
            $this->id_lista_code= $row['id_lista_code'];
            $this->id_requisito= $row['id_requisito'];
            $this->id_requisito_code= $row['id_requisito_code'];

            $is_null_array= false;
            $_array= $this->getNota_reg($row['_id'], null, null, $row['_id_proceso'], true);

            if (is_null($_array)) {
                $_array['id']= $row['_id'];
                $_array['estado']= _IDENTIFICADO;
                $_array['reg_fecha']= null;
                $_array['observacion']= null; 
                $is_null_array= true;
            }
            
            $this->estado= $_array['estado'];
            $this->cumplimiento= $_array['cumplimiento'];

            if ($automatic_note) {
                $_arrayR= $this->compute_status($row['_id']);
                $_array['id_proceso']= $row['_id_proceso'];
                $_array['fecha']= $row['fecha_inicio_real'];

                if ($is_null_array || ((!is_null($_arrayR) && !is_null($_array)) 
                    && (strtotime($_arrayR['cronos']) >= strtotime($_array['cronos']) 
                        || (strtotime($_arrayR['cronos']) < strtotime($_array['cronos']) 
                            && ($_array['id_usuario'] != _USER_SYSTEM && strtotime($row['cronos']) == strtotime($_array['cronos'])))))) {
                    
                    $this->id_nota= $row['_id'];
                    $this->id_nota_code= $row['_id_code'];
                    $this->id_proceso= $row['_id_proceso'];
                    $this->id_proceso_code= $row['_id_proceso_code'];
                    $this->estado= $_arrayR['estado'];
                    $this->id_usuario= _USER_SYSTEM;
                    $this->fecha= $this->cronos;
                    $this->observacion= "Variable ESTADO asignado automaticamente por el sistema";
                    $this->set_estado();

                    $_array['estado']= $this->estado;
                    $_array['reg_fecha']= $this->fecha;
                    $_array['observacion']= $this->observacion;

                    $this->id_proceso= null;
                    $this->id_proceso_code= null;
                    $this->estado= null;
                    $this->id_usuario= null;
                    $this->fecha= null;
                    
                } else {
                    if (is_null($_arrayR) && is_null($_array)) {
                        $_array['estado']= _IDENTIFICADO;
                        $_array['reg_fecha']= null;
                        $_array['observacion']= null;                        
                    } 
                }
            }

            if (is_array($_array))
                $array[$i++]= $_array;
        }

        $this->id_nota= $id_nota;
        $this->id_nota_code= $id_nota_code;
        $this->id_proceso= $id_proceso;
        $this->id_auditoria= $id_auditoria;
        $this->id_auditoria_code= $id_auditoria_code;
        
        $num= $i;
        $j= 0;
        $ranking= null;
        $emax= null;

        for ($k= 0; $k < $num; ++$k) {
            $null = true;

            for ($i = 0; $i < $num; ++$i) {
                if (is_null($array[$i]))
                    continue;

                if ($null) {
                    $emax= $array[$i]['estado'];
                    $p= $i;
                    $null= false;
                    continue;
                }

                if ($array[$i]['estado'] > $emax) {
                    $emax= $array[$i]['estado'];
                    $p= $i;
                }
            }

            $ranking[$j++]= $array[$p];
            $array[$p]= null;
        }

        reset($ranking);
        return $ranking;
    }

    public function set_estado($id_proceso= null, $id_proceso_code= null) {
        Tregister_nota::set_estado($id_proceso, $id_proceso_code);
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";
if (!class_exists('Tregister_planning'))
    include_once "register_planning.class.php";