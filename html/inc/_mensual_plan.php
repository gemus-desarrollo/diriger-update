<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */
$obj->monthstack = $monthstack;
$obj->set_print_reject($print_reject);
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
                }
                ?>

                <th class="plhead" width="12%">Dirige</th>
                <th class="plhead" width="20%">Participan</th>
                <th class="plhead" width="20%">Observaciones sobre el cumplimiento</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $array_week = array('PRIMERA', 'SEGUNDA', 'TERCERA', 'CUARTA', 'QUINTA');
            $nweek = 0;
            $initdayweek = 1;
            $enddayweek = null;
            $j = 0;

            $time->SetMonth($month);
            $time->SetYear($year);
            $lastday = $time->longmonth();

            for ($i = 1; $i <= $lastday; ++$i) {
                $time->SetDay($i);
                if (strtotime($time->GetStrDate()) < strtotime($obj->fecha_inicio_plan_page))  
                    continue;
                if (strtotime($time->GetStrDate()) > strtotime($obj->fecha_fin_plan_page))  
                    continue;

                $weekday = $time->weekDay();

                if ($i == 1 || $weekday == 1) {
                    $enddayweek = $initdayweek + (7 - $weekday);
                    if ($enddayweek > $lastday)
                        $enddayweek = $lastday;
                    ?>
                    <tr>
                        <td colspan="<?= $colspan ?>" class="colspan" align="center">
                            <?= $array_week[$nweek++] ?> SEMANA  (<?= "{$initdayweek}-{$enddayweek}" ?>)
                        </td>
                    </tr>
                    <?php
                    $initdayweek = ++$enddayweek;
                }
                ?>
                <tr>
                    <td colspan="<?= $colspan ?>" class="colspan_dia"><?= $dayNames[$weekday] . " " . $i ?> </td>
                </tr>

                <?php
                $obj->listday($i);
                $array_eventos = $obj->array_eventos;
                $cant = $obj->GetCantidad();

                if (empty($cant)) {
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

                    foreach ($array_eventos as $evento) {
                        $obj_signal->to_write = false;
                        $show = $obj_signal->do_list($evento, _MES_GENERAL, $i);

                        if (!is_null($show)) {
                            ?>
                            <tr>
                                <td class="plinner"><?= ++$j ?></td>

                                <td class="plinner left">
                                    <?php
                                    echo $show;
                                    $k = 0;

                                    if (!$config->hourcolum) {
                                        $br = ($k == 0) ? "" : ", ";
                                        ++$k;
                                        echo $br . odbc2ampm($evento['fecha']);
                                    }
                                    if (!$config->placecolum) {
                                        $br = ($k == 0) ? "" : ", ";
                                        ++$k;
                                        echo $br . $evento['lugar'];
                                    }
                                    if (!$config->datecolum) {
                                        $br = ($k == 0) ? "" : ", ";
                                        ++$k;
                                        echo $br . $dayNames[$weekday] . " " . $i;
                                    }

                                    $msg_prs = null;
                                    if ($evento['toshow'] > 1)
                                        $msg_prs = $obj_signal->write_msg_process($evento);

                                    $ifmeeting = null;
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
                                <td class="plinner left"><?= $dayNames[$weekday] . " " . $i ?></td>
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
                }
            }
            ?>
        </tbody>
    </table>
</div>
