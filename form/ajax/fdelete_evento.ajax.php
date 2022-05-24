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
require_once "../../php/class/usuario.class.php";

require_once "../../php/class/tipo_reunion.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/auditoria.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/tipo_auditoria.class.php";

$_SESSION['debug']= null;

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : null;
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;

$signal= $_GET['signal'];
$tipo_plan= !is_null($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL;
$_id_tipo_reunion= !empty($_GET['id_tipo_reunion']) ? $_GET['id_tipo_reunion'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$_id_proceso= !empty($_GET['_id_proceso']) ? $_GET['_id_proceso'] : null;

$id_proceso= !empty($_id_proceso) ? $_id_proceso : $id_proceso;
$id_proceso_code= null;

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $id_proceso_code= $obj_prs->get_id_code();

    $proceso= $obj_prs->GetNombre().' ('.$Ttipo_proceso_array[$obj_prs->GetTipo()].')';
}

$id_usuario= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : $_SESSION['id_usuario'];
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $_SESSION['id_usuario'];
$id_asignado= !empty($_GET['id_asignado']) ? $_GET['id_asignado'] : $_SESSION['id_asignado'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;
$id= null;
$nombre= null;

if (!empty($id_evento)) {
    $id= $id_evento;
    $obj= new Tevento($clink);
    $obj->SetIdEvento($id_evento);
}
if (!empty($id_auditoria)) {
    $id= $id_auditoria;
    $obj= new Tauditoria($clink);
    $obj->SetIdAuditoria($id_auditoria);
}
$obj->Set();

$if_unique_event= false;
$periodicidad= $obj->GetPeriodicidad();

$id_evento_ref= !empty($id_evento) ? $obj->get_id_evento_ref() : null;
$id_auditoria_ref= !empty($id_auditoria) ? $obj->get_id_auditoria_ref() : null;

$if_unique_event= $periodicidad == 0 && (empty($id_evento_ref) && empty($id_auditoria_ref)) ? true : false;

$id_tipo_reunion= $obj->GetIdTipo_reunion();
$id_tipo_auditoria= $obj->GetTipo_auditoria();

if (!empty($id_evento)) {
    $nombre= $obj->GetNombre();
}
if (!empty($id_auditoria)) {
    $obj_tipo= new Ttipo_auditoria($clink);
    $obj_tipo->Set($id_tipo_auditoria);
    $tipo= $obj->GetNombre();
    $nombre= $Ttipo_nota_origen_array[$obj->GetOrigen()]." / $tipo";
}

$id_tarea= $obj->GetIdTarea();
$id_tarea_code= $obj->get_id_tarea_code();

$obj->SetIdResponsable($id_responsable);

$obj_user= new Tusuario($clink);

$year= date('Y', strtotime($obj->GetFechaInicioPlan()));
$month= date('m', strtotime($obj->GetFechaInicioPlan()));
$day= date('d', strtotime($obj->GetFechaInicioPlan()));

$fecha= "$day/$month/$year";

$if_chief= false;
if ($_SESSION['nivel'] >= _ADMINISTRADOR || $id_usuario == $id_asignado)
    $if_chief= true;

?>

    <script language='javascript' type="text/javascript" charset="utf-8">

    function validar() {
        var text;
        var form= document.forms['fdelete'];
        var signal= form.signal.value;
        var tipo_plan= $('#tipo_plan').val();

        <?php if ($signal != 'anual_plan') { ?>
        if ($('#if_unique_event').val() == 1)
            $('#radio_date2').prop('checked', true);
        <?php } ?>

        <?php if ($signal == 'mensual_plan') { ?>
        $('#_radio_toshow').val($('#radio_toshow').is(':checked') ? 1 : 0);
        <?php } ?>
            
        <?php if ($signal != 'anual_plan_audit' && $signal != 'anual_plan') { ?>
            if (($('#radio_date1').is(':checked') && $('#radio_user1').is(':checked') && signal != 'anual_plan')
                    || ($('#radio_user1').is(':checked') && signal == 'anual_plan')) {

                text= "Ha selecionado eliminar la actividad en la fecha <?= $fecha?> a todos los participantes. ";
                text+= "Desea continuar?";
                confirm(text, function(ok) {
                    if (!ok)
                        return;
                    else {
                        if (!_this_1())
                            return;
                    }
                });
            } else {
                if (!_this_1())
                    return;
            }
        <?php } ?>

        <?php if ($signal == 'anual_plan_audit' || $signal == 'anual_plan') { ?>
            _this_2();
            if (!_this_4())
                return;
        <?php } ?>

        function _this_1() {
            if (($('#radio_date2').is(':checked') && $('#radio_user1').is(':checked') && signal != 'anual_plan')
                                            || ($('#radio_user1').is(':checked') && signal == 'anual_plan')) {

                text= "Ha selecionado eliminar la actividad en la fecha <?= $fecha?> y todas las posteriores ";
                text+= "y a todos los participantes a partir. Desea continuar?";
                confirm(text, function(ok) {
                    if (!ok)
                        return;
                    else {
                        _this_2();
                        if (!_this_3())
                            return false;
                    }
                });
            } else {
                _this_2();
                if (!_this_3())
                    return false;
            }
        }

        function _this_2() {
            <?php if ($signal != 'anual_plan') { ?>
                $('#_radio_date').val(0);
                $('#extend').val('A');

                if ($('#radio_date1').is(':checked')) {
                    $('#_radio_date').val(0);
                    $('#extend').val('A');
                }
                if ($('#radio_date2').is(':checked')) {
                    $('#_radio_date').val(1);
                    <?php if ($signal != 'mensual_plan' && $signal != 'calendar') { ?>
                        $('#extend').val('N');
                    <?php } else { ?> 
                        $('#extend').val('U');
                    <?php } ?>    
                }
                if ($('#radio_date3').is(':checked')) {
                    $('#_radio_date').val(2);
                    $('#extend').val('Y');
                }
                if ($('#radio_user1').is(':checked'))
                    $('#_radio_user').val(1);
                else
                    $('#_radio_user').val(0);
                
            <?php } else { ?>
                $('#_radio_date').val(2);
                $('#extend').val('Y');
            <?php } ?>               
        }

        function _this_3() {
            <?php if ($signal == 'calendar') { ?>
                if ((form._id_usuario.value == form._id_responsable.value) && form._radio_user.value == 0) {
                    text= "Usted es el responsable de la tarea. Si la elimina, esta será eliminada de todos los ";
                    text+= "Planes generales e individuales de la organización. desea continuar?";
                    confirm(text, function(ok) {
                        if (!ok)
                            return false;
                        else {
                            $('#_radio_user').val(1);
                            _this_4();
                            return true;
                        }
                    });
                } else {
                    _this_4();
                    return true
                }
            <?php } else { ?>
                _this_4();
                return true;
            <?php } ?>
        }

        function _this_4() {
            $('#_radio_prs').val(0);

            <?php if ($signal != 'calendar') { ?>
                if ($('#radio_prs2').is(":checked"))
                    $('#_radio_prs').val(2);
                if ($('#radio_prs1').is(":checked"))
                    $('#_radio_prs').val(1);
            <?php } ?>

            <?php if ($if_chief) { ?>
                if ($('#_go_delete').is(":checked"))
                    $('#go_delete').val($('#_go_delete').val());
            <?php } ?>

            <?php if ($signal == 'anual_plan_audit' || $signal == 'anual_plan' || $signal == 'mensual_plan') { ?>
                if ($('#radio_user1').is(":checked"))
                    $('#_radio_user').val(1);
                else
                    $('#_radio_user').val(0);
            <?php } ?>

            ejecutar('delete');
        }
    }
    </script>


    <form class="form-horizontal" id="fdelete" name="fdelete" action="javascript:validar()" method=post>
        <input type="hidden" name="exect" value="set" />
        <input type="hidden" name="id_usuario" value="<?= $_SESSION['id_usuario'] ?>" />
        <input type="hidden" name="id_responsable" value="<?= $_SESSION['id_usuario'] ?>" />
        <input type="hidden" name="id" value="<?= $id ?>" />
        <input type="hidden" name="id_tarea" value="<?= $id_tarea ?>" />
        <input type="hidden" name="id_tipo_reunion" value="<?= $_id_tipo_reunion ?>" />

        <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
        <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

        <input type="hidden" name="menu" value="fdelete" />

        <input type="hidden" name="signal" id="signal" value="<?= $signal ?>" />

        <input type="hidden" id="if_unique_event" name="if_unique_event" value="<?=$if_unique_event ? 1 : 0?>" />
        
        <input type="hidden" id="_radio_date" name="_radio_date" value="0" />
        <input type="hidden" id="_radio_user" name="_radio_user" value="0" />
        <input type="hidden" id="_radio_prs" name="_radio_prs" value="0" />
        <input type="hidden" id="_radio_toshow" name="_radio_toshow" value="0" />
        
        <input type="hidden" name="year" id="year" value="<?= $year ?>" />
        <input type="hidden" name="month" id="month" value="<?= $month ?>" />
        <input type="hidden" name="day" id="day" value="<?= $day ?>" />

        <input type="hidden" name="if_chief" id="if_chief" value="<?= $if_chief ?>" />

        <input type="hidden" id="_id_usuario" name="_id_usuario" value="<?= $id_calendar ?>" />
        <input type="hidden" id="_id_responsable" name=_id_responsable" value="<?= $id_responsable ?>" />

        <input type="hidden" id="tipo_plan" name="tipo_plan" value="<?= $tipo_plan ?>" />
        <input type="hidden" id="extend" name="extend" value="A" />

        <div class="alert alert-info" style="margin: 0px!important; padding: 3px;">
            <?php
            if ($id_tipo_reunion > 0) {
                $obj_meeting= new Ttipo_reunion($clink);
                $obj_meeting->SetIdTipo_reunion($id_tipo_reunion);
                $meeting= $obj_meeting->Set();
            ?>
                <p><strong>Tipo de Reunión: </strong><?=$meeting?></p>
            <?php } ?>
            <p><strong>Actividad: </strong><?= $nombre ?></p>
            <p><strong>Fecha de Inicio: </strong><?= odbc2time_ampm($obj->GetFechaInicioPlan()) ?></p>
            <p><strong>Fecha de Fin: </strong><?= odbc2time_ampm($obj->GetFechaFinPlan()) ?></p>
        </div>

        <?php if ($signal != 'calendar') { ?>
            <legend style="margin-top: 5px;">Subordinación:</legend>
            <div class="checkbox">
                <label>
                    <input type="radio" name="radio_prs" id="radio_prs2" value="2" checked="checked" />
                    Aplicar a <strong><?= $proceso ?></strong> y Unidades Organizativas subordinadas.
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="radio" name="radio_prs" id="radio_prs1" value="1" />
                    Aplicar solo a <strong><?= $proceso ?></strong>.
                </label>
            </div>
        <?php } ?>


        <legend style="margin-top: 5px;">Calendario:</legend>

        <?php if ($signal != 'anual_plan' ) { ?>
            <div class="checkbox">
                <label>
                    <input type="radio" name="radio_date" id="radio_date1" value="0" checked="checked" />
                    Borrar solo en la fecha <?= $fecha ?>
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="radio" name="radio_date" id="radio_date2" value="1" />
                    Borrar en la fecha <?= $fecha ?> y todas las posteriores.
                </label>
            </div>
            <div class="checkbox">
                <label>
                    <input type="radio" name="radio_date" id="radio_date3" value="2" />
                    Borrar en todo el año
                </label>
            </div>
        <?php } else { ?>
            <div class="checkbox">
                <label>
                    <input type="radio" name="radio_date" id="radio_date3" value="2" checked="checked" />
                    Se eliminará todo el año
                </label>
            </div>
        <?php } ?>

        <legend style="margin-top: 5px;">Usuarios:</legend>
        <?php if ($signal == 'calendar') { ?>
            <div class="checkbox">
                <label>
                    <input type="radio" name="radio_user" id="radio_user0" value="0" checked="checked" />
                    Borrar solo en este Plan de Trabajo Individual
                </label>
            </div>
            <?php if ($id_responsable == $id_usuario || $id_asignado == $id_usuario || $_SESSION['nivel'] >= _ADMINISTRADOR) { ?>
                <div class="checkbox">
                    <label>
                        <input type="radio" name="radio_user" id="radio_user1" value="1" />
                        Borrar para todos los participantes de la tarea. El evento ser&aacute; borrado de todos lo
                        Planes de Trabajo Individuales
                    </label>
                </div>
        <?php } } ?>

        <?php if ($signal == 'mensual_plan') { ?>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="radio_toshow" id="radio_toshow" value="1" />
                    Se eliminara la actividad tamnbien en los Planes Anuales
                </label>
            </div>
        <?php } ?>

        <?php if ($signal != 'calendar') { ?>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="radio_user" id="radio_user1" value="1" checked="checked" />
                    Borrarla de los Planes de Trabajo Individuales. Para todos los implicados en la actividad.
                </label>
            </div>
        <?php } ?>

        <?php if ($signal == 'anual_plan') { ?>
            <input type="hidden" name="radio_date" id="radio_date3" value="1" />
        <?php } ?>

        <?php if ($if_chief) { ?>
            <div class="col-12">
                <div class="checkbox text-danger">
                    <label>
                        <input type="checkbox" id="_go_delete" value=<?= _DELETE_PHY ?> name="_go_delete" />
                        Desea realizar el borrado físico. No quede traza en el sistema de
                        las fechas eliminadas, reuniones, o acciones de control.
                    </label>
                </div>
            </div>

        <?php } ?>

        <input type="hidden" name="go_delete" id="go_delete" value="<?= _DELETE_YES ?>" />


        <div id="_submit" class="btn-block btn-app">
            <button type="submit" class="btn btn-primary">Aceptar</button>
            <button type="reset" class="btn btn-warning" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operación puede tardar unos minutos ........
        </div>


    </form>
