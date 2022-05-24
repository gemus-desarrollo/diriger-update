<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

if (!class_exists('Tbase_evento'))
    include_once "base_evento.class.php";

class Ttarea extends Tbase_evento {
    protected $ifgrupo;
    protected $tipo;
    protected $reg_fecha;
    protected $chk_date;
    protected $id_tarea_grupo,
            $id_tarea_grupo_code; // grupo de tareas

    public $array_target_tareas,
            $array_source_tareas;

    private $array_dates;

    public function SetIfGrupo($id) {
        $this->ifgrupo = $id;
    }
    public function GetIfGrupo() {
        return $this->ifgrupo;
    }
    public function SetChkDate($id) {
        $this->chk_date = empty($id) ? 0 : 1;
    }
    public function GetChkDate() {
        return $this->chk_date;
    }
    public function SetIdTarea_grupo($id) {
        $this->id_tarea_grupo = !empty($id) ? $id : null;
    }
    public function set_id_tarea_grupo_code($id) {
        $this->id_tarea_grupo_code = $id;
    }
    public function GetIdTarea_grupo() {
        return $this->id_tarea_grupo;
    }
    public function get_id_tarea_grupo_code() {
        return $this->id_tarea_grupo_code;
    }

    public function __construct($clink = null) {
        $this->clink = $clink;
        Tbase_evento::__construct($clink);
        $this->ifgrupo = null;

        $this->className = 'Ttarea';
    }

    public function Set($id = null, $id_code = null) {
        if (!empty($id))
            $this->id_tarea = $id;
        if (empty($id) && !empty($id_code))
            $this->id_tarea_code = $id_code;

        $this->id = $this->id_tarea;

        $sql = "select * from ttareas where ";
        if (!empty($this->id_tarea))
            $sql.= "id = $this->id_tarea ";
        elseif (!empty($this->id_tarea_code))
            $sql.= "id_code= '$this->id_tarea_code' ";

        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->id_tarea = $row['id'];
            $this->id = $this->id_tarea;
            $this->id_code = $row['id_code'];
            $this->id_tarea_code = $this->id_code;

            $this->nombre = stripslashes($row['nombre']);
            $this->descripcion = stripslashes($row['descripcion']);
            $this->id_responsable = $row['id_responsable'];
            $this->id_usuario = $row['id_usuario'];

            $this->id_proyecto = $row['id_proyecto'];
            $this->id_proyecto_code = $row['id_proyecto_code'];

            $this->id_proceso = $row['id_proceso'];
            $this->id_proceso_code = $row['id_proceso_code'];

            $this->fecha_inicio_plan = $row['fecha_inicio_plan'];
            $this->fecha_fin_plan = $row['fecha_fin_plan'];
            $this->fecha_inicio_real = $row['fecha_inicio_real'];
            $this->fecha_fin_real = $row['fecha_fin_real'];
            $this->chk_date = boolean($row['chk_date']);

            $this->duracion_plan = $row['duracion_plan'];
            $this->duracion_real = $row['duracion_real'];

            $this->id_tarea_grupo = $row['id_tarea'];
            $this->id_tarea_grupo_code = $row['id_tarea_code'];
            $this->ifgrupo = boolean($row['ifgrupo']);
            $this->toshow_plan= $row['toshow'];

            $this->periodicidad = $row['periodicidad'];
            $this->carga = $row['carga'];
            $this->dayweek = $row['dayweek'];
            $this->sendmail = boolean($row['sendmail']);
            $this->sunday = boolean($row['sunday']);
            $this->saturday = boolean($row['saturday']);
            $this->freeday = boolean($row['freeday']);

            $this->origen_data = $row['origen_data'];
            $this->copyto = $row['copyto'];

            if (!empty($this->id_proyecto)) {
                $sql = "select * from tproyectos where id = $this->id_proyecto";
                $result = $this->do_sql_show_error('Set', $sql);
                $row = $this->clink->fetch_array($result);

                $this->id_programa = $row['id_programa'];
            }
        }

