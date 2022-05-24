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
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";

require_once "../php/class/badger.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : null;

if ($action == 'add')
    $action= 'edit';

if (($action == 'list' || $action == 'edit') && is_null($error)) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else
    $obj= new Tproceso($clink);

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];

$time= new TTime;
$current_year= $time->GetYear();

if (empty($year))
    $year= $current_year;

$inicio= $year - 3;
$fin= $current_year + 3;

$id_proceso= 0;
$tipo= null;
$id_proceso= !is_null($_GET['id_proceso']) ? $_GET['id_proceso'] : null;
$tipo= !is_null($_GET['tipo']) ? $_GET['tipo'] : null;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$url_page= "../form/lproceso.php?signal=$signal&action=$action&menu=proceso&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&exect=$action&tipo=$tipo";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>LISTADO DE UNIDADES O PROCESOS</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/table.css" />

    <link href="../libs/alert-panel/alert-panel.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/alert-panel/alert-panel.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language="javascript" type="text/javascript">
    function refreshp(index) {
        var id_proceso = $('#proceso').val();
        var tipo = $('#tipo').val();
        var year = $('#year').val();
        var action = $('#exect').val();

        if (index == 1)
            tipo = 0;

        var url = 'lproceso.php?version=&action=' + action + '&id_proceso=' + id_proceso +
            '&tipo=' + tipo;
        url += '&year=' + year;
        self.location = url;
    }

    function imprimir() {
        var id_proceso = $('#proceso').val();
        var tipo = $('#tipo').val();

        var url = '../print/lproceso.php?id_proceso=' + id_proceso + '&tipo=' + tipo;
        show_imprimir(url, "IMPRIMIENDO LISTADO DE PROCESOS",
            "width=800,height=400,toolbar=no,location=no, scrollbars=yes");
    }

    function add() {
        var year = $('#year').val();
        self.location.href = 'fproceso.php?version=&action=add&signal=proceso&year=' + year;
    }
    </script>

    <script type="text/javascript" charset="utf-8">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp(1);
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp(0);
    }

    function _dropdown_type(tipo) {
        $('#tipo').val(tipo);
        refreshp(0);
    }

    $(document).ready(function() {

    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <!-- Docs master nav -->
    <div id="navbar-secondary">
        <nav class="navd-content">
            <div class="navd-container">
                <div id="dismiss" class="dismiss">
                    <i class="fa fa-arrow-left"></i>
                </div>              
                <a href="#" class="navd-header">
                    PROCESOS O UNIDADES ORGANIZATIVAS
                </a>

                <div class="navd-menu" id="navbarSecondary">
                    <ul class="navbar-nav mr-auto">
                        <?php if ($action == 'add' || $action == 'edit') { ?>
                        <li class="d-none d-md-block">
                            <a href="#" class="" onclick="add()" title="nuevo proceso o Unidad Organizativa">
                                <i class="fa fa-plus"></i>Agregar
                            </a>
                        </li>
                        <?php } ?>

                        <li class="navd-dropdown">
                            <a class="dropdown-toggle" href="#navbarOpciones" data-toggle="collapse" aria-expanded="false">
                                <i class="fa fa-filter"></i>Filtrado<b class="caret"></b>
                            </a>

                            <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>" />

                            <ul class="navd-dropdown-menu" id="navbarOpciones">

                                <li class="navd-dropdown">
                                    <!--
                                    <a class="dropdown-toggle" href="#" id="navbarDropdown" role="button"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-industry"></i>Proceso Superior
                                    </a>

                                    <ul class="navd-dropdown-menu" id="navbarOpciones">
                                        <li>
                                            <a href="#"
                                                class="tooltip-viewport-left <?php if ($id_proceso == 0) echo "active" ?>"
                                                onclick="_dropdown_prs(0)" title="Todos">
                                                Cualquiera ...
                                            </a>
                                        </li>
                                        <?php
                                        /*
                                        $top_list_option = "Todos......";
                                        $id_list_prs = null;
                                        $tipo_list_prs = null;
                                        $order_list_prs = 'eq_asc_desc';
                                        $reject_connected = false;
                                        $id_select_prs = $id_proceso;

                                        $obj_prs= new Tproceso($clink);
                                        $obj_prs->SetIdEntity($_SESSION['id_entity']);
                                        $result_prs= $obj_prs->listar_in_order($order_list_prs, true, $_restrict_prs);
                                        */
                                        foreach ($result_prs as $row) {
                                            if (isset($obj_prs_tmp)) unset($obj_prs_tmp);
                                            $obj_prs_tmp= new Tproceso($clink);

                                            if (!empty($row['id_proceso'])) 
                                                $obj_prs_tmp->Set($row['id_proceso']);
                                            $proceso_sup= $img_conectdo."<br />";
                                            $proceso_sup.= "<strong>Tipo:</strong> ".$Ttipo_proceso_array[$row['tipo']].'<br />';
                                            if (!empty($row['id_proceso'])) 
                                                $proceso_sup.= "<strong>Subordinada a:</strong> ".$obj_prs_tmp= $obj_prs_tmp->GetNombre(). ", <em class=\'tooltip_em\'>".$Ttipo_proceso_array[$obj_prs_tmp->GetTipo()]."</em>";
                                            $proceso_sup.= "<br /><strong>Tipo de Conexion:</strong> ".$Ttipo_conexion_array[$row['conectado']];
                                            $proceso= $row['_nombre'].", <span class='tooltip_em'>".$Ttipo_proceso_array[$row['tipo']]."</span>";
                                        ?>

                                            <li>
                                                <a href="#" class="<?php if ($id_select_prs == $row['id']) echo "active"?>"
                                                    onclick="_dropdown_prs(<?=$row['id']?>)"
                                                    onmouseover="Tip('<?=$proceso_sup?>')" onmouseout="UnTip()">
                                                    <img class="img-rounded icon" src='../img/<?=img_process($row['tipo'])?>'
                                                        title='<?=$Ttipo_proceso_array[$row['tipo']]?>' />
                                                    <?=$proceso?>
                                                </a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                                -->

                                <li class="navd-dropdown">
                                    <a class="tooltip-viewport-leftdropdown-toggle" href="#" id="navbarDropdown"
                                        role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <img src="../img/empresa.ico" class="icon" />Tipo
                                    </a>
                                    <input type="hidden" id="tipo" name="tipo" value="<?=$tipo?>" />

                                    <ul class="navd-dropdown-menu" id="navbarOpciones">
                                        <li>
                                            <a href="#" class="tooltip-viewport-left <?php if ($tipo == 0) echo "active" ?>"
                                                onclick="_dropdown_prs(0)" title="Todos">
                                                Todos ...
                                            </a>
                                        </li>
                                        <?php for ($i= 1; $i <= _MAX_TIPO_PROCESO; ++$i) {  ?>
                                        <li>
                                            <a href="#" class="tooltip-viewport-left <?php if ($tipo == $i) echo "active"?>"
                                                onclick="_dropdown_type(<?=$i?>)">
                                                <img class="img-rounded icon" src='../img/<?=img_process($i)?>'
                                                    title='<?=$Ttipo_proceso_array[$i]?>' />
                                                <?=$Ttipo_proceso_array[$i]?>
                                            </a>
                                        </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            </ul>
                        </li>

                        <?php
                        $use_select_year= true;
                        $use_select_month= false;
                        require "inc/_dropdown_date.inc.php";
                        ?>

                        <li class="d-none d-lg-block">
                            <a href="#" class="" onclick="imprimir()">
                                <i class="fa fa-print"></i>Imprimir
                            </a>
                        </li>
                    </ul>

                    <div class="navd-end">
                        <ul class="navbar-nav mr-auto">
                            <li>
                                <a href="#" onclick="open_help_window('../help/03_procesos.htm#03_5')">
                                    <i class="fa fa-question"></i>Ayuda
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>    
        </nav>
    </div>

    <div id="navbar-third" class="row app-nav d-none d-md-block d-none d-lg-block">
        <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
            <li class="col">
                <label class="badge badge-danger">
                    <?=$year?>
                </label>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label ml-3">Muestra:</label>
                    <div id="nshow" class="badge badge-warning"></div>
                </div>
            </li>

            <li class="col">
                <div class="row">
                    <label class="label ml-3">Ocultos:</label>
                    <div id="nhide" class="badge badge-warning"></div>
                </div>
            </li>                

            <li class="col-3">
                <?php
                if (!empty($id_proceso)) {
                    $obj_prs= new Tproceso($clink);
                    $obj_prs->Set($id_proceso);
                    $proceso_sup= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[(int)$obj_prs->GetTipo()];
                } else {
                    $proceso_sup= "Cualquiera ...";
                }
                ?>
                <div class="row">
                    <label class="label ml-3">Proceso superior:</label>
                    <label class="badge badge-success"><?=$proceso_sup?></label>
                </div>
            </li>

            <li class="col-3">
                <div class="row">
                    <label class="label ml-3">Tipo:</label>
                    <label class="badge badge-success">
                        <?=!empty($tipo) ? $Ttipo_proceso_array[$tipo] : "Todos ... "?>
                    </label>
                </div>
            </li>
        </ul>
    </div>


    <form action='javascript:' method=post>
        <input type="hidden" name="exect" id="exect" value='<?= $action ?>' />
        <input type="hidden" name="menu" id="menu" value="proceso" />
        <input type="hidden" name="year" id="year" value="<?=$year?>" />

        <div class="app-body container-fluid table twobar">
            <table id="table" class="table table-striped" data-toggle="table" data-search="true"
                data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <?php if ($action != 'list') { ?>
                        <th></th>
                        <?php } ?>
                        <th>UNIDAD</th>
                        <th>TIPO</th>
                        <th>ORGANO SUPERIOR</th>
                        <th>LUGAR</th>
                        <th>RESPONSABLE</th>
                        <th>CONEXIÓN</th>
                        <th>LAN / WAM</th>
                        <th>CÓDIGO</th>
                        <th>ARCHIVO</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $i = 0;
                    $obj->SetIdProceso(!empty($id_proceso) ? $id_proceso : $_SESSION['id_entity']);
                    $result= $obj->listar(null, "t2.id");

                    $cant_print_reject= 0;
                    $cant_show= 0;
                    while ($row = $clink->fetch_array($result)) {

                        if ($_SESSION['nivel'] != _GLOBALUSUARIO
                            && ((empty($row['id_entity']) && ($row['id'] != $_SESSION['id_entity'] && $row['id'] != $_SESSION['superior_entity_id']
                                                                && $row['id_proceso'] != $_SESSION['id_entity']))
                            || (!empty($row['id_entity']) && $row['id_entity'] != $_SESSION['id_entity'])))
                            continue;

                        if (($row['inicio'] > $year || $year > $row['fin'])
                            || (!empty($tipo) && $row['tipo'] != $tipo)) {
                            ++$cant_print_reject;
                            continue;
                        }

                        ++$cant_show;
                    ?>

                    <tr>
                        <td><?=++$i?></td>
                        <?php if ($action != 'list') { ?>
                        <td>
                            <?php    
                            $id_entity= $array_procesos_entity[$row['_id']]['id_entity'];

                            if ($_SESSION['nivel'] == _GLOBALUSUARIO 
                                || ($_SESSION['nivel'] >= _ADMINISTRADOR && ($_SESSION['id_entity'] == $id_entity || $_SESSION['id_entity'] == $row['_id']))) {                                    
                            ?>
                            <a class="btn btn-warning btn-sm" href="#"
                                onclick="enviar_proceso(<?= $row['_id'] ?>,'<?= $action ?>');">
                                <i class="fa fa-edit"></i>Editar
                            </a>
                            <a name="<?= $row['_id'] ?>"></a>

                            <?php if ($row['_id'] != $_SESSION['local_proceso_id'] && $row['_id'] != $_SESSION['id_entity']) { ?>
                            <a class="btn btn-danger btn-sm" href="#"
                                onclick="enviar_proceso(<?= $row['_id'] ?>,'delete')">
                                <i class="fa fa-trash"></i>Eliminar
                            </a>
                            <?php } } ?>

                        </td>
                        <?php } ?>

                        <td>
                            <?= $row['_nombre'] ?><br />
                            <?="{$row['inicio']} - {$row['fin']}"?>
                        </td>

                        <td>
                            <?php if ($row['if_entity']) { ?>
                            <img class="img-rounded ico" src="../img/entity.ico" title="Entidad" />
                            <?php } ?>
                            <img class="img-rounded ico"
                                src="../img/<?= img_process($row['tipo']) ?>" /><?= $Ttipo_proceso_array[$row['tipo']] ?>
                        </td>

                        <td>
                            <?php
                            $id_proceso_sup = $row['_id_proceso'];

                            if (!empty($id_proceso_sup)) {
                                $obj->Set($id_proceso_sup);
                                echo $obj->GetNombre() . ' <br/>' . $Ttipo_proceso_array[$obj->GetTipo()];
                            }
                            ?>
                        </td>
                        <td>
                            <?= $row['lugar'] ?>
                        </td>
                        <td>
                            <?= $row['responsable']?>
                            <?=!empty($row['cargo']) ? textparse($row['cargo']) : null?>
                        </td>
                        <td>
                            <?php
                                    $conectado = is_null($row['conectado']) ? 1 : $row['conectado'];
                                    echo $Ttipo_conexion_array[$conectado];
                                    ?>
                        </td>

                        <td>
                            <?php
                            if (!is_null($row['email']))
                                echo "E-CORREO: " . $row['email'];
                            if (!is_null($row['url']))
                                echo "<br/>URL: {$row['protocolo']}://{$row['url']}:{$row['puerto']}";
                            ?>
                        </td>

                        <td>
                            <?= $row['codigo'] ?>
                        </td>
                        <td>
                            <?=$row['codigo_archive']?>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <script type="text/javascript" language="JavaScript">
            document.getElementById('nshow').innerHTML = '<?=$cant_show?>';
            document.getElementById('nhide').innerHTML = '<?=$cant_print_reject?>';
            </script>

        </div>
    </form>
</body>

</html>