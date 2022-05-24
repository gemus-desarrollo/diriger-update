<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 04/04/2018
 * Time: 11:29 a.m.
 */

    $obj_prs = new Tproceso($clink);
    $obj_prs->SetYear($year);
    $obj_prs->SetIdResponsable(null);
    $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
    $obj_prs->SetConectado(null);
    $obj_prs->SetTipo(null);

    if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
        $obj_prs->SetIdUsuario(null);
        $array_procesos = $obj_prs->listar_in_order('eq_desc', true, _TIPO_PROCESO_INTERNO, false);
    } else {
        $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
        $exclude_prs_type= null;
        $array_procesos = $obj_prs->get_procesos_by_user('eq_desc', _TIPO_ARC, false, null, $exclude_prs_type);
    }

    $obj_prs->SetIdUsuario(null);


    unset($obj_prs);
    $obj_prs = new Tproceso($clink);
    $obj_prs->SetYear($year);
    $array_chief_procesos = $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);
    
    $if_jefe= false;
    if ((!is_null($array_chief_procesos)) && array_key_exists($id_proceso, (array)$array_chief_procesos)) 
            $if_jefe= true;
    if ($_SESSION['nivel'] >= _SUPERUSUARIO) 
        $if_jefe= true;
                        
    $permit_add= $if_jefe ? true : false;
    if ($acc == _ACCESO_ALTA) 
        $permit_add= true;
    
    if ($action == 'list' && $permit_add) 
        $action= 'add';
    