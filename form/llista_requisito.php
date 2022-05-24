<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista_requisito.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'flista';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add' || $action == 'update') 
    $action= 'edit';

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$obj_prs= new Tproceso($clink);
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : -1;

if (!empty($id_proceso) && $id_proceso > 0) {
   $obj_prs->SetIdProceso($id_proceso);
   $obj_prs->Set();
   $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
   $conectado= $obj_prs->GetConectado();
   $tipo= $obj_prs->GetTipo();
}

if ($id_proceso == -1) {
    $nombre_prs= "Todas las Unidades Organizativas ...";
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
}
else {
    $obj= new Tlista_requisito($clink);
}

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : $obj->GetIdLista();
if (empty($id_lista)) 
    $id_lista= 0;

$id_tipo_lista= !empty($_GET['id_tipo_lista']) ? $_GET['id_tipo_lista'] : null;
$componente= !empty($_GET['componente']) ? $_GET['componente'] : null;
$id_capitulo= !empty($_GET['id_capitulo']) ? $_GET['id_capitulo'] : null;
$id_subcapitulo= !empty($_GET['id_subcapitulo']) ? $_GET['id_subcapitulo'] : null;
$numero= !empty($_GET['numero']) ? $_GET['numero'] : 0;

$init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$obj->SetYear($year);

$obj_lista= new Tlista($clink);
$obj_lista->SetIdLista($id_lista);
$obj_lista->Set();
$nombre_lista= $obj_lista->GetNombre();
$_inicio= $obj_lista->GetInicio();
$_fin= $obj_lista->GetFin();

// determinar si el usuario es jefe
unset($obj_prs);
$obj_prs= new Tproceso($clink);
!empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
$array_chief_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], null);

$if_jefe= false;
$acc= $_SESSION['acc_planaudit'];
if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
    $if_jefe= true;
if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
    $if_jefe= true;
if ($acc == _ACCESO_BAJA && ($id_proceso == $_SESSION['usuario_proceso_id'] && $id_proceso != $_SESSION['id_entity']))
    $if_jefe= true;
/*
if ($acc == _ACCESO_MEDIA && ($id_proceso == $_SESSION['local_proceso_id']))
    $if_jefe= true;
*/

require_once 'inc/llista_requisito.inc.php';

