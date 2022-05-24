<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
 */

session_start();
require_once "../php/setup.ini.php";
require_once "../php/class/config.class.php";

require_once "../php/inc.php";
require_once "../php/config.inc.php";
require_once "../php/class/connect.class.php";
require_once _PHP_DIRIGER_DIR . "config.ini";

require_once "../tools/dbtools/base_clean.class.php";
require_once "../tools/dbtools/clean.class.php";
require_once "../tools/lote/php/baseLote.class.php";

$year = $_SESSION['current_year'];
$month = $_SESSION['current_month'];
$day = $_SESSION['current_day'];
$id_escenario = $_SESSION['current_id_escenario'];

$action_page = !empty($_GET['action']) ? $_GET['action'] : 'calendar';
$panel = !empty($_GET['panel']) ? $_GET['panel'] : (($_SESSION['id_usuario'] == _USER_SYSTEM || $_SESSION['nivel'] == _ADMINISTRADOR) ? 'sub-conf' : 'main');

$obj_sys = new Tclean($clink);

$url_page = "../html/background.php?csfr_token={$_SESSION['csfr_toke']}&panel=$panel";
set_page($url_page);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>MENU PRINCIPAL</title>
    <link rel="icon" type="image/png" href="../img/gemus_logo.png">

    <?php require '../form/inc/_page_init.inc.php'; ?>

    <link rel="stylesheet" href="../css/general.css">

    <script type="text/javascript" src="../js/menu.js"></script>

    <style type="text/css">
        .alert.alert-warning {
            font-size: 0.9rem;
            margin: 4px!important;
        }
    </style>

    <script language="javascript">
        function to_panel(panel) {
            if (panel == 'main') {
                $("#main-menu").css("display", "block");
                $("#btn-back").css("display", "none");
                $("#btn-pass").css("display", "inline-block");

                $("#sub-indi").hide();
                $("#sub-tools").hide();
                $("#sub-conf").hide();
                $("#sub-esc").hide();
                $("#sub-project").hide();

                $("#sub-indi").removeClass('d-md-inline-block');
                $("#sub-tools").removeClass('d-md-inline-block');
                $("#sub-conf").removeClass('d-md-inline-block');
                $("#sub-esc").removeClass('d-md-inline-block');
                $("#sub-project").removeClass('d-md-inline-block');                

            } else {
                $("#main-menu").css("display", "none");
                <?php if ($_SESSION['id_usuario'] != _USER_SYSTEM && (int)$_SESSION['nivel'] != _ADMINISTRADOR) { ?>
                $("#bar-top-menu").show();
                <?php } ?>
                $("#btn-back").css("display", "inline-block");
                $("#btn-pass").css("display", "none");
            }

            if (panel == 'sub-indi') {
                $("#sub-indi").css("display", "block");
            }

            if (panel == 'sub-conf') {
                $("#sub-conf").show();
                $("#sub-conf").addClass('d-md-inline-block');
            }
            if (panel == 'sub-tools') {
                $("#sub-tools").show();
                $("#sub-tools").addClass('d-md-inline-block');
            }
            if (panel == 'sub-esc') {
                $("#sub-esc").show();
                $("#sub-esc").addClass('d-md-block');
            }
            if (panel == 'sub-project') {
                $("#sub-project").show();
                $("#sub-project").addClass('d-md-inline-block');
            }
                
            parent.activeMenu = panel;
        }

        $("#btn-back").css("display", "inline-block");
    </script>

    <script type="text/javascript" charset="utf-8">
        $(document).ready(function () {
            <?php if ($_SESSION['id_usuario'] != _USER_SYSTEM && $_SESSION['nivel'] != _ADMINISTRADOR) { ?>
            $('.alert.alert-warning').hide();
            <?php } ?>

            if (isMobile()) {
                $('.btn').removeClass('p-3 mb-5');
                $('.btn').addClass('p-1');
            }

            <?php if (!is_null($error)) { ?>
            alert("<?= str_replace("\n", " ", $error) ?>");
            <?php } ?>

            to_panel("<?=$panel?>");
        });
    </script>
</head>

<body>
<script type="text/javascript" src="../libs/wz_tooltip/wz_tooltip.js"></script>

<input type="hidden" id="action" value="<?= ($_SESSION['nivel'] >= _ADMINISTRADOR) ? 'edit' : 'list' ?>"/>
<input type="hidden" name="id_escenario" id="id_escenario" value="<?= $id_escenario ?>"/>
<input type="hidden" name="nivel" id="nivel" value="<?= $_SESSION['nivel'] ?>"/>

