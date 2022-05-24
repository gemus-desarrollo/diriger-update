<?php

/*
 * Copyright 2017
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

    $obj_prs= new Tproceso($clink);
    $pos= 0;
    $obj_gantt= null;
    $array_procesos= null;

    if ($signal == 'calendar') {
        $obj_gantt = new Torgtarea($clink);
        $obj_gantt->SetIdResponsable($_SESSION['id_usuario']);
        $obj_gantt->set_user_date_ref($user_date_ref);

        $obj_gantt->get_subordinados_array(true);
        $if_id_calendar_exist = false;

        $i = 0;
        $if_jefe = false;

        $array_jefes = $obj_gantt->listar_chief($id_calendar);

        foreach ($array_jefes as $array) {
            if ($array['id'] == $_SESSION['id_usuario'])
                $if_jefe = true;
            ?>
            <script language="javascript">
                array_chief_id[<?=$i?>] = <?=$array['id']?>;
                array_chief_nombre[<?=$i?>] = "<?=$array['nombre']?>";
                array_chief_cargo[<?=$i?>] = "<?=$array['cargo']?>";
            </script>
            <?php
            ++$i;
        }
    }

    if ($signal != 'calendar') {
        $obj_prs->SetIdResponsable(null);
        $obj_prs->SetIdProceso(null);
        $obj_prs->SetConectado(null);
        $obj_prs->SetTipo(null);
        $obj_prs->SetYear($year);

        $obj_prs->set_acc($acc);

        $corte_prs= $signal == 'anual_plan_audit' ? _TIPO_PROCESO_INTERNO : null;
        if ($signal != 'anual_plan_audit') {
            if ($config->show_prs_plan)
                $corte_prs= _TIPO_PROCESO_INTERNO;
            elseif ($config->show_group_dpto_plan)
                $corte_prs= _TIPO_DEPARTAMENTO;
            else
                $corte_prs= _TIPO_DIRECCION;
        }

        if ($signal == 'anual_plan_audit') {
            $exclude_prs= null;
        } else {
            $exclude_prs= array();
            if (!$config->show_prs_plan)
                $exclude_prs[_TIPO_PROCESO_INTERNO]= 1;
        }

        $array_procesos_meeting= null;
        if ($signal == 'anual_plan_meeting') {
            $obj_event= new Tevento($clink);
            $obj_event->SetYear($year);
            $array_procesos_meeting= $obj_event->get_array_procesos_meeting($_SESSION['id_usuario']);
            unset($obj_event);
        }

        $obj_prs->SetIdEntity($_SESSION['id_entity']);

        if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
            $obj_prs->SetIdUsuario(null);
            if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO) {
                if (!$config->show_group_dpto_plan && !$config->show_prs_plan) {
                    $obj_prs->SetIdProceso($_SESSION['id_entity']);
                    $array_procesos= $obj_prs->listar_in_order('eq_desc', true,  $corte_prs, false);
                } else
                    $array_procesos= $obj_prs->get_procesos_down_cascade(null, $_SESSION['id_entity'], $corte_prs);
            }  else {
                if (!$config->show_group_dpto_plan && !$config->show_prs_plan)
                    $array_procesos= $obj_prs->get_procesos_down($id_proceso, $corte_prs, null, true);
                else
                    $array_procesos= $obj_prs->get_procesos_down_cascade(null, $_SESSION['id_entity'], $corte_prs);
            }
        } else {
            if (!empty($acc)) {
                $obj_prs->SetIdUsuario(null);
                $obj_prs->SetIdProceso($_SESSION['usuario_proceso_id']);

                if (!$config->show_group_dpto_plan && !$config->show_prs_plan)
                    $array_procesos= $obj_prs->get_procesos_down($_SESSION['usuario_proceso_id'], $corte_prs, null, true);
                else
                    $array_procesos= $obj_prs->get_procesos_down_cascade(null, $_SESSION['usuario_proceso_id'], $corte_prs);
            }
        }

        $array_procesos= array_merge_overwrite($array_procesos, $array_procesos_meeting);

        $obj_prs->SetIdUsuario(null);
        $j= 0;

        if ($acc == _ACCESO_ALTA || $acc == _ACCESO_MEDIA) {
            if (!array_key_exists($_SESSION['id_entity'], (array)$array_procesos)) {
                $obj_prs->Set($_SESSION['id_entity']);

                $array= array('id'=>$_SESSION['id_entity'], 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(),
                        'tipo'=>$obj_prs->GetTipo(), 'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado(),
                        'id_proceso'=>$obj_prs->GetIdProceso_sup());

                $array_procesos[$_SESSION['id_entity']]= $array;
            }
        }

        if ($signal == 'mensual_plan' && (!$if_jefe && $_SESSION['nivel'] <= _REGISTRO)) {
            $obj_prs->Set($_SESSION['usuario_proceso_id']);

            $array= array('id'=>$_SESSION['usuario_proceso_id'], 'id_code'=>$obj_prs->get_id_code(), 'nombre'=>$obj_prs->GetNombre(),
                    'tipo'=>$obj_prs->GetTipo(), 'id_responsable'=>$obj_prs->GetIdResponsable(), 'conectado'=>$obj_prs->GetConectado(),
                    'id_proceso'=>$obj_prs->GetIdProceso_sup());

            $array_procesos[$_SESSION['usuario_proceso_id']]= $array;   
        }

        if (!array_key_exists($id_proceso, (array)$array_procesos))
            $id_proceso= null;

        unset($obj_prs);
        $obj_prs= new Tproceso($clink);
        !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
        $array_chief_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);
    }

    // determinar si el usuario es jefe
    if ($signal != 'calendar') {
        $if_jefe= false;
        if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
            $if_jefe= true;
        if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
            $if_jefe= true;
        if ($acc == _ACCESO_BAJA && ($id_proceso == $_SESSION['usuario_proceso_id'] && $id_proceso != $_SESSION['id_entity']))
            $if_jefe= true;
        /*
        if ($acc == _ACCESO_MEDIA && ($id_proceso == $_SESSION['id_entity']))
            $if_jefe= true;
        */
    }

    $obj_plan= ($signal != 'anual_plan_audit') ? new Tplantrab($clink) : new Tplan_ci($clink);
    ($tipo_plan == _PLAN_TIPO_MEETING) ? $obj_plan->SetIdTipo_reunion(0) : $obj_plan->SetIdTipo_reunion(null);

    $obj_plan->SetIdResponsable(null);
    !empty($id_calendar) ? $obj_plan->SetIdUsuario($id_calendar) : $obj_plan->SetIdUsuario(null);
    $obj_plan->SetRole(null);

    $obj_plan->SetYear($year);
    $_month= ($signal == 'anual_plan' || $signal == 'anual_plan_audit' || $signal == 'anual_plan_meeting') ? null : $month;
    $obj_plan->SetMonth($_month);
    $obj_plan->SetDay(null);

    $obj_plan->SetIfEmpresarial($empresarial);

    if ($signal == 'calendar') {
        $obj_user= new Tusuario($clink);
        $obj_user->Set($id_calendar);
        $id_proceso= $obj_user->GetIdProceso();
        $id_proceso_code= $obj_user->get_id_proceso_code();
    }

    if (isset($obj_prs)) unset($obj_prs);
    $obj_prs= new Tproceso($clink);

    if (!empty($id_proceso)) {
        $obj_prs->SetIdResponsable(null);
        $obj_prs->SetConectado(null);
        $obj_prs->SetTipo(null);
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();

        $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
    }


    $tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL ? $obj_plan->SetIdProceso($id_proceso) : $obj_plan->SetIdProceso(null);
    $obj_plan->SetTipoPlan($tipo_plan);

    $id_plan= null;
    if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL || ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && !empty($id_proceso)))
        $id_plan= $obj_plan->Set();

    $_if_numering= $obj_plan->GetIfNumering();
    $_if_numering= is_null($_if_numering) ? _ENUMERACION_MANUAL : $_if_numering;

    /*
    if (empty($id_plan) && !empty($id_proceso)) {
        $obj_code= new Tcode($clink);
        $id_proceso_code= $obj_code->get_code_from_table('tprocesos', $id_proceso);

        $obj_plan->SetIdProceso($id_proceso);
        $obj_plan->set_id_proceso_code($id_proceso_code);
        $obj_plan->SetIdResponsable($_SESSION['id_usuario']);
        $id_plan= $obj_plan->add_plan();
    }
    */
    
    if ($obj_prs->GetTipo() == _TIPO_PROCESO_INTERNO) {
        $id_proceso= $config->show_prs_plan ? $id_proceso : $_SESSION['id_entity'];
        $id_proceso_asigna= $_SESSION['id_entity'];
        if (!$config->show_prs_plan)
            $nombre_prs= $_SESSION['empresa'].' ('.$Ttipo_proceso_array[$_SESSION['entity_tipo']].')';
    }

    $obj_prs->SetYear($year);
    $id_proceso_asigna= !empty($id_proceso) ? $obj_prs->get_proceso_top($id_proceso, null, true) : $_SESSION['id_entity'];

    $permit_add= false;
    $permit_aprove= false;
    $permit_eval= false;
    $permit_change= false;
    $permit_repro= false;

    if ($signal == 'calendar')
        $permit_add= true;
    else {
        if ($if_jefe
            || ((($acc == _ACCESO_BAJA || $acc == _ACCESO_MEDIA) && $id_proceso == $_SESSION['usuario_proceso_id']) || (($acc == _ACCESO_MEDIA || $acc == _ACCESO_ALTA)
                && $id_proceso == $_SESSION['id_entity'])))
            $permit_add= true;
        else
            $permit_add= false;
    }

    // asignar los permisos de aprobacion o modificacion  del plan
    if ($signal == 'anual_plan' || $signal == 'anual_plan_audit' || $signal == 'anual_plan_meeting') {
        if (($if_jefe || $permit_add) && ($year == $actual_year || ($year == ($actual_year - 1) && $actual_month <= 3))) {
            if ($if_jefe)
                $permit_aprove= true;
            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['usuario_proceso_id'] != $id_proceso)
                $permit_eval= true;
            $permit_change= true;
        }

        if ($if_jefe && $year >= $actual_year)
            $permit_aprove= true;
        if (($if_jefe || $permit_add) && $year >= $actual_year)
            $permit_change= true;
        if (($if_jefe || $permit_add) && $year >= $actual_year-1)
            $permit_repro= true;
    }

    if ($signal == 'mensual_plan') {
        if (($if_jefe || $permit_add)
            && (($year == $actual_year && $month <= $actual_month) || ($year == ($actual_year - 1) && $month >= 11))) {
            if ($if_jefe)
                $permit_aprove= true;
            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['usuario_proceso_id'] != $id_proceso)
                $permit_eval= true;
            $permit_change= true;
            $permit_repro= true;
        }

        if (($if_jefe || $permit_add)
            && (($year == $actual_year && $month >= $actual_month) || $year >= $actual_year)) {
            if ($if_jefe)
                $permit_aprove= true;
            $permit_change= true;
            $permit_repro= true;
        }
    }

    if ($signal == 'calendar') {
        if (($if_jefe || $_SESSION['nivel'] >= _ADMINISTRADOR) || $acc_planwork)
            $if_jefe= true;

        if ($year == $actual_year && $month == $actual_month) {
            $permit_aprove= true;
            $permit_eval= false;
        }

        if ($month == ($actual_month + 1) && $year == $actual_year && $actual_day >= 24)
            $permit_aprove= true;
        if ($month == ($actual_month - 1) && $year == $actual_year && $actual_day <= 15)
            $permit_aprove= true;
        if ($month == 12 && ($year + 1) == $actual_year && ($actual_month == 1 && $actual_day <= 15))
            $permit_aprove= true;

        if ($year == $actual_year && $month <= ($actual_month - 1))
            $permit_eval= true;
        if ($year == ($actual_year - 1) && $month == 12 && $actual_month == 1)
            $permit_eval= true;

        if ($month >= $actual_month && $year == $actual_year)
            $permit_change= true;
        if ($month == ($actual_month - 1) && $year == $actual_year && $actual_day <= 8)
            $permit_change= true;
        if ($month == 12 && ($year + 1) == $actual_year && ($actual_month == 1 && $actual_day <= 8))
            $permit_change= true;
        if (($year - 1) == $actual_year)
            $permit_change= true;

        if ($id_calendar == $_SESSION['id_usuario']) {
            $permit_eval = false;
            $permit_aprove = false;
        }

        if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc) {
            $permit_aprove= true;
            $permit_eval= true;
            $permit_change= true;
            $permit_repro= true;
        }
    }

    if ($signal == 'mensual_plan' && (!$if_jefe && $_SESSION['nivel'] <= _REGISTRO)) {
        $permit_aprove= false;
        $permit_eval= false;
        $permit_change= false;
        $permit_repro= false;        
    }
