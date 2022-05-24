<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
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

require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/regtarea.class.php";

require_once "../php/class/badger.class.php";

$id_tarea= $_GET['id_tarea'];
$if_jefe= !is_null($_GET['if_jefe']) ? $_GET['if_jefe'] : false;

$obj= new Ttarea($clink);
$obj->SetIdTarea($id_tarea);
$obj->Set();

$id_proyecto= $obj->GetIdProyecto();
$fecha_inicio= $obj->GetFechaInicioPlan();
$fecha_fin= $obj->GetFechaFinPlan();
$year= date('Y', strtotime($fecha_inicio));

$year_init= date('Y', strtotime($fecha_inicio));
$year_fin= date('Y', strtotime($fecha_fin));

$obj_event= new Tevento($clink);
$obj_event->SetYear($year_init);
$obj_event->SetIdTarea($id_tarea);
$obj_event->get_eventos_by_tarea(null, array($year_init, $year_fin));
$cant_event= count($obj_event->array_eventos);
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

        <link rel="stylesheet" type="text/css" media="screen" href="../css/alarm.css?">

        <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
        <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

        <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
        <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

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

        <script type="text/javascript">
            var $table_planning;
            var row_planning;

            function TableSortPlanning() {
                var rows;
                rows= $table_planning.bootstrapTable('getData');
                rows.sort(function(a, b) {
                    var valA = getCellValue(a),
                    valB = getCellValue(b)

                    var d= new Date(convertDateFormat(valA)).getTime() >= new Date(convertDateFormat(valB)).getTime() ? 1 : -1;
                    return d;
                });

               $table_planning.bootstrapTable('load', rows);
            }

            function convertDateFormat(string) {
                var info= string.split('/').reverse().join('-');
                return info;
            }

            function getCellValue(row) {
                return row.fecha;
            }
        </script>


	    <script type="text/javascript" charset="utf-8">
            var ifnew= true;
            var oId_planning;
            var arrayIndex_planning= new Array();
            var maxIndex_planning=-1;

            function form_planning(id) {
                ifnew= id == 0 ? true : false;
                var action= id == 0 ? 'add' : 'update';

                var _url= '../form/ajax/ftarea_hito.ajax.php?action='+action+'&id_tarea=<?=$id_tarea?>';

                $.ajax({
                    url:   _url,
                    method: 'GET',

                    cache: false,
                    processData: false,
                    contentType: false,

                    beforeSend: function () {
                        $("#ajax-hito").html("<div class='loading-indicator'>Procesando, espere por favor...</div>");
                    },
                    success: function(response) {
                        $("#ajax-hito").html(response);
                        if (parseInt(id) > 0)
                            _edit_planning(id);
                    },
                    error: function(xhr, status) {
                        alert('Disculpe, existió un problema en la conexión AJAX');
                    }
                });

                displayFloatingDiv('div-panel-hito', "HITO DE LA TAREA", 90, 0, 1, 5);
            }

            function add_planning() {
                var index;
                var strHtml1= '';
                var strHtml2= '';
                var strHtml3= '';
                var strHtml4= '';
                var strHtml5= '';

                if (ifnew) {
                    oId_planning = parseInt($("#cant_hits").val());
                    ++oId_planning;
                    $("#cant_hits").val(oId_planning);
                }

                var fecha = $("#fecha").val();
                var real = $("#real").val();
                var observacion = $("#observacion_hit").val();

                strHtml1 = oId_planning;

                <?php if (empty($fecha_final_real) && empty($ifgrupo)) { ?>
                    strHtml2+= '<a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="delete_planning(' + oId_planning + ');">'+
                        '<i class="fa fa-trash"></i>Eliminar'+
                        '</a>'+
                        '<a href="#" class="btn btn-warning btn-sm" title="Editar" onclick="edit_planning(' + oId_planning + ')">'+
                            '<i class="fa fa-pencil"></i>Editar'+
                        '</a>';
                <?php } ?>

                strHtml3= fecha;
                strHtml4= real;
                strHtml5 = observacion + '<input type="hidden" id="observacion_' + oId_planning + '" name="observacion_' + oId_planning + '" value="' + observacion + '"/>' ;
                strHtml5+= '<input type="hidden" id="real_' + oId_planning + '" name="real_' + oId_planning + '" value="' + real + '"/>';
                strHtml5+= '<input type="hidden" id="fecha_' + oId_planning + '" name="fecha_' + oId_planning + '" value="' + fecha + '"/>';

                if (ifnew) {
                    index= ++maxIndex_planning;
                    arrayIndex_planning['-'+oId_planning]= index;

                    $table_planning.bootstrapTable('insertRow', {
                        index: index,
                        row: {
                            id: strHtml1,
                            <?php if (empty($fecha_final_real) && empty($ifgrupo)) { ?>
                            icon: strHtml2,
                            <?php } ?>
                            fecha: strHtml3,
                            real: strHtml4,
                            observacion: strHtml5
                        }
                    });
                }

                if (!ifnew) {
                    index= arrayIndex_planning['-'+oId_planning];

                    $table_planning.bootstrapTable('updateRow', {
                        index: index,
                        row: {
                            id: strHtml1,
                            <?php if (empty($fecha_final_real) && empty($ifgrupo)) { ?>
                            icon: strHtml2,
                            <?php } ?>
                            fecha: strHtml3,
                            real: strHtml4,
                            observacion: strHtml5
                        }
                    });
                }

                limpiar();
                CloseWindow('div-panel-hito');
                ifnew= true;

                TableSortPlanning();
            }

            function delete_planning(id) {
                oId_planning= id;

                function _this() {
                    $("#fecha_"+id).val(0);
                    $("#real_"+id).val(0);
                    $("#observacion_"+id).val('');

                    var ids= new Array();
                    ids.push(id);

                    $table_planning.bootstrapTable('remove', {
                        field: 'id',
                        values: ids
                    });

                    for(var i= id; i <= $('#cant_hits').val(); ++i) {
                        if (arrayIndex_planning['-'+i] == 'undefined')
                            continue;
                        arrayIndex_planning['-'+i]= arrayIndex_planning['-'+i] ? arrayIndex_planning['-'+i] - 1 : 0;
                        maxIndex_planning= arrayIndex_planning['-'+i];
                    }
                    arrayIndex_planning['-'+id]= 'undefined';
                }

                confirm('Realmente desea eliminar este hito?', function(ok) {
                    if (!ok)
                        return false;
                    else
                        _this();
                });
            }

            function edit_planning(id) {
                if (parseInt(id) == 0)
                    return;

                form_planning(id);
            }

            function _edit_planning(id) {
                oId_planning= id;

                $('#fecha').val($('#fecha_'+id).val());
                var date= new Fecha($('#fecha').val());

                $('#regdate_year').val(date.anio);
                $('#regdate_month').val(date.mes);
                $('#regdate_day').val(date.dia);

                $('#real').val($('#real_'+id).val());
                $('#observacion_hit').val($('#observacion_'+id).val());

                tinymce.get('observacion_hit').setContent($('#observacion_hit').val());

                ifnew= false;              
            }

            function limpiar() {
                $('#fecha').val($('#fecha_inicio').val());
                $('#observacion_hit').val('');
                tinymce.get('observacion_hit').setContent('');
                ifnew= true;
            }

	    </script>

        <script type="text/javascript">
            function refreshplanning() {
                for (value in arrayIndex_planning) {
                    if (value == 'undefined' || value <= 0)
                        continue;
                    else {
                        planning= true;
                        return;
                    }
                }

                planning= false;
                return;
            }

            function closep() {
                if (opener)
                    opener.location.reload()
                self.close();
            }

            function ejecutar() {
                var form= document.forms['ftarea_hito']
                form.action= '../php/tarea_register.interface.php';

                parent.app_menu_functions= false;
                $('#_submit').hide();
                $('#_submited').show();

                form.submit();
            }
        </script>


        <script type="text/javascript">
           $(document).ready(function() {
                InitDragDrop();

                $table_planning = $('#table-planning');
                $table_planning.bootstrapTable('append', row_planning);
           });
       </script>

       <script type="text/javascript" src="../js/form.js?version="></script>
    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

       <div class="app-body form">
            <div class="container-fluid">
                <div class="card card-primary">
                    <div class="card-header">HITOS DE LA TAREA</div>

                    <div class="card-body">
                    <?php if ($cant_event <= 1) { ?>
                        <div class="col-12 m-2 mt-3 p-3">
                            <div class="alert alert-danger m-2 mt-3 p-3">
                                Para definirles hítos a la tarea, esta debe tener no menos de dos días planificados 
                            </div>        
                        </div>
                    <?php } ?>

                    <?php
                    if ($cant_event > 1) {
                        $obj= new Ttarea($clink);
                        $obj->SetIdTarea($id_tarea);
                        $obj->Set();
                        $tarea= $obj->GetNombre();
                        $fecha_inicio= odbc2date($obj->GetFechaInicioPlan());
                        $fecha_fin= odbc2date($obj->GetFechaFinPlan());
                        ?>

                        <div class="alert alert-info">
                            <strong>Tarea:</strong> <?=$tarea?><br />
                            <strong>Duración:</strong> Desde: <?=$fecha_inicio?> Hasta: <?=$fecha_fin?>
                        </div>

                        <form id="ftarea_hito" action='javascript:ejecutar()' method="post">
                            <input type="hidden" id="menu" name="menu" value="tarea_hito" />
                            <input type="hidden" id="id_tarea" name="id_tarea" value="<?=$id_tarea?>" />
                            <input type="hidden" id="id" name="id" value="<?=$id_tarea?>" />

                            <?php
                            $obj_reg= new Tregtarea($clink);
                            if (!empty($id_tarea)) {
                                $obj_reg->SetIdTarea($id_tarea);
                                $obj_reg->Set();
                            }

                            $fecha_final_real = $obj_reg->GetFechaFinReal();

                            if (($if_jefe) && empty($fecha_final_real))
                                $visible = 'visible';
                            else
                                $visible = 'hidden';
                            ?>
                            <div id="toolbar2" class="btn-toolbar">
                                <label class="text mr-2">
                                    Agregar hitos o etapas a la tarea
                                </label>
                                <button id="btn_agregar" type="button" class="btn btn-primary" onclick="form_planning(0);" style="visibility:<?= $visible ?>;">
                                    <i class="fa fa-plus"></i>Nuevo Hito
                                </button>
                            </div>

                            <?php
                            $obj_reg->SetPlanning(1);
                            $obj_reg->SetYear(null);
                            if (!empty($id_tarea))
                                $result = $obj_reg->listar_reg(null, false, 'asc');

                            $i = 0;
                            while ($row = @$clink->fetch_array($result)) {
                            ?>
                                <input type="hidden" id="init_hit_<?= ++$i ?>" name="init_hit_<?= $i ?>" value="<?= $row['_id_code'] ?>" />
                            <?php } ?>


                            <script type='text/javascript'>
                                row_planning= [
                                    <?php
                                    $i = 0;
                                    @$clink->data_seek($result);
                                    while ($row = @$clink->fetch_array($result)) {
                                        if ($i > 0)
                                            echo ", ";
                                    ?>
                                        {
                                            id: <?= ++$i ?>,

                                            <?php if (empty($fecha_final_real) && empty($ifgrupo)) { ?>
                                            icon: ''+
                                                '<a href="#" class="btn btn-danger btn-sm" title="Eliminar" onclick="delete_planning(<?= $i ?>)">'+
                                                    '<i class="fa fa-trash"></i>Eliminar'+
                                                '</a>'+
                                                '<a href="#" class="btn btn-warning btn-sm" title="Editar" onclick="edit_planning(<?= $i ?>)">'+
                                                    '<i class="fa fa-pencil"></i>Editar'+
                                                '</a>'+
                                                '',
                                            <?php } ?>

                                            fecha: ''+
                                                '<?= odbc2date($row['reg_fecha']) ?>'+
                                                '<input type="hidden" id="fecha_<?= $i ?>" name="fecha_<?= $i ?>" value="<?= odbc2date($row['reg_fecha']) ?>" />'+
                                                '',

                                            real: ''+
                                                '<?= $row['valor'] ?>'+
                                                '<input type="hidden" id="real_<?= $i ?>" name="real_<?= $i ?>" value="<?= $row['valor'] ?>" />'+
                                                '',

                                            observacion: ''+
                                                '<?= nl2br($row['observacion']) ?>'+
                                                '<input type="hidden" id="observacion_<?= $i ?>" name="observacion_<?= $i ?>" value="<?= $row['observacion'] ?>" />'+
                                                ''
                                        }
                                    <?php } ?>
                                ];
                            </script>

                            <table id="table-planning" class="table table-hover table-striped"
                                data-toggle="table"
                                data-height="450"
                                data-toolbar="#toolbar2"
                                data-row-style="rowStyle"
                                data-search="true">
                                <thead>
                                    <tr>
                                        <th data-field="id">No.</th>
                                        <?php if (empty($fecha_final_real) && empty($ifgrupo)) { ?>
                                            <th data-field="icon"></th>
                                        <?php } ?>
                                        <th id="table-planning-fecha" data-field="fecha">FECHA</th>
                                        <th data-field="real">%</th>
                                        <th data-field="observacion">OBSERVACIÓN</th>
                                    </tr>
                                </thead>
                            </table>

                            <hr style="margin: 6px 0px 25px 0px;"></hr>

                            <input type="hidden" id="cant_hits" name="cant_hits" value="<?= $i ?>" />

                            <!-- div-ajax-panel -->
                            <div id="div-ajax-panel" class="ajax-panel" data-bind="draganddrop">

                            </div>
                            <!-- div-ajax-panel -->

                            <!-- div-panel-hito -->
                            <div id="div-panel-hito" class="card card-primary ajax-panel win-board" data-bind="draganddrop">
                                <div class="card-header">
                                    <div class="row">
                                        <div class="panel-title ajax-title col-11 m-0 win-drag">HITO</div>
                                        <div class="col-1 m-0">
                                            <div class="close">
                                                <a href="#" onclick="CloseWindow('div-panel-hito')">
                                                    <i class="fa fa-close"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="ajax-hito" class="card-body">

                                </div>
                            </div> <!-- div-panel-hito -->                            
                            <?php } ?>

                            <!-- buttom -->
                            <div id="_submit" class="btn-block btn-app">
                                <?php if ($if_jefe && $cant_event > 1) { ?>
                                    <button class="btn btn-primary" type="submit">Aceptar</button>
                                <?php } ?>
                                <button class="btn btn-warning" type="button" onclick="self.close();">
                                Cerrar
                                </button>
                                <button class="btn btn-danger" type="button" onclick="open_help_window('../help/manual.html#listas')">
                                Ayuda
                                </button>
                            </div>

                            <div id="_submited" style="display:none">
                                <img src="../img/loading.gif" alt="cargando" />     Por favor espere ..........................
                            </div>

                        </form>
                    </div> <!-- panel-body -->
                </div>
            </div>
       </div>

    </body>
</html>
