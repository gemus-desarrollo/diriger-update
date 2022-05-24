<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 8/6/15
 * Time: 7:53 a.m.
 */

if (!class_exists('Tasistencia'))
    include_once "asistencia.class.php";

class Ttematica extends Tasistencia {
    private $if_ttematicas;

    private $id_tematica_ref;
    private $id_tematica_ref_code;

    private $id_evento_accords;
    private $id_evento_accords_code;

    private $id_asistencia_resp;
    private $id_asistencia_resp_code;
    public $array_accords;
    private $if_tmp_meeting;

    private $nums_width_debate;

    public $array_attached_results;

    public function set_id_tematica_ref_code($id) {
        $this->id_tematica_ref_code = $id;
    }
    public function get_id_tematica_ref_code() {
        return $this->id_tematica_ref_code;
    }
    public function set_id_evento_accords($id) {
        $this->id_evento_accords = $id;
    }
    public function get_id_evento_accords() {
        return $this->id_evento_accords;
    }
    public function set_id_evento_accords_code($id) {
        $this->id_evento_accords_code = $id;
    }
    public function get_id_evento_accords_code() {
        return $this->id_evento_accords_code;
    }
    public function SetIdAsistencia_resp($id) {
        $this->id_asistencia_resp = $id;
    }
    public function GetIdAsistencia_resp() {
        return $this->id_asistencia_resp;
    }
    public function set_id_asistencia_resp_code($id) {
        $this->id_asistencia_resp_code = $id;
    }
    public function get_id_asistencia_resp_code() {
        return $this->id_asistencia_resp_code;
    }
    public function get_nums_width_debate() {
        return $this->nums_width_debate;
    }

    public function __construct($clink= null) {
        $this->id_tipo_reunion= null;
        $this->accords= null;
        $this->if_tmp_meeting= false;

        Tasistencia::__construct($clink);
        $this->clink= $clink;
    }

    public function Set($id= null) {
        $id= !empty($id) ? $id : $this->id_tematica;

        $sql= "select * from ttematicas where id = $id";
        $result= $this->do_sql_show_error('Set', $sql);

        $row= $this->clink->fetch_array($result);
        $this->id_tematica= $row['id'];
        $this->id_code= $row['id_code'];
        $this->id_tematica_code= $this->id_code;

        $this->ifaccords= boolean($row['ifaccords']);
        $this->numero= $row['numero'];
        $this->descripcion= $row['descripcion'];

        $this->id_asistencia_resp= $row['id_asistencia_resp'];
        $this->id_asistencia_resp_code= $row['id_asistencia_resp_code'];

        $this->origen_data= $row['origen_data'];

        $this->id_tematica_ref= $row['id_tematica'];
        $this->id_tematica_ref_code= $row['id_tematica_code'];

        $this->id_evento= $row['id_evento'];
        $this->id_evento_code= $row['id_evento_code'];

        $this->id_evento_accords= $row['id_evento_accords'];
        $this->id_evento_accords_code= $row['id_evento_accords_code'];

        $this->fecha_inicio_plan= $row['fecha_inicio_plan'];

        $this->cumplimiento= $row['cumplimiento'];
        $this->evaluado= $row['evaluado'];
        $this->evaluacion= $row['evaluacion'];
        $this->id_responsable_eval= $row['id_responsable_eval'];
    }

    public function add($id_tematica= null, $id_tematica_code= null) {
        $cumplimiento= setNULL($this->cumplimiento);
        $ifaccords= boolean2pg($this->ifaccords);

        $id_reponsable_eval= setNULL($this->id_responsable_eval);
        $evaluado= setNULL_str($this->evaluado);
        $evaluacion= setNULL_str($this->evaluacion);

        $descripcion= setNULL_str($this->descripcion);

        $id_tematica= setNULL($id_tematica);
        $id_tematica_code= setNULL_str($id_tematica_code);
        $numero= setNULL($this->numero);
        
        $id_copyfrom= setNULL($this->id_copyfrom);
        $id_copyfrom_code= setNULL_str($this->id_copyfrom_code);

        $sql= "insert into ttematicas (numero, descripcion, fecha_inicio_plan, id_evento, id_evento_code, id_tematica, ";
        $sql.= "id_tematica_code, id_asistencia_resp, id_asistencia_resp_code, ifaccords, cumplimiento, id_responsable_eval, ";
        $sql.= "evaluado, evaluacion, id_copyfrom, id_copyfrom_code, cronos, situs) values ($numero, $descripcion, ";
        $sql.= "'$this->fecha_inicio_plan', $this->id_evento, '$this->id_evento_code', $id_tematica, $id_tematica_code, ";
        $sql.= "$this->id_asistencia_resp, '$this->id_asistencia_resp_code', $ifaccords, $cumplimiento, $id_reponsable_eval, ";
        $sql.= "$evaluado, $evaluacion, $id_copyfrom, $id_copyfrom_code, '$this->cronos', '$this->location')";
        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
             $this->id= $this->clink->inserted_id("ttematicas");
             $this->id_tematica= $this->id;

             $this->obj_code->SetId($this->id);
             $this->obj_code->set_code('ttematicas','id','id_code');
             $this->id_code= $this->obj_code->get_id_code();
             $this->id_tematica_code= $this->id_code;
        }

