<?php
/**
 * Description of persona
 *
 * @author mustelier
 */

include_once "../../../php/class/base.class.php";
include_once "../../../php/class/usuario.class.php";

include_once "organismo.class.php";

class Tpersona extends Torganismo {
    private $obj_user; 
    public $if_tarchivo_personas;
    
    public function __construct($clink) {
        Torganismo::__construct($clink);
        $this->clink= $clink;
    }    
    
    public function Set($id= null, $noIdentidad= null) {
        $id= !empty($id) ? $id : $this->id_persona;
        
        $sql= "select * from tpersonas where 1 ";
        if (!empty($id)) 
            $sql.= "and id = $id ";
        if (empty($id) && !empty($this->id_responsable)) 
            $sql.= "and id_responsable = $this->id_responsable ";
        if (!empty($noIdentidad)) 
            $sql.= "and noIdentidad = ".stringSQL ($noIdentidad); 
          
        $result= $this->do_sql_show_error('Set', $sql);
        if (!$result) return $this->error;

        $row= $this->clink->fetch_array($result);

        $this->id= $row['id'];
        $this->id_persona= $this->id;
        $this->id_code= $row['id_code'];
        $this->id_persona_code= $this->id_code;
        
        $this->id_responsable= $row['id_responsable'];
        if (!empty($this->id_responsable)) {
            Tusuario::Set($this->id_responsable);
        }        
        
        $this->telefono= $row['telefono'];
        $this->movil= $row['movil'];
        $this->email= $row['email'];

        $this->provincia= $row['provincia'];
        $this->municipio= $row['municipio'];
        $this->direccion= stripslashes($row['direccion']);
        $this->lugar= stripslashes($row['lugar']);

        $this->noIdentidad= $row['noIdentidad'];
        $this->nombre= stripslashes($row['nombre']);
        $this->id_organismo= $row['id_organismo'];
        $this->cargo= stripslashes($row['cargo']);

        $this->id_proceso= $row['id_proceso'];
        $this->id_proceso_code= $row['id_proceso_code'];
        
        $this->activo= boolean($row['activo']);
        
        return null;
    } 
    
    public function add() {
        $this->id= null;
        $this->id_code= null;
        
        $cargo= setNULL_str($this->cargo);
        $id_organismo= setNULL($this->id_organismo);
        $telefono= setNULL_str($this->telefono);
        $movil= setNULL_str($this->movil);
        $email= setNULL_str($this->email);
        
        $provincia= setNULL_str($this->provincia);
        $municipio= setNULL_str($this->municipio);
        $direccion= setNULL_str($this->direccion);
        $noIdentidad= setNULL_str($this->noIdentidad);
        
        $id_responsable= setNULL($this->id_responsable);
        $lugar= setNULL_str($this->lugar); 
        
        $id_proceso= setNULL($this->id_proceso);
        $id_proceso_code= setNULL_str($this->id_proceso_code);
        
        $sql= "insert into tpersonas (nombre,".stringSQL("noIdentidad").",cargo, id_organismo, email, telefono, movil, ";
        $sql.= "provincia, municipio, direccion, lugar, id_responsable, id_proceso, id_proceso_code, id_usuario, cronos, ";
        $sql.= "situs) values ('$this->nombre', $noIdentidad, $cargo, $id_organismo, $email, $telefono, $movil, $provincia, $municipio, ";
        $sql.= "$direccion, $lugar, $id_responsable, $id_proceso, $id_proceso_code, {$_SESSION['id_usuario']},'$this->cronos', ";
        $sql.= "'$this->location')";
        
        $result = $this->do_sql_show_error('add', $sql, false);
        
        if ($result) {
            $this->id= $this->clink->inserted_id("tpersonas");

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('tpersonas', 'id', 'id_code');
            $this->id_code = $this->obj_code->get_id_code();

        } else {
            if (!empty($this->noIdentidad)) {
                $array= $this->buscar($this->id_responsable, $this->noIdentidad);
                $this->id= $array['id'];  
                $this->id_code= $array['id_code']; 
               
                if (!empty($this->id)) {
                    $this->id_persona= $this->id;
                    Tpersona::update();
                    if (!empty($this->error)) return $this->error;
                }    
            }
        }
        
        $this->id_persona= $this->id;
        $this->id_persona_code= $this->id_code;        
        
        return $this->error;
    }
    
