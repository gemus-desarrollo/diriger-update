<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "class/base.class.php";
if (!$clink) 
    require_once _ROOT_DIRIGER_DIR."php/class/connect.class.php";

require_once "class/objetivo.class.php";
require_once "class/objetivo_ci.class.php";
require_once "class/inductor.class.php";
require_once "class/riesgo.class.php";
require_once "class/proceso.class.php";
require_once "class/peso.class.php";

require_once "class/code.class.php";
require_once "base.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class TobjetivoInterface extends TbaseInterface {
    private $obj;


    public function __construct($clink) {
        $this->clink= $clink;
        TbaseInterface::__construct($clink);

        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];

        $this->if_ci= !is_null($_POST['if_control_interno']) ? $_POST['if_control_interno'] : $_GET['if_control_interno'];
        $this->if_objsup= !is_null($_POST['if_objsup']) ? $_POST['if_objsup'] : $_GET['if_objsup'];

        $this->if_send_up= $_POST['if_send_up'];
        $this->if_send_down= $_POST['if_send_down'];
    }

    public function setPeso_inductores() {
        $tobj= new Tinductor($this->clink);
        $tobj->SetYear($this->year);
        $result= $tobj->listar();

        $obj_inductor= new Tinductor($this->clink);
        $obj_inductor->SetYear($this->year);
        $obj_inductor->SetIdObjetivo($this->id);
        $obj_inductor->set_id_objetivo_code($this->id_code);

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];
            $obj_inductor->SetInicio($row['_inicio']);
            $obj_inductor->SetFin($row['_fin']);
            $obj_inductor->SetIdInductor($id);
            $obj_inductor->set_id_inductor_code($row['_id_code']);

            $value= $_POST['select_objt'.$id];
            $_value= $_POST['init_objt'.$id];

            if ($value > 0 && $value != $_value) {
                $obj_inductor->SetPeso($value);
                $obj_inductor->expand_period_ref($this->id);

            } else {
                if ($_value > 0 && empty($value))
                    $obj_inductor->delete_period_ref($this->id);
            }
        }

        $this->clink->free_result($result);
        unset($obj_inductor);
        unset($tobj);
    }

    public function setPeso_politicas() {
        $obj= new Tobjetivo($this->clink);

        $obj->SetYear($this->year);
        $obj->SetIdProceso($_SESSION['id_entity']);
        $obj->set_id_proceso_code($_SESSION['id_entity_code']);

        $obj->SetIdObjetivo($this->id);
        $obj->set_id_objetivo_code($this->id_code);

        $obj->SetInicio($this->inicio);
        $obj->SetFin($this->fin);

        $obj_pol= new Tpolitica($this->clink);
        $result= $obj_pol->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];
            $obj->SetId($id);
            $obj->SetIfObjetivoSup(false);

            $value= (int)$_POST['select_pol'.$id];
            $_value= (int)$_POST['init_pol'.$id];

            if ($value > 0 && $value != $_value) {
                $obj->SetPeso($value);
                $obj->expand_period_ref($row['_id']);

            } else {
                if ($_value > 0 && empty($value))
                    $obj->delete_period_ref($row['_id']);
            }
        }

        $this->clink->free_result($result);
        unset($obj);
    }

    private function setPeso_objetivos_sup() {
        $obj_objetivo= new Tobjetivo($this->clink);

        $obj_objetivo->SetYear($this->year);
        $obj_objetivo->SetIdProceso($_SESSION['id_entity']);
        $obj_objetivo->set_id_proceso_code($_SESSION['id_entity_code']);

        $obj_objetivo->SetIdObjetivo($this->id);
        $obj_objetivo->set_id_objetivo_code($this->id_code);

        $obj_objetivo->SetInicio($this->inicio);
        $obj_objetivo->SetFin($this->fin);

        $obj_objetivo->SetIfObjetivoSup(true);

        $obj= new Tobjetivo($this->clink);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];
            $obj_objetivo->SetId($id);

            $value= $_POST['select_objs'.$id];
            $_value= $_POST['init_objs'.$id];

            if ($value > 0 && (int)$value != (int)$_value) {
                $obj_objetivo->SetPeso($value);
                $obj_objetivo->expand_period_ref(null, $row['_id']);

            } else {
                if ((int)$_value > 0 && empty($value))
                    $obj_objetivo->delete_period_ref($row['_id']);
            }
        }

        $this->clink->free_result($result);
        unset($obj_objetivo);
    }

    private function setPeso_objetivos() {
        $obj_objetivo= new Tobjetivo($this->clink);

        $obj_objetivo->SetYear($this->year);
        $id_proceso= $this->if_objsup ? $_SESSION['id_entity'] : $this->id_proceso;
        $obj_objetivo->SetIdproceso($id_proceso);
        $id_proceso_code= $this->if_objsup ? $_SESSION['id_entity_code'] : $this->id_proceso_code;
        $obj_objetivo->set_id_proceso_code($id_proceso_code);

        $obj_objetivo->SetIfObjetivoSup(true);

        $obj= new Tobjetivo($this->clink);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];

            $obj_objetivo->SetIdObjetivo($id);
            $obj_objetivo->set_id_objetivo_code($row['id_code']);
            $obj_objetivo->SetInicio($row['_inicio']);
            $obj_objetivo->SetFin($row['_fin']);

            $value= $_POST['select_obji'.$id];
            $_value= $_POST['init_obji'.$id];

            if ($value > 0 && $value != $_value) {
                $obj_objetivo->SetPeso($value);
                $obj_objetivo->expand_period_ref(null, $this->id);

            } else {
                if ($_value > 0 && empty($value))
                    $obj_objetivo->delete_period_ref($this->id);
            }
        }

        $this->clink->free_result($result);
        unset($obj_objetivo);
    }

    private function setPeso_tareas() {
        if (empty($_POST['t_cant_task'])) 
            return;

        $tobj= new Ttarea($this->clink);
        $result= $tobj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];
            $id_code= $row['_id_code'];

            $value= $_POST['select_task_'.$id];
            $_value= $_POST['init_task_'.$id];

            reset($this->array_procesos);
            foreach ($this->array_procesos as $array) {
                $this->obj->SetIdProceso($array['id']);
                $this->obj->set_id_proceso_code($array['id_code']);

                if ($value > 0 && $value != $_value) {
                    $this->obj->add_tarea($id, $id_code, $value);
                } else {
                    if ($_value > 0 && $value == 0)
                        $this->obj->delete_tarea($id);
        }   }   }

        unset($tobj);
    }

    private function set_procesos() {
        $tobj= new Tproceso($this->clink);
        $tobj->SetYear($this->year);
        $result= $tobj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $value= $_POST['multiselect-prs_'.$row['_id']];
            $_value= $_POST['multiselect-prs_init_'.$row['_id']];

            $this->obj->SetIdProceso($row['_id']);
            $this->obj->set_id_proceso_code($row['_id_code']);

            if (!empty($value)) {
                $this->array_procesos[$row['id']]= array('id'=>$row['id'], 'id_code'=>$row['id_code']);

                for ($year= $this->inicio; $year <= $this->fin; $year++) {
                    $this->obj->SetYear($year);
                    $this->obj->set_proceso('add');
                }
            } else {
                if (!empty($_value)) {
                    for ($year= $this->year; $year <= $this->fin; $year++) {
                        $this->obj->SetYear($year);
                        if ($year >= $this->year)
                            $this->obj->set_proceso('delete');
                    }

                    $this->obj_code->reg_delete('tproceso_objetivos','id_proceso_code', $row['id_code'], 'id_objetivo_code', $this->id_code);
                }
            }
        }

        if (array_key_exists($this->id_proceso, $this->array_procesos) == false) {
            $this->array_procesos[$this->id_proceso]= array('id'=> $this->id_proceso, 'id_code'=> $this->id_proceso_code);

            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);

            for ($year= $this->inicio; $year <= $this->fin; $year++) {
                $this->obj->SetYear($year);
                $this->obj->set_proceso('add');
            }
        }

        unset($tobj);
    }

    public function apply(&$id_remote= null) {
        $this->obj= new Tobjetivo_ci($this->clink);

        $this->obj->SetIfControlInterno($this->if_ci);
        $this->obj->SetIfObjetivoSup($this->if_objsup);

        $this->obj->action= $this->action;
        $_id_proceso= null;

        if (!empty($this->id)) {
            $this->obj->SetIdObjetivo($this->id);
            $error= $this->obj->Set();
            $this->id_code= $this->obj->get_id_objetivo_code();
            $_id_proceso= $this->obj->GetIdProceso();
        }

        $this->obj->SetYear($this->year);

        $this->obj->SetNombre($this->nombre, false);

        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->obj->SetDescripcion($this->descripcion);

        $this->obj->SetInicio($this->inicio);
        $this->obj->SetFin($this->fin);

        $this->obj->SetIfSend_up($this->if_send_up);
        $this->obj->SetIfSend_down($this->if_send_down);
        $this->obj->SetNumero($this->numero);

        if ($this->action == 'add') {
            $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdObjetivo();
                $this->id_objetivo= $this->id;

                $this->id_code= $this->obj->get_id_code();
                $this->id_objetivo_code= $this->id_code;
            }
        }

        if ($this->action == 'update') {
            $error= $this->obj->update();

            if (!empty($_id_proceso) && $_id_proceso != $this->id_proceso)
                $this->obj->update_proceso_ref($_id_proceso, $this->id_proceso, $this->year);
        }

        if (is_null($error) && ($this->action == 'add' || $this->action == 'update')) {
            if (!$this->if_ci && !$this->if_objsup) {
                $this->setPeso_politicas();
                $this->setPeso_inductores();
                $this->setPeso_objetivos_sup();
            }

            if (!$this->if_ci)
                $this->setPeso_objetivos();

            if ($this->if_ci) {
                $this->set_procesos();
                $this->setPeso_tareas();
            }
        }

        if ($this->action == 'delete') {
            $observacion= "No. ".$this->obj->GetNumero(). " ".$this->obj->GetInicio(). " - ". $this->obj->GetFin(). " ".$this->obj->GetNombre();
            $error= $this->obj->eliminar($this->_radio_date);

            if (is_null($error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tobjetivos', 'id_code', $this->id_code);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list')
            $error= $this->obj->Set();

        if (!is_null($this->model)) {
            $id_remote= $this->id;
            return null;
        }

        $url_page= "../php/objetivo.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu&if_control_interno=$this->if_ci";

        add_page($url_page, $this->action, 'i');

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($this->obj);

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
            ?>
                    self.location.href='<?php next_page();?>';
            <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit') 
                        $this->action= 'update';

                    $form= 'fobjetivo';
                    if ($this->signal == 'objetivo_sup')
                        $form= 'fobjetivo_sup';
                    if ($this->if_objsup)
                        $form= 'fobjetivo_sup';
                    if ($this->if_ci)
                        $form= 'fobjetivo_ci';
                    ?>

                    self.location.href='../form/<?=$form?>.php?action=<?=$this->action?>&signal=<?=$this->signal?>';

                <?php } } else {
                     $this->obj->error= $error;
                     $_SESSION['obj']= serialize($this->obj);
                    ?>
                        self.location.href='<?php prev_page($error);?>';

                    <?php
                }
            }
    }   }
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
                $interface= new TobjetivoInterface($clink);
                $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>