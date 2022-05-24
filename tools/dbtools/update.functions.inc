<?php

/**
 * Reparar la tabla tprocesos
 */
function repare_tprocesos() {
    global $clink;

    $sql= "select * from tprocesos where codigo is not null ";
    $result= $clink->query($sql);

    while ($row= $clink->query($result)) {
        if (empty($row['codigo']))
            continue;
        if ($row['id'] == $_SESSION['local_proceso_id'])
            continue;
        $id_code= build_code(1, $row['codigo']);

        $sql= "update tprocesos set id_code = '$id_code' where id_code != '$id_code' ";
        $clink->query($sql);
    }
}

/**
 * Reparar los campos id_proceso_code de todas las tablas
 */
function set_31() {
    global $clink;

    repare_tprocesos();

    $result_tb= $clink->tables();
    $cant_td= $clink->num_rows($result_tb);

    $i= 0;
    while ($row= $clink->fetch_array($result_tb)) {
        ++$i;
        $table= stringSQL($row[0]);
        $sql= "update $table as t1 ";
        $sql.= $_SESSION['_DB_SYSTEM'] == 'mysql' ? ", tprocesos " : "";
        $sql.= "set t1.id_proceso_code = tprocesos.id_code where t1.id_proceso = tprocesos.id ";
        if ($_SESSION['_DB_SYSTEM'] != 'mysql')
            $sql.= "from tprocesos ";

        $clink->query($sql);

        $r= (float)(++$i) / $cant_td;
        $_r= $r*100; $_r= number_format($_r,1);
         bar_progressCSS(1, "set_31(): completado ..... $_r%", $r);
    }

    return null;
}

/**
 * Crear el nuevo formato con que se guardan la formula del indicador
 * Es yamada desde la funcion set_32()
 */
function replace_form_indi($string, $show_name= false) {
    preg_match_all('/\'[a-zA-Z._:|\s]*\'/', $string, $array_names);
    preg_match_all('/[A-Z]{2}[0-9]{8}/i', $string, $array_code);
    preg_match_all('/IND_\(\'[a-zA-Z._:|\s]*\',\'[A-Z]{2}[0-9]{8}\'\)/i', $string, $array_sign);

    $i= 0;
    foreach ($array_sign[0] as $sign) {
        $term= "_{$array_code[0][$i]}";
        $string= str_replace((String)$sign, $term, $string);
        ++$i;
    }

    return $string;
}
/**
 * Arreglar el formato de los indicadores para el calculo
 */
function set_32() {
    global $clink;

    $sql= "select * from tindicadores";
    $result= $clink->query($sql);
    $cant_td= $clink->num_rows($result);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if (empty($row['calculo']))
            continue;

        $id= $row['id'];
        $calculo= replace_form_indi($row['calculo']);
        $sql= "update tindicadores set calculo = '$calculo' where id = $id";
        $clink->query($sql);

        $r= (float)(++$i) / $cant_td;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_32(): completado ..... $_r%", $r);
    }

    return null;
}

/**
 * Eliminar las tareas registradas en el sistema que mo estan asociadas a algun riesgo
 * nota o proyecto
 */
function set_33() {
    global $clink;

    $array_tareas= array();
    $sql= "select ttareas.id as _id, id_proyecto from ttareas ";
    $result= $clink->query($sql);
    $cant= $result->num_rows;

    $i= 0;
    while ($row= $clink->fetch_array($result))
        $array_tareas[$i++]= array('id'=>$row['_id'], 'id_proyecto'=>$row['id_proyeco']);

    if ($i == 0)
        return;

    $i= 0;
    foreach ($array_tareas as $tarea) {
        if (!empty($tarea['id_proyecto']))
            continue;
        $sql= "select * from triesgo_tareas where id_tarea = {$tarea['id']}";
        $result= $clink->query($sql);

        if (empty($result->num_rows)) {
            $sql= "delete from ttareas where id = {$tarea['id']}";
            $clink->query($sql);
        }

        $r= (float)(++$i) / $cant;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_33(): completado ..... $_r%", $r);
    }

    return null;
}

/**
 *  Agregar nuevos registros a la tabla tregistros de modo que aparezcan las fechas donde se debe
 * realizar la carga del dato real
 */
function set_34() {
    global $clink;

    $obj_indi = new Tindicador($clink);
    $result= $obj_indi->listar();
    $cant= $obj_indi->GetCantidad();

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        if (isset($obj_registro)) unset($obj_registro);
        $obj_registro= new Tregistro($clink);

        $obj_registro->SetYear($row['_inicio']);

        $obj_registro->SetIdIndicador($row['_id']);
        $obj_registro->set_id_code($row['_id_code']);

        $obj_registro->SetCarga($row['carga']);
        $obj_registro->SetPeriodicidad($row['periodicidad']);
        $obj_registro->SetInicio($row['_inicio']);
        $obj_registro->SetFin($row['_fin']);

        $obj_registro->SetTrend($row['trend']);
        $obj_registro->SetIfFormulated($row['formulated']);
        $obj_registro->SetIfCumulative($row['cumulative']);

        $obj_registro->insert_registro();

        $r= (float)(++$i) / $cant;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_34(): Agregando a tregistros ..... $_r%", $r);
    }

    $i= 0;
    $clink->data_seek($result);
    while ($row= $clink->fetch_array($result)) {
        $table= $_SESSION['_DB_SYSTEM'] == 'mysql' ? "tregistro." : "";

        $sql= "update tregistro ";
        $sql.= $_SESSION['_DB_SYSTEM'] == 'mysql' ? ", treg_real " : "";
        $sql.= "set {$table}valor= treg_real.valor, {$table}acumulado_real= treg_real.acumulado_real, ";
        $sql.= "{$table}id_usuario_real= treg_real.id_usuario, {$table}cronos_real= treg_real.cronos ";
        $sql.= "where {$table}day = treg_real.day and {$table}month = treg_real.month and {$table}year = treg_real.year ";
        $sql.= "and {$table}id_indicador = treg_real.id_indicador and treg_real.id_indicador = {$row['_id']}";

        $clink->query($sql);

        $r= (float)(++$i) / $cant;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_34(): Completando ..... $_r%", $r);
    }

    return null;
}

/**
 *  Modificar la tabla ttipo_eventos para agregar la columna subcapitulo0 con el numero del subcapitulo
 * incluir otro nivel de subcapitulo.
 */
function set_35() {
    global $clink;

    $obj = new Ttipo_evento($clink);
    $result = $obj->listar();
    $cant = $obj->GetCantidad();

    $i = 0;
    while ($row = $clink->fetch_array($result)) {
        ++$i;
        if (is_null($row['numero']))
            continue;
        list($index, $subindex) = preg_split('/\./', $row['numero']);

        $sql = "update ttipo_eventos set subcapitulo0 = $subindex where id = {$row['id']}";
        $clink->query($sql);

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_35(): Actualizando ttipo_eventos ..... $_r%", $r);
    }

    return null;
}

/**
 *  Crear un indice para el ordenamiento de la tabla ttipo _eventos.
 */
function set_36() {
    global $clink;

    $obj = new Ttipo_evento($clink);
    $result = $obj->listar();
    $cant = $obj->GetCantidad();

    $i = 0;
    while ($row = $clink->fetch_array($result)) {
        ++$i;
        $subcapitulo0= null;
        if (!empty($row['numero']) && empty($row['subcapitulo0'])) {
            list($capitulo, $subcapitulo0)= preg_split('/\./', $row['numero']);
        } else {
            if (!empty($row['subcapitulo0']))
                $subcapitulo0= $row['subcapitulo0'];
        }

        $index= $row['empresarial']*1000;
        $index+= (!empty($subcapitulo0) ? $subcapitulo0 : 0)*100;
        $index+= (!empty($row['subcapitulo1']) ? $row['subcapitulo1'] : 0)*1;
        $subcapitulo0= setNULL($subcapitulo0);

        $sql = "update ttipo_eventos set indice= $index, subcapitulo0= $subcapitulo0 where id = {$row['id']}";
        $clink->query($sql);

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_36(): Actualizando ttipo_eventos ..... $_r%", $r);
    }

    return null;
}
/**
 * Crear el nuevo formato con que se guardan la formula del indicador
 * Es yamada desde la funcion set_32()
 */
function set_37() {
    return set_32();
}
/**
 * Arreglar el campo indice de la tabla ttipo_eventos
 */
function set_38() {
    global $clink;
    $sql= "select * from ttipo_eventos where numero is not null ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
	list($index0, $index1, $index2) = preg_split('/[.]/', $row['numero']);
	if (empty($index1))
            $index1= 0;
	if (empty($index2))
            $index2= 0;

	$indice=  ($index0+1)*pow(10,6);
	$indice+= $index1*pow(10,3);
	$indice+= $index2*pow(10,1);

	$sql= "update ttipo_eventos set indice = $indice where id = {$row['id']}";
	$clink->query($sql);
    }

    return null;
}

/**
 * Eliminar los usuarios duplicados por el LDAP
 */
function set_39() {
    global $clink;

    $sql = "select * from tusuarios order by id asc";
    $result = $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_39(): Total de usuarios iniciales <strong>$cant ..... 0%", 0);
    $array= array();

    while ($row = $clink->fetch_array($result)) {
        $sql = "select id, usuario from tusuarios where usuario = '{$row['usuario']}' and id > {$row['id']}";
        $result_user= $clink->query($sql);
        $cant= $clink->num_rows($result_user);

        if ($cant) {
            $row_user= $clink->fetch_array($result_user);
            $array[]= array('id'=>$row_user['id'],'usuario'=>$row_user['usuario']);
        }
    }

    $i = 0;
    $j = 0;
    foreach ($array as $row) {
        ++$i;
        $sql = "delete from tusuarios where usuario = '{$row['usuario']}' and id = {$row['id']}";
        $clink->query($sql);

        if ($clink->affected_rows() > 0) {
            ++$j;
            $r = (float)($i) / $cant;
            $_r = $r * 100;
            $_r = number_format($_r, 1);
            bar_progressCSS(1, "set_39(): Eliminado USUARIO={$row['usuario']} ID={$row['id']} ..... $_r%", $r);
            continue;
        } else {
            if ($clink->error()) {
                $sql = "delete from treg_evento where id_usuario = {$row['id']}";
                $clink->query($sql);

                $sql = "delete from tplanes where id_usuario = {$row['id']} and tipo = 5";
                $clink->query($sql);

                $sql = "delete from tusuario_eventos where id_usuario = {$row['id']}";
                $clink->query($sql);

                $sql = "delete from tusuarios where usuario = '{$row['usuario']}' and id = {$row['id']}";
                $clink->query($sql);

                if ($clink->affected_rows() > 0) {
                    ++$j;
                    $r = (float)($i) / $cant;
                    $_r = $r * 100;
                    $_r = number_format($_r, 1);
                    bar_progressCSS(1, "set_39(): Eliminado USUARIO={$row['usuario']} ID={$row['id']} ..... $_r%", $r);
                }
            }
        }

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_39(): Verificando usuario ..... $_r%", $r);
    }

    bar_progressCSS(1, "set_39(): Eliminado <strong>$j</strong> del sistema 100%", 1);
    return null;
}

