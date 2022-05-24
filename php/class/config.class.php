<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 10/01/15
 * Time: 11:41
 */

include_once "base.class.php";

class _Tconfig {
    protected $clink;
    private $id;

    public $location;

    public $charset;
    public $maxfilesize;  // tamaño maximo que puede terner un fichero a cargar al servidor
    public $hoursoldier;
    public $onlypost;
    public $breaktime;      // dias para asignar incumplimiento a la tarea
    public $freeassign;

    public $hourcolum;
    public $datecolum;
    public $placecolum;
    public $observcolum;
    public $grouprows;

    public $hourcolum_y;
    public $placecolum_y;
    public $observcolum_y;
    public $responsable_planwork;  // columna responsable en el resumen

    public $delay;          // tiempo de refresco de la pagina
    public $inactive; 	     // tiempo maximo de inactividad para logout
    public $daysbackup;      // tiempo entre realizaciones de backup de la bd
    public $summaryextend;   // si se imprime el resumen del plan con las tareas dsglosadas

    public $monthstack;      // en los planes mensuales se muestran las tareas organiazadas segun los dias consecutivos

    public $timepurge;
    public $maxexectime;     // tiempo maximo de espera a que termine una operacion
    public $maxwaittime;     // tiempo maximo de inactividad de las operaciones en background
    public $timesynchro;

    public $seemonthplan;  // ver todos los usuarios el plan mensual
    public $seeanualplan;  // ver todos los usuarios el plan anual
    public $use_anual_plan_organismo;  // En el Plan anual se muestran los organismos y entidades destino de las tareas
    public $use_mensual_plan_organismo;  // En el Plan General Mensual se muestran los organismos y entidades destino de las tareas
    public $monthpurge;   // cada cuantos meses se realizara la purga de dotos
    public $dirsize;   // tama;o maximo en gigabytes de las carpetsa que guandan los lotes y backups

    private $sql,
            $save_sql,
            $fline;
    public $loaded;

    public  $riskseeprocess; // asociar los riesgos conmlos procesos internos. Salen los riesgos por procesos en el Plan de Prevencion
    public  $riskseeactivity;
    public  $riskseedescription;
    public  $riskseetype1;
    public  $riskseedetection;
    public  $riskseestate;
    public  $riskseeobserv;

    public $url_backup;
    public $user_backup;
    public $passwd_backup;
    public $smb_version;

    // automatizacion
    public $automatic_risk;
    public $automatic_note;

    public $sipac_format;

    public $type_synchro;
    public $time_synchro;   // tiempo en segundos entre la sincronizacion;

    public $mail_method;

    // conexion al servidor LDAP
    public $block_no_ldap_login;
    public $ldap_login;
    public $mail_use_ldap;
    public $ldap_nameserver;

    // conexion al servidor RADIUS
    public $radius_nameserver;
    public $secret;

    public $hostname;
    public $incoming_mail_server;
    public $outgoing_mail_server;

    public $off_mail_server;

    public $email_user_same_pop3_smtp;
    public $email_login;
    public $email_password;
    public $email_login_smtp;
    public $email_password_smtp;

    public $incoming_protocol;
    public $incoming_port;
    public $incoming_ssl;
    public $outgoing_port;
    public $outgoing_ssl;
    public $smtp_auth;      // habilita la autenticación smtp. Desabilitar para el uso de los certificados
    public $smtp_auth_tls;  // habilita la encriptación TLS automaticamente si el servidor lo soporta. Aun cuando no este configurada el TLS

    public $http_access;    // El sistema pueda ser acedido desde otras estaciones o redes

    public $outgoing_no_tls; // no habilitar tls en popp o imap aun cuando el servidor lo permita

    public $hide_values;
    public $dpto_with_objetive;
    public $show_group_dpto_plan;
    public $show_group_dpto_risk;

    private $var_array;


