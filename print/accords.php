<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
require_once "../php/config.inc.php";

require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/asistencia.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tipo_reunion.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$prev= !empty($_GET['prev']) ? $_GET['prev'] : 0;
$year= $_GET['year'];
$month= $_GET['month'];
$all_matter= !is_null($_GET['all_matter']) ? $_GET['all_matter'] : false;
$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : null;
$id_proceso= $_GET['id_proceso'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_event= new Tevento($clink);
$obj_event->Set($id_evento);

$id_tipo_reunion= $obj_event->GetIdTipo_reunion();
$id_tipo_reunion_code= $obj_event->get_id_tipo_reunion_code();
$nombre= $obj_event->GetNombre();

$fecha_inicio_plan= $obj_event->GetFechaInicioPlan();
$fecha= DateTime_object($fecha_inicio_plan);
$year= $fecha['y'];
$month= $fecha['m'];

$obj= new Ttematica($clink);

$obj->SetIdEvento($id_evento);
$obj->SetIdProceso($id_proceso);

$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetDay(NULL);
$obj->SetMonth(null);
$obj->SetYear(null);
//$all_matter ? $obj->SetMonth(null) : $obj->SetMonth($month);
// !empty($id_evento) ? $obj->SetYear(null) : $obj->SetYear($year);

$obj_user= new Tusuario($clink);
$obj_meeting= new Ttipo_reunion($clink);
$obj_assist= new Tasistencia($clink);

if (!empty($id_tipo_reunion)) {
    $obj_meeting->SetIdTipo_reunion($id_tipo_reunion);
    $obj_meeting->Set();
    $meeting= $obj_meeting->GetNombre();
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "CHEQUEO DE ACUERDOS", "Corresponde a $fecha_inicio_plan");
?>

<html>

<head>
    <title>CHEQUEO DE ACUERDOS</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div align="center" class="title-header">
            <?php
            if ($prev == 0) {
                if (!$all_matter) {
                    $text = "ACUERDOS <br>";
                    $text .= !empty($id_tipo_reunion) && $id_tipo_reunion != _MEETING_TIPO_OTRA ? $meeting : $nombre;
                    echo $text . ", " . odbc2date($fecha_inicio_plan);
                } else {
                    $text = "RELACIÓN COMPLETA DE ACUERDOS. <br />";
                    $text .= !empty($id_tipo_reunion) && $id_tipo_reunion != _MEETING_TIPO_OTRA ? $meeting : $nombre;
                    echo $text . ", " . $year;
                }
            } else {
                $text = "REVISIÓN DE ACUERDOS <br>";
                $text .= !empty($id_tipo_reunion) && $id_tipo_reunion != _MEETING_TIPO_OTRA ? $meeting : $nombre;
                echo $text . ", " . odbc2date($fecha_inicio_plan);
            }
            ?>
        </div>

        <?php if (!$all_matter) { ?>
        <div style="margin-left: 30px;">
            <strong>Dia: </strong><?= $dayNames[(int) $fecha['n']] . ' ' . $fecha['d'] . ', ' . $year; ?><br />
            <strong>Lugar: </strong> <?= $obj_event->GetLugar() ?><br />
            <strong>Hora: </strong> <?= odbc2ampm($obj_event->GetFechaInicioPlan()) ?><br />
            <br />
        </div>
        <?php } ?>
    </div>


    <div class="page center">
        <table id="scheduler" border="1" cellspacing="0" cellpadding="0">
            <thead>
                <th class="plhead left" width="20px">No.</th>
                <th class="plhead" style="min-width: 250px">ACUERDOS</th>
                <th class="plhead" width="100px">FECHA/HORA</th>
                <th class="plhead" width="200px">RESPONSABLE</th>
                <th class="plhead" width="200px">PARTICIPANTES</th>
                <th class="plhead">OBSERVACIONES SOBRE EL CUMPLIMIENTO</th>
            </thead>

            <tbody>
                <?php
                $i = 0;

                if (!$prev) {
                    $result = $all_matter ? $obj->listar_all_accords($year) : $obj->listar(true, $year);
                    while ($row = $clink->fetch_array($result)) {
                        ++$i;
                        $year= date('Y', strtotime($row['fecha_inicio_plan']));
                        $obj_event->SetInicio($year);
                        $obj_event->SetFin($year);
                ?>
                <tr>
                    <td class="plinner left"><?= !empty($row['numero']) ? $row['numero'] : $i ?></td>

                    <td class="plinner"><?= textparse($row['_nombre']) ?></td>

                    <td class="plinner" align="center" width="100px" style="text-align: center">
                        <?= odbc2time_ampm($row['_fecha_inicio_plan']) ?>
                    </td>
                    <td class="plinner" width="25%">
                        <?php
                        $obj_assist->Set($row['id_asistencia_resp']);
                        $nombre= $obj_assist->GetNombre();
                        $cargo= $obj_assist->GetCargo();

                        if (empty($nombre)) {
                            $email= $obj_user->GetEmail($obj_assist->GetIdUsuario());
                            $nombre= $email['nombre'];
                            $cargo= $email['cargo'];
                        }

                        $email = $obj_user->GetEmail($row['_id_responsable']);
                        if ($config->onlypost)
                            echo !empty($cargo) ? $cargo : $nombre;
                        else
                            echo $nombre.(!empty($cargo) ? " ($cargo)" : null);
                        ?>
                    </td>

                    <td class="plinner">
                        <?php
                        $obj_event->SetIdEvento($row['id_evento_accords']);
                        echo $obj_event->get_participantes();
                        ?>
                    </td>

                    <td class="plinner">
                        <strong>ESTADO: </strong><?= $eventos_cump[$row['cumplimiento']] ?><br />
                        <?php
                        if (!empty($row['id_responsable_eval'])) {
                            echo "<strong>Registrado por: </strong>";
                            $email = $obj_user->GetEmail($row['id_responsable_eval']);

                            if ($config->onlypost)
                                echo !empty($email['cargo']) ? $email['cargo'] : $email['nombre'];
                            else
                                echo $email['nombre'].(!empty($email['cargo']) ? " ({$email['cargo']})" : null);

                            echo "<br /> en fecha: " . odbc2time_ampm($row['evaluado']);
                            echo "<br /><br />";

                            echo textparse($row['evaluacion']);
                        }
                        ?>
                    </td>
                </tr>
                <?php }  } ?>

                <?php
                if ($prev) {
                    $array_tematicas = $obj->getPrevAccords();
                    $i = 0;
                    foreach ($array_tematicas as $row) {
                        $year= date('Y', strtotime($row['fecha_inicio']));
                        $obj_event->SetInicio($year);
                        $obj_event->SetFin($year);
                        ?>
                <tr>
                    <td class="plinner left"><?= !empty($row['numero']) ? $row['numero'] : $i ?></td>
                    <td class="plinner"><?= $row['descripcion'] ?></td>
                    <td class="plinner" align="center" width="100px" style="text-align: center">
                        <?= odbc2time_ampm($row['fecha_inicio']) ?></td>

                    <td class="plinner">
                        <?php
                        $obj_assist->Set($row['id_asistencia_resp']);
                        $nombre= $obj_assist->GetNombre();
                        $cargo= $obj_assist->GetCargo();

                        if (empty($nombre)) {
                            $email= $obj_user->GetEmail($obj_assist->GetIdUsuario());
                            $nombre= $email['nombre'];
                            $cargo= $email['cargo'];
                        }

                        $email = $obj_user->GetEmail($row['id_responsable']);
                        if ($config->onlypost)
                            echo !empty($cargo) ? $cargo : $nombre;
                        else
                            echo $nombre.(!empty($cargo) ? " ($cargo)" : null);
                        ?>
                    </td>

                    <td class="plinner">
                        <?php
                        $obj_event->SetIdEvento($row['id_evento_accords']);
                        echo $obj_event->get_participantes();
                        ?>
                    </td>

                    <td class="plinner">
                        <strong>ESTADO: </strong><?= $eventos_cump[$row['cumplimiento']] ?><br />
                        <?php
                        if (!empty($row['id_responsable_eval'])) {
                            echo "<strong>Registrado por: </strong>";
                            $email = $obj_user->GetEmail($row['id_responsable_eval']);

                            if ($config->onlypost)
                                echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                            else
                                echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);

                            echo "<br /> en fecha: " . odbc2time_ampm($row['evaluado']);
                            echo "<br /><br />";
                            echo $row['evaluacion'];
                        }
                        ?>
                    </td>
                </tr>
                <?php } } ?>
            </tbody>
        </table>
    </div>

    <?php require "inc/print_bottom.inc.php";?>