/**
 * Arreglar el campo indice de la tabla ttipo_eventos
 */
function set_40() {
    global $clink;
    $sql= "select * from ttipo_eventos where numero is not null ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
	$indice=  $row['empresarial']*pow(10,6);
	$indice+= $row['subcapitulo0'] ? $row['subcapitulo0']*pow(10,3) : 0;
	$indice+= $row['subcapitulo1'] ? $row['subcapitulo1']*pow(10,1) : 0;

	$sql= "update ttipo_eventos set indice = $indice where id = {$row['id']}";
	$clink->query($sql);
    }
    return null;
}

function set_41() {
    return set_39();
}

/**
 * Fijar oficina de archivo a las Unidades principales
 */
function set_42() {
    global $clink;

    $sql= "update tprocesos set local_archive = ".boolean2pg(1). "where id = ". $_SESSION['local_proceso_id'];
    $clink->query($sql);

    if (!is_null($clink->error())) {
        $error= "$sql <br/><br/>";
        $error.= "<br/>".$clink->error()."<br/>";
        return $error;
    }

    return null;
}

/*
 * Incorporar a la tabla tproceso_eventos el proceso de los responsables de la tarea registrados en ttareas
 */
function set_43() {
    global $clink;
    global $cronos;

    $sql= "select distinct ttareas.id as _id, ttareas.id_code as _id_code, ttareas.id_responsable as _id_responsable, ";
    $sql.= "tusuarios.id_proceso as _id_proceso, tusuarios.id_proceso_code as _id_proceso_code, ttareas.cronos as _cronos ";
    $sql.= "from ttareas, tusuarios where ttareas.id_responsable = tusuarios.id ";

    $result= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_43(): Total de tareas iniciales <strong>$cant ..... 0%", 0);
    $array= array();

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $sql= "insert into tproceso_eventos (id_tarea, id_tarea_code, id_proceso, id_proceso_code, id_responsable, situs, cronos) ";
        $sql.= "values ({$row['_id']}, '{$row['_id_code']}', {$row['_id_proceso']}, '{$row['_id_proceso_code']}', {$row['_id_responsable']}, ";
        $sql.= "'{$_SESSION['location']}', '{$row['_cronos']}) ";

        @$clink->query($sql);

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_43(): Acualizando ..... $_r%", $r);
    }

    return null;
}

/*
 *  Darle a las entidades la posibilidad de tener oficina de archivo
 */
function set_44() {
    global $clink;
    global $cronos;

    $sql= "update tprocesos set local_archive = ". boolean2pg(1).", cronos= '$cronos' ";
    $sql.= "where id = {$_SESSION['local_proceso_id']}";
    $clink->query($sql);

    if (!is_null($clink->error())) {
        $error= "$sql <br/><br/>";
        $error.= "<br/>".$clink->error()."<br/>";
        return $error;
    }

    return null;
}

function set_45() {
    return set_43();
}

/*
 *  Agregar los objetivos de control interno a tproceso_objetivos para que se puedan ver
 */
function set_46() {
    global $clink;
    global $cronos;

    $sql= "select * from tobjetivos where if_control_interno = ".boolean2pg(1)."; ";
    $result= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_46(): Total de objetivos iniciales <strong>$cant ..... 0%", 0);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;

        for ($year= $row['inicio']; $year <= $row['fin']; ++$year) {
            $sql= "insert into tproceso_objetivos (id_objetivo, id_objetivo_code, id_proceso, id_proceso_code, year, cronos, situs) ";
            $sql.= "values ({$row['id']}, '{$row['id_code']}', {$row['id_proceso']}, '{$row['id_proceso_code']}', $year, ";
            $sql.= "'{$row['cronos']}', '{$row['situs']}')";

            @$clink->query($sql);
        }

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_46(): Acualizando ..... $_r%", $r);
    }

    if (!is_null($clink->error())) {
        $error= "$sql <br/><br/>";
        $error.= "<br/>".$clink->error()."<br/>";
        return $error;
    }

    return null;
}

/*
 *  Quitar redundancia en la tabla tindicador_criterio
 */
function set_47() {
    global $clink;
    global $cronos;

    $sql= "select distinct id, id_indicador, id_proceso, year from tindicador_criterio order by id desc";
    $result= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_47(): Total de registros indicadores iniciales <strong>$cant ..... 0%", 0);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;

        $sql= "delete from tindicador_criterio where id_indicador = {$row['id_indicador']} and id_proceso = {$row['id_proceso']} ";
        $sql.= "and year = {$row['year']} and id < {$row['id']}";
        @$clink->query($sql);

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_47(): Acualizando ..... $_r%", $r);
    }

    return null;
}

/*
 *  Arreglar la tabla ttipo_eventos para la sincronizacion
 */
function set_48() {
    global $clink;
    global $cronos;

    $sql= "select * from ttipo_eventos";
    $result= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_48(): Total de registros iniciales <strong>$cant ..... 0%", 0);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;

        $id_code= build_code($row['id']);
        $id_subcapitulo_code= build_code($row['id_subcapitulo']);

        $sql= "update ttipo_eventos set id_proceso= {$_SESSION['local_proceso_id']}, id_proceso_code= '{$_SESSION['local_proceso_id_code']}', ";
        $sql.= "id_code= '{$id_code}', id_subcapitulo_code= '{$id_subcapitulo_code}' where id = {$row['id']}";
        @$clink->query($sql);

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_48(): Acualizando ..... $_r%", $r);
    }

    return null;
}

/*
 *  Arreglar la tabla ttipo_eventos para la sincronizacion
 */
function set_49() {
    global $clink;
    global $cronos;

    $sql= "select * from teventos";
    $result= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_49(): Total de registros iniciales <strong>$cant ..... 0%", 0);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        ++$j;
        if (empty($row['id_tipo_evento']))
            continue;
        $id_code= build_code($row['id_tipo_evento']);
        $sql.= "update teventos set id_tipo_evento_code= '$id_code' where id = {$row['id']}; ";

        if ($j > 100) {
            @$clink->query($sql);
            $sql= null;
            $j= 0;
        }

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_49(): Acualizando ..... $_r%", $r);
    }

    if ($sql) {
        @$clink->query($sql);
        bar_progressCSS(1, "set_49(): Terminado $_r%", $r);
    }

    return null;
}

/*
 *  Recuperar las tareas principales de los planes
 */
function set_50() {
    global $clink;
    global $cronos;

    $sql= "select id from tplanes where id_usuario is not null";
    $result= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_50(): Total de registros iniciales <strong>$cant ..... 0%", 0);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if (!empty($row['objetivos']))
            continue;

        $sql= "update tplanes set objetivos= (select observacion from treg_plantrab where id_plan= {$row['id']} ";
        $sql.= "and action= 'APROBADO' order by cronos desc limit 1) where id= {$row['id']}";
        @$clink->query($sql);

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_50(): Acualizando ..... $_r%", $r);
    }

    return null;
}

/*
 *  Recuperar las tareas principales de los planes
 */
function set_51() {
    global $clink;
    global $cronos;

    $sql= "select * from tpoliticas";
    $result= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_51(): Total de registros iniciales <strong>$cant ..... 0%", 0);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $id_code= build_code($row['id'], $_SESSION['location']);

        $sql= "update tpoliticas set id_code= '$id_code', situs='{$_SESSION['location']}' where id = {$row['id']}";
        @$clink->query($sql);

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_51(): Acualizando ..... $_r%", $r);
    }

    return null;
}

/**
 * Arreglar el id_tipo_evento en la tabla teventos despues de la sincronizacion;
 */
function set_52() {
    global $clink;
    global $cronos;

    bar_progressCSS(1, "set_52(): Iniciando ..... 0%", 0.1);

    $sql= "update teventos set id_tipo_evento= null, id_tipo_evento_code= null ";
    $sql.= "where id_proceso <> {$_SESSION['local_proceso_id']}";
    @$clink->query($sql);

    bar_progressCSS(1, "set_52(): Eliminando registros foraneos de ttipo_eventos ..... 0%", 0.4);

    $sql= "delete from ttipo_eventos where id_proceso <> {$_SESSION['local_proceso_id']}";
    @$clink->query($sql);

    bar_progressCSS(1, "set_52(): Eliminando registros foraneos de ttipo_eventos ..... 0%", 0.7);

    $result= $clink->tables();
    $nums_tb= $clink->num_rows($result);
    $array= array();

    if ($_SESSION["_DB_SYSTEM"] == 'mysql')
        while ($row = mysqli_fetch_array($result))
            $array[] = $row;
    else
        $array = pg_fetch_all($result);

    $clink->free_result($result);

    $i= 0;
    $j= 0;
    $sql= null;

    $array_table= array('_config', '_config_synchro', 'tsincronizacion', 'talerts', 'tseries',
                        'tsubordinados', 'tgrupos', 'texcel', 'texcel_celdas', 'texcel_plantillas',
                        'ttableros', 'tusuario_tableros', 'tsystem', 'tusuario_grupos');

    reset($array);
    foreach ($array as $row) {
        $table= $row[0];
        ++$i;
        if (array_search($table, $array_table) !== false)
            continue;
        $sql.= "update $table set cronos_syn= null where cronos_syn is not null and cronos_syn > '2018-03-20 00:00:00'; ";
        ++$j;
        if ($j > 500) {
            $clink->multi_query($sql);
            $j= 0;
            $sql= null;
        }

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_52(): Actualizando campo cronos_syn en tabla $table ($_r%)....", $r);
    }

    if (!is_null($sql))
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_52(): Terminada 100%", 1);
    return null;
}

/**
 * Poner el id_proceso de las auditorias y los eventos en el proceso original
 */
