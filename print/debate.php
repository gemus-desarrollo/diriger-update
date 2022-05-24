<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";

require_once "../php/class/time.class.php";

require_once "../php/class/base.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/tipo_reunion.class.php";
require_once "../php/class/evento.class.php";
require_once "../php/class/tematica.class.php";
require_once "../php/class/debate.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$time= new TTime();

$year= $_GET['year'];
$month= $_GET['month'];
$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : null;
$id_proceso= $_GET['id_proceso'];

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$proceso= $obj_prs->GetNombre();
unset($obj_prs);

$obj_event= new Tevento($clink);
$obj_event->Set($id_evento);

$id_tipo_reunion= $obj_event->GetIdTipo_reunion();

$obj_meeting= new Ttipo_reunion($clink);

if (!empty($id_tipo_reunion)) {
    $obj_meeting->SetIdTipo_reunion($id_tipo_reunion);
    $obj_meeting->Set();
    $meeting= $obj_meeting->GetNombre();
}

$meeting= !empty($id_tipo_reunion) ? $meeting : $obj_event->GetNombre();
$meeting= strtoupper($meeting);

$fecha_inicio_plan= $obj_event->GetFechaInicioPlan();

$fecha= DateTime_object($fecha_inicio_plan);
$year= $fecha['y'];
$month= $fecha['m'];

$obj_user= new Tusuario($clink);

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "ORDEN DEL DÍA y DEBATES", "Corresponde a $fecha_inicio_plan");
?>

<html>
    <head>
        <title>ORDEN DEL DÍA y DEBATES</title>

        <?php require "inc/print_top.inc.php";?>

        <div class="container-fluid center">
            <div align="center" class="title-header">
                <div align="center" style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
                    DEBATE<br/>
                </div>
            </div>
        </div>        

        <div style="margin-left: 30px;">
            <strong>Dia: </strong><?= $dayNames[(int) $fecha['n']] . ' ' . $fecha['d'] . ', ' . $year; ?><br />
            <strong>Lugar: </strong> <?= $obj_event->GetLugar() ?><br />
            <strong>Hora: </strong> <?= odbc2ampm($obj_event->GetFechaInicioPlan()) ?><br />
            <br />
        </div>

        <div class="container-fluid center">
            <?php
            $obj= new Ttematica($clink);
            $obj->SetIdEvento($id_evento);
            $obj->SetIdProceso($id_proceso);

            $obj->SetIdResponsable(null);
            $obj->SetIdUsuario(null);
            $obj->SetDay(null);
            $obj->SetMonth($month);
            $obj->SetYear($year);

            $result= $obj->listar(false);

            $obj_deb= new Tdebate($clink);
            $obj_deb->SetIdEvento($id_evento);

            $i = 0;
            while ($row= $clink->fetch_array($result)) {
                ++$i;
            ?>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1"><?=$row['numero']?></p>
                        <small><?=odbc2ampm($row['fecha_inicio_plan'])?></small>
                        </div>
                        <h4 class="mb-1" style="text-align: left;"><?=textparse($row['descripcion'])?></h4 >
                    </a>

                    <?php
                    $obj_deb->SetIdTematica($row['_id']);
                    $result_deb= $obj_deb->listar();

                    while ($row_deb= $clink->fetch_array($result_deb)) {
                        $email= $obj_user->GetEmail($row_deb['_id_usuario']);
                        $usuario= $email['nombre'];
                        $usuario.= !empty($email['cargo']) ? " {$email['cargo']}" : null;
                    ?>
                        <a href="#" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                            <div class="mb-1"><?=$usuario?></div>
                            <small class="text-muted"><?=odbc2ampm($row_deb['hora'])?></small>
                            </div>
                            <div class="mb-1" style="text-align: left;"><?=textparse($row_deb['observacion'])?></div>
                        </a>
                    
                <?php } ?>        
            </div>
            <br/><br/>
            <?php } ?>
        </div>            
           
    <?php require "inc/print_bottom.inc.php";?>

