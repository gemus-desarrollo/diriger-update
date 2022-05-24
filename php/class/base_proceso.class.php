<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2018
 */

 if (!class_exists('Tregister_planning_base'))
    include_once "register_planning_base.class.php";

class Tbase_proceso extends Tregister_planning_base {
    protected $entrada,
            $salida,
            $recursos;
    protected $conectado,
            $puerto,
            $codigo,
            $protocolo,
            $url;
    public $array_codigos;
    public $array_cascade_down,
            $array_cascade_up;

    protected $id_proceso_sup,
            $id_proceso_sup_code;
    protected $local_archive,
            $codigo_archive;
    public $array_codigo_archives;

    public $array_usuarios_entity;

    protected $acc;
    protected $if_entity;
    private $cronos_syn;

    public $error_contrains;

    public function __construct($clink= null) {
        $this->tipo= null;
        $this->lugar= null;
        $this->nombre= null;
        $this->conectado= null;

        $this->clink= $clink;
        Tregister_planning_base::__construct($clink);
        if (empty($this->cronos))
            $this->cronos= date('Y-m-d H:i:s');
        $this->className= "Tproceso";

        $this->get_if_copy_tprocesos_exist();
    }

    public function SetIdProceso_sup($id) {
        $this->id_proceso_sup = $id;
    }
    public function GetIdProceso_sup() {
        return $this->id_proceso_sup;
    }
    public function set_id_proceso_sup_code($id) {
        $this->id_proceso_sup_code = $id;
    }
    public function get_id_proceso_sup_code() {
        return $this->id_proceso_sup_code;
    }
    public function GetEntrada() {
        return $this->entrada;
    }
    public function SetEntrada($id) {
        $this->entrada = $id;
    }
    public function GetSalida() {
        return $this->salida;
    }
    public function SetSalida($id) {
        $this->salida = $id;
    }
    public function GetRecursos() {
        return $this->recursos;
    }
    public function SetRecursos($id) {
        $this->recursos = $id;
    }
    public function GetProtocolo() {
        return $this->protocolo;
    }
    public function SetProtocolo($id) {
        $this->protocolo = $id;
    }
    public function GetPuerto() {
        return $this->puerto;
    }
    public function SetPuerto($id) {
        $this->puerto = $id;
    }
    public function GetConectado() {
        return $this->conectado;
    }
    public function SetConectado($id) {
        $this->conectado = $id;
    }
    public function GetCodigo() {
        return $this->codigo;
    }
    public function SetCodigo($id) {
        $this->codigo = $id;
    }
    public function GetURL() {
        return $this->url;
    }
    public function SetURL($id) {
        $this->url = $id;
    }
    public function SetLocalArchive($id= true) {
        $this->local_archive= $id;
    }
    public function GetLocalArchive() {
        return $this->local_archive;
    }
    public function SetCodigoArchive($id= true) {
        $this->codigo_archive= $id;
    }
    public function GetCodigoArchive() {
        return $this->codigo_archive;
    }
    public function set_acc($id) {
        $this->acc= $id;
    }
    public function get_acc() {
        return $this->acc;
    }
    public function GetIfEntity() {
        return $this->if_entity;
    }
    public function SetIfEntity($id= true) {
        $this->if_entity= $id;
    }
    public function SetNull_cronos_syn() {
        $this->cronos_syn= true;
    }

    public function Set($id= null, $id_code= null) {
        $error= null;

        $id= empty($id) ? $this->id_proceso : $id;
        if (!empty($id)) {
            $this->id_proceso= $id;
            $this->id= $id;
        }

        if (empty($id) && empty($id_code))
            return "No hay valor de ID para ejecutar consulta";

        $tprocesos= $this->use_copy_tprocesos ? "_ctprocesos" : "tprocesos";
        $sql= "select * from $tprocesos where 1 ";
        if (!empty($id) && empty($id_code))
            $sql.= "and id = $id ";
        if (empty($id) && !empty($id_code))
            $sql= "and id_code = '$id_code' ";
        $result= $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->id= $row['id'];
            $this->id_code= $row['id_code'];
            $this->id_proceso= $this->id;
            $this->id_proceso_code= $this->id_code;

            $this->id_proceso_sup= $row['id_proceso'];
            $this->id_proceso_sup_code= $row['id_proceso_code'];

            $this->nombre= stripslashes($row['nombre']);
            $this->lugar= stripslashes($row['lugar']);
            $this->entrada= stripslashes($row['entrada']);
            $this->salida= stripslashes($row['salida']);
            $this->recursos= stripslashes($row['recursos']);
            $this->tipo= $row['tipo'];

            $this->if_entity= boolean($row['if_entity']);
            $this->id_entity= $row['id_entity'];
            $this->id_entity_code= $row['id_entity_code'];

            $this->descripcion= stripslashes($row['descripcion']);
            $this->id_responsable= $row['id_responsable'];
            $this->email= $row['email'];
            $this->conectado= $row['conectado'];
            $this->codigo= $row['codigo'];
            $this->protocolo= $row['protocolo'];
            $this->url= $row['url'];
            $this->puerto= $row['puerto'];

            $this->local_archive= boolean($row['local_archive']);
            $this->codigo_archive= $row['codigo_archive'];

            $this->inicio= $row['inicio'];
            $this->fin= $row['fin'];
        }

