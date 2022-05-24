<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";

require_once "../../php/class/schedule.class.php";
require_once "../../php/class/evento.class.php";


$signal= $_GET['signal'];
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : 0;
$id_tarea= !empty($_GET['id_tarea']) ? $_GET['id_tarea'] : 0;

$obj= new Tevento($clink);
$obj->SetIdTarea($id_tarea);

$obj_task= new Ttarea($clink);
$obj_task->SetIdTarea($id_tarea);
$obj_task->Set();

$year_init= date('Y', strtotime($obj_task->GetFechaInicioPlan()));
$year_fin= date('Y', strtotime($obj_task->GetFechaFinPlan()));

$obj->SetYear($year_init);
$obj->get_eventos_by_tarea(null, array($year_init, $year_fin));

$cant_event= 0;
$array_dates= array();
foreach ($obj->array_eventos as $evento) {
    ++$cant_event;
    $array_dates[]= odbc2date($evento['fecha_inicio']);
}
?>

<link href="../../libs/spinner-button/spinner-button.css" rel="stylesheet" />
<script type="text/javascript" src="../../libs/spinner-button/spinner-button.js"></script>

<script type="text/javascript" src="../../js/time.js?version="></script>

<script type="text/javascript" language='javascript'>
function set_year() {
    var year = $('#regdate_year').val();
    $('#regdate_month optgroup').hide();
    $('#regdate_month').val(0);
    $('#y' + year).show();
    set_month();
}

function set_month() {
    var year = $('#regdate_year').val();
    var month = $('#regdate_month').val();
    $('#regdate_day optgroup').hide();
    $('#y' + year + '-' + 'm' + month).show();
    $('#regdate_day').val(0);
}

function validar_planning() {
    var form = document.forms['ftarea-hito-ajax'];
    tinymce.get('observacion_hit').save();
    /*
    alert(tinymce.get('observacion').getContent());
    alert(form.observacion.value);
    */

    if (isNaN(parseInt($('#regdate_day').val())) || isNaN(parseInt($('#regdate_month').val())) 
                                                    || isNaN(parseInt($('#regdate_year').val()))) {
        alert("Debe especificar año, mes y día de la fecha a la que se corresponde este registro.");
        return;
    }

    $('#fecha').val($('#regdate_day').val() + '/' + $('#regdate_month').val() + '/' + $('#regdate_year').val());

    if (!Entrada($('#fecha').val())) {
        alert('Debe especificar la fecha en la que se establece el hito o estado de avance de la tarea.');
        return;
    } else {
        if (!isDate_d_m_yyyyy($('#fecha').val())) {
            alert('Fecha del hito con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010 08:30 AM');
            return;
        }
    }

    if ($('#real').val() <= 0) {
        alert('No ha especificado el avance en el cumplimiento de la tarea para el hito que se definirá.');
        return;
    }
    if (!Entrada(form.observacion_hit.value)) {
        alert(
        'No ha realizado ninguna observación relativa al estado de la tarea en la fecha del hito que se definirá');
        return;
    }

    add_planning();
}
</script>

<script type="text/javascript" language='javascript'>
$(document).ready(function() {
    new BootstrapSpinnerButton('spinner-real', 1, 100);

    try {
        $('#observacion_hit').tinymce().destroy();
    } catch (e) {;}

    tinymce.init({
        selector: '#observacion_hit',
        theme: 'modern',
        language: 'es',
        height: 170,
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
        $('#observacion_hit').val(<?= json_encode($observacion)?>);
    } catch (e) {
        ;
    }

    var array_years = [];
    var array_months = [];
    var array_days = [];

    var array_dates = [
        <?php 
        $i= 0;
        foreach ($array_dates as $date) {
            ++$i;
            if ($i > 1)
                echo ", ";
            echo "'$date'";
        }
        ?>
    ];

    var count = <?=$i?>;
    var year;
    var month;
    var day;
    var _month;
    var date;

    for (var i = 0; i < count; i++) {
        if (!isDate(array_dates[i]))
            continue;

        date = new Fecha(array_dates[i])
        year = date.anio;
        month = date.mes;
        day = date.dia;

        if (array_years.indexOf(year) == -1) {
            array_years.push(year);
            $('#regdate_year').append('<option value="' + year + '">' + year + '</option>');
            $('#regdate_month').append('<optgroup id="y' + year + '"></optgroup>');
        }

        _month = 'y' + year + '-m' + month;
        if (array_days.indexOf(_month) == -1) {
            array_days.push(_month);
            $('#y' + year).append('<option value="' + month + '">' + monthNames[parseInt(month)] + '</option>');
            $('#regdate_day').append('<optgroup id="' + _month + '"></optgroup>');
        }

        $('#' + _month).append('<option value="' + day + '">' + day + '</option>');
    }

    $('#regdate_year').change(function() {
        set_year();    
    });
    $('#regdate_month').change(function() {
        set_month();             
    });        
    set_year();
});
</script>

<?php if ($cant_event > 1) { ?>
    <form class="form-horizontal" id="ftarea-hito-ajax" name="ftarea-hito-ajax" action='javascript:validar_planning()'
        method="post">
        <input type="hidden" id="fecha" name="fecha" value="" />
        <input type="hidden" id="menu" name="menu" value="tarea_hito_ajax" />
        <input type="hidden" id="id" name="id" value="<?=$id_tarea?>" />

        <div class="form-group row">
            <label class="col-form-label col-2">
                Planificación:
            </label>
            <div class="row col-10">
                <label class="col-form-label col-2">
                    Año:
                </label>
                <div class=" col-2">
                    <select id="regdate_year" class="form-control">
                    </select>
                </div> 
                <label class="col-form-label col-2">
                    Mes:
                </label>
                <div class=" col-3">
                    <select id="regdate_month" class="form-control">
                    </select>
                </div>                   
                <label class="col-form-label col-1">
                    Día:
                </label>
                <div class=" col-2">
                    <select id="regdate_day" class="form-control">
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-form-label col-3">
                Porciento de ejecución:
            </label>
            <div class="col-4">
                <div id="spinner-real" class="input-group spinner">
                    <input type="text" name="real" id="real" class="form-control" value="1">

                    <span class="input-group-text">%</span>

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
            <div class="col-12">
                <textarea name="observacion_hit" id="observacion_hit" class="form-control" rows="4"></textarea>
            </div>
        </div>


        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="submit"> Aceptar</button>
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-panel-hito')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operaciÃ³n puede tardar unos minutos
            ........
        </div>
    </form>
<?php } else { ?>
    <div class="col-12 m-2 mt-3">
        <div class="alet alert-danger">
            Para definirles hítos a la tarea, esta debe tener no menos de dos días planificados 
        </div>        
    </div>

    <div id="_submit" class="btn-block btn-app">
        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-panel-hito')">Cancelar</button>
    </div>
<?php } ?>