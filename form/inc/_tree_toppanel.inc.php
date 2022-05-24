<?php
$obj_prs= new Tproceso($clink);

$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$id_proceso_code= $obj_prs->get_id_proceso_code();
$id_proceso_sup= $obj_prs->GetIdProceso_sup();
$conectado= $obj_prs->GetConectado();
$type= $obj_prs->GetTipo();

$nombre= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];

$nombre_sup= null;

if (!empty($id_proceso_sup) && $signal == 'objetivo_sup') {
    $obj_prs->SetIdProceso($id_proceso_sup);
    $obj_prs->Set();
    $conectado_sup= $obj_prs->GetConectado();
    $type_sup= $obj_prs->GetTipo();
    $nombre_sup= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
}

if ($signal != "objetivo_ci") {
    $edit= ($action == 'edit' || $action == 'add') ? true : false;
    if ($edit && ($id_proceso != $_SESSION['local_proceso_id'] && $conectado != _LAN)) 
        $edit= false;
    if ($edit && ($_SESSION['nivel'] < _SUPERUSUARIO && $_SESSION['id_usuario'] != $obj_prs->GetIdResponsable())) 
        $edit= false;
    if ($edit && (($id_proceso_sup == $_SESSION['local_proceso_id'] || empty($id_proceso_sup)) && $conectado == _NO_LOCAL)) 
        $edit= true;
    if ($signal == 'objetivo_sup' && $_SESSION['nivel'] >= _SUPERUSUARIO) 
        $edit= empty($id_proceso_sup) ? false : true;
} else 
    $edit= (($action == 'edit' || $action == 'add') || $permit_change) ? true : false;


$obj_peso= new Tpeso_calculo($clink);
$obj_user= new Tusuario($clink);
?>

<?php
if ($signal == 'inductor') {
    $help = '../help/10_inductores.htm#10_12';
    $title = 'OBJETIVOS DE TRABAJO';
}
if ($signal == 'objetivo') {
    $help = '../help/09_objetivos.htm#09_11';
    $title = 'OBJETIVOS ESTRAT&Eacute;GICOS';
}
if ($signal == 'politica') {
    $help = '../help/07_lineamientos.htm#07_9';
    $title = 'POLITICAS O LINEAMIENTOS';
}
if ($signal == 'perspectiva') {
    $help = '../help/08_perspectivas.htm#08_10';
    $title = 'PERSPECTIVAS';
}
if ($signal == 'programa') {
    $help = '../help/13_programas.htm#13_24';
    $title = 'PROGRAMA';
}
if ($signal == 'objetivo_sup') {
    $help = '../help/09_objetivos.htm#09_11.1';
    $title = 'OBJETIVOS SUPERIORES';
}
if ($signal == 'objetivo_ci') {
    $help = '../help/14_riesgos.htm#25.2';
    $title = 'OBJETIVOS DE CONTROL';
}
?>