function set_53() {
    global $clink;
    global $cronos;
    $year= date('Y');

    bar_progressCSS(1, "set_53(): Iniciando ..... 0%", 0.1);

    $sql= "update teventos set id_proceso= {$_SESSION['local_proceso_id']}, id_proceso_code= '{$_SESSION['local_proceso_id_code']}' ";
    $sql.= "where situs = '{$_SESSION['location']}' and year(fecha_inicio_plan) > $year";
    @$clink->query($sql);

    bar_progressCSS(1, "set_53(): terminado. 60%", 0.6);

    $sql= "update tauditorias set id_proceso= {$_SESSION['local_proceso_id']}, id_proceso_code= '{$_SESSION['local_proceso_id_code']}' ";
    $sql.= "where situs = '{$_SESSION['location']}' and year(fecha_inicio_plan) > $year";
    @$clink->query($sql);

    bar_progressCSS(1, "set_53(): terminado. 100%", 1);
    return null;
}

function clean_html($v) {
    if (is_null($v) || strlen($v) == 0)
        return null;

    $v= preg_replace("#<!--\[if gte mso (.?)\]>(.*?)<!\[endif\]-->#is",'', $v);
    $v= preg_replace("#<!--\[if gte mso (.?)(.?)\]>(.*?)<!\[endif\]-->#is",'', $v);
    return $v;
}

function purge_table($table, $field) {
    global $clink;
    global $cronos;

    $sql= "select id, $field from $table";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        if (empty($row[1]))
            continue;
        ++$j;
        ++$i;
        $text= clean_html($row[1]);
        $text= setNULL_str($text);

        $sql.= "update $table set $field = $text where id = {$row['0']}; ";

        if ($j > 500) {
            $_result= $clink->multi_query($sql);
            $j= 0;
            $sql= null;

            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_54 ---> purge_{$table}(): Actualizando campo $field en tabla $table ($_r%)....", $r);
        }
    }

    if (!is_null($sql)) {
        $clink->multi_query($sql);
        bar_progressCSS(1, "set_54 ---> purge_{$table}(): Terminada 100%", 1);
    }

    return null;
}

function set_54() {
    global $clink;
    global $cronos;

    purge_table("treg_evento", "observacion");
    purge_table("treg_plantrab", "observacion");
    purge_table("teventos", "descripcion");
    purge_table("teventos", "lugar");
    purge_table("tproceso_eventos", "evaluacion");
    purge_table("tauditorias", "lugar");
    purge_table("tauditorias", "objetivos");
    purge_table("tdebates", "observacion");
    purge_table("tnotas", "descripcion");
    purge_table("tplanes", "objetivos");
    purge_table("treg_riesgo", "observacion");
    purge_table("triesgos", "descripcion");

    return null;
}

function set_55() {
    global $clink;
    global $cronos;

    $sql= "update ttematicas set id_proceso = {$_SESSION['local_proceso_id']}, id_proceso_code = '{$_SESSION['local_proceso_id_code']}' ";
    $sql.= "where situs = '{$_SESSION['location']}'";

    $result= $clink->query($sql);
    bar_progressCSS(1, "set_55 ---> Terminada 100%", 1);
    return null;
}

/*
 * rellenar el campo situs que esta null
 */
function set_56() {
    global $clink;
    global $cronos;
    global $location;

    $result= $clink->tables();
    $nums_tb= $clink->num_rows($result);

    $i= 0;
    while ($table= $clink->fetch_array($result)) {
        ++$i;
        if ($table[0] == "_config" || $table[0] == "tprocesos")
            continue;

        $sql= "update {$table[0]} set situs= '$location' where (situs is null or length(situs) = 0)";
        $clink->query($sql);

        $r= (float)($i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_56 ---> {$table[0]} -- modificadas: $j:  ($_r%)....", $r);
    }

    return null;
}

/*
 * llamada por set_56()
 */
function test_treg_evento($id_evento, $id_usuario= null) {
    global $clink;

    $sql= "select count(*) as _count from treg_evento where id_evento = $id_evento ";
    if (!empty($id_usuario))
        $sql.= "and id_usuario = $id_usuario";
    $result= $clink->query($sql);
    $nums= $clink->fetch_array($result)[0];
    return $nums;
}

/*
 * Eliminar los registros de teventos que no tienen refrencia el id_responsable en la tabla treg_evento
 */
function set_57() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from teventos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $t= 0;
    $i= 0;
    $d= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$t;
        $nums= test_treg_evento($row['id'], $row['id_responsable']);
        if (!empty($nums))
            continue;
        $nums= test_treg_evento($row['id'], null);

        if (!empty($nums)) {
            ++$i;
            $sql= "insert into treg_evento (id_evento, id_evento_code, id_usuario, id_responsable, compute, toshow, cumplimiento, ";
            $sql.= "user_check, rechazado, cronos, situs) values ({$row['id']}, '{$row['id_evento_code']}', {$row['id_responsable']}, 1, ";
            $sql.= "0, 0, 6, 1, '{$row['cronos']}', '{$row['cronos']}', '{$row['situs']}');";

            $clink->query($sql);
        } else {
            ++$d;
            $sql= "delete from teventos where id = {$row['id']}";
            $_result= $clink->query($sql);

            if ($_result) {
                $sql= "delete from tproceso_eventos where id_evento = {$row['id']}";
                $clink->query($sql);

                $sql= "delete from tusuario_eventos where id_evento = {$row['id']}";
                $clink->query($sql);
            }
        }

        ++$j;
        if ($j > 500) {
            $j= 0;
            $r= (float)($t) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_57 ---> total=$t  insert=$i   delete=$d ($_r%)....", $r);
        }
    }

    if ($j)
        bar_progressCSS(1, "set_57 ---> total=$t    insert=$i    delete=$d Terminada 100%", 1);

    return null;
}

/*
 * Arreglar loas campos numeros vacios que existen en las BD viejas
 */
function _test_58($id_subcapitulo) {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select numero from ttipo_eventos where id = $id_subcapitulo";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);
    return $row[0];
}

function set_58() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from ttipo_eventos where numero is null";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $index[1][0]= 0;
    $index[2][0]= 0;
    $index[3][0]= 0;
    $index[4][0]= 0;
    $index[5][0]= 0;
    $index[6][0]= 0;

    $j= 0;
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $numero= null;
        $in= null;
        if (!empty($row['id_subcapitulo']))
            continue;

        $in= $row['empresarial'] - 1;
        ++$index[$in][0];
        $numero= "$in.{$index[$in][0]}";

        ++$j;
        $numero= setNULL_str($numero);
        $subcapitulo0= setNULL($index[$in][0]);
        $sql= "update ttipo_eventos set numero= $numero, subcapitulo0= $subcapitulo0 where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_58 ---> ($_r%)....", $r);
    }

    $clink->data_seek($result);

    while ($row= $clink->fetch_array($result)) {
        $numero= null;
        $in= null;
        if (empty($row['id_subcapitulo']))
            continue;

        $in= $row['empresarial'] - 1;
        ++$index[$in][$row['id_subcapitulo']];

        $numero= _test_58($row['id_subcapitulo']);

        if (!empty($numero))
            $numero.= ".{$index[$in][$row['id_subcapitulo']]}";
        else
            $numero= "$in.{$index[$in][0]}.{$index[$in][$row['id_subcapitulo']]}";

        ++$j;
        $numero= setNULL_str($numero);
        $subcapitulo0= setNULL($index[$in][0]);
        $subcapitulo1= setNULL($index[$in][$row['id_subcapitulo']]);
        $sql= "update ttipo_eventos set numero= $numero, subcapitulo0= $subcapitulo0, subcapitulo1= $subcapitulo1 ";
        $sql.= "where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_58 ---> ($_r%)....", $r);
    }

    if ($j)
        return set_40();
    else
        return null;
}

/*
 * arreglar el code y el situs de las politicas que han sido insertadas desde el script de
 * actualizacion
 */
function set_59() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tpoliticas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $id_code= str_replace("XX", $location, $row['id_code']);
        $sql= "update tpoliticas set id_code = '$id_code', inicio= 2012, fin= 2030, situs= '$location' where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_59 ---> ($_r%)....", $r);
    }

    return null;
}

/*
 * arreglar el code y el situs de las politicas que han sido insertadas desde el script de
 * actualizacion
 */
function set_60() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "show tables";
    $result_table= $clink->query($sql);

    $j= 0;
    while ($row_table= $clink->fetch_array($result_table)) {
        $table= $row_table[0];
        ++$j;
        $sql= "select id, origen_data from $table";
        $result= $clink->query($sql);
        if (!$result)
            continue;
        $nums_tb = $clink->num_rows($result);

        $i= 0;
        while ($row= $clink->fetch_array($result)) {
            if (empty($row['origen_data']))
                continue;

            $origen_data= preg_replace('&', '^', $row['origen_data']);
            $sql= "update $table set origen_data = '$origen_data' where id = {$row['id']}";
            $clink->query($sql);

            $r= (float)(++$i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_60 tabla:$table ---> ($_r%)....", $r);
        }
    }

   bar_progressCSS(1, "set_60 terminada ---> (100%)....", 1);
   return null;
}

/*
 * arreglar el code y el situs de la tabla tlistas insertada desde el script de
 * actualizacion
 */
function set_61() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tlistas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $id_code= str_replace("XX", $location, $row['id_code']);
        $id_proceso_code= !empty($row['id_proceso_code']) ? str_replace("XX", $location, $row['id_proceso_code']) : null;

        $id_proceso_code= setNULL_str($id_proceso_code);

        $sql= "update tlistas set id_code = '$id_code', id_proceso= {$_SESSION['local_proceso_id']}, ";
        $sql.= "id_proceso_code= $id_proceso_code, situs= '$location' where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_61 ---> ($_r%)....", $r);
    }

    return null;
}

/*
 * arreglar el code y el situs de la tabla ttipo_listas insertada desde el script de
 * actualizacion
 */
function set_62() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from ttipo_listas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $id_code= str_replace("XX", $location, $row['id_code']);
        $id_lista_code= !empty($row['id_lista_code']) ? str_replace("XX", $location, $row['id_lista_code']) : null;
        $id_capitulo_code= !empty($row['id_capitulo_code']) ? str_replace("XX", $location, $row['id_capitulo_code']) : null;
        $id_proceso_code= !empty($row['id_proceso_code']) ? str_replace("XX", $location, $row['id_proceso_code']) : null;

        $id_lista_code= setNULL_str($id_lista_code);
        $id_capitulo_code= setNULL_str($id_capitulo_code);
        $id_proceso_code= setNULL_str($id_proceso_code);

        $sql= "update ttipo_listas set id_code = '$id_code', id_proceso= {$_SESSION['local_proceso_id']}, ";
        $sql.= "id_lista_code= $id_lista_code, id_proceso_code= $id_proceso_code, id_capitulo_code= $id_capitulo_code, ";
        $sql.= "situs= '$location' where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_62 ---> ($_r%)....", $r);
    }

    return null;
}

