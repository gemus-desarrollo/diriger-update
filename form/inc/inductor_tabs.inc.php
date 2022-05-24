<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 24/02/15
 * Time: 19:17
 */

if (empty($id_list_prs)) 
    $obj_prs_tmp= new Tproceso($clink);

$obj_inductor= new Tinductor($clink);
$obj_inductor->SetYear($year);
$obj_inductor->SetInicio($inicio);
$obj_inductor->SetFin($fin);
$obj_inductor->SetIdProceso($id_list_prs);
$result= $obj_inductor->listar($with_null_perspectiva);

$_cant_objt= $result ? $obj_inductor->GetCantidad() : 0;
$cant_objt+= (int)$_cant_objt;

$use_weight_select= !is_null($use_weight_select) ? $use_weight_select : true; 
?>

<?php if ($_cant_objt > 0) { ?>
    <?php
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $_prs= $array_procesos_entity[$row['id_proceso']];

        if ($restrict_list_to_entity) {
            if (!empty($_prs['id_entity']) && $_prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_down'] && $_prs['tipo'] < $_SESSION['entity_tipo']))
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_up'] && $_prs['tipo'] > $_SESSION['entity_tipo']))
                continue;             
        } else {
            if (!empty($_SESSION['superior_entity_id']) && $_prs['id'] == $_SESSION['superior_entity_id'])
                continue;
        }

        if ((empty($_prs['id_entity']) && $_prs['id'] != $_SESSION['id_entity']) 
                    && ($_prs['id'] != $_SESSION['superior_entity_id'] && $_prs['id_proceso'] != $_SESSION['id_entity'])
                    ) 
            continue;  
        ++$i;
    }

    if($i > 0) {
    ?>    
    <tr>
        <td colspan="3" class="alert alert-warning">
            <?php if ($_connect) { ?><img class="img-rounded row_prs_connect" src="../img/transmit.ico" /><?php } ?>
            <?= $proceso ?>        
        </td>        
    </tr>
    <?php } ?>

    <?php
    $clink->data_seek($result);
    while ($row= $clink->fetch_array($result)) {
        $_prs= $array_procesos_entity[$row['id_proceso']];

        if ($restrict_list_to_entity) {
            if (!empty($_prs['id_entity']) && $_prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_down'] && $_prs['tipo'] < $_SESSION['entity_tipo']))
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_up'] && $_prs['tipo'] > $_SESSION['entity_tipo']))
                continue;             
        } else {
            if (!empty($_SESSION['superior_entity_id']) && $_prs['id'] == $_SESSION['superior_entity_id'])
                continue;
        }

        if ((empty($_prs['id_entity']) && $_prs['id'] != $_SESSION['id_entity']) 
                    && ($_prs['id'] != $_SESSION['superior_entity_id'] && $_prs['id_proceso'] != $_SESSION['id_entity'])) 
            continue;  

        $_inicio= $row['_inicio'];
        $_fin= $row['_fin'];

        if (empty($id_list_prs)) {
            $obj_prs_tmp->Set($row['_id_proceso']);
            $proceso= $obj_prs_tmp->GetNombre().', '.$Ttipo_proceso_array[ $obj_prs_tmp->GetTipo()];
        }

        if ($use_weight_select) {
            $value= !is_array($array_pesos[$row['_id']]) ? $array_pesos[$row['_id']] : $array_pesos[$row['_id']]['peso'];
            $value= setZero($value);
        } else 
            $value= is_array($array_pesos[$row['_id']]) ? 1 : 0;
        
        if (!empty($value)) 
            ++$i_objt;
    ?>
        <tr>
            <td><?=++$j_objt?></td> 

            <td>
                <?php if ($use_weight_select) { ?>
                    <div style="max-width:170px;">
                        <select  id="select_objt<?= $row['_id'] ?>" name="select_objt<?= $row['_id'] ?>"  class="form-control input-sm" onchange="set_cant_objt(<?= $row['_id'] ?>)">
                            <?php for ($k = 0; $k < 8; ++$k) { ?>
                                <option value="<?= $k ?>" <?php if ($k == $value) echo "selected='selected'" ?> ><?= $Tpeso_inv_array[$k] ?></option>
                            <?php } ?>
                        </select>
                    </div>    
                <?php } else { ?>
                    <div style="max-width:70px;">
                        <select  id="select_objt<?= $row['_id'] ?>" name="select_objt<?= $row['_id'] ?>"  class="form-control input-sm" onchange="set_cant_objt(<?= $row['_id'] ?>)">
                            <option value="0" <?php if (empty($value)) echo "selected='selected'" ?> >NO</option>
                            <option value="1" <?php if (!empty($value)) echo "selected='selected'" ?> >SI</option>
                        </select>
                    </div>                   
                <?php } ?>
            </td>

            <td>
                <?="No.{$row['numero']}"?> <span><?="{$row['_nombre']} <br />periodo: {$_inicio} - $_fin"?></span>
                <input type="hidden" id="init_objt<?=$row['_id']?>" name="init_objt<?=$row['_id']?>" value="<?=$value?>" />
                <input type="hidden" id="id_objt_code<?=$row['_id']?>" name="id_objt_code<?=$row['_id']?>" value="<?=$row['_id_code']?>" />
            </td> 
        </tr>    
    <?php } ?>     
<?php } ?>
      