$url_page= "../form/llista_requisito.php?signal=$signal&action=$action&menu=tipo_lista&year=$year";
$url_page.= "&id_proceso=$id_proceso&id_lista=$id_lista&if_jefe=$if_jefe&componente=$componente";
$url_page.= "&id_capitulo=$id_capitulo&id_subcapitulo=$id_subcapitulo&id_tipo_lista=$id_tipo_lista";
$url_page.= "&init_row_temporary=$init_row_temporary";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE REQUISITOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../libs/btn-toolbar/btn-toolbar.css" />
    <script type="text/javascript" src="../libs/btn-toolbar/btn-toolbar.js"></script>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/lista.css" />
    <script type="text/javascript" src="../js/lista.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript">
        function add() {
            var url = 'flista_requisito.php' + set_url();
            self.location.href = url;
        }

        function edit(id) {
            var exect = $('#exect').val();
            var id_lista = $('#id_lista').val();

            var action = (exect == 'add' || exect == 'update' || exect == 'edit') ? 'edit' : 'list';
            var url = '../php/lista_requisito.interface.php?menu=frequisito&id=' + id + '&action=' + action +
                '&if_jefe=<?=$if_jefe?>';
            url += '&id_lista=' + id_lista;
            self.location.href = url;
        }

        function _delete(id) {
            var id_lista = $('#id_lista').val();
            var url = '../php/lista_requisito.interface.php?menu=frequisito&id=' + id + '&action=delete' +
                '&if_jefe=<?=$if_jefe?>';
            url += '&id_lista=' + id_lista;
            self.location.href = url;
        }

        function imprimir() {
            var url = '../print/llista_requisito.php' + set_url();

            prnpage = window.open(url, "IMPRIMIENDO ESTRUCTURA DE GUIA DE CONTROL",
                "width=900,height=600,toolbar=no,location=no, scrollbars=yes");              
        }

        function form_filter() {
            var action= $('#exect').val();

            var _url = 'ajax/flista_requisito_filter.ajax.php?action='+action + '&year=' + <?=$year?> + '&inicio=' + <?=$_inicio?>;
                _url += '&fin=' + <?=$_fin?>+'&componente=' + <?=$componente ? $componente : 0?> + '&id_capitulo=' + <?=$id_capitulo ? $id_capitulo : 0?>;
                _url += '&id_subcapitulo=' + <?=$id_subcapitulo ? $id_subcapitulo : 0?>;

            var valores = '';
            var title = "FILTRADO DE REQUISITOS";

            $.ajax({
                url: _url,
                method: 'GET',

                cache: false,
                processData: false,
                evalScripts: true,

                beforeSend: function() {
                    $('#div-filter-ajax').html("<div class='loading-indicator'>Procesando, espere por favor...</div>");
                },
                success: function(response) {
                    $('#div-filter-ajax').html(response);

                    displayFloatingDiv('div-panel-filter', title, 60, 0, 10, 20);

                    refresh_ajax_select('', <?= !empty($id_capitulo) ? $id_capitulo : 0 ?>, <?= $numero ?>);
                    <?php if($id_capitulo > 0) { ?>
                        refresh_ajax_select_capitulo('', <?= !empty($id_subcapitulo) ? $id_subcapitulo : 0 ?>, <?= $numero ?>);   
                    <?php } ?>
                },
                error: function(xhr, status) {
                    alert('Disculpe, existió un problema en la conexión AJAX');
                }
            });     
        }

        function filtrar() {
            $('#year').val($('#_year').val());
            $('#id_componente').val($('#componente').val());
            $('#id_capitulo').val($('#capitulo').val());
            $('#id_subcapitulo').val($('#subcapitulo').val());

            var url = set_url();
            self.location.href = 'llista_requisito.php' + url;
        }

        function closep() {
            var url = 'llista.php?action=<?=$action?>&id_proceso=<?=$id_proceso?>&year=<?=$year?>';
            self.location.href = url;
        }

        function showWindow(id_requisito) {
            var action = $('#exect').val();
            var id_lista = $('#id_lista').val();
            var id_proceso = $('#id_proceso').val();
            var year = $('#year').val();

            var url = '../form/fdocument.php?action=' + action + '&id_proceso=' + id_proceso + '&year=' + year;
            url += '&id_lista=' + id_lista + '&id_requisito=' + id_requisito;

            win_document = document.open(url, "_blank",
                "width=900,height=640,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=no");;
        }
    </script>

    <script type="text/javascript" charset="utf-8">
        function refreshp() {
            var url = set_url();
            url+= '&init_row_temporary='+$('#init_row_temporary').val();

            self.location.href = 'llista_requisito.php' + url;        
        }

        function refreshTab(id) {
            if (id < 0)
                id = 0;
            $('#init_row_temporary').val(id);
            refreshp();
        }

        function _dropdown_prs(id) {
            $('#id_proceso').val(id);
            refreshp();
        }

        $(document).ready(function() {
            InitDragDrop();

            try {
                InitBtnToolbar(<?=$init_row_temporary+1?>);
            } catch (e) {
                ;
            }

            $('#componente').on('change', function() {
                refresh_ajax_select('', 0, 0);
            });

            <?php if (!is_null($error)) { ?>
            alert("<?= str_replace("\n", " ", $error) ?>");
            <?php } ?>
        });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>  

                <a href="#" class="navd-header">
                    LISTADO DE REQUISITOS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <li class="nav-item">
                            <div class="badge badge-warning mt-3">
                                <?= textparse($nombre_lista) ?>
                            </div>
                        </li>
                        
                        <?php if ($if_jefe) { ?>
                        <li class="nav-item d-none d-md-block">
                            <a href="#" class="" onclick="add()">
                            <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarUsuarios" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-industry"></i>Unidades Organizativas
                            </a>

                            <ul class="navd-dropdown-menu" id="navbarUsuarios">
                                <!-- procesos ---------------------------------------------------------------------------------- -->
                                <?php
                                $obj_prs= new Tproceso_item($clink);
                                $obj_prs->SetYear($year);
                                $obj_prs->SetIdLista($id_lista);
                                $array_procesos= $obj_prs->getProcesoLista();
                                ?>

                                <li class="nav-item">
                                    <a href="#" class="<?php if ($id_proceso == -1) echo "active"?>" onclick="_dropdown_prs(-1)" title="Todas las Unidades Organizativas">
                                        Todas las Unidades Organizativas ... 
                                    </a>
                                </li> 

                                <?php
                                if ($id_proceso != -1 && !array_key_exists($id_proceso, (array)$array_procesos))
                                    $id_proceso= null;

                                foreach ($array_procesos as  $array) {
                                    if (empty($array['id']))
                                        continue;
                                    if (empty($id_proceso))
                                        $id_proceso= $array['id'];

                                    include "../form/inc/_tablero_tabs_proceso.inc.php";
                                }

                                $_SESSION['id_proceso']= $id_proceso;

                                if (!empty($id_proceso) && $id_proceso != -1) {
                                    $obj_prs = new Tproceso($clink);
                                    $obj_prs->SetYear($year);
                                    $obj_prs->SetIdProceso($id_proceso);
                                    $obj_prs->Set();

                                    $conectado= $obj_prs->GetConectado();
                                    $nombre_prs_title= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];

                                    if ($obj_prs->GetConectado() != _NO_LOCAL)
                                        $id_proceso_asigna= $id_proceso;
                                    else {
                                        $id_proceso_asigna= $obj_prs->get_proceso_top($id_proceso, null, true);
                                    }
                                } elseif ($id_proceso == -1) {
                                    $nombre_prs_title= "Todas las Unidades Organizativas ...";
                                }
                                ?>
                            </ul>
                        </li>

                        <li class="nav-item d-none d-md-block">
                            <a href="#" class="" onclick="form_filter();">
                                <i class="fa fa-filter"></i>Filtrar
                            </a>
                        </li>
                        
                        <li class="nav-item d-none d-md-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li class="nav-item">
                                <a href="#" onclick="open_help_window('../help/manual.html#listas')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" onclick="self.location.href = '<?php prev_page() ?>'">
                                    <i class="fa fa-close"></i>Cerrar
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
            <li class="col">
                <label class="badge badge-success">
                    <?=$year?>
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
                    <label class="label ml-3">Ocultos:</label>
                    <div id="nhide" class="badge badge-warning">0</div>
                </div>
            </li>

            <li class="col">
                <div class="col-sm-12">
                    <label class="badge badge-danger">
                        <?php if ($conectado && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                            class="fa fa-wifi"></i><?php } ?>
                        <?=$nombre_prs_title?>
                    </label>
                </div>
            </li>
        </ul>
    </div>

    <?php
    $nshow= 0;
    $nhide= 0;
    $_componente= null;                 
    $_id_capitulo= null;
    $_id_tipo_lista= null;

    $obj= new Tlista_requisito($clink);
    $obj->SetIdLista($id_lista);
    $obj->SetYear($year == -1 ? null : $year);
    $obj->SetIdProceso(is_null($id_proceso) || $id_proceso == -1 ? null : $id_proceso);
    
    $obj->init_row_temporary= $init_row_temporary;
    $obj->limited= true;
    $obj->listar(null, false);
    $nshow= $obj->GetCantidad();

    $max_num_pages= $obj->max_num_pages;
    ?>

    <div class="row app-pagination d-none d-md-block">
        <div class="toolbar">
            <div class="toolbar-center">
                <div class="center-inside">
                    <?php for ($i=0; $i < $max_num_pages; $i++) { ?>
                    <a href="javascript:refreshTab(<?=$i?>)" class="btn btn-default <?php if ($i == $init_row_temporary) echo "active"?>">
                        <?=($i+1)?>
                    </a>
                    <?php } ?>
                </div>
            </div>

            <div class="btn-left">
                <div class="btn btn-default double">
                    <i class="fa fa-angle-double-left fa-2x"></i>
                </div>
                <div class="btn btn-default single">
                    <i class="fa fa-angle-left fa-2x"></i>
                </div>
            </div>

            <div class="btn-right">
                <div class="btn btn-default single">
                    <i class="fa fa-angle-right fa-2x"></i>
                </div>
                <div class="btn btn-default double">
                    <i class="fa fa-angle-double-right fa-2x"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="app-body container-fluid table threebar">
        <form action='javascript:' method=post class="form-horizontal">
            <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
            <input type="hidden" name="menu" id="menu" value="tipo_lista" />
            <input type="hidden" name="year" id="year" value="<?=$year?>" />

            <input type="hidden" name="id_auditoria" id="id_auditoria" value="0" />
            <input type="hidden" name="id_proceso" id="id_proceso" value="<?=$id_proceso?>" />
            <input type="hidden" name="id_lista" id="id_lista" value="<?= $id_lista ?>" />

            <input type="hidden" name="id_tipo_lista" id="id_tipo_lista"
                value="<?= !empty($id_tipo_lista) ? $id_tipo_lista : 0 ?>" />
            <input type="hidden" name="id_componente" id="id_componente"
                value="<?= !empty($componente) ? $componente : 0 ?>" />
            <input type="hidden" name="id_capitulo" id="id_capitulo"
                value="<?= !empty($id_capitulo) ? $id_capitulo : 0 ?>" />
            <input type="hidden" name="id_subapitulo" id="id_subcapitulo"
                value="<?= !empty($id_subcapitulo) ? $id_subcapitulo : 0 ?>" />
            
            <input type= "hidden" id="if_jefe" name= "if_jefe" value="<?=$if_jefe?>" />
            <input type= "hidden" id="inicio" name= "inicio" value="<?=$_inicio?>" />
            <input type= "hidden" id="fin" name= "fin" value="<?=$_fin?>" />

            <input type="hidden" name="numero" id="numero" value="<?= $numero ?>" />

            <input type="hidden" name="proceso" id="proceso" value="<?=$id_proceso?>" />

            <input type="hidden" id="init_row_temporary" name="init_row_temporary" value="<?=$init_row_temporary?>" />

            <table class="table table-hover table-striped" 
                data-toggle="table" 
                data-toolbar="#toolbar"
                data-search="true" 
                data-show-columns="true">
                <thead>
                    <tr>
                        <th data-field="id">No.</th>
                        <?php if ($if_jefe) { ?>
                        <th data-field="icons"></th>
                        <?php } ?>
                        <th data-field="peso">Peso</th>
                        <th data-field="periodo">Periodo</th>
                        <th data-field="nombre">Requisitos a Evaluar</th>
                        <th data-field="descripcion">Evidencias</th>
                        <th data-field="indicacion">Indicaciones al Equipo Evaluador</th>
                        <th data-field="procesos">Unidades Organizativas</th>
                        <th data-field="registro">Registro</th>
                    </tr>
                </thead>

                <tbody>
                <?php 
                $obj->limited= false;
                for ($_componente = 1; $_componente < _MAX_COMPONENTES_CI; $_componente++) { 
                    if (!empty($componente) && $_componente != $componente)
                        continue;

                    $obj->SetComponente($_componente);
                    $obj->SetIdCapitulo(null);
                    $obj->SetIdTipo_lista(null);
                    
                    $obj->listar(null, false);
                    $cant= $obj->GetCantidad();
                    
                    if (empty($cant))
                        continue;
                ?>
                    <tr>
                        <td colspan="<?=$if_jefe ? 9 : 8?>"  class="colspan">
                            <?=$_componente.') '. $Tambiente_control_array[$_componente]?>
                        </td>
                    </tr>

                    <?php
                    $numero= $_componente;
                    lista_requisito($_componente, 0);

                    if (isset($obj_tipo1)) 
                        unset($obj_tipo1);
                    $obj_tipo1= new Ttipo_lista($clink);

                    $obj_tipo1->SetYear($year == -1 ? null : $year);
                    $obj_tipo1->SetIdLista($id_lista);
                    $obj_tipo1->SetComponente($_componente);
                    $result1= $obj_tipo1->listar();

                    while ($row1= $clink->fetch_array($result1)) {
                        if (!empty($row1['id_capitulo']))
                            continue;
                        $_id_capitulo= $row1['id'];
                        if (!empty($id_capitulo) && $_id_capitulo != $id_capitulo)
                            continue;

                        $obj->SetComponente($_componente);
                        $obj->SetIdTipo_lista(null);
                        $obj->SetIdCapitulo($_id_capitulo);

                        $obj->listar_all();
                        $cant1= $obj->GetCantidad();
                        
                        if (empty($cant1))
                            continue;                            
                    ?>
                        <tr>
                            <td colspan="<?=$if_jefe ? 9 : 8?>"  class="colspan">
                                <?=$row1['numero'].") ". $row1['nombre']?>
                            </td>
                        </tr>

                        <?php
                        $numero= $row1['numero'];
                        lista_requisito($_componente, $_id_capitulo);

                        if (isset($obj_tipo2)) 
                            unset($obj_tipo2);
                        $obj_tipo2= new Ttipo_lista($clink);

                        $obj_tipo2->SetYear($year == -1 ? null : $year);
                        $obj_tipo2->SetIdLista($id_lista);
                        $obj_tipo2->SetComponente($_componente);
                        $obj_tipo2->SetIdCapitulo($_id_capitulo);
                        $result2= $obj_tipo2->listar();

                        while ($row2= $clink->fetch_array($result2)) {
                            $_id_subcapitulo= $row2['id'];
                            if (!empty($id_subcapitulo) && $_id_subcapitulo != $id_subcapitulo)
                                continue;
                            ?>
                            <tr>
                                <td colspan="<?=$if_jefe ? 9 : 8?>"  class="colspan">
                                    <?=$row2['numero'].") ". $row2['nombre']?>
                                </td>
                            </tr>
                            <?php
                            $numero= $row2['numero'];
                            lista_requisito($_componente, $_id_subcapitulo);
                        ?>                    

                <?php } } }?>    
                </tbody>
            </table>

            <input type="hidden" id="cant" name="cant" value="<?= $i ?>">

            <script type="text/javascript" language="JavaScript">
            document.getElementById('nshow').innerHTML = '<?=$nshow?>';
            document.getElementById('nhide').innerHTML = '<?=$nhide?>';
            </script>  
        </form> 
    </div>
        
    <!-- panel-requisito -->
    <div id="div-filter-ajax">
    </div>        
    
</body>

</html>