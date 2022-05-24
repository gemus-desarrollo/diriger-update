<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 3/26/15
 * Time: 6:59 a.m.
 */

require_once "config.inc.php";
require_once "class/base.class.php";
require_once "class/proceso_item.class.php";
require_once "class/tipo_reunion.class.php";
require_once "class/tipo_evento.class.php";
require_once "class/tipo_auditoria.class.php";

require_once "./../tools/archive/php/class/organismo.class.php";

class TbaseInterface extends Tbase {

    public $menu;
    
    protected $fecha_inicio, 
            $time_inicio, 
            $fecha_fin, 
            $time_fin;
    protected $model;
    protected $_radio_date;

    protected $numero;
    protected $peso;

    protected $if_send_up, 
            $if_send_down;

    protected $control_list;
    protected $accept_user_list, 
            $denied_user_list;
    protected $accept_group_list, 
            $denied_group_list;
    protected $accept_mail_user_list, 
            $denied_mail_user_list;
    protected $accept_process_list, 
            $denied_process_list;

    protected $accep_organizations_list,
            $denied_organizations_list;

    protected $_toshow;
    protected $_id_tipo_evento, 
              $_id_tipo_evento_code;

    protected $empresarial, 
              $_empresarial;
    protected $indice,
              $indice_plus;          
    protected $_indice,
              $_indice_plus;
              
    protected $_id_responsable;

    protected $table;
    protected $field;
    public $error;

    protected $origen;

    protected $_fecha_inicio,
              $_fecha_fin;
    
    protected $_inicio,
            $_fin;
    
    public function __construct($clink) {
        Tbase::__construct($clink);
        $this->clink= $clink;

        $this->_radio_date= !is_null($_POST['_radio_date']) ? $_POST['_radio_date'] : $_GET['_radio_date'];
        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->id_code= !empty($_GET['id_code']) ? $_GET['id_code'] : $_POST['id_code'];

        $this->action= !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->signal= !empty($_GET['signal']) ? $_GET['signal'] : $_POST['signal'];
        $this->menu= !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];

        $this->year= !empty($_GET['year']) ? (int)$_GET['year'] : (int)$_POST['year'];
        $this->month= !empty($_GET['month']) ? (int)$_GET['month'] : (int)$_POST['month'];
        $this->day= !empty($_GET['day']) ? (int)$_GET['day'] : (int)$_POST['day'];
        
        if (empty($this->month))
            $this->month= null;
        if (empty($this->day))
            $this->day= null;
        
        $this->error= $_GET['error'];

        $this->inicio= $_POST['inicio'];
        $this->fin= $_POST['fin'];

        $this->id_escenario= !empty($_GET['id_escenario']) ? $_GET['id_escenario'] : $_POST['id_escenario'];
        $this->id_escenario_code= $_POST['id_escenario_code_'.$this->id_escenario];

        $this->id_proceso=  !empty($_POST['proceso']) ? $_POST['proceso'] : $_POST['id_proceso'];
        $this->id_proceso_code=  $_POST['proceso_code_'.$this->id_proceso];

        if (is_null($this->id_escenario)) {
            $this->id_escenario= $_POST['id_escenario_'.$this->id_proceso];
            $this->id_escenario_code= $_POST['id_escenario_code_'.$this->id_proceso];
        }
        if (empty($this->id_escenario)) {
            $this->id_escenario= $_SESSION['current_id_escenario'];
            $this->id_escenario_code= $_SESSION['current_id_escenario_code'];
        }

        $this->id_perspectiva= $_POST['perspectiva'];
        $this->id_perspectiva_code= $_POST['perspectiva_code_'.$this->id_perspectiva];

        $this->nombre= $_POST['nombre'];
        $this->numero= $_POST['numero'];
        $this->peso= $_POST['peso'];

        $this->observacion= trim(!empty($_POST['observacion']) ? $_POST['observacion'] : $_GET['observacion']);
        $this->descripcion= trim(!empty($_POST['descripcion']) ? $_POST['descripcion'] : $_GET['descripcion']);

