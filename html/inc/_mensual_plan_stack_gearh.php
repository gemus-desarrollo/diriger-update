<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */
$obj->monthstack = $monthstack;
$obj->set_print_reject($print_reject);

$time->SetMonth($month);
$time->SetYear($year);

$obj_tipo1= new Ttipo_evento($clink);
// $obj_tipo1->SetYear($year);
$obj_tipo1->SetIdProceso($id_proceso);
$obj_tipo1->set_id_proceso_asigna($id_proceso_asigna);

$obj_tipo2= new Ttipo_evento($clink);
// $obj_tipo2->SetYear($year);
$obj_tipo2->SetIdProceso($id_proceso);
$obj_tipo2->set_id_proceso_asigna($id_proceso_asigna);

$obj_tipo3= new Ttipo_evento($clink);
// $obj_tipo3->SetYear($year);
$obj_tipo3->SetIdProceso($id_proceso);
$obj_tipo3->set_id_proceso_asigna($id_proceso_asigna);
?>

<div id="scheduler-container">
    <?php $colspan = 5; ?>
    <table id="scheduler" cellspacing=0 cellpadding=3>
        <thead>
            <tr>
                <th class="plhead" width="30px">No.</th>

                <th class="plhead">
                    <?php
                    $i = 0;
                    $colum = "Actividad";

                    if (!$config->hourcolum) {
                        $colum .= ($i == 0) ? ", " : " y ";
                        ++$i;
                        $colum .= "Hora";
                    }
                    if (!$config->placecolum) {
                        $colum .= ($i == 0) ? ", " : " y ";
                        ++$i;
                        $colum .= "Lugar";
                    }
                    if (!$config->datecolum) {
                        $colum .= ($i == 0) ? ", " : " y ";
                        ++$i;
                        $colum .= "Fecha";
                    }
                    echo $colum;
                    ?>
                </th>

                <?php if ($config->hourcolum) { ?>
                    <th class="plhead" width="40px">Hora</th>
                    <?php
                    ++$colspan;
                }
                ?>
                <?php if ($config->placecolum) { ?>
                    <th class="plhead" width="14%">Lugar</th>
                    <?php
                    ++$colspan;
                }
                ?>
                <?php if ($config->datecolum) { ?>
                    <th class="plhead" width="50px">Fecha</th>
                    <?php
                    ++$colspan;
                } ?>

                <th class="plhead" width="12%">Dirige</th>
                <th class="plhead" width="25%">Participan</th>
                <th class="plhead" width="20%">Observaciones sobre el cumplimiento</th>
            </tr>
        </thead>

        <tbody>
        <?php
        mensual_plan(null, 1, 0, null, null);

        for ($i= 2; $i < _MAX_TIPO_ACTIVIDAD; ++$i) {
            $ktotal= 0;
            $j= $i-1;

            $print_bar0= false;
            $print_bar1= false;
            $print_bar2= false;

            $header0= null;
            $header1= null;
            $header2= null;

            $header0=  number_format_to_roman($j).'. '.$tipo_actividad_array[$i];
            $num_rows= mensual_plan($header0, $i, 0, null, null);

            $result1= $obj_tipo1->listar($i, 0);
            $k= 0;
            while ($row1= $clink->fetch_array($result1)) {
                $print_bar1= false;
                $header1= (!empty($row1['numero']) ? "{$row1['numero']}) " : "{$j}.{$k}) "). " {$row1['nombre']}";
                $num_rows1= mensual_plan($header0, $i, $row1['id'], $header1, null);

                $result2= $obj_tipo2->listar($i, $row1['id']);
                while ($row2= $clink->fetch_array($result2)) {
                    $print_bar2= false;
                    $header2= "{$row2['numero']}) {$row2['nombre']}";
                    $num_rows2= mensual_plan($header0, $i, $row2['id'], $header1, $header2);
                }
            }
        }
        ?>
        </tbody>
    </table>
</div>

