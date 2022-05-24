<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2014
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/time.class.php";
require_once "../../php/class/usuario.class.php";
require_once "../../php/class/time.class.php";

require_once "../../php/class/proceso.class.php";
require_once "../../php/class/nota.class.php";
require_once "../../php/class/auditoria.class.php";

require_once "../../php/class/tipo_auditoria.class.php";

$action= !empty( $_GET['action']) ? $_GET['action'] : 'list';

$year= $_GET['year'];
$id_auditoria= $_GET['id_auditoria'];
$origen= $_GET['origen'];
$id_proceso= $_GET['id_proceso'];
$fecha_inicio= urldecode($_GET['fecha_inicio']);
$fecha_fin= urldecode($_GET['fecha_fin']);
$signal= $_GET['signal'];
$panel= !empty($_GET['panel']) ? $_GET['panel'] : null;

if (!is_null($fecha_inicio)) 
    $fecha_inicio= date2odbc($fecha_inicio);
if (!is_null($fecha_fin)) 
    $fecha_fin= date2odbc($fecha_fin);

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso);

if ($id_proceso != $_SESSION['id_entity']) {
    $obj_prs->get_procesos_up_cascade(null, null, null, true);
    $array_procesos= $obj_prs->array_cascade_up;
} else {
    $obj_prs->get_procesos_down(null, null, null, true);
    $array_procesos= $obj_prs->array_cascade_down;    
}

$obj_user= new Tusuario($clink);
$obj= new Tauditoria($clink);

$obj->SetYear($year);
$obj->SetFechaInicioPlan($fecha_inicio);
$obj->SetFechaFinPlan($fecha_fin);
$obj->SetOrigen($origen);

$array= array();
if (count($array_procesos)) {
    $obj->SetIdProceso(null);
    foreach ($array_procesos as $key => $prs) 
        $array[]= $prs['id'];
} else {
    $obj->SetIdProceso($id_proceso);
    $array= null;
}

$result= $obj->listar(null, $array);

while ($row= $clink->fetch_array($result)) {
    $obj_prs->Set($row['_id_proceso']);
    $name_prs= "<b>".$obj_prs->GetNombre()."</b>, ".$Ttipo_proceso_array[(int)$obj_prs->GetTipo()];
                            
    if (!empty($array_auditorias[$row['_id']])) {
        $array_auditorias[$row['_id']]['proceso'].= "<br/>".$name_prs;
        $array_auditorias[$row['_id']]['id_proceso']= $row['_id_proceso'];
    } else {
        $array_auditorias[$row['_id']]= array('id'=>$row['_id'], 'id_code'=>$row['_id_code'], 
            'nombre'=>$row['nombre'], 'fecha_inicio_plan'=>$row['fecha_inicio_plan'], 
            'fecha_fin_plan'=>$row['fecha_fin_plan'],'periodic'=>$row['periodic'], 
            'id_responsable'=>$row['id_responsable'],'cargo'=>$row['cargo'], 'organismo'=>$row['organismo'], 
            'origen'=>$row['origen'], 'id_tipo_auditoria'=>$row['id_tipo_auditoria'], 'objetivos'=>$row['objetivos'], 
            'lugar'=>$row['lugar'], 'proceso'=>$name_prs, 'id_proceso'=>$row['_id_proceso']);
    }
}

$obj_tipo= new Ttipo_auditoria($clink);
$obj_tipo->SetYear($year);
$obj_tipo->SetIdProceso($id_proceso);
?>

<link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
<script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>         

<script language='javascript'>
    function select_chk(id) {
        $('#id_auditoria').val(id);
        $('#id_auditoria_code').val(id > 0 ? $('#id_auditoria_code_'+id).val() : 0);
        $('#auditoria').val(id > 0 ? $('#auditoria_'+id).val() : '');
        $('#selected').val(id > 0 ? 1 : 0);
        
        if (parseInt(id) == 0) {              
            $('.chk-radio').is(':checked', false);
            $('.chk-radio').prop('checked', false);
            
            if ($('#nav-tab8')) {
                $('#nav-tab8').hide();
                $('#tab8').hide();                     
            }
            return;
        }

        if ($('#nav-tab8')) {
            $('ul.nav.nav-tabs li').removeClass('active');
            $(".tabcontent").hide();

            $('#nav-tab8').show();
            $('#tab8').show();   
            $('#nav-tab8').addClass('active');
        }
    }

    function ejecutar_auditoria() {
        if (parseInt($('#selected').val()) == 0) {
            alert("No ha seleccionado ninguna auditoría o acción de control.", function(ok) {
                refreshp();
            });
        }

        <?php if ($signal == "fnota") { ?>
            CloseWindow('div-ajax-panel'); 
        <?php } ?>
        <?php if ($signal == "nota" && $panel == "filter") { ?>
            refreshp(); 
        <?php } ?>
        <?php if ($signal == "nota" && $panel == "guide") { ?>
            mostrar_guia('guide'); 
        <?php } ?>
        <?php if ($signal == "nota" && $panel == "resume") { ?>
            mostrar_guia('resume'); 
        <?php } ?>
    }

</script>

