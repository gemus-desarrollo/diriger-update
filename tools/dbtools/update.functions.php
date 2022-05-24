<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 19/09/14
 * Time: 9:51
 */

require_once _ROOT_DIRIGER_DIR."php/config.inc.php";

include_once _ROOT_DIRIGER_DIR."client_images/config.ini.php";

require_once _ROOT_DIRIGER_DIR."tools/dbtools/clean.class.php";
require_once _ROOT_DIRIGER_DIR."tools/common/file.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/code.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/proceso.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/usuario.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/indicador.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/registro.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/tipo_evento.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/asistencia.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/evento.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/auditoria.class.php";
require_once _ROOT_DIRIGER_DIR."php/class/tarea.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/tipo_reunion.class.php";

require_once _ROOT_DIRIGER_DIR."php/class/tablero.class.php";


set_time_limit(0);

global $clink;
global $cronos;
global $error;
global $location;

$cronos= date('Y-m-d H:i:s');
$location= $_SESSION['location'];

require_once _ROOT_DIRIGER_DIR."tools/dbtools/update.functions.inc";

/*
 * Reparar los eventos y auditorias que no se copiaron el plan anual por las direcciones
 */
function test_in_proceso_evento($id_evento_code, $id_auditoria_code, $row_prs) {
    global $clink;
    global $cronos;
    global $location;

    if (!empty($id_evento_code))
        $sql= "select * from teventos where id_code = '$id_evento_code' or id_evento_code = '$id_evento_code'";
    else
        $sql= "select * from tauditorias where id_code = '$id_auditoria_code' or id_auditoria_code = '$id_auditoria_code'";
    $result= $clink->query($sql);

    $array_ids= array();
    while ($row= $clink->fetch_array($result))
        $array_ids[]= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_auditoria'=>$row['id_auditoria'],
            'id_auditoria_code'=>$row['id_auditoria_code'], 'id_tarea'=>$row['id_tarea'], 'id_tarea_code'=>$row['id_tarea_code']);

    $id_responsable= setNULL($row_prs['id_responsable']);
    $toshow= setNULL($row_prs['toshow']);

    $sql= null;
    foreach ($array_ids as $array) {
        if (!empty($id_evento_code)) {
            $id_evento= $array['id'];
            $id_evento_code= setNULL_str($array['id_code']);

            $id_auditoria= setNULL($array['id_auditoria']);
            $id_auditoria_code= setNULL_str($array['id_auditoria_code']);
        } else {
            $id_auditoria= $array['id'];
            $id_auditoria_code= setNULL_str($array['id_code']);

            $id_evento= "NULL";
            $id_evento_code= "NULL";
        }

        $id_tarea= setNULL($row_prs['id_tarea']);
        $id_tarea_code= setNULL_str($row_prs['id_tarea_code']);

        $sql.= "insert into tproceso_eventos_2020 (id_evento, id_evento_code, id_auditoria, id_auditoria_code, ";
        $sql.= "id_proceso, id_proceso_code, id_tarea, id_tarea_code, toshow, id_responsable, cronos, situs) ";
        $sql.= "values ($id_evento, $id_evento_code, $id_auditoria, $id_auditoria_code, {$row_prs['id_proceso']}, ";
        $sql.= "'{$row_prs['id_proceso_code']}', $id_tarea, $id_tarea_code, $toshow, $id_responsable, '$cronos', '$location'); ";
    }
    if ($sql)
        $clink->multi_query($sql);
}

/*
 * Reparar los eventos que no se copiaron el plan anual por las direcciones
 */
function set_89() {
    global $clink;

    $sql= "select * from teventos where id_evento is null and year(fecha_inicio_plan) = 2019 and copyto like '%2020%'";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $sql= "select * from tproceso_eventos_2019 where id_evento = {$row['id']} order by cronos desc";
        $result_prs= $clink->query($sql);

        while ($row_prs= $clink->fetch_array($result_prs)) {
            if ($row_prs['id_proceso'] == $_SESSION['local_proceso_id'])
                continue;
            $copy= null;
            preg_match('/[A-Z]{2}[0-9]{8}/', $row['copyto'], $copy);
            test_in_proceso_evento($copy[0], null, $row_prs);
        }

        ++$j;
        if ($j >= 100) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_89 --->  ($_r%)....", $r);
    }   }
}
/*
 * Reparar las auditorias que no se copiaron el plan anual por las direcciones
 */
function set_90() {
    global $clink;

    $sql= "select * from tauditorias where id_auditoria is null and year(fecha_inicio_plan) = 2019 and copyto like '%2020%'";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $sql= "select * from tproceso_eventos_2019 where id_auditoria = {$row['id']} and id_evento is null";
        $result_prs= $clink->query($sql);

        while ($row_prs= $clink->fetch_array($result_prs)) {
            if ($row_prs['id_proceso'] == $_SESSION['local_proceso_id'])
                continue;
            $copy= null;
            preg_match('/[A-Z]{2}[0-9]{8}/', $row['copyto'], $copy);
            test_in_proceso_evento(null, $copy[0], $row_prs);
        }

        ++$j;
        if ($j >= 100) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_89 --->  ($_r%)....", $r);
    }   }
}

/*
 * Cargar la tabla ttipo_auditorias
 */
function set_91() {
    global $clink;
    global $cronos;
    global $location;
    global $Ttipo_auditoria_array;

    $sql= "select * from ttipo_auditorias";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    if ($nums_tb > 0)
        return null;

    $i= 0;
    foreach ($Ttipo_auditoria_array as $tipo) {
        if ($i == 0) {
            ++$i;
            continue;
        }
        $id_code= $location . str_pad($i, 8, '0', STR_PAD_LEFT);
        $sql= "insert into ttipo_auditorias (id, id_code, numero, nombre, descripcion, id_proceso, id_proceso_code, ";
        $sql.= "cronos, situs) values ($i, '$id_code', $i, '$tipo', null, {$_SESSION['local_proceso_id']}, ";
        $sql.= "'{$_SESSION['local_proceso_id_code']}', '$cronos', '$location')";
        $clink->query($sql);

        ++$i;
    }

    bar_progressCSS(1, "set_91 --->  (100%)....", 1);
}

/*
 * Cargar la tabla ttipo_reuniones
 */
function set_92() {
    global $clink;
    global $cronos;
    global $location;
    global $meeting_array;

    $sql= "select * from ttipo_reuniones";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    if ($nums_tb > 0)
        return null;

    $i= 0;
    foreach ($meeting_array as $id => $nombre) {
        if ($i == 0) {
            ++$i;
            continue;
        }
        $id_code= $location . str_pad($id, 8, '0', STR_PAD_LEFT);
        $sql= "insert into ttipo_reuniones (id, id_code, numero, nombre, descripcion, id_proceso, id_proceso_code, ";
        $sql.= "cronos, situs) values ($id, '$id_code', $id, '$nombre', null, {$_SESSION['local_proceso_id']}, ";
        $sql.= "'{$_SESSION['local_proceso_id_code']}', '$cronos', '$location')";
        $clink->query($sql);

        ++$i;
    }

    bar_progressCSS(1, "set_92 --->  (100%)....", 1);
}

/*
 * Arreglar la tabla tunidades
 */
function set_93() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tunidades";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        $id_code= $location . str_pad($row['id'], 8, '0', STR_PAD_LEFT);

        $sql= "update tunidades set id_code= '$id_code', id_proceso= {$_SESSION['local_proceso_id']}, ";
        $sql.= "id_proceso_code= '{$_SESSION['local_proceso_id_code']}', cronos= '$cronos', situs='$location' ";
        $sql.= "where id = {$row['id']}";
        $clink->query($sql);

        ++$i;
        ++$j;
        if ($j >= 100) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_93 --->  ($_r%)....", $r);
    }   }
}

/*
 * llenar el campo id_entity de la tabla tprocesos
 */
function set_94() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tprocesos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        if ($row['id'] == $_SESSION['local_proceso_id']) {
            $_SESSION['local_proceso_id_code']= $row['id_code'];
            $_SESSION['superior_proceso_id']= $row['id_proceso'];
            continue;
        }
        if (!empty($_SESSION['superior_proceso_id']) && $row['id'] == $_SESSION['superior_proceso_id'])
            continue;

        $sql= "update tprocesos set id_entity = {$_SESSION['local_proceso_id']}, id_entity_code = '{$_SESSION['local_proceso_id_code']}', ";
        $sql.= "cronos = '$cronos' where id = {$row['id']}";
        $clink->query($sql);

        ++$i;
        ++$j;
        if ($j >= 100) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_94 --->  ($_r%)....", $r);
    }   }
}

/*
 * Agregar a la asistencias los responsables y secrtarios de las reuniones
 */
