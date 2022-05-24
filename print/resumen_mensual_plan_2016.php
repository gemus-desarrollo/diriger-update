<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2016
 */


isession_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";

require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/evento.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'list' || $action == 'edit') {
    if (isset($_SESSION['obj']))  
        unset($_SESSION['obj']);
}

$year= $_GET['year'];
$month= $_GET['month'];
$id_proceso= $_GET['id_proceso'];
$print_reject= $_GET['print_reject'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj= new Tplantrab($clink);
$notes= null;

$array= $obj->cumulative_plan($month);

$total_cumulative= 0;
$cumplidas_cumulative= 0;
$incumplidas_cumulative= 0;
$canceladas_cumulative= 0;
$modificadas_cumulative= 0;
$delegadas_cumulative= 0;
$reprogramadas_cumulative= 0;

$efectivas_cumulative= 0;
$efectivas_cumplidas_cumulative= 0;
$efectivas_incumplidas_cumulative= 0;
$efectivas_canceladas_cumulative= 0;

$externas_cumulative= 0;

$extras_cumulative= 0;
$extras_externas_cumulative= 0;
$extras_propias_cumulative= 0;

$anual_cumulative= 0;
$anual_propias_cumulative= 0;
$anual_externas_cumulative= 0;

$mensual_cumulative= 0;
$mensual_propias_cumulative= 0;
$mensual_externas_cumulative= 0;

for ($i= 1; $i < $month; $i++) {
    if ($array[$i]['aprobado']) {
        $total_cumulative+= $obj->total_cumulative;
        $cumplidas_cumulative+= $array[$i]['cumplidas'];
        $incumplidas_cumulative+= $array[$i]['incumplidas'];
        $canceladas_cumulative+= $array[$i]['canceladas'];
        $modificadas_cumulative+= $array[$i]['modificadas'];
        $delegadas_cumulative+= $array[$i]['delegadas'];
        $reprogramadas_cumulative+= $array[$i]['reprogramadas'];

        $efectivas_cumulative+= $array[$i]['efectivas'];
        $efectivas_cumplidas_cumulative+= $array[$i]['efectivas_cumplidas'];
        $efectivas_incumplidas_cumulative+= $array[$i]['efectivas_incumplidas'];
        $efectivas_canceladas_cumulative+= $array[$i]['efectivas_canceladas'];

        $externas_cumulative+= $array[$i]['externas'];

        $extras_cumulative+= $array[$i]['extras'];
        $extras_externas_cumulative+= $array[$i]['extras_externas'];
        $extras_propias_cumulative+= $array[$i]['extras_propias'];

        $anual_cumulative+= $array[$i]['anual'];
        $anual_propias_cumulative+= $array[$i]['anual_propias'];
        $anual_externas_cumulative+= $array[$i]['anual_externas'];

        $mensual_cumulative+= $array[$i]['mensual'];
        $mensual_propias_cumulative+= $array[$i]['mensual_propias'];
        $mensual_externas_cumulative+= $array[$i]['mensual_externas'];

    } else {
        $notes.= "<strong>{$meses_array[$i]}:</strong> El Plan Mensual no ha sido aprobado.<br />";
    }
}

$obj= new Tplantrab($clink);

$obj->SetIdProceso($id_proceso);
$obj->SetYear($year);
$obj->SetIfEmpresarial(1);
$obj->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_MENSUAL);

$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetRole(NULL);

$obj->SetDay(NULL);
$obj->SetMonth($month);

$obj->toshow= _EVENTO_MENSUAL;

$obj->Set();

$obj_user= new Tusuario($clink);

$date_aprb= $obj->GetAprobado();
$id_aprobado= $obj->GetIdResponsable_aprb();
$array_aprb= $obj_user->GetEmail($id_aprobado);

$date_eval= $obj->GetEvaluado();
$array_eval= $obj_user->GetEmail($obj->GetIdResponsable_eval());
$cumplimiento= $obj->GetCumplimiento();

