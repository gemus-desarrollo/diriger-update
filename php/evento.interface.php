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
require_once "class/proceso_item.class.php";

require_once "class/grupo.class.php";
require_once "class/peso.class.php";
require_once "class/mail.class.php";
require_once "class/evento.class.php";
require_once "class/tipo_evento.class.php";
require_once "class/tipo_reunion.class.php";

require_once "class/badger.class.php";
require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !is_null($_GET['ajax_win']) ? $_GET['ajax_win'] : true;
$ajax_win= false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TplanningInterface {

    public function __construct($clink= null) {
        $this->clink = $clink;
        TplanningInterface::__construct($clink);

        $this->obj_meeting= new Ttipo_reunion($this->clink);
    }

    public function apply() {
        $this->multi_query= true;
        
        $this->obj = new Tevento($this->clink);
        $this->obj_matter= new Ttematica($this->clink);

        $fecha_origen= null;
        $error_duplicate= null;
        
        if (!empty($this->id) && ($this->action != 'edit' && $this->action != 'list')) {
            $this->obj->Set($this->id);

            $this->kronos= $this->obj->get_kronos();
            $this->obj->set_id_responsable_2(null);

            $this->id_code = $this->obj->get_id_code();
            $this->id_evento = $this->id;
            $this->id_evento_code = $this->id_code;

            $this->_id_evento = $this->id;
            $this->_id_evento_code = $this->id_code;

            $this->_id_responsable= $this->obj->GetIdResponsable();
            $this->_periodicidad = $this->obj->GetPeriodicidad();
            $fecha_origen= $this->obj->GetFechaInicioPlan();

            $this->id_tipo_reunion= $this->obj->GetIdTipo_reunion();
            $this->id_tipo_reunion_code= $this->obj->get_id_tipo_reunion_code();
            
            $this->_toshow= $this->obj->get_toshow_plan();
            $this->_id_tipo_evento= $this->obj->GetIdTipo_evento();
            $this->_id_tipo_evento_code= $this->obj->get_id_tipo_evento_code();
            $this->_empresarial= $this->obj->GetIfEmpresarial();
            $this->_indice= $this->obj->indice;
            $this->_indice_plus= $this->obj->indice_plus;
        }

        $this->obj->set_cronos($this->cronos);

        if (!empty($_POST['toshow2']))
            $this->toshow = _EVENTO_ANUAL;
        elseif (!empty($_POST['toshow1']))
            $this->toshow = _EVENTO_MENSUAL;
        else
            $this->toshow = _EVENTO_INDIVIDUAL;

        $this->obj->toshow= $this->toshow;
        $this->obj->set_toshow_plan($this->toshow);
        $this->user_check = $_POST['user_check'];     // Las actividades o eventos programados aparecerán ocultas en los Planes de Trabajo Individuales
        $this->obj->set_user_check_plan($this->user_check);

        $this->empresarial = !empty($_POST['tipo_actividad1']) ? $_POST['tipo_actividad1'] : $this->toshow;
        $this->obj->SetIfEmpresarial($this->empresarial);

        $this->id_tipo_evento = !empty($_POST['tipo_actividad3']) ? $_POST['tipo_actividad3'] : $_POST['tipo_actividad2'];
        $this->id_tipo_evento= !empty($this->id_tipo_evento) ? $this->id_tipo_evento : null;
        $this->obj->SetIdTipo_evento($this->id_tipo_evento);

        $this->id_tipo_evento_code= !empty($this->id_tipo_evento) ? get_code_from_table('ttipo_eventos', $this->id_tipo_evento) : null;
        $this->obj->set_id_tipo_evento_code($this->id_tipo_evento_code);

        $this->obj->SetNombre(trim($_POST['nombre']), false);

        if (empty($_POST['responsable']))
            $this->id_responsable = $_POST['id_usuario'];
            
        if ($_POST['responsable'] == 2) {
            $this->obj->SetFuncionario($_POST['funcionario']);
            if ($this->acc == 3)
                $this->id_responsable = $_POST['usuario'];
        } else 
            $this->obj->SetFuncionario(null);
            
        if ($_POST['responsable'] == 1 || ($_POST['responsable'] == 2 && $this->acc != 3))
            $this->id_responsable = $_POST['subordinado'];
        if ($_POST['responsable'] == 3)
            $this->id_responsable = $_POST['usuario'];
        if (empty($this->id_responsable))
            $this->id_responsable = $_POST['id_usuario'];

        $this->obj->SetIdResponsable($this->id_responsable);

        if (($this->action == 'update' && !empty($this->_id_responsable)) && $this->_id_responsable != $this->id_responsable) {
            $this->obj->set_id_responsable_2($this->_id_responsable);
            $this->obj->set_responsable_2_reg_date($this->cronos);
        }

        $this->obj->SetNumero($_POST['numero']);
        $this->obj->SetNumero_plus(trim($_POST['numero_plus']));

        $this->obj->SetLugar(trim($_POST['lugar']));
        if (!is_null($_POST['descripcion']))
            $this->obj->SetDescripcion(trim($_POST['descripcion']));

        if ($this->action == 'add' || $this->action == 'update')
            $this->set_date_time_scheduler();

        if ($this->action == 'add') {
            $this->obj->SetIdTarea(null);
            $this->obj->set_id_tarea_code(null);
        }
            
        $this->obj->SetIdEvento($this->id);
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);
        $this->id_usuario = $_SESSION['id_usuario'];
        $this->obj->action = $this->action;

        $this->className = 'Tevento';
        $this->obj->className = 'Tevento';

        /**
         * configuracion de usuarios y procesos segun las propiedades del usuario
         */
        global $config;
        global $badger;

        $badger = new Tbadger();
        $badger->SetYear($this->year);
        $config->freeassign = ($config->freeassign || ($_SESSION['planwork'] || $_SESSION['nivel'] >= _SUPERUSUARIO)) ? true : false;
        $badger->set_user_date_ref = ($this->fecha_inicio);
        $badger->set_planwork();

        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->id_tipo_reunion = empty($_POST['_tipo_reunion']) ? null : $_POST['tipo_reunion'];
        $this->obj->SetIdTipo_reunion($this->id_tipo_reunion);
        $this->id_tipo_reunion_code= $this->id_tipo_reunion ? get_code_from_table('ttipo_reuniones', $this->id_tipo_reunion) : null;
        $this->obj->set_id_tipo_reunion_code($this->id_tipo_reunion_code);

        $this->accords = null;
        $this->obj->SetIfaccords(null);

        $this->id_secretary= !empty($_POST['secretary']) ? $_POST['secretary'] : null;
        $this->obj->SetIdSecretary($this->id_secretary);
        $this->obj->SetIfAssure($_POST['ifassure']);
        $this->obj->SetIfSend($_POST['if_send']);

        if ($this->id_tipo_reunion && ($this->action == 'add' || $this->action == 'update')) {
            $this->obj_assist= new Tasistencia($this->clink);
            $this->obj_assist->set_cronos($this->cronos);
            $this->obj_assist->SetIdProceso($this->id_proceso);
            $this->obj_assist->set_id_proceso_code($this->id_proceso_code);

            if ($this->action == 'update') {
                $this->obj_assist->SetIdEvento($this->id);
                $this->obj_assist->get_asistencias();
                $this->array_asistencias= $this->obj_assist->array_asistencias;
            }
        }

        if ($this->action == 'add') {
            $error_duplicate= null;
            $this->error= $this->obj->add(null, null, $error_duplicate);

            if (is_null($this->error)) {
                $this->changed = false;

                $this->id = $this->obj->GetIdEvento();
                $this->id_evento = $this->id;
                $this->id_code = $this->obj->get_id_code();
                $this->id_evento_code = $this->id_code;

                $this->_id_evento= $this->id;
                $this->_id_evento_code= $this->id_code;
                $this->_id= $this->id;
                $this->_id_code= $this->id_code;

                $this->indice= $this->obj->indice;
                $this->indice_plus= $this->obj->indice_plus;
                
                $this->setting('NUEVO');
                
            } else {
                $this->id= null;
                if ($error_duplicate) {
                    $this->id= $this->obj->find_evento_duplicated();
                    if ($this->id) {
                        $this->action= 'edit';
                        $this->error.= " Ahora puede modificar esta actividad ya registrada en el sistema.";
                    }
            }   }
        }

        $_radio_date = $_POST['_radio_date'];
        $nums_width_debate= 0;

        if ($this->id_tipo_reunion) {
            if (($this->action == 'update' && ($this->changed && $this->periodicidad == 0)) || $this->action == 'delete') {
                $this->array_tematicas = $this->obj_matter->get_tematicas_by_evento($this->id);
                $nums_width_debate= $this->obj_matter->get_nums_width_debate();

                if ($nums_width_debate > 0) {
                    $this->error= "Esta reunión ya fue realizada y tiene debates o acuerdos registrados. ";
                    $this->error= "No puede ser modificada o eliminada";
        }   }   }

        if ($this->action == 'update' && is_null($this->error)) {
            if ($this->changed) {
                $this->obj_doc= new Tdocumento($this->clink);
                $this->obj_doc->SetIdEvento($this->id);
                $this->array_documentos = $this->obj_doc->get_documentos(false);
            }
            if (is_null($this->error)) {
                $fecha_origen = ($_radio_date == 1) ? $this->fecha_inicio : null;
                $this->obj->get_child_events_by_table('teventos', $this->id_evento, null, $fecha_origen, null);
            }
            if (!$this->changed && is_null($this->error))
                $this->changed = is_null($this->obj->search_date_out_array($this->fecha_inicio, $this->fecha_fin)) ? false : true;

            $array = null;
            if (($this->periodicidad == 0 && (!is_null($this->_periodicidad) && $this->_periodicidad > 0)) && is_null($this->error)) {
                $array = $this->obj->update_exclusive($this->id, $this->fecha_inicio);
                $this->updated = !is_null($array) ? true : false;

                if ($this->updated) {
                    $result= $this->move_tematicas($array[0], $this->fecha_inicio_plan, $array[1]);

                    $this->if_child_event(null, $array[0]);
                    if ($result)
                        $this->delete_periodic();
                }
            }

            if ((!$this->updated && (empty($_radio_date) || $_radio_date == 2)) && is_null($this->error)) {
                $this->error = $this->obj->update();
            }
            
            if (is_null($this->error)) {
                $this->indice= $this->obj->indice;
                $this->indice_plus= $this->obj->indice_plus;                
                
                if ($this->updated) {
                    $this->id_evento = $array[0];
                    $this->id_evento_code = $array[1];

                    $this->_id_evento = $this->id_evento;
                    $this->_id_evento_code = $this->id_evento_code;

                    $this->obj->SetIdEvento($this->id_evento);
                    $this->obj->set_id_evento_code($this->id_evento_code);
                }

                $this->_id= $this->id_evento;
                $this->_id_code= $this->_id_evento_code;

                $this->setting('MODIFICADO', true);
            }
        }

        if (!empty($this->id_evento) && is_null($this->error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->id_evento_ref= $this->id_evento;
            $this->id_evento_ref_code= $this->id_evento_code;

            if ($this->periodicidad > 0 || $this->_periodicidad > 0)
                $this->fix_periodic_events();
        }

        if (($this->action == 'edit' || $this->action == 'list') && !empty($this->id)) {
            $this->obj->SetIdEvento($this->id);
            $this->error= $error_duplicate ? $this->error : null;
            $this->error.= $this->obj->Set();
        }

        if ($this->action == 'delete' && is_null($this->error)) {
            $force_delete= !is_null($_GET['force_delete']) ? $_GET['force_delete'] : false;
            $this->obj_meeting->SetIdTipo_reunion($this->id_tipo_reunion);
            $this->obj_meeting->Set();
            $meeting= $this->obj_meeting->GetNombre();

            $observacion= !empty($this->id_tipo_reunion) ? $meeting : "";
            $observacion.= $this->obj->GetNombre();
            $observacion.= "<br />Inicio: ". $this->obj->GetFechaInicioPlan(). " Fin:". $this->obj->GetFechaFinPlan();

            $result= $this->obj->eliminar($this->id, $force_delete);
            if ($result) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('teventos', 'id_code', $this->id_code);
            }
        }

        $error = $this->error;

        $url_page = "../php/evento.interface.php?id=$this->id&signal=$this->signal&action=$this->action";
        $url_page .= "&id_proceso=$this->id_proceso&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&exect=$this->action&menu=$this->menu&id_tipo_reunion=$this->id_tipo_reunion";
        $url_page.= "&init_row_temporary=$this->init_row_temporary";

        add_page($url_page, $this->action, 'i');

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj'] = serialize($this->obj);

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                ?>
                    self.location.href='<?php next_page(); ?>';
                <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit') $this->action = 'update';
                    $this->obj->action= $this->action;
                    ?>
                    var url= '../form/fevento.php?action=<?= $this->action.'&month='.$this->month.'&year='.$this->year.'&day='.$this->day ?>';
                    url+='<?= '&id_calendar='.$this->id_calendar.'&id_proceso='.$this->id_proceso.'&id_proceso_code='.$this->id_proceso_code ?>';
                    url+= '&init_row_temporary=<?=$this->init_row_temporary?>';

                    self.location= url;
                    <?php
                } else {
                ?>
                    // cerrar();
                <?php
                }
            } else {
                $this->redirect = 'fail';
                $this->obj->error = $error;
                $_SESSION['obj'] = serialize($this->obj);
                    
                if (!$error_duplicate) {    
                    ?>
                       self.location.href='<?php prev_page($error); ?>';
                    <?php
                } else {
                    ?>
                       var url= '../form/fevento.php?action=update&id=<?= $this->id?>';
                       url+= '<?= '&id_calendar='.$this->id_calendar.'&id_proceso='.$this->id_proceso.'&id_proceso_code='.$this->id_proceso_code ?>';
                       self.location= url;
                    <?php   
                }
            }
    }   }

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
        $interface = new Tinterface($clink);
        $interface->apply();
        ?>
    <?php if (!$ajax_win) { ?>
    });
    <?php } ?>
</script>
