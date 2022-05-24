<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

if (!class_exists('Tbase_evento'))
    include_once "base_evento.class.php";
if (!class_exists('Tbase_evento'))
    include_once _ROOT_DIRIGER_DIR."php/class/base_evento.class.php";

class Tevento extends Tbase_evento {
    private $control_list_copy;
    protected $array_tipo_eventos;

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_evento::__construct($clink);

        $this->if_teventos= false;
        $this->if_treg_evento= false;
        $this->if_tusuarios= false;
        $this->like_name= null;

        $this->className= 'Tevento';
        $this->obj_code= new Tcode($this->clink);
    }

    public function Set($id= null) {
        $this->id_evento= !empty($id) ? $id : $this->id_evento;
        $this->id= $this->id_evento;

        if (empty($this->id_evento) && empty($this->id_tematica))
            return null;

        $sql= "select * from teventos ";
        if (!empty($this->id_evento))
            $sql.= "where id= $this->id_evento ";
        elseif (!empty($this->ifaccords) && !empty($this->id_tematica))
            $sql.= "where id_tematica = $this->id_tematica ";

        $result= $this->do_sql_show_error('Set', $sql);
        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->id= $row['id'];
            $this->id_evento= $this->id;

            $this->id_code= $row['id_code'];
            $this->id_evento_code= $this->id_code;

            $this->nombre= stripslashes($row['nombre']);
            $this->numero= $row['numero'];
            $this->numero_plus= $row['numero_plus'];

            $this->descripcion= stripslashes($row['descripcion']);
            $this->lugar= stripslashes($row['lugar']);
            $this->id_responsable= $row['id_responsable'];
            $this->id_responsable_2= $row['id_responsable_2'];
            $this->responsable_2_reg_date= $row['id_responsable_2_reg_date'];

            $this->id_user_asigna= $row['id_usuario'];
            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];

            $this->funcionario= stripslashes($row['funcionario']);
            $this->sendmail= boolean($row['sendmail']);

            $this->fecha_inicio_plan= $row['fecha_inicio_plan'];
            $this->fecha_fin_plan= $row['fecha_fin_plan'];

            $this->toshow_plan= $row['toshow'];
            $this->user_check_plan= $row['user_check'];
            $this->empresarial= $row['empresarial'];

            $this->id_tipo_evento= $row['id_tipo_evento'];
            $this->id_tipo_evento_code= $row['id_tipo_evento_code'];

            $this->kronos= $row['cronos'];

            $this->periodicidad= $row['periodicidad'];
            $this->carga= $row['carga'];
            $this->dayweek= $row['dayweek'];
            $this->sunday= boolean($row['sunday']);
            $this->saturday= boolean($row['saturday']);
            $this->freeday= boolean($row['freeday']);

            $this->id_evento_ref= $row['id_evento'];
            $this->id_evento_ref_code= $row['id_evento_code'];

            $this->id_tarea= $row['id_tarea'];
            $this->id_tarea_code= $row['id_tarea_code'];

            $this->id_auditoria= $row['id_auditoria'];
            $this->id_auditoria_code= $row['id_auditoria_code'];

            $this->id_tipo_reunion= $row['id_tipo_reunion'];
            $this->id_tipo_reunion_code= $row['id_tipo_reunion_code'];
            $this->if_send= boolean($row['if_send']);

            $this->id_tematica= $row['id_tematica'];
            $this->id_tematica_code= $row['id_tematica_code'];

            $this->origen_data= $row['origen_data'];
            $this->copyto= $row['copyto'];

            $this->id_copyfrom= $row['id_copyfrom'];
            $this->id_copyfrom_code= $row['id_copyfrom_code'];

            $this->id_secretary= $row['id_secretary'];
            $this->ifassure= boolean($row['ifassure']);

            $this->id_archivo= $row['id_archivo'];
            $this->id_archivo_code= $row['id_archivo_code'];

            $this->indice= $row['indice'];
            $this->indice_plus= $row['indice_plus'];
            $this->tidx= boolean($row['tidx']);
            $this->year= date('Y', strtotime($this->fecha_inicio_plan));

            $sql= "select * from treg_evento_{$this->year} where id_evento = $this->id_evento ";
            $sql.= !empty($this->id_usuario) ? "and id_usuario = $this->id_usuario " : "and id_usuario = $this->id_responsable ";
            $sql.= "order by cronos desc LIMIT 1";

            $result= $this->do_sql_show_error('Set', $sql);
            if ($result) {
                $row= $this->clink->fetch_array($result);

                $this->aprobado= $row['aprobado'];
                $this->rechazado= $row['rechazado'];
                $this->id_responsable_ref= $row['id_responsable'];
                $this->cumplimiento= $row['cumplimiento'];
                $this->compute= boolean($row['compute']);
                $this->toshow= boolean($row['toshow']);
                $this->outlook= boolean($row['outlook']);

                if (!empty($this->id_tarea)) {
                    $obj_task= new Ttarea($this->clink);
                    $obj_task->Set($this->id_tarea);
                    $this->id_proyecto= $obj_task->GetIdProyecto();
                    $this->id_proyecto_code= $obj_task->get_id_proyecto_code();
        }   }   }

        return $this->error;
    }

    public function set_evento($id) {
        $this->copy_in_object($this->obj_reg);
        $sql= "select teventos.*, id_proceso as id_proceso_asigna from teventos where teventos.id = $id ";

        $result= $this->do_sql_show_error('listyear', $sql);
        $row= $this->clink->fetch_array($result);
        $rowcmp= $this->obj_reg->get_last_reg($id, $row['id_responsable']);

        $cumplimiento= !is_null($rowcmp) ? $rowcmp['cumplimiento'] : _NO_INICIADO;
        $fecha= $row['fecha_inicio_plan'];
        $memo= !is_null($rowcmp) ? $rowcmp['observacion'] : "No existe usuario con la tarea asignada y cumplimiento reportado. Detectedado por el Sistema ". date('d/m/Y H:s');
        $rechazado= !is_null($rowcmp) ? $rowcmp['rechazado'] : null;
        $aprobado= !is_null($rowcmp) ? $rowcmp['aprobado'] : null;

        $time= odbc2ampm(substr($row['fecha_inicio_plan'], 0, 5)).'-'.odbc2ampm(substr($row['fecha_fin_plan'], 0, 5));

        $array= array('id'=>$id,'time'=>$time, 'evento'=>stripslashes($row['nombre']), 'lugar'=>stripslashes($row['lugar']),
            'cumplimiento'=>$cumplimiento, 'fecha'=>$fecha, 'fecha_inicio'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'],
            'memo'=>stripslashes($memo), 'id_tarea'=>$row['id_tarea'], 'id_evento'=> $row['id_evento'], 'empresarial'=>$row['empresarial'],
            'descripcion'=>stripslashes($row['descripcion']), 'id_responsable'=>$row['id_responsable'], 'id_usuario'=>$row['id_usuario'],
            'origen_data_asigna'=>$row['origen_data'], 'id_tipo_evento'=>$row['id_tipo_evento'], 'id_tipo_evento_code'=>$row['id_tipo_evento_code'],
            'aprobado'=>$aprobado, 'rechazado'=>$rechazado, 'year'=>$row['year'], 'month'=>null, 'cronos'=>$row['cronos'],
            'id_auditoria'=> $row['id_auditoria'], 'id_tematica'=> $row['id_tematica'], 'toshow'=>$row['toshow'],
            'id_proceso'=>$row['id_proceso'], 'user_check'=>$rowcmp['user_check'], 'user_check_plan'=>$row['user_check'],
            'id_copyfrom'=>$row['id_copyfrom'], 'id_tipo_reunion'=>$row['id_tipo_reunion'], 'id_secretary'=>$row['id_secretary'],
            'id_proceso_asigna'=>$row['id_proceso_asigna']);

        return $array;
    }

    public function add($id_evento= null, $id_evento_code= null, &$error_duplicate= null) {
        new Ttabla_anno($this->clink, date('Y', strtotime($this->fecha_inicio_plan)));
        $this->set_tidx($id_evento);

        $this->periodicidad= setNULL($this->periodicidad);
        $this->carga= setNULL($this->carga);
        $dayweek= setNULL_str($this->dayweek);
        $funcionario= setNULL_str($this->funcionario);
        $sendmail= boolean2pg($this->sendmail);

        $id_tipo_evento= setNULL_empty($this->id_tipo_evento);
        $id_tipo_evento_code= setNULL_str($this->id_tipo_evento_code);

        $toshow= setZero($this->toshow_plan);
        $toworkplan= setNULL($this->toworkplan);
        $this->empresarial= setZero($this->empresarial);

        $id_tarea= setNULL($this->id_tarea);
        $id_tarea_code= setNULL_str($this->id_tarea_code);

        $saturday= boolean2pg($this->saturday);
        $sunday= boolean2pg($this->sunday);
        $freeday= boolean2pg($this->freeday);

        if (is_null($id_evento))
            $this->id_evento_code= null;
        $id_evento= setNULL($id_evento);
        $id_evento_code= setNULL_str($id_evento_code);

        $id_auditoria= setNULL($this->id_auditoria);
        $id_auditoria_code= setNULL_str($this->id_auditoria_code);

        $id_tematica= setNULL($this->id_tematica);
        $id_tematica_code= setNULL_str($this->id_tematica_code);

        if (empty($this->id_proceso))
            $this->id_proceso= $_SESSION['id_entity'];
        if (empty($this->id_proceso_code))
            $this->id_proceso_code= get_code_from_table('tprocesos', $this->id_proceso);

        $id_usuario= $_SESSION['id_usuario'];

        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $lugar= setNULL_str($this->lugar);
        $user_check= boolean2pg($this->user_check_plan);

        $id_copyfrom= setNULL_empty($this->id_copyfrom);
        $id_copyfrom_code= setNULL_str($this->id_copyfrom_code);

        $id_tipo_reunion= setNULL($this->id_tipo_reunion);
        $id_tipo_reunion_code= setNULL_str($this->id_tipo_reunion_code);

        $ifassure= boolean2pg($this->ifassure);
        $if_send= boolean2pg($this->if_send);
        $id_secretary= setNULL_empty($this->id_secretary);

        $numero= setNULL($this->numero);
        $numero_plus= setNULL_str($this->numero_plus);

        $indice= setNULL($this->indice);
        $indice_plus= setNULL($this->indice_plus);
        $tidx= setNULL($this->tidx);

        $id_archivo= setNULL($this->id_archivo);
        $id_archivo_code= setNULL_str($this->id_archivo_code);

        $sql= "insert into teventos (numero, numero_plus, nombre, id_responsable, id_usuario, id_proceso, id_proceso_code, ";
        $sql.= "fecha_inicio_plan, fecha_fin_plan, empresarial, id_tipo_evento, toshow, user_check, descripcion, lugar, id_evento, ";
        $sql.= "id_evento_code, periodicidad, carga, dayweek, funcionario, sendmail, saturday, sunday, freeday, id_tarea, id_tarea_code, ";
        $sql.= "id_auditoria, id_auditoria_code, id_tipo_reunion, id_tipo_reunion_code, id_tematica, id_tematica_code, id_copyfrom, ";
        $sql.= "id_copyfrom_code, cronos, situs, ifassure, id_secretary, id_archivo, id_archivo_code, if_send, id_tipo_evento_code, indice, ";
        $sql.= "indice_plus, tidx) values ($numero, $numero_plus, $nombre, $this->id_responsable, $id_usuario, {$_SESSION['id_entity']}, ";
        $sql.= "'{$_SESSION['id_entity_code']}', '$this->fecha_inicio_plan', '$this->fecha_fin_plan', $this->empresarial, ";
        $sql.= "$id_tipo_evento, $toshow, $user_check, $descripcion, $lugar, $id_evento, $id_evento_code, $this->periodicidad, ";
        $sql.= "$this->carga, $dayweek, $funcionario, $sendmail, $saturday, $sunday, $freeday, $id_tarea, $id_tarea_code, $id_auditoria, ";
        $sql.= "$id_auditoria_code, $id_tipo_reunion, $id_tipo_reunion_code, $id_tematica, $id_tematica_code, $id_copyfrom, $id_copyfrom_code, ";
        $sql.= "'$this->cronos', '$this->location', $ifassure, $id_secretary, $id_archivo, $id_archivo_code, $if_send, $id_tipo_evento_code, ";
        $sql.= "$indice, $indice_plus, $tidx) ";

        $result= $this->do_sql_show_error('add', $sql);
        
        if ($result) {
            $this->id= $this->clink->inserted_id("teventos");
            $this->id_evento= $this->id;

            $this->obj_code->SetId($this->id);
            $this->id_code= $this->obj_code->set_code('teventos','id','id_code');
            if (empty($this->id_code)) {
                $this->id_code= $this->obj_code->get_id_code();
                $sql = "update teventos set `id_code` = '$this->id_code' where `id`= $this->id";
                $this->do_sql_show_error('set_code', $sql);
            }
            $this->id_evento_code= $this->id_code;
            
        } else {
            $error= $this->clink->error();
            if (stripos($error,'duplicate') !== false || stripos($error,'duplicada') !== false)
                $error_duplicate= true;
        }

        return $this->error;
    }

    public function update($id_evento= null) {
        new Ttabla_anno($this->clink, date('Y', strtotime($this->fecha_inicio_plan)));
        $this->set_tidx($this->id_evento_ref);

        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;

        $this->periodicidad= setNULL($this->periodicidad);
        $this->carga= setNULL($this->carga);
        $dayweek= setNULL_str($this->dayweek);
        $funcionario= setNULL_str($this->funcionario);
        $sendmail= boolean2pg($this->sendmail);

        $this->empresarial= setZero($this->empresarial);
        $toshow= setZero($this->toshow_plan);

        $id_tipo_evento= setNULL_empty($this->id_tipo_evento);
        $id_tipo_evento_code= setNULL_str($this->id_tipo_evento_code);

        $id_responsable_2= setNULL($this->id_responsable_2);
        $responsable_2_reg_date= setNULL_str($this->responsable_2_reg_date);

        $saturday= boolean2pg($this->saturday);
        $sunday= boolean2pg($this->sunday);
        $freeday= boolean2pg($this->freeday);

        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $lugar= setNULL_str($this->lugar);
        $user_check= boolean2pg($this->user_check_plan);

        $id_tipo_reunion= setNULL($this->id_tipo_reunion);
        $id_tipo_reunion_code= setNULL_str($this->id_tipo_reunion_code);

        $numero= setNULL($this->numero);
        $numero_plus= setNULL_str($this->numero_plus);

        $ifassure= boolean2pg($this->ifassure);
        $if_send= boolean2pg($this->if_send);
        $id_secretary= setNULL_empty($this->id_secretary);

        $id_archivo= setNULL($this->id_archivo);
        $id_archivo_code= setNULL_str($this->id_archivo_code);

        $indice= setNULL($this->indice);
        $indice_plus= setNULL($this->indice_plus);
        $tidx= setNULL($this->tidx);

        $sql = "update teventos set nombre= $nombre, id_responsable= $this->id_responsable, descripcion= $descripcion, ";
        $sql .= "lugar= $lugar, empresarial= $this->empresarial, funcionario= $funcionario, sendmail= $sendmail, ";
        $sql .= "saturday= $saturday, sunday= $sunday, freeday= $freeday, cronos= '$this->cronos', situs= '$this->location', ";
        $sql .= "fecha_inicio_plan= '$this->fecha_inicio_plan', fecha_fin_plan= '$this->fecha_fin_plan', ";
        $sql .= "periodicidad= $this->periodicidad, carga= $this->carga, dayweek= $dayweek, id_tipo_evento= $id_tipo_evento, ";
        $sql .= "toshow= $toshow, user_check= $user_check,  numero= $numero, id_tipo_reunion= $id_tipo_reunion, ";
        $sql .= "id_tipo_reunion_code= $id_tipo_reunion_code, ifassure= $ifassure, id_secretary= $id_secretary, ";
        $sql .= "id_archivo= $id_archivo, id_archivo_code= $id_archivo_code, numero_plus= $numero_plus, if_send= $if_send, ";
        $sql .= "id_tipo_evento_code= $id_tipo_evento_code, indice= $indice, indice_plus= $indice_plus, tidx= $tidx ";
        if (!empty($this->id_responsable_2))
            $sql.= ", id_responsable_2= $id_responsable_2, responsable_2_reg_date= $responsable_2_reg_date ";
        $sql.= "where id= $id_evento ";

        $result= $this->do_sql_show_error('Teventos::update', $sql);
        return $this->error;
    }

    public function update_exclusive($id_evento, $fecha) {
        $sql= "select id, id_code from teventos where id_evento= $id_evento and ".str_to_date2pg("fecha_inicio_plan")." = ".str_to_date2pg("'$fecha'");
        $result= $this->do_sql_show_error('Teventos::update_exclusive', $sql);

        if (empty($this->cant) || $this->cant == -1)
            return null;

        $id= $this->clink->fetch_result($result, 0, 'id');
        $id_code= $this->clink->fetch_result($result, 0, 'id_code');

        $funcionario= setNULL_str($this->funcionario);
        $sendmail= boolean2pg($this->sendmail);

        if (empty($this->empresarial))
            $this->empresarial= 0;
        $toshow= setZero($this->toshow_plan);

        $id_tipo_evento= setNULL_empty($this->id_tipo_evento);
        $id_tipo_evento_code= setNULL_str($this->id_tipo_evento_code);

        $id_tipo_reunion= setNULL($this->id_tipo_reunion);
        $id_tipo_reunion_code= setNULL($this->id_tipo_reunion_code);

        $id_responsable_2= setNULL($this->id_responsable_2);
        $responsable_2_reg_date= setNULL_str($this->responsable_2_reg_date);

        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);
        $lugar= setNULL_str($this->lugar);

        $ifassure= boolean2pg($this->ifassure);
        $if_send= boolean2pg($this->if_send);
        $id_secretary= setNULL($this->id_secretary);

        $numero_plus= setNULL_str($this->numero_plus);

        $sql= "update teventos set nombre= $nombre, id_responsable= $this->id_responsable, descripcion= $descripcion, ";
        $sql.= "lugar= $lugar, empresarial= $this->empresarial, funcionario= $funcionario, sendmail= $sendmail, ";
        $sql.= "fecha_inicio_plan= '$this->fecha_inicio_plan', fecha_fin_plan= '$this->fecha_fin_plan', ";
        $sql.= "id_tipo_evento= $id_tipo_evento, toshow= $toshow, id_evento= NULL, id_evento_code= NULL, ";
        $sql.= "id_responsable_2= $id_responsable_2, responsable_2_reg_date= $responsable_2_reg_date, ";
        $sql.= "ifassure= $ifassure, id_secretary= $id_secretary, numero_plus= $numero_plus, ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' if_send= $if_send, id_tipo_evento_code= $id_tipo_evento_code, ";
        $sql.= "id_tipo_reunion= $id_tipo_reunion, id_tipo_reunion_code= $id_tipo_reunion_code ";
        $sql.= "where id_evento= $id_evento and id = $id";

        $this->do_sql_show_error('Teventos::update_exclusive', $sql);
        return array($id, $id_code);
    }

    public function update_id_evento_ref($id_evento= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        if (empty($this->id_evento_ref) || empty($this->id_evento_ref_code))
            return null;

        $this->periodicidad= setNULL($this->periodicidad);
        $this->carga= setNULL($this->carga);
        $dayweek= setNULL_str($this->dayweek);
        $this->empresarial= setZero($this->empresarial);
        $toshow= setZero($this->toshow_plan);

        $sql= "update teventos set id_evento = $this->id_evento_ref, id_evento_code= '$this->id_evento_ref_code', ";
        $sql.= "periodicidad= $this->periodicidad, carga= $this->carga, dayweek= $dayweek, empresarial= $this->empresarial, ";
        $sql.= "toshow= $toshow where id = $id_evento";
        $this->do_sql_show_error('update_id_evento_ref', $sql);
    }

