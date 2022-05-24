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
require_once "../php/class/proceso.class.php";

require_once "../php/class/traza.class.php";

$month= $_GET['month'];
$year= $_GET['year'];

$id_proceso= $_GET['id_proceso'];
$user_date_ref= $_GET['user_date_ref'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;	

$obj= new Tusuario($clink);
$obj->SetIdProceso($id_proceso > 0 ? $id_proceso  : null);
$obj->set_user_date_ref($user_date_ref);

$user_show_reject= !empty($_GET['user_show_reject']) ? $_GET['user_show_reject'] : 0;

if (empty($_id_proceso) || $_id_proceso == -1)
    $_id_proceso= null;
$obj->SetIdProceso($_id_proceso);

$obj->set_user_date_ref(null, true);
$result = empty($_id_proceso) && $_SESSION['id_entity'] == $_SESSION['local_proceso_id'] ? $obj->listar_all(null, null, 1) : $obj->listar(null, null, _LOCAL, 1);

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($id_proceso > 0 ? $id_proceso  : $_SESSION['local_proceso_id']);
    $obj_prs->Set();

    $proceso= $obj_prs->GetNombre();
    $tipo_prs= $obj_prs->GetTipo();	
}

$obj_traza= new Ttraza($clink);
$obj_traza->SetYear(date('Y'));
$obj_traza->SetIdProceso($id_proceso);
$obj_traza->add("IMPRIMIR", "LISTA DE USUARIOS");
?>

<html>

<head>
    <title>LISTA DE USUARIOS</title>

    <?php require "inc/print_top.inc.php";?>

    <div class="container-fluid center">
        <div align="center"
            style="width: 80%; text-align: center; margin: 20px; font-weight: bolder; font-size: 1.2em;">
            RELACIÓN DE USUARIOS <br /><?= $meses_array[(int)$month]?>/<?= $year?>
        </div>
    </div>

    <div class="page center">
        <table cellpadding="0" cellspacing=0>
            <thead>
                <tr>
                    <th class="plhead left" style="min-width:30px">No.</th>
                    <th class="plhead" style="min-width:130px">NOMBRE Y APELLIDOS</th>
                    <th class="plhead" style="min-width:130px">CARGO</th>
                    <th class="plhead" style="min-width:70px">USUARIO</th>
                    <th class="plhead" style="min-width:80px">NIVEL</th>
                    <th class="plhead" style="min-width:100px">E-correo</th>
                    <th class="plhead" style="min-width:70px">C.I.</th>
                    <th class="plhead">FIRMA</th>
                </tr>
            </thead>

            <tbody>
                <?php 
                $i= 0;
                while ($row= $clink->fetch_array($result)) {
                    if (!$user_show_reject && !empty($row['eliminado'])) 
                        continue;
                ?>

                <tr valign="top">
                    <td class="plinner left"><?= ++$i ?></td>
                    <td class="plinner"><?= $row['nombre'] ?></td>
                    <td class="plinner"><?=(!empty($row['cargo'])) ? $row['cargo'] : '&nbsp;'; ?></td>
                    <td class="plinner"><?= $row['usuario'] ?></td>

                    <td class="plinner"><?= $roles_array[$row['nivel']] ?></td>
                    <td class="plinner"><?= $row['email'] ?></td>
                    <td class="plinner"><?= $row['noIdentidad'] ?></td>
                    <td class="plinner"><img
                            src="<?=_SERVER_DIRIGER?>php/image.interface.php?menu=usuario&id=<?=$row['id']?>"
                            <?=$obj->GetDim($row['firma_param'])?> /></td>
                </tr>

                <?php  } ?>
            </tbody>
        </table>

    </div>

    <?php require "inc/print_bottom.inc.php";?>