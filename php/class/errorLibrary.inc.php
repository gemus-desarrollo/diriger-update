<?php
function errorLibrary($err, $function, $class) {
    if (is_null($err)) 
        return null;

	$text= null;

	if (!strcasecmp($class,'Tusuario') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'nombre') !== false)
                $text.= "Ya existe estos nombre y apellidos. Esta información no se acepta duplicada.";
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'noIdentidad') !== false)
                $text.= "Ya existe este número de identidad. Esta información no se acepta duplicada.";
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'usuario') !== false)
                $text.= "Ya existe este nombre de usuario. Esta información no se acepta duplicada.";
	}
	if (!strcasecmp($class,'Tgrupo') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'nombre') !== false)
                $text.= "Ya existe un grupo con este nombre. Esta información no se acepta duplicada.";
	}
	if (!strcasecmp($class,'Tproceso') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'nombre') !== false)
                $text.= "Ya existe un proceso o dirección organizativa con este nombre. Esta información no se acepta duplicada.";
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'email') !== false)
                $text.= "Ya existe registrada esta dirección de correo electrónico ara otro procso dirección. Esta información no se acepta duplicada.";
	}
	if (!strcasecmp($class,'Tescenario') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'escenario') !== false)
                $text.= "En este mismo periodo al proceso o Dirección ya se le ha definido un escenario.  Ninguna organización gestiona más de un escenario en el mismo periodo.";
	}
	if (!strcasecmp($class,'Tpolitica') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'titulo') !== false)
                $text.= "Este lineamiento o política de trabajo ya está en el sistema. No se admiten lineamientos duplicados.";
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'titulo') !== false)
                $text.= "En el sistema ya está definido un título para este capitulo o/y epigrafe.";
	}
	if (!strcasecmp($class,'Tobjetivo') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'objetivo') !== false)
                $text.= "Este objetivo estratégico ya está en el sistema. No se admiten objetivos repetidos para un mismo proceso o Dirección organizativa para un mismo periodo.";
	}

	if (!strcasecmp($class,'Tperspectiva') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'perspectiva_proceso') !== false)
                $text.= "Ya existe una Perspectiva con este nombre. No se admiten nombres de perspectiva repetidos para un mismo proceso o Dirección organizativa para un mismo periodo.";

            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'color') !== false)
                $text.= "Este color está siendo usado otra Perspectiva. No se admiten perspectivas con el mismo color, para el mismo proceso o Dirección Organizativa.";
	}
	if (!strcasecmp($class,'Tinductor') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'inductor_proceso') !== false)
                $text.= "Ya existe un Objetivo de Trabajo con este nombre. No se admiten nombres de Objetivos de Trabajo repetidos para un mismo proceso o Dirección organizativa para un mismo periodo.";
	}
	if (!strcasecmp($class,'Tindicador') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'indicador_proceso') !== false)
                $text.= "Ya existe un Indicador con este nombre. No se admiten nombres de Indicadores repetidos para un mismo proceso o Dirección organizativa para un mismo periodo.";
	}
	if (!strcasecmp($class,'Ttablero') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'tablero_nombre') !== false)
                $text.= "Ya existe un Tablero de Control con este nombre. No se admiten nombres de Tableros repetidos.";
	}
	if (!strcasecmp($class,'Tprograma') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
            if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'nombre') !== false)
                $text.= "Ya existe un Programa con este nombre. No se admiten nombres de Programas repetidos para un mismo proceso o Dirección organizativa para un mismo periodo.";
	}

    if (!strcasecmp($class,'Ttipo_evento') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
        if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'numero') !== false)
            $text.= "Ya existe este número asignado a un subcapitulo del Plan Anual. No se admiten números repetidos.";
    }

    if (!strcasecmp($class,'Tevento') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
        if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'nombre') !== false)
            $text.= "Ya esta creada esta tarea o actividad en el mismo lugar y a la misma hora.";
        if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'numero') !== false)
            $text.= "Ya esta creada una tarea o actividad con el mismo número.";
    }
    if (!strcasecmp($class,'Triesgo') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
        if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'nombre') !== false)
            $text.= "Ya esta definido este riesgo. No se admite el riesgo repetido";
    }
    if (!strcasecmp($class,'Tpersona') && !strcasecmp($function,'eliminar')) {
        if ((stripos($err,'delete') !== false || stripos($err,'duplicada') !== false) && stripos($err,'tpersonas') !== false)
            $text.= "Este remitente o destinatario esta vinculado a un registro de entrada o de salidad. No puede ser eliminado.";
    }
    if (!strcasecmp($class,'Tproyecto') && (!strcasecmp($function,'add') || !strcasecmp($function,'update'))) {
        if ((stripos($err,'duplicate') !== false || stripos($err,'duplicada') !== false) && stripos($err,'nombre') !== false)
            $text.= "Ya esta definido ese proyecto en el sistema.";
    }
    if (!strcasecmp($function, '_delete_if_empty_audit')) {
        if (stripos($err,'tauditorias') !== false && stripos($err,'nota') !== false)
            $text.= "No se puede eliminar esta auditoria o supervision porque contiene al menos una nota de hallazgo asociada.";
    }
    return is_null($text) ? $err : $text;
}

?>