// solo lista los eventos padres
    public function listar($flag= true) {
        $flag= !is_null($flag) ? $flag : true;
        if (isset($this->array_eventos)) unset($this->array_eventos);
        $this->array_eventos= array();
        if (!$flag)
            $obj_reg= new Tregister_planning ($this->clink);
        
        $this->empresarial= empty($this->empresarial) ? 0 : $this->empresarial;

        $fecha_inicio= $this->fecha_inicio_plan;
        $fecha_fin= $this->fecha_fin_plan;

        $sql= "select distinct teventos.* ";
        if (!empty($this->id_usuario))
            $sql.= "from teventos, treg_evento_{$this->year} ";
        elseif (!empty ($this->id_proceso))
            $sql.= "from teventos, tproceso_eventos_{$this->year} ";
        else 
            $sql.= "from teventos ";
        $sql.= "where 1 ";
        if (!empty($fecha_inicio) && !empty($fecha_fin)) {
            $sql.= "and ((".date2pg("fecha_inicio_plan")." >= '$fecha_inicio' and ".date2pg("fecha_inicio_plan")." <= '$fecha_fin') ";
            $sql.= "or (".date2pg("fecha_fin_plan")." >= '$fecha_inicio' and ".date2pg("fecha_fin_plan")." <= '$fecha_fin')) ";
        }
        if (!empty($this->id_usuario))
            $sql.= "and (teventos.id = treg_evento_{$this->year}.id_evento and treg_evento_{$this->year}.id_usuario = $this->id_usuario) ";
        if (!empty($this->id_proceso)) {
            $sql.= "and ((teventos.id = tproceso_eventos_{$this->year}.id_evento and tproceso_eventos_{$this->year}.id_proceso = $this->id_proceso) ";
            $sql.= "or teventos.id_proceso = $this->id_proceso) ";
        }
        if (!empty($this->empresarial))
            $sql.= "and teventos.empresarial >= 1 ";
        if (!is_null($this->toshow_plan))
            $sql.= "and teventos.toshow = $this->toshow_plan ";
        if (!empty($this->id_responsable))
            $sql.= "and (teventos.id_responsable = $this->id_responsable or teventos.id_responsable_2 = $this->id_responsable) ";
        $sql.= "order by teventos.fecha_inicio_plan asc, fecha_fin_plan desc";

        $result= $this->do_sql_show_error('listar', $sql);
        if ($flag)
            return $result;
        
        while ($row= $this->clink->fetch_array($result)) {
            $rowcmp= null;
            if (!empty($this->id_usuario)) {
                $obj_reg->SetIdUsuario($this->id_usuario);
                $rowcmp= $obj_reg->getEvento_reg($row['id']);
            }
            if (empty($this->id_usuario) && !empty($this->id_proceso)) {
                $obj_reg->SetIdProceso($this->id_proceso);
                $rowcmp= $obj_reg->get_reg_proceso($row['id']);
            }            
            
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_usuario'=> $this->id_usuario,
                'id_responsable'=>$row['id_responsable'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'],
                'id_auditoria'=>$row['id_auditoria'], 'id_auditoria_code'=>$row['id_auditoria_code'],
                'id_tematica'=>$row['id_tematica'], 'id_tematica_code'=>$row['id_tematica_code'], 'evento'=>$row['nombre'],
                'lugar'=>$row['lugar'], 'descripcion'=>$row['descripcion'], 'fecha_inicio'=>$row['fecha_inicio_plan'],
                'fecha_fin'=>$row['fecha_fin_plan'], 'id_archivo'=>$row['id_archivo'], 'id_archivo_code'=>$row['id_archivo_code'], 
                'id_proceso'=>$row['id_proceso'], 'id_proceso_code'=>$row['id_proceso_code'], 'ifmeeting'=>$row['id_tipo_reunion'],
                'rechazado'=>$rowcmp['rechazado'], 'aprobado'=>$rowcmp['aprobado'], 'cumplimiento'=>$rowcmp['cumplimiento'],
                'outlook'=>boolean($rowcmp['outlook']), 'flag'=>1);
            $this->array_eventos[]= $array; 
        }    
    }

    public function listyear($empresarial=0, $id_tipo_evento=0, $limited= false) {
        if (isset($this->array_eventos)) unset($this->array_eventos);
        $this->copy_in_object($this->obj_reg);

        $sql= "select distinct _teventos.*, _teventos.id as _id, _teventos.numero as _numero, _teventos.nombre as evento, ";
        $sql.= "_teventos.id_tarea as _id_tarea, _teventos.empresarial, _teventos.id_evento as _id_evento, _teventos.id_responsable as _id_responsable, ";
        $sql.= "_teventos.id_auditoria as _id_auditoria, _teventos.id_tematica as _id_tematica, ".time2pg("fecha_inicio_plan")." as time1, ";
        $sql.= time2pg("fecha_fin_plan")." as time2, _teventos.id_user_asigna as _id_usuario, _teventos.id_tipo_evento, _teventos.cronos as _cronos, ";
        $sql.= "_teventos.toshow as _toshow, _teventos.id_proceso as _id_proceso, _teventos.id_proceso_code as _id_proceso_code, ";
        $sql.= "_teventos.funcionario, _teventos.numero_plus as _numero_plus, _teventos.user_check as _user_check, ";
        $sql.= "_teventos.id_copyfrom, _teventos.id_copyfrom_code, id_tipo_reunion, ifassure, id_secretary, id_archivo, ";
        $sql.= "_teventos.id_proceso_asigna as id_proceso_asigna, _teventos.indice_plus as _indice_plus, numero_tmp, _teventos.observacion ";
        if ($this->create_temporary_treg_evento_table && $this->if_treg_evento)
            $sql.= ", outlook ";
        $sql.= "from _teventos ";
        if ($this->create_temporary_treg_evento_table) {
            if ($this->if_treg_evento)
                $sql.= ", _treg_evento where (_teventos.id = _treg_evento.id_evento and _treg_evento.toshow = true) ";
            if ($this->if_tproceso_eventos)
                $sql.= "where _teventos.toshow = 2 ";
        } else {
            if ($limited && $this->if_tidx) {
                $sql.= ", _tidx where ((_tidx.id is not null and (_teventos.id = _tidx.id or _teventos.id_evento = _tidx.id)) ";
                $sql.= "or ((_tidx.id_auditoria is not null and _teventos.id_auditoria = _tidx.id_auditoria) ";
                $sql.= "or (_tidx.id_tarea is not null and _teventos.id_tarea = _tidx.id_tarea))) ";
                $sql.= "and _teventos.toshow = 2 ";
            } else
                $sql.= "where _teventos.toshow = 2 ";
        }
        if (!empty($empresarial)) {
            $sql.= "and (_teventos.empresarial = $empresarial ";
            if (!empty($id_tipo_evento))
                $sql.= "and _teventos.id_tipo_evento = $id_tipo_evento ";
            else
                $sql.= "and (_teventos.id_tipo_evento is null or _teventos.id_tipo_evento = 0) ";
            $sql.= ") ";
        }
        $sql.= "order by _teventos.indice asc, _teventos.numero asc, _teventos.indice_plus asc, _teventos.cronos asc ";

        $result= $this->do_sql_show_error('listyear', $sql);
        if (empty($this->cant))
            return 0;

        $fecha_inicio= null;
        $fecha_fin= null;
        $array_month= array();

        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            $id= null;
            $id_evento= null;
            $id_auditoria= null;
            $id_tarea= null;

            if ($array_ids[$row['_id']])
                continue;
            else
                $array_ids[$row['_id']]= 1;

            if (!empty($row['_id_auditoria'])) {
                if ($this->tidx_array_auditoria[$row['_id_auditoria']][1])
                    continue;
                $this->tidx_array_auditoria[$row['_id_auditoria']][1]= true;
                $id_auditoria= $row['_id_auditoria'];
                $idr= $this->tidx_array_auditoria[$row['_id_auditoria']][0];

            } elseif (!empty($row['_id_tarea'])) {
                if ($this->tidx_array_tarea[$row['_id_tarea']][1])
                    continue;
                $this->tidx_array_tarea[$row['_id_tarea']][1]= true;
                $idr= $this->tidx_array_tarea[$row['_id_tarea']][0];
                $id_tarea= $row['_id_tarea'];

            } elseif (!empty($row['_id_evento'])) {
                if ($this->tidx_array_evento[$row['_id_evento']][1])
                    continue;
                $this->tidx_array_evento[$row['_id_evento']][1]= true;
                $idr= $this->tidx_array_evento[$row['_id_evento']][0];
                $id_evento= $row['_id_evento'];

            } else {
                $id= $row['_id'];
                $idr= $id;
                $this->tidx_array_evento[$id][1]= true;
            }

            $_id= !empty($row['_id_evento']) ? $row['_id_evento'] : $row['_id'];

            $rowcmp= $this->obj_reg->getEvento_reg_anual($id, $id_evento, $id_auditoria, $id_tarea);

            $cumplimiento= !is_null($rowcmp) ? $rowcmp['cumplimiento'] : _NO_INICIADO;
            $fecha= !is_null($rowcmp) ? $rowcmp['fecha_inicio_plan'] : $fecha_inicio;
            $fecha_fin= $row['fecha_fin_plan'];
            $memo= !is_null($rowcmp) ? $rowcmp['observacion'] : "No existe usuario con la tarea asignada y cumplimiento reportado. Detectedado por el Sistema ". date('d/m/Y H:s');
            $rechazado= !is_null($rowcmp) ? $rowcmp['rechazado'] : null;
            $aprobado= !is_null($rowcmp) ? $rowcmp['aprobado'] : null;

            $time= odbc2ampm(substr($row['time1'],0,5)).'-'.odbc2ampm(substr($row['time2'],0,5));

            $array= array('id'=>$_id,'time'=>$time, 'evento'=>stripslashes($row['evento']), 'lugar'=>stripslashes($row['lugar']),
            'cumplimiento'=>$cumplimiento, 'fecha'=>$fecha, 'fecha_inicio'=>$fecha_inicio, 'fecha_fin'=>$fecha_fin,
            'memo'=>stripslashes($memo), 'id_tarea'=>$row['_id_tarea'], 'id_evento'=> $row['_id_evento'], 'numero'=>$row['_numero'],
            'empresarial'=>$row['empresarial'], 'descripcion'=>stripslashes($row['descripcion']), 'id_responsable'=>$row['_id_responsable'],
            'id_usuario'=>$row['_id_usuario'], 'origen_data_asigna'=>$row['origen_data_asigna'], 'id_tipo_evento'=>$row['id_tipo_evento'],
            'aprobado'=>$aprobado, 'rechazado'=>$rechazado, 'year'=>$row['year'], 'month'=> $this->tidx_array[$idr]['month'], 'cronos'=>$row['_cronos'],
            'id_auditoria'=> $row['_id_auditoria'], 'id_tematica'=> $row['_id_tematica'], 'toshow'=>$row['_toshow'], 'id_proceso'=>$row['_id_proceso'],
            'user_check'=>boolean($rowcmp['user_check']), 'user_check_plan'=>boolean($row['_user_check']), 'id_copyfrom'=>$row['id_copyfrom'],
            'id_tipo_reunion'=>$row['id_tipo_reunion'], 'outlook'=>$row['outlook'], 'ifassure'=>boolean($row['ifassure']), 'id_secretary'=>$row['id_secretary'],
            'id_archivo'=>$row['id_archivo'], 'id_proyecto'=>null, 'numero_plus'=>$row['_numero_plus'], 
            'id_responsable_asigna'=>$row['id_responsable_asigna'], 'id_proceso_asigna'=>$row['id_proceso_asigna'],
            'indice_plus'=>$row['_indice_plus'], 'numero_tmp'=>$row['numero_tmp'], 'funcionario'=>$row['funcionario'],
            'id_responsable_2'=>$row['id_responsable_2'], 'responsable_2_reg_date'=>$row['responsable_2_reg_date']);

            $this->array_eventos[$i++]= $array;
        }

        $this->cant= $i;
    }

    public function listmonth($empresarial= null, $id_tipo_evento= null, $toshow= null) {
        if (isset($this->array_eventos)) unset($this->array_eventos);
        $this->copy_in_object($this->obj_reg);

        $sql= "select distinct _teventos.id as _id, numero, _teventos.nombre as evento, _teventos.id_tarea as _id_tarea, _teventos.empresarial, ";
        $sql.= "_teventos.id_evento as _id_evento, _teventos.id_auditoria as _id_auditoria, _teventos.id_tematica as _id_tematica, ";
        $sql.= "fecha_inicio_plan, fecha_fin_plan, descripcion, lugar, ".time2pg("fecha_inicio_plan")." as time1, ";
        $sql.= time2pg("fecha_fin_plan")." as time2, _teventos.id_responsable as _id_responsable, _teventos.funcionario, ";
        $sql.= "_teventos.id_user_asigna as _id_usuario, _teventos.id_tipo_evento, year, _teventos.cronos as _cronos, ";
        $sql.= "month, day, _teventos.toshow as _toshow, _teventos.id_proceso as _id_proceso, _teventos.user_check as _user_check, ";
        $sql.= "_teventos.id_proceso_code as _id_proceso_code, _teventos.id_copyfrom, _teventos.id_copyfrom_code, ";
        $sql.= "_teventos.id_tipo_reunion, ifassure, id_secretary, id_archivo, numero_plus, _teventos.observacion, cumplimiento, rechazado, ";
        $sql.= "_teventos.id_responsable_asigna as id_responsable_asigna, _teventos.id_proceso_asigna as id_proceso_asigna, numero_tmp ";
        if ($this->if_treg_evento)
            $sql.= ", outlook ";
        $sql.= "from _teventos ";

        if ($this->if_treg_evento)
            $sql.= ", _treg_evento where (_teventos.id = _treg_evento.id_evento and _treg_evento.toshow = ".boolean2pg(1).") ";
        if ($this->if_tproceso_eventos)
            $sql.= "where _teventos.toshow >= 1 ";

        if (!is_null($empresarial) && empty($empresarial))
            $sql.= "and _teventos.empresarial >= 1 ";
        if (!empty($empresarial))
            $sql.= "and _teventos.empresarial = $empresarial ";
        if (!empty($id_tipo_evento))
            $sql.= "and _teventos.id_tipo_evento = $id_tipo_evento ";
        else if (empty($id_tipo_evento) && !is_null($id_tipo_evento))
            $sql.= "and (_teventos.id_tipo_evento is null or _teventos.id_tipo_evento = 0) ";
        $sql.= "order by ";
        $sql.= $this->if_numering == _ENUMERACION_MANUAL || is_null($this->if_numering) ? "_teventos.numero asc, " : "_teventos.numero_tmp asc, ";
        $sql.= "_teventos.fecha_inicio_plan asc, numero_plus asc ";

        $result= $this->do_sql_show_error('listmoth', $sql);

        $array_control= array();
        $array_month= array();

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $value= (int)$row['cumplimiento'];
            $aprobado= null;

            $continue= true;
            switch($this->print_reject) {
                case(_PRINT_REJECT_NO):
                    if (!empty($row['rechazado']) && ($value == _SUSPENDIDO || $value == _CANCELADO || $value == _DELEGADO))
                        $continue= false;
                    break;

                case(_PRINT_REJECT_OUT):
                    if (!empty($row['rechazado']) && ($value == _SUSPENDIDO || $value == _CANCELADO || $value == _DELEGADO))
                        $continue= false;
                    break;
            }

            if (!$continue)
                continue;
            if (!is_null($toshow) && $row['_toshow'] != $toshow)
                continue;
            if ($i == 0) {
                $array_control[$row['_id']]= $row['_id'];
            } else {
                if (array_key_exists($row['_id'], $array_control))
                    continue;
                $array_control[$row['_id']]= $row['_id'];
            }

            if (!$this->monthstack) {
                $array_month= null;
            } else {
                reset($this->array_eventos);
                $found= false;

                while ($tmp_array= @current($this->array_eventos)) {
                   if (($tmp_array['id'] === $row['_id_evento'] || (!empty($row['_id_evento'] && $tmp_array['id_evento'] === $row['_id_evento'])))
                       || (!empty($row['_id_auditoria']) && $tmp_array['id_auditoria'] == $row['_id_auditoria']))
                   {
                       $k= key($this->array_eventos);

                       $this->array_eventos[$k]['month'][$row['day']]= $row['_id'];
                       $found= true;
                       break;
                   }

                   next($this->array_eventos);
               }

               if ($found)
                   continue;

               if (isset($array_month)) {
                   unset($array_month);
                   $array_month= array();
                   for ($dd= 1; $dd <= 31; $dd++)
                        $array_month[$dd]= 0;
               }

               $array_month[(int)$row['day']]= $row['_id'];
            }

            if (!empty($row['aprobado'])) {
                $aprob= $this->get_aprobado_prs($row['_id']);
                $aprobado= $aprob['nombre']." (".$aprob['cargo'].") el ".odbc2date($aprob['fecha']);
            }

            if ($this->empresarial >= 1 && empty($row['_toshow']))
                continue;

            $time= odbc2ampm(substr($row['time1'],0,5)).'-'.odbc2ampm(substr($row['time2'],0,5));

            $array= array('id'=>$row['_id'], 'time'=>$time, 'evento'=>stripslashes($row['evento']), 'lugar'=>stripslashes($row['lugar']),
            'cumplimiento'=>$row['cumplimiento'], 'fecha'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'],
            'memo'=>stripslashes($row['observacion']), 'id_tarea'=>$row['_id_tarea'], 'id_evento'=> $row['_id_evento'],
            'empresarial'=>$row['empresarial'], 'descripcion'=>stripslashes($row['descripcion']), 'id_responsable'=>$row['_id_responsable'],
            'id_usuario'=>$row['_id_usuario'], 'aprobado'=>$aprobado, 'rechazado'=>$row['rechazado'], 'id_auditoria'=> $row['_id_auditoria'],
            'id_tematica'=> $row['_id_tematica'], 'cronos'=>$row['_cronos'], 'day'=>$row['day'], 'toshow'=>$row['_toshow'],
            'month'=>$array_month, 'id_proceso'=>$row['_id_proceso'], 'user_check'=>boolean($row['user_check']), 'user_check_plan'=>boolean($row['_user_check']),
            'id_copyfrom'=>$row['id_copyfrom'], 'id_tipo_reunion'=>$row['id_tipo_reunion'], 'numero'=>$row['numero'], 'outlook'=>$row['outlook'],
            'ifassure'=>boolean($row['ifassure']), 'id_secretary'=>$row['id_secretary'], 'id_archivo'=>$row['id_archivo'], 'id_proyecto'=>null,
            'numero_plus'=>$row['numero_plus'], 'id_proceso_asigna'=>$row['id_proceso_asigna'], 'funcionario'=>$row['funcionario'],
            'rechazado'=>$row['rechazado'], 'cumplimiento'=>$row['cumplimiento'], 'numero_tmp'=>$row['numero_tmp'],
            'id_responsable_asigna'=>$row['id_responsable_asigna'], 
            'id_responsable_2'=>$row['id_responsable_2'], 'responsable_2_reg_date'=>$row['responsable_2_reg_date']);

            $this->array_eventos[$i++]= $array;
        }

        $this->cant= $i;
    }

    public function listday($day, $empresarial= null, $id_tipo_evento= null, $flag= true) {
        $empresarial= !is_null($empresarial) ? $empresarial : $this->empresarial;
        $id_tipo_evento= !is_null($id_tipo_evento) ? $id_tipo_evento : null;

        if (isset($this->array_eventos)) 
            unset($this->array_eventos);
        $this->copy_in_object($this->obj_reg);

        $tmp_day= $this->day;
        $this->day= $day;
        $this->date_interval($fecha_inicio, $fecha_end);
        $this->day= $tmp_day;

        $teventos= ($this->if_teventos) ? "_teventos" : "teventos";
        $treg_evento= ($this->if_treg_evento) ? "_treg_evento" : "treg_evento_{$this->year}";

        $sql= "select DISTINCT $teventos.id as _id, $teventos.numero as _numero, $teventos.nombre as evento, $teventos.id_tarea as _id_tarea, ";
        $sql.= "empresarial, $teventos.id_evento as _id_evento, fecha_inicio_plan, fecha_fin_plan, descripcion, lugar, ";
        $sql.= time2pg("fecha_inicio_plan")." as time1, ".time2pg("fecha_fin_plan")." as time2, $teventos.id_responsable as _id_responsable, ";
        if ($this->toshow == _EVENTO_INDIVIDUAL && ($this->if_teventos && $this->if_treg_evento))
            $sql.= "$treg_evento.cronos, outlook, $treg_evento.user_check as _user_check, ";
        $sql.= "$teventos.id_auditoria as _id_auditoria, $teventos.id_tematica as _id_tematica, ";
        $sql.= $this->if_teventos ? "_teventos.cronos_asigna as _cronos, " : "teventos.cronos as _cronos, ";
        $sql.= $this->if_teventos ? "_teventos.id_responsable_asigna as id_responsable_asigna, " : "teventos.id_responsable as id_responsable_asigna, ";
        $sql.= "$teventos.funcionario, $teventos.toshow as _toshow, $teventos.id_proceso as _id_proceso, ";
        $sql.= "$teventos.id_proceso_code as _id_proceso_code, $teventos.user_check as user_check_plan, ";
        $sql.= "$teventos.id_copyfrom, $teventos.id_copyfrom_code, id_tipo_reunion, ifassure, id_secretary, id_archivo, numero_plus, ";
        $sql.= "$teventos.id_responsable_2, $teventos.responsable_2_reg_date, ";
        if ($this->if_teventos) {
            $sql.= "$teventos.id_proceso_asigna as id_proceso_asigna, numero_tmp, $teventos.aprobado, $teventos.rechazado, $teventos.cumplimiento, ";
            $sql.= "_teventos.id_user_asigna as _id_usuario, _teventos.origen_data_asigna as _origen_data_asigna, _teventos.observacion ";
        }
        else {
            $sql.= "teventos.id_usuario as _id_usuario, teventos.origen_data as _origen_data_asigna ";
        }
        $sql.= "from $teventos ";
        if ($this->toshow == _EVENTO_INDIVIDUAL && ($this->if_teventos && $this->if_treg_evento))
            $sql.= ", $treg_evento where $treg_evento.id_evento = $teventos.id ";
        else {
            $sql.= $this->toshow == _EVENTO_INDIVIDUAL ? "where $teventos.toshow >= 0 " : "where $teventos.toshow >= "._EVENTO_MENSUAL." ";
        }
            
        if (!is_null($empresarial)) {
            if (empty($empresarial))
                $sql.= "and empresarial >= 1 ";
            else
                $sql.= "and empresarial = $empresarial ";

            if (!empty($id_tipo_evento))
                $sql.= "and _teventos.id_tipo_evento = $id_tipo_evento ";
            else if (empty($id_tipo_evento) && !is_null($id_tipo_evento))
                $sql.= "and (id_tipo_evento is null or id_tipo_evento = 0) ";
        } else {
            $sql.= "and empresarial >= 0 ";
        }
        $sql.= "and date(fecha_inicio_plan) = date('$fecha_inicio') ";
        $sql.= "order by fecha_inicio_plan asc, _numero asc, numero_plus asc, ";

        if ($this->toshow == _EVENTO_INDIVIDUAL && ($this->if_teventos && $this->if_treg_evento))
            $sql.= $flag ? "$treg_evento.cronos desc " : "$teventos.cronos asc ";
        else
            $sql.= "$teventos.cronos asc ";
        $result= $this->do_sql_show_error('listday', $sql);

        $i= 0;
        $array_control= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (count($array_control) == 0)
                $array_control[$row['_id']]= $row['_id'];
            else {
                if (array_key_exists($row['_id'], $array_control))
                    continue;
                $array_control[$row['_id']]= $row['_id'];
            }

            $aprobado= $row['aprobado'];
            if (!empty($row['aprobado'])) {
                $aprob= $this->get_aprobado($row['_id']);
                $aprobado= $aprob['nombre'].", ".$aprob['cargo']."  ".odbc2date($aprob['fecha']);
            }

            if ($this->empresarial >= 1 && ! boolean($row['_toshow']))
                continue;

            $time= odbc2ampm($row['time1']).'-'.odbc2ampm($row['time2']);

            $array= array('id'=>$row['_id'],'time'=>$time, 'evento'=>stripslashes($row['evento']), 'lugar'=>stripslashes($row['lugar']),
            'cumplimiento'=>$row['cumplimiento'], 'fecha'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'],
            'memo'=>stripslashes($row['observacion']), 'id_tarea'=>$row['_id_tarea'], 'id_evento'=> $row['_id_evento'],
            'empresarial'=>$row['empresarial'], 'descripcion'=>stripslashes($row['descripcion']), 'id_responsable'=>$row['_id_responsable'],
            'id_usuario'=>$row['_id_usuario'], 'origen_data_asigna'=>$row['_origen_data_asigna'], 'aprobado'=>$aprobado,
            'rechazado'=>$row['rechazado'], 'id_auditoria'=> $row['_id_auditoria'], 'id_tematica'=> $row['_id_tematica'],
            'cronos'=>$row['_cronos'], 'id_proceso'=>$row['_id_proceso'], 'toshow'=>$row['_toshow'], 'user_check'=>boolean($row['_user_check']),
            'user_check_plan'=>boolean($row['user_check_plan']), 'id_copyfrom'=>$row['id_copyfrom'], 'id_tipo_reunion'=>$row['id_tipo_reunion'],
            'numero'=>$row['_numero'], 'outlook'=>$row['outlook'],'ifassure'=>boolean($row['ifassure']), 'id_secretary'=>$row['id_secretary'],
            'id_archivo'=>$row['id_archivo'], 'id_proyecto'=>null, 'numero_plus'=>$row['numero_plus'], 'id_proceso_asigna'=>$row['id_proceso_asigna'],
            'numero_tmp'=>$row['numero_tmp'], 'funcionario'=>$row['funcionario'], 'id_responsable_asigna'=>$row['id_responsable_asigna'], 
            'id_responsable_2'=>$row['id_responsable_2'], 'responsable_2_reg_date'=>$row['responsable_2_reg_date']);

            $this->array_eventos[$i++]= $array;
        }

        $this->cant= $i;
    }

    public function sort_eventos() {
        reset($this->array_eventos);
        $i= 0;
        foreach ($this->array_eventos as $id => $evento) {
            ++$i;
            $numero= $this->if_numering == _ENUMERACION_MANUAL || is_null($this->if_numering) ? $evento['numero'] : $evento['numero_tmp'];
            $numero[$id]= !empty($numero) ? $numero : $i;
            $numero_plus[$id]= $evento['indice_plus'];
            $cronos[$id]= $evento['cronos'];
        }
        array_multisort($numero, SORT_ASC, $numero_plus, SORT_ASC, $cronos, SORT_ASC, $this->array_eventos);
    }

    public function eliminar_if_empty($id = null, $id_code= null, $if_parent= false, &$observacion= null) {
        $if_parent= !is_null($if_parent) ? $if_parent : false;
        $id = !empty($id) ? $id : $this->id_evento;
        $id_code = !empty($id_code) ? $id_code : $this->id_evento_code;
        
        $array= array();
        $array['id']= $id;
        $array['id_code']= $id_code;
        $array['fecha_inicio']= $this->fecha_inicio_plan;
        $array['fecha_fin']= $this->fecha_fin_plan;
            
        return $this->_delete_if_empty($array, false, $if_parent, $observacion);
    }

    public function eliminar($id_evento= null, $force_delete= false) {
        $force_delete= !is_null($force_delete) ? $force_delete : false;
        $id_evento = !empty($id_evento) ? $id_evento : $this->id_evento;
        
        if ($force_delete) {
            $sql= "delete from tasistencias where id_evento = $id_evento";
            $this->do_sql_show_error('Teventos::eliminar', $sql);
            
            $sql= "delete from ttematicas where id_evento = $id_evento";
            $this->do_sql_show_error('Teventos::eliminar', $sql);
        }
        $sql= "delete from teventos where id = $id_evento ";
        $result= $this->do_sql_show_error('Teventos::eliminar', $sql);
        return $result ? true : false;
    }

    public function get_eventos_by_tarea($id_tarea= null, $array_years= null) {
        if (isset($this->array_eventos)) unset($this->array_eventos);
        $id_tarea= !empty($id_tarea) ? $id_tarea :$this->id_tarea;
        $array_ids= array();
        if (is_null($array_years))
            $array_years= array($this->year, $this->year);

        $sql= null;
        for ($year= $array_years[0]; $year <= $array_years[1]; $year++) {
            if ($year > $array_years[0])
                $sql.= " union ";
            $sql.= "select distinct teventos.* from teventos, treg_evento_$year ";
            $sql.= "where teventos.id_tarea = $id_tarea and (teventos.id = treg_evento_$year.id_evento ";
            $sql.= "and teventos.id_responsable = treg_evento_$year.id_usuario) ";
            if (!empty($this->reg_fecha))
                $sql.= "and ".date2pg("fecha_inicio_plan")." >= '$this->reg_fecha' ";
        }
        $sql.= "order by fecha_inicio_plan desc";
        $result= $this->do_sql_show_error('get_eventos_by_tarea', $sql);
        if (empty($this->cant))
            return null;

        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['id']])
                continue;
            $array_ids[$row['id']]= $row['id'];

            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'],
                            'fecha_inicio'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'],
                            'id_responsable'=>$row['id_responsable']);
            $this->array_eventos[$row['id']]= $array;
        }
        return $this->cant;
    }

    public function set_copyto($id, $year, $id_code) {
        $this->copyto= "$year($id_code)-";
        $sql= "update teventos set copyto= '$this->copyto' where id = $id ";
        $result= $this->do_sql_show_error('set_copyto', $sql);
    }

    private function _fix_array_tipo_eventos_in_year($to_year) {
        $obj= new Ttipo_evento($this->clink);
        $obj->SetYear($to_year);
        $array_tipo_eventos= $obj->listar_all();

        foreach ($array_tipo_eventos as $row) {
            if ($row['inicio'] < $to_year || $row['fin'] > $to_year)
                continue;
            $this->array_tipo_eventos[$row['id']]= $row;
        }
    }

