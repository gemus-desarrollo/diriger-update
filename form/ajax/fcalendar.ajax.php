<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/grupo.class.php";
require_once "../../php/class/orgtarea.class.php";

require_once "../../php/class/evento.class.php";
require_once "../../php/class/tarea.class.php";
require_once "../../php/class/auditoria.class.php";

$time= new TTime();
$_year= $time->GetYear();

$id= !empty($_GET['id']) ? $_GET['id'] : null;
$table= $_GET['table'];
$fecha_inicio= !empty($_GET['fecha_inicio']) ? time2odbc(urldecode($_GET['fecha_inicio'])) : "{$_year}-01-01";
$fecha_fin= !empty($_GET['fecha_fin']) ? time2odbc(urldecode($_GET['fecha_fin'])) : "{$_year}-12-31";
$periodicidad= !is_null($_GET['periodicidad']) ? $_GET['periodicidad'] : 0;

$init_year= (int)date('Y', strtotime($fecha_inicio));
$init_month= (int)date('m', strtotime($fecha_inicio));

$end_year= (int)date('Y', strtotime($fecha_fin));
$end_month= (int)date('m', strtotime($fecha_fin));

if ($table == 'teventos')
    $obj= new Tevento($clink);
if ($table == 'ttareas')
    $obj= new Ttarea($clink);
if ($table == 'tauditorias')
    $obj= new Tauditoria($clink);
?>


    <div class="calendar">
        <?php for ($_year= $init_year; $_year <= $end_year; ++$_year) { ?>
        <div id="calendar-year-<?=$_year?>" class="months-container row col-12">
            <?php
            for ($im= 1; $im < 13; $im++) {
                if ($_year <= $init_year && $im < $init_month)
                    continue;
                if ($_year >= $end_year && $im > $end_month)
                    continue;

                $weekday= 0;
                $time->SetYear($_year);
                $time->SetMonth($im);
                $mm = str_pad($im, 2, '0', STR_PAD_LEFT);
            ?>
                <div class="month-container">
                    <table class="month table-calendar">
                        <thead>
                            <tr>
                                <th class="month-title" colspan="7">
                                    <?=$meses_array[$im]?> <span style="font-weight: normal;">/ <?=$_year?></span>
                                </th>
                            </tr>
                            <tr>
                                <th class="day-header">Lu</th>
                                <th class="day-header">Ma</th>
                                <th class="day-header">Mi</th>
                                <th class="day-header">Ju</th>
                                <th class="day-header">Vi</th>
                                <th class="day-header free">Sa</th>
                                <th class="day-header free">Do</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <?php
                                $d= 1;
                                $time->SetDay($d);
                                $firstday = $time->weekDay();
                                $lastday = $time->longmonth();

                                for ($i= 1; $i < $firstday && $i < 8; ++$i) {
                                ?>
                                   <td class="day new"></td>
                                <?php
                                }

                                for ($i= $firstday; $i < 8; $i++) {

                                ?>
                                   <td id="td-<?="{$_year}-{$im}-{$d}"?>" class="day" onclick='selectday(this,<?="{$d},{$im},{$_year}"?>,<?=$i?>)'>
                                       <div class="day-content"><?=$d?></div>
                                        <?php ++$d ?>
                                   </td>
                                <?php
                                }
                            ?>
                            </tr>

                            <?php
                            $col= 1;
                            for ($i= $d; $i <= $lastday; ++$i) {
                                if ($col == 1) {
                                ?>
                                    <tr>
                                <?php  } ?>
                                    <td id="td-<?="{$_year}-{$im}-{$d}"?>" class="day" onclick='selectday(this,<?="{$d},{$im},{$_year}"?>,<?=$col?>)'>
                                        <div class="day-content"><?=$d?></div>
                                        <?php ++$d ?>
                                    </td>
                                <?php
                                ++$col;
                                if ($col > 7) {
                                    $col= 1;
                                ?>
                                    </tr>
                            <?php
                                }
                            }
                            if ($col < 7) {
                                for ($i= $col; $i < 8; $i++) {
                            ?>
                                    <td class="day new"></td>
                            <?php
                                }
                            ?>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>


    <div class="btn-bar row col-12 button-float-bottom">
        <div class="btn-block btn-app center-block">
            <button class="btn btn-danger" onclick="unmark_all_days()">Desmarcar Todos</button>
            <button class="btn btn-success" onclick="HideContent('div-panel-calendar')">Aceptar</button>
        </div>
    </div>

    <input type="hidden" id="y_m_d" value="" />

    <script language="javascript" type="text/javascript">
        edit_control= false;
        fix_td_calendar();
        select_freq(<?=$periodicidad?>,true);
        edit_control= true;
    </script>