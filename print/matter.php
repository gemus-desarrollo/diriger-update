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
require_once "../php/class/usuario.class.php";
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/evento.class.php";

require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$time = new TTime();

$year = $_GET['year'];
$month = $_GET['month'];
$all_matter = !is_null($_GET['all_matter']) ? $_GET['all_matter'] : false;
$id_evento = !empty($_GET['id_evento']) ? $_GET['id_evento'] : null;
$id_proceso = $_GET['id_proceso'];

$obj_meeting= new Ttipo_evento($clink);

$obj_prs = new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso = $obj_prs->GetNombre();
unset($obj_prs);

$obj_event = new Tevento($clink);
$obj_event->Set($id_evento);

$id_tipo_reunion = $obj_event->GetIdTipo_reunion();

if (!empty($id_tipo_reunion)) {
    $obj_meeting->SetIdTipo_reunion($id_tipo_reunion);
    $obj_meeting->Set();
    $meeting= $obj_meeting->GetNombre();
}

$meeting = !empty($id_tipo_reunion) ? $meeting : $obj_event->GetNombre();
$meeting = strtoupper($meeting);
$fecha_inicio_plan = $obj_event->GetFechaInicioPlan();

$fecha = DateTime_object($fecha_inicio_plan);
$year = $fecha['y'];
$month = $fecha['m'];

$obj = new Ttematica($clink);
$obj->SetIdEvento($id_evento);
$obj->SetIdProceso(null);

$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetDay(NULL);
$all_matter ? $obj->SetMonth(null) : $obj->SetMonth($month);
$obj->SetYear($year);

$result= null;
$array_tematicas= null;

if (!$all_matter) 
    $result= $obj->listar(false);
else 
    $array_tematicas= $obj->list_tematicas ();

$obj_user = new Tusuario($clink);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "ORDEN DEL DÍA", "Corresponde a periodo mes/año: $month/$year");
?>

<html>

<head>
    <title>ORDEN DEL DÍA</title>

    <?php require "inc/print_top.inc.php";?>

    <style type="text/css">
    .plinner.month {
        font-size: 0.8em;
        padding: 2px 2px 2px 4px;
    }
    </style>

    <div class="container-fluid center">
        <div align="center" class="title-header">
            <?php
                if (!$all_matter) {
                    $text = "ORDEN DEL DÍA.<br /> ";
                    if (!empty($id_tipo_reunion) && $id_tipo_reunion != _MEETING_TIPO_OTRA)
                        $text .= $meeting;
                    echo $text . " " . odbc2date($fecha_inicio_plan);
                } else {
                    $text = "PLAN TEMÁTIO. ";
                    if (!empty($id_tipo_reunion) && $id_tipo_reunion != _MEETING_TIPO_OTRA)
                        $text .= $meeting;
                    echo $text . " " . $year;
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

    <?php if (!$all_matter) { ?>
    <div class="page center">
        <table id="scheduler" with="800px" border=1 cellspacing=0 cellpadding=0>
            <thead>
                <th class="plhead left" width="20px">No.</th>
                <th class="plhead">TEMAS</th>
                <th class="plhead"><?php if ($all_matter) echo "FECHA/" ?>HORA</th>
                <th class="plhead">RESPONSABLE</th>
                <th class="plhead" width="200px">PARTICIPANTES</th>
            </thead>

            <tbody>
                <?php
                $i = 0;
                while ($row= $clink->fetch_array($result)) {
                    ++$i;
                ?>
                <tr>
                    <td class="plinner left">
                        <?= !empty($row['numero']) ? $row['numero'] : $i ?>
                    </td>
                    <td class="plinner">
                        <?= $row['_nombre'] ?>
                    </td>
                    <td class="plinner" align="center" width="100px" style="text-align: center">
                        <?= odbc2ampm($row['_fecha_inicio_plan']) ?></td>
                    <td class="plinner" width="25%">
                        <?php
                        $mail= $obj->GetEmail($row['id_asistencia_resp']);
                        $name= !empty($mail['cargo']) ? textparse($mail['nombre']).', '.textparse($mail['cargo']) : textparse($mail['nombre']);
                        echo $name;
                        ?>
                    </td>
                    <td class="plinner">
                        <?php
                        $obj->SetFechaInicioPlan($row['_fecha_inicio_plan']);
                        echo $obj->get_participantes($row['_id']);
                        ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php } else { ?>

    <div>
        <table align="center" id="scheduler" width="1024px" border=1 cellspacing='0' cellpadding='0'>
            <thead>
                <tr>
                    <th rowspan="2" class="plhead left" width="20px">No.</th>
                    <th rowspan="2" class="plhead">TEMAS</th>
                    <th colspan="12" class="plhead">Meses</th>
                    <th rowspan="2" class="plhead" width="200px">RESPONSABLE /<br />PARTICIPANTES</th>
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
                $i= 0;
                $max_numero= 0;
                foreach ($array_tematicas as $row) {
                    ++$i;
                    $numero= !empty($row['numero']) ? $row['numero'] : $i;
                    if ($numero > $max_numero) 
                        $max_numero= $numero;
                ?>

                <tr>
                    <td class="plinner left">
                        <?=$numero?>
                    </td>
                    <td class="plinner"><?= $row['nombre'] ?></td>

                    <?php for ($j = 1; $j < 13; $j++) { ?>
                    <td class="plinner month" style="text-align: center">
                        <?php foreach ($row['array_month'][$j] as $day) { ?>
                        <?="{$day['weekday']}, {$day['day']} {$day['time']}"?>
                        <br />
                        <?php } ?>
                    </td>
                    <?php } ?>

                    <td class="plinner month" width="25%">
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
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php } ?>
    </div>

    <?php require "inc/print_bottom.inc.php";?>