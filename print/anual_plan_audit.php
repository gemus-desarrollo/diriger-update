<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';
$_SESSION['trace_time']= 'no';

require_once "../php/config.inc.php";

require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/tipo_evento.class.php";

require_once "../php/class/base_evento.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/auditoria.class.php";
require_once "../php/class/orgtarea.class.php";
require_once "../php/class/plan_ci.class.php";

require_once "../php/class/tipo_auditoria.class.php";

require_once "../form/class/auditoria.signal.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'list' || $action == 'edit') {
    if (isset($_SESSION['obj']))  unset($_SESSION['obj']);
}

$year= $_GET['year'];
$id_proceso= $_GET['id_proceso'];

$origen= !empty($_GET['origen']) ? $_GET['origen'] : 0;
$tipo= !empty($_GET['tipo']) ? $_GET['tipo'] : 0;
$organismo= !empty($_GET['organismo']) ? urldecode($_GET['organismo']) : null;

$obj_signal= new Taudit_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);
$obj_signal->to_write= true;

$obj_user= new Tusuario($clink);

$print_reject= !is_null($_GET['print_reject']) ? $_GET['print_reject'] : _PRINT_REJECT_DEFEAT;
$acc_planrisk= !is_null($_SESSION['acc_planrisk']) ? $_SESSION['acc_planrisk'] : 0;
$tipo_plan= !is_null($_GET['tipo_plan']) ? $_GET['tipo_plan'] : _PLAN_TIPO_AUDITORIA;

$obj_signal->print_reject= $print_reject;

if ($tipo_plan == _PLAN_TIPO_SUPERVICION) {
    $title= "PLAN ANUAL DE ACCIONES DE CONTROL";
    $td1= "ORGANIZACIONES PARTICIPANTES";
    $td2= "TIPO DE ACCIÓN";
    $td3= "RESPONSABLE";
    $legend= "Supervisión";
}
if ($tipo_plan == _PLAN_TIPO_AUDITORIA) {
    $title= "PLAN ANUAL DE AUDITORIAS";
    $td1= "ORGANIZACIONES PARTICIPANTES";
    $td2= "TIPO DE AUDITORÍA";
    $td3= "JEFE DEL EQUIPO AUDITOR";
    $legend= "Auditoría";
}

$obj_plan= new Tplan_ci($clink);

$obj_plan->SetIdResponsable(NULL);
$obj_plan->SetIdUsuario(NULL);
$obj_plan->SetRole(NULL);

$obj_plan->SetDay(NULL);
$obj_plan->SetMonth(NULL);
$obj_plan->SetYear($year);

$obj_plan->SetTipoPlan($tipo_plan);
$obj_plan->SetIfAuditoria(true);
$obj_plan->SetIdProceso($id_proceso);

$objetivos= $obj_plan->GetObjetivo();
$date_aprb= $obj_plan->GetAprobado();
$array_aprb= $obj_user->GetEmail($obj_plan->GetIdResponsable_aprb());

$obj_plan->set_cronos(date('Y-m-d H:i:s'));

$obj_plan->SetIdProceso($id_proceso);
$obj_plan->SetTipo_prs($tipo);

$obj_plan->SetIdResponsable(NULL);
$obj_plan->SetIdUsuario(NULL);
$obj_plan->SetRole(NULL);

$obj_plan->SetYear($year);
$obj_plan->SetDay(NULL);
$obj_plan->SetMonth(NULL);
$obj_plan->SetTipoPlan($tipo_plan);

$obj_plan->SetOrigen($origen);
$obj_plan->SetTipo($tipo);
$obj_plan->SetOrganismo($organismo);

$obj_plan->automatic_audit_status();

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();

$obj= new Tauditoria($clink);
$obj_plan->copy_in_object($obj);

$obj_tipo= new Ttipo_auditoria($clink);
$obj_tipo->SetYear($year);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", $title, "Corresponde a $fecha_inicio_plan");
?>

<html>

