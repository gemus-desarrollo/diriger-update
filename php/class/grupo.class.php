<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

if (!class_exists('Tbase_usuario'))
    include_once "base_usuario.class.php";

class Tgrupo extends Tbase_usuario {
    public function __construct($clink= null) {
        $this->clink= $clink;
        Tbase_usuario::__construct($clink);

        $this->if_copy_tusuarios= false;
        $this->className= "Tgrupo";
    }

    public function SetId($id) {
        $this->id = $id;
        $this->id_grupo = $id;
    }

    public function Set($id= null) {
        if (!empty($id)) $this->id_grupo= $id;

        $sql= "select * from tgrupos where id = $this->id_grupo ";
        $result= $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->nombre= stripslashes($row['nombre']);
            $this->descripcion= stripslashes($row['descripcion']);
            $this->id_entity= $row['id_entity'];
            $this->id_entity_code= $row['id_entity_code'];

            $this->kronos= $row['cronos'];
        }

        return $this->error;
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);

        $sql = "insert into tgrupos (nombre, id_entity, id_entity_code, descripcion, cronos) ";
        $sql.= "values ($nombre, $this->id_entity, '$this->id_entity_code', $descripcion, '$this->cronos')";
        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_grupo = $this->clink->inserted_id("tgrupos");
            $this->id = $this->id_grupo;
        }
        return $this->error;
    }

    public function update() {
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);

        $sql= "update tgrupos set nombre= $nombre, descripcion= $descripcion, cronos= '$this->cronos' ";
        $sql.= "where id = $this->id_grupo ";
        $result= $this->do_sql_show_error('update', $sql);

        return $this->error;
     }

    
     public function listar($flag= true) {
        $sql= "select *, id as _id from tgrupos ";
        if (!empty($this->id_entity))
            $sql.= "where id_entity= $this->id_entity ";
        $sql.= "order by nombre asc ";
        $result= $this->do_sql_show_error('listar', $sql);

        if ($flag)
            return $result;

        $this->_list_group($sql, false);
    }

    public function eliminar() {
        $sql= "delete from tgrupos where id = $this->id_grupo";
        $result= $this->do_sql_show_error('eliminar', $sql);
    }
    
    public function listar_usuarios($use_id_user= true) {
        $sql= "select tusuarios.*, tusuarios.id as _id, tusuario_grupos.cronos as _cronos ";
        $sql.= "from tusuarios, tusuario_grupos where tusuarios.id = tusuario_grupos.id_usuario ";
        $sql.= "and (tusuarios.eliminado is null or tusuarios.eliminado > '$this->user_date_ref') ";
        if (!empty($this->id_grupo))
            $sql.= "and tusuario_grupos.id_grupo = $this->id_grupo ";
        $sql.= "order by nombre asc ";

        return $this->_list_user($sql, $use_id_user);
    }

    public function GetUserGrupos() {
        $sql= "select DISTINCT tgrupos.id, tgrupos.id as _id, nombre, descripcion ";
        $sql.= "from tgrupos, tusuario_grupos where tgrupos.id = tusuario_grupos.id_grupo ";
        $sql.= "and tusuario_grupos.id_usuario = $this->id_usuario order by tgrupos.nombre asc  ";
        $result= $this->do_sql_show_error('GetUserGrupos', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion']);
            $this->array_grupos[$row['_id']]= $array;
            ++$i;
        }
        if ($i > 0)
            $this->clink->data_seek($result);

        return $result;
    }

    public function cleanUsuario() {
        $sql = "delete from tusuario_grupos where id_usuario = $this->id_usuario ";
        $result = $this->do_sql_show_error('cleanUsuario', $sql);
        return $this->error;
    }

    public function cleanObjeto() {
        $sql = "delete from tusuario_grupos where id_grupo= $this->id_grupo ";
        return $this->_clean_object($sql);
    }

    public function setUsuario($value = 'add') {
        if ($value == 'add') {
            $sql = "insert into tusuario_grupos (id_usuario, id_grupo, cronos) ";
            $sql.= "values ($this->id_usuario, $this->id_grupo, '$this->cronos')";
        } else
            $sql = "delete from tusuario_grupos where id_usuario = $this->id_usuario and id_grupo = $this->id_grupo ";

        return $this->_set_user($sql);
    }
}