        return $error;
    }

    public function add() {
        $lugar= setNULL_str($this->lugar);
        $url= setNULL_str($this->url);
        $conectado= setNULL($this->conectado);
        $email= setNULL_str($this->email);
        $port= setNULL($this->port);
        $protocolo= setNULL_str($this->protocolo);
        $codigo= setNULL_str($this->codigo);

        $recursos= setNULL_str($this->recursos);
        $entrada= setNULL_str($this->entrada);
        $salida= setNULL_str($this->salida);

        $id_proceso= setNULL_empty($this->id_proceso_sup);
        $id_proceso_code= setNULL_str($this->id_proceso_sup_code);

        $location= empty($this->codigo) ? $_SESSION['location'] : $this->codigo;
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);

        $codigo_archive= setNULL_str($this->codigo_archive);
        $if_entity= boolean2pg($this->if_entity);
        $id_entity= setNULL($this->id_entity);
        $id_entity_code= setNULL_str($this->id_entity_code);

        $sql= "insert into tprocesos (nombre, tipo, if_entity, id_entity, id_entity_code, lugar, entrada, salida ,recursos, ";
        $sql.= "descripcion, id_responsable, conectado, email, protocolo, puerto, codigo, url, id_proceso, id_proceso_code, ";
        $sql.= "local_archive, codigo_archive, inicio, fin, cronos, situs) values ($nombre, $this->tipo, $if_entity, $id_entity, ";
        $sql.= "$id_entity_code, $lugar, $entrada, $salida, $recursos, $descripcion, $this->id_responsable, $conectado, $email, ";
        $sql.= "$protocolo, $port, $codigo, $url, $id_proceso, $id_proceso_code, ".boolean2pg($this->local_archive).", ";
        $sql.= "$codigo_archive, $this->inicio, $this->fin, '$this->cronos', '$location') ";

        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_proceso= $this->clink->inserted_id("tprocesos");
            $this->id= $this->id_proceso;
        }
        return $this->error;
    }

    public function update() {
        $lugar= setNULL_str($this->lugar);
        $url= setNULL_str($this->url);
        $email= setNULL_str($this->email);
        $puerto= setNULL($this->puerto);
        $protocolo= setNULL_str($this->protocolo);
        $conectado= setNULL($this->conectado);
        $codigo= setNULL_str($this->codigo);

        $recursos= setNULL_str($this->recursos);
        $entrada= setNULL_str($this->entrada);
        $salida= setNULL_str($this->salida);

        $id_proceso= setNULL_empty($this->id_proceso_sup);
        $id_proceso_code= setNULL_str($this->id_proceso_sup_code);

        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);

        $codigo_archive= setNULL_str($this->codigo_archive);
        $if_entity= boolean2pg($this->if_entity);
        $id_entity= setNULL($this->id_entity);
        $id_entity_code= setNULL_str($this->id_entity_code);

        $sql= "update tprocesos set tipo= $this->tipo, lugar= $lugar, entrada= $entrada, salida= $salida, ";
        $sql.= "recursos= $recursos, descripcion= $descripcion, id_responsable= $this->id_responsable, ";
        $sql.= "conectado= $conectado,  local_archive= ". boolean2pg($this->local_archive).", ";
        $sql.= "id_proceso= $id_proceso, id_proceso_code= $id_proceso_code, inicio= $this->inicio, ";
        $sql.= "codigo_archive= $codigo_archive, fin= $this->fin, cronos= '$this->cronos' ";
        if ($_SESSION['nivel'] == _GLOBALUSUARIO)
            $sql.= ", if_entity= $if_entity, id_entity= $id_entity, id_entity_code= $id_entity_code ";
        if ($this->id_proceso != $_SESSION['local_proceso_id']) {
            $sql.= ", url= $url, protocolo= $protocolo, puerto= $puerto, email= $email, ";
            $sql.= "nombre= $nombre, codigo= $codigo ";
            if (!empty($this->codigo))
                $sql.= ", situs= $codigo ";
        }
        if ($this->cronos_syn)
            $sql.= ", cronos_syn= null ";
        $sql.= "where id = $this->id_proceso ";

        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function eliminar() {
        $sql= "delete from tprocesos where id = $this->id_proceso";
        $this->do_sql_show_error('eliminar', $sql);

        if ($this->error) {
            $this->fix_contrains();
            return $this->eliminar();
        }

        if (is_null($this->error)) {
            $sql= "update tusuarios set id_proceso = {$_SESSION['id_entity']}, id_proceso_code= '{$_SESSION['id_entity_code']}' ";
            $sql.= "where id_proceso_code = '$this->id_proceso_code'";
            $this->do_sql_show_error('eliminar', $sql);
        }
        return $this->error;
    }


    private function fix_contrains() {
        $array_tables= array("tarchivos", "tasistencias", "tnotas", "triesgos", "teventos", "tauditorias", "tdebates", "ttareas", "triesgos", "tdocumentos", "tescenarios", "tgrupos", "tindicadores", "tinductores", "tlistas", "tobjetivos", "tpersonas", "tperspectivas", "tplanes", "tprogramas", "tproyectos", "ttematicas", "tprocesos");

        $sql= "show tables";
        $result= $this->do_sql_show_error('fix_contrains', $sql);     

        while ($row= $this->clink->fetch_array($result)) {
            $fixed_register= array_search($row[0], $array_tables);

            if ($fixed_register) {
                $sql= "update {$row[0]} set id_proceso = {$_SESSION['id_entity']}, id_proceso_code = '{$_SESSION['id_entity_code']}' ";
                $sql.= "where id_proceso = $this->id_proceso ";                
            } else {
                $sql= "delete from {$row[0]} where id_proceso = $this->id_proceso";
            }
            $this->do_sql_show_error('fix_contrains', $sql);
        }
    } 
    
    public function get_codigo_array() {
        $sql= "select codigo from tprocesos ";
        $result= $this->do_sql_show_error('get_codigo_array', $sql);

        $i= 0;
        if (isset($this->array_codigos)) unset($this->array_codigos);
        $this->array_codigos[$i]= null;

        while ($row= $this->clink->fetch_array($result))
            $this->array_codigos[$i++]= $row['codigo'];
    }

    public function get_codigo_archive_array($id_proceso_exept= null) {
        $sql= "select id, codigo_archive from tprocesos where local_archive = true";
        $result= $this->do_sql_show_error('get_codigo_array', $sql);

        $i= 0;
        if (isset($this->array_codigo_archives)) unset($this->array_codigo_archives);
        $this->array_codigo_archives= null;

        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            if (!empty($id_proceso_exept) && $row['id'] == $id_proceso_exept)
                continue;
            $this->array_codigo_archives[$row['id']]= $row['codigo_archive'];
        }
        return $i;
    }

    /*
     * Configurar el proceso cuando no se ha craedo aun al Jefe del proceso
     */
    public function init_proceso_install() {
        $sql= "select * from tprocesos, tusuarios where tprocesos.id_responsable = tusuarios.id ";
        $sql.= "and tprocesos.id = {$_SESSION['local_proceso_id']} ";
        $result= $this->do_sql_show_error('init_proceso', $sql);
        if ($this->cant)
            return null;

        $sql= "update tprocesos set id_responsable = "._USER_SYSTEM." where id = {$_SESSION['local_proceso_id']} ";
        $this->do_sql_show_error('init_proceso', $sql);
    }

    public function get_proceso_from_code ($code) {
        $sql= "select * from tprocesos where codigo = '$code' ";
        $result= $this->do_sql_show_error('get_proceso_from_code', $sql);
        $row= $this->clink->fetch_array($result);

        $this->id= $row['id'];
        $this->id_code= $row['id_code'];
        $this->id_proceso= $this->id;
        $this->id_proceso_code= $this->id_code;

        $this->id_proceso_sup= $row['id_proceso'];
        $this->id_proceso_sup_code= $row['id_proceso_code'];

        $this->nombre= stripslashes($row['nombre']);
        $this->lugar= stripslashes($row['lugar']);
        $this->entrada= stripslashes($row['entrada']);
        $this->salida= stripslashes($row['salida']);
        $this->recursos= stripslashes($row['recursos']);
        $this->tipo= $row['tipo'];
        $this->if_entity= $row['if_entity'];
        $this->descripcion= stripslashes($row['descripcion']);
        $this->id_responsable= $row['id_responsable'];

        $this->email= $row['email'];
        $this->conectado= $row['conectado'];

        $this->codigo= $row['codigo'];
        $this->protocolo= $row['protocolo'];
        $this->url= $row['url'];
        $this->puerto= $row['puerto'];

        $this->local_archive= boolean($row['local_archive']);
        $this->codigo_archive= $row['codigo_archive'];

        $this->inicio= $row['inicio'];
        $this->fin= $row['fin'];
    }

    public function listar_procesos_entity() {
        $sql= "select * from tprocesos where (id = {$_SESSION['id_entity']} or id_entity = {$_SESSION['id_entity']}) ";
        $sql.= "and (inicio <= $this->year and fin >= $this->year) ";
        $result = $this->do_sql_show_error('listar_procesos_entity', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'],
                            'lugar'=>$row['lugar'], 'descripcion'=>$row['descripcion'], 'id_responsable'=>$row['id_responsable'],
                            'conectado'=>$row['conectado'], 'codigo'=>$row['codigo'], 'id_proceso'=>$row['id_proceso'],
                            'if_entity'=> boolean($row['if_entity']), 'id_entity'=>$row['id_entity'],
                            'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
            $this->array_procesos_entity[$row['id']]= $array;
        }
    }

    public function test_if_proceso_in_entity($id_proceso) {
        $found= false;
        foreach ($this->array_procesos_entity as $id => $array) {
            if ($id_proceso == $id && (($id == $_SESSION['id_entity'] || $array['id_entity'] == $_SESSION['id_entity'])
                                    || $id_proceso == $array['id_proceso'])) {
                $found= true;
                break;
        }   }

        reset($this->array_procesos_entity);
        return $found;
    }

    protected function get_array_usuarios_entity() {
        $this->array_usuarios_entity= array();

        $sql= "select tusuarios.id as id, tprocesos.id as id_proceso, tprocesos.id_code as id_proceso_code, ";
        $sql.= "tprocesos.id_entity as id_entity, tprocesos.id_entity_code as id_entity_code ";
        $sql.= "from tusuarios, tprocesos where tusuarios.id_proceso = tprocesos.id ";
        $result= $this->do_sql_show_error('get_array_usuarios_entity', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            $id_entity= !empty($row['id_entity']) ? $row['id_entity'] : $row['id_proceso'];
            $id_entity_code= !empty($row['id_entity']) ? $row['id_entity_code'] : $row['id_proceso_code'];
            $this->array_usuarios_entity[$row['id']]= array($id_entity, $id_entity_code);
        }

        return $this->array_usuarios_entity;
    }

    private function set_entity_table($id_entity, $table, $result) {
        $i= 0;
        $j= 0;
        $k= 0;
        $sql= null;
        while ($row= $this->clink->fetch_array($result)) {
            ++$i;
            if (empty($row['id_proceso']))
                continue;
            $id= $this->array_usuarios_entity[$row['id_usuario']][0];
            $id_code= $this->array_usuarios_entity[$row['id_usuario']][1];
            if ($id != $id_entity)
                continue;

            if ($row['id_proceso'] != $id_entity) {
                ++$k;
                ++$j;
                $sql.= "update $table set id_proceso = $id, id_proceso_code= '$id_code' where id = {$row['id']}; ";
            }

            if ($j >= 500) {
                $this->do_multi_sql_show_error('set_entity_table', $sql);
                $sql= null;
                $j= 0;
        }   }

        if ($sql)
            $this->do_multi_sql_show_error('set_entity_table', $sql);
    }

    public function set_entity($id_old_entity, $id_new_entity) {
        $this->get_array_usuarios_entity();

        $sql= "select * from teventos where id_proceso = $id_old_entity";
        $result= $this->do_sql_show_error('set_entity', $sql);
        $this->set_entity_table($id_new_entity, "teventos", $result);

        $sql= "select * from tauditorias where id_proceso = $id_old_entity";
        $result= $this->do_sql_show_error('set_entity', $sql);
        $this->set_entity_table($id_new_entity,"tauditorias", $result);

        $sql= "select * from ttareas where id_proceso = $id_old_entity";
        $result= $this->do_sql_show_error('set_entity', $sql);
        $this->set_entity_table($id_new_entity, "ttareas", $result);
    }

    public function listar_entity($skip_synchronize= false) {
        $sql= "select * from tprocesos where if_entity = true ";
        if ($skip_synchronize)
            $sql.= "and cronos_syn is null ";
        $sql.= "order by tipo asc";
        $result= $this->do_sql_show_error('listar_entity', $sql);
        return $result;
    }

    public function listar_usuarios($use_id_user= true) {
        $sql= "select distinct tusuarios.*, tusuarios.id as _id, usuario, nombre, email, cargo, ";
        $sql.= "tusuario_procesos.cronos as _cronos from tusuarios, tusuario_procesos ";
        $sql.= "where tusuarios.id = tusuario_procesos.id_usuario and tusuario_procesos.id_proceso = $this->id_proceso ";
        return $this->_list_user($sql, $use_id_user);
    }

    public function setUsuario($action= 'add') {
        $error= NULL;

        if ($action == 'add') {
            $sql= "insert into tusuario_procesos (id_proceso, id_proceso_code, id_usuario, cronos, situs) ";
            $sql.= "values ($this->id_proceso, '$this->id_code', $this->id_usuario, '$this->cronos', '$this->location') ";
        } else
            $sql= "delete from tusuario_procesos where id_proceso = $this->id_proceso and id_usuario = $this->id_usuario ";
        $error= $this->_set_user($sql);
        return $error;
    }

    public function listar_grupos() {
        $sql= "select id_grupo as _id, nombre from tgrupos, tusuario_procesos ";
        $sql.= "where tgrupos.id = tusuario_procesos.id_grupo and id_proceso = $this->id_proceso ";
        return $this->_list_group($sql);
    }

    public function setGrupo($action= 'add') {
        $error= null;
        if ($action == 'add') {
            $sql= "insert into tusuario_procesos (id_proceso, id_proceso_code, id_grupo, cronos) ";
            $sql.= "values ($this->id_proceso, '$this->id_code', $this->id_grupo, '$this->cronos') ";
        } else
            $sql= "delete from tusuario_procesos where id_proceso = $this->id_proceso and id_grupo = $this->id_grupo ";
        $error= $this->_set_group($sql);
        return $error;
    }

    public function cleanObjeto() {
        $sql= "delete from tusuario_procesos where id_proceso = $this->id_proceso ";
        return $this->_clean_object($sql);
    }

    public function cleanObjetoByUser() {
        $sql= "delete from tusuario_procesos where id_usuario = $this->id_usuario ";
        return $this->_clean_object($sql);
    }

    /**
     * cargo de los usuarios y nombres de los grupos de usuarios participantes del proceso
     * @param null $id_proceso
     * @return string
     */
    public function get_participantes($id_proceso= null) {
        $array= array();
        if (empty($id_proceso))
            $id_proceso= $this->id_proceso;

        $sql= "select distinct nombre from tgrupos, tusuario_procesos where tusuario_procesos.id_grupo = tgrupos.id ";
        $sql.= "and tusuario_procesos.id_proceso = $id_proceso ";
        $result= $this->do_sql_show_error('get_participantes', $sql);

        while ($row= $this->clink->fetch_array($result))
            $array[]= $row['nombre'];

        $sql= "select distinct cargo from tusuarios, tusuario_procesos where tusuarios.id = tusuario_procesos.id_usuario ";
        $sql.= "and tusuario_procesos.id_proceso = $id_proceso ";
        $result= $this->do_sql_show_error('get_participantes', $sql);

        while ($row= $this->clink->fetch_array($result))
            $array[]= $row['cargo'];

        return implode(", ", $array);
    }

    /**
     * lista de todos los usuarios particpantes del proceso
     * @user_show_reject true: muestra los usuarios que han sido eliminados del sistema
     */
    public function listar_usuarios_proceso($id_proceso= null, $init_array= true, $flag= true, $user_show_reject= 0, $fix_user_process= false) {
        $this->use_copy_tusuarios= !is_null($this->use_copy_tusuarios) ? $this->use_copy_tusuarios : false;
        $tusuarios= ($this->if_copy_tusuarios && $this->use_copy_tusuarios) ? '_ctusuarios' : 'tusuarios';
        if ($this->use_copy_tusuarios)
            $flag= true;

        $init_array= !is_null($init_array) ? $init_array : true;
        if (!empty($id_proceso))
            $this->id_proceso= $id_proceso;
        $user_show_reject= !is_null($user_show_reject) ? $user_show_reject : 0;
        $flag= !is_null($flag) ? $flag : true;

        if ($init_array)
            if (isset($this->array_usuarios)) unset($this->array_usuarios);

        if ($tusuarios == '_ctusuarios') {
            $this->_listar_for_copy('id_usuario', 'id', $user_show_reject);
            $this->_listar_for_copy('id_grupo', 'id', $user_show_reject);
            return $this->array_usuarios;
        }

        $sql= null;

        if ($tusuarios == 'tusuarios' && !empty($this->id_proceso)) {
            if (!$fix_user_process) {
                $sql= "select distinct tusuarios.* , tusuarios.id as _id ";
                $sql.= "from tusuarios, tusuario_procesos where (tusuario_procesos.id_grupo is null ";
                $sql.= "and ((tusuario_procesos.id_proceso = $this->id_proceso and tusuario_procesos.id_usuario = tusuarios.id)) ";
                $sql.= "or tusuarios.id_proceso = $this->id_proceso) ";
                if (!empty($this->user_date_ref))
                    $sql.= "and (tusuarios.eliminado is null or date(tusuarios.eliminado) > date('$this->user_date_ref')) ";
                $sql.= "union ";
                $sql.= "select distinct tusuarios.*, tusuarios.id as _id from tusuarios, tusuario_procesos, tusuario_grupos ";
                $sql.= "where tusuario_procesos.id_usuario is null and (tusuario_procesos.id_proceso = $this->id_proceso ";
                $sql.= "and tusuario_procesos.id_grupo = tusuario_grupos.id_grupo and tusuario_grupos.id_usuario = tusuarios.id) ";
                if (!empty($this->user_date_ref) && !$user_show_reject)
                    $sql.= "and (tusuarios.eliminado is null or date(tusuarios.eliminado) > date('$this->user_date_ref')) ";
            }
            if ($fix_user_process) {
                $sql= "select distinct tusuarios.* , tusuarios.id as _id from tusuarios ";
                $sql.= "where tusuarios.id_proceso = $this->id_proceso and tusuarios.id != "._USER_SYSTEM." ";
                if (!empty($this->user_date_ref) && !$user_show_reject)
                    $sql.= "and (tusuarios.eliminado is null or tusuarios.eliminado > '$this->user_date_ref') ";
            }
            $sql.= "order by nombre asc ";
        }

        $result= $this->do_sql_show_error('listar_usuarios_proceso', $sql);
        if (!$flag)
            return $result;

        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'nombre'=>stripslashes($row['nombre']), 'email'=>$row['email'],'cargo'=>stripslashes($row['cargo']),
                'usuario'=>$row['usuario'], 'nivel'=>$row['nivel'], 'indirect'=>null, 'flag'=>0);
            $this->array_usuarios[$row['id']]= $array;
        }

        if ($this->cant > 0)
            $this->order_array();

        return $this->cant;
    }

    public function getJefe($id_proceso) {
        $sql= "select id_responsable, tusuarios.nombre as responsable, usuario, tprocesos.email as email, nivel, cargo ";
        $sql.= "from tprocesos, tusuarios where tprocesos.id_responsable = tusuarios.id and tprocesos.id = $id_proceso ";

        $result= $this->do_sql_show_error('Tprocesos::getJefe', $sql);
        $row= $this->clink->fetch_array($result);

        $array= array('id'=>$row['id_responsable'], 'nombre'=>stripslashes($row['responsable']), 'email'=>$row['email'],
                'cargo'=>stripslashes($row['cargo']), 'usuario'=>$row['usuario'], 'nivel'=>$row['nivel'], 
                'funcionario'=>$row['funcionario'], 'indirect'=>NULL, 'flag'=>0);

        return $array;
    }

    /**
     * lista los procesos de los cuales es responsable el usuario;
     * id_proceso: proceso de mayoe jerarquia a partir del cual comienza la busqueda
     */
    public function getProceso_if_jefe($id_usuario= null, $id_proceso= null, $corte= null, $exclude_prs_type= null) {
        $this->use_copy_tprocesos= !is_null($this->use_copy_tprocesos) ? $this->use_copy_tprocesos : false;
        $tprocesos= ($this->if_copy_tprocesos && $this->use_copy_tprocesos) ? '_ctprocesos' : 'tprocesos';
        $id_usuario= !empty($id_usuario) ? $id_usuario : $this->id_usuario;
        $row= null;

        if (isset($this->array_cascade_up)) unset($this->array_cascade_up);
        $this->array_cascade_up= null;

        $sql= "select distinct t2.* from tprocesos as t1, $tprocesos as t2 ";
        $sql.= "where (t2.id_responsable = $id_usuario OR (t1.id_responsable = $id_usuario and t2.id_proceso = t1.id)) ";
        if (!empty($this->id_entity))
            $sql.= "and (t1.id_entity = $this->id_entity or t1.id_entity is null) ";
        if (!empty($corte))
            $sql.= "and t2.tipo <= $corte ";
        if (!empty($id_proceso))
            $sql.= "and (t2.id = $id_proceso or t2.id_proceso = $id_proceso) ";
        if (!empty($this->year))
            $sql.= "and (t2.inicio <= $this->year and t2.fin >= $this->year) ";
        $sql.= "order by tipo asc ";
        $result= $this->do_sql_show_error('Tprocesos::getProceso_if_jefe', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($exclude_prs_type) && (int)$row['tipo'] == (int)$exclude_prs_type)
                continue;
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'],
                         'id_responsable'=>$row['id_responsable'], 'conectado'=>$row['conectado'], 'id_proceso'=>$row['id_proceso'],
                         'inicio'=>$row['inicio'], 'fin'=>$row['fin']);
            $this->array_cascade_up[$row['id']]= $array;
            ++$i;
        }
        return $i > 0 ? $this->array_cascade_up : null;
    }

    private function insert_in_array(&$array, $cell, $p) {
        $size= count($array);
        
        if ($p == ($size-1)) {
            $array[$size]= $cell;
            return $array;
        }
            
        for ($i= $size; $i >= ($p+2); $i--)
            $array[$i]= $array[$i-1];
        $array[$p+1]= $cell;
        return $array;
    }

    private function _sort_array_procesos_1(&$array_procesos, $prs) {
        $i= 0;
        $size= count($array_procesos);
        if ($size == 0) {
            $array_procesos[0]= $prs;
            return $array_procesos;
        }

        for ($i= 0; $i < $size; $i++) {
            if ($prs['tipo'] >= $array_procesos[$i]['tipo'] 
                && ($i == ($size-1) || ($i < ($size-1) && $prs['tipo'] <= $array_procesos[$i+1]['tipo']))) {
                $this->insert_in_array($array_procesos, $prs, $i);
                return $array_procesos;
        }   }
        return $array_procesos;
    }

    private function _move_in_array(&$array_procesos, $p, $prs) {
        $size= count($array_procesos);
        for ($i= $size; $i > $p+1; $i--) {
            $array_procesos[$i]= $array_procesos[$i-1]; 
        }
        $array_procesos[$p+1]= $prs;

        $index= null;
        for ($i=0; $i <= $size; $i++) {
            if ($array_procesos[$i]['id'] == $prs['id'])
                $index= $i;
        }

        if ($index != $p+1) {
            for ($i=$index; $i < $size; $i++)
                $array_procesos[$i]= $array_procesos[$i+1];            
        }

        unset($array_procesos[$size]);
    }
    /*
    * Organiza los proceso en order jerarquico segun tipo, pero colocando los porcesos subordinados
    * inmediatamente debajo del proceso superior o responsable jerarquicamente.
    */

    private function _sort_array_procesos_init(&$array_procesos_init, &$array_procesos) {
        $array_procesos= array();
        if (is_null($array_procesos_init)) {
            $i= 0;
            foreach ($this->array_procesos as $prs) {
                $array_procesos[$i++]= $prs;
            }
            $size= $i;            
        } else {
            $i= 0;
            foreach ($array_procesos_init as $prs) {
                $array_procesos[$i++]= $prs;
            }
            $size= $i;  
        }

        $array_procesos2= array();
        for ($i= 0; $i < $size; $i++) {
            $prs= $array_procesos[$i];
            $this->_sort_array_procesos_1($array_procesos2, $prs);
        }

        $array_procesos_init= array();
        for ($i=0; $i < $size; $i++) {
            $array_procesos_init[$i]= $array_procesos2[$i];
            $array_procesos[$i]= $array_procesos2[$i];
        }

        return $size;
    }

    private function _sort_index(&$array_procesos2, $prs, $j, $index, $size) {
        if ($array_procesos2[$j]['id_proceso'] != $prs['id_proceso'] || $array_procesos2[$j]['id'] == $prs['id'])
            return $index;

        $array_ids_k= array();
        $flag_k= false;
        $array_ids_k[$array_procesos2[$j]['id']]= $array_procesos2[$j]['id'];

        for ($k= $j+1; $k < $size; $k++) {
            if (array_search($array_procesos2[$k]['id_proceso'], $array_ids_k)) {
                $index= $k;
                $flag_k= true;
                $array_ids_k[$array_procesos2[$k]['id']]= $array_procesos2[$k]['id'];
            }
        }
        if (!$flag_k)
            $index= $j;
        
        if ($index+1 < $size-1) {
            return $this->_sort_index($array_procesos2, $prs, $index+1, $index, $size);
        } 

        return $index;
    }

    public function sort_array_procesos(&$array_procesos, $flag= false) {
        $flag= !is_null($flag) ? $flag : false;
        $array_procesos2= array();
        $size= $this->_sort_array_procesos_init($array_procesos, $array_procesos2);

        $array_ids_top= array();
        for ($i= 0; $i < $size; $i++) {
          $prs= $array_procesos[$i];
            if(empty($prs['id_proceso'])) {
                $array_ids_top[$prs['id']]= $prs['id'];
                continue;
            }   

            $array_ids= array();
            $index= null;
            for ($j= 0; $j < $size; $j++) {
                if ($array_procesos2[$j]['tipo'] < $prs['tipo'] 
                    && ($array_procesos2[$j]['id'] == $prs['id_proceso'] 
                        || (array_search($prs['id_proceso'], $array_ids_top) && $array_procesos2[$j]['id_proceso'] == $prs['id_proceso']))) {
                    $index= $j;
                    $array_ids[$array_procesos2[$j]['id']]= $array_procesos2[$j]['id'];
                } 
                if (!is_null($index)) {
                    if (empty($array_procesos2[$j]['id_proceso']))
                        continue;
                    if ($array_procesos2[$j]['id'] == $prs['id']) 
                        break; 

                    if ($array_procesos2[$j]['id_proceso'] != $prs['id_proceso'] && $array_procesos2[$j]['tipo'] >= $prs['tipo']) {
                        if (array_search($array_procesos2[$j]['id_proceso'], $array_ids)) {
                            $array_ids[$array_procesos2[$j]['id']]= $array_procesos2[$j]['id'];
                            $index= $i;
                        }    
                    }    
                           
                    if (!array_search($array_procesos2[$j]['id_proceso'], $array_ids_top) 
                        && ($array_procesos2[$j]['id_proceso'] == $prs['id_proceso'] && $array_procesos2[$j]['tipo'] < $prs['tipo'])) { 
                        if ($j+1 >= $size)
                            $index= $j;

                        if ($j < $size-1) {
                            $index= $this->_sort_index($array_procesos2, $prs, $j, $index, $size);
                        }
                        $j= $index+1;
                    }
                }
            }   

            if (!is_null($index)) {
                $this->_move_in_array($array_procesos2, $index, $prs);
            }
        }
        
        if ($flag) {
            unset($this->array_procesos);
            $this->array_procesos= array();
            for ($i= 0; $i < $size; $i++) {
                $prs= $array_procesos2[$i];
                $this->array_procesos[$prs['id']]= $prs;
            }            
        } 
        
        $array_procesos_init= array();
        for ($i= 0; $i < $size; $i++) {
            $prs= $array_procesos2[$i];
            $array_procesos_init[$prs['id']]= $prs;
        }

        return $array_procesos_init;
    }
}


/*
 * Clases adjuntas o necesarias
 */
 include_once "../config.inc.php";

 if (!class_exists('Tbase_usuario'))
    include_once "base_usuario.class.php";
 if (!class_exists('Tgrupo'))
    include_once "grupo.class.php";
if (!class_exists('Ttarea'))
    include_once "tarea.class.php";