// solo se compian eventos simples o individuales
    public function this_copy($id_proceso=null, $id_proceso_code= null, $tipo= null, $radio_prs= null,
                                $to_year= null, $array_id= null) {
        $plus_year= empty($to_year) ? 1 : ($to_year - $this->year);
        if ($plus_year <= 0)
            $plus_year= 1;
        $to_year= !empty($to_year) ? $to_year : ($this->year + $plus_year);

        if (!$this->array_tipo_eventos)
            $this->_fix_array_tipo_eventos_in_year($to_year);

        $id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];
        $id_proceso_code= !empty($id_proceso_code) ? $id_proceso_code : get_code_from_table('tprocesos', $id_proceso);
        $tipo= !empty($tipo) ? $tipo : $_SESSION['entity_tipo'];

        $obj= $this->_this_copy();

        $obj->SetLink($this->clink);
        $obj->obj_code->SetLink($this->clink);
        $obj->SetId(null);
        $obj->SetIdEvento(null);
        $obj->set_id_code(null);
        $obj->set_id_evento_code(null);

        $obj->tidx= $this->tidx;
        $obj->SetIdTipo_reunion($this->id_tipo_reunion);
        $obj->set_id_tipo_reunion_code($this->id_tipo_reunion_code);
        $obj->SetIdTematica(null);
        $obj->set_id_tematica_code(null);
        
        if (!empty($this->id_tipo_evento)) {
            $id_tipo_evento= array_key_exists($this->id_tipo_evento, $this->array_tipo_eventos) ? $this->id_tipo_evento : null;
            $this->id_tipo_evento= !empty($id_tipo_evento) ? $id_tipo_evento : null;
            $this->id_tipo_evento_code= !empty($id_tipo_evento) ? $this->array_tipo_eventos[$id_tipo_evento]['id_code'] : null;            
        }

        $obj->SetIdTipo_evento($this->id_tipo_evento);
        $obj->set_id_tipo_evento_code($this->id_tipo_evento_code);

        $obj->set_id_responsable_2(null);
        $obj->set_responsable_2_reg_date(null);

        $obj->set_id_copyfrom(null);
        $obj->set_id_copyfrom_code(null);
        $obj->SetOrigenData(null);
        $obj->SetIdProceso($id_proceso);
        $obj->set_id_proceso_code($id_proceso_code);
        $obj->SetIdUsuario($_SESSION['id_usuario']);
        $obj->set_cronos($this->cronos);

        $this->id_proceso= $radio_prs == 2 ? null : $id_proceso;
        $this->id_entity= $radio_prs == 2 ? null : $this->id_entity;
        $this->listar_usuarios();
        $this->listar_grupos();
        
        $this->get_array_reg_usuarios($this->year);
        $this->get_array_reg_procesos($this->year, $id_proceso, $tipo);
        $this->get_array_reg_objs($to_year);
        $this->get_array_reg_tematicas();
        $this->get_array_reg_asistencias();

        $obj_sched= new Tschedule;

        $obj_sched->SetPeriodicidad($this->periodicidad);
        $obj_sched->SetCarga($this->carga);
        $obj_sched->SetDayWeek($this->dayweek);
        $obj_sched->saturday= $this->saturday;
        $obj_sched->sunday= $this->sunday;
        $obj_sched->freeday= $this->freeday;
        if ($this->periodicidad == 3)
            $obj_sched->fixed_day= empty($this->dayweek) ? 0 : 1;

        if ($this->periodicidad == 4) {
            $this->get_child_events_by_table('teventos', $this->id);

            foreach ($this->array_eventos as $evento) {
                $fecha= $evento['fecha_inicio_plan'];
                $obj_sched->input_array_dates[]= add_date($fecha, 0, 0, $plus_year);
            }
        }
        
        return $this->_this_copy_evento($obj, $obj_sched, $plus_year, $to_year, $array_id);
    }    
        
    private function _this_copy_evento($obj, $obj_sched, $plus_year, $to_year, $array_id) {    
        $this->fecha= add_date($this->fecha_inicio_plan, 0, 0, $plus_year);
        $obj->SetFechaInicioPlan($this->fecha);
        $obj_sched->SetFechaInicioPlan($this->fecha);
        
        $obj->SetFechaFinPlan(add_date($this->fecha_fin_plan, 0, 0, $plus_year));
        $obj_sched->SetFechaFinPlan($obj->GetFechaFinPlan());

        $obj_sched->set_dates();
        $obj_sched->create_array_dates();

        if (is_null($array_id)) {
            $this->error= $obj->add();
            if (!is_null($this->error)) {
                unset($obj_sched);
                unset($obj);
                return null;
            }
            $id= $obj->GetId();
            $id_code= $obj->get_id_code();

            $this->set_copyto($this->id, $to_year, $id_code);
        } else {
            $id= $array_id['id'];
            $id_code= $array_id['id_code'];

            $obj->SetIdEvento($id);
            $obj->set_id_evento_code($id_code);

            $this->error= $obj->update($id);
        }

        $this->control_list_copy= 0;
        $this->_copy_reg($to_year, $id, $id_code);

        $array= array('id'=>$id, 'id_code'=>$id_code);
        if (!$this->periodicidad) {
            unset($obj_sched);
            unset($obj);
            return $array;
        }
        reset($obj_sched->array_dates);

        foreach ($obj_sched->array_dates as $date) {
            $error= null;
            $obj->set_null_periodicity();
            $this->fecha= $date['inicio'];
            $obj->SetFechaInicioPlan($this->fecha);
            $obj->SetFechaFinPlan($date['fin']);

            if (is_null($array_id)) {
                $error= $obj->add($id, $id_code);
                $_id= $obj->GetId();
                $_id_code= $obj->get_id_code();
            }

            if (!is_null($array_id)
                    || (!is_null($error) && (stripos($error,'duplicate') !== false || stripos($error,'duplicada') !== false))) {

                $_array_id= $obj->find_evento($date['inicio'], $id);

                if (is_null($_array_id)) {
                    if (is_null($error))
                        $obj->add($id, $id_code);
                    else
                        continue;

                    $_id= $obj->GetId();
                    $_id_code= $obj->get_id_code();
                } else {
                    $_id= $_array_id['id'];
                    $_id_code= $_array_id['id_code'];
                }
            }
            
            ++$this->control_list_copy;
            $this->_copy_reg($to_year, $_id, $_id_code);
        }

        unset($obj_sched);
        unset($obj);
        return $array;
    }

    private function _copy_reg_init($to_year, $id_evento, $id_evento_code) {
        /* copia tusuario_eventos */
        $error= $this->_copy_evento_usuarios($to_year, $id_evento, $id_evento_code, null, null, null, null);

        /* copia treg_evento */
        $error.= $this->_copy_reg_usuarios($to_year, $id_evento, $id_evento_code, null, null, null, null);

        /* copia tproceso_eventos */
        $error.= $this->_copy_proceso($to_year, $id_evento, $id_evento_code, null, null, null, null);        
    }
    
    private function _copy_reg_tematicas($to_year, $id_evento, $id_evento_code) {
        $date= date('Y-m-d', strtotime($this->fecha));
        
        reset($this->array_tematicas);
        foreach ($this->array_tematicas as $array) {
            if (isset($obj_matter)) unset($obj_matter);
            
            $obj_matter= new Ttematica($this->clink);
            $obj_matter->SetYear($this->year);
            $obj_matter->SetId($array['id']);
            $obj_matter->SetIdTematica($array['id']);
            
            $obj_matter->Set();
            
            $time= $obj_matter->GetFechaInicioPlan();
            $time= date('H:i:s', strtotime($time));
            
            $obj_matter->SetFechaInicioPlan("$date $time");
            $obj_matter->SetIdEvento($id_evento);
            $obj_matter->set_id_evento_code($id_evento_code);
            
            if ($this->control_list_copy > 0) {
                $id= $this->array_tematicas[$array['id']]['id_tematica'];
                $id_code= $this->array_tematicas[$array['id']]['id_tematica_code'];
            } else {
                $id= null;
                $id_code= null;
            }
            
            $obj_matter->this_copy($to_year, $id, $id_code);
            
            if (!empty($obj_matter->error) 
                && (stripos($obj_matter->error,'duplicate') !== false || stripos($obj_matter->error,'duplicada') !== false)) {
                $error.= $obj_matter->error."<br/>";
                continue;
            }    
            if ($this->control_list_copy == 0) {
                $this->array_tematicas[$array['id']]['id_tematica']= $obj_matter->GetId();
                $this->array_tematicas[$array['id']]['id_tematica_code']= $obj_matter->get_id_code();
            }
            $this->array_tematicas[$array['id']]['_id']= $obj_matter->GetId();
            $this->array_tematicas[$array['id']]['_id_code']= $obj_matter->get_id_code();
        }
        return $error;
    }
    
    private function _copy_reg_asistencias($id_evento, $id_evento_code) {
        reset($this->array_asistencias);
        foreach ($this->array_asistencias as $array) {
            $this->array_asistencias[$array['id']]['_id']= null;
            $this->array_asistencias[$array['id']]['_id_code']= null;
        }
        
        reset($this->array_asistencias);
        foreach ($this->array_asistencias as $array) {
            if (isset($obj_assist)) 
                unset($obj_assist);
            
            $obj_assist= new Tasistencia($this->clink);
            $obj_assist->SetYear($this->year);
            $obj_assist->SetId($array['id']);
            $obj_assist->SetIdAsistencia($array['id']);
            
            $obj_assist->Set();
            
            $obj_assist->this_copy($id_evento, $id_evento_code);
            
            $this->array_asistencias[$array['id']]['_id']= $obj_assist->GetId();
            $this->array_asistencias[$array['id']]['_id_code']= $obj_assist->get_id_code();            
            
            if (!empty($obj_assist->error)
                && (stripos($obj_assist->error,'duplicate') !== false || stripos($obj_assist->error,'duplicada') !== false))
                $error.= $obj_assist->error."<br/>";
        } 
        
        return $error;
    }
    
    public function _copy_reg($to_year= null, $id_evento= null, $id_evento_code= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $id_evento_code= !empty($id_evento) ? $id_evento_code : $this->id_evento_code;
        $to_year= !empty($to_year) ? $to_year : $this->year + 1;        
        
        $error= null;
        $error.= $this->_copy_reg_init($to_year, $id_evento, $id_evento_code);
        $error.= $this->_copy_reg_tematicas($to_year, $id_evento, $id_evento_code);
        $error.= $this->_copy_reg_asistencias($id_evento, $id_evento_code);
        
        $sql= null;
        reset($this->array_asistencias);
        foreach ($this->array_asistencias as $array) {
            if (empty($array['_id']))
                continue;
            $sql.= "update ttematicas set id_asistencia_resp = {$array['_id']}, id_asistencia_resp_code = '{$array['_id_code']}' ";
            $sql.= "where id_evento = $id_evento and id_asistencia_resp = {$array['id']}; ";
        }
        if ($sql)
            $this->do_multi_sql_show_error('_copy_reg (teventos)', $sql);

        if ($this->empresarial == 0)
            return;

        /* copia tinductor_eventos */
        $sql= null;
        reset($this->array_inductores);
        foreach ($this->array_inductores as $array) {
            $id_inductor= $array['id'];
            $id_inductor_code= setNULL_str($array['id_code']);
            $peso= setNULL($array['peso']);

            $sql= "insert into tinductor_eventos (id_evento, id_evento_code, id_inductor, id_inductor_code, ";
            $sql.= "peso, cronos, situs) values ($id_evento, $id_evento_code, $id_inductor, $id_inductor_code, ";
            $sql.= "$peso, '$this->cronos', '$this->location')";

            $this->do_sql_show_error('_copy_reg', $sql);
            if (!empty($this->error)
                && (stripos($this->clink->error,'duplicate') !== false || stripos($this->clink->error,'duplicada') !== false))
                $error.= $this->error."<br/>";
        }

        return $error;
    }
}

/*
 * Clases adjuntas o necesarias
 */
if (!class_exists('Ttipo_evento'))
    include_once "tipo_evento.class.php";
