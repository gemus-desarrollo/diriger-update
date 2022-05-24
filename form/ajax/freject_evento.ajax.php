<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/auditoria.class.php";

$_SESSION['debug']= 'no';

$id_evento= $_GET['id_evento'];
$id_tarea= $_GET['id_tarea'];
$id_auditoria= $_GET['id_auditoria'];

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
//$empresarial= !empty($_GET['empresarial']) ? $_GET['empresarial'] : 0;

$print_reject= !empty($_GET['print_reject']) ? $_GET['print_reject'] : 0;

$signal= $_GET['signal'];

$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

if ($signal == 'anual_plan') $empresarial= 2;
if ($signal == 'calendar' && empty($id_usuario)) $id_usuario= $_SESSION['id_usuario'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

if ($signal != 'anual_plan_audit') {
    $obj= new Tevento($clink);
    $obj->SetIdEvento($id_evento);
    $obj->SetIdUsuario($id_usuario);
}

if ($signal == 'anual_plan_audit') {
    $obj= new Tauditoria($clink);
    $obj->SetIdAuditoria($id_auditoria);
}

$obj->Set();
$cumplimiento= $obj->GetCumplimiento();
$id_responsable= $obj->GetIdResponsable();

if ($signal != 'anual_plan_audit') {
    $nombre= $obj->GetNombre();
}

if ($signal == 'anual_plan_audit') {
    $obj_prs= new Tproceso($clink);
    $obj_prs->Set($id_proceso);
    $nombre_prs= $obj_prs->GetNombre();
    $nombre= "Auditoria ".$Ttipo_auditoria_array[$obj->GetTipo()].$nombre_prs.'<br/>'.$Ttipo_proceso_array[$obj_prs->GetTipo()];
}

$time= new TTime();
$fecha_fin_plan= $obj->GetFechaFinPlan();
$fecha_fin_plan= add_date($fecha_fin_plan, (int)$config->breaktime);

$actual_date= $time->GetStrTime();

$init= _NO_INICIADO;
if (strtotime($fecha_fin_plan) <= strtotime($actual_date)) $init= _COMPLETADO;

$obj_usr= new Tusuario($clink);

$if_jefe= false;
if (($obj->GetIdResponsable() == $_SESSION['id_usuario'] || $obj->get_id_user_asigna() == $_SESSION['id_usuario'])
    || $_SESSION['nivel'] >= _ADMINISTRADOR) $if_jefe= true;

if ($empresarial == 1 || $empresarial == 2) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->Set($id_proceso);
    
    if ($_SESSION['nivel'] >= _ADMINISTRADOR || $obj_prs->GetIdResponsable() == $_SESSION['id_usuario']) $if_jefe= true;
}
?>

<script language='javascript' type="text/javascript" charset="utf-8">
function validar() {
    var form = document.forms['freject'];
    var text;

    if (!Entrada($('#observacion').val())) {
        alert('Debe especificar las razones por la que rechaza la tarea.');
        return;
    }

    $('#observacion').val(trim_str($('#observacion').val()));

    <?php if ($if_jefe) { ?>
    if (document.getElementById('radio_user').checked) form._radio_user.value = 1;
    else form._radio_user.value = 0;
    <?php } ?>

    if ($('#observacion').val().length < 5) {
        text =
            "Por favor, la explicación no puede ser tan corta, explicaciones como -ok-,  -si-, -no- no son aceptados, ";
        text += "por favor sea más explicito."
        alert(text);
        return;
    }

    if ($('#signal').val() == 'calendar') {
        if ($('#_id_responsable').val() == $('#id_calendar').val()) {
            text = "Usted está tratando de rechazar una tarea de la cual es responsable, deberá delegarla primero. ";
            text +=
                "¿De continuar la actividad será rechazada en los Planes Individuales de todos los participantes. Desea continuar?";
            confirm(text, function(ok) {
                if (ok) {
                    $('#_radio_user').val(1);
                    ejecutar('reject');
                } else return;
            });
        } else {
            ejecutar('reject');
        }
    } else {
        ejecutar('reject');
    }
}
</script>
<script type="text/javascript">
$(document).ready(function() {
    <?php if (!is_null($error)) { ?>
    alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>

<form id="freject" name="freject" action="javascript:validar()" class="form-horizontal" method=post>
    <input type="hidden" name="exect" value="set" />
    <input type="hidden" name="id_calendar" id="id_calendar" value="<?= $id_usuario ?>" />
    <input type="hidden" id="_id_responsable" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
    <input type="hidden" id="_id_responsable_ref" name="id_responsable_ref" value="<?= $id_responsable ?>" />
    <input type="hidden" name="id" value="<?= $id_evento ?>" />

    <input type="hidden" id="day" name="day" value="<?= $day ?>" />
    <input type="hidden" id="month" name="month" value="<?= $month ?>" />
    <input type="hidden" id="year" name="year" value="<?= $year ?>" />

    <input type="hidden" name="_radio_user" id="_radio_user" value="<?=$signal == 'calendar' ? 0 : 1?>" />

    <input type="hidden" name="menu" value="freject" />

    <input type="hidden" name="signal" id="signal" value="<?= $signal ?>" />

    <div class="alert alert-info">
        <strong>Actividad: </strong><?=$nombre?><br />
        <div class="row">
            <div class="col-xs-6 col-sm-6 col-md-6">
                <strong>Inicio: </strong><?=odbc2date($obj->GetFechaInicioPlan())?>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6 pull-left">
                <strong>Fin: </strong><?=odbc2date($obj->GetFechaFinPlan())?>
            </div>
        </div>
    </div>

    <div class="form-group row">
        <label class="col-form-label col-xs-3 col-sm-2 col-md-2">
            Aplicar a:
        </label>
        <div class="col-xs-9 col-sm-9 col-md-9">
            <select id="extend" name="extend" class="form-control">
                <option value="A">Solo a esta actividad</option>
                <option value="U">A esta misma Actividad siempre que aparezca en el mes ...</option>
                <option value="D">A todas las actividades de este DíA ...</option>
                <option value="S">A todas las actividades de la SEMANA ...</option>
                <option value="M">A todas las actividades del MES ...</option>
            </select>
        </div>
    </div>

    <?php if ($if_jefe) { ?>
    <div class="form-group row">
        <div class="checkbox col-xs-12 col-sm-12 col-md-12" style="margin-left: 20px;">
            <label class="text">
                <input type="checkbox" name="radio_user" id="radio_user" value="1" />
                Rechazar en los Planes de Trabajo Individuales de todos los implicados.
            </label>
        </div>
    </div>
    <?php } ?>

    <div class="form-group row">
        <label class="col-form-label col-xs-3 col-sm-2 col-md-2">
            Observaciones:
        </label>
        <div class="col-xs-9 col-sm-10 col-md-10">
            <textarea name="observacion" rows="4" id="observacion"
                class="form-control"><?= $obj->GetObservacion() ?></textarea>
        </div>
    </div>

    <div id="_submit" class="btn-block btn-app">
        <button class="btn btn-primary" type="submit"> Aceptar</button>
        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
    </div>

    <div id="_submited" class="submited" align="center" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operaciÃ³n puede tardar unos minutos
        ........
    </div>

</form>