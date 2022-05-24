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
require_once "class/connect.class.php";
require_once "class/escenario.class.php";
require_once "class/proceso_item.class.php";

require_once "class/indicador.class.php";
require_once "class/programa.class.php";
require_once "class/peso.class.php";

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends Tbase {
    public $menu;
    private $obj;

    public function Tinterface($clink) {
        $this->clink= $clink;

        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->action= !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->menu= !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];
        $this->signal= !empty($_GET['signal']) ? $_GET['signal'] : $_POST['signal'];
        $this->year= !empty($_GET['year']) ? $_GET['year'] : $_POST['year'];
        $this->month= !empty($_GET['month']) ? $_GET['month'] : $_POST['month'];
        $this->day= !empty($_GET['day']) ? $_GET['day'] : $_POST['day'];
        $this->id_escenario= !empty($_GET['id_escenario']) ? $_GET['id_escenario'] : $_POST['id_escenario'];

        $this->id_escenario_code= $_POST['id_escenario_code_'.$this->id_escenario];

        $this->obj_code= new Tcode($this->clink);
    }

    private function setIndicadores() {
        $error= NULL;
        if ($_POST['t_cant_multiselect-inds'] == 0)
            return null;

        $use_undefined= $_POST['multiselect-inds_use_undefined'];
        $obj= new Tindicador($this->clink);
        $result= $obj->listar();

        $array_ids= null;
        while ($row= $this->clink->fetch_array($result)) {
            if($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= $row['_id'];

            $value = !$use_undefined ? $_POST['multiselect-inds_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_ind' . $row['_id']]);
            $_value = !$use_undefined ? $_POST['multiselect-inds_init_ind' . $row['_id']] : setNULL_undefined($_POST['multiselect-inds_init_ind' . $row['_id']]);
            $peso = $_POST['multiselect-inds-select_' . $row['_id']];

            if (($peso != $_value  || ($this->inicio != $this->_inicio || $this->fin != $this->_fin))
                    && ((!$use_undefined && !empty($value)) || ($use_undefined && !is_null($value)))) {
                $this->obj->SetPeso($peso);
                $this->obj->expand_period_ref($row['_id']);
            } else {
                if ((!$use_undefined && (!empty($_value) && empty($value))) || ($use_undefined && (!is_null($_value) && is_null($value)))) {
                    $this->obj->delete_period_ref($row['_id']);
                }
            }

            if (!is_null($error))
                break;
        }

        unset($obj);
        return $error;
    }

    private function setProcesos() {
        $tobj= new Tproceso_item($this->clink);
        $result= $tobj->listar();

        $tobj->SetIdPrograma($this->id);
        $tobj->set_id_programa_code($this->id_code);

        while ($row= $this->clink->fetch_array($result)) {
            $value= $_POST['multiselect-prs_'.$row['_id']];

            $tobj->SetIdProceso($row['_id']);
            $tobj->set_id_proceso_code($row['_id_code']);

            if (!empty($value)) {
                for ($year= $this->inicio; $year <= $this->fin; $year++) {
                    if ($this->action == 'update' && $year < $this->year)
                        continue;
                    $tobj->SetYear($year);
                    $tobj->setProyecto('add');
                }

            } else {
                if (!empty($_POST['multiselect-prs_init_'.$row['_id']])) {
                    for ($year= $this->inicio; $year <= $this->fin; $year++) {
                        if ($this->action == 'update' && $year < $this->year)
                            continue;
                        $tobj->SetYear($year);
                        $tobj->setProyecto('delete');

                        $this->obj_code->reg_delete('tref_programa', 'id_proceso_code', $row['_id_code'], 'id_programa_code', $this->id_code, 'year', $year);
        }   }   }   }

        $tobj->SetIdProceso($this->id_proceso);
        $tobj->set_id_proceso_code($this->id_proceso_code);

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            if ($this->action == 'update' && $year < $this->year)
                continue;
            $tobj->SetYear($year);
            $tobj->setProyecto('add');
        }

        unset($tobj);
    }

    public function apply() {
        $this->obj= new Tprograma($this->clink);

        if (!empty($this->id)) {
            $this->obj->SetIdPrograma($this->id);
            $error= $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();
            
            $this->_inicio= $this->obj->GetInicio();
            $this->_fin= $this->obj->GetFin();
        }

        $this->obj->SetYear($this->year);

        $this->id_proceso= $_POST['proceso'];
        $this->obj->SetIdProceso($this->id_proceso);
        $this->id_proceso_code= $_POST['proceso_code_'.$this->id_proceso];
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->obj->action= $this->action;

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNombre(trim($_POST['nombre']), false);
            $this->obj->SetDescripcion(trim($_POST['descripcion']));

            $this->inicio= $_POST['inicio'];
            $this->obj->SetInicio($this->inicio);

            $this->fin= $_POST['fin'];
            $this->obj->SetFin($this->fin);
        }

        if ($this->action == 'add') {
            $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdPrograma();
                $this->id_programa= $this->id;
                $this->id_code= $this->obj->get_id_code();
                $this->id_programa_code= $this->id_code;
            }
        }

        if ($this->action == 'update')
            $error= $this->obj->update();

        if (is_null($error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->setIndicadores();
            $this->setProcesos();
        }

        if ($this->action == 'delete')	{
            $radio_date= !is_null($_POST['_radio_date']) ? $_POST['_radio_date'] : $_GET['_radio_date'];
            $observacion= "No. ".$this->obj->GetNumero(). " ".$this->obj->GetInicio(). " - ". $this->obj->GetFin(). " ".$this->obj->GetNombre();

            $error= $this->obj->eliminar($radio_date);

            if (is_null($error) && $radio_date == 2) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tinductores','id_code',$this->id_code);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();
        }

        $url_page= "../php/programa.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

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
                if ($this->action == 'edit') $this->action= 'update';
            ?>
                self.location='../form/fprograma.php?action=<?= $this->action?>#<?= $this->id; ?>';

            <?php } } else {
                $this->obj->error= $error;
                $_SESSION['obj']= serialize($this->obj);
            ?>
                self.location.href='<?php prev_page($error);?>';

            <?php
            }
    }  }
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
        $interface= new Tinterface($clink);
        $interface->apply();
        ?>

       <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>