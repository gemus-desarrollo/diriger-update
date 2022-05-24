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
require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/asistencia.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tipo_reunion.class.php";

require_once "../form/class/tarea.signal.class.php";

require_once "../php/class/traza.class.php";

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : null;

$obj= new Ttematica($clink);

$obj->SetIdEvento($id_evento);
$obj->SetIdProceso($id_proceso);

$obj->SetIdResponsable(NULL);
$obj->SetIdUsuario(NULL);
$obj->SetDay(NULL);
$obj->SetMonth(null);
$obj->SetYear($year);

$obj_user= new Tusuario($clink);
$obj_event= new Tevento($clink);
$obj_meeting= new Ttipo_reunion($clink);
$obj_assist= new Tasistencia($clink);

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($id_proceso);
$obj_prs->Set();
$proceso= $obj_prs->GetNombre();
$tipo_prs= $obj_prs->GetTipo();

$obj->list_reg();

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "RESUMEN DE CUMPLIENTO DE LOS ACUERDOS", "Corresponde a periodo mes/año: $month/$year");
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>RESUMEN DE CUMPLIENTO DE LOS ACUERDOS</title>
        
        <?php require "inc/print_top.inc.php";?>
        
        <style type="text/css">
            .page.block {
                width: 800px;
                text-align: left;
            }
            
        </style>
        
        <div class="page center">
            <table class="center none-border" width="100%">                
                <tr>
                    <td class="none-border">
                        <div class="center">
                            <h1>RESUMEN DEL CUMPLIMIENTO DE LOS ACUERDOS</h1>
                            <strong>AÑO <?= $year ?></strong><br />
                        </div>
                    </td>
                </tr>
                <!--
                <tr>
                    <td class="none-border pull-left">
                        <h1 style="text-decoration: underline">TAREAS PRINCIPALES</h1><br />
                        <?php
                        $objetivos= $obj->GetObjetivo();
                        $objetivos= purge_html($objetivos, false);
                        $objetivos= textparse($objetivos, false);
                        echo $objetivos;
                        ?>
                    </td>
                </tr>
                -->
                <!--
                <tr>
                    <td class="none-border">
                        <div class="container-fluid pull-right">
                            <strong>Elaborado por:</strong><br />
                            <?=$cargo_print?><br /><?=$usuario_print?><br /><?=$proceso_print?><br />
                            <img id="img" src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&signal=&id=<?=$_SESSION['id_usuario']?>" border="0" />
                        </div>
                    </td>
                </tr> 
                -->
            </table>
        </div>

        <div class="page center">
            <br /><br />
            
            <table class="none-border center" width="800px">
                <thead>
                    <tr>
                        <th rowspan="2" align="center" class="plhead left right">TOTAL DE ACUERDOS EN EL AÑO</th>
                        <th colspan="3" align="center" class="plhead right bottom">DE ELLOS</th>
                    </tr>
                    <tr>
                        <th align="center" class="plhead right">CUMPLIDOS</th>
                        <th align="center" class="plhead right">INCUMPLIDOS</th>
                        <th align="center" class="plhead right">SUSPENDIDOS O POSPUESTOS</th>
                    </tr>
                </thead> 
    
                <tbody> 
                    <tr>
                        <td class="plinner left center none-bottom">
                            <?= $obj->total ." (100%)"?>
                        </td>

                        <td class="plinner center none-bottom">
                            <?php
                            if (!empty($obj->cumplidas)) {
                                echo $obj->cumplidas;
                                $ratio = ($obj->cumplidas / $obj->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            }
                            ?>
                        </td>

                        <td class="plinner center none-bottom">
                            <?php
                            if (!empty($obj->incumplidas)) {
                                echo $obj->incumplidas;
                                $ratio = ($obj->incumplidas / $obj->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            }
                            ?>
                        </td>

                        <td class="plinner center none-bottom">
                            <?php
                            if (!empty($obj->canceladas)) {
                                echo $obj->canceladas;
                                $ratio = ($obj->canceladas / $obj->total) * 100;
                                $ratio = setNULL($ratio, true);
                                echo ' (' . number_format($ratio, 1) . '%)';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>    
                
            <table class="none-border center" width="800px"> 
                <tr>
                    <td class="plinner left top center" colspan="2"><strong>OBSERVACIONES DEL CUMPLIMIENTO</strong></td>
                    <td class="plinner top" style="text-align:center">
                        <strong><?=$config->responsable_planwork ? "RESPONSABLE" : "QUIEN LAS ORIGINO"?></strong>
                    </td>
                    <td class="plinner top" style="text-align:center"><strong>CAUSAS</strong></td>
                </tr>

                <tr>
                    <td colspan="4" class="plinner left">
                        <strong>ACUERDOS INCUMPLIDOS</strong>
                    </td>
                </tr>

                <?php
                $i = 0;
                foreach ($obj->incumplidas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left"><?= ++$i ?></td>
                        <td class="plinner">
                            <?= textparse($array['evento']) ?><br /><?= odbc2time_ampm($array['plan']) ?>
                        </td>

                        <td class="plinner">
                            <?php
                            $email = $config->responsable_planwork ? $obj_user->GetEmail($array['id_responsable']) : $obj_user->GetEmail($array['id_user_asigna']);
                            echo textparse($email['nombre']) . ' <br>' . $email['cargo'];
                            ?>
                        </td>

                        <td class="plinner"><?= textparse($array['observacion']); ?></td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td align="center" class="plinner left center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="4" class="plinner left">
                        <strong>SUSPENDIDOS O POSPUESTOS</strong>
                    </td>
                </tr>

                <?php
                $i = 0;
                foreach ($obj->canceladas_list as $array) {
                    ?>
                    <tr>
                        <td class="plinner left"><?= ++$i ?></td>
                        <td class="plinner"><?= textparse(purge_html($array['evento'])) ?><br /><?= odbc2time_ampm($array['plan']) ?></td>

                        <td class="plinner">
                            <?php
                            $email = $config->responsable_planwork ? $obj_user->GetEmail($array['id_responsable']) : $obj_user->GetEmail($array['id_user_asigna']);
                            echo textparse($email['nombre']) . ' <br>' . $email['cargo'];
                            ?>
                        </td>

                        <td class="plinner"><?= textparse(purge_html($array['observacion'])) ?></td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td align="center" class="plinner left center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                    </tr>
                <?php } ?>

                <?php if ($i == 0) { ?>
                    <tr>
                        <td align="center" class="plinner left center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                        <td align="center" class="plinner center">&nbsp;</td>
                    </tr>
                <?php } ?>

                <tr>
                    <td colspan="2" class="none-border top"></td>
                    <td class="none-border top"></td>
                    <td colspan="2" class="none-border top"></td>
                </tr>
            </table>
        </div>    
    
    <?php require "inc/print_bottom.inc.php";?>
