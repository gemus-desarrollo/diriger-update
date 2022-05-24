<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";
require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";

require_once "../php/class/programa.class.php";
require_once "../php/class/escenario.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";
require_once "../php/class/tarea.class.php";
require_once "../php/class/proyecto.class.php";

require_once "../php/class/code.class.php";

require_once "../php/class/badger.class.php";

$_SESSION['debug']= 'no';

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$error= !empty($_GET['error']) ? $_GET['error'] : null;

if ($action == 'add' && is_null($error)) {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tproyecto($clink);
}

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'proyecto';
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $obj->GetIdProceso();
$id_programa= !empty($_GET['id_programa']) ? $_GET['id_programa'] : $obj->GetIdPrograma();

if (empty($id_programa))
    $id_programa= 0;
if (empty($id_proceso))
    $id_proceso= $_SESSION['id_entity'];

$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();

$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $obj->GetIdResponsable();
if (empty($id_responsable))
    $id_responsable= 0;

$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : $obj->GetIdProyecto();
if (empty($id_proyecto))
    $id_proyecto= 0;
$id= $id_proyecto;

if (!empty($_GET['fecha_inicio']))
    $fecha_inicio= urldecode($_GET['fecha_inicio']);
if (empty($fecha_inicio))
    $fecha_inicio= odbc2date($obj->GetFechaInicioPlan());

if (!empty($_GET['fecha_fin']))
    $fecha_fin= urldecode($_GET['fecha_fin']);
if (empty($fecha_fin))
    $fecha_fin= odbc2date($obj->GetFechaFinPlan());

$year= !empty($fecha_inicio) ? date("Y", strtotime(date2odbc($fecha_inicio))) : $year;

if (!empty($_GET['descripcion']))
    $descripcion= urldecode($_GET['descripcion']);
if (empty($descripcion))
    $descripcion= $obj->GetDescripcion();

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

str_replace("\n", " ", addslashes($error));
/*
if (is_null($error) && $signal == 'proyecto' && !empty($id_proyecto)) {
    $error= "Debe asignar las tareas ha realizar para ejecución del proyecto.";
}
*/
/*
if (!empty($id_proyecto)) {
    $obj->listar_usuarios();
    $array_usuarios= $obj->array_usuarios;

    $obj->listar_grupos();
    $array_grupos= $obj->array_grupos;
}
*/
$codigo= !empty($_GET['codigo']) ? urldecode($_GET['codigo']) : $obj->GetCodigo();

if ($action == 'add' && empty($codigo)) {
    $id_last= get_last_id('tproyectos', $id_proceso);
    $id_last= $_SESSION['origen']. (++$id_last);
}

$array_procesos_init= null;

if (!empty($id_programa)) {
    $obj_prs= new  Tproceso_item($clink);
    $obj_prs->SetIdPrograma($id_programa);
    $obj_prs->SetIdProyecto(null);
    $array_procesos_init= $obj_prs->GetProcesosProyecto();
}

/**
 * configuracion de usuarios y procesos segun las proiedades del usuario
 */
global $config;
global $badger;

$badger= new Tbadger($clink);
$badger->set_user_date_ref($fecha_inicio);
$badger->set_planproject();

