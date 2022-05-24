<?php

/*
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

include_once "../../../php/class/library.php";
include_once "../../../php/class/library_string.php";
include_once "../../../php/class/library_style.php";

include_once "../../../php/class/base.class.php";
include_once "../../../php/class/document.class.php";
include_once "../../../php/class/usuario.class.php";
include_once "organismo.class.php";
include_once "persona.class.php";

/**
 * Tref Archivo
 */
class Tref_archivo extends Tpersona {
    public $id_ref_archivo;
    public $if_sender;
    public $if_output;
    private $if_anonymous;

    public $array_personas;
    
    private $array_tarchivo_personas;   
    private $if_array_tarchivo_personas;
    
    public function __construct($clink) {
        Tpersona::__construct($clink);

        $this->clink = $clink;
        $this->if_array_tarchivo_personas= false;
    }

    public function SetIfAnonymous($id) {
        $this->if_anonymous= $id;
    }
    public function GetIfAnonymous() {
        return $this->if_anonymous;
    }
    
    public function add_person() {
        return Tpersona::add();
    }

    public function add() {
        $this->id_ref_archivo= null;

        $id_usuario= setNULL($this->id_usuario);
        $id_grupo= setNULL($this->id_grupo);
        $id_persona= setNULL($this->id_persona);
        $id_persona_code= setNULL_str($this->id_persona_code);
        
        $sql= "insert into tarchivo_personas (id_archivo, id_archivo_code, id_persona, id_persona_code, id_usuario, ";
        $sql.= "id_grupo, if_sender, if_anonymous, cronos, situs) value ($this->id_archivo, '$this->id_archivo_code', ";
        $sql.= "$id_persona, $id_persona_code, $id_usuario, $id_grupo, ".boolean2pg($this->if_sender). ", $this->if_anonymous, ";
        $sql.= "'$this->cronos', '$this->location')";
        
        $result = $this->do_sql_show_error('add', $sql, false);
        
        if ($result) {
            $this->id_ref_archivo = $this->clink->inserted_id("tarchivo_personas");
        } 
        
        return $this->error;
    }
    
    public function update_person() {
        return Tpersona::update();
    }
    
    public function update() {
        $id_usuario= setNULL($this->id_usuario);
        $id_grupo= setNULL($this->id_grupo);
        $id_persona= setNULL($this->id_persona);
        $id_persona_code= setNULL_str($this->id_persona_code);

        $sql= "update tarchivo_personas set id_persona= $id_persona, id_persona_code= $id_persona_code, ";
        $sql.= "id_usuario= $id_usuario, id_grupo= $id_grupo, cronos= '$this->cronos', situs= '$this->location' ";
        $sql.= "where id_archivo = $this->id_archivo ";
        
        $result= $this->do_sql_show_error('update', $sql, false);
        return $this->error;
    }

    public function _setUsuario($action= 'add') {
        $id_usuario= setNULL($this->id_usuario);
        $id_grupo= setNULL($this->id_grupo);
        
        if ($action == 'add') {
            $sql= "insert into tarchivo_personas (id_archivo, id_archivo_code, id_usuario, id_grupo, if_sender, ";
            $sql.= "cronos, situs) values ($this->id_archivo, '$this->id_archivo_code', $id_usuario, $id_grupo, ";
            $sql.= boolean2pg($this->if_sender).", '$this->cronos', '$this->location');";
        }
        
        if ($action == 'delete') {
            $sql= "delete from tarchivo_personas where 1 ";
            if (!empty($this->id_ref_archivo)) 
                $sql.= "and id = $this->id_ref_archivo ";
            if (!empty($this->id_archivo)) 
                $sql.= "and id_archivo = $this->id_archivo ";
            if (!empty($this->id_usuario)) 
                $sql.= "and id_usuario = $this->id_usuario ";
            if (!empty($this->id_grupo)) 
                $sql.= "and id_grupo = $this->id_grupo ";
        }
        
        $result= $this->do_sql_show_error("_setUsuario($action)", $sql);
        return $this->error;
    }
    
