<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

$obj_tipo1= new Ttipo_evento($clink);
$obj_tipo1->SetYear($year);
$obj_tipo1->SetIdProceso($id_proceso);
if ($id_proceso != $id_proceso_asigna)
    $obj_tipo1->set_id_proceso_asigna($id_proceso_asigna);

$obj_tipo2= new Ttipo_evento($clink);
$obj_tipo2->SetYear($year);
$obj_tipo2->SetIdProceso($id_proceso);
if ($id_proceso != $id_proceso_asigna)
    $obj_tipo2->set_id_proceso_asigna($id_proceso_asigna);

$obj_tipo3= new Ttipo_evento($clink);
$obj_tipo3->SetYear($year);
$obj_tipo3->SetIdProceso($id_proceso);
if ($id_proceso != $id_proceso_asigna)
    $obj_tipo3->set_id_proceso_asigna($id_proceso_asigna);
/*
$obj_tipo1->set_id_proceso_asigna($_SESSION['id_entity']);
$obj_tipo2->set_id_proceso_asigna($_SESSION['id_entity']);
$obj_tipo3->set_id_proceso_asigna($_SESSION['id_entity']);
*/
?>

<div id="scheduler-container">
    <?php $colspan= 17; ?>
    <table id="scheduler" cellspacing=0 cellpadding=3>
      <thead>
        <tr>
            <th rowspan="2" class="plhead">No.</th>

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
            <?php
                ++$colspan;
            }
            ?>
            <?php if ($config->placecolum_y) { ?>
                <th rowspan="2" class="plhead">Lugar</th>
            <?php
                ++$colspan;
            }
            ?>

            <th colspan="12" class="plhead">Meses</th>
            <th rowspan="2" class="plhead">Dirige</th>
            <th rowspan="2" class="plhead" width="20%">Participantes</th>
            <th rowspan="2" class="plhead" width="20%">Observaciones sobre el cumplimiento</th>
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
            $num_rows= anual_plan($header0, $i, 0, null, null);

            $result1= $obj_tipo1->listar($i, 0);
            $k= 0;
            while ($row1= $clink->fetch_array($result1)) {
                $print_bar1= false;
                $header1= (!empty($row1['numero']) ? "{$row1['numero']}) " : "{$j}.{$k}) "). " {$row1['nombre']}";
                $num_rows1= anual_plan($header0, $i, $row1['id'], $header1, null);

                $result2= $obj_tipo2->listar($i, $row1['id']);
                while ($row2= $clink->fetch_array($result2)) {
                    $print_bar2= false;
                    $header2= "{$row2['numero']}) {$row2['nombre']}";
                    $num_rows2= anual_plan($header0, $i, $row2['id'], $header1, $header2);
                }
            }
        }
        ?>
      </tbody>
    </table>
</div>


<?php
function anual_plan($header0, $empresarial, $id_tipo_evento, $header1= null, $header2= null) {
    global $config;
    global $clink;
    global $array_procesos_entity;
    
    global $obj;
    global $obj_event;
    global $obj_signal;
    global $ktotal;
    global $limited;

    global $print_bar0;
    global $print_bar1;
    global $print_bar2;

    global $nums_id_show;
    global $array_id_show;
    global $id_proceso;

    global $signal;
    global $if_numering;

    $obj_tipo= new Tbase($clink);
    
    $obj_user= new Tusuario($clink);
    $obj->listyear($empresarial, $id_tipo_evento, $limited);
    $cant= $obj->GetCantidad();
    ?>

    <?php if ($cant > 0 && !$print_bar0) { ?>
        <tr>
            <td colspan="19" class="colspan">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header0?></div>
            </td>
        </tr>
    <?php
        $print_bar0= true;
    }
    ?>
    <?php if ($cant > 0 && !empty($header1) && !$print_bar1) { ?>
        <tr>
            <td colspan="19" class="colspan">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header1?></div>
            </td>
        </tr>
    <?php
        $print_bar1= true;
    }
    ?>
    <?php if ($cant > 0 && !empty($header2) && !$print_bar2) { ?>
        <tr>
            <td colspan="19" class="colspan">
                <div align="left" style=" font-style:oblique; font-weight:600;"><?=$header2?></div>
            </td>
        </tr>
    <?php
        $print_bar2= true;
    }
    ?>

    <?php
    $j= 0;
    if (empty($cant))
        return 0;

    $obj->sort_eventos();
    foreach ($obj->array_eventos as $evento) {
        ++$ktotal;
        $memo= $evento['memo'];

        $obj_event->Set($evento['id']);
        $fecha_inicio= $obj_event->GetFechaInicioPlan();
        $fecha_fin= $obj_event->GetFechaFinPlan();

        if (empty($evento['id_tarea']) && empty($evento['id_auditoria'])) {
            ++$nums_id_show;
            $array_id_show.= ($nums_id_show > 1) ? ",".$evento['id'] : $evento['id'];
        }

        $numero=  $if_numering == _ENUMERACION_MANUAL || is_null($if_numering) ? $evento['numero'] : $evento['numero_tmp'];
        $numero= !empty($numero) ? $numero : null;
        if (!empty($evento['numero']))
            $numero.= !empty($evento['numero_plus']) ? ".{$evento['numero_plus']}" : null;
        ?>

        <tr>
            <td class="plinner"><?= !empty($numero) ? $numero : ++$j ?></td>
            <!-- <td class="plinner"><?=++$j?></td> -->
            <?php // $obj_event->funcion_temporal_eti(!empty($evento['id_evento']) ? $evento['id_evento'] : $evento['id'], $j); ?>

            <td class="plinner">
                <?php
                $obj_signal->do_list($evento, _YEAR, 0);

                if (!$config->hourcolum_y)
                    echo odbc2ampm($fecha_inicio).', ';
                if (!$config->placecolum_y)
                    echo textparse ($evento['lugar']);

                $if_synchronize= $obj_signal->if_synchronize;
                $entity_event= $obj_signal->test_if_entity($evento);
                $if_entity= $entity_event[0];
                $entity_tipo= $entity_event[1];
                $ifmeeting= null;
                include "inc/_hidden_input.inc.php";
                ?>
            </td>

            <?php if ($config->hourcolum_y) { ?>
                <td class="plinner">
                    <?=odbc2ampm($fecha_inicio)?>
                </td>
            <?php } ?>
            <?php if ($config->placecolum_y) { ?>
                <td class="plinner">
                    <?=$evento['lugar']?>
                </td>
            <?php } ?>

            <?php for ($k= 1; $k < 13; ++$k) { ?>
                <td class="plinner" valign="middle" align="center">
                    <?php build_intervals($evento['month'][$k]); ?>
                </td>
            <?php } ?>

            <td class="plinner">
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

            <td class="plinner">
                <?php
                $obj->set_user_date_ref($evento['fecha_inicio']);
                $array= $obj->get_participantes($evento['id'], null, null, $id_proceso);
                echo $array;

                $origen_data= $obj_tipo->GetOrigenData('participant', $evento['origen_data_asigna']);
                if (!is_null($origen_data))
                    echo "<br /> ".merge_origen_data_participant($origen_data);
                ?>
            </td>

            <td class="plinner"><?= textparse($memo)?></td>
        </tr>

    <?php
    }

    return $j;
}

?>