$url_page= "../form/fproyecto.php?signal=$signal&action=$action&menu=$menu&exect=$action&id_proceso=$id_proceso";
$url_page.= "&year=$year&month=$month&day=$day&id_programa=$id_programa&id_responsable=$id_responssable";
$url_page.= "&id_proyecto=$id_proyecto";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>PROYECTOS</title>

    <?php require_once "inc/tarea_tabs.ini.inc.php";?>

    <script language='javascript' type="text/javascript">
    function validar() {
        var form = document.forms[0];
        var text;

        if (!Entrada($('#codigo').val())) {
            $('#codigo').focus(focusin($('#codigo')));
            text = "No ha introducido un código para la identificación del proyecto. ";
            text+= "El sistema asignará uno por defecto. ¿Desea continuar?";
            confirm(text, function(ok) {
                if (!ok)
                    if (parseInt($("#control_page_origen").val()) == 0) 
                        return;
                    else     
                        return false;
                else {
                    $('#codigo').val($('#id_last').val());
                    if (!this_1())
                        if (parseInt($("#control_page_origen").val()) == 0) 
                            return;
                        else     
                            return false;
                }
            });
        } else {
            if (!this_1())
                if (parseInt($("#control_page_origen").val()) == 0) 
                    return;
                else     
                    return false;
        }

        function this_1() {
            if (!Entrada($('#nombre').val())) {
                $('#nombre').focus(focusin($('#nombre')));
                alert('Introduzca el título del proyecto');
                return false;
            }
            if ($('#responsable').val() == 0) {
                $('#responsable').focus(focusin($('#responsable')));
                alert('Selecione el responsable de la ejecución del proyecto');
                return false;
            }
            if (!validate_date())
                return false;

            if (parseInt($('#cant_tab_user').val()) == 0) {
                $('#causa').focus(focusin($('#causa')));
                alert("Debe especificar los integrantes del equipo de proyecto o los participantes del mismo.");
                return false;
            }

            form.action = '../php/proyecto.interface.php';

            parent.app_menu_functions = false;
            $('#_submit').hide();
            $('#_submited').show();

            form.submit();
        }
    }
    </script>

    <script language="javascript" type="text/javascript">
    function rfreshp() {
        var id_proceso = $('#proceso').val();
        var id_programa = $('#programa').val();
        var id_responsable = $('#responsable').val();
        var signal = $('#signal').val();
        var id_proyecto = $('#id_proyecto').val();
        var codigo = encodeURI($('#codigo').val());

        var fecha_inicio = encodeURI($('#fecha_inicio').val());
        var fecha_fin = encodeURI($('#fecha_fin').val());
        var nombre = encodeURI($('#nombre').val());
        var descripcion = encodeURI($('#descripcion').val());
        var year = $('#year').val();

        var url = '&id_proceso=' + id_proceso + '&fecha_inicio=' + fecha_inicio + '&fecha_fin=' + fecha_fin + '&nombre=' + nombre;
        url += '&descripcion=' + descripcion + '&year=' + year + '&id_programa=' + id_programa + '&codigo=' + codigo;
        url += '&id_responsable=' + id_responsable + '&id_proyecto=' + id_proyecto + '&signal=' + signal;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        self.location.href = 'fproyecto.php?version=&action=<?=$action?>' + url;
    }

    function refresh_ajax_users() {
        var id_responsable = $('#id_responsable').val();

        $.ajax({
            data: {
                "signal": "proyecto",
                "name": "responsable",
                "plus_name": "",
                "id_responsable": id_responsable,
                "id_proceso": "",
                "year": <?=$year?>,
                "nivel": ""
            },
            url: 'ajax/select_users.ajax.php',
            type: 'get',
            beforeSend: function() {
                $("#responsable-container").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#responsable-container").html(response);
                $("#responsable").combobox();
            }
        });
    }

    function validate_date() {
        if (!Entrada($('#fecha_inicio').val())) {
            $('#causa').focus(focusin($('#causa')));
            alert('Introduzca la fecha en la que debe iniciarse la ejecución de la proyecto');
            return false;

        } else if (!isDate_d_m_yyyyy($('#fecha_inicio').val())) {
            $('#fecha_inicio').focus(focusin($('#fecha_inicio')));
            alert('Fecha de inicio con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
            return false;
        }

        if (!Entrada($('#fecha_fin').val())) {
            $('#fecha_fin').focus(focusin($('#fecha_fin')));
            alert('Introduzca la fecha en la que se estima que se debe culminar la ejecución de la proyecto');
            return false;

        } else if (!isDate_d_m_yyyyy($('#fecha_fin').val())) {
            $('#fecha_fin').focus(focusin($('#fecha_fin')));
            alert('Fecha de terminación con formato incorrecto. (d/m/yyyy) Ejemplo: 1/1/2010');
            $('#fecha_fin').val($('#fecha_inicio').val());
            return false;
        }

        if (DiferenciaFechas($('#fecha_fin').val(), $('#fecha_inicio').val(), 's') < 0) {
            $('#fecha_fin').focus(focusin($('#fecha_fin')));
            alert('La fecha de terminación del proyecto no puede ser anterior a la de inicio');
            $('#fecha_fin').val($('#fecha_inicio').val());
            return false;
        }

        return true;
    }
    </script>

    <script type="text/javascript">
    var focusin;
    $(document).ready(function() {
        InitDragDrop();

        refresh_ajax_users();

        <?php
        $id = $id_proyecto;
        $user_ref_date = date2odbc($fecha_fin);
        $restrict_prs = array(_TIPO_PROCESO_INTERNO);
        ?>

        $.ajax({
            data: {
                "signal": "proyecto",
                "id_proyecto": <?=!empty($id_proyecto) ? $id_proyecto : 0?>,
                "tipo_plan": <?=_PLAN_TIPO_PROYECTO?>,
                "year": <?=!empty($year) ? $year : date('Y')?>,
                "user_ref_date": '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',
                "id_user_restrict": <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>,
                "restrict_prs": <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                "use_copy_tusuarios": <?=$use_copy_tusuarios ? $use_copy_tusuarios : 0?>,
                /*
                "array_usuarios" : <?= !empty($array_usuarios) ? '"'. addslashes(serialize($array_usuarios)).'"' : 0?>,
                "array_grupos" : <?= !empty($array_grupos) ? '"'. addslashes(serialize($array_grupos)).'"' : 0?>
                */
            },
            url: 'ajax/usuario_tabs.ajax.php',
            type: 'post',
            beforeSend: function() {
                $("#ajax-tab-users").html("Procesando, espere por favor...");
            },
            success: function(response) {
                $("#ajax-tab-users").html(response);
            }
        });

        $('#div_fecha_inicio').datepicker({
            format: 'dd/mm/yyyy'
        });
        $('#div_fecha_fin').datepicker({
            format: 'dd/mm/yyyy'
        });

        $('#fecha_inicio').on('change', function() {
            validate_date();
        });
        $('#fecha_fin').on('change', function() {
            validate_date();
        });

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

        <?php if (!empty($id_proyecto)) { ?>
        $('ul.nav.nav-tabs li').removeClass('active');
        $(".tabcontent").hide();
        $('#nav-tab5').addClass('active');
        $('#tab5').show();

        tarea_table_ajax('<?=$action?>');
        <?php } ?>

        if (parseInt($('#t_cant_multiselect-prs').val()) == 0) {
            $('#nav-tab4').hide();
            $('#nav-tab4').addClass('d-none');
        }

        <?php if (!is_null($error)) { ?>
        alert("<?=str_replace("\n"," ", addslashes($error))?>");
        <?php } ?>
    });
    </script>
</head>

<body>
    <script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

    <div class="app-body form">
        <div class="container">
            <div class="card card-primary">
                <div class="card-header">PROYECTO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <?php if ($signal == 'proyecto' || $signal == 'fproyecto' || $signal == "tablero") { ?>
                        <li id="nav-tab1" class="nav-item" title=""><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab2" class="nav-item" title=""><a class="nav-link" href="tab2">Descripción</a></li>
                        <li id="nav-tab3" class="nav-item" title=""><a class="nav-link" href="tab3">Integrantes del Equipo/Accesos</a></li>
                        <li id="nav-tab4" class="nav-item" title="">
                            <a class="nav-link" href="tab4">Unidades Organizativas/Procesos Involucrados</a>
                        </li>
                        <?php } ?>
                        <?php if (!empty($id_proyecto)) { ?>
                            <li id="nav-tab5" class="nav-item" title=""><a class="nav-link" href="tab5">Tareas</a>
                        </li><?php } ?>
                    </ul>

                    <form name="fproyecto" class="form-horizontal" action='javascript:validar_main()' method='post'>
                        <input type="hidden" id="exect" name="exect" value="<?=$action?>" />
                        <input type="hidden" name="id" id="id" value="<?=$id_proyecto?>" />
                        <input type="hidden" name="menu" id="menu" value="proyecto" />

                        <input type="hidden" name="id_programa" id="id_programa" value="<?=$id_programa?>" />
                        <input type="hidden" name="id_proyecto" id="id_proyecto" value="<?=$id_proyecto?>" />
                        <input type="hidden" name="id_responsable" id="id_responsable" value="<?=$id_responsable?>" />
                        <input type="hidden" name="id_proceso" id="id_proceso" value="<?=$id_proceso?>" />

                        <input type="hidden" name="id_last" id="id_last" value="<?=$id_last?>" />

                        <input type="hidden" name="signal" id="signal" value="<?= $signal?>" />
                        <input type="hidden" id="year" name="year" value="<?=$year?>" />
                        <input type="hidden" name="id_nota" id="id_nota" value="0" />
                        <input type="hidden" name="id_riesgo" id="id_riesgo" value="0" />
                        <input type="hidden" name="id_auditoria" id="id_auditoria" value="0" />
                        <input type="hidden" name="origen" id="origen" value="0" />

                        <input type="hidden" name="control_page_origen" id="control_page_origen" value="0" />
                        <input type="hidden" name="id_tarea" id="id_tarea" value="0" />
                        <input type="hidden" name="signal" id="signal" value="<?=$signal?>" />

                        <!-- Generales -->
                        <div class="tabcontent" id="tab1">
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Programa:
                                </label>
                                <div class="col-md-10">
                                    <select name="programa" id="programa" class="form-control" onchange="refreshp()">
                                        <option value="0">Selecione ... </option>
                                        <?php
                                         $obj_prog = new Tprograma($clink);
                                         $obj_prog->SetYear($year);
                                         $result_prog = $obj_prog->listar();

                                         while ($row = $clink->fetch_array($result_prog)) {
                                        ?>
                                        <option value="<?= $row['_id'] ?>"
                                            <?php if ($row['_id'] == $id_programa) echo "selected='selected'"; ?>>
                                            <?= $row['nombre'] ?></option>
                                        <?php } ?>
                                    </select>

                                    <?php
                                    $clink->data_seek($result_prog);
                                    while ($row = $clink->fetch_array($result_prog)) {
                                    ?>
                                    <input type="hidden" id="programa_code_<?= $row['_id'] ?>"
                                        name="programa_code_<?= $row['_id'] ?>" value="<?= $row['_id_code'] ?>" />
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Código:
                                </label>
                                <div class="col-md-3">
                                    <input name="codigo" class="form-control" id="codigo" maxlength="10"
                                        value="<?=$codigo ?>" />
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Título:
                                </label>
                                <div class="col-md-10">
                                    <textarea name="nombre" class="form-control" id="nombre"
                                        rows="2"><?=$nombre?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">
                                    Responsable:
                                </label>
                                <div id="responsable-container" class="col-md-7">
                                    <select id="responsable" name="responsable" class="form-control">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-3">
                                    Fecha de inicio (propuesta):
                                </label>
                                <div class="col-md-3">
                                    <div class='input-group date' id='div_fecha_inicio' data-date-language="es">
                                        <input id="fecha_inicio" name="fecha_inicio" class="form-control"
                                            value="<?= $fecha_inicio ?>" />
                                        <span class="input-group-text"><span
                                                class="fa fa-calendar"></span></span>
                                    </div>
                                </div>
                                <label class="col-form-label col-md-3">
                                    Fecha de término (propuesta):
                                </label>
                                <div class="col-md-3">
                                    <div class='input-group date' id='div_fecha_fin' data-date-language="es">
                                        <input id="fecha_fin" name="fecha_fin" class="form-control"
                                            value="<?= $fecha_fin ?>" />
                                        <span class="input-group-text"><span
                                                class="fa fa-calendar"></span></span>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Generales -->

                        <!-- relacion de los procesos a los que pertenece -->
                        <div class="tabcontent" id="tab4">
                            <?php
                             $id= $id_proyecto;

                            $obj_prs = new Tproceso_item($clink);
                            !empty($year) ? $obj_prs->SetYear($year) : $obj_prs->SetYear(date('Y'));
                            if ($_SESSION['nivel'] >= _SUPERUSUARIO || $badger->acc == 3)
                                $obj_prs->set_use_copy_tprocesos(false);
                            else
                                $exits = $obj_prs->set_use_copy_tprocesos(true);

                            $result_prs_array = $obj_prs->listar_in_order('eq_desc', false, _TIPO_DEPARTAMENTO, false, 'asc');
                            $cant_prs = $obj_prs->GetCantidad();
                            
                            if ($cant_prs > 0)
                                $clink->data_seek($result_prs);

                            $id= $id_proyecto;
                            if (!empty($id_proyecto)) {
                                $obj_prs->SetIdProyecto($id);
                                $array_procesos= $obj_prs->GetProcesosProyecto();
                            }

                            $restrict_prs= array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
                            $restrict_up_prs= true;
                            
                            $create_select_input= false;
                            require "inc/proceso_tabs.inc.php";
                             ?>
                        </div> <!-- relacion de los procesos a los que pertenece -->

                        <!-- integrantes del equippo de proyectos -->
                        <div class="tabcontent" id="tab3">
                            <div id="ajax-tab-users">

                            </div>
                        </div> <!-- integrantes del equipo de proyectos -->

                        <!-- tareas del proyecto -->
                        <?php if (!empty($id_proyecto)) { ?>
                        <div class="tabcontent" id="tab5">
                            <div id="ajax-task-table" class="ajax-task-table">

                            </div>
                        </div> <!-- tareas del proyecto -->
                        <?php } ?>

                        <!-- descripcion -->
                        <div class="tabcontent" id="tab2">
                            <textarea name="descripcion" id="descripcion"><?=$descripcion; ?></textarea>
                        </div> <!-- descripcion -->

                        <hr>
                        </hr>
                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/manual.html')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div>


            <div id="div-ajax-panel" class="ajax-panel">

            </div>

        </div>

</body>

</html>