/*
 * arreglar el code y el situs de la tabla tlista_requisitos insertada desde el script de
 * actualizacion
 */
function set_63() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tlista_requisitos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $id_code= str_replace("XX", $location, $row['id_code']);
        $id_lista_code= !empty($row['id_lista_code']) ? str_replace("XX", $location, $row['id_lista_code']) : null;
        $id_tipo_lista_code= !empty($row['id_tipo_lista_code']) ? str_replace("XX", $location, $row['id_tipo_lista_code']) : null;

        $id_lista_code= setNULL_str($id_lista_code);
        $id_tipo_lista_code= setNULL_str($id_tipo_lista_code);

        $sql= "update tlista_requisitos set id_code = '$id_code', id_lista_code= $id_lista_code, ";
        $sql.= "id_tipo_lista_code= $id_tipo_lista_code, situs= '$location' where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_63 ---> ($_r%)....", $r);
    }

    return null;
}

/*
 * arreglar el code y el situs de la tabla tproceso_listas insertada desde el script de
 * actualizacion
 */
function set_64() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tproceso_listas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $id_code= str_replace("XX", $location, $row['id_code']);
        $id_lista_code= !empty($row['id_lista_code']) ? str_replace("XX", $location, $row['id_lista_code']) : null;
        $id_proceso_code= !empty($row['id_proceso_code']) ? str_replace("XX", $location, $row['id_proceso_code']) : null;

        $id_lista_code= setNULL_str($id_lista_code);
        $id_proceso_code= setNULL_str($id_proceso_code);

        $sql= "update tproceso_listas set id_lista_code= $id_lista_code, id_proceso= {$_SESSION['local_proceso_id']}, ";
        $sql.= "id_proceso_code= $id_proceso_code, situs= '$location' where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_64 ---> ($_r%)....", $r);
    }

    return null;
}

/*
 *  Hacer que el intervalo de años de indicador sea el mismo que abarca la tabla tindicador_criterio
 */
function set_65() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tindicadores where situs = '$location'";
    $result_indi= $clink->query($sql);
    $cant = $clink->num_rows($result);

    bar_progressCSS(1, "set_65(): Total de registros indicadores iniciales <strong>$cant ..... 0%", 0);

    $i= 0;
    while ($row= $clink->fetch_array($result_indi)) {
        $id= $row['id'];
        $inicio= $row['inicio'];
        $fin= $row['fin'];

        ++$i;
        for ($year= $inicio; $year <= $fin; $year++) {
            $query= "select * from tindicador_criterio where situs = '$location' and year = $year and id_indicador = $id";
            $result= $clink->query($query);
            $exists= $clink->num_rows($result);

            if ($exists && $exists != -1)
                continue;

            $sql= "insert into tindicador_criterio (year, trend, id_indicador, id_indicador_code, id_proceso, ";
            $sql.= "id_proceso_code, cronos, situs) values ($year, 1, {$row['id']}, '{$row['id_code']}', {$row['id_proceso']}, ";
            $sql.= "'{$row['id_proceso_code']}', '$cronos', '$situs')";
            $clink->query($sql);
        }

        $r = (float)($i) / $cant;
        $_r = $r * 100;
        $_r = number_format($_r, 1);
        bar_progressCSS(1, "set_65(): Acualizando ..... $_r%", $r);
    }

    return null;
}

/**
* hacer corresponder el campo fin de la tabla tindicadores con el ultimo anno que aparece en la tabla tregistro,
* tref_indicadores, tindicador_criterio. Se toma como ultimo anno el que parezca en tregistro
*/
function set_66() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select distinct * from tindicadores where situs = '$location'";
    $result_1= $clink->query($sql);
    $cant_1= $clink->num_rows($result_1);

    $i= 0;
    while ($row_1= $clink->fetch_array($result_1)) {
        ++$i;

        $id= $row_1['id'];
        $inicio= $row_1['inicio'];
        $fin= $row_2['fin'];

        $sql= "select id_indicador, max(year) as fin, min(year) as inicio from tregistro where id_indicador= $id";
        $result_2= $clink->query($sql);
        if (!$result_2) return $sql;

        $cant_2= $clink->num_rows($result_2);
        if ($cant_2 == 0) continue;
        $row_2= $clink->fetch_array($result_2);

        $sql= "update tindicadores set inicio= {$row_2['inicio']}, fin= {$row_2['fin']}, cronos= '$cronos' ";
        $sql.= "where situs= '$location' and id = $id";
        $clink->query($sql);

        $sql= "delete from tindicador_criterio where (year < {$row_2['inicio']} or year > {$row_2['fin']}) ";
        $sql.= "and situs = '$location' and id_indicador = $id";
        $clink->query($sql);

        $r= (float)($i) / $cant_1;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_66(): completado ..... $_r%", $r);
    }

    return null;
}


function test_if_in_proceso($id_user, $array_proceso) {
    global $clink;

    $obj_user= new Tusuario($clink);
    $obj_user->Set($id_user);
    $id_proceso= $obj_user->GetIdProceso();

    return array_key_exists($id_proceso, $array_proceso) ? true : false;
}
/*
 * Eliminar los registros de teventos que no tienen referencia el id_responsable en la tabla treg_evento
 */
function set_67() {
    global $clink;
    global $cronos;
    global $location;

    $obj_prs= new Tproceso($clink);
    $obj_prs->SetIdProceso($_SESSION['local_proceso_id']);
    $obj_prs->Set();
    $id_chief_prs= $obj_prs->GetIdResponsable();
    $array_proceso= $obj_prs->get_procesos_down();

    $sql= "select * from teventos where situs != '$location'";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$j;
        ++$i;

        $in_reg= test_treg_evento($row['id'], $row['id_responsable']);
        $in_prs= test_if_in_proceso($row['id_responsable'], $array_proceso);
        if ($in_reg && $in_prs)
            continue;

        if (!$in_prs) {
            $found= test_treg_evento($row['id'], $id_chief_prs);
            if ($found)
                continue;

            $sql= "insert into treg_evento (id_evento, id_evento_code, id_usuario, id_responsable, compute, toshow, cumplimiento, ";
            $sql.= "user_check, rechazado, cronos, situs) values ({$row['id']}, '{$row['id_evento_code']}', $id_chief_prs, 1, ";
            $sql.= "1, 0, 6, 1, null, '{$row['cronos']}', '{$row['situs']}');";

            $clink->query($sql);
        }

        if ($j > 500) {
            $j= 0;
            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_67 --->  ($_r%)....", $r);
        }
    }

    return null;
}

/*
 * Eliminar los eventos que hayan sido copiados para el proximo ano y sean resultado de la sincronizacion;
 */
global $array_procesos;

function _set_68_table($table) {
    global $clink;
    global $cronos;
    global $location;

    global $array_procesos;

    $year= (int)date('Y') + 1;
    $sql= "select id, id_proceso from $table where year(fecha_inicio_plan) = $year";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $id_field= null;
    switch($table) {
        case "teventos" :
            $id_field= "id_evento";
            break;
        case "tauditorias" :
            $id_field= "id_auditoria";
            break;
        case "ttareas" :
            $id_field= "id_tarea";
            break;
        default:
            $id_field= "id_evento";
    }

    $j= 0;
    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$j;
        ++$i;

        if (!empty($row['id_proceso'])
                && (array_key_exists($row['id_proceso'], $array_procesos) && $row['id_proceso'] != $_SESSION['local_proceso_id'])) {
            $sql= "delete from $table where id= {$row['id']}";
            $clink->query($sql);

            $sql= "delete from treg_evento where $id_field= {$row['id']}";
            $clink->query($sql);

            $sql= "delete from tusuario_eventos where $id_field= {$row['id']}";
            $clink->query($sql);

            $sql= "delete from tproceso_eventos where $id_field= {$row['id']}";
            $clink->query($sql);
        }

        if ($j > 500) {
            $j= 0;
            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_68($table) --->  ($_r%)....", $r);
        }
    }

    return null;
}

function set_68() {
    global $clink;
    global $cronos;
    global $location;

    global $array_procesos;

    $obj_prs= new Tproceso($clink);
    $obj_prs->SetConectado(_NO_LOCAL);
    $obj_prs->listar_in_order('asc_desc', false);
    $array_procesos= $obj_prs->array_procesos;

    _set_68_table("teventos");
    _set_68_table("tauditorias");
    _set_68_table("ttareas");

    return null;
}

/* Recuperar los registros de tasistencias que se han perdido */
function set_69() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tdebates";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        ++$j;

        $sql= "select teventos.* from teventos, ttematicas where teventos.id = ttematicas.id_evento ";
        $sql.= "and ttematicas.id = {$row['id_tematica']}";
        $result2= $clink->query($sql);
        $row2= $clink->fetch_array($result2);

        if (empty($row2['id']))
            continue;

        $sql= "insert into tasistencias (id_usuario, id_evento, id_evento_code, id_proceso, id_proceso_code, cronos, situs) ";
        $sql.= "values ({$row['id_responsable']}, {$row2['id']}, '{$row2['id_code']}', {$row2['id_proceso']}, ";
        $sql.= "'{$row2['id_proceso_code']}', '{$row2['cronos']}', '{$row2['situs']}')";
        $result3= $clink->query($sql);

        if ($result3) {
            $id= $clink->inserted_id();
            $id_code= build_code($id, $row2['situs']);

            $sql= "update tasistencias set id_code= '$id_code' where id = $id";
            $clink->query($sql);
        }

        if ($j > 500) {
            $j= 0;
            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_70 --->  ($_r%)....", $r);
        }
    }

    return null;
}

/* Crear el campo id_code en la tabla tasistencias  */
function set_70() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tasistencias";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        if (!empty($row['id_code']))
            continue;

        $id_code= build_code($row['id'], $row['situs']);
        $sql= "update tasistencias set id_code = '$id_code' where id = {$row['id']}";
        $clink->query($sql);

        ++$i;
        ++$j;
        if ($j > 500) {
            $j= 0;
            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_70 --->  ($_r%)....", $r);
        }
    }

    return null;
}

