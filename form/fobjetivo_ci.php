<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/escenario.class.php";
require_once "../php/class/riesgo.class.php";
require_once "../php/class/objetivo_ci.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/peso.class.php";

require_once "../php/class/code.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$signal= !empty($_GET['signal'])?  $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'add') {
    if (isset($_SESSION['obj'])) unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tobjetivo_ci($clink);
}

$id_objetivo= $obj->GetIdObjetivo();
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

$time= new TTime;

$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= !empty($_GET['month']) ? $_GET['month'] : $_SESSION['current_month'];
$day= !empty($_GET['day']) ? $_GET['day'] : $_SESSION['current_day'];

if (empty($year)) 
    $year= $time->GetYear();

$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
$id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['current_proceso_id'];
$id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];

$id_proceso_code= get_code_from_table('tprocesos', $id_proceso);

$numero= !empty($_GET['numero']) ? $_GET['numero'] : $obj->GetNumero();
$if_send_up= !empty($_GET['if_send_up']) ? $_GET['if_send_up'] : $obj->GetIfSend_up();
$if_send_down= !empty($_GET['if_send_down']) ? $_GET['if_send_down'] : $obj->GetIfSend_down();

$nombre= urldecode($_GET['nombre']);
if (empty($nombre)) 
    $nombre= $obj->GetNombre();

$descripcion= urldecode($_GET['descripcion']);
if (empty($descripcion)) 
    $descripcion= $obj->GetDescripcion();

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();

if (empty($inicio)) 
    $inicio= $year;
if (empty($fin)) 
    $fin= $year;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);
$tipo= $obj_prs->GetTipo();
$conectado= $obj_prs->GetConectado();

require_once "inc/escenario.ini.inc.php";

/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;
global $badger;

$badger= new Tbadger($clink);
$badger->SetYear($year);
$badger->set_user_date_ref($fecha_inicio);
$badger->set_planrisk();

$url_page= "../$('#/fobjetivo_ci.php?signal=$signal&action=$action&menu=objetivo_ci&exect=$action";
$url_page.= "&id_proceso=$id_proceso&year=$year&month=$month&day=$day";

