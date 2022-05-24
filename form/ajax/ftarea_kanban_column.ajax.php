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
require_once "../../php/class/tarea.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/kanban.class.php";


$id_tarea= $_GET['id_tarea'];
$id_kanban_column= $_GET['id_kanban_column'];
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'jkanban';
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : null;
$id_calendar= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : null;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$id_responsable= $_SESSION['id_usuario'];

$obj_task= new Ttarea($clink);
$obj_task->Set($id_tarea);
$id_proyecto= $obj_task->GetIdProyecto();
$id_proyecto= !empty($id_proyecto) ? $id_proyecto : null;
$id_responsable= $obj_task->GetIdResponsable();
$fecha_inicio_plan= $obj_task->GetFechaInicioPlan();
$observacion= $obj_task->GetObservacion();
$nombre= $obj_task->GetNombre();
$year= date('Y', strtotime($fecha_inicio_plan));

$obj_user= new Tusuario($clink);
$obj_user->SetIdUsuario($id_responsable);
$email= $obj_user->GetEmail($id_responsable);

$responsable= $email['nombre'];
if (!empty($email['cargo'])) 
    $responsable.= ", ".textparse($email['cargo']);
unset($obj_user);

$obj= new Tkanban($clink);
if (!empty($id_calendar))
    $obj->SetIdResponsable($id_calendar);
else
    $obj->SetIdProyecto($id_proyecto);
?>

<!-- Bootstrap core CSS -->
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<style type="text/css">
.container-column {
    width: 100%;
    min-height: 200px;
    overflow-y: auto;
    overflow-x: hidden;
}
.container-column .column {
    padding: 4px;
    margin: 4px;
    width: 30%;
    min-width: 110px;
    border-radius: 5px;
    height: min-content;
    float:left;
}
</style>

<script language='javascript' type="text/javascript" charset="utf-8">
function validar() {
    var text;

    $(".kanban_column").each(function() {
        if ($(this).is(':checked'))
            $('#id_kanban_column_target').val($(this).val());
    });

    if (!$("#id_kanban_column_target").val()) {
        text= "No ha especificado la columna destino. De continuar la tarea no se moverá. ¿Desea continuar?";
        confirm(text, function(ok) {
            if (ok) 
                _this_1();
            else
                return;
        });
    } 
    function _this_1() {
        ejecutar('column');
    }
}
</script>

<script type="text/javascript">
$(document).ready(function() {
    //When page loads...
    $(".tabcontent").hide(); //Hide all content
    $("ul.nav li:first a").addClass("active").show(); //Activate first tab
    $(".tabcontent:first").show(); //Show first tab content

    //On Click Event
    $("ul.nav li a").click(function() {
        $("ul.nav li a").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tabcontent").hide(); //Hide all tab content

        var activeTab = $(this).attr("href"); //Find the href attribute value to identify the active tab + content          
        $("#" + activeTab).fadeIn(); //Fade in the active ID content
        //         $("#" + activeTab + " .form-control:first").focus();
        return false;
    });

    try {
        $('#observacion_column').tinymce().destroy();
    } catch (e) {
        ;
    }

    tinymce.init({
        selector: '#observacion_column',
        theme: 'modern',
        language: 'es',
        height: 200,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor textcolor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime table paste code help wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify ' +
            '| bullist numlist outdent indent | removeformat | help',

        content_css: '../css/content.css'
    });

    try {
        $('#observacion_column').val(<?=json_encode($observacion)?>);
    } catch (e) {
        ;
    }

    <?php if (!is_null($error)) { ?>
    alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>

<ul class="nav nav-tabs" style="margin-bottom: 10px;">
    <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
    <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Observaciones</a></li>
    <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Registros Anteriores</a></li>
</ul>

<form class="form-horizontal" id="fkanban_column" name="fkanban_column" action="javascript:validar()" method="post">
    <input type="hidden" name="exect" value="drag_task" />
    <input type="hidden" name="id_calendar" id="id_calendar" value="<?=$id_usuario?>" />
    <input type="hidden" name="id_responsable" value="<?=$id_responsable?>" />
    <input type="hidden" id="id" name="id" value="<?=!empty($id_evento) ? $id_evento : $id_tarea?>)" />
    <input type="hidden" id="id_evento" name="id_evento" value="<?=!empty($id_evento) ? $id_evento : 0 ?>" />
    <input type="hidden" name="year" value="<?=$year ?>" />
    <input type="hidden" id="id_tarea" name="id_tarea" value="<?=$id_tarea?>" />
    <input type="hidden" id="id_proyecto" name="id_proyecto" value="<?=$id_proyecto?>" />
    <input type="hidden" name="menu" value="regtarea" />

    <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />
    
    <input type="hidden" id="id_kanban_column_origen" name="id_kanban_column_origen" value="<?=$id_kanban_column?>" />
    <input type="hidden" id="id_kanban_column_target" name="id_kanban_column_target" value="0" />

    <div id="tab1" class="tabcontent">
        <div class="alert alert-info">
            <strong>Tarea: </strong><?=$nombre?>
            <br />
            <strong>Responsable: </strong> <?=$responsable?>
        </div>

        <div class="container-column">
            <?php
            $result= $obj->listar();
            while ($row= $clink->fetch_array($result)) {
            ?>
                <div class="column <?=$row['class']?>">
                    <input type="radio" class="kanban_column" name="kanban_column" id="kanban_column_<?=$row['_id']?>"  
                        value="<?=$row['_id']?>" <?php if ($row['_id'] == $id_kanban_column) {?>checked="checked"<?php } ?> />
            
                    <?=$row['_nombre']?>
                </div>
            <?php } ?>
        </div>
    </div>


    <div id="tab2" class="tabcontent">
        <div class="form-group row">
            <div class="col-12">
                <textarea name="observacion" rows="6" id="observacion_column"
                    class="form-control"><?=$observacion?></textarea>
            </div>
        </div>
    </div>

    <div id="tab3" class="tabcontent">
        <table id="table" 
            class="table table-hover table-striped" 
            data-toggle="table" 
            data-height="350"
            data-search="true" 
            data-show-columns="true">

            <thead>
                <tr>
                    <th>No.</th>
                    <th>COLUMNA</th>
                    <th>FECHA Y HORA</th>
                    <th>RESPONSABLE</th>
                    <th>OBSERVACIÓN</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $result= $obj->list_reg();

                $i = 0;
                while ($row = $clink->fetch_array($result)) {
                ?>
                <tr>
                    <td><?= ++$i ?></td>
                    <td><?= $row['nombre'] ?></td>
                    <td><?= odbc2time_ampm($row['cronos']) ?></td>
                    <td>
                        <?php
                        $email= $obj_user->GetEmail($row['id_usuario']);
                        echo $email['nombre'];
                        echo !empty($email['cargo']) ? ", ".textparse($email['cargo']) : null;
                        ?>
                    </td>

                    <td><?= $row['observacion'] ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>


    <div id="_submit" class="btn-block btn-app">
        <button class="btn btn-primary" type="submit"> Aceptar</button>
        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
    </div>

    <div id="_submited" class="submited" align="center" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operación puede tardar unos minutos
        ........
    </div>

</form>