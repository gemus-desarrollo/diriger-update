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
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/programa.class.php";
require_once "../php/class/proyecto.class.php";
require_once "../php/class/regtarea.class.php";
require_once "../php/class/orgtarea.class.php";

require_once "../php/class/base_evento.class.php";
require_once "../form/class/tarea.signal.class.php";

$_SESSION['debug']= 'no';
$signal= 'gantt';

$force_user_process= true;
require_once "../php/inc_escenario_init.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$id_proyecto= 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : 0;
$id_programa= !empty($_GET['id_programa']) ? $_GET['id_programa'] : 0;
$acc= !empty($_SESSION['acc_planproject']) ? $_SESSION['acc_planproject'] : 0;

$obj_user= new Tusuario($clink);

$url_page= "../html/gantt.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_proyecto=$id_proyecto&id_programa=$id_programa";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <?php 
    if (!$auto_refresh_stop && (is_null($_SESSION['debug']) || $_SESSION['debug'] == 'no')) { 
        $delay= (int)$config->delay*60;
        header("Refresh: $delay; url=$url_page&csfr_token=123abc"); 
    }         
    ?>

    <title>GANTT - PROYECTOS</title>

    <?php require_once "inc/_proyecto_top_head.inc.php"; ?>

    <link rel="stylesheet" href="../libs/jsGantt-1.7.5.4/jsgantt-1.8.0.css?version=">
    <script type="text/javascript" src="../libs/tether-1.4.7/tether-1.4.7.min.js" charset="utf-8"></script>
    <script type="text/javascript" src="../libs/jsGantt-1.7.5.4/jsgantt-1.7.5.4.js?version=" charset="utf-8"></script>
    <script type="text/javascript" src="../js/jsgantt.js?version=" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
    i.fa.fa-road {
        color: #C28585;
        vertical-align: middle !important;
        text-align: left;
        margin: 3px;
    }
    </style>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }
    function _dropdown_prs(id) {
        $('#programa').val(id);
        refreshp();
    }
    function _dropdown_proyecto(id) {
        $('#proyecto').val(id);
        refreshp();
    }
    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp();
    }
    function _dropdown_month(month) {
        $('#month').val(month);
        refreshp();
    }

    function _dropdown_day(day) {
        $('#day').val(day);
        refreshp();
    }


    $(document).ready(function() {
        if (parseInt($('#id_proyecto').val()) > 0)
            $("#li-add-proyecto").hide();

        InitDragDrop();

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>

</head>

<body>
    <?php require_once "inc/_proyecto_top.inc.php";?>

    <div class="app-body dashboard container-fluid threebar">
        <div class="gantt" id="GanttChartDIV">

            <?php if (!empty($id_proyecto)) { ?>

            <script type="text/javascript" charset="utf-8">
            var g = new JSGantt.GanttChart(document.getElementById('GanttChartDIV'), 'week');
            if (g.getDivId() != null) {
                g.setCaptionType('Complete'); 
                // Set to Show Caption (None,Caption,Resource,Duration,Complete)
                g.setQuarterColWidth(36);
                g.setDateTaskDisplayFormat('day dd month yyyy'); 
                // Shown in tool tip box
                g.setDayMajorDateDisplayFormat('mon yyyy - Week ww') 
                // Set format to display dates in the "Major" header of the "Day" view
                g.setWeekMinorDateDisplayFormat('dd mon') 
                // Set format to display dates in the "Minor" header of the "Week" view
                g.setShowTaskInfoLink(1); 
                // Show link in tool tip (0/1)
                g.setShowEndWeekDate(0); 
                // Show/Hide the date for the last day of the week in header for daily view (1/0)
                g.setUseSingleCell(10000); 
                        // Set the threshold at which we will only use one cell per table row (0 disables).  
                        // Helps with rendering performance for large charts.
                g.setFormatArr('Day', 'Week', 'Month', 'Quarter'); 
                // Even with setUseSingleCell using Hour format on such a large chart can cause issues in some browsers
                g.setLang('es');

                <?php
                $obj_task= new Tregtarea($clink);
                $obj_signal= new Ttarea_signals;

                $obj_signal->SetYear($year);
                $obj_signal->SetMonth($month);

                $obj_task->SetYear($year);
                $obj_task->SetMonth($month);
                $obj_task->SetDay($day);

                $obj_task->SetIdProyecto($id_proyecto);
                $obj_task->SetIdProceso($id_proceso);

                $result= $obj_task->listar(false);
                $obj_task->automatic_tarea_status($result);
                $obj_task->update_tarea_status();

                $clink->data_seek($result);
                while ($row= $clink->fetch_array($result)) {
                    $pID= $row['_id'];
                    $obj_task->SetIdTarea($pID);

                    if (isset($obj_user)) unset($obj_user);
                    $obj_user= new Tusuario($clink);
                    $obj_user->Set($row['id_responsable']);
                    $pRes= $obj_user->GetUsuario();

                    $pName= $row['tarea'];
                    $pStart= date('Y-m-d', strtotime($row['fecha_inicio_plan']));
                    $pEnd= date('Y-m-d', strtotime($row['fecha_fin_plan']));

                    $array= $obj_task->getTarea_reg($pID, false);
                    $pComp= !empty($array['valor']) ? $array['valor'] : "''";

                    if (boolean($row['ifgrupo']))
                        $pClass= 'ggroupblack';
                    else {
                        $alarm= $obj_signal->get_alarm_gtask($array);
                        $pClass= $alarm['class'];
                    }

                    $pLink= "ShowContentTask({$pID})";

                    $pGroup= boolean($row['ifgrupo']) ? 1 : 0;
                    $pMile= 0;
                    $pParent= empty($row['id_tarea_grupo']) ? 0 : $row['id_tarea_grupo'];
                    $pOpen= 1;

                    $pDepend= $obj_task->GetDependencies_string($pID);

                //	$pCaption;
                ?>
                g.AddTaskItem(new JSGantt.TaskItem(<?=$row['_id']?>, '<?=$pName?>', '<?=$pStart?>', '<?=$pEnd?>',
                    '<?=$pClass?>', '<?=$pLink?>', <?=$pMile?>, '<?=$pRes?>', <?=$pComp?>, <?=$pGroup?>,
                    <?=$pParent?>, <?=$pOpen?>, '<?=$pDepend?>', '', 'Observacion aqui', g));

                <?php } ?>

                g.Draw();
                g.DrawDependencies();
            } else {
                alert("Error, No es posible crear el diagrama de Gantt");
            }
            </script>
            <?php } ?>
        </div> <!-- GanttChartDIV -->

        <?php
        $cant_task= 0;
        $clink->data_seek($result);
        while ($row= $clink->fetch_array($result)) {
            ++$cant_task;
            $email= $obj_user->GetEmail($row['id_responsable']);
            $responsable= textparse($email['nombre']);
            $responsable.= !empty($email['cargo']) ? ", ".textparse($email['cargo']) : null;
        ?>
            <input type="hidden" id="id_tarea_<?=$row['_id']?>" value="<?=$row['_id']?>" />
            <input type="hidden" id="tarea_<?=$row['_id']?>" value="<?=$row['tarea']?>" />
            <input type="hidden" id="descripcion_<?=$row['_id']?>" value="<?= textparse($row['descripcion'])?>" />
            <input type="hidden" id="responsable_<?=$row['_id']?>" value="<?=$responsable?>" />
            <input type="hidden" id="fecha_inicio_<?=$row['_id']?>" value="<?= odbc2date($row['fecha_inicio_plan'])?>" />
            <input type="hidden" id="fecha_fin_<?=$row['_id']?>" value="<?= odbc2date($row['fecha_fin_plan'])?>" />
            <input type="hidden" id="avance_<?=$row['_id']?>" value="<?=$cumplimiento?>" />
            <input type="hidden" id="numero_<?=$row['_id']?>" value="<?=$row['_numero']?>" />
            <input type="hidden" id="ifgrupo_<?=$row['_id']?>" value="<?=boolean($row['ifgrupo'])?>" />
        <?php } ?>

        <?php require_once "inc/_tablero_gantt_botom.inc.php";?>
</body>

</html>