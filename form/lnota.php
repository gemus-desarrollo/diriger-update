<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/time.class.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/register_nota.class.php";
require_once "../php/class/nota.class.php";
require_once "../php/class/plan_ci.class.php";

$signal= 'lnota';

$id_redirect= 'ok';
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') 
    $action= 'edit';

if (!empty($_GET['id_redirect'])) 
    $id_redirect = $_GET['id_redirect'];

if (($action == 'list' || $action == 'edit') && $id_redirect == 'ok') {
    if (isset($_SESSION['obj'])) 
        unset($_SESSION['obj']);
}

$obj= new Tnota($clink);

$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;
$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['id_entity'];
$tipo= !empty($_GET['tipo']) ? $_GET['tipo'] : null;

$noconf=!is_null($_GET['noconf']) ? $_GET['noconf'] : 0;
$mej= !is_null($_GET['mej']) ? $_GET['mej'] : 0;
$observ= !is_null($_GET['observ']) ? $_GET['observ'] : 0;

$inicio= $year -3;
$fin= $year;

if (empty($noconf) && empty($mej) && empty($observ)) {
    $noconf= 1;
    $mej= 1;
    $observ= 1;
}

$obj->SetTipo($tipo);
$obj->SetIdProceso($id_proceso);


$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$_connect= $obj_prs->GetConectado();
$nombre_prs= $obj_prs->GetNombre();
$nombre_prs.= ", ".$Ttipo_proceso_array[(int)$obj_prs->GetTipo()];

$url_page= "../form/lnota.php?signal=$signal&action=$action&menu=tablero&id_proceso=$id_proceso&year=$year";
$url_page.= "&noconf=$noconf&mej=$mej&observ=$observ";

set_page($url_page);
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE NOTAS</title>

    <?php require_once "../html/inc/_tablero_top_riesgo.inc.php" ?>

