<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/asistencia.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tipo_reunion.class.php";

require_once "../form/class/tarea.signal.class.php";

require_once "../php/class/traza.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_redirect= !empty($_GET['id_redirect']) ? $_GET['id_redirect'] : 'ok';

if ($action == 'add')
    $action= 'edit';
if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;

$tipo_reunion= !empty($_GET['tipo_reunion']) ? $_GET['tipo_reunion'] : null;
$cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$obj= new Ttematica($clink);

$obj->SetIdEvento($id_evento);
$obj->SetIdProceso($id_proceso);

$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetDay(NULL);
$obj->SetMonth(null);
$obj->SetYear($year);

$obj_user= new Tusuario($clink);
$obj_event= new Tevento($clink);
$obj_meeting= new Ttipo_reunion($clink);
$obj_assist= new Tasistencia($clink);
$obj_signal= new Ttarea_signals();

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();

    $proceso= $obj_prs->GetNombre();
    $tipo_prs= $obj_prs->GetTipo();
}

require_once "../php/config.inc.php";

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE ACUERDOS", "Corresponde a periodo año: $year");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>LISTADO DE ACUERDOS</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div align="center" class="title-header">
                <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
                    LISTADO DE ACUERDOS<br/> <?=$year?>
                </div>
            </div>
        </div>

        <div class="page center">
            <table width="100%" border=1 cellspacing=0 cellpadding=2>
                <thead>
                    <th class="plhead left" width="20px">No.</th>
                    <th>ACUERDO</th>
                    <th width="200px">REGISTRO</th>
                    <th width="100px">FECHA/HORA<br/>CUMPLIMIENTO</th>
                    <th width="200px">RESPONSABLE</th>
                    <th width="200px">PARTICIPANTES</th>
                    <th>OBSERVACIONES SOBRE EL CUMPLIMIENTO</th>
                </thead>

                <tbody>
                <?php
                $result= $obj->listar_all_accords($year, true, true);

                $i = 0;
                while ($row = $clink->fetch_array($result)) {
                    $obj_event->Set($row['id_evento']);
                    if (!empty($tipo_reunion) && $tipo_reunion != $obj_event->GetIdTipo_reunion())
                        continue;

                    $obj_assist->Set($row['id_asistencia_resp']);
                    $id_responsable= $obj_assist->GetIdUsuario();

                    if (isset($obj_reg)) unset ($obj_reg);
                    $obj_reg= new Tregister_planning($clink);
                    $obj_reg->SetIdEvento($row['id_evento_accords']);
                    $obj_reg->SetYear(date('Y', strtotime($row['fecha_inicio_plan'])));
                    $row_reg= $obj_reg->getEvento_reg(null, array('id_responsable'=>$id_responsable));

                    if (!empty($cumplimiento) && $cumplimiento != $row_reg['cumplimiento'])
                        continue;
                ?>
                    <tr>
                        <td class="plinner left">
                            <?=$row['numero']?>
                        </td>
                        <td class="plinner">
                            <?= $row['observacion'] ?>
                        </td>
                        <td class="plinner">
                            <?php
                            echo $obj_event->GetNombre();
                            echo "<p>".odbc2time_ampm($row['cronos'])."</p>";
                            ?>
                        </td>
                        <td class="plinner">
                            <?=odbc2time_ampm($row['fecha_inicio_plan'])?>
                        </td>
                        <td class="plinner">
                            <?php
                            $nombre= $obj_assist->GetNombre();
                            $cargo= $obj_assist->GetCargo();

                            if (empty($nombre)) {
                                $email= $obj_user->GetEmail($id_responsable);
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
                            $obj_event->SetYear(date('Y', strtotime($row['fecha_inicio_plan'])));
                            $obj_event->SetIdEvento($row['id_evento_accords']);
                            echo $obj_event->get_participantes();
                            ?>
                        </td>
                        <td class="plinner">
                            <?php
                            $alarm= $obj_signal->get_alarm($row_reg);
                            ?>
                            <div class="alert bg-<?=$alarm['class']?> small" style="max-width: 200px;"><?=$eventos_cump[(int)$row_reg['cumplimiento']]?></div>
                            <?=$row_reg['observacion']?>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

        </div>
    <?php require "inc/print_bottom.inc.php";?>
