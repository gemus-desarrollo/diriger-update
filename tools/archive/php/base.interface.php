<?php

/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

session_start();
require_once "../../../php/setup.ini.php";
require_once "../../../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../../../php/config.inc.php";
require_once "../../../php/class/base.class.php";
require_once "../../../php/class/connect.class.php";
require_once "../../../php/class/proceso_item.class.php";
require_once "../../../php/class/document.class.php";

require_once "../../../php/class/code.class.php";

require_once "../../../php/class/tmp_tables_planning.class.php";
require_once "../../../php/class/register_planning.class.php";
require_once "../../../php/class/evento.class.php";

require_once "class/organismo.class.php";
require_once "class/persona.class.php";
require_once "class/ref_archivo.class.php";
require_once "class/archivo.class.php";
require_once "class/serie.class.php";

?>

<?php
class Tbase_interface extends Tbase {
    protected $obj;
    protected $obj_ref;
    protected $obj_event;
    
    protected $if_anonymous;
    protected $if_output;
    protected $if_immediate;
    
    protected $id_archivo;
    protected $id_archivo_code;
    
    protected $id_documento;
    protected $id_documento_code;
    
    protected $id_persona;
    protected $id_persona_code;

    protected $indicaciones;
    protected $fecha_entrega;
    protected $fecha_inicio_plan;
    protected $fecha_fin_plan;
    protected $id_responsable;

    protected $menu;
    protected $to_print;
    
    public $toshow;
    protected $sendmail;
    protected $obj_mail;
    
    protected $accept_user_list, $denied_user_list;
    protected $accept_group_list, $denied_group_list;
    protected $accept_mail_user_list, $denied_mail_user_list;
    
    protected $_id_responsable;
    protected $accept_persona_list;
    protected $denied_persona_list;
    protected $n_user_persona ;
    
    protected $date_init;
    protected $date_end;
    protected $id_organismo;
    protected $id_proceso;
    
    protected $keywords;
    protected $numero;
    protected $numero_keywords;
    
