<?php

/*
 * Copyright 2017
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */
?>
<?php
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
?>

<div id="navbar-secondary">
    <nav class="navd-content">
        <div class="navd-container">
            <div id="dismiss" class="dismiss">
                <i class="fa fa-arrow-left"></i>
            </div>           
            <a href="#" class="navd-header">
                TAREAS INDIVIDUALES
            </a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navd-collapse">

                    <?php if ($signal == "jkanban") { ?>}
                    <li>
                        <a href="#" onclick="displayWindow('div-ajax-panel');" title="nueva columna">
                            <i class="fa fa-plus"></i>Agregar columna
                        </a>
                    </li>
                    <?php }  ?>


                    <li class="navd-dropdown">
                    <a class="dropdown-toggle" href="#navbarUsuarios" data-toggle="collapse" aria-expanded="false">
                            <i class="fa fa-user"></i>Usuarios<span class="caret"></span>
                        </a>

                        <ul class="navd-dropdown-menu" id="navbarUsuarios">
                            <li class="<?php if ($id_calendar == $_SESSION['id_usuario']) echo "active"?>">
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
                            <li class="nav-item <?php if ($id == $id_calendar) echo "active" ?>"
                                onmouseover="Tip('<?=addslashes($proceso)?>')" onmouseout="UnTip()">
                                <a href="#" onclick="loadpage(<?= $id ?>)" onmouseover="Tip('<?=addslashes($proceso)?>')"
                                    onmouseout="UnTip()">
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
                        ?>
                        </ul>
                    </li>

                    <?php
                    $use_select_year= true;
                    $use_select_month= true;
                    $use_select_day= false;
                    require "../form/inc/_dropdown_date.inc.php";
                    ?>
                </ul>

                <div class="navd-end">
                    <ul class="navbar-nav mr-auto">
                        <li>
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


<div id="navbar-third" class="app-nav d-none d-md-block">
    <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
        <li class="col-auto">
            <label class="badge badge-success">
                <?= (int)$day ?> de <?= $meses_array[(int)$month] ?>, <?= $year ?>
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
        <li>
            <div class="col-sm-12">
                <label class="badge badge-danger">
                    <?=$nombre_user?>
                </label>
            </div>
        </li>
    </ul>
</div>
</nav>

<input type="hidden" id="menu" name="menu" value="tablero" />
<input type="hidden" name="exect" id="exect" value="<?= $if_jefe ? 'add' : 'list' ?>" />

<input type="hidden" id="id_calendar" name="id_calendar" value="<?= $id_calendar ?>" />

<input type="hidden" id="id_proyecto" name="id_proyecto" value="<?= $id_proyecto ?>" />
<input type="hidden" id="proyecto" name="proyecto" value="0" />

<input type="hidden" id="id_programa" name="id_programa" value="<?= $id_programa ?>" />
<input type="hidden" id="programa" name="programa" value="0" />

<input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />
<input type="hidden" id="proceso" name="proceso" value="0" />

<input type="hidden" id="fecha_origen" name="fecha_origen"
    value="<?= $pStart ? date('d/m/Y', strtotime($pStart)) : null ?>" />
<input type="hidden" id="fecha_termino" name="fecha_termino"
    value="<?= $pEnd ? date('d/m/Y', strtotime($pEnd)) : null ?>" />

<input type="hidden" id="year" name="year" value="<?= $year ?>" />
<input type="hidden" id="month" name="month" value="<?= $month ?>" />
<input type="hidden" id="day" name="day" value="<?= $day ?>" />

<input type="hidden" id="tarea" name="tarea" value="" />
<input type="hidden" id="id_tarea" name="id_tarea" value="" />
<input type="hidden" id="descripcion" name="descripcion" value="" />
<input type="hidden" id="responsable" name="responsable" value="" />
<input type="hidden" id="fecha_inicio" name="fecha_inicio" value="" />
<input type="hidden" id="fecha_plan" name="fecha_plan" value="" />
<input type="hidden" id="avance" name="avance" value="" />

<input type="hidden" id="if_jefe" name="if_jefe" value="<?= $if_jefe ? 1 : 0 ?>" />