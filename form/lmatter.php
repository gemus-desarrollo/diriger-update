<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tematica.class.php";

$_SESSION['debug']= 'no';

$action= !empty( $_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'edit') 
    $action= 'add';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'lmatter';

$year= $_GET['year'];
$id_evento= $_GET['id_evento'];
$id_proceso= $_GET['id_proceso'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
if ($obj_prs->GetTipo() == _TIPO_PROCESO_INTERNO)
    $id_proceso= $obj_prs->GetIdProceso_sup();

$obj_user= new Tusuario($clink);

$obj_evento= new Tevento($clink);
$obj_evento->Set($id_evento);
$asunto= $obj_evento->GetNombre();
$fecha_inicio= $obj_evento->GetFechaInicioPlan();
$fecha_fin= $obj_evento->GetFechaFinPlan();
$id_responsable= $obj_evento->GetIdResponsable();
$id_secretary= $obj_evento->GetIdSecretary();
$hora_inicio= date('h:i A', strtotime($fecha_inicio));

$obj= new Ttematica($clink);
$obj->SetIdProceso(null);
$obj->SetIdEvento($id_evento);
$obj->SetYear($year);

$array_tematicas= $obj->list_tematicas();

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>PLAN TEMÁTICO DEL ANO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/table.css" />

    <link href="../libs/bootstrap-datetimepicker/bootstrap-timepicker.css" rel="stylesheet" type="text/css">
    <script src="../libs/bootstrap-datetimepicker/bootstrap-timepicker.js"></script>

    <link rel="stylesheet" href="../libs/windowmove/windowmove.css?" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?"></script>
    <script language="javascript" type="text/javascript"
        src="../js/windowcontent.js?version="></script>

    <link rel="stylesheet" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css">
    <script type="text/javascript" src="../js/widget.js"></script>

    <script type="text/javascript" src="../js/time.js?" charset="utf-8"></script>
    <script type="text/javascript" src="../js/ajax_core.js" charset="utf-8"></script>

    <script type="text/javascript" src="../js/tematica.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language='javascript' type="text/javascript">
    var ifnew = false;
    var max_numero = 0;
    var oId;

    function print_matter() {
        var id_evento = $("#id_evento").val();
        var url = "../print/matter.php?id_evento=" + id_evento + "&id_proceso=&all_matter=1";
        prnpage = parent.show_imprimir(url, "IMPRIMIENDO PLAN TEMÁTICO",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }
    </script>

    <script type="text/javascript">
    var focusin;
    array_usuarios = Array();

    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-numero_matter', 1, 5000);
        InitDragDrop();

        $('#div_time_matter').timepicker({
            minuteStep: 1,
            showMeridian: true
        });
        $('#div_time_matter').timepicker().on('changeTime.timepicker', function(e) {
            $('#time_matter').val($(this).val());
        });

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body table">
        <div class="container-fluid">
            <form id="form-list-matter" action='javascript:' class="form-single" method=post>
                <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
                <input type="hidden" id="signal" name="signal" value="<?=$signal?>" />
                <input type="hidden" id="id_evento" name="id_evento" value="<?=$id_evento?>" />

                <input type="hidden" name="menu" value="form-matter" />

                <input type="hidden" id="selected" name="selected" value="0" />
                <input type="hidden" id="cant_" name="cant_" value="0" />
                <input type="hidden" id="hora_inicio" value="<?=$hora_inicio?>" />
                <input type="hidden" id="year" name="year" value="<?=$year?>" />

                <input type="hidden" id="id" name="id" value="" />
                <input type="hidden" id="ifaccords" name="ifaccords" value="0" />

                <div id="idem-evento" class="container-fluid">
                    <div class="row">
                        <label class="col-form-label col-sm-1 col-md-1">Reunión:</label>
                        <div class="col-sm-11 col-md-11">
                            <div class="alert text alert-info">
                                <?=$asunto?>
                                <br />
                                <strong>Hora inicio:</strong> <?=$hora_inicio?>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="toolbar" class="btn-btn-group btn-app row">
                    <?php if ($action == 'update' || $action == 'add') { ?>
                    <button id="btn_agregar" class="btn btn-primary hidden-xs ml-2" type="button"
                        onclick="form_matter('add', 0);" style="visibility:<?=$visible?>">
                        <i class="fa fa-plus"></i>Agregar
                    </button>
                    <?php } ?>
                    <button id="btn-print" class="btn btn-info d-none d-md-block ml-2" type="submit" onclick="print_matter();"
                        style="visibility:<?=$visible?>">
                        <i class="fa fa-print"></i>Imprimir
                    </button>
                </div>

                <table id="table-matter" class="table table-hover table-striped" data-toggle="table"
                    data-toolbar="#toolbar" data-search="true" data-show-columns="true">
                    <thead>
                        <tr>
                            <th rowspan="2">No.</th>
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <th rowspan="2"></th>
                            <?php } ?>
                            <th rowspan="2">TEMÁTICA</th>
                            <th rowspan="2">RESPONSABLE /<br />PARTICIPANTES</th>
                            <th colspan="12">MESES</th>
                        </tr>
                        <tr>
                            <th>Enero</th>
                            <th>Febrero</th>
                            <th>Marzo</th>
                            <th>Abril</th>
                            <th>Mayo</th>
                            <th>Junio</th>
                            <th>Julio</th>
                            <th>Agosto</th>
                            <th>Septiembre</th>
                            <th>Octubre</th>
                            <th>Noviembre</th>
                            <th>Diciembre</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i= 0;
                        $max_numero= 0;
                        foreach ($array_tematicas as $row) {
                            ++$i;
                            $numero= !empty($row['numero']) ? $row['numero'] : $i;
                            if ($numero > $max_numero) 
                                $max_numero= $numero;
                        ?>  
                        <tr>
                            <td>
                                <?=$numero?>
                            </td>
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <td>
                                <a class="btn btn-warning btn-sm" href="#" alt="Editar"
                                    onclick="form_matter('edit',<?=$i?>)">
                                    <i class="fa fa-edit"></i>Editar
                                </a>

                                <a class="btn btn-danger btn-sm" href="#" alt="Eliminar" onclick="del_matter(<?=$i?>)">
                                    <i class="fa fa-trash"></i>Eliminar
                                </a>
                            </td>
                            <?php } ?>
                            <td>
                                <?=$row['nombre']?>
                                <input type="hidden" id="id_matter_<?=$i?>" name="id_matter_<?=$i?>"
                                    value="<?=$row['id']?>" />

                                <input type="hidden" id="numero_matter_<?=$i?>" name="numero_matter_<?=$i?>"
                                    value="<?=$numero?>" />
                                <input type="hidden" id="time_matter_<?=$i?>" name="time_matter_<?=$i?>"
                                    value="<?= odbc2ampm($row['time'])?>" />
                                <input type="hidden" id="id_asistencia_resp_<?=$i?>" name="id_asistencia_resp_<?=$i?>"
                                    value="<?=$row['id_asistencia_resp'] ?>" />
                                <input type="hidden" id="matter_<?=$i?>" name="matter_<?=$i?>"
                                    value="<?=$row['nombre']?>" />
                            </td>
                            <td>
                                <?php
                                $email= $obj->GetEmail($row['id_asistencia_resp']);
                                echo ($config->onlypost) ? $email['cargo'] : $email['nombre'].' <br />'.textparse($email['cargo']);
                                ?>
                                <br /><strong>PARTICIPANTES</strong><br />
                                <?php
                                $obj->SetFechaInicioPlan($row['time']);
                                echo $obj->get_participantes($row['id']);
                                ?>
                            </td>

                            <?php for ($j= 1; $j < 13; $j++) { ?>
                            <td>
                                <?php
                                foreach ($row['array_month'][$j] as $day) {
                                    ++$i;
                                ?>
                                <input type="hidden" id="id_matter_<?=$i?>" name="id_matter_<?=$i?>"
                                    value="<?=$day['id']?>" />
                                <p>
                                    <?php if ($action == 'update' || $action == 'add') { ?>
                                    <a class="btn btn-danger btn-sm" title="Eliminar Tematica"
                                        onclick="del_matter(<?=$i?>)">
                                        <i class="fa fa-trash"></i>Eliminar
                                    </a>
                                    <?php } ?>
                                    <?="{$day['weekday']}, {$day['day']} {$day['time']}"?>
                                </p>
                                <?php } ?>
                            </td>
                            <?php } ?>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <script language="javascript">
                max_numero_accords = <?=$max_numero?>;
                </script>


                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <button class="btn btn-warning" type="reset" onclick="self.close()">Cancelar</button>
                    <button class="btn btn-danger" type="button"
                        onclick="open_help_window('../help/manual.htm')">Ayuda</button>
                </div>

                <div id="_submited" style="display:none">
                    <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                </div>

            </form>

        </div> <!-- container -->
    </div>

    <!-- div-ajax-panel -->
    <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

    </div> <!-- div-ajax-panel -->

</body>

</html>