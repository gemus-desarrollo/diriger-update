<?php

/*
 * Copyright 2017
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */
?>


<div id="navbar-secondary">
    <nav class="navd-content">
        <div class="navd-container">
            <div id="dismiss" class="dismiss">
                <i class="fa fa-arrow-left"></i>
            </div>            
            <a href="#" class="navd-header">
                <?=$title_plan?>
            </a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navd-collapse">
                    <?php if ($permit_change && $permit_add) {?>
                    <li class="nav-item d-none d-md-block">
                        <a class="icon" href="javascript:add()" title="nuevo evento">
                            <i class="fa fa-plus"></i>Agregar
                        </a>
                    </li>
                    <?php } ?>  
                    
                    <li class="navd-dropdown">
                        <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-cogs"></i>Opciones
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarOpciones">
                            <li class="nav-item">
                                <a id="btn-filter" class="icon d-none d-md-block" href="#"
                                    title="Filtrar por Tipo u Origen">
                                    <i class="fa fa-filter"></i>Filtrar
                                </a>
                            </li>

                            <?php if ($signal == 'mensual_plan') { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:monthstack()"
                                    title="mostrar las actividades rechazadas y de las que es responsable pero no participa">
                                    <i
                                        class="fa fa-table"></i><?=!$monthstack ? "Mostrar tareas agrupadas" : "Mostrar tareas por días"?>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($signal == 'calendar') { ?>
                            <?php if ($print_reject == _PRINT_REJECT_NO) { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:mostrar('print')"
                                    title="mostrar las actividades rechazadas y de las que es responsable pero no participa">
                                    <i class="fa fa-eye"></i>Mostrar tareas ocultas
                                </a>
                            </li>
                            <?php } else { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:mostrar('print')"
                                    title="ocultar las actividades rechazadas y de las que es responsable pero no participa">
                                    <i class="fa fa-eye-slash"></i>Ocultar tareas rechazadas
                                </a>
                            </li>
                            <?php } ?>

                            <li class="nav-item d-none d-lg-block">
                                <a class="icon" href="javascript:mostrar('outlook')"
                                    title="Enviar las actividades al calendario de Outlook Express">
                                    <i class="fa fa-mobile-phone"></i>Enviar a Outlook
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($signal == 'mensual_plan') { ?>
                            <?php if ($print_reject == _PRINT_REJECT_NO) { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:ver(0)" title="mostrar las actividades rechazadas">
                                    <i class="fa fa-eye"></i>Mostrar tareas ocultas
                                </a>
                            </li>
                            <?php } else { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:ver(0)" title="ocultar las actividades rechazadas">
                                    <i class="fa fa-eye-slash"></i>Ocultar tareas rechazadas
                                </a>
                            </li>
                            <?php } ?>
                            <?php } ?>

                            <?php if ($signal == 'anual_plan_audit' || $signal == 'anual_plan_meeting') { ?>
                            <?php if ($print_reject == _PRINT_REJECT_NO) { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:ver(0)" title="mostrar los controles rechazados">
                                    <i class="fa fa-eye"></i>Mostrar tareas ocultas
                                </a>
                            </li>
                            <?php } else { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:ver(0)" title="ocultar los controles rechazadas">
                                    <i class="fa fa-eye-slash"></i>Ocultar tareas rechazadas
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($tipo_plan == _PLAN_TIPO_AUDITORIA) { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:ver(1)" title="ver plan anual de auditorias">
                                    <i class="fa fa-fire-extinguisher"></i>Supervisiones
                                </a>
                            </li>
                            <?php } ?>
                            <?php if ($tipo_plan == _PLAN_TIPO_SUPERVICION) { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:ver(1)"
                                    title="ver plan anual de acciones de control">
                                    <i class="fa fa-fire"></i>Auditorias
                                </a>
                            </li>
                            <?php } ?>
                            <?php } ?>

                            <?php
                            if (($signal != 'anual_plan_meeting' && $signal != 'anual_plan_audit') &&  ($if_jefe || ($signal == 'calendar' && ($id_calendar == $_SESSION['id_usuario'] || $permit_aprove)))) {
                                $text_objetivo= ($signal == 'mensual_plan' || $signal == 'calendar') ? "Tareas Principales" : "Objetivos de Trabajo";
                                ?>
                            <li class="nav-item d-none d-md-block">
                                <a class="icon" href="javascript:mostrar('ob')">
                                    <i class="fa fa-gavel"></i><?=$text_objetivo?>
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($permit_aprove && ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL
							|| ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $id_calendar != $_SESSION['id_usuario']))) { ?>
                            <li class="nav-item d-none d-md-block">
                                <a class="icon" href="javascript:mostrar('ap')">
                                    <i class="fa fa-gavel"></i>Aprobar
                                </a>
                            </li>
                            <?php } ?>

                            <div class="dropdown-divider"></div>

                            <?php if (($signal != 'anual_plan_meeting' && $signal != 'anual_plan_audit') && (($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $id_calendar == $_SESSION['id_usuario'])
                                    || ($permit_aprove && $tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL))) { ?>
                            <li class="nav-item d-none d-md-block">
                                <a href="javascript:mostrar('auto_eval')">
                                    <i class="fa fa-gavel"></i>Auto Evaluar
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($permit_eval && ($signal != 'anual_plan_meeting' && $signal != 'anual_plan_audit')) { ?>
                            <li class="nav-item d-none d-md-block">
                                <a class="icon" href="javascript:mostrar('ev')">
                                    <i class="fa fa-gavel"></i>Evaluar
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($signal != 'anual_plan_audit' && $signal != 'anual_plan_meeting') { ?>
                            <!--
                            <div class="icon icon-objs" onclick="graficar()" title="Impacto sobre los Objetivos de Trabajo" style="right:390px;">Graficar</div>
                            -->
                            <?php } ?>

                            <?php if ($signal == 'mensual_plan' && !empty($acc)) { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:mostrar('points')"
                                    title="Resumen de Puntualizaciones">
                                    <i class="fa fa-check-circle"></i>Puntualización
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($signal != 'anual_plan_meeting' && $signal != 'anual_plan_audit')  { ?>
                            <li class="nav-item d-none d-md-block">
                                <a class="icon" href="javascript:imprimir(2)" title="Resumen del Plan">
                                    <i class="fa fa-print"></i>Resumen
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($signal == 'anual_plan_meeting')  { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:go_accords()"
                                    title="ver todos los acuerdos tomados en el año">
                                    <i class="fa fa-list"></i>Resumen de acuerdos
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($signal == 'anual_plan' || $signal == 'mensual_plan')  { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:showInductores()"
                                    title="Cumplimiento de los Objetivos de Trabajo a partir del cumplimiento de las actividades">
                                    <i class="fa fa-star-o"></i>Resumen por Objetivos de Trabajo
                                </a>
                            </li>
                            <?php } ?>

                            <?php if (!empty($id_proceso) && (int)$id_proceso == $_SESSION['id_entity']) { ?>
                            <?php if ($permit_repro && ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL && $tipo_plan != _PLAN_TIPO_ACTIVIDADES_MENSUAL)) { ?>
                            <li class="nav-item">
                                <a class="icon" href="javascript:_mostrar('copy',0)"
                                    title="Copiar las tareas en de esta página para el proximo año">
                                    <i class="fa fa-copy"></i>Copiar Página
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="icon" href="javascript:set_copy_all()"
                                    title="Copiar todo el Plan para el proximo año">
                                    <i class="fa fa-copy"></i>Copiar Todo el Plan
                                </a>
                            </li>
                            <?php } ?>     
                            
                            <?php if ($permit_repro && ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL)) { ?>
                            <li class="navd-dropdown">
                                <a class="dropdown-toggle" href="#navbarEnumerar" role="button"
                                    data-toggle="collapse" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-sort-numeric-asc"></i>Enumerar actividades
                                </a>

                                <ul class="navd-dropdown-menu" id="navbarEnumerar">
                                    <li class="nav-item">
                                        <a class="" href="javascript:set_numering(<?=_ENUMERACION_MANUAL?>)"
                                            title="Todas las actividades se enumeran según numero definido para la tarea">
                                            <i class="fa fa-edit"></i>Enumeración definida
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="" href="javascript:set_numering(<?=_ENUMERACION_CONTINUA?>)"
                                            title="Todas las actividades se enumeran en enumeración continua">
                                            <i class="fa fa-sort-numeric-asc"></i>Enumeración continua
                                        </a>
                                    </li>

                                    <?php if ($signal == 'anual_plan' || ($signal == 'mensual_plan' && $config->grouprows)) { ?>
                                    <li class="nav-item">
                                        <a class="" href="javascript:set_numering(<?=_ENUMERACION_CAPITULOS?>)"
                                            title="Cada capítulo o sub-capítulo se inicializa la numeración en 1">
                                            <i class="fa fa-indent"></i>Inicia cada capítulo en 1
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </li>
                            <?php } ?>
                            <?php } ?>                            
                        </ul>
                    </li>  
                    
                    <li class="navd-dropdown">
                        <a class="dropdown-toggle" href="#navbarUsuarios" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-<?=$signal == 'calendar' ? 'user' : 'industry'?>"></i>
                            <?= $signal == 'calendar' ? "Subordinados" : "Unidades Organizativas" ?>
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarUsuarios">
                            <?php
                            if ($signal == 'calendar') { ?>
                                <li class="nav-item <?php if ($id_calendar == $_SESSION['id_usuario']) echo "active"?>">
                                    <a href="javascript:loadpage(<?=$_SESSION['id_usuario']?>)" title="<?=$str?>">
                                        <?php
                                        $nombre_user= $_SESSION['nombre'];
                                        $email_user= $_SESSION['email'];
                                        if (!empty($_SESSION['cargo']))
                                            $nombre_user.= ', '.$_SESSION['cargo'];
                                        echo $nombre_user;
                                        ?>
                                    </a>
                                </li>

                                <!-- usuarios ------------------------------------------------------------------------------------ -->
                                <?php
                                $j = 0;
                                $array_ids= null;
                                if (is_array($obj_gantt->array_usuarios)) {
                                    foreach ($obj_gantt->array_usuarios as $array) {
                                        $id = $array['id'];

                                        if ($id == $_SESSION['id_usuario'])
                                            continue;
                                        if (array_key_exists($id, (array)$array_ids))
                                            continue;
                                        $array_ids[$id]= $id;

                                        if (empty($id_calendar))
                                            $id_calendar = $id;
                                        ++$j;

                                        unset($obj_prs);
                                        $obj_prs = new Tproceso($clink);
                                        $obj_prs->Set($array['id_proceso']);
                                        $tipo_prs = $obj_prs->GetTipo();
                                        $conectado = $obj_prs->GetConectado();

                                        $_in_building = ($array['id_proceso'] != $_SESSION['id_entity']) ? $obj_prs->get_if_in_building($array['id_proceso']) : true;

                                        $img_conectdo = ($obj_prs->GetConectado() != _NO_LOCAL && ($array['id_proceso'] != $_SESSION['local_proceso_id'] || !$_in_building)) ? "<img src='../img/transmit.ico' alt='requiere transmisión de datos' />" : null;
                                        $img_tipo = "<img src='../img/" . img_process($tipo_prs) . "' title='" . $Ttipo_proceso_array[$tipo_prs] . "' />";
                                        $tips_title_prs = $array['nombre'];

                                        $cargo = str_replace("\n", "", nl2br($array['cargo']));
                                        $cargo = str_replace('"', "''", $cargo);
                                        $proceso = "<b>CARGO:</b> " . str_replace("\r", "", $cargo);

                                        $proceso .= "<br />" . $img_tipo . "&nbsp;" . $img_conectdo . "<br />";
                                        $proceso .= $tmp_str;
                                        $proceso .= "<strong>Subordinada a:</strong> " . $obj_prs = $obj_prs->GetNombre() . ", <em class='tooltip_em'>" . $Ttipo_proceso_array[$tipo_prs] . "</em>";
                                        if (!$_in_building)
                                            $proceso .= "<br /><strong>Tipo de Conexion:</strong> " . $Ttipo_conexion_array[$conectado];

                                        if ($id == $id_calendar) {
                                            $nombre_user= $array['nombre'];
                                            $email_user= $array['email'];
                                            if (!empty($array['cargo']))
                                                $nombre_user.= ', '.textparse($array['cargo']);
                                        }
                                        ?>
                                        <li class="<?php if ($id == $id_calendar) echo "active" ?>"
                                            onmouseover="Tip('<?=addslashes($proceso)?>')" onmouseout="UnTip()">
                                            <a href="#" onclick="loadpage(<?= $id ?>)"
                                                onmouseover="Tip('<?=addslashes($proceso)?>')" onmouseout="UnTip()">
                                                <?php
                                                $user= $array['nombre'];
                                                if (!empty($array['cargo']))
                                                    $user.= ', '.textparse($array['cargo']);
                                                echo $user;
                                                ?>
                                            </a>
                                        </li>
                                <?php
                                    }
                                }

                                unset($array_ids);
                                $_SESSION['id_calendar']= $id_calendar;
                            }

                            if (empty($id_plan) && ($signal == 'calendar' && !empty($id_calendar))) {
                                $obj_plan->SetIdProceso($id_proceso);
                                $obj_plan->set_id_proceso_code($id_proceso_code);
                                $obj_plan->SetIdUsuario($id_calendar);
                                $id_plan= $obj_plan->Set();

                                if (empty($id_plan))
                                    $id_plan= $obj_plan->add_plan();
                            }

                            !empty($id_calendar) ? $obj_signal->SetIdUsuario($id_calendar) : $obj_signal->SetIdUsuario(null);
                            ?>

                            <!-- procesos ---------------------------------------------------------------------------------- -->
                            <?php
                            if ($signal != 'calendar' && !is_null($array_procesos)) {

                                if (!array_key_exists($id_proceso, (array)$array_procesos))
                                    $id_proceso= null;

                                foreach ($array_procesos as  $array) {
                                    if (empty($array['id']))
                                        continue;
                                    if (empty($id_proceso))
                                        $id_proceso= $array['id'];
                                    
                                    if ((!$config->show_group_dpto_plan && (!$config->show_prs_plan && $signal != 'anual_plan_audit')) 
                                        && $array['tipo'] > _TIPO_GRUPO)
                                        continue;
                                    if ($array['tipo'] == _TIPO_DEPARTAMENTO && $signal != 'anual_plan_audit')
                                        continue;

                                    if ((($signal == "anual_plan" || $signal == "anual_plan_meeting"  || $signal == "anual_plan_audit") && !$config->seeanualplan)
                                        || ($signal == "mensual_plan" && !$config->seemonthplan)) {

                                        if ((empty($acc) || $acc < 3) && ($_SESSION['id_entity'] == $array['id'] || $array['tipo'] <= $_SESSION['entity_tipo'])) {
                                            if (!array_key_exists($array['id'], $array_chief_procesos)) {
                                                if ($id_proceso == $array['id'])
                                                    $id_proceso= null;
                                                continue;
                                            }
                                        }
                                    }

                                    if (((!$config->show_prs_plan && $array['tipo'] >= _TIPO_PROCESO_INTERNO) || ($config->show_prs_plan && $array['tipo'] > _TIPO_PROCESO_INTERNO))
                                        && ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL))
                                        continue;

                                    include "../form/inc/_tablero_tabs_proceso.inc.php";
                                }

                                $_SESSION['id_proceso']= $id_proceso;
                            }

                            if (isset($obj_prs))
                                unset($obj_prs);
                            $obj_prs= new Tproceso($clink);
                            $obj_prs->SetYear($year);

                            if (!empty($id_proceso)) {
                                if ($id_proceso != $_SESSION['id_entity']) {
                                    $obj_prs->SetIdProceso($id_proceso);
                                    $obj_prs->Set();

                                    $conectado= $obj_prs->GetConectado();
                                    $nombre_prs_title= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];

                                    if ($obj_prs->GetConectado() != _NO_LOCAL)
                                        $id_proceso_asigna= $id_proceso;
                                    else {
                                        $id_proceso_asigna= $obj_prs->get_proceso_top($id_proceso, null, true);
                                    }
                                } else {
                                    $conectado= $_SESSION['entity_conectado'];
                                    $nombre_prs_title= $_SESSION['entity_nombre'].', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];      
                                    $id_proceso_asigna= $_SESSION['id_entity'];                            
                                }    

                            } else {
                                $obj_prs->SetIdProceso($_SESSION['usuario_proceso_id']);
                                $obj_prs->Set();
                                
                                $id_proceso_asigna= !empty($obj_prs->GetIdEntity()) ? $obj_prs->GetIdEntity() : $_SESSION['id_entity'];
                                $conectado= $obj_prs->GetConectado();
                                $nombre_prs_title= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
                            }

                            if (empty($id_plan) && ($signal != 'calendar' && !empty($id_proceso))) {
                                $obj_code= new Tcode($clink);
                                $id_proceso_code= $obj_code->get_code_from_table('tprocesos', $id_proceso);
                        
                                $obj_plan->SetIdProceso($id_proceso);
                                $obj_plan->set_id_proceso_code($id_proceso_code);
                                $obj_plan->SetIdResponsable(null);
                                $id_plan= $obj_plan->Set();

                                if (empty($id_plan))
                                    $id_plan= $obj_plan->add_plan();
                            }

                            ?>
                        </ul>
                    </li>
                    
                    <?php
                    $use_select_year= true;
                    $use_select_month= ($signal == 'mensual_plan' || $signal == 'calendar') ? true : false;
                    $use_select_day= false;
                    require "../form/inc/_dropdown_date.inc.php";
                    ?>

                    <?php if ($signal != 'calendar') { ?>
                    <li class="nav-item d-none d-lg-block">
                        <a id="open" class="icon" href="javascript:imprimir(1)">
                            <i class="fa fa-print"></i>Imprimir
                        </a>
                    </li>
                    <?php } else { ?>
                    <li class="navd-dropdown d-none d-lg-block">
                        <a id="open" class="dropdown-toggle" href="#navbarImprimir" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-print"></i>Imprimir<span class="caret"></span>
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarImprimir">
                            <li class="nav-item">
                                <a class="icon" href="javascript:imprimir(1)">
                                    <i class="fa fa-print"></i>Continua
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="icon" href="javascript:imprimir('week')">
                                    <i class="fa fa-print"></i>Semana por página
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php } ?>                    
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a class="icon" href="open_help_window('<?=$help?>')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>        
</div>

<?php $date= ($signal != 'anual_plan' && $signal != 'anual_plan_meeting') ? $meses_array[(int)$month].', '.$year : $year; ?>
<div id="navbar-third" class="app-nav">
    <ul class="navd-static d-flex flex-row p-2 row col-12">
        <li class="col-auto">
            <label class="badge badge-success">
                <?=$date?>
            </label>
        </li>

        <li class="col">
            <div class="row">
                <label class="label ml-3">Muestra:</label>
                <div id="nshow" class="badge badge-warning">0</div>                    
            </div>
        </li>

        <li class="col">
            <div class="row">
                <label class="label ml-3">Ocultas:</label>
                <div id="nhide" class="badge badge-warning">0</div>                
            </div>
        </li>

        <li class="col-auto">
            <div class="badge badge-danger">
                <?php
                if ($signal != 'calendar') {
                    if (!empty($id_proceso) && $id_proceso != -1) {
                        $_connect= ($conectado != _LAN && $id_proceso != $_SESSION['local_proceso_id']) ? _NO_LOCAL : _LOCAL;
                    }
                    if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) {
                ?>
                    <i class="fa fa-wifi"></i>
                    <?php } ?>
                    <?=$nombre_prs_title?>
                    <?php } else { ?>
                    <?=$nombre_user?>
                <?php } ?>
            </div>
        </li>

        <li class="col-md-3 d-none d-md-block">
            <div class="row">
                <label class="label ml-3">Filtrado:</label>

                <div class="badge badge-warning">
                    <?php if (!empty($capitulo) || !empty($id_tipo_evento) || (!empty($like_name) && is_string($like_name))) { ?>
                        <?php
                        if ($tipo_plan == _PLAN_TIPO_AUDITORIA || $tipo_plan == _PLAN_TIPO_SUPERVICION) {
                            if (!empty($origen))
                                echo "{$Ttipo_nota_origen_array[(int)$origen]} / {$Ttipo_auditoria_array[(int)$tipo]}";
                            if (!empty($organismo))
                                echo " / {$organismo}";
                        } else {
                            if (!empty($capitulo) && $capitulo > 1) {
                                echo "Capítulo: <span class=nshow>".number_format_to_roman($capitulo-1)."</span>";
                            }
                            if (!empty($id_tipo_evento)) {
                                $obj_tipo= new Ttipo_evento($clink);
                                $obj_tipo->Set($id_tipo_evento);
                                $numero= $obj_tipo->GetNumero();

                                echo "Clasificación: <span class=nshow>{$numero}</span>";
                            }
                            if (!empty($like_name) && is_string($like_name))
                                echo "  Contiene el texto: <span class=\"nshow\">{$like_name}</span>";
                        }
                        ?>
                    <?php } else { ?>
                        &nbsp;
                    <?php } ?>
                </div>                
            </div>
        </li>

        <li class="col-lg-2 d-none d-lg-block">
            <a href="javascript:showInfoPanel()">
                <i class="fa fa-trophy"></i>Estado del Plan
            </a>
        </li>
    </ul>
</div>