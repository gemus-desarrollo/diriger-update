<?php

/*
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

include_once "tmp_tables_archivos.class.php";

class Tarchivo extends Ttmp_tables_archivos {
    protected $numero;
    protected $noIndentidad;
    
    protected $indicaciones;
    protected $antecedentes;

    protected $fecha_origen;
    protected $fecha_entrega;

    private $if_anonymous;
    private $if_immediate;
    
    private $clase;
    private $prioridad;
    private $activo;
    
    public $array_archivos;
    public $do_filter_by_fin_plan;

    protected $id_organismo;
    protected $id_organismo_code;
    
    protected $date_init, 
            $date_end; 
    protected $keywords, 
            $persona_keywords,
            $numero_keywords;
    protected $only_event;
    
    protected $codigo;

    private $obj_ref;
    
    private $create_view;

    public function SetNumero($param) {
        $this->numero= $param;
    }
    public function GetNumero() {
        return $this->numero;
    }
    public function SetAntecedentes($param) {
        $this->antecedentes= $param;
    }
    public function GetAntecedentes() {
        return $this->antecedentes;
    }    
    public function SetIndicaciones($param) {
        $this->indicaciones = $param;
    }
    public function GetIndicaciones() {
        return $this->indicaciones;
    }
    public function SetFechaOrigen($param) {
        $this->fecha_origen= $param;
    }
    public function SetFechaEntrega($param) {
        $this->fecha_entrega= $param;
    }
    public function GetFechaOrigen() {
        return $this->fecha_origen;
    }
    public function GetFechaEntrega() {
        return $this->fecha_entrega;
    }
    public function SetIfAnonymous($id) {
        $this->if_anonymous = $id;
    }
    public function GetIfAnonymous() {
        return $this->if_anonymous;
    }
    public function SetIfImmediate($id) {
        $this->if_immediate = $id;
    }
    public function GetIfImmediate() {
        return $this->if_immediate;
    }
    public function SetClase($id) {
        $this->clase= $id;
    }
    public function GetClase() {
        return $this->clase;
    }
    public function SetPrioridad($id) {
        $this->prioridad= $id;
    }
    public function GetPrioridad() {
        return $this->prioridad;
    }
    public function GetActivo() {
        return $this->activo;
    }
    public function GetIdOrganismo() {
        return $this->id_organismo;
    }
    public function SetIdOrganismo($id) {
        $this->id_organismo= $id;
    }
    public function SetCodigo($id) {
        $this->codigo= $id;
    }
    public function GetCodigo() {
        return $this->codigo;
    }
    
    public function __construct($clink) {
        Ttmp_tables_archivos::__construct($clink);
        $this->do_filter_by_fin_plan= false;
        $this->max_row_in_page= 200;
        $this->clink= $clink;
    }
    
    public function Set($id= null) {
        $id= !empty($id) ? $id : $this->id_archivo;
        
        $sql= "select * from tarchivos where id = $id";
        $result= $this->do_sql_show_error('Set', $sql);
        
        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->id_documento= $row['id_documento'];
            $this->id_documento_code= $row['id_documento_code'];
            
            if (!empty($this->id_documento)) {
                Tdocumento::Set($this->id_documento);
            }
            
            $this->id= $row['id'];
            $this->id_code= $row['id_code'];
            $this->id_archivo= $this->id;
            $this->id_archivo_code= $this->id_code;
            
            $this->numero= $row['numero'];
            $this->codigo= $row['codigo'];
            $this->year= $row['year'];
            $this->clase= $row['clase'];
            $this->prioridad= $row['prioridad'];
            
            $this->tipo= $row['tipo'];
            $this->descripcion= stripslashes($row['descripcion']);
            $this->keywords= stripslashes($row['keywords']);
            $this->indicaciones= stripslashes($row['indicaciones']);
            $this->antecedentes= $row['antecedentes'];
            
            $this->fecha_entrega= $row['fecha_entrega'];
            $this->fecha_origen= $row['fecha_origen'];
            
            $this->id_usuario= $row['id_usuario'];
            $this->id_responsable= $row['id_responsable'];
            
            $this->id_evento= $row['id_evento'];
            $this->id_evento_code= $row['id_evento_code'];
            $this->fecha_fin_plan= $row['fecha_fin_plan'];
            
            $this->if_anonymous= $row['if_anonymous'];
            $this->if_immediate= $row['if_immediate'];
            $this->if_output= boolean($row['if_output']);
            $this->sendmail= boolean($row['sendmail']);
            $this->toshow= $row['toshow'];
            
            $this->activo= boolean($row['activo']);
        }
        
        return $this->error;
    }

    public function add() {
        $numero= setNULL($this->numero);
        $indicaciones= setNULL_str($this->indicaciones);
        $antecedentes= setNULL_str($this->antecedentes);
        $keywords= setNULL_str($this->keywords);
        $fecha_fin_plan= setNULL_str($this->fecha_fin_plan);
        $if_anonymous= setZero($this->if_anonymous);
        $id_reponsable= setNULL($this->id_responsable);
        $toshow= setNULL($this->toshow);
        
        $prioridad= setNULL($this->prioridad);
        $clase= setNULL($this->clase);
        
        $sql= "insert into tarchivos (numero, year, tipo, descripcion, keywords, indicaciones, antecedentes, ";
        $sql.= "fecha_origen, fecha_entrega, if_output, if_anonymous, id_usuario, cronos, situs, fecha_fin_plan, ";
        $sql.= "toshow, sendmail, id_responsable, if_immediate, id_proceso, id_proceso_code, prioridad, clase) ";
        $sql.= "values ($numero, $this->year, '$this->tipo', '$this->descripcion', $keywords, ";
        $sql.= "$indicaciones, $antecedentes, '$this->fecha_origen', '$this->fecha_entrega', ".boolean2pg($this->if_output).", ";
        $sql.= "$if_anonymous, {$_SESSION['id_usuario']}, '$this->cronos', '$this->location', $fecha_fin_plan, $toshow, ";
        $sql.= boolean2pg($this->sendmail).", $id_reponsable, ".boolean2pg($this->if_immediate).", ";
        $sql.= "$this->id_proceso, '$this->id_proceso_code', $prioridad, $clase) ";
        
        $result= $this->do_sql_show_error('add', $sql);
        
        if ($result) {
            $this->id = $this->clink->inserted_id("tarchivos");
            $this->id_archivo = $this->id;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tarchivos', 'id', 'id_code');
            $this->id_code = $this->obj_code->get_id_code();
            $this->id_archivo_code = $this->id_code;
        }
        
        return $this->error;
    }
    
    public function add_upload() {
        Tdocumento::add();
    }
    
    public function update() {
        $fecha_entrega = setNULL_str($this->fecha_entrega);
        $fech_origen = setNULL_str($this->fecha_origen);
        
        $fecha_fin_plan= setNULL_str($this->fecha_fin_plan);
        $fecha_entrega= setNULL_str($this->fecha_entrega);
        
        $id_documento= setNULL($this->id_documento);
        $id_documento_code= setNULL_str($this->id_documento_code);
        
        $id_evento= setNULL($this->id_evento);
        $id_evento_code= setNULL_str($this->id_evento_code);
        $id_reponsable= setNULL($this->id_responsable);
        
        $if_anonymous= setZero($this->if_anonymous);
        $toshow= setNULL($this->toshow);
        $indicaciones= setNULL_str($this->indicaciones);
        $numero= setNULL($this->numero);
        
        $descripcion= setNULL_str($this->descripcion);
        $antecedentes= setNULL_str($this->antecedentes);
        $codigo= setNULL_str($this->codigo);
        
        $prioridad= setNULL($this->prioridad);
        $clase= setNULL($this->clase);
        
        $sql = "update tarchivos set id_documento=$id_documento,  id_documento_code=$id_documento_code, id_responsable= $id_reponsable, ";
        $sql.= "id_evento=$id_evento, id_evento_code=$id_evento_code, fecha_origen=$fech_origen, fecha_fin_plan= $fecha_fin_plan, ";
        $sql.= "sendmail= ".boolean2pg($this->sendmail).", toshow= $toshow, indicaciones= $indicaciones, if_anonymous= $if_anonymous, ";
        $sql.= "if_immediate= ".boolean2pg($this->if_immediate).", descripcion= $descripcion, antecedentes= $antecedentes, ";
        $sql.= "prioridad= $prioridad, clase= $clase, cronos='$this->cronos', id_usuario={$_SESSION['id_usuario']}, ";
        $sql.= "situs='$this->location', numero= $numero, codigo= $codigo where id = $this->id_archivo ";

        $result= $this->do_sql_show_error('add', $sql);
        return $this->error;
    }

    public function eliminar($activo= false) {
        if (!empty($this->id_documento)) {
            Tdocumento::eliminar($this->url);
            if (!is_null($this->error)) 
                return $this->error;
        }  
 
        if (!empty($this->id_evento)) {
            $obj_event= new Tevento($this->clink);
            $obj_event->SetIdEvento($this->id_evento);
            $obj_event->set_id_evento_code($this->id_evento_code);
            
            $this->error= $obj_event->delete_reg($this->fecha_fin_plan, $this->id_evento);
            
            if (!is_null($this->error)) 
                return $this->error;
        }
        
       $this->SetActivo($activo, $this->id);
    }
    
    private function SetActivo($activo= false, $id= null) {
        $id = !empty($id) ? $id : $this->id;
        $sql= "update tarchivos set activo= ". boolean2pg($activo)." where id = $id";
        $this->do_sql_show_error('SetActivo', $sql);
        return $this->error;        
    }

    private function fill_array_result($result, &$result_array) {
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $result_array[]= $row;
        }    
        return $i;
    }
    
    private function compare_cell($row1, $row2) {
        $item1= null;
        $item2= null;
        
        if (empty($this->create_view) || $this->create_view == 1) {
            $item1= "cronos";
            $item2= "fecha_entrega";
        } else {
            $item1= "fecha_fin_plan";
            $item2= "cronos";            
        }   
        
        $time11= strtotime($row1[$item1]);
        $time12= strtotime($row1[$item2]);
        $time21= strtotime($row2[$item1]);
        $time22= strtotime($row2[$item2]);
        
        $result= (($time11 < $time21) || ($time11 == $time21 && $time12 < $time22)) ? -1 : 1;
        return $result;
    }
    
    public function listar($date_init, $date_end, $create_view= false, $only_event= null, $keywords= null, $persona_keywords= null, 
                                                                                    $numero_keywords= null, $restric_to_target= true) {
        
        $restric_to_target= !is_null($restric_to_target) ? $restric_to_target : true;
        $this->create_view= !is_null($create_view) ? $create_view : false;
        
        if (isset($this->array_archivos)) unset($this->array_archivos);
        $this->array_archivos= array();
        
        $this->debug_time('create_tmp_tables');
        $this->create_tmp_tables($date_init, $date_end, $only_event, $keywords, $persona_keywords, $numero_keywords);
        $this->debug_time('create_tmp_tables');
        
        $this->debug_time('automatic_archive');
        
        $result_array= array();
        $nums= 0;
        $result= $this->_automatic_archive();
        $nums+= $this->fill_array_result($result, $result_array);

        $result= $this->automatic_archive();
        $nums+= $this->fill_array_result($result, $result_array);

        $this->debug_time('automatic_archive');
        
        $obj_array_result= new ArrayObject($result_array);
        $obj_array_result->uasort('compare_cell');
        $result_array= $obj_array_result->getArrayCopy();
        unset($obj_array_result);
        
        $init_row= $this->init_row_temporary*$this->max_row_in_page;
        $this->max_num_pages= (int)ceil((float)$nums/$this->max_row_in_page);
        if ($this->max_num_pages == 0) $this->max_num_pages= 1; 
        
        $i= 0;
        foreach ($result_array as $row) {
            $continue= true;
            if ($this->limited) {
                if ($i < $init_row && $init_row != 0) 
                    $continue= false;
                if ($i > ($init_row + $this->max_row_in_page)) 
                    break;
            }     
            
            ++$i;
            if (!$continue) 
                continue;
            
            $this->array_archivos[$row['_id']]= array('id'=>$row['_id'], 'sender'=>null);
            $array_result[]= $row;
        }
        
        $i= 0;
        $this->obj_ref= new Tref_archivo($this->clink);
        foreach ($this->array_archivos as $row) {
            ++$i;
            $sender= $this->listar_by_if_sender(true, $row['id']);
            $this->array_archivos[$row['id']]['sender']= $sender;
        }

        reset($this->array_archivos);       
        return $array_result;
    }     
    
    private function select_init($flag= false) {
        if (!$this->if_tarchivos) 
            $this->Set_tmp_tables ();
        
        $sql= "select * from _tarchivos where 1 ";
        if (!is_null($this->only_event)) {
            $sql.= "and (indicaciones is not null or fecha_fin_plan is not null) ";
            if ($this->only_event) 
                $sql.= "and id_evento is not null "; 
        }        
        if ($flag) 
            return $sql;
        $result= $this->do_sql_show_error('select_by_archivos', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $this->array_ids[$row['id']]= $row['id'];
        }
        return $i;
    }  
    
    private function select_by_archivos($flag= false, $order= false) {
        
        $sql= $this->select_init(true);
        
        $i= 0;
        $cant_keywords= count($this->keywords);
        foreach ($this->keywords as $word) {
            ++$i;
            if ($i == 1) 
                $sql.= "and ";
            if ($i == 1 && $cant_keywords > 1) 
                $sql.= " (";
            if ($i > 1) 
                $sql.= "or ";
            $sql.= "(lower(antecedentes) like '%$word%' or lower(descripcion) like '%$word%' or lower(keywords) like '%$word%' ";
            $sql.= "or lower(indicaciones) like '%$word%') ";
        }
        if ($cant_keywords > 1) 
            $sql.= ") ";
        $sql.= "and _tarchivos.activo = ". boolean2pg(1). " ";
        if ($order) 
            $sql.= "order by cronos asc, fecha_entrega asc";   
        
        if ($flag) 
            return $sql;
        
        $result= $this->do_sql_show_error('select_by_archivos', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $this->array_archivo_ids[$row['id']]= $row['id'];
        }
        return $i;
    }    
    
    protected function test_lugar($id_archivo= null) {
        $cant_keywords= count($keywords) + count($this->persona_keywords);
        $id_archivo= !empty($id_archivo) ? $id_archivo : $this->id_archivo;
        
        if (!$this->if_tarchivo_personas) {
            $sql= "select tpersonas.* from tpersonas, tarchivo_personas where tpersonas.id = tarchivo_personas.id_persona ";
            $sql.= "and tarchivo_personas.id_archivo = $id_archivo ";
        } else 
            $sql= "select * from _tarchivo_personas where id_archivo = $id_archivo ";
        if (!empty($this->lugar)) {
            $sql.= "and (LOWER(lugar) like '%".strtolower($this->lugar)."%' ";
            $sql.= "or LOWER(direccion) like '%".strtolower($this->lugar)."%' )";
        }    
        
        $result= $this->do_sql_show_error('test_lugar', $sql);
        $nrows= $this->clink->num_rows($result);
        
        while ($row= $this->clink->fetch_array($result)) {
            reset($this->keywords);
            foreach ($this->keywords as $word) {            
                if (!empty($row['lugar']) && stripos(strtolower($row['lugar']), $word) !== false) 
                    return true;
                if (!empty($row['direccion']) && stripos(strtolower($row['direccion']), $word) !== false) 
                    return true; 
            } 
            
            reset($this->persona_keywords);
            foreach ($this->persona_keywords as $word) {
                if (!empty($row['nombre']) && stripos(strtolower($row['nombre']), $word) !== false) 
                    return true;                
                if (!empty($row['cargo']) && stripos(strtolower($row['cargo']), $word) !== false) 
                    return true;                 
            }
            if ((!empty($this->id_organismo) && !empty($row['id_organismo'])) && stripos($row['id_organismo'], $this->id_organismo) !== false) 
                return true;
        }
        
        return $nrows > 0 && (empty($this->keywords) && empty($this->persona_keywords)) ? true : false;
    }
    
    private function select_by_lugares() {
        if (empty($this->lugar) && is_null($this->keywords) && is_null($this->persona_keywords)) 
            return null;
        
        $sql= $this->select_init(true); 
        $result= $this->do_sql_show_error('select_by_lugares', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if ($this->test_lugar($row['id'])) {
                ++$i;
                $this->array_lugar_ids[$row['id']]= $row['id'];  
            }    
        } 
        return $i;
    }
    
    private function select_by_numeros() {
        if (is_null($this->numero_keywords)) 
            return null;
        
        $sql= $this->select_init(true); 
        $result= $this->do_sql_show_error('select_by_lugares', $sql);    
        
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {           
            if (!empty($row['numero']) && array_search ($row['numero'], $this->numero_keywords) !== false) {
                ++$i;           
                $this->array_numero_ids[$row['id']]= $row['id'];
            }    
        }       
        return $i;
    }    

    protected function create_tmp_tables($date_init, $date_end, $only_event= null, 
                            $keywords= null, $persona_keywords= null, $numero_keywords= null) { 
        
        $this->date_init= !empty($date_init) ? $date_init : null;
        $this->date_end= !empty($date_end) ? $date_end : null;
        $this->only_event= !is_null($only_event) ? $only_event : null;    

        $this->keywords= !empty($keywords) ? $keywords : null;
        $this->persona_keywords= !empty($persona_keywords) ? $persona_keywords : null;
        $this->numero_keywords= !empty($numero_keywords) ? $numero_keywords : null;        
        
        $this->Set_tmp_tables();
        
        $this->select_by_archivos();
        $this->select_by_lugares();
        $this->select_by_numeros(); 
        
        foreach ($this->array_archivo_ids as $id) {
            $found_lugar= true;
            $found_numero= true;
            
            if (!empty($this->lugar) || !empty($this->keywords) || !empty($this->persona_keywords))
                if (!empty($this->array_lugar_ids) && !array_search ($id, $this->array_lugar_ids))
                    $found_lugar= false; 
            if (!empty($this->array_numero_ids) && !array_search($id, $this->array_numero_ids))
                $found_numero= false;    
            if ($found_lugar && $found_numero)
                $this->array_ids[$id]= $id; 
        }
        
        $this->refresh_tmp_tarchivos();
        $this->purge_tarchivo_personas();
    }
    
    private function _automatic_archive() {         
        $sql= "select distinct _tarchivos.*, _tarchivos.id as _id, _tarchivos.id_code as _id_code, _tarchivos.fecha_entrega as _fecha_entrega, ";
        $sql.= "_tarchivos.id_usuario as _id_user_asigna, _tarchivos.id_responsable as _id_responsable, ";
        $sql.= "null as id_ref, null as id_persona, null as if_sender, null as _id_usuario, null as id_grupo, null as nombre, ";
        $sql.= "null as cargo, null as id_organismo, null as _id_responsable_reg, _tarchivos.cronos as _cronos ";
        $sql.= "from _tarchivos where if_anonymous = "._IF_ANONYMOUS_SENDER_TARGET." ";

        $result= $this->do_sql_show_error('_automatic_archive', $sql);
        return $result;
    }
    
    private function automatic_archive($restric_to_target= false) { 
        $sql= "select distinct _tarchivos.*, _tarchivos.id as _id, _tarchivos.id_code as _id_code, _tarchivos.fecha_entrega as _fecha_entrega, ";
        $sql.= "_tarchivos.id_usuario as _id_user_asigna, _tarchivos.id_responsable as _id_responsable, _tarchivo_personas.id as id_ref, ";
        $sql.= "id_persona, if_sender, _tarchivo_personas.id_usuario as _id_usuario, id_grupo, ";
        if ($this->if_tarchivo_personas) 
            $sql.= "nombre, cargo, id_organismo, _tarchivo_personas.id_responsable as _id_responsable_reg, ";
        $sql.= "_tarchivo_personas.cronos as _cronos from _tarchivos, _tarchivo_personas where _tarchivos.id = _tarchivo_personas.id_archivo ";
        if ($restric_to_target) 
            $sql.= "and (if_sender = 0 or if_sender is null) ";
        if (!empty($this->id_persona)) {
            $sql.= "and (_tarchivo_personas.id_persona = $this->id_persona and _tarchivo_personas.activo = ".boolean2pg(1).") ";
        }       
        if (!empty($this->id_proceso)) 
            $sql.= "and _tarchivos.id_proceso = $this->id_proceso ";
        if (!empty($this->id_responsable)) 
            $sql.= "and _tarchivos.id_responsable = $this->id_responsable ";

        $result= $this->do_sql_show_error('automatic_archive', $sql);
        return $result;
    }
    
    public function listar_simple($date_init= null, $date_end= null, $create_view= false, 
                            $only_event= null, $keywords= null, $persona_keywords= null, $numero_keywords= null, $restric_to_target= true) {
        
        $restric_to_target= !is_null($restric_to_target) ? $restric_to_target : true;
        $this->create_view= !is_null($create_view) ? $create_view : false;
        
        if (isset($this->array_archivos)) unset($this->array_archivos);
        $this->array_archivos= array();    
        
        $this->debug_time('create_tmp_tables');
        $this->create_tmp_tables($date_init, $date_end, $only_event, $keywords, $persona_keywords, $numero_keywords);
        $this->debug_time('create_tmp_tables');
        
        $result= $this->automatic_archive();
        $nums= $this->fill_array_result($result, $result_array);
        if (empty($result_array))
            return null;
        
        $obj_array_result= new ArrayObject($result_array);
        $obj_array_result->uasort('compare_cell');
        $result_array= $obj_array_result->getArrayCopy();
        unset($obj_array_result);

        $init_row= $this->init_row_temporary*$this->max_row_in_page;
        $this->max_num_pages= (int)ceil((float)$nums/$this->max_row_in_page);
        if ($this->max_num_pages == 0) 
            $this->max_num_pages= 1; 
        
        $i= 0;
        foreach ($result_array as $row) {
            $continue= true;
            if ($this->limited) {
                if ($i < $init_row && $init_row != 0) {
                    $continue= false;
                }
                if ($i > ($init_row + $this->max_row_in_page)) {
                    break;
            }   }  
            
            ++$i;
            if (!$continue) 
                continue;
            
            $this->array_archivos[$row['id']]= array('id'=>$row['id'], 'sender'=>null);
            $array_result[]= $row;
        }
        
        $i= 0;
        $this->obj_ref= new Tref_archivo($this->clink);
        foreach ($this->array_archivos as $row) {
            ++$i;
            $sender= $this->listar_by_if_sender(true, $row['id']);
            $this->array_archivos[$row['id']]['sender']= $sender;
        }

        reset($this->array_archivos);
        return $array_result;        
    }
    
    public function listar_by_if_sender($if_sender= null, $id_archivo= null) {
        $id_archivo= !empty($id_archivo) ? $id_archivo : $this->id_archivo;
        
        $this->obj_ref->SetIdArchivo($id_archivo);
        $this->obj_ref->if_tarchivos= $this->if_tarchivos;
        $this->obj_ref->if_tarchivo_personas= $this->if_tarchivo_personas;
        
        return $this->obj_ref->getParticipantes_from_array($id_archivo, $if_sender);
    }
    
    public function addReg($cumplimiento= null) {
        if (is_null($cumplimiento)) 
            $cumplimiento= $this->cumplimiento;
        $observacion= setNULL_str($this->observacion);
        
        $sql= "insert into treg_archivo (id_archivo, id_archivo_code, cumplimiento, observacion, reg_fecha, id_usuario, id_responsable, ";
        $sql.= "cronos, situs) values ($this->id_archivo, '$this->id_archivo_code', $cumplimiento, $observacion, '$this->reg_fecha', ";
        $sql.= "$this->id_usuario, {$_SESSION['id_usuario']}, '$this->cronos', '$this->location')";
        $result= $this->do_sql_show_error('addCumplimiento', $sql);
        return $this->error;        
    }
    
    public function getReg() {
        $sql= "select * from treg_archivo where id_archivo = $this->id_archivo ";
        if (!empty($this->id_usuario)) 
            $sql.= "and id_usuario = $this->id_usuario ";
        $sql.= "order by cronos desc limit 1";
        $result= $this->do_sql_show_error('getReg', $sql);      
        $row= $this->cant > 0 ? $this->clink->fetch_array($result) : null;
        return $row;
    }


    private $array_traze;
    
    public function traze_archive($codigo_ref, $codigo_pass= null, $init_array= true) {
        if ($init_array) {
            if (isset ($this->array_traze)) 
                unset($this->array_traze);
        }
        
        $sql= "select * from tarchivos where antecedentes like '%$codigo_ref%' ";
        if (!empty($codigo_pass)) 
            $sql.= "or codigo like '%$codigo_pass%' ";
        $result= $this->do_sql_show_error('traze_archive', $sql);
        
        if (empty($this->cant))
            return;
        
        while ($row= $this->clink->fetch_array($result)) {
            if ($this->array_traze[$row['id']])
                continue;
            
            $array= array('id'=>$row['id'], 'if_output'=>$row['output'], 'codigo'=>$row['codigo'], 'id_usuario'=>$row['id_usuario'], 
                    'id_proceso'=>$row['id_proceso'], 'antecedentes'=>$row['entecedentes'], 'cronos'=>$row['cronos']);
            $this->array_traze[$row['id']]= $array;
            
            if (!empty($row['antecedentes']))
                $this->traze_archive ($row['codigo'], $row['antecedentes'], false);
        }
        
        $array_cronos= array();
        foreach ($this->array_traze as $id => $array) {
            $array_cronos[$id]= $array['cronos'];
        }    
        array_multisort($array_cronos, SORT_ASC, $this->array_traze);
        
        return $this->array_traze;
    }

    
}  