    protected $id_responsable_init;
    
    
    public function __construct($clink) {
        Tbase::__construct($clink);
        $this->clink= $clink;
        
        $this->menu= !empty($_POST['menu']) ? $_POST['menu'] : $_GET['menu'];
        
        $this->action= !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->id = !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->year= !empty($_POST['year']) ? $_POST['year'] : date('Y');
        
        $this->id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : $_POST['id_evento'];
        $this->id_evento_code= !empty($_GET['id_evento_code']) ? $_GET['id_evento_code'] : $_POST['id_evento_code'];
        
        $this->if_anonymous= !empty($_POST['if_anonymous']) ? $_POST['if_anonymous'] : null;
        $this->id_persona= !empty($_POST['personas']) ? $_POST['personas'] : null;
        $this->if_output= !empty($_POST['if_output']) ? $_POST['if_output'] : 0;
        $this->if_immediate= !empty($_POST['if_immediate']) ? $_POST['if_immediate'] : 0;
        $this->to_print= !is_null($_GET['to_print']) ? $_GET['to_print'] : false;
        $this->sendmail= !empty($_POST['sendmail']) ? $_POST['sendmail'] : $_GET['sendmail'];
        $this->toshow= !empty($_POST['toshow']) ? $_POST['toshow'] : $_GET['toshow'];
        
        $this->date_init= !empty($_GET['date_init']) ? urldecode($_GET['date_init']) : date('d/m/Y');
        $this->date_end= !empty($_GET['date_end']) ? urldecode($_GET['date_end']) : date('d/m/Y h:i A');

        $this->id_organismo= !empty($_GET['id_organismo']) ? $_GET['id_organismo'] : null; 
        $this->id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : null; 
        $this->keywords= !empty($_GET['keywords']) ? urldecode($_GET['keywords']) : null; 
        
        $this->cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null;
        
        $this->id_responsable_init= null;
        
        $this->id_proceso= !empty($_POST['id_proceso']) ? $_POST['id_proceso'] : $_GET['id_proceso']; 
        if (empty($this->id_proceso))
            $this->id_proceso= $_SESSION['id_entity'];
        $this->id_proceso_code= !empty($this->id_proceso) ? get_code_from_table('tprocesos', $this->id_proceso) : null;
        
        $this->obj_code= new Tcode($this->clink);
        $this->obj = new Tarchivo($this->clink);
        $this->obj_ref = new Tref_archivo($this->clink); 
    }
 
    
    protected function sendMail() {
        $this->obj_mail = new Tmail();

        reset($this->accept_persona_list);
        reset($this->accept_mail_user_list);
        
        if ($_FILES['file_doc-upload']["size"] > 0) 
            $this->obj_mail->AddAttachment(_UPLOAD_DIRIGER_DIR.$this->url, $this->filename);
        
        if ($this->if_output) {
            foreach ($this->accept_persona_list as $row) {
                if (empty($row['email'])) 
                    continue;
                if (!empty($row['flag'])) 
                    continue;
                
                $this->obj_mail->responsable = $row['nombre'];
                $this->obj_mail->cargo = !empty($row['cargo']) ? textparse($row['cargo']) : null;
                $this->obj_mail->Subject= "Oficina de Archivo. Sistema Diriger";
                $this->obj_mail->fecha_inicio = $this->fecha_inicio_plan;
                $this->obj_mail->fecha_fin = $this->fecha_fin_plan;

                $this->obj_mail->periodicidad= 0;
                $this->obj_mail->lugar= null;
                $this->obj_mail->evento= "";
                if ($this->indicaciones)
                    $this->obj_mail.= "<p>$this->indicaciones</p>";
                if (!empty($this->descripcion)) 
                    $this->obj_mail.= "<p>$this->descripcion</p>";  
                
                $this->obj_mail->body_event("Indicación");

                $nombre= $row['nombre'];
                $nombre.= !empty($row['cargo']) ? ", ".textparse($row['cargo']) : null;
                $this->obj_mail->AddAddress($row['email'], $nombre);

                $this->obj_mail->send();
                $this->obj_mail->ClearAddresses();                
            }   
        } else {
            foreach ($this->accept_mail_user_list as $row) {
                if (empty($row['email'])) 
                    continue;                
                $this->obj_mail->responsable = $row['nombre'];
                $this->obj_mail->cargo = !empty($row['cargo']) ? textparse($row['cargo']) : null;

                $this->obj_mail->fecha_inicio = $this->fecha_inicio_plan;
                $this->obj_mail->fecha_fin = $this->fecha_fin_plan;

                $this->obj_mail->periodicidad = 0;
                $this->obj_mail->lugar = null;
                $this->obj_mail->evento = $this->descripcion;  
                
                $this->obj_mail->body_event("Indicación");
                
                $nombre= $row['nombre'];
                $nombre.= !empty($row['cargo']) ? ", {$row['cargo']}" : "";
                $this->obj_mail->AddAddress($row['email'], $nombre);
                
                $this->obj_mail->send();
                $this->obj_mail->ClearAddresses();                  
            }
        }    
    }    
    
