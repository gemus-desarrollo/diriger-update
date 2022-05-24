<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */
session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/proceso.class.php";
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/plantrab.class.php";

$_SESSION['debug']= 'no';

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$month= !empty($_GET['month']) ? $_GET['month'] : date('m');
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : $_SESSION['local_proceso_id'];

$time= new TTime();
$actual_year= $time->GetYear();
$actual_month= (int)$time->GetMonth();
$actual_day= $time->GetDay();

$time->SetYear($year);
$time->SetMonth($month);
$time->SetDay($day);
$lastday= $time->longmonth();

$startDate= "01/".str_pad($month,2,'0', STR_PAD_LEFT)."/$year";
$endDate= "$lastday/".str_pad($month,2,'0', STR_PAD_LEFT)."/$year";

$id_usuario= $_SESSION['id_usuario'];
$error= !empty($_GET['error']) ? urldecode($_GET['error']) : NULL;

$obj= new Tevento($clink);
$obj->SetIdAuditoria($id_auditoria);
$obj->Set();
$obj->SetYear($year);

$_month= str_pad($month, 2, "0", STR_PAD_LEFT);
$fecha_inicio= "01/{$_month}/{$year}";
$fecha_fin= "{$lastday}/{$_month}/{$year}";

$prs_name= null;

if (!empty($id_proceso)) {
    $obj_prs= new Tproceso($clink);	
    $obj_prs->SetIdProceso($id_proceso);
    $obj_prs->Set();
    $prs_name= $obj_prs->GetNombre().", ".$Ttipo_proceso_array[$obj_prs->GetTipo()];
    unset($obj_prs);
} 
    
$array_procesos= null;
$obj_prs= new Tproceso($clink);	
$array_procesos= $obj_prs->getProceso_if_jefe($_SESSION['id_usuario'], $id_proceso, _TIPO_DIRECCION);

if (!is_null($array_procesos)) {
    $i= 0;
    foreach ($array_procesos as $array) {
        if ($i >0) $prs_name= ",";
        $prs_name.= " ".$array['nombre'];
        ++$i;
    }
    reset($array_procesos);
}

$toshow= false;
$toshow= $obj->get_toshow_plan($id_proceso);

$obj->SetIdResponsable($_SESSION['id_usuario']);
?>

    <script language='javascript' type="text/javascript" charset="utf-8">
        function validar() {
            var form= document.forms['freproevento'];
            var text;

            var date_inicio= $('#fecha_inicio').val() + ' 00:00:00';
            var date_fin= $('#fecha_fin').val() + ' 23:59:00';
            var diff= DiferenciaFechas(date_fin, date_inicio, 's');  

            if (diff < 0 || diff == 0) {
                alert("La fecha de inicio no puede ser igual o posterior a la fecha final");
                return;
            }

            $('#observacion').val(trim_str($('#observacion').val()));

            if (Entrada($('#observacion').val()) && $('#observacion').val().length < 5) {
                text= "Por favor, la explicación no debe ser tan corta, explicaciones como -ok-, -si-, -no- no son aceptados, ";
                text+= "por favor sea más explicito."
                alert(text);
                return;
            }

            imprimir(3);
        }
    </script>
 
    <script type="text/javascript">	
        $(document).ready(function() {
            $('#div_fecha_inicio').datepicker({
                format: 'dd/mm/yyyy',
                startDate: '<?=$startDate?>',
                endDate: '<?=$endDate?>'                  
             });
            $('#div_fecha_fin').datepicker({
                format: 'dd/mm/yyyy',
                startDate: '<?=$startDate?>',
                endDate: '<?=$endDate?>'                   
             });               
                 
            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        }); 
    </script>
    
    
    <form id="freproevento" name="fpointsevento" class="form-horizontal" action="javascript:validar()"  method=post>
        <input type="hidden" name="exect" value="set" />	

        <input type=hidden name="id_proceso" value="<?= $id_proceso ?>" />
        <input type=hidden name="id_proceso_code" value="<?= $id_proceso_code ?>" />

        <input type="hidden" id="year" name="year" value="<?= $year ?>" />
        
        <input type="hidden" id="_fecha_inicio" name="_fecha_inicio" value="<?= $fecha_inicio ?>" />
        <input type="hidden" id="_fecha_fin" name="_fecha_fin" value="<?= $fecha_fin ?>" />

        <input type="hidden" name="menu" value="fpointsevento" />	
        
        <div class="form-group row">
            <label class="col-form-label col-2">
                Desde:
            </label>
            <div class="col-xs-6 col-4">
                <div class='input-group date' id='div_fecha_inicio' data-date-language="es">
                    <input type='text' id="fecha_inicio" name="fecha_inicio" class="form-control" readonly value="<?=$fecha_inicio?>" />
                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                </div>	                 
            </div>          

            <label class="col-form-label col-2">
                hasta:
            </label>
            <div class="col-xs-6 col-4">
                <div class='input-group date' id='div_fecha_fin' data-date-language="es">
                    <input type='text' id="fecha_fin" name="fecha_fin" class="form-control" readonly value="<?=$fecha_fin?>" />
                    <span class="input-group-text"><span class="fa fa-calendar"></span></span>
                </div>	                 
            </div>           
        </div>

        <div class="form-group row">
            <label class="col-form-label col-xs-3 col-sm-2 col-md-2">
                Observaciones:
            </label>        
            <div class="col-xs-9 col-sm-10 col-md-10">
                <textarea id="observacion" name="observacion" class="form-control" rows="7"></textarea>
            </div>
        </div>

        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="submit"> Aceptar</button>  
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
        </div>

        <div id="_submited" class="submited" align="center" style="display:none">
            <img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operaciÃ³n puede tardar unos minutos ........
        </div> 
    </form>