    public function setUsuario($action= 'add') {
        $this->id_persona= null;
        $this->id_persona_code= null;
        $this->id_grupo= null;
        
        $this->_setUsuario($action);
    }
    
    public function setGrupo($action= 'add') {
        $this->id_persona= null;
        $this->id_persona_code= null;
        $this->id_usuario= null;
        
        $this->_setUsuario($action);
    }
    
    private function setReg() {
        $obj= new Tarchivo($this->clink);
        $obj->SetIdArchivo($this->id_archivo);
        $obj->set_id_archivo_code($this->id_archivo_code);
        
        $i= 0;
        foreach ($this->array_usuarios as $user) {
            $obj->Set($user['id']);
            $row= $obj->getReg();
            
            if ($row['cumplimiento'] != _CUMPLIDA) {
                ++$i;
                $sql= "delete from treg_archivo where id_archivo = $this->id_archivo and id_usuario = ".$user['id'];
                $this->do_sql_show_error('delete', $sql);
            }
        }
        return $i;
    }
    
    public function eliminar() {        
        if (isset($this->array_usuarios)) unset($this->array_usuarios);
        $this->array_usuarios= array();
        
        $i= 0;
        
        if ($this->id_ref_archivo) {
            $sql= "select * from tarchivo_personas where id = $this->id_ref_archivo ";
            $result= $this->do_sql_show_error('delete', $sql); 
            $row= $this->clink->fetch_array($result);
            
            $this->id_persona= $row['id_persona'];
            $this->id_usuario= $row['id_usuario'];
            $this->id_grupo= $row['id_grupo'];
        }
        
        if (!empty($this->id_persona)) {
            $obj_pers= new Tpersona($this->clink);
            $obj_pers->Set($this->id_persona);
            $id_usuario= $obj_pers->GetIdUsuario();
            
            $array= array('id'=>$id_usuario, 'nombre'=>$obj_pers->GetNombre(), 'email'=>$obj_pers->GetMail_address(),'cargo'=>$obj_pers->GetCargo(),
                        'eliminado'=>$obj_pers->GetEliminado(), 'usuario'=>$obj_pers->GetUsuario(), '_id'=>$id_usuario,
                        'id_proceso'=>$obj_pers->GetIdProceso(), 'id_proceso_code'=>$obj_pers->get_id_proceso_code());  
            ++$i;
            $this->array_usuarios[]= $array; 
        }
        
        if (!empty($this->id_usuario)) {
            $obj_user= new Tusuario($this->clink);
            $obj_user->Set($this->id_usuario);
            $array= array('id'=>$this->id_usuario, 'nombre'=>$obj_user->GetNombre(), 'email'=>$obj_user->GetMail_address(),'cargo'=>$obj_user->GetCargo(),
                        'eliminado'=>$obj_user->GetEliminado(), 'usuario'=>$obj_user->GetUsuario(), '_id'=>$this->id_usuario,
                        'id_proceso'=>$obj_user->GetIdProceso(), 'id_proceso_code'=>$obj_user->get_id_proceso_code());            
            ++$i;
            $this->array_usuarios[]= $array; 
        }
        
        if (!empty($this->id_grupo)) {
            $obj_grp= new Tgrupo($this->clink);
            $obj_grp->Set($this->id_grupo);
            $obj_grp->listar_usuarios();
            
            $i= $obj_grp->GetCantidad();
            $this->array_usuarios= $obj_grp->array_usuarios;
        }
        
        if ($i == $this->setReg()) {
            /*
            $sql= "delete from tarchivo_personas where id_archivo is not null ";
            if (!empty($this->id_archivo)) $sql.= "and id_archivo = $this->id_archivo ";
            if (!empty($this->id_persona)) $sql.= "and id_persona = $this->id_persona ";
            if (!empty($this->id_usuario)) $sql.= "and id_usuario = $this->id_usuario ";
            if (!empty($this->id_grupo)) $sql.= "and id_grupo = $this->id_grupo ";
            if (!empty($this->id_ref_archivo)) $sql.= "and id = $this->id_ref_archivo ";

            $this->do_sql_show_error('delete', $sql);
            */
            
           $this->SetActivo(false);
        }

        return $this->error;
    }
    
