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
require_once "../../php/class/proceso_item.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/tipo_evento.class.php";

require_once "../../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$id_evento= $_GET['id'];
$signal= $_GET['signal'];
$tipo_plan= !empty($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL;
$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$_id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : 0;
$if_synchronize= !is_null($_GET['if_synchronize']) ? $_GET['if_synchronize'] : 0;

$time= new TTime();
$actual_year= $time->GetYear();
$actual_month= (int)$time->GetMonth();
$actual_day= $time->GetDay();

$time->SetYear($year);
$time->SetMonth($month);
$time->SetDay($day);
$lastday= $time->longmonth();

if ($signal == 'calendar' && empty($id_usuario))
    $id_usuario= $_SESSION['id_usuario'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

$obj= new Tevento($clink);
$obj->SetIdEvento($id_evento);
$obj->SetIdUsuario($id_usuario);
$obj->Set();

$nombre= $obj->GetNombre();
$fecha= $obj->GetFechaInicioPlan();
$fecha_inicio= date('d/m/Y', strtotime($fecha));
$hora_inicio= date('h:i A', strtotime($fecha));
$lugar= $obj->GetLugar();
$id_responsable= $obj->GetIdResponsable();
$id_tipo_reunion= $obj->GetIdTipo_reunion();
$id_secretary= $obj->GetIdSecretary();
$numero= $obj->GetNumero();
$hora_fin= date('h:i A', strtotime($fecha));

$year= date('Y', strtotime($fecha));
$month= date('m', strtotime($fecha));
$day= date('d', strtotime($fecha));

$obj->SetIdResponsable($_SESSION['id_usuario']);
$obj->SetIdEvento(null);

$obj_user= new Tusuario($clink);

$empresarial= 0;
$id_tipo_evento= null;
$id_subcapitulo0= 0;
$id_subcapitulo1= 1;

if (!empty($id_proceso) && $tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) {
    $obj_prs= new Tproceso_item($clink);
    $obj_prs->Set($id_proceso);
    $id_proceso_code= $obj_prs->get_id_code();
    $nombre_prs= $obj_prs->GetNombre();

    $obj_prs->SetYear($year);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->SetIdEvento($id_evento);
    $obj_prs->SetIdAuditoria($id_auditoria);

    $row= $obj_prs->get_reg_proceso();
    $id_responsable= $row['id_responsable'];
    $empresarial= $row['empresarial'];
    $id_tipo_evento= $row['id_tipo_evento'];
}

if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL)
    $id_responsable= $_id_responsable;

if (!empty($id_responsable)) {
    $obj_user->Set($id_responsable);
    $nombre_resp= $obj_user->GetNombre();
    $cargo_prs= $obj_user->GetCargo();
    $nombre_resp.= !empty($cargo_prs) ? ", $cargo_prs" : null;
}

if (!empty($id_tipo_evento)) {
    $obj_tipo= new Ttipo_evento($clink);
    $obj_tipo->SetIdTipo_evento($id_tipo_evento);
    $obj_tipo->Set();

    $id_subcapitulo= $obj_tipo->GetIdSubcapitulo();

    if (!empty($id_subcapitulo)) {
        $id_subcapitulo0= $id_subcapitulo;
        $id_subcapitulo1= $id_tipo_evento;
    } else {
        $id_subcapitulo0= $id_tipo_evento;
        $id_subcapitulo1= 0;
    }
}

if ($badger->freeassign)
    $obj_user->set_use_copy_tusuarios(false);
else
    $obj_user->set_use_copy_tusuarios(true);

$obj_user->SetIdProceso(null);
$obj_user->set_user_date_ref($fecha_inicio);

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);
$badger->set_planwork();
?>

