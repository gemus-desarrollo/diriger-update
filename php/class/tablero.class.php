<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

include_once "grupo.class.php";

class Ttablero extends Tgrupo {
    protected $id_tablero;
    public $array_tableros;
    public $use_perspectiva;

    public function __construct($clink= null){
        $this->clink= $clink;
        Tgrupo::__construct($clink);

        $this->className= "Ttablero";
    }

    public function SetId($id) {
        $this->id = $id;
        $this->id_tablero = $id;
    }

    public function Set() {
        $sql = "select * from ttableros where id = $this->id_tablero ";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);
            $this->nombre = stripslashes($row['nombre']);
            $this->descripcion = stripslashes($row['descripcion']);
            $this->use_perspectiva = boolean($row['use_perspectiva']);
        }

        return $this->error;
    }

    public function add() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);
        $use_perspectiva = boolean2pg($this->use_perspectiva);

        $sql = "insert into ttableros (nombre, descripcion, use_perspectiva, id_entity, id_entity_code) ";
        $sql.= "values ($nombre, $descripcion, $use_perspectiva, $this->id_entity, '$this->id_entity_code')";
        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_tablero = $this->clink->inserted_id("ttableros");
            $this->id = $this->id_tablero;
        }

        return $this->error;
    }

    public function update() {
        $descripcion = setNULL_str($this->descripcion);
        $nombre = setNULL_str($this->nombre);
        $use_perspectiva = boolean2pg($this->use_perspectiva);

        $sql = "update ttableros set nombre= $nombre, descripcion= $descripcion, use_perspectiva= $use_perspectiva ";
        $sql .= "where id = $this->id_tablero ";
        $this->do_sql_show_error('update', $sql);

        return $this->error;
    }

    public function listar() {
        $sql = "select distinct *, id as _id from ttableros ";
        $sql.= "where id_entity = {$_SESSION['id_entity']}";
        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar() {
        if ($this->id_tablero == 1) {
            $error = "No esta permitido borrar este tablero.";
            return $error;
        }

        $sql = "delete from ttableros where id = $this->id_tablero";
        $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
    }
    
    public function GetIdIntegral() {
        $sql= "select * from ttableros where id_entity = {$_SESSION['id_entity']} limit 1";
        $result= $this->do_sql_show_error('GetIdIntegral', $sql);
        $row= $this->clink->fetch_array($result);
        return $row['id'];
    }

    public function setIndicador($value = 'add', $id_tablero = null) {
        $error = null;
        if (empty($id_tablero))
            $id_tablero = $this->id_tablero;

        if ($value == 'add') {
            $sql = "insert into tindicador_tableros (id_indicador, id_indicador_code, id_tablero, cronos, situs) ";
            $sql .= "values  ($this->id_indicador, '$this->id_indicador_code', $id_tablero, '$this->cronos', '$this->location') ";
        } else {
            $sql = "delete from tindicador_tableros where id_indicador = $this->id_indicador and id_tablero = $id_tablero ";
        }
        $result = $this->do_sql_show_error('setIndicador', $sql);

        if (!$result) {
            if (stristr($this->error, "Duplicate")) {
                $this->error = null;
            }
        }

        return $this->error;
    }

    public function listar_indicadores($flag= true, $use_perspectiva= null) {
        $flag= !is_null($flag) ? $flag : true;

        $sql= "select distinct tindicadores.id as _id_indicador, tindicadores.id as _id, tindicadores.nombre as _nombre, ";
        $sql.= "tindicadores.numero as _numero, id_unidad, id_unidad_code, tindicadores.id_proceso as _id_proceso, ";
        $sql.= "id_perspectiva, id_proyecto from tindicadores, tindicador_criterio ";
        if (!empty($this->id_tablero))
            $sql.= ", tindicador_tableros ";
        if (!empty($this->id_proceso))
            $sql.= ", tproceso_indicadores ";
        $sql.= "where tindicadores.id = tindicador_criterio.id_indicador ";
        if (!empty($this->year))
            $sql.= "and tindicador_criterio.year = $this->year ";

        if (!is_null($use_perspectiva)) {
            if ($use_perspectiva) {
                if (!empty($this->id_perspectiva))
                    $sql.= "and tindicador_criterio.id_perspectiva = $this->id_perspectiva ";
                else
                    $sql.= "and tindicador_criterio.id_perspectiva is not null ";
            } else {
                $sql.= "and tindicador_criterio.id_perspectiva is null ";
            }
        }

        if (!empty($this->id_tablero)) {
            $sql.= "and (tindicador_criterio.id_indicador = tindicador_tableros.id_indicador ";
            $sql.= "and tindicador_tableros.id_tablero = $this->id_tablero) ";
        }
        if (!empty($this->id_proceso)) {
            $sql.= "and (tindicador_criterio.id_proceso = tproceso_indicadores.id_proceso ";
            $sql.= "and tproceso_indicadores.id_proceso = $this->id_proceso) ";
        }
        $sql.= "order by _nombre asc ";

        $result= $this->do_sql_show_error('listar_indicadores', $sql);

        if ($flag)
            return $result;
        if (empty($this->cant))
            return null;

        if (isset($this->array_indicadores))
            unset($this->array_indicadores);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['_id'], 'nombre'=>$row['_nombre'], 'descripcion'=>$row['descripcion'], 'peso'=>1);
            $this->array_indicadores[$row['_id']]= $array;
        }

        return $this->array_indicadores;
    }


    public function GetSelectedByIndicador() {
        if (isset($this->array_tableros))
            unset($this->array_tableros);

        $sql = "select * from tindicador_tableros, ttableros where ttableros.id = tindicador_tableros.id_tablero ";
        $sql.= "and id_indicador = $this->id_indicador ";
        $result = $this->do_sql_show_error('GetSelectedByIndicador', $sql);

        while ($row = $this->clink->fetch_array($result)) {
            $array = array('id' => $row['id_tablero'], 'nombre' => $row['nombre'], 'descripcion' => $row['descripcion']);
            $this->array_tableros[$row['id_tablero']] = $array;
        }

        return $this->array_tableros;
    }

    public function get_procesos_by_user() {
        if (isset($this->array_tableros))
            unset($this->array_tableros);

        $sql= "select ttableros.*, ttableros.id as id_tablero from ttableros, tusuario_tableros ";
        $sql.= "where id_usuario= $this->id_usuario and tusuario_tableros.id_tablero = ttableros.id ";
        if (!empty($this->id_entity))
            $sql.= "and ttableros.id_entity = $this->id_entity ";
        $sql.= " UNION ";
        $sql.= "select ttableros.*, ttableros.id as id_tablero from ttableros, tusuario_tableros, tusuario_grupos ";
        $sql.= "where tusuario_grupos.id_usuario = $this->id_usuario and tusuario_tableros.id_grupo = tusuario_grupos.id_grupo ";
        $sql.= "and ttableros.id = tusuario_tableros.id_tablero ";
        if (!empty($this->id_entity))
            $sql.= "and ttableros.id_entity = $this->id_entity ";
        $sql.= "order by id_tablero asc";
        $result = $this->do_sql_show_error('get_procesos_by_user', $sql);
        
        while ($row = $this->clink->fetch_array($result)) {
            $array = array('id' => $row['id_tablero'], 'nombre' => $row['nombre'], 'descripcion' => $row['descripcion'],
                'use_perspectiva' => boolean($row['use_perspectiva']));
            $this->array_tableros[$row['id_tablero']] = $array;
        }
        return $this->array_tableros;
    }

    public function cleanObjeto() {
        $sql = "delete from tusuario_tableros where id_tablero= $this->id_tablero ";
        return $this->_clean_object($sql);
    }

    public function setUsuario($value = 'add') {
        if ($value == 'add') {
            $sql = "insert into tusuario_tableros (id_usuario,id_tablero, cronos, situs) ";
            $sql .= "values ($this->id_usuario, $this->id_tablero, '$this->cronos', '$this->location') ";
        } else
            $sql = "delete from tusuario_tableros where id_tablero= $this->id_tablero and id_usuario = $this->id_usuario ";

        return $this->_set_user($sql);
    }

    public function listar_usuarios($use_id_user = true) {
        $sql = "select tusuarios.*, tusuarios.id as _id, tusuario_tableros.cronos as _cronos from tusuarios, ";
        $sql.= "tusuario_tableros where tusuarios.id = tusuario_tableros.id_usuario ";
        $sql .= "and tusuario_tableros.id_tablero = $this->id_tablero order by nombre asc ";

        return $this->_list_user($sql, $use_id_user);
    }

    public function listar_grupos() {
        $sql = "select tgrupos.*, tgrupos.id as _id from tgrupos, tusuario_tableros ";
        $sql .= "where tgrupos.id = tusuario_tableros.id_grupo and tusuario_tableros.id_tablero = $this->id_tablero ";
        $sql.= "order by nombre asc ";

        return $this->_list_group($sql);
    }

    public function setGrupo($value = 'add') {
        if ($value == 'add') {
            $sql = "insert into tusuario_tableros (id_grupo,id_tablero, cronos, situs) ";
            $sql .= "values ($this->id_grupo, $this->id_tablero, '$this->cronos', '$this->location' ) ";
        } else
            $sql = "delete from tusuario_tableros where id_tablero= $this->id_tablero and id_grupo = $this->id_grupo ";

        return $this->_set_group($sql);
    }

    public function cleanUsuario() {
        $error = null;

        $sql = "delete from tusuario_tableros where id_usuario = $this->id_usuario ";
        $result = $this->clink->query($sql);

        if (!$result) {
            $error = $this->clink->errno() . ": " . $this->clink->error() . "\n";
        }
        return $error;
    }

    public function getParticipantes() {
        $this->GetSelectedByIndicador();

        $array_grupos= array();
        $array_usuarios= array();
        foreach ($this->array_tableros as $id) {
            $this->listar_grupos();
            foreach ($this->array_grupos as $id => $array)
                $array_grupos[$id]= $array;

            $this->listar_usuarios();
            foreach ($this->array_usuarios as $id => $array)
                $array_usuarios[$id]= $array;
        }

        unset($this->array_grupos);
        foreach ($array_grupos as $id => $array)
            $this->array_grupos[$id]= $array;

        unset($this->array_usuarios);
        foreach ($array_usuarios as $id => $array)
            $this->array_usuarios[$id]= $array;
    }

    public function listar_usuarios_by_indicador() {
        $this->GetSelectedByIndicador();

        $array_usuarios= array();
        foreach ($this->array_tableros as $id_tab => $array_tab) {
            $this->id_tablero= $id_tab;
            $this->listar_usuarios();
            foreach ($this->array_usuarios as $id => $array)
                $array_usuarios[$id]= $array;
        }

        unset($this->array_usuarios);
        $this->array_usuarios= array();
        foreach ($array_usuarios as $id => $array)
            $this->array_usuarios[$id]= $array;
    }

    public function listar_grupos_by_indicador() {
        $this->GetSelectedByIndicador();

            foreach ($this->array_tableros as $id_tab => $array_tab) {
                $this->id_tablero= $id_tab;
                $this->listar_grupos();
                foreach ($this->array_grupos as $id => $array)
                    $array_grupos[$id]= $array;
            }

            unset($this->array_grupos);
            $this->array_grupos= array();
            foreach ($array_grupos as $id => $array)
                $this->array_grupos[$id]= $array;
    }

    public function set_entity($id_origen, $id_target, $id_target_code) {
        $sql= "select * from ttableros where id_entity = $id_origen limit 4";
        $result = $this->do_sql_show_error("set_entity", $sql);

        $this->id_entity= $id_target;
        $this->id_entity_code= $id_target_code;

        while ($row= $this->clink->fetch_array($result)) {
            $this->use_perspectiva= $row['use_perspectiva'];
            $this->nombre= $row['nombre'];
            $this->descripcion= $row['descripcion'];

            $this->add();
        }    
    } 
}
