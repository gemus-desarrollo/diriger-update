<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of init
 *
 * @author mustelier
 */
class Tinit {
    public function __construct($clink = null) {
        $this->clink= $clink;
    }

    public function SetLocalProceso() {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
        $obj_prs->Set();  

        $msg= "Al parecer existe diferencias entre el fichero config.ini y los datos del proceso en la base de datos. ";
        $msg.= "Por favor, consulte al personal de GEMUS.";
        $situs= $_SESSION['location'];
        $_SESSION['location']= $obj_prs->GetCodigo();
        if ((!empty($situs) && !empty($_SESSION['location'])) && $situs != $_SESSION['location'])
            die($msg);

        $_SESSION['local_proceso_id_code']= $obj_prs->get_id_code();
        $_SESSION['local_proceso_conectado']= $obj_prs->GetConectado();
        $_SESSION['local_proceso_nombre']= $obj_prs->GetNombre();
        $_SESSION['local_proceso_codigo']= $obj_prs->GetCodigo();
        $_SESSION['local_proceso_lugar']= $obj_prs->GetLugar();
        $_SESSION['local_proceso_tipo']= $obj_prs->GetTipo();
        $_SESSION['local_proceso_id_responsable']= $obj_prs->GetIdResponsable();
    }
    
    public function SetCurrentProceso($id_entity) {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->SetIdProceso($id_entity);
        $obj_prs->Set();

        $_SESSION['superior_proceso_id']= $obj_prs->GetIdProceso_sup();
        $_SESSION['superior_proceso_id_code']= $obj_prs->get_id_proceso_sup_code();

        $_SESSION['current_id_proceso']= $id_entity;
        $_SESSION['current_id_proceso_code']= $obj_prs->get_id_proceso_code();

        $obj_time= new TTime();
        $_SESSION['current_year']= $obj_time->GetYear();
        $_SESSION['current_month']= $obj_time->GetMonth();
        $_SESSION['current_day']= $obj_time->GetDay();
    }

    public function SetUsuario(&$obj, &$obj_prs) {
        global $Ttipo_proceso_array;

        $obj->SetConectado();

        $_SESSION['usuario']= $obj->GetUsuario();
        $_SESSION['nombre']= $obj->GetNombre();
        $_SESSION['cargo']= $obj->GetCargo();
        $_SESSION['email']= $obj->GetMail_address();
        $_SESSION['nivel']= $obj->GetRole();

        $_SESSION['user_ldap']= !empty($obj->get_user_ldap()) ? $obj->get_user_ldap() : null;

        $_SESSION['usuario_proceso_id']= $obj->GetIdProceso();
        $_SESSION['usuario_proceso_id_code']= $obj->get_id_proceso_code();

        $_SESSION['acc_planwork']= $obj->get_acc_planwork();
        $_SESSION['acc_planrisk']= $obj->get_acc_planrisk();
        $_SESSION['acc_planaudit']= $obj->get_acc_planaudit();
        $_SESSION['acc_planheal']= $obj->get_acc_planheal();
        $_SESSION['acc_planproject']= $obj->get_acc_planproject();
        $_SESSION['freeassign']= $obj->get_freeassign();

        $_SESSION['acc_archive']= $obj->get_acc_archive();
        $_SESSION['nivel_archive2']= $obj->get_nivel_archive2();
        $_SESSION['nivel_archive3']= $obj->get_nivel_archive3();
        $_SESSION['nivel_archive4']= $obj->get_nivel_archive4();

        $_SESSION['usuario_data']= $_SESSION['nombre'];
        if (!is_null($_SESSION['cargo']))
            $_SESSION['usuario_data'].= ', '.$_SESSION['cargo'];
        if (!is_null($_SESSION['email']))
            $_SESSION['usuario_data'].= '<br /><strong>e-correo: </strong>'.$_SESSION['email'];
        $_SESSION['usuario_data'].= '<br /><strong>perteneciente a: </strong>'.$obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
    }

    public function SetEscenario(&$obj_esc) {
        $obj_esc->SetEscenario($_SESSION['current_year'], $_SESSION['local_proceso_id'], _LOCAL);
        $id_escenario= $obj_esc->GetIdEscenario();

        $obj_esc->Set();
        $id_escenario_code= $obj_esc->get_id_escenario_code();

        $esc_inicio= $obj_esc->GetInicio();
        $esc_fin= $obj_esc->GetFin();

        $_SESSION['inicio']= $esc_inicio;
        $_SESSION['fin']= $esc_fin;

        $_SESSION['id_escenario']= $id_escenario;
        $_SESSION['id_escenario_code']= $id_escenario_code;

        $_SESSION['current_id_escenario']= $_SESSION['id_escenario'];
        $_SESSION['current_id_escenario_code']= $_SESSION['id_escenario_code'];

        $_SESSION['id_tablero']= 0;
        $_SESSION['id_gantt']= 0;
        $_SESSION['id_proyecto']= 0;
    }

    public function SetEntity($id_entity) {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->Set($id_entity);

        $_SESSION['id_entity']= $obj_prs->GetId();
        $_SESSION['id_entity_code']= $obj_prs->get_id_code();
        $_SESSION['entity_tipo']= $obj_prs->GetTipo();
        $_SESSION['entity_nombre']= $obj_prs->GetNombre();
        $_SESSION['entity_lugar']= $obj_prs->GetLugar();
        $_SESSION['entity_id_responsable']= $obj_prs->GetIdResponsable();

        $_SESSION['entity_conectado']= $obj_prs->GetConectado();

        $_SESSION['superior_entity_id']= $obj_prs->GetIdProceso_sup();
        $_SESSION['superior_entity_id_code']= $obj_prs->get_id_proceso_sup_code();

        $_SESSION['current_id_proceso']= $_SESSION['id_entity'];
        $_SESSION['current_id_proceso_code']= $_SESSION['id_entity_code'];
        
        $array_1= $obj_prs->get_synchronization_setup();
        $array_2= $obj_prs->get_entity_migrate_setup();

        $_SESSION['if_send_up']= ($array_1['up'] || $array_2['up']);
        $_SESSION['if_send_down']= ($array_1['down'] || $array_2['down']);        
    }
}