    public $accords_automatic;  // Permite que en los planes mensuales e individuales los acuerdos puedan tomar cumplimieento automaticamente
                                 // cuando se selecciona todos los dias de las semanas o del mes
    public $risk_note_automatic;  // Permite que en los planes mensuales e individuales las tareas de riesgos y notas 
                                    // puedan tomar cumplimieento automaticamente cuando se selecciona todos los dias de las semanas o del mes

    public function __construct($clink= null) {
        $domain= substr($_SESSION['email_app'], stripos($_SESSION['email_app'], '@')+1);
        // hay que agrehar las variables a ete array
        $this->clink= $clink;

        $this->var_array= array(
            // variable  =>  tipo   default   seguarda desde esta clase
            'location' => array('string', $_SESSION['location'], true),
            'local_proceso_tipo' => array('integer', $_SESSION['local_proceso_tipo'], true),
            'empresa' => array('string', $_SESSION['empresa'], true),
            'local_proceso_id' => array('integer', $_SESSION['local_proceso_id'], true),
            'local_proceso_id_code' => array('string', $_SESSION['local_proceso_id_code'], true),

            'charset' => array('string', null, true),
            'maxfilesize' => array('integer', 5, true),
            'seemonthplan' => array('boolean', false, true),
            'hourcolum' => array('boolean', false, true),
            'datecolum' => array('boolean', false, true),
            'placecolum' => array('boolean', false, true),
            'observcolum' => array('boolean', false, true),
            'monthstack' => array('boolean', false, true),
            'grouprows' => array('boolean', false, true),

            'hoursoldier' => array('boolean', false, true),
            'onlypost' => array('boolean', false, true),
            'summaryextend' => array('boolean', false, true),

            'breaktime' => array('integer', 2, true),
            'freeassign' => array('integer', 0, true),

            'hourcolum_y' => array('boolean', false, true),
            'placecolum_y' => array('boolean', false, true),
            'observcolum_y' => array('boolean', false, true),
            'responsable_planwork' => array('boolean', false, true),

            'delay' => array('integer', 25, true),
            'inactive' => array('integer', 30, true),
            'daysbackup' => array('integer', 7, true),

            'timepurge' => array('time', false, true),
            'dirsize' => array('integer', 4, true),

            'seeanualplan' => array('boolean', false, true),
            'use_anual_plan_organismo' => array('boolean', false, true),
            'use_mensual_plan_organismo' => array('boolean', false, true),

            'monthpurge' => array('integer', 3, true),
            'maxexectime' => array('integer', 2, true),
            'maxwaittime' => array('integer', 15, true),

            'riskseeprocess' => array('boolean', false, true),
            'riskseeactivity' => array('boolean', false, true),
            'riskseedescription' => array('boolean', false, true),
            'riskseetype1' => array('boolean', false, true),
            'riskseedetection' => array('boolean', false, true),
            'riskseestate' => array('boolean', false, true),
            'riskseeobserv' => array('boolean', false, true),

            'url_backup' => array('string', null, true),
            'user_backup' => array('string', null, true),
            'passwd_backup' => array('string', null, true),
            'smb_version' => array('integer', 2.1, true),

            'automatic_risk' => array('boolean', false, true),
            'automatic_note' => array('boolean', false, true),

            'sipac_format' => array('boolean', false, true),

            'timesynchro' => array('time', false, false),
            'type_synchro' => array('integer', 0, false),
            'time_synchro' => array('integer', null, false),

            'off_mail_server' => array('boolean', false, false),

            'mail_method' => array('string', 'smtp', false),
            'hostname' => array('string', gethostname().".$domain", false),
            'incoming_mail_server' => array('string', null, false),
            'outgoing_mail_server' => array('string', null, false),

            'email_user_same_pop3_smtp' => array('boolean', true, false),
            'email_login' => array('string', null, false),
            'email_password' => array('string', null, false),
            'email_login_smtp' => array('string', null, false),
            'email_password_smtp' => array('string', null, false),

            'outgoing_port' => array('integer', 25, false),
            'outgoing_ssl' => array('integer', 0, false),
            'incoming_protocol' => array('string', 'POP3', false),
            'incoming_port' => array('integer', 110, false),
            'incoming_ssl' => array('integer', 0, false),
            'smtp_auth' => array('boolean', true, false),
            'smtp_auth_tls' => array('boolean', true, false),
            'http_access' => array('boolean', false, false),

            'outgoing_no_tls' => array('boolean', false, false),

            'block_no_ldap_login' => array('boolean', false, false),
            'ldap_login' => array('boolean', false, false),
            'mail_use_ldap' => array('boolean', false, false),
            'ldap_nameserver' => array('string', null, false),

            'hide_values' => array('boolean', false, true),
            'dpto_with_objetive' => array('boolean', false, true),
            'show_group_dpto_plan' => array('boolean', false, true),
            'show_prs_plan' => array('boolean', false, true),
            'show_group_dpto_risk' => array('boolean', false, true),

            'accords_automatic' => array('boolean', false, true),
            'risk_note_automatic' => array('boolean', false, true)
       );

        $this->loaded= $this->read_file() ? true : false;
        /*
        if (!$this->loaded && !empty($this->clink))
            $this->read_db();
        */
        if (!$this->loaded)
            $this->init_vars();
    }

