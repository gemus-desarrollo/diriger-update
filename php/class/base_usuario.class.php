<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 8/02/15
 * Time: 0:59
 */

include_once "base.class.php";

class Tbase_usuario extends Tbase {
    protected $cargo;
    protected $noIdentidad;
    protected $telefono;
    protected $movil;
    protected $organismo;
    protected $provincia;
    protected $municipio;
    protected $direccion;
    protected $lugar;

    public function SetTelefono($id) {
        $this->telefono = $id;
    }
    public function GetTelefono() {
        return $this->telefono;
    }
    public function SetMovil($id) {
        $this->movil = $id;
    }
    public function GetMovil() {
        return $this->movil;
    }
    public function SetProvincia($id) {
        $this->provincia = $id;
    }
    public function GetProvincia() {
        return $this->provincia;
    }
    public function SetMunicipio($id) {
        $this->municipio = $id;
    }
    public function GetMunicipio() {
        return $this->municipio;
    }
    public function SetDireccion($id) {
        $this->direccion = $id;
    }
    public function GetDireccion() {
        return $this->direccion;
    }
    public function SetOrganismo($id) {
        $this->organismo = $id;
    }
    public function GetOrganismo() {
        return $this->organismo;
    }
    public function SetLugar($id) {
        $this->lugar= $id;
    }
    public function GetLugar() {
        return $this->lugar;
    }
    public function GetCargo() {
        return $this->cargo;
    }
    public function SetCargo($id) {
        $this->cargo = $id;
    }
    public function GetNoIdentidad() {
        return $this->noIdentidad;
    }
    public function SetNoIdentidad($id) {
        $this->noIdentidad = $id;
    }

    protected $id_entity,
              $id_entity_code;

    public function GetIdEntity() {
        return $this->id_entity;
    }
    public function SetIdEntity($id) {
        $this->id_entity= $id;
    }
    public function set_id_entity_code($id){
        $this->id_entity_code= $id;
    }
    public function get_id_entity_code(){
        return $this->id_entity_code;
    }

    protected $id_documento;
    protected $id_documento_code;

    protected $id_persona;
    protected $id_persona_code;

    public function GetIdDocumento() {
        return $this->id_documento;
    }
    public function SetIdDocumento($id) {
        $this->id_documento= $id;
    }
    public function SetIdPersona($id) {
        $this->id_persona= $id;
    }
    public function GetIdPersona() {
        return $this->id_persona;
    }
    public function set_id_persona_code($id) {
        $this->id_persona_code= $id;
    }
    public function get_id_persona_code() {
        return $this->id_persona_code;
    }

    public function __construct($clink= null) {
        Tbase::__construct($clink);
        $this->clink= $clink;

        $this->id_entity= $_SESSION['id_entity'];
        $this->id_entity_code= $_SESSION['id_entity_code'];

        $this->obj_code= new Tcode($clink);
    }

