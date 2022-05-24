<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";

require_once "../../php/class/proceso_item.class.php";
require_once "../../php/class/base_evento.class.php";

require_once "../../php/class/evento.class.php";
require_once "../../php/class/plantrab.class.php";
require_once "../../php/class/orgtarea.class.php";


$year= $_GET['year'];
$signal= $_GET['signal'];
$tipo_plan= $_GET['tipo_plan'];

$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$month= !empty($_GET['month']) ? $_GET['month'] : 0;
$action= !empty($_GET['action']) ? $_GET['action'] : 'object';
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $empresarial= 0;
    $id_proceso= 0;
} else {
    $id_usuario= 0;
}

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL) {
    $empresarial= 2;
    $month= 0;
}

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL) 
    $empresarial= 1;
if ($tipo_plan == _PLAN_TIPO_MEETING || $tipo_plan == _PLAN_TIPO_AUDITORIA || $tipo_plan == _PLAN_TIPO_SUPERVICION) 
    $month= 0;
if (isset($_GET['hide_trz'])) 
    $hide_trz= $_GET['hide_trz'];

$if_jefe= false;
$acc= $signal != 'anual_plan_audit' ? $_SESSION['acc_planwork'] : $_SESSION['acc_planaudit'];

if ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $if_jefe= false;
    $obj_prs= new Tproceso($clink);
    $array= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], $id_proceso, null);

    if (!is_null($array) && array_key_exists($id_proceso, (array)$array)) 
        $if_jefe= true;
    if ($acc == _ACCESO_ALTA || $_SESSION['nivel'] >= _SUPERUSUARIO) 
        $if_jefe= true;
    if ($acc >= 1 && $_SESSION['usuario_proceso_id'] == $id_proceso) 
        $if_jefe= true;
}

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $obj_org= new Torgtarea($clink);
    $obj_org->SetIdUsuario($id_usuario);
    $if_jefe= $obj_org->if_chief($_SESSION['id_usuario']);
 	
    if ($_SESSION['nivel'] >= _SUPERUSUARIO) 
        $if_jefe= true;
    if ($_SESSION['id_usuario'] == $id_usuario && ($action == 'object' || $action= 'auto_eval')) 
        $if_jefe= true;
}

if ($action == 'aprove' && $hide_trz)
    $action= 'object';
$status= ($action == 'object') ? _OBJETIVO_FIJADO : _APROBADO;

$obj= new Tplantrab($clink);
($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) ? $obj->SetIdProceso(null) : $obj->SetIdProceso($id_proceso);
($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL)? $obj->SetIdUsuario($id_usuario) : $obj->SetIdUsuario(null);

$obj->SetIdResponsable($_SESSION['id_usuario']);
$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetTipoPlan($tipo_plan);

$id_plan= $obj->Set();
$id_plan_code= $obj->get_id_code();

$objetivo= $obj->GetObjetivo();

$id_proceso= $obj->GetIdProceso();
$id_proceso_code= $obj->get_id_proceso_code();

if (isset($_SESSION['obj_plantrab'])) 
    unset($_SESSION['obj_plantrab']);
?>

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>


