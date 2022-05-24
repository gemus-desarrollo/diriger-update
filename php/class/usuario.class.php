<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

if (!class_exists('Tbase_usuario'))
    include_once "base_usuario.class.php";

// SI SE CAMBIA EL ARRAY SE MODIFICA LA SIGANACION DE PERMISOS
$nivel= array('INVITADO','MONITOREAR','ACTUALIZAR','PLANIFICAR','ADMINISTRAR', 'SUPERUSUARIO');

class Tusuario extends Tbase_usuario {
    protected $usuario;
    protected $responsable;
    protected $global_user;
    private $clave;
    private $conectado;
    private $firma;
    private $param;
    private $eliminado;
    private $acc_sys;

    private $acc_planwork;
    private $acc_planrisk;
    private $acc_planaudit;
    private $acc_planheal;
    private $acc_planproject;
    private $acc_archive;

    private $nivel_archive2;
    private $nivel_archive3;
    private $nivel_archive4;

    private $freeassign;
    private $user_ldap;

    protected $id_proceso_jefe,
                $id_proceso_jefe_code;

    public function SetId($id) {
        $this->id_usuario = $id;
    }

    public function __construct($clink = null) {
        Tbase_usuario::__construct($clink);
        $this->Init();

        $this->clink = $clink;
        $this->className = "Tusuario";

        $this->acc_sys = 0;
        $this->acc_planaudit = 0;
        $this->acc_planwork = 0;
        $this->acc_planrisk = 0;
        $this->acc_planheal = 0;
        $this->acc_planproject = 0;
        $this->acc_archive = 0;
        $this->freeassign = 0;
        $this->global_user = 0;

        $this->nivel_archive2= null;
        $this->nivel_archive3= null;
        $this->nivel_archive4= null;
    }

    public function GetUsuario() {
        return $this->usuario;
    }
    public function GetClave() {
        return $this->clave;
    }
    public function GetIfGlobalUser() {
        return $this->global_user;
    }
    public function SetIfGlobalUser($id) {
        $this->global_user = $id;
    }
    public function GetConectado() {
        return $this->conectado;
    }
    public function GetImage() {
        return $this->firma;
    }

    public function GetParam() {
        list($name, $type, $size, $dim) = preg_split(':', $this->param);
        $this->param['name'] = $name;
        $this->param['type'] = $type;
        $this->param['size'] = $size;
        $this->param['dim'] = $dim;

        return $this->param;
    }

    public function GetDim($param= null) {
        $param = $param ? $param : $this->param;

        list($name, $type, $size, $dim) = preg_split('/:/', $param);
        list($x1, $w, $x3, $h) = preg_split('/\"/', $dim);
        /*
        $h = (int) substr($h, 0, strlen($h) - 1);
        $w = (int) substr($w, 0, strlen($w) - 1);
        */
        $ratio = (float) $w / $h;
        $wscale = ($w > _MAX_IMG_SING_WIDTH) ? (float) $w / _MAX_IMG_SING_WIDTH : 1;
        $hscale = ($h > _MAX_IMG_SING_HEIGHT) ? (float) $h / _MAX_IMG_SING_HEIGHT : 1;
        $scale = max($wscale, $hscale);

        if ($scale > 1) {
            if ($wscale == $hscale || $wscale > $hscale) {
                $w = _MAX_IMG_SING_WIDTH;
                $h = $w / $ratio;
            } else {
                $h = _MAX_IMG_SING_HEIGHT;
                $w = $h * $ratio;
            }
        }

        $dim = "width=$w  height=$h";
        return $dim;
    }

//	public function GetEmail() {return $this->email;}

    public function SetUsuario($id) {
        $this->usuario = strtolower(trim($id));
    }
    public function SetClave($id) {
        $this->clave = !empty($id) ? trim($id) : null;
    }
    public function get_acc_sys() {
        return $this->acc_sys;
    }
    public function get_acc_planwork() {
        return $this->acc_planwork;
    }
    public function get_acc_planrisk() {
        return $this->acc_planrisk;
    }
    public function get_acc_planaudit() {
        return $this->acc_planaudit;
    }
    public function get_acc_planheal() {
        return $this->acc_planheal;
    }
    public function get_acc_planproject() {
        return $this->acc_planproject;
    }
    public function get_freeassign() {
        return $this->freeassign;
    }
    public function set_acc_sys($id = 0) {
        if (empty($id)) $id = 0;
        $this->acc_sys = $id;
    }
    public function set_acc_planwork($id = 0) {
        if (empty($id)) $id = 0;
        $this->acc_planwork = $id;
    }

