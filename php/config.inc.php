<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 4/02/15
 * Time: 18:16
 */

header ("Expires: Fri, 14 Mar 1980 20:53:00 GMT"); //la pagina expira en fecha pasada
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); //ultima actualizacion ahora cuando la cargamos
header ("Cache-Control: no-cache, must-revalidate"); //no guardar en CACHE
header ("Pragma: no-cache"); //PARANOIA, NO GUARDAR EN CACHE

ini_set("display_errors", "On");
ini_set("error_reporting", E_ERROR | E_PARSE);
//ini_set("error_reporting", E_ALL); // para el desarrollo

ini_set("allow_url_include", "On");
ini_set("date.timezone", "America/Havana");
ini_set("max_input_vars", "10000");

$SQL_texttypes= array('CHAR', 'CHARACTER', 'VARCHAR', 'CHARACTER VARYING', 'TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT', 'STRING',
                        'char', 'character', 'varchar', 'character varying', 'tinytext', 'text', 'mediumtext', 'longtext', 'string');

$SQL_blobtypes= array('TINYBLOB', 'MEDIUMBLOB', 'BLOB', 'LONGBLOB', 'BYTEA', 
                        'tinyblob', 'mediumblob', 'blob', 'longblob', 'bytea');

$SQL_timetypes= array('DATE', 'TIME', 'DATETIME', 'TIMESTAMP', 'TIMESTAMP WITHOUT TIME ZONE', 'TIME WITHOUT TIME ZONE',
                        'date', 'time', 'datetime', 'timestamp', 'timestamp without time zone', 'time without time zone');

$SQL_numtypes= array('YEAR', 'MONTH', 'SMALLINT','TINYINT','MEDIUMINT','INT', 'INTEGER', 'LONGINT', 'DECIMAL','FLOAT',
                        'year', 'month', 'smallint','tinyint','mediumint','int', 'integer', 'longint', 'decimal','float',
                    'DOUBLE', 'INT2', 'INT4', 'INT8', 'BIGINT', 'SERIAL', 'BIGSERIAL', 'REAL', 'DOUBLE PRECISION',
                        'double', 'int2', 'int4', 'int8', 'bigint', 'serial', 'bigserial', 'real', 'double precision');
					
$SQL_booltypes= array("TINYINT(1)", "BOOLEAN", "TINYINT", "tinyint(1)", "boolean", "tinyint");
		
define("_EMPTY", -1);

define("_ESTRATEGICO", 1);
define ("_OPERATIVO", 2);

define('_MES',1);
define('_SEMANA',2);
define('_DIA',3);
define('_MES_GENERAL',4);
define('_YEAR',5);
define('_YEAR_PLANNING',6);
define('_MES_GENERAL_STACK',7);

define('_PRINT_IND',-1);

// SI SE CAMBIA EL ARRAY SE MODIFICA LA SIGANACION DE PERMISOS
$roles_array= array('INVITADO','MONITOREO','REGISTRO','PLANIFICADOR','ADMINISTRADOR','SUPERUSUARIO', 'GLOBALUSUARIO');
define('_INVITADO',0);
define('_MONITOREO',1);
define('_REGISTRO',2);
define('_PLANIFICADOR',3);
define('_ADMINISTRADOR',4);
define('_SUPERUSUARIO',5);
define('_GLOBALUSUARIO',6);
define ('_USER_SYSTEM', 1);

