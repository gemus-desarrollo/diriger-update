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
require_once "../php/class/time.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/evento.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/debate.class.php";
require_once "../php/class/asistencia.class.php";
require_once "../php/class/tipo_reunion.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : null;
$id_proceso= $_GET['id_proceso'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_event= new Tevento($clink);
$obj_event->Set($id_evento);
$id_tipo_reunion= $obj_event->GetIdTipo_reunion();
$id_secretary= $obj_event->GetIdSecretary();
$id_responsable= $obj_event->GetIdResponsable();

$numero= $obj_event->GetNumero();

$fecha_inicio_plan= $obj_event->GetFechaInicioPlan();
$fecha= DateTime_object($fecha_inicio_plan);
$year= $fecha['y'];
$month= $fecha['m'];

$obj= new Ttematica($clink);
$obj->SetIdEvento($id_evento);
$obj->SetIdProceso(null);

$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetDay(NULL);
$obj->SetMonth(null);
$obj->SetYear(null);

$result_matter= $obj->listar(false);

$obj_assist= new Tasistencia($clink);
$obj_assist->SetIdEvento($id_evento);
$id_evento_code= !empty($id_evento) ? get_code_from_table('teventos', $id_evento) : null;
$obj_assist->set_id_evento_code($id_evento_code);

$obj_assist->SetIdProceso($id_proceso);
$id_proceso_code= !empty($id_proceso) ? get_code_from_table('tprocesos', $id_proceso) : null;
$obj_assist->set_id_proceso_code($id_proceso_code);

$obj_assist->SetIdProceso(null);

$obj_assist->set_user_date_ref($fecha_inicio_plan);
$obj_assist->listar(false);
$array_assist= $obj_assist->array_asistencias;

$obj_user= new Tusuario($clink);
$obj_meeting= new Ttipo_reunion($clink);

if (!empty($id_tipo_reunion)) {
    $obj_meeting->SetIdTipo_reunion($id_tipo_reunion);
    $obj_meeting->Set();
    $meeting= $obj_meeting->GetNombre();
}

$meeting= !empty($id_tipo_reunion) && $id_tipo_reunion != _MEETING_TIPO_OTRA ? $meeting : $obj_event->GetNombre();
$meeting= strtoupper($meeting);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "ACTA DE $meeting", "Corresponde a $fecha_inicio_plan");
?>

<html>

