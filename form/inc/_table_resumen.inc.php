<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 19/02/15
 * Time: 13:26
 */

?>

<table width="100%" id=table-res border=0 cellspacing=0 >
<tr>
    <td width=24 rowspan="3" valign=middle class=td-head>No</td>
    <td width=204 rowspan="3" valign=middle class=td-head>Indicador</td>
    <td width=30 rowspan="3" valign=middle class=td-head>U</td>
    <td colspan="6" class=td-head>Enero</td>
    <td colspan="6" class=td-head>Febrero</td>
    <td colspan="6" class=td-head>Marzo</td>
    <td colspan="6" class=td-head>Abril</td>
    <td colspan="6" class=td-head>Mayo</td>
    <td colspan="6" class=td-head>Junio</td>
    <td colspan="6" class=td-head>Julio</td>
    <td colspan="6" class=td-head>Agosto</td>
    <td colspan="6" class=td-head>Septiembre</td>
    <td colspan="6" class=td-head>Octubre</td>
    <td colspan="6" class=td-head>Novimbre</td>
    <td colspan="6" class=td-head>Diciembre</td>
</tr>

<tr>
    <?php for($i= 0; $i<12; ++$i) { ?>
        <td colspan="3" class=td-head>Mes</td>
        <td colspan="3" class=td-head>Acumulado</td>
    <?php } ?>
</tr>

<tr>
    <?php for($i= 0; $i<12; ++$i) { ?>
        <td class=td-head>Plan</td>
        <td class=td-head>Real</td>
        <td class=td-head>%</td>
        <td class=td-head>Plan</td>
        <td class=td-head>Real</td>
        <td class=td-head>%</td>
    <?php } ?>
</tr>

<?php
while ($row_indicador= $clink->fetch_array($result_indi)) {
    $id_indicador= $row_indicador['_id'];

    $obj_ind->SetIdIndicador($id_indicador);
    $obj_ind->SetYear($year);
    $obj_ind->Set($id_indicador);
    ?>


    <tr>
        <td class=td-head valign="top" style="vertical-align:text-top"><?php echo ++$j ?></td>
        <td class=td-head style="text-align:left; vertical-align:top;"><span style="text-align:left; vertical-align:text-top;"><?php echo $row_indicador['_nombre'] ?></span></td>
        <td class=td-head style="text-align:left; vertical-align:top"><?php echo $obj_ind->GetUnidad() ?></td>

        <?php
        if(isset($obj_cell)) unset($obj_cell);
        $obj_cell= new Tcell_list($clink);

        $obj_cell->SetIdIndicador($id_indicador);
        $obj_cell->SetYear($year);

        $row= $obj_cell->GetCells_monthly();

        for($k= 1; $k <= 12; ++$k) {
            $real= (is_null($row[$k]['real'])) ? null : $row[$k]['real'];
            $plan= (is_null($row[$k]['plan'])) ? null : $row[$k]['plan'];
            $percent= $row[$k]['percent'];

            $percent= is_null($percent) ? "&nbsp;" : number_format($percent, 1,'.','');

            $acumulado_real= (!is_null($row[$k]['acumulado_real']) && !is_null($real)) ? $row[$k]['acumulado_real'] : null;
            $acumulado_plan= (!is_null($row[$k]['acumulado_plan']) && !is_null($plan)) ? $row[$k]['acumulado_plan'] : null;

            $percent_cumulative= (!is_null($real) && !is_null($plan)) ? $row[$k]['percent_cumulative'] : null;
            $percent_cumulative= is_null($percent_cumulative) ? "&nbsp;" : number_format($percent_cumulative, 1,'.','');

            ?>

            <td class=plan><?php echo $plan ?></td>
            <td class=real><?php echo $real ?></td>
            <td class=real><?php echo $percent ?></td>

            <td class=plan><?php echo $acumulado_plan ?></td>
            <td class=real><?php echo $acumulado_real ?></td>
            <td class=sum><?php echo $percent_cumulative ?></td>

        <?php } ?>

    </tr>
<?php } ?>

</table>