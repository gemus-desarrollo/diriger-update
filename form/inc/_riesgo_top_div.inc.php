<?php

/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 6/1/15
 * Time: 9:29 a.m.
 */
?>
<?php
$nshow= 0;
$nhide= 0;

if ($signal == 'riesgo') {
    $title = 'riesgo';
    $id_tipo_plan = _PLAN_TIPO_PREVENCION;
    $title_plan = "prevención";
}
if ($signal == 'graph_nota') {
    $title = 'Resumen de hallazgo';
    $id_tipo_plan = _PLAN_TIPO_PREVENCION;
    $title_plan = "medidas";
}
if ($signal == 'nota') {
    $title = 'hallazgo';
    $title_plan = "medidas";
    $id_tipo_plan = _PLAN_TIPO_ACCION;
}
?>

<?php
$id_select_prs = $id_proceso;

$obj_prs = new Tproceso($clink);
$obj_prs->SetYear($year);
$obj_prs->SetIdResponsable(null);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->SetConectado(null);
$obj_prs->SetTipo(null);

if ($_SESSION['nivel'] >= _SUPERUSUARIO || $acc == _ACCESO_ALTA) {
    $obj_prs->SetIdUsuario(null);
    $array_procesos = $obj_prs->listar_in_order('eq_desc', false, _TIPO_PROCESO_INTERNO, false);
} else {
    $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
    if ($config->show_group_dpto_risk)
        $exclude_prs_type = null;
    else
        $exclude_prs_type = array(_TIPO_DEPARTAMENTO, _TIPO_GRUPO);
    $array_procesos = $obj_prs->get_procesos_by_user('eq_desc', _TIPO_PROCESO_INTERNO, false, null, $exclude_prs_type);
}

$obj_prs->SetIdUsuario(null);

if ($acc == _ACCESO_ALTA) {
    if (!array_key_exists($_SESSION['id_entity'], $array_procesos)) {
        $obj_prs->Set($_SESSION['id_entity']);

        $array = array(
            'id' => $_SESSION['id_entity'], 'id_code' => $obj_prs->get_id_code(), 'nombre' => $obj_prs->GetNombre(), 
            'tipo' => $obj_prs->GetTipo(), 'id_responsable' => $obj_prs->GetIdResponsable(),  
            'conectado' => $obj_prs->GetConectado(), 'id_proceso' => $obj_prs->GetIdProceso_sup()
        );

        $_array[$_SESSION['id_entity']] = $array;
        $array_procesos = $_array + $array_procesos;
    }
}

$array_procesos = $obj_prs->get_procesos_down_cascade(null, null, null, $array_procesos);

if ($id_proceso != -1 && !array_key_exists($id_proceso, $array_procesos))
    $id_proceso = null;

unset($obj_prs);
$obj_prs = new Tproceso($clink);
$obj_prs->SetYear($year);
$array_chief_procesos = $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);
?>

<?php
if (empty($id_proceso)) {
    $obj_prs->SetIdUsuario(null);
    reset($array_procesos);
    if (empty($id_select_prs) || ($id_select_prs != -1 && !array_key_exists($id_proceso, $array_procesos)))
        $id_select_prs = 0;

    foreach ($array_procesos as  $array) {
        if (empty($array['id']))
            continue;
        if ($_SESSION['entity_tipo'] < _TIPO_UEB 
            && ($array['tipo'] == _TIPO_GRUPO || $array['tipo'] == _TIPO_DEPARTAMENTO || $array['tipo'] == _TIPO_ARC))
            continue;
        if (empty($id_select_prs)) {
            $id_select_prs = $array['id'];
            $_connect_prs = $array['conectado'];
        }
    }
}
?>

<?php
$if_jefe = false;
if ((!is_null($array_chief_procesos)) && array_key_exists($id_select_prs, (array)$array_chief_procesos))
    $if_jefe = true;
