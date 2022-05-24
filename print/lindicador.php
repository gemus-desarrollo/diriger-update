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
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/unidad.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/traza.class.php";

$action= $_GET['action'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_perspectiva= !empty($_GET['id_perspectiva']) ? $_GET['id_perspectiva'] : null;
$id_inductor= !empty($_GET['id_inductor']) ? $_GET['id_inductort'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();

$obj_un= new Tunidad($clink);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTADO DE INDICADORES");
?>

<html>

<head>
    <title>LISTADO DE INDICADORES</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div class="title-header">LISTADO DE INDICADORES<br />
            <?= $meses_array[(int)$month] ?>/<?= $year?>
        </div>
    </div>

    <div class="page center">
        <?php
            write_table(null, null, null, $id_proceso);

            $obj_persp= new Tperspectiva($clink);

            $obj_persp->SetIdProceso($id_proceso);
            $obj_persp->SetYear($year);
            $result_persp= $obj_persp->listar();

            $color= null;
            $i= 0;
            while ($row_persp= $clink->fetch_array($result_persp)) {
                $color= '#'.$row_persp['color'];

                write_table($row_persp['_id'], $row_persp['nombre'], $color, $row_persp['_id_proceso']);
            }
            ?>
    </div>

    <?php require "inc/print_bottom.inc.php";?>

    <?php
function write_table($id_perspectiva, $perspectiva, $color, $_id_proceso) {
    global $action;
    global $year;
    global $periodo_inv;
    global $clink;
    global $id_proceso;
    global $Ttipo_proceso_array;

    $obj_indi= new Tindicador($clink);
    $obj_user= new Tusuario($clink);
    $obj_un= new Tunidad($clink);
    $obj_prs= new Tproceso($clink);

    $obj_indi->SetIdPerspectiva($id_perspectiva);
    $obj_indi->SetYear($year);

    $use_perspectiva= is_null($perspectiva) ? _PERSPECTIVA_NULL : _PERSPECTIVA_NOT_NULL;
    $result= $obj_indi->listar($year, $use_perspectiva);
    $cant= $obj_indi->GetCantidad();

    if (empty($cant)) 
        return;
?>

    <?php if (!is_null($perspectiva)) { ?>
    <div style="margin:10px; margin-top: 30px; font-size: 1em; width: 830px; text-align: center"><strong>PERSPECTIVA:
        </strong>
        <?php
        echo $perspectiva;

        if ($id_proceso != $_id_proceso) {
            $obj_prs->Set($_id_proceso);
            echo "<div class='comment' style='width: 400px;'>".$obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()].'</div>';
        }
        ?>
    </div>
    <?php } ?>

    <table cellpadding="0" cellspacing="0" border=1>
        <thead>
            <tr>
                <th class="plhead left">No.</th>
                <th class="plhead">INDICADOR</th>

                <th class="plhead">UNIDAD</th>
                <th class="plhead">DECIMALES</th>

                <th class="plhead">CÁLCULO</th>
                <th class="plhead">DESCRIPCIÓN</th>
                <th class="plhead">PLAN</th>
                <th class="plhead">CARGA</th>
                <?php if ($action == 'user') { ?>
                <th class="plhead">ACTUALIZA</th>
                <th class="plhead">PLANIFICA</th>
                <?php } ?>
            </tr>
        </thead>

        <tbody>

            <?php
        $i= 0;
        $array_ids= array();
        while ($row= $clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= 1;

            if ($row['id_proceso'] != $id_proceso)
                continue;
            if ($row['id_proceso'] != $_SESSION['id_entity']) {
                if (!$obj_indi->test_if_in_proceso($_SESSION['id_entity'], $row['_id']))
                    continue;
            }
            
            if ($year < $row['_inicio'] || $year > $row['_fin'])
                continue;
        ?>
            <tr>
                <td class="plinner left"><?= !empty($row['_numero']) ? $row['_numero'] : ++$i?></td>

                <td class="plinner" <?php if (!is_null($color)) echo "style='background:$color;'"?>>
                    <?php
                    echo $row['_nombre'];

                    if ($id_proceso != $row['_id_proceso']) {
                        $obj_prs->Set($row['_id_proceso']);
                        echo "<span class='comment'>".$obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()].'</span>';
                    }
                    ?>
                </td>

                <?php
                $unidad= $obj_un->GetUnidadById($row['id_unidad']);
                $unidad = preg_replace("#<p>(.*)<\/p>#", '$1', $unidad);
                $unidad.= "<br>".$obj_un->GetDescripcion();
                $decimal= $obj_un->GetDecimal();
                ?>

                <td class="plinner" style="text-align: center"><?= $unidad ?></td>
                <td class="plinner" style="text-align: center"><?= $decimal ?></td>

                <td class="plinner">
                    <?= $obj_indi->replace_formulate($row['calculo']) ?>
                </td>
                <td class="plinner">
                    <?= nl2br(stripslashes($row['_descripcion'])) ?>
                </td>
                <td class="plinner">
                    <?= $periodo_inv[$row['periodicidad']] ?>
                </td>
                <td class="plinner">
                    <?= $periodo_inv[$row['carga']] ?>
                </td>

                <?php if ($action == 'user') { ?>
                <td class="plinner">
                    <?php
                    $id_usuario= NULL;
                    $id_usuario= $row['id_usuario_real'];
                    $email= $obj_user->GetEmail($id_usuario);

                    if (!empty($id_usuario))
                        echo $email['nombre'].' ('.$email['cargo'].')';
                    else
                        echo "&nbsp;";
                    ?>
                </td>
                <td class="plinner">
                    <?php
                    $id_usuario= NULL;
                    $id_usuario= $row['id_usuario_plan'];
                    $email= $obj_user->GetEmail($id_usuario);

                    if (!empty($id_usuario))
                        echo $email['nombre'].' ('.$email['cargo'].')';
                    else
                        echo "&nbsp;";
                    ?>
                </td>
                <?php } ?>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php } ?>