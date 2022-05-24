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

require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/time.class.php";
require_once "class/usuario.class.php";
require_once "class/grupo.class.php";
require_once "class/peso.class.php";

require_once "class/proceso_item.class.php";

require_once "class/tmp_tables_planning.class.php";
require_once "class/register_planning.class.php";
require_once "class/tematica.class.php";
require_once "class/evento.class.php";
require_once "class/mail.class.php";

require_once "class/badger.class.php";
require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {
    private $to_print;
    private $id_asistencia_resp;
    private $id_asistencia_resp_code;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TplanningInterface::__construct($clink);

        $this->to_print= $_GET['to_print'];
        $this->ifaccords= $_POST['ifaccords'];
        $this->id_evento= !empty($_POST['id_evento']) ? $_POST['id_evento'] : $_GET['id_evento'];
    }

    private function setPrevAccords() {
        $cant_prev= $_POST['cant_matter_prev'];
        if (empty($cant_prev))
            return;

        $this->obj_matter->SetIdProceso($_SESSION['local_proceso_id']);
        $this->obj_matter->set_id_proceso_code($_SESSION['local_proceso_id_code']);
        $this->obj_matter->SetIdResponsable_eval($_SESSION['id_usuario']);

        for ($i=1; $i <= $cant_prev; $i++) {
            $id= $_POST['id_matter_prev_'.$i];
            $_value= $_POST['tab_accords_prev_'.$i];
            $value= $_POST['cumplimiento_prev_'.$i];

            $this->obj_matter->SetNumero($_POST['numero_matter_prev_'.$i]);

            if ($_value != $value) {
                $this->obj_matter->SetCumplimiento($value);
                $this->obj_matter->SetIdResponsable_eval($_POST['id_responsable_eval_prev_'.$i]);
                $this->obj_matter->SetEvaluacion(trim($_POST['observacion_accords_prev_'.$i]));
                $this->obj_matter->SetEvaluado(time2odbc($_POST['time_accords_prev_'.$i]));

                $this->obj_matter->update_cump_matter($id, true);
        }   }
    }

    /**
     * @param $id El evento(reunion) al que se le agregaran tematicas o acuerdos
     */
    private function set_tematica() {
        $this->obj_matter->SetYear($this->year);
        $this->obj_matter->set_cronos($this->cronos);

        if (($this->action == 'update' || $this->action == 'delete') && !empty($this->id)) {
            $this->obj_matter->Set($this->id);
            $this->id_tematica= $this->obj_matter->GetIdTematica();
            $this->id_tematica_code= $this->obj_matter->get_id_tematica_code();
        }

        if ($this->action == 'add') {
            $this->ifaccords ? $this->obj_matter->SetIfaccords(1) : $this->obj_matter->SetIfaccords(null);

            $this->obj_matter->SetIdEvento($this->id_evento);
            $this->obj_matter->set_id_evento_code($this->id_evento_code);
            $this->obj_matter->SetIdProceso($_SESSION['local_proceso_id']);
            $this->obj_matter->set_id_proceso_code($_SESSION['local_proceso_id_code']);
        }

        $this->id_asistencia_resp= $_POST['asistencia_resp'];
        $this->id_asistencia_resp_code= $_POST['asistencia_resp_code_'.$this->id_asistencia_resp];
        $this->obj_matter->SetDescripcion(trim($_POST['observacion_matter']));
        $this->obj_matter->SetIdAsistencia_resp($this->id_asistencia_resp);
        $this->obj_matter->set_id_asistencia_resp_code($this->id_asistencia_resp_code);

        $time= ampm2odbc($_POST['hora_matter']);
        $fecha= $this->ifaccords ? date2odbc($_POST['fecha_matter']) : $this->obj->GetFechaInicioPlan();
        $this->fecha_inicio_plan= substr($fecha, 0, 10).' '.$time;

        $this->obj_matter->SetFechaInicioPlan($this->fecha_inicio_plan);
        $this->obj_matter->SetNumero($_POST['numero_matter']);

        if ($this->action == 'add') {
            $this->error= $this->obj_matter->add();

            if (is_null($this->error)) {
                $this->id_tematica= $this->obj_matter->GetIdTematica();
                $this->id_tematica_code= $this->obj_matter->get_id_tematica_code();
            }
        }

        if ($this->action == 'update') {
            $this->error= $this->obj_matter->update();
        }

        if ($this->action == 'add' || $this->action == 'update') {
            if ($this->ifaccords)  {
                $this->set_evento($this->obj_matter);
            } else {
                $this->obj->SetIfaccords($this->ifaccords);
                $this->obj->SetIdTematica($this->id_tematica);
                $this->obj->set_id_tematica_code($this->id_tematica_code);

                $this->set_usuarios();
                $this->set_grupos();
            }
        }

        if ($this->action == 'delete') {
            $this->obj_matter->SetIdProceso(null);
            $this->obj_matter->eliminar($this->id);

            $this->obj_code->reg_delete('ttematicas', 'id_code', $this->id_tematica_code);
        }
    }

    private function set_evento() {
        $this->obj= new Tevento($this->clink);
        $this->obj->set_cronos($this->cronos);
        $this->obj->SetIfaccords($this->ifaccords);
        $this->obj->SetIdTematica($this->id_tematica);
        $this->obj->set_id_tematica_code($this->id_tematica_code);
        $this->obj->SetFechaInicioPlan($this->fecha_inicio_plan);

        $id_asistencia= $this->obj_matter->GetIdAsistencia_resp();
        $this->id_responsable= $this->obj_matter->array_id_asistencia_usuario[$id_asistencia];

        if ($this->action == 'update') {
            $this->id_evento= $this->obj_matter->get_id_evento_accords();
            $this->id_evento_code= $this->obj_matter->get_id_evento_accords_code();

            $this->obj->SetIdResponsable($this->id_responsable);
            $this->obj->SetIdEvento($this->id_evento);
            $this->obj->set_id_evento_code($this->id_evento_code);
            $this->obj->Set();
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetFechaInicioPlan($this->obj_matter->GetFechaInicioPlan());
            $this->obj->SetFechaFinPlan($this->obj_matter->GetFechaInicioPlan());
            $this->obj->SetNombre($this->obj_matter->GetDescripcion(), false);

            $this->obj->SetIdResponsable($this->id_responsable);
            $this->obj->toshow= _EVENTO_INDIVIDUAL;

            $this->obj->SetIdProceso($this->obj_matter->GetIdProceso());
            $this->obj->set_id_proceso_code($this->obj_matter->get_id_proceso_code());
        }

        if ($this->action == 'add') {
            $this->error= $this->obj->add();

            if (is_null($this->error)) {
                $this->id_evento= $this->obj->GetIdEvento();
                $this->id_evento_code= $this->obj->get_id_evento_code();

                $this->obj_matter->set_evento_accords($this->id_evento, $this->id_evento_code);
            }
        }

        if ($this->action == 'update') {
            $this->error= $this->obj->update();
        }

        if (is_null($this->error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->className= 'Tevento';
            $found= $this->action == 'add' ? false : true;
            $this->_id_evento= $this->id_evento;
            $this->_id_evento_code= $this->id_evento_code;

            $this->setting($this->action, $found);
        }
    }

    protected function set_usuarios_from_array() {
        $this->obj->SetIdGrupo(null);

        reset($this->accept_user_list);
        foreach ($this->accept_user_list as $id => $user) {
            $this->obj->SetIdUsuario($id);
            $this->obj->setUsuario('add');
        }

        reset($this->denied_user_list);
        foreach ($this->denied_user_list as $id => $user) {
            $this->obj->SetIdUsuario($id);
            $this->obj->setUsuario('delete');
        }

        if (!array_key_exists($this->id_responsable, (array)$this->accept_user_list)) {
            $this->obj->SetIdUsuario($this->id_responsable);
            $this->obj->setUsuario('add');
        }
    }

    private function set_usuarios() {
        $naccepted= 0;
        $user_ref_date= !is_null($this->user_date_ref) ? $this->user_date_ref : $this->fecha_inicio_plan;

        $obj_user= new Tusuario($this->clink);
        $obj_user->set_use_copy_tusuarios(false);
        $obj_user->set_user_date_ref($user_ref_date);
        $result= $obj_user->listar(null);

        $this->obj->SetIdGrupo(null);

        while ($row= $this->clink->fetch_array($result)) {
            $id= $_POST['multiselect-users_user'.$row['_id']];
            $_id= $_POST['multiselect-users_init_user'.$row['_id']];

            $this->obj->SetIdUsuario($row['_id']);

            if (!empty($id) && empty($_id)) {
                ++$naccepted;
                if (!array_key_exists($row['_id'], (array)$this->accept_user_list)) {
                    $this->accept_user_list[$row['_id']]= $row['_id'];
                    $this->obj->setUsuario('add');
            }   }

            if (empty($id) && !empty($_id)) {
                if (!array_key_exists($row['_id'], (array)$this->denied_user_list)) {
                    $this->denied_user_list[$row['_id']]= $row['_id'];
                    $this->obj->setUsuario('delete');
        }   }   }

        if (!array_key_exists($this->id_responsable, (array)$this->accept_user_list)) {
            $this->obj->SetIdUsuario($this->id_responsable);
            $this->obj->setUsuario('add');
        }

        unset($obj_user);
    }

    protected function set_grupos_from_array() {
        $this->obj->SetIdUsuario(null);

        reset($this->accept_group_list);
        foreach ($this->accept_group_list as $id => $grp) {
            $this->obj->SetIdGrupo($id);
            $this->obj->setGrupo('add');
        }

        reset($this->denied_group_list);
        foreach ($this->denied_group_list as $id => $grp) {
            $this->obj->SetIdGrupo($id);
            $this->obj->setGrupo('delete');
        }
    }

    private function set_grupos() {
        $error= null;
        $obj_grp= new Tgrupo($this->clink);
        $obj_grp->set_user_date_ref($this->fecha_fin);
    	$result= $obj_grp->listar();

    	while ($row= $this->clink->fetch_array($result)) {
            $id= $_POST['multiselect-users_grp'.$row['_id']];
            $_id= $_POST['multiselect-users_init_grp'.$row['_id']];

            $this->obj->cleanListaUser();
            $this->obj->SetIdUsuario(null);
            $this->obj->SetIdGrupo($row['_id']);

            if (!empty($id) || !empty($_id)) {
                $this->obj->push2ListaUserGroup($row['_id'], true);
                $user_array= $this->obj->get_list_user();
            } else
                continue;

            if (!empty($id) && empty($_id)) {
                if (!array_key_exists($row['_id'], $this->accept_group_list)) {
                    $this->accept_group_list[$row['_id']]= $row['_id'];
                    $this->accept_mail_user_list= array_merge_overwrite((array)$this->accept_mail_user_list, (array)$user_array);
                    $this->obj->setGrupo('add');
                }
            }

            if (empty($id) && !empty($_id)) {
                if (!array_key_exists($row['_id'], $this->denied_group_list)) {
                    $this->denied_group_list[$row['_id']]= $row['_id'];
                    $this->denied_mail_user_list= array_merge_overwrite((array)$this->denied_mail_user_list, (array)$user_array);
                    $this->obj->setGrupo('delete');
	}   }   }

        unset($obj_user);
        $this->error= $error;
    }

    protected function set_tematica_list($id, $id_code) {
        $cant= $_POST['cant_matter'];
        if (empty($cant))
            return null;
        $array_eventos_accords= null;

        $this->obj_matter->SetYear($this->year);
        $this->ifaccords ? $this->obj_matter->SetIfaccords(1) : $this->obj_matter->SetIfaccords(null);

        $this->obj_matter->SetIdEvento($id);
        $this->obj_matter->set_id_evento_code($id_code);
        $this->obj_matter->SetIdProceso($_SESSION['id_entity']);
        $this->obj_matter->set_id_proceso_code($_SESSION['id_entity_code']);

        for ($i= 1; $i <= $cant; ++$i) {
            reset($this->accept_process_list);
            reset($this->denied_process_list);

            $id_tematica= $_POST['id_matter_'.$i];

            $this->obj_matter->SetDescripcion(trim($_POST['matter_'.$i]));

            $id_asistencia= $_POST['id_asistencia_resp_'.$i];
            $id_asistencia_code= get_code_from_table("tasistencias", $id_asistencia);
            $this->obj_matter->SetIdAsistencia_resp($id_asistencia);
            $this->obj_matter->set_id_asistencia_resp_code($id_asistencia_code);

            $time= $this->ifaccords ? ampm2odbc(time2odbc($_POST['time_matter_'.$i])) : ampm2odbc($_POST['time_matter_'.$i]);
            $fecha= $this->ifaccords ? date2odbc($_POST['time_matter_'.$i]) : $this->obj->GetFechaInicioPlan();
            $fecha= substr($fecha, 0, 10).' '.$time;

            $this->obj_matter->SetFechaInicioPlan($fecha);
            $this->obj_matter->SetNumero($_POST['numero_matter_'.$i]);

            $id_evento_accords= $this->ifaccords ? $_POST['id_evento_accords_'.$i] : null;
            $id_evento_accords_code= $this->ifaccords ? $_POST['id_evento_accords_code_'.$i] : null;

            if ($this->ifaccords
                && ((empty($id_tematica) && $_POST['cumplimiento_'.$i]) || (!empty($id_tematica) && $_POST['tab_matter_'.$i] == 2))) {

                $value= $_POST['cumplimiento_'.$i];
                $value= empty($value) ? _NO_INICIADO : $value;

                $this->obj_matter->SetCumplimiento($value);
                $this->obj_matter->SetIdResponsable_eval($_POST['id_responsable_eval_'.$i]);
                $this->obj_matter->SetEvaluacion(trim($_POST['observacion_accords_'.$i]));
                $this->obj_matter->SetEvaluado(time2odbc($_POST['time_accords_'.$i]));

                $this->obj_matter->update_cump_matter($id_tematica, true);
                $this->register_accords($id_evento_accords, $id_evento_accords_code);
        }   }
    }

    private function register_accords($id_evento_accords, $id_evento_accords_code) {
        $obj_register= new Tregister_planning($this->clink);
        $this->obj_matter->copy_in_object($obj_register);

        $obj_register->SetIdEvento($id_evento_accords);
        $obj_register->set_id_evento_code($id_evento_accords_code);
        $obj_register->_get_users($this->id_evento, $array_usuarios);

        $obj_register->SetIdResponsable($_SESSION['id_usuario']);

        $id_asistencia= $this->obj_matter->GetIdAsistencia_resp();
        $id_responsable= $this->obj_matter->array_id_asistencia_usuario[$id_asistencia];
        $obj_register->SetIdUsuario($id_responsable);
        $obj_register->add_cump();

        foreach ($array_usuarios as $user) {
            $obj_register->SetIdUsuario($user['id']);
            $obj_register->add_cump();
        }
    }

    private function add_from_eventos_array() {
        if (count($this->array_eventos) == 0)
            return;

        foreach ($this->array_eventos as $evento) {
            if (!empty($evento['periodicidad']))
                continue;

            $this->obj->SetIdEvento($evento['id']);
            $this->obj->set_id_evento_code($evento['id_code']);
            $fecha= $evento['fecha_inicio_plan'];
            $fecha= substr($fecha, 0, 10).' '.$this->time_inicio;
            $this->obj->SetFechaInicioPlan($fecha);

            $this->error= $this->obj->add($this->id_tematica, $this->id_tematica_code);

            if (is_null($this->error)) {
                $this->set_usuarios_from_array();
                $this->set_grupos_from_array();
            } else
                break;
        }
    }

    private function update_from_tematicas_array() {
        $array_tematicas= $this->obj->get_tematicas_by_tematica($this->id);

        foreach ($array_tematicas as $id => $matter) {
            $this->obj->SetIdTematica($matter['id']);
            $this->obj->set_id_tematica_code($matter['id_code']);

            $this->obj->SetObservacion($this->observacion);
            $fecha= substr($matter['fecha_inicio_plan'], 0, 10).' '.$this->time_inicio;
            $this->obj->SetFechaInicioPlan($fecha);
            $this->obj->SetIdResponsable($matter['id_responsable']);

            $this->error= $this->obj->update();

            if (is_null($this->error)) {
                $this->obj->SetIdEvento(null);
                $this->obj->set_id_evento_code(null);

                $this->set_usuarios_from_array();
                $this->set_grupos_from_array();
            } else
                break;
        }
    }

    public function apply() {
        global $ajax_win;

        $this->error= null;
        $fecha_inicio_plan= null;
        $time= ampm2odbc($_POST['hora_matter']);

        $this->obj_matter= new Ttematica($this->clink);
        $this->obj_matter->fill_array_asistencia_usuarios();

        $this->obj= new Ttematica($this->clink);
        $this->obj->SetYear($this->year);

        if (!empty($this->id)) {
            $this->obj->Set($this->id);
            $this->id_code= $this->obj->get_id_code();
            $fecha_inicio_plan= $this->obj->GetFechaInicioPlan();
        }

        $this->obj->set_cronos($this->cronos);

        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetIdProceso($this->id_proceso);
        $obj_prs->GetProcesoEvento($this->id_evento, $this->id_proceso);
        unset($obj_prs);

        $obj_evento= new Tevento($this->clink);
        $obj_evento->SetYear($this->year);
        $obj_evento->Set($this->id_evento);

        $array_evento= array('id'=>$obj_evento->GetId(), 'id_code'=>$obj_evento->get_id_code(), 
            'fecha_inicio_plan'=>$obj_evento->GetFechaInicioPlan(), 'id_responsable'=>$obj_evento->GetIdResponsable(), 
            'id_auditoria'=>$obj_evento->GetIdAuditoria(), 'id_auditoria_code'=>$obj_evento->get_id_auditoria_code(), 
            'toshow'=>$obj_evento->toshow, 'cant_days'=>$obj_evento->cant_days, 'flag'=>0);

        $this->denied_mail_user_list= null;
        $this->array_eventos= null;

        if ($this->action == 'add' || $this->action == 'update') {
            $this->array_eventos= $obj_evento->get_child_events_by_table('teventos', $this->id_evento);

            $this->descripcion= trim($_POST['observacion_matter']);
            $this->obj->SetDescripcion($this->descripcion);

            $this->id_asistencia_resp= $_POST['asistencia_resp'];
            $this->id_asistencia_resp_code= $_POST['asistencia_resp_code_'.$this->id_asistencia_resp];
            $this->obj->SetIdAsistencia_resp($this->id_asistencia_resp);
            $this->obj->set_id_asistencia_resp_code($this->id_asistencia_resp_code);

            $this->time_inicio= ampm2odbc($_POST['hora_matter']);
            $this->fecha_inicio_plan= substr($array_evento['fecha_inicio_plan'], 0, 10).' '.$this->time_inicio;
            $this->obj->SetFechaInicioPlan($this->fecha_inicio_plan);
            $this->obj->SetNumero($_POST['numero_matter']);

            $this->obj->SetIdProceso($_SESSION['local_proceso_id']);
            $this->obj->set_id_proceso_code($_SESSION['local_proceso_id_code']);
        }

        if ($this->action == 'add') {
            $this->obj->SetIdEvento($this->id_evento);
            $this->obj->set_id_evento_code($array_evento['id_code']);

            $this->error= $this->obj->add();

            if (is_null($this->error)) {
                $this->id_tematica= $this->obj->GetIdTematica();
                $this->id_tematica_code= $this->obj->get_id_tematica_code();

                $this->obj->SetIdEvento($array_evento['id']);
                $this->obj->set_id_evento_code($array_evento['id_code']);

                $this->set_usuarios();
                $this->set_grupos();

                $this->add_from_eventos_array();
            }
        }

        if ($this->action == 'update') {
            $this->obj->SetIdTematica($this->id);
            $this->obj->SetIdEvento(null);
            $this->obj->SetIdProceso(null);

            $array_tematicas= $this->obj->get_tematicas_by_tematica($this->id);
            $cant= $this->obj->GetCantidad();

            if (empty($cant)) {
                $fecha_inicio_plan= substr($fecha_inicio_plan, 0, 10).' '.$this->time_inicio;
                $this->obj->SetFechaInicioPlan($fecha_inicio_plan);
            }

            $this->error= $this->obj->update();

            if (empty($this->error)) {
                $this->obj->SetIdEvento(null);
                $this->obj->set_id_evento_code(null);

                $this->set_usuarios();
                $this->set_grupos();

                $this->update_from_tematicas_array();
            }
        }

        if ($this->action == 'delete') {
            $observacion= "No.".$this->obj->GetNumero."  ".$this->obj->GetObservacion();

            $this->error= $this->obj->eliminar($this->id);

            if (is_null($this->error)) {
                $obj_code= new Tcode($this->clink);
                $obj_code->SetObservacion($observacion);
                $obj_code->reg_delete('ttematicas','id_code', $this->id_code);
            }
        }

        if ($ajax_win) {
        ?>
            close_matter(true);
        <?php
        }

        if ($this->action == 'delete' || $this->action == 'update')
            $this->action= 'add';
        ?>
           url= "../form/lmatter.php?id_evento=<?=$this->id_evento?>&id_proceso=<?=$this->id_proceso?>&error=<?=urlencode($this->error)?>";
           url+= "&action=<?= $this->action?>";
           self.location.href= url;
        <?php
    }

    public function apply_list() {
        global $ajax_win;

        $this->obj= new Tevento($this->clink);

        $this->obj_matter= new Ttematica($this->clink);
        $this->obj_matter->fill_array_asistencia_usuarios();

        if (!empty($this->id_evento) && ($this->action != 'edit' && $this->action != 'list')) {
            $this->obj->Set($this->id_evento);
            $this->id_evento_code= $this->obj->get_id_code();
            $this->year= date('Y', strtotime($this->obj->GetFechaInicioPlan()));
        }
        $this->obj->SetYear($this->year);

        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetYear($this->year);
        $obj_prs->SetIdProceso($this->id_proceso);
        $obj_prs->GetProcesoEvento($this->id_evento, $this->id_proceso);
        unset($obj_prs);

        $this->obj->set_cronos($this->cronos);

        $this->set_tematica_list($this->id_evento, $this->id_evento_code);
        $this->setPrevAccords();

        if ($ajax_win)
            $this->set_tematica();

        if (is_null($this->error)) {
            $url= "../print/matter.php?";
            $title= "ORDEN DEL DÍA";

            if ($this->ifaccords) {
                $url= "../print/accords.php?";
                $title= $this->to_print == 1 ? "RELACIÓN DE NUEVOS ACUERDOS" : "REVISIÓN DE ACUERDOS";
                $url.= $this->to_print == 1 ? "&prev=0" : "&prev=1";
            }

            $url.= "&id_evento=$this->id_evento&id_proceso=$this->id_proceso";
        ?>

        <?php if ($this->to_print == 1 || $this->to_print == 2) { ?>
            // prnpage= parent.show_imprimir('<?=$url?>&all_matter=0',"IMPRIMIENDO <?=$title?>","width=900,height=600,toolbar=no,location=no, scrollbars=yes");
            self.location.href= '<?=$url?>&all_matter=0';
            
        <?php } else { ?>
            <?php if ($ajax_win) { ?>
                close_matter(true);
            <?php } else { ?>
                opener.location.reload();
                self.close();
        <?php } } ?>

        <?php } else { ?>
            self.location.href='../form/fmatter.php?action=add&id_event=<?=$this->id?>&id_proceso=<?=$this->id_proceso ?>&error=<?=urlencode($this->error)?>'
        <?php
        }
    }
}
?>

<?php if (is_null($using_remote_functions)) { ?>
    <?php if (!$ajax_win) { ?>
                </div>
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
                $menu= !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];
                if (empty($menu))
                    $menu= 'form-list-matter';

                $interface= new Tinterface($clink);
                $menu == 'form-list-matter' ? $interface->apply_list() : $interface->apply();
                ?>
        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>