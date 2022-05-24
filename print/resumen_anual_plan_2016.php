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
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/evento.class.php";
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

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;
$date_eval= !empty($_GET['date_eval']) ? urldecode($_GET['date_eval']) : 0;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$note= null;
$obj_plan= new Tplantrab($clink);
$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetYear($year);
$obj_plan->SetIfEmpresarial(2);
$obj_plan->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_ANUAL);

$obj_plan->Set();

$date_aprb= $obj_plan->GetAprobado();
$id_aprobado= $obj_plan->GetIdResponsable_aprb();
$date_evalued= $obj_plan->GetEvaluado();
$cumplimiento= $obj_plan->GetCumplimiento();
$evaluado= $obj_plan->GetEvaluado();
$evaluacion= $obj_plan->GetEvaluacion();
$auto_evaluacion= $obj_plan->GetAutoEvaluacion();

$obj_user= new Tusuario($clink);
$array_aprb= $obj_user->GetEmail($id_aprobado);
$array_eval= $obj_user->GetEmail($obj_plan->GetIdResponsable_eval());

unset($obj_user);
$obj_user= new Tusuario($clink);
$obj_user->SetIdUsuario($_SESSION['id_usuario']);
$obj_user->Set();
$usuario_print= $obj_user->GetNombre();
$cargo_print= $obj_user->GetCargo();
$id_proceso_print= $obj_user->GetIdProceso();
$firma_print= $obj_user->GetParam();

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso_print);
$proceso_print= $obj_prs->GetNombre();
unset($obj_prs);

if ((!empty($date_aprb) || !empty($date_eval)) && !is_null($obj_plan->anual)) {
    $date= !empty($date_evalued) ? $date_evalued : $date_aprb;
    $date= odbc2time_ampm($date);
    $text= !empty($date_evalued) ? "Evaluado" : "Aprobado";
    $note= "Este plan ya fue $text en fecha $date. Los datos que se muestran en el resumen se corresponden con dicho momento. <br/>";
    $note.= "Para actualizar la información deberá aprobar o evaluar para recalcular nuevamente el plan.";
}

if (!empty($date_eval)) 
    $obj_plan->SetDate_eval_cutoff($date_eval);
$obj_plan->set_cronos(date('Y-m-d H:i:s'));
$obj_plan->set_print_reject($print_reject);

