<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/usuario.class.php";

require_once "../../php/class/programa.class.php";
require_once "../../php/class/politica.class.php";
require_once "../../php/class/objetivo_ci.class.php";
require_once "../../php/class/inductor.class.php";
require_once "../../php/class/indicador.class.php";

require_once "../../php/class/plantrab.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/proceso.class.php";

require_once "../../php/class/peso.class.php";


$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];
$id_proceso= $_GET['id_proceso'];

$id= $_GET['id'];
$_item= $_GET['_item'];
$signal= $_GET['signal'];
$i_global= $_GET['i_global'];
  
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;	

$action= 'list'; 
$if_jefe= false;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$id_proceso_code= $obj_prs->get_id_proceso_code();

if ($_SESSION['nivel'] >= _ADMINISTRADOR || $obj_prs->GetIdResponsable() == $_SESSION['id_usuario']) {
   $action= 'edit'; 
   $if_jefe= true;
}

$obj_user= new Tusuario($clink);
$obj= new Tpeso($clink);

$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetIdProceso($id_proceso);

$item= null;
$result= null;

switch($_item) {
    case 'per': {
        $item= 'treg_perspectiva';
        $obj->flag_field_prs= false;
        $result= $obj->get_observacion($item, 'id_perspectiva', $id, true);
        break;
    }
    case 'prog': {
        $item= 'treg_programa';
        $obj->flag_field_prs= true;
        $result= $obj->get_observacion($item, 'id_programa', $id, true);
        break;
    }
    case 'pro': {
        $item= 'treg_proceso'; 
        $obj->SetIdProceso(null);
        $obj->flag_field_prs= false;
        $result= $obj->get_observacion($item, 'id_proceso', $id, true);
        break;
    }
    case 'pol': {
        $item= 'treg_politica'; 
        $obj->SetIdProceso(null);
        $obj->flag_field_prs= true;
        $result= $obj->get_observacion($item, 'id_politica', $id, true);
        break;
    }	
    case 'ind': {
        $item= 'treg_inductor';
        $obj->flag_field_prs= false;
        $result= $obj->get_observacion($item, 'id_inductor', $id, true);
        break;
    }	
    case 'obj': {
        $item = 'treg_objetivo';
        $obj->flag_field_prs = true;
        $result = $obj->get_observacion($item, 'id_objetivo', $id, true);
        break;
    }
    case 'obj_sup': {
        $item = 'treg_objetivo';
        $obj->flag_field_prs = true;
        $result = $obj->get_observacion($item, 'id_objetivo', $id, true);
        break;
    }
    case 'obj_ci': {
        $item = 'treg_objetivo_ci';
        $obj->flag_field_prs = true;
        $result = $obj->get_observacion($item, 'id_objetivo', $id, true);
        break;
    }
}

$row= $clink->fetch_array($result);
$observacion= $row['observacion'];
$value= $row['valor'];
$id_usuario= $row['id_usuario'];
$email_user= $obj_user->GetEmail($row['id_usuario']);
$registro= odbc2date($row['reg_fecha']).'  '.$email_user['nombre'].', '.textparse($email_user['cargo']);
?>

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

<script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>

<script language='javascript' type="text/javascript" charset="utf-8">
function validar() {
    var form = document.forms['frm'];

    if (!Entrada(form.observacion.value)) {
        $('#observacion').focus(focusin($('#observacion')));
        alert('Debe escribir las observaciones referentes al estado de cumplimiento.');
        return;
    }
    if (Entrada(form._observacion.value) && form.observacion.value == form._observacion.value) {
        $('#observacion').focus(focusin($('#observacion')));
        alert("Es la misma observación anterior. El sistema no tiene nada que hacer.");
        return;
    }

    _ejecutar();
}
</script>

<script type="text/javascript">
    $(document).ready(function() {
        tinymce.init({
            selector: '#observacion',
            theme: 'modern',
            height: 150,
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

    <?php if (!is_null($error)) { ?>
    alert("<?= str_replace("\n"," ", addslashes($error)) ?>");
    <?php } ?>
});
</script>


    <div class="card card-primary">
        <div class="card-header">
            <div class="row">
                <div id="win-title" class="panel-title ajax-title win-drag col-11 m-0">
                </div>
                <div class="col-1 m-0">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">

            <ul class="nav nav-tabs" style="margin-bottom: 10px;">
                <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Evaluación</a></li>
                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Registros Anteriores</a></li>
            </ul>

            <form id="frm" name="frm" action="javascript:validar()" method="post">

                <input type="hidden" name="id" value="<?= $id ?>" />
                <input type="hidden" name="id_code" value="<?= $id_code ?>" />

                <input type="hidden" name="_item" id="_item" value="<?= $_item ?>" />
                <input type="hidden" name="i_global" id="i_global" value="<?= $i_global ?>" />
                <input type="hidden" name="signal" value="<?= $signal ?>" />

                <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
                <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

                <input type="hidden" name="_value" value="<?= $value ?>" />
                <input type="hidden" id="_observacion" name="_observacion" value="<?= $observacion ?>" />

                <input type="hidden" name="exect" value="<?= $action ?>" />

                <input type="hidden" id="year" name="year" value="<?= $year ?>" />
                <input type="hidden" id="month" name="month" value="<?= $month ?>" />
                <input type="hidden" id="day" name="day" value="<?= $day ?>" />


                <div class="tabcontent" id="tab1">
                    <label class="alert alert-info text">
                        <strong style="margin-right: 10px;">Registro: </strong>
                        <?php if (!empty($row['id_usuario'])) echo $registro; ?>
                    </label>

                    <div class="form-group row col-12">
                        <textarea name="observacion" rows="7" id="observacion" class="form-control"><?=$observacion?></textarea>
                    </div>
                </div>

                <div class="tabcontent" id="tab2">
                    <table id="table" class="table table-striped" data-toggle="table" data-height="300"
                        data-search="true" data-show-columns="true">
                        <thead>
                            <tr>
                                <th>CORTE</th>
                                <th>RESPONSABLE</th>
                                <th>OBSERVACIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            //	$result= $obj->listar_status($status);
                            $clink->data_seek($result);
                            while ($row = $clink->fetch_array($result)) {
                                ?>
                            <tr>
                                <td>
                                    <?= odbc2date($row['reg_fecha']) ?>
                                </td>
                                <td>
                                    <?php
                                    $email_user = $obj_user->GetEmail($row['id_usuario']);
                                    echo $email_user['nombre'] . '<br />' . textparse($email_user['cargo']);
                                    echo "<br /> <strong>Registrado: </strong>" . odbc2time_ampm($row['cronos']);
                                    ?>
                                </td>
                                <td>
                                    <?= textparse($row['observacion']) ?>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <?php if ($action == 'edit') { ?>
                    <button class="btn btn-primary" type="submit">Aceptar</button>
                    <?php } ?>
                    <button class="btn btn-warning" type="reset"
                        onclick="CloseWindow('div-ajax-panel');">Cancelar</button>
                </div>

                <div id="_submited" style="display:none">
                    <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                </div>

            </form>

        </div> <!-- panel-body -->
    </div> <!-- panel -->
