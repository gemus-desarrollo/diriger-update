<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2015
 */

session_start();
require_once "../../php/setup.ini.php";
require_once "../../php/class/config.class.php";

require_once "../../php/config.inc.php";
require_once "../../php/class/connect.class.php";
require_once "../../php/class/lista.class.php";
require_once "../../php/class/tipo_lista.class.php";

$year= !empty($_GET['year']) ? $_GET['year'] : date('Y');
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$id_lista= !empty($_GET['id_lista']) ? $_GET['id_lista'] : 0;

$obj_lista= new Tlista($clink);
$obj_lista->SetYear($year);
$obj_lista->SetIdProceso($id_proceso);

$result= $obj_lista->listar();
?>

<?php while ($row= $clink->fetch_array($result)) { ?>
<input type="hidden" id="id_lista_code_<?=$row['_id']?>" name="id_lista_code_<?=$row['_id']?>" value="<?=$row['_id_code']?>" />
<?php } ?>

 <div class="form-group row">
     <label class="col-form-label col-1">
         Lista de chequeo:
     </label>
     <div class="col-11">
         <select class="form-control" id="lista" name="lista"> 
             <option value="0">...   </option>
               <?php
               $result->data_seek(0);
               while ($row= $clink->fetch_array($result)) { 
               ?>
             <option value="<?=$row['_id']?>"><?=$row['nombre']?></option>
             <?php } ?>
         </select>	                                                   
     </div>
 </div> 

<div class="btn-block btn-app">
    <button type="button" class="btn btn-primary" onclick="validar_lista()">Aceptar</button> 
    <button type="button" class="btn btn-danger" onclick="HideContent('div-ajax-panel-lista')">Cerrar</button> 
</div>