<?php

/*
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */


include_once "../../../php/class/usuario.class.php";
include_once "../../../php/class/grupo.class.php";
include_once "../../../php/class/tmp_tables_planning.class.php";
include_once "../../../php/class/evento.class.php";
include_once "../../../php/class/document.class.php";

include_once "organismo.class.php";
include_once "persona.class.php";
include_once "ref_archivo.class.php";

class Ttmp_tables_archivos extends Tdocumento {
    protected $date_init,
            $date_end;
    protected $only_event;

    public $if_output;
    public $if_tarchivos;
    public $if_tarchivo_personas;

    protected $array_ids,
           $array_archivo_ids,
           $array_lugar_ids,
           $array_numero_ids;
    
    private $array_tarchivos;
    protected $array_tpersonas;
    protected $array_tgrupos;
    protected $array_tusuarios;
    
    public function SetIfOutput($id) {
        $this->if_output = $id;
    }
    public function GetIfOutput() {
        return $this->if_output;
    }    
    
    public function __construct($clink) {
        Tdocumento::__construct($clink);
        $this->clink= $clink;
        
        $this->if_tarchivos= false;
        $this->if_tarchivo_personas= false;
    }
    
    public function Set_tmp_tables() {
        $this->debug_time('create_tmp_tarchivos');
        $this->create_tmp_tarchivos();
        $this->debug_time('create_tmp_tarchivos');
        $this->debug_time('create_tmp_tarchivo_personas');
        $this->create_tmp_tarchivo_personas();
        $this->debug_time('create_tmp_tarchivo_personas');
    }
    
    private function sql_init() {
        $sql= "select * from tarchivos where 1 ";
        if (!$this->do_filter_by_fin_plan) {
            if (!empty($this->date_init) && !empty($this->date_end)) 
                $sql.= "and (fecha_entrega >= '$this->date_init' and fecha_entrega <= '$this->date_end') ";
            if (!empty($this->date_init) && empty($this->date_end)) 
                $sql.= "and fecha_entrega >= '$this->date_init' ";            
            if (empty($this->date_init) && !empty($this->date_end)) 
                $sql.= "and fecha_entrega <= '$this->date_end' ";
        } else {
            if (!empty($this->date_init) && !empty($this->date_end)) 
                $sql.= "and (fecha_fin_plan >= '$this->date_init' and fecha_fin_plan <= '$this->date_end') ";      
        }    
        if (!empty($this->id_responsable)) 
            $sql.= "and tarchivos.id_responsable = $this->id_responsable ";
        if (!empty($this->id_proceso)) 
            $sql.= "and tarchivos.id_proceso = $this->id_proceso ";
        if (!is_null($this->if_output)) 
            $sql.= "and tarchivos.if_output = ".boolean2pg($this->if_output)." ";        
   
        return $sql;
    }    
    
    private function _create_tmp_tarchivos() {
        $sql= "drop table if exists ".stringSQL("_tarchivos");
        $this->do_sql_show_error('create_tmp_tarchivos', $sql);
//
        $fields= $this->clink->fields("tarchivos");
        $nums_fields= count($fields);
// 
        $sql= "CREATE TEMPORARY TABLE _tarchivos (";
        $i= 0;
        foreach ($fields as $field) {
            $sql.= showFieldSQL($field);
            ++$i;
            $sql.= $i < $nums_fields ? ", \r\n" : "";
        }
        $sql.= "); ";

        $this->do_sql_show_error('_create_tmp_tarchivos', $sql);
        return $this->error;        
    }
    
    protected function create_tmp_tarchivos($date_init= null, $date_end= null, $only_event= null) { 
        $this->_create_tmp_tarchivos();
        
        $sql= "insert into _tarchivos ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : " ";  
        $sql.= $this->sql_init($date_init, $date_end, $only_event);
        
        $this->do_sql_show_error('create_tmp_tarchivos', $sql);
        $this->if_tarchivos= is_null($this->error) ? true : false;
        return $this->error;
    }    

