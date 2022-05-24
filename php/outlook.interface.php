<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 9/8/2016
 * Time: 7:02 p.m.
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";

require_once "interface.class.php";
require_once "class/connect.class.php";
require_once "class/proceso.class.php";

require_once "class/mail.class.php";
require_once "class/code.class.php";
require_once "class/badger.class.php";

require_once "register.interface.php";


class Tinterface extends TRegister {

    public function __construct($clink= null) {
        $this->clink = $clink;
        TRegister::__construct($clink);

        $this->year= !empty($_POST['year']) ? $_POST['year'] : $_GET['year'];
        $this->radio_prs = $_POST['_radio_prs'];
        $this->radio_date = $_POST['_radio_date'];
        $this->radio_user = $_POST['_radio_user'];

        $this->extend= $_POST['extend'];
        $this->print_reject= !empty($_POST['print_reject']) ? $_POST['print_reject'] : $_GET['print_reject'];

        $this->id_usuario= $this->id_calendar;
        $this->id_responsable= $_SESSION['id_usuario'];
        $this->id_proceso= $_POST['id_proceso'];
        $this->id_proceso_code= $_POST['id_proceso_code'];
    }

    private function escapeString($string) {
        // $string= textparse($string);
        $string= preg_replace('/([\,;])/','\\\$1', $string);
        return $string;
    }

