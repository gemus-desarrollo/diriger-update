<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */
?>

<div id="scheduler-container">
    <?php
    $day_fix= date('d');

    $obj_signal->calendar_type= $calendar_type;
    include_once "_calendar_top.inc";
    ?>

    <table id="scheduler" width="100%" cellspacing=0 cellpadding=0>
        <?php
        $time->SetDay(1);
        $time->SetYear($year);
        $time->SetMonth($month);
        $firstday = $time->weekDay();
        $lastday = $time->longmonth();
        $dd = 0;
        $tdd = 0;
        ?>

        <tr height="20px">
            <?php
            for ($k = 1; $k < $firstday; ++$k) {
                $class = 'left';
                ?>
                <td class='<?=$class?>'></td>
                <?php
            }

            for ($k = $firstday; $k < 8; ++$k) {
                $time->SetDay(++$dd);

                if ($k == 7)
                    $class = 'sunday';
                else {
                    $class = '';
                    if ($k == $firstday)
                        $class .= ' first';
                    if ($dd == $day_fix && $month == $actual_month)
                        $class .= ' today';
                }
                ?>
                <td class='head <?= $class ?>'>
                    <?php if ($k == 1) { ?>
                    <a href="#" class="tdday" onclick="go_calendar(1,<?=$dd?>)" title="Click para ir al plan del día">
                        <?="{$dayNames[$k]} $dd"?>
                    </a>
                    <?php } else { ?>
                    <a href="#" class="tdday" onclick="go_calendar(2,<?=$dd?>)" title="Click para ir al plan del día">
                        <?="{$dayNames[$k]} $dd"?>
                    </a>
                    <?php } ?>
                </td>

            <?php } ?>

        </tr>

        <tr>
            <?php
            for ($i = 1; $i < 8; ++$i) {
                if ($i >= $firstday) {
                    $tdd++;
                    $cellclass = 'inner';

                    if ($i == 7)
                        $cellclass .= ' sunday';
                    if ($i == $firstday)
                        $cellclass .= ' first';
                    if ($tdd == $day_fix && $month == $actual_month)
                        $cellclass .= ' today';
                } else {
                    $cellclass = 'left';
                }
                ?>

                <td class='<?=$cellclass?>'>
                    <?php
                    if ($tdd >= 1) {
                        $obj->listday($tdd);
                        $array = $obj->array_eventos;

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

                        $obj_signal->list_day($array, _MES, $tdd);
                    }
                ?>
                </td>

        <?php } ?>

        </tr>

        <?php
        $col = 1;

        for ($k = $dd; $k < $lastday; ++$k) {
            if ($col == 1)
                echo "<tr>";
            $time->SetDay( ++$dd);

            if ($col == 7)
                $class = 'sunday';
            else
                $class = null;

            if ($col == 1)
                $class = ' first';
            if ($dd == $day_fix && $month == $actual_month)
                $class .= ' today';
            ?>
            <td class='head <?=$class?>'>
                <?php if ($col == 1) { ?>
                <a href="#" class="tdday" onclick="go_calendar(1,<?=$dd?>)" title="Click para ir al plan del día">
                    <?="{$dayNames[$col]} $dd"?>
                </a>
                <?php } else { ?>
                <a href="#" class="tdday" onclick="go_calendar(2,<?=$dd?>)" title="Click para ir al plan del día">
                    <?="{$dayNames[$col]} $dd"?>
                </a>
                <?php } ?>
            </td>

            <?php
            ++$col;
            if ($col == 8) {
                $col = 1;
                echo "</tr><tr>";

                for ($i = 1; $i < 8; ++$i) {
                    $tdd++;

                    if ($i == 7)
                        $class = 'sunday';
                    else {
                        $class = 'inner';
                        if ($i == 1)
                            $class .= ' first';
                        if ($tdd == $day_fix && $month == $actual_month)
                            $class .= ' today';
                    }
                    ?>
                    <td class='<?=$class?>'>
                        <?php
                        $obj->listday($tdd);
                        $array = $obj->array_eventos;

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

                        $obj_signal->list_day($array, _MES, $tdd);
                    ?>
                    </td>
                    <?php
                }

                $col = 1;
                echo "</tr>";
            }
        }

        for ($k = $col; $k < 8; ++$k)
            echo "<td class='right'></td>";

        if ($col < 8) {
            echo "</tr><tr>";

            for ($k = 1; $k < 8; ++$k) {
                $class = null;
                ++$tdd;

                if ($tdd <= $lastday) {
                    $cellclass = 'inner';
                    if ($k == 7)
                        $class = 'sunday';
                    if ($tdd == $day_fix&& $month == $actual_month)
                        $class = ' today';
                    if ($k == 1)
                        $cellclass .= ' first';
                }
                else {
                    $cellclass = 'right';
                    if ($k == 7)
                        $class = null;
                }
                ?>
                <td class='<?="$class $cellclass"?>'>
                    <?php
                    if ($tdd <= $lastday) {
                        $obj->listday($tdd);
                        $array = $obj->array_eventos;

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

                        $obj_signal->list_day($array, _MES, $tdd);
                    }
                    ?>
                </td>

        <?php
    }
    echo "</tr>";
}
?>

    </table>
</div>
