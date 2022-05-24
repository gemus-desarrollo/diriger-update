<style type="text/css">
body {
    background: #ececec;
}

.div-ajax {
    overflow: hidden;
}

.win-log,
.panel.win-log {
    width: 99%;
    display: block;
    top: 130px;
    left: 1%;
    position: relative;

    z-index: 999999999999;
}

.win-log .body-log {
    max-height: 450px;
    min-height: 100px;
    overflow: scroll;
}

.colspan {
    text-align: left;
}

.audit-filter {
    overflow: hidden;
    position: fixed;
    display: none;
    float: right;

    top: 71px;
    right: 100px;
    Z-INDEX: 100;
    BORDER-BOTTOM: #333333 2px solid;

    padding: 5px;
    text-align: left;

    filter: alpha(opacity=80);
    -moz-opacity: 0.8;
    opacity: 0.8;

    text-decoration: none !important;
    color: #CCC;
    text-shadow: #444 0px -1px 0px;
    letter-spacing: normal;
    background-color: #464646;
    text-align: left;
}

#scheduler td.plinner div.inside_responsable {
    color: #999990;
    font-style: oblique;
}

.horizontal-scroll {
    overflow: scroll!important;
    overflow-x: scroll!important;
}
</style>

<script language="javascript" type="text/javascript">
function loadpage(id) {
    if ($('#id_plan').val() == '') {
        return;
    }

    var year = $('#year').val();
    var month = $('#month').val();
    var tipo_plan = $('#tipo_plan').val();
    var print_reject = $('#print_reject').val();
    var init_row_temporary = $('#init_row_temporary').val();
    var if_numering = $('#if_numering').val();
    var calendar_type = $('#calendar_type').val();

    var url = 'tablero_planning.php?signal=<?=$signal?>&tipo_plan=' + tipo_plan + '&year=' + year + '&month=' + month;
    url += '&print_reject=' + print_reject + '&init_row_temporary=' + init_row_temporary + '&if_numering=' +
    if_numering;
    url += '&calendar_type=' + calendar_type;
    <?php if ($signal != 'calendar') { ?>url += '&id_proceso=' + id;
    <?php } ?>
    <?php if ($signal == 'calendar') { ?>url += '&id_calendar=' + id;
    <?php } ?>

    self.location.href = url;
}

function ver(flag) {
    if ($('#id_plan').val() == '') {
        return;
    }

    if (ifblock_app_menu())
        return;

    var print_reject = $('#print_reject').val();
    var tipo = $('#tipo_plan').val();

    <?php if ($signal == 'mensual_plan') { ?>
    if (print_reject == <?=_PRINT_REJECT_NO?>)
        $('#print_reject').val(<?=_PRINT_REJECT_OUT?>);
    else
        $('#print_reject').val(<?=_PRINT_REJECT_NO?>);

    refreshp();
    <?php } ?>

    <?php if ($signal != 'calendar' && $signal != 'mensual_plan' && $signal != 'anual_plan_audit') { ?>
    if (print_reject == <?=_PRINT_REJECT_OUT?>)
        $('#print_reject').val(<?=_PRINT_REJECT_DEFEAT?>);
    else
        $('#print_reject').val(<?=_PRINT_REJECT_OUT?>);

    refreshp();
    <?php } ?>

    <?php if ($signal == 'calendar') { ?>
    $('#print_reject').val(flag);

    if (print_reject != flag)
        refreshp();
    else {
        closeFloatingDiv('div-ajax-panel');
        parent.app_menu_functions = true;
    }
    <?php } ?>

    <?php if ($signal == 'anual_plan_audit' || $signal == 'anual_plan_meeting') { ?>
    if (flag == 0) {
        if (print_reject == <?=_PRINT_REJECT_DEFEAT?>)
            $('#print_reject').val(<?=_PRINT_REJECT_NO?>);
        else
            $('#print_reject').val(<?=_PRINT_REJECT_DEFEAT?>);
    }
    if (flag == 1) {
        if (tipo == <?=_PLAN_TIPO_AUDITORIA?>)
            $('#tipo_plan').val(<?=_PLAN_TIPO_SUPERVICION?>);
        else
            $('#tipo_plan').val(<?=_PLAN_TIPO_AUDITORIA?>);
    }

    refreshp();
    <?php } ?>
}

