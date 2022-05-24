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

$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'mapaestrategico';

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
if ($action == 'add') $action= 'edit';

require_once "../php/inc_escenario_init.php";

$tablero= !empty($_GET['tablero']) ? $_GET['tablero'] : 1;
$id_escenario= !empty($_GET['id_escenario']) ? $_GET['id_escenario'] : 0;

$url_page= "../html/mapaestrategico.php?signal=$signal&action=$action&menu=tablero";
$url_page.= "&id_proceso=$id_proceso&year=$year&id_escenario=$id_escenario&tablero=$tablero";

set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />

    <title>MAPAS EMPRESARIALES</title>

    <?php require '../form/inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>
  
    <link rel="stylesheet" href="../libs/windowmove/windowmove.css?version=" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../css/widget.css?version=">
    <script type="text/javascript" src="../js/widget.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version=" charset="utf-8"></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
    body {
        overflow: hidden;
    }

    a>div.thumbnail>img {
        max-width: 100% !important;
        max-height: 100% !important;
    }

    .thumbnail {
        width: 100px;
    }
    </style>

    <script language="javascript" type="text/javascript">
    function refreshp(tablero) {
        var id_proceso = $('#proceso').val();
        var id_escenario = $('#escenario').val();
        var action = $('#exect').val();

        self.location.href = 'mapaestrategico.php?id_proceso=' + id_proceso + '&action=' + action + '&id_escenario=' +
            id_escenario + '&tablero=' + tablero;
    }

    function edit(action) {
        var id_escenario = $('#escenario').val();
        var action = $('#exect').val();

        var url = '../php/escenario.interface.php?version=&action=' + action;
        url += '&signal=mapaestrategico&id=' + id_escenario + '&menu=escenario';

        self.location.href = url;
    }

    function imprimir(id) {
        var id_proceso = $('#proceso').val();
        var id_escenario = $('#escenario').val();

        var url = '../print/mapaestrategico.php';
        url += '?id_proceso=' + id_proceso + '&id_escenario=' + id_escenario + '&tablero=' + id;

        show_imprimir(url, "IMPRIMIENDO MAPAS EMPRESARIALES",
            "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function closep() {
        var year = $('#year').val();
        self.location.href = '../form/lescenario.php?version=&year=' + year;
    }
    </script>

    <script type="text/javascript">
    function _dropdown_prs(id) {
        $('#proceso').val(id);
        refreshp($('#tablero').val());
    }

    function _dropdown_esc(id) {
        $('#escenario').val(id);
        refreshp($('#tablero').val());
    }

    function _dropdown_year(year) {
        $('#year').val(year);
        refreshp($('#tablero').val());
    }
    $(document).ready(function() {
        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <?php
        $obj_prs= new Tproceso($clink);
        $obj_prs->SetIdProceso($id_proceso);
        $obj_prs->Set();
        $nombre= $obj_prs->GetNombre();

        $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
        $obj_prs->SetTipo($_SESSION['local_proceso_tipo']);
        $obj_prs->SetIdResponsable(null);

        switch($tablero) {
            case 1: $_signal = 'strat';
                $title = "MAPA ESTRATÉGICO";
                break;
            case 2: $_signal = 'proc';
                $title = "MAPA DE PROCESOS";
                break;
            case 3: $_signal = 'org';
                $title = "ORGANIGRAMA FUNCIONAL";
                break;
        }
        ?>

    <!-- Docs master nav -->


    <div id="navbar-secondary" class="row app-nav d-none d-md-block">

        <nav class="navd-content">
            <a href="#" class="navd-header">MAPAS ESTRATÉGICOS</a>

            <div class="navd-menu" id="navbarSecondary">
                <ul class="navbar-nav mr-auto">
                    <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO) { ?>
                    <li>
                        <a href="#" class="" onclick="edit()" title="Editar Mapa">
                            <i class="fa fa-edit"></i>Editar
                        </a>
                    </li>
                    <?php } ?>

                    <?php
                        $use_select_year= true;
                        $id_select_prs= $id_proceso;
                        $restrict_prs= array(_TIPO_ARC, _TIPO_DEPARTAMENTO, _TIPO_PROCESO_INTERNO, _TIPO_GRUPO);

                        require "../form/inc/_dropdown_prs.inc.php";
                       ?>


                    <li class="navd-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                            <i class="fa fa-industry"></i>Escenarios<b class="caret"></b>
                        </a>

                        <ul class="dropdown-menu mega-menu">
                            <?php
                            $obj_esc= new Tescenario($clink);
                            $obj_esc->SetIdProceso($id_proceso);

                            $result= $obj_esc->listar();
                            while ($row= $clink->fetch_array($result)) {
                                if (empty($id_escenario))
                                    $id_escenario= $id_escenario= $row['_id'];
                                $escenario= "{$row['proceso']} {$row['inicio']} / {$row['fin']}";
                            ?>
                            <li>
                                <a href="#" class="<?php if ($id_escenario == $row['_id']) echo "active"?>"
                                    onclick="_dropdown_esc(<?=$row['_id']?>)"
                                    onmouseover="Tip(<?= textparse($row['descripcion'])?>)" onmouseout="UnTip()">
                                    <img class="img-rounded icon"
                                        src='<?=_SERVER_DIRIGER?>img/<?=img_process($row['tipo'])?>'
                                        title='<?=$Ttipo_proceso_array[$row['tipo']]?>' />
                                    <?=$escenario?>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>

                        <input type="hidden" id="escenario" name="escenario" value="<?=$id_escenario?>" />
                    </li>


                    <?php
                        $use_select_year= true;
                        $use_select_month= false;
                        $use_select_day= false;
                        $inicio= $year - 10;
                        $fin= $year + 10;
                        require "../form/inc/_dropdown_date.inc.php";
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
                            <a href="#" onclick="open_help_window('../help/06_escenario.htm#06_8')">
                                <i class="fa fa-question"></i>Ayuda
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>


    <div id="navbar-third" class="row app-nav d-none d-md-block">
        <nav class="navd-content">
            <ul class="navd-static d-flex flex-row list-unstyled p-2 row col-12">
                <li class="col-3">
                    <label class="badge badge-success">
                        <?=$esc_inicio?> - <?=$esc_fin?>
                    </label>
                </li>

                <li class="col-auto">
                    <div>
                        <label class="badge badge-danger">
                            <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?><i
                                class="fa fa-wifi"></i><?php } ?>
                            <?=$nombre?>
                        </label>
                    </div>
                </li>
            
                <?php if ($signal != 'mapaestrategico') { ?>
                <li>
                    <a href="#" onclick="closep()">
                        <i class="fa fa-close"></i>Cerrar
                    </a>
                </li>
                <?php } ?>
            </ul>
        </nav>
    </div>


    <?php
        $obj= new Tescenario($clink);

        if (!empty($id_escenario)) {
            $obj->Set($id_escenario);
        }
        ?>

    <input type="hidden" name="exect" id="exect" value="<?=$action?>" />
    <input type="hidden" id="tablero" name="tablero" value="<?=$tablero?>" />
    <input type="hidden" id="escenario" name="escenario" value="<?=$id_escenario?>" />

    <div class="app-body container-fluid twobar" style="padding-top: 20px;">
        <div class="row col-12">

            <div class="col-9">

                <div class="card card-primary">
                    <div class="card-header"><?= $title ?></div>

                    <div class="card-body">

                        <nav style="margin-bottom: 10px;">
                            <ul class="nav nav-tabs" role="tablist">
                                <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Imagen</a></li>
                                <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Descripción</a></li>
                            </ul>
                        </nav>

                        <div class="tabcontent" id="tab1">
                            <img class="img-fluid" id="img"
                                src="../php/image.interface.php?menu=escenario&signal=<?= $_signal ?>&id=<?= $id_escenario ?>"
                                border="0" />
                        </div>

                        <div class="tabcontent" id="tab2">
                            <?php
                             switch ($tablero) {
                                case 1:
                                    $observacion = $obj->GetDescripcion();
                                    break;
                                case 2:
                                    $observacion = $obj->get_observacion('proc');
                                    break;
                                case 3:
                                    $observacion = $obj->get_observacion('org');
                                    break;
                            }
                            ?>

                            <p><?=$title?></p>
                            <p><?=$nombre?></p>
                            <p><?=$observacion?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-3">
                <div class="jumbotron sidebar-nav">
                    <ul class="nav">
                        <li>
                            <a href="#" onclick="refreshp(1)">
                                <label>MAPA ESTRATÉGICO</label>
                                <br />
                                <?php if (!is_null($obj->GetImage('strat'))) { ?>
                                <div class="thumbnail">
                                    <img class="img-fluid" id="img<?= $id_escenario ?>_1"
                                        src="../php/image.interface.php?menu=escenario&signal=strat&id=<?= $id_escenario ?>" />
                                </div>
                                <?php } ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="refreshp(2)">
                                <label>MAPA DE PROCESOS INTERNOS</label>
                                <br />
                                <?php if (!is_null($obj->GetImage('proc'))) {?>
                                <div class="thumbnail">
                                    <img class="img-fluid" id="img<?=$id_escenario?>_2"
                                        src="../php/image.interface.php?menu=escenario&signal=proc&id=<?=$id_escenario?>" />
                                </div>
                                <?php } ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="refreshp(3)">
                                <label>ORGANIGRAMA FUNCIONAL</label>
                                <br />
                                <?php if (!is_null($obj->GetImage('org'))) { ?>
                                <div class="thumbnail">
                                    <img class="img-fluid" id="img<?= $id_escenario ?>_3"
                                        src="../php/image.interface.php?menu=escenario&signal=org&id=<?= $id_escenario?>" />
                                </div>
                                <?php } ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <!--/.well -->
            </div>
        </div>

    </div>
</body>

</html>