    protected function _setPersona($i, $action) {
        $this->obj_ref->SetIdArchivo($this->id_archivo);
        $this->obj_ref->set_id_archivo_code($this->id_archivo_code);
        $this->obj_ref->id_ref_archivo= null;
        $this->obj_ref->SetIdPersona(null);
           
        $id= $_POST['tab_persona_init_'.$i];
        $this->obj_ref->SetNoIdentidad(trim($_POST['noIdentidad_'.$i]));
        
        $nombre= trim($_POST['nombre_'.$i]);
        $this->obj_ref->SetNombre($nombre, false);
        
        $cargo= trim($_POST['cargo_'.$i]);
        $this->obj_ref->SetCargo($cargo);
        $this->obj_ref->SetIdOrganismo(trim($_POST['organismo_'.$i]));

        $this->obj_ref->SetProvincia($_POST['provincia_'.$i]);
        $this->obj_ref->SetMunicipio($_POST['municipio_'.$i]);
        $this->obj_ref->SetDireccion(trim($_POST['direccion_'.$i]));
        $this->obj_ref->SetLugar(trim($_POST['lugar_'.$i]));
        
        $this->obj_ref->SetTelefono(trim($_POST['telefono_'.$i]));
        $this->obj_ref->SetMovil(trim($_POST['movil_'.$i])); 
        $email= trim($_POST['email_'.$i]);
        $this->obj_ref->SetMail_address($email); 
        
        $this->obj_ref->SetIdResponsable($this->_id_responsable);
        
        if ($action == 'add') {
            $this->obj_ref->SetIdProceso($_SESSION['id_entity']);
            $this->obj_ref->set_id_proceso_code($_SESSION['id_entity_code']);
            
            $this->obj_ref->add_person();
            $this->id_persona= $this->obj_ref->GetIdPersona();
            $this->id_persona_code= $this->obj_ref->get_id_persona_code();            
        }
        
        if ($action == 'update') {
            $this->obj_ref->SetIdPersona($this->id_persona);
            $this->obj_ref->update_person();
        }
        
        if ($action == 'add' || $action == 'update' || $action == 'list') {
            $array = array('id' => $this->_id_responsable, 'nombre' => $nombre, 'email' => $email, 'cargo' => $cargo,
                     'flag' => 0, 'persona'=>true);

            $this->accept_persona_list[$i]= $array;
        }

        if ($action == 'delete' && !empty($id)) {
           $array = array('id' => $this->_id_responsable, 'nombre' => $nombre, 'email' => $email, 'cargo' => $cargo,
                    'flag' => 0, 'persona'=>true);
           $this->denied_persona_list[$i]= $array; 
           
           $this->obj_ref->id_ref_archivo= null;
           $this->obj_ref->SetIdArchivo($this->id_archivo);
           $this->obj_ref->set_id_archivo_code($this->id_archivo_code);
           $this->obj_ref->SetIdPersona($id);
           $this->obj_ref->eliminar();           
        }
    }
    
    protected function setPersonas() {
        $this->n_user_persona= 0;
        $cant= $_POST['cant_personas'];
        
        $this->obj_ref->SetIdArchivo($this->id_archivo);
        $this->obj_ref->set_id_archivo_code($this->id_archivo_code);
      
        for ($i= 1; $i <= $cant; ++$i) {
            $status= $_POST['tab_persona_'.$i];
            $if_anonymous= $_POST['if_anonymous_'.$i];
            $id= $_POST['tab_persona_init_'.$i];
            
            if (empty($id)) {
                if ($if_anonymous == 1) {
                    $this->_id_responsable= null;
                    $this->id_persona= $_POST['id_persona_'.$i];
                    $this->id_persona_code= get_code_from_table('tpersonas', $this->id_persona);
                    
                    $this->_setPersona($i, 'list');
                }

                if ($if_anonymous == 2) {
                    ++$this->n_user_persona;
                    $this->_id_responsable= $_POST['id_responsable_'.$i];
                    
                    $this->obj_ref->update_from_usuario($this->_id_responsable);
                    $this->id_persona= $this->obj_ref->GetIdPersona();
                    $this->id_persona_code= $this->obj_ref->get_id_persona_code();
                    if (empty($this->id_persona)) 
                        continue;
                    
                    $this->_setPersona($i, 'update');
                }

                if ($if_anonymous == 3) {
                    $this->_id_responsable= null;
                    $this->_setPersona($i, 'add');
                }  
                
                $this->obj_ref->SetIfAnonymous($if_anonymous);
                $this->obj_ref->SetIdPersona($this->id_persona);
                $this->obj_ref->set_id_persona_code($this->id_persona_code);

                $this->obj_ref->add();  
                
                $this->accept_persona_list[$i]['flag']= 0;
                
            } else {
                $this->_id_responsable= $_POST['id_responsable_'.$i];
                
                if (!empty($status)) {
                    $this->id_persona= $_POST['tab_persona_init_'.$i];
                    $this->_setPersona($i, 'update');
                    
                    $this->accept_persona_list[$i]['flag']= 1;
                    
                } else {
                    $this->_setPersona($i, 'delete');
                }
    }   }   }
    