if ((!empty($date_eval)) && !is_null($obj->anual)) {
    $date= !empty($date_eval) ? $date_eval : $date_aprb;
    $date= odbc2time_ampm($date);
    $text= !empty($date_eval) ? "Evaluado" : "Aprobado";
    $note= "<p>Este plan ya fue $text en fecha $date. Los datos que se muestran en el resumen se corresponden ";
    $note.= "con dicho momento. <br/>Para actualizar la información deberá evaluar nuevamente el plan.</p>";

} else {
    $obj->SetIdProceso($id_proceso);
    $obj->SetIdResponsable(NULL);
    $obj->SetIdUsuario(NULL);
    $obj->SetRole(NULL);

    $obj->SetDay(NULL);
    $obj->SetMonth($month);
    $obj->SetYear($year);

    $obj->SetIfEmpresarial(NULL);
    $obj->toshow= _EVENTO_MENSUAL;
    $obj->SetCumplimiento(null);

    $obj->set_print_reject($print_reject);
    $obj->list_reg($obj->toshow);

    $print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_OUT;

    $obj_signal= new Tevento_signals($clink);
    $obj_signal->print_reject= $print_reject;
}

$array= $obj->cumulative_plan();

$total_cumulative= 0;
$cumplidas_cumulative= 0;
$incumplidas_cumulative= 0;
$canceladas_cumulative= 0;
$modificadas_cumulative= 0;
$delegadas_cumulative= 0;
$reprogramadas_cumulative= 0;

$efectivas_cumulative= 0;
$efectivas_cumplidas_cumulative= 0;
$efectivas_incumplidas_cumulative= 0;
$efectivas_canceladas_cumulative= 0;

$externas_cumulative= 0;

$extras_cumulative= 0;
$extras_externas_cumulative= 0;
$extras_propias_cumulative= 0;

$anual_cumulative= 0;
$anual_propias_cumulative= 0;
$anual_externas_cumulative= 0;

$mensual_cumulative= 0;
$mensual_propias_cumulative= 0;
$mensual_externas_cumulative= 0;