    public function file_sh_backup() {
        $fp= fopen(_DIRIGER_DIR."backup.sh", 'w');
        $dir_backup= _DATA_DIRIGER_DIR."sql/*";
        $line= "#!/etc/sh \n";
        $line.= "mount.cifs -o username={$this->user_backup},password={$this->passwd_backup}";
        if (!empty($this->smb_version)) 
            $line.= ",vers={$this->smb_version}";
        $line.= " {$this->url_backup}/ /mnt/diriger.backup/ \n";
        $line.= "cp -r {$dir_backup} /mnt/diriger.backup/";

        fwrite($fp, $line);
        fclose($fp);

        exec("sh /var/diriger/backup.sh");
    }

    public function SetLink($clink) {
        $this->clink= $clink;
    }

    private function init_vars() {
        /* especificacion de variables atipicas --------------------------------------------- */
        $this->maxfilesize= !empty($this->maxfilesize) ? $this->maxfilesize : $this->var_array['maxfilesize'][1];

        $this->location= !empty($this->location) ? $this->location : $this->var_array['location'][1];
        $this->local_proceso_tipo= !empty($this->local_proceso_tipo) ? $this->local_proceso_tipo : $this->var_array['local_proceso_tipo'][1];
        $this->empresa= !empty($this->empresa) ? $this->empresa : $this->var_array['empresa'][1];
        $this->local_proceso_id= !empty($this->local_proceso_id) ? $this->local_proceso_id : $this->var_array['local_proceso_id'][1];
        $this->local_proceso_id_code= !empty($this->local_proceso_id_code) ? $this->local_proceso_id_code : $this->var_array['local_proceso_id_code'][1];

        $this->time_synchro= !empty($this->time_synchro) ? $this->time_synchro : $this->var_array['time_synchro'][1];
        $this->incoming_protocol= !empty($this->incoming_protocol) ? $this->incoming_protocol : $this->var_array['incoming_protocol'][1];
        $this->incoming_port= !empty($this->incoming_port) ? $this->incoming_port : $this->var_array['incoming_port'][1];
        $this->outgoing_port= !empty($this->outgoing_port) ? $this->outgoing_port : $this->var_array['outgoing_port'][1];

        $this->breaktime= !empty($this->breaktime) ? $this->breaktime : $this->var_array['breaktime'][1];
        $this->daysbackup= !is_null($this->daysbackup) ? $this->daysbackup : $this->var_array['daysbackup'][1];
        $this->delay= !empty($this->delay) ? $this->delay : $this->var_array['delay'][1];
        $this->inactive= !empty($this->inactive) ? $this->inactive : $this->var_array['inactive'][1];

        $this->monthpurge= !empty($this->monthpurge) ? $this->monthpurge : $this->var_array['monthpurge'][1];
        $this->maxexectime= !empty($this->maxexectime) ? $this->maxexectime : $this->var_array['maxexectime'][1];
        $this->maxwaittime= !empty($this->maxwaittime) ? $this->maxwaittime : $this->var_array['maxwaittime'][1];
        $this->dirsize= !empty($this->dirsize) ? $this->dirsize : $this->var_array['dirsize'][1];

        $this->mail_method= !empty($this->mail_method) ? $this->mail_method : 'smtp';

        $this->off_mail_server= is_null($this->off_mail_server) ? false : $this->off_mail_server ? true : false;
        $this->email_user_same_pop3_smtp= is_null($this->email_user_same_pop3_smtp) ? true : $this->email_user_same_pop3_smtp;

        $this->email_login= !is_null($this->email_login) ? $this->email_login : ($this->email_user_same_pop3_smtp ? $this->email_login_smtp : null);
        $this->email_password= !is_null($this->email_password) ? $this->email_password : ($this->email_user_same_pop3_smtp ? $this->email_password_smtp : null);

        $this->smtp_auth= is_null($this->smtp_auth) ? true : ($this->smtp_auth ? true : false);
        $this->smtp_auth_tls= is_null($this->smtp_auth_tls) ? true : ($this->smtp_auth_tls ? true : false);
        $this->http_access= is_null($this->http_access) ? false : ($this->http_access ? true : false);

        $this->outgoing_no_tls= is_null($this->outgoing_no_tls) ? false : ($this->outgoing_no_tls ? true : false);

        $domain= substr($_SESSION['email_app'], stripos($_SESSION['email_app'], '@')+1);
        $this->hostname= !empty($this->hostname) ? $this->hostname : gethostname().".$domain";
        $this->outgoing_mail_server= !empty($this->outgoing_mail_server) ? $this->outgoing_mail_server : $this->var_array['outgoing_mail_server'][1];
        $this->incoming_mail_server= !empty($this->incoming_mail_server) ? $this->incoming_mail_server : $this->var_array['incoming_mail_server'][1];

        $this->sipac_format= !is_null($this->sipac_format) ? $this->sipac_format : false;
        $this->use_anual_plan_organismo= !is_null($this->use_anual_plan_organismo) ? $this->use_anual_plan_organismo : false;
        $this->use_mensual_plan_organismo= !is_null($this->use_mensual_plan_organismo) ? $this->use_mensual_plan_organismo : false;

        $this->smb_version= !is_null($this->smb_version) ? $this->smb_version : 2.1;

        $this->hide_values= !is_null($this->hide_values) ? $this->hide_values : false;
        $this->dpto_with_objetive= !is_null($this->dpto_with_objetive) ? $this->dpto_with_objetive : false;
        $this->show_group_dpto_plan= !is_null($this->show_group_dpto_plan) ? $this->show_group_dpto_plan : false;
        $this->show_prs_plan= !is_null($this->show_prs_plan) ? $this->show_prs_plan : false;
        $this->show_group_dpto_risk= !is_null($this->show_group_dpto_risk) ? $this->show_group_dpto_risk : false;

        $this->responsable_planwork= !is_null($this->responsable_planwork) ? $this->responsable_planwork : false;

        $this->accords_automatic= !is_null($this->accords_automatic) ? $this->accords_automatic : false;
        $this->risk_note_automatic= !is_null($this->risk_note_automatic) ? $this->risk_note_automatic : false;
    }