    private function _create_tmp_tarchivo_personas () {
        $sql= "drop table if exists ".stringSQL("_tarchivo_personas");
        $this->do_sql_show_error('_create_tmp_tarchivo_personas', $sql);
//         
        $sql= "CREATE TEMPORARY TABLE _tarchivo_personas (";
          $sql.= "id int(11) DEFAULT NULL,";
          $sql.= "id_archivo int(11) DEFAULT NULL,";
          $sql.= "id_archivo_code CHAR(12) DEFAULT NULL,";
          $sql.= "id_persona int(11) DEFAULT NULL,";
          $sql.= "id_responsable int(11) DEFAULT NULL,";
          $sql.= "id_usuario int(11) DEFAULT NULL,";
          $sql.= "id_grupo int(11) DEFAULT NULL,";
          $sql.= "if_sender tinyint(1) DEFAULT NULL,";          
          $sql.= "if_anonymous tinyint(1) DEFAULT NULL,";              
          $sql.= "activo tinyint(1) DEFAULT NULL, ";          
          $sql.= "nombre varchar(255) DEFAULT NULL,";
          $sql.= "cargo varchar(255) DEFAULT NULL,";
          $sql.= "id_organismo int(11) DEFAULT NULL,";
          $sql.= "lugar varchar(180) DEFAULT NULL,";
          $sql.= "direccion varchar(255) DEFAULT NULL,";
          $sql.= "id_proceso int(11) DEFAULT NULL,";
          $sql.= "cronos datetime DEFAULT NULL";
        $sql.= ");";
        
        $result= $this->do_sql_show_error('_create_tmp_tarchivo_personas', $sql);
        return $this->error; 
    }
    
