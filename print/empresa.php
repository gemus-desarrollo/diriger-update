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
require_once "../php/class/time.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/objetivo.class.php";
require_once "../php/class/politica.class.php";
require_once "../php/class/perspectiva.class.php";
require_once "../php/class/inductor.class.php";
require_once "../php/class/indicador.class.php";

require_once "../php/class/cell.class.php";

require_once "../php/class/peso.class.php";
require_once "../php/class/peso_calculo.class.php";

require_once "../form/class/list.signal.class.php";

require_once "../php/inc_escenario_init.php";

require_once "../php/class/traza.class.php";

$id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : 1;

$obj_signal= new Tlist_signals($clink);
$obj_signal->SetYear($year);
$obj_signal->SetMonth($month);

$_id_proceso= $id_proceso;

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($_id_proceso);
$obj_prs->Set();
$tipo_prs= $obj_prs->GetTipo();
$proceso= $obj_prs->GetNombre();

$obj_user= new Tusuario($clink);
?>

<?php
if ($id_proceso != $_id_proceso) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
}    
$nombre= $obj_prs->GetNombre();
unset($obj_prs);

$obj_peso= new Tpeso_calculo($clink);

$obj_peso->SetYear($year);
$obj_peso->set_matrix();

if (!empty($id_proceso_code)) {
    $obj_peso->SetIdProceso($id_proceso);
    $obj_peso->set_id_proceso_code($id_proceso_code);
}

$obj_peso->SetYear($year);
$obj_peso->SetMonth($month);

$obj_peso->init_calcular();

$obj_peso->SetIdProceso($id_proceso);
$obj_peso->set_id_proceso_code($id_proceso_code);
$obj_peso->SetYearMonth($year, $month);
$obj_peso->compute_traze= true;
$value2= $obj_peso->calcular_empresa($id_proceso);

$obj_signal->get_month($_month, $_year);

$obj_peso->SetYear($_year);
$obj_peso->SetMonth($_month);

$obj_peso->init_calcular();
$obj_peso->SetIdProceso($id_proceso);
$obj_peso->SetYearMonth($_year, $_month);
$value1= $obj_peso->calcular_empresa($id_proceso);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN GENERAL", "Corresponde a periodo año: $year");
?>