function insert_tasistencia($id_evento, $id_evento_code, $id_usuario) {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tasistencias where id_evento= $id_evento and id_usuario = $id_usuario";
    $result= $clink->query($sql);
    $cant= $clink->num_rows($result);
    if (!empty($cant) && $cant != -1)
        return;

    $sql= "insert into tasistencias (id_evento, id_evento_code, id_usuario, id_proceso, id_proceso_code, cronos, situs) ";
    $sql.= "values ($id_evento, '$id_evento_code', $id_usuario, {$_SESSION['local_proceso_id']}, ";
    $sql.= "'{$_SESSION['local_proceso_id_code']}', '$cronos', '$location')";
    $clink->query($sql);
    $error= $clink->error();
    if (!empty($error))
        return;

    $id= $clink->inserted_id("tasistencias");

    $id_code= $location . str_pad($id, 8, '0', STR_PAD_LEFT);
    $sql= "update tasistencias set id_code= '$id_code' where id = $id";
    $clink->query($sql);
}

function set_95() {
    global $clink;

    $sql= "select * from teventos where id_tipo_reunion is not null and id_proceso = {$_SESSION['local_proceso_id']}";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        insert_tasistencia($row['id'], $row['id_code'], $row['id_responsable']);
        insert_tasistencia($row['id'], $row['id_code'], $row['id_secretary']);

        ++$i;
        ++$j;
        if ($j >= 100) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_95 --->  ($_r%)....", $r);
    }   }
}

/*
 * Poner las tareas en la tablas tproceso_eventos
 */
function set_96() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from ttareas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        $year_init= date('Y', strtotime($row['fecha_inicio_plan']));
        $year_end= date('Y', strtotime($row['fecha_fin_plan']));

        $sql= null;
        for ($year= $year_init; $year <= $year_end; $year++) {
            $sql.= "insert into tproceso_eventos_$year (id_tarea, id_tarea_code, id_proceso, id_proceso_code, cronos, situs) values ";
            $sql.= "({$row['id']}, '{$row['id_code']}', {$row['id_proceso']}, '{$row['id_proceso_code']}', '$cronos', '$location'); ";
        }
        if ($sql)
            $clink->multi_query($sql);

        ++$i;
        ++$j;
        if ($j >= 100) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_96 --->  ($_r%)....", $r);
    }   }
}

/*
 * Alargar los campos id_code
 */
require_once _ROOT_DIRIGER_DIR."tools/repare/repare_id.inc";

/*
// solo para casos exepcionales
$array_only_table_set_97= array('ttipo_reuniones', 'ttipo_auditorias', 'teventos', 'tauditorias', 'tproceso_eventos_2017',
                            'tproceso_eventos_2018', 'tproceso_eventos_2019', 'tproceso_eventos_2020', 'tproceso_eventos_2021');
*/
function set_97() {
    global $clink;
    global $array_only_table_set_97;

    execute_rebuild();
}

/*
 * ponerle a los archivos (tarchivos) los procesos a los que pertenecen (id_proceso) a partir
 * del proceso al que pertenece el uusario que los genero
 */
function set_98() {
    global $clink;

    $sql= "select distinct id, id_proceso, id_proceso_code from tusuarios";
    $result= $clink->query($sql);

    $array_usuarios= array();
    while ($row= $clink->fetch_array($result)) {
        $array_usuarios[$row['id']]= array($row['id_proceso'], $row['id_proceso_code']);
    }

    $sql= "select id, id_usuario from tarchivos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $id_proceso= !empty($row['id_usuario']) ? $array_usuarios[$row['id_usuario']][0] : $_SESSION['local_proceso_id'];
        $id_proceso_code= !empty($row['id_usuario']) ? $array_usuarios[$row['id_usuario']][1] : $_SESSION['local_proceso_id_code'];

        $sql.= "update tarchivos set id_proceso= $id_proceso, id_proceso_code= '$id_proceso_code' where id = {$row['id']}; ";

        ++$i;
        ++$j;
        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_98 --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_98 --->  (100%)....", 1);
}


require_once _ROOT_DIRIGER_DIR."tools/repare/repare_serie_archive.inc";

/*
 * Arreglar la numeracion de los archivos en la oficina de archivo de las direcciones functionales
 * UEB y departamento
 */
function set_99() {
    global $clink;

    execute_repare_archive();
}

/*
 * llenar el campo codigo de la tabla tarchivos
 */
function set_100 () {
    global $clink;

    $sql= "select * from tarchivos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $obj_prs= new Tproceso($clink);
    $obj_prs->get_codigo_archive_array();
    $array_codigo_archives= $obj_prs->array_codigo_archives;

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $codigo= boolean($row['if_output']) ? "RS" : "RE";
        $codigo.= "-".str_pad($row['numero'], 6, "0", STR_PAD_LEFT);
        $codigo.= "-{$row['year']}";

        if (!empty($array_codigo_archives[$row['id_proceso']]))
            $codigo.= "-{$array_codigo_archives[$row['id_proceso']]}";
        $sql.= "update tarchivos set codigo = '$codigo' where id = {$row['id']}; ";

        ++$i;
        ++$j;
        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_100 --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_100 --->  (100%)....", 1);
}

/*
 * crear la carpeta data/temp
 */
function set_101 () {
    $dir= _DATA_DIRIGER_DIR."temp";
    exec("mkdir $dir");
}

/*
 * Actualizar el campo id_entity en los tableros
 */
function set_102 () {
    global $clink;
    global $config;

    if (empty($config->local_proceso_id)) {
        $config->local_proceso_id= $_SESSION['local_proceso_id'];
    }
    if (empty($config->local_proceso_id_code)) {
        $config->local_proceso_id_code= $_SESSION['local_proceso_id_code'];
    }
    $sql= "update ttableros set id_entity= $config->local_proceso_id, id_entity_code = '$config->local_proceso_id_code' ";
    $clink->query($sql);
}

/*
 * Agregar los que estan en los grupos y no estan en la asistencia a los eventos
 */
function if_exist_id_usuario($id_usuario, $array_asistencias) {
    reset($array_asistencias);
    foreach ($array_asistencias as $array) {
        if ($array['id_usuario'] == $id_usuario)
            return true;
    }
    return false;
}

function _set_asistencia_usuario($id_evento, $id_evento_code, $fecha_inicio_plan) {
    global $clink;

    $obj_event= new Tevento($clink);
    $obj_event->SetYear(date('Y', strtotime($fecha_inicio_plan)));
    $obj_event->SetIdEvento($id_evento);
    $obj_event->set_id_evento_code($id_evento_code);
    $array_usuarios= $obj_event->get_usuarios_array_from_evento();

    $obj_assist= new Tasistencia($clink);
    $obj_assist->SetIdEvento($id_evento);
    $obj_assist->set_id_evento_code($id_evento_code);
    $array_asistencias= $obj_assist->get_asistencias();

    $obj_assist->SetIdProceso($_SESSION['local_proceso_id']);
    $obj_assist->set_id_proceso_code($_SESSION['local_proceso_id_code']);

    foreach ($array_usuarios as $id_usuario => $array) {
        $found= if_exist_id_usuario($id_usuario, $array_asistencias);
        if (!$found) {
            $obj_assist->SetIdUsuario($id_usuario);
            $obj_assist->add();
        }
    }
}

function set_103 () {
    global $clink;

    $sql= "select * from teventos where id_tipo_reunion is not null";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        _set_asistencia_usuario($row['id'],$row['id_code'], $row['fecha_inicio_plan']);

        ++$i;
        $r= (float)$i / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_103 --->  ($_r%)....", $r);
    }

    bar_progressCSS(1, "set_103 --->  (100%)....", 1);
}

/*
 * Agregar el campo if_entity a las tablas tproceso_eventos_{year} para identificar el proceso
 * entidad que dio oriigen a la tarea, auditoria o evento
 */

$array_usuarios_entity= array();

function _set_104_part($result, $item, $nums_tb) {
    global $clink;
    global $array_usuarios_entity;

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $id_entity= $array_usuarios_entity[$row['id_usuario']][0];
        $id_entity_code= $array_usuarios_entity[$row['id_usuario']][1];

        if ($id_entity != $row['id_proceso']) {
            $sql.= "update t{$item}s set id_proceso= $id_entity, id_proceso_code= '$id_entity_code' ";
            $sql.= "where id = {$row['id']}; ";
        }

        ++$i;
        ++$j;
        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_104 ($item) --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_104 ($item) --->  (100%)....", 1);
}

/*
 * A las tablas teventos, tauditorias y ttareas modificarle el campo id_proceso, especificando
 * la entidad en la que fueron creadas
 */