    protected function setPersonas_event() {
        $error = NULL;
        reset($this->accept_persona_list);
        reset($this->denied_persona_list);
        
        foreach ($this->accept_persona_list as $row) {
            if (empty($row['id'])) 
                continue;
            
            $this->obj_event->SetIdUsuario($row['id']);
            $error = $this->obj_event->setUsuario('add');
            if (!is_null($error)) 
                break;
            
            $this->obj_event->set_user_check(true);
            $this->obj_event->SetCumplimiento(_NO_INICIADO);
            $this->obj_event->SetIdUsuario($row['id']);

            $this->obj_event->update_reg('add', null, _USER_SYSTEM);              
        }

        if (is_null(error)) {
            foreach ($this->denied_persona_list as $row) {
                if (empty($row['id'])) 
                    continue;
                $this->obj_event->SetIdUsuario($row['id']);
                $error = $this->obj_event->setUsuario('delete');
                if (!empty($error)) 
                    break;
                
                $this->obj_event->set_user_check(true);
                $this->obj_event->SetCumplimiento(_CANCELADO);
                $this->obj_event->SetIdUsuario($row['id']);

                $this->obj_event->update_reg('delete', null, _USER_SYSTEM);                      
            }
        }

        $this->error= $error;
        return $error;        
    }
    
    protected function setUsuarios() {
        $this->obj_ref->SetIdPersona(null);
        $this->obj_ref->set_id_persona_code(null);
        
        $error = NULL;
        $user_ref_date = $this->fecha_fin_plan;

        $obj = new Tusuario($this->clink);
        $obj->set_user_date_ref($this->fecha_fin_plan);
        $result = $obj->listar(null, null, _LOCAL);

        while ($row = $this->clink->fetch_array($result)) {
            $id = $_POST['multiselect-users_user' . $row['_id']];
            $_id = $_POST['multiselect-users_init_user' . $row['_id']];

            $array = array('id' => $row['_id'], 'nombre' => $row['nombre'], 'email' => $row['email'], 'cargo' => $row['cargo'],
                     'flag' => 0, 'persona'=>false);
            $array['flag'] = empty($_id) ? 0 : 1;

            $this->obj_ref->SetIdUsuario($row['_id']);

            if (!empty($id)) {
                if (!array_key_exists($row['_id'], (array) $this->accept_mail_user_list)) {
                    if (empty($array['flag']))
                        $error = $this->obj_ref->setUsuario('add');
                    if (is_null($error))
                        $this->accept_user_list[$row['_id']] = $array;
                }
            }
            else {
                if (!empty($_id)) {
                    if (!array_key_exists($row['_id'], (array) $this->denied_mail_user_list)) {
                        $error = $this->obj_ref->setUsuario('delete');
                        $nums = $this->obj_ref->GetCantidad();

                        if (!empty($nums) && is_null($error)) {
                            $this->denied_user_list[$row['_id']] = $array;
            }   }  }  }

            if (!is_null($error)) 
                break;
        }

        if ((!empty($this->id_responsable) && $this->id_responsable != _USER_SYSTEM) && array_key_exists($this->id_responsable, $this->accept_user_list) == false) {
            $row = $obj->GetEmail($this->id_responsable);
            $this->obj_ref->SetIdUsuario($this->id_responsable);
            $error = $this->obj_ref->setUsuario();

            $array = array('id' => $row['id'], 'nombre' => $row['nombre'], 'email' => $row['email'], 'cargo' => $row['cargo'],
                        'flag' => 0, 'persona'=>false);

            if (is_null($error))
                $this->accept_user_list[$row['id']] = $array;
        }
        
        if ((!empty($this->id_responsable_init) && $this->id_responsable_init != $this->id_responsable) && array_key_exists($this->id_responsable_init, $this->accept_user_list) == false) {
            $obj_user= new Tusuario($this->clink);
            $obj_user->Set($this->id_responsable_init);
            
            $array = array('id' => $this->id_responsable, 'nombre' => $obj_user->GetNombre(), 'email' => $obj_user->GetMail_address(), 'cargo' => $obj_user->GetCargo(),
                     'flag' => 0, 'persona'=>false);            
            $this->denied_user_list[$this->id_responsable_init]= $array;
        }

        $this->accept_mail_user_list = array_merge_overwrite((array) $this->accept_mail_user_list, (array) $this->accept_user_list);
        $this->denied_mail_user_list = array_merge_overwrite((array) $this->denied_mail_user_list, (array) $this->denied_user_list);

        unset($obj);
        return $error;
    }

