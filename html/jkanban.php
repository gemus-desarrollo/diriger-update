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

require_once "../php/class/kanban.class.php";

require_once "../form/class/tarea.signal.class.php";

$_SESSION['debug']= 'no';
$signal= 'jkanban';

$force_user_process= true;
require_once "../php/inc_escenario_init.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$id_proyecto= 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : 0;
$id_programa= !empty($_GET['id_programa']) ? $_GET['id_programa'] : 0;
$acc= !empty($_SESSION['acc_planproject']) ? $_SESSION['acc_planproject'] : 0;

$obj_user= new Tusuario($clink);

$url_page= "../html/jkanban.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso";
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

    <title>KANBAN - PROYECTO</title>

    <?php require_once "inc/_proyecto_top_head.inc.php"; ?>

    <script type="text/javascript" src="../js/jsgantt.js"></script>

    <link rel="stylesheet" href="../libs/jkanban/jkanban.css" />
    <script type="text/javascript" src="../libs/jkanban/jkanban.js"></script> 
    <script type="text/javascript" src="../js/jkanban.js"></script> 

    <link rel="stylesheet" href="../css/jkanban.css" />
    <script type="text/javascript" src="../js/form.js?version="></script>


<script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp();
    }
    function _dropdown_prog(id) {
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


    $(document).ready( function () {
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

    <!-- app-body -->
    <div class="app-body form container-fluid twobar">
        <div id="myKanban" style="padding-top: 15px;">

        </div>
    </div>

    <?php
    $obj= new Tkanban($clink);
    $obj->SetIdProyecto($id_proyecto);
    $obj->SetIdResponsable(null);
    $obj->listar(false);

    foreach ($obj->array_kanban_columns as $column) {
        $obj->SetIdKanbanColumn($column['id']);
        $result= $obj->listar_tareas_from_column(true);

        while ($row= $clink->fetch_array($result)) {
    ?>
            <input type="hidden" id="id_tarea_<?=$row['_id']?>" value="<?=$row['_id']?>" />
            <input type="hidden" id="tarea_<?=$row['_id']?>" value="<?=$row['_nombre']?>" />
            <input type="hidden" id="descripcion_<?=$row['_id']?>" value="<?= textparse($row['descripcion'])?>" />
            <input type="hidden" id="responsable_<?=$row['_id']?>" value="<?=$responsable?>" />
            <input type="hidden" id="fecha_inicio_<?=$row['_id']?>" value="<?= odbc2date($row['fecha_inicio_plan'])?>" />
            <input type="hidden" id="fecha_fin_<?=$row['_id']?>" value="<?= odbc2date($row['fecha_fin_plan'])?>" />
            <input type="hidden" id="avance_<?=$row['_id']?>" value="<?=$cumplimiento?>" />
            <input type="hidden" id="numero_<?=$row['_id']?>" value="<?=$row['_numero']?>" />
    <?php } } ?>


    <script type="text/javascript">
    var KanbanTest = new jKanban({
        element: "#myKanban",
        gutter: "10px",
        widthBoard: "300px",

        itemHandleOptions: {
            enabled: true,
        },
        click: function(el) {
            id_kanban_task= kanban_getId(el.getAttribute('data-eid'));
            id_kanban_column= kanban_getId(el.offsetParent.getAttribute('data-id'));
            console.log("id_task="+id_kanban_task);
            console.log("id_kanban_column="+id_kanban_column);
            ShowContentTask(id_kanban_task);
        },
        dropEl: function(el, target, source, sibling) {
            id_kanban_task= kanban_getId(el.getAttribute('data-eid'));
            kanban_task_numero= $('#numero_'+id_kanban_task).val();
            id_kanban_column_origen= kanban_getId(source.parentElement.getAttribute('data-id'));
            id_kanban_column_target= kanban_getId(target.parentElement.getAttribute('data-id'));
            console.log('id='+id_kanban_task+' source='+id_kanban_column_origen+' target='+id_kanban_column_target);
            dragTask_ajax();
        },
        selectBoard: function(el, boardId) {
            id_kanban_column= kanban_getId(boardId);
            console.log("selectColumn="+id_kanban_column);

        },        
        deleteBoard: function(el, boardId) {
            id_kanban_column= kanban_getId(boardId);
            console.log("deleteColumn="+id_kanban_column);
            deleteBoard_ajax();
        },

        buttonClick: function(el, boardId) {
            console.log(id_kanban_task);
            
        },
        dragendBoard: function(el) {
            dragColum_ajax();
        },
        
        boards: [
            <?php
            $i= 0;
            reset($obj->array_kanban_columns);
            foreach ($obj->array_kanban_columns as $column) {
                echo $i > 0 ? ", " : "";
                ++$i;

                $obj->SetIdKanbanColumn($column['id']);
                $obj->listar_tareas_from_column(false);
            ?>
                {
                    id: "_column<?=$column['id']?>",
                    title: "<?=textparse($column['nombre'])?>",
                    class: "<?=$column['class']?>",
                    item: [
                        <?php
                        $j= 0;
                        foreach ($obj->array_kanban_tareas as $task) {
                            echo $j > 0 ? ", " : "";
                            ++$j;    
                        ?> 
                            {
                                id: "_task<?=$task['id']?>",
                                title: "<?=textparse($task['nombre'])?>",
                                drag: function(el, source) {
                                    // console.log("START DRAG : " + el.dataset.eid);
                                },
                                dragend: function(el) {
                                    // console.log("END DRAG : " + el.dataset.eid);
                                },
                                drop: function(el) {
                                    // console.log("DROPPED : " + el.dataset.eid);
                                }
                            }   
                        <?php } ?>
                    ]
                }
            <?php } ?>
            ]
    });

    </script>

    <?php require_once "inc/_tablero_gantt_botom.inc.php";?>

</body>

</html>