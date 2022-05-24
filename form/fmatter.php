<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';
require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";

require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/asistencia.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add' && empty($_GET['id_redirect'])) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'fmatter';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$id_evento= $_GET['id_evento'];
$id_proceso= $_GET['id_proceso'];
$ifaccords= (int)$_GET['ifaccords'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$obj_evento= new Tevento($clink);
$obj_matter= new Ttematica($clink);
$obj_user= new Tusuario($clink);
$obj_assist= new Tasistencia($clink);

$obj_evento->Set($id_evento);
$asunto= $obj_evento->GetNombre();
$fecha_inicio= $obj_evento->GetFechaInicioPlan();
$fecha_fin= $obj_evento->GetFechaFinPlan();
$id_responsable= $obj_evento->GetIdResponsable();
$id_secretary= $obj_evento->GetIdSecretary();
$id_evento_ref= $obj_evento->get_id_evento_ref();

$year= date('Y', strtotime($fecha_inicio));

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? $_GET['error'] : $obj_tematica->error;

$obj_matter->SetIfaccords($ifaccords);
$obj_matter->SetIdEvento($id_evento);
$obj_matter->SetIdProceso(null);
$obj_matter->SetYear(null);

$numero= $ifaccords ? $obj_matter->find_max_numero_accords(!empty($id_evento_ref) ? $id_evento_ref : $id_evento) : 0;
$result= !empty($id_evento) ? $obj_matter->listar($ifaccords) : null;
$visible= ($action == 'update' || $action == 'add') ? 'visible' : 'hidden';

$menu= "tablero";
$title= "ORDEN DEL DÍA";
$title_th= "Temática";

if ($ifaccords) {
    $title= "ACUERDOS";
    $title_th= "Acuerdo";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title><?=$title?></title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" type="text/css" media="screen" href="../css/alarm.css?">

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.min.js"></script>
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-datepicker.es.min.js"></script>

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet">
    <script type="text/javascript" src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js?version="></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/ajax_core.js"></script>

    <script type="text/javascript" src="../js/time.js?version="></script>

    <script language="javascript">
    var eventos_cump = Array();
    eventos_cump[0] = null;
    var eventos_cump_class = Array();
    eventos_cump_class[0] = null;

    <?php
    for ($j= 1; $j < _MAX_STATUS_EVENTO; ++$j) {
        echo "eventos_cump[$j]= '".$eventos_cump[$j]."'; ";
        echo "eventos_cump_class[$j]= '".$eventos_cump_class[$j]."'; ";
    }
    ?>
    </script>
    <!--
    <link href="../css/tematica.css?version=" rel="stylesheet" />
    -->
    <script type="text/javascript" src="../js/tematica.js?version="></script>

    <script type="text/javascript">
    max_numero_accords = <?=$numero?>;

    function set_numero_matter(val) {
        $('#numero_matter').val(val);
    }

    function submit_matter(to_print) {
        var form = document.forms['form-list-matter'];
        var url = '../php/tematica.interface.php?action=add&ajax_win=0&to_print=' + to_print + '&signal=' + $('#signal')
            .val();
        url += '&year=<?=$year?>';
        form.action = url;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        form.submit();
    }

    function print_acta() {
        var id_evento = $('#id_evento').val();
        var id_proceso = $('#proceso').val();
        var url = '../print/acta_meeting.php?id_evento=' + id_evento + '&id_proceso=' + id_proceso;
        url += '&year=<?=$year?>';
        self.location.href = url;
    }

    function showWindow(id_evento) {
        var action = $('#exect').val();
        var id_proceso = $('#id_proceso').val();
        var year = $('#year').val();

        var url = '../form/fdocument.php?action=' + action + '&id_proceso=' + id_proceso + '&year=' + year;
        url += '&id_evento=' + id_evento + '&signal=<?=$signal?>';

        win_document = document.open(url, "_blank",
            "width=900,height=640,toolbar=no,location=0, menubar=0, titlebar=yes, scrollbars=no");;
    }
    </script>

    <script type="text/javascript">
    var focusin;
    title_th = '<?= $title_th ?>';
    var row_matter;
    var row_accords;

    $(document).ready(function() {
        InitDragDrop();

        $('#div_fecha_accords').datepicker({
            format: 'dd/mm/yyyy',
            startDate: '<?= date('d/m/Y', strtotime($fecha_inicio)) ?>'
        });
        $('#div_hora_accords').timepicker({
            minuteStep: 5,
            showMeridian: true
        });
        $('#div_hora_accords').timepicker().on('changeTime.timepicker', function(e) {
            $('#hora_accords').val($(this).val());
        });

        $table_matter = $('#table-matter');
        $table_matter.bootstrapTable('append', row_matter);

        $table_accords = $('#table-accords');
        $table_accords.bootstrapTable('append', row_accords);

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>

    <script type="text/javascript" src="../js/form.js?version="></script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header"><?=$title?></div>
                <div class="card-body">

                    <?php if ($ifaccords) { ?>
                        <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                            <li id="nav-tab7" class="nav-item" title=""><a class="nav-link" href="tab7">Nuevos Acuerdos</a></li>
                            <li id="nav-tab8" class="nav-item" title=""><a class="nav-link" href="tab8">Revisión de Acuerdos anteriores</a></li>
                        </ul>
                    <?php } ?>

                    <form id="form-list-matter" action='#' method=post>
                        <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
                        <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />
                        <input type="hidden" id="id" name="id" value="" />
                        <input type="hidden" id="menu" name="menu" value="form-list-matter" />

                        <input type="hidden" id="id_usuario" name="id_usuario" value="<?=$_SESSION['id_usuario']?>" />
                        <input type="hidden" name="nivel" id="nivel" value="<?=$_SESSION['nivel']?>" />

                        <input type="hidden" id="id_evento" name="id_evento" value="<?=$id_evento?>" />
                        <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" id="proceso_code" name="proceso_code" value="<?=$id_proceso_code?>" />
                        
                        <input type="hidden" id="id_proceso" name="id_proceso" value="<?=$id_proceso?>" />
                        <input type="hidden" id="id_proceso_code" name="id_proceso_code" value="<?=$id_proceso_code?>" />

                        <input type="hidden" id="fecha_inicio" name="fecha_inicio"
                            value="<?= odbc2date($fecha_inicio)?>" />
                        <input type="hidden" id="ifaccords" name="ifaccords" value="<?=$ifaccords?>" />
                        <input type="hidden" id="prev" value="" />

                        <input type="hidden" id="id_responsable" name="id_responsable" value="<?=$id_responsable?>" />
                        <input type="hidden" id="id_secretary" name="id_secretary" value="<?=$id_secretary?>" />

                        <input type="hidden" id="year" name="year" value="<?=$year?>" />
                        <input type="hidden" id="hora_inicio" value="<?=date('H:i A', strtotime($fecha_inicio))?>" />
                        <input type="hidden" id="hora_fin" value="<?=date('H:i A', strtotime($fecha_fin))?>" />


                        <script type="text/javascript" language="javascript">
                        array_usuarios = Array();
                        </script>

                        <?php
                            $if_jefe_meeting= $menu == "tablero" ? false : true;
                            if ($_SESSION['nivel'] >= _SUPERUSUARIO)
                                $if_jefe_meeting= true;
                            if ($id_secretary == $_SESSION['id_usuario'] || $id_responsable == $_SESSION['id_usuario'])
                                $if_jefe_meeting= true;
                            $if_jefe_meeting= $action == 'add' ? $if_jefe_meeting : false;
                            $hide= $menu == 'tablero' ? null : "hide";
                            ?>

                        <!-- plan tematico -->
                        <div class="tabcontent <?=$hide?>" id="tab7">
                            <div id="idem-evento" class="container-fluid">
                                <div class="col-12">
                                    <div class="alert text alert-info">
                                        <strong>Reunión: </strong>
                                        <?=$asunto?>

                                        <br />
                                        <strong>Fecha/Hora: </strong>
                                        <?php
                                        $date= $dayNames[(int)date('N', strtotime($fecha_inicio))];
                                        $date.= ' '.(int)date('d', strtotime($fecha_inicio));
                                        $date.= ', '.$meses_array[(int)date('m', strtotime($fecha_inicio))];
                                        $date.= ' '.date('Y', strtotime($fecha_inicio))
                                        ?>
                                        <?=$date?>
                                    </div>
                                </div>

                                <div id="toolbar" class="btn-group btn-app row">
                                    <?php if ($if_jefe_meeting) { ?>
                                    <button id="btn_agregar" class="btn btn-primary" type="button"
                                        onclick="form_matter('add', 0);" style="visibility:<?=$visible?>">
                                        <i class="fa fa-plus"></i>Agregar
                                    </button>
                                    <?php } ?>
                                    <button id="btn-print" class="btn btn-success d-none d-md-block ml-1" type="submit"
                                        onclick="submit_matter(1);">
                                        <i class="fa fa-print"></i>Imprimir
                                    </button>

                                    <?php if ($menu == 'tablero') { ?>
                                    <button id="btn-print" class="btn btn-info ml-1" type="button" onclick="print_acta();">
                                        <i class="fa fa-print"></i><i class="fa fa-file-text"></i>Imprimir Acta
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php
                                $i= 0;
                                while ($row= $clink->fetch_array($result)) {
                                    ++$i;
                                ?>
                                <input type="hidden" id="tab_matter_<?=$i?>" name="tab_matter_<?=$i?>" value="1">
                                <input type="hidden" id="id_matter_<?=$i?>" name="id_matter_<?=$i?>"
                                    value="<?=$row['id']?>">

                            <?php if ($row['ifaccords']) { ?>
                                <input type="hidden" id="tab_accords_<?=$i?>" name="tab_accords_<?=$i?>" value="1">
                                <input type="hidden" id="id_evento_accords_<?=$i?>" name="id_evento_accords_<?=$i?>"
                                    value="<?=$row['id_evento_accords']?>">
                                <input type="hidden" id="id_evento_accords_code_<?=$i?>"
                                    name="id_evento_accords_code_<?=$i?>" value="<?=$row['id_evento_accords_code']?>">
                            <?php } } ?>

                            <script type='text/javascript'>
                            array_ids_matter.push(0);

                            <?php
                            $i= 0;
                            $k= 0;
                            $clink->data_seek($result);
                            while ($row= $clink->fetch_array($result)) {
                                ++$i;
                                ++$k;
                                $numero= !empty($row['numero']) ? $row['numero'] : $k;
                            ?>
                            array_ids_matter.push(<?=$numero?>);
                            <?php } ?>
                            </script>

                            <script type='text/javascript'>
                            row_matter = [
                                <?php
                                $array_class= array('');
                                $max_numero_accords= $numero;
                                $clink->data_seek($result);

                                $i= 0;
                                $k= 0;
                                while ($row= $clink->fetch_array($result)) {
                                    ++$i;
                                    ++$k;
                                    $numero= !empty($row['numero']) ? $row['numero'] : $k;
                                    if ($numero > $max_numero_accords)
                                        $max_numero_accords= $numero;
                                    if ($i > 1)
                                        echo ",";
                                ?> {
                                    id: <?=$numero?>,

                                    <?php if ($if_jefe_meeting) { ?>
                                    icons: '' +
                                        '<a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="del_matter(<?=$i?>);" style="visibility: <?=$visible?>">' +
                                        '<i class="fa fa-trash"></i>Eliminar' +
                                        '</a>' +

                                        '<a href="#" class="btn btn-warning btn-sm" title="Editar" onclick="form_matter(\'edit\', <?=$i?>);" style="visibility: <?=$visible?>" >' +
                                        '<i class="fa fa-edit"></i>Editar' +
                                        '</a>' +
                                        <?php if ($ifaccords) { ?> '<a href="#" class="btn btn-info btn-sm" title="registrar situación o cumplimiento" onclick="edit_accords(<?=$i?>,0);">' +
                                        '<i class="fa fa-check"></i>Registrar' +
                                        '</a>' +
                                        <?php } ?>
                                    <?php if (!empty($row['id']) && !$ifaccords) { ?> 
                                        '<a href="#" class="btn btn-primary btn-sm" title="describir las intervenciones en el debate de la temática" onclick="add_debate(<?=$i?>);">' +
                                        '<i class="fa fa-angelist"></i>Debate' +
                                        '</a>' +
                                    <?php } ?> 
                                        '',
                                    <?php } ?>

                                    nombre: '' +
                                        <?php if ($ifaccords) { ?> '<label class="alarm <?=$eventos_cump_class[$row['cumplimiento']]?>" id="cumplimiento_text_<?=$i?>">' +
                                        '<?=$eventos_cump[$row['cumplimiento']]?>' +
                                        '</label><br />' +
                                        <?php } ?> 
                                        '<p><?=substr(textparse(purge_html(json_decode(json_encode($row['descripcion']))), true), 0, strlen($row['descripcion']))?></p>' +

                                        '<input type="hidden" id="matter_<?=$i?>" name="matter_<?=$i?>" value="<?= textparse(purge_html($row['observacion']), true)?>" />' +
                                        '<input type="hidden" id="numero_matter_<?=$i?>" name="numero_matter_<?=$i?>" value="<?=$numero?>" />' +
                                        <?php if ($ifaccords) { ?> '<input type="hidden" id="cumplimiento_<?=$i?>" name="cumplimiento_<?=$i?>" value="<?=$row['cumplimiento']?>" />' +
                                        '<input type="hidden" id="time_accords_<?=$i?>" name="time_accords_<?=$i?>" value="<?=odbc2time_ampm($row['_fecha_inicio_plan'], false) ?>" />' +
                                        '<input type="hidden" id="id_responsable_eval_<?=$i?>" name="id_responsable_eval_<?=$i?>" value="<?=$row['id_responsable_eval']?>" />' +
                                        '<input type="hidden" id="observacion_accords_<?=$i?>" name="observacion_accords_<?=$i?>" value="<?= json_decode(json_encode(textparse($row['evaluacion'], true, true)))?>" />' +
                                        <?php } ?> '',

                                    fecha: '' +
                                        '<input type="hidden" id="time_matter_<?=$i?>" name="time_matter_<?=$i?>" value="<?=$ifaccords ? odbc2time_ampm($row['fecha_inicio_plan'], false) : odbc2ampm($row['fecha_inicio_plan'], false)?>" />' +
                                        '<?=$ifaccords ? odbc2time_ampm($row['fecha_inicio_plan'], false) : odbc2ampm($row['fecha_inicio_plan'], false)?>' +
                                        '',

                                    responsable: '' +
                                        <?php
                                        $obj_matter->SetFechaInicioPlan($row['_fecha_inicio_plan']);
                                        $obj_matter->SetYear(date('Y', strtotime($row['_fecha_inicio_plan'])));
                                        $mail= $obj_assist->GetEmail($row['id_asistencia_resp']);
                                        $name= !empty($mail['cargo']) ? textparse($mail['nombre']).', '.textparse($mail['cargo']) : textparse($mail['nombre']);
                                        echo "'$name'+";
                                        echo "'<br/><strong>PARTICIPANTES</strong><br/>".$obj_matter->get_participantes($row['_id'])."'+";
                                        ?> '<input type="hidden" id="id_asistencia_resp_<?=$i?>" name="id_asistencia_resp_<?=$i?>" value="<?=$row['id_asistencia_resp'] ?>" />' +
                                        ''
                                }
                                <?php } ?>
                            ];
                            </script>
                            <div class="container-fluid">
                                <table id="table-matter" class="table table-hover table-striped" data-toggle="table"
                                    data-toolbar="#toolbar" data-height="420" data-search="true"
                                    data-show-columns="true">
                                    <thead>
                                        <tr>
                                            <th data-field="id">No.</th>
                                            <?php if ($if_jefe_meeting) { ?>
                                            <th data-field="icons"></th>
                                            <?php } ?>
                                            <th data-field="nombre"><?= $title_th ?></th>
                                            <th data-field="fecha">Fecha/Hora</th>
                                            <th data-field="responsable">Responsable</th>
                                        </tr>
                                    </thead>
                                </table>

                                <input type="hidden" id="cant_matter" name="cant_matter" value="<?=$i?>">
                                <script language="javascript">
                                max_numero_accords = <?=$max_numero_accords?>;
                                </script>

                                <script type="text/javascript">
                                maxIndex = <?= $i-1 ?>;

                                <?php
                                $k= 0;
                                for ($j= 1; $j <= $i; ++$j) {
                                ?>
                                    arrayIndex['-' + <?=$j?>] = <?=$k++?>;
                                <?php } ?>
                                </script>

                            </div>
                        </div> <!-- plan tematico -->


                        <?php
                        $result= null;
                        $array_accords= null;
                        if ($ifaccords) {
                            $obj_matter= new Ttematica($clink);
                            $obj_matter->SetIdEvento($id_evento);
                            $array_accords= $obj_matter->getPrevAccords();
                        }
                        ?>

                        <?php if ($ifaccords) { ?>
                        <!-- acuedos para revizar Revision de Acuerdos anteriores -->
                        <div class="tabcontent" id="tab8">
                            <div id="toolbar-accords" class="btn-btn-group btn-app row">
                                <button id="btn-print" class="btn btn-success d-none d-lg-block" type="submit" onclick="submit_matter(2);"
                                    style="visibility:<?=$visible?>">
                                    <i class="fa fa-print"></i>Imprimir
                                </button>

                                <?php if ($menu == 'tablero') { ?>
                                <button id="btn-print" class="btn btn-info d-none d-lg-block ml-1" type="button" onclick="print_acta();"
                                    style="visibility:<?= $visible ?>">
                                    <i class="fa fa-file-text"></i><i class="fa fa-print"></i>Imprimir Acta
                                </button>
                                <?php } ?>
                            </div>

                            <?php
                            $i= 0;
                            foreach ($array_accords as $row) {
                                ++$i;
                            ?>
                                <input type="hidden" id="id_matter_prev_<?=$i?>" name="id_matter_prev_<?=$i?>"
                                    value="<?=$row['id']?>">
                                <input type="hidden" id="tab_accords_prev_<?=$i?>" name="tab_accords_prev_<?=$i?>"
                                    value="<?=$row['cumplimiento']?>">
                            <?php } ?>

                            <script type="text/javascript">
                            row_accords = [
                                <?php
                                $array_class= array('');
                                
                                $i= 0;
                                reset($array_accords);
                                foreach ($array_accords as $row) {
                                    ++$i;
                                    $numero= !empty($row['numero']) ? $row['numero'] : $i;
                                    if ($i > 1)
                                        echo ", ";
                                ?> {
                                    id: <?=$numero?>,

                                    accords: '' +
                                        <?php
                                        echo "'".textparse(purge_html($row['descripcion']))."'+";
                                        ?> 
                                        '<br />' +
                                        '<span style="font-style: oblique; text-decoration: underline;">' +
                                        <?php
                                        echo "'   ".$meses_array[date('n', strtotime($row['fecha_inicio']))]."'+";
                                        echo "', ".date('j', strtotime($row['fecha_inicio']))."'+";
                                        ?> '<br />' +
                                        '</span>' +

                                        '<input type="hidden" id="numero_matter_prev_<?=$i?>" name="numero_matter_prev_<?=$i?>" value="<?=$numero?>"/>' +

                                        '<input type="hidden" id="cumplimiento_prev_<?=$i?>" name="cumplimiento_prev_<?=$i?>" value="<?=$row['cumplimiento']?>" />' +
                                        '<input type="hidden" id="time_accords_prev_<?=$i?>" name="time_accords_prev_<?=$i?>" value="<?=odbc2time_ampm($row['fecha_inicio'])?>" />' +
                                        '<input type="hidden" id="id_responsable_eval_prev_<?=$i?>" name="id_responsable_eval_prev_<?=$i?>" value="<?=$row['id_responsable_eval']?>" />' +
                                        '<input type="hidden" id="observacion_accords_prev_<?=$i?>" name="observacion_accords_prev_<?=$i?>" value="<?= json_decode(json_encode(textparse($row['evaluacion'], true, true)))?>" />' +
                                        '',
                                    
                                    estado: '' +
                                        <?php if ($if_jefe_meeting) { ?> 
                                        '<a href="#" class="btn btn-info" title="registrar situación o cumplimiento" onclick="edit_accords(<?=$i?>,1);">' +
                                        '<i class="fa fa-check"> Registrar</i>' +
                                        '</a>' +
                                        <?php } ?>
                                                                                
                                        '<a href="#" class="btn btn-success" title="documentos adjuntos" onclick="showWindow(<?=$row['id_evento_accords']?>,1);">' +
                                        '<i class="fa fa-file-word-o"> Documentos</i>' +
                                        '</a>' + 
                                        
                                        '<br /><label class="alarm <?=$eventos_cump_class[$row['cumplimiento']]?>" id="cumplimiento_text_prev_<?=$i?>"><?=$eventos_cump[$row['cumplimiento']]?></label>' +
                                        '',
                                        
                                    value: '' +
                                        '<input type="hidden" id="time_matter_prev_<?=$i?>" name="time_matter_prev_<?=$i?>" value="<?=odbc2time_ampm($row['fecha_inicio'], false)?>">' +
                                        '<?=odbc2time_ampm($row['fecha_inicio'], false)?>' +
                                        '',

                                    responsable: '' +
                                        <?php
                                        $mail= $obj_assist->GetEmail($row['id_asistencia_resp']);
                                        $name= !empty($mail['cargo']) ? textparse($mail['nombre']).', '.textparse($mail['cargo']) : textparse($mail['nombre']);
                                        echo "'$name'+";

                                        $obj_matter->SetIdTematica($row['id']);
                                        $obj_matter->SetIdEvento($row['id_evento']);
                                        $obj_matter->SetYear(date('Y', strtotime($row['fecha_inicio'])));

                                        echo "'<br/><strong>PARTICIPANTES</strong><br/>".$obj_matter->get_participantes()."'+";
                                        ?> '<input type="hidden" id="id_asistencia_resp_prev_<?=$i?>" name="id_asistencia_resp_prev_<?=$i?>" value="<?=$row['id_asistencia_resp'] ?>" />' +
                                        '',
                                    
                                    observacion: '' +
                                        '<label id="observacion_accords_prev_text_<?=$i?>">' + 
                                        '<?=textparse(purge_html(json_decode(json_encode($row['evaluacion']))), true)?>' +
                                        '</label>' + 
                                        ''
                                }
                                <?php } ?>
                            ];
                            </script>

                            <div class="container-fluid">
                                <table id="table-accords" class="table table-hover table-striped" data-toggle="table"
                                    data-toolbar="#toolbar-accords" data-height="480" data-search="true"
                                    data-show-columns="true">
                                    <thead>
                                        <tr>
                                            <th data-field="id">No.</th>
                                            <th data-field="accords">Acuerdo</th>
                                            <th data-field="estado">Estado</th>
                                            <th data-field="value">A cumplir</th>
                                            <th data-field="responsable">Responsable</th>
                                            <th data-field="observacion">Observación</th>
                                        </tr>
                                    </thead>
                                </table>

                                <input type="hidden" id="cant_matter_prev" name="cant_matter_prev" value="<?=$i?>">
                            </div>
                        </div> <!-- acuedos para revizar Revision de Acuerdos anteriores -->
                        <?php } ?>

                        <!-- div-ajax-panel -->
                        <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

                        </div>
                        <!-- div-ajax-panel -->


                        <!-- div-ajax-panel-accords -->
                        <div id="div-ajax-panel-accords" class="card card-primary ajax-panel" data-bind="draganddrop">
                            <div class="card-header">
                                <div class="row">
                                    <div class="panel-title ajax-title col-11 m-0 win-drag">
                                        ESTADO DEL ACUERDO
                                    </div>

                                    <div class="col-1 m-0">
                                        <div class="close">
                                            <a href="javascript:HideContent('div-ajax-panel-accords');" title="cerrar ventana">
                                                <i class="fa fa-close"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="form-horizontal">
                                    <div class="form-group row">
                                        <label class="col-form-label col-2">
                                            Estado de ejecución:
                                        </label>

                                        <div class="col-3">
                                            <select name="cumplimiento" id="cumplimiento" class="form-control">
                                                <option value="0">Seleccione...</option>
                                                <?php
                                                for ($j = 1; $j < _MAX_STATUS_EVENTO; ++$j) {
                                                    if ($j == _CANCELADO || $j == _ESPERANDO)
                                                        continue;
                                                ?>
                                                <option value="<?= $j ?>"><?= $eventos_cump[$j] ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-7 row">
                                            <label class="col-form-label col-3">
                                                Fecha y Hora de cumplimiento: 
                                            </label>

                                            <div class="row col-9">
                                                <div class="col-7">
                                                    <div class='input-group date' id='div_fecha_accords'
                                                        data-date-language="es">
                                                        <input type='text' id="fecha_accords" name="fecha_accords"
                                                            class="form-control" readonly value="" />
                                                        <span class="input-group-text"><span
                                                                class="fa fa-calendar"></span></span>
                                                    </div>
                                                </div>

                                                <div class="col-5">
                                                    <div class="input-group bootstrap-timepicker timepicker"
                                                        id='div_hora_accords'>
                                                        <input type="text" id="hora_accords" name="hora_accords"
                                                            class="form-control input-small" readonly value="" />
                                                        <span class="input-group-text"><i
                                                                class="fa fa-calendar-times-o"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-form-label col-12 pull-left">
                                            Observación:
                                        </label>
                                    </div>

                                    <div class="form-group row">
                                        <div class="col-12">
                                            <textarea name="observacion_accords" id="observacion_accords"
                                                class="form-control" rows="7"></textarea>
                                        </div>
                                    </div>
                                </div>

                                <hr class="divider" />

                                <div class="btn-block btn-app" style="margin-top: 10px;">
                                    <button type="button" class="btn btn-primary" onclick="reg_accords()"
                                        title="Registrar estado del acuerdo">Aceptar</button>
                                    <button type="button" class="btn btn-warning" onclick="close_accords()"
                                        title="Cerrar">Cerrar</button>
                                </div>
                            </div>
                        </div>
                        <!--div-ajax-panel-accords -->


                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                                <button class="btn btn-primary" type="submit" onclick="submit_matter(0)">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="button" onclick="self.close();">Cerrar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html#listas')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div>
        </div>

    </div>

</body>

</html>