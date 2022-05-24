 <?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

$cant_print_reject= 0;
$obj->monthstack = $monthstack;
$time->SetMonth($month);
$time->SetYear($year);
?>

<div id="scheduler-container">
    <?php $colspan = 5; ?>
    <table id="scheduler" cellspacing='0' cellpadding='3'>
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
            $j = 0;

            $obj->set_print_reject(-1);
            $obj->listmonth();
            $cant = $obj->GetCantidad();

            $array_eventos = $obj->array_eventos;        
            $obj_signal->array_status_eventos_ids= $obj->array_status_eventos_ids;
            $show= $obj_signal->get_status_intervals($array_eventos);
            $cant_print_reject= $obj_signal->cant_print_reject;

            if (empty($cant) || !$show) {
            ?>
                <tr>
                    <td class="plinner">&nbsp;</td>

                    <td class="plinner left"></td>
                    <?php if ($config->hourcolum) { ?>
                    <td class="plinner left"></td>
                    <?php } ?>
                    <?php if ($config->placecolum) { ?>
                    <td class="plinner left"></td>
                    <?php } ?>
                    <?php if ($config->datecolum) { ?>
                    <td class="plinner left"></td>
                    <?php } ?>

                    <td class="plinner left"></td>
                    <td class="plinner left"></td>
                    <td class="plinner left"></td>
                </tr>

                <?php
            } else {
                reset($array_eventos);
                foreach ($array_eventos as $evento) {
                    $obj_signal->to_write = false;
                    $show = $obj_signal->do_list($evento, _MES_GENERAL_STACK, $i);
                    
                    if (is_null($show))
                        continue;
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
                                $cant_print_reject+= build_intervals($evento['month'], $obj->array_status_eventos_ids, $print_reject);
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

                        <?php if ($config->hourcolum) { ?>
                        <td class="plinner left"><?= odbc2ampm($evento['fecha']) ?></td>
                        <?php } ?>
                        <?php if ($config->placecolum) { ?>
                        <td class="plinner left"><?= $evento['lugar'] ?></td>
                        <?php } ?>
                        <?php if ($config->datecolum) { ?>
                            <td class="plinner left">
                                <?php build_intervals($evento['month'], $obj->array_status_eventos_ids, $print_reject) ?>
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
                <?php 
                }
            } 
            ?>
        </tbody>
    </table>
</div>
