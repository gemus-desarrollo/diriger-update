<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/auditoria.class.php";
require_once "../php/class/document.class.php";
require_once "../php/class/register_nota.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/code.class.php";

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'flista';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_auditoria= !is_null($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;
$id_tipo_lista= !is_null($_GET['id_tipo_lista']) && strlen($_GET['id_tipo_lista']) > 0 ? $_GET['id_tipo_lista'] : null;
$id_lista= !is_null($_GET['id_lista']) ? $_GET['id_lista'] : null;
$componente= !is_null($_GET['componente']) && strlen($_GET['componente']) > 0 ? $_GET['componente'] : null;
$id_capitulo= !is_null($_GET['id_capitulo']) && strlen($_GET['id_capitulo']) > 0 ? $_GET['id_capitulo'] : null;
$id_subcapitulo= !is_null($_GET['id_subcapitulo']) && strlen($_GET['id_subcapitulo']) > 0 ? $_GET['id_subcapitulo'] : null;
$numero= !empty($_GET['numero']) ? $_GET['numero'] : 0;

$init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;

$obj_lista= new Tlista($clink);
$obj_lista->SetYear($year);
$obj_lista->Set($id_lista);
$id_lista_code= $obj_lista->get_id_code();
$nombre_lista= $obj_lista->GetNombre();
$_inicio= $obj_lista->GetInicio();
$_fin= $obj_lista->GetFin();

$obj= new Tlista_requisito($clink);
$obj->SetYear($year);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$nombre_prs= $obj_prs->GetNombre();
$conectado= $obj_prs->GetConectado();
$tipo_prs= $obj_prs->GetTipo();

$nombre_prs.= ", ".$Ttipo_proceso_array[$tipo_prs];

// ----------- auditoria ------------------------------------
$obj_audit= new Tauditoria($clink);
$obj_audit->SetIdAuditoria($id_auditoria);
$obj_audit->Set();
$auditoria= $obj_audit->GetNombre();

$obj_reg= new Tregister_nota($clink);
$obj_reg->SetYear($year);
$obj_reg->SetIdAuditoria($id_auditoria);
$obj_reg->SetIdProceso($id_proceso);
$obj_reg->SetIdLista($id_lista);

$obj_reg->SetChkApply(false);

//----------------------------------------------------------

$if_jefe= false;
$acc= $_SESSION['acc_planaudit'];
if ($acc || $_SESSION['nivel'] >= _SUPERUSUARIO)
    $if_jefe= true;

require_once 'inc/flista_requisito_status.inc.php';

$array_files= null;

$url_page= "../form/flista_requisito_status.php?signal=$signal&action=$action&menu=frequisito&exect=$action";
$url_page.= "&indicacion=$indicacion&year=$year&evidencia=$evidencia&componente=$componente";
$url_page.= "&id_tipo_lista=$id_tipo_lista&id_lista=$id_lista&id_auditoria=$id_auditoria&id_proceso=$id_proceso";
$url_page.= "&id_capitulo=$id_capitulo&id_subcapitulo=$id_subcapitulo";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>REQUISITO DE LISTA DE CHEQUEO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../css/menu.css">
    <link rel="stylesheet" href="../libs/btn-toolbar/btn-toolbar.css" />
    <script type="text/javascript" src="../libs/btn-toolbar/btn-toolbar.js"></script>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" href="../libs/btn-toolbar/btn-toolbar.css" />
    <script type="text/javascript" src="../libs/btn-toolbar/btn-toolbar.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/general.css?version=">
    <link rel="stylesheet" type="text/css" href="../css/custom.css?version=">
    <link rel="stylesheet" type="text/css" href="../css/alarm.css" />

    <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/data.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/drilldown.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/widget.css?version=">
    <script type="text/javascript" src="../js/widget.js?version=" charset="utf-8"></script>

    <link rel="stylesheet" type="text/css" href="../css/lista.css" />
    <script type="text/javascript" src="../js/lista.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/form.js?version="></script>

    <style type="text/css">
    #div-ajax-table {
        height: 400px !important;
        margin-top: 6px;
    }
    .select-width {
        width: 150px!important;
    }
    </style>

    <script language="javascript" type="text/javascript">
        function refreshTab(id) {
            if (id < 0)
                id = 0;
            $('#init_row_temporary').val(id);
            validar(1);
        }
    </script>

    <script language="javascript" type="text/javascript">
        function form_filter() {
            var action= $('#exect').val();

            var _url = 'ajax/flista_requisito_filter.ajax.php?action='+action + '&year=' + <?=$year?> + '&inicio=' + <?=$_inicio?>;
                _url += '&fin=' + <?=$_fin?>+'&componente=' + <?=$componente ? $componente : 0?> + '&id_capitulo=' + <?=$id_capitulo ? $id_capitulo : 0?>;
                _url += '&id_subcapitulo=' + <?=$id_subcapitulo ? $id_subcapitulo : 0?>;

            var valores = '';
            var title = "FILTRADO DE REQUISITOS";

            var text= "Sí ha realizado algún cambio deberá selecionar primero el boton -Aplicar- o se perderán los cambios. ¿Desea continuar?";
            confirm(text, function(ok) {
                if (ok)
                    this_1();
                else
                    return;
            });            

            function this_1() {
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
        }

        function filtrar() {
            $('#year').val($('#_year').val());
            $('#id_componente').val($('#componente').val());
            $('#id_capitulo').val($('#capitulo').val());
            $('#id_subcapitulo').val($('#subcapitulo').val());

            var url = set_url();
            self.location.href = 'flista_requisito_status.php' + url;
        }

        function closep(error) {
            var year = $('#year').val()
            var id_proceso = $('#id_proceso').val()
            var id_auditoria = $('#id_auditoria').val()
            var id_lista = $('#id_lista').val();

            var text= "¿Desea guardar los cambios realizados? De lo contrario los cambios realizados seran perdidos ";
            confirm(text, function(ok) {
                if (ok)
                    validar(0);
                else
                    this_1();
            });            

            function this_1() {
                var url = "../html/nota.php?id_lista=" + id_lista + '&id_auditoria=' + id_auditoria;
                url += '&yea=' + year + '&id_proceso=' + id_proceso + '&error=' + encodeURIComponent(error);
                self.location.href = url;                
            }
        }

        function validar(print) {
            var form = document.forms[0];
            var url= '../php/lista_requisito.interface.php?print=' + print + '&componente=' + <?=$componente ? $componente : 0?>;
            url += '&id_capitulo=' + <?=$id_capitulo ? $id_capitulo : 0?> + '&id_subcapitulo=' + <?=$id_subcapitulo ? $id_subcapitulo : 0?>;
          
            form.action = url;
            form.submit();
        }
        </script>

        <script type="text/javascript">
        $(document).ready(function() {
            InitDragDrop();

            setInterval('validar(1)', 350000);

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
            <a href="#" class="navd-header">LISTADO DE REQUISITOS</a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item ml-3">
                        <label class="badge badge-warning mt-3">
                            <?=$nombre_lista?>
                        </label>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a href="#" class="" onclick="validar(1);"
                        title="Guarda los cambios en la Lista de Chequeo pero no modifica el eatado los hallazgos, ni el cumplimiento de la Lista" >
                            <i class="fa fa-desktop"></i>Guardar
                        </a>
                    </li>  
                    <li class="nav-item d-none d-md-block">
                        <a href="#" class="" onclick="validar(3);"
                        title="Guarda los cambios en la Lista de Chequeo, cambia el estado de los hallazgos, crea o elimina no-conformidades, incide en el cumplimiento de la Lista" >
                            <i class="fa fa-check-square"></i>Aplicar
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a href="#" class="" onclick="form_filter();">
                            <i class="fa fa-filter"></i>Filtrar
                        </a>
                    </li>
                    <li class="nav-item d-none d-md-block">
                        <a href="#" class="" onclick="validar(2)">
                            <i class="fa fa-print"></i>Imprimir
                        </a>
                    </li>                    
                </ul>
            </div>    

            <div class="navd-end">
                <ul class="navbar-nav mr-auto">
                    <li>
                        <a href="#" onclick="open_help_window('<?=$help?>')">
                            <i class="fa fa-question"></i>Ayuda
                        </a>
                    </li>
                    <li>
                        <a href="#" onclick="closep()">
                            <i class="fa fa-close"></i>Cerrar
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>

    <div id="navbar-third" class="row app-nav d-none d-md-block d-none d-lg-block">
        <nav class="navd-content">
            <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
                <li class="col-2">
                    <label class="badge badge-success">
                        <?=$year?>
                    </label>
                </li>

                <li class="col-auto">
                    <div class="badge badge-danger">
                        <?php
                        if (!empty($id_proceso) && $id_proceso != -1) {
                            $_connect= ($conectado != _LAN && $id_proceso != $_SESSION['local_proceso_id']) ? _NO_LOCAL : _LOCAL;
                        }
                        if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) {
                        ?>
                            <i class="fa fa-wifi"></i>
                        <?php } ?>
                        <?=$nombre_prs?>
                    </div>
                </li>

                <li class="col-auto">
                    <label class="badge badge-warning">
                    <?=$auditoria?>
                    </label>
                </li>                

                <li class="col-2">
                    <div class="row">
                        <label class="label col-5">Muestra:</label>
                        <div id="nshow" class="badge badge-warning">0</div>
                    </div>
                </li>

                <li class="col-2">
                    <div class="row">
                        <label class="label col-xs-7 col-5">Ocultas:</label>
                        <div id="nhide" class="badge badge-warning">0</div>
                    </div>
 
                </li>
            </ul>
        </nav>
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
    $id_proceso= !empty($id_proceso) && $id_proceso > 0 ? $id_proceso : null;
    $obj->SetIdProceso($id_proceso);
    
    $obj->limited= true;
    $obj->init_row_temporary= $init_row_temporary;
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

    <?php
    $visible= 'hidden';
    if ($action == 'update' || $action == 'add') 
        $visible= 'visible';
    ?>

    <div class="app-body container-fluid table threebar">   
        <form action='javascript:' method="post" class="intable">
             <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
            <input type="hidden" name="menu" id="menu" value="flista_register" />

            <input type="hidden" id="_nhide" value="0" />

            <input type="hidden" id="id_auditoria" name="id_auditoria" value="<?=$id_auditoria?>" />
            <input type="hidden" id="id_lista" name="id_lista" value="<?=$id_lista?>" />
            <input type="hidden" id="id_lista_code" name="id_lista_code" value="<?=$id_lista_code?>" />

            <input type="hidden" name="id_tipo_lista" id="id_tipo_lista"
                value="<?= !empty($id_tipo_lista) ? $id_tipo_lista : 0 ?>" />
            <input type="hidden" name="id_componente" id="id_componente"
                value="<?= !empty($componente) ? $componente : 0 ?>" />
            <input type="hidden" name="id_capitulo" id="id_capitulo"
                value="<?= !empty($id_capitulo) ? $id_capitulo : 0 ?>" />
            <input type="hidden" name="id_subapitulo" id="id_subcapitulo"
                value="<?= !empty($id_subcapitulo) ? $id_subcapitulo : 0 ?>" />

            <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
            
            <input type= "hidden" id="if_jefe" name= "if_jefe" value="<?=$if_jefe?>" />
            <input type= "hidden" id="inicio" name= "inicio" value="<?=$_inicio?>" />
            <input type= "hidden" id="fin" name= "fin" value="<?=$_fin?>" />

            <input type="hidden" id="year" name="year" value="<?=$year?>" />
            
            <input type="hidden" id="init_row_temporary" name="init_row_temporary" value="<?=$init_row_temporary?>" />

            <table class="table table-hover table-striped" 
                data-toggle="table" 
                data-toolbar="#toolbar"
                data-search="true" 
                data-show-columns="true">
                <thead>
                    <tr>
                        <th data-field="id">No.</th>
                        <th data-field="peso">Peso</th>
                        <th data-field="nombre">Requisitos a Evaluar</th>
                        <th data-field="descripcion">Evidencias</th>
                        <th data-field="indicacion">Indicaciones al Equipo Evaluador</th>
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
                    $obj->SetIdProceso($id_proceso);

                    $obj->listar(null, false);
                    $cant= $obj->GetCantidad();
                    
                    if (empty($cant))
                        continue;
                ?>
                    <tr>
                        <td colspan="5"  class="colspan">
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
                            <td colspan="5"  class="colspan">
                                <?=$_componente.",".$row1['numero'].") ". $row1['nombre']?>
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
                <?php } } } ?>    
                </tbody>
            </table>

            <input type="hidden" id="cant" name="cant" value="<?= $i ?>">

            <script type="text/javascript" language="JavaScript">
            document.getElementById('nshow').innerHTML = '<?=$nshow?>';
            /*
            document.getElementById('nhide').innerHTML = '<?=$nhide?>';
            */
            </script>

            <!-- panel-requisito -->
            <div id="div-filter-ajax">

            </div>  
        </form>
    </div>
</body>

</html>

<?php $_SESSION['obj']= serialize($obj); ?>