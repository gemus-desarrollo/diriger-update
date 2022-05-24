<?php
function _set_init_112($year, &$array_eventos, &$array_id_eventos, &$array_id_procesos) {
    global $clink;

    bar_progressCSS(1, "set_112 -->_set_112_init($year) --->  (10%)....", 0.1);
    
    $sql= "select * from tproceso_eventos_$year order by cronos desc, cumplimiento desc";
    $result= $clink->query($sql);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        if (empty($row['id_evento']))
            continue;
        if ($array_eventos[$row['id_evento']][$row['id_proceso']]['count'] >= 6)
            continue;
        ++$i;
        $array_eventos[$row['id_evento']][$row['id_proceso']]['id'][]= $row['id'];
        $array_eventos[$row['id_evento']][$row['id_proceso']]['count']+= 1;

        $array_id_eventos[$row['id_evento']]= $row['id_evento'];
        $array_id_procesos[$row['id_proceso']]= $row['id_proceso'];
    }
    
    bar_progressCSS(1, "set_112 -->_set_init_112($year) --->  (100%)....", 1);
    return $i;
}

function _set_112_execute($year, $nums_tb, $array_eventos, $array_id_eventos, $array_id_procesos) {
    global $clink;

    $i= 0;
    $j= 0;
    $sql= null;
    foreach ($array_id_eventos as $id_evento) {
        reset($array_id_procesos);
        foreach ($array_id_procesos as $id_proceso) {
            if (empty($array_eventos[$id_evento][$id_proceso]))
                continue;
            if ($array_eventos[$id_evento][$id_proceso]['count'] < 6)
                continue;

            $string= implode(",", $array_eventos[$id_evento][$id_proceso]['id']);

            $sql.= "delete from tproceso_eventos_$year where id_evento = $id_evento and id_proceso= $id_proceso and id not in ($string); ";
            ++$i;
            ++$j;
            if ($j >= 5000) {
                $clink->multi_query($sql);

                $sql= null;
                $j= 0;
                $r= (float)$i / $nums_tb;
                $_r= $r*100; $_r= number_format($_r,3);
                bar_progressCSS(1, "set_112 -->_set_112_execute($year) ---> modificados:$i ($_r%)....", $r);
            }
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_112 --->_set_112_execute($year) -- (100%)....", 1);
}


