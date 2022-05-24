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
require_once "../../php/class/auditoria.class.php";
require_once "../../php/class/riesgo.class.php";
require_once "../../php/class/proyecto.class.php";
require_once "../../php/class/nota.class.php";
require_once "../../php/class/proceso.class.php";

$_SESSION['debug']= null;

$action= !empty($_GET['action']) ? $_GET['action'] : 'repro';

$id_evento= $_GET['id_evento'];
$id_auditoria= $_GET['id_auditoria'];
$id_tarea= $_GET['id_tarea'];
$id_riesgo= $_GET['id_riesgo'];
$id_proyecto= $_GET['id_proyecto'];
$id_nota= $_GET['id_nota'];
$id_proceso= $_GET['id_proceso'];
$signal= $_GET['signal'];

$copy_all= $_GET['copy_all'];
$nums_id_show= $_GET['nums_id_show'];
$array_id_show= $_GET['array_id_show'];

$id_usuario= !empty($_GET['id_usuario']) ? $_GET['id_usuario'] : 0;
$id_asignado= !empty($_GET['id_asignado']) ? $_GET['id_asignado'] : $_SESSION['id_asignado'];

$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

$actual_year= date('Y');

if ($signal != 'anual_plan_audit') {
    $_signal= empty($nums_id) ? "actividad" : "Plan Anual de Actividades";
    $id= $id_evento;
    $obj= new Tevento($clink);
    $obj->SetIdEvento($id_evento);
}

if ($signal == 'anual_plan_audit') {
    $_signal= empty($nums_id) ? "auditoria o control" : "Plan de Auditorias o Acciones de Control";
    $id= $id_auditoria;
    $obj= new Tauditoria($clink);
    $obj->SetIdAuditoria($id_auditoria);
}

if ($signal == 'riesgo') {
    $_signal= empty($nums_id) ? "riesgo" : "Plan de Prevención";
    $id= $id_riesgo;
    $obj= new Triesgo($clink);
    $obj->SetIdRiesgo($id_riesgo);
}

if ($signal == 'nota') {
    $_signal= empty($nums_id) ? "nota" : "todas las notas";
    $id= $id_nota;
    $obj= new Tnota($clink);
    $obj->SetIdNota($id_nota);
}

if ($signal == 'proyecto') {
    $_signal= empty($nums_id) ? "proyecto" : "todos los proyectos";
    $id= $id_proyecto;
    $obj= new Tproyecto($clink);
    $obj->SetIdProyecto($id_proyecto);
}

$obj->Set();

$ifcopy= $obj->get_ifcopyto($year+1);
$ifcopy= is_null($ifcopy) ? false : true;

$if_chief= false;
if ($_SESSION['nivel'] >= _ADMINISTRADOR || $id_usuario == $id_asignado)
    $if_chief= true;

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();

$proceso= $obj_prs->GetNombre();
$id_proceso_code= $obj_prs->get_id_code();
$tipo= $Ttipo_proceso_array[$obj_prs->GetTipo()];

