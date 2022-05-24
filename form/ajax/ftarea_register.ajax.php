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
require_once "../../php/class/evento.class.php";
require_once "../../php/class/regtarea.class.php";

$id_tarea= !empty($_GET['id_tarea']) ? $_GET['id_tarea'] : 0;
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'gantt';
$id_calendar= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : $_SESSION['id_usuario'];

$id_responsable= $_SESSION['id_usuario'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$obj_task= new Ttarea($clink);
$obj_task->Set($id_tarea);
$id_proyecto= $obj_task->GetIdProyecto();
$id_proyecto= !empty($id_proyecto) ? $id_proyecto : 0;
$id_responsable= $obj_task->GetIdResponsable();
$fecha_inicio_plan= $obj_task->GetFechaInicioPlan();
$observacion= $obj_task->GetObservacion();
$nombre= $obj_task->GetNombre();
$year= date('Y', strtotime($fecha_inicio_plan));

$obj_evento= new Tevento($clink);
$obj_evento->SetYear($year);
$obj_evento->get_eventos_by_tarea($id_tarea);
$array_eventos= $obj_evento->array_eventos;

$time= new TTime();
$fecha_fin_plan= $obj_evento->GetFechaFinPlan();
$fecha_fin_plan= add_date($fecha_fin_plan, $config->breaktime);

$actual_date= $time->GetStrTime();
$init= _NO_INICIADO;

if (strtotime($fecha_fin_plan) <= strtotime($actual_date)) 
    $init= _COMPLETADO;

$obj_user= new Tusuario($clink);
$email= $obj_user->GetEmail($id_responsable);

$responsable= $email['nombre'];
if (!empty($email['cargo'])) 
    $responsable.= ", ".textparse($email['cargo']);

?>

<!-- Bootstrap core CSS -->
<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
<!--  -->

<script language='javascript' type="text/javascript" charset="utf-8">
function validar() {
    if (parseInt($('#eventos').val()) == 0) {
        alert("Debe de especificar el corte al que se refiere la evaluación");
        return;
    }
    if (parseInt($('#cumplimiento_').val()) == 0) {
        alert('Debe deinir el estado de avance o cumplimiento de la tarea.');
        return;
    }

    var estado= $('#estado').val();
    if(estado == 0) {
        alert("No ha definido el estado de ejecución de la tarea hasta la fecha o hito referido");
        if($('#real_').val() < evento_cump 
            && (estado != <?=_CANCELADO?> && estado != <?=_POSPUESTO?> && estado != <?=_SUSPENDIDO?>)) {
            $('#estado').val(<?=_INCUMPLIDO?>);
        }
        if($('#real_').val() >= evento_cump) {
            $('#estado').val(<?=_CUMPLIDA?>);
        }
        return;
    }

    ejecutar('register');
}
</script>

<script type="text/javascript">
var array_evento_cump= Array();
var evento_cump= 0;

<?php 
$itotal= count($array_eventos);
$i= 0; 
reset($array_eventos);
foreach ($array_eventos as $id => $evento) {
    ++$i; 
    $value= (float)$i/$itotal*100;
    echo "array_evento_cump[$id]= ".number_format($value, 0).";";
} 
?>
</script>

<script type="text/javascript">
$(document).ready(function() {
    new BootstrapSpinnerButton('spinner-real_', 1, 100);

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
        $('#observacion_hour').tinymce().destroy();
    } catch (e) {
        ;
    }

    tinymce.init({
        selector: '#observacion_hour',
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
        $('#observacion').val(<?=json_encode($observacion)?>);
    } catch (e) {
        ;
    }

    evento_cump= this.value > 0 ? array_evento_cump[this.value] : 0;
    $('#eventos').change(function() {    
        evento_cump= array_evento_cump[this.value];     
        $('#real_').val(evento_cump); 
        $('#id_evento').val(this.value);
    });

    <?php if (!is_null($error)) { ?>
    alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>

<ul class="nav nav-tabs" style="margin-bottom: 10px;">
    <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Generales</a></li>
    <li id="nav-tab5" class="nav-item"><a class="nav-link" href="tab5">Observaciones</a></li>
    <li id="nav-tab6" class="nav-item"><a class="nav-link" href="tab6">Registros Anteriores</a></li>
</ul>

<form class="form-horizontal" id="fregtarea" name="fregtarea" action="javascript:validar()" method="post">
    <input type="hidden" name="exect" value="set" />
    <input type="hidden" name="id_calendar" id="id_calendar" value="<?=$id_usuario?>" />
    <input type="hidden" name="id_responsable" value="<?=$id_responsable?>" />
    <input type="hidden" id="id" name="id" value="<?=!empty($id_evento) ? $id_evento : $id_tarea?>)" />
    <input type="hidden" id="id_evento" name="id_evento" value="<?=!empty($id_evento) ? $id_evento : 0 ?>" />
    <input type="hidden" name="year" value="<?=$year ?>" />
    <input type="hidden" id="id_tarea" name="id_tarea" value="<?=$id_tarea?>" />
    <input type="hidden" id="id_proyecto" name="id_proyecto" value="<?=$id_proyecto?>" />
    <input type="hidden" name="menu" value="regtarea" />

    <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />

    <div id="tab4" class="tabcontent">
        <div class="alert alert-info">
            <strong>Tarea: </strong><?=$nombre?>
            <br />
            <strong>Responsable: </strong> <?=$responsable?>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3">
                Fecha de corte (YYYY-MM-DD):
            </label>

            <?php 
            reset($array_eventos);
            foreach ($array_eventos as $id => $evento) { 
            ?>
            <input type="hidden" id="id_evento_code_<?=$id?>" name="id_evento_code_<?=$id?>" value="<?=$evento['id_code']?>" />
            <?php } ?>

            <div id="eventos-container" class="col-2">
                <select id="eventos" name="eventos" class="form-control">
                    <option value="0"></option>
                    <?php 
                    reset($array_eventos);
                    foreach ($array_eventos as $id => $evento) { 
                    ?>
                    <option value="<?=$id?>"><?=date('Y-m-d', strtotime($evento['fecha_inicio']))?></option>
                    <?php } ?>
                </select>
            </div>

            <label class="col-form-label col-2">
                Ejecución al %:
            </label>

            <div class="col-2">
                <div id="spinner-real_" class="input-group spinner">
                    <input type="text" name="real_" id="real_" class="form-control" value="0">
                    <div class="input-group-btn-vertical">
                        <button class="btn btn-default" type="button" data-bind="up">
                            <i class="fa fa-arrow-up"></i>
                        </button>
                        <button class="btn btn-default" type="button" data-bind="down">
                            <i class="fa fa-arrow-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <div class="row col-12">
                <label class="col-form-label col-1">
                    Estado:
                </label>

                <div class="col-md-4 col-lg-3">
                    <select name="cumplimiento" id="cumplimiento" class="form-control">
                        <option value="0">Seleccione ... </option>
                        <?php for ($i= 1; $i < _MAX_STATUS_EVENTO; $i++) { ?>
                        <option value="<?= $i?>"><?= $eventos_cump[$i] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <label class="col-form-label col-2">
                    <input type="checkbox" name="ended" id="ended" value="1" <?=$ended?> onClick="test_ended(1)" />
                    Finalizado:
                </label>                
            </div>
        </div>

        <div class="form-group row">
            <div class="col-12">
                <div class="checkbox ">
                    <label class="text">
                        <input type="checkbox" name="radio_user" id="radio_user" value="1" checked="checked" />
                        Aplicar el estado de cumplimiento a en la fecha seleccionada a todos los participantes en la tarea y sus actividades.
                    </label>
                </div>
                <div class="checkbox">
                    <label class="text">
                        <input type="checkbox" name="radio_date" id="radio_date" value="1" />
                        Aplicar el estado de cumplimiento en todas las fechas posteriores a la fecha selecionada.
                    </label>
                </div>
            </div>
        </div>

    </div>


    <div id="tab5" class="tabcontent">
        <div class="form-group row">
            <div class="col-12">
                <textarea name="observacion" rows="6" id="observacion_hour"
                    class="form-control"><?=$observacion?></textarea>
            </div>
        </div>
    </div>


    <div id="tab6" class="tabcontent">
        <table id="table" 
            class="table table-hover table-striped" 
            data-toggle="table" 
            data-height="350"
            data-search="true" 
            data-show-columns="true">

            <thead>
                <tr>
                    <th>No.</th>
                    <th>ESTADO</th>
                    <th>REGISTRO</th>
                    <th>CORTE</th>
                    <th>%</th>
                    <th>COLUMNA KANBAN</th>
                    <th>OBSERVACIÓN</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $obj_task= new Tregtarea($clink);
                $obj_task->SetIdUsuario(null);
                $obj_task->SetCumplimiento(null);
                $obj_task->SetIdResponsable(null);
                $obj_task->SetIdTarea($id_tarea);

                if (!empty($id_proyecto))
                    $obj_task->SetIdProyecto($id_proyecto);
                else 
                    $obj_task->SetIdResponsable($id_calendar);
                
                $result = $obj_task->listTarea_reg($id_tarea, false);

                $i = 0;
                while ($row = $clink->fetch_array($result)) {
                ?>
                <tr>
                    <td>
                        <?= ++$i ?>
                    </td>
                    <td>
                        <?= $eventos_cump[$row['cumplimiento']] ?>
                    </td>
                    <td>
                        <?php
                        if (!empty($row['id_usuario'])) {
                            $email= $obj_user->GetEmail($row['id_usuario']);
                            $nombre= $email['nombre']. " ".!empty($email['cargo']) ? $email['cargo'] : null;
                            echo $nombre;
                        } elseif (!empty($row['_origen_data'])) {
                            $origen_data = $obj->GetOrigenData('user', $row['_origen_data']);
                            if (!empty($origen_data))
                                echo merge_origen_data_user($origen_data);
                        }
                        ?>
                        <br/><?= odbc2time_ampm($row['cronos']) ?>
                    </td>

                    <td>
                        <?=odbc2date($row['reg_fecha'])?>
                    </td>
                    <td>
                        <?=$row['valor']?>
                    </td>
                    <td>
                        <?=$row['kanban_column']?>
                    </td>
                    <td>
                        <?=textparse($row['observacion'])?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>


    <div id="_submit" class="btn-block btn-app">
        <?php if ($action != 'list') { ?>
        <button class="btn btn-primary" type="submit"> Aceptar</button>
        <?php } ?>
        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
    </div>

    <div id="_submited" class="submited" align="center" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operaciÃ³n puede tardar unos minutos
        ........
    </div>

</form>