<script type="text/javascript" charset="utf-8">
function validar_ap() {
    var form = document.forms['frm_ap'];
    var text;

    <?php if ($action == 'object') { ?>
    if (!Entrada(form.objetivos.value)) {
        text = "Debe definir los Objetivos o Tareas Principales de este Plan";
        alert(text);
        return;
    }
    <?php } ?>

    form.objetivos.value = trim_str(form.objetivos.value);

    if (Entrada(form.objetivos.value) && form.objetivos.value.length < 5) {
        text =
            "Por favor, las Tareas principales del Plan u Objetivos no deben ser tan cortos, textos como -ok-,  -si-, -no-, ";
        text += "no son aceptados, por favor sea mÃ¡s explicito.";
        alert(text);
        return;
    }

    <?php if ($signal == 'calendar') { ?>
    if (document.getElementById('radio_user').checked) document.getElementById('_radio_user').value = 1;
    <?php } ?>

    ejecutar('<?= $action?>');
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

    tinymce.init({
        selector: '#objetivos',
        theme: 'modern',
        height: 200,
        language: 'es',
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
        $('#objetivos').val(<?= json_encode($objetivo)?>);
    } catch (e) {
        ;
    }

    <?php if (!is_null($error)) { ?>
    alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>

<?php 
switch($empresarial) {
    case 0:
        $title= "INDIVIDUAL";
        break;
    case 1:
        $title= "GENERAL";
        break;
    case 2:
        $title= "ANUAL";
        break;										
}
?>

<ul class="nav nav-tabs" style="margin-bottom: 10px;">
    <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1"><?= $action == 'ob' ? 'Objetivos' : "Generales"?></a></li>
    <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Registros Anteriores</a></li>
</ul>

<form id="frm_ap" name="frm_ap" action="javascript:validar_ap()" method="post">
    <input type="hidden" name="id" value="<?= $id_plan ?>" />
    <input type="hidden" name="id_code" value="<?= $id_plan_code ?>" />
    <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
    <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

    <input type="hidden" name="_objetivos" value="<?= textparse($objetivo, true) ?>" />

    <input type="hidden" name="exect" value="<?= $action ?>" />
    <input type="hidden" name="if_jefe" value="<?= $if_jefe ?>" />

    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>" />
    <input type="hidden" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
    <input type="hidden" id="year" name="year" value="<?= $year ?>" />
    <input type="hidden" id="_year" name="_year" value="<?= $year ?>" />
    <input type="hidden" id="month" name="month" value="<?= $month ?>" />
    <input type="hidden" name="empresarial" value="<?= $empresarial ?>" />
    <input type="hidden" name="tipo_plan" id=tipo_plan value="<?= $tipo_plan ?>" />

    <input type="hidden" id="_radio_user" name="_radio_user" value="0" />


    <div class="tabcontent" id="tab1">
        <?php if ($signal == 'calendar') { ?>
        <div class="form-group row">
            <div class="col-md-12">
                <label class="text">
                    <input type="checkbox" id="radio_user" name="radio_user" value="1" />
                    Transmitir estas tareas principles a los Planes Individuales de todos los subordinados.
                </label>
            </div>
        </div>
        <?php } ?>

        <?php if ($action == 'aprove' && $signal == 'anual_plan_audit') { ?>
        <div class="form-group row">
            <div class="col-md-12">
                <label class="text">
                    <input type="checkbox" id="radio_user" name="radio_user" value="1" />
                    Aprobar las tareas en los Planes Individuales de todos los subordinados implicados.
                </label>
            </div>
        </div>
        <?php } ?>

        <div class="form-group row">
            <div class="col-md-12 col-lg-12">
                <textarea name="objetivos" id="objetivos" class="form-control"><?= $objetivo ?></textarea>
            </div>
        </div>
    </div>


    <div class="tabcontent" id="tab2" class="container-fluid">
        <table id="table" class="table table-hover table-striped" data-toggle="table" data-height="280">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>FECHA Y HORA</th>
                    <th>RESPONSABLE y ORIGEN</th>
                    <th>OBJETIVOS O TAREAS PRINCIPALES</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $obj->SetCumplimiento(NULL);
                    $obj->SetIdUsuario($id_usuario);
                    $obj->SetIfEmpresarial($empresarial);
                    
                    if (!empty($id_proceso))
                        $obj->SetIdProceso($id_proceso);

                    $result = $obj->listar_status_plan($status);
                    $i = 0;
                    while ($row = $clink->fetch_array($result)) {
                    ?>
                <tr>
                    <td><?= ++$i ?></td>
                    <td><?= odbc2time_ampm($row['cronos']) ?></td>
                    <td>
                        <?php
                                $obj_user= new Tusuario($clink);
                                $email= $obj_user->GetEmail($row['id_usuario']);
                                $nombre= $email['nombre'];
                                if (!empty($email['cargo'])) $nombre.= "<br/>" . textparse($email['cargo']);
                                echo $nombre;
                                ?>
                    </td>

                    <td><?= textparse($row['observacion']) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div id="_submit" class="btn-block btn-app">
        <?php if ($if_jefe && $action == 'aprove') { ?>
        <button type="submit" class="btn btn-primary">Aprobar</button>
        <?php } ?>
        <?php if ($if_jefe && $action == 'object') { ?>
        <button type="submit" class="btn btn-primary">Aceptar</button>
        <?php } ?>

        <button type="reset" class="btn btn-warning" onclick="CloseWindow('div-ajax-panel');">Cancelar</button>
    </div>

    <div id="_submited" class="submited" align="center" style="display:none">
        <img src="../img/loading.gif" alt="cargando" /> Por favor espere, la operaciÃ³n puede tardar unos minutos
        ........
    </div>
</form>