        return $this->error;
    }

    public function add($stop_by_error = true) {
        new Ttabla_anno($this->clink, date('Y', strtotime($this->fecha_inicio_plan)));

        $time = new TTime;
        $during = diffDate($this->fecha_inicio_plan, $this->fecha_fin_plan);
        $this->duracion_plan = "{$during['y']}-{$during['m']}-{$during['d']}";
        unset($time);

        $id_proyecto = setNULL($this->id_proyecto);
        $id_proyecto_code = setNULL_str($this->id_proyecto_code);

        if (empty($this->chk_date))
            $this->chk_date = 0;
        if (empty($this->id_grupo))
            $this->id_grupo = 0;

        $chk_date = boolean2pg($this->chk_date);

        $this->periodicidad = setNULL($this->periodicidad);
        $this->carga = setNULL($this->carga);
        $dayweek = setNULL_str($this->dayweek);
        $sendmail = boolean2pg($this->sendmail);

        $id_tarea_grupo = setNULL_empty($this->id_tarea_grupo);
        if (!empty($this->id_tarea_grupo))
            $this->id_tarea_grupo_code= get_code_from_table('ttareas', $this->id_tarea_grupo);
        $id_tarea_grupo_code = setNULL_str($this->id_tarea_grupo_code);

        $toshow= setNULL($this->toshow_plan);

        if (empty($this->saturday))
            $this->saturday = 0;
        if (empty($this->sunday))
            $this->sunday = 0;
        if (empty($this->freeday))
            $this->freeday = 0;

        $saturday = boolean2pg($this->saturday);
        $sunday = boolean2pg($this->sunday);
        $freeday = boolean2pg($this->freeday);

        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $ifgrupo = boolean2pg($this->ifgrupo);
        $ifassure = boolean2pg($this->ifassure);

        $sql = "insert into ttareas (nombre, id_responsable, id_usuario, id_proyecto, id_proyecto_code, ifgrupo, ";
        $sql.= "toshow, fecha_inicio_plan, fecha_fin_plan, chk_date, duracion_plan, descripcion, id_tarea, id_tarea_code, ";
        $sql.= "id_proceso, id_proceso_code, periodicidad, carga, dayweek, sendmail, saturday, sunday,freeday, cronos, ";
        $sql.= "situs, ifassure) values ($nombre, $this->id_responsable, $this->id_usuario, $id_proyecto, $id_proyecto_code, ";
        $sql.= "$ifgrupo, $toshow, '$this->fecha_inicio_plan', '$this->fecha_fin_plan', $chk_date, '$this->duracion_plan', ";
        $sql.= "$descripcion, $id_tarea_grupo, $id_tarea_grupo_code, {$_SESSION['id_entity']}, '{$_SESSION['id_entity_code']}', ";
        $sql.= "$this->periodicidad, $this->carga, $dayweek, $sendmail, $saturday, $sunday, $freeday, ";
        $sql.= "'$this->cronos', '$this->location', $ifassure) ";

        $result = $this->do_sql_show_error('add', $sql, $stop_by_error);

        if ($result) {
            $this->id = $this->clink->inserted_id("ttareas");
            $this->id_tarea = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('ttareas', 'id', 'id_code');
            $this->id_code = $this->obj_code->get_id_code();
            $this->id_tarea_code = $this->id_code;
        }

        return $this->error;
    }

    public function update($id= null) {
        new Ttabla_anno($this->clink, date('Y', strtotime($this->fecha_inicio_plan)));

        $id= !empty($id) ? $id : $this->id_tarea;
        $time = new TTime;
        $during = diffDate($this->fecha_inicio_plan, $this->fecha_fin_plan);
        $this->duracion_plan = $during['y'] . '-' . $during['m'] . '-' . $during['d'];

        $id_proyecto = setNULL_empty($this->id_proyecto);
        $id_proyecto_code = setNULL_str($this->id_proyecto_code);

        if (empty($this->chk_date))
            $this->chk_date = 0;
        $chk_date = boolean2pg($this->chk_date);

        $id_tarea_grupo = setNULL_empty($this->id_tarea_grupo);
        $id_tarea_grupo_code = setNULL_str($this->id_tarea_grupo_code);

        $this->periodicidad = setNULL($this->periodicidad);
        $this->carga = setNULL($this->carga);
        $dayweek = setNULL_str($this->dayweek);
        $sendmail = boolean2pg($this->sendmail);

        if (empty($this->saturday))
            $this->saturday = 0;
        if (empty($this->sunday))
            $this->sunday = 0;
        if (empty($this->freeday))
            $this->freeday = 0;

        $saturday = boolean2pg($this->saturday);
        $sunday = boolean2pg($this->sunday);
        $freeday = boolean2pg($this->freeday);

        $id_responsable_2= setNULL($this->id_responsable_2);
        $responsable_2_reg_date= setNULL_str($this->responsable_2_reg_date);

        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $ifgrupo = boolean2pg($this->ifgrupo);
        $ifassure = boolean2pg($this->ifassure);
        $toshow= setNULL($this->toshow_plan);

        $sql = "update ttareas set nombre= $nombre, descripcion= $descripcion, id_responsable= $this->id_responsable, ";
        $sql.= "id_tarea= $id_tarea_grupo, id_tarea_code= $id_tarea_grupo_code, ifassure= $ifassure, ";
        $sql.= "fecha_inicio_plan= '$this->fecha_inicio_plan', fecha_fin_plan= '$this->fecha_fin_plan', ";
        $sql.= "duracion_plan= '$this->duracion_plan', ifgrupo= $ifgrupo, toshow= $toshow, ";
        $sql.= "id_proyecto= $id_proyecto, id_proyecto_code= $id_proyecto_code, ";
        $sql.= "saturday= $saturday, sunday= $sunday, freeday= $freeday, chk_date= $chk_date, ";
        $sql.= "periodicidad= $this->periodicidad, carga= $this->carga, dayweek= $dayweek, sendmail= $sendmail, ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' ";
        if (!empty($this->id_responsable_2))
            $sql.= ", id_responsable_2= $id_responsable_2, responsable_2_reg_date= $responsable_2_reg_date ";
        $sql.= "where id = $id ";
        $result= $this->do_sql_show_error('update', $sql);

        unset($time);
        return $this->error;
    }

    public function listar($filter = true, $planning = false, $prs_list= null, $only_project= false) {
        $filter= !is_null($filter) ? $filter : true;
        $planning= !is_null($planning) ? $planning : false;
        $only_project= !empty($only_project) ? true : false;

        if ($filter) {
            if (!empty($this->year) && $filter)
                $this->date_interval($fecha_inicio, $fecha_fin);

            $fecha_fin= !empty($this->fecha_fin_plan) ? $this->fecha_fin_plan : $fecha_fin;
            $fecha_inicio= !empty($this->fecha_inicio_plan) ? $this->fecha_inicio_plan : $fecha_inicio;

            $fecha_inicio= date('Y-m-d', strtotime($fecha_inicio));
            $fecha_fin= date('Y-m-d', strtotime($fecha_fin));
        }

        $sql= "select distinct ttareas.*, ttareas.id as _id, ttareas.id_code as _id_code, ttareas.id_tarea as id_tarea_grupo, ";
        $sql.= "ttareas.id_tarea_code as id_tarea_grupo_code, tusuarios.nombre as responsable, ttareas.nombre as tarea ";
        $sql.= "from ttareas, tusuarios where id_responsable = tusuarios.id ";
        if (!empty($this->id_responsable))
            $sql.= "and id_responsable = $this->id_responsable ";
        if($only_project)
            $sql.= "and id_proyecto > 0 ";
        if (!empty($this->id_proyecto))
            $sql.= "and id_proyecto = $this->id_proyecto ";
        if (!empty($this->id_proceso))
            $sql.= "and ttareas.id_proceso = $this->id_proceso ";
        if (!empty($this->year) && $filter) {
            if ($planning) {
                $sql.= "and ((" . date2pg("fecha_inicio_plan") . " >= '$fecha_inicio' and " . date2pg("fecha_inicio_plan") . " <= '$fecha_fin') ";
                $sql.= "or (" . date2pg("fecha_fin_plan") . " >= '$fecha_inicio' and " . date2pg("fecha_fin_plan") . " <= '$fecha_fin')) ";
            }
            if (!$planning) {
                $sql.= "and ((" . date2pg("fecha_inicio_real") . " >= '$fecha_inicio' and  " . date2pg("fecha_inicio_real") . " <= '$fecha_fin') ";
                $sql.= "or (" . date2pg("fecha_fin_real") . " >= '$fecha_inicio' and " . date2pg("fecha_fin_real") . " <= '$fecha_fin')) ";
            }
        }
        if (!is_null($this->ifgrupo)) {
            if (!$this->ifgrupo)
                $sql.= "and (ifgrupo = false or ifgrupo is null) ";
            else
                $sql.= "and ifgrupo = true ";
        }

        $sql.= "order by ifgrupo desc, fecha_inicio_plan asc, ttareas.nombre asc ";

        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function listar_ifgrupo() {
        if (!empty($this->year))
            $this->date_interval($fecha_inicio, $fecha_fin);

        $fecha_fin= !empty($this->fecha_fin_plan) ? $this->fecha_fin_plan : $fecha_fin;
        $fecha_inicio= !empty($this->fecha_inicio_plan) ? $this->fecha_inicio_plan : $fecha_inicio;

        $fecha_inicio= date('Y-m-d', strtotime($fecha_inicio));
        $fecha_fin= date('Y-m-d', strtotime($fecha_fin));

        $sql= "select distinct ttareas.*, ttareas.id as _id, ttareas.id_code as _id_code, ttareas.id_tarea as id_tarea_grupo, ";
        $sql.= "ttareas.id_tarea_code as id_tarea_grupo_code, tusuarios.nombre as responsable, ttareas.nombre as tarea ";
        $sql.= "from ttareas, tusuarios where id_responsable = tusuarios.id and ifgrupo = true ";
        if (!empty($this->id_responsable))
            $sql.= "and (id_responsable = $this->id_responsable or id_usuario = $this->id_responsable) ";
        if (!empty($this->id_proyecto))
            $sql.= "and id_proyecto = $this->id_proyecto ";
        if (!empty($this->id_proceso))
            $sql.= "and ttareas.id_proceso = $this->id_proceso ";
        $sql.= "and (" . date2pg("fecha_fin_plan") . " > '$fecha_inicio' and " . date2pg("fecha_inicio_plan") . " < '$fecha_fin') ";
        $sql.= "order by ifgrupo desc, fecha_inicio_plan asc, ttareas.nombre asc ";

        $result = $this->do_sql_show_error('listar_ifgrupo', $sql);
        return $result;
    }

    public function listar_by_usuario($id_usuario = null, $flag = false) {
        $id_usuario = !empty($id_usuario) ? $id_usuario : $this->id_usuario;

        $sql = "select distinct ttareas.*, ttareas.id as _id from ttareas, treg_evento ";
        $sql.= "where treg_evento.id_tarea = ttareas.id and treg_evento.id_usuario = $id_usuario ";
        if (!empty($this->year))
            $sql.= "and (".year2pg("fecha_inicio_plan")." = $this->year or $this->year = ".year2pg("fecha_fin_plan").") ";
        $result = $this->do_sql_show_error('listar_by_usuario', $sql);

        if (!false)
            return $result;

        while ($row = $this->clink->fetch_array($result)) {
            $array = array('id' => $row['id'], 'id_code' => $row['id_code'], 'id=>id_responsable' => $row['id_responsable'],
                'fecha_inicio' => $row['fecha_inicio_plan'], 'fecha_fin' => $row['fecha_fin_plan'], 'peso' => $row['peso'],
                'nombre' => $row['nombre'], 'descripcion' => $row['descripcion'], 'cumplimiento' => null, 'array' => null);
            $this->array_tareas[$row['id']] = $array;
        }

        return $this->array_tareas;
    }

    public function get_riesgos($id_tarea= null, $id = null, $flag = false, $init_array = true) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $flag = !is_null($flag) ? $flag : false;
        $init_array= !is_null($init_array) ? $init_array : true;
        $string = null;

        if ($init_array)
            if (isset($this->array_riesgos)) 
                unset($this->array_riesgos);

        $sql = "select triesgos.*,id_tarea, id_tarea_code, nombre from triesgo_tareas, triesgos where id_riesgo is not null ";
        if (!empty($id_tarea))
            $sql.= "and id_tarea = $id_tarea ";
        $sql.= "and triesgos.id = triesgo_tareas.id_riesgo ";
        if (!empty($this->year))
            $sql.= "and YEAR(fecha_inicio_plan) = $this->year ";
        if (!empty($id)) 
            $sql.= "and id_riesgo = $id ";
        $result = $this->do_sql_show_error('get_riesgos', $sql);

        while ($row = $this->clink->fetch_array($result)) {
            $string.= 'RIESGO: ' . odbc2date($row['fecha_fin_plan']) . '  ' . $row['nombre'] . '\n';

            if ($flag) {
                $array = array('id' => $row['id'], 'id_code' => $row['id_code'], 'nombre'=>$row['nombre'],
                'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code']);
                $this->array_riesgos[$row['id']] = $array;
            }
        }
        return $string;
    }

    public function get_notas($id_tarea= null, $id = null, $flag = false, $init_array = true) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $flag = !is_null($flag) ? $flag : false;
        $init_array= !is_null($init_array) ? $init_array : true;
        $string = null;

        if ($init_array)
            if (isset($this->array_notas)) 
                unset($this->array_notas);

        $sql = "select tnotas.*,id_tarea, id_tarea_code, descripcion from triesgo_tareas, tnotas where id_nota is not null ";
        if (!empty($id_tarea))
            $sql.= "and id_tarea = $id_tarea ";
        $sql.= "and tnotas.id = triesgo_tareas.id_nota ";
        if (!empty($this->year))
            $sql.= "and YEAR(fecha_inicio_real) = $this->year ";        
        if (!empty($id))
            $sql.= "and id_nota = $id ";
        $result = $this->do_sql_show_error('get_notas', $sql);

        while ($row = $this->clink->fetch_array($result)) {
            if ($row['tipo'] == _NO_CONFORMIDAD)
                $tipo = "NO CONFORMIDAD";
            if ($row['tipo'] == _OBSERVACION)
                $tipo = "OBSERVACIÓN";
            if ($row['tipo'] == _OPORTUNIDAD)
                $tipo = "OPORTUNIDAD DE MEJORA";
            $string.= "HALLAZGO {$tipo}: ". odbc2date($row['fecha_fin_plan']) . '  ' . $row['nombre'] . '\n';

            if ($flag) {
                $array = array('id' => $row['id'], 'id_code' => $row['id_code'], 'nombre'=>$row['descripcion'],
                'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code']);
                $this->array_notas[$row['id']] = $array;
            }
        }
        return $string;
    }

    public function get_proyectos($id_tarea= null, $flag = false, $init_array = true) {
        $id_tarea= !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $flag = !is_null($flag) ? $flag : false;
        $init_array= !is_null($init_array) ? $init_array : true;
        $string = null;

        if ($init_array)
            if (isset($this->array_proyectos)) 
                unset($this->array_proyectos);

        $sql = "select tproyectos.*, tproyectos.nombre as _nombre, tproyectos.fecha_fin_plan as _fecha_fin_plan, ";
        $sql.= "tproyectos.id_responsable as _id_responsable, ttareas.id as id_tarea, ttareas.id_code as id_tarea_code ";
        $sql.= "from ttareas, tproyectos where 1 ";
        if (!empty($id_tarea))
            $sql.= "and ttareas.id = $id_tarea ";
        $sql.= "and ttareas.id_proyecto = tproyectos.id ";
        $result = $this->do_sql_show_error('get_proyectos', $sql);

        while ($row = $this->clink->fetch_array($result)) {
            $string.= 'PROYECTO: ' . odbc2date($row['_fecha_fin_plan']) . '  ' . $row['_nombre'] . '\n';

            if ($flag) {
                $array = array('id' => $row['id'], 'id_code' => $row['id_code'], 'nombre'=>$row['_nombre'],
                'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'], 
                'id_responsable'=>$row['_id_responsable']);
                $this->array_proyectos[$row['id']] = $array;
            }
        }
        return $string;
    }

    public function get_references() {
        $date= $this->year.'-'.str_pad($this->month, 2, '0', STR_PAD_LEFT).'-'.str_pad($this->day, 2, '0', STR_PAD_LEFT);
        $sql= "select * from triesgo_tareas where id_tarea = $this->id_tarea and date(cronos) >= '$date' order by cronos desc";
        $result = $this->do_sql_show_error('get_references', $sql);

        $array_tarea= array('id'=>$this->id_tarea, 'id_riesgo'=>null, 'id_nota'=>null, 'id_politica'=>null);
        $i= 0;
        while ($row = $this->clink->fetch_array($result)) {
            ++$i;
            if (empty($array_tarea['id_riesgo']))
                $array_tarea['id_riesgo']= $row['id_riesgo'];
            if (empty($array_tarea['id_nota']))
                $array_tarea['id_nota']= $row['id_nota'];
            if (empty($array_tarea['id_politica']))
                $array_tarea['id_politica']= $row['id_politica'];
        }

        return $i ? $array_tarea : null;
    }

    public function eliminar_if_empty($id_tarea = null) {
        $id_tarea = !empty($id_tarea) ? $id_tarea : $this->id_tarea;

        $sql = "select * from teventos where id_tarea = $id_tarea";
        $result = $this->do_sql_show_error('eliminar_if_empty', $sql);
        $this->cant = $this->clink->num_rows($result);

        if (empty($this->cant))
            $this->eliminar($id_tarea);
    }

    public function eliminar($id_tarea = null) {
        $id_tarea = !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $id_tarea_code = get_code_from_table('ttareas', $id_tarea);

        $sql= "select * from teventos where id_tarea = $id_tarea";
        $result= $this->do_sql_show_error('eliminar', $sql);

        $found= false;
        while ($row= $this->clink->fetch_array($result)) {
            $sql= "select ttematicas.id from ttematicas, tdebates where ttematicas.id_evento = {$row['id']} ";
            $sql.= "and ttematicas.id = tdebates.id_tematica ";
            $this->do_sql_show_error('_eliminar', $sql);
            if ($this->cant > 0) {
                $found= true;
                break;
            }
        }

        if ($found) {
            $this->error= "No se puede borrar la tarea. Una o mas actividades derivadas son reuniones realizadas con debates asociados ";
            $this->error.= "Primero debera eliminar la actividad con todas las tematicas y debates registrados.";
            return $this->error;
        }

        $this->clink->data_seek($result, 0);
        $sql= null;
        while ($row= $this->clink->fetch_array($result))
            $sql.= "delete from ttematicas where id_evento = {$row['id']};";
        $this->do_multi_sql_show_error('_eliminar', $sql);

        $sql = "delete from ttareas where id = $id_tarea";
        $result= $this->do_sql_show_error('eliminar', $sql);
        if ($result)
            $this->obj_code->reg_delete('ttareas', 'id_code', $id_tarea_code);

        return null;
    }

    public function set_fin_by_proyecto() {
        $descripcion = setNULL_str($this->descripcion);

        $sql = "update ttareas set fecha_fin_real= '$this->fecha_fin_real', cronos= '$this->cronos', ";
        $sql.= "situs= '$this->location' where id_proyecto = $this->id_proyecto ";
        $result= $this->do_sql_show_error('set_fin_by_proyecto', $sql);

        $sql = "update tproyectos set fecha_fin_real= '$this->fecha_fin_real', descripcion= $descripcion, ";
        $sql.= "cronos= '$this->cronos', situs= '$this->location' where id = $this->id_proyecto ";
        $result= $this->do_sql_show_error('set_fin_by_proyecto', $sql);

        return true;
    }

    public function init_list(){
        parent::init_list();
    }

    public function compute_from_eventos($id_tarea = null, $id_objetivo= null) {
        $id_tarea = !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        $total= 0;
        $ncumplidas= 0;

        $sql = "select distinct * from treg_evento_{$this->year} where id_tarea = $id_tarea ";
        if (!empty($this->id_responsable))
            $sql.= "and id_usuario = $this->id_responsable ";
        $sql.= "order by reg_fecha desc, cronos desc";
        $result = $this->do_sql_show_error('compute_from_eventos', $sql);
        if (empty($this->cant))
            return null;

        $array_ids= array();
        while ($row = $this->clink->fetch_array($result)) {
            if (empty($row['id_evento']))
                continue;
            if ($array_ids[$row['id_usuario']][$row['id_evento']])
                continue;
            $array_ids[$row['id_usuario']][$row['id_evento']]= 1;

            $cumplimiento= $row['cumplimiento'];
            $array= array('id'=>$row['id_evento'], 'id_evento'=>$row['id_evento'], 'id_tarea'=>$row['id_tarea'], 'id_objetivo'=>$id_objetivo,
                        'cumplimiento'=>$cumplimiento, 'aprobado'=>$row['aprobado'], 'rechazado'=>$row['rechazado'],
                        'id_responsable'=>$row['id_responsable'], 'observacion'=>$row['observacion'], 'cronos'=>$row['cronos']);
            $this->array_eventos[$row['id_evento']]= $array;

            ++$this->total;
            ++$total;
            if ($cumplimiento == _NO_INICIADO) {
                ++$this->no_iniciadas;
                $this->no_iniciadas_list[]= $array;
            }
            if ($cumplimiento == _COMPLETADO) {
                ++$this->cumplidas;
                $this->cumplidas_list[]= $array;
                ++$ncumplidas;
            }
            if ($cumplimiento == _INCUMPLIDO) {
                ++$this->incumplidas;
                $this->incumplidas_list[]= $array;
            }
            if ($cumplimiento == _POSPUESTO || $cumplimiento == _CANCELADO || $cumplimiento == _SUSPENDIDO) {
                ++$this->canceladas;
                $this->canceladas_list[]= $array;
            }
        }

        return $total > 0 ? ((float)$ncumplidas/$total)*100 : null;
    }

    public function compute_tarea($id_tarea = null) {
        $id_tarea = !empty($id_tarea) ? $id_tarea : $this->id_tarea;

        $sql = "select distinct * from treg_tarea where id_tarea = $id_tarea and planning = false ";
        if (!empty($this->id_responsable))
            $sql.= "and id_usuario = $this->id_responsable ";
        $sql.= "order by reg_fecha desc, cronos desc limit 1";
        $result= $this->do_sql_show_error('compute_from_eventos', $sql);
        $row= $this->clink->fetch_array($result);

        $array = array('valor'=>$row['value'], 'id_responsable' => $row['id_usuario'],
            'reg_fecha'=>$row['reg_fecha'], 'observacion'=>$row['observacion'], 'cronos'=>$row['cronos']);

        return $array;
    }

    private function set_copyto($id, $year, $id_code) {
        $this->copyto = "$year($id_code)-";
        $sql = "update ttareas set copyto= '$this->copyto' where id = $id ";
        $this->do_sql_show_error('set_copyto', $sql);
    }

    private function get_texttitle() {
        $texttitle= null;
        $plus= null;

        if (!empty($this->id_riesgo)) {
            $plus= $texttitle.= !empty($texttitle) ? ", " : null;
            $texttitle.= $plus."RIESGO";
        }
        if (!empty($this->id_nota)) {
            $plus= $texttitle.= !empty($texttitle) ? ", " : null;
            $texttitle.= $plus."NOTA";
        }
        if (!empty($this->id_proyecto)) {
            $plus= $texttitle.= !empty($texttitle) ? ", " : null;
            $texttitle.= $plus."PROYECTO";
        }
        if (!empty($this->id_programa)) {
            $plus= $texttitle.= !empty($texttitle) ? ", " : null;
            $texttitle.= $plus."PROGRAMA";
        }
        if (is_null($texttitle))
            $texttitle= "TAREA";

        return $texttitle;
    }

    public function this_copy($id_proceso = null, $id_proceso_code = null, $tipo = null,
                                                    $radio_prs = null, $to_year = null, $array_id= null) {
        $plus_year = empty($to_year) ? 1 : ($to_year - $this->year);
        if ($plus_year <= 0)
            $plus_year = 1;
        $to_year = !empty($to_year) ? $to_year : ($this->year + $plus_year);

        $id_proceso = !empty($id_proceso) ? $id_proceso : $_SESSION['local_proceso_id'];
        $id_proceso_code = !empty($id_proceso_code) ? $id_proceso_code : get_code_from_table('tprocesos', $id_proceso);
        $tipo = !empty($tipo) ? $tipo : $_SESSION['local_proceso_tipo'];

        $radio_prs = is_null($radio_prs) ? 2 : $radio_prs;

        $obj = $this->_this_copy();

        $obj->SetLink($this->clink);
        $obj->obj_code->SetLink($this->clink);
        $obj->SetId(null);
        $obj->SetIdTarea(null);
        $obj->set_id_code(null);
        $obj->set_id_tarea_code(null);

        $obj->SetIdProyecto(null);
        $obj->set_id_proyecto_code(null);

        $obj->SetOrigenData(null);
        $obj->SetIdProceso($id_proceso);
        $obj->set_id_proceso_code($id_proceso_code);
        $obj->SetIdUsuario($_SESSION['id_usuario']);

        $obj->set_id_responsable_2(null);
        $obj->set_responsable_2_reg_date(null);

        $obj->set_cronos($this->cronos);
        $obj->SetFechaInicioReal(null);
        $obj->SetFechaFinReal(null);

        $fecha = add_date($this->fecha_inicio_plan, 0, 0, $plus_year);
        $obj->SetFechaInicioPlan($fecha);
        $obj->SetFechaFinPlan(add_date($this->fecha_fin_plan, 0, 0, $plus_year));

        $this->id_evento = null;
        $this->id_proceso= $radio_prs == 2 ? null : $id_proceso;
        $this->id_entity= $radio_prs == 2 ? null : $this->id_entity;
        $this->listar_usuarios();
        $this->listar_grupos();

        $this->get_array_reg_usuarios();
        $this->get_array_reg_procesos($this->year, $id_proceso, $tipo);

        $this->get_array_planning_dates();

        if (is_null($array_id)) {
            $error = $obj->add(false);
            if (!is_null($error))
                return null;

            $id= $obj->GetId();
            $id_code= $obj->get_id_code();

            $this->set_copyto($this->id, $to_year, $id_code);
        } else {
            $id= $array_id['id'];
            $id_code= $array_id['id_code'];

            $obj->SetId($id);
            $obj->SetIdTarea($id);
            $obj->set_id_tarea_code($id_code);

            $this->error= $obj->update($id);
        }

        $this->_copy_event($obj, $array_id, $id, $id_code, $plus_year, $id_proceso, $id_proceso_code, $tipo);

        return array('id' => $id, 'id_code' => $id_code);
    }


    private function _copy_event($obj, $array_id, $id, $id_code, $plus_year, $id_proceso, $id_proceso_code, $tipo) {
        /*  creando el arraeglo de fechas para registrar los eventos */
        $obj_sched = new Tschedule;

        $obj_sched->SetFechaInicioPlan($obj->GetFechaInicioPlan());
        $obj_sched->SetFechaFinPlan($obj->GetFechaFinPlan());

        $obj_sched->SetPeriodicidad($this->periodicidad);
        $obj_sched->SetCarga($this->carga);
        $obj_sched->SetDayWeek($this->dayweek);
        $obj_sched->saturday = $this->saturday;
        $obj_sched->sunday = $this->sunday;
        $obj_sched->freeday = $this->freeday;

        if ($this->periodicidad == 3)
            $obj_sched->fixed_day = empty($this->dayweek) ? 0 : 1;

        if ($this->periodicidad == 4) {
            $this->get_child_events_by_table('ttareas', $this->id);

            foreach ($this->array_eventos as $evento) {
                $fecha = $evento['fecha_inicio_plan'];
                $obj_sched->input_array_dates[] = add_date($fecha, 0, 0, $plus_year);
            }
        }

        $obj_sched->set_dates();
        $obj_sched->create_array_dates();

        /* creando los eventos  */
        $this->id_proceso = $id_proceso;
        $this->id_proceso_code = $id_proceso_code;
        $this->tipo = $tipo;

        foreach ($obj_sched->array_dates as $date) {
            if (isset($obj_event)) unset($obj_event);
            $obj_event = new Tevento($this->clink);
            copy_tarea_to_evento($obj, $obj_event);

            $obj_event->SetLugar("Esta actividad se originó a partir de una ".$this->get_texttitle());

            $obj_event->set_null_periodicity();
            $obj_event->SetFechaInicioPlan($date['inicio']);
            $obj_event->SetFechaFinPlan($date['fin']);

            $obj_event->SetIdTarea($id);
            $obj_event->set_id_tarea_code($id_code);

            if (is_null($array_id)) {
                $error= $obj_event->add();

                $_id = $obj_event->GetId();
                $_id_code = $obj_event->get_id_code();
            }

            if (!is_null($array_id)
                    || (!is_null($error) && (stripos($error,'duplicate') !== false || stripos($error,'duplicada') !== false))) {

                $_array_id= $obj_event->find_evento($date['inicio'], null, null, $id);

                if (!is_null($_array_id)) {
                    $_id= $_array_id['id'];
                    $_id_code= $_array_id['id_code'];
                } else {
                    $error= $obj_event->add();

                    $_id = $obj_event->GetId();
                    $_id_code = $obj_event->get_id_code();
                }
            }

            $obj_event->array_usuarios = $this->array_usuarios;
            $obj_event->array_grupos = $this->array_grupos;
            $obj_event->array_reg_usuarios = $this->array_reg_usuarios;
            $obj_event->array_reg_procesos = $this->array_reg_procesos;
            $obj_event->array_inductores = null;

            $obj_event->_copy_reg($this->year + $plus_year, $_id, $_id_code);
        }
    }

    private function get_array_planning_dates() {
        $sql = "select reg_fecha, observacion, valor from treg_tarea where planning = " . boolean2pg(1) . " and id_tarea = $this->id_tarea ";
        $result = $this->do_sql_show_error('get_array_planning_dates', $sql);

        while ($row = $this->clink->fetch_array($result)) {
            $array = array('reg_fecha' => $row['reg_fecha'], 'observacion' => $row['reg_observacion'], 'valor' => $row['valor']);
            $this->array_dates[] = $array;
        }
        return $this->array_dates;
    }

    private function _copy_reg($id, $id_code) {
        $id_usuario = $_SESSION['id_usuario'];

        /* copiar los hitos de la tarea */
        foreach ($this->array_dates as $array) {
            $valor = setNULL($array['valor']);
            $observacion = setNULL_str($array['observacion'], false);
            $date = is_null($array['reg_fecha']) ? 'NULL' : "'" . add_date($array['reg_fecha'], 0, 0, 1) . "'";

            $sql = "insert into treg_tarea (id_tarea, id_tarea_code, id_usuario, reg_fecha, observacion, valor, cronos, situs)   ";
            $sql.= "values ($id, '$id_code', $id_usuario, $date, $observacion, $valor, '$this->cronos', '$this->location') ";
            $this->do_sql_show_error('_copy_reg', $sql);
        }
    }

    /*
     * target => Devuelven las tareas que influyen, que condicionan
     * source => devuelven las tarea que son determinadas que son condicionada por este id_tarea
     */
    public function setDependencies($id_depend, $id_depend_code = null, $tipo, $action = 'add', $_id_depend = null) {
        $_id_depend = !empty($_id_depend) ? $_id_depend : $id_depend;
        $id_depend_code = !empty($id_depend_code) ? $id_depend_code : get_code_from_table('ttareas', $id_depend);
        $this->id_code = !empty($this->id_code) ? $this->id_code : get_code_from_table('ttareas', $this->id);
        $tipo= setNULL_str($tipo);

        if ($action == 'delete') {
            $sql = "delete from ttarea_code where id_tarea = $this->id and id_depend = $id_depend ";
            $this->do_sql_show_error("setDependencies{$action}", $sql);
        }
        if ($action == 'add') {
            $sql = "insert into ttarea_tarea (id_tarea, id_tarea_code, id_depend, id_depend_code, tipo, cronos, situs) ";
            $sql.= "values ($this->id, '$this->id_code', $id_depend, '$id_depend_code', $tipo, '$this->cronos', ";
            $sql.= "'$this->location') ";
            $this->do_sql_show_error("setDependencies{$action}", $sql);
        }
        if ($action == 'update' || ($action == 'add' && !empty($this->error))) {
            $sql = "update ttarea_tarea set tipo= $tipo, id_depend= $id_depend, id_depend_code= '$id_depend_code' ";
            $sql.= "where id_tarea = $this->id ";
            if (!empty($_id_depend))
                $sql.= "and id_depend = $_id_depend ";
            $this->do_sql_show_error("setDependencies{$action}", $sql);
        }
    }
    /*
    * request: estado co condicion de la tarea identificada por el ID
    * source: devuelve las target. Todas las tareas que son condicionadas por este ID
    * target: devuelve todas las sources. Todas las tareas que condicionan a este ID
    */
    public function GetDependencies($id_tarea = null, $request = 'source') {
        $this->cant = 0;

        if ($request == 'source')
            $this->array_target_tareas= array();
        if ($request == 'target')
            $this->array_source_tareas= array();

        $id_tarea = !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        if (empty($id_tarea))
            return null;

        $sql = "select distinct ttareas.*, ttareas.id as _id, ttareas.id_code as _id_code, ttareas.id_tarea as id_tarea_grupo, ";
        $sql.= "ttareas.id_tarea_code as id_tarea_grupo_code, ttarea_tarea.tipo as tipo_depend from ttareas, ttarea_tarea ";
        if ($request == 'source')
            $sql.= "where (ttareas.id = ttarea_tarea.id_tarea and ttarea_tarea.id_depend = $id_tarea) ";
        if ($request == 'target')
            $sql.= "where (ttareas.id = ttarea_tarea.id_depend and ttarea_tarea.id_tarea = $id_tarea) ";
        $sql.= "order by ttareas.fecha_inicio_plan asc, ttareas.fecha_inicio_real asc ";
        $result = $this->do_sql_show_error('GetDependencies', $sql);

        $i = 0;
        while ($row = $this->clink->fetch_array($result)) {
            $fecha_inicio = !empty($row['fecha_inicio_real']) ? $row['fecha_inicio_real'] : $row['fecha_inicio_plan'];
            $fecha_fin = !empty($row['fecha_fin_real']) ? $row['fecha_fin_real'] : $row['fecha_fin_plan'];

            $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'nombre' => stripslashes($row['nombre']),
                'tipo_depend' => $row['tipo_depend'], 'fecha_inicio' => $fecha_inicio, 'fecha_fin' => $fecha_fin,
                'id_tarea_grupo' => $row['id_tarea_grupo'], 'id_tarea_grupo_code' => $row['id_tarea_grupo_code'],
                'tipo' => $row['tipo'],'descripcion' => stripslashes($row['descripcion']),
                'id_responsable' => $row['id_responsable'],'id_usuario_asigna' => $row['id_usuario'],
                'origen_data_asigna' => $row['origen_data'], 'id_proceso' => $row['id_proceso'], 'flag' => 0);

            if ($request == 'source')
                $this->array_target_tareas[++$i]= $array;
            if ($request == 'target')
                $this->array_source_tareas[++$i]= $array;
        }
        $this->cant = $i;

        if ($request == 'source')
            return $this->array_target_tareas;
        if ($request == 'target')
            return $this->array_source_tareas;
    }

    public function GetDependencies_string($id_tarea = null, $request = 'target') {
        $array_tareas= $this->GetDependencies($id_tarea, $request);
        $i= 0;
        $str= '';
        foreach ($array_tareas as $row) {
            if ($i > 0)
                $str.= ",";
            $str.= "{$row['id']}{$row['tipo_depend']}";
        }
        return $str;
    }

    public function listar_non_dependencies($id_tarea = null) {
        $id_tarea = !empty($id_tarea) ? $id_tarea : $this->id_tarea;
        if (empty($id_tarea))
            return $this->listar(true, true);

        $sql = "drop view if exists _tdepend";
        $this->do_sql_show_error('listar_non_dependencies', $sql);

        $sql = "create view _tdepend as ";
        $sql.= "select distinct id_depend as _id from ttarea_tarea where id_tarea = $id_tarea ";
        $this->do_sql_show_error('listar_non_dependencies', $sql);

        $sql = "select distinct ttareas.*, ttareas.id as _id, ttareas.id_code as _id_code, ";
        $sql.= "ttareas.id_tarea as id_tarea_grupo, ttareas.id_tarea_code as id_tarea_grupo_code ";
        $sql.= "from ttareas where (ifgrupo is null or ifgrupo = false) ";
        if (!empty($this->id_proyecto))
            $sql.= "and id_proyecto = $this->id_proyecto ";
        $sql.= "and id not in (select distinct _id from _tdepend) ";

        $result = $this->do_sql_show_error('listar_non_dependencies', $sql);
        $cant = $this->clink->num_rows($result);

        $sql = "drop view _tdepend";
        $this->do_sql_show_error('listar_non_dependencies', $sql);

        $this->cant = --$cant;
        return $result;
    }

}