<head>
    <title><?=$title?></title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <?php if (!empty($array_aprb)) { ?>
        Aprobado por: <?=$array_aprb['cargo']?><br />
        <span style="margin-left: 70px"><?=$array_aprb['nombre']?></span><br />
        <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$id_aprobado?>"
            border="0" />
        <?php } ?>

        <div align="center" class="title-header">
            <?=$title?> DE <?=$proceso?>. AÑO <?=$year?>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="page center">
        <table id="scheduler" width="100%" border=1 cellspacing=0 cellpadding=3>
            <thead>
                <tr>
                    <th rowspan="2" class="plhead left" style="width:20px;">No.</th>
                    <th rowspan="2" class="plhead" style="min-width:150px"><?= $td1 ?></th>
                    <th rowspan="2" class="plhead" style="min-width:150px"><?= $td2 ?></th>
                    <th rowspan="2" class="plhead" style="min-width:150px"><?= $td3 ?></th>
                    <th colspan="12" class="plhead">MESES</th>
                </tr>

                <tr>
                    <th class="plhead month">E</th>
                    <th class="plhead month">F</th>
                    <th class="plhead month">M</th>
                    <th class="plhead month">A</th>
                    <th class="plhead month">M</th>
                    <th class="plhead month">J</th>
                    <th class="plhead month">J</th>
                    <th class="plhead month">A</th>
                    <th class="plhead month">S</th>
                    <th class="plhead month">O</th>
                    <th class="plhead month">N</th>
                    <th class="plhead month">D</th>
                </tr>
            </thead>

            <tbody>
                <?php
                    if (!empty($id_proceso)) {
                        $j = 0;

                        for ($i = 1; $i < _MAX_TIPO_NOTA_ORIGEN; ++$i) {

                            if ($tipo_plan == _PLAN_TIPO_AUDITORIA && ($i != _NOTA_TIPO_AUDITORIA_EXTERNA && $i != _NOTA_TIPO_AUDITORIA_INTERNA))
                                continue;
                            if ($tipo_plan == _PLAN_TIPO_SUPERVICION && ($i == _NOTA_TIPO_AUDITORIA_EXTERNA || $i == _NOTA_TIPO_AUDITORIA_INTERNA))
                                continue;

                            $ktotal = 0;
                            $count = $obj->listyear($i);
                            if ($count == 0)
                                continue;
                            ?>
                <tr>
                    <td colspan="18" class="colspan">
                        <div align="left" style=" font-style:oblique; font-weight:600;">
                            <?=$Ttipo_nota_origen_array[$i]?>
                        </div>
                    </td>
                </tr>
                <?php
                reset($obj->array_procesos);

                foreach ($obj->array_procesos as $prs) {
                    $count = $obj->listyear($i, null, $prs['id']);
                    if ($count == 0)
                        continue;

                    $obj_signal->array_auditorias = $obj->array_auditorias;

                    foreach ($obj->array_auditorias as $evento) {
                        if (!is_null($evento['id_auditoria']))
                            continue;
                        if ($evento['periodic'] && empty($evento['hit']))
                            continue;

                        $memo = $evento['memo'];
                        ++$ktotal;
                        ?>

                <tr>
                    <td class="plinner left" style="background-color:#EAF4FF">
                        <?= ++$j ?>
                    </td>
                    <td class="plinner">
                        <?php
                        $fecha = odbc2time_ampm($evento['fecha']);
                        $email = $obj_user->GetEmail($evento['id_responsable']);
                        $responsable = $email['nombre'] . ' (' . $email['cargo'] . ')';

                        $email = $obj_user->GetEmail($evento['id_usuario']);
                        $usuario = $email['nombre'] . ' (' . $email['cargo'] . ') ' . odbc2time_ampm($evento['cronos']);

                        echo $prs['nombre'] . '<br/>' . $Ttipo_proceso_array[$prs['tipo']];
                        ?>
                    </td>

                    <td class="plinner">
                        <?php 
                        $obj_tipo->SetIdTipo_auditoria($evento['id_tipo_auditoria']);
                        $obj_tipo->Set();
                        echo $obj_tipo->GetNombre();
                        ?>
                    </td>

                    <td class="plinner">
                        <?php
                        $email= $obj_user->GetEmail($evento['id_responsable']);
                        if (!empty($evento['jefe_auditor']))
                            echo $evento['jefe_auditor'];
                        else    
                            echo textparse($email['nombre']).', '.textparse($email['cargo']);
                        ?>
                    </td>

                    <?php for ($k = 1; $k < 13; ++$k) { ?>
                    <td class="plinner" valign="middle" align="center">
                        <?php $obj_signal->do_list($evento, $k, _PRINT_IND);?>
                    </td>
                    <?php } ?>
                </tr>

                <?php
                    }
                }

                if ($ktotal == 0) {
                ?>
                <tr>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                    <td class="plinner">&nbsp;</td>
                </tr>
                <?php }
                        }
                    }
                ?>

            </tbody>
        </table>

    </div>

    <?php require "inc/print_bottom.inc.php";?>