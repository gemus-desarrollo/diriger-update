<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/escenario.class.php";

require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/time.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";
require_once "../php/class/cell.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/class/traza.class.php";

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : null;

require_once "../php/inc_escenario_init.php";

if (empty($id_proceso)) 
    $id_proceso= $_SESSION['id_entity'];
$signal= "graph_proceso";

$obj_user= new Tusuario($clink);

$obj= new Tcell($clink);
$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetDay($day);

$array_criterio= array(null, '&ge;','&le;','[]');

$obj_peso= new Tpeso_calculo($clink);

$obj_peso->SetDay($day);
$obj_peso->SetMonth($month);
$obj_peso->SetYear($year);

$obj_peso->SetIdProceso($id_proceso);
$obj_peso->set_id_proceso_code($id_proceso_code);

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_prs= new Tproceso($clink);
$array= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], $id_proceso, null);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DE PROCESOS INTERNOS", "Corresponde a periodo año: $year");
?>


<html>
    <head>
        <title>RESUMEN GENERAL</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="page center">
            <div class="container-fluid center">
                <div class="title-header">RESUMEN DE PROCESOS INTERNOS<br/>
                    <?= $meses_array[(int)$month] ?>/<?= $year ?>
                </div>
            </div>

            <?php
            $obj_signal= new Tlist_signals($clink);
            $obj_signal->SetYear($year);

            $obj_peso= new Tpeso_calculo($clink);
            $obj_peso->SetYear($year);
            $obj_peso->set_matrix();

            $obj_prs= new Tproceso($clink);
            $obj_prs->SetYear($year);
            $obj_prs->SetIdUsuario($_SESSION['id_usuario']);
            $obj_prs->get_procesos_by_user('eq_desc', _TIPO_PROCESO_INTERNO);
            $array_procesos= $obj_prs->array_procesos;
            
            unset($obj_prs);
            $obj_prs= new Tproceso($clink);
            $obj_prs->SetYear($year);    
            $obj_prs->SetTipo(_TIPO_PROCESO_INTERNO);
            $result= $obj_prs->listar(false);
            $cant_prs= $obj_prs->GetCantidad();                    
            ?>
        </div>

        <div class="page center">
            <h1>GENERAL</h1>
            <table width="100%">
                <thead>
                    <tr>
                        <th class="plhead left" rowspan="2">PROCESOS</th>
                        <th class="plhead"colspan="12">MESES</th>
                        <th class="plhead" rowspan="2">OBSERVACIONES</th>
                    </tr>
                    <tr>
                        <th class="plhead month">Ene</th>
                        <th class="plhead month">Feb</th>
                        <th class="plhead month">Mar</th>
                        <th class="plhead month">Abr</th>
                        <th class="plhead month">May</th>
                        <th class="plhead month">Jun</th>
                        <th class="plhead month">Jul</th>
                        <th class="plhead month">Ago</th>
                        <th class="plhead month">Sep</th>
                        <th class="plhead month">Oct</th>
                        <th class="plhead month">Nov</th>
                        <th class="plhead month">Dic</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    foreach ($obj_prs->array_procesos as $row) {
                    ?>
                    <tr>
                        <td class="plinner left name">
                            <?php if (array_key_exists($row['id'], $array_procesos) || $if_jefe) { ?>
                            <a href="#" class="btn btn-info btn-sm" title="ver detallkes del estado"
                                onclick="go_proceso(<?=$row['id']?>);">
                                <i class="fa fa-eye"> Detalles</i>
                            </a>
                            <?php } ?>
                            <?=$row['nombre']?>
                        </td>
                        <?php
                        $obj_signal->SetIdProceso($row['id']);
                        $obj_signal->set_criterio();
                        
                        $j= 0;
                        $value= null;
                        $observacion= null;
                        for ($mm= 1; $mm < 13; $mm++) {
                            $value2= null;
                            $observacion2= null;
                            if ($mm <= $month) {
                                ++$j;
                                $obj_peso->SetMonth($mm);
                                $obj_peso->SetDay(null);

                                $obj_peso->init_calcular();
                                $obj_peso->SetYearMonth($year, $mm);

                                $value2= $obj_peso->calcular_proceso($row['id'], $row['tipo'], $observacion2);
                                $value= $value2;
                                if ($mm == $month)
                                    $observacion.= !empty($observacion2) ? "$observacion2" :  null;
                                $if_eficaz= $obj_peso->get_if_eficaz();
                            }
                            ?>
                            <td class="plinner cell-alarm">
                                <?=!is_null($value2) ? number_format($value2, 1,'.','').'%' : ''?>
                                <?php
                                if (!is_null($value2))
                                    $obj_signal->get_alarm_prs($value2, true, false);
                                ?>
                            </td>
                        <?php } ?>

                        <td class="plinner">
                            <?php
                            if (empty($value))
                                $danger= "default";
                            else 
                                $danger= $if_eficaz ? "success" : "danger";
                            ?>
                            <div class="alert alert-<?=$danger?>">
                                <?=!empty($value) ? ($if_eficaz ? "EFICAZ" : "NO EFICAZ") : ''?>
                            </div>
                            <div style="text-align: left;">
                                <?=!empty($observacion) ? $observacion : null?>
                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>

    <?php require "inc/print_bottom.inc.php";?>