<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

$terminado= !empty($_GET['terminado']) ? $_GET['terminado'] : 0;

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
    $action= $obj->action;
} else
    $obj= new Tproyecto($clink);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_proceso'];
if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];
$id_programa= !empty($_GET['id_programa']) ? $_GET['id_programa'] : $obj->GetIdPrograma();
if (empty($id_programa)) 
    $id_programa= 0;

$time= new TTime();
$year= $time->GetYear();
$month= $time->GetMonth();
$lastday= $time->longmonth();

$inicio= $year - 5;
$fin= $year + 5;

$obj_user= new Tusuario($clink);

$obj_prog= new Tprograma($clink);
$obj_prog->SetYear($year);
$obj_prog->SetIdProceso($id_proceso);

$obj_prs = new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$tipo_prs = $obj_prs->GetTipo();
$proceso = $obj_prs->GetNombre();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE PROYECTOS", "Corresponde a periodo año: $year");
?>


<html>

<head>
    <title>LISTADO DE PROYECTOS</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div class="title-header">
            LISTADO DE PROYECTOS <?= $year ?>
        </div>
    </div>

    <div class="page center">

        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left">No.</th>
                    <th class="plhead">PROYECTO</th>
                    <th class="plhead">PROGRAMA</th>
                    <th class="plhead">RESPONSABLE</th>
                    <th class="plhead">INICIO (plan)</th>
                    <th class="plhead">FIN (plan)</th>
                    <th class="plhead">INICIO (real)</th>
                    <th class="plhead">FIN (real)</th>
                    <th class="plhead">DESCRIPCIÓN</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $if_jefe= false;
                if (!is_null($array_chief_procesos) && array_key_exists($id_proceso, (array)$array_chief_procesos))
                    $if_jefe= true;
                if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO)
                    $if_jefe= true;
                if ($acc >= 1 && $_SESSION['usuario_proceso_id'] == $id_proceso)
                    $if_jefe= true;

                $i= 0;

                $obj->SetIdPrograma($id_programa);
                $obj->SetIdProceso($id_proceso);
                $obj->SetFechaInicioPlan(date2odbc($inicio));
                $obj->SetFechaFinPlan(date2odbc($fin));
                $result= $obj->listar();

                while ($row= $clink->fetch_array($result)) {
                    if (!empty($terminado))
                        if (!empty($row['fecha_final_real']))
                            continue;
                ?>

                <tr>
                    <td class="plinner left">
                        <?=++$i?>
                    </td>

                    <td class="plinner">
                        <a name="<?= $row['_id'] ?>"></a>
                        <?= $row['_nombre'] ?>
                    </td>
                    <td class="plinner">
                        <?php
                        if (!empty($row['id_programa'])) {
                            $obj_prog->SetYear($year);
                            $obj_prog->Set($row['id_programa']);
                            echo $obj_prog->GetNombre();
                        }
                        ?>
                    </td>
                    <td class="plinner">
                        <?php
                        $email = $obj_user->GetEmail($row['id_responsable']);
                        echo $email['nombre']?>
                        <?=!empty($email['cargo']) ? textparse($email['cargo']) : null?>
                    </td>
                    <td class="plinner">
                        <?php $fecha = odbc2date($row['fecha_inicio_plan']); ?>
                        <?= $fecha ?>
                    </td>
                    <td class="plinner">
                        <?php $fecha = odbc2date($row['fecha_fin_plan']); ?>
                        <?= $fecha ?>
                    </td>
                    <td class="plinner">
                        <?php
                        if (empty($row['fecha_inicio_real']))
                            $fecha = '&nbsp;';
                        else
                            $fecha = odbc2date($row['fecha_inicio_real']);
                        ?>

                        <?= $fecha ?>
                    </td>
                    <td class="plinner">
                        <?php
                        if (empty($row['fecha_inicio_real']))
                            $fecha = '&nbsp;';
                        else
                            $fecha = odbc2date($row['fecha_fin_real']);

                        if (empty($fecha))
                            $fecha = '&nbsp;';
                        ?>

                        <?= $fecha ?>
                    </td>
                    <td class="plinner">
                        <?= textparse($row['descripcion']) ?>
                    </td>

                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

<?php require "inc/print_bottom.inc.php";?>