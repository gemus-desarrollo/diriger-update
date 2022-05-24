<?php
/**
 * Description of base_evento
 *
 * @author mustelier
 */

if (!class_exists('Tplanning'))
    include_once "planning.class.php";

class Tbase_evento extends Tplanning {
    protected $funcionario;
    protected $obj_reg;
    protected $obj_tables;

    public $like_name;

    public function SetFuncionario($id) {
        $this->funcionario = $id;
    }
    public function GetFuncionario() {
        return $this->funcionario;
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tplanning::__construct($clink);

        $this->obj_reg= new Tregister_planning($this->clink);
        $this->obj_tables= new Ttmp_tables_planning($this->clink);
    }

    public function GetNumero() {
        if (empty($this->year))
            $this->year= !empty($this->fecha_inicio_plan) ? date('Y', strtotime($this->fecha_inicio_plan)) : date('Y');

        return !empty($this->numero) ? $this->numero : $this->find_numero($this->year, $this->empresarial);
    }

    private function _update_empresarial($date, $id_evento, $toshow, $sign, $ref_code, $id_proceso= null) {
        $id_proceso= !is_null($id_proceso) ? $id_proceso : $this->id_proceso;
        $table= $_SESSION["_DB_SYSTEM"] == "mysql" ? "tproceso_eventos_{$this->year}." : "";

        $sql= "update tproceso_eventos_{$this->year}";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? ", teventos " : " ";
        $sql.= "set {$table}toshow= $toshow, {$table}id_usuario= {$_SESSION['id_usuario']}, {$table}cronos= '$this->cronos', ";
        $sql.= "{$table}situs= '$this->location' ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "" : "from teventos ";

        if ($ref_code)
            $sql.= "where (teventos.id_evento = $id_evento or teventos.id = $id_evento) ";
        else
            $sql.= "where teventos.id = $id_evento ";

        $sql.= "and ".date2pg("fecha_inicio_plan")." $sign '$date' and tproceso_eventos_{$this->year}.id_evento = teventos.id ";
        if (!empty($id_proceso))
            $sql.= "and tproceso_eventos_{$this->year}.id_proceso = $id_proceso ";
        $result= $this->do_sql_show_error('_update_empresarial', $sql);
    }

    public function update_empresarial($date, $id, $id_code, $toshow, $user_plan= 0, $flag_date= 0, $flag_prs= 0, $ref_code) {
        $sign= empty($flag_date) ? " = " : " >= ";
        $obj_prs= new Tproceso($this->clink);
        $this->if_tusuarios= false;

        $this->copy_in_object($this->obj_tables);
        if ($user_plan)
            $this->obj_tables->add_to_tmp_tusuarios($this->id_proceso);

        if ($flag_prs) {
            $obj_prs->get_procesos_down($this->id_proceso, _TIPO_DEPARTAMENTO);
            foreach ($obj_prs->array_cascade_down as $array)
                $this->obj_tables->add_to_tmp_tusuarios($array['id']);
        }

        $this->_update_empresarial($date, $id, $toshow, $sign, $ref_code, $this->id_proceso);

        if ($flag_prs) {
            reset($obj_prs->array_cascade_down);
            foreach ($obj_prs->array_cascade_down as $array)
                $this->_update_empresarial($date, $id, $toshow, $sign, $ref_code, $array['id']);
        }

        if ($user_plan)
            $this->delete_reg($date, $id, null, $flag_date, $ref_code);
    }

    protected function get_aprobado($id_evento) {
        $treg_evento= ($this->if_treg_evento) ? "_treg_evento" : "treg_evento_{$this->year}";

        $sql= "select nombre, email, cargo, $treg_evento.cronos as fecha from $treg_evento, tusuarios ";
        $sql.= "where $treg_evento.id_responsable = tusuarios.id and aprobado is not null ";
        $sql.= "and $treg_evento.id_evento= $id_evento order by $treg_evento.cronos asc limit 1";
        $result= $this->do_sql_show_error('Teventos::get_aprobado', $sql);
        $row= $this->clink->fetch_array($result);

        return $row;
    }
    
    protected function get_aprobado_prs($id_evento) {
        $tproceso_eventos= ($this->if_tproceso_eventos) ? "_tproceso_eventos" : "tproceso_eventos_{$this->year}";

        $sql= "select nombre, email, cargo, _tproceso_eventos.cronos as fecha from _tproceso_eventos, tusuarios ";
        $sql.= "where $tproceso_eventos.id_usuario = tusuarios.id and aprobado is not null ";
        $sql.= "and $tproceso_eventos.id_evento= $id_evento order by $tproceso_eventos.cronos asc limit 1";
        $result= $this->do_sql_show_error('Teventos::get_aprobado', $sql);
        $row= $this->clink->fetch_array($result);

        return $row;
    }

