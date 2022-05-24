<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 19/02/15
 * Time: 13:26
 */

$obj_um= new Tunidad($clink);

$plhead= $is_not_for_print ? "header" : "plhead";
$left= $is_not_for_print ? null : "left";
$plinner= $is_not_for_print ? null : "plinner";
$cellspacing= $is_not_for_print ? 1 : "0";
$border= $is_not_for_print ? 1 : "0";
?>

<table width="100%" id="table-res" border="<?=$border?>" cellspacing="<?=$cellspacing?>" >
    <thead>
        <tr>
            <th width=24 rowspan="3" valign=middle class="<?=$plhead?> <?=$left?>">No</th>
            <th width=204 rowspan="3" valign=middle class="<?=$plhead?>">Indicador</th>
            <th width=30 rowspan="3" valign=middle class="<?=$plhead?>">U</th>
            <th colspan="6" class="<?=$plhead?>">Enero</th>
            <th colspan="6" class="<?=$plhead?>">Febrero</th>
            <th colspan="6" class="<?=$plhead?>">Marzo</th>
            <th colspan="6" class="<?=$plhead?>">Abril</th>
            <th colspan="6" class="<?=$plhead?>">Mayo</th>
            <th colspan="6" class="<?=$plhead?>">Junio</th>
            <th colspan="6" class="<?=$plhead?>">Julio</th>
            <th colspan="6" class="<?=$plhead?>">Agosto</th>
            <th colspan="6" class="<?=$plhead?>">Septiembre</th>
            <th colspan="6" class="<?=$plhead?>">Octubre</th>
            <th colspan="6" class="<?=$plhead?>">Novimbre</th>
            <th colspan="6" class="<?=$plhead?>">Diciembre</th>
        </tr>        

        <tr>
            <?php for ($i= 0; $i<12; ++$i) { ?>
                <th colspan="3" class="<?=$plhead?>">Mes</th>
                <th colspan="3" class="<?=$plhead?>">Acumulado</th>
            <?php } ?>
        </tr>

        <tr>
            <?php for ($i= 0; $i<12; ++$i) { ?>
                <th class="<?=$plhead?> <?=$left?>">Plan</th>
                <th class="<?=$plhead?> <?=$left?>">Real</th>
                <th class="<?=$plhead?> <?=$left?>">%</th>
                <th class="<?=$plhead?> <?=$left?>">Plan</th>
                <th class="<?=$plhead?> <?=$left?>">Real</th>
                <th class="<?=$plhead?> <?=$left?>">%</th>
            <?php } ?>
        </tr>
    </thead>
    
    <tbody>
        <?php
        while ($row_indi= $clink->fetch_array($result_indi)) {
            $id_indicador= $row_indi['_id'];

            $obj_ind->SetIdIndicador($id_indicador);
            $obj_ind->SetYear($year);
            $obj_ind->Set($id_indicador);
            
            $obj_um->SetIdUnidad($obj_ind->GetIdUnidad());
            $obj_um->Set();
            $unidad= $obj_um->GetNombre();
            ?>

            <tr>
                <td class="plinner <?=$left?>" valign="top"><?= !empty($row_indi['_numero']) ? $row_indi['_numero'] : $nshow ?></td>
                <td class="plinner"><span style="text-align:<?=$left?>; vertical-align:text-top;"><?= $row_indi['_nombre'] ?></span></td>
                <td class="plinner"><?=$unidad?></td>

                <?php
                if (isset($obj_cell)) unset($obj_cell);
                $obj_cell= new Tcell_list($clink);

                $obj_cell->SetIdIndicador($id_indicador);
                $obj_cell->SetYear($year);

                $row= $obj_cell->GetCells_monthly();

                for ($k= 1; $k <= 12; ++$k) {
                    $real= (is_null($row[$k]['real'])) ? null : $row[$k]['real'];
                    $plan= (is_null($row[$k]['plan'])) ? null : $row[$k]['plan'];
                    
                    $corte= null;
                    if (!empty($row[$k]['corte'])) {
                        $month= date('m', strtotime(time2odbc($row[$k]['corte'])));
                    }
                    
                    $percent= is_null($row[$k]['percent']) ? "&nbsp;" : number_format($row[$k]['percent'], 1,'.','');

                    $acumulado_real= (!is_null($row[$k]['acumulado_real']) && !is_null($real)) ? $row[$k]['acumulado_real'] : null;
                    $acumulado_plan= (!is_null($row[$k]['acumulado_plan']) && !is_null($plan)) ? $row[$k]['acumulado_plan'] : null;

                    $percent_cumulative= (!is_null($real) && !is_null($plan)) ? $row[$k]['percent_cumulative'] : null;
                    $percent_cumulative= is_null($percent_cumulative) ? "&nbsp;" : number_format($percent_cumulative, 1,'.','');
                    ?>

                    <td class="plinner plan"><?= (int)$month == $k ? $plan : '&nbsp;'?></td>
                    <td class="plinner real"><?= $real ?></td>
                    <td class="plinner real"><?= $percent ?></td>

                    <td class="plinner plan"><?= (int)$month == $k ? $acumulado_plan : '&nbsp;' ?></td>
                    <td class="plinner real"><?= $acumulado_real ?></td>
                    <td class="plinner sum"><?= $percent_cumulative ?></td>

                <?php } ?>
            </tr>
        <?php } ?>        
    </tbody>

</table>