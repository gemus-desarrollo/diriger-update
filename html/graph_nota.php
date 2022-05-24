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
require_once "../php/class/usuario.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/plan_ci.class.php";
require_once "../php/class/nota.class.php";

require_once "../form/class/nota.signal.class.php";

$_SESSION['debug']= 'no';
$signal= 'graph_nota';

require_once "../php/inc_escenario_init.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_proceso'];
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

if (empty($month) || $month == -1 || $month == 13)
    $month= 12;
$show_all_notes= !empty($_GET['show_all_notes']) ? 1 : 0;

$fin= $actual_year + 2;
if ($year == $actual_year)
    $end_month= $actual_month;
else
    $end_month= 12;

$obj= new Tnota($clink);
$obj_signal= new Tnota_signals($clink);

$obj_user= new Tusuario($clink);

$obj->SetDay(NULL);
$obj->SetMonth($month);
$obj->SetYear($year);

$date_cut= $year.'-'.str_pad($month,'0',2,STR_PAD_LEFT).'-'.str_pad($day,'0',2,STR_PAD_LEFT);

$noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf= 1;
    $mej= 1;
    $observ= 1;
}

$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;

$url_page= "../html/nota.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso&year=$year";
$url_page.= "&month=$month&day=$day&noconf=$noconf&mej=$mej&observ=$observ&id_auditoria=$id_auditoria";
$url_page.= "&show_all_notes=$show_all_notes";

