<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 24/02/15
 * Time: 22:29
 */
?>

<?php
$obj_objetivo= new Tobjetivo($clink);
$obj_objetivo->SetIdProceso($id_list_prs);
$obj_objetivo->SetInicio($inicio);
$obj_objetivo->SetFin($fin);

$result= $obj_objetivo->listar();
$_cant_obji= $obj_objetivo->GetCantidad();
$cant_obji+= (int)$_cant_obji;
?>

<?php if ($_cant_obji > 0) { ?>
    <?php
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $_prs= $array_procesos_entity[$row['id_proceso']];

        if($_prs['tipo'] <= (int)$_SESSION['entity_tipo']-1)
            continue;

        if (!$restrict_list_to_entity) {
            if (!empty($_prs['id_entity']) && $_prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_down'] && $_prs['tipo'] < $_SESSION['entity_tipo']))
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_up'] && $_prs['tipo'] > $_SESSION['entity_tipo']))
                continue;             
        } else {
            if($_prs['id'] == $_SESSION['superior_entity_id'])
                continue;
        }

        if ((empty($_prs['id_entity']) && $_prs['id'] != $_SESSION['id_entity']) 
                && ($_prs['id'] != $_SESSION['superior_entity_id'] && $_prs['id_proceso'] != $_SESSION['id_entity'])) 
            continue;  
        ++$i;
    }

    if($i > 0) {
    ?>  
    <tr>
        <td colspan="3" class="row_prs">
            <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?>
                <img src="../img/transmit.ico" class="row_prs_connect" />
            <?php } ?>
            <?=$proceso?>
        </td>
    </tr>
    <?php } ?>

    <?php
    $clink->data_seek($result);
    while ($row= @$clink->fetch_array($result)) {
        $_prs= $array_procesos_entity[$row['id_proceso']];
        
        if($_prs['tipo'] <= (int)$_SESSION['entity_tipo']-1 )
            continue;

        if (!$restrict_list_to_entity) {
            if (!empty($_prs['id_entity']) && $_prs['id_entity'] != $_SESSION['id_entity'])
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_down'] && $_prs['tipo'] < $_SESSION['entity_tipo']))
                continue;
            if (empty($_prs['id_entity']) && (!$row['if_send_up'] && $_prs['tipo'] > $_SESSION['entity_tipo']))
                continue;             
        } else {
            if($_prs['id'] == $_SESSION['superior_entity_id'])
                continue;
        }

        if ((empty($_prs['id_entity']) && $_prs['id'] != $_SESSION['id_entity']) 
                    && ($_prs['id'] != $_SESSION['superior_entity_id'] && $_prs['id_proceso'] != $_SESSION['id_entity'])) 
            continue;   
        ++$i;
        $value= $array_pesos[$row['_id']];
        $value= setZero($value);
        if (!empty($value)) 
            ++$i_obji;
?>

        <tr id="id_obji<?=$row['_id']?>">
            <td width="30"><?= ++$j_obji?></td>

            <td>
                <select id="select_obji<?= $row['_id'] ?>" name="select_obji<?= $row['_id'] ?>" class="form-control input-sm"
                    onchange="set_cant_obji(<?= $row['_id'] ?>)">
                    <?php for ($k = 0; $k < 8; ++$k) { ?>
                    <option value="<?= $k ?>" <?php if ($k == $array_pesos[$row['_id']]) echo "selected='selected'" ?>>
                        <?= $Tpeso_inv_array[$k] ?>
                    </option>
                    <?php } ?>
                </select>
            </td>
            <td>
                <strong style="font-weight:bold; padding:2px 4px;">No.<?=$row['numero']?></strong>
                <?="{$row['_nombre']}<br /><strong style='margin-left:35px'>periodo: </strong>{$row['inicio']}-{$row['fin']}" ?>

                <input type="hidden" id="init_obji<?=$row['_id']?>" name="init_obji<?=$row['_id']?>" value="<?=$value ?>" />
                <input type="hidden" name="id_obji_code<?=$row['_id']?>" id="id_obji_code<?=$row['_id']?>" value="<?=$row['_id_code']?>" />
            </td>
        </tr>
    <?php } ?>
<?php } ?>