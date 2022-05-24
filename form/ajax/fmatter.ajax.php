<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2017
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

$_SESSION['debug']= 'no';

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";

require_once "../../php/class/evento.class.php";
require_once "../../php/class/asistencia.class.php";
require_once "../../php/class/tematica.class.php";

$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$signal= !empty($_GET['signal']) ? $_GET['signal'] : 'fmatter';

$id= !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$id_evento= !empty($_GET['id_evento']) ? (int)$_GET['id_evento'] : 0;
$ifaccords= !empty($_GET['ifaccords']) ? $_GET['ifaccords'] : 0;
        
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= null;
$month= !is_null($_GET['month']) ? $_GET['month'] : 0;
if ($year == $_SESSION['current_year'] && is_null($month)) 
    $month= $_SESSION['current_month'];

$menu= "tablero";
$title= "ORDEN DEL DÍA";
$title_th= "Temática";

if ($ifaccords) {
    $title= "ACUERDOS";
    $title_th= "Acuerdo";
}

$obj= new Ttematica($clink);
$obj_user= new Tusuario($clink);

$obj->SetYear($year);
$obj->SetMonth($month);

$array_usuarios= array();
$array_grupos= array();

$id_evento_ref= null;

if (!empty($id)) {
    $obj->Set($id);
    $id_evento_ref= $obj->get_id_evento_accords();
  
    $descripcion= $obj->GetDescripcion();
    $id_asistencia_resp= $obj->GetIdAsistencia_resp();
    $id_asistencia_resp_code= $obj->get_id_asistencia_resp_code();
    $numero= $obj->GetNumero();
    $fecha= date('d/m/Y', strtotime($obj->GetFechaInicioPlan())); 
    $year= date('Y', strtotime($obj->GetFechaInicioPlan()));
    $hora= odbc2ampm($obj->GetFechaInicioPlan());
    
    $obj->SetIdEvento(null);
    $obj->listar_usuarios();
    $array_usuarios= $obj->array_usuarios;
    $obj->listar_grupos();
    $array_grupos= $obj->array_grupos;   
} 

$obj_event= new Tevento($clink);
$obj_event->Set($id_evento);
$id_evento_code= $obj_event->get_id_evento_code();
$user_ref_date= $obj_event->GetFechaInicioPlan();

if (empty($id)) {
    $obj_matter= new Ttematica($clink);
    $obj_matter->SetYear($year);
    $numero= $ifaccords ? $obj_matter->find_max_numero_accords($id_evento) : $obj_matter->find_max_numero($id_evento);
}
?>

<style type="text/css">
.panel-multiselect {
    min-height: 270px;
    max-height: 320px;
}
</style>