/* actualizar la tabla tusuario_eventos con los participantes de las tematicas */
function set_71() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from ttematicas where (ifaccords is null or ifaccords = 0)";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $j= 0;
    $i= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $sql= "insert into tusuario_eventos (id_tematica, id_tematica_code, id_usuario, cronos, situs) values (";
        $sql.= "{$row['id']}, '{$row['id_code']}', {$row['id_responsable']}, '{$row['cronos']}', '{$row['situs']}')";

        $clink->query($sql);

        ++$i;
        ++$j;
        if ($j > 500) {
            $j= 0;
            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_71 --->  ($_r%)....", $r);
        }
    }

    return null;
}

function set_72() {
    global $clink;
    global $cronos;
    global $location;

    set_70();

    $sql= "select id, id_usuario, id_evento from tasistencias order by id asc";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    while ($row= $clink->fetch_array($result)) {
        $id= $row['id'];
        $id_evento= $row['id_evento'];
        $id_usuario= $row['id_usuario'];

        $sql= "delete from tasistencias where id > $id and (id_evento = $id_evento and id_usuario = $id_usuario)";
        $clink->query($sql);

        ++$i;
        ++$j;
        if ($j > 500) {
            $j= 0;
            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_72 --->  ($_r%)....", $r);
        }
    }

    $sql= "select MAX(id) from tasistencias";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);
    $id= $row[0]+1;

    $sql= "ALTER TABLE tasistencias AUTO_INCREMENT= $id";
    $clink->query($sql);

    return null;
}

function set_73() {
    return set_69();
}

function set_74() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select id from tgrupos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    while ($row= $clink->fetch_array($result)) {
        $sql= "select max(tusuarios.cronos) as _cronos from tusuarios, tusuario_grupos ";
        $sql.= "where tusuarios.id = tusuario_grupos.id_usuario and tusuario_grupos.id_grupo = {$row['id']}";
        $_result= $clink->query($sql);
        $_row= $clink->fetch_array($_result);

        $sql= "update tgrupos set cronos= '{$_row['_cronos']}' where id = {$row['id']}";
        $clink->query($sql);

        $r= (float)(++$i) / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_74 --->  ($_r%)....", $r);
    }

    return null;
}

/*
 * a la tabla teventos agregarle el campo indice para no tener que carcularlo durante la elaboracion
 * del plan anual
 */
function set_75() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from teventos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $sql= null;
    $array_auditoria= array();
    $array_tarea= array();
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if ($row['empresarial'] < 2)
            continue;

        $tidx= null;
        if (!empty($row['id_evento']) || !empty($row['id_auditoria']) || !empty($row['id_tarea']))
            $tidx= false;
        if (!empty($row['periodicidad']))
            $tidx= true;
        if (empty($row['periodicidad']) && (empty($row['id_evento']) && empty($row['id_auditoria']) && empty($row['id_tarea'])))
            $tidx= true;

        $tidx= boolean2pg($tidx);
        $empresarial= $row['empresarial'];
        $id_tipo_evento= $row['id_tipo_evento'];
        $indice_plus= setNULL(!empty($row['numero_plus']) ? index_to_number($row['numero_plus']) : null);

        if (empty($id_tipo_evento)) {
            $indice= $empresarial*pow(10,6);
            $sql.= "update teventos set indice= $indice, indice_plus= $indice_plus, tidx= $tidx where id = {$row['id']}; ";
        } else {
            $sql.= "update teventos, ttipo_eventos set teventos.indice= ttipo_eventos.indice, indice_plus= $indice_plus, ";
            $sql.= "tidx= $tidx where teventos.id = {$row['id']} and teventos.id_tipo_evento = ttipo_eventos.id; ";
        }

        ++$j;
        if ($j >= 1000) {
            $clink->multi_query($sql);
            $j= 0;
            $sql= null;

            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_75 --->  ($_r%)....", $r);
        }
    }

    if ($j)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_75 --->  (100%)....", 1);
    return null;
}

/*
 * a la tabla tauditorias agregarle el campo indice para no tener que carcularlo durante la elaboracion
 * del plan anual
 */
function set_76() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from tauditorias";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        if ($row['empresarial'] < 2)
            continue;

        $tidx= null;
        if (!empty($row['id_auditoria']))
            $tidx= false;
        if (!empty($row['periodicidad']))
            $tidx= true;
        if (empty($row['periodicidad'])  && empty($row['id_auditoria']))
            $tidx= true;

        $tidx= boolean2pg($tidx);
        $empresarial= $row['empresarial'];
        $id_tipo_evento= $row['id_tipo_evento'];
        $indice_plus= setNULL(!empty($row['numero_plus']) ? index_to_number($row['numero_plus']) : null);

        if (empty($id_tipo_evento)) {
            $indice= $empresarial*pow(10,6);
            $sql.= "update tauditorias set indice= $indice, indice_plus= $indice_plus, tidx= $tidx where id = {$row['id']}; ";
        } else {
            $sql.= "update tauditorias, ttipo_eventos set tauditorias.indice= ttipo_eventos.indice, indice_plus= $indice_plus, ";
            $sql.= "tidx= $tidx where tauditorias.id = {$row['id']} and tauditorias.id_tipo_evento = ttipo_eventos.id; ";
        }

        ++$j;
        if ($j >= 1000) {
            $clink->multi_query($sql);
            $j= 0;
            $sql= null;

            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_76 --->  ($_r%)....", $r);
        }
    }

    if ($j)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_76 --->  (100%)....", 1);
    return null;
}

/*
 * arreglar los id_auditorias en las tablsa treg_evento
 */
function set_77() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from teventos where id_auditoria is not null";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $sql.= "update treg_evento set id_auditoria = {$row['id_auditoria']}, id_auditoria_code = '{$row['id_auditoria_code']}' ";
        $sql.= "where id_evento = {$row['id']}; ";

        ++$j;
        if ($j >= 1000) {
            $clink->multi_query($sql);
            $sql= null;
            $j= 0;

            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_77 --->  ($_r%)....", $r);
        }
    }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_77 --->  (100%)....", 1);
    return null;
}

function _test_reg_nota($id_nota, $id_proceso) {
    global $clink;

    $sql= "select * from treg_nota where id_nota = $id_nota and id_proceso = $id_proceso";
    $result= $clink->query($sql);
    $nums= $clink->num_rows($result);
    return $nums > 0 ? true : false;
}
/*
 * garantiza que todas las notas esten en treg_nota
 */
function set_78() {
    global $clink;
    global $cronos;
    global $location;

    $id_usuario= $_SESSION['id_usuario'];
    $estado= _IDENTIFICADO;
    $observacion= "Registrada el hallazgo";

    $sql= "select * from tnotas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $id_nota= setNULL($row['id']);
        $id_nota_code= setNULL_str($row['id_code']);
        $id_auditoria= setNULL($row['id_auditoria']);
        $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
        $id_requisito= setNULL($row['id_requisito']);
        $id_requisito_code= setNULL_str($row['id_requisito_code']);
        $id_lista= setNULL($row['id_lista']);
        $id_lista_code= setNULL_str($row['id_lista_code']);

        $sql= "select id_proceso, id_proceso_code from tproceso_riesgos where id_nota = {$row['id']}";
        $_result= $clink->query($sql);

        while ($_row= $clink->fetch_array($_result)) {
            $id_proceso= $_row['id_proceso'];
            $id_proceso_code= setNULL_str($_row['id_proceso_code']);

            if (_test_reg_nota($row['id'], $id_proceso) == false) {
                $sql= "insert into treg_nota (id_nota, id_nota_code, id_auditoria, id_auditoria_code, id_lista, id_lista_code, ";
                $sql.= "id_requisito, id_requisito_code, id_proceso, id_proceso_code, id_usuario, reg_fecha, ";
                $sql.= "estado, observacion, cronos, situs) values ($id_nota, $id_nota_code, $id_auditoria, $id_auditoria_code, ";
                $sql.= "$id_lista, $id_lista_code, $id_requisito, $id_requisito_code, $id_proceso, $id_proceso_code, $id_usuario, ";
                $sql.= "'{$row['cronos']}', $estado, '$observacion', '{$row['cronos']}', '{$row['situs']}')";
                $clink->query($sql);
            }
        }

        $r= (float)$i / $nums_tb;
        $_r= $r*100; $_r= number_format($_r,1);
        bar_progressCSS(1, "set_78 --->  ($_r%)....", $r);
    }

    return null;
}

/*
 * Incorporar el id_evento a la tabla tusuario evento con los id_tematica not null
 */
function set_79() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "select * from ttematicas";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $sql.= "update tusuario_eventos set id_tematica = {$row['id']}, id_tematica_code = '{$row['id_code']}' ";
        $sql.= "where id_tematica is null and id_evento = {$row['id_evento']}; ";

        ++$i;
        ++$j;
        if ($j >= 1000) {
            $clink->multi_query($sql);
            $j= 0;
            $sql= null;

            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_79 --->  ($_r%)....", $r);
        }
    }

    if ($j)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_79 --->  (100%)....", 1);
    return null;
}

/*
 * A las tareas y a los eventos ponerles el id_proceso local
 */
function set_80() {
    global $clink;
    global $cronos;
    global $location;

    bar_progressCSS(1, "set_80 (set_80 --> *) --->  (0%)....", 0);

    $sql= "update teventos set id_proceso= {$_SESSION['local_proceso_id']}, id_proceso_code= '{$_SESSION['local_proceso_id_code']}' ";
    $sql.= "where situs = '$location' and cronos_syn is null";
    $result= $clink->query($sql);
    bar_progressCSS(1, "set_80 (set_80 --> teventos) --->  (50%)....", 0.5);

    $sql= "update ttareas set id_proceso= {$_SESSION['local_proceso_id']}, id_proceso_code= '{$_SESSION['local_proceso_id_code']}' ";
    $sql.= "where situs = '$location' and cronos_syn is null";
    $result= $clink->query($sql);
    bar_progressCSS(1, "set_80 (set_80 --> ttareas) --->  (80%)....", 0.8);

    $sql= "update ttematicas set id_proceso= {$_SESSION['local_proceso_id']}, id_proceso_code= '{$_SESSION['local_proceso_id_code']}' ";
    $sql.= "where situs = '$location' and cronos_syn is null";
    $result= $clink->query($sql);
    bar_progressCSS(1, "set_80 (set_80 --> tauditorias) --->  (100%)....", 1);

    return null;
}

