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
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/politica.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

require_once "../php/class/traza.class.php";

$capitulo= !empty($_GET['capitulo']) ? $_GET['capitulo'] : 0;
$grupo= !empty($_GET['grupo']) ? $_GET['grupo'] : 0;
$chk_sys= !is_null($_GET['chk_sys']) ? $_GET['chk_sys'] : 1;
$chk_inner= !is_null($_GET['chk_inner']) ? $_GET['chk_inner'] : 0;
$chk_title= !is_null($_GET['chk_title']) ? $_GET['chk_title'] : 0;

$obj_user= new Tusuario($clink);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();

$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$obj_peso= new Tpeso_calculo($clink);

if (!empty($id_proceso_code)) {
    $obj_peso->SetIdProceso($id_proceso);
    $obj_peso->set_id_proceso_code($id_proceso_code);
}

$obj_peso->SetYear($year);
$obj_peso->set_matrix();

$k_pol= 0;
$k_pol_total= 0;
$array_capitulo= array();
$array_grupo= array();
$ipol= 0;

if (isset($obj)) 
    unset($obj);
$obj= new Tpolitica($clink);

$obj->SetYear($year);
$obj->SetIfTitulo(false);
$result_pol= $obj->listar();
$k_pol_total= $obj->GetCantidad();

unset($obj);
$obj= new Tpolitica($clink);

$obj->SetYear($year);
$obj->SetCapitulo(!empty($capitulo) ? $capitulo : null);
$obj->SetGrupo(!empty($grupo) ? $grupo : null);

$chk_sys= empty($chk_sys) ? false : true;
$result_pol= $obj->listar($chk_sys);

$array_politicas= null;
create_array_valid_politicas($result_pol);