function set_104() {
    global $clink;
    global $array_usuarios_entity;

    $sql= "select distinct tusuarios.id as _id, tprocesos.id as id_entity, tprocesos.id_code as id_entity_code, ";
    $sql.= "tprocesos.id_entity as _id_entity, tprocesos.id_entity_code as _id_entity_code, ";
    $sql.= "if_entity from tusuarios, tprocesos where tusuarios.id_proceso = tprocesos.id ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $array_usuarios_entity[$row['_id']][0]= $row['if_entity'] ? $row['id_entity'] : $row['_id_entity'];
        $array_usuarios_entity[$row['_id']][1]= $row['if_entity'] ? $row['id_entity_code'] : $row['_id_entity_code'];
    }

    $sql= "select *, year(fecha_inicio_plan) as _year from teventos where toshow > 0";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    _set_104_part($result, "evento", $nums_tb);

    $sql= "select * from tauditorias";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    _set_104_part($result, "auditoria", $nums_tb);

    $sql= "select * from ttareas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    _set_104_part($result, "tarea", $nums_tb);
}

global $array_procesos;
global $array_usuarios_entity;

function _set_105_update_proceso($year, $id, $row) {
    $row['id_usuario']= _USER_SYSTEM;

    $empresarial= setNULL($row['empresarial']);
    $id_tipo_evento= setNULL($row['id_tipo_evento']);
    $id_tipo_evento_code= setNULL_str($row['id_tipo_evento_code']);
    $indice= setNULL($row['indice']);
    $indice_plus= setNULL($row['indice_plus']);

    $aprobado= setNULL_str($row['aprobado']);
    $observacion= setNULL_str($row['observacion']);
    $id_responsable_aprb= setNULL(!empty($row['aprobado']) ? $row['id_responsable'] : null);

    $sql= "update tproceso_eventos_{$year} set id_responsable= {$row['id_responsable']}, cumplimiento= {$row['cumplimiento']}, ";
    $sql.= "observacion= $observacion, empresarial= $empresarial, indice= $indice, indice_plus= $indice_plus, ";
    $sql.= "id_tipo_evento= $id_tipo_evento, id_tipo_evento_code= $id_tipo_evento_code, aprobado= $aprobado, ";
    $sql.= "id_responsable_aprb= $id_responsable_aprb where id = $id; ";

    return $sql;
}

function _set_105_test_id_responsable($obj_reg, $id, $year, $id_responsable, $row) {
    $obj_reg->SetIdUsuario($id_responsable);
    $rowcmp= $obj_reg->getEvento_reg();

    if (!$rowcmp)
        return false;

    $rowcmp['empresarial']= $row['empresarial'];
    $rowcmp['id_tipo_evento']= $row['id_tipo_evento'];
    $rowcmp['id_tipo_evento_code']= $row['id_tipo_evento_code'];
    $rowcmp['indice']= $row['indice'];
    $rowcmp['indice_plus']= $row['indice_plus'];

    $sql= _set_105_update_proceso($year, $id, $rowcmp);
    return $sql;
}

function _set_105_init() {
    global $clink;
    global $array_procesos;
    global $array_usuarios_entity;

    $array_procesos= array();

    $sql= "select * from tprocesos";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $array= array('id'=>$row['id'], 'id_code'=>$row['id_code'], 'id_responsable'=>$row['id_responsable'],
                'if_entity'=>$row['if_entity'], 'id_entity'=>$row['id_entity'], 'id_entity_code'=>$row['id_entity_code'],
                'tipo'=>$row['tipo']);
        $array_procesos[$row['id']]= $array;
    }

    $array_usuarios_entity= array();
    $sql= "select tusuarios.id as id, tprocesos.id as id_proceso, tprocesos.id_entity as id_entity ";
    $sql.= "from tusuarios, tprocesos where tusuarios.id_proceso = tprocesos.id ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $id_entity= !empty($row['id_entity']) ? $row['id_entity'] : $row['id_proceso'];
        $array_usuarios_entity[$id_entity][]= $row['id'];
    }
}

function _set_105_find_treg_evento($year, $id_entity, $row) {
    global $clink;
    global $array_usuarios_entity;

    $user_list= implode(",", $array_usuarios_entity[$id_entity]);

    $sql= "select * from treg_evento_{$year} where 1 ";
    $sql.= "and id_evento ".setNULL_equal_sql($row['id_evento']);
    $sql.= "and id_auditoria ".setNULL_equal_sql($row['id_auditoria']);
    $sql.= "and id_tarea ".setNULL_equal_sql($row['id_tarea']);
    $sql.= "and id_usuario in ($user_list) order by cronos asc limit 1";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);

    return !empty($row) ? $row['id_usuario'] : false;
}

function _set_105_register($result, $nums_tb, $year) {
    global $clink;
    global $array_procesos;

    $obj_reg= new Tregister_planning($clink);
    $obj_reg->SetYear($year);

    $obj= new Tevento($clink);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $id_evento= $row['id_evento'];
        $id_auditoria= $row['id_auditoria'];
        $id_proceso= $row['id_proceso'];
        $id_proceso_code= $row['id_proceso_code'];

        $id_responsable_prs= $array_procesos[$id_proceso]['id_responsable'];
        if (empty($array_procesos[$id_proceso]['id_entity'])) {
            $id_responsable_entity= $array_procesos[$id_proceso]['id_responsable'];
            $id_proceso_entity= $array_procesos[$id_proceso]['id'];
        } else {
            $id_responsable_entity= $array_procesos[$array_procesos[$id_proceso]['id_entity']]['id_responsable'];
            $id_proceso_entity= $array_procesos[$id_proceso]['id_entity'];
        }
        $tipo_entity= $array_procesos[$id_proceso_entity]['tipo'];

        if (!empty($id_evento)) {
            unset($obj);
            $obj= new Tevento($clink);
            $obj->Set($id_evento);
        } else {
            unset($obj);
            $obj= new Tauditoria($clink);
            $obj->Set($id_auditoria);
        }

        $id_responsable_event= $obj->GetIdResponsable();
        $year= date('Y', strtotime($obj->GetFechaInicioPlan()));

        $rowcmp['toshow']= $obj->toshow;
        $rowcmp['empresarial']= $obj->GetIfEmpresarial();
        $rowcmp['id_tipo_evento']= $obj->GetIdTipo_evento();
        $rowcmp['id_tipo_evento_code']= $obj->get_id_tipo_evento_code();

        $id_proceso_asigna= $obj->GetIdProceso();
        $id_proceso_asigna_code= $obj->get_id_proceso_code();

        if (empty($array_procesos[$id_proceso_asigna]['id_entity'])) {
            $tipo_entity_event= $array_procesos[$id_proceso_asigna]['tipo'];
        } else {
            $tipo_entity_event= $array_procesos[$array_procesos[$id_proceso_asigna]['id_entity']]['tipo'];
        }

        $rowcmp['indice']= $obj->indice;
        $rowcmp['indice_plus']= $obj->indice_plus;

        $obj_reg->SetYear($year);
        $obj_reg->SetIdEvento($row['id_evento']);
        $obj_reg->set_id_evento_code($row['id_evento_code']);
        $obj_reg->SetIdAuditoria($row['id_auditoria']);
        $obj_reg->set_id_auditoria_code($row['id_auditoria_code']);
        $obj_reg->SetIdtarea($row['id_tarea']);
        $obj_reg->set_id_tarea_code($row['id_tarea_code']);

        $found= null;
        if ($id_proceso_asigna == $id_proceso) {
            $found= _set_105_test_id_responsable($obj_reg, $row['id'], $year, $id_responsable_event, $rowcmp);
        } else {
            if ($rowcmp['empresarial'] == 6 && (($tipo_entity_event == _TIPO_OSDE || $tipo_entity_event == _TIPO_GAE) && $tipo_entity == _TIPO_EMPRESA)) {
                $rowcmp['empresarial']= 5;
                $rowcmp['id_tipo_evento']= null;
                $rowcmp['id_tipo_evento_code']= null;
                $rowcmp['indice']= 5000000;
                $rowcmp['indice_plus']= null;
            }

            $found= _set_105_test_id_responsable($obj_reg, $row['id'], $year, $id_responsable_prs, $rowcmp);
            if (!$found) {
                $found= _set_105_test_id_responsable($obj_reg, $row['id'], $year, $id_responsable_entity, $rowcmp);
            }
            if (!$found) {
                $rowcmp['id_evento']= $row['id'];
                $rowcmp['id_evento_code']= $row['id_code'];
                $rowcmp['id_tarea']= $row['id_tarea'];
                $rowcmp['id_tarea_code']= $row['id_tarea_code'];
                $rowcmp['id_auditoria']= $row['id_auditoria'];
                $rowcmp['id_auditoria_code']= $row['id_auditoria_code'];

                $id_usuario= _set_105_find_treg_evento($year, $id_proceso_entity, $rowcmp);
                if ($id_usuario)
                    $found= _set_105_test_id_responsable($obj_reg, $row['id'], $year, $id_usuario, $rowcmp);
            }
        }

        $sql.= $found ? $found : null;
        ++$i;
        ++$j;
        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_105 --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);
}

/*
 * Actualizar los campos empresarial, id_tipo_evento, indice en las tablas tproceso_eventos
 */