    private function set_array_archivos($id) {
        $sql= "select * from tarchivo_personas where tarchivo_personas.id_archivo = $id; ";
        $result= $this->do_sql_show_error('create_tmp_tarchivo_personas', $sql);
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $this->array_tarchivos[$row['id_archivo']][]= $row;
        }
        return $i;
    }
    
    protected function create_tmp_tarchivo_personas ($create_view= null) {
        $this->_create_tmp_tarchivo_personas();

        $sql= "select distinct id from _tarchivos";
        $result= $this->do_sql_show_error('create_tmp_tarchivo_personas', $sql);
        $num_rows= $this->clink->num_rows($result);

        $this->debug_time('create_tmp_tarchivo_personas_1');

        while ($row= $this->clink->fetch_array($result)) 
            $this->set_array_archivos($row['id']);            
   
        $this->debug_time('create_tmp_tarchivo_personas_1');
        
        $this->debug_time('update_tmp_tarchivo_personas');    
        $this->update_tmp_tarchivo_personas();
        $this->debug_time('update_tmp_tarchivo_personas');
      
        $this->if_tarchivo_personas= is_null($this->error) ? true : false; 
        return $this->error;
    }
    
    private function get_array_personas() {        
        $sql= "select * from tpersonas where id in (select distinct id_persona from tarchivo_personas)"; 
        $result= $this->do_sql_show_error('update_tmp_tarchivo_personas', $sql);
        $num_rows= $this->clink->num_rows($result); 

        $array= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array[$row['id']]= array('id_persona'=>$row['id_persona'], 'nombre'=>$row['nombre'], 
                    'cargo'=>$row['cargo'], 'id_organismo'=>$row['id_organismo'], 'lugar'=>$row['lugar'], 
                    'direccion'=>$row['direccion'], 'id_proceso'=>$row['id_proceso']);
        }
        return $array;
    }
    
    private function get_array_usuarios() {        
        $sql= "select * from tusuarios where id in (select distinct id_usuario from tarchivo_personas)"; 
        $result= $this->do_sql_show_error('update_tmp_tarchivo_personas', $sql);
        $num_rows= $this->clink->num_rows($result); 

        $array= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array[$row['id']]= array('id_persona'=>null, 'nombre'=>$row['nombre'], 'cargo'=>$row['cargo'], 
                    'id_organismo'=>null, 'lugar'=>null, 'direccion'=>null, 'id_proceso'=>$row['id_proceso']);
        }
        return $array;
    }

    private function get_array_grupos() {        
        $sql= "select * from tgrupos where id in (select distinct id_grupo from tarchivo_personas)"; 
        $result= $this->do_sql_show_error('update_tmp_tarchivo_personas', $sql);
        $num_rows= $this->clink->num_rows($result); 

        $array= array();
        while ($row= $this->clink->fetch_array($result)) {
            $array[$row['id']]= array('id_persona'=>null, 'nombre'=>$row['nombre'], 'cargo'=>null, 
                    'id_organismo'=>null, 'lugar'=>null, 'direccion'=>null, 'id_proceso'=>null);
        }
        return $array;
    }
    
    private function _update_tmp_tarchivo_personas($row) {
        $id= $row['id'];
        $id_archivo= $row['id_archivo'];
        $id_archivo_code= setNULL_str($row['id_archivo_code']);
        $id_persona= setNULL($row['id_persona']);
        $id_responsable= setNULL_empty($row['id_responsable']);
        $id_usuario= setNULL($row['id_usuario']);
        $id_grupo= setNULL($row['id_grupo']);
        $if_sender= setNULL($row['if_sender']);          
        $if_anonymous= setNULL($row['if_anonymous']);              
        $activo= boolean2pg($row['activo']);          
        $nombre= setNULL_str(null);
        $cargo= setNULL_str(null);
        $id_organismo= setNULL_str(null);
        $lugar= setNULL_str(null);
        $direccion= setNULL_str(null);
        $id_proceso= setNULL(null);
        $cronos= setNULL_str($row['cronos']);  

        if (!empty($row['id_persona'])) {
            $nombre= setNULL_str($this->array_tpersonas[$row['id_persona']]['nombre']);
            $cargo= setNULL_str($this->array_tpersonas[$row['id_persona']]['cargo']);
            $lugar= setNULL_str($this->array_tpersonas[$row['id_persona']]['lugar']);
            $direccion= setNULL_str($this->array_tpersonas[$row['id_persona']]['direccion']);
            $id_organismo= setNULL_str($this->array_tpersonas[$row['id_persona']]['id_organismo']);
            $id_proceso= setNULL($this->array_tpersonas[$row['id_persona']]['id_proceso']);

        } elseif (!empty($row['id_usuario'])) {
            $nombre= setNULL_str($this->array_tusuarios[$row['id_usuario']]['nombre']);
            $cargo= setNULL_str($this->array_tusuarios[$row['id_usuario']]['cargo']);
            $id_proceso= setNULL($this->array_tusuarios[$row['id_usuario']]['id_proceso']); 

        } elseif (!empty ($row['id_grupo'])) {
            $nombre= setNULL_str($this->array_tgrupos[$row['id_grupo']]['nombre']);
        }

        $sql= "insert into _tarchivo_personas (id, id_archivo, id_archivo_code, id_persona, id_usuario, id_grupo, ";
        $sql.= "if_sender, if_anonymous, activo, id_organismo, id_proceso, nombre, cargo, cronos) values ($id, $id_archivo, ";
        $sql.= "$id_archivo_code, $id_persona, $id_usuario, $id_grupo, $if_sender, $if_anonymous, $activo, $id_organismo, ";
        $sql.= "$id_proceso, $nombre, $cargo, $cronos); "; 
        return $sql;
    }
    
    private function update_tmp_tarchivo_personas() {
        $this->array_tpersonas= $this->get_array_personas();
        $this->array_tusuarios= $this->get_array_usuarios();
        $this->array_tgrupos= $this->get_array_grupos();
        
        $sql= "select * from _tarchivos";
        $result= $this->do_sql_show_error('update_tmp_tarchivo_personas', $sql);
        $num_rows= $this->clink->num_rows($result);
        
        $sql= null;
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            foreach ($this->array_tarchivos[$row['id']] as $array) {
                $sql.= $this->_update_tmp_tarchivo_personas($array);

                ++$i;
                if ($i > 500) {                
                    $this->do_multi_sql_show_error('update_tmp_tarchivo_personas', $sql); 
                    $sql= null;
                    $i= 0;
                }            
        }   }
        if ($sql) 
            $this->do_multi_sql_show_error('update_tmp_tarchivo_personas', $sql);     
    }

    protected function refresh_tmp_tarchivos() {
        $sql= "delete from _tarchivos";
        $result= $this->do_sql_show_error('refresh_tmp_tarchivos', $sql);
        
        $i= 0;
        $sql= null;
        foreach ($this->array_ids as $id) {  
            $sql.= "insert into _tarchivos ";
            $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : " ";              
            $sql.= "select * from tarchivos where id = $id; ";
            
            ++$i;
            if ($i > 500) {                
                $this->do_multi_sql_show_error('refresh_tmp_tarchivos', $sql);          
                $sql= null;
                $i= 0;
            }            
        }
        if ($sql) 
            $this->do_multi_sql_show_error('refresh_tmp_tarchivos', $sql);
    }
    
    protected function purge_tarchivo_personas() {
        $sql= "select distinct id_archivo as _id from _tarchivo_personas";
        $result= $this->do_sql_show_error('purge_tarchivo_personas', $sql);
        
        $sql= null;
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (!array_search($row['_id'], $this->array_ids)) 
                $sql.= "delete from _tarchivo_personas where id_archivo = {$row['_id']}; ";
            
            ++$i;
            if ($i > 500) { 
                $this->do_multi_sql_show_error('purge_tarchivo_personas', $sql);          
                $sql= null;
                $i= 0;
            }            
        }
        if ($sql) 
            $this->do_multi_sql_show_error('purge_tarchivo_personas', $sql);
    }
} 