<html>
    <head>
        <title>RESUMEN GENERAL</title>

        <?php require "inc/print_top.inc.php";?>

        <style>
            h1 {
                margin-top: 30px;
            }
        </style>

        <div class="container-fluid center">
            <div class="title-header">RESUMEN GENERAL<br/>
                <?= $meses_array[(int)$month] ?>/<?= $year ?>
            </div>
        </div>

        <div class="page center">

            <h1>GENERAL</h1>
            <table cellpadding="0" cellspacing="0" width="800px">
                <thead>
                    <tr>
                        <th class="plhead left" width="50">B</th>
                        <th class="plhead" width="50">R</th>
                        <th class="plhead" width="50">M</th>
                        <th class="plhead">OBSERVACIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="plinner signal left">
                            <div class="alarm-block">
                                <?php
                                if (!is_null($value2) && ($value2 > _YELLOW)) {
                                    $obj_signal->get_alarm($value2);
                                    $obj_signal->get_flecha($value2, $value1);

                                    if (!is_null($value2)) 
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
                                }
                                ?>
                            </div>
                        </td>

                        <td class="plinner"><?=$observacion?></td>
                    </tr>
                </tbody>    
            </table>


            <!-- OBJETIVOS DE TRABAJO -->
            <h1>OBJETIVOS DE TRABAJO DEL AÑO</h1>
            <?php
            $k_ind= 0;

            $obj= new Tinductor($clink);
            $obj->SetIdProceso($id_proceso);
            $obj->SetYear($year);

            $result_ind= $obj->listar(_PERSPECTIVA_ALL);
            ?>

            <table cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="plhead left" rowspan="2" width="30">No</th>
                        <th class="plhead" rowspan="2">OBJETIVO</th>
                        <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                    </tr>
                    <tr>
                        <th class="plhead">B</th>
                        <th class="plhead">R</th>
                        <th class="plhead">M</th>
                        <th class="plhead">OBSERVACIONES</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                while ($row= $clink->fetch_array($result_ind)) {
                    ++$k_ind;
                    $id_inductor= $row['_id'];

                    $nombre= $row['nombre'];
                    $descripcion= $if_top_inductor ? $row['descripcion'] : null;
                    $inicio= $row['inicio'];
                    $fin= $row['fin'];
                    $peso= $row['_peso'];

                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2= $obj_peso->calcular_inductor($row['id_inductor'], $row['id_inductor_code']);

                    $array_register= $obj_peso->get_array_register();
                    $obj_signal->get_month($_month, $_year);

                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $value1= $obj_peso->calcular_inductor($row['id_inductor'], $row['id_inductor_code']);

                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];

                    $responsable= null;
                    $observacion= null;

                    if ($id_user && $item_reg == 'IND') {
                        $observacion= $array_register['observacion'];

                        $email_user= $obj_user->GetEmail($id_user);
                        $responsable= $email_user['nombre'];
                        if (!is_null($email_user['cargo'])) 
                            $responsable.= ', '.$email_user['cargo'];
                        $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                    }

                    if (isset($obj_prs)) 
                        unset($obj_prs);
                    $obj_prs= new Tproceso($clink);

                    $obj_prs->Set($row['id_proceso']);
                    $tipo_prs= $obj_prs->GetTipo();
                    $proceso= $obj_prs->GetNombre();
                    $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                    ?>

                    <tr>
                        <td class="plinner left">
                            <?php echo !empty($row['_numero']) ? $row['_numero'] : $k_ind;?>
                        </td>

                        <td class="plinner">
                            <?=stripslashes($row['_nombre'])?>
                            <?php
                            $array= $obj->get_politicas($row['_id']);

                            if (count($array) > 0) {
                                echo "<br/><strong>Lineamientos: </strong>";
                                $j= 0;
                                foreach ($array as $cell) {
                                    if ($j > 0) echo ", ";
                                    echo 'L'.$cell['numero'];
                                    ++$j;
                                }   }
                            ?>
                        </td>

                        <td class="plinner signal">
                            <div class="alarm-block">
                                <?php
                                if (!is_null($value2) && ($value2 > _YELLOW)) {
                                    $obj_signal->get_alarm($value2);
                                    $obj_signal->get_flecha($value2, $value1);
                                    if (!is_null($value2)) 
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                        echo "<br /> ".number_format($value2, 1,'.','').'%';
                                }
                                ?>
                            </div>    
                        </td>

                        <td class="plinner"><?php echo nl2br($observacion)?></td>
                    </tr>
                    <?php }  ?>
                </tbody>
            </table>

            <!-- OBJETIVOS ESTRATEGICOS -->
            <h1> OBJETIVOS ESTRATÉGICOS</h1>

            <?php
            $obj= new Tobjetivo($clink);
            $obj->SetIdProceso($id_proceso);
            $obj->SetYear($year);
            $obj->SetIfControlInterno(false);

            $result_obj= $obj->listar();
            ?>

            <table cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th class="plhead left" rowspan="2" width="30">No</th>
                        <th class="plhead" rowspan="2">OBJETIVO</th>
                        <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                    </tr>
                    <tr>
                        <th class="plhead" width="50">B</th>
                        <th class="plhead" width="50">R</th>
                        <th class="plhead" width="50">M</th>
                        <th class="plhead">OBSERVACIONES</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $k_obj= 0;
                while ($row_obj= $clink->fetch_array($result_obj)) {
                    ++$k_obj;

                    $id_objetivo= $row_obj['_id'];
                    $peso= $row_obj['_peso'];
                    $obj->Set($id_objetivo);
                    $nombre= $obj->GetNombre();

                    $obj_peso->SetIdProceso($id_proceso);
                    $obj_peso->set_id_proceso_code($id_proceso_code);

                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);
                    $obj_peso->SetDay($day);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2= $obj_peso->calcular_objetivo($id_objetivo, $row_obj['_id_code']);
                    $array_register= $obj_peso->get_array_register();

                    $obj_signal->get_month($_month, $_year);

                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $value1= $obj_peso->calcular_objetivo($id_objetivo, $row_obj['_id_code']);

                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];

                    $responsable= null;
                    $observacion= null;

                    if ($id_user && $item_reg == 'OBJ') {
                        $observacion= $array_register['observacion'];

                        $email_user= $obj_user->GetEmail($id_user);
                        $responsable= $email_user['nombre'];
                        if (!is_null($email_user['cargo'])) 
                            $responsable.= ', '.$email_user['cargo'];
                        $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                    }

                    $obj_prs->Set($row_obj['id_proceso']);
                    $tipo_prs= $obj_prs->GetTipo();
                    $proceso= $obj_prs->GetNombre();
                    $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                ?>
                <tr>
                    <td class="plinner left">
                        <?php echo !empty($row_obj['numero']) ? $row_obj['numero'] : $k_obj;?>
                    </td>

                    <td class="plinner">
                        <?=stripslashes($nombre)?>
                        <?php
                        $array= $obj->get_politicas($row['_id']);

                        if (count($array) > 0) {
                            echo "<br/><strong>Lineamientos: </strong>";
                            $j= 0;
                            foreach ($array as $cell) {
                                if ($j > 0) echo ", ";
                                echo 'L'.$cell['numero'];
                                ++$j;
                            }   }
                        ?>
                    </td>

                    <td class="plinner signal">
                        <div class="alarm-block">
                            <?php
                            if (!is_null($value2) && ($value2 > _YELLOW)) {
                                $obj_signal->get_alarm($value2);
                                $obj_signal->get_flecha($value2, $value1);
                                if (!is_null($value2)) 
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
                            }
                            ?>
                        </div>    
                    </td>

                    <td class="plinner"><?=$observacion?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>


        <?php
        $k_per= 0;
        $obj= new Tperspectiva($clink);
        $obj->SetIdProceso($id_proceso);
        $obj->SetYear($year);
        $result_persp= $obj->listar();
        ?>

        <h1>PERSPECTIVAS (CUADRO DE MANDO INTEGRAL)</h1>
        <table cellpadding="0" cellspacing="0" width="800px">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2" width="30">No</th>
                    <th class="plhead" rowspan="2">PERSPECTIVA</th>
                    <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                </tr>
                <tr>
                    <th class="plhead">B</th>
                    <th class="plhead">R</th>
                    <th class="plhead">M</th>
                    <th class="plhead">OBSERVACIONES</th>
                </tr>
            </thead>

            <tbody>
                <?php
                while ($row_persp= $clink->fetch_array($result_persp)) {
                    ++$k_per;

                    $id_persp= $row_persp['_id'];
                    $peso= $row_persp['peso'];
                    $nombre= $row_persp['_nombre'];

                    $obj_peso->SetIdProceso($id_proceso);
                    $obj_peso->set_id_proceso_code($prs['id_code']);
                    $obj_peso->SetYear($year);
                    $obj_peso->SetMonth($month);
                    $obj_peso->SetDay($day);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($year, $month);
                    $obj_peso->compute_traze= true;
                    $value2= $obj_peso->calcular_perspectiva($id_persp, $row_persp['_id_code']);
                    $array_register= $obj_peso->get_array_register();

                    $obj_signal->get_month($_month, $_year);

                    $obj_peso->SetYear($_year);
                    $obj_peso->SetMonth($_month);

                    $obj_peso->init_calcular();
                    $obj_peso->SetYearMonth($_year, $_month);
                    $value1= $obj_peso->calcular_perspectiva($id_persp, $row_persp['_id_code']);

                    $id_user= $array_register['id_usuario'];
                    $item_reg= $array_register['signal'];

                    $responsable= null;
                    $observacion= null;

                    if ($id_user && $item_reg == 'PER') {
                        $observacion= $array_register['observacion'];

                        $email_user= $obj_user->GetEmail($id_user);
                        $responsable= $email_user['nombre'];
                        if (!is_null($email_user['cargo'])) 
                            $responsable.= ', '.$email_user['cargo'];
                        $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                    }

                    $tipo_prs= $row_persp['tipo'];
                    $proceso= $row_persp['proceso'];
                    $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];
                ?>

                <tr>
                    <td class="plinner left"><?php echo !empty($row_persp['numero']) ? $row_persp['numero'] : $k_per;?></td>

                    <td class="plinner">
                        <?=stripslashes($nombre)?>
                    </td>

                    <td class="plinner signal">
                        <div class="alarm-block">
                            <?php
                            if (!is_null($value2) && ($value2 > _YELLOW)) {
                                $obj_signal->get_alarm($value2);
                                $obj_signal->get_flecha($value2, $value1);
                                if (!is_null($value2)) 
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
                            }
                            ?>
                        </div>    
                    </td>

                    <td class="plinner"><?=$observacion?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>


        <!-- POLITICAS O ;INEAMIENTOS -->
        <h1>LINEAMIENTOS</h1>

        <?php
        $k_pol= 0;
        $obj= new Tpolitica($clink);
        $obj->SetIdProceso($id_proceso);
        $obj->SetYear($year);

        $result_pol= $obj->listar(1);
        ?>

        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th class="plhead left" rowspan="2" width="30">No</th>
                    <th class="plhead" rowspan="2">LINEAMIENTO</th>
                    <th class="plhead bottom" colspan="4">EVALUACIÓN</th>
                </tr>
                <tr>
                    <th class="plhead">B</th>
                    <th class="plhead">R</th>
                    <th class="plhead">M</th>
                    <th class="plhead">OBSERVACIONES</th>
                </tr>
            </thead>

            <tbody>
            <?php
            while ($row_pol= $clink->fetch_array($result_pol)) {
                $id_politica= $row_pol['_id'];

                ++$k_pol;
                $nombre= $row_pol['nombre'];
                $numero= $row_pol['numero'];

                $obj_peso->SetYear($year);
                $obj_peso->SetMonth($month);
                $obj_peso->SetDay($day);

                $obj_peso->init_calcular();
                $obj_peso->SetYearMonth($year, $month);
                $obj_peso->compute_traze= true;
                $value2= $obj_peso->calcular_politica($id_politica, $row_pol['_id_code']);
                $array_register= $obj_peso->get_array_register();

                $obj_signal->get_month($_month, $_year);

                $obj_peso->SetYear($_year);
                $obj_peso->SetMonth($_month);

                $obj_peso->init_calcular();
                $obj_peso->SetYearMonth($_year, $_month);
                $value1= $obj_peso->calcular_politica($id_politica, $row_pol['_id_code']);

                $id_user= $array_register['id_usuario'];
                $item_reg= $array_register['signal'];

                $responsable= null;
                $observacion= null;

                if ($id_user && $item_reg == 'POL') {
                    $observacion= $array_register['observacion'];

                    $email_user= $obj_user->GetEmail($id_user);
                    $responsable= $email_user['nombre'];
                    if (!is_null($email_user['cargo'])) 
                        $responsable.= ', '.$email_user['cargo'];
                    $responsable.= '  <br /><u>corte:</u>'.odbc2date($array_register['reg_fecha']).'<br/><u>registrado:</u>'.odbc2time_ampm($array_register['cronos']);
                }

                $if_titulo= setZero($row_pol['titulo']);
                $if_inner= setZero(boolean($row_pol['if_inner']));
            ?>

                <tr>
                    <td class="plinner left"><?php echo !empty($numero) ? $numero : $k_pol;?></td>

                    <td class="plinner">
                        <?php
                        echo nl2br(stripslashes($nombre));

                        if ($row_pol['if_inner']) {
                            unset($obj_prs);
                            $obj_prs= new Tproceso($clink);
                            $obj_prs->Set($row_pol['id_proceso']);
                            $tipo_prs= $obj_prs->GetTipo();
                            $proceso= $obj_prs->GetNombre();
                            $proceso.= ", ".$Ttipo_proceso_array[$tipo_prs];

                            echo "<br /><span class='comment'>Politica del Organismo: $proceso. Para el periodo: ".$row_pol['inicio'].'-'.$row_pol['fin'].'</span>';
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
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
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
                                    echo "<br /> ".number_format($value2, 1,'.','').'%';
                            }
                            ?>
                        </div>    
                    </td>

                    <td class="plinner"><?=$observacion?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        </div>

    <?php require "inc/print_bottom.inc.php";?>