$meses_array= array("Todo el año","Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
$meses_short_array= array("Todo el año","Ene","Feb","Mar","Abr","May","Jun","Jul","Ago","Sep","Oct","Nov","Dic");

$periodo_month= array('D'=>1,'S'=>2, 'Q'=>3, 'M'=>4, 'T'=>5, 'E'=>6, 'A'=>7);
$periodo_month_inv= array(null, 'D','S', 'Q', 'M', 'T', 'E', 'A');
$periodicidad_value= array('D'=>1,'S'=>1, 'Q'=>1, 'M'=>1, 'T'=>3, 'E'=>6, 'A'=>12);

$periodo= array('DIARIA'=>'D','SEMANAL'=>'S','QUINCENAL'=>'Q','MENSUAL'=>'M','TRIMESTRAL'=>'T','SEMESTRAL'=>'E','ANUAL'=>'A');
$periodo_inv= array('D'=>'DIARIA','S'=>'SEMANAL','Q'=>'QUINCENAL','M'=>'MENSUAL','T'=>'TRIMESTRAL','E'=>'SEMESTRAL','A'=>'ANUAL');

$peso= array('sin relaciÃ³n'=>0,'muy bajo'=>1,'bajo'=>2,'ligeramente bajo'=>3,'medio'=>4,'ligeramente alto'=>5,'alto'=>6 ,'muy alto'=>7);
$Tpeso_inv_array= array('NO HAY RELACIÓN','MUY BAJA','BAJA','LIGERAMENTE BAJA','MEDIA','LIGERAMENTE ALTA','ALTA','MUY ALTA');

define("_PESO_ALTO",6);
define("_MAX_PESO",7);

define("_ORANGE", 85.0);
define("_YELLOW", 90.0);
define("_GREEN", 95.0);
define("_AQUA", 105.0);
define("_BLUE", 110.0);

$item_planning_array= array("pol"=>'Politica o Lineamiento', "obj"=>'Objetivo Estratégico', "ind"=>'Objetivo de Trabajo', "indi"=>'Indicador');

//EVENTOS
$reapet_evento_array= array('No periodica','Diaria', 'Semanal','Mensual');

$dayNames= array('undef','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo');
$frecuency_eventos= array('undef','primer', 'segundo', 'tercer', 'cuarto', 'último');

$day_feriados= array('1/01','2/01','1/05','25/07','26/07','27/07','10/10','25/12');

$evaluacion_array= array(null,'DEFICIENTE', 'ADECUADO', 'SUPERIOR');

define("_DEFICIENTE_CUTOFF", 70);
define("_ACEPTABLE_CUTOFF", 80);
define("_SOBRESALIENTE_CUTOFF", 90);

define("_MAX_EVAL_PLAN", 4);

define("_OBJETIVO_FIJADO", 1);
define("_APROBADO", 2);
define("_EVALUADO", 3);
define("_AUTO_EVALUADO", 4);

define("_NO_INICIADO", 1);
define("_EN_CURSO", 2);
define("_COMPLETADO", 3);
define("_ESPERANDO", 4);
define("_POSPUESTO", 5);
define("_SUSPENDIDO", 6);
define("_CANCELADO", 7);
define("_INCUMPLIDO", 8);
define("_REPROGRAMADO", 9);
define("_DELEGADO", 10);

define("_MAX_STATUS_EVENTO", 9);

$eventos_cump= array(null,'NO INICIADO', 'EN CURSO / PENDIENTE', 'COMPLETADO / CUMPLIDA','A LA ESPERA', 'POSPUESTO','SUSPENDIDO','CANCELADO','INCUMPLIDO');
$eventos_cump_class= array(null, 'blank', 'yellow', 'green', 'orange', 'orange', 'orange', 'gray', 'dark');

define('_EVENTO_ANUAL', 2);
define('_EVENTO_MENSUAL', 1);
define('_EVENTO_INDIVIDUAL', 0);

// PARA MOSTRAR EN LOS PLANES
define('_PRINT_REJECT_NO', 0);		// oculta las rechazadas por el sistema
define('_PRINT_REJECT_OUT', 1);     // imprime rechazadas por el sistema
define('_PRINT_REJECT_DEFEAT', 2);  // lo imprime todo. No oculta nada

// ASIGNACION DE TAREAS A OTORS USUARIOS
define('_TO_SUBORDINADOS', 0);			// solo a sus subordinados
define('_TO_ENTITY', 1);				// a todos los que pertenecen a la entidad
define('_TO_ALL_ENTITIES', 2);			// a todos en el sistema

//El conteo comienza a partir del cero;
$tipo_actividad_array= array(
	"Plan de trabajo Individual",
	"Tareas propias del Mes",
	"Trabajo político-ideológico y de organización del Partido", 
	"Funcionamiento y control del Estado", 
	"Funcionamiento y control del Gobierno", 
	"Funciones y encargo estatal en los OACE, OSDE, entidades nacionales y CAP", 
	"Funcionamiento interno", 
	"Defensa, Orden Interior y Defensa Civil", 
	"Otras"
); 

define("_FUNCIONAMIENTO_INTERNO", 6);
define("_MAX_TIPO_ACTIVIDAD", 8);

//RIESGOS
$frecuencia_array= array(null,'RARAMENTE','POCO PROBABLE','MODERADA','PROBABLE','CASI SEGURO');
define("_POCO_PROBABLE", 2);
define("_PROBABLE", 4);

$impacto_array= array(null,'INSIGNIFICANTE','BAJA','MEDIA','MUY ALTA','EXTREMA');
define("_INSIGNIFICANTE", 1);

$deteccion_array= array(null,'CASI CIERTO','ALTO','MODERADO','BAJO','INCIERTO');
define("_CASI_CIERTO", 1);
define("_MODERADO", 3);

$nivel_array= array(null,'TRIVIAL','BAJO','MODERADO','SIGNIFICATIVO','ALTO','MUY ALTO','SEVERO');
define("_NIVEL_RIESGO_BAJO", 2);

$estado_riesgo_array= array(null,'IDENTIFICADO','GESTIONANDOSE','MITIGADO','ELIMINADO');

$estado_hallazgo_array= array(null,'IDENTIFICADO','GESTIONANDOSE','CERRADA');

define("_IDENTIFICADO", 1);
define("_GESTIONANDOSE", 2);
define("_MITIGADO", 3);
define("_CERRADA", 3);
define("_ELIMINADO", 4);

//TAREAS
define("_DESACTUALIZADA", 0); //red
define("_EN_ESPERA", 1);      //blank
define("_ATRAZADA", 2);       //orange
define("_EN_TIEMPO", 3);      //yellow
define("_CUMPLIDA", 4);       //blue
define("_DETENIDA", 5);       //brown

define("_DIAS_SOBRECUMPLIMIENTO", 5);

$Ttarea_restrictions= array(
						'FS' => "Al Finalizar Comienza (FC)", 
                        'SF' => "Al Comenzar Finaliza (CF)", 
                        'SS' => "Al Comenzar Comienza (CC)", 
                        'FF' => "Al Finalizar Finaliza(FF)"
                    );

// DIAGRAMA KANBAN
define("_TAREA_NO_INICIADA", 1);
define("_TAREA_EN_PROCESO", 2);
define("_TAREA_TERMINADA", 3);

// AMBIENTE DE CONTROL
$Tambiente_control_array= array(null, 'AMBIENTE DE CONTROL', 'GESTIÓN Y PREVENCIÓN DE RIESGOS', 'ACTIVIDADES DE CONTROL', 'INFORMACIÓN Y COMUNICACIÓN', 'SUPERVISIÓN Y MONITOREO');

$Tcriterio_array= array(null, 
						array('No procede', 0, 'El requisito no se aplica'), 
						array('No se cumple', 1, 'No se evidencia el cumplimiento del requisito'), 
						array('En proceso', 2, 'Se evidencia el cumplimiento del requisito de forma parcial'), 
						array('Si se cumple', 3, 'Se evidencia el cumplimiento del requisito de forma total')
						);

define("_NO_PROCEDE", 0);						
define("_NO_SE_CUMPLE", 1);
define("_EN_PROCESO", 2);
define("_SE_CUMPLE", 3);

$Tcumplimiento_array= array("NO PROCEDE", "NO SE CUMPLE", "EN PROCESO", "SE CUMPLE");

define("_MAX_COMPONENTES_CI", 6);

//PROCESOS
$Ttipo_proceso_array= array(null, 'CECM', 'OACE', 'OSDE', 'GAE', 'Empresa', 'UEB', 'Area de Regulación y Control', 'Grupo de Trabajo', 'Departamento', 'Proceso Interno', 'Area de Resultados Claves');
$Ttipo_conexion_array= array('DESCONECTADO','INTRANET','CORREO ELECTRÓNICO','RED WAN / (INTERNET)', 'NODO CENTRAL');

define('_DESCONECTADO', 0);
define('_LAN', 1);
define("_EMAIL", 2);
define("_TCT_IP", 3);
define('_NO_LOCAL', 1);
define('_LOCAL', 0);
define('_NO_LOCAL_WAN_NODO', 4);

define('_MAX_TIPO_PROCESO', 11);

define('_TIPO_CECM', 1);
define('_TIPO_OACE', 2);
define('_TIPO_OSDE', 3);
define('_TIPO_GAE', 4);
define('_TIPO_EMPRESA', 5);
define('_TIPO_UEB', 6);
define('_TIPO_DIRECCION', 7);
define('_TIPO_GRUPO', 8);
define('_TIPO_DEPARTAMENTO', 9);
define('_TIPO_PROCESO_INTERNO', 10);
define('_TIPO_ARC', 11);

$jerarquia_proceso_array= array();
//                                  0  1  2  3  4  5  6  7  8  9  10 11
$jerarquia_proceso_array[0]=  array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
$jerarquia_proceso_array[1]=  array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
$jerarquia_proceso_array[2]=  array(0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
$jerarquia_proceso_array[3]=  array(0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0);
$jerarquia_proceso_array[4]=  array(0, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0);
$jerarquia_proceso_array[5]=  array(0, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0);
$jerarquia_proceso_array[6]=  array(0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0);
$jerarquia_proceso_array[7]=  array(0, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0);
$jerarquia_proceso_array[8]=  array(0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0);
$jerarquia_proceso_array[9]=  array(0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0);
$jerarquia_proceso_array[10]= array(0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0);
$jerarquia_proceso_array[11]= array(0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0);

/*
 * Determina si $tipo es subordinado a $tipo_sup
 */
if (!defined("_SYNCHRO_AUTOMATIC_EMAIL")) {
    function if_subordinado($tipo, $tipo_sup){
        global $jerarquia_proceso_array;
        return $jerarquia_proceso_array[(int)$tipo][(int)$tipo_sup];
    }
}

/*
 * Maneras de sincronizacion de las unidades
 */
define("_SYNCHRO_NEVER", 0);
define("_SYNCHRO_MANUAL", 1);
define("_SYNCHRO_AUTOMATIC_EMAIL", 2);
define("_SYNCHRO_AUTOMATIC_HTTP", 3);

// AUDITORIAS Y NO CONFORMIDADES
$Ttipo_nota_array= array(null,'NO CONFORMIDAD O VIOLACIÓN', 'OBSERVACIÓN', 'NOTA DE MEJORA');
$Ttipo_nota_origen_array= array('undef', 'AUDITORIA EXTERNA', 'SUPERVISIÓN EXTERNA', 'AUDITORIA INTERNA', 'SUPERVISIÓN INTERNA', 'AUTOCONTROL', 'REVISIÓN POR LA DIRECCIÓN', 'EVALUACIÓN DE PROVEEDORES', 'EVALUACIÓN DE LA SATISFACCIÓN DE CLIENTES', 'INSPECCIÓN');

define('_NOTA_TIPO_AUDITORIA_EXTERNA', 1);
define('_NOTA_TIPO_SUPERVICION_EXTERNA', 2);

define('_NOTA_TIPO_AUDITORIA_INTERNA', 3);
define('_NOTA_TIPO_SUPERVICION_INTERNA', 4);

define('_NOTA_TIPO_INSPECCION', 9);

define('_NO_CONFORMIDAD',1);
define('_OBSERVACION',2);
define('_OPORTUNIDAD',3);

define('_MAX_TIPO_NOTA', 4);
define('_MAX_TIPO_NOTA_ORIGEN', 10);

// borrado;
define('_DELETE_NO', 0); 
define('_DELETE_YES', 1);
define('_DELETE_PHY', 2);

// AUDITORIAS
$Ttipo_auditoria_array= array(null, 'DE GESTIÓN U OPERACIONAL', 'FINANCIERA', 'ESPECIAL', 'FISCAL', 'CONTROL ESTATAL', 'INFORMÁTICA', 
	'GESTIÓN DE LA CALIDAD', 'DE SEGURIDAD Y SALUD EN EL TRABAJO', 'GESTIÓN AMBIENTAL', 'GESTIÓN DE LA ENERGIA', 
	'GESTIÓN AL SISTEMA INTEGRADO', 'CONTROL INTERNO', 'CONTROL DEL TRANSPORTE');

define('_MAX_TIPO_AUDITORIA', 13);

define('_AUDITORIA_TIPO_ESPECIAL', 3);
define('_AUDITORIA_TIPO_FISCAL', 4);
define('_AUDITORIA_TIPO_CONTROL_ESTATAL', 5);

$Ttipo_plan_array= array(null, "Plan de Prevención", "Programa de Auditoría", "Programa de Supervición", "Plan de Acciones Preventivas y Correctivas", 
	"Plan de Trabajo Individual", "Plan de trabajo Mensual", "Plan General Anual", "Plan de medidas", "Programa de reuniones");

// TIPO DE PLANES
define('_PLAN_TIPO_PREVENCION', 1);
define('_PLAN_TIPO_AUDITORIA', 2);
define('_PLAN_TIPO_SUPERVICION', 3);
define('_PLAN_TIPO_ACCION', 4);
define('_PLAN_TIPO_ACTIVIDADES_INDIVIDUAL', 5);
define('_PLAN_TIPO_ACTIVIDADES_MENSUAL', 6);
define('_PLAN_TIPO_ACTIVIDADES_ANUAL', 7);
define('_PLAN_TIPO_MEDIDAS', 8);
define('_PLAN_TIPO_MEETING', 9);
define('_PLAN_TIPO_INFORMATIVO', 10);
define('_PLAN_TIPO_PROYECTO', 11);

// USO DE LAS PERSPECTIVAS
define('_PERSPECTIVA_ALL', 0);
define('_PERSPECTIVA_NULL', 1);
define('_PERSPECTIVA_NOT_NULL', 2);

// MODOS EN LOS QUE SE MUESTRAN ENUMERADAS LAS ACTIVIDADES EN LOS PLANES
define('_ENUMERACION_MANUAL', 0);
define('_ENUMERACION_CONTINUA', 1);
define('_ENUMERACION_CAPITULOS', 2);

// MAXIMA CANTIDAD DE FILAS EN PLANES ANUALES 
define('_MAX_ROW_IN_PAGE', 30);

// ACCIONES A REALIZAR CON LA LISTA DE REQUISTOS PARA REGISTRO DE ESTADO
define("_GUARDAR_LISTA_TABLERO_NOTAS", 0);
define("_GUARDAR_LISTA_LISTA_REQUISTOS", 1);
define("_GUADAR_LISTA_IMPRIMIR", 2);
define("_APLICAR_LISTA_TABLERO_NOTAS", 3);

// MAXIMA CANTIDAD DE FILAS EN LAS LSITAS DE CHEQUEO 
define('_MAX_ROW_IN_PAGE_CHECKLIST', 50);

// tamano de papel a utilizar
$array_papel_size= array(
	'A4'          =>array(21, 29.7),
	'Carta'       =>array(21.59, 27.94),
	'Oficio'      =>array(21.59, 35.56),
	'Ejecutivo'   =>array(18.41, 26.67),
	'Tabloide'    =>array(27.94, 43.18),
	'A3'          =>array(29.7, 42),
	'Conduce'     =>array(21.59, 13.97)
);

$meeting_array= array(
	0 => null, 
	1 =>  'Otras', 
	2 =>  'Asamblea de Sección Sindical',
	20 => 'Consejo de Calidad',
	3 =>  'Consejo de Dirección Ordinario', 
	13 => 'Consejo de Dirección Extraodinario', 
	14 => 'Consejo de Dirección Análisis Económico', 
	21 => 'Consejo Energético',
	23 => 'Consejo de Calidad',
	4 =>  'Consejo Técnico Asesor', 
	19 => 'Consejo de Producción',
	15 => 'Consejillo de Producción', 
	5 =>  'Comisión de Cuadros', 
	22 => 'Comité de Aprobacción de Divisas',
	6 =>  'Comité de Contratación', 
	25 => 'Comité de Evaluación de las Inversiones', 
	27 => 'Comité de Litigios', 
	17 => 'Comité de Negocios', 
	7 =>  'Comité Financiero', 
	26 => 'Comité de Pago', 
	8 =>  'Comité de Prevención y Control', 
	22 => 'Comité SST',
	16 => 'Chequeo de Objetivos', 
	24 => 'Grupo de Activos y Patrimonios',
	18 => 'Preparación de Cuadros',
	9 =>  'Reunión de Coordinación', 
	30 => 'Reunión de cuentas por cobrar y pagar',
	28 => 'Reunión de estudios politicos',
	31 => 'Reunión de equipos tecnológicos y no tecnológicos',
	10 => 'Reunión del Grupo de Negocios',
	31 => 'Reunión de preparación para el Consejo de Directores Generales',
	32 => 'Reunión de Evaluación del desempeño',
	29 => 'Reunión del Núcleo del PCC', 
	11 => 'Reunión del Comité de Base (UJC)', 
	12 => 'Revisión por la Dirección'
);


/* PARA LA GESTION DE ARCHIVOS */
$Tarray_tipo_documento= array (
	0 => null,
	1 => 'Otros',
	3 => 'Carta',
	8 => 'Comisión de Cuadros',
	9 => 'Comisión de Aprobación de Divisas',	
	6 => 'Consejo de Dirección Ordinario',
	12 => 'Consejo de Dirección Extraordinario',
	14 => 'DEFENSA CIVIL',
	5 => 'Denuncia',
	16 => 'Indicación',
	2 => 'Informe',
	17 => 'Nota Informativa',
	10 => 'Parte diario',
	11 => 'Queja',
	7 => 'Reclamación',
	13 => 'Resolucion',
	4 => 'Solicitud',
	15 => 'Telefonema'
);

define('_MAX_TIPO_DOCUMENTO', 14);

defined('_PLAN_TIPO_INFORMATIVO') or define('_PLAN_TIPO_INFORMATIVO', 10);

$Tarray_estado_archive= array (null, 'cumplido', 'pendiente', 'en proceso');
define('_EN_ARCHIVO', 0);
define('_CUMPLIDO', 1);  
define('_PENDIENTE', 2); 
define('_EN_PROCESO', 3); 

define("_MAX_STATUS_EVENTO", 3);

$Tarray_prioridad= array (null, 'Baja', 'Regular', 'Alta');
define('_ACCESO_BAJA', 1);  
define('_ACCESO_MEDIA', 2); 
define('_ACCESO_ALTA', 3); 

define("_MAX_PRIORIDAD", 3);

$Tarray_clase_archive= array (null, 'Ordinario', 'Limitado', 'Confidencial', 'Secreto', 'Secreto de Estado');
define('_DOCUMENTO_ORDINARIO', 1); 
define('_DOCUMENTO_LIMITDO', 2);  
define('_DOCUMENTO_CONFIDENCIAL', 3); 
define('_DOCUMENTO_SECRETO', 4); 
define("_DOCUMENTO_SECRETO_ESTADO", 5);

define("_MAX_DOCUMENTO_CLASS", 5);

/************************************/
$Tarray_nivel_archive= array (null, 'supervisor', 'registro', 'consultor');
define('_USER_SUPERVISOR_ARCH', 1);
define('_USER_REGISTRO_ARCH', 2);
define('_USER_CONSULTOR_ARCH', 3);

define("_MAX_NIVEL_USER_ARCH", 3);

defined('_MAX_TIPO_MEETING') or define('_MAX_TIPO_MEETING', 22);
define('_MEETING_TIPO_OTRA', 1);

// Tratamiento de las imagenes
define('_MAX_IMG_SING_WIDTH', 180);
define('_MAX_IMG_SING_HEIGHT', 120);

if (defined('_ROOT_DIRIGER_DIR') && file_exists("{$_SESSION['virtualhost_base_dir']}client_images/config.ini.php")) {
	include "{$_SESSION['virtualhost_base_dir']}client_images/config.ini.php";
	if (isset($tipo_proceso_array))
		$Ttipo_proceso_array= $tipo_proceso_array;
}	
?>