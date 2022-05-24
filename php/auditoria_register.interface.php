<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";

require_once "interface.class.php";
require_once "class/connect.class.php";
require_once "class/proceso_item.class.php";
require_once "class/document.class.php";

require_once "class/mail.class.php";
require_once "class/code.class.php";
require_once "class/badger.class.php";

require_once "register.interface.php";

$block_execute_apply_function= true;
require_once "evento_register.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
$ajax_win= true;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TEventoRegisterInterface {
    private $array_dates;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TEventoRegisterInterface::__construct($clink);

        $this->id_usuario= $this->id_calendar;

        $this->id_responsable= $_SESSION['id_usuario'];
        $this->id_proceso= $_POST['id_proceso'];
        $this->id_proceso_code= $_POST['id_proceso_code'];
        $this->tipo= $_POST['tipo'];

        $this->observacion= !empty($_POST['observacion']) ? trim($_POST['observacion']) : null;
        $this->descripcion= !empty($_POST['descripcion']) ? trim($_POST['descripcion']) : null;

        $this->cumplimiento= !empty($_POST['cumplimiento']) ? $_POST['cumplimiento'] : $_GET['cumplimiento'];
        if(empty($this->cumplimiento))
            $this->cumplimiento= null;

        $this->className= "Tauditoria";
    }

    /**
     * @param bool $parent
     * $parent se utiliza en la recursividad para eliminar las auditorias padres que han quedado vacias
     */
    private function _delete($parent= false) {
        $array= $this->set_delete_date();

        $this->array_auditorias= $this->obj->get_auditoria_by_period($array['date'], $array['extend'], $array['id']);

        foreach ($this->array_auditorias as $audit) {
            $this->array_eventos= $this->obj->get_child_events_by_auditoria($audit['id']);

            foreach ($this->array_eventos as $event) {
                $this->id_evento= $event['id'];
                $this->id_evento_code= $event['id_code'];
                $this->id_auditoria= $event['id_auditoria'];
                $this->id_auditoria_code= $event['id_auditoria_code'];

                $this->obj_reg->SetIdEvento($this->id_evento);
                $this->obj_reg->set_id_evento_code($this->id_evento_code);
                $this->obj_reg->SetIdAuditoria($this->id_auditoria);
                $this->obj_reg->set_id_auditoria_code($this->id_auditoria_code);

                $this->init_register_usuarios();
                $this->init_register_procesos();

                $this->delete_usuarios();
                $this->delete_procesos();
            }

            $deleted= $this->obj->delete($audit['id'], $audit['id_code']);
            if (!$deleted && !is_null($this->obj->error)) {
                $this->error= $this->obj->error;
                return $this->error; 
            }    
        }

        $deleted= $this->obj->delete($array['id'], $array['id_code']);
        if (!$deleted && !is_null($this->obj->error)) {
            $this->error= $this->obj->error;
            return $this->error; 
        } 

        return null;       
    }

    private function _init_reprograming() {
        global $day_feriados;

        $this->obj_sch= new Tschedule();
        $this->obj_sch->sunday= $this->obj->sunday;
        $this->obj_sch->saturday= $this->obj->saturday;
        $this->obj_sch->freeday= $this->obj->freeday;

        $go_delete= $this->radio_prs ? _DELETE_YES : _DELETE_NO;

        $_fecha_inicio= $this->obj->GetFechaInicioPlan();
        $_fecha_fin= $this->obj->GetFechaFinPlan();

        $fecha_inicio= date2odbc($_POST['fecha_inicio']);
        $this->fecha_inicio= $fecha_inicio;
        $this->time_inicio= ampm2odbc($_POST['hora_inicio']);
        $this->fecha_inicio_plan= "$this->fecha_inicio $this->time_inicio";

        $fecha_fin= date2odbc($_POST['fecha_fin']);
        $this->fecha_fin= $fecha_fin;
        $this->time_fin= ampm2odbc($_POST['hora_fin']);
        $this->fecha_fin_plan= "$this->fecha_fin $this->time_fin";

        $this->obj->SetIfEmpresarial($this->empresarial);
        $this->obj->SetIdTipo_evento($this->id_tipo_evento);
        $this->obj->set_id_tipo_evento_code($this->id_tipo_evento_code);

        if (strtotime($_fecha_inicio) != strtotime($this->fecha_inicio_plan) || strtotime($_fecha_fin) != strtotime($this->fecha_fin_plan)) {
            $this->obj->SetFechaInicioPlan($this->fecha_inicio_plan);
            $this->obj->SetFechaFinPlan($this->fecha_fin_plan);
            $this->obj->update_date();
        }

        $this->array_eventos= $this->obj->get_child_events_by_auditoria($this->id_auditoria, null, null);
        $this->observacion= "ACTIVIDAD DE CONTROL REPROGRAMADA (".odbc2time_ampm($this->cronos)."): ".$this->observacion;

        $this->obj_event= new Tevento($this->clink);
        $this->obj_event->SetIdEvento($this->id_evento);
        $this->obj_event->SetIdAuditoria($this->id_auditoria);

        $this->array_eventos= $this->obj->get_eventos_by_period(null, null);
    }

    private function _reprograming() {
        global $day_feriados;

        $fecha_inicio= date2odbc($_POST['fecha_inicio']);
        $fecha_fin= date2odbc($_POST['fecha_fin']);

        $this->_init_reprograming();
        $this->init_register_usuarios();
        $this->init_register_procesos();
        $this->get_participantes();
        $this->get_documentos();

        $this->obj->set_null_periodicity();
        $this->obj->toshow= $this->toshow;

        if (!empty($this->sendmail)) {
            $this->toMail("REPROGRAMADA");
        }

        $this->_id_auditoria= $this->id_auditoria;
        $this->_id_auditoria_code= $this->id_auditoria_code;

        $i = 0;
        do {
            $fecha_inicio= $this->obj_sch->get_work_day($fecha_inicio);
            $fecha_fin= date('Y-m-d', strtotime($fecha_inicio)) . ' ' . $this->time_fin;

            if (strtotime($fecha_fin) <= strtotime($this->fecha_fin_plan)) {
                $this->array_dates[$i++]= array('inicio' => $fecha_inicio, 'fin' => $fecha_fin, 'hit' => false);
            }
            $fecha_inicio= add_date($fecha_inicio, 1);
        } while (strtotime($fecha_inicio) <= strtotime($this->fecha_fin_plan));

        $cant= $i;
        $this->action= 'update';
        for ($i= 0; $i < $cant; ++$i) {
            $this->setting_freq($this->array_dates[$i]['inicio'], $this->array_dates[$i]['fin'], $this->array_dates[$i]['hit'], false);

            $this->fix_register_usuarios();
            $this->fix_register_procesos();
            $this->fix_participantes();
            $this->fix_documentos();
        }

        $this->updated= false;
        $this->if_in_date_intervals_all();
        $this->delete_periodic();
    }

    private function _copy() {
        $array_id_show= $this->copy_all == 2 ? $this->obj->lista_parent_eventos() : $this->array_id_show;

        foreach ($array_id_show as $id) {
            if (isset($this->obj)) unset($this->obj);
            $this->obj= new Tauditoria($this->clink);
            $this->obj->SetIdAuditoria($id);
            $this->obj->Set();

            $id_entity= $this->obj->GetIdProceso();
            if ($id_entity != $_SESSION['id_entity'])
                continue;

            $this->if_valid_tipo_evento();
            if ($this->if_synchronize($id_entity))
                continue;

            $id_auditoria_ref= $this->obj->get_id_auditoria_ref();
            if (!empty($id_auditoria_ref)) {
                unset($this->obj);
                $this->obj= new Tauditoria($this->clink);
                $this->obj->SetIdAuditoria($id_auditoria_ref);
                $this->obj->Set();
            }

            $array= $this->obj->if_exists_copyto($this->to_year);

            $this->obj->set_cronos($this->cronos);
            $this->obj->action= $this->action;
            $this->obj->SetIdUsuario($_SESSION['id_usuario']);
            $this->obj->SetYear($this->year);
            $this->obj->SetMonth($this->month);

            $this->obj->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $this->radio_prs, $this->to_year, $array);
        }
    }

    public function apply() {
        $this->obj= new Tauditoria($this->clink);
        $this->obj_reg= new Tplanning($this->clink);

        if ($this->menu != 'fcopy' && $this->action != 'copy') {
            $this->obj->SetIdAuditoria($this->id);
            $this->obj->Set();
            $this->id_code = $this->obj->get_id_code();

            $this->id_auditoria= $this->id;
            $this->id_auditoria_code= $this->id_code;
            $this->id_evento= null;
            $this->id_evento_code= null;
            $this->id_auditoria_ref = $this->obj->get_id_auditoria_ref();
            $this->id_auditoria_ref_code = $this->obj->get_id_auditoria_ref_code();

            $this->id_responsable_ref= $this->obj->GetIdResponsable();

            $this->empresarial= $this->obj->GetIfEmpresarial();
            $this->id_tipo_evento= $this->obj->GetIdTipo_evento();
            $this->id_tipo_evento_code= $this->obj->get_id_tipo_evento_code();

            $this->_id_proceso= $this->obj->GetIdProceso();
            $this->_id_proceso_code= $this->obj->get_id_proceso_code();

            $this->obj->set_cronos($this->cronos);
            $this->obj->SetYear($this->year);
            $this->obj->SetMonth($this->month);
            $this->obj->action= $this->action;
            $this->toshow= $this->obj->get_toshow_plan();

            $this->fecha_inicio_plan= $this->obj->GetFechaInicioPlan();
        }

        $this->obj_reg->set_cronos($this->cronos);
        $this->obj_reg->SetYear($this->year);
        $this->obj_reg->SetMonth($this->month);

        if ($this->menu != 'fcopy') {
            $this->obj->SetIdResponsable($this->id_responsable);
            $this->obj->SetObservacion($this->observacion);
            $this->obj->SetDescripcion($this->descripcion);
            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);
        }

        $this->obj->SetYear($this->year);
        $this->obj->SetIfEmpresarial(null);

        $this->init_entity();
        if (!empty($this->id_proceso))
            $this->init_cascade_down();

        if ($this->menu != 'fcopy' && $this->extend != 'A') {
            $fecha= $_POST['year'].'-'.str_pad($_POST['month'], 2, '0',STR_PAD_LEFT).'-'.str_pad($_POST['day'], 2, '0',STR_PAD_LEFT);

            if ($this->signal == 'calendar')
                $this->obj->SetIfEmpresarial(null);
            if ($this->signal == 'mensual_plan')
                $this->obj->SetIfEmpresarial(1);
            if ($this->signal == 'anual_plan' || $this->signal == 'anual_plan_audit')
                $this->obj->SetIfEmpresarial(2);

            $this->array_auditorias= null;
            $this->obj->SetIdUsuario(null);
            $this->array_eventos= $this->obj->get_eventos_by_period($fecha, $this->extend);
            $this->array_auditorias= $this->obj->get_auditoria_by_period($fecha, $this->extend, $this->id);

            $array= array('id'=> $this->id, 'id_code'=> $this->id_code, 'id_usuario'=>null,
                'id_responsable'=> $this->id_responsable_ref, 'id_auditoria'=> $this->id_auditoria_ref,
                'id_auditoria_code'=> $this->id_auditoria_ref_code, 'rechazado'=>null, 'toshow'=>$this->toshow, 
                'evento'=> null, 'lugar'=> null, 'descripcion'=> null, 'id_user_asigna'=>$this->obj->GetIdusuario(),
                'fecha_inicio'=> $this->fecha_inicio_plan, 'fecha_fin'=> $this->fecha_fin_plan,
                'id_proceso'=> $this->id_proceso, 'id_proceso_code'=> $this->id_proceso_code);

            $this->array_auditorias[]= $array;

        } else {
            $array= array('id'=> $this->id, 'id_code'=> $this->id_code, 'id_usuario'=>null,
                'id_responsable'=> $this->id_responsable_ref, 'id_auditoria'=> $this->id_auditoria_ref,
                'id_auditoria_code'=> $this->id_auditoria_ref_code, 'rechazado'=>null, 'toshow'=>$this->toshow, 
                'evento'=> null, 'lugar'=> null, 'descripcion'=> null, 'id_user_asigna'=>$this->obj->GetIdusuario(),
                'fecha_inicio'=> $this->fecha_inicio_plan, 'fecha_fin'=> $this->fecha_fin_plan,
                'id_proceso'=> $this->id_proceso, 'id_proceso_code'=> $this->id_proceso_code);
            $this->array_auditorias[]= $array;
            
            $this->obj->get_child_events_by_table("tauditorias", $this->id);
            $this->array_eventos= $this->obj->array_eventos;
        }

        $this->obj->copy_in_object($this->obj_reg);

        if ($this->menu == 'fregevento')
            $this->_register();
        if ($this->menu == 'fdelete')
            $this->_delete();
        if ($this->menu == 'freproevento') {
            $this->_reprograming();
        }
        if ($this->menu == 'fdelegate')
            $this->_delegate();

        if ($this->menu == 'fcopy') {
            $this->obj->SetYear($this->year);

            if ($this->action == 'repro') {
                $this->obj->SetIdAuditoria($this->id);
                $this->obj->Set();

                $this->if_valid_tipo_evento();
                $if_synchronized= $this->if_synchronize($this->obj->GetIdProceso()) ? true : false ;
                $array= $this->obj->if_exists_copyto($this->to_year);
                $if_copy_exists= !empty($array) ? true : false;
                $if_entity= array_key_exists($this->obj->GetIdProceso(), $this->array_procesos_entity) ? true : false;

                if (!$if_synchronized && $if_entity) {
                    $this->obj->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $this->radio_prs, $this->to_year, $array);

                } else {
                    if ($if_synchronized && !$if_entity)
                        $this->obj->error= "No puede copiar una tarea generada desde otra Unidad Organizativa.";
                    if ($if_copy_exists)
                        $this->obj->error= "Esta actividad ya fue copiada al $this->to_year";
                }

                $this->obj->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $this->radio_prs);
            }

            if ($this->action == 'copy') {
                $this->_copy();
            }
        }

        if (is_null($this->error)) {
      ?>
            cerrar();

    <?php  } else { ?>
        alert("<?=$this->error?>", function(ok) {
            cerrar();
        });
    <?php
        }
    }
}
?>
        </div>

<?php if (!$ajax_win) { ?>
    </body>
</html>
<?php } else { ?>
</div>
<?php } ?>

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
        $interface->apply();
        ?>

    <?php if (!$ajax_win) { ?>
    });
    <?php } ?>
</script>