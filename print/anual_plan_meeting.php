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
require_once "../php/class/base_evento.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/plantrab.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/tipo_evento.class.php";

require_once "../form/class/evento.signal.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_NO;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_signal= new Tevento_signals($clink);
$obj_signal->tipo_plan= _PLAN_TIPO_ACTIVIDADES_ANUAL;
$obj_signal->print_reject= $print_reject;

$obj_user= new Tusuario($clink);

$obj_plan= new Tplantrab($clink);
$obj_plan->SetIdTipo_reunion(null);
$obj_plan->SetYear($year);
$obj_plan->SetMonth(NULL);
$obj_plan->SetIdUsuario(NULL);
$obj_plan->SetTipoPlan(_PLAN_TIPO_MEETING);

$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetIfEmpresarial(null);
$obj_plan->Set();

$objetivos= $obj_plan->GetObjetivo();
$date_aprb= $obj_plan->GetAprobado();
$id_aprobado= $obj_plan->GetIdResponsable_aprb();
$array_aprb= $obj_user->GetEmail($id_aprobado);

$obj_plan->SetIdResponsable(NULL);
$obj_plan->SetIdUsuario(NULL);
$obj_plan->SetRole(NULL);
$obj_plan->SetIfEmpresarial(NULL);

$obj_plan->toshow= _EVENTO_MENSUAL;
$obj_plan->SetTipoPlan(_PLAN_TIPO_MEETING);
$obj_plan->automatic_event_status($obj_plan->toshow);

$obj_event= new Tevento($clink);
$obj_tipo= new Ttipo_evento($clink);

$obj= new Tevento($clink);
$obj_plan->copy_in_object($obj);
$obj->tidx_array= $obj_plan->tidx_array;
$obj->tidx_array_auditoria= $obj_plan->tidx_array_auditoria;
$obj->tidx_array_evento= $obj_plan->tidx_array_evento;
$obj->tidx_array_tarea= $obj_plan->tidx_array_tarea;

$obj->set_procesos($id_proceso);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "PROGRAMA ANUAL DE REUNIONES", "Corresponde a periodo mes/año: $month/$year");
?>

<html>

<head>
    <title>PROGRAMA ANUAL DE REUNIONES</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="page center">
        <div id="headerpage" style="width: <?= $widthpage?>cm">
            <?php
                if (!empty($array_aprb)) {
                    $email= $obj_user->GetEmail($id_aprobado);
                ?>
            Aprobado por: <?=$array_aprb['cargo']?><br />
            <span style="margin-left: 70px"><?=$array_aprb['nombre']?></span><br />
            <?php if ($email['firma']) { ?>
            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_aprobado?>"
                border="0" />
            <?php } ?>
            <?php } ?>

            <div align="center" class="title-header">
                <h1>CRONOGRAMA DE REUNIONES PARA EL AÑO <?=$year?></h1>
            </div>
        </div>

        <div class="page-break"></div>

        <div id="scheduler-container">
            <?php $colspan = 17; ?>
            <table class="center" width="100%">
                <thead>
                    <tr>
                        <th rowspan="2" class="plhead left" style="width:30px">No.</th>

                        <th rowspan="2" class="plhead">
                            <?php
                                $i = 0;
                                $colum = "Reunión";

                                if (!$config->hourcolum_y) {
                                    if ($i == 0)
                                        $colum .= ", ";
                                    $colum .= "Hora";
                                }
                                if (!$config->placecolum_y) {
                                    $colum .= ($i == 0) ? ", " : " y ";
                                    $colum .= "Lugar";
                                }
                                echo $colum;
                                ?>
                        </th>


                        <?php if ($config->hourcolum_y) { ?>
                        <th rowspan="2" class="plhead">Hora</th>
                        <?php
                                ++$colspan;
                            }
                            ?>
                        <?php if ($config->placecolum_y) { ?>
                        <th rowspan="2" class="plhead">Lugar</th>
                        <?php
                                ++$colspan;
                            }
                            ?>

                        <th colspan="12" class="plhead">Meses</th>
                        <th rowspan="2" class="plhead">Dirige</th>
                        <th rowspan="2" class="plhead">Participantes</th>
                    </tr>

                    <tr>
                        <th class="plhead month">E</th>
                        <th class="plhead month">F</th>
                        <th class="plhead month">M</th>
                        <th class="plhead month">A</th>
                        <th class="plhead month">M</th>
                        <th class="plhead month">J</th>
                        <th class="plhead month">J</th>
                        <th class="plhead month">A</th>
                        <th class="plhead month">S</th>
                        <th class="plhead month">O</th>
                        <th class="plhead month">N</th>
                        <th class="plhead month">D</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if (!empty($id_proceso)) {
                        $obj->listyear();
                        $cant = $obj->GetCantidad();

                        if ($cant > 0) {
                            $j = 0;
                            foreach ($obj->array_eventos as $evento) {
                                ++$ktotal;
                                $memo = $evento['memo'];

                                $obj_event->Set($evento['id']);
                                $fecha_inicio = $obj_event->GetFechaInicioPlan();
                                $fecha_fin = $obj_event->GetFechaFinPlan();
                    ?>
                    <tr>
                        <td class="plinner left"><?= ++$j ?></td>
                        <td class="plinner" style="min-width:150px;">
                            <?php
                            echo stripslashes($evento['evento']);
                            if (!$config->hourcolum_y)
                                echo "<br />" . odbc2ampm($evento['fecha']);
                            $br = !$config->hourcolum_y ? "<br />" : ' ';
                            if (!$config->placecolum_y)
                                echo $br . stripslashes($evento['lugar']);
                            ?>
                        </td>

                        <?php if ($config->hourcolum_y) { ?>
                            <td class="plinner"><?= odbc2ampm($evento['fecha']) ?></td>
                        <?php } ?>
                        <?php if ($config->placecolum_y) { ?>
                            <td class="plinner"><?= $evento['lugar'] ?></td>
                        <?php } ?>

                        <?php for ($k = 1; $k < 13; ++$k) { ?>
                        <td class="plinner month">
                            <?php build_intervals($evento['month'][$k], $obj->array_status_eventos_ids, $print_reject); ?>
                        </td>
                        <?php } ?>

                        <td class="plinner">
                            <?php
                            $email= $obj_user->GetEmail($evento['id_responsable']);
                            if (!empty($evento['funcionario']))
                                echo $evento['funcionario'];
                            else {
                                if ($config->onlypost)
                                    echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                                else
                                    echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);
                            }    
                            ?>
                        </td>

                        <td class="plinner">
                            <?php
                            $array = $obj->get_participantes($evento['id'], null, null, $id_proceso);
                            echo $array;

                            $origen_data = $obj_tipo->GetOrigenData('participant', $evento['origen_data_asigna']);
                            if (!is_null($origen_data))
                                echo "<br /> " . merge_origen_data_participant($origen_data);
                            ?>
                        </td>
                    </tr>
                    <?php }  }  } ?>
                </tbody>
            </table>
        </div>

    </div>

    <?php require "inc/print_bottom.inc.php";?>