    protected function read_file() {
        $url= _PHP_DIRIGER_DIR."system.ini";
        @chmod($url,0666);

        $SYSTEM= parse_ini_file($url);
        if ((!is_array($SYSTEM) || is_null($SYSTEM)) || empty($SYSTEM['delay']))
            return false;

        foreach ($this->var_array as $key => $var) {
            if ($var[0] === 'string')
                $val= setNULL_str($SYSTEM[$key], false);
            if ($var[0] === 'time')
                $val= setNULL_str($SYSTEM[$key], false);
            if ($var[0] === 'integer')
                $val= setNULL($SYSTEM[$key]);
            if ($var[0] === 'boolean')
                $val= boolean($SYSTEM[$key]);
            if ($key == 'time_synchro')
                if (strpos($val, ':') !== false) $val= 900;

            is_null($SYSTEM[$key]) ? eval("\$this->".$key."= null;") : eval("\$this->".$key."= $val;");
        }

        $this->init_vars();
        return true;
    }

    public function set_param() {
        $this->getIdConfig();

        foreach ($this->var_array as $key => $var) {
            if (!$var[2]) 
                continue;

            if ($var[0] != 'boolean')
                $this->$key= !is_null($_POST[$key]) && $_POST[$key] != 'undefined' ? $_POST[$key] : $this->$key;
            else
                $this->$key= is_null($_POST[$key]) ? false : true;
        }

        $error= $this->save_param(true);
        return $error;
    }