function refreshp() {
    var id_proceso = $('#proceso').val();
    var id_calendar = $('#id_calendar').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();
    var print_reject = $('#print_reject').val();
    var tipo_plan = $('#tipo_plan').val();
    var init_row_temporary = $('#init_row_temporary').val();
    var monthstack = $('#monthstack').val();
    var auto_refresh_stop = $('#auto_refresh_stop').val();
    var if_numering = $('#if_numering').val();
    var calendar_type = $('#calendar_type').val();

    <?php if ($signal == 'anual_plan_audit') { ?>
    var origen = $('#origen').val();
    var tipo = $('#tipo').val();
    var organismo = $('#organismo').val();

    <?php } else { ?>

    var capitulo = 0;
    var id_tipo_evento = 0;

    var like_name = $('#like_name').val();
    if (like_name == 'undefined')
        like_name = null;

    <?php if ($signal != 'calendar') { ?>
    if ($('#tipo_actividad1').val())
        capitulo = $('#tipo_actividad1').val() != undefined ? $('#tipo_actividad1').val() : 0;
    if ($('#tipo_actividad2'))
        id_tipo_evento = $('#tipo_actividad2').val() != undefined ? $('#tipo_actividad2').val() : 0;
    <?php } } ?>

    var url = 'tablero_planning.php?signal=<?=$signal?>&id_proceso=' + id_proceso + '&year=' + year + '&month=' + month;
    url += '&day=' + day + '&print_reject=' + print_reject + '&id_calendar=' + id_calendar + '&tipo_plan=' + tipo_plan;
    url += '&init_row_temporary=' + init_row_temporary + '&monthstack=' + monthstack + '&auto_refresh_stop=' +
        auto_refresh_stop;
    url += '&if_numering=' + if_numering + '&calendar_type=' + calendar_type;

    <?php if ($signal == 'anual_plan_audit') { ?>
    url += "&tipo=" + tipo + "&origen=" + origen + "&organismo=" + encodeURI(organismo);
    <?php } else { ?>
    if (like_name.length > 1)
        url += "&like_name=" + encodeURI(like_name);
    <?php if ($signal != 'calendar') { ?>
    url += "&id_tipo_evento=" + id_tipo_evento + "&capitulo=" + capitulo;
    <?php } } ?>

    self.location = url;
}

function monthstack() {
    $('#monthstack').val(parseInt($('#monthstack').val()) ? 0 : 1);
    refreshp();
}