    private function SetActivo($activo= false) {
        $sql= "update tarchivo_personas set activo = ". boolean2pg($activo)." where id_archivo is not null ";
        if (!empty($this->id_archivo)) 
            $sql.= "and id_archivo = $this->id_archivo ";
        if (!empty($this->id_persona)) 
            $sql.= "and id_persona = $this->id_persona ";
        if (!empty($this->id_usuario)) 
            $sql.= "and id_usuario = $this->id_usuario ";
        if (!empty($this->id_grupo)) 
            $sql.= "and id_grupo = $this->id_grupo ";
        if (!empty($this->id_ref_archivo)) $sql.= "and id = $this->id_ref_archivo ";

        $this->do_sql_show_error('SetActivo', $sql);         
    }

    public function listar () {
        $sql= "select distinct tpersonas.*, id_persona, id_persona_code from tarchivo_personas, ";
        $sql.= "tpersonas where tpersonas.id = tarchivo_personas.id_persona and id_responsable is null ";
        if (!empty($this->id_archivo)) 
            $sql.= "and id_archivo = $this->id_archivo ";
        if (!empty($this->id_persona)) 
            $sql.= "and id_persona = $this->id_persona ";
        if (!empty($this->id_usuario)) 
            $sql.= "and tarchivo_personas.id_usuario = $this->id_usuario ";
        if (!empty($this->id_grupo)) 
            $sql.= "and id_grupo = $this->id_grupo ";
        if (!is_null($this->if_sender)) 
            $sql.= "and if_sender = ". boolean2pg($this->if_sender)." "; 
        $sql.= "and tarchivo_personas.activo = ".boolean2pg(1)." ";
        
        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }
  
