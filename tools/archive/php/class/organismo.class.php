<?php
/**
 * Description of persona
 *
 * @author mustelier
 */

include_once "../../../php/class/usuario.class.php";
include_once "../../../php/class/grupo.class.php";
include_once "../../../php/class/tmp_tables_planning.class.php";
include_once "../../../php/class/evento.class.php";

class Torganismo extends Tusuario {
    protected $id_organismo; 
    protected $id_organismo_code;
    protected $codigo;
    protected $activo;
    
    private $use_anual_plan;

    public $array_organismos;

    public function GetActivo() {
        return $this->activo;
    }
    public function GetCodigo() {
        return $this->codigo;
    }
    public function SetCodigo($id) {
        $this->codigo= $id;
    }
    public function GetIdOrganismo() {
        return $this->id_organismo;
    }
    public function SetIdOrganismo($id) {
        $this->id_organismo= $id;
    }
    public function set_id_organismo_code($id) {
        $this->id_organismo_code= $id;
    }    
    public function get_id_organismo_code() {
        return $this->id_organismo_code;
    }
    public function SetUseAnualPlan($id) {
        $this->use_anual_plan= $id;
    }
    public function GetUseAnualPlan($id) {
        return $this->use_anual_plan;
    }


    public function __construct($clink) {
        Tusuario::__construct($clink);
        $this->clink= $clink;
    }
    
    public function Set($id= null) {
        $id= !empty($id) ? $id : $this->id_organismo;
        
        $sql= "select * from torganismos where id = $id ";            
        $result= $this->do_sql_show_error('Set', $sql);
        if (!$result) 
            return $this->error;

        $row= $this->clink->fetch_array($result);

        $this->id= $row['id'];
        $this->id_organismo= $this->id;
        $this->id_code= $row['id_code'];
        $this->id_organismo_code= $this->id_code;
                
        $this->nombre= stripslashes($row['nombre']);
        $this->codigo= $row['codigo'];
        $this->descripcion= stripslashes($row['descripcion']);
        $this->activo= boolean($row['activo']);
        $this->use_anual_plan= boolean($row['use_anual_plan']);

        return null;
    } 
    
    public function add() {   
        $descripcion= setNULL_str($this->descripcion);
        $use_anual_plan= boolean2pg($this->use_anual_plan);

        $sql= "insert into torganismos (nombre, codigo, use_anual_plan, descripcion, activo, cronos, situs) values ";
        $sql.= "('$this->nombre', '$this->codigo', $use_anual_plan, $descripcion, true, '$this->cronos', ";
        $sql.= "'$this->location')";
        $result = $this->do_sql_show_error('add', $sql, false);
        
        if ($result) {
            $this->id= $this->clink->inserted_id("torganismos");

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('torganismos', 'id', 'id_code');
            $this->id_code = $this->obj_code->get_id_code();
        }
        $this->id_organismo= $this->id;
        $this->id_organismo_code= $this->id_code;        
        
        return $this->error;
    }
    
    public function update() {
        $descripcion= setNULL_str($this->descripcion);
        $use_anual_plan= boolean2pg($this->use_anual_plan);

        $sql= "update torganismos set nombre= '$this->nombre', codigo= $this->codigo, descripcion= $descripcion, ";
        $sql.= "use_anual_plan= $use_anual_plan, cronos='$this->cronos', situs='$this->location' ";
        $sql.= "where id = $this->id_organismo";
        $result = $this->do_sql_show_error('add', $sql);
    }
    
    public function eliminar($id= null) {
        $id = !empty($id) ? $id : $this->id_organismo;
        $sql = "delete from torganismos where id = $id";

        $result = $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
        
        $this->SetActivo(false, $id);
    }
    
    private function SetActivo($activo= false, $id= null) {
        $id = !empty($id) ? $id : $this->id_persona;
        $sql= "update torganismos set activo= ". boolean2pg($activo)." where id = $id";
        $this->do_sql_show_error('SetActivo', $sql);
        return $this->error;        
    }
    
    public function listar($flag= false, $use_anual_plan= null) {
        $flag= !is_null($flag) ? $flag : false;
        
        $sql= "select *, id as _id, id_code as _id_code from torganismos where activo = true ";
        if (!is_null($use_anual_plan)) {
            if ($use_anual_plan)
                $sql.= "and use_anual_plan = true ";
            else
                $sql.= "and (use_anual_plan is null or use_anual_plan = false) ";    
        }
        $sql.= "order by nombre asc";
        $result= $this->do_sql_show_error('listar', $sql);
        if (!$flag) 
            return $result;
        
        while ($row= $this->clink->fetch_array($result)) {
            $array_organismos[]= $row; 
        }
        return $array_organismos;
    }
 
    public function setEvento($action= 'add', $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;

        if ($action == 'add') {
            $sql= "insert into torganismo_eventos (id_organismo, id_organismo_code, id_evento, id_evento_code, ";
            $sql.= "id_usuario, cronos, situs) values ($this->id_organismo, '$this->id_organismo_code', $this->id_evento, ";
            $sql.= "'$this->id_evento_code', {$_SESSION['id_usuario']}, '$this->cronos', '$this->location') ";            
        }
        if ($action == 'delete') {
            $sql= "delete from torganismo_eventos ";
            $sql.= "where id_organismo = $this->id_organismo and id_evento = $this->id_evento ";
        }
        $sql.= "; ";
        
        if (!$multi_query)
            $this->do_sql_show_error('setEvento', $sql);
        
        return $multi_query ? $sql : null;
    }

    public function listar_organismos_by_evento($id_evento= null) {
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;
        if (empty($id_evento))
            return null;

        $sql= "select * from torganismo_eventos where 1 ";
        if (!empty($id_evento))
            $sql.= "and id_evento = $id_evento ";
        if (!empty($this->id_organismo))
            $sql.= "and id_organismo = $this->id_organismo ";
         
        $result= $this->do_sql_show_error('listar_organismos_by_evento', $sql);
        $i= 0;
        $array_organismos= array();
        while($row= $this->clink->fetch_array($result)) {
            ++$i;
            $array= array('id'=>$row['id_organismo'], 'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'], 
                    'id_organismo'=>$row['id_organismo'], 'id_organismo_code'=>$row['id_organismo_code']);
            $array_organismos[$row['id_organismo']]= $array;
        }

        $this->_cant= $i;
        return $array_organismos;
    }
} 