<?php
function mensual_plan($header0, $empresarial, $id_tipo_evento, $header1= null, $header2= null) {
    global $config;
    global $clink;
    global $array_procesos_entity;
    
    global $obj;
    global $ktotal;

    global $print_bar0;
    global $print_bar1;
    global $print_bar2;

    global $id_proceso;
    global $print_reject;
    global $dayNames;
    global $monthstack;

    global $obj;
    global $obj_user;
    global $obj_signal;

    global $if_numering;

    $obj_tipo= new Tbase($clink);

    $obj->listmonth($empresarial, $id_tipo_evento, null);
    $cant= $obj->GetCantidad();
    $array_eventos = $obj->array_eventos;
    ?>

    <?php if ($cant > 0 && !$print_bar0) { ?>
        <tr>
            <td colspan="8" class="colspan">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header0?></div>
            </td>
        </tr>
    <?php
        $print_bar0= true;
    }
    ?>

    <?php if ($cant > 0 && !empty($header1) && !$print_bar1) { ?>
        <tr>
            <td colspan="8" class="colspan">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header1?></div>
            </td>
        </tr>
    <?php
        $print_bar1= true;
    }
    ?>

    <?php if ($cant > 0 && !empty($header2) && !$print_bar2) { ?>
        <tr>
            <td colspan="8" class="colspan">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header2?></div>
            </td>
        </tr>
    <?php
        $print_bar2= true;
    }
    ?>

    <?php
    $j = 0;

    if (empty($cant)) {
        ?>
        <!--
        <tr>
            <td class="plinner">&nbsp;</td>

            <td class="plinner left"></td>
            <?php if ($config->hourcolum) { ?><td class="plinner left"></td><?php } ?>
            <?php if ($config->placecolum) { ?><td class="plinner left"></td><?php } ?>
            <?php if ($config->datecolum) { ?><td class="plinner left"></td><?php } ?>

            <td class="plinner left"></td>
            <td class="plinner left"></td>
            <td class="plinner left"></td>
        </tr>
        -->
        <?php
    } else {

        foreach ($array_eventos as $evento) {
            $obj_signal->to_write = false;
            $show= $obj_signal->do_list($evento, _MES_GENERAL_STACK, $i);

            if (!is_null($show)) {
                ?>
                <tr>
                    <td class="plinner">
                        <?php
                        ++$j;
                        /*
                        $numero=  $if_numering == _ENUMERACION_MANUAL || is_null($if_numering) ? $evento['numero'] : $evento['numero_tmp'];
                        $numero= !empty($numero) ? $numero : $j;
                        echo $numero;
                         */
                        echo $j;
                        ?>
                    </td>

                    <td class="plinner left">
                        <?php
                        echo $show;

                        $k = 0;

                        if (!$config->hourcolum) {
                            $br = ($k == 0) ? "" : ", ";
                            ++$k;
                            echo $br . odbc2ampm($evento['fecha']);
                        }
                        if (!$config->datecolum) {
                            $br = ($k == 0) ? " " : ", ";
                            ++$k;
                            echo $br . "Días:";
                            build_intervals($evento['month'], $obj->array_status_eventos_ids, $print_reject);
                        }
                        if (!$config->placecolum) {
                            $br = ($k == 0) ? " " : ", ";
                            ++$k;
                            echo $br . $evento['lugar'];
                        }

                        $msg_prs = null;
                        if ($evento['toshow'] > 1)
                            $msg_prs = $obj_signal->write_msg_process($evento);

                        $ifmeeting = null;
                        $obj_signal->test_if_synchronize($evento);
                        $if_synchronize= $obj_signal->if_synchronize;
                        $entity_event= $obj_signal->test_if_entity($evento);
                        $if_entity= $entity_event[0];
                        $entity_tipo= $entity_event[1];
                        include "inc/_hidden_input.inc.php";
                        ?>
                    </td>

                    <?php if ($config->hourcolum) { ?><td class="plinner left"><?= odbc2ampm($evento['fecha']) ?></td><?php } ?>
                    <?php if ($config->placecolum) { ?><td class="plinner left"><?= $evento['lugar'] ?></td><?php } ?>
                    <?php if ($config->datecolum) { ?>
                        <td class="plinner left">
                            <?php build_intervals($evento['month'], $obj->array_status_eventos_ids, $print_reject); ?>
                        </td>
                    <?php } ?>

                    <td class="plinner left">
                        <?php
                        if (!empty($evento['funcionario']))
                            echo "<strong>Externo: </strong>{$evento['funcionario']}<br/>";  

                        $class= null;
                        if ($evento['id_responsable_asigna'] != $evento['id_responsable']) {
                            $class= "inside_responsable";
                            $email= $obj_user->GetEmail($evento['id_responsable_asigna']);
                            if ($config->onlypost)
                                echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                            else
                                echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null);
                            echo "<br/>";
                        }                    
                        ?>
                        <?php if ($class) { ?>
                        <div class="inside_responsable">
                        <?php } ?>
                        <?php
                        $email= $obj_user->GetEmail($evento['id_responsable']);
                        if ($config->onlypost)
                            echo !empty($email['cargo']) ? textparse($email['cargo']) : $email['nombre'];
                        else
                            echo $email['nombre'].(!empty($email['cargo']) ? ", ".textparse($email['cargo']) : null); 
                        ?>
                        <?php if ($class) { ?>
                        </div>
                        <?php } ?>  
                    </td>

                    <td class="plinner left">
                        <?php
                        $obj->set_user_date_ref($evento['fecha_inicio']);
                        $array = $obj->get_participantes($evento['id'], null, null, $id_proceso);
                        echo $array;

                        $origen_data = $obj->GetOrigenData('participant', $evento['origen_data_asigna']);
                        if (!is_null($origen_data))
                            echo "<br /> " . merge_origen_data_participant($origen_data);
                        ?>
                    </td>
                    <td class="plinner left"><?= !is_null($evento['memo']) ? $evento['memo'] : '&nbsp;' ?></td>
                </tr>

            <?php }
        }
    }
}
?>