    private function send_outlook($id_usuario) {
        global $meses_array;

        $usuario= $this->accept_mail_user_list[$id_usuario];

        if (empty($usuario['email']) || strlen($usuario['email']) == 0) {
            $this->error= "El usuario {$this->accept_mail_user_list[$id_usuario]['nombre']} no tiene definda dirección de correo electrónico. ";
            return;
        }

        $obj_mail= new Tmail();
        $obj_mail->isHTML(true);
        $LE= "\r\n";

        $attachment= "calendar_{$meses_array[(int)$this->month]}.ics";
        $uid_calendar= "00000018-FDC4-71DE-C89B-".str_pad($id_usuario, 11, "0", STR_PAD_LEFT);

        $obj_mail->addCustomHeader("Content-Disposition: inline;charset=utf-8;filename=$attachment");
/*
        $obj_mail->addCustomHeader("BEGIN:VCALENDAR");
        $obj_mail->addCustomHeader("VERSION:2.0");
        $obj_mail->addCustomHeader("METHOD:PUBLISH");
        $obj_mail->addCustomHeader("X-WR-RELCALID:{$uid_calendar}");
        $obj_mail->addCustomHeader("PRODID:-//Microsoft Corporation//Outlook 15.0 MIMEDIR//EN");
        $obj_mail->addCustomHeader("METHOD:REQUEST");
        $obj_mail->addCustomHeader("X-WR-CALNAME:Diriger");
        $obj_mail->addCustomHeader("X-PRIMARY-CALENDAR:FALSE");
*/
        $message= "BEGIN:VCALENDAR{$LE}";
        $message.= "PRODID:-//Microsoft Corporation//Outlook 15.0 MIMEDIR//EN{$LE}";
        $message.= "VERSION:2.0{$LE}";
        $message.= "X-WR-RELCALID:{$uid_calendar}{$LE}";
        $message.= "METHOD:PUBLIC{$LE}";
        $message.= "X-WR-CALNAME:{$usuario['nombre']}{$LE}";
        $message.= "X-PRIMARY-CALENDAR:TRUE{$LE}";
        $message.= "X-MS-OLK-FORCEINSPECTOROPEN:FALSE{$LE}";
        $message.= "X-OWNER;CN=\"{$usuario['usuario']} {$usuario['cargo']}\":mailto:{$usuario['email']}{$LE}";

        $sql= null;
        reset($this->array_eventos);
        foreach ($this->array_eventos as $evento) {
            if (array_search($evento['id'], $this->accept_mail_user_list[$id_usuario]['array_eventos']) === false)
                continue;
            $row= $this->obj->get_last_reg($evento['id'], $id_usuario);

            if (boolean($row['outlook']))
                continue;
            if (!boolean($row['_toshow']))
                continue;

            $continue= true;
            /*
            switch($this->print_reject) {
                case(_PRINT_REJECT_NO):
                    if ($row['user_check_plan'] || $row['_user_check'])
                        $continue= false;

                    if ((!empty($row['rechazado'])
                            && ($row['cumplimiento'] == _SUSPENDIDO || $row['cumplimiento'] == _CANCELADO
                                    || $row['cumplimiento'] == _DELEGADO || $row['cumplimiento'] == _REPROGRAMADO))
                            || ($evento['user_check_plan'] || $row['_user_check']))
                        $continue= false;

                    break;

                case(_PRINT_REJECT_OUT):
                    if ((!empty($row['rechazado'])
                            && ($row['cumplimiento'] == _SUSPENDIDO || $row['cumplimiento'] == _CANCELADO
                                    || $row['cumplimiento'] == _DELEGADO || $row['cumplimiento'] == _REPROGRAMADO))
                            || ($evento['user_check_plan'] || $row['_user_check']))
                        $continue= false;

                    break;
            }
            
            if (!$continue)
                continue;            
            */
            
            if ($row['user_check_plan'] || $row['_user_check'])
                continue;

            if ((!empty($row['rechazado'])
                    && ($row['cumplimiento'] == _SUSPENDIDO || $row['cumplimiento'] == _CANCELADO
                            || $row['cumplimiento'] == _DELEGADO || $row['cumplimiento'] == _REPROGRAMADO))
                    || ($evento['user_check_plan'] || $row['_user_check']))
                continue;

            $this->obj->setEvento_reg($row);
            $this->obj->SetIdEvento($evento['id']);
            $this->obj->SetIdUsuario($id_usuario);
            $this->obj->set_outlook(true);

            $sql.= $this->obj->update_cump($row['id_responsable'], true, $row['id']);

            $this->obj->SetIdTarea(null);
            $this->obj->SetIdAuditoria(null);

            $responsable= $this->accept_mail_user_list[$evento['id_responsable']];
            $organizer= "{$responsable['nombre']} ({$responsable['cargo']})";

            $date= date('Ymd', strtotime($evento['fecha_inicio']));
            $startTime= date('Hi', strtotime($evento['fecha_inicio']));
            $endTime= date('Hi', strtotime($evento['fecha_fin']));
            $sumary= $this->escapeString($evento['evento']);
            $description= $this->escapeString(textparse($evento['descripcion']));
            $lugar= $this->escapeString($evento['lugar']);
/*
            $obj_mail->addCustomHeader("BEGIN:VEVENT");
            $obj_mail->addCustomHeader("UID:{$evento['id']}.{$_SESSION['email_server']}");
            $obj_mail->addCustomHeader("DTSTAMP:" . date('Ymd').'T'. date('His'));
            $obj_mail->addCustomHeader("DTSTART:".$date."T".$startTime."00");
            $obj_mail->addCustomHeader("DTEND:".$date."T".$endTime."00");
            $obj_mail->addCustomHeader("SUMMARY:$sumary");
            $obj_mail->addCustomHeader("ORGANIZER;CN={$organizer}:mailto:{$responsable['email']}");
            $obj_mail->addCustomHeader("LOCATION:{$lugar}");
            $obj_mail->addCustomHeader("DESCRIPTION:{$description}");
            $obj_mail->addCustomHeader("SEQUENCE:0");

            $obj_mail->addCustomHeader("BEGIN:VALARM");
            $obj_mail->addCustomHeader("ACTION:DISPLAY");
            $obj_mail->addCustomHeader("SUMMARY:$sumary");
            $obj_mail->addCustomHeader("TRIGGER:-PT30M");
            $obj_mail->addCustomHeader("END:VALARM");
*/
            /*
            foreach ($array_participant as $participant) {
                $usuario= $this->accept_mail_user_list[$participant['id_usuario']];
                $participant_name= "{$usuario['nombre']} ({$usuario['cargo']})";
                $participant_email= $usuario['email'];
                // $obj_mail->addCustomHeader(null, "ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN{$participant_name};X-NUM-GUESTS=0:MAILTO:{$participant_email}");
            }
            */
/*
            $obj_mail->addCustomHeader("ACTION:DISPLAY");
            $obj_mail->addCustomHeader("END:VEVENT");
*/
            $message.= "BEGIN:VEVENT{$LE}";
            $message.= "UID:{$evento['id']}.{$_SESSION['email_server']}{$LE}";
            
            $message.= "METHOD:PUBLISH{$LE}";
            
            $message.= "DTSTAMP:" . date('Ymd').'T'. date('His') . "{$LE}";
            $message.= "DTSTART:".$date."T".$startTime."00Z{$LE}";
            $message.= "DTEND:".$date."T".$endTime."00Z{$LE}";
            $message.= "SUMMARY:{$sumary}{$LE}";
            $message.= "ORGANIZER;CN={$organizer}:mailto:{$responsable['email']}{$LE}";
            $message.= "LOCATION:{$lugar}{$LE}";
            $message.= "DESCRIPTION:{$description}{$LE}";
            $message.= "SEQUENCE:0{$LE}";

            $message.= "BEGIN:VALARM{$LE}";
            $message.= "ACTION:DISPLAY{$LE}";
            $message.= "SUMMARY:{$sumary}{$LE}";
            $message.= "TRIGGER:-PT30M{$LE}";
            $message.= "END:VALARM{$LE}";
            $message.= "ACTION:DISPLAY{$LE}";
            $message.= "END:VEVENT{$LE}";
        }
        $message.= "END:VCALENDAR{$LE}";

        $obj_mail->From= $_SESSION['email_app'];
        $obj_mail->FromName= "Sistema Informático Diriger";
        $obj_mail->Subject= "Plan de Trabajo Individual. Diriger";

        $body= "Generado desde el Sistema Informático Diriger<br> ";
        $body.= "Generado por: {$_SESSION['nombre']}<br> ";
        $body.= "En fecha: ".odbc2time_ampm(date("d/m/Y H:i"));
        $obj_mail->Body= $body;

        $obj_mail->addStringAttachment($message,$attachment);

        $usuario= $this->accept_mail_user_list[$id_usuario];
        $calendar= "{$usuario['nombre']} ({$usuario['cargo']})";

        $obj_mail->_AddReplayTo($usuario['email'], $calendar);
        $obj_mail->_addAddress($usuario['email'], $calendar);
/*
        if ($this->radio_user) {
            reset($array_participant);
            foreach ($array_participant as $participant) {
                if ($participant['id_usuario'] == $this->id_calendar)
                    continue;
                $responsable= $this->accept_mail_user_list[$participant['id_usuario']];
                $organizer= "{$responsable['nombre']} ({$responsable['cargo']})";

                $obj_mail->_AddAddress($participant['email'], $organizer);
            }
        }
*/
        if (is_null($sql))
            return true;
        $result= false;
        if (!is_null($sql))
            $result= $obj_mail->send();
        /*
        if ($result && !is_null($sql))
            $this->do_multi_sql_show_error('send_outlook', $sql);
         */
        return $result;
    }

