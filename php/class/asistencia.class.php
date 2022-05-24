<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 8/6/15
 * Time: 7:53 a.m.
 */

if (!class_exists('Tbase_evento'))
    include_once "base_evento.class.php";

class Tasistencia extends Tbase_evento {
    private $id_asistencia;
    private $id_asistencia_code;

    private $ausente;
    private $invitado;
    protected $cargo;
    private $entidad;
    public $array_asistencias;

    public $array_id_asistencia_usuario;

    public function SetIdAsistencia($id) {
        $this->id_asistencia= $id;
    }
    public function GetIdAsistencia() {
        return $this->id_asistencia;
    }
    public function set_id_asistencia_code($id) {
        $this->id_asistencia_code= $id;
    }
    public function get_id_asistencia_code() {
        return $this->id_asistencia_code;
    }
    public function SetIfInvitado($id) {
        $this->invitado = $id;
    }
    public function GetIfInvitado() {
        return $this->invitado;
    }
    public function SetIfAusente($id) {
        $this->ausente = $id;
    }
    public function GetIfAusente() {
        return $this->ausente;
    }
    public function SetCargo($id) {
        $this->cargo = $id;
    }
    public function GetCargo() {
        return $this->cargo;
    }
    public function SetEntidad($id) {
        $this->entidad = $id;
    }
    public function GetEntidad() {
        return $this->entidad;
    }

    public function __construct($clink= null) {
        Tbase_evento::__construct($clink);
        $this->clink= $clink;

        $this->array_asistencias= array();
    }

