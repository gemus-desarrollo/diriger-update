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
require_once "../../php/class/orgtarea.class.php";
require_once "../../php/class/evento.class.php";
require_once "../../php/class/proceso.class.php";

$id_evento= $_GET['id_evento'];
$signal= $_GET['signal'];
$id_proceso= $_GET['id_proceso'];

$year= $_GET['year'];
$month= $_GET['month'];
$day= $_GET['day'];

$id_usuario= !empty($_GET['id_calendar']) ? $_GET['id_calendar'] : $_SESSION['id_usuario'];
$id_responsable= !empty($_GET['id_responsable']) ? $_GET['id_responsable'] : $_SESSION['id_usuario'];
$id_asignado= !empty($_GET['id_asignado']) ? $_GET['id_asignado'] : $_SESSION['id_asignado'];
$init_row_temporary= !is_null($_GET['init_row_temporary']) ? $_GET['init_row_temporary'] : 0;

$obj= new Tevento($clink);
$obj->Set($id_evento);

$id_evento_code= $obj->get_id_evento_code();
$id_evento_ref= $obj->get_id_evento_ref();
$id_evento_ref_code= $obj->get_id_evento_ref_code();

$id_tarea= $obj->GetIdTarea();
$id_tarea_code= $obj->get_id_tarea_code();

$current_date= $obj->GetFechaInicioPlan();

$time= new TTime($obj->GetFechaInicioPlan());
if (empty($year)) 
    $year= $time->GetYear();
if (empty($month)) 
    $month= $time->GetMonth();
if (empty($day)) 
    $day= $time->GetDay();

$origen_date= null;

if (!empty($id_evento_ref)) {
    unset($obj);
    $obj= new Tevento($clink);
    $obj->Set($id_evento_ref);
    $origen_date= $obj->GetFechaInicioPlan();
}
?>

    <script type="text/javascript" charset="utf-8">
        function submit_url() {
            var form= document.forms['fedit'];

            var _radio_date= 0;
            var fecha_origen= form.current_date.value;
            var id= form.id.value;

            var id_usuario= form.id_usuario.value;
            var id_proceso= form.id_proceso.value;
            var signal= form.signal.value;
            var year= form.year.value;
            var month= form.month.value;

            if (form.radio_date[0].checked) 
                _radio_date= 0;
            if (form.radio_date[1].checked) 
                _radio_date= 1;
            if (form.radio_date[2].checked) 
                _radio_date= 2;

            if (form.id_ref.value > 0 && _radio_date > 0) {
                id= form.id_ref.value;
                if (_radio_date == 1) 
                    fecha_origen= form.current_date.value;
                if (_radio_date == 2) 
                    fecha_origen= form.origen_date.value;
            }

            var url= '../form/fevento.php?version=&action=update&signal='+signal+'&id='+id+'&id_calendar='+id_usuario+'&year='+year;
            url+= '&month='+month+'&id_proceso='+id_proceso+'&fecha_origen='+encodeURI(fecha_origen)+'&_radio_date='+_radio_date;
            url+= '&init_row_temporary=<?=$init_row_temporary?>';
 
            parent.app_menu_functions= false;
            $('#_submit').hide();
            $('#_submited').show();
            
            self.location.href= url;    
        }
    </script>   
    <script type="text/javascript">	
        $(document).ready(function() {  
    
            <?php if (empty($id_evento_ref)) { ?>
                // alert("Esta actividad o evento ocurre en un solo día, por lo que no es periodica. Solo se editará en el día de referencia.");
                submit_url(0);
            <?php } ?>            
            
            <?php if (!is_null($error)) { ?>
            alert("<?=str_replace("\n"," ", addslashes($error)) ?>");
            <?php } ?>
        });	
    </script>
    
    
    <form id="fedit" name="fedit" action="javascript:submit_url(1)"  method=post>	
        <input type="hidden" name="id" value="<?= $id_evento ?>" />
        <input type="hidden" id="id_ref" name="id_ref" value="<?= $id_evento_ref ?>" />

        <input type="hidden" name="id_tarea" value="<?= $id_tarea ?>" />

        <input type="hidden" name="id_proceso" value="<?= $id_proceso ?>" />
        <input type="hidden" name="id_proceso_code" value="<?= $id_proceso_code ?>" />

        <input type="hidden" name="menu" value="fedit" />	

        <input type="hidden" name="signal" id="signal" value="<?= $signal ?>" />

        <input type="hidden" id="_radio_date" name="_radio_date" value="0" />

        <input type="hidden" name="year" id="year" value="<?= $year ?>" />
        <input type="hidden" name="month" id="month" value="<?= $month ?>" />
        <input type="hidden" name="day" id="day" value="<?= $day ?>" />

        <input type="hidden" name="origen_date" id="origen_date" value="<?= $origen_date ?>" />
        <input type="hidden" name="current_date" id="current_date" value="<?= $current_date ?>" />

        <input type="hidden" name="id_usuario" id="id_usuario" value="<?= $id_usuario ?>" />
   
        <div class="alert alert-info">
            <strong>Actividad: </strong><?=$obj->GetNombre()?>
            <strong>Fecha y hora: </strong><?=odbc2date($current_date)?>
        </div>
        
        <legend>Calendario:</legend>
     
        <div class="checkbox">
            <label>
                <input type="radio" name="radio_date" id="radio_date0" value=0 checked="checked" />
                Modificar solo en la fecha <span class="text text-danger"><?=odbc2date($current_date)?></span>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <input type="radio" name="radio_date" id="radio_date1" value=1 />
                El evento es periodico modificar a partir de la fecha <span class="text text-danger"><?=odbc2date($current_date)?></span>, incluye todas las posteriores
            </label>
        </div>
        <div class="checkbox">
            <label>
                <input type="radio" name="radio_date" id="radio_date2" value=2 />
                El evento es periodico editar en todas las fechas programadas. Primera fecha <span class="text text-danger"><?=odbc2date($origen_date)?></span>
            </label>
        </div>

        <?php 
        $display= 'block';

        if (!empty($id_evento_ref)) { 
            $display= 'none';
        ?>
            <div id="_submit" class="btn-block btn-app">
                <button type="submit" class="btn btn-primary">Aceptar</button> 
                <button type="reset" class="btn btn-warning" onclick="CloseWindow('div-ajax-panel')">Cancelar</button>
             </div>
        <?php } ?>

    <div id="_submited" class="submited" align="center" style="display:<?= $display?>">
	<img src="../img/loading.gif" alt="cargando" />     Por favor espere, la operación puede tardar unos minutos ........
    </div>
        
</form>