if ($_SESSION['nivel'] >= _SUPERUSUARIO || !empty($_SESSION['acc_planrisk']))
    $if_jefe = true;

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdResponsable(null);
$obj_prs->SetConectado(null);
$obj_prs->SetTipo(null);

if (!empty($id_proceso) && $id_proceso != -1)
    $obj_prs->SetIdProceso($id_proceso);
else
    $obj_prs->SetIdProceso($_SESSION['id_entity']);

if (!empty($id_proceso) && $id_proceso != -1) {
    $obj_proceso = new Tproceso($clink);
    $obj_proceso->SetIdProceso($id_select_prs);
    $obj_proceso->Set();
    $id_proceso_code = $obj_proceso->get_id_code();
    $tipo = $obj_proceso->GetTipo();
    $nombre_prs = $obj_proceso->GetNombre() . ' (' . $Ttipo_proceso_array[$obj_proceso->GetTipo()] . ')';
    $id_responsable = $obj_proceso->GetIdResponsable();
    $_connect_prs = $obj_proceso->GetConectado();
} else {
    $nombre_prs = "Todas las Unidades organizativas";
}

$permit_aprove = false;
$permit_eval = false;
$permit_change = false;
$permit_repro = false;

$permit_add = $if_jefe ? true : false;
if ($acc == _ACCESO_ALTA) {
    $permit_add = true;
}
    
if ($id_select_prs != $_SESSION['id_entity']
    && (!is_null($array_procesos) && ($acc == _ACCESO_BAJA || $acc == _ACCESO_MEDIA)) && array_key_exists($id_select_prs, (array)$array_procesos)) {
    $permit_add = true;
}

if ($permit_add && ($year >= $actual_year || ($year == ($actual_year - 1) && (int)$actual_month <= 3))) {
    $permit_aprove = true;
    
    if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['usuario_proceso_id'] != $id_proceso)
        $permit_eval = true;

    if ($permit_add) {
        $execute = 'add';
        $permit_change = true;
    }
}

if (($permit_add && $if_jefe) && $year >= ($actual_year-1)) {
    $execute = 'add';
    $permit_aprove = true;
    $permit_change = true;
}
if ($if_jefe && $year <= $actual_year) {
    $permit_repro = true;
}
if (($if_jefe && $permit_add) && ($year == $actual_year || $year == $actual_year-1)) {
    $permit_copy = true;
}

$action = ($permit_aprove || $permit_change || $permit_repro) ? 'edit' : 'list';

if ($signal == 'riesgo')
    $imprimir = 'Plan de Prevención';
if ($signal == 'lriesgo' || $signal == 'lnota' || $signal == 'graph_nota')
    $imprimir = 'Imprimir';
if ($signal == 'nota')
    $imprimir = 'Plan de Medidas';
?>

<!-- Docs master nav -->