    public function set_acc_planrisk($id = 0) {
        if (empty($id)) $id = 0;
        $this->acc_planrisk = $id;
    }
    public function set_acc_planaudit($id = 0) {
        if (empty($id)) $id = 0;
        $this->acc_planaudit = $id;
    }
    public function set_acc_planheal($id = 0) {
        if (empty($id)) $id = 0;
        $this->acc_planheal = $id;
    }
    public function set_acc_planproject($id = 0) {
        if (empty($id)) $id = 0;
        $this->acc_planproject = $id;
    }
    public function set_freeassign($id = 0) {
        if (empty($id)) $id = 0;
        $this->freeassign = $id;
    }

    public function set_acc_archive($id= 0) {
        if (empty($id))
            $id = 0;
        $this->acc_archive = $id;
    }
    public function get_acc_archive() {
        return $this->acc_archive;
    }
    public function set_nivel_archive2($id= true) {
        $this->nivel_archive2= $id;
    }
    public function get_nivel_archive2() {
        return $this->nivel_archive2;
    }
    public function set_nivel_archive3($id= true) {
        $this->nivel_archive3= $id;
    }
    public function get_nivel_archive3() {
        return $this->nivel_archive3;
    }
    public function set_nivel_archive4($id= true) {
        $this->nivel_archive4= $id;
    }
    public function get_nivel_archive4() {
        return $this->nivel_archive4;
    }

    public function get_user_ldap($exec_sql= false) {
        if (!$exec_sql) return $this->user_ldap;

        $sql= "select user_ldap from tusuarios where id = $this->id_usuario";
        $result = $this->do_sql_show_error('get_user_ldap', $sql);
        $row = $this->clink->fetch_array($result);
        return $row['user_ldap'];
    }

    public function set_user_ldap($id, $update= true) {
        $update= !is_null($update) ? $update : true;
        if (empty($id))
            return;
        $this->user_ldap = $id;
        if (!$update)
            return false;

        $sql= "update tusuarios set user_ldap= '$this->user_ldap' ";
        if (!empty($this->id_usuario))
            $sql.= "where id = $this->id_usuario";
        if (!empty($this->usuario) && !empty($this->clave))
            $sql.= "where usuario = '$this->usuario' && clave = '".md5($this->clave)."'";
        $result = $this->do_sql_show_error('set_user_ldap', $sql);
        return $this->error;
    }

    public function GetEliminado() {
        return $this->eliminado;
    }

    public function SetFirma($id, $param) {
        $this->firma = $id;
        $this->param = $param;
    }


    public function SetIdProceso_jefe($id) {
        $this->id_proceso_jefe= $id;
    }
    public function GetIdProceso_jefe() {
        return $this->id_proceso_jefe;
    }
    public function set_id_proceso_jefe_code($id) {
        $this->id_proceso_jefe_code= $id;
    }
    public function get_id_proceso_jefe_code() {
        return $this->id_proceso_jefe_code;
    }
    
    public function Init() {
        $this->noIdentidad= null;

        $this->id_proceso= null;
        $this->id_proceso_code= null;
        $this->origen_data= null;
        $this->user_ldap= null;

        $this->role = null;
        $this->nombre = null;
        $this->cargo= null;
        $this->email= null;
        $this->clave = null;
        $this->usuario = null;
        $this->responsable = null;
        $this->global_user = 0;

        $this->acc_sys = null;
        $this->acc_planwork = null;
        $this->acc_planrisk = null;
        $this->acc_planaudit = null;
        $this->acc_planheal = null;
        $this->acc_planproject= null;
        $this->acc_archive = null;
        $this->freeassign = null;

        $this->nivel_archive2= null;
        $this->nivel_archive3= null;
        $this->nivel_archive4= null;

        $this->id_proceso_jefe= null;
    }