<script type="text/javascript">
var focusin;
$(document).ready(function() {
    new BootstrapSpinnerButton('spinner-numero_matter', 0, 5000);

    $('#div_fecha_matter').datepicker({
        format: 'dd/mm/yyyy',
        startDate: '<?= date('d/m/Y', strtotime($fecha_inicio)) ?>'
    });
    $('#div_hora_matter').timepicker({
        minuteStep: 5,
        showMeridian: true
    });
    $('#div_hora_matter').timepicker().on('changeTime.timepicker', function(e) {
        $('#hora_matter').val($(this).val());
    });


    <?php
    $restrict_prs= array(_TIPO_PROCESO_INTERNO, _TIPO_ARC);
    ?>

    $.ajax({
        data: {
            "id_tematica": <?=!empty($id) ? $id : 0?>,
            "ifaccords": <?=$ifaccords ? 1 : 0?>,
            "id_evento": <?=$id_evento?>,
            "tipo_plan": <?=_PLAN_TIPO_INFORMATIVO?>,
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

    max_numero_accords = <?=$numero?>;

    <?php if ($action == 'add') { ?>
    set_form_spinit();
    <?php } ?>

    //When page loads...
    $(".tabcontent.ajax").hide(); //Hide all content
    $("ul.nav li.ajax:first-child a").addClass("active").show(); //Activate first tab
    $(".tabcontent.ajax:first").show(); //Show first tab content

    //On Click Event
    $("ul.nav li.ajax a").click(function() {
        $("ul.nav li.ajax a").removeClass("active"); //Remove any "active" class
        $(this).addClass("active"); //Add "active" class to selected tab
        $(".tabcontent.ajax").hide(); //Hide all tab content

        var activeTab = $(this).attr("href"); //Find the href attribute value to identify the active tab + content          
        $("#" + activeTab).fadeIn(); //Fade in the active ID content
        //         $("#" + activeTab + " .form-control:first").focus();
        return false;
    });       
});
</script>


<div class="card card-primary">
    <div class="card-header">
        <div class="row">
            <div class="panel-title ajax-title col-11 m-0 win-drag"></div>

            <div class="col-1 m-0">
                <div class="close">
                    <a href="javascript:HideContent('div-ajax-panel')" title="cerrar ventana">
                        <i class="fa fa-close"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="div-panel-body-ajax" class="card-body">
        <nav style="margin-bottom: 10px;">
            <ul class="nav nav-tabs" role="tablist">
                <li id="nav-tab3" class="nav-item ajax"><a class="nav-link ajax" href="tab3">Generales</a></li>
                <li id="nav-tab4" class="nav-item ajax"><a class="nav-link ajax" href="tab4">Participantes</a></li>
            </ul>
        </nav>

        <?php if ($signal == 'lmatter') { ?>
        <form id="form-matter" name="form-matter" action="javascript:" method="post">
            <input type="hidden" id="exect" name="exect" value="<?= $action ?>" />
            <input type="hidden" id="_signal" name="signal" value="<?=$signal?>" />
            <input type="hidden" id="_id" name=id value="<?=$id?>" />
            <input type="hidden" id="_id_evento" name="id_evento" value="<?= $id_evento ?>" />
            <input type="hidden" id="_menu" name="menu" value="form-matter" />
            <input type="hidden" id="_year" name="year" value="<?=$year?>" />

            <input type="hidden" id="_ifaccords" name="ifaccords" value="0" />
            <?php } ?>

            <!-- tab3 -->
            <div class="tabcontent ajax" id="tab3">
                <div class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Número:
                        </label>
                        <div class="col-7">
                            <div id="spinner-numero_matter" class="input-group spinner">
                                <input type="text" name="numero_matter" id="numero_matter" class="form-control"
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
                        <label class="col-form-label col-3">
                            <?php if ($ifaccords) { ?>
                            Fecha de cumplimiento:
                            <?php } else { ?>
                            Hora:
                            <?php } ?>
                        </label>

                        <div class="col-9">
                            <div class="row col-12">
                                <?php if ($ifaccords) { ?>
                                <div class="col-6">
                                    <div class='input-group date' id='div_fecha_matter' data-date-language="es">
                                        <input type='text' id="fecha_matter" name="fecha_matter"
                                            class="form-control" readonly value="<?=$fecha?>" />
                                        <span class="input-group-text"><span
                                                class="fa fa-calendar"></span></span>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="col-6">
                                    <div class="input-group bootstrap-timepicker timepicker" id='div_hora_matter'>
                                        <input type="text" id="hora_matter" name="hora_matter"
                                            class="form-control input-small" readonly value="<?=$hora?>" />
                                        <span class="input-group-text"><i
                                                class="fa fa-calendar-times-o"></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            Responsable:
                        </label>
                        <div class="col-10">
                            <?php
                            /*
                            $obj_tables= new Ttmp_tables_planning($clink);
                            $obj_tables->SetYear($year);
                            $obj_tables->SetIdEvento($id_evento);
                            $array_usuarios= $obj_tables->sql_tusuarios(null, true);
                            */
                    
                            $obj_assist= new Tasistencia($clink);
                            $obj_assist->SetIdEvento($id_evento);
                            $obj_assist->set_id_evento_code($id_evento_code);
                            $obj_assist->SetYear($year);
                            
                            $obj_assist->listar(false);
                            $array_asistencias= $obj_assist->array_asistencias;
                          
                            $obj_user= new Tusuario($clink);

                            foreach ($array_asistencias as $id_assist => $row) {
                                $value= 0;
                                if (!empty($row['id_usuario'])) {
                                    $value= $row['id_usuario'];
                                    $obj_user->Set($row['id_usuario']);
                                    $array_asistencias[$id_assist]['nombre']= $obj_user->GetNombre();
                                    $array_asistencias[$id_assist]['cargo']= $obj_user->GetCargo();
                                }    
                            ?>
                                <input type="hidden" id="asistencia_usuario_<?=$id_assist?>"
                                    name="asistencia_usuario_<?=$id_assist?>" value="<?=$value?>" />

                                <script type="text/javascript" language="javascript">
                                array_asistencias[<?=$id_assist?>] =
                                    "<?=addslashes($array_usuarios[$id_assist]['nombre']).'<br />'.textparse($array_usuarios[$id_assist]['cargo'], true) ?>";
                                </script>
                                <?php } ?>

                                <?php 
                            reset($array_asistencias);
                            foreach ($array_asistencias as $id_assist => $row) { 
                            ?>
                            <input type="hidden" id="asistencia_resp_code_<?=$id_assist?>"
                                name="asistencia_resp_code_<?=$id_assist?>" value="<?=$row['id_code']?>" />
                            <?php } ?>

                            <select name="asistencia_resp" id="asistencia_resp" class="form-control">
                                <option value="0">selecciona...</option>

                                <?php
                              reset($array_asistencias);
                              foreach ($array_asistencias as $id_assist => $row) {
                                  if (empty($row['nombre'])) 
                                      continue;
                                  $nombre= addslashes($array_asistencias[$id_assist]['nombre']);
                                  if (!empty($array_asistencias[$id_assist]['cargo'])) 
                                      $nombre.= ', '.textparse($array_asistencias[$id_assist]['cargo']);
                              ?>
                                <option value="<?=$id_assist?>"
                                    <?php if ($id_assist == $id_asistencia_resp) echo "selected='selected'"?>>
                                    <?= $nombre?></option>
                                <?php  }  ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-form-label col-2">
                            <?=$title_th?>:
                        </label>
                        <div class="col-10">
                            <textarea name="observacion_matter" id="observacion_matter" class="form-control"
                                rows=7><?=$descripcion?></textarea>
                        </div>
                    </div>
                </div>
            </div> <!-- tab1 -->


            <!-- Participantes -->
            <div class="tabcontent ajax" id="tab4">
                <div id="ajax-tab-users">

                </div>
            </div> <!-- tab2 Participantes-->


            <div class="btn-block btn-app">
                <button class="btn btn-primary" type="button" onclick="add_matter()"
                    title="Agregar nueva <?=$ifaccords ? "acuerdo" : "tematica"?>">
                    Aceptar
                </button>
                <button class="btn btn-warning" type="button" onclick="close_matter(false)" title="Cerrar">
                    Cerrar
                </button>
            </div>
            <?php if ($signal == 'lmatter') { ?>
        </form>
        <?php } ?>
    </div>
</div>