<script type="text/javascript" charset="utf-8">
    $(document).ready( function () {
        <?php if (!empty($id_auditoria) && $id_auditoria > 0) { ?>
            select_chk(<?=$id_auditoria?>, <?=$id_proceso?>);
        <?php } ?>
                
        <?php if (!is_null($error)) { ?>
            alert("<?=$error?>");
        <?php } ?>
    } );
</script>

<?php 
foreach ($array_auditorias as $row) {
    if ($row['id'] == $id_auditoria) { 
    ?>
        <script type="text/javascript">
            $('#selected')val(<?=$id_auditoria?>);
        </script>
<?php } } ?>
    
        
<div class="card card-primary">
    <div class="card-header win-drag">
        <div class="row">
            <div class="panel-title col-11 win-drag">SELECCIONAR ACCIÓN DE CONTROL</div>
            <div class="col-1 pull-right">
                <div class="close">
                    <a href= "javascript:CloseWindow('div-ajax-panel');" title="cerrar ventana">
                        <i class="fa fa-close"></i>
                    </a>
                </div> 
            </div>              
        </div>
    </div>
    
    <div class="card-body form info-panel">
        <input type="hidden" name="exect" value="<?=$action?>" />
        <input type="hidden" name="id" value="<?=$id?>" />
        <input type="hidden" name="menu" value="nota_tarea" />
        <input type="hidden" id="selected" name="selected" value="0" />
        <input type="hidden" id="cant_" name="cant_" value="0" />

        <table id="table" class="table table-hover table-striped" 
                data-toggle="table"
                data-height="400"
                data-search="true"
                data-show-columns="true"> 
            <thead>
                <tr>
                    <th>No.</th>
                    <th>UNIDAD AUDITADA</th>
                    <th>ORIGEN</th>
                    <th>TIPO</th>
                    <th>FECHA</th>
                    <th>ENTIDAD AUDITORA</th>
                    <th>JEFE DEL EQUIPO AUDITOR</th>
                    <th>OBJETIVOS</th>
                    <th>ALCANCE</th>
                </tr>
            </thead>

            <tbody>
            <?php
            $i= 0;
            reset($array_auditorias);
            foreach ($array_auditorias as $row) {
                if (boolean($row['periodic'])) 
                    continue;
                                
                $checked= ($row['id'] == $id_auditoria) ? "checked='checked'" : null;

                $obj_tipo->Set($row['id_tipo_auditoria']);
                $tipo_nombre= $obj_tipo->GetNombre();
                $auditoria= "Origen: {$Ttipo_nota_origen_array[$row['origen']]}     Tipo: $tipo_nombre";
                $auditoria.= "\nInicio: ".odbc2date($row['fecha_inicio_plan'])."  fin:".odbc2date($row['fecha_fin_plan'])."\nOrganismo: ".$row['organismo'];
                $email= $obj_user->GetEmail($row['id_responsable']);
                $responsable= textparse($email['nombre'], true);
                if ($email['cargo']) 
                    $responsable.= ', '.textparse($email['cargo'], true);
                
                $auditoria.= "\nResponsable: $responsable";
                $auditoria.= "\nObjetivos: ".textparse($row['objetivos'], true, true);
                $auditoria.= "\nAlcance: ".textparse($row['lugar'], true, true);
                ?>

                <tr>
                    <td>
                        <?=++$i?>
                        <label class="checkbox text" style="margin: 8px;">
                            <input type="radio" id="chk_auditoria_<?=$row['id']?>" class="chk-radio" name="chk_auditoria" 
                                onclick="select_chk(<?=$row['id']?>)" <?=$checked?> value="<?=$row['id']?>" />  
                        </label>                               
                    </td>
                    <td>
                        <?=$row['proceso'] ?>
                    </td>
                    <td>
                        <input type="hidden" id="id_auditoria_code_<?=$row['id']?>" name="id_auditoria_code_<?=$row['id']?>" 
                                                                                                value="<?=$row['id_code']?>" /> 
                        <?=$Ttipo_nota_origen_array[$row['origen']]?>          
                    </td>
                    <td>
                        <?=$tipo_nombre?>
                    </td>
                    <td>
                        <?=odbc2date($row['fecha_inicio_plan'])?>
                    </td>
                    <td>
                        <?=$row['organismo']?>
                    </td>
                    <td>
                        <?=$responsable?>
                    </td>
                    <td>
                        <?= purge_html($row['objetivos'])?>
                    </td>
                    <td>
                        <?= textparse($row['lugar'])?>
                        <input type="hidden" id="auditoria_<?=$row['id']?>" name="auditoria_<?=$row['id']?>" value="<?= $auditoria?>" />
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <input type="hidden" id="cant" name="cant" value="<?=$i?>">

        <?php
        $visible= 'hidden';
        if ($action == 'update' || $action == 'add') 
            $visible= 'visible';
        ?>    
        <!-- buttom -->
        <div id="_submit" class="btn-block btn-app">
            <button class="btn btn-primary" type="button" style="visibility:'<?= $visible ?>';"
                                                        onclick="ejecutar_auditoria('<?=$panel?>');">
                <?= $signal == 'nota' ? "Aceptar" : "Agregar" ?>
            </button>&nbsp;
            <button class="btn btn-danger" type="reset" style="visibility:'<?= $visible ?>';" onclick="select_chk(0, <?=$id_proceso?>);">
                Limpiar
            </button>&nbsp;        
            <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
        </div>           
    </div> 
    </div>  
            
            





   