    public function find_numero($year= null, $empresarial, $id_tipo_evento= null) {
        $year= !empty($year) ? $year : $this->year;

        $sql= "select max(numero) as _numero from teventos where ".year2pg("fecha_inicio_plan")." = $year ";
        if (!empty($empresarial))
            $sql.= "and empresarial = $empresarial ";
        if (!empty($id_tipo_evento))
            $sql.= "and id_tipo_evento = $id_tipo_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        $result= $this->do_sql_show_error('find_numero', $sql);
        $row= $this->clink->fetch_array($result);

        $numero= !empty($row['_numero']) ? (int)$row['_numero'] : 0;
        return ++$numero;
    }

    public function find_numero_by_id($id_evento= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;

        $sql= "select numero from teventos where id = $id_evento";
        $result= $this->do_sql_show_error('find_numero_by_id', $sql);
        $row= $this->clink->fetch_array($result);

        $numero= !empty($row['numero']) ? (int)$row['numero'] : null;
        $numero_plus= !empty($row['numero_plus']) ? $row['numero_plus'] : null;

        return array($numero, $numero_plus);
    }

    // solo actualizar numero
    public function numering($id_evento, $numero) {
        $sql= "update teventos set numero= $numero where id = $id_evento; ";
        $this->do_sql_show_error('_copy_reg', $sql);
    }

    /*
     * valida si la reunion ya sucedio
     * tiene asistencia => true
     * tiene acuerdo => true
     */
    public function get_if_fixed($id_evento= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $sql= "select count(*) as _count from tasistencias where id_evento = $id_evento";
        $result= $this->do_sql_show_error('get_if_fixed', $sql);
        $row= $this->clink->fetch_array($result);
        $assist= $row[0];

        $sql= "select count(*) as _count from ttematicas where id_evento = $id_evento and ifaccords = ".boolean2pg(1);
        $result= $this->do_sql_show_error('get_if_fixed', $sql);
        $row= $this->clink->fetch_array($result);
        $matter= $row[0];

        return $assist && $matter ? true : false;
    }

    public function update_reg($action, $go_delete= _DELETE_YES, $id_responsable= null, $multi_query= false) {
        $obj_reg= new Tregister_planning($this->clink);
        $this->copy_in_object($obj_reg);
        $obj_reg->array_evento_data= clone_array($this->array_evento_data);

        $sql= $obj_reg->update_reg($action, $go_delete, $id_responsable, $multi_query);
        $this->array_evento_data= clone_array($obj_reg->array_evento_data);

        return $multi_query ? $sql : null;
    }

    public function get_array_procesos_meeting($id_usuario) {
        $sql= "select distinct tproceso_eventos_{$this->year}.id_proceso as _id from teventos, tproceso_eventos_{$this->year} ";
        $sql.= "where teventos.id_secretary = $id_usuario and tproceso_eventos_{$this->year}.id_evento = teventos.id ";
        $result= $this->do_sql_show_error('get_array_procesos_meeting', $sql);
        
        $obj_prs= new Tproceso($this->clink);
        $array_procesos= null;
        while ($row= $this->clink->fetch_array($result)) {
            $obj_prs->Set($row['_id']);
            $array= array('id'=>$row['_id'], 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(),
                    'tipo'=>$obj_prs->GetTipo(), 'lugar'=>$obj_prs->GetLugar(), 'descripcion'=>null,
                    'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado(),
                    'codigo'=>$obj_prs->GetCodigo(), 'id_proceso'=>$obj_prs->GetIdProceso_sup(),
                    'inicio'=> $obj_prs->GetInicio(), 'fin'=>$obj_prs->GetFin());
            $array_procesos[$row['_id']]= $array;        
        }

        return $array_procesos;
    }

    public function if_participant($id_evento= null, $id_usuario= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $id_usuario= !empty($id_usuario) ? $id_usuario : $this->id_usuario;
        if (empty($id_usuario))
            $id_usuario= $_SESSION['id_usuario'];

        return $this->get_usuarios_array_from_evento($id_evento, null, null, $id_usuario);
    }

    public function get_usuarios_array_from_evento($id_evento= null, $year= null, $set_name= false, $id_usuario= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $year= !empty($year) ? $year : $this->year;
        $set_name= !is_null($set_name) ? $set_name : false;
        $array_usuarios= array();

        $sql= "select distinct id_usuario, user_check, toshow from treg_evento_{$year} where 1 ";
        if (!empty($id_evento))
            $sql.= "and id_evento = $id_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";
        if (!empty($this->id_tarea))
            $sql.= "and id_tarea = $this->id_tarea ";
        if (!empty($id_usuario))
            $sql.= "and (id_usuario = $id_usuario && toshow = true) ";
        $sql.= "order by cronos desc ";
        if (!empty($id_usuario))
            $sql.= "limit 1";
        $result= $this->do_sql_show_error('get_user_array_from_teventos', $sql);
        $cant= $this->clink->num_rows($result);

        if ($id_usuario)
            return $cant > 0 ? true : false; 

        $obj_user= new Tusuario($this->clink);
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($array_ids[$row['id_usuario']]))
                continue;
            $array_ids[$row['id_usuario']]= 1;
            $nombre= null;
            $cargo= null;