    public function apply() {
        $this->obj= new Tevento($this->clink);
        $this->obj->SetYear($this->year);
        $obj_user= new Tusuario($this->clink);

        $this->accept_mail_user_list= array();
        $this->obj->SetIdUsuario($this->id_calendar);

        $obj_user->Set($this->id_calendar);
        $this->accept_mail_user_list[$this->id_calendar]= array('id'=>$obj_user->GetId(), 'usuario'=>$obj_user->GetUsuario(),
                    'nombre'=>$obj_user->GetNombre(), 'cargo'=>$obj_user->GetCargo(), 'email'=>$obj_user->GetMail_address(),
                    'array_eventos'=>array());

        if ($this->extend != 'A') {
            $fecha= $this->year.'-'.str_pad($_POST['month'], 2, '0',STR_PAD_LEFT).'-01';

            if ($this->signal == 'calendar')
                $this->obj->SetIfEmpresarial(null);
            else
                $this->obj->SetIfEmpresarial(1);

            $id_evento_ref= $this->extend == 'U' ? $this->id_evento_ref : null;
            $this->array_eventos= $this->obj->get_eventos_by_period($fecha, $this->extend, $id_evento_ref, true, true);

        } else {
            $this->obj->Set($this->id);

            $this->array_eventos[]= array('id'=>$this->id, 'id_code'=>$this->id_code, 'id_usuario'=>$this->id_calendar,
                'evento'=>$this->obj->GetNombre(), 'lugar'=>$this->obj->GetLugar(), 'descripcion'=>$this->obj->GetDescripcion(),
                'fecha_inicio'=>$this->obj->GetFechaInicioPlan(),'fecha_fin'=>$this->obj->GetFechaFinPlan(), 'outlook'=>$this->obj->get_outlook(),
                'id_responsable'=>$this->id_responsable_ref, 'id_tarea'=>$this->obj->GetIdTarea(), 'id_tarea_code'=>$this->obj->get_id_tarea_code(),
                'id_tematica'=>$this->obj->GetIdTematica(), 'id_tematica_code'=>$this->obj->get_id_tematica_code(),
                'rechazado'=>$this->obj->GetRechazado(), 'cumplimiento'=>$this->obj->GetCumplimiento());
        }

        $cant_mail= 0;
        foreach ($this->array_eventos as $evento) {
            if ($evento['outlook'])
                continue;
            ++$cant_mail;

            if (!array_key_exists($evento['id_responsable'], $this->accept_mail_user_list)) {
                $obj_user->Set($evento['id_responsable']);
                $this->accept_mail_user_list[$evento['id_responsable']]= array('id'=>$obj_user->GetId(), 'usuario'=>$obj_user->GetUsuario(),
                        'nombre'=>$obj_user->GetNombre(), 'cargo'=>$obj_user->GetCargo(), 'email'=>$obj_user->GetMail_address(),
                        'array_eventos'=>array());
            } else {
                $this->accept_mail_user_list[$evento['id_responsable']]['array_eventos'][]= $evento['id'];
            }

            if (isset($array_usuarios)) unset($array_usuarios);
            $array_usuarios= array();
            $cant= $this->obj->_get_users($evento['id'], $array_usuarios);

            if ($cant) {
                foreach ($array_usuarios as $user) {
                    if (array_key_exists($user['id_usuario'], $this->accept_mail_user_list)) {
                        $this->accept_mail_user_list[$user['id_usuario']]['array_eventos'][]= $evento['id'];
                        continue;
                    }

                    $obj_user->Set($user['id_usuario']);
                    $this->accept_mail_user_list[$user['id_usuario']]= array('id'=>$obj_user->GetId(), 'usuario'=>$obj_user->GetUsuario(),
                                    'nombre'=>$obj_user->GetNombre(), 'cargo'=>$obj_user->GetCargo(), 'email'=>$obj_user->GetMail_address(),
                                    'array_eventos'=>array());
                    $this->accept_mail_user_list[$user['id_usuario']]['array_eventos'][]= $evento['id'];
        }   }   }

        if ($cant_mail && $this->radio_user) {
            foreach ($this->accept_mail_user_list as $user) {
                $result= $this->send_outlook($user['id_usuario']);
                if (!$result) {
                    $this->error.= "ERROR: El servidor de correo no responde. No se ha establecido la conexion.";
                    break;
        }   }   }

        if ($cant_mail && (($this->radio_user && !array_key_exists($this->id_calendar, $this->accept_mail_user_list)) || !$this->radio_user)) {
            $result= $this->send_outlook($this->id_calendar);
            if (!$result)
                $this->error.= "ERROR: El servidor de correo no responde. No se ha establecido la conexion.";
        }

        if (is_null($this->error)) {
?>
            cerrar();
        <?php  } else { ?>
            alert("<?=$this->error?>", function(ok) {
                if (ok) cerrar();
            });

<?php
        }
    }
}
?>

<script type="text/javascript">
    <?php if (!$ajax_win) { ?>
    $(document).ready(function() {
        setInterval('setChronometer()',1);

        $('#body-log table').mouseover(function() {
            _moveScroll= false;
        });
        $('#body-log table').mouseout(function() {
            _moveScroll= true;
        });
    <?php } ?>

        <?php
        $interface= new Tinterface($clink);

        $badger= new Tbadger();
        $badger->SetLink($clink);
        $badger->SetYear($interface->GetYear());
        $badger->set_user_date_ref();
        $badger->set_tusuarios();

        $interface->apply();
        ?>
    });
</script>