while ($row_pol= $clink->fetch_array($result_pol)) {
    ++$ipol;

    if ($chk_inner && empty($row_pol['if_inner'])) 
        continue;

    if (empty($row_pol['capitulo']))
        if (empty($array_capitulo[$row_pol['id']]['name'])) 
            $array_capitulo[$row_pol['id']]['name']= $row_pol['nombre'];

    if (!empty($row_pol['titulo']) && (!empty($row_pol['capitulo']) && empty($row_pol['grupo'])))
        if (empty($array_grupo[$row_pol['id']]['name'])) 
            $array_grupo[$row_pol['id']]['name']= $row_pol['nombre'];

    if (empty($row_pol['titulo'])) {
        $array_capitulo[$row_pol['capitulo']]['n']= empty($array_capitulo[$row_pol['capitulo']]['n']) ? 1 : ++$array_capitulo[$row_pol['capitulo']]['n'];

        if (!empty($row_pol['grupo']))
            $array_grupo[$row_pol['grupo']]['n']= empty($array_grupo[$row_pol['grupo']]['n']) ? 1 : ++$array_grupo[$row_pol['grupo']]['n'];
    }
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RELACIÓN DE LINEAMIENTOS", "Corresponde a periodo año: $year");
?>

<html>

<head>
    <title>RELACIÓN DE LINEAMIENTOS</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div class="title-header">
            RELACIÓN DE LINEAMIENTOS O POLÍTICAS DE TRABAJO <br /><?= $meses_array[(int)$month] ?>/<?= $year ?>
        </div>
    </div>

    <?php
    $i = 0;
    $clink->data_seek($result_pol);

    while ($row = $clink->fetch_array($result_pol)) {
        if ($chk_inner && empty($row['if_inner']))
            continue;
        if (!empty($row['titulo']))
            continue;

        if ($i > 0)
            $line .= ", ";
        $line .= 'L' . $row['numero'];
        ++$i;
    }
    ?>

    <div class="page center">
        <div style="text-align:left; margin:10px; margin-left:20; max-width: 800px;">
            <strong>Total de Lineamientos:</strong><?= $i ?><br />
            <strong>Lineamientos: </strong><br /> <?= $line ?>
        </div>

        <br />

        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2" width="30">No</th>
                    <th class="plhead" rowspan="2">LINEAMIENTO O POLÍTICA</th>
                    <th class="plhead" colspan="3" class="bottom">EVALUACIÓN</th>
                </tr>
                <tr>
                    <th class="plhead signal">B</th>
                    <th class="plhead signal">R</th>
                    <th class="plhead signal">M</th>
                </tr>
            </thead>

            <?php
            unset($obj);
            $obj = new Tpolitica($clink);
            $obj->SetYear($year);
            $obj->SetIfTitulo(false);
            $obj->SetIfCapitulo(false);

            $k_pol_show = 0;
            $k_pol = 0;

            $obj->SetCapitulo(0);
            _tree_politica($k_pol, 0, 0, $k_pol_show);    

            $obj->SetIfTitulo(true);
            $obj->SetIfCapitulo(true);
            $result_pol_cap = $obj->listar(false);

            while ($row_pol_cap = $clink->fetch_array($result_pol_cap)) {
                if ($chk_inner && empty($row_pol_cap['if_inner']))
                    continue;
                if (!empty($capitulo) && $capitulo != $row_pol_cap['id'])
                    continue;

                ++$k_pol;

                if ((!empty($array_capitulo[$row_pol_cap['id']]['n']) || $chk_title) && !empty($array_capitulo[$row_pol_cap['id']]['name'])) {
            ?>
                    <tr>
                        <td class="plinner left" colspan="6">
                            <?php
                            echo '<strong>' . number_format_to_roman($row_pol_cap['numero']) . '.  ' . $array_capitulo[$row_pol_cap['id']]['name'] . '</strong>';

                            if ($row_pol_cap['if_inner']) {
                                unset($obj_prs);
                                $obj_prs = new Tproceso($clink);
                                $obj_prs->Set($row_pol_cap['id_proceso']);
                                $tipo_prs = $obj_prs->GetTipo();
                                $proceso = $obj_prs->GetNombre();
                                $proceso .= ", " . $Ttipo_proceso_array[$tipo_prs];

                                echo "<br /><span class='comment'>Politica del Organismo: $proceso. Para el periodo: " . $row_pol_cap['inicio'] . '-' . $row_pol_cap['fin'] . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php
                }

                unset($obj);
                $obj = new Tpolitica($clink);

                $obj->SetYear($year);
                $obj->SetIfTitulo(true);
                $obj->SetIfGrupo(true);
                $obj->SetCapitulo($row_pol_cap['id']);

                $result_pol_grupo = $obj->listar(false);
                $cant = $obj->GetCantidad();

                _tree_politica($k_pol, $row_pol_cap['id'], false, $k_pol_show);

                while ($row_pol_grupo = $clink->fetch_array($result_pol_grupo)) {
                    if ($chk_inner && empty($row_pol_grupo['if_inner']))
                        continue;
                    if (!empty($grupo) && $grupo != $row_pol_grupo['id'])
                        continue;

                    ++$k_pol;

                    if ((!empty($array_grupo[$row_pol_grupo['id']]['n']) || $chk_title) && !empty($array_grupo[$row_pol_grupo['id']]['name'])) {
                    ?>
                        <tr>
                            <td class="plinner left" colspan="6">
                                <?php
                                echo '<strong style=\'marging-let:10px;\'>' . $row_pol_grupo['numero'] . '.  </strong>' . $array_grupo[$row_pol_grupo['id']]['name'];

                                if ($row_pol_grupo['if_inner']) {
                                    unset($obj_prs);
                                    $obj_prs = new Tproceso($clink);
                                    $obj_prs->Set($row_pol_grupo['id_proceso']);
                                    $tipo_prs = $obj_prs->GetTipo();
                                    $proceso = $obj_prs->GetNombre();
                                    $proceso .= ", " . $Ttipo_proceso_array[$tipo_prs];

                                    echo "<br /><span class='comment'>Politica del Organismo: $proceso. Para el periodo: " . $row_pol_grupo['inicio'] . '-' . $row_pol_grupo['fin'] . '</span>';
                                }
                                ?>
                            </td>
                        </tr>
            <?php 
                    } 

                    _tree_politica($k_pol, $row_pol_cap['id'], $row_pol_grupo['id'], $k_pol_show);
                }
            }
            ?>

        </table>

    </div>

    <?php require "inc/print_bottom.inc.php";?>

    <?php

function _tree_politica(&$k_pol, $capitulo, $grupo, &$k_pol_show) {
    global $clink;
    global $year, $month, $day;
    global $obj_signal;
    global $obj_user;
    global $id_proceso, $id_proceso_code;
    global $chk_sys, $chk_inner;
    global $array_politicas;
    global $Ttipo_proceso_array;

    global $obj_peso;

    $obj = new Tpolitica($clink);
    $obj->SetYear($year);
    $obj->SetIdProceso($id_proceso);

    $obj->SetIfTitulo(false);
    
    if ($grupo === false)
        $obj->SetIfGrupo(false);
    
    $obj->SetIfCapitulo(false);
    $obj->SetCapitulo($capitulo);

    if (!empty($grupo))
        $obj->SetGrupo($grupo);

    $chk_sys = empty($chk_sys) ? false : true;
    $result_pol = $obj->listar($chk_sys);

    while ($row_pol = $clink->fetch_array($result_pol)) {
        $id_politica= $row_pol['_id'];

        if (empty($array_politicas[$row_pol['_id']]))
            continue;
        if ($chk_inner && empty($row_pol['if_inner']))
            continue;

        ++$k_pol;
        ++$k_pol_show;
        $nombre = $row_pol['nombre'];
        $numero = $row_pol['numero'];

        $obj_peso->SetYear($year);
        $obj_peso->SetMonth($month);
        $obj_peso->SetDay($day);

        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($year, $month);
        $obj_peso->compute_traze= true;
        $value2 = $obj_peso->calcular_politica($id_politica);
        $array_register = $obj_peso->get_array_register();

        $obj_signal->get_month($_month, $_year);
        $obj_peso->SetYear($_year);
        $obj_peso->SetMonth($_month);

        $obj_peso->init_calcular();
        $obj_peso->SetYearMonth($_year, $_month);
        $obj_peso->SetDay(null);
        $value1 = $obj_peso->calcular_politica($id_politica);

        $id_user = $array_register['id_usuario'];
        $item_reg = $array_register['signal'];

        $responsable = null;
        $observacion = null;

        if ($id_user) {
            $observacion = $array_register['observacion'];

            $email_user = $obj_user->GetEmail($id_user);
            $responsable = $email_user['nombre'];
            if (!is_null($email_user['cargo'])) 
                $responsable .= ', ' . $email_user['cargo'];
            $responsable .= '  <br /><u>corte:</u>' . odbc2date($array_register['reg_fecha']) . '<br /><u>registrado:</u>' . odbc2time_ampm($array_register['cronos']);
        }

        $if_titulo = setZero($row_pol['titulo']);
        ?>

    <tr>
        <td class="plinner left"><?= !empty($numero) ? $numero : $k_pol ?></td>

        <td class="plinner">
            <?php
            echo nl2br(stripslashes($nombre));

            if ($row_pol['if_inner']) {
                unset($obj_prs);
                $obj_prs = new Tproceso($clink);
                $obj_prs->Set($row_pol['id_proceso']);
                $tipo_prs = $obj_prs->GetTipo();
                $proceso = $obj_prs->GetNombre();
                $proceso .= ", " . $Ttipo_proceso_array[$tipo_prs];

                echo "<br /><span class='comment'>Politica del Organismo: $proceso. Para el periodo: " . $row_pol['inicio'] . '-' . $row_pol['fin'] . '</span>';
            }

            if (!is_null($observacion)) {
                echo "<br /><strong>Observaciones:</strong><br />";
                echo nl2br($observacion);
            }
            ?>
        </td>

        <td class="plinner signal">
            <div class="alarm-block">
                <?php
                if (!is_null($value2) && ($value2 > _YELLOW)) {
                    $obj_signal->get_alarm($value2);
                    $obj_signal->get_flecha($value2, $value1);

                    if (!is_null($value2))
                        echo "<br /> " . number_format($value2, 1, '.', '') . '%';
                }
                ?>
            </div>
        </td>

        <td class="plinner signal">
            <div class="alarm-block">
                <?php
                if (!is_null($value2) && ($value2 > _ORANGE && $value2 <= _YELLOW)) {
                    $obj_signal->get_alarm($value2);
                    $obj_signal->get_flecha($value2, $value1);

                    if (!is_null($value2))
                        echo "<br /> " . number_format($value2, 1, '.', '') . '%';
                }
                ?>
            </div>
        </td>

        <td class="plinner signal">
            <div class="alarm-block">
                <?php
                if (!is_null($value2) && $value2 <= _ORANGE) {
                    $obj_signal->get_alarm($value2);
                    $obj_signal->get_flecha($value2, $value1);

                    if (!is_null($value2))
                        echo "<br /> " . number_format($value2, 1, '.', '') . '%';
                }
                ?>
            </div>
        </td>
    </tr>
    <?php
    }
}
?>