function set_105() {
    global $clink;

    _set_105_init();

    $year= (int)date('Y');
    for ($_year= 2017; $_year <= $year; $_year++) {
        $sql= "select * from tproceso_eventos_{$_year}";
        $result= $clink->query($sql);
        $nums_tb = $clink->num_rows($result);
        _set_105_register($result, $nums_tb, $_year);        
    }
}

/*
 * Para la ejecucion del set_106
 */
$array_responsable_eventos= array();
$array_responsable_auditorias= array();

function _set_106_update_tproceso_eventos($result_reg, $rowcmp, $year) {
    global $clink;

    $sql= null;
    $i= 0;
    while ($row= $clink->fetch_array($result_reg)) {
        ++$i;
        $id_evento= setNULL($rowcmp['id_evento']);
        $id_evento_code= setNULL_str($rowcmp['id_evento_code']);

        $id_tarea= setNULL($rowcmp['id_tarea']);
        $id_tarea_code= setNULL_str($rowcmp['id_tarea_code']);

        $id_auditoria= setNULL($rowcmp['id_auditoria']);
        $id_auditoria_code= setNULL_str($rowcmp['id_auditoria_code']);

        $toshow= setNULL($rowcmp['toshow']);
        $empresarial= setNULL($rowcmp['empresarial']);
        $id_tipo_evento= setNULL($rowcmp['id_tipo_evento']);
        $id_tipo_evento_code= setNULL_str($rowcmp['id_tipo_evento_code']);
        $indice= setNULL($rowcmp['indice']);
        $indice_plus= setNULL($rowcmp['indice_plus']);

        $id_responsable= $rowcmp['id_responsable'];
        $rechazado= setNULL_str($row['rechazado']);
        $id_responsable_aprb= !empty($row['aprobado']) ? $row['id_responsable'] : null;
        $id_responsable_aprb= setNULL($id_responsable_aprb > 0 ? $id_responsable_aprb : null);

        $aprobado= setNULL_str($row['aprobado']);

        $observacion= setNULL_str($row['observacion']);
        $cumplimiento= setNULL($row['cumplimiento']);
        $id_usuario= $row['id_responsable'] > 0 ? $row['id_responsable'] : _USER_SYSTEM;

        $cronos= setNULL_str($row['cronos']);
        $location= setNULL_str($row['situs']);

        $sql.= "insert into tproceso_eventos_$year (id_evento, id_evento_code, id_auditoria, id_auditoria_code, ";
        $sql.= "id_tarea, id_tarea_code, id_proceso, id_proceso_code, toshow, empresarial, id_tipo_evento, id_tipo_evento_code, ";
        $sql.= "indice, indice_plus, aprobado, id_responsable, cumplimiento, observacion, id_responsable_aprb, rechazado, ";
        $sql.= "id_usuario, cronos, situs) values ($id_evento, $id_evento_code, $id_auditoria, $id_auditoria_code, ";
        $sql.= "$id_tarea, $id_tarea_code, {$rowcmp['id_proceso']}, '{$rowcmp['id_proceso_code']}', $toshow, $empresarial, ";
        $sql.= "$id_tipo_evento, $id_tipo_evento_code, $indice, $indice_plus, $aprobado, $id_responsable, $cumplimiento, ";
        $sql.= "$observacion, $id_responsable_aprb, $rechazado, $id_usuario, $cronos, $location); ";
    }

    if ($sql)
        $clink->multi_query($sql);

    return $i;
}

function _set_106_read_tproceso_eventos($year) {
    global $clink;
    global $array_responsable_eventos;
    global $array_responsable_auditorias;

    $sql= "select * from tproceso_eventos_$year ";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $array_ids= array();
    while ($row= $clink->fetch_array($result)) {
        $id_proceso= $row['id_proceso'];
        $id_evento= !empty($row['id_evento']) ? $row['id_evento'] : 0;
        $id_auditoria= !empty($row['id_auditoria']) ? $row['id_auditoria'] : 0;
        $id_tarea= !empty($row['id_tarea']) ? $row['id_tarea'] : 0;

        if (empty($id_evento) && empty($id_auditoria))
            continue;
        if (!empty($id_evento))
            $id_responsable= $array_responsable_eventos[$id_evento];
        else
            $id_responsable= $array_responsable_auditorias[$id_auditoria];

        ++$i;
        if (empty($id_responsable) || $id_responsable == -1)
            continue;
        if ($array_ids[$id_proceso][$id_evento][$id_auditoria][$id_tarea][$id_responsable])
            continue;
        $array_ids[$id_proceso][$id_evento][$id_auditoria][$id_tarea][$id_responsable]= 1;

        $sql= "select * from treg_evento_$year where id_usuario = $id_responsable ";
        $sql.= "and id_evento ". setNULL_empty_equal_sql($id_evento);
        $sql.= "and id_auditoria ".setNULL_empty_equal_sql($id_auditoria);
        $sql.= "and id_tarea ".setNULL_empty_equal_sql($id_tarea);
        $result_reg= $clink->query($sql);

        _set_106_update_tproceso_eventos($result_reg, $row, $year);
        ++$j;
        if ($j >= 500) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_106 --->  ($_r%)....", $r);
    }   }

    bar_progressCSS(1, "set_106 --->  (100%)....", 1);
}

function _set_106_init($year) {
    global $clink;
    global $array_responsable_eventos;
    global $array_responsable_auditorias;

    $sql= "select * from teventos where year(fecha_inicio_plan) = $year";
    $result= $clink->query($sql);

    $array_responsable_eventos= array();
    while ($row= $clink->fetch_array($result)) {
        $array_responsable_eventos[$row['id']]= $row['id_responsable'];
    }

    $sql= "select * from tauditorias where year(fecha_inicio_plan) = $year";
    $result= $clink->query($sql);

    $array_responsable_auditorias= array();
    while ($row= $clink->fetch_array($result)) {
        $array_responsable_auditorias[$row['id']]= $row['id_responsable'];
    }
}

/*
 * Actualizar los campos aprobados y rechazados en las tablas tproceso_eventos
 */
function set_106() {

    $year= (int)date('Y');
    for ($_year= 2017; $_year <= $year; $_year++) {    
        _set_106_init($_year);
        _set_106_read_tproceso_eventos($_year);
    }
}

/*
 * incorporar los indicadores a la tabla tproceso_indicadores utilizando el campo
 * id_proceso de la tabla tindicadores
 */

function _set_107_init() {
    global $clink;

    $sql= "select distinct tindicadores.id as _id, tindicadores.id_proceso as id_proceso_ref, ";
    $sql.= "tindicadores.id_proceso_code as id_proceso_ref_code, tprocesos.id as _id_proceso, ";
    $sql.= "tprocesos.id_code as _id_proceso_code, if_entity, id_entity, id_entity_code from tindicadores, tprocesos ";
    $sql.= "where tindicadores.id_proceso = tprocesos.id and (if_entity = false and id_entity is not null) ";
    $result= $clink->query($sql);

    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $sql.= "update tindicadores set id_proceso = {$row['id_entity']}, id_proceso_code = '{$row['id_entity_code']}' ";
        $sql.= "where id = {$row['_id']}; ";
    }

    if ($sql)
        $clink->multi_query($sql);
}

function set_107() {
    global $clink;

    _set_107_init();

    $sql= "select * from tindicadores";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        for ($year= $row['inicio']; $year <= $row['fin']; $year++) {
            $sql.= "insert into tproceso_indicadores (id_indicador, id_indicador_code, id_proceso, id_proceso_code, cronos, ";
            $sql.= "situs) values ({$row['id']}, '{$row['id_code']}', {$row['id_proceso']}, '{$row['id_proceso_code']}', ";
            $sql.= "'{$row['cronos']}', '{$row['situs']}'); ";
        }

        $clink->multi_query($sql);

        $r= (float)$i / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_107 --->  ($_r%)....", $r);
    }

     bar_progressCSS(1, "set_107 --->  (100%)....", 1);
}

/*
 * Modificar los campos id_proceso e id_proceso_code de la tabla teventos, tauditorias y ttareas
 * segun la entidad a la que pertenece el usuario que creo el registro
 */
global $array_usuarios_entity;

function _set_108_init() {
    global $clink;
    global $array_procesos;
    global $array_usuarios_entity;

    $array_usuarios_entity= array();
    $sql= "select tusuarios.id as id, tprocesos.id as id_proceso, tprocesos.id_code as id_proceso_code, ";
    $sql.= "tprocesos.id_entity as id_entity, tprocesos.id_entity_code as id_entity_code ";
    $sql.= "from tusuarios, tprocesos where tusuarios.id_proceso = tprocesos.id ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $id_entity= !empty($row['id_entity']) ? $row['id_entity'] : $row['id_proceso'];
        $id_entity_code= !empty($row['id_entity']) ? $row['id_entity_code'] : $row['id_proceso_code'];
        $array_usuarios_entity[$row['id']]= array($id_entity, $id_entity_code);
    }
}

