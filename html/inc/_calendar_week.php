<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2019
 */
?>

<?php
$nweek= date('W', mktime(0, 0, 0, $month, $day, $year));
$dayweek= date('w', mktime(0, 0, 0, $month, $day, $year));
if ($dayweek == 7)
    $dayweek= 0;
$week_init= date('Y-m-d', mktime(0, 0, 0, $month, $day-$dayweek+1, $year));
$week_end= date('Y-m-d', mktime(0, 0, 0, $month, $day+(7-$dayweek), $year));

$day_fix= date('d');
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
                <th class="plhead">Lunes</th>
                <th class="plhead">Martes</th>
                <th class="plhead">Míercoles</th>
                <th class="plhead">Jueves</th>
                <th class="plhead">Viernes</th>
                <th class="plhead">Sábado</th>
                <th class="plhead">Domíngo</th>
            </tr>
            <tr>
                <th class="plhead inner"></th>
                <?php
                $i= 0;
                for ($date= $week_init; $date <= $week_end; $date= date('Y-m-d', strtotime($date."+ 1 days"))) {
                    $dd= date('d', strtotime($date));
                    ++$i;
                    $class = $i == 7 ? 'sunday' : 'inner';

                    if ($dd == $day_fix && $mm == $actual_month)
                        $class .= ' today';
                ?>
                    <th class="plhead <?= $class ?>">
                        <a href="#" class="tdday" onclick="go_calendar(2,<?=$dd?>)" title="Click para ir al plan del día">
                            <?=date('d/m', strtotime($date))?>
                        </a>
                    </th>
                <?php } ?>
            </tr>
        </thead>

        <tbody>
        <?php
        $array_eventos= array();
        for ($date= $week_init; $date <= $week_end; $date= date('Y-m-d', strtotime($date."+ 1 days"))) {
            $dd= date('d', strtotime($date));
            $mm= date('m', strtotime($date));

            $obj->listday($dd);
            if (is_array($obj->array_eventos))
                $array_eventos= array_merge_overwrite_by_id($array_eventos, $obj->array_eventos); 
        }

        for ($hh= 0; $hh < 24; $hh++) {
            $dd= date('d', strtotime($date));
            $mm= date('m', strtotime($date));

            if (!test_if_eventos_in_hour($array_eventos, $hh))
                continue;

        ?>
            <tr>
                <td class="plhead">
                <?php
                if ($config->hoursoldier)
                    echo $hh;
                else {
                    if ($hh < 12)
                        echo "$hh:00 AM";
                    else {
                       $h= $hh-12;
                       echo "$h:00 PM";
                    }
                }
                ?>
                </td>
                <?php
                $i= 0;
                for ($date= $week_init; $date <= $week_end; $date= date('Y-m-d', strtotime($date."+ 1 days"))) {
                    ++$i;

                    $dd= date('d', strtotime($date));
                    $mm= date('m', strtotime($date));
                    $class = $i == 7 ? 'sunday' : 'inner';

                    if ($dd == $day_fix && $mm == $actual_month)
                        $class .= ' today';
                ?>
                <td class='<?= $class ?>'>
                    <?php
                    $array= get_array_eventos_day_hour($array_eventos, $dd, $hh); 
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

                    $obj_signal->list_day($array, _MES, $dd);
                    ?>
                </td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

