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

require_once "class/inductor.class.php";
require_once "class/indicador.class.php";
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
class TinductorInterface extends TbaseInterface {

    public function __construct($clink) {
        $this->clink= $clink;
        TbaseInterface::__construct($clink);

        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->id_inductor= $this->id;

        $this->if_send_up= $_POST['if_send_up'];
        $this->if_send_down= $_POST['if_send_down'];
        $this->peso= !is_null($_POST['peso']) ? $_POST['peso'] : null;
    }

    public function setPeso_indicadores() {
        $error= NULL;
        if (empty($_POST['t_cant_multiselect-inds']))
            return null;

        $use_undefined= $_POST['multiselect-inds_use_undefined'];
        $obj= new Tindicador($this->clink);
        $result= $obj->listar();

        $obj->SetYear($this->year);
        $obj->SetIdInductor($this->id);
        $obj->set_id_inductor_code($this->id_code);

        while ($row= $this->clink->fetch_array($result)) {
            $value = !$use_undefined ? $_POST['multiselect-inds_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_ind' . $row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-inds_init_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_init_ind' . $row['_id']]);
            $peso = $_POST['multiselect-inds-select_' . $row['_id']];

            $inicio=  max($row['_inicio'], $this->inicio);
            $fin= min($row['_fin'], $this->fin);
            $obj->SetInicio($inicio);
            $obj->SetFin($fin);
            $obj->SetIdIndicador($row['_id']);
            $obj->set_id_indicador_code($row['_id_code']);

            if (($peso != $_value || ($this->inicio != $this->_inicio || $this->fin != $this->_fin)) 
                    && ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value)))) {
                $obj->SetPeso($peso);
                $obj->expand_period_ref();
            } else {
                if (((!$use_undefined && empty($value)) || ($use_undefined && is_null($value))) && ((!$use_undefined && !empty($_value)) || ($use_undefined && !is_null($_value))))
                    $obj->delete_period_ref();
            }

            if (!is_null($error))
                break;
        }

        unset($obj);
        return $error;
    }

    private function setPeso_objetivos() {
        $error= NULL;
        if ($_POST['cant_obji'] == 0)
            return null;

        $obj_inductor= new Tinductor($this->clink);

        $obj_inductor->SetYear($this->year);
        $obj_inductor->SetInicio($this->inicio);
        $obj_inductor->SetFin($this->fin);

        $obj_inductor->SetIdInductor($this->id);
        $obj_inductor->set_id_inductor_code($this->id_code);

        $obj= new Tobjetivo($this->clink);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            $id= $row['_id'];

            $value= $_POST['select_obji'.$id];
            $_value= $_POST['init_obji'.$id];

            if ($value > 0 && ($value != $_value  || ($this->inicio != $this->_inicio || $this->fin != $this->_fin))) {
                $obj_inductor->SetPeso($value);
                $obj_inductor->expand_period_ref($id);

            } else {
                if ($_value > 0 && empty($value))
                    $obj_inductor->delete_period_ref($id);
            }
        }

        $this->clink->free_result($result);
        unset($obj_inductor);
    }

    public function apply(&$id_remote= null) {
        $this->obj= new Tinductor($this->clink);
        $_id_perspectiva= null;

        if (!empty($this->id)) {
            $this->obj->SetIdInductor($this->id);
            $error= $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();
            $_id_perspectiva= $this->obj->GetIdPerspectiva();
            
            $this->_inicio= $this->obj->GetInicio();
            $this->_fin= $this->obj->GetFin();
        }

        $this->obj->SetYear($this->year);

        $this->obj->SetIdProceso($this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->obj->action= $this->action;

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNombre($this->nombre, false);
            $this->obj->SetDescripcion($this->descripcion);

            if (($_id_perspectiva != $this->id_perspectiva && $this->action == 'update') || $this->action == 'add') {
                $this->obj->SetIdPerspectiva($this->id_perspectiva);
    		$this->obj->set_id_perspectiva_code($this->id_perspectiva_code);
            }    
            $this->obj->SetInicio($this->inicio);
            $this->obj->SetFin($this->fin);

            $this->obj->SetIfSend_up($this->if_send_up);
            $this->obj->SetIfSend_down($this->if_send_down);
            $this->obj->SetNumero($this->numero);

            $this->obj->SetPeso($this->peso);
        }

        if ($this->action == 'add') {
            $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdInductor();
                $this->id_inductor= $this->id;
                $this->id_code= $this->obj->get_id_code();
                $this->id_inductor_code= $this->id_code;
            }
        }

        if ($this->action == 'update') 
            $error= $this->obj->update();

        if (is_null($error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->setPeso_indicadores();
            $this->setPeso_objetivos();
        }

        if ($this->action == 'delete') {
            $observacion= "No. ".$this->obj->GetNumero(). " ".$this->obj->GetInicio(). " - ". $this->obj->GetFin(). " ".$this->obj->GetNombre();

            $error= $this->obj->eliminar($this->_radio_date);

            if (is_null($error) && $this->_radio_date == 2) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tinductores', 'id_code', $this->id_code);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();
        }

        if (!is_null($this->model)) {
            $id_remote= $this->id;
            return null;
        }

        $url_page= "../php/inductor.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month";
        $url_page.= "&day=$this->day&id_proceso=$this->id_proceso&id_perspectiva=$this->id_perspectiva";
        $url_page.= "&exect=$this->action&menu=$this->menu";

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
                ?>

                self.location='../form/finductor.php?action=<?= $this->action?>#<?= $this->id ?>';

            <?php } } else {
                $this->obj->error= $error;
                $_SESSION['obj']= serialize($this->obj);
                ?>
                self.location.href='<?php prev_page($error);?>';
            <?php
            }
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
        <?php if (is_null($using_remote_functions) && !$ajax_win) { ?>
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
            $interface= new TinductorInterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>