<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 9/03/15
 * Time: 9:59
 */

$top_position_div= !empty($top_position_div) ? $top_position_div : 50;
$right_position_div= !empty($right_position_div) ? $right_position_div : 570;

$use_select_proceso= !is_null($use_select_proceso) ? $use_select_proceso : true;
$use_select_year= !is_null($use_select_year) ? $use_select_year : false;
$use_select_month= !is_null($use_select_month) ? $use_select_month : false;
$use_select_day= !is_null($use_select_day) ? $use_select_day : false;
?>

<style>
.use_select {
    margin: 1px 2px 1px; 4px;
}
</style>


<div id="toppanel" style="top:<?=$top_position_div?>px; right:<?=$right_position_div?>px;" >
    <div id=sp-panel>

        <input type="hidden" id="escenario" name="escenario" value="<?=$id_escenario?>" />
        <input type="hidden" id="id_escenario" name="id_escenario" value="<?=$id_escenario?>" />
        <input type="hidden" id="id_escenario_code" name="id_escenario_code" value="<?=$id_escenario_code?>" />

        <?php if(!$use_select_year) { ?><input type="hidden" id="year" name="year" value="<?=$year?>" /><?php } ?>
        <?php if(!$use_select_month) { ?><input type="hidden" id="month" name="month" value="0" /><?php } ?>
        <?php if(!$use_select_day) { ?><input type="hidden" id="day" name="day" value="0" /><?php } ?>

        <table border="0" cellpadding=2>
            <?php if($use_select_proceso) { ?>
                <tr>
                    <td><span style="padding-bottom:3px;">Unidad Organizativa:</span>
                        <?php
                        $top_list_option= "seleccione........";
                        $id_list_prs= null;
                        $order_list_prs= 'eq_asc_desc';
                        $reject_connected= !is_null($reject_connected) ? $reject_connected : false;
                        $id_select_prs= !is_null($id_select_prs) ? $id_select_prs : $id_proceso;
                        $in_building= !is_null($in_building) ? $in_building : false;

                        include_once('_select_prs.inc.php');
                        ?>
                    </td>
                </tr>
            <?php } else { ?>
                <input type="hidden" id="proceso" name="proceso" value="<?=$id_proceso?>"  />
            <?php } ?>

            <?php if($signal == 'proyecto') { ?>
            <tr>
                <td><span style="padding-bottom:3px;">Programas:</span>
                    <select name="programa" id="programa" class="texta" style="width:300px;" onchange="refreshp()" >
                        <?php
                        $obj_prog= new Tprograma($clink);
                        $result_prog= $obj_prog->listar();

                        while($row= $clink->fetch_array($result_prog)) {
                        ?>
                            <option value="<?=$row['id']?>" <?php if($row['id'] == $id_programa) echo "selected='selected'"; ?>><?=$row['nombre']?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <?php } ?>

            <?php

            if($signal == 'strat' || $signal == 'proc' || $signal == 'org') {
                $obj_esc->SetIdProceso($id_proceso);
                $result_esc= $obj_esc->listar();
            ?>
            <tr>
                <td><span style="padding-bottom:3px;">Escenario:&nbsp; </span>

                    <select id="escenario" name="escenario" class="texta" style="width:100px;" onchange="refreshp(0)">
                        <option value=0>selecione...</option>
                        <?php while($row= $clink->fetch_array($result_esc)) { ?>
                            <option value="<?=$row['id']?>" <?php if($row['id'] == $id_escenario) echo "selected='selected'"; ?> ><?=$row['inicio'].' - '.$row['fin']?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php } ?>

            <?php if($use_select_year || $use_select_month || $use_select_day) { ?>
                <tr>
                    <td>
                        <?php if($use_select_year) { ?>
                            <span class="use_select">A&ntilde;o:</span>

                            <select name=year id=year class="texta" style="width:50px" onchange="refreshp(1)">
                                <?php for($i= $inicio; $i <= $fin; ++$i) { ?>
                                    <option value="<?=$i?>" <?php if($i == $year) echo "selected='selected'"; ?>><?=$i?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>

                        <?php if($use_select_month) { ?>
                            <span class="use_select">Mes:</span>

                            <select name=month id=month class=texta style="width:85px" onchange="refreshp(2)">
                                <option value="-1">Select...</option>
                                <?php for($i=1; $i <= $end_month; ++$i) { ?>
                                    <option value="<?=$i?>" <?php if($i == $month) echo "selected='selected'"; ?>><?=$meses_array[$i]?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>

                        <?php if($use_select_day) { ?>
                            <span class="use_select">Día:</span>

                            <select name=day id=day class=texta style="width:100px" onchange="refreshp(3)">
                                <option value="-1">Select...</option>
                                <?php
                                for($i= 1; $i <= $end_day; ++$i) {
                                    $time->SetDay($i);
                                    $iday= $time->weekDay();
                                    ?>
                                    <option value="<?=$i?>" <?php if($i == $day) echo "selected='selected'"; ?>><?=$dayNames[$iday].", ".$i?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>

                    </td>
                </tr>
            <?php } ?>

            <tr><td align="right"><button onclick="closewidget()" class="btn-close">Cerrar</button></td></tr>
        </table>

        <script language="javascript" type="text/javascript">function closewidget() {document.getElementById('sp-panel').style.display='none';}</script>

    </div>
</div>