<!-- Docs master nav -->
<div id="navbar-secondary">
    <nav class="navd-content">
        <div class="navd-container">
            <div id="dismiss" class="dismiss">
                <i class="fa fa-arrow-left"></i>
            </div>   
            <a href="#" class="navd-header">
                <?=$title?>
            </a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navd-collapse">
                    <?php if ($action == 'edit' && $edit) { ?>
                    <li class="d-none d-md-block">
                        <a href="#" class="" onclick="add()" title="nuevo <?=$signal?>">
                            <i class="fa fa-plus"></i>Agregar
                        </a>
                    </li>
                    <?php } ?>

                    <li class="nav-item">
                        <a href="#" class="" onclick="recompute()">
                            <i class="fa fa-recycle"></i>Recalcular
                        </a>
                    </li>

                    <?php 
                    if ($signal != 'objetivo_sup') {
                        $top_list_option= "seleccione........";
                        $id_list_prs= null;
                        $order_list_prs= 'eq_asc_desc';
                        $reject_connected= false;
                        $id_select_prs= $id_proceso;
                        $in_building= false;

                        $restrict_prs= !$config->dpto_with_objetive ? array(_TIPO_GRUPO) : array(_TIPO_ARC);
                        require "inc/_dropdown_prs.inc.php"; 
                    }
                    ?>

                    <?php 
                    $use_select_year= true;
                    $use_select_month= true;
                    require "inc/_dropdown_date.inc.php"; 
                    ?>

                    <?php if ($signal == 'politica') { ?>
                    <li class="nav-item">
                        <a href="#" class="" onclick="show_politica_filter();">
                            <i class="fa fa-filter"></i>Filtrar
                        </a>
                    </li>
                    <?php } ?>

                    <li class="nav-item d-none d-lg-block">
                        <a href="#" class="" onclick="imprimir()">
                            <i class="fa fa-print"></i>
                            Imprimir
                        </a>
                    </li>
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <a href="#" onclick="open_help_window('<?=$help?>')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>

                        <?php if ($signal == 'objetivo_ci') { ?>
                        <li class="nav-item">
                            <a href="#" onclick="closep()">
                                <i class="fa fa-close"></i>Cerrar
                            </a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>    
    </nav>
</div>


<div id="navbar-third" class="app-nav d-none d-md-block">
    <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
        <li class="col">
            <label class="badge badge-success">
                <?=$dayNames[$iday]?> <?=$day?>, <?=$meses_array[(int)$month]?> <?=$year?>
            </label>
        </li>

        <li class="col">
            <div class="row">
                <label class="label ml-3">Muestra:</label>
                <div id="nshow" class="badge badge-warning">0</div>                    
            </div>

        </li>

        <?php if ($signal == 'politica') { ?>
        <li class="col">
            <div class="row">
                <label class="label ml-3">Ocultas:</label>
                <div id="nhide" class="badge badge-warning">0</div>                    
            </div>
        </li>
        <?php } ?>

        <li class="col-auto">
            <label class="badge badge-danger">
                <?php if ($_connect) { ?><i class="fa fa-wifi"></i><?php } ?>
                <?= !is_null($nombre_sup) ? $nombre_sup : $nombre ?>
            </label>
        </li>
    </ul>
</div>


<form name="treeForm" id="treeForm" action='' method="post"></form>

<input form="treeForm" type="hidden" name="exect" id="exect" value="<?= $action ?>" />
<input form="treeForm" type="hidden" name="menu" id="menu" value="tablero" />
<input form="treeForm" type="hidden" name="signal" id="signal" value="<?= $signal ?>" />

<input form="treeForm" type="hidden" id="id_tablero" value="<?= $id_tablero ?>" />
<input form="treeForm" type="hidden" id=tablero value="<?= $id_tablero ?>" />
<input form="treeForm" type="hidden" id=actual_year value="<?= $actual_year ?>" />

<input form="treeForm" type="hidden" id="id_indicador" name="id_indicador" value=0 />
<input form="treeForm" type="hidden" id="id_persp" name="id_persp" value=0 />
<input form="treeForm" type="hidden" id="id_user_real" name="id_user_real" value=0 />
<input form="treeForm" type="hidden" id="id_user_plan" name="id_user_plan" value=0 />
<input form="treeForm" type="hidden" id="trend" name="trend" value=0 />

<input form="treeForm" type="hidden" id="cumulative" value="" />
<input form="treeForm" type="hidden" id="formulated" value="" />

<input form="treeForm" type="hidden" id="_id" name="_id" value="" />
<input form="treeForm" type="hidden" id="_item" name="_item" value="" />

<input form="treeForm" type="hidden" id="_id_sup" name="_id_sup" value="" />
<input form="treeForm" type="hidden" id="_item_sup" name="_item_sup" value="" />

<input form="treeForm" type="hidden" id="_if_titulo" value=0 />
<input form="treeForm" type="hidden" id="_if_inner" value=0 />

<input form="treeForm" type="hidden" id="_observacion_item" value="" />
<input form="treeForm" type="hidden" id="_registro_item" value="" />
<input form="treeForm" type="hidden" id="_descripcion_item" value="" />

<input form="treeForm" type="hidden" id="id_usuario" name="id_usuario" value=<?= $_SESSION['id_usuario'] ?> />
<input form="treeForm" type="hidden" id="nivel" name="nivel" value=<?= $_SESSION['nivel'] ?> />

<input form="treeForm" type="hidden" id="if_control_interno" name="if_control_interno"
    value="<?= !empty($if_control_interno) ? 1 : 0 ?>" />

<input type="hidden" form="treeForm" id="_date_task" name="_date_task" value="" />
<input type="hidden" form="treeForm" id="_titulo_task" name="_titulo_task" value="" />
<input type="hidden" form="treeForm" id="_descripcion_task" name="_descripcion_task" value="" />
<input type="hidden" form="treeForm" id="_responsable_task" name="_responsable_task" value="" />

<input type="hidden" form="treeForm" id="_observacion_task" name="_observacion_task" value="" />
<input type="hidden" form="treeForm" id="_registro_task" value="" />

<input type="hidden" form="treeForm" id="_if_entity" value="" />

<input type="hidden" id="day" name="day" value="<?=$day?>" />

<?php if ($signal != 'objetivo_sup') { ?>
<input type="hidden" form="treeForm" name="proceso" id="proceso" value="<?=$_SESSION['local_proceso_id'] ?>" />
<?php } ?>


<?php
if (empty($month) || $month == -1 || $month > 13) 
    $month= 12;

$obj_peso->SetYear($year);
$obj_peso->SetMonth($month);
$obj_peso->SetDay($day);

$obj_peso->SetIdProceso($id_proceso);
$obj_peso->set_id_proceso_code($id_proceso_code);
?>