        $this->obj_code= new Tcode($this->clink);
    }

    protected function _setReg($found) {
        $this->_fecha_inicio= $this->obj->GetFechaInicioPlan();

        $this->obj->SetCumplimiento(_NO_INICIADO);
        $this->obj->set_user_check(false);

        $this->obj->SetIdAuditoria($this->_id_auditoria);
        $this->obj->set_id_auditoria_code($this->_id_auditoria_code);
        $this->obj->SetIdEvento($this->_id_evento);
        $this->obj->set_id_evento_code($this->_id_evento_code);
        $this->obj->SetIdTarea($this->id_tarea);
        $this->obj->set_id_tarea_code($this->id_tarea_code);

        $found_user= $found && !is_null($found[1]) ? true : false;
        $this->array_usuarios= $found && !is_null($found[1]) ? $this->obj->get_usuarios_array_from_evento() : false;

        $this->obj->copy_in_object($this->obj_reg);
        $this->obj_reg->SetYear($this->year);
    }

    protected function setUsuarios() {
        $error = null;
        $user_ref_date = !is_null($this->user_date_ref) ? $this->user_date_ref : $this->fecha_fin;

        $obj = new Tusuario($this->clink);
        $obj->SetIdEntity($_SESSION['id_entity']);
        $obj->set_user_date_ref($user_ref_date);
        $result = $obj->listar(null, null, _LOCAL);

        while ($row = $this->clink->fetch_array($result)) {
            $id = $_POST['multiselect-users_user' . $row['_id']];
            $_id = $_POST['multiselect-users_init_user' . $row['_id']];

            $array = array('id' => $row['_id'], 'nombre' => $row['nombre'], 'email' => $row['email'], 'cargo' => $row['cargo'],
                'usuario' => $row['usuario'], 'eliminado' => $row['eliminado'], 'id_proceso' => $row['id_proceso'],
                'id_proceso_code' => $row['id_proceso_code'], 'indirect' => null, 'flag' => 0);

            $array['flag'] = empty($_id) ? 0 : 1;

            $this->obj->SetIdUsuario($row['_id']);

            if (!empty($id) && empty($_id)) {
                if (!array_key_exists($row['_id'], (array) $this->accept_mail_user_list)) {
                    if (empty($array['flag']))
                        $error = $this->obj->setUsuario('add');
                    if (is_null($error))
                        $this->accept_user_list[$row['_id']] = $array;
                }
            } else {
                if (!empty($_id) && empty($id)) {
                    if (!array_key_exists($row['_id'], (array) $this->denied_mail_user_list)) {
                        $error = $this->obj->setUsuario('delete');
                        $nums = $this->obj->GetCantidad();

                        if (!empty($nums) && is_null($error)) {
                            $this->obj_code->reg_delete($this->table, $this->field, $this->id_code, 'id_usuario', $row['_id']);
                            $this->denied_user_list[$row['_id']] = $array;
            }   }   }    }

            if (!is_null($error))
                break;
        }

        if (!empty($this->id_responsable) && array_key_exists($this->id_responsable, $this->accept_user_list) == false) {
            $row = $obj->GetEmail($this->id_responsable);
            $this->obj->SetIdUsuario($this->id_responsable);
            $error = $this->obj->setUsuario();

            $array = array('id' => $row['id'], 'nombre' => $row['nombre'], 'email' => $row['email'], 'cargo' => $row['cargo'],
                'usuario' => $row['usuario'], 'eliminado' => $row['eliminado'], 'id_proceso' => $row['id_proceso'],
                'id_proceso_code' => $row['id_proceso_code'], 'indirect' => null, 'flag' => 0);

            if (is_null($error))
                $this->accept_user_list[$row['id']] = $array;
        }

        $this->accept_mail_user_list = array_merge_overwrite((array) $this->accept_mail_user_list, (array) $this->accept_user_list);
        $this->denied_mail_user_list = array_merge_overwrite((array) $this->denied_mail_user_list, (array) $this->denied_user_list);

        unset($obj);
        return $error;
    }

    protected function setGrupos() {
        $error= null;

        $obj= new Tgrupo($this->clink);
        $obj->SetIdEntity($_SESSION['id_entity']);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $id= $_POST['multiselect-users_grp'.$row['id']];
            $_id= $_POST['multiselect-users_init_grp'.$row['id']];

            $this->obj->cleanListaUser();
            $this->obj->SetIdGrupo($row['_id']);

            if (!empty($id) || !empty($_id)) {
                $this->obj->push2ListaUserGroup($row['_id'], true);
                $user_array= $this->obj->get_list_user();
            }

            $array= array('id'=>$row['_id'], 'flag'=>0);
            $array['flag']= empty($_id)?0:1;

            if (!empty($_id)) {
                while (list($index, $cell)= each($user_array))
                    $user_array[$index]['flag']= 1;
                reset($user_array);
            }

            if (!empty($id)) {
                if (!array_key_exists($row['id'], $this->accept_group_list)) {
                    if (empty($array['flag']))
                        $error= $this->obj->setGrupo('add');

                    if (is_null($error)) {
                        $this->accept_group_list[$row['id']]= $array;
                        $this->accept_mail_user_list= array_merge_overwrite((array)$this->accept_mail_user_list, (array)$user_array);
                    }
            }   }

            else {
                if (!empty($_id)) {
                    $this->obj->SetIdGrupo($row['id']);
                     $this->obj->setGrupo('delete');
                     $nums= $this->obj->GetCantidad();

                     if (!empty($nums) && is_null($error)) {
                         $this->denied_group_list[$row['_id']]= $array;
                         $this->denied_mail_user_list= array_merge_overwrite((array)$this->denied_mail_user_list, (array)$user_array);

                         $this->obj_code->reg_delete($this->table, $this->field, $this->id_code, 'id_grupo', $row['id']);
            }    }   }

            if (!is_null($error))
                break;
        }

        $this->accept_mail_user_list= array_unique((array)$this->accept_mail_user_list, SORT_REGULAR);
        $this->denied_mail_user_list= array_unique((array)$this->denied_mail_user_list, SORT_REGULAR);

        unset($obj);
        return $error;
    }

    protected function setOrganismos() {
        $tobj= new Torganismo($this->clink);
        $result= $tobj->listar(false, true);

        while ($row= $this->clink->fetch_array($result)) {
            $id_prs= $row['_id'];
            $id_prs_code= ($row['_id_code']); 
            
            $id= $_POST['multiselect-org_'.$id_prs];
            $_id= $_POST['multiselect-org_init_'.$id_prs];

            if (!empty($id)) {
                if (is_null($this->accept_organizations_list[$id_prs])) {
                    $array= array('id'=>$id_prs, 'id_code'=>$id_prs_code, 'flag'=>0);

                    $array['flag']= empty($_id) ? 0 : 1;
                    $this->accept_organizations_list[$id_prs]= $array;
                }
            } else {
                 if (!empty($_id)) {
                     if (is_null($this->denied_organizations_list[$id_prs])) {
                         $this->denied_organizations_list[$id_prs]= array('id'=>$id_prs, 'id_code'=>$id_prs_code);
            }   }   }
        }        
    }

    protected function set_organizations_from_array($found= false, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

        $obj_prs= new Torganismo($this->clink);
        $obj_prs->SetIdProceso($_SESSION['id_entity']);
        $obj_prs->SetTipo($_SESSION['entity_tipo']);

        $obj_prs->SetIdEvento($this->_id_evento);
        $obj_prs->set_id_evento_code($this->_id_evento_code);

        reset($this->accep_organizations_list);
        foreach ($this->accept_organizations_list as $array) {
            if ($found && !empty($prs['flag']))
                continue;            
            $obj_prs->SetIdOrganismo($array['id']);
            $obj_prs->set_id_organismo_code($array['id_code']);
            $sql.= $obj_prs->setEvento('add', $multi_query);
        } 

        reset($this->denied_organizations_list);
        foreach ($this->denied_organizations_list as $prs) {
            if (array_key_exists($prs['id'], (array)$this->accept_organizations_list) == true) 
                unset($this->accept_organizations_list[$prs['id']]);

            $obj_prs->SetIdOrganismo($array['id']);
            $obj_prs->set_id_organismo_code($array['id_code']);
            $sql.= $obj_prs->setEvento('delete', $multi_query);            
        }

        if ($multi_query && !empty($sql))
            $this->do_multi_sql_show_error('set_organizations_from_array', $sql);
    }

    protected function setProcesos($ifplanning= true) {
        global $array_procesos_entity;
        
        $naccepted= 0;
        $tobj= new Tproceso_item($this->clink);
        $tobj->set_use_copy_tprocesos(false);
        $result= $tobj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $id_prs= $row['_id'];
            $id_prs_code= ($row['_id_code']);
            $id_entity= !empty($row['id_entity']) ? $row['id_entity'] : $id_prs;   
            
            $id= $_POST['multiselect-prs_'.$id_prs];
            $_id= $_POST['multiselect-prs_init_'.$id_prs];

            if (!empty($id)) {
                ++$naccepted;
                if (is_null($this->accept_process_list[$id_prs])) {
                    $array= array('id'=>$id_prs, 'id_code'=>$id_prs_code, 'tipo'=>$row['tipo'], 'id_entity'=>$id_entity,
                                    'id_responsable'=>$row['id_responsable'], 'flag'=>0);

                    $array['flag']= empty($_id) ? 0 : 1;
                    $this->accept_process_list[$id_prs]= $array;
                }
            } else {
                 if (!empty($_id)) {
                    if (is_null($this->denied_process_list[$id_prs])) {
                        $this->denied_process_list[$id_prs]= array('id'=>$id_prs, 'id_code'=>$id_prs_code, 'tipo'=>$row['tipo'],
                                                                'id_entity'=>$id_entity, 'id_responsable'=>$row['id_responsable']);
            }   }   }
        }

        if ($naccepted == 0) {
            if (!empty($this->id_responsable) && $ifplanning) {
                ++$naccepted;
                $id_entity= $array_procesos_entity[$this->id_proceso_user_responsable]['id_entity'];
                $id_entity_code= $array_procesos_entity[$this->id_proceso_user_responsable]['id_entity_code'];

                $array= array('id'=>$this->id_proceso_user_responsable, 'id_code'=>$this->id_proceso_code_user_responsable, 
                            'id_entity'=>$id_entity, 'id_entity_code'=>$id_entity_code, 'flag'=>0);
                $this->accept_process_list[$this->id_proceso_user_responsable]= $array;
            }

            if (!empty($this->id_proceso) && !$ifplanning) {
                ++$naccepted;
                $id_entity= $array_procesos_entity[$this->id_proceso]['id_entity'];
                $id_entity_code= $array_procesos_entity[$this->id_proceso]['id_entity_code'];

                $array= array('id'=>$this->id_proceso, 'id_code'=>$this->id_proceso_code, 
                            'id_entity'=>$id_entity, 'id_entity_code'=>$id_entity_code, 'flag'=>0);
                $this->accept_process_list[$this->id_proceso]= $array;
        }   }

        if ($naccepted == 0) {
            $prs= array('id'=>$this->id_proceso, 'id_code'=>$this->id_proceso_code, 'flag'=>0,
                        'tipo'=>$array_procesos_entity[$this->id_proceso]['tipo'],
                        'id_responsable'=>$array_procesos_entity[$this->id_proceso]['id_responsable'],
                        'id_entity'=>$array_procesos_entity[$this->id_proceso]['id_entity']);
            $this->accept_process_list[$this->id_proceso]= $prs;
        }
    }

    protected function set_proceso_from_usuario_array() {
        global $array_procesos_entity;

        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetIdProceso($_SESSION['id_entity']);
        $obj_prs->SetTipo($_SESSION['entity_tipo']);

        reset($this->accept_mail_user_list);

        if (!empty($this->id_riesgo)) {
            $obj_prs->SetIdRiesgo($this->id_riesgo);
            $obj_prs->GetProcesosRiesgo(false);
        }
        if (!empty($this->id_nota)) {
            $obj_prs->SetIdNota($this->id_nota);
            $obj_prs->GetProcesosRiesgo(false);
        }
        if (!empty($this->id_proyecto)) {
            $obj_prs->SetIdProyecto($this->id_proyecto);
            $obj_prs->GetProcesosProyecto(false);
        }

        $array_procesos= $obj_prs->array_procesos;

        foreach ($array_procesos as $prs) {
            $id_entity= $array_procesos_entity[$prs['id']]['id_entity'];
            $id_entity_code= $array_procesos_entity[$prs['id']]['id_entity_code'];
            $this->accept_process_list[$prs['id']]= array('id'=>$prs['id'], 'id_code'=>$prs['id_code'], 'tipo'=>$prs['tipo'], 
                                                        'id_entity'=>$id_entity, 'id_entity_code'=>$id_entity_code, 'flag'=>0);
        }

        if ($this->className == 'Ttarea' && !empty($this->id_tarea)) {
            $obj_prs->GetProcesoTarea($this->id_tarea);
            $array_procesos_task= $obj_prs->array_procesos;

            foreach ($array_procesos_task as $prs) {
                $id_entity= $array_procesos_entity[$prs['id']]['id_entity'];
                $id_entity_code= $array_procesos_entity[$prs['id']]['id_entity_code'];
                $this->accept_process_list[$prs['id']]= array('id'=>$prs['id'], 'id_code'=>$prs['id_code'], 'tipo'=>$prs['tipo'], 
                                                                'id_entity'=>$id_entity, 'id_entity_code'=>$id_entity_code, 'flag'=>1);
            }

            if (array_key_exists($this->id_proceso_user_responsable, (array)$this->accept_process_list) == false) {
                $id_entity= $array_procesos_entity[$this->id_proceso_user_responsable]['id_entity'];
                $id_entity_code= $array_procesos_entity[$this->id_proceso_user_responsable]['id_entity_code'];  
                $this->accept_process_list[$this->id_proceso_user_responsable]= array('id'=>$this->id_proceso_user_responsable, 'id_code'=>$this->id_proceso_code_user_responsable, 
                                                                                    'id_entity'=>$id_entity, 'id_entity_code'=>$id_entity_code, 'flag'=>1);
            }
        }

        foreach ($this->accept_mail_user_list as $array) {
            $obj_prs->SetIdUsuario($array['id']);
            $obj_prs->get_procesos_by_user('eq_asc', _TIPO_PROCESO_INTERNO);

            foreach ($obj_prs->array_procesos as $prs) {
                $id_prs= $prs['id'];

                if (!empty($this->id_tarea) && $this->className == 'Ttarea') {
                    if (array_key_exists($id_prs, (array)$this->accept_process_list) == true)
                        if (isset($this->denied_process_list[$id_prs])) 
                            unset($this->denied_process_list[$id_prs]);
                }

                if (is_null($this->accept_process_list[$id_prs])) {
                    $this->accept_process_list[$id_prs]= array('id'=>$prs['id'], 'id_code'=>$prs['id_code'], 
                                                                'id_entity'=>$id_entity, 'id_entity_code'=>$id_entity_code, 'flag'=>0);
                }
        }   }

        $flag= !array_key_exists($this->id_proceso, (array)$this->accept_process_list) ? 0 : $this->accept_process_list[$this->id_proceso]['flag'] ? 1 : 0;
        $id_entity= $array_procesos_entity[$this->id_proceso]['id_entity'];
        $id_entity_code= $array_procesos_entity[$this->id_proceso]['id_entity_code'];
        $this->accept_process_list[$this->id_proceso]= array('id'=>$this->id_proceso, 'id_code'=>$this->id_proceso_code, 
                                                            'id_entity'=>$id_entity, 'id_entity_code'=>$id_entity_code, 'flag'=>$flag);

        foreach ($this->denied_process_list as $prs) {
            if (array_key_exists($prs['id'], (array)$this->accept_process_list) == true) {
                unset($this->accept_process_list[$prs['id']]);
        }   }
    }

    private function set_indice($empresarial, $id_tipo_evento) {
        $indice= null;
        $indice_plus= null;

        if (empty($id_tipo_evento))
            $indice= !empty($empresarial) ? $empresarial*pow(10,6) : null;
        else {
            $obj= new Ttipo_evento($this->clink);
            $obj->Set($id_tipo_evento);
            $indice= $obj->indice;
        }

        if (!empty($this->numero_plus))
            $indice_plus= index_to_number($this->numero_plus);
        
        return array($indice, $indice_plus);
    } 
    
    private function _fix_proceso_array($id_entity_target) { 
        
        if ($_SESSION['id_entity'] == $id_entity_target) {
            $array= array('id_tipo_evento'=> $this->id_tipo_evento, 'id_tipo_evento_code'=> $this->id_tipo_evento_code, 
                          'id_tipo_auditoria'=> $this->id_tipo_auditoria, 'id_tipo_auditoria_code'=> $this->id_tipo_auditoria_code, 
                          'id_tipo_reunion`'=> $this->id_tipo_reunion, 'id_tipo_reunion_code'=>$this->id_tipo_reunion_code);
            return $array;
        }
        
        $array= array('id_tipo_evento'=>null, 'id_tipo_evento_code'=>null, 'id_tipo_auditoria'=>null, 'id_tipo_auditoria_code'=>null, 
                      'id_tipo_reunion`'=>null, 'id_tipo_reunion_code'=>null);
        
        if (!empty($this->id_tipo_evento)) {
            $obj_tipo= new Ttipo_evento($this->clink);
            $obj_tipo->SetYear($this->year);
            
            $result= $obj_tipo->get_from_other_entity($this->id_tipo_evento, $_SESSION['id_entity'], $id_entity_target);
            $array['id_tipo_evento']= $result[0];
            $array['id_tipo_evento_code']= $result[1];
        }
        /*
        if ($this->id_tipo_auditoria) {
            $obj_tipo= new Ttipo_auditoria($this->clink);
            $result= $obj_tipo->get_from_other_entity($this->id_tipo_auditoria, $_SESSION['id_origen'], $id_entity_target);
            $array['id_tipo_auditoria']= $result[0];
            $array['id_tipo_auditoria_code']= $result[1];            
        }
        
        if ($this->id_tipo_reunion) {
            $obj_tipo= new Ttipo_reunion($this->clink);
            $result= $obj_tipo->get_from_other_entity($this->id_tipo_reunion, $_SESSION['id_origen'], $id_entity_target);
            $array['id_tipo_reunion']= $result[0];
            $array['id_tipo_reunion_code']= $result[1];            
        }   
        */
        return $array;
    }
    
    protected function fix_proceso_array($array) {
        $tobj= new Tproceso_item($this->clink);
        $tobj->SetIdEvento($this->_id_evento);
        $tobj->set_id_evento_code($this->_id_evento_code);
        $tobj->SetIdTarea($this->id_tarea);
        $tobj->set_id_tarea_code($this->id_tarea_code);
        $tobj->SetIdAuditoria($this->_id_auditoria);
        $tobj->set_id_auditoria_code($this->_id_auditoria_code);
        $tobj->SetYear($this->year);

        $id_responsable= null;
        $id_proceso= $this->id_proceso == $_SESSION['local_proceso_id'] && $_SESSION['local_proceso_id'] != $_SESSION['id_entity'] ? $_SESSION['id_entity'] : $this->id_proceso;
        $id_proceso_code= $id_proceso == $_SESSION['id_entity'] ? $_SESSION['id_entity_code'] : $this->id_proceso_code;

        $tobj->SetIdProceso($array['id']);
        $row= $this->action == 'update' ? $tobj->get_reg_proceso() : null;

        $fix_empresarial= false;
        if ($_SESSION['entity_tipo'] == _TIPO_CECM && ($array['tipo'] == _TIPO_EMPRESA || $array['tipo'] == _TIPO_GAE || $array['tipo'] == _TIPO_OACE || $array['tipo'] == _TIPO_OSDE))
            $fix_empresarial= true;
        if ($_SESSION['entity_tipo'] == _TIPO_OACE && ($array['tipo'] == _TIPO_EMPRESA || $array['tipo'] == _TIPO_GAE || $array['tipo'] == _TIPO_OSDE))
            $fix_empresarial= true;
        if (($_SESSION['entity_tipo'] == _TIPO_GAE || $_SESSION['entity_tipo'] == _TIPO_OSDE) && $array['tipo'] == _TIPO_EMPRESA)
            $fix_empresarial= true;
        if ($this->action == 'add' && ((!empty($array['id_entity']) && $array['id_entity'] != $_SESSION['id_entity'])
                                        || (empty($array['id_entity']) && $array['id'] != $_SESSION['id_entity'])))
            $id_responsable= $array['id_responsable'];

        $empresarial= $this->empresarial;
        $id_tipo_evento= $this->id_tipo_evento;
        $id_tipo_evento_code= $this->id_tipo_evento_code;

        $empresarial= ($fix_empresarial && $this->empresarial == 6) ? 5 : $this->empresarial;
        if ($fix_empresarial && $this->empresarial == 6) {
            $id_tipo_evento= ($this->action == 'add' || empty($row)) ? null : $row['id_tipo_evento'];
            $id_tipo_evento_code= ($this->action == 'add' || empty($row)) ? null : $row['id_tipo_evento_code'];
        }
        
        if (!empty($id_tipo_evento) && (!$fix_empresarial || ($this->action == 'add' || empty($row)))) {
            $result= $this->_fix_proceso_array($array['id_entity']);
            $id_tipo_evento= $result['id_tipo_evento'];
            $id_tipo_evento_code= $result['id_tipo_evento_code'];
        }

        if (empty($id_responsable))
            $id_responsable= !empty($row) ? $row['id_responsable'] : $this->id_responsable;

        $toshow= (($this->action == 'add' || ($this->action == 'update' && $this->toshow != $this->_toshow)) || empty($row)) ? $this->toshow : $row['toshow'];

        $cumplimiento= ($this->action == 'add' || empty($row)) ? _EN_ESPERA : $row['cumplimiento'];
        $aprobado= ($this->action == 'add' || empty($row)) ? null : $row['aprobado'];
        $id_responsable_aprb= ($this->action == 'add' || empty($row)) ? null : $row['id_responsable_aprb'];

        if ($this->className == "Ttarea") {
            $empresarial= $this->toshow > _EVENTO_INDIVIDUAL ? _FUNCIONAMIENTO_INTERNO : _EVENTO_INDIVIDUAL;
            $id_tipo_evento= null;
            $id_tipo_evento_code= null;
        }

        $result_index= $this->set_indice($empresarial, $id_tipo_evento);

        $result= array($id_proceso, $empresarial, $id_tipo_evento, $id_tipo_evento_code, $id_responsable,
                        $toshow, $cumplimiento, $aprobado, $id_responsable_aprb, $result_index[0], $result_index[1]);
        return $result;
    }

    private function fix_responsable($found, $id_responsable) {
        if ($id_responsable == $this->id_responsable)
            return null;
        if (array_key_exists($id_responsable, $this->accept_mail_user_list))
            return null;
        if (array_key_exists($id_responsable, $this->array_usuarios))
            return null;

        $this->obj_reg->SetIdUsuario($id_responsable);
        $this->obj_reg->update_reg('add', null, _USER_SYSTEM);

        $this->obj->SetIdUsuario($id_responsable);
        $this->obj->setUsuario('add');
    }

    private function if_have_change() {
        if ($this->action == 'update') {
            if ($this->id_responsable != $this->_id_responsable)
                return true;
            if ($this->toshow != $this->_toshow)
                return true;
            if ($this->empresarial != $this->_empresarial)
                return true;
            if ($this->id_tipo_evento != $this->_id_tipo_evento)
                return true;
        }
        return false;        
    }
       
    protected function set_proceso_from_array($found= false, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;
        $if_change= $this->if_have_change();
        
        reset($this->accept_process_list);
        $i= 0;
        foreach ($this->accept_process_list as $prs) {
            if ($found && !empty($prs['flag']) && !$if_change)
                continue;
            $array= array();
            $result= $this->fix_proceso_array($prs);
            $id_proceso= $result[0];

            $this->obj_reg->SetIdProceso($prs['id']);
            $this->obj_reg->set_id_proceso_code($prs['id_code']);
            
            $array['empresarial']= $this->className == "Ttarea" || $this->empresarial != $this->_empresarial ? $result[1] : null;

            $this->obj_reg->SetIfEmpresarial($result[1]);
            $this->obj_reg->SetIdTipo_evento($result[2]);
            $this->obj_reg->set_id_tipo_evento_code($result[3]);
            
            $this->obj_reg->toshow= $result[5];
            if ($this->toshow != $this->_toshow) {
                $this->obj_reg->toshow= $this->toshow;
                $array['toshow']= $this->toshow;
            }
            $id_responsable= $result[4];
            if ($this->id_responsable != $this->_id_responsable && $this->_id_responsable == $id_responsable) {
                $id_responsable= $this->id_responsable;
                $array['id_responsable']= $id_responsable;
            }    
            $this->obj_reg->SetIdResponsable($id_responsable);
            
            $this->obj_reg->SetCumplimiento($result[6]);

            $this->obj_reg->SetAprobado($result[7]);
            $this->obj_reg->SetIdResponsable_aprb($result[8]);

            $this->obj_reg->indice= $result[9];
            $this->obj_reg->indice_plus= $result[10];
            
            $sql.= $this->obj_reg->update_reg_proceso('add', null, null, null, $array, $multi_query);
            ++$i;
            
            if (!is_null($this->error))
                break;

            $this->fix_responsable($found, $result[4]);
        }

        reset($this->denied_process_list);
        $j= 0;
        foreach ($this->denied_process_list as $prs) {
            if (array_key_exists($prs['id'], (array)$this->accept_process_list))
                continue;

            $this->obj_reg->SetIdProceso($prs['id']);
            $this->obj_reg->set_id_proceso_code($prs['id_code']);
            $sql.= $this->obj_reg->update_reg_proceso('delete', _DELETE_PHY, null, null, null, $multi_query);
            ++$j;

            if (!is_null($this->error))
                break;
        }

        if ($i == 0) {
            $this->obj_reg->SetIdProceso($this->id_proceso);
            $this->obj_reg->set_id_proceso_code($this->id_proceso_code);
            $sql.= $this->obj_reg->update_reg_proceso('add', null, null, null, null, $multi_query);
        }

        if ($multi_query && !empty($sql))
            $this->do_multi_sql_show_error('set_proceso_from_array', $sql);

        return $this->error;
    }

    protected function set_array_usuarios_from_procesos() {
        $naccepted= 0;

        reset($this->denied_process_list);
        reset($this->accept_process_list);
        $obj_prs= new Tproceso($this->clink);

        foreach ($this->denied_process_list as $prs)
            $obj_prs->listar_usuarios_proceso($prs['id'], true);

        foreach ($obj_prs->array_usuarios as $user) {
            if ($user['nivel'] < _PLANIFICADOR)
                continue;

            if (!array_key_exists($user['id'], $this->accept_mail_user_list) && !array_key_exists($user['id'], $this->denied_mail_user_list)) {
                $array= array('id'=>$user['id'], 'nombre'=>$user['nombre'], 'email'=>$user['email'],'cargo'=>$user['cargo'],
                    'usuario'=>$user['usuario'], 'eliminado'=>$user['eliminado'], 'id_proceso'=>$user['id_proceso'],
                    'id_proceso_code'=>$user['id_proceso_code'], 'indirect'=>1, 'flag'=>0);

                $this->denied_user_list[$user['id']]= $array;
        }   }

        foreach ($this->accept_process_list as $prs)
            $obj_prs->listar_usuarios_proceso($prs['id'], true);

        foreach ($obj_prs->array_usuarios as $user) {
            if ($user['nivel'] < _PLANIFICADOR)
                continue;

            if (!array_key_exists($user['id'], $this->accept_mail_user_list) && !array_key_exists($user['id'], $this->denied_mail_user_list)) {
                $array= array('id'=>$user['id'], 'nombre'=>$user['nombre'], 'email'=>$user['email'],'cargo'=>$user['cargo'],
                    'usuario'=>$user['usuario'], 'eliminado'=>$user['eliminado'], 'id_proceso'=>$user['id_proceso'],
                    'id_proceso_code'=>$user['id_proceso_code'], 'indirect'=>1, 'flag'=>0);

                $this->accept_user_list[$user['id']]= $array;
                ++$naccepted;
        }   }

        if ($naccepted == 0 && !empty($this->id_responsable)) {
            $obj_user= new Tusuario($this->clink);
            $obj_user->Set($this->id_responsable);

            $array= array('id'=>$obj_user->GetIdProceso(), 'id_code'=>$obj_user->get_id_proceso_code(), 'flag'=>0);
            $this->accept_user_list[$obj_user->GetIdProceso()]= $array;
        }

        $this->accept_mail_user_list= array_merge_overwrite((array)$this->accept_mail_user_list, (array)$this->accept_user_list);
        $this->denied_mail_user_list= array_merge_overwrite((array)$this->denied_mail_user_list, (array)$this->denied_user_list);
    }

    protected function set_usuarios_array($action= null) {
        $naccepted= 0;
        $fecha_fin= $this->obj->GetFechaFinPlan();
        $user_ref_date= !is_null($this->user_date_ref) ? $this->user_date_ref : $fecha_fin;

        $obj_user= new Tusuario($this->clink);
        $obj_user->SetIdEntity($_SESSION['id_entity']);
        $obj_user->set_use_copy_tusuarios(false);
        $obj_user->set_user_date_ref($user_ref_date);
        $result= $obj_user->listar(null);

        while ($row= $this->clink->fetch_array($result)) {
            $id= $_POST['multiselect-users_user'.$row['_id']];
            $_id= $_POST['multiselect-users_init_user'.$row['_id']];

            $array= array('id'=>$row['_id'], 'nombre'=>$row['nombre'], 'email'=>$row['email'],'cargo'=>$row['cargo'],
                    'usuario'=>$row['usuario'], 'eliminado'=>$row['eliminado'], 'id_proceso'=>$row['id_proceso'],
                    'id_proceso_code'=>$row['id_proceso_code'], 'indirect'=>NULL, 'flag'=>0);

            $array['flag']= empty($_id) ? 0 : 1;

            if (!empty($id)) {
                ++$naccepted;
                if (!array_key_exists($row['_id'], (array)$this->accept_user_list))
                        $this->accept_user_list[$row['_id']]= $array;
            } else {
                if (!empty($_id)) {
                    if (!array_key_exists($row['_id'], (array)$this->denied_user_list))
                        if ($action == 'MODIFICADO')
                            $this->denied_user_list[$row['_id']]= $array;
        } } }

         if ($naccepted == 0 || !empty($this->id_documento))  {
             $obj_user= new Tusuario($this->clink);
             $obj_user->Set($this->id_responsable);

            $this->id_proceso_user_responsable= $obj_user->GetIdProceso();
            $this->id_proceso_code_user_responsable= $obj_user->get_id_proceso_code();

             $array= array('id'=>$this->id_responsable, 'nombre'=>$obj_user->GetNombre(), 'email'=>$obj_user->GetMail_address(),
                 'cargo'=>$obj_user->GetCargo(),'usuario'=>$obj_user->GetUsuario(), 'eliminado'=>$obj_user->GetEliminado(),
                 'id_proceso'=>$obj_user->GetIdProceso(), 'id_proceso_code'=>$obj_user->get_id_proceso_code(), 'indirect'=>NULL, 'flag'=>0);

             $this->accept_user_list[$this->id_responsable]= $array;
         }

        // agregar secretaria
        if ($this->id_tipo_reunion  && !empty($this->id_secretary)) {
            if (isset($obj_user)) unset($obj_user);
            $obj_user= new Tusuario($this->clink);
            $obj_user->Set($this->id_secretary);

            $array= array('id'=>$this->id_secretary, 'nombre'=>$obj_user->GetNombre(), 'email'=>$obj_user->GetMail_address(),
                'cargo'=>$obj_user->GetCargo(),'usuario'=>$obj_user->GetUsuario(), 'eliminado'=>$obj_user->GetEliminado(),
                'id_proceso'=>$obj_user->GetIdProceso(), 'id_proceso_code'=>$obj_user->get_id_proceso_code(), 'indirect'=>NULL, 'flag'=>0);

            $this->accept_user_list[$this->id_secretary]= $array;
        }

        $this->accept_mail_user_list= array_merge_overwrite((array)$this->accept_mail_user_list, (array)$this->accept_user_list);
        $this->denied_mail_user_list= array_merge_overwrite((array)$this->denied_mail_user_list, (array)$this->denied_user_list);
        unset($obj_user);

        if (empty($this->id_proceso_user_responsable) || empty($this->tipo_proceso_user_responsable)) {
            if (empty($this->id_proceso_user_responsable)) {
                $obj_user= new Tusuario($this->clink);
                $obj_user->Set($this->id_responsable);
                $this->id_proceso_user_responsable= $obj_user->GetIdProceso();
                $this->id_proceso_code_user_responsable= $obj_user->get_id_proceso_code();
                unset($obj_user);
            }

            $obj_prs= new Tproceso($this->clink);
            $obj_prs->Set($this->id_proceso_user_responsable);
            $this->tipo_proceso_user_responsable= $obj_prs->GetTipo();
            unset($obj_prs);
        }

        unset($obj_user);
    }

    protected function set_usuarios_from_array($action, $found= false, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

        reset($this->accept_user_list);
        reset($this->denied_user_list);

        $this->_fecha_inicio= $this->obj->GetFechaInicioPlan();

        foreach ($this->accept_user_list as $array) {
           if (!is_null($array['eliminado']) && (strtotime($this->_fecha_inicio) > strtotime($array['eliminado'])))
               continue;

    	   $this->obj->SetIdUsuario($array['id']);
           if (!$found || ($found && empty($array['flag'])) || ($found && is_null($found[1])))
               $sql.= $this->obj->setUsuario('add', $array['indirect'], null, $multi_query);
        }

        if ($action == 'MODIFICADO') {
            foreach ($this->denied_user_list as $key => $array) {
                if (!is_null($array['eliminado']) && (strtotime($this->_fecha_inicio) > strtotime($array['eliminado'])))
                    continue;
                $this->obj->setIdUsuario($array['id']);

                $user_delete= true;
                if ($this->id_tipo_reunion) {
                    $this->obj_matter->SetIdEvento($this->_id_evento);
                    $attached= $this->obj_matter->get_if_attached_usuario($array['id']);
                    $user_delete= $array['flag'] ? ($attached ? 0 : 1) : 0;
                    $this->denied_user_list[$key]['flag']= $user_delete;
                }

                if ($user_delete) {
                    $sql.= $this->obj->setUsuario('delete', null, null, $multi_query);
        }   }   }

        $this->error= !$multi_query ? (!empty($sql) ? $sql : null) : $this->obj->error;

        if ($multi_query && !empty($sql))
            $this->do_multi_sql_show_error('set_usuarios_from_array', $sql);
    }

    protected function set_grupos_array($action= null) {
    	$error= null;

        $obj_grp= new Tgrupo($this->clink);
        $obj_grp->SetIdEntity($_SESSION['id_entity']);
        $obj_grp->set_user_date_ref($this->fecha_fin);
    	$result= $obj_grp->listar();

    	while ($row= $this->clink->fetch_array($result)) {
            $id= $_POST['multiselect-users_grp'.$row['_id']];
            $_id= $_POST['multiselect-users_init_grp'.$row['_id']];

            $this->obj->cleanListaUser();
            $this->obj->SetIdGrupo($row['_id']);

            if (!empty($id) || !empty($_id)) {
                $this->obj->push2ListaUserGroup($row['_id'], true);
                $user_array= $this->obj->get_list_user();
            } else {
                continue;
            }

            $array= array('id'=>$row['_id'], 'flag'=>0);
            $array['flag']= empty($_id) ? 0 : 1;

            if (!empty($_id)) {
                while (list($index, $cell)= each($user_array))
                    $user_array[$index]['flag']= $this->action == 'update' && strtotime($user_array[$index]['cronos']) >= strtotime($this->kronos) ? 0 : 1;
                reset($user_array);
            }

            if (!empty($id)) {
                if (!array_key_exists($row['_id'], $this->accept_group_list)) {
                        $this->accept_group_list[$row['id']]= $array;
                        $this->accept_mail_user_list= array_merge_overwrite((array)$this->accept_mail_user_list, (array)$user_array);
                }
            } else {
                if (!empty($_id)) {
                    if (!array_key_exists($row['_id'], $this->denied_group_list)) {
                        if ($action == 'MODIFICADO') {
                            $this->denied_group_list[$row['_id']]= $array;
                            $this->denied_mail_user_list= array_merge_overwrite((array)$this->denied_mail_user_list, (array)$user_array);
	}   }   }   }   }

        $this->accept_mail_user_list= array_unique((array)$this->accept_mail_user_list, SORT_REGULAR);
        $this->denied_mail_user_list= array_unique((array)$this->denied_mail_user_list, SORT_REGULAR);

        unset($obj_user);
        $this->error= $error;
    }

    protected function set_grupos_from_array($action, $found= false, $multi_query= false) {
        $multi_query= !is_null($multi_query) ? $multi_query : false;
        $sql= null;

    	reset($this->accept_group_list);
        reset($this->denied_group_list);

        foreach ($this->accept_group_list as $array) {
    	   $this->obj->SetIdGrupo($array['id']);
           if (!$found || ($found && empty($array['flag'])) || ($found && is_null($found[1])))
               $sql.= $this->obj->setGrupo('add', null, $multi_query);
        }

        if ($action == 'MODIFICADO') {
            foreach ($this->denied_group_list as $array) {
                $this->obj->SetIdGrupo($array['id']);
                $sql.= $this->obj->setGrupo('delete', null, $multi_query);
                $cant= $this->obj->GetCantidad();

                if (!$multi_query && (!is_null($sql) || empty($cant)))
                    continue;

                if ($this->className == 'Tauditoria') {
                    $this->obj_code->reg_delete('tusuario_eventos', 'id_grupo', $array['id'],'id_auditoria_code', $this->_id_code);
                } else {
                    $this->obj_code->reg_delete('tusuario_eventos', 'id_grupo', $array['id'],'id_evento_code', $this->_id_code);
                }
        }   }

        $this->error= !$multi_query ? (!empty($sql) ? $sql : null) : $this->obj->error;

        if ($multi_query && !empty($sql))
            $this->do_multi_sql_show_error('set_grupos_from_array', $sql);
    }

    protected function GetNombre_auditoria() {
        global $Ttipo_nota_origen_array;

        $obj_tipo= new Ttipo_auditoria($this->clink);
        $obj_tipo->SetIdTipo_auditoria($this->id_tipo_auditoria);
        $obj_tipo->Set();

        $this->nombre= $obj_tipo->GetNombre();
        $this->nombre.= ", ".$Ttipo_nota_origen_array[$this->origen];
        return $this->nombre;
    }
}