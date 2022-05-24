<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

$obj_tipo= new Ttipo_evento($clink);
$obj_tipo->SetYear($year);
$obj_tipo->SetIdProceso($id_proceso);
$obj_tipo->set_id_proceso_asigna($id_proceso_asigna);

$obj_signal->to_do= _YEAR_PLANNING;
?>

<div id="scheduler-container">
    <?php $colspan= 17; ?>
    <table id="scheduler" cellspacing=0 cellpadding=3>
      <thead>
        <tr>
            <th rowspan="2" class="plhead" style="width:30px">No.</th>

            <th rowspan="2" class="plhead">
                <?php
                $i= 0;
                $colum= "Actividad";

                if (!$config->hourcolum_y) {
                    if ($i == 0) 
                        $colum.= ", ";
                    $colum.= "Hora";
                }
                if (!$config->placecolum_y) {
                    $colum.= ($i == 0) ? ", " : " y ";
                    $colum.= "Lugar";
                }
                echo $colum;
                ?>
            </th>

            <?php if ($config->hourcolum_y) { ?>
                <th rowspan="2" class="plhead">Hora</th>
            <?php ++$colspan; } ?>
            <?php if ($config->placecolum_y) { ?>
                <th rowspan="2" class="plhead">Lugar</th>
            <?php ++$colspan; } ?>

            <th colspan="12" class="plhead">Meses</th>
        </tr>

        <tr>
            <th class="plhead">E</th>
            <th class="plhead">F</th>
            <th class="plhead">M</th>
            <th class="plhead">A</th>
            <th class="plhead">M</th>
            <th class="plhead">J</th>
            <th class="plhead">J</th>
            <th class="plhead">A</th>
            <th class="plhead">S</th>
            <th class="plhead">O</th>
            <th class="plhead">N</th>
            <th class="plhead">D</th>
        </tr>
    </thead>

    <tbody>

    <?php
    if (!empty($id_proceso)) {
        $obj->listyear();
        $cant= $obj->GetCantidad();

        if ($cant > 0) {
            $j= 0;
            foreach ($obj->array_eventos as $evento) {
                ++$ktotal;
                $memo= $evento['memo'];

                $obj_event->Set($evento['id']);
                $fecha_inicio= $obj_event->GetFechaInicioPlan();
                $fecha_fin= $obj_event->GetFechaFinPlan();

                ++$nums_id_show;
                $array_id_show.= ($nums_id_show > 1) ? ",".$evento['id'] : $evento['id'];
    ?>
                <tr>
                    <td class="plinner"><?=++$j?></td>
                    <td class="plinner" style="min-width:150px;">
                        <?php
                        $obj_signal->SetDay(null);
                        $obj_signal->do_list($evento, _YEAR, 0);

                        if (!$config->hourcolum_y)
                            echo odbc2ampm($evento['fecha']).', ';
                        if (!$config->placecolum_y)
                            echo textparse ($evento['lugar']);

                        $_evento= $evento;

                        $cant_event= 0;
                        for ($k= 1; $k < 13; ++$k) {
                            $array= $_evento['month'][$k];
                            $cant_event+= count_no_zero($array);
                        }

                        $ifmeeting= $cant_event > 1 ? 3 : 1;
                        $id_evento= $_evento['id_evento'];
                        $if_synchronize= $obj_signal->if_synchronize;
                        $entity_event= $obj_signal->test_if_entity($evento);
                        $if_entity= $entity_event[0];
                        $entity_tipo= $entity_event[1];
                        include "inc/_hidden_input.inc.php";
                        ?>
                    </td>

                    <?php if ($config->hourcolum_y) { ?>
                        <td class="plinner"><?=odbc2ampm($evento['fecha']) ?></td>
                    <?php } ?>
                    <?php if ($config->placecolum_y) { ?>
                        <td class="plinner"><?= textparse($evento['lugar']) ?></td>
                    <?php } ?>

                    <?php
                    $i = 0;
                    for ($k= 1; $k < 13; ++$k) {
                    ?>
                        <td class="plinner" valign="middle" align="center">
                            <?php
                            $array= $_evento['month'][$k];

                            if (count($array) > 0) {
                                if (isset($obj_tmp)) unset($obj_tmp);
                                $obj_tmp = new Tevento($clink);
                                $obj_tmp->SetYear($year);

                                foreach ($array as $id) {
                                    if (empty($id))
                                        continue;
                                    ++$i;
                                    $evento= $obj_tmp->set_evento($id);
                                  
                                    $entity_event= $obj_signal->test_if_entity($evento);
                                    $if_entity= $entity_event[0];
                                    $entity_tipo= $entity_event[1];
                                    if ($id_evento != $id) {
                                        $ifmeeting= 1;
                                        if ($cant_event > 1)
                                            include "inc/_hidden_input.inc.php";
                                    }
                                    $obj_signal->to_do= _YEAR_PLANNING;
                                    $obj_signal->write_html_day($evento, true);
                                }
                            }
                            ?>
                        </td>
                    <?php
                    }

                    $evento= $_evento;
                    ?>
                  </tr>

	  <?php } } } ?>

        </tbody>
    </table>
</div>

<?php
function count_no_zero($array) {
    $count= 0;
    foreach ($array as $val) {
        if (!empty($val)) ++$count;
    }
    return $count;
}

?>