function _create_treg_evento_year($year) {
    global $clink;

    $sql= "CREATE TABLE treg_evento_$year ( ";
    $sql.= "id int(11) NOT NULL AUTO_INCREMENT, ";
    $sql.= "id_evento int(11) NOT NULL, ";
    $sql.= "id_evento_code char(10) DEFAULT NULL, ";
    $sql.= "id_usuario int(11) DEFAULT NULL, ";
    $sql.= "id_responsable int(11) DEFAULT NULL, ";
    $sql.= "origen_data text DEFAULT NULL, ";
    $sql.= "aprobado datetime DEFAULT NULL, ";
    $sql.= "rechazado datetime DEFAULT NULL, ";
    $sql.= "cumplimiento smallint(6) NOT NULL DEFAULT 1, ";
    $sql.= "observacion longtext DEFAULT NULL, ";
    $sql.= "compute tinyint(1) NOT NULL DEFAULT 1, ";
    $sql.= "toshow tinyint(4) NOT NULL DEFAULT 1, ";
    $sql.= "user_check tinyint(1) DEFAULT NULL, ";
    $sql.= "id_tarea int(11) DEFAULT NULL, ";
    $sql.= "id_tarea_code char(10) DEFAULT NULL, ";
    $sql.= "id_auditoria int(11) DEFAULT NULL, ";
    $sql.= "id_auditoria_code char(10) DEFAULT NULL, ";
    $sql.= "reg_fecha date DEFAULT NULL, ";
    $sql.= "horas mediumint(9) DEFAULT NULL, ";
    $sql.= "cronos datetime NOT NULL, ";
    $sql.= "cronos_syn datetime DEFAULT NULL, ";
    $sql.= "situs char(2) DEFAULT NULL, ";
    $sql.= "outlook tinyint(1) DEFAULT NULL, ";
    $sql.= "PRIMARY KEY (id), ";
    $sql.= "UNIQUE KEY id_usuario_index_{$year} (id_usuario, cumplimiento, observacion(255), id_evento_code, rechazado, aprobado), ";
    $sql.= "KEY id_evento_{$year} (id_evento), ";
    $sql.= "KEY id_responsable_{$year} (id_responsable), ";
    $sql.= "CONSTRAINT treg_evento_{$year}_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT treg_evento_{$year}_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE CASCADE ON UPDATE CASCADE ";
    $sql.= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

    $result= $clink->query($sql);
    if (!is_null($clink->error())) {
        echo "$sql <br/><br/>";
        $error= $clink->error();
        return "$error  <br/><br/>";
    }

    return null;
}

function _create_tusuario_eventos_year($year) {
    global $clink;

    $sql= "CREATE TABLE tusuario_eventos_$year ( ";
    $sql.= "id int(11) NOT NULL AUTO_INCREMENT, ";
    $sql.= "id_evento int(11) DEFAULT NULL, ";
    $sql.= "id_evento_code char(10) COLLATE utf8_spanish_ci DEFAULT NULL, ";
    $sql.= "id_tarea int(11) DEFAULT NULL, ";
    $sql.= "id_tarea_code char(10) COLLATE utf8_spanish_ci DEFAULT NULL, ";
    $sql.= "id_auditoria int(11) DEFAULT NULL, ";
    $sql.= "id_auditoria_code char(10) COLLATE utf8_spanish_ci DEFAULT NULL, ";
    $sql.= "id_tematica int(11) DEFAULT NULL, ";
    $sql.= "id_tematica_code char(10) COLLATE utf8_spanish_ci DEFAULT NULL, ";
    $sql.= "id_usuario int(11) DEFAULT NULL, ";
    $sql.= "id_grupo int(11) DEFAULT NULL, ";
    $sql.= "aprobado datetime DEFAULT NULL, ";
    $sql.= "indirect tinyint(1) DEFAULT NULL, ";
    $sql.= "cronos datetime NOT NULL, ";
    $sql.= "cronos_syn datetime DEFAULT NULL, ";
    $sql.= "situs char(2) COLLATE utf8_spanish_ci DEFAULT NULL, ";
    $sql.= "PRIMARY KEY (id), ";
    $sql.= "UNIQUE KEY evento_usuario_{$year}_index (id_usuario, id_grupo, id_evento_code, id_tarea_code, id_auditoria_code), ";
    $sql.= "KEY id_usuario_{$year} (id_usuario), ";
    $sql.= "KEY id_evento_{$year} (id_evento), ";
    $sql.= "KEY id_grupo_{$year} (id_grupo), ";
    $sql.= "KEY id_tarea_{$year} (id_tarea), ";
    $sql.= "KEY tusuario_eventos_{$year}_fk4 (id_auditoria), ";
    $sql.= "KEY tusuario_eventos_{$year}_fk5 (id_tematica), ";
    $sql.= "CONSTRAINT tusuario_eventos_{$year}_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tusuario_eventos_{$year}_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tusuario_eventos_{$year}_fk2 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tusuario_eventos_{$year}_fk3 FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tusuario_eventos_{$year}_fk4 FOREIGN KEY (id_auditoria) REFERENCES tauditorias (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tusuario_eventos_{$year}_fk5 FOREIGN KEY (id_tematica) REFERENCES ttematicas (id) ON DELETE CASCADE ON UPDATE CASCADE ";
    $sql.= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

    $result= $clink->query($sql);
    if (!is_null($clink->error())) {
        echo "$sql <br/><br/>";
        $error= $clink->error();
        return "$error  <br/><br/>";
    }

    return null;
}

function _create_tproceso_eventos_year($year) {
    global $clink;

    $sql= "CREATE TABLE tproceso_eventos_$year ( ";
    $sql.= "id int(11) NOT NULL AUTO_INCREMENT, ";
    $sql.= "id_evento int(11) DEFAULT NULL, ";
    $sql.= "id_evento_code char(10) DEFAULT NULL, ";
    $sql.= "toshow tinyint(4) DEFAULT NULL, ";
    $sql.= "id_tarea int(11) DEFAULT NULL, ";
    $sql.= "id_tarea_code char(10) DEFAULT NULL, ";
    $sql.= "id_responsable int(11) DEFAULT NULL, ";
    $sql.= "origen_data text DEFAULT NULL, ";
    $sql.= "id_auditoria int(11) DEFAULT NULL, ";
    $sql.= "id_auditoria_code char(10) DEFAULT NULL, ";
    $sql.= "id_proceso int(11) DEFAULT NULL, ";
    $sql.= "id_proceso_code char(10) DEFAULT NULL, ";
    $sql.= "cumplimiento tinyint(2) DEFAULT NULL, ";
    $sql.= "evaluado datetime DEFAULT NULL, ";
    $sql.= "evaluacion longtext DEFAULT NULL, ";
    $sql.= "id_responsable_eval int(11) DEFAULT NULL, ";
    $sql.= "cronos datetime NOT NULL, ";
    $sql.= "cronos_syn datetime DEFAULT NULL, ";
    $sql.= "situs char(2) DEFAULT NULL, ";
    $sql.= "PRIMARY KEY (id), ";
    $sql.= "UNIQUE KEY evento_evaluacion_index_{$year} (id_evento_code, id_tarea_code, id_auditoria_code, id_proceso_code, cumplimiento, evaluado, evaluacion(255)), ";
    $sql.= "KEY id_proceso_{$year} (id_proceso), ";
    $sql.= "KEY tproceso_eventos_{$year}_fk1 (id_evento), ";
    $sql.= "KEY tproceso_eventos_{$year}_fk2 (id_responsable), ";
    $sql.= "KEY tproceso_eventos_{$year}_fk3 (id_auditoria), ";
    $sql.= "KEY tproceso_eventos_{$year}_fk4 (id_responsable_eval), ";
    $sql.= "KEY tproceso_eventos_fk5 (id_tarea), ";
    $sql.= "CONSTRAINT tproceso_eventos_{$year}_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tproceso_eventos_{$year}_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tproceso_eventos_{$year}_fk2 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tproceso_eventos_{$year}_fk3 FOREIGN KEY (id_auditoria) REFERENCES tauditorias (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tproceso_eventos_{$year}_fk4 FOREIGN KEY (id_responsable_eval) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE, ";
    $sql.= "CONSTRAINT tproceso_eventos_{$year}_fk5 FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON DELETE CASCADE ON UPDATE CASCADE ";
    $sql.= ") ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";

    $result= $clink->query($sql);
    if (!is_null($clink->error())) {
        echo "$sql <br/><br/>";
        $error= $clink->error();
        return "$error  <br/><br/>";
    }

    return null;
}

function _fill_treg_evento_year($result_evento, $year, $nums_tb) {
    global $clink;

    $i= 0;
    $j= 0;
    $k= 0;
    $clink->data_seek($result_evento);
    while ($row_evento= $clink->fetch_array($result_evento)) {
        ++$i;
        $sql= "select * from treg_evento where id_evento = {$row_evento['id']}";
        $result= $clink->query($sql);

        $sql= null;
        $j= 0;
        while ($row= $clink->fetch_array($result)) {
            $id_evento= setNULL($row['id_evento']);
            $id_evento_code= setNULL_str($row['id_evento_code']);
            $id_usuario= setNULL($row['id_usuario']);
            $id_responsable= setNULL($row['id_responsable']);
            $origen_data= setNULL_str($row['origen_data']);
            $aprobado= setNULL_str($row['aprobado']);
            $rechazado= setNULL_str($row['rechazado']);
            $cumplimiento= setNULL($row['cumplimiento']);
            $observacion= setNULL_str($row['observacion']);
            $compute= setNULL($row['compute']);
            $toshow= setNULL($row['toshow']);
            $user_check= boolean($row['user_check']);
            $id_tarea= setNULL($row['id_tarea']);
            $id_tarea_code= setNULL_str($row['id_tarea_code']);
            $id_auditoria= setNULL($row['id_auditoria']);
            $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
            $reg_fecha= setNULL_str($row['reg_fecha']);
            $horas= setNULL($row['horas']);
            $cronos= setNULL_str($row['cronos']);
            $cronos_syn= setNULL_str($row['cronos_syn']);
            $situs= setNULL_str($row['situs']);
            $outlook= boolean($row['outlook']);

            $sql.= "insert into treg_evento_$year (id_evento, id_evento_code, id_usuario, id_responsable, origen_data, aprobado, rechazado, ";
            $sql.= "cumplimiento, observacion, compute, toshow, user_check, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "reg_fecha, horas, cronos, cronos_syn, situs, outlook) values ($id_evento, $id_evento_code, $id_usuario, $id_responsable, ";
            $sql.= "$origen_data, $aprobado, $rechazado, $cumplimiento, $observacion, $compute, $toshow, $user_check, $id_tarea, ";
            $sql.= "$id_tarea_code, $id_auditoria, $id_auditoria_code, $reg_fecha, $horas, $cronos, $cronos_syn, $situs, $outlook); ";

            ++$j;
            if ($j >= 1000) {
                $clink->multi_query($sql);
                $sql= null;
                $j= 0;
            }
        }

        if ($j)
            $clink->multi_query($sql);

        ++$k;
        if ($k >= 1000) {
            $k= 0;
            $r= (float)($i) / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_79 (treg_evento_$year) --->  ($_r%)....", $r);
        }
    }

    return null;
}

