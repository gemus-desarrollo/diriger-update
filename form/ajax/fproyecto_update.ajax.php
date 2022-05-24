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
require_once "../../php/class/time.class.php";
require_once "../../php/class/proyecto.class.php";


$action = empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') {
	if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
	$obj = unserialize($_SESSION['obj']);
	$obj->SetLink($clink);
	$action = $obj->action;
} else {
	$obj = new Tproyecto($clink);
}

$id_proyecto = !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : $obj->GetIdProyecto();

if (!empty($id_proyecto)) {
	$obj->SetIdProyecto($id_proyecto);
	$obj->Set();

	$id_responsable = $obj->GetIdResponsable();
	$id_proyecto = $obj->GetIdProyecto();
	$fecha_inicio_real = odbc2date($obj->GetFechaInicioReal());
	$fecha_fin_real = odbc2date($obj->GetFechaFinReal());
	$reg_fecha = odbc2date($obj->GetFecha());
}

if (empty($id_responsable)) 
	$id_responsable = $_GET['id_responsable'];
if (empty($id_responsable)) 
	$id_responsable = 0;

$redirect = $obj->redirect;
$error = !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time = new TTime();
$time->SetFormat('d/m/Y');
$fecha = $time->GetStrDate();

?>


<link rel="stylesheet" type="text/css" href="../../css/scripts.css?version=">
<script language="JavaScript" type="text/javascript"
    src="../js/datetimepicker_css.js?version="></script>

<link rel="stylesheet" type="text/css" media="screen" href="../../css/main.css?version=" />
<link rel="stylesheet" type="text/css" media="screen" href="../../css/form.css?version=" />
<script language="javascript" type="text/javascript" charset="utf-8"
    src="../../js/string.js?version="></script>


<script language='javascript'>
function validar() {
    var form = document.forms['fproyecto'];

    form.fecha_final.value = form._fecha_final.value;


    if (!Entrada(form.descripcion.value)) {
        alert('No ha realizado ninguna descripción relativa al estado del proyecto');
        return;
    }

    if (form.ended.checked && !Entrada(form.fecha_final.value)) {
        alert('Debe especificar la fecha en la terminó la ejecucinó del proyecto.');
        return;
    }

    if (Entrada(form.fecha_final.value)) {
        if (!isDate_d_m_yyyyy(form.fecha_final.value)) {
            alert('Fecha de finalización con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
            return;
        }
    }

    ejecutar();
}

function onchange_fecha_final() {
    var form = document.forms['fproyecto'];

    if (Entrada(form._fecha_final.value)) {
        form.ended.checked = true;
        document.getElementById('imgcal').style.visibility = 'hidden';

        form.fecha_final.value = form._fecha_final.value;
    }
}

function onclick_ended() {
    var form = document.forms['fproyecto'];
    ok = confirm(
        "Al cerrar el proyecto serán dadas por terminadas todas las tareas del proyecto independientemente del estado en el que se encuentren. Desea continuar?"
    );

    if (!ok) {
        form.ended.checked = false;
        return false;
    }

    if (form.ended.checked && ok) {
        alert("Debe especificar la fecha en la que se terminó la tarea.");
        NewCssCal('_fecha_final', 'ddMMyyyy');
    }
}
</script>

<form id="fproyecto" name="fproyecto" action='javascript:validar()' method=post style="border:none">
    <input type=hidden name=exect value=<?= $action ?> />
    <input type=hidden name=id value=<?php echo $id_proyecto ?> />
    <input type=hidden name=proyecto value=<?php echo $id_proyecto ?> />
    <input type=hidden name=menu value=regproyecto />

    <table cellspacing="4">
        <tr>
            <td><label for="label">Responsable:</label></td>
            <td>
                <?php
				$obj_user = new Tusuario($clink);
				$obj_user->SetIdUsuario($id_responsable);
				$obj_user->Set();
				echo $obj_user->GetNombre();
				unset($obj_user);
				?>

            </td>
        </tr>
        <tr>
            <td width="75"><label for="nombre">Proyecto:</label></td>

            <td><?php echo $obj->GetNombre(); ?></td>
        </tr>
        <tr>
            <td><label for="label">Observaciones:</label></td>
            <td>
                <textarea name="descripcion" rows="4" id="descripcion" class="texta" style="width:400px;"
                    <?php echo $disabled ?> /><?php echo $obj->GetObservacion(); ?></textarea>
            </td>
        </tr>
        <tr>
            <td width="75"><label for="nombre">Terminar o Cancelar:</label></td>
            <input type="hidden" name="fecha_final" value="<?php echo $fecha_fin_real; ?>" />


            <td>

                <input type="checkbox" name="ended" id="ended" value="1" <?php echo $ended ?> onClick="onclick_ended()"
                    <?php echo $disabled ?> />
                <input id="_fecha_final" name="_fecha_final" class="texta" style="width:100px;"
                    value="<?php echo $ended; ?>" onChange="onchange_fecha_final()" <?php echo $disabled ?> />
                &nbsp;<?php if (empty($disabled)) { ?><img src="../img/cal.gif" id="imgcal"
                    onClick="javascript:NewCssCal('_fecha_fin','ddMMyyyy')" style="cursor:pointer"
                    alt="Click aqui para seleccionar la fecha" /><?php } ?>
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td align="center">
                <div class="submit" align="center" style="text-align:center">
                    <?php if ($action == 'update' || $action == 'add') { ?>
                    <input value="Aceptar" type="submit">&nbsp;
                    <input value="Eliminar Proyecto" type="button" onClick="eliminar()">&nbsp;
                    <?php } ?>
                    <input value="Cancelar" type="reset" onclick="closeFloatingDiv('div-ajax-panel')">
                </div>
            </td>
        </tr>
    </table>

</form>


<?php if (!is_null($error)) { ?>
<script language='javascript' type="text/javascript" charset="utf-8">
alert("<?php echo $error ?>")
</script>
<?php } ?>