?>

    <link rel="stylesheet" href="../../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <script type="text/javascript" charset="utf-8">
        function validar() {
            var form= document.forms['fcopy'];

            function _this() {
                if (form.radio_prs.checked)
                    form._radio_prs.value= 2;

                return true;
            }

            if (parseInt(form.ifcopy.value)) {
                var text= "Este <?=$_signal ?> ya fue copiado para el plan correspondiente al año <?=($year+1)?>. ";
                text+= "Para realizar cualquier modificación edite el <?=$_signal ?> en el año <?=($year+1)?> y realizar ";
                text+= "los cambios, o eliminela del <?=($year+1)?> y vuelva a copiar o reprogramar. ";
                text+= "Si continua puede que se produzca un error. ¿Desea continuar?";
                confirm(text, function(ok) {
                    if (!ok)
                        return;
                    else {
                       if (!_this())
                           return;
                       else
                           ejecutar('<?=$action?>');
                    }
                });
            } else {
                if (!_this())
                    return;
                else
                    ejecutar('<?=$action?>');
            }
        }
    </script>


    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header">
                <div class="row">
                    <div class="panel-title ajax-title col-11 win-drag "></div>
                    <div class="col-1 close pull-right">
                        <div class="close">
                            <a href="#" onclick="CloseWindow('div-ajax-panel')">
                                <i class="fa fa-close"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id='div-ajax' class="card-body">
                <form id="fcopy" name="fcopy" class="form-horizontal" action="javascript:validar()"  method=post>
                    <input type="hidden" name=exect value="<?= $action ?>" />
                    <input type="hidden" name="signal" id="signal" value="<?= $signal ?>" />

                    <input type="hidden" name="id" value="<?= $id ?>" />
                    <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
                    <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />
                    <input type="hidden" name="tipo" value="<?= $obj_prs->GetTipo() ?>" />

                    <input type="hidden" id="day" name="day" value="<?= $day ?>" />
                    <input type="hidden" id="month" name="month" value="<?= $month ?>" />
                    <input type="hidden" id="year" name="year" value="<?= $year ?>" />

                    <input type="hidden" id="ifcopy" name="ifcopy" value="<?= $ifcopy ?>" />

                    <input type="hidden" id="_radio_prs" name="_radio_prs" value="0" />

                    <input type="hidden" id="copy_all" name="copy_all" value="<?= $copy_all ?>" />
                    <input type="hidden" id="nums_id_show" name="nums_id_show" value="<?= $nums_id_show ?>" />
                    <input type="hidden" id="array_id_show" name="array_id_show" value="<?= $array_id_show ?>" />

                    <input type="hidden" name="menu" value="fcopy" />

                    <?php if (!$copy_all) {?>
                    <div class="row col-sm-12">
                        <div class="alert alert-info">
                            <strong><?=ucwords($_signal)?>: </strong> <?=$obj->GetNombre()?>
                            <br/>
                            <strong>Fecha de Inicio:</strong><?=odbc2date($obj->GetFechaInicioPlan())?>
                            <strong>Fin:</strong><?=odbc2date($obj->GetFechaFinPlan())?>
                        </div>
                    </div>
                    <?php } ?>

                    <div class="form-group row">
                        <label class="col-form-label col-sm-5 col-md-5">
                            Copiar la(el) <?=$_signal?> para el año:
                        </label>
                        <div class="col-sm-3 col-md-3">
                            <select id="to_year" name="to_year" class="form-control">
                                <?php if ($year < $actual_year) { ?><option value="<?= $actual_year ?>"><?= $actual_year ?></option><?php } ?>
                                <option value="<?= ($actual_year + 1) ?>"><?= ($actual_year + 1) ?></option>
                            </select>
                        </div>
                    </div>

                    <!--
                    <?php if ($if_chief) { ?>
                    <div class="form-group row">
                        <div class="radio col-md-12">
                            <label>
                                <input type="radio" name="radio_prs" id="radio_prs1" value=2 />
                                Aplicar a todas las Unidades Organizativas involucradas, independientemente de la subordinación administrativa.
                            </label>
                        </div>
                    </div>
                    <?php } ?>
                    --->
                    <div class="form-group row">
                        <div class="radio col-12">
                            <label>
                                <input type="checkbox" name="radio_prs" id="radio_prs2" value="1" checked="checked" />
                                Aplicar a <strong><?= "$proceso ($tipo)" ?></strong> y en todas las Unidades Organizativas involucradas subordinadas.
                            </label>
                        </div>
                    </div>

                    <!--
                    <div class="form-group row">
                        <div class="radio col-md-12">
                            <label>
                                <input type="radio" name="radio_prs" id="radio_prs3" value=0 />
                                Aplicar solo a <?= $proceso ?>
                            </label>
                        </div>
                    </div>
                    -->
                    <hr/>
                     <div id="_submit" class="btn-block btn-app">
                        <?php if ($action != 'list') { ?> <button class="btn btn-primary" type="submit"> Aceptar</button><?php } ?>
                        <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
                    </div>

                    <div id="_submited" class="submited" align="center" style="display:none">
                        <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
                    </div>

                </form>
            </div>
        </div>
    </div>