    protected function setGrupos() {
        $error= NULL;

        $obj= new Tgrupo($this->clink);
        $result= $obj->listar();	

        while ($row= $this->clink->fetch_array($result)) {
            $id= $_POST['multiselect-users_grp'.$row['id']];
            $_id= $_POST['multiselect-users_init_grp'.$row['id']];

            $this->obj_ref->cleanListaUser();
            $this->obj_ref->SetIdGrupo($row['_id']);

            if (!empty($id) || !empty($_id)) {
                $this->obj_ref->push2ListaUserGroup($row['_id'], true);
                $user_array= $this->obj_ref->get_list_user();
            }

            $array= array('id'=>$row['_id'], 'flag'=>0, 'persona'=>false);
            $array['flag']= empty($_id) ? 0 : 1;

            if (!empty($_id)) {
                while (list($index, $cell)= each($user_array)) 
                    $user_array[$index]['flag']= 1;
                reset($user_array);
            }

            if (!empty($id)) {
                if (!array_key_exists($row['id'], $this->accept_group_list)) {
                    if (empty($array['flag'])) 
                        $error= $this->obj_ref->setGrupo('add');

                    if (is_null($error)) {
                        $this->accept_group_list[$row['id']]= $array;
                        $this->accept_mail_user_list= array_merge_overwrite((array)$this->accept_mail_user_list, (array)$user_array);
                    }
            }   }

            else {
                if (!empty($_id)) {
                    $this->obj_ref->SetIdGrupo($row['id']);
                     $this->obj_ref->setGrupo('delete');
                     $nums= $this->obj_ref->GetCantidad();

                     if (!empty($nums) && is_null($error)) {
                         $this->denied_group_list[$row['_id']]= $array;
                         $this->denied_mail_user_list= array_merge_overwrite((array)$this->denied_mail_user_list, (array)$user_array);
            }    }   }
			
            if (!is_null($error)) 
                break;
        }

        $this->accept_mail_user_list= array_unique((array)$this->accept_mail_user_list, SORT_REGULAR);
        $this->denied_mail_user_list= array_unique((array)$this->denied_mail_user_list, SORT_REGULAR);

        unset($obj);
        return $error;	        
    }
      
    protected function setUsuarios_event() {
        $error = NULL;
        reset($this->accept_user_list);
        reset($this->denied_user_list);
        
        foreach ($this->accept_user_list as $row) {
            $this->obj_event->SetIdUsuario($row['id']);
            $error = $this->obj_event->setUsuario('add');
            if (!is_null($error)) 
                break;
        }

        if (is_null(error)) {
            foreach ($this->denied_user_list as $row) {
                $this->obj_event->SetIdUsuario($row['id']);
                $error = $this->obj_event->setUsuario('delete');
                if (!empty($error)) 
                    break;
            }
        }

        $this->error= $error;
        return $error;
    }

    protected function setGrupos_event() {
        $error = NULL;
        reset($this->accept_group_list);
        reset($this->denied_group_list);
        
        foreach ($this->accept_group_list as $row) {
            $this->obj_event->SetIdGrupo($row['id']);
            $error = $this->obj_event->setGrupo('add');
            if (!empty($error)) 
                break;   
        }

        if (is_null(error)) {
            foreach ($this->denied_group_list as $row) {
                $this->obj_event->SetIdGrupo($row['id']);
                $error = $this->obj_event->setGrupo('delete'); 
                if (!empty($error)) 
                    break;
            }
        }

        $this->error= $error;
        return $error;        
    }
    