<head>
    <title>ACTA DE <?=$meeting?></title>

    <style type="text/css">
    #tableAssist td {
        padding: 4px;
    }

    #tableMatters td {
        padding: 4px;
    }

    #tablePoints td {
        border: none !important;
        padding: 4px;
    }

    #tableAccords td {
        border: none !important;
        padding: 4px;
    }
    </style>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div align="center" class="title-header">
            <?= $meeting ?><br />
            Acta No.______
        </div>
    </div>

    <div class="container-fluid center">
        <?= "{$meses_array[(int) $month]}, $year" ?><br />
        Dia y Hora: <?= "{$dayNames[(int) $fecha['n']]} {$fecha['d']}" ?>,
        <?= odbc2ampm($obj_event->GetFechaInicioPlan()) ?><br />
        Lugar: <?= $obj_event->GetLugar() ?>
    </div>

    <div class="page center">
        <h1>ASISTENTES</h1>
        <table id="tableAssist" width="790px" border="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left"><strong>Nombre</strong></th>
                    <th class="plhead"><strong>Cargo</strong></th>
                </tr>
            </thead>

            <tbody>
                <?php
                    $members = 0;
                    $assist = 0;
                    $guest = 0;
                    $absent = 0;
                    $externos= 0;

                    foreach ($array_assist as $row) {
                        if (empty($row['id_usuario'])) {
                            ++$externos;
                            continue;
                        }

                        if (boolean($row['invitado']))
                            ++$guest;
                        else
                            ++$members;
                        if (!boolean($row['invitado']) && boolean($row['ausente']))
                            ++$absent;
                        if (!boolean($row['invitado']) && !boolean($row['ausente']))
                            ++$assist;

                        if (boolean($row['ausente']))
                            continue;
                        $email = $obj_user->GetEmail($row['id_usuario']);
                        ?>
                <tr>
                    <td class="plinner left"><?= textparse($email['nombre']) ?></td>
                    <td class="plinner"><?= textparse($email['cargo']) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if ($externos) { ?>
        <h1>INVITADOS EXTERNOS</h1>
        <table id="tableAssist" width="790px" border="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left"><strong>Nombre</strong></th>
                    <th class="plhead"><strong>Cargo</strong></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ($array_assist as $row) {
                    if (!empty($row['id_usuario']))
                        continue;
                    ?>
                <tr>
                    <td class="plinner left"><?= textparse($row['nombre']) ?></td>
                    <td class="plinner"><?= textparse($row['cargo']) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>

        <?php if ($absent) { ?>
        <br />
        <h1>AUSENTES</h1>
        <table id="tableAssist" width="790px" border="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left"><strong>Nombre</strong></th>
                    <th class="plhead"><strong>Cargo</strong></th>
                </tr>
            </thead>

            <tbody>
                <?php
                reset($array_assist);
                $i = 0;
                foreach ($array_assist as $row) {
                    if (empty($row['id_usuario']))
                        continue;
                    if (boolean($row['invitado']))
                        continue;
                    if (!boolean($row['ausente']))
                        continue;

                    ++$i;
                    $email = $obj_user->GetEmail($row['id_usuario']);
                    ?>
                <tr>
                    <td class="plinner left"><?= textparse($email['nombre']) ?></td>
                    <td class="plinner"><?= $email['cargo'] ?></td>
                </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                <tr>
                    <td class="plinner left">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>

        <?php if ($guest) { ?>
        <br />
        <h1>INVITADOS</h1>
        <table id="tableAssist" width="790px" border="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left"><strong>Nombre</strong></th>
                    <th class="plhead"><strong>Cargo</strong></th>
                    <th class="plhead"><strong>Entidad</strong></th>
                </tr>
            </thead>

            <tbody>
                <?php
                reset($array_assist);
                foreach ($array_assist as $row) {
                    if (empty($row['id_usuario']))
                        continue;
                    if (!boolean($row['invitado']))
                        continue;
                    ?>
                <tr>
                    <td class="plinner left"><?= textparse($row['nombre']) ?></td>
                    <td class="plinner"><?= $row['cargo'] ?></td>
                    <td class="plinner"><?= $row['entidad'] ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>

        <div align="left" style="width: 790px; margin-top: 10px; margin-bottom: 20px;">
            <?php $r = number_format(($assist / (float) $members) * 100, 1); ?>
            La sesión se desarrolla con el <?= $r ?>% de asistencia de sus miembros.
        </div>


        <h1>ORDEN DEL DÍA</h1>
        <table id="tableMatters" width="790px" cellspacing="0">
            <thead>
                <th class="plhead left" width="30px">No.</th>
                <th class="plhead" width="70px">Hora</th>
                <th class="plhead">Punto</th>
                <th class="plhead">Responsable</th>
            </thead>

            <tbody>
                <?php
                $i = 0;
                while ($row = $clink->fetch_array($result_matter)) {
                    ++$i;
                ?>
                <tr>
                    <td class="plinner left">No.<?= $row['numero'] ?></td>
                    <td class="plinner"><?= odbc2ampm($row['fecha_inicio_plan'], $config->hoursoldier) ?></td>
                    <td class="plinner"><?= $row['_nombre'] ?></td>
                    <td class="plinner">
                        <?php
                        $email = $obj_assist->GetEmail($row['id_asistencia_resp']);
                        echo !empty($email['cargo']) ? textparse($email['nombre']) . ' ('.textparse($email['cargo']).')' : textparse($email['nombre']);

                        $obj->SetFechaInicioPlan($row['_fecha_inicio_plan']);
                        $participantes= $obj->get_participantes($row['_id'], null, null, $row['_id_responsable']);
                        if ($participantes)
                            echo "<br/>$participantes";
                        ?>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                <tr>
                    <td class="plinner left">&nbsp;</td>
                    <td class="plinner"></td>
                    <td class="plinner"></td>
                    <td class="plinner"></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php
        if (isset($obj_event)) unset($obj_event);

        $obj_matter = new Ttematica($clink);
        $obj_matter->SetIdEvento($id_evento);
        $obj_matter->SetIdProceso(null);
        $array_accords = $obj_matter->getPrevAccords();
        ?>

        <div align="left" style="width: 790px; margin-top: 20px;">
            <h1>CHEQUEO DE ACUERDOS</h1>
        </div>
        <table id="tableAccords" width="790px" border="0" cellspacing="0">
            <?php
            $i = 0;
            foreach ($array_accords as $row) {
                ++$i;
                $numero = !empty($row['numero']) ? $row['numero'] : $i;
            ?>
            <tr>
                <td>No.<?= $numero ?></td>
                <td colspan="3"><?= textparse(purge_html($row['descripcion'])) ?></td>
            </tr>
            <tr>
                <td></td>
                <td width="100px"><strong>Responsable:</strong></td>
                <td width="250px" align="left" style="text-align: left;">
                    <?php
                    $obj_assist->Set($row['id_asistencia_resp']);
                    $nombre= $obj_assist->GetNombre();
                    $cargo= $obj_assist->GetCargo();

                    if (empty($nombre)) {
                        $email= $obj_user->GetEmail($obj_assist->GetIdUsuario());
                        $nombre= $email['nombre'];
                        $cargo= $email['cargo'];
                    }
                    echo textparse($nombre) . (!empty($cargo) ? "<br />$cargo" : null);
                    ?>
                </td>
                <td valign="bottom">Fecha: <?= odbc2date($row['fecha_inicio']) ?></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="3">
                    <strong>Observación:</strong><br />
                    <?= textparse(purge_html($row['evaluacion'])) ?>
                </td>
            </tr>
            <?php } ?>
        </table>

        <h1>DESARROLLO</h1>
        <table id="tablePoints" width="790px" border="0" cellspacing="0">
            <?php
            $i = 0;
            $clink->data_seek($result_matter);
            while ($row_matter = $clink->fetch_array($result_matter)) {
            ?>
            <?php if ($i) { ?>
            <tr>
                <td colspan="3"><br /></td>
            </tr>
            <?php } ?>

            <tr>
                <td colspan="3">
                    <strong>Punto No.<?= $row_matter['numero'] ?></strong> <?= $row_matter['_nombre'] ?><br />
                </td>
            </tr>

            <tr>
                <td colspan="3"><strong>Discución o Intervenciones:</strong></td>
            </tr>
            <?php
            if (isset($obj)) unset($obj);
            $obj = new Tdebate($clink);
            $obj->SetIdTematica($row_matter['_id']);
            $result = $obj->listar();

            while ($row = $clink->fetch_array($result)) {
            ?>
            <tr>
                <td width="40"><strong>No.</strong><?= ++$i ?></td>
                <td width="100"><strong>Hora: </strong><?= odbc2ampm($row['hora'], $config->hoursoldier) ?></td>
                <td>
                    <strong>Ponente: </strong>
                    <?php
                    $array= $obj_assist->GetAsistencia($row['id_asistencia']);
                    echo $array['nombre'];
                    if (!empty($array['cargo']))
                        echo ', '.textparse($array['cargo']);
                    if (!empty($array['endidad']))
                        echo ', '.textparse($array['entidad']);
                    ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <?= textparse(purge_html($row['_observacion'])) ?><br />
                </td>
            </tr>
            <?php } } ?>
        </table>

        <?php
        if (isset($obj)) unset($obj);
        $obj = new Ttematica($clink);

        $obj->SetIdEvento($id_evento);
        $obj->SetIdProceso(null);

        $obj->SetIdResponsable(NULL);
        $obj->SetIdUsuario(NULL);
        $obj->SetDay(NULL);
        $obj->SetMonth(null);
        $obj->SetYear(null);

        $result_matter = $obj->listar(true);
        ?>

        <h1>ACUERDOS ADOPTADOS:</h1>
        <table id="tableAccords" width="790px" border="0" cellspacing="0">
            <?php while ($row = $clink->fetch_array($result_matter)) { ?>
            <tr>
                <td>No.<?= $row['numero'] ?></td>
                <td colspan="3"><?= textparse(purge_html($row['_nombre'])) ?></td>
            </tr>
            <tr>
                <td></td>
                <td width="100px"><strong>Responsable:</strong></td>
                <td width="250px" align="left" style="text-align: left;">
                    <?php
                    $obj_assist->Set($row['id_asistencia_resp']);
                    $nombre= $obj_assist->GetNombre();
                    $cargo= $obj_assist->GetCargo();

                    if (empty($nombre)) {
                        $email= $obj_user->GetEmail($obj_assist->GetIdUsuario());
                        $nombre= $email['nombre'];
                        $cargo= $email['cargo'];
                    }
                    echo textparse($nombre) . (!empty($cargo) ? "<br />$cargo" : null);
                    ?>
                </td>
                <td valign="bottom">Fecha: <?= odbc2date($row['_fecha_inicio_plan']) ?></td>
            </tr>
            <?php } ?>
        </table>


        <table border="0" style="width: 90%; text-align: right; padding: 10px; border: none;">
            <tr>
                <td style="text-align: left; padding: 10px; border: none;">
                    <?php
                    $mail = null;
                    $mail = $obj_user->GetEmail($id_responsable);
                    ?>
                    <strong>Responsable:</strong><br />
                    <?= $mail['cargo'] ?><br /><?= textparse($mail['nombre']) ?><br /><?= $proceso ?><br />
                    <?php if ($mail['firma']) { ?>
                    <img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_responsable ?>"
                        border="0" />
                    <?php } ?>
                </td>
                <td width="200px" style="border: none;"></td>
                <td style="text-align: left; border: none;">
                    <?php
                    $mail = null;
                    $mail = $obj_user->GetEmail($id_secretary);
                    ?>
                    <strong>Secretario:</strong><br />
                    <?= $mail['cargo'] ?><br /><?= textparse($mail['nombre']) ?><br /><?= $proceso ?><br />
                    <?php if ($mail['firma']) { ?>
                    <img id="img"
                        src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?= $id_secretary ?>"
                        border="0" />
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>

    <?php require "inc/print_bottom.inc.php";?>