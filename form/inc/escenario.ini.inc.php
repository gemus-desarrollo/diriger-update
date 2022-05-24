<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 9/6/2015
 * Time: 10:58 a.m.
 */

$additive_prs= ($conectado == _NO_LOCAL || ($conectado != _NO_LOCAL && $id_proceso == $_SESSION['local_proceso_id'])) ? true : false;

$obj_esc= new Tescenario($clink);
$obj_esc->get_init_fin_year();
$_inicio= $obj_esc->GetInicio();
$_fin= $obj_esc->GetFin();

?>