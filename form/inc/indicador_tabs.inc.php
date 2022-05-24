<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 10/03/15
 * Time: 7:06
 */

if (isset($obj_indicador)) unset($obj_indicador);
$obj_indicador= new Tindicador($clink);

$obj_indicador->SetYear($year);
$obj_indicador->SetInicio($inicio);
$obj_indicador->SetFin($fin);
$obj_indicador->SetIdProceso($id_list_prs);

$result= $obj_indicador->listar();

$_cant_indi= $result ? $obj_indicador->GetCantidad() : 0;
$cant_indi+= $_cant_indi;
?>

<?php if ($_cant_indi > 0) { ?>
    <tbody>
        <tr>
            <td colspan="3" class="row_prs">
                <?php if ($_connect && $id_proceso != $_SESSION['local_proceso_id']) { ?><img src="../img/transmit.ico" class="row_prs_connect" /><?php } ?>
                <?=$proceso?>
            </td>
        </tr>
        <?php

        while ($row= @$clink->fetch_array($result)) {
            $class= ($i_indi%2 == 0) ? 'roweven' : '';
            ++$i_indi;

            $_inicio= $row['_inicio'];
            $_fin= $row['_fin'];
            $value= $array_pesos[$row['_id']];
            if (empty($value)) $value= 0;

            if ($value > 0) ++$j_indi;
            ?>

            <tr id="id_<?=$i_indi?>">
                <td><?=$i_indi?></td>
                <td>
                    <div style="width: 170px;">
                        <select name="select_indi<?= $row['_id'] ?>" id="select_indi<?= $row['_id'] ?>" class="form-control input-sm" onchange="set_cant_indi(<?= $row['_id'] ?>)">
                            <?php for ($k = 0; $k < 8; ++$k) { ?>
                                <option value="<?= $k ?>" <?php if ($k == $value) echo "selected='selected'" ?> ><?= $Tpeso_inv_array[$k] ?></option>
                            <?php } ?>
                        </select>                    
                    </div>

                    <input type="hidden" name="init_indi<?=$row['_id']?>" id="init_indi<?= $row['_id']?>" value="<?=$value?>" />
                    <input type="hidden" name="id_indicador_code<?=$row['_id']?>" id="id_indicador_code<?=$row['_id']?>" value="<?=$row['_id_code']?>" />
                    <input type="hidden" id="indi_prs<?=$row['_id']?>" name="indi_prs<?=$row['_id']?>" value="<?=$prs['id']?>" />
                </td>
                <td>
                    <?="{$row['_nombre']}  <br /><em>({$_inicio} - {$_fin})  $proceso </em>"?>
                </td>
            </tr>
        <?php  } ?>

    </tbody>
<?php } ?>

