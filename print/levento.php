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

require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/register_planning.class.php";
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/regtarea.class.php";

require_once "../php/class/traza.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['inicio']) ? date('m', strtotime(urldecode($_GET['inicio']))) : date('m');

$time= new TTime();
$time->splitTime();
$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$fecha_inicio= !empty($_GET['fecha_inicio']) ? urldecode($_GET['fecha_inicio']) : "01/$month/$year 00:00:00";
$fecha_final= !empty($_GET['fecha_final']) ? urldecode($_GET['fecha_final']) : "$lastday/$month/$year 00:00:00";
$cumplimiento= !empty($_GET['cumplimiento']) ? $_GET['cumplimiento'] : null;
$texto= !empty($_GET['texto']) ? urldecode($_GET['texto']) : null;

$empresarial= !empty($empresarial) ? $_GET['empresarial'] : null;

$obj= new Tevento($clink);

$obj->SetYear($year);
$obj->SetIdProceso($id_proceso);
$obj->SetFechaInicioPlan(date2odbc($fecha_inicio));
$obj->SetFechaFinPlan(date2odbc($fecha_final));

$obj->SetCumplimiento(null);
$obj->SetIfEmpresarial($empresarial);

$obj->SetIdEscenario(null);
$obj->SetIdResponsable(null);
$obj->SetIdUsuario(null);

$result= $obj->listar();

$obj_user= new Tusuario($clink);
$obj_tipo_evento= new Ttipo_evento($clink);
$obj_reg= new Tregister_planning($clink);
$obj_prs= new Tproceso($clink);

if ($id_proceso != -1) {
    $obj_prs->SetIdProceso($id_proceso ? $id_proceso : $_SESSION['id_entity']);
    $obj_prs->Set();
    $tipo_prs= $obj_prs->GetTipo();
    $proceso= $obj_prs->GetNombre();
} else {
    $proceso= "Todos los procesos";
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE TAREAS", "Corresponde a periodo: $fecha_inicio / $fecha_fin");
?>

<html>

<head>
    <title>LISTADO DE ACTIVIDADES O EVENTOS</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div class="title-header">
            LISTADO DE ACTIVIDADES O EVENTOS <br />DESDE <?= $fecha_inicio ?> HASTA <?= $fecha_final ?>
        </div>
    </div>

    <div class="page center">
        <table cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left">No.</th>
                    <th class="plhead">Número</th>
                    <?php if ($action == 'add' || $action == 'edit') { ?><th class="plhead"></th><?php } ?>
                    <th class="plhead">ACTIVIDAD</th>
                    <th class="plhead">ESTADO</th>
                    <th class="plhead">APROBADO</th>
                    <th class="plhead">RESPONSABLE</th>
                    <th class="plhead">INICIA</th>
                    <th class="plhead">FINALIZA</th>
                    <th class="plhead">LUGAR</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $i = 0;
                $_id = array(null);

                while ($row = $clink->fetch_array($result)) {
                    ++$i;
                    $array_responsable = array('id_responsable' => $row['id_responsable'], 'id_responsable_2' => $row['id_responsable_2'],
                        'responsable_2_reg_date' => $row['responsable_2_reg_date']);
                    
                    $obj_reg->SetYear($year);
                    $array = $obj_reg->getEvento_reg($row['id'], $array_responsable);

                    //$img= $obj->getCumplidoImage();
                    $numero= !empty($row['numero']) ? $row['numero'] : null;
                    $numro.= !empty($row['numero_plus']) ? ".{$row['numero_plus']}" : null;

                    $capitulo= null;
                    if (!empty($row['id_tipo_evento'])) {
                        $obj_tipo_evento->Set($row['id_tipo_evento']);
                        $capitulo= $obj_tipo_evento->GetNumero();
                    } else {
                        if (!empty($row['empresrial']) && $row['empresarial'] >= 1)
                            $capitulo= ($row['empresarial'] - 1);
                    }

                    if (!empty($capitulo))
                        $numero= "{$capitulo}.{$numero}";

                    if (!empty($texto) && (stripos(strtolower($row['nombre']), strtolower($texto)) === false)
                                          && (stripos(strtolower($row['descripcion']), strtolower($texto)) === false))
                        continue;
                    ?>

                <tr>
                    <td class="plinner left"><?=++$j?></td>

                    <td class="plinner"><?=!empty($numero) ? $numero : $i?></td>

                    <?php if ($action == 'add' || $action == 'edit') { ?>
                    <td class="plinner">
                        <a class="btn btn-warning btn-sm" href="#" title="Editar"
                            onclick="enviar_evento(<?= $row['id'] ?>, 'edit')">
                            <i class="fa fa-edit"></i>Editar
                        </a>

                        <a class="btn btn-danger btn-sm" href="#" title="Eliminar"
                            onclick="enviar_evento(<?= $row['id'] ?>, 'delete')">
                            <i class="fa fa-trash"></i>Eliminar
                        </a>
                    </td>
                    <?php } ?>

                    <td class="plinner">
                        <?php if (!empty($row['periodicidad'])) { ?>
                        <i class="fa fa-folder-open-o fa-2x"></i>
                        <?php } ?>

                        <?= $row['nombre'] ?>
                    </td>
                    <td class="plinner">
                        <label class="text alarm <?=$eventos_cump_class[$array['cumplimiento']]?>"
                            onclick="mostrar(<?= $row['id'] ?>)">
                            <?=$eventos_cump[$array['cumplimiento']]?>
                        </label>
                        <br />
                        <p>
                            <?php
                            $email= $obj_user->GetEmail($array['id_responsable']);
                            echo $email['nombre'];
                            if (!empty($email['cargo'])) 
                                echo ", ".textparse($email['cargo']);
                            echo "<br />". odbc2time_ampm($array['cronos']);
                            ?>
                        </p>
                    </td>
                    <td class="plinner">
                        <?php
                            if (!empty($array['aprobado'])) {
                                echo odbc2time_ampm($array['aprobado']);
                            }
                            ?>
                    </td>
                    <td class="plinner">
                        <?php
                            $email= $obj_user->GetEmail($row['id_responsable']);
                            echo $email['nombre'];
                            if (!empty($email['cargo'])) 
                                echo ", ".textparse($email['cargo']);
                            ?>
                    </td>
                    <td class="plinner">
                        <?= odbc2time_ampm($row['fecha_inicio_plan']) ?>
                    </td>
                    <td class="plinner">
                        <?= odbc2time_ampm($row['fecha_fin_plan']) ?>
                    </td>
                    <td class="plinner">
                        <?= textparse($row['lugar']) ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>

    <?php require "inc/print_bottom.inc.php";?>