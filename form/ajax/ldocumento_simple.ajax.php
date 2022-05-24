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
require_once "../../php/class/document.class.php";

$id= !empty($_GET['id']) ? $_GET['id'] : 0;
$action= !empty($_GET['action']) ? $_GET['action'] : 'list';
$year= !empty($_GET['year']) ? $_GET['year'] : $_SESSION['current_year'];
$month= null;
$month= !is_null($_GET['month']) ? $_GET['month'] : 0;
if ($year == $_SESSION['current_year'] && is_null($month)) 
    $month= $_SESSION['current_month'];

$id_evento= !empty($_GET['id_evento']) ? $_GET['id_evento'] : 0;
$id_proceso= !empty($_GET['id_proceso']) ? $_GET['id_proceso'] : 0;
$id_proyecto= !empty($_GET['id_proyecto']) ? $_GET['id_proyecto'] : 0;
$id_auditoria= !empty($_GET['id_auditoria']) ? $_GET['id_auditoria'] : 0;
$id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : 0;
$id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : 0;
$id_requisito= !empty($_GET['id_requisito']) ? $_GET['id_requisito'] : 0;

$obj_doc= new Tdocumento($clink);

$obj_doc->SetYear($year);
$obj_doc->SetMonth($month);

$id_responsable= 0;
$keywords= null;
$descripcion= null;

$exect= false;
if (!empty($id_evento)) { 
    $obj_doc->SetIdEvento($id_evento);
    $exect= true;
}    
if (!empty($id_procesoo)) {
    $obj_doc->SetIdProceso($id_proceso);
    $exect= true;
}    
if (!empty($id_auditoria)) {
    $obj_doc->SetIdAuditoria($id_auditoria);
    $exect= true;
}    
if (!empty($id_proyecto)) {
    $obj_doc->SetIdProyecto($id_proyecto);
    $exect= true;
}    
if (!empty($id_nota)) {
    $obj_doc->SetIdNota($id_nota);
    $exect= true;
}    
if (!empty($id_riesgo)) {
    $obj_doc->SetIdRiesgo($id_riesgo);
    $exect= true;
}    
if (!empty($id_requisito)) {
    $obj_doc->SetIdRequisito($id_requisito);
    $exect= true;
}

$array_files= $exect ? $obj_doc->listar(false) : null;
?>

<table class="table table-striped table-hover" 
   data-toggle="table" 
   data-height="420" 
   data-toolbar="#toolbar"
   data-search="true"
   data-show-columns="true">
   <thead>
       <tr>
           <th>No.</th>
           <?php if ($action == 'update' || $action == 'add'|| $action == 'edit') { ?>
           <th></th>
           <?php } ?>
           <th>Nombre</th>
           <th>Descripción</th>
           <th>Fecha/Hora</th>
       </tr>
   </thead>

   <tbody>
   <?php
   $array_class= array('');
   $i= 0;
   foreach ($array_files as $row) {
       ++$i;
       ?>
       <tr>
           <td>
               <?=$i?>
               <input type="hidden" id="id_doc_<?=$i?>" name="id_doc_<?=$i?>" value="<?=$row['id']?>" />
               <input type="hidden" id="id_usuario_doc_<?=$i?>" name="id_usuario_doc_<?=$i?>" value="<?=$row['id_usuario']?>" />
           </td>

           <?php if ($if_jefe || ($_SESSION['id_usuario'] == $row['id_responsable'] || $_SESSION['id_usuario'] == $row['id_usuario'])) { ?>
           <td>
               <a class="btn btn-danger btn-sm" href="#" title="Eliminar" onclick="del_doc(<?=$i?>);" style="cursor:pointer; visibility: <?=$visible?>">
                   <i class="fa fa-trash"></i>Eliminar
               </a>
               <a class="btn btn-warning btn-sm" href="#" title="Editar" onclick="edit_doc(<?=$i?>);" style="cursor:pointer; visibility: <?=$visible?>">
                   <i class="fa fa-edit"></i>Editar
               </a>
           </td>
           <?php } ?>

           <td>
               <?php 
               if (isset($obj_doc)) unset($obj_doc);
               $obj_doc= new Tdocumento($clink);
               $obj_doc->Set($row['id']);

               $type= get_file_type($obj_doc->filename);
               $mime= mime_type($type['ext']);  

               $array= get_file_type($row['nombre'])
               ?>

               <a href="../php/download.interface.php?id=<?=$row['id']?>" name="<?=$obj_doc->filename?>" type="<?=$mime?>" target="_blank">
                   <img src="../img/<?=$array['img']?>" alt="<?=$array['type']?>" title="<?=$array['type']?>" />
                   <?=$row['nombre']?>
               </a>

               <input type="hidden" id="nombre_doc_<?=$i?>" name="nombre_doc_<?=$i?>" value="<?=$row['nombre']?>" />
           </td>

           <td>
               <?=$row['descripcion']?>
               <input type="hidden" id="descripcion_doc_<?=$i?>" name="descripcion_doc_<?=$i?>" value="<?=$row['descripcion']?>" />
               <input type="hidden" id="keywords_doc_<?=$i?>" name="keywords_doc_<?=$i?>" value="<?=$row['keywords']?>" />
           </td>

           <td>
               <?=odbc2time_ampm($row['cronos'])?>
           </td>
       </tr>
   <?php } ?>
   </tbody>
</table>

<input type="hidden" id="cant_doc" name="cant_doc" value="<?=$i?>">  
