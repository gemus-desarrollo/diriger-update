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

require_once "../../php/class/tmp_tables_planning.class.php";
require_once "../../php/class/register_planning.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/tarea.class.php";
require_once "../../php/class/auditoria.class.php";

require_once "../../php/class/tipo_auditoria.class.php";

$_SESSION['debug']= 'no';

$id= $_GET['id'];
$signal= $_GET['signal'];
$year= $_GET['year'];

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : 0;
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$id_tarea= !empty($_GET['id_tarea']) ? $_GET['id_tarea'] : 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : 0;

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
//$empresarial= !empty($_GET['empresarial']) ? $_GET['empresarial'] : 0;
$print_reject= !empty($_GET['print_reject']) ? $_GET['print_reject'] : 0;

if ($signal == 'anual_plan')
    $empresarial= 2;
if ($signal == 'calendar' && empty($id_usuario))
    $id_usuario= $_SESSION['id_usuario'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($signal != 'anual_plan_audit') {
    $obj= new Tevento($clink);
    $id_evento= !empty($id_evento) ? $id_evento : $id;
    $obj->SetIdEvento($id_evento);
    $obj->SetIdUsuario($id_usuario);
}

if ($signal == 'anual_plan_audit') {
    $obj= new Tauditoria($clink);
    $id_auditoria= !empty($id_auditoria) ? $id_auditoria : $id;
    $obj->SetIdAuditoria($id_auditoria);
}

$obj->Set();
$cumplimiento= $obj->GetCumplimiento();
$rechazado= $obj->GetRechazado();
$id_responsable= $obj->GetIdResponsable();

if (!empty($id_proceso) && empty($id_usuario)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->SetYear($year);
    $rowcmp= $obj_prs->get_reg_proceso($id_evento, $id_auditoria);
    $cumplimiento= $rowcmp['cumplimiento'];
    $rechazado= $rowcmp['rechazado'];
    $id_responsable= $rowcmp['id_responsable'];
}

$only_activity= false;
if (!is_null($rechazado)
    || ($cumplimiento == _SUSPENDIDO || $cumplimiento == _CANCELADO || $cumplimiento == _DELEGADO || $cumplimiento == _POSPUESTO))
    $only_activity= true;

$fecha_inicio_plan= $obj->GetFechaInicioPlan();
$year= date('Y', strtotime($fecha_inicio_plan));
$month= date('m', strtotime($fecha_inicio_plan));
$day= date('d', strtotime($fecha_inicio_plan));
$obj->SetYear($year);

$id_tipo_reunion= $obj->GetIdTipo_reunion();

if ($signal != 'anual_plan_audit') {
    $nombre= $obj->GetNombre();
}

if (isset($obj_prs)) 
    unset($obj_prs);

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$id_proceso_code= $obj_prs->get_id_code();
$nombre_prs= $obj_prs->GetNombre();

if ($signal == 'anual_plan_audit') {
    $obj_tipo= new Ttipo_auditoria($clink);
    $obj_tipo->SetIdTipo_auditoria($obj->GetTipo());
    $nombre= "Auditoria ".$obj_tipo->GetNombre().$nombre_prs.'<br/>'.$Ttipo_proceso_array[$obj_prs->GetTipo()];
}

$time= new TTime();
$fecha_fin_plan= $obj->GetFechaFinPlan();
$fecha_fin_plan= add_date($fecha_fin_plan, (int)$config->breaktime);

$observacion= $obj->GetObservacion();

$actual_date= $time->GetStrTime();

$init= _NO_INICIADO;
if (strtotime($fecha_fin_plan) <= strtotime($actual_date))
    $init= _COMPLETADO;

$obj_usr= new Tusuario($clink);

$if_jefe= false;
if (($id_responsable == $_SESSION['id_usuario'] || $obj->get_id_user_asigna() == $_SESSION['id_usuario'])
    || $_SESSION['nivel'] > _ADMINISTRADOR)
    $if_jefe= true;

if ($empresarial == 1 || $empresarial == 2) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->Set($id_proceso);
    $id_proceso_code= $obj_prs->get_id_code();

    if ($_SESSION['nivel'] > _ADMINISTRADOR 
        || ($id_responsable == $_SESSION['id_usuario'] || $obj_prs->GetIdResponsable() == $_SESSION['id_usuario']))
        $if_jefe= true;
}