<script type="text/javascript" charset="utf-8" src="../js/tipo_evento.js?version="></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            var text;
            var _radio_user;
            var form= document.forms['fdelegateevento'];

            if ($('#usuario').val() == 0 || $('#usuario').val() == 'undefined') {
                text= "Debe especificar a quien será delegada o asignada la actividad o tarea.";
                alert(text);
                return;
            }

            $('#_radio_user').val(0);
            $('#_radio_prs').val(0);

            <?php if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
            if (!$('#radio_user1').is(':checked') && !$('#radio_user2').is(':checked')) {
                text= "Debe de especificar si para la actividada será delegada toda la responsabilidad ";
                text+= "o solo será asignada a otro usuario";
                alert(text);
                return;
            }

            _radio_user= $('#radio_user1').is(':checked') ? 1 : 0;
            $('#_radio_user').val(_radio_user);

            if ($('#_id_responsable').val() && parseInt($('#_id_responsable').val()) == parseInt($('#usuario').val())) {
                text= "Debe especificar a otro usuario como responsable. No hay nada que hacer.";
                alert(text);
            }
            <?php } ?>

            <?php if ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
            if ($('#id_proceso').val()) {
                if ($('#radio_prs1').is(':checked'))
                    $('#_radio_prs').val(0);
                if ($('#radio_prs2').is(':checked'))
                    $('#_radio_prs').val(1);
            }
            <?php } ?>

            ejecutar('delegate');
        }
    </script>

    <script type="text/javascript">
        $(document).ready(function() {
            <?php
            $restrict_prs= array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
            ?>
            <?php if (!empty($id_proceso) && $tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
            $.ajax({
                data:  {
                        "id_auditoria" : <?=$id_auditoria ? $id_auditoria : 0?>,
                        "id_evento" : <?=$id_evento?>,
                        "tipo_plan" : <?=_PLAN_TIPO_ACTIVIDADES_INDIVIDUAL?>,
                        "year" : <?=!empty($year) ? $year : date('Y')?>,
                        "user_ref_date" : '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',
                        "id_user_restrict" : <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>,
                        "restrict_prs" : <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                        "use_copy_tusuarios" : <?=$use_copy_tusuarios ? $use_copy_tusuarios : 0?>,
                        "array_usuarios" : <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>,
                        "array_grupos" : <?= !empty($array_grupos) ? '"'. urlencode(serialize($array_grupos)).'"' : 0?>
                    },
                url:   '../form/ajax/usuario_tabs.ajax.php',
                type:  'post',
                beforeSend: function () {
                    $("#ajax-tab-users").html("Procesando, espere por favor...");
                },
                success:  function (response) {
                    $("#ajax-tab-users").html(response);
                }
            });
            <?php } ?>

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

            <?php if (!empty($id_proceso) && $tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
            $('#tipo_actividad_flag').val("-ajax");
            refresh_ajax_select(<?=$id_subcapitulo0?>, <?=$numero?>, <?=$id_subcapitulo1?>);
            <?php } ?>

            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });
    </script>

    <ul class="nav nav-tabs" style="margin-bottom: 10px;">
		<li id="nav-tab5" class="nav-item"><a class="nav-link" href="tab5">Responsable</a></li>
        <?php if (!empty($id_proceso) && $tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
		<li id="nav-tab6" class="nav-item"><a class="nav-link" href="tab6">Participantes</a></li>
        <li id="nav-tab7" class="nav-item"><a class="nav-link" href="tab7">Clasificación</a></li>
        <?php } ?>
	 </ul>

    <form id="fdelegate" name="fdelegate" class="form-horizontal" action="javascript:validar()"  method="post">
        <input type="hidden" name="exect" value="set" />
        <input type="hidden" id="tipo_plan" name="tipo_plan" value=<?= $tipo_plan ?> />

        <input type="hidden" id="year" name="year" value=<?=$year?> />
        <input type="hidden" id="month" name="month" value=<?=$month?> />
        <input type="hidden" id="day" name="day" value=<?=$day?> />

        <input type="hidden" id="id_proceso" name="id_proceso" value=<?= $id_proceso ?> />
        <input type="hidden" name="id_calendar" id="id_calendar" value="<?= $id_usuario ?>" />
        <input type="hidden" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
        <input type="hidden" name="_radio_user" id="_radio_user" value="" />
        <input type="hidden" name="_radio_prs" id="_radio_prs" value="" />

        <input type="hidden" id="_id_responsable" name="_id_responsable" value="<?= $id_responsable ?>" />
        <input type="hidden" name="id" value="<?= $id_evento ?>" />

        <input type="hidden" name="menu" value="fdelegate" />


        <div class="tabcontent" id="tab5">
            <div class="alert alert-info" style="margin-top: 8px">
                <strong>Actividad: </strong><?=$nombre?><br />
                <div class="row">
                    <div class="col-6">
                        <strong>Inicio: </strong><?=odbc2date($obj->GetFechaInicioPlan())?>
                    </div>
                    <div class="col-6 pull-left">
                        <strong>Fin: </strong><?=odbc2date($obj->GetFechaFinPlan())?>
                    </div>
                    <div class="col-6 pull-left">
                        <strong>Responsable:</strong> <?=$nombre_resp?>
                    </div>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-2">
                    Aplicar a:
                </label>
                <div class="col-6">
                    <select id="extend" name="extend" class="form-control">
                        <?php if ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_ANUAL) { ?>
                            <option value="A">Solo a esta actividad</option>
                            <option value="U">A esta misma Actividad siempre que aparezca en el mes ...</option>
                        <?php } ?>
                        <?php if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_MENSUAL || $tipo_plan == _PLAN_TIPO_ACTIVIDADES_ANUAL
                                || $tipo_plan == _PLAN_TIPO_AUDITORIA || $tipo_plan == _PLAN_TIPO_MEETING) { ?>
                            <option value="Y">A esta misma Actividad, planificada en todo el año en curso ...  </option>
                        <?php } ?>
                        <?php if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
                            <option value="D">A todas las actividades de este DíA ...</option>
                            <option value="S">A todas las actividades de la SEMANA ...</option>
                            <option value="M">A todas las actividades del MES ...</option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label class="col-form-label col-xs-3 col-sm-3 col-md-2 col-lg-2">
                     Responsable (Subordinado):
                </label>
                <div class="col-sm-9 col-md-8 col-lg-8">
                    <?php
                    $array_ids= array();
                    $array_ids[$_SESSION['id_usuario']]= $_SESSION['id_usuario'];
                    ?>
                    <select name="usuario" id="usuario" class="form-control">
                        <option value="0">selecciona...</option>
                        <option value="<?=$_SESSION['id_usuario']?>" <?php if ($_SESSION['id_usuario'] == $id_responsable) echo "selected" ?>>
                            <?="{$_SESSION['nombre']}, {$_SESSION['cargo']}"?>
                        </option>
                        <?php
                        
                        foreach ($badger->obj_sub->array_usuarios as $user) {
                            if ($array_ids[$user['id']])
                                continue;
                            $array_ids[$user['id']]= $user['id'];

                            if (empty($user['nombre']))
                                continue;
                            $name= textparse($user['nombre']);
                            if (!empty($user['cargo']))
                                $name.= ", ". textparse($user['cargo']);
                            ?>
                            <option value="<?= $user['id'] ?>" <?php if ($user['id'] == $id_responsable) echo "selected" ?> >
                                <?= $name?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>


            <div class="form-group row">
                <div class="col-xs-12 col-sm-12 col-md-12" style="margin-left: 8px;">
                    <label class="checkbox text" style="margin-left: 20px;">
                        <input type="checkbox" id="sendmail" name="sendmail" value="1" />
                        Enviar aviso por correo electronico.
                    </label>

                   <?php if ($tipo_plan == _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
                    <label class="checkbox text col-sm-12 col-md-12" style="margin-left: 20px">
                        <input type="checkbox" id="radio_user1" name="radio_user" value="1" <?=$if_synchronize ? "checked='checked'" : null?> />
                        Se delega toda la responsabilidad. Se elimina del Plan de Trabajo Individual del actual responsable.
                    </label>
                    <label class="checkbox text col-sm-12 col-md-12" style="margin-left: 20px">
                        <input type="checkbox" id="radio_user2" name="radio_user" value="0" />
                        Se le asigna la tarea a un subordinado. Se mantine la responsabilidad del cumplimiento y chequeo.
                        Se oculta en el Plan de Trabajo Individual del actual responsable.
                    </label>
                   <?php } ?>

                   <?php if ($tipo_plan != _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL) { ?>
                    <label class="checkbox text col-sm-12 col-md-12">
                        <input type="radio" name="radio_prs" id="radio_prs2" value="1" checked="checked" />
                        Cambiar el responsable en la Unidad Organizativa <strong><?=$nombre_prs?></strong> y a todas las unidades subordinadas.
                    </label>
                    <label class="checkbox text col-sm-12 col-md-12">
                        <input type="radio" name="radio_prs" id="radio_prs1" value="0" />
                        Cambiar el responsable solo en la Unidad Organizativa <strong><?=$nombre_prs?></strong>.
                    </label>
                   <?php } ?>
                </div>
            </div>

        </div>

        <div class="tabcontent" id="tab6">
            <div id="ajax-tab-users">

            </div>
        </div>

        <div class="tabcontent" id="tab7">
            <input type="hidden" name="numero" id="numero" class="form-control" value="<?=$numero?>" >

            <div class="form-group row" style="margin-top: 10px">
                <label class="col-form-label col-2">
                    Capítulo:
                </label>
                <div class="col-10">
                    <select id="tipo_actividad-ajax1" name="tipo_actividad1" class="form-control" onchange="refresh_ajax_select(<?=$id_subcapitulo0?>, 0, 0)">
                        <option value=0>...</option>
                        <?php for ($i = 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) { ?>
                            <option value="<?= $i ?>" <?php if ($i == $empresarial) echo "selected='selected'" ?>><?= number_format_to_roman($i - 1) . '. ' . $tipo_actividad_array[$i] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-group row" id="tr-tipo_actividad2">
                <label class="col-form-label col-2">Sub Capítulo:</label>
                <div class="col-10 ajax-select" id="ajax-tipo-evento-ajax">
                    <select id="tipo_actividad-ajax2" class="form-control input-sm">

                    </select>
                </div>
            </div>
            <div class="form-group row" id="tr-tipo_actividad3">
                <label class="col-form-label col-2">Epígrafe:</label>
                <div class="col-10 ajax-select" id="ajax-subtipo-evento-ajax">
                    <select id="tipo_actividad-ajax3" class="form-control input-sm">

                    </select>
                </div>
            </div>
        </div>


        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="submit"> Aceptar</button>
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
        </div>
    </form>
