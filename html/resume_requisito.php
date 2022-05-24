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

require_once "../php/class/document.class.php";
require_once "../php/class/register_nota.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/tipo_auditoria.class.php";
require_once "../php/class/tipo_lista.class.php";
require_once "../php/class/lista.class.php";
require_once "../php/class/lista_requisito.class.php";

require_once "../php/class/code.class.php";

$signal = !empty($_GET['signal']) ? $_GET['signal'] : 'flista';
$action = !empty($_GET['action']) ? $_GET['action'] : 'list';
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$id_proceso = !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$year = !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_auditoria = !is_null($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;
$id_tipo_lista = !is_null($_GET['id_tipo_lista']) && strlen($_GET['id_tipo_lista']) > 0 ? $_GET['id_tipo_lista'] : null;
$id_lista = !is_null($_GET['id_lista']) ? $_GET['id_lista'] : null;
$componente = !is_null($_GET['componente']) && strlen($_GET['componente']) > 0 ? $_GET['componente'] : null;
$id_capitulo = !is_null($_GET['id_capitulo']) && strlen($_GET['id_capitulo']) > 0 ? $_GET['id_capitulo'] : null;

$obj_lista = new Tlista($clink);
$obj_lista->SetYear($year);
$obj_lista->Set($id_lista);
$id_lista_code = $obj_lista->get_id_code();
$nombre_lista = $obj_lista->GetNombre();

$obj_audit = new Tauditoria($clink);
$obj_audit->SetYear($year);
$obj_audit->SetIdAuditoria($id_auditoria);
$obj_audit->Set();
$id_auditoria_code = $obj_audit->get_id_code();
$id_tipo_auditoria = $obj_audit->GetIdTipo_auditoria();

$fecha_inicio = $obj_audit->GetFechaInicioPlan();
$fecha_fin = $obj_audit->GetFechaFinPlan();

$obj_tipo_audit = new Ttipo_auditoria($clink);
$obj_tipo_audit->SetYear($year);
$obj_tipo_audit->SetIdTipo_auditoria($id_tipo_auditoria);
$obj_tipo_audit->Set();
$nombre_auditoria = $obj_tipo_audit->GetNombre();
$nombre_auditoria .= "<strong style='margin-left:30px;'>Inicia: </strong>" . odbc2date($fecha_inicio) . " <strong>Finaliza: </strong>" . odbc2date($fecha_fin);

$obj = new Tlista_requisito($clink);
$obj->SetYear($year);

$obj_prs = new Tproceso($clink);

$obj_reg = new Tregister_nota($clink);
$obj_reg->SetYear($year);
$obj_reg->SetIdAuditoria($id_auditoria);
$obj_reg->SetIdProceso($id_proceso);
$obj_reg->SetIdLista($id_lista);

$array_files = null;

$url_page = "../html/resume_requisito.php?signal=$signal&action=$action&menu=frequisito&exect=$action&indicacion=$indicacion";
$url_page .= "&year=$year&evidencia=$evidencia&componente=$componente&id_tipo_lista=$id_tipo_lista&id_lista=$id_lista";
$url_page .= "&id_auditoria=$id_auditoria&id_proceso=$id_proceso&id_capitulo=$id_capitulo";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>RESUMEN DE LA APLICACIÓN DE GUÍA DE CONTROL</title>

    <?php require '../form/inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/btn-toolbar/btn-toolbar.css" />
    <script type="text/javascript" src="../libs/btn-toolbar/btn-toolbar.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/general.css?version=">
    <link rel="stylesheet" type="text/css" href="../css/custom.css?version=">
    <link rel="stylesheet" type="text/css" href="../css/alarm.css" />

    <link rel="stylesheet" type="text/css" href="../css/lista.css" />
    <script type="text/javascript" src="../js/lista.js" charset="utf-8"></script>

    <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/data.js"></script>
    <script type="text/javascript" src="../libs/hichart/js/modules/drilldown.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/widget.css?version=">
    <script type="text/javascript" src="../js/widget.js?version=" charset="utf-8"></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/form.js?version="></script>

    <style type="text/css">
        body {
            background: #fff;
        }
        table {
            border: 1px solid #ccc;
            margin: 0px 10px 0px 10px;
            border: 1px solid #ccc;
            width: 100%;
            margin-top: 20px;
        }
        th,
        td {
            border: 1px solid #ccc;
            border: 1px solid black;
            padding: 4px;
        }
        th {
            color: #000000;
            font-weight: bolder;
            text-align: left;
        }
        td {
            text-align: center;
        }
        .title {
            font-weight: bold;
        }
        td.title {
            text-align: left;
        }
        .container {
            width: 100px;
            height: 100px;
        }
    </style>

    <script language="javascript" type="text/javascript">
        function closep(error) {
            var year = $('#year').val();
            var id_proceso = $('#id_proceso').val()
            var id_auditoria = $('#id_auditoria').val()
            var id_lista = $('#id_lista').val();

            var url = "../html/nota.php?id_lista=" + id_lista + '&id_auditoria=' + id_auditoria;
            url += '&year=' + year + '&id_proceso=' + id_proceso;
            self.location.href = url;
        }

        function imprimir() {
            var url= set_url();
        
            url= '../print/resume_requisito.php' + url;
            prnpage = window.open(url, "IMPRIMIENDO RESUMEN DE GUIA DE CONTROL",
                        "width=900,height=600,toolbar=no,location=no, scrollbars=yes");                                
        }

        function drownpie(id, title, data) {
            // Create the chart
            $('#container_' + id).highcharts({
                chart: {
                    type: 'pie'
                },
                title: {
                    text: title
                },
                plotOptions: {
                    series: {
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y:.1f}%'
                        }
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:11px; text-align: left;">{series.name}</span><br>',
                    pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>'
                },

                series: [{
                    type: 'pie',
                    name: 'Brands',
                    colorByPoint: true,
                    data: data
                }],
            });
        }
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-third" class="app-nav d-none d-md-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <a href="#" class="navbar-brand">
                    RESUMEN DE REQUISITOS
                </a>                
            </li>

            <li class="col">
                <label class="badge badge-success">
                    <?= $year ?>
                </label>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label col-5">Muestra:</label>
                    <div id="nshow" class="badge badge-warning">0</div>
                </div>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label ml-3">Ocultas:</label>
                    <div id="nhide" class="badge badge-warning">0</div>                            
                </div>
            </li>

            <li class="nav-item col-auto d-none d-md-block">
                <a href="#" class="" onclick="imprimir()">
                    <i class="fa fa-print"></i>Imprimir
                </a>
            </li>  

            <li>
                <div class="col-auto justify-content-end">
                    <ul class="navbar-nav list-group-horizontal mr-auto"> 
                        <li class="list-group-item">
                            <a href="#" onclick="open_help_window('<?= $help ?>')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>

                        <li class="list-group-item">
                            <a href="#" onclick="closep()">
                                <i class="fa fa-close"></i>Cerrar
                            </a>
                        </li>
                    </ul>
                </div>  
            </li>  
        </ul>
    </div>
    

    <div class="app-body container-fluid onebar">
        <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
        <input type="hidden" name="menu" id="menu" value="flista_register" />

        <input type="hidden" id="_nhide" value="0" />

        <input type="hidden" id="id_auditoria" name="id_auditoria" value="<?= $id_auditoria ?>" />
        <input type="hidden" id="id_lista" name="id_lista" value="<?= $id_lista ?>" />
        <input type="hidden" id="id_lista_code" name="id_lista_code" value="<?= $id_lista_code ?>" />

        <input type="hidden" name="id_tipo_lista" id="id_tipo_lista"
            value="<?= !empty($id_tipo_lista) ? $id_tipo_lista : 0 ?>" />
        <input type="hidden" name="id_componente" id="id_componente"
            value="<?= !empty($componente) ? $componente : 0 ?>" />
        <input type="hidden" name="id_capitulo" id="id_capitulo"
            value="<?= !empty($id_capitulo) ? $id_capitulo : 0 ?>" />
        <input type="hidden" name="id_subapitulo" id="id_subcapitulo"
            value="<?= !empty($id_subcapitulo) ? $id_subcapitulo : 0 ?>" />

        <input type="hidden" id="id_proceso" name="id_proceso" value="<?= $id_proceso ?>" />

        <input type= "hidden" id="if_jefe" name= "if_jefe" value="0" />
        <input type= "hidden" id="inicio" name= "inicio" value="0" />
        <input type= "hidden" id="fin" name= "fin" value="0" />

        <input type="hidden" id="year" name="year" value="<?=$year?>" />
        <input type="hidden" id="_year" name="_year" value="<?=$year?>" />

        <div class="alert alert-info" style="margin: 1px 5px 4px 5px;">
            <div class="row">
                <div class="col-2" style="font-weight: bold">Acción de Control:</div>
                <div class="col-10 pull-left"><?= textparse($nombre_auditoria) ?></div>
            </div>
        </div>
        <div class="alert alert-info" style="margin: 1px 5px 4px 5px;">
            <div class="row">
                <div class="col-2" style="font-weight: bold">Lista de chequeo:</div>
                <div class="col-10 pull-left"><?= textparse($nombre_lista) ?></div>
            </div>
        </div>

        <?php
        for ($i = 0; $i < 6; $i++) {
            $total[$i] = 0;
            $no_procede[$i] = 0;
            $no_se_cumple[$i] = 0;
            $en_proceso[$i] = 0;
            $se_cumple[$i] = 0;
            $no_definido[$i] = 0;
        }

        $obj = new Tlista_requisito($clink);
        $obj->SetIdAuditoria($id_auditoria);
        $obj->SetIdLista($id_lista);
        $obj->SetYear($year);
        $obj->SetIdProceso($id_proceso);
        $obj->SetComponente($componente);
        $obj->SetIdCapitulo($id_capitulo);

        $result = $obj->listar();

        $i = 0;
        $j = 0;
        $nshow = 0;
        $array_ids= array();
        while ($row = $clink->fetch_array($result)) {
            if (isset($array_ids[$row['_id']]))
                continue;
            $array_ids[$row['_id']] = 1;
            ++$nshow;

            $icomponente = !is_null($row['componente']) ? (int)$row['componente'] : 0;
            ++$total[$icomponente];
            ++$total[0];

            $obj_reg->SetIdAuditoria($id_auditoria);
            $obj_reg->SetIdRequisito($row['_id']);
            $obj_reg->SetChkApply(true);
            $array = $obj_reg->getNota_reg();

            switch ($array['cumplimiento']) {
                case _NO_PROCEDE:
                    ++$no_procede[0];
                    ++$no_procede[$icomponente];
                    break;
                case _NO_SE_CUMPLE:
                    ++$no_se_cumple[0];
                    ++$no_se_cumple[$icomponente];
                    break;
                case _EN_PROCESO:
                    ++$en_proceso[0];
                    ++$en_proceso[$icomponente];
                    break;
                case _SE_CUMPLE:
                    ++$se_cumple[0];
                    ++$se_cumple[$icomponente];
                    break;
                default:
                    ++$no_definido[0];
                    ++$no_definido[$icomponente];
                    break;
            }
        }
        ?>

        <div class="row col-8 mb-4">
            <table>
                <thead>
                    <tr>
                        <th>COMPONENTE</th>
                        <th>TOTAL</th>
                        <?php if ($year < 2021) { ?>
                        <th>NO PROCEDE</th>
                        <?php } ?>
                        <th>NO SE CUMPLE</th>
                        <?php if ($year < 2021) { ?>
                        <th>EN PROCESO</th>
                        <?php } ?>
                        <th>SE CUMPLE</th>
                        <th>NO DEFINIDO</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="title">AMBIENTE DE CONTROL</td>
                        <td><?= $total[1] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[1] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[1] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[1] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[1] ?></td>
                        <td><?= $no_definido[1] ?></td>
                    </tr>
                    <tr>
                        <td class="title">GESTIÓN Y PREVENCIÓN DE RIESGO</td>
                        <td><?= $total[2] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[2] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[2] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[2] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[2] ?></td>
                        <td><?= $no_definido[2] ?></td>
                    </tr>
                    <tr>
                        <td class="title">ACTIVIDADES DE CONTROL</td>
                        <td><?= $total[3] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[3] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[3] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[3] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[3] ?></td>
                        <td><?= $no_definido[3] ?></td>
                    </tr>
                    <tr>
                        <td class="title">INFORMACIÓN Y COMUNICACIÓN</td>
                        <td><?= $total[4] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[4] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[4] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[4] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[4] ?></td>
                        <td><?= $no_definido[4] ?></td>
                    </tr>
                    <tr>
                        <td class="title">SUPERVISIÓN Y MONITOREO</td>
                        <td><?= $total[5] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[5] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[5] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[5] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[5] ?></td>
                        <td><?= $no_definido[5] ?></td>
                    </tr>
                    <tr>
                        <td class="title">TOTAL</td>
                        <td><?= $total[0] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $no_procede[0] ?></td>
                        <?php } ?>
                        <td><?= $no_se_cumple[0] ?></td>
                        <?php if ($year < 2021) { ?>
                        <td><?= $en_proceso[0] ?></td>
                        <?php } ?>
                        <td><?= $se_cumple[0] ?></td>
                        <td><?= $no_definido[0] ?></td>
                    </tr>
                </tbody>

            </table>
        </div>

        <div class="row col-12">
            <div id="container_0" class="col-6">
            </div>
            <div id="container_1" class="col-6">
            </div>
        </div>

        <div class="row col-12">
            <div id="container_2" class="col-6">
            </div>
            <div id="container_3" class="col-6">
            </div>
        </div>

        <div class="row col-12">
            <div id="container_4" class="col-6">
            </div>
            <div id="container_5" class="col-6">
            </div>
        </div>


        <script type="text/javascript">
            <?php for ($i = 0; $i < 6; $i++) { ?>
                data_<?= $i ?> = [
                    <?php if ($year < 2021) { ?>
                    ['En proceso', <?= $total[$i] ? ($en_proceso[$i]/$total[$i])*100 : 0 ?>],
                    ['No procede', <?= $total[$i] ? ($no_procede[$i]/$total[$i])*100 : 0 ?>],
                    <?php } ?>
                    {
                        name: 'Se cumple',
                        y: <?= $total[$i] ? ($se_cumple[$i]/$total[$i])*100 : 0 ?>,
                        sliced: true,
                        selected: true
                    },
                    ['No se cumple', <?= $total[$i] ? ($no_se_cumple[$i]/$total[$i])*100 : 0 ?>]
                ];
            <?php } ?>

            drownpie(0, "RESUMEN GENERAL", data_0);

            drownpie(1, "AMBIENTE DE CONTROL", data_1);

            drownpie(2, "GESTIÓN Y PREVENCIÓN DE RIESGO", data_2);

            drownpie(3, "ACTIVIDADES DE CONTROL", data_3);

            drownpie(4, "INFORMACIÓN Y COMUNICACIÓN", data_4);

            drownpie(5, "SUPERVISIÓN Y MONITOREO", data_5);
        </script>


        <script type="text/javascript" language="JavaScript">
            document.getElementById('nshow').innerHTML = '<?= $nshow ?>';
            document.getElementById('nhide').innerHTML = '<?= $nhide ?>';
        </script>
    </div>

</body>

</html>

<?php $_SESSION['obj'] = serialize($obj); ?>