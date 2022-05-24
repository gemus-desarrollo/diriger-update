<?php

/*
 * Copyright 2017
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

    $objetivos= $obj_plan->GetObjetivo();
    $date_aprb= $obj_plan->GetAprobado();
    $array_aprb= $obj_user->GetEmail($obj_plan->GetIdResponsable_aprb());
    //	if (!is_null($array_aprb)) $array_aprb= ($config->onlypost) ? $array_aprb['cargo'] : $array_aprb['nombre'].' ('.$array_aprb['cargo'].')';

    $auto_evaluacion= $obj_plan->GetAutoEvaluacion();
    $date_auto_eval= $obj_plan->GetAutoEvaluado();
    $array_auto_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_auto_eval());
    //	if (!is_null($array_auto_eval)) $array_auto_eval= ($config->onlypost) ? $array_auto_eval['cargo'] : $array_auto_eval['nombre'].' ('.$array_auto_eval['cargo'].')';

    $evaluacion= $obj_plan->GetEvaluacion();
    $date_eval= $obj_plan->GetEvaluado();
    $array_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());
    //	if (!is_null($array_eval)) $array_eval= ($config->onlypost) ? $array_eval['cargo'] : $array_eval['nombre'].' ('.$array_eval['cargo'].')';

    $cumplimiento= $obj_plan->GetCumplimiento();

    /*
    * calculo del plan para la confeccion del plan de trabajo
    */
    $obj_plan->set_cronos(date('Y-m-d H:i:s'));

    $obj_plan->SetIdProceso($id_proceso);
    $obj_plan->set_id_proceso_asigna($id_proceso_asigna);
    $obj_plan->SetIdResponsable(null);
    $obj_plan->SetRole(null);
    $obj_plan->divout= $obj->divout;
    !empty($id_calendar) ? $obj_plan->SetIdUsuario($id_calendar) : $obj_plan->SetIdUsuario(null);

    $obj_plan->SetDay(null);
    $obj_plan->SetMonth($_month);
    $obj_plan->SetIfEmpresarial(null);
    $obj_plan->SetTipoPlan($tipo_plan);
    $obj_plan->signal= $signal;
    $obj_plan->set_init_row_temporary($init_row_temporary);
    if ($signal != 'anual_plan_audit')
        $obj_plan->toshow= $empresarial;
    if ($tipo_plan == _PLAN_TIPO_MEETING)
        $obj_plan->toshow= _EVENTO_MENSUAL;

    $obj_plan->debug_time('automatic_event_status');
    $if_teventos= false;
    $if_treg_evento= false;
    $limited= ($signal != 'calendar') ? true : false;
    $obj_plan->SetTipoPlan($tipo_plan);

    if ($signal != 'anual_plan_audit') {
        if ((is_null($if_numering) && $_if_numering != _ENUMERACION_MANUAL)
            || (!is_null($if_numering) && $if_numering != $_if_numering)) {
            $obj_num= new Tevento_numering($clink);

            $obj_num->SetIdPlan($id_plan);
            $obj_num->SetYear($year);
            $obj_num->SetMonth($_month);
            $obj_num->SetIfEmpresarial(null);
            $obj_num->SetIdProceso($id_proceso);
            $obj_num->set_id_proceso_asigna($id_proceso_asigna);
            $obj_num->toshow= $obj_plan->toshow;
            $obj_num->signal= $signal;
            $obj_num->SetIfNumering($if_numering);

            $obj_num->build_numering();
            $if_teventos= $obj_num->if_teventos;
            $if_treg_evento= $obj_num->if_treg_evento;
        }

        $obj_plan->create_temporary_treg_evento_table= $tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL ? false : true;

        if (!empty($capitulo))
            $obj_plan->SetIfEmpresarial($capitulo);
        if (!empty($id_tipo_evento))
            $obj_plan->SetIdTipo_evento($id_tipo_evento);
        if (!empty($like_name) && is_string($like_name))
            $obj_plan->like_name= $like_name;

        $obj_plan->if_teventos= $if_teventos;
        $obj_plan->if_treg_evento= $if_treg_evento;
        $obj_plan->SetIfNumering($if_numering);
        $update_cump= $tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL ? true : false;

        $obj_plan->monthstack= $monthstack;
        $obj_plan->automatic_event_status($obj_plan->toshow, $limited, true, $update_cump);
    }
    elseif ($signal == 'anual_plan_audit') {
        $obj_plan->SetOrigen($origen);
        $obj_plan->SetTipo($tipo);
        $obj_plan->SetOrganismo($organismo);
        
        $obj_plan->automatic_audit_status($limited);
    }

    $obj_plan->debug_time('automatic_event_status');

    $obj_event= new Tevento($clink);
    ($tipo_plan == _PLAN_TIPO_MEETING) ? $obj_event->SetIdTipo_reunion(0) : $obj_event->SetIdTipo_reunion(null);

    $max_num_pages= $obj_plan->max_num_pages;