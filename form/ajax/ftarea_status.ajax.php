<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/time.class.php";

require_once "../../php/class/tmp_tables_planning.class.php";
require_once "../../php/class/register_planning.class.php";
require_once "../../php/class/tarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/riesgo.class.php";
require_once "../../php/class/nota.class.php";

require_once "../class/tarea.signal.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= $_GET['month'];
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];

$id= null;
$id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : null;
$id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : null;

$obj_user= new Tusuario($clink);
$obj= new Ttarea($clink);
$obj_signal= new Ttarea_signals;
$obj_event= new Tevento($clink);

$obj= null;

if (!is_null($id_riesgo)) {
    $obj= new Triesgo($clink);
    $id= $id_riesgo;
}
if (!is_null($id_nota)) {
    $obj= new Tnota($clink);
    $id= $id_nota;
}

$obj->Set($id);

$fecha_inicio= $id_riesgo ? $obj->GetFechaInicioPlan() : $obj->GetFechaInicioReal();
$fecha_fin= $obj->GetFechaFinPlan();

$obj->SetInicio(date('Y', strtotime($fecha_inicio)));
$obj->SetFin(date('Y', strtotime($fecha_fin)));
$obj->SetYear($year);
$obj->SetMonth(null);

$id_proceso= $id_proceso != $_SESSION['id_entity'] ? $id_proceso : null; 
$obj->SetIdproceso($id_proceso);
$result= $obj->listar_tareas($id, $id_proceso, true);

$obj_task= new Ttarea($clink);
$obj_task->SetYear($year);
$obj_task->SetMonth($year < date('Y') ? 12 : date('m'));
$obj_task->SetDay($year < date('Y') ? 31 : date('d'));

$obj_reg= new Tregister_planning($clink);
$obj_reg->SetYear($year);
?>

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<style type="text/css">
.td-task {
    color: white;
    background: lightslategray;
}
</style>

<div class="card card-primary">
    <div class="card-header">
        <div class="row">
            <div class="panel-title win-drag col-11 m-0">ESTADO DE LAS TAREAS</div>
            <div class="col-1 m-0">
                <div class="close">
                    <a href="javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                        <i class="fa fa-close"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body form info-panel">
        <table id="table" class="table table-striped" 
        data-toggle="table" 
        data-height="340" 
        data-search="true"
        data-show-columns="true">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>TAREA</th>
                    <th>RESPONSABLE</th>
                    <th>OBSERVACIÓN</th>
                    <th>PARTICIPANTES</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $i = 0;
                    foreach ($obj->array_tareas as $id => $row) {
                        $year_init= date('Y', strtotime($row['fecha_inicio']));
                        $year_end= date('Y', strtotime($row['fecha_fin']));
                ?>

                <tr class="pline">
                    <td valign="top" rowspan="2">
                        <?= ++$i ?>
                    </td>
                    <td class="td-task">
                        <?= textparse($row['nombre']) ?>
                    </td>
                    <td class="td-task">
                        <?php
                        $email = $obj_user->GetEmail($row['id_responsable']);
                        echo textparse($email['nombre']);
                        if (!empty($email['cargo'])) 
                            echo ", ".textparse($email['cargo']);
                        ?>
                    </td>
                    <td class="td-task">
                        <?= textparse($row['descripcion']) ?>
                    </td>
                    <td>
                        <?php
                        $obj_task->SetInicio($year_init);
                        $obj_task->SetFin($year_end);
                        $string = $obj_task->get_participantes($id, 'tarea', $row['id_responsable']);
                        echo $string;

                        $origen_data = $obj->GetOrigenData('participant', $row['origen_data']);
                        if (!is_null($origen_data))
                            echo "<br /> " . merge_origen_data_participant($origen_data);
                        ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="5" align="center">
                        <?php
                        $obj->SetIdTarea($row['id']);
                        $obj->Set();
                        ?>

                        <table class="table">
                            <thead>
                                <tr>
                                    <td class="td-task">ESTADO DE CUMPLIMIENTO</td>
                                    <td class="td-task">FECHA PLANIFICADA</td>
                                    <td class="td-task">REGISTRO</td>
                                    <td class="td-task">OBSERVACIONES</td>
                                </tr>
                            </thead>

                            </tbody>
                            <?php
                                $obj_task->SetInicio($year_init);
                                $obj_task->SetFin($year_end);
                                $array_eventos = $obj_task->get_child_events_by_table('ttareas', $id);

                                foreach ($array_eventos as $array) {
                                    $row_event = $obj_reg->get_last_reg($array['id'], $array['id_responsable'], $array['year']);
                                    if (is_null($row_event)) continue;
                                ?>
                            <tr>
                                <?php $_array = $obj_signal->get_alarm($row_event); ?>
                                <td>
                                    <div class="alarm-box <?= $_array['class'] ?>" style="color: #000"><?= $_array['msg'] ?></div>
                                </td>

                                <td class="" style="width:80px!important;"><?= odbc2date($array['fecha_inicio_plan']) ?></td>

                                <td class="">
                                    <?php
                                    $email = $obj_user->GetEmail($array['id_responsable']);
                                    echo textparse($email['nombre']);
                                    if (!empty($email['cargo'])) echo ", ".textparse($email['cargo'])." <br/>";
                                    echo odbc2date($row_event['cronos']);
                                    ?>
                                </td>

                                <td class=""><?= textparse($row_event['observacion']) ?></td>
                            </tr>
                            <?php } ?>
                            <tbody>
                        </table>
        </td>
        </tr>

        <?php } ?>
        </tbody>
        </table>

        <!-- buttom -->
        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="reset" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
        </div>

    </div>
</div>