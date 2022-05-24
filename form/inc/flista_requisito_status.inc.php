<?php

function lista_requisito($componente, $id_tipo_lista= 0) {
    global $clink;
    global $obj_reg;
    global $Tcriterio_array;
    
    global $id_proceso;
    global $year;
    global $id_lista;
    global $id_auditoria;
    global $nhide;
    global $obj;
    global $numero;

    $obj->SetIdLista($id_lista);
    $obj->SetYear($year == -1 ? null : $year);
    $obj->SetIdProceso($id_proceso);
    $obj->SetComponente($componente);
    $obj->SetIdTipo_lista($id_tipo_lista);

    $result= $obj->listar(null, false);
    $cant= $obj->GetCantidad();
    if (empty($cant))
        return 0;

    $i= 0;
    $array_ids= array();
    while ($row= $clink->fetch_array($result)) {
        if (isset($array_ids[$row['_id']]))
            continue;
        $array_ids[$row['_id']]= 1;
        ++$i;
        --$nhide;

        $obj_reg->SetIdAuditoria($id_auditoria);
        $obj_reg->SetIdRequisito($row['_id']);
        $obj_reg->SetChkApply(false);
        $array= $obj_reg->getNota_reg();

        if (!empty($array['reg_fecha'])) {
            $_year= date('Y', strtotime($array['reg_fecha']));
            $_month= date('m', strtotime($array['reg_fecha']));
        } else {
            $_year= $year;
            $_month= $year == date('Y') ? date('m') : '01';
        }
        $cumplimiento= !is_null($array['cumplimiento']) ? $array['cumplimiento'] : 0;
        ?>

        <tr>
            <td>
            <input type="hidden" id="chk_apply_<?=$row['_id']?>" 
                name="chk_apply_<?=$row['_id']?>" value="<?=$array['chk_apply'] ? 1 : 0?>" />            
            <input type="hidden" id="id_requisito_code_<?=$row['_id']?>" 
                name="id_requisito_code_<?=$row['_id']?>" value="<?=$row['_id_code']?>" />

            <input type="hidden" id="id_requisito_init_<?=$row['_id']?>" 
                name="id_requisito_init_<?=$row['_id']?>" value="<?=$cumplimiento?>" />  

                <?=$numero.".".$row['numero_plus'].")"?>
            </td>
            <td>
                <select class="form-control select-width" id="cumplimiento_<?=$row['id']?>"
                    name="cumplimiento_<?=$row['id']?>">
                    <option value="">...  </option>
                    <?php
                    for ($i= 1; $i < 5; $i++) {
                        $array= $Tcriterio_array[$i];
                        if (((int)$_year > 2021 || ((int)$_year == 2021 && (int)$_month >= 5)) 
                            && ($array[1] == _NO_PROCEDE || $array[1] == _EN_PROCESO))
                            continue;

                    ?>
                    <option value="<?=$array[1]?>" <?php if ((int)$array[1] == (int)$cumplimiento) { ?>selected<?php } ?> title="<?=$array[2]?>">
                        <?=$array[0]?>
                    </option>
                    <?php } ?>
                </select>
            </td>

            <td>
                <?= textparse($row['nombre'])?>
            </td>
            <td>
                <?= textparse($row['evidencia'])?>
            </td>
            <td>
                <?= textparse($row['indicacion'])?>
            </td>
        </tr>
    <?php } ?>
<?php 
    return $i;
} 
?>    