function get_chain_tematicas($id) {
    global $clink;

    $sql= "select id from ttematicas where id_evento = $id";
    $result= $clink->query($sql);
    $nums= $clink->num_rows($result);
    if (empty($nums))
        return null;

    $i= 0;
    $ids= "";
    while ($row= $clink->fetch_array($result)) {
        ++$i;
        $ids.= $i > 1 ? "," : "";
        $ids.= $row[0];
    }
    return "($ids)";
}

function get_auditorias_array($year, $equal) {
    global $clink;

    $sign= $equal ? "=" : "<=";
    $array= array();

    $sql= "select id, year(fecha_inicio_plan) as _year from tauditorias where year(fecha_inicio_plan) $sign $year ";
    $sql.= "and id not in (select id_auditoria from teventos where id_auditoria is not null and year(fecha_inicio_plan) $sign $year) ";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $sql= "select id from tauditorias where id_auditoria = {$row['id']} ";
        $_result= $clink->query($sql);

        while ($_row= $clink->fetch_array($_result))
            $array[$_row['id']]= $row['id'];
    }
    return $array;
}

function _fill_tusuario_eventos_year($result_evento, $year, $array_auditorias, $nums_tb) {
    global $clink;

    $i= 0;
    $j= 0;
    $k= 0;
    $array_ids= array();
    $clink->data_seek($result_evento);
    while ($row_evento= $clink->fetch_array($result_evento)) {
        ++$i;

        $sql= null;
        if (!empty($row_evento['id'])) {
            $sql.= "select * from tusuario_eventos where id_evento = {$row_evento['id']} ";
        }
        if (!empty($row_evento['id_auditoria']))  {
            $sql.= $sql ? "union " : "";
            $sql.= "select * from tusuario_eventos where id_evento is null and id_auditoria = {$row_evento['id_auditoria']} ";

            if ($array_auditorias[$row_evento['id_auditoria']]) {
                $sql.= $sql ? "union " : "";
                $sql.= "select * from tusuario_eventos where id_evento is null and id_auditoria = {$array_auditorias[$row_evento['id_auditoria']]} ";
            }
        }
        if (!empty($row_evento['id_tarea']))  {
            $sql.= $sql ? "union " : "";
            $sql.= "select * from tusuario_eventos where id_evento is null and id_tarea = {$row_evento['id_tarea']} ";
        }
        if ($row_evento['id_tipo_reunion']) {
            $ids_matter= get_chain_tematicas($row_evento['id']);
            if ($ids_matter) {
                $sql.= $sql ? "union " : "";
                $sql.= "select * from tusuario_eventos where id_evento is null and id_tematica in {$ids_matter} ";
        }   }
        $sql.= "order by id asc ";

        $result= $clink->query($sql);

        $sql= null;
        $j= 0;
        while ($row= $clink->fetch_array($result)) {
            if ($array_ids[$row['id']])
                continue;
            $array_ids[$row['id']]= 1;

            $id_evento= setNULL($row['id_evento']);
            $id_evento_code= setNULL_str($row['id_evento_code']);
            $id_tarea= setNULL($row['id_tarea']);
            $id_tarea_code= setNULL_str($row['id_tarea_code']);
            $id_auditoria= setNULL($row['id_auditoria']);
            $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
            $id_tematica= setNULL($row['id_tematica']);
            $id_tematica_code= setNULL_str($row['id_tematica_code']);
            $id_usuario= setNULL($row['id_usuario']);
            $id_grupo= setNULL($row['id_grupo']);
            $aprobado= setNULL_str($row['aprobado']);
            $indirect= boolean($row['indirect']);
            $cronos= setNULL_str($row['cronos']);
            $cronos_syn= setNULL_str($row['cronos_syn']);
            $situs= setNULL_str($row['situs']);

            $sql.= "insert into tusuario_eventos_$year (id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "id_tematica, id_tematica_code, id_usuario, id_grupo, aprobado, indirect, cronos, cronos_syn, situs) ";
            $sql.= "values ($id_evento, $id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, $id_tematica, ";
            $sql.= "$id_tematica_code, $id_usuario, $id_grupo, $aprobado, $indirect, $cronos, $cronos_syn, $situs); ";

            ++$j;
            if ($j >= 1000) {
                $clink->multi_query($sql);
                $sql= null;
                $j= 0;
            }
        }

        if ($j)
            $clink->multi_query($sql);

        ++$k;
        if ($k >= 1000) {
            $k= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_79 (tusuario_eventos_$year) --->  ($_r%)....", $r);
        }
    }

    return null;
}

function _fill_proceso_eventos_year($result_evento, $year, $array_auditorias, $nums_tb) {
    global $clink;

    $i= 0;
    $j= 0;
    $k= 0;
    $array_ids= array();
    $clink->data_seek($result_evento);
    while ($row_evento= $clink->fetch_array($result_evento)) {
        ++$i;
        $sql= null;
        if (!empty($row_evento['id'])) {
            $sql.= "select * from tproceso_eventos where id_evento = {$row_evento['id']} ";
        }
        if (!empty($row_evento['id_auditoria']))  {
            $sql.= $sql ? "union " : "";
            $sql.= "select * from tproceso_eventos where id_evento is null and id_auditoria = {$row_evento['id_auditoria']} ";

            if ($array_auditorias[$row_evento['id_auditoria']]) {
                $sql.= $sql ? "union " : "";
                $sql.= "select * from tproceso_eventos where id_evento is null and id_auditoria = {$array_auditorias[$row_evento['id_auditoria']]} ";
            }
        }
        if (!empty($row_evento['id_tarea']))  {
            $sql.= $sql ? "union " : "";
            $sql.= "select * from tproceso_eventos where id_evento is null and id_tarea = {$row_evento['id_tarea']} ";
        }
        $sql.= "order by id asc ";

        $result= $clink->query($sql);

        $sql= null;
        $j= 0;
        while ($row= $clink->fetch_array($result)) {
            if ($array_ids[$row['id']])
                continue;
            $array_ids[$row['id']]= 1;

            $id_evento= setNULL($row['id_evento']);
            $id_evento_code= setNULL_str($row['id_evento_code']);
            $toshow= setNULL($row['toshow']);
            $id_tarea= setNULL($row['id_tarea']);
            $id_tarea_code= setNULL_str($row['id_tarea_code']);
            $id_responsable= setNULL($row['id_responsable']);
            $id_auditoria= setNULL($row['id_auditoria']);
            $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
            $id_proceso= setNULL($row['id_proceso']);
            $id_proceso_code= setNULL_str($row['id_proceso_code']);
            $cumplimiento= setNULL($row['cumplimiento']);
            $evaluado= setNULL_str($row['evaluado']);
            $evaluacion= setNULL_str($row['evaluacion']);
            $id_responsable_eval= setNULL($row['id_responsable_eval']);
            $cronos= setNULL_str($row['cronos']);
            $cronos_syn= setNULL_str($row['cronos_syn']);
            $situs= setNULL_str($row['situs']);

            $sql.= "insert into tproceso_eventos_$year (id_evento, id_evento_code, toshow, id_tarea, id_tarea_code, id_responsable, ";
            $sql.= "id_auditoria, id_auditoria_code, id_proceso, id_proceso_code, cumplimiento, evaluado, evaluacion, id_responsable_eval, ";
            $sql.= "cronos, cronos_syn, situs) values ($id_evento, $id_evento_code, $toshow, $id_tarea, $id_tarea_code, $id_responsable, ";
            $sql.= "$id_auditoria, $id_auditoria_code, $id_proceso, $id_proceso_code, $cumplimiento, $evaluado, $evaluacion, $id_responsable_eval, ";
            $sql.= "$cronos, $cronos_syn, $situs); ";

            ++$j;
            if ($j >= 1000) {
                $clink->multi_query($sql);
                $sql= null;
                $j= 0;
            }
        }

        if ($j)
            $clink->multi_query($sql);

        ++$k;
        if ($k >= 1000) {
            $k= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_79 (tproceso_eventos_$year)--->  ($_r%)....", $r);
        }
    }

    return null;
}

function _fill_year($year, $equal, &$nums_tb) {
    global $clink;

    $sign= $equal ? "=" : "<=";
    $sql= "select * from teventos where year(fecha_inicio_plan) $sign $year ";
    $result_evento= $clink->query($sql);
    $nums= $clink->num_rows($result);
    $nums_tb+= $nums;

    $array_auditorias= get_auditorias_array($year, $equal);

    _fill_treg_evento_year($result_evento, $year, $nums);
    _fill_tusuario_eventos_year($result_evento, $year, $array_auditorias, $nums);
    _fill_proceso_eventos_year($result_evento, $year, $array_auditorias, $nums);
}

/*
 * Crear las tablas divididas por annos a partir del 2017
 */
function set_81() {
    global $clink;
    global $cronos;
    global $location;

    $result= $clink->query("describe treg_evento_2017");
    if ($result)
        if ($clink->num_rows($result) > 1)
            return;

    $nums_tb= 0;

    $year= date('Y');
    for ($_year= 2017; $_year <= $year; $_year++) {
        $error= _create_treg_evento_year($_year);
        if (!is_null($error))
            return $error;
        $error= _create_tusuario_eventos_year($_year);
        if (!is_null($error))
            return $error;
        $error= _create_tproceso_eventos_year($_year);
        if (!is_null($error))
            return $error;
        _fill_year($_year, false, $nums_tb);
    }

    return null;
}
/*
 * Preparar la tabla torganismos
 */
function set_82() {
    global $clink;
    global $cronos;
    global $location;

    $sql= "update torganismos set id_code= REPLACE(id_code, 'XX', '$location')";
    $result= $clink->query($sql);

    $sql= "update torganismos set situs= REPLACE(situs, 'XX', '$location')";
    $result= $clink->query($sql);

    return null;
}

/*
 * corregir el error de set_82
 */
function set_83() {
    return set_82();
}
/*
 * crear las tablas para el 2020
 */

