<?php

function lista_requisito($componente, $id_tipo_lista= 0) {
    global $clink;
    global $Tpeso_inv_array;
    global $Ttipo_proceso_array;
    global $year;
    global $id_lista;
    global $id_proceso;
    global $if_jefe;
    global $nshow;
    global $nhide;
    global $obj;
    global $action;
    global $numero;

    $obj->SetYear($year == -1 ? null : $year);
    $obj->SetIdProceso(is_null($id_proceso) || $id_proceso == -1 ? null : $id_proceso);
    $obj->SetComponente($componente);
    $obj->SetIdTipo_lista($id_tipo_lista);

    $result= $obj->listar(null, false);
    $cant= $obj->GetCantidad();
    if (empty($cant))
        return 0;

    $obj_prs= new Tproceso_item($clink);
    $obj_prs->SetIdLista($id_lista);
    $obj_prs->SetYear($year);

    $i= 0;
    $array_ids= array();
    while ($row= $clink->fetch_array($result)) {
        if (isset($array_ids[$row['_id']]))
            continue;
        $array_ids[$row['_id']]= 1;
        ++$i;
        ++$nshow;
        --$nhide;
    ?>
        <tr>
            <td>
                <?=$numero.".".$row['numero_plus'].")"?>
            </td>

            <?php if ($if_jefe) { ?>
            <td>
                <a class="btn btn-warning btn-sm" href="javascript:edit(<?= $row['_id'] ?>);">
                    <i class="fa fa-edit"></i>Editar
                </a>

                <?php if ($action != 'list') { ?>
                <a class="btn btn-danger btn-sm" href="javascript:_delete(<?= $row['_id'] ?>)">
                    <i class="fa fa-trash"></i>Eliminar
                </a>
                <?php } ?>
                <a class="btn btn-info btn-sm" href="javascript:showWindow(<?= $row['_id'] ?>);">
                    <i class="fa fa-file-text"></i>Documentos
                </a>
            </td>
            <?php } ?>

            <td>
                <?= $Tpeso_inv_array[$row['peso']] ?>
            </td>
            <td>
                <?= "{$row['inicio']} - {$row['fin']}" ?>
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
            <td>
                <?php
                $obj_prs->SetIdRequisito($row['_id']);
                $array_procesos= $obj_prs->getProcesoRequisito();

                $j= 0;
                $prs_string= null;
                foreach ($array_procesos as $prs) {
                    echo $j > 0 ? "<br/>" : "";
                    echo "{$prs['nombre']}, {$Ttipo_proceso_array[$prs['tipo']]}";
                    ++$j;
                }
                ?>                
            </td>
            <td>

            </td>
        </tr>
    <?php } ?>
<?php 
    return $i;
} 
?>    