        return $this->error;
    }

    public function update($id_tematica= null, $only_cump= false) {
        $id_tematica= !empty($id_tematica) ? $id_tematica : $this->id_tematica;
        $cumplimiento= setNULL($this->cumplimiento);

        $id_reponsable_eval= setNULL($this->id_responsable_eval);
        $evaluado= setNULL_str($this->evaluado);
        $evaluacion= setNULL_str($this->evaluacion);
        $numero= setNULL($this->numero);

        $descripcion= setNULL_str($this->descripcion);

        $sql= "update ttematicas set ";
        if (!$only_cump) {
            $sql.= "descripcion= $descripcion, fecha_inicio_plan= '$this->fecha_inicio_plan', numero= $numero, ";
            $sql.= "id_asistencia_resp= $this->id_asistencia_resp, id_asistencia_resp_code= '$this->id_asistencia_resp_code', ";
        }
        $sql.= "cumplimiento= $cumplimiento, id_responsable_eval= $id_reponsable_eval, evaluado= $evaluado, ";
        $sql.= "evaluacion= $evaluacion, cronos= '$this->cronos', situs= '$this->location' ";
        if ((!empty($this->id_proceso) || !empty($this->id_evento)) && empty($id_tematica)) {
            $sql.= "where 1 ";
            if (!empty($this->id_tematica_ref))
                $sql.= "and id_tematica = $this->id_tematica_ref ";
            if (!empty($this->id_evento))
                $sql.= "and id_evento = $this->id_evento ";
            if (!empty($this->id_proceso))
                $sql.= "and id_proceso = $this->id_proceso ";
        } else
            $sql.= "where id = $id_tematica ";

        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    /**
     * @param $id correponde al id del evento construido despues de acuerdo. El evento es un acuerdo
     * @param $id_code
     */
    public function set_evento_accords($id, $id_code) {
        $this->id_evento_accords= $id;
        $this->id_evento_accords_code= $id_code;

        $sql= "update ttematicas set id_evento_accords= $id, id_evento_accords_code= '$id_code' ";
        $sql.= "where id = $this->id_tematica";
        $result= $this->do_sql_show_error('update', $sql);
    }

    public function update_cump_matter($id_tematica = null, $only_cump = true) {
        Ttematica::update($id_tematica, $only_cump);
    }

    public function eliminar($id= null) {
        $id= !empty($id) ? $id : $this->id_tematica;

        $sql= "delete from ttematicas where 1 ";
        if (!empty($id))
            $sql.= "and id = $id ";
        if (!is_null($this->ifaccords))
            $sql.= $this->ifaccords ? "and ifaccords = true " : "and (ifaccords = false or ifaccords is null) ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_evento ";
        $result= $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
    }

    public function listar($ifaccords= false, $year_evento= null, $order_by_date= false) {
        $ifaccords= !is_null($ifaccords) ? $ifaccords : false;
        $ttematicas= $this->if_ttematicas ? '_cttematicas' : 'ttematicas';

        $sql= "select distinct $ttematicas.*, numero, $ttematicas.descripcion as _nombre, ";
        $sql.= "$ttematicas.id as _id, $ttematicas.fecha_inicio_plan as _fecha_inicio_plan, ".month2pg("$ttematicas.fecha_inicio_plan")." ";
        $sql.= "as _month, ".day2pg("$ttematicas.fecha_inicio_plan")." as _day from $ttematicas where ";
        $sql.= $ifaccords ? "ifaccords = true " : "(ifaccords = false or ifaccords is null) ";

        $sql_select= null;
        if (!empty($this->id_evento) || !empty($this->id_tipo_reunion) || !empty($year_evento)) {
            $sql_select= "($ttematicas.id_evento in (select id from teventos where 1 ";
            if (!empty($year_evento))
                $sql_select.= "and year(teventos.fecha_inicio_plan) = $year_evento ";
            if (!empty($this->id_tipo_reunion))
                $sql_select.= "and teventos.id_tipo_reunion = $this->id_tipo_reunion ";
            if (!empty($this->id_evento))
                $sql_select.= "and (teventos.id = $this->id_evento or teventos.id_evento = $this->id_evento) ";
            $sql_select.= ")) ";
        }

        $sql_year= null;
        if (!empty($this->year) || !empty($this->month))
            $sql_year= "(".year2pg("$ttematicas.fecha_inicio_plan")." = $this->year ";
        if (!empty($this->month))
            $sql_year.= "and ".month2pg("$ttematicas.fecha_inicio_plan")." = $this->month ";
        if (!empty($this->year) || !empty($this->month))
            $sql_year.= ") ";

        if ($sql_select && is_null($sql_year))
            $sql.= "and $sql_select";
        if (is_null($sql_select) && $sql_year)
            $sql.= "and $sql_year";
        if ($sql_select && $sql_year)
            $sql.= "and ($sql_select and $sql_year) ";

        if (!empty($this->id_asistencia_resp))
            $sql.= "and $ttematicas.id_asistencia_resp= $this->id_asistencia_resp ";
        $sql.= !$order_by_date ? "order by numero asc, cronos asc" : "order by fecha_inicio_plan asc, cronos asc";

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function list_tematicas() {
        global $dayNames;

        $obj_evento= new Tevento($this->clink);

        $result= $this->listar();
        if (isset($this->array_tematicas)) 
            unset($this->array_tematicas);
        $this->array_tematicas= array();

        $array_id_tematicas= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($row['id_tematica']))
                $array_id_tematicas[(int)$row['id_tematica']]= $row['id_tematica'];
        }

        $this->clink->data_seek($result);
        while ($row= $this->clink->fetch_array($result)) {
            reset($this->array_tematicas);
            $found= false;
            $_id= null;
            $array_month= array();

            if (!empty($row['id_tematica'])) {
                $_id= $row['id_tematica'];
                foreach ($this->array_tematicas as $id => $array) {
                    if ($id == $row['id_tematica']) {
                        $_id= $id;
                        $found= true;
                        break;
                }   }
            } else {
                $_id= $row['id'];
                foreach ($this->array_tematicas as $id => $array) {
                    if ($id == $row['id']) {
                        $_id= $id;
                        $found= true;
                        break;
            }   }   }

            /*
             * En el Plan tematico no se muestran las tematicas de las reuniones que estan registradas como suspendidas
             * o rechazadas
             */
            $obj_evento->SetIdUsuario(null);
            $obj_evento->Set($row['id_evento']);
            if (!empty($obj_evento->GetRechazado()))
                continue;
            $value= $obj_evento->GetCumplimiento();
            if (empty($value) || ($value == _SUSPENDIDO || $value == _CANCELADO || $value == _POSPUESTO || $value == _DELEGADO))
                continue;

            if (!$found) {
                for ($i= 1; $i < 13; $i++) {
                    if ($_id != $row['id'] || ((int)$_id == (int)$row['id'] && !array_key_exists((int)$row['id'], $array_id_tematicas))) {
                        if ($i == (int)$row['_month'])
                            $array_month[$i][] = array('id' => $row['id'], 'numero' => $row['numero'], 'day' => $row['_day'],
                                                    'time' => odbc2ampm($row['_fecha_inicio_plan']),
                                                    'weekday' => $dayNames[(int)date('N', strtotime($row['_fecha_inicio_plan']))]);
                        else
                            $array_month[$i] = null;
                }   }
                $this->array_tematicas[$_id]= array('id'=>$_id, 'numero'=>$row['numero'], 'nombre'=>$row['_nombre'], 'time'=>$row['_fecha_inicio_plan'],
                                               'id_proceso'=>$row['id_proceso'], 'id_asistencia_resp'=>$row['id_asistencia_resp'], 'array_month'=>$array_month,
                                                'cant_debates'=>0);
            }
            else {
                if ($_id != $row['id'] || ((int)$_id == (int)$row['id'] && !array_key_exists((int)$row['id'], $array_id_tematicas))) {
                    $this->array_tematicas[$_id]['array_month'][(int)$row['_month']][]= array('id'=>$row['id'], 'numero'=>$row['numero'],
                                'day'=>$row['_day'], 'time'=>odbc2ampm($row['_fecha_inicio_plan']),
                                'weekday'=>$dayNames[(int)date('N', strtotime($row['_fecha_inicio_plan']))]);
        }   }   }

        reset($this->array_tematicas);
        return $this->array_tematicas;
    }

    /*
     * marca las tematicas que tienen debates y/o acuerdos asociados
     */
    public function fix_debates() {
        $this->nums_width_debate= 0;

        foreach ($this->array_tematicas as $index => $row) {
            $sql= "select * from tdebates where id_tematica = {$row['id']} ";
            $result= $this->do_sql_show_error('fix_debates', $sql);

            $this->array_tematicas[$index]['cant_debates']= $this->cant;
            if ($this->cant > 0 || !empty($row['id_accords']))
                ++$this->nums_width_debate;
        }

        reset($this->array_tematicas);
        return $this->nums_width_debate;
    }

    public function get_tematicas_by_tematica($id_tematica= null) {
        $id= !empty($id_tematica) ? $id_tematica : $this->id_tematica;

        $sql= "select * from ttematicas where id_tematica = $id ";
        return $this->_get_tematicas($sql);
    }

    public function get_tematicas_by_evento($id_evento= null) {
        $id= !empty($id_evento) ? $id_evento : $this->id_evento;

        $sql= "select * from ttematicas where id_evento = $id ";

        $this->_get_tematicas($sql);
        $this->fix_debates();
        return $this->array_tematicas;
    }

    private function _get_tematicas($sql) {
        if (isset($this->array_tematicas)) unset($this->array_tematicas);
        $this->array_tematicas= array();

        $result= $this->do_sql_show_error('_get_tematicas', $sql);
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'fecha_inicio_plan'=>$row['fecha_inicio_plan'],
                'nombre'=>$row['descripcion'], 'id_responsable'=>$row['id_responsable'], 'id_accords'=>$row['id_evento_accords'],
                'cant_debates'=>0);
            $this->array_tematicas[$row['id']]= $array;
        }
        $this->cant= $i;
        return $this->array_tematicas;
    }

    public function move_tematica($id_evento=null, $fecha= null, $id_evento_code= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $id_evento_code= !empty($id_evento_code) ? $id_evento_code : get_code_from_table('teventos', $id_evento, $this->clink);

        $sql= "update ttamaticas set id_evento= $id_evento, id_evento_code= '$id_evento_code', fecha_inicio_plan= '$fecha' ";
        $sql.= "where id = $this->id";
        $result= $this->do_sql_show_error('move_tematica', $sql);
        return $this->error;
    }

    public function find_max_numero_accords($id_evento) {
        $sql= "select id_evento from teventos where id = $id_evento";
        $result= $this->do_sql_show_error('find_max_numero_accords', $sql);
        $row= $this->clink->fetch_array($result);

        $id_evento= !empty($row['id_evento']) ? $row['id_evento'] : $id_evento;

        $sql= "select max(numero) from ttematicas where ifaccords = true and ".year2pg('fecha_inicio_plan')." = $this->year ";
        $sql.= "and (id_evento  = $id_evento or id_evento in (select id from teventos where id = $id_evento or id_evento = $id_evento)) ";

        $result= $this->do_sql_show_error('find_max_numero_accords', $sql);
        $row= $this->clink->fetch_array($result);
        return !empty($row[0]) ? $row[0] : 0;
    }

    public function find_max_numero($id_evento) {
        $sql= "select max(numero) from ttematicas where (ifaccords = false or ifaccords is null) and id_evento = $id_evento ";

        $result= $this->do_sql_show_error('find_max_numero', $sql);
        $row= $this->clink->fetch_array($result);
        return !empty($row[0]) ? $row[0] : 0;
    }

    public function listar_all_accords($year= null, $order_by_date= false) {
        $this->if_ttematicas= false;
        $this->_create_copy_table('ttematicas');
        if (!is_null($this->error))
            return $this->error;

        $result= $this->listar(true, $year);
        if (empty($this->cant))
            return null;

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['id'];

            $sql= "insert into _cttematicas ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : "";
            $sql.= "select * from ttematicas where (id_tematica = $id or (id = $id and id_tematica is null)) ";
            $this->do_sql_show_error('listar_all_accords', $sql);
        }

        $this->if_ttematicas= true;
        return $this->listar(true, $year, $order_by_date);
    }

    protected function get_array_usuarios() {
        $sql= "select distinct id_usuario, id_grupo, indirect from tusuario_eventos_{$this->year} ";
        $sql.= "where id_tematica = $this->id_tematica ";
        $result= $this->do_sql_show_error('get_array_usuarios', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_usuarios[]= array('id_usuario'=>$row['id_usuario'], 'id_grupo'=>$row['id_grupo']);
        }
        return $this->array_usuarios;
    }

    public function this_copy($to_year= null, $id_tematica= null, $id_tematica_code= null) {
        $obj= $this->_this_copy();

        $obj->SetLink($this->clink);
        $obj->obj_code->SetLink($this->clink);
        $obj->SetId(null);
        $obj->set_id_code(null);

        $obj->SetIdEvento($this->id_evento);
        $obj->set_id_evento_code($this->id_evento_code);

        $obj->SetOrigenData(null);
        $obj->SetEvaluacion(null);
        $obj->SetEvaluado(null);
        $obj->SetCumplimiento(null);
        $obj->SetIdResponsable_eval(null);
        $obj->SetIdUsuario($_SESSION['id_usuario']);
        $obj->set_cronos($this->cronos);

        $obj->SetFechaFinPlan($this->fecha_fin_plan);

        $this->get_array_usuarios();

        $obj->set_id_copyfrom($this->id);
        $obj->set_id_copyfrom_code($this->id_code);
        
        $this->error= $obj->add($id_tematica, $id_tematica_code);
        if (!is_null($this->error))
            return null;

        $id= $obj->GetId();
        $id_code= $obj->get_id_code();

        $this->set_copyto($this->id, $to_year, $id_code);

        $this->_copy_reg($to_year, $id, $id_code);

        $this->id= $id;
        $this->id_code= $id_code;
        
        $array= array('id'=>$id, 'id_code'=>$id_code);
        return $array;
    }

    public function _copy_reg($to_year= null, $id_tematica= null, $id_tematica_code= null) {
        $error= null;
        $id_tematica= !empty($id_tematica) ? $id_tematica : $this->id_tematica;
        $id_tematica_code= !empty($id_tematica) ? $id_tematica_code : $this->id_tematica_code;
        $to_year= !empty($to_year) ? $to_year : $this->year + 1;

        reset($this->array_usuarios);

/* copia tusuario_eventos */
        foreach ($this->array_usuarios as $array) {
            $id_usuario= setNULL($array['id_usuario']);
            $id_grupo= setNULL($array['id_grupo']);

            $sql= "insert into tusuario_eventos_{$to_year} (id_evento, id_evento_code, id_tematica, id_tematica_code, ";
            $sql.= "id_usuario, id_grupo, cronos, situs) values ($this->id_evento, '$this->id_evento_code', $id_tematica, ";
            $sql.= "'$id_tematica_code', $id_usuario, $id_grupo, '$this->cronos', '$this->location')";
            $this->do_sql_show_error('_copy_reg', $sql);
            if (!empty($this->error) && (stripos($this->clink->error,'duplicate') !== false || stripos($this->clink->error,'duplicada') !== false))
                $error.= $this->error."<br/>";
        }
    }

    private function set_copyto($id, $year, $id_code) {
        $this->copyto= "$year($id_code)-";
        $sql= "update ttematicas set copyto= '$this->copyto' where id = $id ";
        $this->do_sql_show_error('set_copyto', $sql);
    }

    private function create_tmp_meeting() {
        $sql= "drop table if exists ".stringSQL("_tmp_meeting");
        $this->do_sql_show_error('create_tmp_meeting', $sql);

        $sql= "CREATE TEMPORARY TABLE ".stringSQL("_tmp_meeting")." ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " numero ".field2pg("MEDIUMINT(4)").", ";
        $sql.= " nombre ".field2pg("TEXT").", ";
        $sql.= " fecha_inicio ".field2pg("DATETIME").", ";
        $sql.= " fecha_prev ".field2pg("DATETIME");
        $sql.= ") ";

        if (empty($this->error))
            $this->do_sql_show_error('create_tmp_meeting', $sql);
        if (empty($this->error))
            $this->if_tmp_meeting= true;
    }

    private function test_date_accords($date, $value, $array_dates) {
        reset($array_dates);
        $_date= strtotime($date);

        $i= 0;
        foreach ($array_dates as $row) {
            ++$i;
            if ($i == 1 && ($_date <= strtotime($row[3]) && $_date > strtotime($row[4])))
                return true;
            if ($value == _INCUMPLIDO && $_date <= strtotime($row[3]))
                return true;
        }
        return false;
    }


    public function getPrevAccords($id= null) {
        $obj_reg= new Tregister_planning($this->clink);
        
        $id= !empty($id) ? $id : $this->id_evento;
        $this->if_tmp_meeting= false;

        if (empty($this->id_evento))
            return null;

        $sql= "select * from teventos where id = $id";
        $result= $this->do_sql_show_error('getPreveAccords', $sql);

        $row= $this->clink->fetch_array($result);
        $fecha_up= $row['fecha_inicio_plan'];
        $id_evento= $row['id_evento'];
        $id_tipo_reunion= $row['id_tipo_reunion'];
        $nombre= $row['nombre'];
        $numero= $row['numero'];
        $date= null;

        $sql= "select id, fecha_inicio_plan, numero, nombre, ".year2pg("fecha_inicio_plan")." as _year, ";
        $sql.= "id_responsable from teventos where id_tipo_reunion = $id_tipo_reunion and id_evento = $id_evento and toshow >= 0 ";
        $sql.= "and ".date2pg("fecha_inicio_plan")." <= ".date2pg("'$fecha_up'")." order by fecha_inicio_plan asc ";

        $result= $this->do_sql_show_error('getPreveAccords', $sql);
        $cant= $this->clink->num_rows($result);
        if (empty($cant))
            return null;

        $this->create_tmp_meeting();

        $fecha_down= null;
        $array_dates= array();
        $i= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $obj_reg->SetYear($row['_year']);
            $obj_reg->SetIdUsuario($row['id_responsable']);
            $rowcmp= $obj_reg->getEvento_reg($row['id']);
            
            if ($rowcmp['rechazado'] || ($rowcmp['cumplimiento'] == _SUSPENDIDO || $rowcmp['cumplimiento'] == _CANCELADO))
                continue;
            
            if (is_null($fecha_down))
                $fecha_down= $row['fecha_inicio_plan'];
            if ($i == 1) {
                $date= $row['fecha_inicio_plan'];
                $date_prev= null;
            } else {
                $date_prev= $date;
                $date= $row['fecha_inicio_plan'];
            }
            $array_dates[]= array($row['id'], $row['numero'], $row['nombre'], $date, $date_prev);
        }

        $array_dates= array_reverse($array_dates);
        return $this->_getPrevAccords($id, $array_dates, $fecha_down, $fecha_up);
    }

    private function _getPrevAccords($id, $array_dates, $fecha_down, $fecha_up) {
        $i= 0;
        $j= 0;
        $sql= null;
        foreach ($array_dates as $row) {
            ++$j;
            if ($row[0] == $id)
                continue;

            $numero= setNULL($row[1]);
            $date_prev= setNULL_str($row[4]);

            $sql.= "insert into _tmp_meeting ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
            $sql.= "values ({$row[0]}, $numero, '{$row[2]}', '{$row[3]}', $date_prev); ";

            ++$i;
            if ($i >= $_SESSION["_max_register_block_db_input"]) {
                $this->do_multi_sql_show_error('getPrevAccords', $sql);
                $sql= null;
                $i= 0;
            }
        }
        if (!is_null($sql))
            $this->do_multi_sql_show_error('getPrevAccords', $sql);

        $array_accords= array();

        $sql= "select distinct ttematicas.*, ttematicas.id as _id, ttematicas.id_evento as _id_evento ";
        $sql.= "from ttematicas, _tmp_meeting  where ifaccords = true ";
        $sql.= "and (".date2pg("fecha_inicio_plan")." >= ".date2pg("'$fecha_down'")." and ".date2pg("fecha_inicio_plan")." <= ".date2pg("'$fecha_up'").") ";
        $sql.= "and (ttematicas.id_evento = _tmp_meeting.id or ttematicas.id_evento_accords = _tmp_meeting.id) ";
        $sql.= "order by ttematicas.numero asc, ttematicas.fecha_inicio_plan desc";
        $result= $this->do_sql_show_error('getPreveAccords', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if ($row['_id_evento'] == $id)
                continue;
            if (!$this->test_date_accords($row['fecha_inicio_plan'], $row['cumplimiento'], $array_dates))
                continue;

            $array= array('id'=>$row['_id'], 'numero'=>$row['numero'], 'descripcion'=>$row['descripcion'],'fecha_inicio'=>$row['fecha_inicio_plan'],
                        'id_proceso'=>$row['id_proceso'],'id_responsable'=>$row['id_responsable'], 'cumplimiento'=>$row['cumplimiento'],
                        'id_responsable_eval'=>$row['id_responsable_eval'],'evaluado'=>$row['evaluado'], 'evaluacion'=>$row['evaluacion'],
                         'id_evento'=>$row['_id_evento'], 'id_evento_accords'=>$row['id_evento_accords'], 
                         'id_asistencia_resp'=>$row['id_asistencia_resp']);
            $array_accords[$row['_id']]= $array;
        }
        return $array_accords;
    }

    public function move_all_by_evento_to($id_evento= null) {
        $sql= "select * from ttematicas where id_evento= $id_evento";
        $result_list= $this->do_sql_show_error('move_all_by_evento_to', $sql);
        if (empty($this->cant))
            return null;

        $fecha_inicio_plan= setNULL_str($this->fecha_inicio_plan);
        
        if (isset($this->array_tematicas)) unset($this->array_tematicas);
        $this->array_tematicas= array();
        
        $sql= null;
        $i= 0;
        while ($row= $this->clink->fetch_array($result_list)) {
            ++$i;
            $sql= "update ttematicas set id_evento= $this->id_evento, id_evento_code= '$this->id_evento_code', ";
            $sql.= "fecha_inicio_plan = $fecha_inicio_plan, cronos= '$this->cronos', situs= '$this->location' ";
            $sql.= "where id_evento = $id_evento and id = {$row['id']}; ";
            $result= $this->do_sql_show_error('move_all_by_evento_to', $sql);

            if ($result)
                $this->array_tematicas[$row['id']]= array('id'=>$row['id'], 'id_code'=>$row['id_code']);
            else
                return false;
        }
        return $this->array_tematicas;
    }


    public function copy_all_tematicas_by_evento($id_evento) {
        $sql= "select * from ttematicas where id_evento= $id_evento";
        $result_list= $this->do_sql_show_error('move_all_tematicas_by_evento', $sql);
        if (empty($this->cant))
            return;
        $fecha_inicio_plan= setNULL_str($this->fecha_inicio_plan);

        $array_tematicas= array();
        $sql= null;
        $i= 0;
        while ($row= $this->clink->fetch_array($result_list)) {
            ++$i;
            $ifaccords= boolean2pg($row['ifaccords']);
            $id_evento_accords= setNULL($row['id_evento_accords']);
            $id_evento_accords_code= setNULL($row['id_evento_accords_code']);

            $id_tematica= setNULL($row['id_tematica']);
            $id_tematica_code= setNULL_str($row['id_tematica_code']);

            $id_responsable_eval= setNULL($row['id_responsable_eval']);
            $evaluado= setNULL_str($row['evaluado']);
            $evaluacion= setNULL_str($row['evaluacion']);

            $cumplimiento= setNULL($row['cumplimiento']);
            $observacion= setNULL_str($row['observacion']);

            $sql= "insert into ttematicas (numero, ifaccords, descripcion, id_asistencia_resp, id_asistencia_resp_code, ";
            $sql.= "fecha_inicio_plan, id_evento, id_evento_code, id_evento_accords, id_evento_accords_code, ";
            $sql.= "id_tematica, id_tematica_code, cumplimiento, id_responsable_eval, evaluado, evaluacion, ";
            $sql.= "'cronos, situs) values ({$row['numero']}, $ifaccords, {$row['descripcion']}', {$row['id_asistencia_resp']}, ";
            $sql.= "'{$row['id_asistencia_resp_code']}', $fecha_inicio_plan, $this->id_evento, '$this->id_evento_code', ";
            $sql.= "$id_evento_accords, $id_evento_accords_code, $id_tematica, $id_tematica_code, $cumplimiento, ";
            $sql.= "$id_responsable_eval, $evaluado, $evaluacion, '$this->cronos', '$this->location'); ";
            $result= $this->do_sql_show_error('copy_all_tematicas_by_evento', $sql);

            if ($result) {
                 $id= $this->clink->inserted_id("ttematicas");
                 $this->obj_code->SetId($id);
                 $id_code= $this->obj_code->set_code('ttematicas','id','id_code');

                 $array_tematicas[$row['id']]= array('id'=>$row['id'], 'id_code'=>$row['id_code']);
            } else {
                return false;
            }
        }
        return $array_tematicas;
    }

    private function set_array_responsabilities() {
        /* Responsabilidad y Parcipacion en tematicas */
        $sql= "select * from ttematicas where id_evento = $this->id_evento";
        $result_matter= $this->do_sql_show_error('set_array_responsabilities', $sql);
        if (empty($this->cant))
            return;

        while ($row_matter= $this->clink->fetch_array($result_matter)) {
            $this->array_attached_results[$this->id_evento]['tematicas'][]= array(
                    'id'=>$row_matter['id'], 'id_responsable'=>$this->array_id_asistencia_usuario[$row_matter['id_asistencia_resp']],
                    'id_evento_accords'=>$row_matter['id_evento_accords']);

            $sql= "select * from tdebates where id_tematica = {$row_matter['id']}";
            $result_debates= $this->do_sql_show_error('set_array_responsabilities', $sql);
            if (empty($this->cant))
                continue;

            while ($row_debates= $this->clink->fetch_array($result_debates)) {
                $this->array_attached_results[$this->id_evento]['debates'][]= array(
                        'id'=>$row_debates['id'], 'id_tematica'=>$row_debates['id_tematica'], 'id_responsable'=>$row_debates['id_responsable']
                );
        }   }
    }

    private function _set_if_attached($id_evento= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;

        $this->fill_array_asistencia_usuarios();

        if (empty($this->array_attached_results[$id_evento]))  {
            $this->set_array_responsabilities();
            $this->set_array_assisted();
        }
    }
    
    public function get_if_attached_usuario($id_usuario, $id_evento= null) {
        $this->_set_if_attached($id_evento);
        
        foreach ($this->array_attached_results[$id_evento]['tematicas'] as $array) {
            if ($array['id_responsable'] == $id_usuario)
                return true;
        }
        foreach ($this->array_attached_results[$id_evento]['debates'] as $array) {
            if ($array['id_responsable'] == $id_usuario)
                return true;
        }
        foreach ($this->array_attached_results[$id_evento]['asistencias'] as $array) {
            if ($array['id_usuario'] == $id_usuario)
                return true;
        }
        return false;
    }
    
    public function get_if_attached_evento($id_evento= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        $this->_set_if_attached($id_evento);
        
        foreach ($this->array_attached_results[$id_evento]['tematicas'] as $array) {
            if (!empty($array['id_evento_accords']))
                return true;
        }
        if (count($this->array_attached_results[$id_evento]['debates']) > 0) {
            return true;
        }
        return false;        
    }

    public function listar_grupos($id= null, $id_proceso= null, $id_proceso_users= null, $flag = false) {
        $id= !empty($id) ? $id : $this->id_tematica;
        $flag= !is_null($flag) ? $flag : false;
        $users_from_proceso= !empty($id_proceso) || (!empty($id_proceso_users) && ($id_proceso_users != $_SESSION['local_proceso_id'])) ? true : false;

        if (!$users_from_proceso) {
            $sql= "select distinct tgrupos.id as _id, tgrupos.* from tgrupos, tusuario_eventos_{$this->year} ";
            $sql.= "where tusuario_eventos_{$this->year}.id_grupo = tgrupos.id and tusuario_eventos_{$this->year}.id_tematica = $id ";
        }
        if ($users_from_proceso && !empty($this->id_evento)) {
            $sql= "select distinct tgrupos.id as _id, tgrupos.* from tgrupos, tusuario_eventos_{$this->year} ";
            $sql.= "where tusuario_eventos_{$this->year}.id_tematica = $id and tusuario_eventos_{$this->year}.id_evento = $this->id_evento ";
            $sql.= "and tgrupos.id = tusuario_eventos_{$this->year}.id_grupo ";
            $sql.= "and tgrupos.id_entity = {$_SESSION['id_entity']}"; 
        }

        $this->_list_group($sql, false);

        if (!$flag) {
            $result= $this->do_sql_show_error('listar_grupos', $sql);
            return $result;
        }
    }

    public function listar_usuarios($id= null, $id_proceso= null, $id_proceso_users= null, $id_responsable= null, $flag = false) {
        $id= !empty($id) ? $id : $this->id_tematica;
        $flag= !is_null($flag) ? $flag : false;
        $users_from_proceso= !empty($id_proceso) || (!empty($id_proceso_users) && ($id_proceso_users != $_SESSION['local_proceso_id'])) ? true : false;

        if (!$users_from_proceso) {
            $sql= "select distinct tusuarios.id as _id, tusuarios.*, tusuarios.id_proceso as _id_proceso_user ";
            $sql.= "from tusuarios, tusuario_eventos_{$this->year} where tusuarios.id = tusuario_eventos_{$this->year}.id_usuario ";
            $sql.= "and tusuario_eventos_{$this->year}.id_tematica = $id and (eliminado is null or eliminado < '$this->fecha_inicio_plan')";
        }
        if ($users_from_proceso && !empty($this->id_evento)) {
            $sql= "select distinct tusuarios.id as _id, tusuarios.*, tusuarios.id_proceso as _id_proceso_user, ";
            $sql.= "from tusuarios, tusuario_eventos_{$this->year} ";
            $sql.= "where tusuarios.id = tusuario_eventos_{$this->year}.id_usuario ";
            $sql.= "and (tusuario_eventos_{$this->year}.id_tematica = $id and tusuario_eventos_{$this->year}.id_evento = $this->id_evento) ";
            $sql.= "and (eliminado is null or eliminado < '$this->fecha_inicio_plan')";
        }
        if (!is_null($id_responsable))
            $sql.= "and id_usuario <> $id_responsable ";

        $this->_list_user($sql, null, false);

        if (!$flag) {
            $result= $this->do_sql_show_error('listar_usuarios', $sql);
            return $result;
        }
    }

    public function get_participantes($id= null, $id_proceso= null, $id_proceso_users= null, $id_responsable= null, $flag= false) {
        global $config;
        $j= 0;
        $array= array();

        $id= !empty($id) ? $id : $this->id_tematica;

        $result= $this->listar_grupos($id, $id_proceso, $id_proceso_users, false);

        while ($row= $this->clink->fetch_array($result)) {
            $item= $row['nombre'];
            if (array_search($item, $array) === false)
                $array[$j++]= $item;
        }

        $result= $this->listar_usuarios($id, $id_proceso, $id_proceso_users, $id_responsable, false);

        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($id_proceso_users) && ($id_proceso_users != $_SESSION['id_entity'])) {
                if ($row['_id_proceso_user'] != $id_proceso_users)
                    continue;
            }
            if (empty($id_proceso) || (!empty($id_proceso) && $id_proceso == $row['_id_proceso_user'])) {
                $item= $config->onlypost ? trim($row['cargo']) : !empty($row['cargo']) ? $row['nombre']. ' ('.$row['cargo'].')' : $row['nombre'];
                if (array_search($item, $array) === false)
                    $array[$j++]= $item;
            }
        }

        $this->cant= $j;
        return $this->cant ? implode(", ", $array) : null;
    }

    public function if_fixed($id_evento= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        if (empty($id_evento))
            return null;

        $sql= "select * from ttematicas where id_evento = $id_evento and ifaccords = true";
        $result= $this->do_sql_show_error('if_fixed', $sql);
        if ($this->cant > 0)
            return true;

        $sql= "select * from tdebates where id_evento = $id_evento";
        $result= $this->do_sql_show_error('if_fixed', $sql);
        if ($this->cant > 0)
            return true;

        $sql= "select * from tasitencias where id_evento = $id_evento and ausente = true";
        $result= $this->do_sql_show_error('if_fixed', $sql);
        if ($this->cant > 0)
            return true;

        return false;
    }

    public function list_reg() {
        $result= $this->listar_all_accords($this->year, true, true);
        $this->total= 0;
        $array_eventos= array();
        $obj_event= new Tevento($this->clink);

        $i = 0;
        while ($row = $this->clink->fetch_array($result)) {
           if (isset($array_eventos[$row['id_evento_accords']]))
               continue;

            ++$this->total;
            $array_eventos[$row['id_evento_accords']]= 1;

            $obj_event->SetIdEvento($row['id_evento_accords']);
            $obj_event->Set();
            $this->year= date('Y', strtotime($obj_event->GetFechaInicioPlan()));

            $this->cumplimiento = null;
            $array_responsable= array('id_responsable'=>$obj_event->GetIdResponsable(), 'id_responsable_2'=>$obj_event->get_id_responsable_2(),
                                'responsable_2_reg_date'=>$obj_event->get_responsable_2_reg_date());
            $_row = $this->getEvento_reg($row['id_evento_accords'], $array_responsable);

            $array = array('id'=>$row['id_evento_accords'], 'evento' => $obj_event->GetNombre(), 'plan' => $obj_event->GetFechaInicioPlan(),
                'real' => $row['_fecha_fin_real'], 'descripcion' => $obj_event->GetDescripcion(), 'id_responsable' => $obj_event->GetIdResponsable(),
                'observacion' => $_row['observacion'],
                'id_user_asigna' => $obj_event->GetIdUsuario(), 'origen_data' => $row['origen_data'],
                'id_user_reg' => $_row['id_user_reg'], 'month' => $row['month'], 'year' => $row['year']);

            if ($_row['cumplimiento'] == _INCUMPLIDO) {
                $new_incumplida= !isset($this->incumplidas_list[$row['id_evento_accords']]) ? true : false;
                if (!is_array($this->incumplidas_list))
                    $this->incumplidas_list = array();
                $this->incumplidas_list[$row['id_evento_accords']] = $array;

                if ($new_incumplida)
                    ++$this->incumplidas;
            }

            $new_cancelada= !isset($this->canceladas_list[$row['id_evento_accords']]) ? true : false;
            if ($_row['cumplimiento'] == _CANCELADO || ($_row['cumplimiento'] == _POSPUESTO || $_row['cumplimiento'] == _SUSPENDIDO)) {
                if (!is_array($this->canceladas_list))
                    $this->canceladas_list = array();
                $this->canceladas_list[$row['id_evento_accords']] = $array;

                if ($new_cancelada)
                    ++$this->canceladas;
            }

            if ($_row['cumplimiento'] == _COMPLETADO) {
                if (!is_array($this->cumplidas_list))
                    $this->cumplidas_list = array();
                $this->cumplidas_list[$row['id_evento_accords']] = $array;
                ++$this->cumplidas;
            }
        }
    }

    public function get_array_eventos_accords() {
        $sql= "select * from ttematicas where year(fecha_inicio_plan) = $this->year and id_evento_accords is not null ";
        $result= $this->do_sql_show_error('get_array_eventos_accords', $sql);

        $i= 0;
        while ($row = $this->clink->fetch_array($result)) {
            ++$i;
            $array= array('id'=>$row['id'], 'id_evento'=>$row['id_evento_accords'], 
                            'id_evento_code'=>$row['id_evento_accords_code']);
            $this->array_accords[$row['id']]= $array;
        }

        return $i;
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";

if (!class_exists('Tplanning'))
    include_once "planning.class.php";