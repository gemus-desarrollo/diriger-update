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
require_once "../../php/class/proceso_item.class.php";

$year= !empty($_POST['year']) ? (int)$_POST['year'] : $date('Y'); 

$id_user_restrict= !empty($_POST['id_usuario']) ? (int)$_POST['id_usuario'] : null;
$user_ref_date= !empty($_POST['user_ref_date']) ? (int)$_POST['user_ref_date'] : date('Y-m-d H:i:s');
$restrict_prs= !empty($_POST['restrict_prs']) ? unserialize($_POST['restrict_prs']) : array(_TIPO_PROCESO_INTERNO);

$array_usuarios= !empty($_POST['array_usuarios']) ? unserialize(urldecode($_POST['array_usuarios'])) : null;
$array_grupos= !empty($_POST['array_grupos']) ? unserialize(urldecode($_POST['array_grupos'])) : null;

$config->freeassign= true;

$obj_prs= new Tproceso($clink);
$obj_prs->SetIdProceso($_SESSION['id_entity']);
$obj_prs->SetTipo($_SESSION['entity_tipo']);
$result_prs_array= $obj_prs->listar_in_order('eq_asc_desc', true);
?>

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

            $config->freeassign ? $obj_user->set_use_copy_tusuarios(false) : $obj_user->set_use_copy_tusuarios(true);
            $obj_user->set_user_date_ref($user_ref_date);
            $config->freeassign ? $obj_user->SetIdProceso($_SESSION['id_entity']) : $obj_user->SetIdProceso(null) ;
            $result_user= $obj_user->listar(false, null, _NO_LOCAL);

            if (count($obj_user->array_usuarios) > 0
                    && ($_SESSION['local_proceso_id'] == $_SESSION['id_entity'])) {
                ++$j;
                $colom= (int)$j > 1 ? "," : "";
            ?>
            <?=$colom?>[0, "<label><?=$_SESSION['entity_nombre']?></label>", 0, 0, 0, '<?=color_proccess($_SESSION['entity_tipo'])?>']

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

                    <?=$colom?>['user<?=$row['id']?>', "<i class='fa fa-user text-danger'></i><?= $name?>", <?=$value?>, 0, 0, '']

            <?php } } ?>

           <?php
            reset($result_prs_array);
            $result_prs_array= $obj_prs->sort_array_procesos($result_prs_array);
            
            $k= 0;         
            foreach ($result_prs_array as $row_prs) { 
                if ($row_prs['id'] == $_SESSION['id_entity'] && $row_prs['id'] == $_SESSION['local_proceso_id'])
                    continue;                
                
                $only_responsable= null;
                if (empty($row_prs['id_entity']) 
                    && ($row_prs['id'] != $_SESSION['id_entity'] && $row_prs['id'] != $_SESSION['superior_entity_id'])) {
                    if (empty($row_prs['id_proceso']) || (!empty($row_prs['id_proceso']) && $row_prs['id_proceso'] != $_SESSION['id_entity']))
                        continue;
                    $only_responsable= $row_prs['id_responsable'];
                }
                if ($row_prs['id'] == $_SESSION['superior_entity_id'])
                    $only_responsable= $row_prs['id_responsable'];

                if (!empty($row_prs['id_entity']) && $row_prs['id_entity'] != $_SESSION['id_entity'])
                    continue;
                if (!empty($restrict_prs) && array_search($row_prs['tipo'], $restrict_prs) !== false)
                    continue;

                $_connect= is_null($row_prs['conectado']) ? _NO_LOCAL : $row_prs['conectado'];
                if ($row_prs['id'] != $_SESSION['local_proceso_id'])
                    $_connect= ($_connect == _NO_LOCAL) ? _NO_LOCAL : _LOCAL;
                else
                    $_connect= _NO_LOCAL;

                $obj_user->SetIdProceso($row_prs['id']);

                if (($row_prs['tipo'] <= _TIPO_DEPARTAMENTO && $_SESSION['entity_tipo'] < _TIPO_UEB) 
                        || ($row_prs['tipo'] >= _TIPO_DEPARTAMENTO && $_SESSION['entity_tipo'] >= _TIPO_UEB))
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

                <?=$colom?>[0, "<?=$name?>", 0, 0, 0, '<?=color_proccess($row_prs['tipo'])?>']

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

                     <?=$colom?>['user<?=$row['id']?>', "<i class='fa fa-user text-danger'></i><?=$name?>", <?=$value?>, 0, 0, '']

           <?php } } ?>

           ];

           multiselect('multiselect-users', data_users, false);

           $("#t_cant_multiselect-users").val(<?= $j ?>);
           $("#cant_multiselect-users").val(<?= $i ?>);
       });

    </script>

    <div id="multiselect-users"></div>
 </div>
