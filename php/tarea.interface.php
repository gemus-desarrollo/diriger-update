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
require_once "class/time.class.php";
require_once "class/tarea.class.php";
require_once "class/riesgo.class.php";
require_once "class/nota.class.php";
require_once "class/proyecto.class.php";
require_once "class/regtarea.class.php";

require_once "class/kanban.class.php";

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends  TplanningInterface {
    private $obj_tr;
    private $obj_evt;

    private $texttitle;
    private $_id_proyecto;
    private $id_tarea_grupo,
            $id_tarea_grupo_code;

    protected $id_kanaban_column,
              $id_kanaban_column_code;

    public function __construct($clink= null) {
        $this->id_riesgo= !empty($_POST['id_riesgo']) ? $_POST['id_riesgo'] : $_GET['id_riesgo'];
        $this->id_nota= !empty($_POST['id_nota']) ? $_POST['id_nota'] : $_GET['id_nota'];

        $this->id_proyecto= !empty($_POST['id_proyecto']) ? $_POST['id_proyecto'] : $_GET['id_proyecto'];
        $this->id_programa= !empty($_POST['id_programa']) ? $_POST['id_programa'] : $_GET['id_programa'];

        $this->clink= $clink;
        TplanningInterface::__construct($clink);

        if (!empty($this->id_riesgo)) {
            $plus= $this->texttitle.= !empty($this->texttitle) ? ", " : null;
            $this->texttitle.= $plus."RIESGO";
        }
        if (!empty($this->id_nota)) {
            $plus= $this->texttitle.= !empty($this->texttitle) ? ", " : null;
            $this->texttitle.= $plus."NOTA";
        }
        if (!empty($this->id_proyecto)) {
            $plus= $this->texttitle.= !empty($this->texttitle) ? ", " : null;
            $this->texttitle.= $plus."PROYECTO";
        }
        if (!empty($this->id_programa)) {
            $plus= $this->texttitle.= !empty($this->texttitle) ? ", " : null;
            $this->texttitle.= $plus."PROGRAMA";
        }
        if (is_null($this->texttitle))
            $this->texttitle= "TAREA";
    }

    private function set_kanban_proyecto() {
        if (empty($this->id_proyecto) 
            || (!empty($this->id_proyecto) && $this->id_proyecto == $this->_id_proyecto))
            return null;

        $obj_kan= new Tkanban($this->clink);
        $obj_kan->SetIdProyecto($this->id_proyecto);
        $obj_kan->set_id_proyecto_code($this->id_proyecto_code);
        $obj_kan->SetIdResponsable(null);
        $kanban_column= $obj_kan->listar(false, true);

        $obj_kan->SetIdKanbanColumn($kanban_column['id']);
        $obj_kan->set_id_KanbanColumn_code($kanban_column['id_code']);
        $obj_kan->SetIdTarea($this->id);
        $obj_kan->set_id_tarea_code($this->id_code);
        $obj_kan->add_tarea();

        $this->id_kanaban_column= $kanban_column['id'];
        $this->id_kanaban_column_code= $kanban_column['id_code'];
    }

    private function set_kanban_responsable() {
        if(empty($this->id_proyecto))
            return null;
        if (!empty($this->_id_responsable) && $this->id_responsable == $this->_id_responsable)
            return null;
            
        $obj_kan= new Tkanban($this->clink);
        $obj_kan->SetIdProyecto(null);
        $obj_kan->SetIdResponsable($this->id_responsable);
        $kanban_column= $obj_kan->listar(false, true);

        if(is_null($kanban_column)) {
            $obj_kan->set_new_responsable();
            $kanban_column= $obj_kan->listar(false, true); 
        }

        $obj_kan->SetIdKanbanColumn($kanban_column['id']);
        $obj_kan->SetIdTarea($this->id);
        $obj_kan->set_id_tarea_code($this->id_code);
        $obj_kan->add_tarea();

        if (empty($this->id_proyecto)) {
            $this->id_kanaban_column= $kanban_column['id'];
            $this->id_kanaban_column_code= $kanban_column['id_code'];            
        }
    }

    private function set_tarea() {
        $this->obj->SetFecha($this->cronos);
        $this->obj->SetPlanning(0);
        $this->obj->SetCumplimiento(_NO_INICIADO);
        $this->obj->SetValue(0);
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);

        $this->obj->SetIdKanbanColumn($this->id_kanaban_column);
        $this->obj->set_id_kanbanColumn_code($this->id_kanaban_column_code);

        $this->obj->add_cump_to_task();        
    }

    private function _set_var() {
        $this->obj->SetNombre(trim($_POST['nombre']), false);

        if (!empty($this->id_proyecto)) {
            $this->obj->SetIdProyecto($this->id_proyecto);
            $this->id_proyecto_code= get_code_from_table('tproyectos', $this->id_proyecto);
            $this->obj->set_id_proyecto_code($this->id_proyecto_code);
        }
        if (!empty($this->id_programa)) {
            $this->obj->SetIdPrograma($this->id_programa);
            $this->id_programa_code= get_code_from_table('tprogramas', $this->id_programa);
            $this->obj->set_id_programa_code($this->id_programa_code);
        }

        $this->id_responsable= $_POST['responsable'];
        $this->obj->SetIdResponsable($this->id_responsable);

        if (($this->action == 'update' && !empty($this->_id_responsable)) 
                        && $this->_id_responsable != $this->id_responsable) {
            $this->obj->set_id_responsable_2($this->_id_responsable);
            $this->obj->set_responsable_2_reg_date($this->cronos);
        }

        if (!is_null($_POST['descripcion']))
            $this->obj->SetDescripcion(trim($_POST['descripcion']));

        $this->obj->SetIfGrupo($this->ifGrupo);
        $this->obj->SetIdTarea_grupo($_POST['id_grupo']);

        if (!$this->ifGrupo) {
            if (!empty($_POST['toshow2']))
                $this->toshow = _EVENTO_ANUAL;
            elseif (!empty($_POST['toshow1']))
                $this->toshow = _EVENTO_MENSUAL;
            else
                $this->toshow = _EVENTO_INDIVIDUAL;
        } else {
            $this->toshow = null;
            $this->empresarial= null;
        }

        $this->empresarial= $this->toshow ? _FUNCIONAMIENTO_INTERNO : _EVENTO_INDIVIDUAL;
        $this->obj->set_toshow_plan($this->toshow);
        $this->obj->SetIfEmpresarial($this->empresarial);

        $this->id_tarea_grupo= !empty($_POST['tarea_grupo']) ? $_POST['tarea_grupo'] : null;
        $this->id_tarea_grupo_code= !empty($this->id_tarea_grupo) ? get_code_from_table("ttareas", $this->id_tarea_grupo) : null;
        $this->obj->SetIdTarea_grupo($this->id_tarea_grupo);
        $this->obj->set_id_tarea_grupo_code($this->id_tarea_grupo_code);

        $this->obj->SetChkDate($_POST['chk_date']);

        $obj_user= new Tusuario($this->clink);
        $email_from= $obj_user->GetEmail($this->id_responsable);
        //$this->from= $email_from['email'];
        $this->from= $_SESSION['email_app'];
        $this->responsable= $email_from['nombre'];
        $this->cargo= $email_from['cargo'];
        unset($obj_user);

        $this->obj->SetLugar("Esta actividad se originó a partir de una $this->texttitle");
    }

    private function _set_by_signal() {
        $obj_tmp= null;

        if ($this->signal == 'friesgo') {
            if (!isset($obj_tmp))
                $obj_tmp= new Triesgo($this->clink);
            $obj_tmp->SetIdRiesgo($this->id_riesgo);
        }
        if ($this->signal == 'fnota') {
            if (!isset($obj_tmp))
                $obj_tmp= new Tnota($this->clink);
            $obj_tmp->SetIdNota($this->id_nota);
        }
        if ($this->signal == 'fproyecto') {
            if (!isset($obj_tmp))
                $obj_tmp= new Tproyecto($this->clink);
            $obj_tmp->SetIdProyecto($this->id_proyecto);
        }

        if (isset($obj_tmp)) {
            $obj_tmp->Set();
            $obj_tmp->SetIdTarea($this->id);
            $obj_tmp->set_id_tarea_code($this->id_code);
            $obj_tmp->add_tarea();

            unset($obj_tmp);
        }
    }

    public function apply() {
        if ($this->menu == 'tarea' || $this->menu == 'tablero') {
            $this->obj= new Tregtarea($this->clink);
            $this->obj_tr= new Tregtarea($this->clink);
            $this->obj_evt= new Tevento($this->clink);
        }

        $this->obj_tr->set_cronos($this->cronos);
        $this->obj_evt->set_cronos($this->cronos);
        $this->obj->set_cronos($this->cronos);

        $this->obj->SetYear($this->year);
        $this->obj_tr->SetYear($this->year);
        $this->obj_evt->SetYear($this->year);

        $this->id_tipo_evento= null;
        $this->id_tipo_evento_code= null;
        
        if (!empty($this->id)) {
            $this->id_tarea= $this->id;
            $this->obj->SetIdTarea($this->id);
            $this->obj->Set();

            $this->kronos= $this->obj->get_kronos();
            $this->obj->set_id_responsable_2(null);

            $this->id_code= $this->obj->get_id_code();
            $this->id_tarea_code= $this->id_code;
            $this->_id_proyecto= $this->obj->GetIdProyecto();

            $this->_id_responsable= $this->obj->GetIdResponsable();
            $this->_toshow= $this->obj->get_toshow_plan();

            $this->id_tarea_grupo= $this->obj->GetIdTarea_grupo();
            $this->id_tarea_grupo_code= $this->obj->get_id_tarea_grupo_code();
        }

        if ($this->action != 'edit') {
            $this->_set_var();
        }
        
        if ($this->action == 'add') {
            $this->id_proceso= $_SESSION['id_entity'];
            $this->id_proceso_code= $_SESSION['id_entity_code'];
        }

        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->className= 'Ttarea';
        $this->obj->className= 'Ttarea';

        if ($this->action =='add' || $this->action == 'update')
            $this->set_date_time_scheduler();

        $this->sendmail= $_POST['sendmail'];
        $this->obj->SetSendMail($this->sendmail);

        $this->obj->SetIdTarea($this->id);
        if ($this->action != 'update')
            $this->obj->SetIdUsuario($_SESSION['id_usuario']);
        $this->obj->action= $this->action;

        $this->obj->SetIfAssure($_POST['ifassure']);

        if ($this->action == 'add') {
            $this->error= $this->obj->add();

            if (empty($this->error)) {
                $this->changed= false;
                $this->id= $this->obj->GetIdTarea();
                $this->id_tarea= $this->id;
                $this->id_code= $this->obj->get_id_code();
                $this->id_tarea_code= $this->id_code;
        }   }

        if ($this->action == 'update') {
            $this->error= $this->obj->update();

            if (is_null($this->error)) {
                $this->obj->Set();
                $this->obj->get_child_events_by_table('ttareas', $this->id);
                $cant= count($this->obj->array_eventos);
                if (empty($cant))
                    $this->set_date_time_scheduler(false);
        }   }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->set_peso(null, null, false);
        }

        if (!$this->ifGrupo && ($this->action == 'add' || ($this->action == 'update' 
            && ($this->id_proyecto != $this->_id_proyecto || $this->id_responsable != $this->_id_responsable)))) {
            $this->set_kanban_proyecto();
            $this->set_kanban_responsable();
        }

        if ($this->action == 'add' && empty($this->error)) {
            $this->set_tarea();
        }

        // Crear o modificar los eventos a partir de la tarea ya creada
        if ((!$this->ifGrupo && empty($this->error))
            && (!empty($this->id_tarea) && ($this->action == 'add' || $this->action == 'update'))) {
            $this->setProcesos();
            $this->set_proceso_from_array();

            copy_tarea_to_evento($this->obj, $this->obj_evt);
            copy_tarea_to_evento($this->obj, $this->obj_tr);

            $this->obj= $this->obj_evt;
            $this->className= 'Ttarea';
            $this->obj->className= 'Ttarea';

            $this->id_evento= null;
            $this->id_evento_code= null;
            
            $this->fix_periodic_events();

            $this->obj= $this->obj_tr;
        }

        if ($this->action == 'delete' && empty($this->error)) {
            $observacion= $this->obj->GetNombre();
            $observacion.= "<br />Inicio: ". $this->obj->GetFechaInicioPlan(). " Fin:". $this->obj->GetFechaFinPlan();

            $this->error= $this->obj->eliminar();
            if (is_null($this->error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('ttareas', 'id_code', $this->id_code);
            }
        }

        if (($this->action == 'update' || $this->action == 'add') && empty($this->error)) {
            $this->_set_by_signal();
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $this->error= $this->obj->Set();
        }

        $url_page= "../php/tarea.interface.php?id=$this->id&signal=$this->signal&action=$this->action";
        $url_page.= "&id_proceso=$this->id_proceso&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_riesgo=$this->id_riesgo&id_nota=$this->id_nota&id_proyecto=$this->id_proyecto";
        $url_page.= "&id_tarea_grupo=$this->id_tarea_grupo&exect=$this->action&menu=$this->menu&toshow=$this->toshow";
        $url_page.= "&id_lista=$this->id_lista&id_requisito=$this->id_requisito&cumplimiento=$this->cumplimiento";

        add_page($url_page, $this->action, 'i');

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (empty($this->error)) {
                if ($this->menu == 'tarea' || $this->menu =='tablero') {
                    $_SESSION['obj']= serialize($this->obj);

                    if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                    ?>
                        self.location.href='<?php next_page();?>';
                    <?php
                    }

                    if ($this->action == 'edit' || $this->action == 'list') {
                        if ($this->action == 'edit')
                            $this->action= 'update';

                        $url= "?action=$this->action&signal=$this->signal&id_proyecto=$this->id_proyecto";
                        $url.= "&id_nota=$this->id_nota&id_riesgo=$this->id_riesgo&year=$this->year#$this->id";
              ?>
                        self.location.href='../form/ftarea.php<?=$url?>';
            <?php
                    }
                } else {
            ?>
                    CloseWindow('div-ajax-panel');
                    self.location.reload();
            <?php
                }
            } else {
                $this->obj->error= $this->error;
                $_SESSION['obj']= serialize($this->obj);
            ?>
                self.location.href='<?php prev_page($this->error);?>';
            <?php
            }
    }   }
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
    setInterval('setChronometer()', 1);

    $('#body-log table').mouseover(function() {
        _moveScroll = false;
    });
    $('#body-log table').mouseout(function() {
        _moveScroll = true;
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
<?php } ?>