$obj_reg= new Tregister_planning($clink);
$obj->copy_in_object($obj_reg);
$obj->SetYear($year);
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script type="text/javascript" src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js?version="></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            var form= document.forms['fregevento'];
            var text;

            if (form.cumplimiento.value == 0) {
                alert('Debe deinir el estado de avance o cumplimiento del evento.');
                return;
            }
            if (!Entrada(form.observacion.value) && form.cumplimiento.value != <?= _COMPLETADO?>) {
                alert('Faltan las observaciones sobre el cumplimiento o estado en la ejecución evento.');
                return;
            }

            $('#_radio_prs').val(0);
            if ($('#id_proceso').val()) {
                if ($('#radio_prs1').is(':checked'))
                    $('#_radio_prs').val(1);
                if ($('#radio_prs2').is(':checked'))
                    $('#_radio_prs').val(2);
            }

            form.observacion.value= trim_str(form.observacion.value);

            if (Entrada(form.observacion.value) && form.observacion.value.length < 5) {
                text= "Por favor, la explicación no debe ser tan corta, explicaciones como -ok-, -si-, -no- ";
                text+= "no son aceptados, por favor sea más explicito.";
                alert(text);
                return;
            }
            <?php if ($if_jefe) { ?>
                if (document.getElementById('radio_user').checked)
                    form._radio_user.value= 1;
                else
                    form._radio_user.value= 0;
            <?php } ?>

            ejecutar('register');
        }

    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            try {
                $('#observacion').tinymce().remove();
                tinymce().remove('observacion');
            }
            catch(e) {

            }

            tinymce.init({
                selector: '#observacion',
                theme: 'modern',
                height: 130,
                language: 'es',
                plugins: [
                   'advlist autolink lists link image charmap print preview anchor textcolor',
                   'searchreplace visualblocks code fullscreen',
                   'insertdatetime table paste code help wordcount'
                ],
                toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify '+
                        '| bullist numlist outdent indent | removeformat | help',

                content_css: '../css/content.css'
            });

            try {
                $('#observacion').val(<?= json_encode($observacion)?>);
            } catch(e) {;}


            focusin=function(_this) {
               tabId= $(_this).parents('* .tabcontent');
               $(".tabcontent").hide();
               $('#nav-'+tabId.prop('id')).addClass('active');
               tabId.show();
               $(_this).focus();
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
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });
    </script>

    <ul class="nav nav-tabs" style="margin-bottom: 10px;">
		<li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
		<li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Observacion</a></li>
        <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Registros Anteriores</a></li>
	 </ul>

    <form id="fregevento" name="fregevento" class="form-horizontal" action="javascript:validar()"  method=post>
        <input type="hidden" name="exect" value="set" />

        <input type="hidden" name="id_calendar" id="id_calendar" value="<?= $id_usuario ?>" />
        <input type="hidden" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
        <input type="hidden" name="id" value="<?= $id ?>" />

        <input type="hidden" id="day" name="day" value="<?= $day ?>" />
        <input type="hidden" id="month" name="month" value="<?= $month ?>" />
        <input type="hidden" id="year" name="year" value="<?= $year ?>" />

        <input type="hidden" id="tipo_plan" name="tipo_plan" value=<?= $tipo_plan ?> />

        <input type="hidden" id="id_proceso" name="id_proceso" value=<?= $id_proceso ?> />
        <input type="hidden" id="id_proceso_code" name="id_proceso_code" value=<?= $id_proceso_code ?> />
        <input type="hidden" id="signal" name="signal" value="<?= $signal ?>" />
        <input type="hidden" id="_radio_user" name="_radio_user" value="0" />
        <input type="hidden" id="_radio_prs" name="_radio_prs" value="0" />

        <input type="hidden" id="print_reject" name="print_reject" value="<?= $print_reject ?>" />

        <input type="hidden" name="id_proyecto" id="id_proyecto" value="<?= $id_proyecto ?>" />

        <input type="hidden" name="menu" value="fregevento" />

        <div class="tabcontent" id="tab1">
            <div class="alert alert-info">
                <strong>Actividad: </strong><?=$nombre?><br />
                <div class="row">
                    <div class="col-6">
                        <strong>Inicio: </strong><?=odbc2date($obj->GetFechaInicioPlan())?>
                    </div>
                    <div class="col-6 pull-left">
                        <strong>Fin: </strong><?=odbc2date($obj->GetFechaFinPlan())?>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-1">
                    Estado:
                </label>
                <div class="col-3">
                    <select name="cumplimiento" id="cumplimiento" class="form-control">
                        <option value="0">Seleccione...</option>
                        <?php
                        for ($i = $init; $i < _MAX_STATUS_EVENTO; ++$i) {
                            if ($i == _CANCELADO || $i == _DELEGADO)
                                continue;
                            if ($i == _EN_CURSO && empty($id_proyecto))
                                continue;
                            ?>
                            <option value="<?= $i ?>" <?php if ($i == $cumplimiento) echo "selected='selected'" ?>><?= $eventos_cump[$i] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <label class="col-form-label col-2">
                    Aplicar a:
                </label>
                <div class="col-6">
                    <?php if ($signal == 'anual_plan' || $signal == 'anual_plan_audit') { ?>
                        <select id="extend" name="extend" class="form-control">
                            <?php if ($signal == 'anual_plan_audit') { ?>
                            <option value="A">Solo a esta actividad</option>
                            <?php } ?>
                            <option value="M">Solo las acciones de esta actividad correspondientes al mes en curso ...</option>
                            <option value="Y">A todas las acciones de esta actividad, planificadas en el año en curso ...  </option>
                            <option value="N">A todas las acciones de esta actividad planificadas en el año, a partir de esta fecha ...  </option>
                        </select>
                    <?php } ?>

                    <?php if ($signal != 'anual_plan' && $signal != 'anual_plan_audit') { ?>
                        <select id="extend" name="extend" class="form-control">
                            <option value="A">Solo a esta actividad</option>
                            <option value="U">A esta misma Actividad siempre que aparezca en el mes ...</option>
                            <?php if (!$only_activity) { ?>
                            <option value="D">A todas las actividades de este DíA ...</option>
                            <option value="S">A todas las actividades de la SEMANA ...</option>
                            <option value="M">A todas las actividades del MES ...</option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>
            </div>

            <?php if ($if_jefe) { ?>
            <div class="form-group row">
                <div class="checkbox col-sm-12 col-md-12" style="margin-left: 20px;">
                    <label class="text">
                        <input type="checkbox" name="radio_user" id="radio_user" value="1" />
                        Asignar este cumplimiento a todos los implicados en la actividad.
                    </label>
                </div>

            <?php if (!empty($id_proceso) && empty($id_tipo_reunion)) { ?>
                <div class="checkbox col-sm-12 col-md-12">
                    <label class="text">
                        <input type="radio" name="radio_prs" id="radio_prs2" value="2" checked="checked" />
                        Aplicar el estado de cumplimiento a la Unidad Organizativa <strong><?=$nombre_prs?></strong>
                        y a todas las unidades subordinadas.
                    </label>
                </div>
                <div class="checkbox col-sm-12 col-md-12">
                    <label class="text">
                        <input type="radio" name="radio_prs" id="radio_prs1" value="1" />
                        Aplicar el estado de cumplimiento solo la Unidad Organizativa <strong><?=$nombre_prs?></strong>.
                    </label>
                </div>
            <?php } ?>

            <?php if (!empty($id_proceso) && !empty($id_tipo_reunion)) { ?>
                <input type="hidden" name="radio_prs1" id="radio_prs1" value="0" />
                <input type="hidden" name="radio_prs2" id="radio_prs2" value="1" />
            <?php } ?>
            </div>
            <?php } ?>
        </div>


        <div class="tabcontent" id="tab2">
            <div class="form-group row">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <textarea id="observacion" name="observacion" class="form-control"></textarea>
                </div>
            </div>
         </div>


        <div class="tabcontent" id="tab3">
            <table id="table-plan"class="table table-striped"
                   data-toggle="table"
                   data-height="280"
                   data-search="true"
                   data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ESTADO</th>
                        <th>FECHA Y HORA</th>
                        <th>REGISTRADO POR</th>
                        <th>OBSERVACIÓN</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    !empty($id_usuario) ? $obj->SetIdUsuario($id_usuario) : $obj->SetIdUsuario(null);
                    $obj->SetCumplimiento(null);
                    $result = $signal == 'calendar' ? $obj->listEvento_reg($id_evento, true, null, true) : $obj->listEvento_reg_proceso($id_evento, true, null, true);
                    $cant = $obj->GetCantidad();
                    $i = 0;

                    if ($cant > 0) {
                        foreach ($result as $row) {
                    ?>
                            <tr>
                                <td>
                                    <?= ++$i ?>
                                </td>
                                <td>
                                    <?= $eventos_cump[$row['cumplimiento']] ?>
                                </td>
                                <td>
                                    <?= odbc2time_ampm($row['cronos']) ?>
                                </td>
                                <td>
                                    <?= ($row['id_responsable'] == _USER_SYSTEM) ? "SISTEMA DIRIGER" : $row['responsable'] ?>
                                </td>
                                <td>
                                    <?= textparse($row['observacion']) ?>
                                </td>
                            </tr>
                        <?php }
                    } ?>
                </tbody>
            </table>
        </div>

        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="submit"> Aceptar</button>
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
        </div>

    </form>

