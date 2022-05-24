<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2016
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";

require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'list' || $action == 'edit') {if (isset($_SESSION['obj']))  unset($_SESSION['obj']);}

$year= $_GET['year'];
$month= $_GET['month'];
$id_proceso= $_GET['id_proceso'];
$print_reject= $_GET['print_reject'];

$origen= !empty($_GET['origen']) ? $_GET['origen'] : 0;
$tipo= !empty($_GET['tipo']) ? $_GET['tipo'] : 0;
$organismo= !empty($_GET['organismo']) ? urldecode($_GET['organismo']) : null;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$note= null;
$obj= new Tplantrab($clink);

$obj->SetIdProceso($id_proceso);
$obj->SetYear($year);
$obj->SetIfEmpresarial(2);
$obj->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_ANUAL);

$obj->Set();

$obj_user= new Tusuario($clink);

$date_aprb= $obj->GetAprobado();
$id_aprobado= $obj->GetIdResponsable_aprb();
$array_aprb= $obj_user->GetEmail($id_aprobado);

$date_eval= $obj->GetEvaluado();
$array_eval= $obj_user->GetEmail($obj->GetIdResponsable_eval());
$cumplimiento= $obj->GetCumplimiento();

$obj= new Tplan_ci($clink);

if ((!empty($date_eval)) && !is_null($obj->anual)) {
    $date= !empty($date_eval) ? $date_eval : $date_aprb;
    $date= odbc2time_ampm($date);
    $text= !empty($date_eval) ? "Evaluado" : "Aprobado";
    $note= "Este plan ya fue $text en fecha $date. Los datos que se muestran en el resumen se corresponden con dicho momento. <br/>";
    $note.= "Para actualizar la información deberá aprobar o evaluar para recalcular nuevamente el plan.";
} else {
    $obj->set_print_reject($print_reject);

    $obj->SetOrigen($origen);
    $obj->SetTipo($tipo);
    $obj->SetOrganismo($organismo);

    $obj->automatic_audit_status();
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DEL PLAN ANUAL DE ACCIONES DE CONTROL", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>RESUMEN DEL PLAN ANUAL DE ACCIONES DE CONTROL</title>
        
         <?php include "inc/print.ini.php";?>	

        <style type="text/css">
            .cell {
                width:100px;
                text-align:center;
                vertical-align:text-top;
            }
        </style>

        <center>
            <strong>RESUMEN DEL PLAN DE ACCIONES DE CONTROL, AÑO: </strong><?= $year ?><br />
            <strong>ENTIDAD:</strong><?= $proceso ?><br />
            <br />
            <table width="800" border="1" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="plhead" colspan="7">ACTIVIDADES DE CONTROL</th>
                    </tr>
                    <tr>
                        <th class="plhead" rowspan="2">TIPO</th>
                        <th class="plhead" colspan="2">TOTAL</th>
                        <th class="plhead" colspan="2">EXTERNAS<br />(DE NIVEL SUPERIOR O IGUAL)</th>
                        <th class="plhead" colspan="2">PROPIAS <br />
                            (AUTOCONTROLES)</th>
                    </tr>
                    <tr>
                        <th class="plhead">AUDITORÍAS</th>
                        <th class="plhead">SUPERVISIONES O CONTROLES</th>
                        <th class="plhead">AUDITORÍAS</th>
                        <th class="plhead">SUPERVISIONES O CONTROLES</th>
                        <th class="plhead">AUDITORÍAS</th>
                        <th class="plhead">SUPERVISIONES O CONTROLES</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>I</strong></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->anual_array[1]) ? $obj->anual_array[1] : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->assure_array[1]) ? $obj->assure_array[1] : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->anual_externas_array[1]) ? $obj->anual_externas_array[1] : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->assure_externas_array[1]) ? $obj->assure_externas_array[1] : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->anual_propias_array[1]) ? $obj->anual_propias_array[1] : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->assure_propias_array[1]) ? $obj->assure_propias_array[1] : "" ?></td>
                    </tr>

                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>TOTAL</strong></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->anual) ? $obj->anual : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->assure) ? $obj->assure : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->anual_externas) ? $obj->anual_externas : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->assure_externas) ? $obj->assure_externas : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->anual_propias) ? $obj->anual_propias : "" ?></td>
                        <td class="plinner" style="text-align:center"><?= !empty($obj->assure_propias) ? $obj->assure_propias : "" ?></td>
                    </tr>
                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>% DEL TOTAL</strong></td>
                        <td class="plinner" style="text-align:center"><?= number_format(($obj->anual / (float) $obj->anual) * 100, 1) ?></td>
                        <td class="plinner" style="text-align:center"><?= number_format(($obj->assure / (float) $obj->anual) * 100, 1) ?></td>
                        <td class="plinner" style="text-align:center"><?= number_format(($obj->anual_externas / (float) $obj->anual) * 100, 1) ?></td>
                        <td class="plinner" style="text-align:center"><?= number_format(($obj->assure_externas / (float) $obj->anual) * 100, 1) ?></td>
                        <td class="plinner" style="text-align:center"><?= number_format(($obj->anual_propias / (float) $obj->anual) * 100, 1) ?></td>
                        <td class="plinner" style="text-align:center"><?= number_format(($obj->assure_propias / (float) $obj->anual) * 100, 1) ?></td>
                    </tr>

                </tbody>
            </table>

            <?php if (!empty($note)) { ?>
                <div style="width: 800px; text-align: left">
                    <h1 style="font-size: 1.2em;">Observaciones:</h1>
                    <p><?= $note ?></p>
                </div>
            <?php } ?>

            <div id="marca_print_diriger">Generado por Sistema Informático para la Gestión Integrada <strong>Diriger versión <?= $_SESSION['version'] ?></strong></div>
        </center>

    </body>
</html>