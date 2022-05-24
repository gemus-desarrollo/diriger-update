<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/registro.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

$time= new TTime();
 
if (!empty($_GET['year'])) 
    $year= $_GET['year'];
if (empty($year)) 
    $year= $_SESSION['current_year'];
if (empty($year)) 
    $year= $time->GetYear();

if (empty($_GET['month'])) 
    $month= $_GET['month'];
if (empty($month)) 
    $month= $_SESSION['current_month'];
if ($month == -1 || empty($month)) 
    $month= $time->GetMonth();

$time->SetMonth($month);
$time->SetYear($year);

if (!empty($_GET['day'])) 
    $day= $_GET['day'];
if (empty($day)) 
    $day= $time->longmonth();

$id_indicador= 0;
if (!empty($_GET['id_indicador'])) 
    $id_indicador= $_GET['id_indicador'];

$obj= new Tregistro($clink);
$obj->SetYear($year);
$obj->SetMonth($month);
$obj->SetDay($day);

$obj->setIdIndicador($id_indicador);

$array= array();
$result_reg= $obj->listar_reg_plan($array);
$n= 0;
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>         

    <div class="container-fluid">
        <div class="card card-primary">
            <div class="card-header win-drag">
                <div class="row">
                    <div class="panel-title col-11 m-0 win-drag">
                        REGISTROS. Anteriores a la fecha <?="{$array['day']}/{$array['month']}/{$array['year']}"?>
                    </div>
                    <div class="col-1 m-0">
                        <div class="close">
                            <a href= "javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>                                
                        </div>
                    </div>              
                </div>
            </div>
            <div class="card-body">
   
                <table id="table" class="table table-striped" 
                       data-toggle="table"
                       data-height="420"
                       data-search="true"
                       data-show-columns="true"> 

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>PLAN</th>
                            <th>ACUMULADO</th>
                            <!--
                            <th>REGISTRO</th>
                            -->
                            <th>CORTE</th>
                            <th>FECHA Y HORA</th>
                            <th>USUARIO</th>
                            <th>OBSERVACIÓN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 0;
                        while ($row = $clink->fetch_array($result_reg)) { 
                            if ($row['id_usuario'] == _USER_SYSTEM)
                                continue;                            
                        ?>
                            <tr>
                                <td><?=++$i?></td>
                                <td>
                                    <?php
                                    if (!empty($row['plan_cot']))
                                        echo $row['plan_cot'] . " <strong>[..]</strong> ";
                                    echo $row['plan'];
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($row['acumulado_plan_cot']))
                                        echo $row['acumulado_plan_cot'] . " <strong>[..]</strong> ";
                                    echo $row['acumulado_plan'];
                                    ?>
                                </td>
                                <!--
                                <td>
                                    <?= odbc2time_ampm($row['reg_date']) ?>
                                </td>
                                -->                                
                                <td>
                                    <?= "{$row['day']}/{$row['month']}/{$row['year']}" ?>
                                </td>
                                <td>
                                    <?= odbc2time_ampm($row['reg_date']) ?>
                                </td>                                
                                <td>
                                    <?php 
                                    $nombre= textparse($row['nombre']);
                                    if (!empty($row['cargo']))
                                        $nombre.= ", ". textparse($row['cargo']);
                                    echo $nombre;
                                    ?>
                                </td>
                                <td>
                                    <?= textparse($row['observacion']) ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                
                <div class="btn-block btn-app">
                    <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
                </div>     
                
        </div>
    </div>            