    public function Set($id= null) {
        $id= !empty($id) ? $id : $this->id;
        $sql= "select * from tasistencias where id = $id";
        $result= $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->id= $row['id'];
            $this->id_asistencia= $this->id;
            $this->id_code= $row['id_code'];
            $this->id_asistencia= $this->id_code;

            $this->id_evento= $row['id_evento'];
            $this->id_evento_code= $row['id_evento_code'];
            $this->id_usuario= $row['id_usuario'];

            $this->nombre= stripslashes($row['nombre']);
            $this->cargo= stripslashes($row['cargo']);
            $this->entidad= stripslashes($row['entidad']);
            $this->ausente= boolean($row['ausente']);
            $this->invitado= boolean($row['invitado']);

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];
        }
    }

    public function add($multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        
        $id_usuario= setNULL($this->id_usuario);
        $ausente= boolean2pg($this->ausente);
        $invitado= boolean2pg($this->invitado);

        $nombre= setNULL_str($this->nombre);
        $cargo= setNULL_str($this->cargo);
        $entidad= setNULL_str($this->entidad);

        $sql= "insert into tasistencias (id_usuario, id_evento, id_evento_code, nombre, cargo, entidad, ausente, invitado, ";
        $sql.= "id_proceso, id_proceso_code, cronos, situs) values ($id_usuario, $this->id_evento, '$this->id_evento_code',";
        $sql.= " $nombre, $cargo, $entidad, $ausente, $invitado, $this->id_proceso, '$this->id_proceso_code', ";
        $sql.= "'$this->cronos', '$this->location'); ";

        if (!$multi_query)
            $result= $this->do_sql_show_error('add', $sql);

        if (!$multi_query) {
            if ($result) {
                $this->id= $this->clink->inserted_id("tasistencias");
                $this->id_asistencia= $this->id;

                $this->obj_code->SetId($this->id);
                $this->obj_code->set_code('tasistencias','id','id_code');
                $this->id_code= $this->obj_code->get_id_code();
                $this->id_asistencia_code= $this->id_code;
            } else {
                if (!empty($this->error) 
                    && (stripos((string)$this->error,'duplicate') !== false || stripos((string)$this->error,'duplicada') !== false)) {
                    $this->error= null;
                }
            }            
        }
    
        return $multi_query ? $sql : $this->error;
    }

    public function update($id= null) {
        $ausente= boolean2pg($this->ausente);
        $invitado= boolean2pg($this->invitado);

        $nombre= setNULL_str($this->nombre);
        $cargo= setNULL_str($this->cargo);
        $entidad= setNULL_str($this->entidad);

        $sql= "update tasistencias set ausente= $ausente, invitado= $invitado, nombre= $nombre, ";
        $sql.= "cargo= $cargo, entidad= $entidad ";
        if (!empty($id)) {
            $sql.= "where id = $id ";
        } else {
            $sql.= "and id_usuario = $this->id_usuario and id_evento = $this->id_evento ";
            if (!empty($this->id_proceso))
                $sql.= "and id_proceso = $this->id_proceso";
        }
        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function eliminar($id= null, $validate_matter_exist= true, $multi_query= false) {
        $validate_matter_exist= !is_null($validate_matter_exist) ? $validate_matter_exist : true;
        $multi_query= !is_null($multi_query) ? $multi_query : false;

        if ($validate_matter_exist) {
            $if_have_matter= $this->if_have_tematica();
                if (!is_null($if_have_matter) && $if_have_matter)
                    return null;
        }
        
        $sql= "delete from tasistencias where 1 ";
        if (!empty($id))
            $sql.= "and id = $id ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_evento ";
        if (!empty($this->id_usuario))
            $sql.= "and id_usuario = $this->id_usuario ";
        $sql.= "; ";

        if (!$multi_query)    
            $result= $this->do_sql_show_error('eliminar', $sql);

        return $multi_query ? $sql : $this->error;
    }

    private function if_have_tematica() {
        if (empty($this->id_usuario) && empty($this->id_evento))
            return null;

        $sql= "select * from ttematicas where 1 ";
        if (!empty($this->id_usuario))
            $sql.= "and id_responsable = $this->id_usuario ";
        $sql.= "and id_evento = $this->id_evento";
        $result= $this->do_sql_show_error('if_have_tematica', $sql);
        return $this->cant > 0 ? true : false;
    }

    private function set_usuarios() {
        $obj_evento= new Tevento($this->clink);
        $obj_evento->SetIdEvento($this->id_evento);
        $obj_evento->set_id_evento_code($this->id_evento_code);
        $obj_evento->SetYear($this->year);

        $obj_evento->listar_usuarios(false);
        $array_usuarios= $obj_evento->array_usuarios;

        $obj_evento->listar_grupos();
        $array_grupos= $obj_evento->array_grupos;

        $obj_grp= new Tgrupo($this->clink);

        foreach ($array_grupos as $grp) {
            $obj_grp->SetIdGrupo($grp['id']);
            $obj_grp->listar_usuarios(false);
            $_array_usuarios= $obj_grp->array_usuarios;
            if (count($_array_usuarios))
                $array_usuarios= array_merge_overwrite((array)$array_usuarios, (array)$_array_usuarios);
        }

        foreach ($array_usuarios as $user) {
            $this->id_usuario= $user['id'];
            $this->nombre= $user['nombre'];
            $this->cargo= $user['cargo'];
            $this->id_proceso= $user['id_proceso'];
            $this->id_proceso_code= $user['id_proceso_code'];
            $this->ausente= null;
            $this->invitado= null;

            $this->add();
        }
    }

    public function listar($flag= true) {
        $flag= !is_null($flag) ? $flag : true;

        $sql= "select distinct tasistencias.*, tasistencias.id as _id, tasistencias.id_code as _id_code, ";
        $sql.= "tasistencias.id_usuario as _id_usuario, tusuarios.nombre as _nombre, tusuarios.cargo as _cargo "; 
        $sql.= "from tasistencias, tusuarios where tusuarios.id = tasistencias.id_usuario ";
        $sql.= "and tasistencias.id_evento = $this->id_evento ";
        if (!empty($this->id_usuario))
            $sql.= "and tasistencias.id_usuario = $this->id_usuario ";
        if (!empty($this->id_proceso))
            $sql.= "and tasistencias.id_proceso= $this->id_proceso ";
        $sql.= "order by tasistencias.cronos asc";

        $result= $this->do_sql_show_error('listar', $sql);

        if ($result && ($this->cant == 0 && (empty($this->id_usuario) && empty($this->id_proceso)))) {
            $this->set_usuarios();

            $this->id_proceso= null;
            $this->id_usuario= null;
            return $this->listar($flag);
        }

        if ($flag)
            return $result;

        $obj_user= new Tusuario($this->clink);
        $obj_user->set_user_date_ref($this->user_date_ref);

        if (isset($this->array_asistencias)) unset($this->array_asistencias);
        $this->array_asistencias= array();

        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($row['_id_usuario'])) {
                if ($array_ids[$row['_id_usuario']])
                    continue;
                $array_ids[$row['_id_usuario']]= 1;

                $user= array();
                if ($obj_user->if_eliminado($row['_id_usuario'], $user))
                    continue;
                $nombre= stripslashes($user['nombre']);
                $cargo= stripslashes($user['cargo']);

            } else {
                if ($array_ids[$row['id_usuario'].$row['_cargo']])
                    continue;
                $array_ids[$row['id_usuario'].$row['_cargo']]= 1;

                $nombre= stripslashes($row['_nombre']);
                $cargo= stripslashes($row['_cargo']);
            }

            ++$i;
            $array= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 'id_usuario'=>$row['_id_usuario'], 
                    'id_evento'=>$row['id_evento'], 'nombre'=>$nombre, 'cargo'=>$cargo, 'entidad'=>$row['entidad'], 
                    'ausente'=>boolean($row['ausente']), 'invitado'=>boolean($row['invitado']), 
                    'id_proceso'=>$row['id_proceso'], 'origen_data'=>stripslashes($row['origen_data']));

            $this->array_asistencias[(int)$row['_id']]= $array;
        }

        if ($i > 0) {
            $array_names= array();
            foreach ($this->array_asistencias as $key => $user)
                $array_names[]= strtolower($user['nombre']);
            array_multisort ($array_names, SORT_ASC, SORT_STRING, (array)$this->array_asistencias);
        }
        reset($this->array_asistencias);
        return $i;
    }

    public function GetAsistencia($id= null, $id_usuario= null) {
        $found= null;

        if (count($this->array_asistencias) == 0)
            $this->listar(false);

        foreach ($this->array_asistencias as $assist) {
            if ((!empty($id_usuario) && $assist['id_usuario'] == $id_usuario) || (!empty($id) && $assist['id'] == $id)) {
                $found= $assist;
                break;
            }
        }
        reset($this->array_asistencias);
        return $found;
    }

    public function get_asistencias($id_evento= null, $id_usuario= null) {
        if (isset($this->array_asistencias)) 
            unset($this->array_asistencias);
        $this->array_asistencias= array();
        $id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;

        $sql= "select * from tasistencias where id_evento = $id_evento ";
        if (!empty($id_usuario))
            $sql.= "and id_usuario = $id_usuario";
        $result= $this->do_sql_show_error('get_asistencias', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_usuario'=>$row['id_usuario'],
                    'nombre'=>$row['nombre'], 'cargo'=>$row['cargo'], 'entidad'=>boolean($row['entidad']),
                    'invitado'=>boolean($row['invitado']), 'ausente'=> boolean($row['ausente']));
            $this->array_asistencias[$row['id']]= $array;
        }
        return $this->array_asistencias;
    }

    public function this_copy($id_evento, $id_evento_code) {
        $obj= new Tasistencia($this->clink);
        $obj= $this->_this_copy();

        $obj->SetLink($this->clink);
        $obj->obj_code->SetLink($this->clink);
        $obj->SetId(null);
        $obj->set_id_code(null);
        $obj->SetIdEvento($id_evento);
        $obj->set_id_evento_code($id_evento_code);
        $obj->SetIfAusente(null);

        $this->error= $obj->add();
        
        $this->id= $obj->GetId();
        $this->id_code= $obj->get_id_code();
        return $this->error;
    }

    public function move_all_by_evento_to($id_evento) {
        $sql= "select * from tasistencias where id_evento= $id_evento";
        $result_list= $this->do_sql_show_error('move_all_by_evento_to', $sql);
        if (empty($this->cant))
            return null;

        $array_asistencias= array();
        $sql= null;
        $i= 0;
        while ($row= $this->clink->fetch_array($result_list)) {
            ++$i;
            $sql= "update tasistencias set id_evento= $this->id_evento, id_evento_code= '$this->id_evento_code', ";
            $sql.= "cronos = '$this->cronos', situs= '$this->location' where id_evento = $id_evento and id = {$row['id']}";
            $result= $this->do_sql_show_error('move_all_by_evento_to', $sql);

            if ($result)
                $array_asistencias[$row['id']]= array('id'=>$row['id'], 'id_code'=>$row['id_code']);
            else
                return false;
        }

        return $array_asistencias;
    }

    public function GetEmail($id_asistencia_resp= null) {
        $obj_user= new Tusuario($this->clink);
        $id_asistencia_resp= !empty($id_asistencia_resp) ? $id_asistencia_resp : $this->id_asistencia;

        $sql= "select * from tasistencias where id = $id_asistencia_resp";
        $result= $this->do_sql_show_error('GetEmail', $sql);
        $row= $this->clink->fetch_array($result);

        if ($row['id_usuario']) {
            $obj_user->SetIdUsuario($row['id_usuario']);
            $obj_user->Set();
            $nombre= $obj_user->GetNombre();
            $cargo= $obj_user->GetCargo();
            $id_proceso= $obj_user->GetIdProceso();
        } else {
            $nombre= $row['nombre'];
            $cargo= $row['cargo'];
            $id_proceso= $row['id_proceso'];
        }

        $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_usuario'=>$row['id_usuario'],
                        'nombre'=>$nombre, 'cargo'=>$row['cargo'], 'id_proceso'=>$id_proceso);
        return $array;
    }

    protected function set_array_assisted() {
        $sql= "select * from tasistencia where id_evento = $this->id_evento";
        $result= $this->do_sql_show_error('if_have_assist', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $this->array_attached_results[$this->id_evento]['asistencias'][]= array('id_usuario'=>$row['id_usuario']);
        }    
    }

    public function fill_array_asistencia_usuarios() {
        $sql= "select * from tasistencias where id_usuario is not null";
        $result= $this->do_sql_show_error('build_array_usuario_asistencia', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            $this->array_id_asistencia_usuario[$row['id']]= $row['id_usuario'];
        }
        return $i;
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";

if (!class_exists('Tbase_planning'))
    include_once "base_planning.class.php";
