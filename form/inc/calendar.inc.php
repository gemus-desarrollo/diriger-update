<?php

/* 
 * Geraudis Mustelier Portuondo
 */


$fecha_inicio= !empty($fecha_inicio) ? $fecha_inicio : "{$_year}-01-01";
$fecha_fin= !empty($fecha_fin) ? $fecha_fin : "{$_year}-12-31";

$_init_year= (int)date('Y', strtotime($fecha_inicio));
$init_month= 1;

$_end_year= (int)date('Y', strtotime($fecha_fin));
$end_month= 12;  

if ($table == 'teventos') 
    $obj= new Tevento($clink);
if ($table == 'ttareas') 
    $obj= new Ttarea($clink);
if ($table == 'tauditorias')
    $obj= new Tauditoria($clink);  
?>

<script type="text/javascript">
    <?php
    $cant = 0;
    if (!empty($id)) {
        $obj->get_child_events_by_table($table, $id);
        $cant = count($obj->array_eventos);
    }

    if ($cant > 0) {
        ?>  
            nselected_days= <?= $cant ?>;  
        <?php
    }
    $i = 0;
    foreach ($day_feriados as $day_f) {
        ?>
        day_feriados[<?= $i++ ?>] = "<?= $day_f ?>";
    <?php } ?>    
    
    
    function set_calendar() {
        var active;
        
        <?php
        $no_reject= 0;
        for ($_year= $_init_year; $_year <= $_end_year; ++$_year) { 
            for ($im= 1; $im < 13; $im++) {
                if ($_year <= $_init_year && $im < $init_month) 
                    continue;
                if ($_year >= $_end_year && $im > $end_month) 
                    continue;

                $weekday= 0;
                $time->SetYear($_year);
                $time->SetMonth($im);
                $mm = str_pad($im, 2, '0', STR_PAD_LEFT);

                $lastday = $time->longmonth();

                for ($d= 1; $d <= $lastday; ++$d) {
                    $found= $obj->ifDayEvent($d, $im, $_year);
                    
                    $fecha = $_year . '-' . $mm . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                    $work= !get_work_day($fecha, $weekday) ? "free" : null;
                    $event= !empty($id) && $found ? " event" : null;            
                    $reject= !empty($id) && (!empty($found['aprobado']) || $found['cumplimiento'] == _COMPLETADO) ? " reject" : null;  
                    
                    if ($reject) 
                        ++$no_reject;
            ?>      
                    active= parseInt($('#'+'<?="{$_year}-{$im}-{$d}"?>').val()) == 1 ? " active"  : ""; 
                    _array_td_days["td-<?="{$_year}-{$im}-{$d}"?>"]= "day <?=$work?><?=$event?><?=$reject?>" + active;  
            <?php }  }  } ?>     
    }
</script>
