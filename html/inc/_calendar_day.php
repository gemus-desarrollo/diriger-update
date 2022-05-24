<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */
?>

<?php
$dayweek= date('w', mktime(0, 0, 0, $month, $day, $year));
$obj->listday($day);

$array_eventos= array();
if (is_array($obj->array_eventos))
    $array_eventos= array_merge_overwrite_by_id($array_eventos, $obj->array_eventos);
?>

<div id="scheduler-container">
    <?php
    $obj_signal->calendar_type= $calendar_type;
    require_once "_calendar_top.inc";
    ?>

    <table id="scheduler" width="100%" cellspacing=0 cellpadding=0>
        <thead>
            <tr>
                <th class="plhead">HORA</th>
                <th class="plhead"><?="{$meses_array[$month]}, {$dayNames[$dayWeek]} $day"?></th>
            </tr>
        </thead>

        <tbody>
        <?php
        for ($hh= 0; $hh < 24; $hh++) {
            if (!test_if_eventos_in_hour($array_eventos, $hh))
                continue;

            if ($hh == date('h'))
                $class .= ' today';
        ?>
            <tr>
                <td class='plhead <?= $class ?>'>
                <?php
                if ($config->hoursoldier)
                    echo $hh;
                else {
                    if ($hh < 12)
                        echo "$hh:00 AM";
                    else {
                       $h= $hh-12;
                       if ($h == 0)
                           $h= 12;
                       echo "$h:00 PM";
                    }
                }
                ?>
                </td>
                <td class='inner <?= $class ?>'>
                    <?php
                    $array= get_array_eventos_day_hour($array_eventos, $day, $hh);

                    foreach ($array as $key => $evento) {
                        $msg_prs = null;
                        if ($evento['toshow'] > 0)
                            $msg_prs = $obj_signal->write_msg_process($evento);

                        $ifmeeting = null;
                        $obj_signal->test_if_synchronize($evento);
                        $if_synchronize= $obj_signal->if_synchronize;
                        $entity_event= $obj_signal->test_if_entity($evento);
                        $if_entity= $entity_event[0];
                        $entity_tipo= $entity_event[1];
                        include "inc/_hidden_input.inc.php";
                    }

                    $obj_signal->list_day($array, _MES, $day);
                    ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