function imprimir(flag) {
    if ($('#id_plan').val() == '') {
        return;
    }

    if (ifblock_app_menu())
        return;

    var text;
    var id_proceso = $('#proceso').val();
    var id_calendar = $('#id_calendar').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var print_reject = $('#print_reject').val();
    var tipo_plan = $('#tipo_plan').val();
    var id_proceso_asigna = $('#id_proceso_asigna').val();
    var monthstack = $('#monthstack').val();
    var if_numering = $('#if_numering').val();
    var url;

    <?php if ($signal == 'anual_plan_audit') { ?>
    var origen = $('#origen').val();
    var tipo = $('#tipo').val();
    var organismo = $('#organismo').val();
    <?php }?>

    var _url = '?signal=<?=$signal?>' + '&id_usuario=' + id_calendar + '&id_proceso=' + id_proceso;
    _url += '&month=' + month + '&year=' + year + '&tipo_plan=' + tipo_plan + '&id_proceso_asigna=' + id_proceso_asigna;
    _url += '&monthstack=' + monthstack + '&if_numering=' + if_numering;

    <?php if ($signal == 'anual_plan_audit') { ?>
    _url += "&origen=" + origen + "&tipo=" + tipo + "&organismo=" + encodeURI(organismo);
    <?php }?>

    <?php if ($signal == 'calendar' || $signal == 'mensual_plan' || $signal == 'anual_plan_meeting') { ?>
    if (flag == 1 || flag == 'week') {
        _url += '&print_reject=' + print_reject;
    }
    <?php } ?>

    if (flag == 2) {
        <?php if ($signal == 'anual_plan' || $signal == 'anual_plan_audit') { ?>
        text = "Desea que en la impresión del Resumen se consideren las tareas o actividades que estan identificadas ";
        text += "como rechazadas, suspendidas y las que no estan asignadas aun usuario (actividades \"fantasmas\")?";
        <?php } ?>

        <?php if ($signal == 'calendar' || $signal == 'mensual_plan') { ?>
        text = "Desea que en la impresión del Resumen se consideren las tareas o actividades ocultas ?";
        <?php } ?>

        confirm(text, function(ok) {
            if (ok) {
                print_reject = <?=_PRINT_REJECT_DEFEAT?>;
                _url += '&print_reject=' + print_reject;
                _this_2();

            } else {
                print_reject = <?=_PRINT_REJECT_NO?>;
                _url += '&print_reject=' + print_reject;
                _this_2();
            }
        });
    }

    var date_eval = 0;

    function _this_2() {
        if (flag == 2) {
            text = "Este plan ya fue evaluado. Desea que en el resumen se consideren las actividades ";
            text += "y registros hechos en fecha posterior a la evaluación del plan";
            date_eval = $("#date_eval").val();

            if ($("#date_eval").val().length > 0) {
                confirm(text, function(ok) {
                    if (ok) {
                        date_eval = 0;
                        _url += "&date_eval=" + encodeURIComponent(date_eval);
                        _this_3();
                    } else {
                        _url += "&date_eval=" + encodeURIComponent(date_eval);
                        _this_3();
                    }
                });
            } else {
                _url += "&date_eval=" + encodeURIComponent(date_eval);
                _this_3();
            }
        }
    }

    if (flag == 1) {
        <?php 
        $oaces= null;
        $stack= null;
        $gearh= null;

        if ($signal == 'anual_plan') 
            $oaces= $signal == 'anual_plan' && $config->use_anual_plan_organismo ? "_oaces" : null; 
        if ($signal == 'mensual_plan') {
            $stack= $monthstack ? "_stack" : "";
            $oaces= $signal == 'mensual_plan' && $config->use_mensual_plan_organismo ? "_oaces" : null;
            $gearh= $config->grouprows ? "_gearh" : null; 
        }
        ?>
        url = '../print/<?=$signal?><?=$stack?><?=$oaces?><?=$gearh?>.php' + _url;

        show_imprimir(url, "IMPRIMIENDO <?=$title_plan?>",
            "width=600,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    if (flag == 'week') {
        url = '../print/calendar_week.php' + _url;
        show_imprimir(url, "IMPRIMIENDO <?=$title_plan?>",
            "width=600,height=600,toolbar=no,location=no, scrollbars=yes");
    }

    function _this_3() {
        if (flag == 2) {
            <?php if ($signal == 'calendar') { ?>
            //  url= '../print/<?=$config->summaryextend ? 'resumen_plan_extended_2016' : 'resumen_plan'?>.php'+_url;
            url = '../print/resumen_plan_extended_2016.php' + _url;
            <?php } ?>

            <?php if ($signal == 'mensual_plan') { ?>
            //  url= '../print/<?=$config->summaryextend ? 'resumen_mensual_plan_extended_2016' : 'resumen_mensual_plan_2016'?>.php'+_url;
            url = '../print/resumen_mensual_plan_extended_2016.php' + _url;
            <?php } ?>

            <?php if ($signal == 'anual_plan') { ?>
            url = '../print/resumen_anual_plan_2016.php' + _url;
            <?php } ?>

            <?php if ($signal == 'anual_plan_audit') { ?>
            url = '../print/resumen_audit_2016.php' + _url;
            <?php } ?>

            show_imprimir(url, "IMPRIMIENDO RESUMEN DEL <?=$title_plan?>",
                "width=600,height=400,toolbar=no,location=no,scrollbars=yes");
        }
    }

    if (flag == 3) {
        <?php if ($signal == 'mensual_plan') { ?>
        _url += '&fecha_inicio=' + encodeURIComponent($('#fecha_inicio').val() + ' 00:00:00');
        _url += '&fecha_fin=' + encodeURIComponent($('#fecha_fin').val() + ' 23:59:00');
        _url += '&observacion=' + encodeURIComponent($('#observacion').val());
        url = '../print/mensual_puntualizacion_2016.php' + _url;
        <?php } ?>

        show_imprimir(url, "IMPRIMIENDO RESUMEN DE LA PUNTUALIZACIÓN MENSUAL",
            "width=600,height=400,toolbar=no,location=no,scrollbars=yes");
        CloseWindow('div-ajax-panel');
    }
}

function displayWindow(panel) {
    if ($('#id_plan').val() == '') {
        return;
    }

    var w = 70;
    var title;

    if (panel == 'register')
        title = "ESTADO DE EJECUCIÓN";

    if (panel == 'delete') {
        title = "ELIMINAR";
        w = 50;
    }
    if (panel == 'if_numering') {
        title = "ENUMERAR LAS ACTIVIDADES";
        w = 50;
    }
    if (panel == 'edit') {
        title = "EDITAR / MODIFICAR LA ACTIVIDAD";
        w = 40;
    }
    if (panel == 'reject') {
        w = 50;
        title = "RECHAZAR O SUSPENDER LA ACTIVIDAD";
    }
    if (panel == 'delegate') {
        w = 70;
        title = "DELEGAR O AGREGAR PARTICIPANTES";
    }
    if (panel == 'points') {
        w = 50;
        title = "INTERVALO DE FECHAS PARA LA PUNTUALIZACIÓN";
    }
    if (panel == 'repro') {
        w = 70;
        <?php if ($signal == 'anual_plan' || $signal == 'anual_plan_audit') {?>
        title = "COPIAR ACTIVIDAD PARA EL PRÓXIMO AÑO";
        <?php } ?>

        <?php if ($signal != 'anual_plan' && $signal != 'anual_plan_audit') {?>
        title = "REPROGRAMAR TAREA O ACTIVIDAD";
        <?php } ?>

        <?php if ($signal == 'anual_plan_audit') {?>
        w = 70;
        title = "REPROGRAMAR AUDITORIA O SUPERVICIÓN";
        <?php } ?>
    }
    if (panel == 'copy') {
        title = "COPIAR TODO EL PLAN PARA EL PRÓXIMO AÑO";
        w = 40;
    }
    if (panel == 'ob')
        title = "DEFINIR LOS OBJETIVOS O TAREAS PRINCIPALES DEL <?=$title_plan?>";
    if (panel == 'ap')
        title = "APROBAR EL <?=$title_plan?>";
    if (panel == 'ev')
        title = "EVALUAR EL CUMPLIMIENTO";
    if (panel == 'auto_eval')
        title = "AUTO EVALUAR EL CUMPLIMIENTO";
    if (panel == 'hour')
        title = "REGISTRO DE CUMPLIMIENTO Y TIEMPO INVERTIDO";
    if (panel == 'advance') {
        title = "REGISTRO DE AVANCE EN LA EJECUCIÓN";
        w = 50;
    }
    if (panel == 'print') {
        title = "MOSTRAR TAREAS O ACTIVIDADES OCULTAS";
        w = 50;
    }
    if (panel == 'outlook') {
        title = "Enviar las actividades o tareas al calendario de Outlook Express";
        w = 50;
    }

    displayFloatingDiv('div-ajax-panel', title, w, 0, 10, 20);
}

function set_numering(if_numering) {
    if ($('#id_plan').val() == '') {
        return;
    }    

    var text;
    var year = $('#year').val();
    var month = $('#month').val();
    var id_proceso = $('#proceso').val();
    var id_proceso_asigna = $('#id_proceso_asigna').val();
    var id_plan = $('#id_plan').val();
    var signal = $('#signal').val();

    $('#if_numering').val(if_numering);

    if (parseInt(id_proceso) == <?=$_SESSION['local_proceso_id']?> && signal == "anual_plan") {
        text = "Está en el Plan General Anual de <?=$_SESSION['empresa']?>. Esta operación afectará los números ";
        text += "que fueron asignadas a las actividades duranre su planificación. Desea continuar?";
        confirm(text, function(ok) {
            if (ok) {
                var url = '../php/event_numering.interface.php?id_plan=' + id_plan + '&if_numering=' +
                    if_numering;
                url += '&signa=<?=$signal?>&year=' + year + '&month=' + month + '&id_proceso=' + id_proceso +
                    '&id_proceso_asigna=' + id_proceso_asigna;

                var capa = 'div-ajax';
                var metodo = 'GET';
                var valores = '';
                var funct= '';

                displayWindow('if_numering');
                FAjax(url, capa, valores, metodo, funct);
            } else {
                return;
            }
        });
    } else {
        refreshp();
    }
}

function cerrar(error) {
    parent.app_menu_functions = false;
    try {
        if (error && error != 'undefined')
            alert(error);
    } catch (e) {
        error = 'undefined';
    }

    CloseWindow('div-ajax-panel');

    if (!Entrada(error) || error == 'undefined' || !error) {
        $('#auto_refresh_stop').val(0);
        refreshp();
    } else {
        parent.app_menu_functions = true;
    }
}

function mostrar(_frm) {
    if ($('#id_plan').val() == '') {
        return;
    }

    var action = 'add';
    <?php if ($signal == 'calendar') { ?>
        action = 'object';
    <?php } ?>

    var hide_trz = 0;
    var frm = _frm;
    var action;
    var year = $('#year').val();
    var month = $('#month').val();
    var id_proceso = $('#proceso').val();
    var tipo_plan = $('#tipo_plan').val();
    var if_jefe = $('#if_jefe').val();
    var id_calendar = parseInt($('#id_calendar').val());
    var aprobado = $('#aprobado').val();

    if (_frm == 'ob') {
        frm = 'ap';
        action = 'object';
    }
    if (_frm == 'ap') {
        frm = 'ap';
        action = 'aprove';
    }
    if (_frm == "auto_eval") {
        frm = "ev";
        action = "auto_eval";
    }
    if (_frm == 'ev') {
        if (!Entrada(aprobado) || aprobado == 'undefined') {
            alert(
            "No se puede evaluar un Plan de Trabajo que aún no ha sido aprobado. Primero deberá aprobar el Plan.");
            return;
        }
        action = "eval";
    }
    if (action == "eval" || action == 'auto_eval') {
        if (!Entrada(aprobado) || aprobado == 'undefined') {
            alert("No se puede evaluar o autoeveluar un plan que aun no ha sido aprobado. Debe aprobar el Plan.");
            return;
        }
    }
    if (_frm == 'outlook' && !Entrada($('#email').val())) {
        alert("El usuario no tiene dirección de correo electrónico definida en el sistema");
        return;
    }
    if (_frm == 'points') {
        frm = 'points';
        action = 'points';
    }

    var url = '../form/ajax/fcalendar_' + frm + '.ajax.php';
    url += '?signal=<?=$signal?>&action=' + action + '&if_jefe=' + if_jefe;
    url += '&month=' + month + '&year=' + year + '&id_proceso=' + id_proceso + '&id_usuario=' + id_calendar +
        '&tipo_plan=' + tipo_plan;

    var capa = 'div-ajax';
    var metodo = 'GET';
    var valores = '';
    var funct= "ajaxPanelScrollY('div-ajax-panel', 70, 0);";

    FAjax(url, capa, valores, metodo, funct);
    displayWindow(_frm);
}

function _mostrar(panel, id) {
    if ($('#id_plan').val() == '') {
        return;
    }

    if (ifblock_app_menu())
        return;

    displayWindow(panel);

    var id_usuario = $('#id_calendar').val();
    var id_responsable = $('#id_responsable').val();
    var if_jefe = $('#if_jefe').val();

    var tipo_plan = $('#tipo_plan').val();
    var id_proceso = $('#proceso').val();

    if (tipo_plan == <?=_PLAN_TIPO_AUDITORIA?> || tipo_plan == <?=_PLAN_TIPO_SUPERVICION?>) {
        id_proceso = id > 0 ? $('#id_proceso').val() : $('#proceso').val();
    }

    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();
    var calendar_type = $('#calendar_type').val();

    var id_asignado = $('#id_asignado').val();

    var id_evento = $('#id_evento').val();
    var id_tarea = $('#id_tarea').val();
    var id_auditoria = $('#id_auditoria').val();
    var id_proyecto = $('#id_proyecto').val();

    var print_reject = $('#print_reject').val();
    var ifmeeting = $('#ifmeeting').val();
    var if_synchronize = $('#if_synchronize').val();

    var url = null;

    if (panel == 'register')
        url = '../form/ajax/fevento.ajax.php?';
    if (panel == 'reject')
        url = '../form/ajax/freject_evento.ajax.php?';
    if (panel == 'delete')
        url = '../form/ajax/fdelete_evento.ajax.php?';
    if (panel == 'delegate')
        url = '../form/ajax/fevento_delegate.ajax.php?';
    if (panel == 'repro') {
        <?php if ($signal != 'anual_plan' && $signal != 'anual_plan_audit') {?>
        url = '../form/ajax/fevento_repro.ajax.php?';
        <?php } ?>
        <?php if ($signal == 'anual_plan_audit') {?>
        url = '../form/ajax/fauditoria_repro.ajax.php?';
        <?php } ?>
    }

    if (panel == 'copy' && id > 0) {
        url = '../form/ajax/fcalendar_copy.ajax.php?copy_all=0&';
    }
    if (panel == 'copy' && (id == 0 || id == -1)) {
        url = '../form/ajax/fcalendar_copy.ajax.php';
        url += id == -1 ? '?copy_all=2&' : '?copy_all=1&';
        var nums_id_show = $('#nums_id_show').val();
        var array_id_show = $('#array_id_show').val();
        url += '&nums_id_show=' + nums_id_show + '&array_id_show=' + array_id_show + '&';
    }

    if (panel == 'hour')
        url = '../form/ajax/ftarea_hour.ajax.php?';
    if (panel == 'advance')
        url = '../form/ajax/ftarea_register.ajax.php?';

    url += 'signal=<?=$signal?>&id=' + id + '&action=' + panel + '&id_proceso=' + id_proceso + '&if_jefe=' + if_jefe;
    url += '&print_reject=' + print_reject + '&year=' + year + '&month=' + month + '&day=' + day + '&id_asignado=' +
        id_asignado;
    url += '&id_usuario=' + id_usuario + '&id_responsable=' + id_responsable + '&tipo_plan=' + tipo_plan;
    url += '&id_evento=' + id_evento + '&id_tarea=' + id_tarea + '&id_auditoria=' + id_auditoria + '&id_proyecto=' +
        id_proyecto;
    url += '&ifmeeting=' + ifmeeting + '&if_synchronize=' + if_synchronize + '&calendar_type=' + calendar_type;

    var capa = 'div-ajax';
    var metodo = 'GET';
    var valores = '';
    var funct= "ajaxPanelScrollY('div-ajax-panel', 10, 0);";

    $('#div-ajax').empty();
    FAjax(url, capa, valores, metodo, funct);
}

function ejecutar(panel) {
    var url;
    var _form;
    var id_usuario = $('#id_calendar').val();
    var print_reject = $('#print_reject').val();
    var tipo_plan = $('#tipo_plan').val();

    if (panel == 'register')
        _form = 'fregevento';
    if (panel == 'reject')
        _form = 'freject';
    if (panel == 'delete')
        _form = 'fdelete';
    if (panel == 'delegate')
        _form = 'fdelegate';
    if (panel == 'eval' || panel == 'auto_eval')
        _form = 'frm_ev';
    if (panel == 'object' || panel == 'aprove')
        _form = 'frm_ap';
    if (panel == 'repro')
        _form = 'freproevento';
    if (panel == 'copy')
        _form = 'fcopy';

    if (panel == 'register' || panel == 'reject' || panel == 'delete' || (panel == 'repro' || panel == 'copy' ||
            panel == 'delegate')) {
        <?php if ($signal != 'anual_plan_audit') { ?>url = '../php/evento_register.interface.php';
        <?php } ?>
        <?php if ($signal == 'anual_plan_audit') { ?>url = '../php/auditoria_register.interface.php';
        <?php } ?>
        url += '?signal=<?=$signal?>&id_calendar=' + id_usuario + '&print_reject=' + print_reject + '&tipo_plan=' +
            tipo_plan;
        url += '&win_ajax=1';
    }
    if ((panel == 'eval' || panel == 'auto_eval') || panel == 'object' || panel == 'aprove') {
        <?php if ($signal != 'anual_plan_audit') { ?>url = '../php/plantrab.interface.php';
        <?php } ?>
        <?php if ($signal == 'anual_plan_audit') { ?>url = '../php/plan_ci.interface.php';
        <?php } ?>
        url += '?signal=<?=$signal?>&id_calendar=' + id_usuario + '&print_reject=' + print_reject + '&tipo_plan=' +
            tipo_plan;
    }
    if (panel == 'advance' || panel == 'hour') {
        if (panel == 'hour')
            _form = 'fhoras';
        if (panel == 'advance')
            _form = 'fregtarea';
        url = '../php/tarea_register.interface.php?signal=calendar&id_calendar=' + id_usuario + '&print_reject=' +
            print_reject;
        url += '&tipo_plan=' + tipo_plan;
    }
    if (panel == 'outlook') {
        _form = "foutlook";
        url = '../php/outlook.interface.php?signal=calendar&id_calendar=' + id_usuario + '&print_reject=' +
        print_reject;
    }

    var metodo = 'POST';
    var capa = 'div-ajax';
    var valores = $("#" + _form).serialize();
    var funct= '';

    $('#_submit').css('display', 'none');
    $('#_submited').css('display', 'block');

    FAjax(url, capa, valores, metodo, funct);
    parent.app_menu_functions = false;
}

function add() {
    if ($('#id_plan').val() == '') {
        return;
    }

    if (ifblock_app_menu())
        return;

    var id_calendar = $('#id_calendar').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var id_proceso = $('#id_proceso').val();
    var tipo_plan = $('#tipo_plan').val();

    <?php if ($signal != 'anual_plan_audit') { ?>
    var url = '../form/fevento.php';
    <?php } ?>
    <?php if ($signal == 'anual_plan_audit') { ?>
    var url = '../form/fauditoria.php';
    <?php } ?>
    url += '?version=&action=add&signal=<?=$signal?>';
    url += '&id_calendar=' + id_calendar + '&month=' + month + '&year=' + year + '&id_proceso=' + id_proceso;
    <?php if ($signal == 'anual_plan_meeting') {?>url += '&_ifmeeting=1';
    <?php } ?>
    url += '&init_row_temporary=<?=$init_row_temporary?>&tipo_plan=' + tipo_plan;

    self.location.href = url;
}

function edit(id) {
    if (ifblock_app_menu())
        return;

    var year = $('#year').val();
    var month = $('#month').val();
    var id_calendar = $('#id_calendar').val();
    var id_proceso = $('#id_proceso').val();
    var tipo_plan = $('#tipo_plan').val();

    <?php if ($signal == 'anual_plan' || $signal == 'anual_plan_meeting') { ?>
        var url = '../php/evento.interface.php?version=&action=edit&signal=<?= $signal?>';
        url += '&month=' + month + '&year=' + year + '&id=' + id + '&id_evento=' + id + '&id_proceso=' + id_proceso;

        self.location.href = url;

    <?php } else { ?>

        displayWindow('edit');

        var url = '../form/ajax/fedit_evento.ajax.php?id_evento=' + id + '&id_calendar=' + id_calendar + '&month=' + month +
            '&year=' + year;
        url += '&signal=<?=$signal?>&id_proceso=' + id_proceso + '&tipo_plan=' + tipo_plan +
            '&init_row_temporary=<?=$init_row_temporary?>';

        var capa = 'div-ajax';
        var metodo = 'GET';
        var valores = '';
        var funct= '';

        FAjax(url, capa, valores, metodo, funct);
    <?php } ?>
}

function graficar() {
    if ($('#id_plan').val() == '') {
        return;
    }

    if (ifblock_app_menu())
        return;

    var year = $('#year').val();
    var month = $('#month').val();
    var id_calendar = $('#id_calendar').val();
    var id_proceso = $('#proceso').val();

    var url = 'resume_objs_event.php?signal=<?=$signal?>&month=' + month + '&year=' + year + '&id_proceso=' +
    id_proceso;
    url += '&id_calendar=' + id_calendar;

    self.location.href = url;
}

function imprimir_matter(index) {
    if (ifblock_app_menu())
        return;

    var id_proceso_asigna = $('#id_proceso_asigna').val();
    var id_proceso = $('#proceso').val();
    var id_evento = $('#id').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var ifmeeting = $('#ifmeeting').val();
    var title;
    var all = ifmeeting == 1 ? 0 : 1;
    var url = '?id_evento=' + id_evento + '&id_proceso=' + id_proceso + '&month=' + month + '&year=' + year +
        '&all_matter=' + all;
    url += '&id_proceso_asigna=' + id_proceso_asigna;

    if (index == 'matter') {
        url = '../print/matter.php' + url;
        title = "PLAN TEMÁTICO";
    }
    if (index == 'accords') {
        url = '../print/accords.php' + url;
        title = "RELACIÓN DE ACUERDOS";
    }
    if (index == 'debate') {
        url = '../print/debate.php' + url;
        title = "RELACIÓN DE INTERVENCIONES";
    }

    prnpage = show_imprimir(url, "IMPRIMIENDO " + title, "width=800,height=500,toolbar=no,location=no, scrollbars=yes");
}

function refreshTab(id) {
    if (id < 0)
        id = 0;
    $('#init_row_temporary').val(id);
    refreshp();
}

function filtrar(flag) {
    if ($('#id_plan').val() == '') {
        return;
    }

    $('#tipo_actividad_flag').val("");

    if (flag == 0) {
        <?php if ($signal == 'anual_plan_audit') { ?>
        $('#origen').val(0);
        $('#tipo').val(0);
        $('#organismo').val(0);

        <?php } else { ?>
        $('#like_name').val('');
        <?php if ($signal != 'calendar') { ?>
        $('#tipo_actividad1').val(0);
        $('#tipo_actividad2').val(0);
        <?php } } ?>
    }
    refreshTab(0);
}

function refresh_ajax_select(id, numero) {
    var capitulo = $('#tipo_actividad1').val();
    var id_proceso = $('#id_proceso').val();
    var year = $('#year').val();

    try {
        if (id == 0)
            id = $('#tipo_actividad2').val();
    } catch (e) {}

    var _url = '../form/ajax/select_tipo_evento.ajax.php?width=400&empresarial=' + capitulo + '&id=' + id;
    _url += '&year=' + year + '&id_proceso=' + id_proceso;

    $.ajax({
        //   data:  parametros,
        url: _url,
        type: 'get',
        beforeSend: function() {
            $("#ajax-tipo-evento").html("Procesando, espere por favor...");
        },
        success: function(response) {
            $("#ajax-tipo-evento").html(response);
        }
    });
}

function setNumero(n) {
    return;
}

function showInfoPanel() {
    displayFloatingDiv('info-panel-plan', '', 50, 0, 5, 15);
    ajaxPanelScrollY('info-panel-plan', 50, 0);
}

function showInductores() {
    if ($('#id_plan').val() == '') {
        return;
    }
        
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();
    var month = $('#month').val();
    var print_reject = $('#print_reject').val();
    var tipo_plan = $('#tipo_plan').val();
    var text = "Esta operación puede demorar varios minutos. Se paciente y espere ...... ";

    _alert(text);
    parent.app_menu_functions = false;

    var url = 'resume_objs_event.php?signal=<?=$signal?>&id_proceso=' + id_proceso + '&year=' + year + '&month=' +
    month;
    url += '&print_reject=' + print_reject + '&tipo_plan=' + tipo_plan;
    self.location = url;
}
</script>

<script type="text/javascript">
function set_copy_all() {
    $('#auto_refresh_stop').val(1);
    refreshp();
}

function copy_all() {
    displayWindow('copy');

    var if_jefe = $('#if_jefe').val();
    var id_proceso = $('#proceso').val();

    var year = $('#year').val();
    var month = $('#month').val();
    var day = $('#day').val();

    var id_evento = $('#id_evento').val();
    var id_tarea = $('#id_tarea').val();
    var id_auditoria = $('#id_auditoria').val();
    var id_proyecto = $('#id_proyecto').val();

    var url = '../form/ajax/fcalendar_copy.ajax.php?copy_all=2&';
    url += 'signal=<?=$signal?>&id=' + id + '&action=copy&id_proceso=' + id_proceso + '&if_jefe=' + if_jefe;
    url += '&year=' + year + '&month=' + month + '&day=' + day;
    url += '&id_evento=' + id_evento + '&id_tarea=' + id_tarea + '&id_auditoria=' + id_auditoria + '&id_proyecto=' +
        id_proyecto;

    var capa = 'div-ajax';
    var metodo = 'GET';
    var valores = '';
    var funct= '';
    
    FAjax(url, capa, valores, metodo, funct);
}

function writeLog(date, line, divout) {
    if (Entrada(date))
        line = '<p>' + date + ' --->' + line + '</p>';
    document.getElementById('body-log').innerHTML += line;
}

function set_calendar_type(type, plus) {
    var calendar_type = $('#calendar_type').val();
    $('#calendar_type').val(type != undefined && plus == 0 ? type : calendar_type);
    var year = <?=$year?>;
    var month = <?=!empty($month) ? $month : date('m')?>;
    var day = <?=!empty($day) ? $day : date('d')?>;
    var date = year + '-' + month + '-' + day;

    if (type == 0)
        date = add_to_date(date, 0, plus, 0);
    if (type == 1)
        date = add_to_date(date, plus * 7, 0, 0);
    if (type == 2)
        date = add_to_date(date, plus, 0, 0);

    var _date = new Fecha(date);

    $('#year').val(_date.anio);
    $('#month').val(_date.mes);
    $('#day').val(_date.dia);

    refreshp();
}

function go_calendar(type, day) {
    $('#calendar_type').val(type);
    $('#day').val(day);
    refreshp();
}

function go_accords() {
    var id_proceso = $('#proceso').val();
    var year = $('#year').val();

    var url = '../form/laccords.php?&action=<?=$action?>&id_proceso=' + id_proceso;
    url += '&year=' + year;

    self.location.href = url;
}
</script>

<script language="javascript">
var array_chief_id = new Array(10);
var array_chief_nombre = new Array(10);
var array_chief_cargo = new Array(10);
</script>

<script type="text/javascript">
function _dropdown_prs(id) {
    $('#proceso').val(id);
    refreshp();
}
function _dropdown_year(year) {
    $('#year').val(year);
    refreshp();
}
function _dropdown_month(month) {
    $('#month').val(month);
    refreshp();
}

$(document).ready(function() {
    InitDragDrop();
    
    $('div.alarm-box').tooltip({html:true});

    try {
        InitBtnToolbar(<?=$init_row_temporary+1?>);
    } catch (e) {
        ;
    }

    $("#btn-filter").click(function() {
        if ($('#id_plan').val() == '') {
            return;
        }
        displayFloatingDiv("panel-filter", '', 50, 0, 15, 25);
    });

    if ($('#auto_refresh_stop').val() == 1) {
        copy_all();
    }

    <?php if ($signal != 'anual_plan_audit' && $signal != 'calendar') { ?>
    refresh_ajax_select(<?=$id_tipo_evento?>, 0);

    $('#tipo_actividad1').on('change', function() {
        var id_tipo_evento = $(this).val();
        refresh_ajax_select(id_tipo_evento, 0);
    });
    <?php } ?>      
});
</script>