<?php

/* 
 * Copyright 2017 
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";

require_once "../../php/class/badger.class.php";

$action= null;
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$acc= $_SESSION['acc_archive']; 
$acc_nivel_entity= $_SESSION['nivel_archive2']; 
$acc_nivel_prs= $_SESSION['nivel_archive3'];
$acc_indicaciones= $_SESSION['nivel_archive4'];

$permit_register= null;

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_planarchive();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

    <link rel="icon" type="image/png" href="../../img/gemus_logo.png">

    <title>CONTROL DE DOCUMENTOS</title>

    <?php 
    $dirlibs= "../../";
    require '../../form/inc/_page_init.inc.php'; 
    ?>

    <link rel="stylesheet" href="../../css/menu.css">

    <link rel="stylesheet" href="../../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../../css/table.css?version=">

    <link href="../../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../../libs/spinner-button/spinner-button.js"></script>

    <link href="../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link href="../../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">
    <script type="text/javascript" src="../../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <link href="../../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" src="../../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" charset="utf-8" src="../../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../../js/general.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../../js/form.js?version="></script>

    <style type="text/css">
    body {
        height: 100vh !important;
        overflow: hidden;
    }
    </style>

    <script type="text/javascript">
    var faccords;
    var winSelected = "btn_inicio";

    function btn_inicio() {
        var id_proceso = $('#proceso').val();
        var action= parseInt($('#permit_register').val()) ? 'add' : 'list';

        $('.nav-item').removeClass('active');
        $("#btn_inicio a").addClass('active');  
        $('#mainWin').prop('src', "form/lrecord.php?action="+action+"&if_output=0&id_proceso=" + id_proceso);
    }
    function btn_entrada() {
        var id_proceso = $('#proceso').val();
        var action= parseInt($('#permit_register').val()) ? 'add' : 'list';

        $('.nav-item').removeClass('active');
        $("#btn_entrada a").addClass('active');       
        $('#mainWin').prop('src', "form/frecord.php?action="+action+"&if_output=0&id_proceso=" + id_proceso);
    }
    function btn_salida() {
        var id_proceso = $('#proceso').val();
        var action= parseInt($('#permit_register').val()) ? 'add' : 'list';

        $('.nav-item').removeClass('active');
        $("#btn_salida a").addClass('active'); 
        $('#mainWin').prop('src', "form/frecord.php?action="+action+"&if_output=1&id_proceso=" + id_proceso);
    }
    function btn_list() {
        var id_proceso = $('#proceso').val();
        
        $('.nav-item').removeClass('active');
        $("#btn_config>a").addClass('active');
        $("#btn_list a").addClass('active');
        $('#mainWin').prop('src', "form/larchive.php?action=edit&id_proceso=" + id_proceso);
    }
    function btn_senders() {
        var id_proceso = $('#proceso').val();

        $('.nav-item').removeClass('active');
        $("#btn_config>a").addClass('active');
        $("#btn_senders a").addClass('active');
        $('#mainWin').prop('src', "form/lperson.php?action=edit&id_proceso=" + id_proceso);
    }
    function btn_accords() {
        var id_proceso = $('#proceso').val();

        $('.nav-item').removeClass('active');
        $("#btn_accords a").addClass('active');        
        var url = "form/faccords.php?action=<?=$action?>&if_output=1&id_proceso=" + id_proceso;
        faccords = document.open(url, "_blank",
            "width=1024,height=840,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=yes");
    }
    function btn_organismos() {
        $('.nav-item').removeClass('active');
        $("#btn_config a").addClass('active');
        $("#btn_organismos").addClass('active');
        $('#mainWin').prop('src', "form/lorganismo.php?action=edit");
    }

    function show_proceso(nombre_prs) {
        $('#nombre_prs').text(nombre_prs);
    }
    </script>

    <script type="text/javascript">
    function _dropdown_prs(id) {
        $('#proceso').val(id);

        if (winSelected == "btn_inicio")
            btn_inicio();
        if (winSelected == "btn_entrada")
            btn_entrada();
        if (winSelected == "btn_salida")
            btn_salida();
        if (winSelected == "btn_senders")
            btn_senders();
        if (winSelected == "btn_list")
            btn_list();
    }

    $(document).ready(function() {
        if (parseInt($('#id_proceso').val()) && $('#permit_register').val()) {
            $("#btn_entrada").show();
            $("#btn_salida").show();
            $("#btn_entrada").removeClass('d-none');
            $("#btn_salida").removeClass('d-none'); 
        } else {
            $("#btn_entrada").hide();
            $("#btn_salida").hide();
            $("#btn_entrada").addClass('d-none');
            $("#btn_salida").addClass('d-none'); 
        }

        if ($('#cant_prs').val() > 0) {
            btn_inicio();
        } else {
            $("#btn_inicio").hide();
            $("#btn_accords").hide();

            $('#mainWin').prop('src', "block.html");
        }

        $("#btn_inicio").click(function() {
            winSelected = "btn_inicio";
            btn_inicio();
        });

        $("#btn_entrada a").click(function() {
            winSelected = "btn_entrada";          
            btn_entrada();
        });
        $("#btn_salida a").click(function() {
            winSelected = "btn_salida";          
            btn_salida();
        });

        $("#btn_accords a").click(function() {
            btn_accords();
        });

        $("#btn_senders").click(function() {
            winSelected = "btn_senders";          
            btn_senders();
        });
        $("#btn_organismos").click(function() {
            winSelected = "btn_organismos";
            btn_organismos();
        });
        $("#btn_list").click(function() {
            winSelected = "btn_list";
            btn_list();
        });
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php 
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetYear($year);
    $obj_prs->SetIdUsuario(null);
    $obj_prs->SetIdEntity(null);
    $obj_prs->SetIdProceso(null);
    $obj_prs->set_use_copy_tprocesos(true);
    
    $array_procesos= null;
    $id_select_prs= null;

    $obj_prs->listar_in_order('eq_desc', false, null, false);
    foreach ($obj_prs->array_procesos as $prs) {
        if ($prs['id'] != $_SESSION['id_entity'] && !$prs['local_archive'])
            continue;
        $array_procesos[$prs['id']]= $prs;

        if (empty($id_proceso) || $id_proceso == -1) {
            $id_proceso= $prs['id'];
            $id_select_prs= $id_proceso;
        }   
        if (!empty($id_proceso) && $id_proceso == $prs['id'])
            $id_select_prs= $prs['id'];
    }

    $obj_prs= new Tproceso($clink);    
    if (!empty($id_proceso)) {
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
        $conectado= $obj_prs->GetConectado();
        $tipo= $obj_prs->GetTipo();             
    }
    ?>

    <?php
    if ($acc == _ACCESO_ALTA && !empty($id_proceso)) {
        $permit_register= true;
    }
    if ($acc == _ACCESO_MEDIA && $id_proceso == $_SESSION['id_entity']) {
        $permit_register= $acc_nivel_entity == _USER_REGISTRO_ARCH ? true: false;
    }
    if (($acc >= _ACCESO_BAJA && !empty($id_proceso)) 
        && ($id_proceso == $_SESSION['usuario_proceso_id'] 
            || ($id_proceso != $_SESSION['id_entity'] && array_key_exists($id_proceso, $array_procesos)))) {
        $permit_register= $acc_nivel_prs == _USER_REGISTRO_ARCH ? true : false;
    }

    unset($obj_prs);
    $obj_prs= new Tproceso($clink);
    ?>

    <!-- Fixed navbar -->

    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>              
                <a href="#" class="navd-header">CONTROL DE DOCUMENTOS</a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navd-collapse">
                        <?php 
                        $i= 0;
                        if (!empty($acc)) {
                        ?>
                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarUnidades" data-toggle="collapse"
                                aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-industry"></i>Unidades Organizativas<b class="caret"></b>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarUnidades">
                                <?php  
                                $i= 0;
                                foreach ($array_procesos as  $array) {
                                    ++$i;
                                    require "../../form/inc/_tablero_tabs_proceso.inc.php";
                                } 
                                ?>
                            </ul>
                        </li>
                        <?php } ?>

                        <input type="hidden" id="proceso" name="proceso" value="<?=!empty($id_proceso) ? $id_proceso : 0?>" />
                        <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso ? $id_proceso : 0?>" />

                        <li id="btn_inicio" class="nav-item">
                            <a href="#">Registros</a>
                        </li>

                        <?php
                        $hidden= !$permit_register ? "d-none" : "";
                        ?>
                        <li id="btn_entrada" class="nav-item <?=$hidden?>">
                            <a href="#">
                                Entrada
                            </a>
                        </li>
                        <li id="btn_salida" class="nav-item <?=$hidden?>">
                            <a href="#">
                                Salida
                            </a>
                        </li>

                        <?php
                        $hidden= !is_null($permit_register) >= _SUPERUSUARIO ? '' : 'd-none';
                        ?>                        
                        <li id="btn_accords" class="nav-item <?=$hidden?>">
                            <a href="#">
                                Seguimiento
                            </a>
                        </li>
                        
                        <?php
                        $hidden= $permit_register || $_SESSION['nivel'] >= _SUPERUSUARIO ? '' : 'd-none';
                        ?>
                        <li id="btn_config" class="navd-dropdown <?=$hidden?>">
                            <a  href="#navbarConfiguracion" class="dropdown-toggle" data-toggle="collapse" aria-expanded="false">
                                Configuración <span class="caret"></span>
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarConfiguracion">
                                <li class="nav-item">
                                    <a id="btn_list" href="#">
                                        Archivos procesados
                                    </a>
                                </li>
                                <div class="dropdown-divider"></div>
                                <li class="nav-item">
                                    <a id="btn_organismos" href="#">
                                        Organismos (OACE, OSDE, GAE)
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a id="btn_senders" href="#">
                                        Remitentes/Destinatarios
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

    <input type="hidden" id="cant_prs" name="cant_prs" value="<?=$i?>" />
    <input type="hidden" id="permit_register" name="permit_register" value="<?=$permit_register ? 1 : 0?>" />

    <iframe id="mainWin" class="app-body container-fluid sevenbar"></iframe> <!-- /container -->

</body>

</html>