for ($i= 1; $i < $month; $i++) {
    if ($array[$i]['aprobado']) {
        $total_cumulative+= $obj->total_cumulative;
        $cumplidas_cumulative+= $array[$i]['cumplidas'];
        $incumplidas_cumulative+= $array[$i]['incumplidas'];
        $canceladas_cumulative+= $array[$i]['canceladas'];
        $modificadas_cumulative+= $array[$i]['modificadas'];
        $delegadas_cumulative+= $array[$i]['delegadas'];
        $reprogramadas_cumulative+= $array[$i]['reprogramadas'];

        $efectivas_cumulative+= $array[$i]['efectivas'];
        $efectivas_cumplidas_cumulative+= $array[$i]['efectivas_cumplidas'];
        $efectivas_incumplidas_cumulative+= $array[$i]['efectivas_incumplidas'];
        $efectivas_canceladas_cumulative+= $array[$i]['efectivas_canceladas'];

        $externas_cumulative+= $array[$i]['externas'];

        $extras_cumulative+= $array[$i]['extras'];
        $extras_externas_cumulative+= $array[$i]['extras_externas'];
        $extras_propias_cumulative+= $array[$i]['extras_propias'];

        $anual_cumulative+= $array[$i]['anual'];
        $anual_propias_cumulative+= $array[$i]['anual_propias'];
        $anual_externas_cumulative+= $array[$i]['anual_externas'];

        $mensual_cumulative+= $array[$i]['mensual'];
        $mensual_propias_cumulative+= $array[$i]['mensual_propias'];
        $mensual_externas_cumulative+= $array[$i]['mensual_externas'];

    } else {
        $notes.= "<strong>{$meses_array[$i]}:</strong> El Plan Mensual no ha sido aprobado.<br />";
    }
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DEL CUMPLIMIENTO DEL  PLAN DE TRABAJO", "Corresponde a periodo mes/año: $month/$year");
?>

<html>
    <head>
        <title>PLAN MENSUAL MODELO 2016</title>
        
         <?php include "inc/print.ini.php";?>	

        <style type="text/css">
            .cell {
                width:100px;
                text-align:center;
                vertical-align:text-top;
            }
        </style>

        <center>
            <strong>RESUMEN DEL CUMPLIMIENTO DEL  PLAN DE TRABAJO DEL MES DE:</strong>  <?=strtoupper($meses_array[(int)$month])?>, <?=$year?><br />
            <strong>ENTIDAD:</strong>  <?=$proceso?><br />
            <br />
            <table width="800px" border="1" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th colspan="13">TAREAS PLANIFICADAS</th>
                    </tr>
                    <tr>
                        <th class="plhead" colspan="2" rowspan="2">TOTAL TAREAS DEL PLAN MENSUAL</th>
                        <th class="plhead" colspan="5">DEL PLAN ANUAL PARA EL MES</th>
                        <th class="plhead" colspan="5">NUEVAS TAREAS INCORPORADAS  EN LA  PUNTUALIZACIÓN MENSUAL</th>
                        <th class="plhead" rowspan="2">% INCORPORADAS vs PLAN ANUAL</th>
                    </tr>
                    <tr>
                        <th class="plhead">SUB TOTAL</th>
                        <th class="plhead">EXTERNAS (Nivel igual o superior)</th>
                        <th class="plhead">%</th>
                        <th class="plhead">PROPIAS</th>
                        <th class="plhead">%</th>
                        <th class="plhead">SUB TOTAL</th>
                        <th class="plhead">EXTERNAS (Nivel igual o superior)</th>
                        <th class="plhead">%</th>
                        <th class="plhead">PROPIAS</th>
                        <th class="plhead">%</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">MES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?=$obj->anual?></td>
                        <td class="plinner"><?=$obj->anual_externas?></td>
                        <td class="plinner"><?= number_format(($obj->anual_externas/(float)$obj->anual)*100,1) ?></td>
                        <td class="plinner"><?=$obj->anual_propias?></td>
                        <td class="plinner"><?= number_format(($obj->anual_propias/(float)$obj->anual)*100,1) ?></td>
                        <td class="plinner"><?=$obj->mensual?></td>
                        <td class="plinner"><?=$obj->mensual_externas?></td>
                        <td class="plinner"><?= number_format(($obj->mensual_externas/(float)$obj->mensual)*100,1) ?></td>
                        <td class="plinner"><?=$obj->mensual_propias?></td>
                        <td class="plinner"><?= number_format(($obj->mensual_propias/(float)$obj->mensual)*100,1) ?></td>
                        <td class="plinner"><?= number_format(($obj->mensual/(float)$obj->total)*100,1) ?></td>
                    </tr>
                    <tr>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">ACUMULADAS DE MESES ANTERIORES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?=$anual_cumulative?></td>
                        <td class="plinner"><?=$anual_externas_cumulative?></td>
                        <td class="plinner"><?= number_format(($anual_externas_cumulative/(float)$anual_cumulative)*100,1) ?></td>
                        <td class="plinner"><?=$anual_propias_cumulative?></td>
                        <td class="plinner"><?= number_format(($anual_propias_cumulative/(float)$anual_cumulative)*100,1) ?></td>
                        <td class="plinner"><?=$mensual_cumulative?></td>
                        <td class="plinner"><?=$mensual_externas_cumulative?></td>
                        <td class="plinner"><?= number_format(($mensual_externas_cumulative/(float)$mensual_cumulative)*100,1) ?></td>
                        <td class="plinner"><?=$mensual_propias_cumulative?></td>
                        <td class="plinner"><?= number_format(($mensual_propias_cumulative/(float)$mensual_cumulative)*100,1) ?></td>
                        <td class="plinner"><?= number_format(($mensual_cumulative/(float)$total_cumulative)*100,1) ?></td>
                    </tr>
                </tbody>
            </table>

            <br /><br />
            <table width="800px" border="1" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th colspan="13">TAREAS CUMPLIDAS</th>
                    </tr>
                    <tr>
                        <th class="plhead" colspan="2" rowspan="2">TOTAL TAREAS CUMPLIDAS EN EL MES</th>
                        <th class="plhead" colspan="5">PLANIFICADAS EN EL PLAN MENSUAL (INCLUYE LAS DEL PLAN ANUAL Y LAS PUNTUALIZADAS)</th>
                        <th class="plhead" colspan="6">EXTRA PLANES</th>
                    </tr>
                    <tr>
                        <th class="plhead">PLANIFICADAS</th>
                        <th class="plhead">CUMPLIDAS</th>
                        <th class="plhead">%</th>
                        <th class="plhead">INCUMPLIDAS</th>
                        <th class="plhead">POSPUESTAS  O SUSPENDIDAS</th>
                        <th class="plhead">TOTAL</th>
                        <th class="plhead">EXTERNAS (Nivel igual o superior)</th>
                        <th class="plhead">%</th>
                        <th class="plhead">PROPIAS</th>
                        <th class="plhead">%</th>
                        <th class="plhead">% ESTRAPLANES vs TAREAS PLANIFICADAS</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">MES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?=$obj->efectivas?></td>
                        <td class="plinner"><?=$obj->efectivas_cumplidas?></td>
                        <td class="plinner"><?= number_format(($obj->efectivas_cumplidas/(float)$obj->efectivas)*100,1) ?></td>
                        <td class="plinner"><?=$obj->efectivas_incumplidas?></td>
                        <td class="plinner"><?=$obj->efectivas_canceladas?></td>
                        <td class="plinner"><?=$obj->extras?></td>
                        <td class="plinner"><?=$obj->extras_externas?></td>
                        <td class="plinner"><?= number_format(($obj->extras_externas/(float)$obj->extras)*100,1) ?></td>
                        <td class="plinner"><?=$obj->extras_propias?></td>
                        <td class="plinner"><?= number_format(($obj->extras_propias/(float)$obj->extras)*100,1) ?></td>
                        <td class="plinner"><?= number_format(($obj->extras/(float)$obj->efectivas)*100,1) ?></td>
                    </tr>
                    <tr>
                        <td class="plhead left" align="left" style="text-align:left; font-weight:bold">ACUMULADAS DE MESES ANTERIORES</td>
                        <td class="plinner">&nbsp;</td>
                        <td class="plinner"><?=$efectivas_cumulative?></td>
                        <td class="plinner"><?=$efectivas_cumplidas_cumulative?></td>
                        <td class="plinner"><?= number_format(($efectivas_cumplidas_cumulative/(float)$efectivas_cumulative)*100,1) ?></td>
                        <td class="plinner"><?=$efectivas_incumplidas_cumulative?></td>
                        <td class="plinner"><?=$efectivas_canceladas_cumulative?></td>
                        <td class="plinner"><?=$extras_cumulative?></td>
                        <td class="plinner"><?=$extras_externas_cumulative?></td>
                        <td class="plinner"><?= number_format(($extras_externas_cumulative/(float)$extras_cumulative)*100,1) ?></td>
                        <td class="plinner"><?=$extras_propias_cumulative?></td>
                        <td class="plinner"><?= number_format(($extras_propias_cumulative/(float)$extras_cumulative)*100,1) ?></td>
                        <td class="plinner"><?= number_format(($extras_cumulative/(float)$efectivas_cumulative)*100,1) ?></td>
                    </tr>
                </tbody>
            </table>

            <br />
            <?php if (!empty($note)) {?>
            <div style="width: 800px; text-align: left">
                <h1 style="font-size: 1.2em;">Observaciones:</h1>
                <p><?=$notes?></p>
                <p>
                    Las actividades registradas Planes de Actividades que no han sido aprobados
                    no son considerardas en los calculos del acumulado hasta el actual mes de <?=$meses_array[(int)$month]?>
                </p>
            </div>
            <?php } ?>

            <div id="marca_print_diriger">Generado por Sistema Informático para la Gestión Integrada <strong>Diriger versión <?=$_SESSION['version']?></strong></div>
        </center>
    </body>
</html>