    public function Set($id_usuario = 0) {
        $this->Init();
        if (empty($id_usuario))
            $id_usuario = $this->id_usuario;

        $this->id = $id_usuario;
        $sql = "select * from tusuarios where id = $id_usuario ";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->role = $row['nivel'];
            $this->nombre = stripslashes($row['nombre']);
            $this->clave = $row['clave'];
            $this->usuario = stripslashes($row['usuario']);
            $this->cargo = stripslashes($row['cargo']);

            $this->conectado = $row['conectado'];
            $this->eliminado = $row['eliminado'];

            $this->acc_sys = $row['acc_sys'];
            $this->acc_planwork = $row['acc_planwork'];
            $this->acc_planrisk = $row['acc_planrisk'];
            $this->acc_planaudit = $row['acc_planaudit'];
            $this->acc_planheal = $row['acc_planheal'];
            $this->acc_planproject = $row['acc_planproject'];

            $this->freeassign = $row['freeassign'];

            $this->acc_archive = $row['acc_archive'];
            $this->nivel_archive2 = $row['nivel_archive2'];
            $this->nivel_archive3 = $row['nivel_archive3'];
            $this->nivel_archive4 = boolean($row['nivel_archive4']);

            $this->email = $row['email'];
            $this->firma = $row['firma'];
            $this->param = $row['firma_param'];

            $this->noIdentidad = $row['noIdentidad'];
            $this->global_user = boolean($row['global_user']);

            $this->id_proceso = $row['id_proceso'];
            $this->id_proceso_code = $row['id_proceso_code'];
            $this->origen_data = stripslashes($row['origen_data']);

            $this->user_ldap = $row['user_ldap'];
            $this->id_proceso_jefe= $row['id_proceso_jefe'];
            $this->id_proceso_jefe_code= $row['id_proceso_jefe_code'];

            $this->kronos = $row['cronos'];
        }
        return $this->error;
    }

    public function add() {
        $clave= !empty($this->clave) ? md5(trim($this->clave)) : null;
        $clave = setNULL_str($clave);

        $nombre = setNULL_str($this->nombre);
        $cargo = setNULL_str($this->cargo);
        $usuario = setNULL_str($this->usuario);
        $email = setNULL_str($this->email);
        $user_ldap= setNULL_str($this->user_ldap);

        $freeassign= setNULL($this->freeassign);

        $acc_planwork = setNULL($this->acc_planwork);
        $acc_planrisk = setNULL($this->acc_planrisk);
        $acc_planaudit = setNULL($this->acc_planaudit);
        $acc_planheal = setNULL($this->acc_planheal);
        $acc_planproject = setNULL($this->acc_planproject);

        $acc_sys = setNULL($this->acc_sys);

        $acc_archive = setNULL($this->acc_archive);
        $nivel_archive2= setNULL_empty($this->nivel_archive2);
        $nivel_archive3= setNULL_empty($this->nivel_archive3);
        $nivel_archive4= boolean2pg($this->nivel_archive4);

        if ($this->param)
            $param = "'{$this->param['name']}:{$this->param['type']}:{$this->param['size']}:{$this->param['dim']}'";
        else
            $param= setNULL_empty($this->param);

        $firma= $this->firma ? decodeBlob2pg($this->firma) : 'NULL';

        $noIdentidad = setNULL_str($this->noIdentidad);
        if (empty($this->global_user))
            $this->global_user = 0;

        $sql = "insert into tusuarios (nivel, nombre, usuario, clave, cargo, email, firma, firma_param, " . stringSQL("noIdentidad") . ", ";
        $sql.= "global_user, acc_sys, acc_planwork, acc_planrisk, acc_planaudit, acc_planheal, acc_planproject, acc_archive, nivel_archive2, ";
        $sql.= "nivel_archive3, nivel_archive4, freeassign, id_proceso, id_proceso_code, cronos, situs, user_ldap) values ($this->role, ";
        $sql.= "$nombre, $usuario, $clave, $cargo, $email, $firma, $param, $noIdentidad, " . boolean2pg($this->global_user) . ", $acc_sys, ";
        $sql.= "$acc_planwork, $acc_planrisk, $acc_planaudit, $acc_planheal, $acc_planproject, $acc_archive, $nivel_archive2, $nivel_archive3, ";
        $sql.= "$nivel_archive4, $freeassign, $this->id_proceso, '$this->id_proceso_code', ";
        $sql.= "'$this->cronos', '$this->location', $user_ldap)";
        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_usuario = $this->clink->inserted_id("tusuarios");
            $this->id = $this->id_usuario;
        }
        return $this->error;
    }

    public function set_ldap() {
        $sql= "update tusuarios set user_ldap= NULL, eliminado= null, acc_sys= null where id = $this->id_usuario ";
        $result= $this->do_sql_show_error('set_ldap', $sql);
        return $this->error;
    }

    public function update($encrypt_clave= false) {
        $encrypt_clave= !is_null($encrypt_clave) ? $encrypt_clave : false;

        $update_clave= strlen($this->clave) >= 5 && $this->clave != "12345678" ? true: false;
        $clave= !empty($this->clave) ? trim($this->clave) : null;
        $clave = setNULL_str(!$encrypt_clave ? $clave : md5($clave));   

        $noIdentidad = setNULL_str($this->noIdentidad);
        $nombre = setNULL_str($this->nombre);
        $cargo = setNULL_str($this->cargo);
        $usuario = setNULL_str($this->usuario);
        $email = setNULL_str($this->email);

        $freeassign= setNULL($this->freeassign);

        if ($this->id_usuario == _USER_SYSTEM)
            $this->user_ldap= null;

        $sql = "update tusuarios set id = id ";
        if (!empty($this->role))
            $sql .= ", nivel= $this->role ";
        if (!empty($this->nombre))
            $sql .= ", nombre= $nombre ";
        if (!empty($this->usuario))
            $sql .= ", usuario= $usuario ";
        if (!empty($this->email))
            $sql .= ", email= $email ";
        if (!empty($this->cargo))
            $sql .= ", cargo= $cargo ";

        if (!is_null($this->acc_planwork) || !is_null($this->acc_planrisk) || !is_null($this->acc_planaudit) || !is_null($this->acc_planheal)
                || !is_null($this->acc_planproject) || !is_null($this->acc_archive)) {

            $acc_planwork = setNULL($this->acc_planwork);
            $acc_planrisk = setNULL($this->acc_planrisk);
            $acc_planaudit = setNULL($this->acc_planaudit);
            $acc_planheal = setNULL($this->acc_planheal);
            $acc_planproject = setNULL($this->acc_planproject);
            $acc_archive = setNULL($this->acc_archive);
            $acc_sys = setNULL($this->acc_sys);

            $sql .= ", acc_sys= $acc_sys, acc_planwork= $acc_planwork, acc_planrisk= $acc_planrisk, ";
            $sql .= "acc_planaudit= $acc_planaudit, acc_planheal= $acc_planheal, acc_planproject= $acc_planproject, ";
            $sql .= "acc_archive= $acc_archive ";
        }

        if (!is_null($this->nivel_archive2) || !is_null($this->nivel_archive3) || !is_null($this->nivel_archive4)) {
            $nivel_archive2= setNULL_empty($this->nivel_archive2);
            $nivel_archive3= setNULL_empty($this->nivel_archive3);
            $nivel_archive4= boolean2pg($this->nivel_archive4);
            $sql.= ", nivel_archive2= $nivel_archive2, nivel_archive3= $nivel_archive3, nivel_archive4= $nivel_archive4 ";
        }
        if (!is_null($this->freeassign)) {
            $sql.= ", freeassign= $freeassign ";
        }
        if (!is_null($this->global_user)) {
            if (empty($this->global_user))
                $this->global_user = 0;
            $sql .= ", global_user= " . boolean2pg($this->global_user) . " ";
        }
        if (!is_null($this->noIdentidad)) {
            $sql.= ", ".stringSQL("noIdentidad") . "= $noIdentidad ";
        }
        if (!empty($this->id_proceso)) {
            $sql .= ", id_proceso= $this->id_proceso, id_proceso_code= '$this->id_proceso_code' ";
        }    
        $sql.= ", cronos= '$this->cronos', situs= '$this->location' ";

        if (!is_null($this->firma)) {
            if ($this->param)
                $param = "'{$this->param['name']}:{$this->param['type']}:{$this->param['size']}:{$this->param['dim']}'";
            else
                $param= setNULL_empty($this->param);

            $firma= !empty($this->firma) ? decodeBlob2pg($this->firma) : 'NULL';
        }
        if (!is_null($this->firma))
            $sql .= ", firma= $firma, firma_param= $param ";

        if ($update_clave) {
            $sql .= ", clave= $clave ";
        }
        if (!empty($this->user_ldap)) {
            $user_ldap= setNULL_str($this->user_ldap);
            $sql.= ", user_ldap= $user_ldap ";
        }
        $sql .= "where id = $this->id_usuario ";

        $result= $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function update_proceso($id= null, $id_code= null) {
        $this->id_proceso= !empty($id) ? $id : $this->id_proceso;
        $this->id_proceso_code= !empty($id_code) ? $id_code : $this->id_proceso_code;

        $sql= "update tusuarios set id_proceso= $this->id_proceso, id_proceso_code= '$this->id_proceso_code', ";
        $sql.= "cronos= '$this->cronos' where id = $this->id_usuario";
        $result= $this->do_sql_show_error('update_proceso', $sql);
    }

    public function update_clave($array_NO_LOCALs = null, $email = null) {
        global $config;

        if (empty($this->clave))
            return -1;
        if (empty($this->id_usuario) && (empty($this->usuario) || empty($array_NO_LOCALs)))
            return -1;

        $sql= "select * from tusuarios ";
        if (!empty($this->id_usuario))
            $sql .= "where id = $this->id_usuario ";
        elseif (!empty($this->usuario) && !empty($array_NO_LOCALs)) {
            $prs = implode(",", $array_NO_LOCALs);
            $sql .= "where usuario = '$this->usuario' and id_proceso in ($prs) ";
        }
        if (!empty($this->user_ldap)) {
            $user_ldap= setNULL_str($this->user_ldap);
            $sql.= "and user_ldap = $user_ldap ";
        }
        $result= $this->do_sql_show_error('update_clave', $sql);
        $cant= $this->cant;
        $id_usuario= $this->clink->fetch_array($result)['id'];

        if(!empty($id_usuario)) {
            $sql = "update tusuarios set cronos= '$this->cronos', situs= '$this->location' ";
            if (!$config->block_no_ldap_login)
                $sql.= ", clave= '".md5($this->clave)."' ";
            if (!empty($email))
                $sql .= ", email= '$email' ";
            $sql.= "where id = $id_usuario";
        }    
        $this->do_sql_show_error('update_clave', $sql);
        return $cant;
    }

    public function listar($flag = true, $index = 'id', $local = _NO_LOCAL, $user_show_reject = 0, $init_array = true,
                            $only_jefe= false, $only_in_entity= false) {
        $flag= is_null($flag) ? true : $flag;
        $index= is_null($index) ? 'id' : $index;
        $local= !is_null($local) ? $local : _NO_LOCAL;
        $user_show_reject= !is_null($user_show_reject) ? $user_show_reject : 0;
        $only_jefe = !is_null($only_jefe) ? (bool)$only_jefe : false;
        $only_in_entity = !is_null($only_in_entity) ? (bool)$only_in_entity : false;
        $init_array = is_null($init_array) ? true : $init_array;

        $this->use_copy_tusuarios = !is_null($this->use_copy_tusuarios) ? $this->use_copy_tusuarios : false;
        $tusuarios = ($this->if_copy_tusuarios && $this->use_copy_tusuarios) ? '_ctusuarios' : 'tusuarios';
		
		$sql_entity= null;
		if (!empty($this->id_entity)) {
			$sql_entity= "and ($tusuarios.id_proceso = tprocesos.id ";
			if (!empty($this->id_entity)) {
				$sql_entity.= "and ((tprocesos.id = $this->id_entity or tprocesos.id_entity = $this->id_entity) ";
				if (!$only_in_entity)
					$sql_entity.= "or ($tusuarios.id = tprocesos.id_responsable and tprocesos.id_entity is null) ";
				$sql_entity.= ") ";
			}    
			$sql_entity.= ") ";
		}

        if ($init_array)
            if (isset($this->array_usuarios)) unset($this->array_usuarios);

        if (empty($this->id_proceso)) {
            $sql = "select distinct $tusuarios.*, $tusuarios.id as _id, $tusuarios.id_proceso as _id_proceso ";
            $sql .= "from $tusuarios, tprocesos where ($tusuarios.id is not null and $tusuarios.id != "._USER_SYSTEM.") ";
            $sql.= $sql_entity;
            if (!$user_show_reject) {
                $sql .= "and ($tusuarios.eliminado is null ";
                if (!empty($this->user_date_ref))
                    $sql.= "or $tusuarios.eliminado > '$this->user_date_ref' ";
                $sql.= ") ";
            } 
            $sql .= "order by nombre asc ";
        }	

        if (!empty($this->id_proceso)) {
            if ($local == _NO_LOCAL && $tusuarios == '_ctusuarios' && !$only_jefe) {
                $this->_listar_for_copy('id_usuario', $index, $user_show_reject);
                $this->_listar_for_copy('id_grupo', $index, $user_show_reject);

                return $this->array_usuarios;
            }
            if ($local == _LOCAL) {
                $sql = "select distinct $tusuarios.*, $tusuarios.id as _id, $tusuarios.id_proceso as _id_proceso ";
                $sql.= "from $tusuarios where nombre is not null ";
                $sql.= $only_jefe ? "and id_proceso_jefe = $this->id_proceso " : "and id_proceso = $this->id_proceso ";
                $sql.= "and $tusuarios.id != "._USER_SYSTEM." ";
                if (!$user_show_reject) {
                    $sql .= "and ($tusuarios.eliminado is null ";
                    if (!empty($this->user_date_ref))
                        $sql.= "or $tusuarios.eliminado > '$this->user_date_ref'";
                    $sql.= ") ";
                } 
                $sql .= "order by nombre asc ";
            }
            if ($local == _NO_LOCAL && $tusuarios == 'tusuarios' && !$only_jefe) {
                $sql = "select distinct tusuarios.*, tusuarios.id as _id, tusuarios.id_proceso as _id_proceso ";
                $sql.= "from tusuarios, tusuario_procesos ";
                $sql.= "where tusuarios.nombre is not null and ((tusuario_procesos.id_grupo is null ";
                $sql.= "and (tusuario_procesos.id_proceso = $this->id_proceso and tusuario_procesos.id_usuario = tusuarios.id)) ";
                $sql.= "or tusuarios.id_proceso = $this->id_proceso) and tusuarios.id != "._USER_SYSTEM." ";
                if (!$user_show_reject) {
                    $sql .= "and ($tusuarios.eliminado is null ";
                    if (!empty($this->user_date_ref))
                        $sql.= "or $tusuarios.eliminado > '$this->user_date_ref'";
                    $sql.= ") ";
                } 
                $sql.= "union ";
                $sql.= "select distinct tusuarios.*, tusuarios.id as _id, tusuarios.id_proceso as _id_proceso ";
                $sql.= "from tusuarios, view_usuario_proceso_grupos ";
                $sql.= "where tusuarios.id = view_usuario_proceso_grupos.id_usuario ";
                $sql.= "and view_usuario_proceso_grupos.id_proceso = $this->id_proceso ";
                if (!$user_show_reject) {
                    $sql .= "and ($tusuarios.eliminado is null ";
                    if (!empty($this->user_date_ref))
                        $sql.= "or $tusuarios.eliminado > '$this->user_date_ref'";
                    $sql.= ") ";
                } 
                $sql .= "and tusuarios.id != "._USER_SYSTEM." order by nombre asc ";
            }
            if ($local == _NO_LOCAL && $only_jefe) {
                $sql = "select distinct $tusuarios.*, $tusuarios.id as _id, $tusuarios.id_proceso as _id_proceso from $tusuarios ";
                $sql.= "where id_proceso_jefe = $this->id_proceso ";
            }
        }

        $result = $this->do_sql_show_error('listar', $sql);

        if ($flag)
            return $result;

        $array_ids= null;
        while ($row = $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= 1;

            $array = array('id' => $row['_id'], 'nombre' => $row['nombre'], 'email' => $row['email'], 'cargo' => $row['cargo'],
                'origen_data' => $row['origen_data'], 'eliminado' => $row['eliminado'], 'usuario' => $row['usuario'],
                '_id' => $row['_id'], 'id_proceso'=>$row['_id_proceso'], 'situs'=>$row['situs'], 'nivel'=>$row['nivel']);

            if ($index == 'id')
                $this->array_usuarios[$row['_id']] = $array;
            else
                $this->array_usuarios[$row['usuario']] = $array;
        }
        return $this->array_usuarios;
    }

    public function listar_all($flag = true, $index = 'id', $user_show_reject = 0) {
        $flag = is_null($flag) ? true : $flag;
        $index = is_null($index) ? 'id' : $index;

        if (isset($this->array_usuarios)) unset($this->array_usuarios);
        $this->use_copy_tusuarios = !is_null($this->use_copy_tusuarios) ? $this->use_copy_tusuarios : false;
        $tusuarios = ($this->if_copy_tusuarios && $this->use_copy_tusuarios) ? '_ctusuarios' : 'tusuarios';

        $sql = "select distinct $tusuarios.*, $tusuarios.id as _id from $tusuarios, tprocesos where ($tusuarios.id is not null ";
        $sql .= "and $tusuarios.id != "._USER_SYSTEM.") ";
        if (!$user_show_reject) {
            $sql .= "and ($tusuarios.eliminado is null ";
            if (!empty($this->user_date_ref))
                $sql.= "or $date($tusuarios.eliminado) > date('$this->user_date_ref')";
            $sql.= ") ";
        }    
        $sql .= "order by nombre asc ";
        $result = $this->do_sql_show_error('listar', $sql);

        if ($flag)
            return $result;

        $array_ids= null;
        while ($row = $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= 1;

            $array = array('id' => $row['_id'], 'nombre' => $row['nombre'], 'email' => $row['email'], 'cargo' => $row['cargo'],
                'origen_data' => $row['origen_data'], 'eliminado' => $row['eliminado'], 'usuario' => $row['usuario'],
                '_id' => $row['_id'], 'situs'=>$row['situs']);

            if ($index == 'id')
                $this->array_usuarios[$row['_id']] = $array;
            else
                $this->array_usuarios[$row['usuario']] = $array;
        }
        return $this->array_usuarios;
    }

    public function eliminar($go_delete= true, $show_error_delete= false) {
        $go_delete= !is_null($go_delete) ? $go_delete : true;
        $show_error_delete= !is_null($show_error_delete) ? $show_error_delete : false;
        $this->error= null;
        $error_delete= null;

        if ($go_delete) {
            $sql = "delete from tusuarios where id = $this->id_usuario and eliminado is null";
            $this->do_sql_show_error('eliminar', $sql);
        }
        if (!is_null($this->error))
            $error_delete= "$this->error --> ";

        if (!is_null($this->error) || !$go_delete)
            $this->set_eliminado();

        $error= $error_delete.$this->error;
        $error= !empty($error) ? $error : null;
        return !$show_error_delete ? $this->error : $error;
    }

    public function set_eliminado($restore = null) {
        $eliminado = (is_null($restore) || $restore) ? $this->cronos : null;
        $eliminado = setNULL_str($eliminado);

        $sql = "update tusuarios set eliminado= $eliminado where id = $this->id_usuario ";
        $this->do_sql_show_error('set_eliminado', $sql);

        $sql = "update tusuario_grupos set eliminado= $eliminado where id_usuario = $this->id_usuario ";
        $this->do_sql_show_error('set_eliminado', $sql);

        if (is_null($restore) || $restore)
            $this->clean_reg($eliminado);

        if (is_null($this->error) && $restore)
            $this->error = "Para hacer efectivo este cambio deberá salir y entrar nuevamente al sistema";

        return $this->error;
    }

    private function clean_reg($eliminado) {
        /*
        $sql = "delete from teventos where fecha_inicio_plan >= '$eliminado' ";
        $sql .= "and id_responsble = $this->id_usuario ";
        $this->do_sql_show_error('clean_reg', $sql);

        $sql = "delete from teventos where fecha_inicio_plan >= '$eliminado' ";
        $sql .= "and id_responsble = $this->id_usuario ";
        $this->do_sql_show_error('clean_reg', $sql);

        $sql = "delete ";
        $sql .= $_SESSION["_DB_SYSTEM"] == "mysql" ? "treg_evento from treg_evento, teventos " : "from treg_evento using teventos ";
        $sql .= "where treg_evento.id_evento = teventos.id ";
        $sql .= "and fecha_inicio_plan >= $eliminado and treg_evento.id_usuario = $this->id_usuario ";

        $this->do_sql_show_error('clean_reg', $sql);
         */
    }

    public function if_unique_username() {
        $usuario = setNULL_str($this->usuario, true, false);
        if (isset($this->array_procesos)) 
            unset($this->array_procesos);

        $sql = "select tusuarios.id as _id_usuario, tprocesos.id as _id, tprocesos.id_code as _id_code, ";
        $sql .= "tprocesos.nombre as _nombre, tprocesos.tipo as tipo from tusuarios, tprocesos where usuario = $usuario ";
        $sql.= "and tusuarios.id_proceso = tprocesos.id ";
        if (!empty($this->id_entity))
            $sql.= "and (id_entity = $this->id_entity or tprocesos.id = $this->id_entity) ";
        $result = $this->do_sql_show_error('if_unique_username', $sql);
        $cant = $this->cant;

        if (empty($cant) || $cant == -1)
            return null;

        $i = 0;
        while ($row = $this->clink->fetch_array($result)) {
            $array = array('id' => $row['_id'], 'id_code' => $row['_id_code'], 'nombre' => stripslashes($row['_nombre']),
                            'tipo' => $row['tipo'], 'id_usuario' => $row['_id_usuario']);
            $this->array_procesos[$i++] = $array;
        }
        return $i;
    }

    public function login($login_db = true) {
        $login_db = !is_null($login_db) ? $login_db : true;
        $clave = md5($this->clave);
        $usuario = setNULL_str($this->usuario, true, false);

        $sql = "select * from tusuarios where usuario = $usuario ";
        if ($login_db)
            $sql .= "and clave = '$clave' ";
        if (!empty($this->id_proceso))
            $sql .= "and id_proceso = $this->id_proceso ";
        if (!empty($this->user_ldap))
            $sql.= "and user_ldap = '$this->user_ldap' ";
        $result = $this->do_sql_show_error('login', $sql);

        if (!$result)
            return false;
        if ($this->cant == 0)
            return false;

        $row = $this->clink->fetch_array($result);
        $this->id_usuario = $row['id'];
        $this->nombre = stripslashes($row['nombre']);
        $this->role = $row['nivel'];
        $this->conectado = boolean($row['conectado']);
        $this->eliminado = $row['eliminado'];

        $this->acc_sys = $row['acc_sys'];
        $this->acc_planwork = $row['acc_planwork'];
        $this->acc_planaudit = $row['acc_planaudit'];
        $this->acc_planrisk = $row['acc_planrisk'];
        $this->acc_planheal = $row['acc_planheal'];
        $this->acc_planproject = $row['acc_planproject'];
        $this->acc_archive = $row['acc_archive'];

        $this->user_ldap= $row['user_ldap'];

        return true;
    }

    public function SetConectado($conectado = true) {
        if (empty($conectado)) 
            $conectado = 0;
        $this->conectado= $conectado;

        $sql = "update tusuarios set conectado= " . boolean2pg($conectado) . " where id = $this->id_usuario";
//	$result= $this->clink->query($sql);
    }

    public function GetEmail($id_usuario = null) {
        if (empty($id_usuario))  
            return null;

        $sql = "select id, usuario, nombre, email, cargo, firma, id_proceso, id_proceso_code from tusuarios where id = $id_usuario ";
        $result = $this->do_sql_show_error('GetEmail', $sql);
        $this->cant = $this->clink->num_rows($result);
        if (empty($this->cant))
            return null;
        return $this->clink->fetch_array($result);
    }

    public function if_eliminado($id_usuario, &$row) {
        if (!empty($this->user_date_ref)) {
            $date= date('Y-m-d', strtotime($this->user_date_ref));
        } else {
            $date= "$this->year-".str_pad($this->month, '0', 2, STR_PAD_RIGHT).'-'.str_pad($this->day, '0', 2, STR_PAD_RIGHT);
        }

        $sql= "select nombre, cargo, email, eliminado from tusuarios where id = $id_usuario";
        $result= $this->do_sql_show_error('if_eliminado', $sql);
        $row= $this->clink->fetch_array($result);

        $deleted= $row['eliminado'] && strtotime($row['eliminado']) <= strtotime($date) ? true : false;
        return $deleted;
    }

    private function _tipo_id_proceso_jefe() {
        $sql= "select * from tprocesos where id_responsable = $this->id_usuario order by tipo asc limit 1";
        $result= $this->do_sql_show_error('_tipo_id_proceso_jefe', $sql);
        $row= $this->clink->fetch_array($result);
        return $row['tipo'];
    }

    public function update_id_proceso_jefe($action= 'add', $tipo_prs) {
        $tipo= $this->_tipo_id_proceso_jefe();
        if (!empty($tipo) && $tipo_prs > $tipo)
            return null;

        if ($action == 'update') {
            $sql= "update tusuarios set id_proceso_jefe = NULL, id_proceso_jefe_code = NULL ";
            $sql.= "where id_proceso_jefe = $this->id_proceso_jefe";
            $result= $this->do_sql_show_error('update_id_proceso_jefe', $sql);
        }
        $sql= "update tusuarios set id_proceso_jefe = $this->id_proceso_jefe, id_proceso_jefe_code = '$this->id_proceso_jefe_code' ";
        $sql.= "where id = $this->id_usuario";
        $result= $this->do_sql_show_error('update_id_proceso_jefe', $sql);
    }
}
?>