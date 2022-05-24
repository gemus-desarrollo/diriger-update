<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2013
 */


session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once "../php/class/time.class.php";

require_once "../php/class/usuario.class.php";
require_once "../php/class/grupo.class.php";

require_once "../php/class/escenario.class.php";
require_once "../php/class/indicador.class.php";
require_once "../php/class/proceso.class.php";
require_once "../php/class/proceso_item.class.php";

require_once "../php/class/code.class.php";
require_once "../php/class/badger.class.php";

$signal= !empty($_GET['signal'])?  $_GET['signal'] : null;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];

if (empty($year))
    $year= date('Y');

$_inicio= $year - 3;
$_fin= $_SESSION['current_year'] + 3;

if ($action == 'add') {
    if (isset($_SESSION['obj']))
        unset($_SESSION['obj']);
}

if (isset($_SESSION['obj'])) {
    $obj= unserialize($_SESSION['obj']);
    $obj->SetLink($clink);
} else {
    $obj= new Tproceso_item($clink);
}

$conectado= $obj->GetConectado();
if (is_null($conectado))
    $conectado= 1;

$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $obj->GetIdResponsable();
if (empty($id_responsable))
    $id_responsable= 0;

$nombre= !empty($_GET['nombre']) ? urldecode($_GET['nombre']) : $obj->GetNombre();
$entrada= !empty($_GET['entrada']) ? urldecode($_GET['entrada']) : $obj->GetEntrada();
$salida= !empty($_GET['salida']) ? urldecode($_GET['salida']) : $obj->GetSalida();
$recursos= !empty($_GET['recursos']) ? urldecode($_GET['recursos']) : $obj->GetRecursos();
$lugar= !empty($_GET['lugar']) ? urldecode($_GET['lugar']) : $obj->GetLugar();
$descripcion= !empty($_GET['descripcion']) ? urldecode($_GET['descripcion']) : $obj->GetDescripcion();
$local_archive= !is_null($_GET['local_archive']) ? $_GET['local_archive'] : $obj->GetLocalArchive();
$codigo_archive= !is_null($_GET['codigo_archive']) ? $_GET['codigo_archive'] : $obj->GetCodigoArchive();

$url= !empty($_GET['url']) ? urldecode($_GET['url']) : $obj->GetURL();
$protocolo= !empty($_GET['protocolo']) ? urldecode($_GET['protocolo']) : $obj->GetProtocolo();
$puerto= !empty($_GET['port']) ? $_GET['puerto'] : $obj->GetPuerto();

$inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $obj->GetInicio();
$fin= !empty($_GET['fin']) ? $_GET['fin'] : $obj->GetFin();

$if_entity= !is_null($obj->GetIfEntity()) ? $obj->GetIfEntity() : 0;
$id_entity= !empty($obj->GetIdEntity()) ? $obj->GetIdEntity() : 0;
$id_entity_code= !empty($obj->get_id_entity_code()) ? $obj->get_id_entity_code() : null;

if (empty($inicio))
    $inicio= $_SESSION['inicio'];
if (empty($fin))
    $inicio= $_SESSION['fin'];

$id_proceso= $obj->GetIdProceso();
if (empty($id_proceso))
    $id_proceso= $_GET['id_proceso'];
if (empty($id_proceso))
    $id_proceso= 0;

if ($id_proceso == $_SESSION['local_proceso_id'] || empty($id_proceso)) {
    $inicio= 2009;
    $fin= 2030;
    $_inicio= 2009;
    $_fin= 2030;
}

$_inicio= min($inicio, $_inicio);
$_fin= max($fin, $_fin);

if ($id_proceso == $_SESSION['id_entity'])
    $local_archive= true;

$id_proceso_sup= !empty($_GET['id_proceso_sup']) ? $_GET['id_proceso_sup'] : $obj->GetIdProceso_sup();
$tipo_sup= 0;
$conectado_sup= _NO_LOCAL;

$obj_prs= new Tproceso($clink);

if (!empty($id_proceso_sup)) {
    $obj_prs->Set($id_proceso_sup);
    $tipo_sup= $obj_prs->GetTipo();
    $conectado_sup= $obj_prs->GetConectado();
}

$obj_prs->get_codigo_archive_array($id_proceso);
$array_codigo_archives= $obj_prs->array_codigo_archives;

unset($obj_prs);

$tipo= 0;
if (!empty($_GET['tipo']))
    $tipo= $_GET['tipo'];
if (empty($tipo))
    $tipo= $obj->GetTipo();

$redirect= $obj->redirect;
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : $obj->error;

if (empty($action))
    $action= 'list';

$obj->SetYear($year);

$user_ref_date= date('Y-m-d H:i:s');
$obj->set_user_date_ref($user_ref_date);

if (!empty($id_proceso)) {
    $obj->listar_usuarios();
    $array_usuarios= $obj->array_usuarios;

    $obj->listar_grupos();
    $array_grupos= $obj->array_grupos;

    $obj->listar_indicadores();
    $array_indicadores= $obj->array_indicadores;
}

$obj->SetYear($year);

$obj_prs= new Tproceso($clink);
$obj_prs->SetYear($year);
$array_procesos= $obj_prs->listar(false);

$obj_code= new Tcode($clink);

$url_page= "../form/fproceso.php?signal=$signal&action=$action&menu=proceso&exect=$action";
$url_page.= "&year=$year&month=$month&day=$day&id_usuario=$id_usuario&id_responsable=$id_responsable";
$url_page.= "&id_proceso_sup=$id_proceso_sup&id_proceso=$id_proceso";

add_page($url_page, $action, 'f');
?>

<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
    <title>UNIDAD ORGANIZATIVA</title>

    <?php require 'inc/_page_init.inc.php'; ?>

    <!-- Bootstrap core JavaScript