$obj_plan->list_reg_anual();
$obj_plan->update();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DEL PLAN ANUAL DE ACTIVIDADES", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>RESUMEN DEL PLAN ANUAL DE ACTIVIDADES</title>

        <?php require "inc/print_top.inc.php";?>	

        <style type="text/css">
            .cell {
                width:100px;
                text-align:center;
                vertical-align:text-top;
            }
        </style>

        <div class="page center">
            <table class="center none-border" width="100%">
                <tr>
                    <td class="none-border">
                        <strong>Aprobado por:</strong><br />
                        <?php if (!empty($array_aprb)) { ?>
                            <?= $array_aprb['cargo'] ?><br />
                            <?= $array_aprb['nombre'] ?><br/>
                            <?php if (!is_null($array_aprb['firma'])) { ?>
                                <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_aprobado ?>" border="0" />
                            <?php } ?>
                        <?php } ?>      
                    </td> 
                </tr>

                <tr>
                    <td class="none-border">
                        <div class="center">
                            <h1>RESUMEN DEL PLAN ANUAL DE ACTIVIDADES PARA EL AÑO: </h1><?= $year ?><br />
                            <h1>ENTIDAD:</h1><?= $proceso ?><br />
                            <br />
                        </div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class="none-border pull-left">
                        <h1 style="text-decoration: underline">OBJETIVOS DE TRABAJO</h1><br />
                        <?php
                        $obj_obj= $obj_plan->GetObjetivo();
                        $obj_obj= purge_html($obj_obj, false);
                        $obj_obj= textparse($obj_obj, false);
                        echo $obj_obj;
                        ?>
                    </td>
                </tr>
                -->
                <tr>
                    <td class="none-border">
                        <div class="container-fluid pull-right">
                            <strong>Elaborado por:</strong><br />
                            
                            <?=$cargo_print?><br /><?=$usuario_print?><br /><?=$proceso_print?><br />
                            <?php if ($firma_print['name']) { ?>
                            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$_SESSION['id_usuario']?>" border="0" />
                            <?php } ?>
                            <br/><br/>
                        </div>
                    </td>
                </tr> 
            </table>
        </div>
    
       <div class="page-break"></div>
    
    
        <div class="page center mb-3">
            <table class="center" width="800px">
                <thead>
                    <tr>
                        <th class="plhead left" colspan="7">ACTIVIDADES  Y TAREAS DE ASEGURAMINETO PLANIFICADAS</th>
                    </tr>
                    <tr>
                        <th class="plhead left" rowspan="2">CAPITULOS </th>
                        <th class="plhead" colspan="2">TOTAL</th>
                        <th class="plhead" colspan="2">EXTERNAS<br />(DE NIVEL SUPERIOR O IGUAL)</th>
                        <th class="plhead" colspan="2">PROPIAS</th>
                    </tr>
                    <tr>
                        <th class="plhead">ACTIVIDADES</th>
                        <th class="plhead">TAREAS DE ASEGURAMIENTO</th>
                        <th class="plhead">ACTIVIDADES</th>
                        <th class="plhead">TAREAS DE ASEGURAMIENTO</th>
                        <th class="plhead">ACTIVIDADES</th>
                        <th class="plhead">TAREAS DE ASEGURAMIENTO</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>I</strong></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_array[1]) ? $obj_plan->anual_array[1] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_array[1]) ? $obj_plan->assure_array[1] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_externas_array[1]) ? $obj_plan->anual_externas_array[1] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_externas_array[1]) ? $obj_plan->assure_externas_array[1] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_propias_array[1]) ? $obj_plan->anual_propias_array[1] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_propias_array[1]) ? $obj_plan->assure_propias_array[1] : "" ?></td>
                    </tr>

                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>II</strong></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_array[2]) ? $obj_plan->anual_array[2] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_array[2]) ? $obj_plan->assure_array[2] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_externas_array[2]) ? $obj_plan->anual_externas_array[2] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_externas_array[2]) ? $obj_plan->assure_externas_array[2] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_propias_array[2]) ? $obj_plan->anual_propias_array[2] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_propias_array[2]) ? $obj_plan->assure_propias_array[2] : "" ?></td>
                    </tr>
                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>III</strong></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_array[3]) ? $obj_plan->anual_array[3] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_array[3]) ? $obj_plan->assure_array[3] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_externas_array[3]) ? $obj_plan->anual_externas_array[3] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_externas_array[3]) ? $obj_plan->assure_externas_array[3] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_propias_array[3]) ? $obj_plan->anual_propias_array[3] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_propias_array[3]) ? $obj_plan->assure_propias_array[3] : "" ?></td>
                    </tr>

                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>IV</strong></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_array[4]) ? $obj_plan->anual_array[4] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_array[4]) ? $obj_plan->assure_array[4] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_externas_array[4]) ? $obj_plan->anual_externas_array[4] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_externas_array[4]) ? $obj_plan->assure_externas_array[4] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_propias_array[4]) ? $obj_plan->anual_propias_array[4] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_propias_array[4]) ? $obj_plan->assure_propias_array[4] : "" ?></td>
                    </tr>
                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>V</strong></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_array[5]) ? $obj_plan->anual_array[5] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_array[5]) ? $obj_plan->assure_array[5] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_externas_array[5]) ? $obj_plan->anual_externas_array[5] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_externas_array[5]) ? $obj_plan->assure_externas_array[5] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_propias_array[5]) ? $obj_plan->anual_propias_array[5] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_propias_array[5]) ? $obj_plan->assure_propias_array[5] : "" ?></td>
                    </tr>

                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>VI</strong></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_array[6]) ? $obj_plan->anual_array[6] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_array[6]) ? $obj_plan->assure_array[6] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_externas_array[6]) ? $obj_plan->anual_externas_array[6] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_externas_array[6]) ? $obj_plan->assure_externas_array[6] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_propias_array[6]) ? $obj_plan->anual_propias_array[6] : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_propias_array[6]) ? $obj_plan->assure_propias_array[6] : "" ?></td>
                    </tr>

                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>TOTAL</strong></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual) ? $obj_plan->anual : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure) ? $obj_plan->assure : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_externas) ? $obj_plan->anual_externas : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_externas) ? $obj_plan->assure_externas : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->anual_propias) ? $obj_plan->anual_propias : "" ?></td>
                        <td class="plinner center"><?= !empty($obj_plan->assure_propias) ? $obj_plan->assure_propias : "" ?></td>
                    </tr>
                    <tr>
                        <td class="plinner left" style="text-align:center"><strong>% DEL TOTAL</strong></td>
                        <td class="plinner center"><?= $obj_plan->anual > 0 ? number_format(($obj_plan->anual / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                        <td class="plinner center"><?= $obj_plan->anual > 0 ? number_format(($obj_plan->assure / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                        <td class="plinner center"><?= $obj_plan->anual > 0 ?  number_format(($obj_plan->anual_externas / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                        <td class="plinner center"><?= $obj_plan->anual > 0 ? number_format(($obj_plan->assure_externas / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                        <td class="plinner center"><?= $obj_plan->anual > 0 ? number_format(($obj_plan->anual_propias / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                        <td class="plinner center"><?= $obj_plan->anual > 0 ? number_format(($obj_plan->assure_propias / (float) $obj_plan->anual) * 100, 1) : '' ?></td>
                    </tr>

                </tbody>
            </table>

            <?php if (!empty($note) && empty($evaluado)) { ?>
                <div class="pull-left text-left" style="width: 800px;">
                    <h1>Observaciones:</h1>
                    <p><?= $note ?></p>
                </div>
            <?php } else { ?>
                <br/><br/>
            <?php } ?>

            <div class="pull-left text-left" style="width: 800px;">
            <h1>AUTO EVALUACION:</h1>
            <?php
            echo $auto_evaluacion;
            ?>
            </div>

            <div class="pull-left text-left mt-2" style="width: 800px;">
            <h1>EVALUACION:</h1>
            <?php
            echo $evaluacion;
            $usuario= $array_eval['nombre'];
            $usuario.= !empty($array_eval['cargo']) ? "<br/>{$array_eval['cargo']}" : null;
            echo $usuario;
            ?>
            </div>            
        </div>    
         
    <?php require "inc/print_bottom.inc.php";?>