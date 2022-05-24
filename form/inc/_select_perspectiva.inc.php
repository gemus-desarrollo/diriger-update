<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 9/03/15
 * Time: 12:17
 */

$persp_inicio= !empty($persp_inicio) ? $persp_inicio : null;
$persp_fin= !empty($persp_fin) ? $persp_fin : null;
// id del proceso a partir del cual se genera la cascada de procesos superiores
$persp_id_proceso= !empty($persp_id_proceso) ? $persp_id_proceso : null;
$persp_corte_prs= !is_null($persp_corte_prs) ? $persp_corte_prs : null;

unset($obj_prs);
$obj_prs= new Tproceso($clink);

$persp_id_proceso= !empty($persp_id_proceso) ? $persp_id_proceso : $_SESSION['id_entity'];
$obj_prs->get_procesos_up_cascade($persp_id_proceso, null, null, true);

foreach ($obj_prs->array_cascade_up as $row_prs) {
    if ((!empty($row_prs['id_entity']) && $row_prs['id_entity'] != $_SESSION['id_entity'])
            || (empty($row_prs['id_entity']) && $row_prs['id'] != $_SESSION['id_entity']))
        continue;
    
    if (isset($obj_persp)) unset($obj_persp);
    $obj_persp= new Tperspectiva($clink);
    $obj_persp->SetIdProceso($row_prs['id']);

    $obj_persp->SetYear($year_persp);
    $obj_persp->SetInicio($persp_inicio);
    $obj_persp->SetFin($persp_fin);

    $result_persp= $obj_persp->listar();

    while ($row_persp= $clink->fetch_array($result_persp)) {
        ?>
        <input type="hidden" id="perspectiva_code_<?=$row_persp['_id']?>" name="perspectiva_code_<?=$row_persp['_id']?>" value="<?=$row_persp['_id_code']?>" />
<?php } } ?>


<select name="perspectiva" id="perspectiva" class="form-control input-sm" <?php if (!is_null($refresh_function)) echo "onchange=\"javascript:$refresh_function\""?> >
    <option value="0" <?=empty($id_perspectiva) ? "selected='selected'" : ""?>>Seleccione ... </option>
    
    <?php
    reset($obj_prs->array_cascade_up);
    foreach ($obj_prs->array_cascade_up as $row_prs) {
        if ((!empty($row_prs['id_entity']) && $row_prs['id_entity'] != $_SESSION['id_entity'])
                || (empty($row_prs['id_entity']) && $row_prs['id'] != $_SESSION['id_entity']))
            continue;
    
        $nombre_prs= $row_prs['nombre'].',  '.$Ttipo_proceso_array[$row_prs['tipo']];

        if (isset($obj_persp)) unset($obj_persp);
        $obj_persp= new Tperspectiva($clink);
        $obj_persp->SetIdProceso($row_prs['id']);

        $obj_persp->SetYear($year_persp);
        $obj_persp->SetInicio($persp_inicio);
        $obj_persp->SetFin($persp_fin);

        $result_persp= $obj_persp->listar();

        while ($row_persp= $clink->fetch_array($result_persp)) {
    ?>
        <option value="<?=$row_persp['_id']?>" <?php if ($row_persp['_id'] == $id_perspectiva) echo "selected='selected'"; ?>>
            No.<?="{$row_persp['numero']}  {$row_persp['_nombre']} ({$row_persp['inicio']}-{$row_persp['fin']}  / {$nombre_prs}"?>
        </option>
    <?php } } ?>
</select>