    private function get_array_tarchivo_personas() {
        if (isset($this->array_tarchivo_personas)) unset($this->array_tarchivo_personas);
        $this->array_tarchivo_personas= array();
        
        $sql= "select * from _tarchivo_personas";
        $result= $this->do_sql_show_error('delete', $sql);
        
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $this->array_tarchivo_personas[$row['id_archivo']][]= $row;
        }
        if ($i) 
            $this->if_array_tarchivo_personas= true;       
    }
    
    public function get_personas($if_sender= null) {
        if (isset($this->array_personas)) unset($this->array_personas);
        $this->array_personas= array();
        
        reset($this->array_tarchivo_personas);
        $i= 0;
        foreach ($this->array_tarchivo_personas[$this->id_archivo] as $row) {
            if (empty($row['id_persona']))
                continue;
            if ($row['if_sender'] != $if_sender)
                continue;
            
            ++$i;
            $array= array('id'=>$row['id_persona'], 'nombre'=>$row['nombre'], 'cargo'=>$row['cargo'],
                    'id_organismo'=>$row['id_organismo'], 'lugar'=>$row['lugar'], 'if_anonymous'=>$row['if_anonymous']);
                $this->array_personas[$row['id_persona']]= $array;
        }
        
        return $this->array_personas;        
    }
    
    public function get_usuarios($if_sender= null) {
        if (isset($this->array_usuarios)) unset($this->array_usuarios);
        $this->array_usuarios= array();
        
        reset($this->array_tarchivo_personas);
        $i= 0;
        foreach ($this->array_tarchivo_personas[$this->id_archivo] as $row) {
            if (empty($row['id_usuario']))
                continue;
            if ($row['if_sender'] != $if_sender)
                continue;
            
            ++$i;
            $array= array('id'=>$row['id_usuario'], 'nombre'=>$row['nombre'], 'cargo'=>$row['cargo'],
                    'id_organismo'=>$row['id_organismo'], 'lugar'=>$row['lugar'], 'if_anonymous'=>$row['if_anonymous']);
                $this->array_usuarios[$row['id_usuario']]= $array;
        }
        
        return $this->array_usuarios;        
    }    

    public function get_grupos($if_sender= null) {
        if (isset($this->array_grupos)) unset($this->array_grupos);
        $this->array_grupos= array();
        
        reset($this->array_tarchivo_personas);
        $i= 0;
        foreach ($this->array_tarchivo_personas[$this->id_archivo] as $row) {
            if (empty($row['id_grupo']))
                continue;
            if ($row['if_sender'] != $if_sender)
                continue;
            
            ++$i;
            $array= array('id'=>$row['id_grupo'], 'nombre'=>$row['nombre'], 'cargo'=>$row['cargo'],
                    'id_organismo'=>$row['id_organismo'], 'lugar'=>$row['lugar'], 'if_anonymous'=>$row['if_anonymous']);
                $this->array_grupos[$row['id_grupo']]= $array;
        }
        
        return $this->array_grupos;        
    }  
    
    public function getParticipantes_from_array($id_archivo= null, $if_sender= null) {
        $this->id_archivo= !empty($id_archivo) ? $id_archivo : $this->id_archivo;
        $i= 0;
        $sender= '';
        if (!$this->if_array_tarchivo_personas)
            $this->get_array_tarchivo_personas ();
        
        $obj_org= new Torganismo($this->clink);
        
        $result= $this->get_personas($if_sender);
        foreach ($result as $row) {
            ++$i;
            if ($i > 1) $sender.= "<br />";
            $sender.= $row['nombre'];
            if (!empty($row['cargo'])) 
                $sender.= ", {$row['cargo']}";
            
            if (!empty($row['id_organismo'])) {
                $obj_org->Set($row['id_organismo']);
                $sender.= ", ".$obj_org->GetCodigo();
            }
            if (!empty($row['lugar'])) 
                $sender.= ", {$row['lugar']}";            
        }
        
        $result= $this->get_usuarios($if_sender);
        foreach ($result as $row) {
            ++$i;
            if ($i > 1) $sender.= "<br />";   
            $sender.= $row['nombre'];
            if (!empty($row['cargo'])) 
                $sender.= ", {$row['cargo']}";
        }
        
        $result= $this->get_grupos($if_sender);
        foreach ($result as $row) {
            ++$i;
            if ($i > 1) $sender.= "<br />";   
            $sender.= $row['nombre'];
        } 
        
        return $i > 0 ? $sender : null;
    }    
    
    public function listar_usuarios($use_id_user= true, $if_sender= null) {
        $use_id_user= !is_null($use_id_user) ? $use_id_user : true;
        $table= $this->if_tarchivo_personas ? "_tarchivo_personas" : "tarchivo_personas";
        
        $sql= "select distinct tusuarios.*, tusuarios.id as _id, nombre, email, cargo, $table.cronos as _cronos ";
        $sql.= "from tusuarios, $table where 1 ";
        $sql.= $this->if_tarchivo_personas ? "and tusuarios.id = $table.id_usuario_reg " : "and tusuarios.id = $table.id_usuario ";
        if (!empty($this->id_archivo)) 
            $sql.= "and $table.id_archivo = $this->id_archivo ";
        if (!is_null($if_sender)) 
            $sql.= "and if_sender = ". boolean2pg($if_sender)." ";
        $sql.= "order by nombre asc ";

        return $this->_list_user($sql,$use_id_user);
    }

    public function listar_grupos($if_sender= null) {
        $table= $this->if_tarchivo_personas ? "_tarchivo_personas" : "tarchivo_personas";
        
        $sql= "select distinct id_grupo as _id, nombre from tgrupos, $table ";
        $sql.= "where tgrupos.id = $table.id_grupo ";
        if (!empty($this->id_archivo)) 
            $sql.= "and $table.id_archivo = $this->id_archivo ";
        if (!is_null($if_sender)) 
            $sql.= "and if_sender = ". boolean2pg($if_sender)." ";
        $sql.= "order by nombre asc ";
        
        return $this->_list_group($sql);
    }
    
    public function listar_personas($if_sender= null, $flag= true) {
        if (isset($this->array_personas)) unset($this->array_personas);
        $this->array_personas= array();
        
        if ($this->if_tarchivo_personas) {
            $sql= "select distinct *, id_persona as _id from _tarchivo_personas where 1 ";
            if (!empty($this->id_archivo)) 
                $sql.= "and _tarchivo_personas.id_archivo = $this->id_archivo ";
        } else {
            $sql= "select distinct tpersonas.*, tpersonas.id as _id, if_anonymous from tpersonas, tarchivo_personas ";
            $sql.= "where tpersonas.id = tarchivo_personas.id_persona ";
            if (!empty($this->id_archivo)) 
                $sql.= "and tarchivo_personas.id_archivo = $this->id_archivo ";            
        }
        if (!is_null($if_sender)) 
            $sql.= "and if_sender = ". boolean2pg($if_sender)." ";
        $sql.= "order by nombre asc ";
        
        $result= $this->do_sql_show_error('listar_personas', $sql);
       
        if ($flag) 
            return $result;
        
        $i= 0;
        while ($row= $this->clink->fecth_array($result)) {
            ++$i;
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'id_organismo'=>$row['id_organismo'], 
                    'lugar'=>$row['lugar'], 'if_anonymous'=>$row['if_anonymous']);
                $this->array_personas[$row['_id']]= $array;
        }
        
        return $this->array_personas;
    }
    
    public function getParticipantes($id_archivo= null, $if_sender= null) {
        $this->id_archivo= !empty($id_archivo) ? $id_archivo : $this->id_archivo;
        $i= 0;
        $sender= '';
        
        $obj_org= new Torganismo($this->clink);
        
        $result= $this->listar_personas($if_sender);
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            if ($i > 1) $sender.= "<br />";
            $sender.= $row['nombre'];
            if (!empty($row['cargo'])) 
                $sender.= ", {$row['cargo']}";
            
            if (!empty($row['id_organismo'])) {
                $obj_org->Set($row['id_organismo']);
                $sender.= ", ".$obj_org->GetCodigo();
            }
            if (!empty($row['lugar'])) 
                $sender.= ", {$row['lugar']}";            
        }
        
        $result= $this->listar_usuarios(true, $if_sender);
         while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            if ($i > 1) $sender.= "<br />";   
            $sender.= $row['nombre'];
            if (!empty($row['cargo'])) 
                $sender.= ", {$row['cargo']}";
        }
        
        $result= $this->listar_grupos($if_sender);
         while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            if ($i > 1) $sender.= "<br />";   
            $sender.= $row['nombre'];
        } 
        
        return $i > 0 ? $sender : null;
    }
    
    public function _get_user() {
        if (isset($this->array_usuarios)) unset($this->array_usuarios);
        $this->array_usuarios= array();
        
        $sql= "select distinct tusuarios.*, tusuarios.id as _id from tusuarios, treg_archivo ";
        $sql.= "where tusuarios.id = treg_archivo.id_usuario and treg_archivo.id_archivo = $this->id_archivo ";
        if (!is_null($this->if_sender)) 
            $sql.= "and treg_archivo.if_sender = ". boolean2pg($this->if_sender)." ";
        $result= $this->do_sql_show_error('_get_user', $sql);
       
        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                        'eliminado'=>$row['eliminado'], 'usuario'=>$row['usuario'], '_id'=>$row['_id'],
                        'id_proceso'=>$row['id_proceso'], 'id_proceso_code'=>$row['id_proceso_code']);
            $this->array_usuarios[$row['_id']]= $array;
        }
        
        return $i;
    }    
}