function _set_108_exect_table($table, $result, $nums_tb) {
    global $clink;
    global $array_usuarios_entity;

    $i= 0;
    $j= 0;
    $k= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if (empty($row['id_proceso']))
            continue;
        $id_entity= $array_usuarios_entity[$row['id_usuario']][0];
        $id_entity_code= $array_usuarios_entity[$row['id_usuario']][1];

        if ($row['id_proceso'] != $id_entity) {
            ++$k;
            ++$j;
            $sql.= "update $table set id_proceso = $id_entity, id_proceso_code= '$id_entity_code' where id = {$row['id']}; ";
        }

        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_108 $table:(modificados $k de $nums_tb) --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_108 $table:(modificados $k de $nums_tb) --->  (100%)....", 1);
}

function set_108() {
    global $clink;

    _set_108_init();

    $sql= "select * from teventos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    _set_108_exect_table("teventos", $result, $nums_tb);


    $sql= "select * from tauditorias";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    _set_108_exect_table("tauditorias", $result, $nums_tb);

    $sql= "select * from ttareas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    _set_108_exect_table("ttareas", $result, $nums_tb);
}

/*
 * Actualizar los campos indice e indice_plus de la tabla tproceso_eventos
 */
function _set_109_init_array_tipo_eventos(&$array_tipo_eventos) {
    global $clink;

    $sql= "select * from ttipo_eventos";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $array_tipo_eventos[$row['id']]= $row['indice'];
    }
    bar_progressCSS(1, "set_109 -->_set_109_init_array_tipo_eventos --->  (100%)....", 1);
}

function _set_109_array_eventos($year, &$array_eventos) {
    global $clink;

    $sql= "select id, id_auditoria, empresarial, id_tipo_evento, numero_plus from teventos ";
    $sql.= "where year(fecha_inicio_plan)= $year and numero_plus is not null";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $array_eventos[$row['id']]= array('numero_plus'=>$row['numero_plus'], 'id_auditoria'=>$row['id_auditoria']);
    }

    bar_progressCSS(1, "set_109 -->_set_109_array_eventos --->  (100%)....", 1);
}

function _set_109_get_indice($empresarial, $id_tipo_evento, $array_tipo_eventos) {
    $indice= null;
    if (empty($id_tipo_evento))
        $indice= !empty($empresarial) ? $empresarial*pow(10,6) : null;
    else
        $indice= $array_tipo_eventos[$id_tipo_evento];

    return $indice;
}

function _set_109_execute_indice($year, $array_tipo_eventos) {
    global $clink;

    $sql= "select distinct empresarial, id_tipo_evento from tproceso_eventos_$year";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if (empty($row['id_tipo_evento']))
            continue;

        $indice= setNULL(_set_109_get_indice($row['empresarial'], $row['id_tipo_evento'], $array_tipo_eventos));
        $empresarial= setNULL_empty_equal_sql($row['empresarial']);
        $id_tipo_evento= setNULL_empty_equal_sql($row['id_tipo_evento']);

        $sql= "update tproceso_eventos_$year set indice= $indice ";
        $sql.= "where indice is null and (empresarial $empresarial and id_tipo_evento $id_tipo_evento); ";
        $clink->query($sql);

        ++$j;
        if ($j >= 500) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_109 --> _set_109_execute_indice($year) --->  ($_r%)....", $r);
    }   }

    bar_progressCSS(1, "set_109 --> _set_109_execute_indice($year) --->  (100%)....", 1);
}

function _set_109_execute_indice_plus($year, &$array_eventos) {
    global $clink;

    $i= 0;
    $j= 0;
    $sql= null;
    foreach ($array_eventos as $id_evento => $evento) {
        ++$i;
        $id_auditoria= $evento['id_auditoria'];
        $numero_plus= $evento['numero_plus'];
        $indice_plus= !empty($numero_plus) ? index_to_number($numero_plus) : null;

        $sql.= "update tproceso_eventos_$year set indice_plus= $indice_plus where id_evento = $id_evento ";
        if (!empty($id_auditoria))
            $sql.= "or id_auditoria = $id_auditoria";
        $sql.= "; ";

        ++$j;
        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_109 -->_set_109_execute_indice_plus($year) --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_109 -->_set_109_execute_indice_plus($year) --->  (100%)....", 1);
}

function set_109() {
    $array_tipo_eventos= array();
    _set_109_init_array_tipo_eventos($array_tipo_eventos);

    $year= (int)date('Y');
    for ($_year= 2020; $_year <= $year; $_year++) {   
        $array_eventos= array();
        _set_109_array_eventos($_year, $array_eventos);
        _set_109_execute_indice($_year, $array_tipo_eventos);
        _set_109_execute_indice_plus($_year, $array_eventos);
    }
}

/*
 * Crear la tabla tproceso_eventos_2021 en las que no se crearon
 */

function _set_110_sql($row, $year) {
   $cumplimiento= _NO_INICIADO;
   $empresarial= setNULL($row['empresarial']);
   $toshow= setNULL($row['toshow']);
   $id_tipo_evento= setNULL($row['id_tipo_evento']);
   $id_tipo_evento_code= setNULL_str($row['id_tipo_evento_code']);
   $indice= setNULL($row['indice']);
   $indice_plus= setNULL($row['indice_plus']);

   $id_auditoria= setNULL($row['id_auditoria']);
   $id_auditoria_code= setNULL_str($row['id_auditoria_code']);

   $id_tarea= setNULL($row['id_tarea']);
   $id_tarea_code= setNULL_str($row['id_tarea_code']);

   $sql= "insert into tproceso_eventos_$year (id_evento, id_evento_code, id_proceso, id_proceso_code, cumplimiento, ";
   $sql.= "empresarial, toshow, id_tipo_evento, id_tipo_evento_code, id_auditoria, id_auditoria_code, id_tarea, ";
   $sql.= "id_tarea_code, indice, indice_plus, id_usuario, cronos, situs) values ({$row['id']}, '{$row['id_code']}', ";
   $sql.= "{$row['id_proceso']}, '{$row['id_proceso_code']}', $cumplimiento, $empresarial, $toshow, $id_tipo_evento,";
   $sql.= " $id_tipo_evento_code, $id_auditoria, $id_auditoria_code, $id_tarea, $id_tarea_code, $indice, $indice_plus, ";
   $sql.= "{$row['id_usuario']}, '{$row['cronos']}', '{$row['situs']}'); ";
   return $sql;
}

function set_110() {
    global $clink;
    $year= date('Y');

    if ($clink->if_table_exist("tproceso_eventos_$year"))
        return null;

    $year_init= (int)$year - 1;
    $sql= "show create table tproceso_eventos_$year_init";
    $result = $clink->query($sql);
    $row= $clink->fetch_array($result);
    $sql_table= $row[1];

    $sql_table= str_replace("_$year_init", "_$year", $sql_table);
    $clink->query($sql_table);

    $sql= "select * from teventos where YEAR(fecha_inicio_plan) = $year ";
    $result = $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $sql.= _set_110_sql($row, $year);

        ++$i;
        ++$j;
        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_110 --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_110 --->  (100%)....", 1);
}


/*
 * Escribir el valor de id_tipo_evento y id_tipo_evento_code que le correspode a la entidad creade desde el local_proceso_id
 */
function _set_111_init_matrix_tipos(&$matrix_tipo_eventos) {
    global $clink;
    global $array_procesos;

    $id_local_proceso= $_SESSION['local_proceso_id'];
    $local_tipo= $_SESSION['local_proceso_tipo'];
    $matrix_tipo_eventos= array();

    $sql= "select t1.id as id_tipo_evento, t1.empresarial as empresarial, t2.id as _id_tipo_evento, ";
    $sql.= "t2.id_code as _id_tipo_evento_code, t2.empresarial as _empresarial, t2.id_proceso as _id_proceso ";
    $sql.= "from ttipo_eventos as t1, ttipo_eventos as t2 ";
    $sql.= "where (lower(t1.nombre) = lower(t2.nombre) and t1.empresarial = t2.empresarial) ";
    $sql.= "and t1.id_proceso = $id_local_proceso and t2.id_proceso <> $id_local_proceso";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $id_proceso= $row['_id_proceso'];
        $tipo= $array_procesos[$id_proceso]['tipo'];

        $fix_empresarial= false;
        if ($local_tipo == _TIPO_CECM && ($tipo == _TIPO_EMPRESA || $tipo == _TIPO_GAE || $tipo == _TIPO_OACE || $tipo == _TIPO_OSDE))
            $fix_empresarial= true;
        if ($local_tipo == _TIPO_OACE && ($tipo == _TIPO_EMPRESA || $tipo == _TIPO_GAE || $tipo == _TIPO_OSDE))
            $fix_empresarial= true;
        if (($local_tipo == _TIPO_GAE || $local_tipo == _TIPO_OSDE) && $tipo == _TIPO_EMPRESA)
            $fix_empresarial= true;

        $id_tipo_evento= $row['_id_tipo_evento'];
        $id_tipo_evento_code= $row['_id_tipo_evento_code'];

        if ($fix_empresarial && $row['empresarial'] == 6) {
            $id_tipo_evento= null;
            $id_tipo_evento_code= null;
        }

        $matrix_tipo_eventos[$row['id_tipo_evento']][$row['_id_proceso']]= array('id_tipo_evento'=>$id_tipo_evento,
                                                                                'id_tipo_evento_code'=>$id_tipo_evento_code);
    }

    bar_progressCSS(1, "set_111--->_set_111_init_matrix_tipos --->  100%....", 1);
}

