<input type="hidden" id="t_cant_multiselect-users" name="t_cant_multiselect-users" value="0" />
<input type="hidden" id="cant_multiselect-users" name="cant_multiselect-users" value="0" />

 <div class="container-fluid">
     <div class="row">
        <div class="col-5">
            <legend>Usuarios y grupos</legend>
        </div>
        <div class="col-2"></div>
        <div class="col-5">
            <legend>Seleccionados</legend>
        </div>
     </div>

     <script type="text/javascript" charset="utf-8">
       $(document).ready(function() {
            var data_users= [
            <?php
            $i= 0;
            $j= 0;

            $obj_grp= new Tgrupo($clink);
            $obj_grp->SetIdentity($_SESSION['id_entity']);
            $result_grp= $obj_grp->listar();

            while ($row= $clink->fetch_array($result_grp)) {
                $value= $array_grupos[$row['_id']] ? 1 : 0;
                ++$j;
                if ($value)
                    ++$i;
                $colom= (int)$j > 1 ? "," : "";
            ?>
                <?=$colom?>['grp<?=$row['_id']?>', "<i class='fa fa-users text-danger'></i><?= addslashes($row['nombre'])?>", <?=$value?>, 0, 0, '']
            <?php
            }

            $clink->data_seek($result_prs);
            if (isset($obj_user)) unset($obj_user);
            $obj_user= new Tusuario($clink);

            ($badger->freeassign || $config->freeassign) ? $obj_user->set_use_copy_tusuarios(false) : $obj_user->set_use_copy_tusuarios(true);
            $obj_user->set_user_date_ref($user_ref_date);
            ($badger->freeassign || $config->freeassign) ? $obj_user->SetIdProceso($_SESSION['id_entity']) : $obj_user->SetIdProceso(null) ;
            $result_user= $obj_user->listar(false, null, _NO_LOCAL);

            if (count($obj_user->array_usuarios) > 0
                    && ($_SESSION['local_proceso_id'] == $_SESSION['id_entity'] || $_SESSION['nivel'] == _GLOBALUSUARIO)) {
                ++$j;
                $colom= (int)$j > 1 ? "," : "";
            ?>
            <?=$colom?>[0,"<label><?=$_SESSION['empresa']?></label>",0,0, 0, '<?=color_proccess($_SESSION['entity_tipo'])?>']

            <?php
                foreach ($obj_user->array_usuarios as $row) {
                    if (empty($row['nombre']))
                        continue;
                    if ($row['id'] == _USER_SYSTEM)
                        continue;
                    if (!empty($id_user_restrict) && $row['id'] == $id_user_restrict)
                        continue;

                    $value= $array_usuarios[$row['id']] ? 1 : 0;
                    ++$j;
                    $colom= (int)$j > 1 ? "," : "";
                    if ($value)
                        ++$i;
                    $name= textparse($row['nombre'], true);
                    $name.= !empty($row['cargo']) ? ", ".textparse($row['cargo'], true) : "";
                    ?>

                    <?=$colom?>['user<?=$row['id']?>',"<i class='fa fa-user text-danger'></i><?= $name?>", <?=$value?>, 0, 0, '']

            <?php } } ?>

           <?php
            foreach ($result_prs as $row_prs) {
                if ($row_prs['id'] == $_SESSION['local_proceso_id'])
                    continue;
                if ($badge->freeassign < _TO_ALL_ENTITIES) {
                    if ($row_prs['tipo'] <= $_SESSION['entity_tipo'] 
                        && (($row_prs['id'] != $_SESSION['superior_entity_id'] && $row_prs['id'] != $_SESSION['id_entity'])
                            && (!empty($row_prs['id_proceso']) && $row_prs['id_proceso'] != $_SESSION['id_entity'])))
                        continue; 
                    if ($row_prs['id'] == $_SESSION['superior_entity_id']) 
                        continue;
                    if (($signal == "evento" || $signal == "auditoria" || $signal == "tarea" || $signal == "proyecto") 
                            && ($row_prs['id'] == $_SESSION['superior_entity_id'])) 
                        continue;
                    if (!empty($restrict_prs) && $row_prs['tipo'] == $restrict_prs) 
                        continue;                    
                }

                $_connect= is_null($row_prs['conectado']) ? _NO_LOCAL : $row_prs['conectado'];

                if ($row_prs['id'] != $_SESSION['local_proceso_id'])
                    $_connect= ($_connect == _NO_LOCAL) ? _NO_LOCAL : _LOCAL;
                else
                    $_connect= _NO_LOCAL;

                $obj_user->SetIdProceso($row_prs['id']);

                if ($row_prs['tipo'] >= _TIPO_DIRECCION && $row_prs['conectado'] == _NO_LOCAL)
                    $result_user= $obj_user->listar(false, null, _LOCAL);
                else
                    $result_user= $obj_user->listar(false, null, _NO_LOCAL);

                if (count($obj_user->array_usuarios) == 0)
                    continue;

                $name= $_connect == _LOCAL ? "<i class='fa fa-wifi text-danger'></i>" : "";
                $name.= "<label>".textparse($row_prs['nombre'], true)."</label>";
                ++$j;
                $colom= (int)$j > 1 ? "," : "";
           ?>

                <?=$colom?>[0, "<?=$name?>",0,0, 0, '<?=color_proccess($row_prs['tipo'])?>']

           <?php
                foreach ($obj_user->array_usuarios as $row) {
                    if (!empty($only_responsable) && $only_responsable != $row['id'])
                        continue;
                    if (empty($row['nombre']))
                        continue;
                    if ($row['id'] == _USER_SYSTEM)
                        continue;
                    if (!empty($id_user_restrict) && $row['id'] == $id_user_restrict)
                        continue;

                    $value = $array_usuarios[$row['id']] ? 1 : 0;
                    ++$j;
                    $colom= (int)$j > 1 ? "," : "";
                    if ($value)
                        ++$i;
                    $name= textparse($row['nombre'], true);
                    $name.= !empty($row['cargo']) ? ", ".textparse($row['cargo'], true) : "";
            ?>

                     <?=$colom?>['user<?=$row['id']?>',"<i class='fa fa-user text-danger'></i><?=$name?>", <?=$value?>, 0, 0, '']

           <?php } } ?>

           ];

           multiselect('multiselect-users', data_users, false);

           $("#t_cant_multiselect-users").val(<?= $j ?>);
           $("#cant_multiselect-users").val(<?= $i ?>);
       });

    </script>

    <div id="multiselect-users"></div>
 </div>