    protected function setReg_event() {
        reset($this->accept_mail_user_list);
        reset($this->denied_mail_user_list);
        
        foreach ($this->accept_mail_user_list as $row) {
            // $this->obj_event->set_user_check(true);
            $this->obj_event->SetCumplimiento(_NO_INICIADO);
            $this->obj_event->SetIdUsuario($row['id']);
            $this->obj_event->update_reg('add', null, $this->id_responsable);             
        }
        
        foreach ($this->denied_mail_user_list as $row) {
            $this->obj_event->SetCumplimiento(_CANCELADO);
            $this->obj_event->SetIdUsuario($row['id']);
            $this->obj_event->update_reg('delete', null, $this->id_responsable);             
        }
        
        if (!empty($this->id_responsable) && !array_key_search($this->accept_mail_user_list, $this->id_responsable)) {
            $this->obj_event->set_user_check(true);
            $this->obj->SetIdUsuario($this->id_responsable);
            $this->_setReg(_NO_INICIADO);                
        }           
    }
    
    protected function setUsuarios_doc() {
        reset($this->accept_user_list);
        reset($this->denied_user_list);

        foreach ($this->accept_user_list as $array) {
            $this->obj->SetIdUsuario($array['id']);
            $error= $this->obj->setUsuario('add');
            if (!empty($error)) 
                break;
        }

        if (!empty($error)) {
            foreach ($this->denied_user_list as $array) {
                $this->obj->setIdUsuario($array['id']);
                $error= $this->obj->setUsuario('delete');
                if (!empty($error)) 
                    break;
            }
        }

        $this->error= $error;
    }

    protected function setGrupos_doc() {
        reset($this->accept_group_list);
        reset($this->denied_group_list);

        foreach ($this->accept_group_list as $array) {
            $this->obj->SetIdGrupo($array['id']);
            $error= $this->obj->setGrupo('add');
            if (!empty($error)) 
                break;
        }

        if (!empty($error)) {
            foreach ($this->denied_group_list as $array) {
                $this->obj->SetIdGrupo($array['id']);
                $error= $this->obj->setGrupo('delete');
                if (!empty($error)) 
                    break;
            }
        }

        $this->error= $error;
    }    
    
    protected function _setReg($value) {
        $this->obj->SetFecha($this->fecha_inicio_plan);
        $this->obj->SetCumplimiento($value);
        $this->obj->SetObservacion(null);
        $this->obj->addReg();     
    }
    
    protected function setReg() {
        if ($this->if_output) {
            reset($this->accept_persona_list);
            reset($this->denied_persona_list);   
            
            foreach ($this->accept_persona_list as $array) {
                if ($array['flag']) 
                    continue;
                if (empty($array['id'])) 
                    continue;
                
                $this->obj->SetIdUsuario($array['id']);
                $this->_setReg(_NO_INICIADO);
            }
            
            if (!empty($this->id_responsable) && !array_key_search($this->accept_persona_list, $this->id_responsable)) {
                $this->obj->SetIdUsuario($this->id_responsable);
                $this->_setReg(_NO_INICIADO);                
            }
            
            foreach ($this->denied_persona_list as $array) {
                if (empty($array['id'])) 
                    continue;
                
                $this->obj->SetIdUsuario($array['id']);
                $this->_setReg(_CANCELADO);
            }            
        } else {
            reset($this->accept_mail_user_list);
            reset($this->denied_mail_user_list);  
            
            foreach ($this->accept_mail_user_list as $array) {
                if ($array['flag']) 
                    continue;
                
                $this->obj->SetIdUsuario($array['id']);
                $this->_setReg(_NO_INICIADO);
            } 

             foreach ($this->denied_mail_user_list as $array) {
                $this->obj->SetIdUsuario($array['id']);
                $this->_setReg(_CANCELADO);
            }           
        }
        
        if ((!empty($this->id_responsable) && $this->id_responsable != _USER_SYSTEM) && !array_key_search($this->accept_mail_user_list, $this->id_responsable)) {
            $this->obj->set_user_check(true);
            $this->obj->SetIdUsuario($this->id_responsable);
            $this->_setReg(_NO_INICIADO);                
        }            
    }
}
?>	
       