function _set_111_init_array_tipo_by_proceso(&$array_tipo_evento_procesos) {
    global $clink;

    $sql= "select * from ttipo_eventos";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $array_tipo_evento_procesos[$row['id_proceso']][]= $row['id'];
    }
    bar_progressCSS(1, "set_111--->_set_111_init_array_tipo_by_proceso --->  100%....", 1);
}

function _set_111_init($year, &$array_proceso_eventos) {
    global $clink;

    $sql= "select distinct id_evento, id_auditoria, empresarial, id_tipo_evento, id_proceso from tproceso_eventos_$year ";
    $result = $clink->query($sql);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if (empty($row['id_evento']) && empty($row['id_auditoria']))
            continue;

        $array_proceso_eventos[$row['id_proceso']]= $row['id_proceso'];
    }
    bar_progressCSS(1, "set_111--->_set_111_init($year) --->  100%....", 1);
    return $i;
}

function _set_111_exect($year, $nums_tb, $array_proceso_eventos, $matrix_tipo_eventos, $array_tipo_evento_procesos) {
    global $clink;
    global $array_procesos;

    $i= 0;
    reset($array_proceso_eventos);
    foreach ($array_proceso_eventos as $id_proceso) {
        $id_entity= !empty($array_procesos[$id_proceso]['id_entity']) ? $array_procesos[$id_proceso]['id_entity'] : $id_proceso;

        if ($id_proceso == $_SESSION['local_proceso_id'] || $id_entity == $_SESSION['local_proceso_id'])
            continue;
        if ($array_procesos[$id_proceso]['tipo'] < $_SESSION['local_proceso_tipo'])
            continue;

        reset($array_tipo_evento_procesos[$_SESSION['local_proceso_id']]);
        foreach ($array_tipo_evento_procesos[$_SESSION['local_proceso_id']] as $_id_tipo_evento) {
            $id_tipo_evento= setNULL($matrix_tipo_eventos[$_id_tipo_evento][$id_proceso]['id_tipo_evento']);
            $id_tipo_evento_code= setNULL_str($matrix_tipo_eventos[$_id_tipo_evento][$id_proceso]['id_tipo_evento_code']);

            $sql= "update tproceso_eventos_$year set id_tipo_evento = $id_tipo_evento, id_tipo_evento_code= $id_tipo_evento_code ";
            $sql.= "where id_proceso = $id_proceso and id_tipo_evento = $_id_tipo_evento; ";
            $result = $clink->query($sql);
            $cant= $clink->num_rows($result);

            $i+= $cant;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_111($year) --->  ($_r%)....", $r);
        }

    }
}

function set_111() {
    global $clink;

    _set_105_init();

    $matrix_tipo_eventos= array();
    _set_111_init_matrix_tipos($matrix_tipo_eventos);

    $array_tipo_evento_procesos= array();
    _set_111_init_array_tipo_by_proceso($array_tipo_evento_procesos);

    $year= (int)date('Y');
    for ($_year= 2020; $_year <= $year; $_year++) {       
        $array_proceso_eventos= array();
        $nums_tb= _set_111_init($_year, $array_proceso_eventos);
        _set_111_exect($_year, $nums_tb, $array_proceso_eventos, $matrix_tipo_eventos, $array_tipo_evento_procesos);
    }
}

/*
 * Eliminar el exceso de registros redundantes que existe en tproceso_eventos
 */
require_once _ROOT_DIRIGER_DIR."tools/repare/repare_tproceso_eventos.inc";

function set_112() {
    $year= (int)date('Y');
    for ($_year= 2020; $_year <= $year; $_year++) {   
        $array_eventos= array();
        $array_id_eventos= array();
        $array_id_procesos= array();

        $nums_tb= _set_init_112($_year, $array_eventos, $array_id_eventos, $array_id_procesos);
        _set_112_execute($_year, $nums_tb, $array_eventos, $array_id_eventos, $array_id_procesos);
    }
}

/*
 * Asignar cumplimiento a las tareas de los Planes anuales y mensuales a las tareas que aun no lo tienen
 */
function _set_113_init($year, &$array_eventos, &$array_id_eventos, &$array_id_procesos) {
    global $clink;

    bar_progressCSS(1, "set_113 -->_set_113_init($year) --->  (10%)....", 0.1);
    $fecha_inicio_plan= $year == date('Y') ? add_date(date('Y-m-d'), -7) : "$year-12-31 23:59:00";

    $sql= "select tproceso_eventos_$year.id as _id, teventos.id as _id_evento, tproceso_eventos_$year.id_proceso as _id_proceso, ";
    $sql.= "tproceso_eventos_$year.id_responsable as _id_responsable, cumplimiento, tproceso_eventos_$year.cronos as _cronos ";
    $sql.= "from teventos, tproceso_eventos_$year where teventos.id = tproceso_eventos_$year.id_evento ";
    $sql.= "and (periodicidad = 0 or periodicidad is null) ";
    $sql.= "and fecha_inicio_plan <= '$fecha_inicio_plan' order by _cronos desc";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        if ($row['cumplimiento'] && $row['cumplimiento'] != _NO_INICIADO)
            continue;
        if (!empty($array_eventos[$row['_id_evento']][$row['_id_proceso']]))
            continue;
        ++$i;
        $array_id_eventos[$row['_id_evento']]= $row['_id_evento'];
        $array_id_procesos[$row['_id_proceso']]= $row['_id_proceso'];
        $array_eventos[$row['_id_evento']][$row['_id_proceso']]= array('id'=>$row['_id'], '_id_evento'=>$row['_id_evento'],
                                                    'id_responsable'=>$row['_id_responsable'],
                                                    'id_proceso'=>$row['_id_proceso'], 'cumplimiento'=>$row['cumplimiento']);
    }

    bar_progressCSS(1, "set_113 -->_set_113_init($year) --->  (100%)....", 1);
    return $i;
}

function _set_113_execute($year, $nums_tb, $array_eventos, $array_id_eventos, $array_id_procesos) {
    global $clink;

    $obj_reg= new Tregister_planning($clink);
    $obj_reg->SetYear($year);

    $sql= null;
    $i= 0;
    $j= 0;
    $k= 0;
    foreach ($array_id_eventos as $id_evento) {
        reset($array_id_procesos);
        foreach ($array_id_procesos as $id_proceso) {
            if (empty($array_eventos[$id_evento][$id_proceso]))
                continue;
            $id= $array_eventos[$id_evento][$id_proceso]['id'];
            $id_responsable= $array_eventos[$id_evento][$id_proceso]['id_responsable'];

            $obj_reg->SetIdEvento($id_evento);
            $obj_reg->SetIdUsuario($id_responsable);
            $row= $obj_reg->getEvento_reg();

            $cumplimiento= _INCUMPLIDO;
            $id_usuario= _USER_SYSTEM;

            if (!empty($row) && $row['cumplimiento'] != _NO_INICIADO) {
                $cumplimiento= $row['cumplimiento'];
                $id_usuario= !empty($row['id_user_reg']) ? $row['id_user_reg'] : $row['_id_responsable'];
            }

            ++$i;
            if (!empty($id_usuario) && $id_usuario != -1) {
                $sql.= "update tproceso_eventos_$year set cumplimiento = $cumplimiento, id_usuario = $id_usuario where id = $id; ";
            } else {
                ++$k;
            }

            ++$j;
            if ($j >= 5000) {
                $clink->multi_query($sql);

                $sql= null;
                $j= 0;
                $r= (float)$i / $nums_tb;
                $_r= $r*100; $_r= number_format($_r,3);
                bar_progressCSS(1, "set_113 -->_set_113_execute($year) ---> modificados:$i (problematicos: $k)  ($_r%)....", $r);
            }
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_113 --->_set_113_execute($year)  (100%)....", 1);
}

function set_113() {
    global $clink;

    $year= (int)date('Y');
    for ($_year= 2020; $_year <= $year; $_year++) {  
        $array_eventos= array();
        $array_id_procesos= array();
        $array_id_eventos= array();

        $nums_tb= _set_113_init($_year, $array_eventos, $array_id_eventos, $array_id_procesos);
        _set_113_execute($_year, $nums_tb, $array_eventos, $array_id_eventos, $array_id_procesos);
    }
}

/*
 * Purgar la tabla tref_documentos de los elementos que estan duplicados 
 */
function set_114_execute($nums_tb, $array_rows) {
    global $clink;
    
    $i= 0;
    $j= 0;
    $sql= null;
    foreach ($array_rows as $row) {
        $sql.= "delete from tref_documentos where id != {$row['id']} ";
        $id= setNULL_equal_sql($row['id_evento']);
        $sql.= "and id_evento $id ";
        $id= setNULL_equal_sql($row['id_auditoria']);
        $sql.= "and id_auditoria $id ";
        $id= setNULL_equal_sql($row['id_proyecto']);
        $sql.= "and id_proyecto $id ";
        $id= setNULL_equal_sql($row['id_riesgo']);
        $sql.= "and id_riesgo $id ";
        $id= setNULL_equal_sql($row['id_nota']);
        $sql.= "and id_nota $id ";
        $id= setNULL_equal_sql($row['id_requisito']);
        $sql.= "and id_requisito $id ";                                
        $id= setNULL_equal_sql($row['id_indicador']);
        $sql.= "and id_indicador $id "; 
        $sql.= "; ";
         
        ++$i;
        ++$j;
        if ($j >= 1000) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,3);
            bar_progressCSS(1, "set_114 -->_set_114_execute  ($_r%)....", $r);
        }
    }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_114_execute  ....", 1); 
}    