/**
 * para la copia de los objetos, se copia de la tarea al evento
 */
function copy_tarea_to_evento(/* Ttarea */ $obj1, /* Tevento */ $obj2) {
    $obj2->SetNombre($obj1->GetNombre(), false);
    $obj2->SetLugar($obj1->GetLugar());
    $obj2->SetDescripcion($obj1->GetDescripcion());
    $obj2->SetIfAssure($obj1->GetIfAssure());

    $obj2->SetIdUsuario($obj1->GetIdUsuario());
    $obj2->SetIdProceso($obj1->GetIdProceso());
    $obj2->set_id_proceso_code($obj1->get_id_proceso_code());

    $obj2->SetIdResponsable($obj1->GetIdResponsable());
    $obj2->set_id_responsable_2($obj1->get_id_responsable_2());

    $obj2->SetFechaInicioPlan($obj1->GetFechaInicioPlan());
    $obj2->SetFechaFinPlan($obj1->GetFechaFinPlan());

    $obj2->SetCarga($obj1->GetCarga());
    $obj2->SetPeriodicidad($obj1->GetPeriodicidad());
    $obj2->SetDayWeek($obj1->GetDayWeek());

    $obj2->saturday = $obj1->saturday;
    $obj2->sunday = $obj1->sunday;
    $obj2->freeday = $obj1->freeday;

    $obj2->SetSendMail($obj1->GetSendMail());

    $obj2->SetIfEmpresarial($obj1->GetIfEmpresarial());
    $obj2->toshow = $obj1->toshow;
    $obj2->set_toshow_plan($obj1->get_toshow_plan());

    $obj2->SetIdTarea($obj1->GetIdTarea());
    $obj2->set_id_tarea_code($obj1->get_id_tarea_code());

    $obj2->SetIdAuditoria($obj1->GetIdAuditoria());
    $obj2->set_id_auditoria_code($obj1->get_id_auditoria_code());

    $obj2->SetYear($obj1->GetYear());

    foreach ($obj1->array_eventos as $key => $array) {
        $obj2->array_eventos[$key] = $array;
    }
}


/*
 * Clases adjuntas o necesarias
 */

include_once "code.class.php";
include_once "time.class.php";

if (!class_exists('Tscheduler'))
    include_once "schedule.class.php";
if (!class_exists('Tplanning'))
    include_once "planning.class.php";
if (!class_exists('Tevento'))
    include_once "evento.class.php";