    private function getIdConfig() {
        $result= $this->clink->query("select id from _config order by id desc limit 1");
        $row= $this->clink->fetch_array($result);
        $this->id= $row[0];
    }

    public function save_param($save_sql= true) {
        $this->save_sql= $save_sql;
        $error= null;
        reset($this->var_array);

        $i= 0;
        foreach ($this->var_array as $key => $var) {
            ++$i;
            $value= null;
            $value=$this->$key;

            if ($var[0] == "integer")
                $value= setNULL($value);
            if ($var[0] === 'string')
                $value= setNULL_str($value);

            if ($var[0] == "time") {
                $value= empty($value) ? $value : ampm2odbc($value);
                $value= setNULL_str($value, false);
            }
  /*
            if ($var[0] == "boolean")
                $value= !is_null($this->$key) ? boolean2pg($this->$key) : "false";
            $this->add_to_sql($key, $value);
    */
            if ($var[0] == "boolean")
                $value= $this->$key ? "true" : "false";
            $this->add_to_file($key, $value);
        }

        // if ($this->save_sql) $error= $this->add_to_sql();
        if (is_null($error))
            $error= $this->add_to_file();
        return $error;
    }

    /*
    private function add_to_sql($var= null, $value= null) {
        if (is_null($var) && is_null($value)) {
            $this->sql.= " where id = $this->id";
            $result= $this->clink->query($this->sql);

            if (!$result) return $this->clink->error();
            else {
                $this->loaded= true;
                return null;
            }

        } else {
            if (is_null($this->sql)) $this->sql= "update _config set ";
            else $this->sql.= ", ";

            $this->sql.= "$var= ".addslashes($value);
            return null;
        }
    }
    */
    protected function add_to_file($var= null, $value= null) {
        $error= "Error al intentar escribir en el fichero de configuración ‘system.ini’";
        $canWrite= false;

        if (is_null($var) && is_null($value)) {
            $url= _PHP_DIRIGER_DIR."system.ini";
            @chmod($url,0666);

            if ($fp = fopen($url, 'w')) {
                $startTime = microtime();

                do {
                    $canWrite = flock($fp, LOCK_EX);
                    // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
                    if (!$canWrite) 
                        usleep(round(rand(0, 100)*1000));
                } while ((!$canWrite) && ((microtime()-$startTime) < 1000));

                //file was locked so now we can store information
                if ($canWrite) {
                    fwrite($fp, $this->fline);
                    flock($fp, LOCK_UN);
                } else {
                    return $error;
                }

                fclose($fp);
                @chmod($url,0666);

                $this->file_sh_backup();

                return null;

            } else {
                return $error;
            }

        } else {
            $this->fline.= "$var= $value\r\n";
            return null;
        }
    }
}


global $config;
global $clink;

if (is_null($not_load_config_class) || !$not_load_config_class) {
    $config= new _Tconfig($clink);

    if ($config->loaded) {
        ini_set("max_input_time", (int)$config->inactive*60);
        set_time_limit((int)$config->inactive*60);
        session_cache_expire((int)$config->inactive);
    }
}
?>