function set_114() {
    global $clink;

    $sql= "select * from tref_documentos order by cronos desc";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    
    $i= 0;
    $j= 0;
    $array_ids= array();
    $array_rows= null;
    while ($row= $clink->fetch_array($result)) {
        $id_evento= setZero($row['id_evento']);
        $id_auditoria= setZero($row['id_auditoria']);
        $id_proyecto= setZero($row['id_proyecto']);
        $id_riesgo= setZero($row['id_riesgo']);
        $id_nota= setZero($row['id_nota']);
        $id_requisito= setZero($row['id_requisito']);
        $id_indicador= setZero($row['id_indicador']);

        if($array_ids[$id_evento][$id_auditoria][$id_proyecto][$id_riesgo][$id_nota][$id_requisito][$id_indicador])
            continue;
        $array_rows[]= $row;
        $array_ids[$id_evento][$id_auditoria][$id_proyecto][$id_riesgo][$id_nota][$id_requisito][$id_indicador]= 1;

        ++$i;
        ++$j;
        if ($j >= 1000) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,3);
            bar_progressCSS(1, "set_114 ...  ($_r%)....", $r);
        }        
    } 

    $nums_tb= $i;
    set_114_execute($nums_tb,$array_rows);

    $sql= "CREATE UNIQUE INDEX tref_documentos_index ON tref_documentos (id_documento_code, id_evento_code, id_auditoria_code, ";
    $sql.= "id_proyecto_code, id_riesgo_code, id_nota_code, id_requisito_code, id_indicador_code)"; 
    $clink->query($sql);
    bar_progressCSS(1, "set_114 ...  (100%)....", 1);
}    

/*
 * Incorporar a la tabla tproceso_indicadores, los indicadores de las entidades que no fueron incorporados
 */
function set_115_execute($row) {
    global $clink;
    global $cronos;
    global $location;

    for($year= $row['inicio']; $year <= $row['fin']; $year++) {
        $id_proceso= setNULL($row['id_proceso']);
        $id_proceso_code= setNULL_str($row['id_proceso_code']);
        $id_indicador= setNULL($row['id']);
        $id_indicador_code= setNULL_str($row['id_code']);

        $sql= "insert into tproceso_indicadores (year, id_indicador, id_indicador_code, id_proceso, id_proceso_code, cronos, situs) ";
        $sql.= "values ($year, $id_indicador, $id_indicador_code, $id_proceso, $id_proceso_code, '$cronos', '$location')";
        $clink->query($sql);
    }
}

function set_115() {
    global $clink;

    $sql= "select * from tindicadores";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        set_115_execute($row);

        $r= (float)++$i / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,3);
        bar_progressCSS(1, "set_115 ...  ($_r%)....", $r);        
    }

    bar_progressCSS(1, "set_115 ...  (100%)....", 1);
}    

/*
* Hacer que los id_tipo_reunion se correspondan con la entidad donde pertenece la actividad
*/
global $array_procesos_entities;

function _set_array_proceso_entities() {
    global $clink;
    global $array_procesos_entities;

    $sql= "select * from tprocesos where if_entity = true";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $array_procesos_entities[$row['id']]= array('id'=>$row['id'], 'id_code'=>$row['id_code']);
    }
}

function _set_116_array_tipo_eventos(&$array_tipo_reuniones) {
    global $clink;

    $sql= "select distinct ttipo_reuniones.id as _id_tipo_reunion, ttipo_reuniones.id_code as _id_tipo_reunion_code, if_entity, ";
    $sql.= "tprocesos.id as _id_proceso, tprocesos.id_code as _id_proceso_code, id_entity, id_entity_code ";
    $sql.= "from ttipo_reuniones, tprocesos where ttipo_reuniones.id_proceso = tprocesos.id ";
    $result= $clink->query($sql);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $array= array('id'=>$row['_id_tipo_reunion'], 'id_code'=>$row['_id_tipo_reunion_code'], 'if_entity'=>$row['if_entity'], 
                    'id_proceso'=>$row['_id_proceso'], 'id_proceso_code'=>$row['_id_proceso_code'],
                    'id_entity'=>$row['id_entity'], 'id_entity_code'=>$row['id_entity_code']);
        $array_tipo_reuniones[$row['_id_tipo_reunion']]= $array;
    }
}

function _set_116_update_null($id_evento, $id_proceso) {
    global $clink;

    $sql= "select * from ttipo_reuniones where nombre = 'Otras' and id_proceso = $id_proceso";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result); 

    $sql= "update teventos set id_tipo_reunion= {$row['id']}, id_tipo_reunion_code= '{$row['id_code']}' ";
    $sql.= "where id = $id_evento";
    $result= $clink->query($sql);
} 

function _set_116_match($id_tipo_reunion, $id_proceso, $id_proceso_code) {
    global $clink;

    $sql= "select * from ttipo_reuniones where id = $id_tipo_reunion"; 
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);

    $sql= "select * from ttipo_reuniones where nombre = '{$row['nombre']}' and id_proceso = $id_proceso"; 
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);
    $nums_tb = $clink->num_rows($result);
    
    if ($row && $nums_tb > 0) {
        return array('id'=>$row['id'], 'id_code'=>$row['id_code']);
    }

    $obj_tipo= new Ttipo_reunion($clink);
    $obj_tipo->SetIdTipo_reunion($id_tipo_reunion);
    $obj_tipo->Set();
    $obj_tipo->SetIdProceso($id_proceso);
    $obj_tipo->set_id_proceso_code($id_proceso_code);
    $obj_tipo->add();

    return array('id'=>$obj_tipo->GetId(), 'id_code'=>$obj_tipo->get_id_code());
}

function set_116() {
    global $clink;
    global $cronos;
    bar_progressCSS(1, "set_116 --->  (0%)....", 0);

    $array_tipo_reuniones= array();
    _set_116_array_tipo_eventos($array_tipo_reuniones);

    bar_progressCSS(1, "set_116 creado array inicial --->  (10%)....", 0.1);

    $sql= "select id, nombre, fecha_inicio_plan, id_proceso, id_proceso_code, id_tipo_reunion, id_tipo_reunion_code ";
    $sql.= "from teventos where id_tipo_reunion is not null;";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);
    
    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;   
        if (empty($array_tipo_reuniones[$row['id_tipo_reunion']])) {
            _set_116_update_null($row['id'], $row['id_proceso']);
            continue;
        }    
        if ($row['id_proceso'] == $array_tipo_reuniones[$row['id_tipo_reunion']]['id_proceso'])
            continue;

        $array= _set_116_match($row['id_tipo_reunion'], $row['id_proceso'], $row['id_proceso_code']);

        $sql.= "update teventos set id_tipo_reunion= {$array['id']}, id_tipo_reunion_code= '{$array['id_code']}', ";
        $sql.= "cronos= '$cronos' where id = {$row['id']}; ";

        ++$j;
        if ($j >= 500) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_116 --->  ($_r%)....", $r);
    }   }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_116 --->  (100%)....", 1);    
}

/*
* Mantener la correspondencia de los usuarios entre las tablas treg_evento y tusuario_eventos
*/
function _set_117_usuarios_reg($id_evento, $year) {
    global $clink;

    $sql= "select * from treg_evento_$year where id_evento = $id_evento order by cronos desc, id desc";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result); 
    $array_reg= array();

    $i= 0;
    $array_id_usuarios= array();
    while ($row= $clink->fetch_array($result)) {
        if (array_key_exists($row['id_usuario'], $array_id_usuarios))
            continue;
        $array_id_usuarios[$row['id_usuario']]= $row['id_usuario'];     
        ++$i;
        $array_reg[]= $row;
    }
    return $array_reg; 
}