function _set_84 ($year) {
    global $clink;
    global $cronos;
    global $location;

    $result= $clink->query("describe treg_evento_$year");
    if ($result) {
        if ($clink->num_rows($result) > 1)
            return null;
    }

    _create_treg_evento_year($year);
    _create_tusuario_eventos_year($year);
    _create_tproceso_eventos_year($year);

    $sql= "select * from teventos where year(fecha_inicio_plan) = $year";
    $result= $clink->query($sql);

    while ($row= $clink->fetch_array($result)) {
        $id= $row['id'];
        $id_code= setNULL_str($row['id_code']);
        $id_tarea= setNULL($row['id_tarea']);
        $id_tarea_code= setNULL_str($row['id_tarea_code']);
        $id_auditoria= setNULL($row['id_auditoria']);
        $id_auditoria_code= setNULL_str($row['id_auditoria_code']);

        $sql= "insert into treg_evento_$year (id_evento, id_evento_code, id_auditoria, id_auditoria_code, id_tarea, id_tarea_code, ";
        $sql.= "id_usuario, id_responsable, cronos, situs) values ($id, $id_code, $id_auditoria, $id_auditoria_code, $id_tarea, $id_tarea_code, ";
        $sql.= "{$row['id_responsable']}, {$row['id_usuario']}, '$cronos', '$location'); ";

        $sql.= "insert into tusuario_eventos_$year (id_evento, id_evento_code, id_auditoria, id_auditoria_code, id_tarea, id_tarea_code, ";
        $sql.= "id_usuario, cronos, situs) values ($id, $id_code, $id_auditoria, $id_auditoria_code, $id_tarea, $id_tarea_code, ";
        $sql.= "{$row['id_responsable']}, '$cronos', '$location'); ";

        $sql.= "insert into tproceso_eventos_$year (id_evento, id_evento_code, id_auditoria, id_auditoria_code, id_tarea, id_tarea_code, ";
        $sql.= "id_proceso, id_proceso_code, cronos, situs) values ($id, $id_code, $id_auditoria, $id_auditoria_code, $id_tarea, $id_tarea_code, ";
        $sql.= "{$_SESSION['local_proceso_id']}, '{$_SESSION['local_proceso_id_code']}', '$cronos', '$location'); ";

        $clink->multi_query($sql);
    }
}

function set_84() {
    global $clink;
    global $cronos;
    global $location;

    $year= date('Y');
    for ($_year= 2020; $_year <= $year; $_year++) {
        _set_84($_year);
    }

    return null;
}

/*
 * cambiar el texto de las observacion del evento que deriva de una tarea
 */
function get_texttitle_tarea($id) {
    global $clink;

    $sql= "select id_proyecto from ttareas where id = $id";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);
    $id_proyecto= $row['id_proyecto'];

    $sql= "select id_riesgo, id_nota from triesgo_tareas where id_tarea =  $id";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);
    $id_riesgo= $row['id_riesgo'];
    $id_nota= $row['id_nota'];

    $sql= "select id_programa from tref_programas where id_tarea =  $id";
    $result= $clink->query($sql);
    $row= $clink->fetch_array($result);
    $id_programa= $row['id_programa'];

    $texttitle= null;
    $plus= null;

    if (!empty($id_riesgo)) {
        $plus= $texttitle.= !empty($texttitle) ? ", " : null;
        $texttitle.= $plus."RIESGO";
    }
    if (!empty($id_nota)) {
        $plus= $texttitle.= !empty($texttitle) ? ", " : null;
        $texttitle.= $plus."NOTA";
    }
    if (!empty($id_proyecto)) {
        $plus= $texttitle.= !empty($texttitle) ? ", " : null;
        $texttitle.= $plus."PROYECTO";
    }
    if (!empty($id_programa)) {
        $plus= $texttitle.= !empty($texttitle) ? ", " : null;
        $texttitle.= $plus."PROGRAMA";
    }
    if (is_null($texttitle))
        $texttitle= "TAREA";

    return $texttitle;
}

function set_85() {
    global $clink;

    $sql= "select id, id_tarea from teventos where id_tarea is not null";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        $observacion= "Se originó a partir de una ".get_texttitle_tarea($row['id_tarea']);
        $sql.= "update teventos set descripcion= '$observacion', lugar= '$observacion' where id = {$row['id']}; ";

        ++$i;
        ++$j;
        if ($j >= 100) {
            $clink->multi_query($sql);
            $j= 0;
            $sql= null;

            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_85 --->  ($_r%)....", $r);
        }
    }

    if ($j) {
        $clink->multi_query($sql);
        bar_progressCSS(1, "set_85 --->  (100%)....", 1);
    }

    return null;
}

/*
 * Reparar la tabla tdocumentos que no incorporo los id_code
 */
function set_86() {
    global $clink;

    $obj_code= new Tcode($clink);

    $sql= "select * from tdocumentos";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        if (!empty($row['id_code']) && strlen($row['id_code']) > 0)
            continue;
        $id_code= build_code($row['id']);

        $sql= null;
        $sql.= "update tdocumentos set id_code='$id_code' where id = {$row['id']}; ";
        $sql.= "update tref_documentos set id_documento_code= '$id_code' where id_documento= {$row['id']}; ";
        $sql.= "update tusuario_documentos set id_documento_code= '$id_code' where id_documento= {$row['id']}; ";
        $clink->multi_query($sql);

        ++$j;
        ++$i;
        if ($j >= 100) {
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_86 --->  ($_r%)....", $r);
        }
    }

    bar_progressCSS(1, "set_86 --->  (100%)....", 1);
    return null;
}

/*
 * corregir los registros de treg_evento que tienen el id_responsable y el id_usuario en null
 */
function test_treg_evento_year($id_evento, $id_responsable, $year) {
    global $clink;

    $sql= "select * from treg_evento_$year where id_evento = $id_evento and id_usuario = $id_responsable";
    $result= $clink->query($sql);
    return $clink->num_rows($result);
}

function set_87() {
    global $clink;
    $array= array();

    $sql= "select * from tprocesos";
    $result= $clink->query($sql);
    while ($row= $clink->fetch_array($result))
        $array_prs[$row['id']]= $row['id_responsable'];

    $sql= "select *, year(fecha_inicio_plan) as _year from teventos ";
    $sql.= "where year(fecha_inicio_plan) = 2019 and id_proceso != {$_SESSION['local_proceso_id']} ";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;

        if (!test_treg_evento_year($row['id'], $row['id_responsable'], $row['_year'])) {
            $id_evento= setNULL($row['id']);
            $id_evento_code= setNULL_str($row['id_code']);
            $id_tarea= setNULL($row['id_tarea']);
            $id_tarea_code= setNULL_str($row['id_tarea_code']);
            $id_auditoria= setNULL($row['id_auditoria']);
            $id_auditoria_code= setNULL_str($row['id_auditoria_code']);
            $year= $row['_year'];

            $sql.= "insert into treg_evento_$year (id_evento, id_evento_code, id_tarea, id_tarea_code, id_auditoria, id_auditoria_code, ";
            $sql.= "id_usuario, id_responsable, user_check, hide_synchro, cronos, cronos_syn, situs) values ($id_evento, ";
            $sql.= "$id_evento_code, $id_tarea, $id_tarea_code, $id_auditoria, $id_auditoria_code, {$row['id_responsable']}, ";
            $sql.= _USER_SYSTEM.", true, true, '{$row['cronos']}', '{$row['cronos_syn']}', '{$row['situs']}'); ";

            ++$j;
            if ($j >= 100) {
                $clink->multi_query($sql);
                $sql= null;
                $j= 0;

                $r= (float)$i / $nums_tb;
                $_r= $r*100; $_r= number_format($_r,1);
                bar_progressCSS(1, "set_87 --->  ($_r%)....", $r);
            }
        }
    }

    if ($sql)
        $clink->multi_query($sql);

    bar_progressCSS(1, "set_87 --->  (100%)....", 1);
    return null;
}

/*
 * Hacer que los responsables de las tematicas estenn referidos a la tabla tasistencias
 */
function _insert_ttematicas($array) {
    global $clink;
    global $cronos;
    $obj_code= new Tcode($clink);

    $sql= "insert into tasistencias (id_usuario, id_evento, id_evento_code, id_proceso, id_proceso_code, cronos, situs) ";
    $sql.= "values ({$array['id_usuario']}, {$array['id_evento']}, '{$array['id_evento_code']}', {$_SESSION['local_proceso_id']}, ";
    $sql.= "'{$_SESSION['local_proceso_id_code']}', '$cronos', '{$_SESSION['location']}')";
    $result= $clink->query($sql);

    $id= $clink->inserted_id("tasistencias");
    $id_code= $obj_code->set_code("tasistencias", "id", "id_code", $id, $_SESSION['location']);
    return array($id, $id_code);
}

function set_88() {
    global $clink;
    $array= array();

    $sql= "select id, id_asistencia_resp, id_evento, id_evento_code from ttematicas ";
    $sql.= "where (ifaccords is null or ifaccords = ". boolean2pg(0).") and id_proceso = {$_SESSION['local_proceso_id']}";
    $result= $clink->query($sql);
    $nums_tb = $clink->num_rows($result);

    $i= 0;
    $j= 0;
    $sql= null;
    while ($row= $clink->fetch_array($result)) {
        ++$i;

        $sql= "select id, id_code from tasistencias where id_evento = {$row['id_evento']} and id_usuario = {$row['id_asistencia_resp']} ";
        $_result= $clink->query($sql);
        $_row= $clink->fetch_array($_result);

        $id= $_row['id'];
        $id_code= $_row['id_code'];

        if (empty($id)) {
            $array= array('id_usuario'=>$row['id_asistencia_resp'], 'id_evento'=>$row['id_evento'], 'id_evento_code'=>$row['id_evento_code']);
            $_array= _insert_ttematicas($array);
            $id= $_array[0];
            $id_code= $_array[1];
        }

        $sql= "update ttematicas set id_asistencia_resp= $id, id_asistencia_resp_code= '$id_code' ";
        $sql.= "where id = {$row['id']}";
        $clink->query($sql);

        ++$j;
        if ($j >= 100) {
            $j= 0;
            $r= (float)$i / $nums_tb;
            $_r= $r*100; $_r= number_format($_r,1);
            bar_progressCSS(1, "set_88 --->  ($_r%)....", $r);
        }
    }

    $sql= "ALTER TABLE ttematicas ADD CONSTRAINT ttematicas_fk1 FOREIGN KEY (id_asistencia_resp) ";
    $sql.= "REFERENCES tasistencias (id) ON DELETE SET NULL ON UPDATE CASCADE";
    $clink->query($sql);

    bar_progressCSS(1, "set_88 --->  (100%)....", 1);
    return null;
}