================================================== -->

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>

    <link rel="stylesheet" type="text/css" href="../css/custom.css">

    <script type="text/javascript" charset="utf-8" src="../js/string.js?version="></script>
    <script type="text/javascript" charset="utf-8" src="../js/general.js?version="></script>

    <link rel="stylesheet" type="text/css" href="../libs/multiselect/multiselect.css?version=" />
    <script type="text/javascript" charset="utf-8" src="../libs/multiselect/multiselect.js?version="></script>

    <link href="../libs/windowmove/windowmove.css" rel="stylesheet" />
    <script type="text/javascript" src="../libs/windowmove/windowmove.js?version="></script>

    <script type="text/javascript" src="../js/time.js?version="></script>

    <script type="text/javascript" src="../js/ajax_core.js?version="></script>

    <script type="text/javascript" src="../js/form.js?version="></script>

    <style type="text/css">
    .in-percentil {
        width: 40px;
        text-align: right;
        padding-right: 2px;
    }
    ._no_eficaz {
        color: white;
        background: #DF0000;
        text-align: center;
    }
    ._eficaz {
        color: white;
        background: #008000;
        text-align: center;
    }
    div.point {
        color: rgb(255, 255, 255);

        font-size: 5px;
        text-align: center;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        -moz-border-radius: 50%;
        background-color: rgb(225, 124, 79);
    }
    </style>

    <script language="javascript">
    var array_codigo_archive = [
        <?php
        $i= 0;
        foreach ($array_codigo_archives as $id => $code) {
            if (empty($code))
                continue;
            ++$i;
            if ($i > 1)
                echo ",";
            echo "\"$code\"";
        }
        ?>
    ];

    var array_procesos_entity= [];
    <?php 
    reset ($array_procesos_entity);
    foreach ($array_procesos_entity as $prs) { 
    ?>
    array_procesos_entity[<?=$prs['id']?>]= {'nombre':'<?=$prs['nombre']?>', 'id_entity':<?=$prs['id_entity']?>};
    <?php 
    } 
    reset ($array_procesos_entity);
    ?>

    function refreshp(index) {
        var id_proceso= $('#proceso').val();
        if (array_procesos_entity[id_proceso]['id_entity'] != <?=$_SESSION['id_entity']?> 
            && array_procesos_entity[id_proceso]['id_entity'] != $id_proceso) {
            alert("No puede ponerle Unidades suboordinadas a "+array_procesos_entity[id_proceso]['nombre']+" desde esta entidad.")
            $('#proceso').val(<?=$_SESSION['id_entity']?>);
            return;
        }
    }

    function refreshpage() {
        var action = $('#exect').val();
        var tipo = $('#tipo').val();

        set_proceso_entity();

        var nombre = 0;
        <?php if ($action == 'add' || ($action == "update" || $action == 'list') && $id_proceso != $_SESSION['id_entity']) { ?>
        nombre = encodeURI($('#nombre').val());
        <?php } ?>

        var lugar = encodeURI($('#lugar').val());
        var id_responsable = $('#responsable').val();
        var id_proceso = $('#id').val();
        var id_proceso_sup = $('#proceso').val();
        id_proceso_sup = (id_proceso_sup == 'undefined' || id_proceso_sup == 'null') ? 0 : id_proceso;
        var year = $('#year').val();

        var descripcion = encodeURI($('#descripcion').val());
        var entrada = encodeURI($('#entrada').val());
        var salida = encodeURI($('#salida').val());
        var recursos = encodeURI($('#recursos').val());
        var local_archive = $('#local_archive').is(':checked') ? 1 : 0;
        var codigo_archive = Entrada($('#codigo_archive').val()) ? $('#codigo_archive').val() : '';

        var inicio = $('#inicio').val();
        var fin = $('#fin').val();

        var url = '&tipo=' + tipo + '&nombre=' + nombre + '&lugar=' + lugar + '&id_responsable=' + id_responsable +
            '&id_proceso=' + id_proceso;
        url += '&year=' + year + '&descripcion=' + descripcion + '&entrada=' + entrada + '&salida=' + salida +
            '&recursos=' + recursos;
        url += '&local_archive=' + local_archive + '&inicio=' + inicio + '&fin=' + fin + '&codigo_archive=' +
            codigo_archive;
        url += '&id_proceso_sup=' + id_proceso_sup;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        self.location.href = 'fproceso.php?version=&action=' + action + url;
    }

    function update_codigo() {
        $('#codigo').val($('#_codigo').val());
    }

    function set_page() {
        $("#email").attr("disabled", true);
        $("#puerto").attr("disabled", true);
        $("#_codigo").attr("disabled", true);
        $("#url").attr("disabled", true);
        $("#btn-http").attr("disabled", true);

        $("#tr-email").css("display", "none");
        $("#tr-ip").css("display", "none");
        $("#tr-email").css("display", "none");
        $("#tr-url").css("display", "none");
        $("#tr-codigo").css("display", "none");

        var idlocal = parseInt($("#id").val()) != parseInt("<?=$_SESSION['local_proceso_id']?>") ? false : true;

        if ($("#conectado1").is(':checked')) {
            $("#codigo").val(0);
        }

        if (!idlocal && ($("#conectado0").is(':checked') || $("#conectado2").is(':checked') || $("#conectado3").is(
                ':checked') || $("#conectado4").is(':checked'))) {
            $("#_codigo").attr("disabled", false);
            $("#tr-codigo").show();
        }

        if ($("#conectado2").is(':checked')) {
            $("#email").attr("disabled", false);
            $("#tr-email").show();
        }

        if (!idlocal && $("#conectado3").is(':checked')) {
            $("#puerto").attr("disabled", false);
            $("#email").attr("disabled", false);
            $("#url").attr("disabled", false);
            $("#btn-http").attr("disabled", false);

            $("#tr-email").show();
            $("#tr-ip").show();
            $("#tr-email").show();
            $("#tr-url").show();
            $("#tr-codigo").show();
        }

        if (!idlocal && $("#conectado4").is(':checked')) {
            $("#puerto").attr("disabled", false);
            $("#tr-ip").show();
        }

        try {
            if ($("#tipo").val() >= <?=_TIPO_PROCESO_INTERNO?>)
                $("#conectado1").attr("checked", true);
            else {
                $("#tab2").css("visibility", "hidden");
                $("#nav-tab2").css("visibility", "hidden");

                if ($("#exect").val() != 'add') {
                    $("#tab6-1").css("visibility", "hidden");
                    $("#tab6").css("visibility", "hidden");
                }
            }
        } catch (e) {
            ;
        }

        <?php if ($id_proceso != $_SESSION['local_proceso_id']) { ?>
        if ($("#conectado1").is(':checked') == false || $("#tipo").val() >= <?=_TIPO_PROCESO_INTERNO?>) {
            $("#nav-tab3").css("visibility", "visible");
            $("#tab3").css("visibility", "visible");
        } else {
            $("#nav-tab3").css("visibility", "hidden");
            $("#tab3").css("visibility", "hidden");
        }
        <?php } ?>
    }

    function validar() {
        var j;

        <?php if ($action == 'add' || ($action == "update" || $action == 'list') && $id_proceso != $_SESSION['local_proceso_id']) { ?>
        if (!Entrada($('#nombre').val())) {
            $('#nombre').focus(focusin($('#nombre')));
            alert('Introduzca el nombre del proceso o Unidad organizativa');
            return;
        }
        <?php } ?>

        if ($('#tipo').val() == 0) {
            $('#tipo').focus(focusin($('#tipo')));
            alert('Debe seleccionar el tipo de Unidad Organizativa.');
            return;
        }
        if ($('#responsable').val() == 0) {
            $('#responsable').focus(focusin($('#responsable')));
            alert('Debe identificar al máximo responsable del proceso o Unidad Organizativa');
            return;
        }
        if ($('#tipo').val() > <?=$_SESSION['local_proceso_tipo']?> && $('#proceso').val() == 0) {
            $('#tipo').focus(focusin($('#tipo')));
            alert('Debe identificar el proceso u Organo de Dirección Superior.');
            return;
        }
        if (!test_codigo_archive()) {
            $('#codigo_archive').focus(focusin($('#tipo')));
            return;
        }
        if (($('#id').val() == <?=$_SESSION['local_proceso_id']?>) && !$('#local_archive').is(':checked')) {
            alert(
                "Toda Unidad Organizativa principal debe tener una Oficina de Archivo, para el resguardo de la documentacion clasificada"
                );
            $('#local_archive').is(':checked', true);
            return
        }
        if (($('#id').val() != 'undefined' && $('#id').val() > 0) && ($('#id').val() == $('#proceso').val())) {
            $('#proceso').val(0);
            $('#proceso').focus(focusin($('#proceso')));
            alert("El proceso o Unidad Organizativa no puede tenerse a si misma como Organo Superior de Dirección");
            return;
        }

        <?php if ($action != 'add' && $tipo == _TIPO_PROCESO_INTERNO) { ?>
        if (!test_proceso_interno())
            return;
        <?php } ?>

        if (!test_connect())
            return;

        parent.app_menu_functions = false;
        $('#_submit').hide();
        $('#_submited').show();

        document.forms[0].action = '../php/proceso.interface.php';
        document.forms[0].submit();
    }

    function test_proceso_interno() {
        var text;

        if ($('#tipo').val() == <?=_TIPO_PROCESO_INTERNO?>) {
            for (k = 1; k < 6; ++k) {
                if (Entrada($('#_c' + k).val())) {
                    if (!IsNumeric($('#_c' + k).val())) {
                        $('#_c4').focus(focusin($('#_c4')));
                        alert('Error en el formato de los valores para la escala de colores.');
                        return false;
                    }
                }
            }

            j = 1;
            for (i = 1; i < 6; ++i) {
                if (Number($('#_c' + i).val()) < Number($('#_c' + j).val())) {
                    $('#_c4').focus(focusin($('#_c4')));
                    text = "Error en el orden de los valores de la escala de colores. Los valores deben de ";
                    text += "crecer de Rojo(menor) a Azul fuerte (mayor)";
                    alert(text);
                    return false;
                }

                j = i;
            }
            if (Number($('#_c4').val()) < 100 || Number($('#_c5').val()) < 100) {
                $('#_c4').focus(focusin($('#_c4')));
                alert(
                    'Error en el orden de los valores de la escala de colores (Los colores azules NO deben estar por debajo del 100%).'
                    );
                return false;
            }
            if (Number($('#_c3').val()) > 100) {
                $('#_c3').focus(focusin($('#_c3')));
                alert(
                    'Error en el orden de los valores de la escala de colores (El color verde no debe estar por encima del 100%).'
                    );
                return false;
            }
        }

        return true;
    }

    function test_connect() {
        var text;

        <?php if ($id_proceso == $_SESSION['local_proceso_id']) { ?>
        return true;
        <?php } ?>

        if ((($('#proceso').val() > 0 && $('#proceso').val() != <?=$_SESSION['local_proceso_id']?>) &&
                ($('#conectado_sup').val() != <?=_NO_LOCAL?> && $('#proceso_sup').val() !=
                    <?=$_SESSION['local_proceso_id']?>)) &&
            $('#conectado1').is(':checked')) {

            $('#conectado1').focus(focusin($('#conectado1')));
            text = "Incongruencia entre la configuración de la conexión escogida para esta Unidad Organizativa ";
            text += "o proceso y la que se definió para su Unidad o proceso superior, al que se subordina.";
            alert(text);
            return false;
        }
        if (!$("#conectado0").is(':checked') && !$("#conectado1").is(':checked') && !$("#conectado2").is(':checked') &&
            !$("#conectado3").is(':checked') && !$("#conectado4").is(':checked')) {
            $('#conectado1').focus(focusin($('#conectado1')));
            alert(
                'No ha especificado la manera en que se produce la comunicación o acceso de datos con la unidad o proceso.'
                );
            return false;
        }
        if ($('#conectado2').is(':checked') || $('#conectado3').is(':checked')) {
            if (!Entrada($('#email').val())) {
                $('#email').focus(focusin($('#email')));
                alert('Debe especificar la dirección de correo electrónico que será utilizada para la comunicación');
                return false;
            } else {
                if (!valEmail($('#email').val())) {
                    $('#email').focus(focusin($('#email')));
                    alert('Dirección de correo electrónico incorrecta.');
                    return false;
                }
            }
        }

        if ($('#conectado3').is(':checked')) {
            if (!Entrada($('#url').val())) {
                alert("Debe especificar la dirección url o el número IP del servidor remoto del cual leerá Diriger");
                return false;
            }
            if (!Entrada($('#puerto').val())) {
                text =
                    "No ha especificado el puerto del servidor remoto por el cual leerá Diriger. Por defecto se tomará el puerto 80.";
                text += " Desea continuar?";
                confirm(text, function(ok) {
                    if (ok)
                        return _this();
                    else
                        return false;
                });
            } else
                return _this();
        } else
            return _this();

        function _this() {
            if ($('#conectado3').is(':checked')) {
                if ($('#protocolo').val() == 'http')
                    $('#puerto').val(80);
                if ($('#protocolo').val() == 'https')
                    $('#puerto').val(443);
            }

            if ($('#conectado0').is(':checked') || $('#conectado2').is(':checked') || $('#conectado3').is(':checked')) {
                if ($('#codigo').val() == 0) {
                    $('#codigo').focus(focusin($('#codigo')));
                    text = "Debe especificar un código de dos letras para identificar a la unidad o proceso dentro de ";
                    text += "la Organización Empresarial";
                    alert(text);
                    return false;
                }
            }

            return true;
        }
    }

    function testId() {
        var id = $('#responsable').val();
        var text;

        if (!Entrada($('#noIdentidad_' + id).val())) {
            $('#responsable').focus(focusin($('#responsable')));
            text =
                "Este usuario no tiene definido su número de Carnet de Identidad, en el sistema. No puede ser responsable ";
            text += "de ningún proceso, proyecto, riesgo, nota de hallazgo, y actividades o tareas estratégicas.";
            alert(text);
            $('#responsable').val(0);
        }
        if (parseInt($('#nivel_' + id).val()) < 3) {
            $('#responsable').focus(focusin($('#responsable')));
            text = "Para ser responsable de un proceso debe tener un nivel de acceso al sistema de PLANIFICADOR ";
            text += "o superior (ASMINISTRADOPR O SUPERUSUARIO).";
            alert(text);
            $('#responsable').val(0);
        }
    }

    function test_http() {
        if (!test_connect())
            return;

        var url = $('#url').val();
        var protocolo = $('#protocolo').val();
        var puerto = $('#puerto').val();

        var url = '../form/ajax/test_http.ajax.php?url=' + url + '&protocolo=' + protocolo;
        url += '&puerto=' + puerto;

        var capa = 'div-ajax-body';
        var metodo = 'GET';
        var valores = '';
        var funct= '';
        
        FAjax(url, capa, valores, metodo, funct);
        displayFloatingDiv('div-ajax-panel', '', 60, 0, 15, 10);
    }

    function test_codigo_archive() {
        if ($('#local_archive').is(':checked')) {
            $('#codigo_archive').prop('disabled', false);
        } else {
            $('#codigo_archive').empty();
            $('#codigo_archive').prop('disabled', true);
            return true;
        }

        var codigo = $('#codigo_archive').val();

        if (!Entrada(codigo) && $('#id').val() != <?=$_SESSION['local_proceso_id']?>) {
            alert("Debe especificar el código de la oficina de archivo");
            return false;
        }
        if (Entrada(codigo) && codigo.length < 2) {
            alert("El código de la oficina de archivo no puede tener menos de dos caracteres");
            return false;
        }
        if (Entrada(codigo)) {
            for (i = 0; i < codigo.length; i++) {
                if (!(codigo.charAt(i) >= "A" && codigo.charAt(i) <= "Z") && !(codigo.charAt(i) >= "a" && codigo.charAt(
                        i) <= "z")) {
                    alert("El código de la oficina de archivo solo puede contener letras");
                    return false;
                }
            }
        }

        if (Entrada(codigo) && array_codigo_archive.indexOf(codigo) != -1) {
            alert("Ya ese código de oficina de archivo esta en huso. Debe escoger otro código");
            return false;
        }

        return true;
    }
    </script>

    <script language="javascript">
    function set_entity() {
        if ($('#if_entity') &&
            ($('#if_entity').is(':checked') && parseInt($('#id').val()) != <?=$_SESSION['local_proceso_id']?>)) {
            $('#div-conectado0').hide();
            $('#div-conectado2').hide();
            $('#div-conectado3').hide();
            $('#div-conectado4').hide();

            $('#proceso').val(<?=$_SESSION['local_proceso_id']?>);
        } else {
            $('#div-conectado0').show();
            $('#div-conectado2').show();
            $('#div-conectado3').show();
            $('#div-conectado4').show();
        }

        if ($('#if_entity').is(':checked')) {
            $('#proceso option').hide();
            $('.select_prs').show();
            $('#option-prs-<?=$_SESSION['local_proceso_id']?>').show();
        } else {
            $('#proceso option').show();
        }

        if (parseInt($('#proceso').val()) != <?=$_SESSION['local_proceso_id']?>) {
            $('#div-entity').hide();
        }
    }

    function set_alert_entity() {
        if (!$('#div-entity'))
            return;
        if (!$('#tipo').val()) {
            $('#div-entity').hide();
            return;
        }

        var tipo = parseInt($('#tipo').val());
     
        if (tipo >= <?=_TIPO_GRUPO?> ||
            ((tipo >= <?=_TIPO_DIRECCION?> || tipo >= <?=_TIPO_UEB?>) && $("#conectado1").is(':checked'))) {
            if (!$('#if_entity').is(':checked')) 
                $('#div-entity').hide();
            return;
        }
        
        $('#div-entity').show();
    }

    function set_proceso_entity() {
        var if_entity = 0;
        if ($('#if_entity'))
            if_entity = $('#if_entity').is(':checked') ? 1 : 0;

        if (if_entity)
            $('#proceso').val(<?=$_SESSION['local_proceso_id']?>);
    }
    </script>

    <script type="text/javascript">
    $(document).ready(function() {
        set_page();
        InitDragDrop();

        <?php
            $id= $id_proceso;
            $restrict_prs= array(_TIPO_PROCESO_INTERNO);
            $config->freeassign= true;
            $badger= null;
            ?>

        $.ajax({
            data: {
                "id": <?=!empty($id) ? $id : 0?>,
                "tipo_plan": 0,
                "year": <?=!empty($year) ? $year : date('Y')?>,
                "user_ref_date": '<?=!empty($user_ref_date) ? $user_ref_date : date('Y-m-d H:i:s')?>',
                "id_user_restrict": <?=!empty($id_user_restrict) ? $id_user_restrict : 0?>,
                "restrict_prs": <?= !empty($restrict_prs) ? '"'. serialize($restrict_prs).'"' : 0?>,
                "use_copy_tusuarios": <?=$use_copy_tusuarios ? $use_copy_tusuarios : 0?>,
                "array_usuarios": <?= !empty($array_usuarios) ? '"'. urlencode(serialize($array_usuarios)).'"' : 0?>,
                "array_grupos": <?= !empty($array_grupos) ? '"'. urlencode(serialize($array_grupos)).'"' : 0?>
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

        $("ul.nav.nav-tabs li").removeClass("active");
        $(".tabcontent").hide();
        <?php if ($action == 'add' || $tipo != _TIPO_PROCESO_INTERNO) { ?>
        $("#nav-tab1").addClass("active");
        $("#tab1").show();
        <?php } ?>
        <?php if ($action != 'add'  && $tipo == _TIPO_PROCESO_INTERNO) { ?>
        $("#nav-tab6").addClass("active");
        $("#tab6").show();
        <?php } ?>

        $('#proceso').on('change', function() {
            if ($('#id').val() > 0 && $(this).val() == $('#id').val()) {
                alert("La Unidad Organizativa no puede subordinarce a si misma.");
                $(this).val(0);
            }
        });

        test_codigo_archive();
        $('#local_archive').on('change', function(ok) {
            test_codigo_archive();
        });

        $('#if_entity').on('change', function(ok) {
            if (!$('#if_entity').is(':checked')) {
                var text= "No es posible realizar esta operación. Deberá eliminar la estructura y volverla a crear. ";
                alert(text, function(ok) {
                    $('#if_entity').is(':checked', true);
                    $('#if_entity').prop('checked', true);                    
                });
                /*
                var text= "Esta operacion es muy destructiva para la estructura interna del sistema. ";
                    text+= "Esta seguro de querer continuar?";
                confirm(text, function(ok) {
                    if (!ok) {
                        $('#if_entity').is(':checked', true);
                        $('#if_entity').prop('checked', true);
                    }
                });
                */
            }
        });

        set_entity();
        set_alert_entity();
        set_proceso_entity();

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
                <div class="card-header">ESTRUCTURA DE DIRECCIÓN / PROCESO</div>
                <div class="card-body">

                    <ul class="nav nav-tabs" style="margin-bottom: 10px;" role="tablist">
                        <li id="nav-tab1" class="nav-item"><a class="nav-link" href="tab1">Generales</a></li>
                        <li id="nav-tab4" class="nav-item"><a class="nav-link" href="tab4">Participantes</a></li>
                        <?php if ($id_proceso != $_SESSION['local_proceso_id']) {?>
                            <li id="nav-tab5" class="nav-item">
                            <a class="nav-link" href="tab5">Comunicación</a></li><?php } ?>
                        <?php if ($id_proceso != $_SESSION['id_entity']) { ?> 
                            <li id="nav-tab3" class="nav-item"><a class="nav-link" href="tab3">Indicadores Año:<?=$year?></a></li><?php } ?>
                        <li id="nav-tab2" class="nav-item"><a class="nav-link" href="tab2">Entradas, Salidas y Recursos</a></li>
                        <?php if ($action != 'add'  && $tipo == _TIPO_PROCESO_INTERNO) { ?><li id="nav-tab6">
                            <a class="nav-link" href="tab6">Criterios de Evaluación</a></li><?php } ?>
                    </ul>

                    <form class="form-horizontal" name="fproceso" id="fproceso" action="javascript:validar()"
                        method="POST">
                        <input type=hidden id=exect name=exect value="<?=$action?>" />
                        <input type=hidden id="id" name=id value="<?=$id_proceso?>" />
                        <input type=hidden name=menu value=proceso />

                        <input type="hidden" id="conectado_sup" value="<?=$conectado_sup?>" />
                        <input type="hidden" id="proceso_sup" value="<?=$id_proceso_sup?>" />
                        <input type="hidden" id="tipo_sup" value="<?=$tipo_sup?>" />

                        <input type="hidden" id="id_entity" value="<?=$id_entity?>" />
                        <input type="hidden" id="id_entity_code" value="<?=$id_entity_code?>" />

                        <input type="hidden" id="_if_entity" name="_if_entity" value="<?=$if_entity?>" />

                        <input type="hidden" id="user_ref_date" name="user_ref_date" value="<?=$user_ref_date?>" />

                        <!-- generales -->
                        <div class="tabcontent" id="tab1">

                            <?php if ($_SESSION['nivel'] == _GLOBALUSUARIO) { ?>
                            <div id="div-entity" class="form-group row col-12">
                                <div class="alert alert-danger strong-title" style="font-size: 1.4em;">
                                    <input type="checkbox" id="if_entity" name="if_entity" value="1"
                                        <?php if ($if_entity) echo "checked='checked'" ?> onclick="set_entity()" />
                                    Es una nueva entidad. No existe intercambio de información entre las entidades
                                </div>
                            </div>
                            <?php } ?>

                            <div class="form-group row">
                                <?php $disable= $id_proceso == $_SESSION['local_proceso_id'] ? "disabled=\"yes\"" : ""?>
                                <label class="col-form-label col-md-2">
                                    Vigencia:
                                </label>
                                <label class="col-form-label col-md-2">
                                    Desde:
                                </label>
                                <div class=" col-md-2">
                                    <input type="hidden" id="init_inicio" name="init_inicio" value="<?=$inicio?>" />

                                    <select name="inicio" id="inicio" class="form-control" onchange="set_page()"
                                        <?=$disable?>>
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
                                    <input type="hidden" id="init_fin" name="init_fin" value="<?=$fin?>" />

                                    <select name="fin" id="fin" class="form-control" onchange="set_page()"
                                        <?=$disable?>>
                                        <?php for ($i = $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?= $i ?>" <?php if ($i == $fin) echo "selected='selected'"; ?>>
                                            <?= $i ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-form-label col-md-3">Año de referencia:</label>
                                <div class="col-md-3">
                                    <select name="year" id="year" class="form-control input-sm"
                                        onchange="refreshpage()">
                                        <?php for ($i= $_inicio; $i <= $_fin; ++$i) { ?>
                                        <option value="<?=$i?>" <?php if ($i == $year) echo "selected='selected'"; ?>>
                                            <?=$i?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Nombre:</label>
                                <div class="col-md-10">
                                    <?php if ($action == 'add' || ($action == "update" || $action == 'list') && $id_proceso != $_SESSION['local_proceso_id']) { ?>
                                    <input id="nombre" name="nombre" class="form-control" value="<?=$nombre?>">
                                    <?php } else { ?>
                                    <?=$nombre?>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Tipo:</label>
                                <div class="col-md-10">
                                    <select name="tipo" id="tipo" class="form-control input-sm"
                                        onchange="refreshpage()">
                                        <option value=''>Seleccione.....</option>

                                        <?php for ($i = 1; $i <= _MAX_TIPO_PROCESO; ++$i) { ?>
                                        <option value="<?= $i ?>"
                                            <?php if ($i == $tipo) echo "selected='selected'" ?>>
                                            <?= $Ttipo_proceso_array[$i] ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="row col-md-12">
                                    <div class="col-md-6">
                                        <div class="checkbox col-12">
                                            <label class="text">
                                                <input type="checkbox" id="local_archive" name="local_archive" value="1"
                                                    <?php if ($local_archive) echo "checked='checked'" ?> />
                                                Tiene una oficina para el resguardo y el control de documentos impresos.
                                            </label>
                                        </div>
                                    </div>

                                    <div class="row col-md-6">
                                        <label class="col-form-label col-6">
                                            Código de la oficina de archivo:
                                        </label>
                                        <div class="col-4">
                                            <input type="text" id="codigo_archive" name="codigo_archive"
                                                class="form-control" maxlength="6" value="<?=$codigo_archive?>" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Lugar:</label>
                                <div class="col-md-10">
                                    <textarea id="lugar" name="lugar" class="form-control input-sm"
                                        rows="2"><?=$lugar?></textarea>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Jefe o Responsable:</label>
                                <div class="col-md-10">
                                    <?php
                                    $obj_user = new Tusuario($clink);
                                    $obj_user->set_user_date_ref($fecha_origen);
                                    
                                    $id_entity= $_SESSION['id_entity'];
                                    if ($_SESSION['id_entity'] == $_SESSION['local_proceso_id'] && $_SESSION['nivel'] == _GLOBALUSUARIO)
                                        $id_entity= null;
                                    $obj_user->SetidEntity($id_entity);
                                    $result_user = $obj_user->listar(null, null, false);

                                    while ($row = $clink->fetch_array($result_user)) {
                                        if (empty($row['noIdentidad']))
                                            continue;
                                    ?>
                                    <input type="hidden" id="noIdentidad_<?= $row['_id'] ?>"
                                        name="noIdentidad_<?= $row['_id'] ?>" value="<?= $row['noIdentidad'] ?>" />
                                    <input type="hidden" id="nivel_<?= $row['_id'] ?>" name="nivel_<?= $row['_id'] ?>"
                                        value="<?= $row['nivel'] ?>" />
                                    <?php
                                    }

                                    $obj_prs= new Tproceso($clink);
                                    $clink->data_seek($result_user);
                                    ?>

                                    <select name="responsable" id="responsable" class="form-control"
                                        onchange="testId()">
                                        <option value=0>Seleccione ... </option>

                                        <?php
                                        while ($row = $clink->fetch_array($result_user)) {
                                            if (empty($row['nombre']))
                                                continue;
                                            if (empty($row['noIdentidad']))
                                                continue;
                                            if ($row['nivel'] < _PLANIFICADOR)
                                                continue;

                                            $prs= $array_procesos[$row['id_proceso']];
                                            if (!empty($prs['id_entity']) && $prs['id_entity'] != $_SESSION['id_entity'] 
                                                && ($_SESSION['id_entity'] != $_SESSION['local_proceso_id'] 
                                                    || ($_SESSION['id_entity'] == $_SESSION['local_proceso_id'] && _SESSION['nivel'] != $_GLOBALUSUARIO)))
                                                continue;
                                            ?>
                                        <option value="<?= $row['_id'] ?>"
                                            <?php if ($row['_id'] == $id_responsable) echo "selected='selected'"; ?>>
                                            <?php echo $row['nombre'] . ', ' . textparse($row['cargo']) . ', (' . $prs['nombre'] . ')' ?>
                                        </option>
                                        <?php } ?>
                                        <?php unset($obj_user); ?>
                                    </select>

                                    <div class="text-info col-12">
                                        Se muestran solo los usuarios con Número de Identidad definido en el Sistema
                                    </div>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Entidad o Dirección Superior:</label>
                                <div class="col-md-10">
                                    <?php
                                    $top_list_option= "........No tiene........";
                                    $id_list_prs= null;
                                    $tipo_list_prs= $tipo;
                                    $order_list_prs= 'eq_asc';
                                    $id_select_prs= $id_proceso_sup;
                                    $reject_connected= false;
                                    $restrict_prs= array(_TIPO_ARC);
                                    require_once "inc/_select_prs.inc.php";
                                    ?>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-2">Descripción:</label>
                                <div class="col-md-10">
                                    <textarea name="descripcion" rows="5" id="descripcion"
                                        class="form-control input-sm"><?=$descripcion?></textarea>
                                </div>
                            </div>
                        </div> <!-- generales -->


                        <!-- Participantes -->
                        <div class="tabcontent" id="tab4">
                            <div id="ajax-tab-users">

                            </div>
                        </div> <!-- tab4 Participantes-->

                        <!-- indicadors del procesos -->
                        <?php if ($id_proceso != $_SESSION['id_entity']) { ?>
                        <div class="tabcontent" id="tab3">
                            <?php
                            $id = $id_proceso;
                            $restrict_prs = array(_TIPO_PROCESO_INTERNO);

                            $create_select_input= true;
                            include "inc/indicador.inc.php";
                            ?>
                        </div> <!-- indicadors del procesos -->
                        <?php } ?>


                        <!-- entradas, salias y recursos del proceso -->
                        <div class="tabcontent" id="tab2">
                            <div class="form-group row">
                                <label class="col-form-label col-md-1">Entradas:</label>
                                <div class="col-md-11">
                                    <textarea name="entrada" rows="6" id="entrada"
                                        class="form-control input-sm"><?=$entrada?></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-1">Salidas:</label>
                                <div class="col-md-11">
                                    <textarea name="salida" rows="6" id="salida"
                                        class="form-control input-sm"><?=$salida?></textarea>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-1">Recursos:</label>
                                <div class="col-md-11">
                                    <textarea name="recursos" rows="6" id="recursos"
                                        class="form-control input-sm"><?=$recursos?></textarea>
                                </div>
                            </div>
                        </div> <!-- entradas, salias y recursos del proceso -->


                        <!-- metodos de comunicacion -->
                        <div class="tabcontent" id="tab5">
                            <div id="div-conectado0" class="checkbox">
                                <label>
                                    <input type="radio" name="conectado" id="conectado0" value="0"
                                        <?php if ($conectado == 0) echo "checked='checked'";?>
                                        onchange="set_alert_entity(); set_page();" />
                                    Tiene una instalación propia del sistema. No está accesible para la transmisión
                                    automática de datos. Será utilizado un soporte de almacenamiento para el transporte
                                    de la información.
                                </label>
                            </div>
                            <div id="div-conectado1" class="checkbox">
                                <label>
                                    <input type="radio" name="conectado" id="conectado1" value=1
                                        <?php if ($conectado == 1) echo "checked='checked'";?> onchange="set_page()" />
                                    No tiene dominio propio, porque está conectado a la Intranet (red LAN). Se accede
                                    directamente en la intranet.
                                </label>
                            </div>
                            <div id="div-conectado2" class="checkbox">
                                <label>
                                    <input type="radio" name="conectado" id="conectado2" value=2
                                        <?php if ($conectado == 2) echo "checked='checked'";?>
                                        onchange="set_alert_entity(); set_page();" />
                                    Tiene una instalación propia del sistema. Está fuera de la Intranet. Tienen acceso a
                                    través de un cuenta de correo electrónico.
                                </label>
                            </div>
                            <div id="div-conectado3" class="checkbox">
                                <label>
                                    <input type="radio" name="conectado" id="conectado3" value=3
                                        <?php if ($conectado == 3) echo "checked='checked'";?>
                                        onchange="set_alert_entity(); set_page();" />
                                    Tiene una instalación propia del sistema. Esta accesible a través de la Internet
                                    (TCP/IP). Servidor de correo. Servicio WEB.
                                </label>
                            </div>
                            <div id="div-conectado4" class="checkbox">
                                <label>
                                    <input type="radio" name="conectado" id="conectado4" value=4
                                        <?php if ($conectado == 4) echo "checked='checked'";?>
                                        onchange="set_alert_entity(); set_page();" />
                                    No tiene instalación propia del sistema, pero tiene dominio propio o subred. Accede
                                    directamente al nodo central a través de una WAN.
                                </label>
                            </div>

                            <hr>
                            </hr>

                            <div id="tr-email" class="form-group row">
                                <label class="col-form-label col-md-2">E-mail:</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control input-sm" id="email" name="email"
                                        value="<?=$obj->GetMail_address()?>" />
                                </div>
                            </div>

                            <div id="tr-url" class="form-group row">
                                <label class="col-form-label col-md-2">URL/IP:</label>
                                <div class="col-md-2">
                                    <select id="protocolo" name="protocolo" class="form-control">
                                        <option value="http" <?php if ($protocolo == 'http') echo "selected" ?>>http://
                                        </option>
                                        <option value="https" <?php if ($protocolo == 'https') echo "selected" ?>>
                                            https://</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" class="form-control input-sm" id="url" name="url"
                                        value="<?=$obj->GetURL()?>" />
                                </div>
                                <label class="col-form-label col-md-1">Puerto:</label>
                                <div class="col-md-2">
                                    <input type="text" class="form-control input-sm" id="puerto" name="puerto"
                                        maxlength="4" value="<?= $puerto ?>" />
                                </div>
                            </div>


                            <div id="tr-codigo" class="form-group row">
                                <label class="col-form-label col-md-2">Código:</label>
                                <div class="col-md-4 ">
                                    <?php
                                         $obj->get_codigo_array();
                                         $disabled = ($id_proceso == $_SESSION['local_proceso_id']) ? "disabled='disabled'" : null;
                                         ?>

                                    <select name="_codigo" id="_codigo" class="form-control" <?= $disabled ?>
                                        onchange="update_codigo()">
                                        <option value=0>...</option>
                                        <?php
                                        for ($i = 65; $i <= 90; ++$i) {
                                            for ($j = 65; $j <= 90; ++$j) {
                                                $code = chr($i) . chr($j);
                                                if (array_search($code, $obj->array_codigos) != false)
                                                    continue;
                                                ?>
                                        <option value="<?= $code ?>"><?= $code ?></option>
                                        <?php }
                                         } ?>
                                    </select>
                                </div>

                                <div class="col-md-5 col-lg-5">
                                    <input type="hidden" name="codigo" id="codigo" value="<?=$obj->GetCodigo()?>" />
                                    <label class="alert alert-danger"><?=$obj->GetCodigo()?></label>
                                </div>

                                <div class="col-sm-3 col-md-3 col-lg-2">
                                    <button type="button" id="btn-http" class="btn btn-info"
                                        onclick="test_http()">Probar Conexión</button>
                                </div>
                            </div>

                            <hr>
                            </hr>

                        </div><!-- metodos de comunicacion -->


                        <!-- indicadores criticos para los procesos internos -->
                        <?php if ($action != 'add' && $tipo == _TIPO_PROCESO_INTERNO) { ?>
                        <div class="tabcontent" id="tab6">
                            <legend>
                                Identificación de los Indicadores críticos o de obligatorio cumplimiento para evaluar de
                                eficaz el proceso interno
                            </legend>

                            <?php
                            if ($action != 'add')
                                $obj->get_criterio_eval($year);

                            $_orange = $obj->get_orange();
                            $_orange = empty($_orange) ? 85 : $_orange;

                            $_yellow = $obj->get_yellow();
                            $_yellow = empty($_yellow) ? 90 : $_yellow;

                            $_green = $obj->get_green();
                            $_green = empty($_green) ? 95 : $_green;

                            $_aqua = $obj->get_aqua();
                            $_aqua = empty($_aqua) ? 105 : $_aqua;

                            $_blue = $obj->get_blue();
                            $_blue = empty($_blue) ? 110 : $_blue;
                            ?>

                            <label class="text col-md-12 col-lg-12">
                                Criterio de eficacia del proceso Interno. Escala de colores de resultado a partir del
                                desempeño de los indicadores.
                            </label>
                            <div class="row col-12">
                                <div class="badge col-6 bg-red">
                                    NO EFICAZ
                                </div>
                                <div class="badge col-6 bg-green">
                                    EFICAZ
                                </div>
                            </div>

                            <div class="row col-12 mt-1">
                                <div class="row col-6">
                                    <div class="row col-4">
                                        <div class="col-2">
                                            <div class="alarm-cicle small bg-red"></div>
                                        </div>
                                        <div class="col-2 alarm-arrow horizontal">
                                            <i class="fa fa-arrow-right text-green"></i>
                                        </div> 
                                    </div>
                                    <div class="row col-4 mx-auto">
                                        <div class="col-2">
                                            <div class="alarm-cicle small bg-orange"></div>
                                        </div>
                                        <div class="col-2 alarm-arrow horizontal">
                                            <i class="fa fa-arrow-right text-green"></i>
                                        </div>
                                    </div>
                                    <div class="row col-4 mx-auto">
                                        <div class="col-2">
                                            <div class="alarm-cicle small bg-yellow"></div>
                                        </div>
                                        <div class="col-2 alarm-arrow horizontal">
                                            <i class="fa fa-arrow-right text-green"></i>
                                        </div>                                           
                                    </div>
                                </div>

                                <div class="row col-6">
                                    <div class="row col-4 mx-auto">
                                        <div class="col-2">
                                            <div class="alarm-cicle small bg-green"></div>
                                        </div>
                                        <div class="col-2 alarm-arrow horizontal">
                                            <i class="fa fa-arrow-right text-green"></i>
                                        </div>                                                                
                                    </div>
                                    <div class="row col-4 mx-auto">
                                        <div class="col-2">
                                            <div class="alarm-cicle small bg-aqua"></div>
                                        </div>
                                        <div class="col-2 alarm-arrow horizontal">
                                            <i class="fa fa-arrow-right text-green"></i>
                                        </div>
                                    </div>
                                    <div class="row col-4 mx-auto">
                                        <div class="col-2">
                                            <div class="alarm-cicle small bg-blue"></div>
                                        </div>
                                        <div class="col-2 alarm-arrow horizontal">
                                            <i class="fa fa-arrow-right text-green"></i>
                                        </div>                                          
                                    </div>
                                </div>
                            </div>

                            <div class="row col-12">
                                <div class="row col-6 mt-1">
                                    <div class="col-3">
                                        Fracaso
                                    </div>
                                    <div class="col-4 input-group">
                                        <input type="text" id="_c1" name="_c1" value='<?= $_orange ?>'
                                            class="form-control input-sm" maxlength="6" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="col-4 input-group">
                                        <input type="text" id="_c2" name="_c2" value='<?= $_yellow ?>'
                                            class="form-control input-sm" maxlength="6" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="row col-6 mt-1">
                                    <div class="col-4 input-group">
                                        <input type="text" id="_c3" name="_c3" value='<?= $_green ?>'
                                            class="form-control input-sm" maxlength="6" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="col-4 input-group">
                                        <input type="text" id="_c4" name="_c4" value='<?= $_aqua ?>'
                                            class="form-control input-sm" maxlength="6" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                    <div class="col-4 input-group">
                                        <input type="text" id="_c5" name="_c5" value='<?= $_blue ?>'
                                            class="form-control input-sm" maxlength="6" />
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>

                            <hr />
                            <div id="toolbar-indi" class="text col-12">
                                Indicadores que son invalidantes o criticos para la eficacia del proyecto.
                                Su incumplimiento hace ineficaz el proyecto
                            </div>
                            <table class="table table-hover table-striped" data-toggle="table" data-height="330"
                                data-toolbar="#toolbar-indi" data-search="true">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Critico?</th>
                                        <th>Nombre del Indicador</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php
                                             reset($array_indicadores);
                                             $i= 0;

                                             foreach ($array_indicadores as $array) {
                                                 if (empty($array['peso']))
                                                     continue;

                                                 $checked = ($array['critico']) ? "checked='checked'" : "";
                                                 $obj_prs->Set($array['id_proceso']);
                                                 $proceso = $obj_prs->GetNombre() . ', ' . $Ttipo_proceso_array[$obj_prs->GetTipo()];
                                                 ?>
                                    <tr>
                                        <td><?= ++$i ?></td>
                                        <td>
                                            <input type="checkbox" id="id_ind_<?= $array['id'] ?>"
                                                name="id_ind_<?= $array['id'] ?>" value="1" <?= $checked ?> />
                                        </td>
                                        <td>
                                            <?php echo textparse($array['nombre']) . '  (' . $array['inicio'] . ' - ' . $array['fin'] . ') / ' ?>
                                            <span class="text-persp" style="color: saddlebrown"><?= $proceso ?></span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                        </div>
                        <?php } ?>

                        <!-- buttom -->
                        <div id="_submit" class="btn-block btn-app">
                            <?php if ($action == 'update' || $action == 'add') { ?>
                            <button class="btn btn-primary" type="submit">Aceptar</button>
                            <?php } ?>
                            <button class="btn btn-warning" type="reset"
                                onclick="self.location.href='<?php prev_page() ?>'">Cancelar</button>
                            <button class="btn btn-danger" type="button"
                                onclick="open_help_window('../help/03_procesos.htm#03_5.1')">Ayuda</button>
                        </div>

                        <div id="_submited" style="display:none">
                            <img src="../img/loading.gif" alt="cargando" /> Por favor espere ..........................
                        </div>

                    </form>
                </div> <!-- panel-body -->
            </div> <!-- panel -->
        </div> <!-- container -->

    </div>

    <div id="div-ajax-panel" class="ajax-panel panel panel-primary win-board" data-bind="draganddrop">
        <div class="card-header">
            <div class="row win-drag">
                <div class="col-xs-12">
                    <div id="win-title"
                        class="panel-title ajax-title clear col-11 win-drag">CONEXIÓN A
                        SERVIDOR REMOTO</div>

                    <div class="col-1 pull-right">
                        <div class="close">
                            <a href="javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                                <i class="fa fa-close"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="div-ajax-body" class="card-body output-board">

        </div>
    </div>

</body>

</html>