    protected function _listar_for_copy($field, $index= 'id', $user_show_reject= 0, $fix_user_process= false, $only_jefe= false) {
        $index= is_null($index) ? 'id' : $index;
        $user_show_reject= !is_null($user_show_reject) ? $user_show_reject : 0;
        $fix_user_process= !is_null($fix_user_process) ? $fix_user_process : false;
        $only_jefe = !is_null($only_jefe) ? (bool)$only_jefe : false;

        $sql= "select distinct _ctusuarios.*, _ctusuarios.id as _id, _ctusuarios.id_proceso as _id_proceso ";
        if ($fix_user_process) {
            $sql.= "from _ctusuarios where_ctusuarios.nombre is not null and id_proceso = $this->id_proceso and _ctusuarios.id != "._USER_SYSTEM." ";
        }
        if ($field == 'id_usuario' && !$fix_user_process) {
            $sql.= "from _ctusuarios, tusuario_procesos ";
            $sql.= "where ((tusuario_procesos.id_grupo is null ";
            $sql.= "and (tusuario_procesos.id_proceso = $this->id_proceso and tusuario_procesos.id_usuario = _ctusuarios.id)) ";
            $sql.= "or _ctusuarios.id_proceso = $this->id_proceso) and _ctusuarios.id != "._USER_SYSTEM." ";
        }
        if ($field == 'id_grupo' && !$fix_user_process) {
            $sql.= "from _ctusuarios, view_usuario_proceso_grupos ";
            $sql.= "where _ctusuarios.id = view_usuario_proceso_grupos.id_usuario and view_usuario_proceso_grupos.id_proceso = $this->id_proceso ";
        }
        if (!$user_show_reject)
            $sql.= "and (_ctusuarios.eliminado is null or tusuarios.eliminado > '$this->user_date_ref') ";
        if ($only_jefe)
            $sql.= "and _ctusuarios.id_proceso_jefe = $this->id_proceso ";
        $sql.= "and _ctusuarios.id != 1 order by nombre asc ";

        $result= $this->do_sql_show_error('_listar_for_copy', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                'eliminado'=>$row['eliminado'], 'usuario'=>$row['usuario'], 'id_proceso'=>$row['_id_proceso'], 
                '_id'=>$row['_id'], 'nivel'=>$row['nivel']);

            if ($index == 'id')
                $this->array_usuarios[$row['id']]= $array;
            else
                $this->array_usuarios[$row['usuario']]= $array;
        }
    }

    /**
     * **********************************************************************************************************
     */
    private $nums_user;
    private $_user_array;

    protected function _list_group($sql, $flag= false)   {
        $flag= !is_null($flag) ? $flag : false;
        if (isset($this->array_grupos)) unset($this->array_grupos);

        $result= $this->do_sql_show_error('_list_group', $sql);
        if ($flag)
            return $result;
        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            else
                $array_ids[$row['_id']]= $row['_id'];

            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'id_proceso'=>$row['id_proceso'], 'id_proceso'=>$row['id_proceso_code'],
                'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'], 'id_auditoria'=>$row['id_auditoria'],
                'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'],
                'id_tematica'=>$row['id_tematica'], 'id_tematica_code'=>$row['id_tematica_code'],
                'id_entity'=>$row['id_entity'], 'id_entity_code'=>$row['id_entity_code']);
            $this->array_grupos[$row['_id']]= $array;
            ++$i;
        }

        if ($i > 0) $this->clink->data_seek($result);
        $this->cant= $i;
        return $this->array_grupos;
    }

    protected function _list_user($sql, $use_id_user= true, $flag= false) {
        $use_id_user= !is_null($use_id_user) ? $use_id_user : true;
        $flag= !is_null($flag) ? $flag : false;
        if (isset($this->array_usuarios)) unset($this->array_usuarios);

        $result= $this->do_sql_show_error('_list_user', $sql);
        if ($flag)
            return $result;

        $i= 0;
        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (isset($array_ids[$row['_id']]))
                continue;
            else
                $array_ids[$row['_id']]= $row['_id'];

            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                    'eliminado'=>$row['eliminado'], 'usuario'=>$row['usuario'], 'cronos'=>$row['_cronos'], '_id'=>$row['_id'],
                    'id_proceso'=>$row['id_proceso'], 'id_proceso_code'=>$row['id_proceso_code'], 'nivel'=>$row['nivel'],
                    'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code'], 'id_auditoria'=>$row['id_auditoria'],
                    'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code'],
                    'id_tematica'=>$row['tematica'], 'id_tematica_code'=>$row['id_tematica_code']);

            $index= $use_id_user ? $row['_id'] : $row['usuario'];
            $this->array_usuarios[$index]= $array;
            ++$i;
        }

        if ($i > 0)
            $this->clink->data_seek($result);
        $this->cant= $i;
        return $this->array_usuarios;
    }

    public function cleanListaUser() {
        if (isset($this->_user_array)) unset($this->_user_array);
        $this->nums_user= 0;
    }

    public function push2ListaUser($id_usuario, $use_id_user= false) {
        $obj= new Tusuario($this->clink);
        $obj->Set($id_usuario);

        $id= $obj->GetIdUsuario();
        $nombre= $obj->GetNombre();
        $nivel= $obj->GetRole();
        $email= $obj->GetEmail();
        $cargo= $obj->GetCargo();
        $usuario= $obj->GetUsuario();
        $eliminado= $obj->GetEliminado();
        $id_proceso= $obj->GetIdProceso();
        $id_proceso_code= $obj->get_id_proceso_code();

        $array= array('id'=>$id, 'nombre'=>$nombre, 'email'=>$email,'nivel'=>$nivel, 'cargo'=>$cargo, 'usuario'=>$usuario,
            'eliminado'=> $eliminado, 'id_proceso'=>$id_proceso, 'id_proceso_code'=>$id_proceso_code, 'flag'=>0);

        $index= $use_id_user ? $id : $usuario;
        $this->_user_array[$index]= $array;
        ++$this->nums_user;

        unset($obj);
    }

    public function get_list_user($sorting= true) {
        if (is_null($this->_user_array))
            return NULL;
        if ($sorting)
            sort($this->_user_array);
        return $this->_user_array;
   }

    public function push2ListaUserGroup($id_grupo, $use_id_user= true) {
        $obj= new Tgrupo($this->clink);
        $obj->set_user_date_ref($this->user_date_ref);
        $obj->SetIdGrupo($id_grupo);
        $obj->listar_usuarios($use_id_user);

        if (!is_array($obj->array_usuarios))
            return;

        foreach ($obj->array_usuarios as $array) {
            $id= $array['id'];
            $nombre= $array['nombre'];
            $nivel= $array['nivel'];
            $email= $array['email'];
            $cargo= $array['cargo'];
            $usuario= $array['usuario'];
            $id_proceso= $array['id_proceso'];
            $id_proceso_code= $array['id_proceso_code'];

            $tarray= array('id'=>$id, 'nombre'=>$nombre, 'nivel'=>$nivel, 'email'=>$email,'cargo'=>$cargo, 'usuario'=>$usuario, 'cronos'=>$array['cronos'],
                            'eliminado'=> $array['eliminado'], 'id_proceso'=>$id_proceso, 'id_proceso_code'=>$id_proceso_code, 'flag'=>0);

            $index= $use_id_user ? $id : $usuario;
            $this->_user_array[$index]= $tarray;
            ++$this->nums_user;
        }

        unset($obj);
    }

    protected function _clean_object($sql) {
        $this->do_sql_show_error('_clean_object', $sql);
        return $this->error;
    }

    protected function _set_group($sql) {
        return $this->_set_user($sql);
    }

    protected function order_array($use_id_user= true) {
        $_array= array();
        $_array_usuarios= array();

        foreach ($this->array_usuarios as $node) {
            $_array[$node['id']]= $node['nombre'];
            $_array_usuarios[$node['id']]= $node;
        }

        asort($_array, SORT_STRING);
        unset($this->array_usuarios);

        while (list($key, $value)= each($_array)) {
            $index= $use_id_user ? $key : $_array_usuarios[$key]['usuario'];
            $this->array_usuarios[$index]= $_array_usuarios[$key];
        }
    }

    public function _to_array_user($id_usuario= null) {
        $tusuarios= ($this->use_copy_tusuarios && $this->if_tusuarios) ? "_ctusuarios" : "tusuarios";

        $sql= "select distinct * from $tusuarios ";
        if (!empty($id_usuario))
            $sql.= "where tusuarios.id = $id_usuario ";
        $result= $this->do_sql_show_error('_to_array_user', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                'usuario'=>$row['usuario'], 'nivel'=>$row['nivel'], 'indirect'=>NULL, 'flag'=>0);
            $this->array_usuarios[$row['id']]= $array;
        }
    }

    protected function _to_array_user_from_group($id_grupo) {
        $sql= "select tusuarios.* from tusuarios, tusuario_grupos ";
        $sql.= "where tusuarios.id = tusuario_grupos.id_usuario and tusuario_grupos.id_grupo = $id_grupo ";

        $result= $this->do_sql_show_error('_to_array_user_from_group', $sql);
        if (empty($this->cant))
            return 0;

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                          'nivel'=>$row['nivel'], 'usuario'=>$row['usuario']);
            $this->array_usuarios[$row['id']]= $array;
        }
    }

    protected function _create_copy_table($table) {
        $ctable= "_c".$table;
        $sql= "drop table if exists ".stringSQL($ctable)." ";
        $this->do_sql_show_error('_create_copy_table', $sql);
//
        $sql= "CREATE TEMPORARY TABLE ".stringSQL($ctable)." (";
        $row_table= $this->clink->fields($table);
        $num_fields= count($row_table);
        --$num_fields;

        $i= 0;
        foreach ($row_table as $row) {
            $sql.= showFieldSQL($row);
            $sql.= $i < $num_fields ? ", " : "";
            ++$i;
        }
        $sql.= "); ";
        $result= $this->do_sql_show_error('_create_copy_table', $sql);
        return $result;
    }

    protected function if_in_tmp_tusuarios($id_usuario) {
        if (!$this->if_tusuarios)
            return false;
        $cant= 0;

        $sql= "select * from _tusuarios where id = $id_usuario";
        $result= $this->do_sql_show_error('if_in_tmp_tusuarios', $sql);
        if (empty($this->cant))
            return false;
        $row= $this->clink->fetch_array($result);
        return $row;
   }

   public function create_views() {
        $sql= "DROP VIEW IF EXISTS view_usuario_proceso_grupos; ";
        $result= $this->do_sql_show_error('create_views', $sql);

        $sql= "CREATE VIEW view_usuario_proceso_grupos AS select distinct tusuario_grupos.id_usuario, tusuario_grupos.id_grupo, ";
        $sql.= "tusuario_procesos.id_proceso from tusuario_procesos, tusuario_grupos ";
        $sql.= "where tusuario_procesos.id_grupo is not null and tusuario_grupos.id_grupo = tusuario_procesos.id_grupo; ";
        $result= $this->do_sql_show_error('create_views', $sql);

        $sql= "DROP VIEW IF EXISTS view_usuario_grupos; ";
        $result= $this->do_sql_show_error('create_views', $sql);

        $sql= "CREATE VIEW view_usuario_grupos AS select distinct tusuarios.*, id_grupo ";
        $sql.= "from tusuarios, tusuario_grupos where tusuarios.id = tusuario_grupos.id_usuario; ";
        $result= $this->do_sql_show_error('create_views', $sql);
    }

    public function update_all_id_proceso_jefe() {
        $sql= "select * from tprocesos order by tipo asc";
        $result= $this->do_sql_show_error('create_views', $sql);

        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if (empty($row['id_responsable']))
                continue;
            if ($array_ids[$row['id_responsable']])
                continue;
            else
                $array_ids[$row['id_responsable']]= 1;

            $sql= "update tusuarios set id_proceso_jefe = {$row['id']}, id_proceso_jefe_code= '{$row['id_code']}' ";
            $sql.= "where id = {$row['id_responsable']}";
            $this->do_sql_show_error('create_views', $sql);
        }
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";