            if ($set_name) {
                $obj_user->Set($row['id_usuario']);
                $nombre= $obj_user->GetNombre();
                $cargo= $obj_user->GetCargo();
            }
            $array_usuarios[$row['id_usuario']]= array('id_usuario'=>$row['id_usuario'], 'user_check'=>$row['user_check'],
                            'id'=>$row['id_usuario'], 'nombre'=>$nombre, 'cargo'=>$cargo);
        }
        return $array_usuarios;
    }

    public function get_usuarios_array_from_tusuario_eventos($id= null, $year= null, $funct= null) {
        $id = !empty($id) ? $id : $this->id_evento;
        $year= !empty($year) ? $year : $this->year;
        $funct= !empty($funct) ? $funct : 'evento';

        $array_usuarios= array();
        $obj_grupos= new Tgrupo($this->clink);

        $result= $this->get_participante_grupos($id, $year, 'evento', false);
        while ($row= $this->clink->fetch_array($result)) {
            $obj_grupos->SetIdGrupo($row['id_grupo']);
            $_array_usuarios= $obj_grupos->listar_usuarios();
            $array_usuarios= array_merge_overwrite($array_usuarios, $_array_usuarios);
        }

        $result= $this->get_participantes_usuarios($id, $year);
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                    'eliminado'=>$row['eliminado'], 'usuario'=>$row['usuario'], 'cronos'=>$row['_cronos'], '_id'=>$row['_id'],
                    'id_proceso'=>$row['id_proceso'], 'id_proceso_code'=>$row['id_proceso_code'], 'nivel'=>$row['nivel'],
                    'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'], 'id_auditoria'=>$row['id_auditoria'],
                    'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'],
                    'id_tematica'=>$row['tematica'], 'id_tematica_code'=>$row['id_tematica_code']);

            $array_usuarios[$row['_id']]= $array;
        }

        return $array_usuarios;
    }

    public function get_participante_grupos($id, $year, $funct, $fix_entity= true) {
        $sql= "select distinct nombre, tgrupos.id_entity as _id_proceso_user, id_grupo ";
        $sql.= "from tgrupos, tusuario_eventos_$year where tgrupos.id = tusuario_eventos_$year.id_grupo ";
        $sql.= "and tusuario_eventos_$year.id_$funct = $id ";
        if ($fix_entity)
            $sql.= "and tgrupos.id_entity = {$_SESSION['id_entity']}";

        $result= $this->do_sql_show_error('get_participante_grupos', $sql);
        return $result;
    }

    public function get_participantes_usuarios($id, $year, $funct= 'evento', $id_responsable= null, $user_ref_date= null) {
        $user_ref_date= !empty($user_ref_date) ? $user_ref_date : $this->user_date_ref;

        $sql= "select distinct nombre, cargo, id_proceso as _id_proceso, tusuarios.id_proceso as _id_proceso_user, ";
        $sql.= "tusuarios.id as _id from tusuarios, tusuario_eventos_$year where tusuarios.id = tusuario_eventos_$year.id_usuario ";
        $sql.= "and tusuario_eventos_$year.id_$funct = $id and (eliminado is null or eliminado >= '$user_ref_date')";
        if (!is_null($id_responsable))
            $sql.= "and id_usuario <> $id_responsable ";

        $result= $this->do_sql_show_error('get_participantes_usuarios', $sql);
        return $result;
    }

    public function get_participantes($id= null, $funct= 'evento', $id_responsable= null, $id_proceso= null, $id_proceso_users= null) {
        global $config;

        $funct= !is_null($funct) ? $funct : 'evento';
        $array= array();
        $id= !empty($id) ? $id : $this->id_evento;
        $j= 0;
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;

        if (empty($this->array_procesos && !empty($id_proceso))) {
            $this->set_procesos();
        }

        $this->inicio= $this->inicio ? $this->inicio : $this->year;
        $this->fin= $this->fin ? $this->fin : $this->year;

        $user_ref_date= $this->year."-".str_pad($this->month,2,'0',STR_PAD_LEFT)."-".str_pad($this->day,2,'0',STR_PAD_LEFT);
        $user_ref_date= !empty($this->user_date_ref) ? $this->user_date_ref : $user_ref_date;

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            $result= $this->get_participante_grupos($id, $year, $funct);

            while ($row= $this->clink->fetch_array($result)) {
                if (!array_key_exists($row['_id_proceso_user'], $this->array_procesos))
                    continue;
                $item= textparse($row['nombre']);
                if (array_search($item, $array) === false)
                    $array[$j++]= $item;
        }   }

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            $result= $this->get_participantes_usuarios($id, $year, $funct, $id_responsable, $user_ref_date);

            while ($row= $this->clink->fetch_array($result)) {
                if (!empty($this->array_procesos) && !array_key_exists($row['_id_proceso_user'], $this->array_procesos))
                    continue;
                if (!empty($id_proceso_users) && ($id_proceso_users != $_SESSION['id_entity'])) {
                    if ($row['_id_proceso'] != $id_proceso_users)
                        continue;
                }
                if ((!empty($id_proceso) && $id_proceso != $_SESSION['id_entity']) && $row['_id_proceso_user'] != $id_proceso)
                    continue;

                if ($config->onlypost)
                    $item= !empty($row['cargo']) ? textparse($row['cargo']) : textparse($row['nombre']);
                else {
                    $item= textparse($row['nombre']);
                    $item.= !empty($row['cargo']) ? " (".textparse($row['cargo']).")" : null;
                }

                if (array_search($item, $array) === false)
                    $array[$j++]= $item;
        }   }

        $this->cant= $j;
        return implode(", ", $array);
    }

    private function filter_register_result($result) {
        $array_register= null;
        $array0= array();

        while($row= $this->clink->fetch_array($result)) {
            $_array= array('id_usuario'=>$row['id_usuario'], 'cumplimiento'=>$row['cumplimiento'], 
                        'observacion'=>$row['observacion'], 'id_responsable'=>$row['id_responsable'], 
                        'aprobado'=>$row['aprobado'], 'responsable'=>$row['responsable']);

            $array= array('id'=>$row['_id'], 'id_usuario'=>$row['id_usuario'], 'cumplimiento'=>$row['cumplimiento'], 
                        'observacion'=>$row['observacion'], 'id_responsable'=>$row['id_responsable'], 
                        'aprobado'=>$row['aprobado'], 'responsable'=>$row['responsable'], 'cronos'=>$row['cronos']);

            if($_array === $array0)
                continue;
            $array_register[]= $array;
            $array0= $_array;
        }
        return $array_register;
    }

    public function listEvento_reg($id_evento, $flag= false, $id_responsable= null, $filter= false) {
        $filter= !is_null($filter) ? $filter : false;

        $sql= "select distinct treg_evento_{$this->year}.*, treg_evento_{$this->year}.id as _id, ";
        $sql.= "treg_evento_{$this->year}.origen_data as _origen_data, tusuarios.nombre as responsable, ";
        $sql.= "id_usuario, id_responsable from treg_evento_{$this->year}, tusuarios ";
        if (!empty($id_evento) && empty($this->id_tarea))
            $sql.= " where id_evento = $id_evento "; 
        if (!empty($this->id_tarea) && empty($id_evento))
            $sql.= "where id_tarea = $this->id_tarea ";    
        $sql.= "and (tusuarios.id = treg_evento_{$this->year}.id_responsable ";
        $sql.= "or (treg_evento_{$this->year}.id_responsable is null and treg_evento_{$this->year}.origen_data is not null)) ";
        if (!empty($this->id_usuario) && empty($id_responsable))
            $sql.= "and id_usuario = $this->id_usuario ";
        if (!empty($this->cumplimiento))
            $sql.= "and cumplimiento = $this->cumplimiento ";
        if (!empty($id_responsable))
            $sql.= "and id_usuario= $id_responsable ";
        if (!empty($this->reg_fecha))
            $sql.= "and ".date2pg("cronos")." <= '$this->reg_fecha' ";
        $sql.= "order by treg_evento_{$this->year}.cronos desc ";

        $result= $this->do_sql_show_error('listEvento_reg', $sql);

        if($filter)
            return $this->filter_register_result($result);
        if ($flag)
            return $result;

        if ($this->clink->fetch_array($result) > 0)
            return $this->clink->fetch_array($result);
        else
            return null;
    }

    public function listEvento_reg_proceso($id_evento= null, $flag= false, $id_responsable= null, $filter= false) {
        $filter= !is_null($filter) ? $filter : false;
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        
        $sql= "select distinct tproceso_eventos_{$this->year}.*, tproceso_eventos_{$this->year}.id as _id, ";
        $sql.= "tproceso_eventos_{$this->year}.origen_data as _origen_data, ";
        $sql.= "tusuarios.nombre as responsable, id_usuario, id_responsable from tproceso_eventos_{$this->year}, ";
        $sql.= "tusuarios where 1 ";
        if (!empty($id_evento))
            $sql.= "and id_evento = $id_evento ";
        if (!empty($this->id_auditoria))
            $sql.= "and id_auditoria = $this->id_auditoria ";     
        $sql.= "and (tusuarios.id = tproceso_eventos_{$this->year}.id_responsable ";
        $sql.= "or (tproceso_eventos_{$this->year}.id_responsable is null and tproceso_eventos_{$this->year}.origen_data is not null)) ";
        if (!empty($this->id_usuario) && empty($id_responsable))
            $sql.= "and id_usuario = $this->id_usuario ";
        if (!empty($this->cumplimiento))
            $sql.= "and cumplimiento = $this->cumplimiento ";
        if (!empty($id_responsable))
            $sql.= "and tproceso_eventos_{$this->year}.id_usuario= $id_responsable ";
        if (!empty($this->reg_fecha))
            $sql.= "and ".date2pg("cronos")." <= '$this->reg_fecha' ";
        $sql.= "order by tproceso_eventos_{$this->year}.cronos desc ";

        $result= $this->do_sql_show_error('listEvento_reg', $sql);

        if($filter)
            return $this->filter_register_result($result);        
        if ($flag)
            return $result;

        if ($this->clink->fetch_array($result) > 0)
            return $this->clink->fetch_array($result);
        else
            return null;
    }

    /*
    *   date  = "A" ==>Solo a esta actividad
        date  = "U" ==>A esta misma Actividad siempre que aparezca en el mes ...
        date  = "D" ==>A todas las actividades de este DíA ...
        date  = "S" ==>A todas las actividades de la SEMANA ...
        date  = "M" ==>A todas las actividades del MES ...
        date  = "Y" ==>A todas las actividades del AÑO ...
     */

    public function get_eventos_by_period($date, $period, $id_evento= null, $strict_daily= false, 
                                                        $with_task_added= false, $all_month= true) {
        $strict_daily= !is_null($strict_daily) ? $strict_daily : false;
        $with_task_added= !is_null($with_task_added) ? $with_task_added : false;
        $all_month= !is_null($all_month) ? $all_month : true;

        if (isset($this->array_eventos)) unset($this->array_eventos);

        $obj_reg= null;
        if (!empty($this->id_usuario) || !empty($this->id_proceso)) {
            $obj_reg= new Tregister_planning($this->clink);
            $obj_reg->SetYear($this->year);
        }

        if (!empty($period)) {
             $array_date= getDateInterval($date, $period, $all_month);
             $fecha_inicio= $array_date['inicio'];
             $fecha_fin= $array_date['fin'];
        } else {
            $fecha_inicio= $this->fecha_inicio_plan;
            $fecha_fin= $this->fecha_fin_plan;
        }

       $sql= "select distinct id as _id_evento, id_code, id_tarea as _id_tarea, id_tarea_code as _id_tarea_code, ";
       $sql.= "id_auditoria as _id_auditoria, id_auditoria_code as _id_auditoria_code, id_tematica as _id_tematica, ";
       $sql.= "id_tematica_code as _id_tematica_code, id_responsable as _id_responsable, nombre, lugar, descripcion, ";
       $sql.= "fecha_inicio_plan, fecha_fin_plan, lugar, id_archivo, id_archivo_code, id_proceso, id_proceso_code, ";
       $sql.= "empresarial, id_tipo_reunion, id_tipo_evento, teventos.id_usuario as _id_user_asigna, toshow as toshow_plan ";
       $sql.= "from teventos where (date(fecha_inicio_plan) >= '$fecha_inicio' and date(fecha_fin_plan) <= '$fecha_fin') ";
       if (!empty($this->empresarial))
           $sql.= "and (empresarial is not null and empresarial > 0) ";
       if (!empty($this->id_auditoria))
           $sql.= "and id_auditoria = $this->id_auditoria ";
       if (!empty($this->id_tarea))
           $sql.= "and id_tarea = $this->id_tarea ";
       if (!empty($id_evento))
           $sql.= "and (id = $id_evento or id_evento = $id_evento) ";
       if ($strict_daily)
           $sql.= "and ((periodicidad is null or periodicidad = 0) and (carga is null or carga = 0) and dayweek is null) ";

       $result= $this->do_sql_show_error('get_eventos_by_period', $sql);

       $obj_task= new Ttarea($this->clink);
       $array_ids= array();
       while ($row= $this->clink->fetch_array($result)) {
            if (!empty($array_ids[$row['_id_evento']]))
                continue;
            $array_ids[$row['_id_evento']]= 1;

            $id_proyecto= null;
            if (!empty($row['id_tarea'])) {
                $obj_task->Set($row['id_tarea']);
                $id_proyecto= $obj_task->GetIdProyecto();
            }

            $rowcmp= null;
            if (!empty($this->id_usuario) && empty($this->id_proceso)) {
                $obj_reg->SetIdUsuario($this->id_usuario);
                $rowcmp= $obj_reg->getEvento_reg($row['_id_evento']);
            }
            if (empty($this->id_usuario) && !empty($this->id_proceso)) {
                $obj_reg->SetIdProceso($this->id_proceso);
                $rowcmp= $obj_reg->get_reg_proceso($row['_id_evento']);
            }
            if (empty($rowcmp))
                continue;

            $array= array('id'=>$row['_id_evento'], 'id_code'=>$row['id_code'], 'id_usuario'=> $this->id_usuario,
                'id_responsable'=>$row['_id_responsable'], 'id_tarea'=>$row['_id_tarea'], 'id_tarea_code'=>$row['_id_tarea_code'],
                'id_auditoria'=>$row['_id_auditoria'], 'id_auditoria_code'=>$row['_id_auditoria_code'],
                'id_tematica'=>$row['_id_tematica'], 'id_tematica_code'=>$row['_id_tematica_code'], 'evento'=>$row['nombre'],
                'lugar'=>$row['lugar'], 'descripcion'=>$row['descripcion'], 'fecha_inicio'=>$row['fecha_inicio_plan'],
                'fecha_fin'=>$row['fecha_fin_plan'], 'id_archivo'=>$row['id_archivo'],
                'id_archivo_code'=>$row['id_archivo_code'], 'id_proceso'=>$row['id_proceso'],
                'id_proceso_code'=>$row['id_proceso_code'], 'ifmeeting'=>$row['id_tipo_reunion'], 'toshow'=>$row['toshow_plan'], 
                'rechazado'=>$rowcmp['rechazado'], 'aprobado'=>$rowcmp['aprobado'], 'cumplimiento'=>$rowcmp['cumplimiento'],
                'id_nota'=>null, 'id_nota_code'=>null, 'id_riesgo'=>null, 'id_riesgo_code'=>null,
                'ifaccords'=>null, 'id_proyecto'=>$id_proyecto, 'id_proyecto_code'=>null,
                'id_user_asigna'=>$row['_id_user_asigna'], 'outlook'=>boolean($rowcmp['outlook']), 'flag'=>1);
            $this->array_eventos[]= $array;
       }

       return $this->array_eventos;
    }

    public function get_auditoria_by_period($date, $period, $id_auditoria= null, $strict_daily= false, $with_task_added= false) {
       $strict_daily= !is_null($strict_daily) ? $strict_daily : false;
       $with_task_added= !is_null($with_task_added) ? $with_task_added : false;

       if (isset($this->array_auditorias)) unset($this->array_auditorias);

       $array_date= getDateInterval($date, $period);
       $fecha_inicio= $array_date['inicio'];
       $fecha_fin= $array_date['fin'];

        $sql= "select *, id_usuario as _id_user_asigna, toshow as toshow_plan from tauditorias ";
        $sql.= "where (date(fecha_inicio_plan) >= '$fecha_inicio' and date(fecha_fin_plan) <= '$fecha_fin') ";
        if (!empty($id_auditoria))
            $sql.= "and (id = $id_auditoria or id_auditoria = $id_auditoria) ";
       if ($strict_daily)
           $sql.= "and ((periodicidad is null or periodicidad = 0) and (carga is null or carga = 0) and dayweek is null) ";

        $result= $this->do_sql_show_error('get_event_by_period', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_usuario'=>$row['_id_usuario'],
                'id_responsable'=>$row['_id_responsable'], 'id_auditoria'=>$row['_id_auditoria'],
                'id_auditoria_code'=>$row['_id_auditoria_code'], 'rechazado'=>$row['rechazado'],
                'evento'=>$row['nombre'], 'lugar'=>$row['lugar'], 'descripcion'=>$row['descripcion'],
                'fecha_inicio'=>$row['fecha_inicio_plan'], 'fecha_fin'=>$row['fecha_fin_plan'],
                'id_proceso'=>$row['id_proceso'], 'id_proceso_code'=>$row['id_proceso_code'], 
                'id_user_asigna'=>$row['_id_user_asigna'], 'toshow'=>$row['toshow_plan']);
            $this->array_auditorias[]= $array;
        }

        return $this->array_auditorias;
    }

    public function find_evento($fecha, $id_evento= null, $id_auditoria= null, $id_tarea= null) {
        if (empty($fecha))
            return null;

        $table= "teventos";
        switch ($this->className) {
            case 'Tevento':
                $table= "teventos";
                break;
            case 'Tauditoria':
                $table= "tauditorias";
                break;
            case 'Ttarea':
                $table= "ttareas";
                break;
            default:
                $table= "teventos";
        }

        $sql= "select id, id_code from $table where fecha_inicio_plan = '$fecha' and id_proceso = $this->id_proceso ";
        if (!empty($id_evento))
            $sql.= "and id_evento = $id_evento ";
        if (!empty($id_auditoria))
            $sql.= "and id_auditoria = $id_auditoria ";
        if (!empty($id_tarea))
            $sql.= "and id_tarea = $id_tarea ";

        $result= $this->do_sql_show_error('find_evento', $sql);
        if ($this->cant == 0 || $this->cant == -1)
            return null;

        $row= $this->clink->fetch_array($result);
        return $row;
    }

    public function lista_parent_eventos($year= null) {
        $year= !empty($year) ? $year : $this->year;

        $table= "teventos";
        $id_field= "id_evento";

        switch ($this->className) {
            case 'Tevento':
                $table= "teventos";
                $id_field= "id_evento";
                break;
            case 'Tauditoria':
                $table= "tauditorias";
                $id_field= "id_auditoria";
                break;
            default:
                $table= "teventos";
        }

        $array_id_field= array();
        $sql= "select id from $table where (year(fecha_inicio_plan) = $year and $id_field is null)";
        $result= $this->do_sql_show_error('lista_parent_eventos_1', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $array_id_field[$row['id']]= $row['id'];
        }

        $array_id_dist= array();
        $sql= "select distinct $id_field from $table where year(fecha_inicio_plan) = $year and $id_field is not null";
        $result= $this->do_sql_show_error('lista_parent_eventos_2', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if (array_key_exists($row[$id_field], $array_id_field))
                continue;
            $array_id_dist[$row[$id_field]]= $row[$id_field];
        }

        foreach ($array_id_dist as $id => $row) {
            $sql= "select id from teventos where $id_field = $id";
            $result= $this->do_sql_show_error('lista_parent_eventos_3', $sql);

            while ($row= $this->clink->fetch_array($result)) {
                ++$i;
                $array_id_field[$row['id']]= $row['id'];
            }
        }

        if ($i == 0)
            return null;

        return $array_id_field;
    }

    public function update_responsable($table= "teventos", $id_responsable, $id= null) {
        $id= !empty($id) ? $id : $this->id;
        $sql= "update $table set id_responsable= $id_responsable, id_responsable_2= $this->id_responsable, ";
        $sql.= "responsable_2_reg_date= '$this->cronos', cronos= '$this->cronos', situs= '$this->location' where id = $id";
        $result= $this->do_sql_show_error('update_responsable', $sql);
    }

    protected function _copy_evento_usuarios($to_year, $id_evento= null, $id_evento_code= null,
                                            $id_auditoria= null, $id_auditoria_code= null, $id_tarea= null, $id_tarea_code= null) {
        $error= null;
        $id_evento= setNULL(!empty($id_evento) ? $id_evento : $this->id_evento);
        $id_evento_code= setNULL_str(!empty($id_evento_code) ? $id_evento_code : $this->id_evento_code);

        $id_tarea= setNULL(!empty($id_tarea) ? $id_tarea : $this->id_tarea);
        $id_tarea_code= setNULL_str(!empty($id_tarea_code) ? $id_tarea_code : $this->id_tarea_code);

        $id_auditoria= setNULL(!empty($id_auditoria) ? $id_auditoria : $this->id_auditoria);
        $id_auditoria_code= setNULL_str(!empty($id_auditoria_code) ? $id_auditoria_code : $this->id_auditoria_code);

        reset($this->array_usuarios);
        foreach ($this->array_usuarios as $array) {
            $id_usuario= setNULL($array['id']);
            $indirect= setNULL_empty(boolean2pg($array['indirect']));

            $sql= "insert into tusuario_eventos_$to_year (id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
            $sql.= "id_auditoria_code, id_usuario, id_grupo, indirect, cronos, situs) values ($id_evento, $id_evento_code, ";
            $sql.= "$id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, $id_usuario, null, $indirect, ";
            $sql.= "'$this->cronos', '$this->location')";

            $this->do_sql_show_error('_copy_evento_usuarios', $sql);
            if (!empty($this->error)
                && (stripos($this->clink->error,'duplicate') !== false || stripos($this->clink->error,'duplicada') !== false))
                $error.= $this->error."<br/>";
        }

        reset($this->array_grupos);
        foreach ($this->array_grupos as $array) {
            $id_grupo= setNULL($array['id']);

            $sql= "insert into tusuario_eventos_$to_year (id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
            $sql.= "id_auditoria_code, id_usuario, id_grupo, indirect, cronos, situs) values ($id_evento, $id_evento_code, ";
            $sql.= "$id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, null, $id_grupo, null, ";
            $sql.= "'$this->cronos', '$this->location')";

            $this->do_sql_show_error('_copy_evento_usuarios', $sql);
            if (!empty($this->error)
                && (stripos($this->clink->error,'duplicate') !== false || stripos($this->clink->error,'duplicada') !== false))
                $error.= $this->error."<br/>";
        }
        return $error;
    }

    protected function _copy_reg_usuarios($to_year, $id_evento= null, $id_evento_code= null,
                                            $id_auditoria= null, $id_auditoria_code= null, $id_tarea= null, $id_tarea_code= null) {
        $error= null;
        $id_responsable= $_SESSION['id_usuario'];

        $id_evento= setNULL(!empty($id_evento) ? $id_evento : $this->id_evento);
        $id_evento_code= setNULL_str(!empty($id_evento_code) ? $id_evento_code : $this->id_evento_code);

        $id_tarea= setNULL(!empty($id_tarea) ? $id_tarea : $this->id_tarea);
        $id_tarea_code= setNULL_str(!empty($id_tarea_code) ? $id_tarea_code : $this->id_tarea_code);

        $id_auditoria= setNULL(!empty($id_auditoria) ? $id_auditoria : $this->id_auditoria);
        $id_auditoria_code= setNULL_str(!empty($id_auditoria_code) ? $id_auditoria_code : $this->id_auditoria_code);

        reset($this->array_reg_usuarios);
        foreach ($this->array_reg_usuarios as $array) {
            $id_usuario= $array['id'];
            $user_check= boolean2pg($array['user_check']);

            $sql= "insert into treg_evento_$to_year (id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
            $sql.= "id_auditoria_code, id_usuario, id_responsable, user_check, cumplimiento, cronos, situs) values ";
            $sql.= "($id_evento, $id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, ";
            $sql.= "$id_usuario, $id_responsable, $user_check, "._NO_INICIADO.", '$this->cronos', '$this->location')";

            $this->do_sql_show_error('_copy_reg_usuarios', $sql);
            if (!empty($this->error)
                && (stripos($this->clink->error,'duplicate') !== false || stripos($this->clink->error,'duplicada') !== false))
                $error.= $this->error."<br/>";
        }
        return $error;
    }

    protected function _copy_proceso($to_year, $id_evento= null, $id_evento_code= null,
                                        $id_auditoria= null, $id_auditoria_code= null, $id_tarea= null, $id_tarea_code= null) {
        $error= null;
        $obj_tipo= new Ttipo_evento($this->clink);
        $obj_tipo->SetYear($to_year);

        $id_evento= setNULL(!empty($id_evento) ? $id_evento : $this->id_evento);
        $id_evento_code= setNULL_str(!empty($id_evento_code) ? $id_evento_code : $this->id_evento_code);

        $id_tarea= setNULL(!empty($id_tarea) ? $id_tarea : $this->id_tarea);
        $id_tarea_code= setNULL_str(!empty($id_tarea_code) ? $id_tarea_code : $this->id_tarea_code);

        $id_auditoria= setNULL(!empty($id_auditoria) ? $id_auditoria : $this->id_auditoria);
        $id_auditoria_code= setNULL_str(!empty($id_auditoria_code) ? $id_auditoria_code : $this->id_auditoria_code);

        reset($this->array_reg_procesos);
        foreach ($this->array_reg_procesos as $array) {
            $id_proceso= $array['id'];
            $id_proceso_code= setNULL_str($array['id_code']);
            $toshow= setNULL($array['toshow']);
            $id_usuario= _USER_SYSTEM;

            $empresarial= setNULL($array['empresarial']);
            $id_tipo_evento= setNULL($array['id_tipo_evento']);
            $id_tipo_evento_code= setNULL_str($array['id_tipo_evento_code']);
            $indice= setNULL_empty($array['indice']);
            $indice_plus= setNULL_empty($array['indice_plus']);
            $id_responsable= $array['id_responsable'];

            if (!$obj_tipo->if_valid_tipo_evento($array['id_tipo_evento'])) {
                $id_tipo_evento= setNULL(null);
                $id_tipo_evento_code= setNULL_str(null);
                $indice= setNULL($empresarial * pow(10,6));
                $indice_plus= setNULL(null);
            }

            $sql= "insert into tproceso_eventos_$to_year (id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, ";
            $sql.= "id_auditoria_code, id_proceso, id_proceso_code, id_responsable, cumplimiento, toshow, empresarial, ";
            $sql.= "id_tipo_evento, id_tipo_evento_code, indice, indice_plus, id_usuario, cronos, situs) values ($id_evento, ";
            $sql.= "$id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, $id_proceso, $id_proceso_code, ";
            $sql.= "$id_responsable, "._NO_INICIADO.", $toshow, $empresarial, $id_tipo_evento, $id_tipo_evento_code, ";
            $sql.= "$indice, $indice_plus, $id_usuario, '$this->cronos', '$this->location')";

            $this->do_sql_show_error('_copy_proceso', $sql);
            if (!empty($this->error)
                && (stripos($this->clink->error,'duplicate') !== false || stripos($this->clink->error,'duplicada') !== false))
                $error.= $this->error."<br/>";
        }

        return $error;
    }
    
    public function find_evento_duplicated() {
        $sql= "select * from teventos where lower(nombre) = '".strtolower($this->nombre)."' and id_proceso_code = '$this->id_proceso_code' ";
        $sql.= "and lower(lugar) = '".strtolower($this->lugar)."' ";
        $sql.= "and fecha_inicio_plan = '$this->fecha_inicio_plan' and fecha_fin_plan = '$this->fecha_fin_plan' ";
        $result= $this->do_sql_show_error('find_evento_duplicated', $sql);
        $row= $this->clink->fetch_array($result);
        
        return $row['id'];
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";
include_once "code.class.php";

if (!class_exists('Ttmp_tables_planning'))
    include_once "tmp_tables_planning.class.php";
if (!class_exists('Tregister_planning'))
    include_once "register_planning.class.php";

include_once "tabla_anno.class.php";