set_page($url_page);
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
        <?php 
        if (!$auto_refresh_stop && (is_null($_SESSION['debug']) || $_SESSION['debug'] == 'no')) { 
            $delay= (int)$config->delay*60;
            header("Refresh: $delay; url=$url_page&csfr_token=123abc"); 
        } 
        ?>

        <title>GRAFICO DE NOTAS DE HALLAZGOS</title>

        <?php
        $acc= $_SESSION['acc_planheal'];
        $tipo_plan= _PLAN_TIPO_ACCION;

        require_once "inc/_tablero_top_riesgo.inc.php";
        ?>

        <script type="text/javascript" src="../libs/hichart/js/highcharts.js"></script>

        <style>
            body {
                background: white;
            }
            .prs-text {
                color: #828282;
                font-weight: bold;
            }
        </style>

        <script type="text/javascript">
            function close() {
                var noconf= 0, mej= 0, observ= 0;
                var url= "nota.php?";

                if ($('#mej').is(':checked'))
                    mej= 1 ;
                if ($('#observ').is(':checked'))
                    observ= 1 ;
                if ($('#noconf').is(':checked'))
                    noconf= 1

                url+= '&noconf='+noconf+'&mej='+mej+'&observ='+observ+'&id_auditoria='+$('#id_auditoria').val();
                self.location= url;
            }
        </script>
    </head>

    <body>
        <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

        <?php
        $obj_proceso= new Tproceso($clink);
        $obj_proceso->SetIdProceso($id_proceso);
        $obj_proceso->Set();
        $id_proceso_code= $obj_proceso->get_id_code();
        $tipo= $obj_proceso->GetTipo();
        $nombre= $obj_proceso->GetNombre().' ('.$Ttipo_proceso_array[$obj_proceso->GetTipo()].')';
        
        include_once "../form/inc/_riesgo_top_div.inc.php";
        ?>
        
        <input type="hidden" id="id_auditoria" name="id_auditoria" value="<?=$id_auditoria?>" />
        <input type="hidden" id="id_auditoria_code" name="id_auditoria_code" value="" />

        <!-- app-body -->
        <div class="app-body dashboard container-fluid sixthbar" style="background: white;">
            <?php
            $cant_show= 0;
            $cant_print_reject= 0;

            $obj_event= new Tevento($clink);

            if (!empty($id_proceso) && !is_null($array_procesos)) {
                $obj->SetTipo(null);
                $obj->SetIdProceso(null);
                $obj->SetYear($year);
                $obj->SetMonth(null);
                $obj->SetIdAuditoria($id_auditoria);
                $obj->SetChkApply(true);
                
                $obj_prs= new Tproceso($clink);
                $obj_prs->SetYear($year);
                $obj_prs->SetIdProceso($id_proceso);

                $obj_prs->get_procesos_down(null, null, null, true);
                $array_procesos_down= $obj_prs->array_cascade_down;

                if (isset($array)) unset($array);
                $array= array();
                foreach ($array_procesos_down as $key => $prs)
                    $array[]= $key;

                $obj->set_show_all_notes($show_all_notes);
                $result= $obj->listar($noconf, $mej, $observ, true, $array);

                if (count($array) == 1) {
                    $obj->SetIdProceso($id_proceso);
                    $obj->set_id_proceso_code($id_proceso_code);
                }

                $ranking= $obj->list_ranking($result, $config->automatic_note);

                for ($i= 1; $i < 13; $i++)
                    $array_month[$i]= array(0, 0);

                $total= 0;
                $cerradas= 0;
                foreach ($ranking as $nota) {
                    ++$total;
                    $mm= (int)date('m', strtotime($nota['fecha']));
                    $array_month[$mm][0]+= 1;
                    if ($nota['estado'] == _CERRADA) {
                        ++$cerradas;
                        $array_month[$mm][1]+=1;
                    }
                }
                ?>

                <div class="container" style="margin-top: 20px;">
                    <div class="row">
                        <div class="col-md-3 col-lg-3">
                            <div class="alert alert-info">
                                <strong>Total:</strong> <?=$total?>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3">
                            <div class="alert alert-info">
                                <strong>Cerradas:</strong> <?=$cerradas?>
                            </div>
                        </div>
                        <div class="col-md-3 col-lg-3">
                            <div class="alert alert-info">
                                <strong>Porciento:</strong> <?= number_format(($cerradas/$total)*100, 2)?>%
                            </div>
                        </div>

                    </div>
                </div>

                <?php
                if ($noconf && $observ && $mej)
                    $title= "Notas de hallazgos";
                else {
                    $i= 0;
                    if ($noconf) {
                        ++$i;
                        $title= "No Conformidades";
                    }
                    if ($observ) {
                        $title.= $i ? ", " : null;
                        ++$i;
                        $title.= "Observaciones ";
                    }
                    if ($mej) {
                        $title.= $i ? ", " : null;
                        $title.= "Notas de mejoras";
                    }
                }
                ?>

                <script type="text/javascript">
                    $(function () {
                        $('#container').highcharts({
                            chart: {
                                type: 'column'
                            },
                            title: {
                                text: '<?=$title?>'
                            },
                            xAxis: {
                                categories: [
                                    'Ene',
                                    'Feb',
                                    'Mar',
                                    'Abr',
                                    'May',
                                    'Jun',
                                    'Jul',
                                    'Ago',
                                    'Sep',
                                    'Oct',
                                    'Nov',
                                    'Dic'
                                ]
                            },
                            yAxis: {
                                min: 0,
                                title: {
                                    text: 'Cantidad'
                                }
                            },
                            tooltip: {
                                headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                                pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                                    '<td style="padding:0"><b>{point.y:.1f}</b></td></tr>',
                                footerFormat: '</table>',
                                shared: true,
                                useHTML: true
                            },
                            plotOptions: {
                                column: {
                                    pointPadding: 0.2,
                                    borderWidth: 0
                                }
                            },
                            series: [{
                                name: 'Detectadas',
                                data: [
                                    <?php
                                    for ($i= 1; $i < 13; $i++) {
                                        echo $i > 1 ? "," : null;
                                        echo $array_month[$i][0];
                                    }
                                    ?>
                                ]
                            }, {
                                name: 'Cerradas',
                                data: [
                                    <?php
                                    for ($i= 1; $i < 13; $i++) {
                                        echo $i > 1 ? "," : null;
                                        echo $array_month[$i][1];
                                    }
                                    ?>
                                ]
                            }]
                        });
                    });
                </script>

                <div id="container" style="min-width: 310px; height: 400px; margin: 0 auto"></div>

            <?php } else { ?>
                <div class="container-fluid">
                    <div class="alert alert-danger">
                        <h1>USTED NO TIENE ACCESO A NINGÚN PROCESO INTERNO. DEBE CONSULTAR AL ADMINISTRADOR DEL SISTEMA.</h1>
                    </div>
                </div>
            <?php } ?>
        </div> <!-- app-body -->

        <script type="text/javascript">
            document.getElementById('nshow').innerHTML= <?=$total?>;
            document.getElementById('nhide').innerHTML= <?=$cant_print_reject?>;
        </script>


        <!-- Panel2 -->
        <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
            <div class="card-header">
                <div class="row">
                    <div class="panel-title win-drag col-10">FILTRADO DE NOTAS</div>
                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href= "javascript:CloseWindow('div-filter');" title="cerrar ventana">
                               <i class="fa fa-close"></i>
                           </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body form">
                <div class="col-md-12">
                    <label class="checkbox text">
                        <input type="checkbox" name="noconf" id="noconf" value="1" <?php if (!empty($noconf)) echo "checked='checked'" ?> />
                        Mostrar las No-conformidades
                    </label>
                    <label class="checkbox text">
                        <input type="checkbox" name="observ" id="observ" value="1" <?php if (!empty($observ)) echo "checked='checked'" ?> />
                        Mostrar las Observaciones
                    </label>
                    <label class="checkbox text">
                        <input type="checkbox" name="mej" id="mej" value="1" <?php if (!empty($mej)) echo "checked='checked'" ?> />
                        Mostrar las Notas de Mejora
                    </label>

                    <hr/>
                    <!-- buttom -->
                    <div id="_submit" class="btn-block btn-app">
                        <button class="btn btn-primary" type="button" onclick="refreshp()">Aceptar</button>
                        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-filter')">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
        <!--panel2 -->


        <?php include_once "inc/_tablero_bottom_riesgo.inc.php"; ?>

         <div id="div-ajax-panel" data-bind="draganddrop">

        </div>
    </body>
</html>



