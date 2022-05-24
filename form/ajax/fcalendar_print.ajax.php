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
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/proceso.class.php";

$id_evento= $_GET['id_evento'];
$signal= $_GET['signal'];
$print_reject= $_GET['print_reject'];

$id_proceso= $_GET['id_proceso'];

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();

$proceso= $obj_prs->GetNombre().' ('.$Ttipo_proceso_array[$obj_prs->GetTipo()].')';

$id_usuario= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : $_SESSION['id_usuario'];
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $_SESSION['id_usuario'];
$id_asignado= !empty($_GET['id_asignado']) ? $_GET['id_asignado'] : $_SESSION['id_asignado'];

if (!empty($_GET['error'])) $error= urldecode($_GET['error']);		
else $error= NULL;

$obj= new Tevento($clink);
$obj->SetIdEvento($id_evento);
$obj->Set();

$id_tarea= $obj->GetIdTarea();
$id_tarea_code= $obj->get_id_tarea_code();

$obj->SetIdResponsable($id_responsable);

$obj_user= new Tusuario($clink);

$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

$fecha= $day.'/'.$month.'/'.$year;
?>

<script type="text/javascript" charset="utf-8">
    function validar() {
        var i;
        for(i= 0; i < 3; i++) {
            if ($('#radio_usr'+i).is(':checked')) {
                if (parseInt($('#radio_usr'+i).val()) == parseInt($('#_init_print_reject').val())) 
                    CloseWindow('div-ajax-panel');
                else {
                    ver($('#radio_usr'+i).val());
                }
            }
        }

         parent.app_menu_functions= false;
         $('#_submit').hide();
         $('#_submited').show();		
    }

</script>

<div class="container-fluid">
    <form class="form-horizontal" id="fprint_reject" name="fprint_reject" action="javascript:validar()"  method=post>
        <input type="hidden" id="_init_print_reject" value="<?= $print_reject ?>" />
        
        <div class="radio col-md-12">
            <label>
                <input type="radio" name="radio_usr" id="radio_usr0" value="<?= _PRINT_REJECT_NO?>" <?php if ($print_reject == _PRINT_REJECT_NO)  echo "checked='checked'"; ?> />
                No mostrar las tareas ocultas.
            </label>
        </div>
        <div class="radio col-md-12">
            <label>
                <input type="radio" name="radio_usr" id="radio_usr1" value="<?= _PRINT_REJECT_OUT?>" <?php if ($print_reject == _PRINT_REJECT_OUT)  echo "checked='checked'"; ?> />&nbsp;
                Mostrar solo las tareas de las que el usuario es responsable  pero no participante. No se muestras las tareas rechazadas por el sistema o el  usuario.
            </label>
        </div>
        <div class="radio col-md-12">
            <label>
                <input type="radio" name="radio_usr" id="radio_usr2" value="<?= _PRINT_REJECT_DEFEAT?>" <?php if ($print_reject == _PRINT_REJECT_DEFEAT)  echo "checked='checked'"; ?> />&nbsp;
                Mostrar todas tareas ocultas. Incluye las rechazadas por el  sistema o el usuario.
            </label>
        </div>
        
        <div class="btn-block btn-app">
            <button type="submit" class="btn btn-primary">Aceptar</button>
            <button type="reset" class="btn btn-warning" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
        </div>
        
        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operación puede tardar unos minutos ........
        </div>        
    </form>    
</div>
   