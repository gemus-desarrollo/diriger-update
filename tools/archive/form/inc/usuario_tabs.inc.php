<input type="hidden" id="t_cant_multiselect-users" name="t_cant_multiselect-users" value=0 />
<input type="hidden" id="cant_multiselect-users" name="cant_multiselect-users" value=0 />

<?php
if (isset($obj_user)) unset($obj_user);
$obj_user= new Tusuario($clink);
if (is_null($use_copy_tusuarios) || !isset($use_copy_tusuarios)) 
    $use_copy_tusuarios= false;
$obj_user->set_use_copy_tusuarios($use_copy_tusuarios);

$result_user= $obj_user->listar();
$max_id= 0;
?>

 <div class="container-fluid">
     <div class="row">
        <div class="col-xs-5">
            <legend>Usuarios y grupos</legend>
        </div>
        <div class="col-xs-2"></div>
        <div class="col-xs-5">
            <legend>Seleccionados</legend>
        </div>
     </div>
     
     <script type="text/javascript" charset="utf-8">
       $(document).ready(function() {
           <?php
            $obj_user= new Tusuario($clink);
            $obj_user->set_user_date_ref($user_ref_date);
            $result_user= $obj_user->listar($clink);

            while ($row= $clink->fetch_array($result_user)) {
            ?>
                   var _multiselect_btn_user<?=$row['_id']?>= null;
            <?php }  ?>
           
           
           var data_users= [
           <?php 
           $i= 0; 
           $j= 0;

           $obj_grp= new Tgrupo($clink);
           $result_grp= $obj_grp->listar();

           while ($row= $clink->fetch_array($result_grp)) {
               $value= $array_grupos[$row['_id']] ? 1 : 0;
               ++$j; 
               if ($value) 
                   ++$i;
               $colom= (int)$j > 1 ? "," : "";
           ?>

               <?=$colom?>['grp<?=$row['_id']?>',"<i class='fa fa-users text-danger'></i><?= addslashes($row['nombre'])?>", <?=$value?>, 0, 0, '']
           <?php  
           }

           $clink->data_seek($result_prs);
           if (isset($obj_user)) unset($obj_user);
           $obj_user= new Tusuario($clink);

           ($badger->freeassign || $config->freeassign) ? $obj_user->set_use_copy_tusuarios(false) : $obj_user->set_use_copy_tusuarios(true);
           $obj_user->set_user_date_ref($user_ref_date);
           ($badger->freeassign || $config->freeassign) ? $obj_user->SetIdProceso($_SESSION['local_proceso_id']) : $obj_user->SetIdProceso(null) ;
           $result_user= $obj_user->listar(false, null, _NO_LOCAL);

           if (count($obj_user->array_usuarios) > 0) {
               ++$j;
               $colom= (int)$j > 1 ? "," : "";
           ?>
           <?=$colom?>[0,"<label><?=$_SESSION['empresa']?></label>",0,0, 0, '<?=color_proccess($_SESSION['entity_tipo'])?>']

           <?php
           foreach ($result_user as $row) {
               if (empty($row['nombre'])) 
                   continue;
               
               if ($row['id'] == _USER_SYSTEM)
                   continue;
               if (!empty($id_user_restrict) && $row['id'] == $id_user_restrict)
                   continue;

               $value= $array_usuarios[$row['id']] ? 1 : 0;
               ++$j;
               $colom= (int)$j > 1 ? "," : ""; 
               if ($value) ++$i;
               $name= textparse($row['nombre']);
               $name.= !empty($row['cargo']) ? ", ".textparse($row['cargo']) : "";  
               ?>

               <?=$colom?>['user<?=$row['id']?>',"<i class='fa fa-user text-danger'></i><?= $name?>", <?=$value?>, _multiselect_btn_user<?=$row['id']?>, 0, '']

           <?php } } ?>  

           <?php   
           while ($row_prs= $clink->fetch_array($result_prs)) {
               if ($row_prs['_id'] == $_SESSION['local_proceso_id']) 
                   continue;
               $_connect= is_null($row_prs['conectado']) ? _NO_LOCAL : $row_prs['conectado'];

               if (!empty($restrict_prs) && $row_prs['tipo'] ==  $restrict_prs) 
                   continue;

               if ($row_prs['_id'] != $_SESSION['local_proceso_id']) 
                   $_connect= ($_connect == _NO_LOCAL) ? _NO_LOCAL : _LOCAL;
               else 
                   $_connect= _NO_LOCAL;

               $obj_user->SetIdProceso($row_prs['_id']);

               if ($row_prs['tipo'] >= _TIPO_DIRECCION && $row_prs['conectado'] == _NO_LOCAL)
                   $result_user= $obj_user->listar(false, null, _LOCAL);
               else
                   $result_user= $obj_user->listar(false, null, _NO_LOCAL);

               if (count($obj_user->array_usuarios) == 0) 
                   continue; 

               $name= $_connect == _LOCAL ? "<i class='fa fa-wifi text-danger'></i>" : "";
               $name.= "<label>{$row_prs['_nombre']}</label>";
               ++$j;
               $colom= (int)$j > 1 ? "," : ""; 
           ?> 

               <?=$colom?>[0,"<?=$name?>",0,0, 0, '<?=color_proccess($row_prs['tipo'])?>']

           <?php
               foreach ($result_user as $row) {
                   if (empty($row['nombre'])) 
                       continue;
                   
                   if ($row['id'] == _USER_SYSTEM)
                       continue;
                   if (!empty($id_user_restrict) && $row['id'] == $id_user_restrict)
                       continue;

                   $value = $array_usuarios[$row['id']] ? 1 : 0;
                   ++$j; 
                   $colom= (int)$j > 1 ? "," : "";
                   if ($value) ++$i;
                   $name= textparse($row['nombre']);
                   $name.= !empty($row['cargo']) ? ", ".textparse($row['cargo']) : "";                
           ?> 

                <?=$colom?>['user<?=$row['id']?>',"<i class='fa fa-user text-danger'></i><?=$name?>", <?=$value?>, _multiselect_btn_user<?=$row['id']?>, 0, ''] 

           <?php } } ?>     

           ];

           multiselect('multiselect-users', data_users);

           $("#t_cant_multiselect-users").val(<?= $j ?>);
           $("#cant_multiselect-users").val(<?= $i ?>);
       }); 

    </script>        

    <div id="multiselect-users"></div>
 </div>
 