<input type="hidden" name="day" id="day" value="<?= $day ?>"/>
<input type="hidden" name="month" id="month" value="<?= $month ?>"/>
<input type="hidden" name="year" id="year" value="<?= $year ?>"/>

<input type="hidden" id="action_page" name="action_page" value="<?= $action_page ?>"/>

<div class="container-fluid app-body m-0 row justify-content-center">
    <div id="body-menu">
        <div class="row col-12 justify-content-end mt-3 mb-3">
            <div class="badge badge-success mb-0" style="font-weight: normal;">
                Actualización: <?= _UPDATE_DIRIGER ?>
            </div>
        </div>

        <div class="row col-12 ml-3 mr-3 mt-3 justify-content-center d-none d-lg-block">
            <div class="row" style="margin-top: 10px;">
                <?php
                $obj_sys = new Tclean($clink);
                $fecha_backup = $obj_sys->get_system('backupbd');
                $observacion = $obj_sys->GetObservacion();

                $fecha_backup = !is_null($fecha_backup) ? $fecha_backup : $_SESSION['current_year'] . '-01-01';
                $now = date('Y-m-d');
                $result = date_diff(date_create($fecha_backup), date_create($now));
                $days = (int)$result->format('%a');

                if ((!empty($config->daysbackup) && (is_null($fecha_backup) || ($days - $config->daysbackup) > 1)) || !empty($observacion)) {
                    ?>
                    <div class="alert alert-warning col-12">
                        No se está realizando la salva de la Base de datos <?php if (empty($observacion)) { ?>desde
                            <?= odbc2time_ampm($fecha_backup) ?><?php } ?>.
                        <?= !empty($observacion) ? " $observacion." : null ?>
                        Por favor, contacte con el personal de GEMUS. <br/>
                        E-correo: gemus@nauta.cu. Teléfonos: 58200755 / 53740039
                    </div>
                <?php } ?>

                <?php
                $obj_sys = new Tclean($clink);
                $obj_sys->get_system('purge');
                $fecha_clean = $obj_sys->GetFecha();
                $observacion = $obj_sys->GetObservacion();

                $fecha_clean = !is_null($fecha_clean) ? $fecha_clean : '2013-01-01';
                $now = date('Y-m-d');
                $result = date_diff(date_create($fecha_clean), date_create($now));
                $months = (int)$result->format('%m');

                $fecha_clean = !empty($config->monthpurge) ? add_date($fecha_clean, 0, $config->monthpurge) : $fecha_clean;
                $result = date_diff(date_create($fecha_clean), date_create($now));
                $days_clean = (int)$result->format('%R%a');

                if ((!empty($config->monthpurge) && (is_null($fecha_clean) || $days_clean > 1)) || !empty($observacion)) {
                    ?>
                    <div class="alert alert-warning col-12">
                        No se está realizando el mantenimiento automático de la Base de
                        datos<?php if (empty($observacion)) { ?>, debio realizarse en fecha
                            <?= odbc2time_ampm($fecha_clean) ?><?php } ?>.
                        <?= !empty($observacion) ? " $observacion." : null ?>
                        Por favor, contacte con el personal de GEMUS. <br/>
                        E-correo: gemus@nauta.cu. Teléfonos: 58200755 / 53740039
                    </div>
                <?php } ?>

                <?php
                if (!empty($config->type_synchro)) {
                    $obj_sys->get_system('Lote');
                    $fecha_synchro = $obj_sys->GetFecha();
                    $observacion = $obj_sys->GetObservacion();

                    $fecha_synchro = !empty($fecha_synchro) ? $fecha_synchro : $_SESSION['current_year'] . '-01-01';
                    $now = date('Y-m-d H:i:s');
                    $seconds = !empty($fecha_synchro) ? (int)s_datediff('s', date_create($fecha_synchro), date_create($now)) : 0;
                    $array = split_time_seconds($seconds);

                    $day_synchro = $array['d'];
                    $hour_synchro = $array['h'];
                    $min_synchro = $array['i'];

                    if ((is_null($fecha_synchro) || ($seconds - (int)$config->time_synchro) / 3600 > 24) || !empty($observacion)) {
                        ?>
                        <div class="alert alert-warning col-12">
                            No se está realizando la sincronización de datos <?php if (empty($observacion)) { ?>del sistema
                                desde <?= odbc2time_ampm($fecha_synchro) ?><?php } ?>.
                            <?= !empty($observacion) ? " $observacion." : null ?>
                            Por favor, contacte con el personal de GEMUS. <br/>
                            E-correo: gemus@nauta.cu. Teléfonos: 58200755 / 53740039
                        </div>
                    <?php }
                } ?>
            </div>
            
            <?php if ($_SESSION['id_usuario'] == _USER_SYSTEM) { ?>
            <div class="row" style="margin-top: 10px;">
                <?php if (!extension_loaded("gd")) { ?>
                    <label class="alert alert-danger text d-none d-lg-block">
                        No está instalada la librería gd. No funcionarán las opciones graficas.
                        <p>Ejecute <strong>apt-get install php7.x-gd</strong></p>
                    </label>
                <?php } ?>
                <?php if (!extension_loaded("imap")) { ?>
                    <label class="alert alert-danger text d-none d-lg-block">
                        No está instalada la librería imap. No funcionará el servicio de correo electrónico.
                        <p>Ejecute <strong>apt-get install php7.x-imap</strong></p>
                    </label>
                <?php } ?>
                <!--
                    <?php if (!extension_loaded("mcrypt")) { ?>
                    <label class="alert alert-danger text">
                        No está instalada la librería mcrypt. Los lotes de sincronización serán generados sin ser encriptados.
                        <p>Ejecute <strong>apt-get install php7.x-mcrypt</strong></p>
                    </label>
                    <?php } ?>
                    -->
                <?php if (!extension_loaded("ldap")) { ?>
                    <label class="alert alert-danger text d-none d-lg-block">
                        No está instalada la librería ldap. No se podrá establecer conexión con el Directorio Activo para
                        autenticar los usuarios.
                        <p>Ejecute <strong>apt-get install php7.x-ldap</strong></p>
                    </label>
                <?php } ?>
                <?php if (!extension_loaded("xsl")) { ?>
                    <label class="alert alert-danger text d-none d-lg-block">
                        No está instalada la librería xsl. No se podrá trabajar con los ficheros en formato Excell.
                        <p>Ejecute <strong>apt-get install php7.x-xsl</strong></p>
                    </label>
                <?php } ?>
                <?php if (!extension_loaded("curl")) { ?>
                    <label class="alert alert-danger text d-none d-lg-block">
                        No está instalada la librería CURL. No se podrá realizar la sincronización por servivio WEB
                        protocolo HTTP/HTTPS.
                        <p>Ejecute <strong>apt-get install php7.x-curl</strong></p>
                    </label>
                <?php } ?>
            </div> 
            <?php } ?>               
        </div>

        <div class="d-flex justify-content-end mr-3 mt-3">
            <?php $display = ($_SESSION['id_usuario'] != _USER_SYSTEM && (int)$_SESSION['nivel'] != _ADMINISTRADOR) ? "inline-block" : "none" ?>
            <div id="bar-top-menu" class="" style="clear:both; display:<?= $display ?>">
                <a id="btn-back" class="btn btn-app shadow-sm p-3 mb-5 rounded btn-exit" style="display:none"
                onclick="to_panel('main')"
                title="Regresar al Menú Principal">
                    <i class="fa fa-home"></i>Menu Principal
                </a>
            </div> 

            <div>
                <a class="btn btn-app shadow-sm p-3 mb-1 rounded btn-exit b-inline-block b-lg-none" 
                onclick="load_url('<?=_SERVER_DIRIGER?>php/exit.php?action=exit');" title="Salir del sistema">
                    <i class="fa fa-power-off"></i>Salir
                </a>
            </div>
        </div>

        <div id="main-menu" class="row col-12 ml-3 mr-3 mt-3 justify-content-center" style="display: <?= $display ?>">
            <div class="x_panel">
                <div class="x_title">
                    <h2>PLANIFICACIÓN DE ACTIVIDADES</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="row">
                    <!--Siempre-->
  
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-inline-block"
                    onclick="sendpage('../html/tablero_planning.php?signal=calendar');"
                    title="Plan de trabajo individual del usuario y sus subordinados">
                        <i class="fa fa-clock-o"></i>Plan Individual
                    </a>
                    <!--solo PC y Table-->
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/tablero_planning.php?signal=mensual_plan')"
                    title="Planificación de las actividades principales de la Organización para el mes">
                        <i class="fa fa-calendar-o"></i>Plan Mensual
                    </a>

                    <!--solo PC-->
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/tablero_planning.php?signal=anual_plan')"
                    title="Planificación de las actividades principales de la Organización para todo el año">
                        <i class="fa fa-calendar"></i>Plan Anual
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/tablero_planning.php?signal=anual_plan_meeting')"
                    title="Programa o cronograma anual de las reuniones">
                        <i class="fa fa-coffee"></i>Reuniones
                    </a>
                    <!--
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded"  onclick="sendpage('../html/resume_work.php?id_calendar=<?= $_SESSION['id_usuario'] ?>&action=<?= $action ?>')"
                            title="Reporte de los incumplimientos de las actividades y tareas">
                            <i class="fa fa-bomb"></i>Incumplimientos
                        </a>
                        -->
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded" onclick="to_panel('sub-project')"
                    title="Seguimiento, control y evaluación de la ejecución de los proyectos">
                        <i class="fa fa-tasks"></i>Proyectos y tareas
                    </a>
                </div>
            </div> <!-- x_panel -->


            <div class="x_panel">
                <div class="x_title">
                    <h2>GESTIÓN Y CONTROL INTERNO</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <?php $action = ($_SESSION['nivel'] >= _PLANIFICADOR) ? 'add' : 'list'; ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                    onclick="sendpage('../form/fdocument.php?signal=home&action=<?= $action ?>')"
                    title="Relación y control de los documentos de la Organización">
                        <i class="fa fa-folder"></i>Documentos
                    </a>

                    <?php
                    if ($_SESSION['acc_archive']) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                        onclick="sendpage('../tools/archive/index.php?action=')"
                        title="Control de Entrada y Salidas de documentos impresos. Gestión de archivos.">
                            <i class="fa fa-archive"></i>Archivos
                        </a>
                    <?php } ?>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                    onclick="sendpage('../html/riesgo.php?signal=home&action=<?= $action ?>')"
                    title="Gestión y seguimiento a los riesgos empresariales. Control interno">
                        <i class="fa fa-shield"></i>Riesgos
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                    onclick="sendpage('../form/llista.php?signal=home&action=<?= $action ?>')"
                    title="Seguimiento de Listas de Chequeo o Guías de Control interno o Auditorías">
                        <i class="fa fa-book"></i>Listas de Chequeo
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/tablero_planning.php?signal=anual_plan_audit')"
                    title="Planificación de auditorías, supervisiones y demás acciones de control">
                        <i class="fa fa-fire"></i>Auditorias
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded" style="width:110px;"
                    onclick="sendpage('../html/nota.php?signal=home&action=<?= $action ?>')"
                    title="Control y seguimiento a las violaciones, no conformidades, observaciones y oportunidades de mejoras identificadas">
                        <i class="fa fa-neuter"></i>Hallazgos
                    </a>
                </div>
            </div> <!-- x_panel -->


            <div class="x_panel">
                <div class="x_title">
                    <h2>ESTRATEGIA EMPRESARIAL</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                    onclick="sendpage('../html/tablero.php?action=<?= $action ?>')"
                    title="Panel de indicadores, funcionalidades, gráficos y registro de situación de cumplimiento">
                        <i class="fa fa-dashboard"></i>Tableros
                    </a>

                    <?php $action = ($_SESSION['nivel'] >= _PLANIFICADOR) ? 'edit' : 'list'; ?>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lpolitica.php?action=<?= $action ?>')"
                    title="Control y seguimiento del cumplimiento de los Lineamientos y Políticas del Estado y del Órgano Superior de Dirección">
                        <i class="fa fa-tree"></i>Lineamientos
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lobjetivo_sup.php?action=<?= $action ?>')"
                    title="Control y seguimiento del cumplimiento de los Objetivos dictados por la entidad superior a la Organización">
                        <i class="fa fa-star"></i>Objetivos Superiores
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lobjetivo.php?action=<?= $action ?>')"
                    title="Seguimiento sistemático a los Objetivos Estratégicos a todos los niveles de la Organización">
                        <i class="fa fa-star-half-o"></i>Objetivos Estratégicos
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/linductor.php?action=<?= $action ?>')"
                    title="Seguimiento sistemático a los Objetivos de Trabajo u Objetivos Anuales a todos los niveles de la Organización">
                        <i class="fa fa-star-o"></i>Objetivos de Trabajo
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lprograma.php?action=<?= $action ?>');"
                    title="Seguimiento y evaluación sistemática a los programas de trabajo, de desarrollo o inversiones que ejecuta o en los que participa la Organización">
                        <i class="fa fa-product-hunt"></i>Programas
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lperspectiva.php?action=<?= $action ?>');"
                    title="Medición sistemática de los resultados de la Organización dentro de las Perspectivas del Cuadro de Mando Integral (CMI)">
                        <i class="fa fa-cubes"></i>Perspectivas
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block" onclick="to_panel('sub-indi')"
                    title="Gestión de los indicadores definidos en el sistema">
                        <i class="fa fa-line-chart"></i>Indicadores
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/graph_proceso.php?action=<?= $action ?>')"
                    title="Seguimiento y medición de la eficacia de los Procesos Internos de la Organización">
                        <i class="fa fa-cogs"></i>Procesos Internos
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/fcorrelacion.php?action=add')"
                    title="Graficar el comportamiento de varios elementos simultáneamente comparando su comportamiento">
                        <i class="fa fa-area-chart"></i>Correlación
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/resumen.php?action=<?= $action ?>')"
                    title="Tablas de indicadores de la Organización, Empresa, UEB etc, según corresponda">
                        <i class="fa fa-file-excel-o"></i>Resumen de Indicadores
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block" onclick="to_panel('sub-esc')"
                    title="Escenarios en que se desempeña la Organización y sus Empresa, UEB, etc, según corresponda"/>
                    <i class="fa fa-globe"></i>Escenarios
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/empresa.php?action=<?= $action ?>')"
                    title="Balance General de la Organización, resultados generales de la Empresa, UEB,  etc, según corresponda">
                        <i class="fa fa-bar-chart"></i>Resumen General
                    </a>
                </div>
            </div> <!-- x_panel -->


            <div class="x_panel d-none d-md-inline-block">
                <div class="x_title">
                    <h2>CONFIGURACIÓN Y HERRAMIENTAS</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <?php if (!$config->ldap_login || empty($_SESSION['user_ldap']) || $_SESSION['id_usuario'] == _USER_SYSTEM) { ?>
                        <a id="btn-pass" class="btn btn-app shadow-sm p-3 mb-5 rounded" style="display:inline-block;"
                        onclick="sendpage('../form/fclave.php?action=add')">
                            <i class="fa fa-key"></i>Cambiar contraseña
                        </a>
                    <?php } ?>
                    <?php $display = ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['acc_planwork'] == 3) ? "d-md-inline-block" : "" ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none <?= $display ?>"
                    onclick="to_panel('sub-conf')"
                    title="Administración del Sistema, Gestión de los Usuarios, Grupos de Usuarios. Configuración del Sistema para las opciones relativas a Formato de Documentos y Registros, Seguridad, Comunicación, Transmisión de datos, etc">
                        <i class="fa fa-bug"></i>Configuración
                    </a>
                    <?php $display = ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['acc_planwork'] == 3) ? "d-md-inline-block" : "" ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded  d-none <?= $display ?>" 
                    onclick="to_panel('sub-tools')"
                    title="Herramientas para mejorar la funcionabilidad y rendimiento del Sistema">
                        <i class="fa fa-wrench"></i>Herramientas
                    </a>
                </div>
            </div> <!-- x_panel -->
        </div>


        <!-- INDICADORES -->
        <div id="sub-indi" class="row col-12 ml-3 mr-3 justify-content-center" style="display: none;">
            <div class="x_panel">
                <div class="x_title">
                    <h2>INDICADORES</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lindicador.php?action=<?= $action ?>')"
                    title="Listar los indicadores registrados en el sistema. Agregar nuevos indicadores">
                        <i class="fa fa-list"></i>Listar
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded" onclick="sendpage('../form/plan.php?action=add')"
                    title="Planificar los periodos. Definir los valores de Plan">
                        <i class="fa fa-flask"></i>Planificar
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                    onclick="sendpage('../form/real.php?action=add&month=-1')"
                    title="Ingresar los datos reales de cada indicador">
                        <i class="fa fa-registered"></i>Datos Reales
                    </a>

                    <?php $action = ($_SESSION['nivel'] >= _SUPERUSUARIO) ? 'edit' : 'list'; ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/findicador_usuarios.php?action=<?= $action ?>')"
                    title="Definir los permisos de los usuarios para modificar los valores de plan y/o reales de los indicadores">
                        <i class="fa fa-unlock-alt"></i>Permiso de Acceso
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lunidad.php?action=<?= $action ?>')"
                    title="Crear nuevas Unidades de medidas o modificar las ya existentes">
                        <i class="fa fa-hourglass-half"></i>Unidades de Medición
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/ltablero.php?action=<?= $action ?>')"
                    title="Crear y configurar los Tableros de Indicadores para definir los indicadores a mostrar y los usuarios con permiso para verlos">
                        <i class="fa fa-dashboard"></i>Configuración de Tableros
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded">
                        <i class="fa fa-question"></i>Ayuda
                    </a>
                </div>
            </div>
        </div>


        <!-- PROYECTOS -->
        <div id="sub-project" class="row col-12 ml-3 mr-3 justify-content-center" style="display: none;">
            <div class="x_panel">
                <div class="x_title">
                    <h2>PROYECTOS Y TAREAS</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block" onclick="sendpage('../html/gantt.php?')"
                    title="Seguimiento, control y evaluación de la ejecución de los proyectos, utilizando el diagrama de Gantt">
                        <i class="fa fa-tasks"></i>Diagrama de Gantt <br/>(Proyecto)
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded" onclick="sendpage('../html/jkanban.php?')"
                    title="Seguimiento, control y evaluación de la ejecución de las tareas, utilizando la metodologia kanban">
                        <i class="fa fa-check-square"></i>Tableros Kanban <br/>(Proyecto)
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block" onclick="sendpage('../html/gantt_user.php?')"
                    title="Seguimiento, control y evaluación de la ejecución de las tareas de las que es responsable el usuario. Diagrama de Gantt">
                        <i class="fa fa-user"></i><i class="fa fa-tasks"></i>Diagrama de Gantt <br/>(Individual)
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded" onclick="sendpage('../html/jkanban_user.php?')"
                    title="Seguimiento, control y evaluación de la ejecución de las tareas de las que es responsable el usuario, utilizando la metodologia kanban">
                        <i class="fa fa-user"></i><i class="fa fa-check-square"></i>Tableros Kanban <br/>(Individual)
                    </a>

                    <?php $action = ($_SESSION['nivel'] >= _SUPERUSUARIO) ? 'edit' : 'list'; ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lproyecto.php?action=<?= $action ?>')"
                    title="Listar los proyectos definidos en el sistema. Definir nuevos proyectos">
                        <i class="fa fa-list"></i>Listar Proyectos
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/fproyecto.php?action=add')"
                    title="Definir un proyecto a ser gestionado por el sistema">
                        <i class="fa fa-plus-circle"></i>Nuevo Proyecto
                    </a>

                    <?php $action = ($_SESSION['nivel'] >= _SUPERUSUARIO) ? 'edit' : 'list'; ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/ltarea.php?action=<?= $action ?>')"
                    title="Listar las tareas definidas en el sistema">
                        <i class="fa fa-list"></i>Listar Tareas
                    </a>
                    <?php $action = ($_SESSION['nivel'] >= _SUPERUSUARIO) ? 'add' : 'list'; ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded" style="width:110px;"
                    onclick="sendpage('../form/ftarea.php?action=<?= $action ?>')"
                    title="Agregar/Registrar nueva tarea al sistema">
                        <i class="fa fa-plus-circle"></i>Nueva tarea
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded">
                        <i class="fa fa-question"></i>Ayuda
                    </a>
                </div>
            </div>
        </div>

        <div id="sub-esc" class="row col-12 ml-3 mr-3 justify-content-center" style="display: none">
            <div class="x_panel">
                <div class="x_title">
                    <h2>ESCENARIOS ESTRATÉGICOS</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lescenario.php?action=<?= $action ?>')"
                    title="Estado general de la organización en el periodo de tiempo de la Planificación Estratégica">
                        <i class="fa fa-list"></i>Escenarios
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../html/mapaestrategico.php?action=<?= $action ?>')"
                    title="Mapa Estratégico, Mapa de Procesos y Organigrama funcional">
                        <i class="fa fa-file-image-o"></i>Mapas Empresariales
                    </a>
                </div>
            </div>
        </div> 

        <!-- CONFIGURACION -->
        <?php
        $display = ($_SESSION['nivel'] >= _ADMINISTRADOR || $_SESSION['acc_planwork'] == 3) ? "d-md-inline-block" : "";
        $margin_top = ($_SESSION['id_usuario'] == _USER_SYSTEM || $_SESSION['nivel'] == _ADMINISTRADOR) ? "margin-top: 20px;" : null;
        ?>

        <div id="sub-conf" class="row col-12 ml-3 mr-3 justify-content-center d-none <?= $display ?>" style="<?= $margin_top ?>">
            <div class="x_panel">
                <div class="x_title">
                    <h2>CONFIGURACIÓN DEL SISTEMA</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content">
                    <?php $action = ($_SESSION['nivel'] >= _ADMINISTRADOR || $_SESSION['acc_planwork'] == 3) ? 'edit' : 'list'; ?>
                    <?php if ($_SESSION['nivel'] >= _ADMINISTRADOR) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        onclick="sendpage('../form/lusuario.php?action=<?= $action ?>')"
                        title="Listar los usuarios registrados en el sistema. Agregar nuevos o modificar">
                            <i class="fa fa-user"></i>Usuarios
                        </a>
                    <?php } ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/lgrupo.php?action=<?= $action ?>')"
                    title="Listar los Grupos de Usuarios creados. Agregar nuevos o modificar los que ya están definidos">
                        <i class="fa fa-group"></i>Grupos de Usuarios
                    </a>
                    <?php if ($_SESSION['nivel'] >= _ADMINISTRADOR) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        onclick="sendpage('../form/lproceso.php?action=<?= $action ?>')"
                        title="Listar las estructuras de dirección registradas, Entidades, Direcciones Funcionales, Procesos Internos, Áreas de Resultados Claves. Crear nuevas estructuras o modificar las existentes">
                            <i class="fa fa-industry"></i>Estructura y Procesos
                        </a>
                    <?php } ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/ltipo_evento.php?action=<?= $action ?>')"
                    title="Definir tipos o clasificaciones para las Actividades a partir de las ya definidas según la Instrucción No. 1 del 2012">
                        <i class="fa fa-tasks"></i>Clasificación de Actividades
                    </a>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../tools/archive/form/lorganismo.php?action=<?= $action ?>')"
                    title="Instituciones u Organismos externos con los que se intercambia información y actividades">
                        <i class="fa fa-building-o"></i>Organismos externos
                    </a>

                    <?php if ($_SESSION['nivel'] >= _ADMINISTRADOR) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        onclick="sendpage('../form/ltablero.php?action=<?= $action ?>')"
                        title="Crear y configurar los Tableros de Indicadores para definir los indicadores a mostrar y los usuarios con permiso para verlos">
                            <i class="fa fa-dashboard"></i>Configuración de Tableros
                        </a>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        onclick="sendpage('../form/findicador_usuarios.php?action=<?= $action ?>')"
                        title="Definir los permisos de los usuarios para modificar los valores de plan y/o reales de los indicadores">
                            <i class="fa fa-unlock-alt"></i>Permiso de Acceso
                        </a>

                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        onclick="sendpage('../form/lunidad.php?action=<?= $action ?>')"
                        title="Crear nuevas Unidades de medidas o modificar las ya existentes">
                            <i class="fa fa-hourglass-half"></i>Unidades de Medición
                        </a>
                    <?php } ?>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/ltipo_auditoria.php?action=<?= $action ?>')"
                    title="Tipos de acciones de control en el sistema">
                        <i class="fa fa-fire"></i>Tipos de acciones de control
                    </a>
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                    onclick="sendpage('../form/ltipo_reunion.php?action=<?= $action ?>')"
                    title="Órganos, comisiones o grupos que se reunen">
                        <i class="fa fa-coffee"></i>Tipos de reuniones
                    </a>    
                        
                    <br/>
                    <?php $action = ($_SESSION['nivel'] >= _ADMINISTRADOR) ? 'add' : 'list'; ?>

                    <?php if ($_SESSION['nivel'] == _GLOBALUSUARIO) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-lg-inline-block"
                        onclick="sendpage('../form/foptions.php?action=<?= $action ?>')"
                        title="Opciones de configuración del Sistema. Formato de documentos y registros, Seguridad, Comunicación y otros">
                            <i class="fa fa-cog"></i>Configuración General
                        </a>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-lg-inline-block"
                        onclick="sendpage('../form/foptions_mail.php?action=<?= $action ?>')"
                        title="Configuración del Correo electrónico">
                            <i class="fa fa-at"></i>Correo Electrónico / HTTP
                        </a>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-lg-inline-block"
                        onclick="sendpage('../form/foptions_ldap.php?action=<?= $action ?>')"
                        title="Configuración de la conexión de servidor LDAP. Se puede activar la autentición RADIUS si fuese requerida">
                            <i class="fa fa-windows"></i>Autenticación LDAP / RADIUS
                        </a>
                    <?php } ?>

                    <?php if ($_SESSION['id_usuario'] == _USER_SYSTEM) { ?>
                        <a id="btn-pass" class="btn btn-app shadow-sm p-3 mb-5 rounded d-inline-block"
                        onclick="sendpage('../form/fclave.php?action=add')">
                            <i class="fa fa-key"></i>Cambiar contraseña
                        </a>
                    <?php } ?>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded">
                        <i class="fa fa-question"></i>Ayuda
                    </a>
                </div>
            </div>
        </div>

        <!-- HERRAMIENTAS -->
        <?php $action = ($_SESSION['nivel'] >= _ADMINISTRADOR) ? 'edit' : 'list'; ?>
        <div id="sub-tools" class="row col-12 ml-3 mr-3 justify-content-center d-none <?= $display ?>">
            <div class="x_panel">
                <div class="x_title">
                    <h2>HERRAMIENTAS</h2>
                    <div class="clearfix"></div>
                </div>

                <div class="x_content d-none d-md-inline-block">
                <!--
                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                    onclick="parent.location.href='../tools/excel/home.php'"
                    title="Lectura y Escritura de documentos Excel">
                        <i class="fa fa-file-excel-o"></i>Ficheros Excel
                    </a>
                -->    
                    <?php if (!empty($config->type_synchro) && $_SESSION['nivel'] == _GLOBALUSUARIO) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                        onclick="sendpage('../tools/lote/index.php?action=resume')"
                        title="Transmisión y recepción manual de datos. Sincronización de datos con otros sistemas">
                            <i class="fa fa-wifi"></i>Sincronización de Datos
                        </a>
                    <?php } ?>

                    <?php if ($_SESSION['nivel'] == _GLOBALUSUARIO) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                        onclick="sendpage('../tools/dbtools/gen_backup.interface.php?action=export&execute=1')"
                        title="Crear salva de la base de datos, para una restauración posterior en caso de fallos o realizar una copia hacia otra PC o Laptop">
                            <i class="fa fa-download"></i>Salvar Base de Datos
                        </a>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-lg-inline-block"
                        onclick="sendpage('../get_backup.php?action=form&signal=menu&show_mainmenu=0')"
                        title="Cargar Base de Datos. Se destruye la actual y se sustituye por la que se pretende cargar">
                            <i class="fa fa-upload"></i>Restaurar Base de Datos
                        </a>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        onclick="sendpage('../tools/dbtools/clean.interface.php?action=form&signal=menu&show_mainmenu=0')"
                        title="Eliminar información redundante de la Base de Datos. Disminuye el tamaño de la Base de Datos y aumenta el rendimiento del Sistema">
                            <i class="fa fa-magic"></i>Purgar Base de Datos
                        </a>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                        title="Desbloquear todos los usuarios. Bloqueados por auto proteción del sistema" href="#"
                        onclick="sendpage('../unblock_users.php?action=form&signal=menu&show_mainmenu=0')">
                            <i class="fa fa-unlock-alt"></i>Desbloquear Usuarios
                        </a>
                    <?php } ?>

                    <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO || $_SESSION['acc_planwork'] == 3) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded"
                        title="Traspasar actividades, tareas y responsabilidades de un usuario a otro" href="#"
                        onclick="sendpage('../form/fusuario_usuario.php?action=add')">
                            <i class="fa fa-random"></i>Transferir tareas y responsabilidades
                        </a>
                    <?php } ?>

                    <?php if ($_SESSION['nivel'] >= _SUPERUSUARIO) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        title="Traza de operaciones de borrados" href="#"
                        onclick="sendpage('../form/ldelete.php?')">
                            <i class="fa fa-bitbucket-square"></i>Traza de Borrados
                        </a>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        title="Traza de acceso al sistema e impresiones de documentos" href="#"
                        onclick="sendpage('../form/ltraza.php?')">
                            <i class="fa fa-bitbucket-square"></i>Traza de Accesos e Impresiones
                        </a>
                    <?php } ?>

                    <?php if ($_SESSION['nivel'] == _GLOBALUSUARIO) { ?>
                        <a class="btn btn-app shadow-sm p-3 mb-5 rounded d-none d-md-inline-block"
                        onclick="sendpage('../form/fmail.php?action=<?= $action ?>')"
                        title="Enviar correos electrónico y/o adjuntos desde la cuenta de del Sistema Diriger o leer el contenido del buzón del Sistema. Uso exclusivo para la administración del sistema">
                            <i class="fa fa-mail-forward"></i>Correo electrónico
                        </a>
                    <?php } ?>

                    <a class="btn btn-app shadow-sm p-3 mb-5 rounded">
                        <i class="fa fa-question"></i>Ayuda
                    </a>
                </div>
            </div>
        </div> 
    </div>
</div>
</body>

</html>