     public function buscar($id_usuario= null, $noIdentidad= null) {
        $sql= "select * from tpersonas where 1 ";
        if (!empty($id_usuario)) 
            $sql.= "and id_responsable = $id_usuario ";
        if (!empty($noIdentidad)) 
            $sql.= "and noIdentidad = '$this->noIdentidad' "; 
            
        $result= $this->do_sql_show_error('buscar', $sql);  
        $row= $this->clink->fetch_array($result);
        return $row;
    }   

    public function update_from_usuario($id= null) {
        $id= !empty($id) ? $id : $this->id_responsable; 
        Tusuario::Set($id);
        
        $this->id_responsable= $id;
        Tpersona::add();
        
        return $this->error;
    }
    
    public function update() {
        $cargo= setNULL_str($this->cargo);
        $id_organismo = setNULL($this->id_organismo);
        $telefono = setNULL_str($this->telefono);
        $movil = setNULL_str($this->movil);
        $email = setNULL_str($this->email);

        $provincia = setNULL_str($this->provincia);
        $municipio = setNULL_str($this->municipio);
        $direccion = setNULL_str($this->direccion);
        $noIdentidad = setNULL_str($this->noIdentidad);
        
        $id_responsable= setNULL_empty($this->id_responsable);
        $lugar= setNULL_str($this->lugar);
        
        $sql = "update tpersonas set nombre= '$this->nombre'," . stringSQL("noIdentidad") . "=$noIdentidad,";
        $sql.= "cargo=$cargo, id_organismo=$id_organismo,email=$email,telefono=$telefono, movil=$movil,provincia=$provincia, ";
        $sql.= "municipio=$municipio, direccion=$direccion, id_responsable= $id_responsable, lugar= $lugar, ";
        $sql.= "id_usuario={$_SESSION['id_usuario']}, cronos='$this->cronos', ";
        $sql.= "situs='$this->location' where id = $this->id_persona";

        $result = $this->do_sql_show_error('add', $sql);
    }
    
    public function eliminar($id= null) {
        $id = !empty($id) ? $id : $this->id_persona;
        $sql = "delete from tpersonas where id = $id";

        $result = $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
        
        $this->SetActivo(false, $id);
    }
    
    private function SetActivo($activo= false, $id= null) {
        $id = !empty($id) ? $id : $this->id_persona;
        $sql= "update tpersonas set activo= ". boolean2pg($activo)." where id = $id";
        $this->do_sql_show_error('SetActivo', $sql);
        return $this->error;        
    }
    
    public function listar_lugares()  {
        $sql= "select distinct lugar from tpersonas where lugar is not null";
        $result= $this->do_sql_show_error('list_lugares', $sql);
        $lugares= array();
        while ($row= $this->clink->fetch_array($result)) {
            $lugares[]= $row[0];
        }
        return $lugares;
    }
    
    public function listar($only_persons= true, $flag= false) {
        $only_persons= !is_null($only_persons) ? $only_persons : false;
        $flag= !is_null($flag) ? $flag : false;
        
        $sql= "select *, id as _id from tpersonas where activo = ". boolean2pg(1)." ";
        if ($only_persons) 
            $sql.= "and id_responsable is null and (nombre is not null or cargo is not null) ";
        if (!empty($this->municipio)) 
            $sql.= "and municipio = '$this->municipio' ";
        if (!empty($this->provincia)) 
            $sql.= "and provincia = '$this->provincia' ";
        if (!empty($this->id_organismo)) 
            $sql.= "and id_organismo = $this->id_organismo ";
        if (!empty($this->id_proceso)) 
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($this->lugar)) 
            $sql.= "and lugar = '$this->lugar' ";
        $sql.= "order by nombre asc, cargo asc";
        
        $result= $this->do_sql_show_error('listar', $sql);
        if (!$flag) return $result;
        
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            $nombre= strtolower(setNULL_str($row['nombre']));
            $cargo= strtolower(setNULL_str($row['cargo']));
            if (!empty($array_ids[$nombre][$cargo])) 
                continue;
            $array_ids[$nombre][$cargo]= 1;
            
            $array_persons[]= $row; 
        }
        return $array_persons;
    }
    
} // Tpersona
