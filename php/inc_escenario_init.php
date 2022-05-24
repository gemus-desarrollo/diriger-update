<?php

/**
 * @author muste
 * @copyright 2012
 */

require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/peso.class.php";
require_once "../php/class/code.class.php";

require_once "../php/class/badger.class.php";

$time= new TTime();

$actual_year= $time->GetYear();
$actual_month= $time->GetMonth();
$actual_day= $time->GetDay();

$year= !empty($_GET['year']) ? $_GET['year'] : $actual_year;
$month= !empty($_GET['month']) ? $_GET['month'] : 0;

$all_year= false;
if ($month == 13)
    $all_year= true;

if (empty($month) || $month == -1) {
    if ($year == $actual_year)
        $month= $actual_month;
    if ($year > $actual_year)
        $month= 1;
    if ($year < $actual_year)
        $month= 12;
}

if ($year == $actual_year)
    $end_month= $actual_month;
else
    $end_month= 12;

$time->SetYear($year);
$time->SetMonth($month);
$lastday= $time->longmonth();

if (!empty($_GET['day']))
    $day= $_GET['day'];
else {
    if ($month != $actual_month || $year != $actual_year)
        $day= $lastday;
    else
        $day= $actual_day;
}

if ($day > $lastday || $month < $actual_month)
    $end_day= $lastday;
else if (($month != $actual_month && $year == $actual_year) || $year != $actual_year)
    $end_day= $lastday;
else if ($day < $actual_day && ($month == $actual_month && $year == $actual_year))
    $end_day= $actual_day;
else $end_day= $day;

$_SESSION['current_year']= $year;
$_SESSION['current_month']= $month;
$_SESSION['current_day']= $day;

if (empty($id_proceso)) {
    $id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
    if (empty($id_proceso) && $force_user_process)
        $id_proceso= $_SESSION['usuario_proceso_id'];
    if (empty($id_proceso))
        $id_proceso= $_SESSION['current_id_proceso'];
}

$obj_prs= new Tproceso($clink);

$tipo= null;
$conectado= null;
$id_proceso_code= null;

if (!empty($id_proceso) && $id_proceso != -1) {
    $obj_prs->Set($id_proceso);
    $tipo= $obj_prs->GetTipo();
    $conectado= $obj_prs->GetConectado();
    $id_proceso_code= $obj_prs->get_id_proceso_code();
}

$obj_esc= new Tescenario($clink);
$obj_esc->get_init_fin_year();
$inicio= (int)$obj_esc->GetInicio();
$fin= (int)$obj_esc->GetFin();

if (empty($_GET['year'])) {
    if (!empty($fin))
        if ($year >= $fin)
            $year= $fin;
    if (!empty($inicio))
        if ($year <= $inicio)
            $year= $inicio;
}

//if (empty($inicio) && empty($fin)) {
    if ($fin < $actual_year)
        $fin= $actual_year + 5;
    else
        $year + 5;

    $inicio= $year - 5;
//}

$obj_peso= new Tpeso($clink);
if (!empty($id_escenario)) {
    $obj_peso->SetIdEscenario($id_escenario);
    $obj_peso->set_id_escenario_code($id_escenario_code);
}

if (!empty($id_proceso_code)) {
    $obj_peso->SetIdProceso($id_proceso);
    $obj_peso->set_id_proceso_code($id_proceso_code);
}

$obj_peso->SetYear($year);
$obj_peso->SetMonth($month);

?>