</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
        $title= "NOTAS DE HALLAZGOS";
        require_once "../form/inc/_riesgo_top_div.inc.php";
        ?>

    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value="<?= $action ?>" />
        <input type="hidden" name="menu" id="menu" value="nota" />
        <input type="hidden" id="show_all_notes" name="show_all_notes" value="<?=$show_all_notes?>" />

        <input type="hidden" name="id_auditoria" id="id_auditoria" value="0" />

        <div class="app-body container-fluid table fourbar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th rowspan="2">No.</th>
                        <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO && ($action == 'edit'|| $action == 'add')) { ?>
                        <th rowspan="2"></th>
                        <?php } ?>
                        <th rowspan="2">DESCRIPCIÓN</th>
                        <th rowspan="2">LUGAR</th>
                        <th rowspan="2">PROCESO</th>
                        <th rowspan="2">FECHA <br />DE DETECCIÓN</th>
                        <th rowspan="2">FECHA <br />DE CIERRE <br />PLANIFICADA</th>
                        <th colspan="2">ANÁLISIS DE CAUSAS / FACTIBILIDAD</th>
                    </tr>
                    <tr>
                        <th>FECHA</th>
                        <th>CAUSAS / FACTIBILIDAD</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $obj_prs = new Tproceso($clink);
                    $obj_causa= new Tregister_nota($clink);

                    $i = 0;
                    $obj->SetTipo(null);
                    $obj->SetIdProceso($id_proceso == $_SESSION['id_entity'] || $id_proceso == -1 ? null : $id_proceso);
                    $obj->SetIdEntity($_SESSION['id_entity']);
                    $obj->SetYear($year);
                    $result_nota = $obj->listar($noconf, $mej, $observ, true);

                    if ($obj->GetCantidad() > 0) {
                        while ($row= $clink->fetch_array($result_nota)) {
                            $obj_causa->SetIdNota($row['_id']);
                            $obj_causa->listar_causas(true);
                            $array_causas= $obj_causa->array_causas;
                            $cant= $obj_causa->GetCantidad();
                            $rowspan= $cant > 1 ? $cant : 1;
                    ?>
                    <tr>
                        <td rowspan="<?=$rowspan?>">
                            <?=++$i?>
                            <!-- <a name="<?= $row['_id'] ?>"></a> -->
                        </td>

                        <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO && ($action == 'edit'|| $action == 'add')) { ?>
                        <td rowspan="<?=$rowspan?>">
                            <a class="btn btn-warning btn-sm" href="javascript:#"
                                onclick="enviar_nota(<?= $row['_id'] ?>,'<?= $action ?>')">
                                <i class="fa fa-edit"></i>Editar
                            </a>
                            <a class="btn btn-danger btn-sm" href="javascript:#"
                                onclick="enviar_nota(<?= $row['_id'] ?>,'delete')">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                        </td>
                        <?php } ?>

                        <td rowspan="<?=$rowspan?>">
                            <?="({$Ttipo_nota_array[$row['tipo']]}) <br/>{$row['descripcion']}" ?>
                        </td>

                        <td rowspan="<?=$rowspan?>">
                            <?= purge_html($row['_lugar'])?>
                        </td>
                        <td rowspan="<?=$rowspan?>">
                            <?php
                                        $obj_prs->Set($row['_id_proceso']);
                                        $proceso= $obj_prs->GetNombre();
                                        $proceso.= ", ". $Ttipo_proceso_array[$obj_prs->GetTipo()];
                                        echo $proceso;
                                        ?>
                        </td>
                        <td rowspan="<?=$rowspan?>">
                            <?= odbc2date($row['fecha_inicio_real'])?>
                        </td>
                        <td rowspan="<?=$rowspan?>">
                            <?= odbc2date($row['fecha_fin_plan'])?>
                        </td>
                        <?php
                                    $j= 0;
                                    foreach ($array_causas as $causa) {
                                        ++$j;
                                    ?>
                        <?php if ($j > 1) { ?>
                    <tr>
                        <?php } ?>
                        <td>
                            <?= odbc2date($causa['fecha'])?>
                        </td>
                        <td>
                            <?= textparse($causa['descripcion'])?>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php if ($cant == 0) { ?>
                    <td></td>
                    <td></td>
                    </tr>
                    <?php } } ?>
                    <?php } else { ?>
                    <tr>
                        <td>&nbsp;</td>
                        <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO && ($action == 'edit'|| $action == 'add')) { ?>
                        <td>&nbsp;</td>
                        <?php } ?>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </form>



    <div id="div-ajax-panel" class="ajax-panel">

    </div>

    <!-- Panel2 -->
    <div id="div-filter" class="card card-primary ajax-panel" data-bind="draganddrop">
        <div class="card-header">
            <div class="row form-inline">
                <div class="panel-title win-drag col-md-10">FILTRADO DE NOTAS</div>
                <div class="col-1pull-right">
                    <div class="close">
                        <a href="javascript:CloseWindow('div-filter');" title="cerrar ventana">
                            <i class="fa fa-close"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="col-md-12">
                <label class="checkbox text">
                    <input type="checkbox" name="noconf" id="noconf" value="1"
                        <?php if (!empty($noconf)) echo "checked='checked'" ?> onchange="javascript:refreshp()" />
                    Mostrar las No-conformidades
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="observ" id="observ" value="1"
                        <?php if (!empty($observ)) echo "checked='checked'" ?> onchange="javascript:refreshp()" />
                    Mostrar las Observaciones
                </label>
                <label class="checkbox text">
                    <input type="checkbox" name="mej" id="mej" value="1"
                        <?php if (!empty($mej)) echo "checked='checked'" ?> onchange="javascript:refreshp()" />
                    Mostrar las Notas de Mejora
                </label>

                <hr />
                <!-- buttom -->
                <div id="_submit" class="btn-block btn-app">
                    <button class="btn btn-primary" type="button" onclick="refreshp()">Aceptar</button>
                    <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-filter')">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    <!--panel2 -->


</body>

</html>