<div id="navbar-secondary">
    <nav class="navd-content">
        <div class="navd-container">
            <div id="dismiss" class="dismiss">
                <i class="fa fa-arrow-left"></i>
            </div>            
            <a href="#" class="navd-header">
                <?= strtoupper($title) ?>
            </a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navd-collapse">
                    <!-- Select de Procesos -->

                    <?php if ($signal == 'riesgo' || $signal == 'nota') { ?>
                    <?php if ($execute == 'add') { ?>
                    <li class="nav-item d-none d-md-block">
                        <a href="#" class="" onclick="_add()" title="nuevo <?= $title ?>">
                            <i class="fa fa-plus"></i>Agregar
                        </a>
                    </li>
                    <?php } } ?>

                    <?php if ($signal == 'riesgo' || $signal == 'nota') { ?>
                    <li class="navd-dropdown">
                        <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-cogs"></i>Opciones<b class="caret"></b>
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarOpciones">
                            <?php if ($signal == 'nota' || $signal == 'lnota') { ?>
                            <li class="navd-dropdown">
                                <a class="dropdown-toggle" href="#navbarFiltrar" data-toggle="collapse" aria-expanded="false">
                                    <i class="fa fa-filter"></i>Filtrar
                                </a>

                                <ul class="navd-dropdown-menu" id="navbarFiltrar">
                                    <li class="nav-item">
                                        <a href="#" class="" onclick="show_filter()">
                                            Tipo de Nota o Hallazgo
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="#" class="" onclick="mostrar_auditorias('filter')">
                                            Auditorías
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <?php } ?>

                            <?php if ($signal == 'riesgo' || $signal == 'lriesgo') { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="show_filter()">
                                    <i class="fa fa-filter"></i>Filtrar
                                </a>
                            </li>
                            <?php } ?>


                            <?php if ($signal == 'nota') { ?>
                            <?php if (!$show_all_notes) { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="show_all_notes(1)">
                                    <i class="fa fa-filter"></i>Mostrar todas las no cerradas
                                </a>
                            </li>
                            <?php } else { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="show_all_notes(0)">
                                    <i class="fa fa-filter"></i>Mostrar solo las que corresponden al año
                                    <?= $year ?>
                                </a>
                            </li>
                            <?php 
                                }
                            } 
                            ?>

                            <?php if ($signal == 'nota') { ?>
                            <li class="navd-dropdown">
                                <a class="dropdown-toggle" href="#navbarChequeo" data-toggle="collapse" aria-expanded="false">
                                    <i class="fa fa-book"></i>Listas Chequeo
                                    </a>

                                    <ul class="navd-dropdown-menu" id="navbarChequeo">
                                        <li class="nav-item">
                                            <a href="#" class="" onclick="mostrar_auditorias('guide')">
                                                Aplicar Lista Chequeo
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="" onclick="mostrar_auditorias('resume')">
                                                Resumen Lista de chequeo
                                            </a>
                                        </li>
                                        <?php if ($id_proceso == $_SESSION['id_entity']) {?>
                                        <li class="nav-item">
                                            <a href="#" class="" onclick="mostrar_resume_guia(0)">
                                                Resumen Gráfico
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="#" class="" onclick="mostrar_resume_guia(1)">
                                                Resumen General
                                            </a>
                                        </li>                                        
                                        <?php } ?>
                                    </ul>
                            </li>
                            <?php } ?>

                            <?php if ($permit_repro && $signal == 'riesgo') { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="mostrar('copy')">
                                    <i class="fa fa-copy"></i>Copiar Todo
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($permit_aprove) { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="mostrar('aprove')">
                                    <i class="fa fa-gavel"></i>Aprobar
                                </a>
                            </li>

                            <?php if ($signal == 'riesgo') { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="listar_objetivos_ci()">
                                    <i class="fa fa-star-half-full"></i>Objetivos CI
                                </a>
                            </li>
                            <?php 
                                }
                            } 
                            ?>

                            <li class="nav-item">
                                <a href="#" class="" onclick="imprimir(2)" title="Resumen del Plan">
                                    <i class="fa fa-print"></i>Resumen
                                </a>
                            <li class="nav-item">
                                <a href="#" class="" onclick="imprimir(3)"
                                    title="Resumen del Estado de las tareas asociadas">
                                    <i class="fa fa-print"></i>Resumen de tareas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="" onclick="resume(0)">
                                    <i class="fa fa-list"></i><?= $signal == "riesgo" ? "Listado" : "Análisis de Causas/Oportunidades" ?>
                                </a>
                            </li>

                            <?php if ($signal == 'riesgo') { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="resume(1)">
                                    <i class="fa fa-list"></i>Levantamiento de Riesgos
                                </a>
                            </li>
                            <?php } ?>

                            <?php if ($signal == 'nota') { ?>
                            <li class="nav-item">
                                <a href="#" class="" onclick="grafico()">
                                    <i class="fa fa-list"></i>Graficar Notas de hallazgos
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li>
                    <?php } ?>


                    <?php
                    unset($obj_prs);
                    $obj_prs = new Tproceso($clink);
                    $obj_prs->SetConectado(_NO_LOCAL);
                    $array_proceso_conected = $obj_prs->listar(false);

                    unset($obj_prs);
                    $obj_prs = new Tproceso($clink);

                    reset($array_procesos);
                    ?>

                    <?php if (!is_null($array_procesos)) { ?>
                    <li class="navd-dropdown">
                        <a class="dropdown-toggle" href="#navbarUnidades" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-industry"></i>Unidades Organizativas<b class="caret"></b>
                        </a>
                        <input type="hidden" id="proceso" name="proceso" value="<?= $id_select_prs ?>" />

                        <ul class="navd-dropdown-menu" id="navbarUnidades">
                            <li class="nav-item">
                                <a href="#" class="<?php if ($id_select_prs == -1) echo "active" ?>"
                                    onclick="_dropdown_prs(-1)" title="Todos las Unidades Organizativas">
                                    Todos las Unidades Organizativas ...
                                </a>
                            </li>

                            <?php
                            $j = 0;
                            $pos = 0;
                            $obj_prs->SetIdUsuario(null);
                            if (empty($id_select_prs) || ($id_select_prs != -1 && !array_key_exists($id_proceso, $array_procesos)))
                                $id_select_prs = 0;

                            reset($array_procesos);
                            foreach ($array_procesos as $id => $row) {
                                if ($signal == 'lriesgo' || $signal == 'lnota') {
                                    if ((!empty($row['id_entity']) && $row['id_entity'] != $_SESSION['id_entity'])
                                        || (empty($row['id_entity']) && $row['id'] != $_SESSION['id_entity'])
                                    )
                                        continue;
                                }
                                if (empty($row['id']))
                                    continue;
                                if ($row['tipo'] == _TIPO_ARC)
                                    continue;
                                $img_conectdo = ($row['conectado'] != _LAN && $row['id'] != $_SESSION['local_proceso_id']) ? "<img  class=\'img-rounded icon\' src=\'" . _SERVER_DIRIGER . "img/transmit.ico\' alt=\'requiere transmisión de datos\' />" : null;

                                if (empty($id_select_prs)) {
                                    $_connect = $row['conectado'];
                                    $id_select_prs = $row['id'];
                                }

                                if (isset($obj_prs_tmp)) unset($obj_prs_tmp);
                                $obj_prs_tmp = new Tproceso($clink);

                                if (!empty($row['id_proceso']))
                                    $obj_prs_tmp->Set($row['id_proceso']);
                                $proceso_sup = $img_conectdo . "<br />";
                                $proceso_sup .= "<strong>Tipo:</strong> " . $Ttipo_proceso_array[$row['tipo']] . '<br />';
                                if (!empty($row['id_proceso']))
                                    $proceso_sup .= "<strong>Subordinada a:</strong> " . $obj_prs_tmp = $obj_prs_tmp->GetNombre() . ", <em class=\'tooltip_em\'>" . $Ttipo_proceso_array[$obj_prs_tmp->GetTipo()] . "</em>";
                                $proceso_sup .= "<br /><strong>Tipo de Conexion:</strong> " . $Ttipo_conexion_array[$row['conectado']];
                                $proceso = $row['nombre'] . ", <span class='tooltip_em'>" . $Ttipo_proceso_array[$row['tipo']] . "</span>";
                            ?>
                            <li class="nav-item">
                                <a href="#" class="<?php if ($id_select_prs == $row['id']) echo "active" ?>"
                                    onclick="_dropdown_prs(<?= $row['id'] ?>)"
                                    onmouseover="Tip('<?= $proceso_sup ?>')" onmouseout="UnTip()">
                                    <img class="img-rounded icon" src='../img/<?= img_process($row['tipo']) ?>'
                                        title='<?= $Ttipo_proceso_array[$row['tipo']] ?>' />
                                    <?= stripslashes($img_conectdo) ?>
                                    <?= $proceso ?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                    </li> <!-- Select de Procesos -->
                    <?php } ?>


                    <?php
                    $id_proceso = $id_select_prs;

                    $use_select_year = true;
                    $use_select_month = false;

                    require "../form/inc/_dropdown_date.inc.php";
                    ?>

                    <li class="nav-item">
                        <a href="#" class="" onclick="imprimir(1)">
                            <i class="fa fa-print"></i><?= $imprimir ?>
                        </a>
                    </li>
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a href="#" onclick="open_help_window('../help/14_riesgos.htm#14_2')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>
                        <?php if ($signal == 'lriesgo' || $signal == 'lnota' || $signal == 'graph_nota') { ?>
                        <li class="nav-item">
                            <a href="#" class="" onclick="closep()" title="regresar al tablero">
                                <?php if ($signal != 'graph_nota') { ?>
                                <i class="fa fa-home"></i>Cerrar
                                <?php } else { ?>
                                <i class="fa fa-close"></i>Cerrar
                                <?php } ?>
                            </a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</div>

<div id="navbar-third" class="app-nav d-none d-md-block">
    <ul class="navd-static d-flex flex-row p-2 row col-12">
        <li class="col">
            <label class="badge badge-success">
                <?= $year ?>
            </label>
        </li>
        <li class="col">
            <div class="row">
                <label class="label ml-3">Muestra:</label>
                <div id="nshow" class="badge badge-warning"></div>
            </div>    
        </li>

        <li class="col">
            <div class="row">
                <label class="label ml-3">Ocultas:</label>
                <div id="nhide" class="badge badge-warning"></div>
            </div>    
        </li>
        <li class="col-auto">
            <label class="badge badge-danger">
                <?php if ($_connect_prs != _LAN && ($id_select_prs != -1 && $id_select_prs != $_SESSION['local_proceso_id'])) { ?><i
                    class="fa fa-wifi"></i><?php } ?>
                <?= $nombre_prs ?>
            </label>
        </li>
        <li class="col">
            <a href="javascript:showInfoPanel()">
                <i class="fa fa-trophy"></i>Estado del Plan
            </a>
        </li>
    </ul>
</div>


<div id="navbar-four" class="row app-nav d-none d-md-block d-none d-lg-block ">
    <nav class="navd-content">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="row col-8">
                <label class="col-1">Filtrado:</label>
                
                <label class="badge badge-warning">
                    <?php
                    if ($signal == 'riesgo' || $signal == 'lriesgo') {
                        if ($estrategico == 1)
                            echo " Estratégicos";
                        else
                            echo " Operativos";
                        if ($econ == 1)
                            echo " || Económicos";
                        if ($reg == 1)
                            echo " || Regulatorios";
                        if ($calidad == 1)
                            echo " || Gestión de la Calidad";
                        if ($sst == 1)
                            echo " || Seguridad y Salud en el Trabajo";
                        if ($ma == 1)
                            echo " || Medioambientales";
                        if ($info == 1)
                            echo " || Informáticos";
                        if ($origen == 1)
                            echo " || Externos";
                    }

                    if ($signal == 'nota' || $signal == 'lnota') {
                        if ($noconf == 1)
                            echo " No-conformidades || ";
                        if ($observ == 1)
                            echo " Observaciones || ";
                        if ($mej == 1)
                            echo " Notas de Mejoras ";
                    }
                    ?>
                </label>
            </li>

            <?php if (!empty($id_auditoria)) { ?>
                <li class="row col-4">
                    <label class="col-6">Acción de control:</label>
                    
                    <label class="badge badge-warning">
                        <?php
                        if($id_auditoria > 0) {
                            $obj_audit= new Tauditoria($clink);
                            $obj_audit->SetIdAuditoria($id_auditoria);
                            $obj_audit->Set();
                            $id_tipo_auditoria= $obj_audit->GetIdTipo_auditoria();

                            $obj_tipo= new Ttipo_auditoria($clink);
                            $obj_tipo->SetIdTipo_auditoria($id_tipo_auditoria);
                            $obj_tipo->Set();
                            echo $obj_tipo->GetNombre()." No.".$obj_audit->GetNumero();
                        }    
                        ?>
                </label>
                </li>                
            <?php } ?>
        </ul>
    </nav>    
</div>

<input type="hidden" id="signal" name="signal" value="<?= $signal ?>" />

<input type="hidden" name="menu" id="menu" value="riesgo" />
<input type="hidden" name="if_jefe" id="if_jefe" value="<?= $permit_aprove ?>" />

<input type="hidden" id="day" name="day" value="0" />
<input type="hidden" id="month" name="month" value="0" />

<input type="hidden" id="id_riesgo" value="" />
<input type="hidden" id="id_nota" value="" />
<input type="hidden" id="id_evento" value="0" />
<input type="hidden" id="id_auditoria" name="id_auditoria" value="<?= !empty($id_auditoria) ? $id_auditoria : 0 ?>" />
<input type="hidden" id="id_auditoria_code" name="id_auditoria_code" value="" />
<input type="hidden" id="id_proyecto" value="0" />
<input type="hidden" id="id_indicador" value="0" />
<input type="hidden" id="id_politica" value="0" />

<input type="hidden" id="tipo_plan" name="tipo_plan" value="<?= $tipo_plan ?>" />
<input type="hidden" id="id_plan" name="id_plan" value="<?= $id_plan ?>" />
<input type="hidden" id="id_plan_code" name="id_plan_code" value="<?= $id_plan_code ?>" />
<input type="hidden" id="exect" name="exect" value="<?= $permit_change ? 'add' : 'list' ?>" />

<input type="hidden" id="id_proceso_item" name="id_proceso_item" value="0" />
<input type="hidden" id="id_proceso_item_code" name="id_proceso_item_code" value="0" />

<input type="hidden" id="permit_change" name="permit_change" value="<?= $permit_change ?>" />

<input type="hidden" id="if_entity" value="" />


<?php
$objetivos = null;
$date_aprb = null;
$date_eval = null;
$date_auto_eval = null;

if (isset($obj_user)) unset($obj_user);
$obj_user = new Tusuario($clink);

if (!empty($id_select_prs) && $id_select_prs != -1) {
    $obj_plan = new Tplan_ci($clink);
    $obj_plan->SetTipoPlan($id_tipo_plan);
    $obj_plan->SetYear($year);
    $obj_plan->SetIdProceso($id_select_prs);

    $id_plan = $obj_plan->Set();

    if (empty($id_plan)) {
        $obj_plan->SetTipoPlan($id_tipo_plan);
        $obj_plan->SetYear($year);
        $obj_plan->SetIdProceso($id_select_prs);
        $id_select_prs_code = get_code_from_table('tprocesos', $id_select_prs);
        $obj_plan->set_id_proceso_code($id_select_prs_code);

        $id_plan = $obj_plan->add_plan();
    }

    $objetivos = $obj_plan->GetObjetivo();
    $date_aprb = $obj_plan->GetAprobado();
    $array_aprb = $obj_user->GetEmail($obj_plan->GetIdResponsable_aprb());
    //	if (!is_null($array_aprb)) $array_aprb= ($config->onlypost) ? $array_aprb['cargo'] : $array_aprb['nombre'].' ('.$array_aprb['cargo'].')';

    $auto_evaluacion = $obj_plan->GetAutoEvaluacion();
    $date_auto_eval = $obj_plan->GetAutoEvaluado();
    $array_auto_eval = $obj_user->GetEmail($obj_plan->GetIdResponsable_auto_eval());
    //	if (!is_null($array_auto_eval)) $array_auto_eval= ($config->onlypost) ? $array_auto_eval['cargo'] : $array_auto_eval['nombre'].' ('.$array_auto_eval['cargo'].')';

    $evaluacion = $obj_plan->GetEvaluacion();
    $date_eval = $obj_plan->GetEvaluado();
    $array_eval = $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());
    //	if (!is_null($array_eval)) $array_eval= ($config->onlypost) ? $array_eval['cargo'] : $array_eval['nombre'].' ('.$array_eval['cargo'].')';
}