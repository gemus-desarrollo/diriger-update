<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2016
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tipo_evento.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$observacion= !empty($_GET['observacion']) ? urldecode($_GET['observacion']) : null;

$fecha_inicio= !empty($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : null;
$fecha_fin= !empty($_GET['fecha_fin']) ? urldecode($_GET['fecha_fin']) : null;

if (!empty($fecha_inicio)) 
    $fecha_inicio= date2odbc($fecha_inicio);
if (!empty($fecha_fin)) 
    $fecha_fin= date2odbc($fecha_fin);

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$time= new TTime();
$obj_signal= new Tevento_signals($clink);
$obj_signal->tipo_plan= _PLAN_TIPO_ACTIVIDADES_MENSUAL;

$obj_user= new Tusuario($clink);
$obj= new Tplantrab($clink);

$obj->SetTipoPlan(_PLAN_TIPO_ACTIVIDADES_MENSUAL);
$obj->SetIdProceso($id_proceso);
$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetRole(NULL);

$obj->SetDay(NULL);
$obj->SetMonth($month);
$obj->SetYear($year);
$obj->Set();

$obj->SetIfEmpresarial(NULL);
$obj->toshow= _EVENTO_MENSUAL;
$obj->SetCumplimiento(null);

$obj->list_puntualizacion();

$obj_tipo= new Ttipo_evento($clink);

$time->SetMonth($month);
$time->SetYear($year);
$lastday= $time->longmonth();

$array_eventos= array();

$obj_event= new Tevento($clink);
$obj_event->SetYear($year);

$obj_event->set_procesos($id_proceso);

function list_reg($empresarial, $id_tipo_evento= null) {
    global $obj;
    global $array_eventos;
    global $fecha_inicio;
    global $fecha_fin;

    reset($obj->array_eventos);
    $cant= 0;
    $array_eventos= array();

    foreach ($obj->array_eventos as $evento) {
        if (is_null($evento)) 
            continue;
        if (!empty($fecha_inicio) && strtotime($evento['fecha_inicio']) < strtotime($fecha_inicio))
            continue;
        if (!empty($fecha_fin) && strtotime($evento['fecha_fin']) > strtotime($fecha_fin))
            continue;

        if (!empty($id_tipo_evento)) {
            if ($evento['id_tipo_evento'] == $id_tipo_evento) {
                $array_eventos[]= $evento;
                ++$cant;
            }
        } else {
            if ((empty($evento['id_tipo_evento']) && empty($id_tipo_evento))) {
                if ($empresarial > 2 && $evento['empresarial'] == $empresarial) {
                    $array_eventos[]= $evento;
                    ++$cant;
                }
                if ($empresarial == 0 && ($evento['empresarial'] == 1 || $evento['empresarial'] == 2)) {
                    $array_eventos[]= $evento;
                    ++$cant;
    }   }   }   }

    return $cant;
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PLAN GENERAL MENSUAL -- PUNTUALIZACION", "Corresponde a periodo mes/año: $month/$year");
?>

<html>

<head>
    <title>PLAN MENSUAL MODELO 2016</title>

    <?php require "inc/print_top.inc.php";?>

    <style type="text/css">
    .cell {
        width: 100px;
        text-align: center;
        vertical-align: text-top;
    }
    </style>

    <div class="page center">
        <h1>ACTIVIDADES PUNTUALIZADAS EN EL MES DE <?= strtoupper($meses_array[(int)$month]) ?>, <?= $year ?></h1>
        <h1>ORGANIZACIÓN:</h1> <?= $proceso ?><br />
        <strong>Desde</strong> <?=date('d/m/Y', strtotime($fecha_inicio))?> <strong>hasta</strong>
        <?=date('d/m/Y', strtotime($fecha_fin))?>

        <br /><br /><br />
        <table class="center" width="100%">
            <thead>
                <tr>
                    <th width="46" rowspan="2" class="plhead left">No</th>
                    <th rowspan="2" class="plhead">ACTIVIDADES</th>
                    <th colspan="2" class="plhead none-bottom">Mes de <?= $meses_array[(int)$month] ?></th>
                    <th width="124" rowspan="2" class="plhead">DIRIGENTE</th>
                    <th rowspan="2" class="plhead">PARTICIPANTES</th>
                    <th rowspan="2" class="plhead">OBSERVACIÓN</th>
                </tr>
                <tr>
                    <th class="plhead">Fecha y hora planificada</th>
                    <th class="plhead">Fecha y hora puntualizada</th>
                </tr>
            </thead>

            <tbody>
                <?php
                    $k = 0;
                    $cant = list_reg(0, 0);

                    if (!empty($cant)) {
                        foreach ($array_eventos as $evento) {
                            ++$k;
                            ?>

                <tr>
                    <td align="left" class="plinner left" style="text-align:center">
                        <?= !empty($evento['numero']) ? $evento['numero'] : $k ?>
                    </td>
                    <td class="plinner"><?= $evento['evento'] ?></td>
                    <td class="plinner"><?= odbc2time_ampm($evento['plan']) ?></td>
                    <td class="plinner"><?= odbc2time_ampm($evento['new']) ?></td>
                    <td class="plinner">
                        <?php
                        $email= $obj_user->GetEmail($evento['id_responsable']);
                        if (!empty($evento['funcionario']))
                            echo "<strong>Externo: </strong>{$evento['funcionario']}<br/>";
                        if ($config->onlypost)
                            echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                        else
                            echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);
                        ?>
                    </td>
                    <td class="plinner">
                        <?php
                        $obj_event->set_user_date_ref($evento['fecha_inicio']);
                        $array = $obj_event->get_participantes($evento['id'], null, null, $id_proceso);
                        echo $array;
                        ?>
                    </td>
                    <td class="plinner">
                        <?=$evento['observacion']?>
                    </td>
                </tr>
                <?php
                        }
                    }
                    ?>

                <?php if ($k == 0) { ?>
                <tr>
                    <td align="left" class="plinner left" style="text-align:left; font-weight:bold">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                </tr>
                <?php } ?>

                <?php
                    for ($i= 2; $i < _MAX_TIPO_ACTIVIDAD; $i++) {
                        $k= 0;
                        $cant= list_reg($i, 0);
                    ?>
                <tr>
                    <td colspan="7" align="left" class="plinner left"
                        style="text-align:left; font-weight:bold; padding-left: 10px;">
                        <?=number_format_to_roman($i-1)."  ".strtoupper($tipo_actividad_array[$i])?>
                    </td>
                </tr>
                <?php
                if (!empty($cant)) {
                    foreach ($array_eventos as $evento) {
                    ++$k;
                ?>

                <tr>
                    <td align="left" class="plinner left" style="text-align:center">
                        <?=!empty($evento['numero']) ? $evento['numero'] : $k?>
                    </td>
                    <td class="plinner"><?=$evento['evento']?></td>
                    <td class="plinner"><?=odbc2time_ampm($evento['plan'])?></td>
                    <td class="plinner"><?=odbc2time_ampm($evento['new'])?></td>
                    <td class="plinner">
                        <?php
                                $email= $obj_user->GetEmail($evento['id_responsable']);
                                if (!empty($evento['funcionario']))
                                    echo "<strong>Externo: </strong>{$evento['funcionario']}<br/>";
                                if ($config->onlypost)
                                    echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                                else
                                    echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);
                                ?>
                    </td>
                    <td class="plinner">
                        <?php
                               $array= $obj_event->get_participantes($evento['id'], null, null, $id_proceso);
                               echo $array;
                               ?>
                    </td>
                    <td class="plinner">
                        <?=$evento['observacion']?>
                    </td>
                </tr>
                <?php } } ?>

                <?php
                       $obj_tipo->SetIfEmpresarial($i);
                       $result= $obj_tipo->listar();

                       while ($row= $clink->fetch_array($result)) {
                           $k= 0;
                           $cant= list_reg($i, $row['id']);

                           if (!empty($cant)) {
                       ?>
                <tr>
                    <td colspan="7" align="left" class="plinner left"
                        style="text-align:left; font-weight:bold; padding-left: 20px;">
                        <?=$row['nombre']?>
                    </td>
                </tr>
                <?php
                foreach ($array_eventos as $evento) {
                    ++$k;
                ?>
                <tr>
                    <td align="left" class="plinner left" style="text-align:center">
                        <?=!empty($evento['numero']) ? $evento['numero'] : $k?></td>
                    <td class="plinner"><?=$evento['evento']?></td>
                    <td class="plinner"><?=odbc2time_ampm($evento['plan'])?></td>
                    <td class="plinner"><?=odbc2time_ampm($evento['new'])?></td>
                    <td class="plinner">
                        <?php
                        $email= $obj_user->GetEmail($evento['id_responsable']);
                        if (!empty($evento['funcionario']))
                            echo "<strong>Externo: </strong>{$evento['funcionario']}<br/>";
                        if ($config->onlypost)
                            echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                        else
                            echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);
                        ?>
                    </td>
                    <td class="plinner">
                        <?php
                        $array= $obj_event->get_participantes($evento['id'], null, null, $id_proceso);
                        echo $array;
                        ?>
                    </td>
                    <td class="plinner">
                        <?=$evento['observacion']?>
                    </td>
                </tr>
                <?php } } } ?>

                <?php if ($k == 0) { ?>
                <tr>
                    <td class="plinner left">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>

        <div class="col-md-12 col-lg-12" style="text-align: left;">
            <br /><br />
            <strong>OBSERVACIONES:</strong>
            <p><?=nl2br($observacion)?></p>
        </div>
    </div>



    <?php require "inc/print_bottom.inc.php";?>