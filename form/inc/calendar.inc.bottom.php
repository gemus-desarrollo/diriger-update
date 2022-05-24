<?php

/* 
 * Geraudis Mustelier Portuondo
 */

if (!empty($id)) {
    switch ($signal) {
        case "anual_plan" :
            $tipo_plan= _PLAN_TIPO_ACTIVIDADES_ANUAL;
            break;
        case "mensual_plan" :
            $tipo_plan= _PLAN_TIPO_ACTIVIDADES_MENSUAL;
            break;
        default :
            $tipo_plan= _PLAN_TIPO_ACTIVIDADES_INDIVIDUAL;
    }
    
    $obj->SetInicio((int)$init_year);
    $obj->SetFin((int)$end_year);
    $obj->get_child_events_by_table($table, $id);

    for ($_year= $init_year; $_year <= $end_year; ++$_year) { 
        for ($im= 1; $im < 13; $im++) {
            if ($_year <= $init_year && $im < $init_month) 
                continue;
            if ($_year >= $end_year && $im > $end_month) 
                continue;

            $weekday= 0;
            $time->SetYear($_year);
            $time->SetMonth($im);
            $mm = str_pad($im, 2, '0', STR_PAD_LEFT);

            $lastday = $time->longmonth();

            for ($i= 1; $i <= $lastday; ++$i) { 
                $found= $obj->ifDayEvent($i, $im, $_year, null);
                if ($found) {                  
?>                 
                   <input class="input-calendar" type="hidden" value="1" id="<?="{$_year}-{$im}-{$i}"?>" name="<?="{$_year}-{$im}-{$i}"?>" /> 

                <?php
                    $reject= !empty($id) && (!empty($found['aprobado']) || $found['cumplimiento'] == _COMPLETADO) ? 1 : 0;
                    if ($reject) {
                ?>   
                        <input class="input-calendar-go_delete" type="hidden" value="0" id="<?="{$_year}-{$im}-{$i}-go_delete"?>" name="<?="{$_year}-{$im}-{$i}-go_delete"?>" />
<?php }  }  }  }  }  } ?> 
