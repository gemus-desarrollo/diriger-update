<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/tmp_tables_planning.class.php";
require_once "../php/class/register_planning.class.php";

require_once "../php/class/auditoria.class.php";
require_once "../php/class/tipo_evento.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/regtarea.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();
$time->splitTime();
$year= $time->GetYear();
$month= (int)$time->GetMonth();
$lastday= $time->longmonth();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$obj= new Tauditoria($clink);

$obj->SetIdEscenario(null);
$obj->SetIdResponsable(null);
(!empty($id_proceso) && $id_proceso != -1) ? $obj->SetIdProceso($id_proceso) : $obj->SetIdProceso(null);
$obj->SetIdUsuario(null);

$result= $obj->listar();

$obj_user= new Tusuario($clink);
$obj_tipo_evento= new Ttipo_evento($clink);
$obj_reg= new Tregister_planning($clink);
$obj_prs= new Tproceso($clink);

if ($id_proceso != -1) {
    $obj_prs->SetIdProceso($id_proceso ? $id_proceso : $_SESSION['id_enity']);
    $obj_prs->Set();
    $tipo_prs= $obj_prs->GetTipo();
    $proceso= $obj_prs->GetNombre();
} else {
    $proceso= "Todos los procesos";
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE ACCIONES DE CONTROL", "Corresponde a periodo año: $year");
?>

<html>
    <head>
        <title>LISTADO DE ACCIONES DE CONTROL</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div class="title-header">
                LISTADO DE ACCIONES DE CONTROL AÑO <?= $year ?>
            </div>
        </div>

        <div class="page center">
            <table cellspacing="0">
                <thead>
                <tr>
                    <th class="plhead left">No.</th>
                    <th class="plhead">Número</th>
                    <th class="plhead">AUDITORIA</th>
                    <th class="plhead">ESTADO</th>
                    <th class="plhead">APROBADO</th>
                    <th class="plhead">RESPONSABLE</th>
                    <th class="plhead">INICIA</th>
                    <th class="plhead">FINALIZA</th>
                    <th class="plhead">LUGAR</th>
                </tr>
            </thead>

                <tbody>
                <?php
                $i = 0;
                $_id = array(null);

                $obj_reg= new Tregister_planning($clink);

                while ($row = $clink->fetch_array($result)) {
                    ++$i;
                    $array_responsable = array('id_responsable' => $row['id_responsable'], 'id_responsable_2' => $row['id_responsable_2'],
                        'responsable_2_reg_date' => $row['responsable_2_reg_date']);

                    $array = $obj_reg->getEvento_reg($row['id'], $array_responsable);

                    //$img= $obj->getCumplidoImage();
                    $numero= !empty($row['numero']) ? $row['numero'] : null;
                    $numro.= !empty($row['numero_plus']) ? ".{$row['numero_plus']}" : null;

                    $capitulo= null;
                    if (!empty($row['id_tipo_evento'])) {
                        $obj_tipo_evento->Set($row['id_tipo_evento']);
                        $capitulo= $obj_tipo_evento->GetNumero();
                    } else {
                        if (!empty($row['empresrial']) && $row['empresarial'] >= 1) $capitulo= ($row['empresarial'] - 1);
                    }

                    if (!empty($capitulo)) $numero= "{$capitulo}.{$numero}";

                    $obj_reg->SetIdAuditoria($row['id']);
                    $array = $obj_reg->getEvento_reg(null, $array_responsable);
                    ?>

                    <tr>
                        <td class="plinner left"><?=++$j?></td>

                        <td class="plinner"><?=!empty($numero) ? $numero : $i?></td>

                        <td class="plinner">
                            <?php if (!empty($row['periodicidad'])) { ?>
                            <i class="fa fa-folder-open-o fa-2x"></i>
                            <?php } ?>

                            <?= $Ttipo_nota_origen_array[$row['origen']] ?>
                            <br/>
                            <?= $Ttipo_auditoria_array[$row['tipo']] ?>
                        </td>
                        <td class="plinner">

                            <?=$eventos_cump[$array['cumplimiento']]?>
                            <br />
                            <p>
                            <?php
                            $email= $obj_user->GetEmail($array['id_responsable']);
                            echo $email['nombre'];
                            if (!empty($email['cargo'])) echo ", ".textparse($email['cargo']);
                            echo "<br />". odbc2time_ampm($array['cronos']);
                            ?>
                            </p>
                        </td>
                        <td class="plinner">
                            <?php
                            if (!empty($array['aprobado'])) {
                                echo odbc2time_ampm($array['aprobado']);
                            }
                            ?>
                        </td>
                        <td class="plinner">
                            <?php
                            $email= $obj_user->GetEmail($row['id_responsable']);
                            echo $email['nombre'];
                            if (!empty($email['cargo'])) echo ", ".textparse($email['cargo']);
                            ?>
                        </td>
                        <td class="plinner">
                            <?= odbc2time_ampm($row['fecha_inicio_plan']) ?>
                        </td>
                        <td class="plinner">
                            <?= odbc2time_ampm($row['fecha_fin_plan']) ?>
                        </td>
                        <td class="plinner">
                            <?= textparse($row['lugar']) ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
            </table>

        </div>

         <?php require "inc/print_bottom.inc.php";?>