add_page($url_page, $action, 'f');

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>OBJETIVOS DE CONTROL INTERNO</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
    ================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link href="../libs/spinner-button/spinner-button.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/spinner-button/spinner-button.js"></script>

    <script type="text/javascript" charset="utf-8" src="../js/string.js"></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js"></script>

    <link rel="stylesheet" type="text/css" media="screen" href="../libs/multiselect/multiselect.css" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js"></script>

    <script type="text/javascript" src="../libs/tinymce/tinymce.min.js"></script>
    <script type="text/javascript" src="../libs/tinymce/jquery.tinymce.min.js"></script>

    <script type="text/javascript" src="../js/form.js"></script>

    <script language='javascript' type="text/javascript" charset="utf-8">
    function validar() {
        var text;

        if (parseInt($('#inicio').val()) > parseInt($('#fin').val())) {
            $('#inicio').focus(focusin($('#inicio')));
            alert(
                "El año de inicio de la vigencia del Objetivo de Control Interno no puede ser superior al año en que finaliza.");
            return;
        }
        if (parseInt($('#year').val()) < parseInt($('#inicio').val()) || parseInt($('#year').val()) > parseInt($('#fin')
                .val())) {
            $('#year').focus(focusin($('#year')));
            alert("El año de referencia tiene que estar entre los años de vigencia del Objetivo de Control Internoo.");
            return;
        }
        if ($('#numero').val() == 0) {
            $('#numero').focus(focusin($('#numero')));
            text = "No ha especificado un número para identificar este Objetivo de Control Interno. ";
            text += "De este número dependerá el orden al listarlo.";
            alert(text);
            return;
        }
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el titulo de objetivo de Control Interno');
            return;
        }

        var conectado = $('#proceso_conectado_' + $('#proceso').val()).val();
        conectado = conectado != <?=_NO_LOCAL?> ? true : false;

        if (conectado && (!$('#if_send_up').is(':checked') && !$('#if_send_down').is(':checked'))) {
            text = "No ha especificado la dirección en que migrará la información relativa a este Objetivo. ";
            text +=
                "Las direcciones superiores o subordinadas no recibirán información relativa a este Objetivo. ¿Desea continuar?";
            confirm(text, function(ok) {
                if (!ok)
                    return;
                else {
                    if (!this_1())
                        return;
                }
            });
        } else {
            if (!this_1())
                return;
        }

        function this_1() {
            if (!Entrada($('#descripcion').val())) {
                $('#descripcion').focus(focusin($('#descripcion')));

                text = "No ha descrito una estrategia para alcanzar el Objetivo propuesto. Esto no es obligatorio, ";
                text +=
                "pero si necesario si desea que el objetivo sea bien entendido por los demás. ¿Desea continuar?";
                confirm(text, function(ok) {
                    if (!ok)
                        return false;
                    else {
                        if (!this_2())
                            return false;
                    }
                });
            } else {
                if (!this_2())
                    return false;
            }
        }

        function this_2() {
            if (parseInt($('#cant_tab_prs').val()) == 0) {
                text = "Debe de especificar las Unidades Organizativas en cuyos Planes de Prevención desea que ";
                text += "aparezca el objetivo de Control Interno";
                alert(text);
                return false;
            }

            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            document.forms[0].action = '../php/objetivo.interface.php?if_control_interno=1';
            document.forms[0].submit();
        }
    }

    function refreshp() {
        var inicio = $('#inicio').val();
        var fin = $('#fin').val();
        var id_proceso = $('#proceso').val();
        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURIComponent($('#descripcion').val());
        var year = $('#year').val();
        var numero = $('#numero').val();
        var if_send_up = $('#if_send_up').is(':checked') ? 1 : 0;
        var if_send_down = $('#if_send_down').is(':checked') ? 1 : 0;

        var url = '&inicio=' + inicio + '&fin=' + fin + '&nombre=' + nombre + '&descripcion=' + descripcion +
            '&id_proceso=' + id_proceso;
        url += '&year=' + year + '&numero=' + numero + '&if_send_up=' + if_send_up + '&if_send_down=' + if_send_down;

        self.location = 'fobjetivo_ci.php?action=<?=$action?>' + url;
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        new BootstrapSpinnerButton('spinner-numero', <?=$numero ? $numero : 1?>, 255);

        function set_numero(val) {
            $('#numero').val(val);
        }

        tinymce.init({
            selector: '#descripcion',
            theme: 'modern',
            height: 300,
            language: 'es',
            plugins: [
                'advlist autolink lists link image charmap print preview anchor textcolor',
                'searchreplace visualblocks code fullscreen',
                'insertdatetime table paste code help wordcount'
            ],
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify ' +
                '| bullist numlist outdent indent | removeformat | help',

            content_css: '../css/content.css'
        });

        <?php if (!$_SESSION['if_send_up'] || $id_proceso != $_SESSION['id_entity']) { ?>
        $('#if_send_up').attr("disabled", "disabled");
        <?php } ?>
        <?php if (!$_SESSION['if_send_down'] || $id_proceso != $_SESSION['id_entity']) { ?>
        $('#if_send_down').attr("disabled", "disabled");
        <?php } ?>

        try {
            $('#descripcion').val(<?= json_encode($descripcion)?>);
        } catch (e) {
            ;
        }

        <?php if (!is_null($error)) { ?>
        alert("<?= str_replace("\n", " ", $error) ?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container-fluid">
            <div class="card card-primary">
                <div class="card-header">OBJETIVOS DE CONTROL INTERNO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item" title="Definiciones Generales"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item"
                            title="Descripción de la estrategia a seguir, u observaciones relativas al Objetivo de Control Interno">
                            <a class="nav-link" href="tab2">Descripción</a></li>
                        <li id="nav-tab3" class="nav-item"
                            title="Direcciones y/o Procesos Superiores a los que se les asocia el Objetivo">
                            <a class="nav-link" href="tab3">Planes de Prevención</a></li>
                        <li id="nav-tab4" class="nav-item" title=""><a class="nav-link" href="tab4">Tareas (Gestión de Riesgos)</a></li>
                    </ul>

                    <form class="form-horizontal" action='javascript:validar()' method="post">
                        <input type="hidden" name="exect" value="<?=$action?>" />
                        <input type="hidden" name="id" value="<?=$id_objetivo?>" />
                        <input type="hidden" name="menu" value="objetivo_ci" />

                        <input type="hidden" id="year" name="year" value="<?=$year?>" />
                        <input type="hidden" id="month" name="month" value="<?=$month?>" />
                        <input type="hidden" id="day" name="day" value="<?=$day?>" />

                        <input type="hidden" id="if_control_interno" name="if_control_interno" value="1" />
                        <input type="hidden" id="if_objsup" name="if_objsup" value="0" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-lg-2">
                                    Vigencia:
                                </label>
                                <label class="col-form-label col-md-2">
                                    Desde:
                                </label>
                                <div class=" col-md-2">
                                    <select name="inicio" id="inicio" class="form-control input-sm"
                                        onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $inicio) echo "selected='selected'"; ?>><?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <label class="col-form-label col-md-2">
                                    Hasta:
                                </label>
                                <div class=" col-md-2">
                                    <select name="fin" id="fin" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <label class="alert alert-info">
                                Periodo en el que será gestionable el Objetivo. Es el intervalo de años en el que esta
                                vigente y constituye una meta para la organización, por lo que es calculado o medible
                                por el sistema.
                            </label>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Año de referencia:
                                </label>
                                <div class=" col-md-2">
                                    <select name="year" id="year" class="form-control input-sm" onchange="refreshp()">
                                        <?php for ($i = $_inicio; $i <= $_fin; $i++) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ((int) $i == (int) $year) echo "selected='selected'"; ?>><?= $i ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class=" col-md-8">
                                    <label class="alert alert-info">
                                        Año para el cual son fijadas las ponderaciones del efecto del Objetivo sobre las
                                        Tareas.
                                    </label>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Objetivo No.:
                                </label>
                                <div class=" col-md-10">
                                    <div id="spinner-numero" class="input-group spinner">
                                        <input type="text" name="numero" id="numero" class="form-control"
                                            value="<?=$numero?>">
                                        <div class="input-group-btn-vertical">
                                            <button class="btn btn-default" type="button" data-bind="up">
                                                <i class="fa fa-arrow-up"></i>
                                            </button>
                                            <button class="btn btn-default" type="button" data-bind="down">
                                                <i class="fa fa-arrow-down"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Unidad Organizativa:
                                </label>
                                <div class=" col-md-10">
                                    <?php
                                    $top_list_option= "seleccione........";
                                    $id_list_prs= null;
                                    $order_list_prs= 'eq_desc';
                                    $reject_connected= false;
                                    $in_building= ($action == 'add' || $action == 'update') ? true : false;
                                    $only_additive_list_prs= ($action == 'add') ? true : false;
                                    $use_copy_tprocesos= true;

                                    $id_select_prs= $id_proceso;
                                    require_once "inc/_select_prs.inc.php";
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Objetivo:
                                </label>
                                <div class="col-md-10">
                                    <textarea id="nombre" name="nombre" class="form-control input-sm"
                                        rows="4"><?= $nombre ?></textarea>
                                </div>
                            </div>
                        </div><!-- generales -->

                        <!-- Descripcion -->
                        <div class="tabcontent" id="tab2">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="if_send_up" name="if_send_up" value="1"
                                        <?php if (!empty($if_send_up)) echo "checked='checked'" ?> />
                                    Transmitir este Control Interno a la Dirección Empresarial o Proceso superior. Será
                                    transmitido su estado de cumplimiento periódicamente.
                                </label>
                            </div>
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="if_send_down" name="if_send_down" value="1"
                                        <?php if (!empty($if_send_down)) echo "checked='checked'" ?> />
                                    Transmitir este Control Interno a las Direcciones Empresariales o Procesos
                                    subordinados. No será transmitido su estado de cumplimiento.
                                </label>
                            </div>

                            <hr>
                            </hr>
                            <div class="form-group row">
                                <div class="col-lg-12">
                                    <textarea name="descripcion" rows=15 id="descripcion"
                                        class="form-control input-sm"><?= $descripcion ?></textarea>
                                </div>
                            </div>
                        </div><!-- Descripcion -->

                        <!-- relacion de los procesos a los que pertenece -->
                        <div class="tabcontent" id="tab3">
                            <?php
                            $id= $id_objetivo;
                            $obj_prs= new Tproceso_item($clink);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));

                            $obj_prs->set_use_copy_tprocesos(true);
                            $result_prs_array= $obj_prs->listar_in_order("eq_desc", true, _TIPO_ARC);
                            $restrict_prs= array(_TIPO_ARC);
                            $restrict_up_prs= true;
                            $id_restrict_prs= $id_proceso;
                            
                            if (!empty($id_objetivo)) {
                                $obj_prs->SetIdObjetivo($id_objetivo);
                                $array_procesos= $obj_prs->getProcesoObjetivo();
                            }
                            $create_select_input= false;
                            $name_form= "fobjetivo_ci";
                            require "inc/proceso_tabs.inc.php";
                            ?>
                        </div> <!-- relacion de los procesos a los que pertenece -->


                        <!-- tareas -->
                        <div class="tabcontent" id="tab4">
                            <div id="info-panel">
                                <!-- info-panel -->
                                <table class="table table-striped" data-toggle="table" data-height="400"
                                    data-row-style="rowStyle">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>PESO</th>
                                            <th>TAREA</th>
                                            <th>PROCESO</th>
                                            <th>RESPONSABLE</th>
                                            <th>INICIO / FIN</th>
                                            <th>DESCRIPCIÓN</th>
                                            <th>EJECUTANTES</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php
                                        $obj_prs= new Tproceso($clink);
                                        $obj_user= new Tusuario($clink);

                                        $i_task= 0;
                                        $j_task= 0;

                                        $obj->SetIdProceso($id_proceso);
                                        $obj->SetYear($year);
                                        $array_pesos= null;
                                        if (!empty($id_objetivo))
                                            $array_pesos= $obj->get_tareas(false);

                                        $obj_reg= new Triesgo($clink);
                                        $obj_reg->SetIdRiesgo(null);
                                        $obj_reg->SetYear($year);
                                        $obj_reg->SetInicio((int)$inicio);
                                        $obj_reg->SetFin((int)$fin);

                                        $result= $obj_reg->listar_tareas(null, null, true);

                                        $obj_task= new Ttarea($clink);
                                        $obj_task->SetYear($year);
                                        
                                        $array_ids= array();
                                        foreach ($obj_reg->array_tareas as $row) {
                                            if ($array_ids[$row['id']])
                                                continue;
                                            $array_ids[$row['id']]= 1;
                                         ?>
                                        <tr>
                                            <td valign="top"><?=++$j_task?></td>

                                            <td>
                                                <div style="width: 170px;">
                                                    <?php if ($action != 'list') { ?>
                                                    <select id="select_task_<?=$row['id']?>"
                                                        name="select_task_<?=$row['id']?>" class="form-control"
                                                        onchange="set_cant_task(<?=$row['id']?>)">
                                                        <?php for ($k= 0; $k < 8; ++$k) { ?>
                                                        <option value="<?=$k?>"
                                                            <?php if ($k == $array_pesos[$row['id']]) echo "selected='selected'"?>>
                                                            <?=$Tpeso_inv_array[$k]?></option>
                                                        <?php } ?>
                                                    </select>

                                                    <input type="hidden" name="init_task_<?=$row['id']?>"
                                                        id="init_task_<?=$row['id']?>"
                                                        value="<?=!empty($array_pesos[$row['id']]) ? $array_pesos[$row['id']] : 0?>" />
                                                    <br />
                                                    <?php } else { ?>
                                                    <?=$Tpeso_inv_array[$array_pesos[$row['id']]]?>
                                                    <?php } ?>

                                                    <?php 
                                                        if (!empty($array_pesos[$row['id']])) 
                                                            ++$i_task; 
                                                        ?>
                                                </div>

                                            </td>

                                            <td>
                                                <?= textparse($row['nombre'])?>
                                            </td>

                                            <td>
                                                <?php
                                                foreach ($row['procesos'] as $prs) {
                                                    $obj_prs->Set($prs['id']);
                                                    echo $obj_prs->GetNombre() . ' (' . $Ttipo_proceso_array[$obj_prs->GetTipo()] . '), <br >';
                                                }
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                $array= $obj_user->GetEmail($row['id_responsable']);
                                                echo $array['nombre'];
                                                if (!empty($array['cargo']))
                                                    echo ', ' . textparse($array['cargo']);
                                                ?>
                                            </td>
                                            <td>
                                                <?=odbc2time_ampm($row['fecha_inicio']) ?>
                                                <br /><br />
                                                <?=odbc2time_ampm($row['fecha_fin']) ?>
                                            </td>

                                            <td><?= textparse($row['descripcion'])?></td>

                                            <td>
                                                <?php
                                                $string = $obj_task->get_participantes($row['id'], 'tarea');
                                                echo $string;

                                                $origen_data = $obj_user->GetOrigenData('participant', $row['origen_data']);
                                                if (!is_null($origen_data))
                                                    echo "<br /> " . merge_origen_data_participant($origen_data);
                                                ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                Cantidad de tareas: <strong><?=$j_task?></strong>
                            </div><!-- info-panel -->


                            <input type="hidden" name="cant_task" id="cant_task" value="<?=$i_task?>" />
                            <input type="hidden" name="t_cant_task" id="t_cant_task" value="<?=$j_task?>" />

                            <script language="javascript">
                            if ($('#t_cant_task').val() == 0) {
                                $('#info-panel').hide();
                                box_alarm(
                                    "Aún no hay tareas en el periodo fijado, vinculadas a la gestión de los riesgos.  Deberá crear las tareas asociada a la gestión de los riesgos y posteriormente podrá utilizar esta funcionalidad"
                                    );
                            }

                            function set_cant_task(id) {
                                var nvalue = parseInt($('#cant_task').val());
                                var select = parseInt($('#select_task_' + id).val());
                                var init = parseInt($('#init_task_' + id).val());

                                if (select > 0 && init == 0)
                                    ++nvalue;
                                if (select == 0 && init > 0)
                                    --nvalue;
                                $('#cant_task').val(nvalue);
                            }
                            </script>
                        </div><!-- tareas -->


                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'add' || $action == 'update') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href = '<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/19_rieesgos.htm#14_25.1')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>

                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>

</body>

</html>