function _set_117_delete($year, $reg) {
    $sql= "delete from treg_evento_$year where id_usuario = {$reg['id_usuario']} ";
    if (!empty($reg['id_evento']))
        $sql.= "and id_evento = {$reg['id_evento']} ";
    if (!empty($reg['id_auditoria']))
        $sql.= "and id_auditoria = {$reg['id_auditoria']} ";
    if (!empty($reg['id_tarea']))
        $sql.= "and id_tarea = {$reg['id_tarea']} ";   
    $sql.= "; "; 

    return $sql;
}

function _set_117_set_reg_evento($row, $array_reg, $array_usuarios, $year) {
    global $clink;
    global $location;

    $i= 0;
    $j= 0;
    $n_add= 0;
    $n_delete= 0;
    $sql= null;
    foreach ($array_reg as $reg) {
        ++$i;
        if ($reg['id_usuario'] == _USER_SYSTEM)
            continue; 

        if ($reg['id_usuario'] != $row['id_responsable'] && !array_key_exists($reg['id_usuario'], $array_usuarios)) {
            if (!empty($reg['toshow'])) {
                if (empty($reg['user_check'])) { 
                    ++$n_add;
                    $id_evento= setNULL($reg['id_evento']);
                    $id_evento_code= setNULL_str($reg['id_evento_code']);

                    $id_auditoria= setNULL($reg['id_auditoria']);
                    $id_auditoria_code= setNULL_str($reg['id_auditoria_code']);

                    $id_tarea= setNULL($reg['id_tarea']);
                    $id_tarea_code= setNULL_str($reg['id_tarea_code']);

                    $aprobado= setNULL_str($reg['aprobado']);
                    $_cronos= setNULL_str($reg['cronos']);

                    $sql.= "insert into tusuario_eventos_$year (id_evento, id_evento_code, id_auditoria, id_auditoria_code, ";
                    $sql.= "id_tarea, id_tarea_code, id_usuario, aprobado, cronos, situs) value ($id_evento, $id_evento_code, ";
                    $sql.= "$id_auditoria, $id_auditoria_code, $id_tarea, $id_tarea_code, {$reg['id_usuario']}, $aprobado, ";
                    $sql.= "$_cronos, '$location'); ";
                }  
                else {
                    ++$n_delete;
                    $sql.= _set_117_delete($year, $reg);
                }                
            } 
            else {
                ++$n_delete;
                $sql.= _set_117_delete($year, $reg);
            }  
        }

        ++$j;
        if ($j >= 500 && !is_null($sql)) {
            $clink->multi_query($sql);
            $sql= null;
            $j= 0;
        }    
    }

    if ($sql)
        $clink->multi_query($sql); 
        
    return array($n_add, $n_delete);
}

function set_117() {
    global $clink;
    global $cronos;
    bar_progressCSS(1, "set_117 --->  (0%)....", 0);

    $year= 2021;
    $array_reg= array();
    $array_usuarios= array();

    $sql= "select * from teventos where year(fecha_inicio_plan) = $year";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);    

    $i= 0;
    $j= 0;
    $n_add= 0; 
    $n_delete= 0;   
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $array_reg= _set_117_usuarios_reg($row['id'], $year);

        if (isset($obj_evento)) unset($obj_event);
        $obj_evento= new Tevento($clink);
        $obj_evento->SetYear($year);
        $obj_evento->SetIdEvento($row['id']);
        $array_usuarios= $obj_evento->get_usuarios_array_from_tusuario_eventos();

        $array= _set_117_set_reg_evento($row, $array_reg, $array_usuarios, $year);
        $n_add+= $array[0];
        $n_delete+= $array[1];

        ++$j;
        if ($j >= 500) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_117 ---> adicionados=$n_add eliminados=$n_delete ($_r%)....", $r);        
    }   }

    bar_progressCSS(1, "set_117 ---> adicionados=$n_add eliminados=$n_delete  (100%)....", 1); 
}

/*
* Crear tableros para las entidades. Por defecto se crea el tablero INTEGRAL
*/
function set_118() {
    global $clink;

    bar_progressCSS(1, "set_118 --->  (0%)....", 0);

    $sql= "select * from tprocesos where if_entity = true";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result); 

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if ($row['id'] == $_SESSION['local_proceso_id'])
            continue;
        if (!$row['if_entity'])
            continue;
        $obj= new Ttablero($clink);
        $obj->set_entity($_SESSION['local_proceso_id'], $row['id'], $row['id_code']);         
    }  
    bar_progressCSS(1, "set_118 --->  (100%)....", 1);      
}    

/*
* Agregar al campo url del tdocumentos el id del Usuario que cargo el documento
* Podran existir ficheros con el mismo nombre, siempre que sean cargados por usuarios diferentes
*/
function set_119() {
    global $clink;

    $sql= "select * from tdocumentos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);    

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;

        $exect= null;
        if (file_exists(_UPLOAD_DIRIGER_DIR.$row['url']))
            $exect= rename(_UPLOAD_DIRIGER_DIR.$row['url'], _UPLOAD_DIRIGER_DIR.$row['url'].".{$row['id_usuario']}");

        if ($exect) {
            $sql.= "update tdocumentos set url= '{$row['url']}.{$row['id_usuario']}' ";
            $sql.= "where id = {$row['id']}; "; 
        }

        ++$j;
        if ($j >= 10) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_119 --->  ($_r%)....", $r);
    }   }  
    
    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_119 --->  (100%)....", 1);       
} 

/*
* Modificar el valor del indice_plus a partir de valor del campo numero_plus
* 
*/
function _set_120($year) {
    global $clink;

    if (!$clink->if_table_exist("tproceso_eventos_$year")) {
        bar_progressCSS(1, "set_120 || year=$year --->  (100%)....", 1);
        return;
    }

    $sql= "select * from teventos where YEAR(fecha_inicio_plan) = $year";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);  

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if (empty($row['numero_plus']))
            continue;

        $indice_plus= setNULL(index_to_number($row['numero_plus']));

        $sql.= "update teventos set indice_plus = $indice_plus where id = {$row['id']}; ";
        $sql.= "update tproceso_eventos_$year set indice_plus = $indice_plus where id_evento = {$row['id']}; ";

        ++$j;
        if ($j >= 10) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_120 || year=$year --->  ($_r%)....", $r);
    }   }  
    
    if ($sql) {
        $clink->multi_query($sql);
        bar_progressCSS(1, "set_120 || year=$year --->  (100%)....", 1);
    }  
}

function set_120() {
    _set_120(2019);
    _set_120(2020);
    _set_120(2021);
    _set_120(2022);
}

/*
* Arreglar el intervalo de annos de duracion de la estructura de la listas de chequeo 
*/
function set_121() {
    global $clink;

    $sql= "select * from tlistas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result); 

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;

        $sql.= "update ttipo_listas set inicio= {$row['inicio']}, fin= {$row['fin']} ";
        $sql.= "where id_lista = {$row['id']}; ";
        $sql.= "update tlista_requisitos set inicio= {$row['inicio']}, fin= {$row['fin']} ";
        $sql.= "where id_lista = {$row['id']}; "; 

        ++$j;
        if ($j >= 10) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_121 --->  ($_r%)....", $r);
    }   }  
    
    if ($sql) {
        $clink->multi_query($sql);
        bar_progressCSS(1, "set_121 --->  (100%)....", 1);
    }       
}

/*
* Pobnerle valores al campo indice de la tabla tlista_requisitos
*/
/*
function _set_122() {
    global $clink;

    $sql= "select * from ttipo_listas";
    $result= $clink->query($sql);

    $array_tipo_lista= array();
    while ($row= $clink->fetch_array($result)) { 
        $array_tipo_lista[$row['id']]= array('id'=>$row['id'], 'indice'=>$row['indice']); 
    }
    return $array_tipo_lista;
}


function set_122() {
    global $clink;

    $array_tipo_lista= _set_122();

    $sql= "select * from tlista_requisitos ";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result); 
    
    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {  
        if (!empty($row['id_tipo_lista']))
            $indice= setNULL($array_tipo_lista[$row['id_tipo_lista']]['indice']);
        else    
            $indice= "NULL";

        $sql.= "update tlista_requisitos set indice=$indice where id_tipo_lista = {row['id']}; ";
        
        ++$j;
        if ($j >= 10) {
            $clink->multi_query($sql);

            $sql= null;
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_122 --->  ($_r%)....", $r);
    }   }  
    
    if ($sql) {
        $clink->multi_query($sql);
        bar_progressCSS(1, "set_122 --->  (100%)....", 1);
    }
}
*/