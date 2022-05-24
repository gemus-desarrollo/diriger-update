<?php 
/**
 * @author Geraudis Mustelier
 * @copyright 2020
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
require_once "../../php/class/auditoria.class.php";
require_once "../../php/class/lista.class.php";
require_once "../../php/class/tipo_auditoria.class.php";

$action= !empty( $_GET['action']) ? $_GET['action'] : 'list';

$year= $_GET['year'];
$id_lista= $_GET['id_lista'];
$signal= $_GET['signal'];
$panel= !empty($_GET['panel']) ? $_GET['panel'] : null;
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : null;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : null;

$obj_prs= new Tproceso($clink);
$obj_prs->Set($id_proceso == -1 ? $_SESSION['id_entity'] : $id_proceso);
if ($id_proceso == -1) 
    $obj_prs->SetIdEntity($_SESSION['id_entity']);

if ((!empty($id_proceso) && $id_proceso > 0) && $id_proceso != $_SESSION['id_entity']) {
    $obj_prs->get_procesos_up_cascade(null, null, null, true);
    $array_procesos= $obj_prs->array_cascade_up;
} else {
    $obj_prs->get_procesos_down(null, null, null, true);
    $array_procesos= $obj_prs->array_cascade_down;    
}

$obj_user= new Tusuario($clink);

if ($id_proceso > 0) {
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
}

$obj_lista= new Tlista($clink);
$obj_lista->SetYear($year);
$obj_lista->SetIdProceso($id_proceso > 0 ? $id_proceso : $_SESSION['id_entity']);
?>

    <link rel="stylesheet" href="../libs/bootstrap-table/bootstrap-table.min.css">
    <script src="../libs/bootstrap-table/bootstrap-table.min.js"></script>         

    <script type="text/javascript" charset="utf-8">
        $(document).ready( function () {
            <?php if (!is_null($error)) { ?>
                alert("<?=$error?>");
            <?php } ?>
        } );
    </script>

    <script language='javascript'>
        function select_chk_lista(id) {
            $('#id_lista').val(id);
            $('#id_lista_code').val(id > 0 ? $('#id_lista_code_'+id).val() : 0);
            $('#selected_lista').val(id > 0 ? 1 : 0);
            
            if (parseInt(id) == 0) {              
                $('.chk-list').is(':checked', false);
                $('.chk-list').prop('checked', false);
                return;
            }            
        }

        function ejecutar_lista() {
            var id_proceso=  $('#_id_proceso').val();

            if (parseInt($('#selected_lista').val()) == 0) {
                alert("No ha seleccionado ninguna lista de chequeo.");
                return;
            }

            if (id_proceso > 0) 
                to_guia('<?=$panel?>');
            if (id_proceso == -1)
                to_general_resume('<?=$panel?>');    
        }
    </script>

        
    <div class="card card-primary">
       <div class="card-header win-drag">
           <div class="row">
               <div class="panel-title col-11 win-drag">SELECCIONAR LISTA DE CHEQUEO</div>
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
            <input type="hidden" name="menu" value="nota_lista" />

            <input type="hidden" id="_id_proceso" name="_id_proceso" value="<?=$id_proceso ?>" />
            <input type="hidden" id="selected_lista" name="selected_lista" value="0" />
            <input type="hidden" id="cant_lista" name="cant_lista" value="0" />
    
            <?php
            $array_listas= null;

            if (!empty($id_auditoria)) {
                $obj_lista->SetIdAuditoria($id_auditoria);
                $array_listas= $obj_lista->get_lista_by_auditoria();
            }    
   
            if ($id_proceso == -1) {
                $obj_lista->SetIdAuditoria(null);
                $obj_lista->SetYear($year);
                $obj_lista->SetIdProceso($_SESSION['id_entity']);
                $array_listas= $obj_lista->listar(true, false);
            } 
            ?>                

            <?php
            if (!empty($array_listas)) { 
                foreach ($array_listas as $array) { 
            ?>
                    <input type="hidden" id="chk_lista_init_<?=$array['id']?>" name="chk_lista_init_<?=$array['id']?>" value="1" />
            <?php } } ?>

            <table id="table-guest" class="table table-hover table-striped" 
                   data-toggle="table"
                   data-height="420"
                   data-toolbar="#toolbar"
                   data-search="true"
                   data-show-columns="true">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th></th>
                        <th>TÍTULO</th>
                        <th>DESCRIPCIÓN</th>
                        <th>ORIGEN</th>
                    </tr>
                </thead>

                <tbody>
                    <?php 
                    $i= 0;
                    reset($array_listas);
                    foreach ($array_listas as $row) {
                    ?>
                    <tr>
                        <td><?=++$i?></td>
                        <td>
                            <input type="hidden" id="id_lista_code_<?=$row['id']?>" name="id_lista_code_<?=$row['id']?>" value="<?=$row['id_code']?>" />
                            <input type="radio" class="chk-list" id="chk_lista_<?=$row['id']?>" name="chk_lista_<?=$row['id']?>" onclick="select_chk_lista(<?=$row['id']?>)" value="1" />
                        </td>
                        
                        <td><?=$row['nombre']?></td>
                        
                        <td><?= textparse($row['descripcion'])?></td>
                        
                        <td>
                            <?php
                            $obj_prs->Set($row['id_proceso']);
                            $nombre_prs= $obj_prs->GetNombre().', '.$Ttipo_proceso_array[$obj_prs->GetTipo()];
                            echo $nombre_prs;
                            ?>        
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>   

            <input type="hidden" id="cant_lista" name="cant_lista" value="<?=$i?>">

            <?php
            $visible= 'hidden';
            if ($action == 'update' || $action == 'add') 
                $visible= 'visible';
            ?>    
           <!-- buttom -->
           <div id="_submit" class="btn-block btn-app">
               <button class="btn btn-primary" type="button" style="visibility:'<?= $visible ?>';" onclick="ejecutar_lista('<?=$panel?>');">
                    <?= $signal == 'nota' ? "Aceptar" : "Agregar" ?>
               </button>&nbsp;
               <button class="btn btn-danger" type="reset" style="visibility:'<?= $visible ?>';" onclick="select_chk_lista(0);">Limpiar</button>&nbsp;        
               <button class="btn btn-warning" type="reset" onclick="CloseWindow('div-ajax-panel')">Cerrar</button>
           </div>           
        </div> 
     </div>  
