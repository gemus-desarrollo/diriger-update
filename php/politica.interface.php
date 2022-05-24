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

require_once "class/politica.class.php";
require_once "class/objetivo.class.php";
require_once "class/peso.class.php";

require_once "class/code.class.php";
require_once "base.interface.php";
?>

<?php
global $using_remote_functions;
if (is_null($using_remote_functions)) 
    include "_header.interface.inc";
?>

<?php
class TpoliticaInterface extends TbaseInterface {

    public function __construct($clink) {
        $this->clink= $clink;

        TbaseInterface::__construct($clink);

        $this->id_escenario= $_SESSION['current_id_escenario'];
        $this->id_escenario_code= $_SESSION['current_id_escenario_code'];

        $this->id_proceso= !empty($_POST['id_proceso']) ? $_POST['id_proceso'] : $_SESSION['id_entity'];
        $this->id_proceso_code= !empty($_POST['id_proceso_code']) ? $_POST['id_proceso_code'] : $_SESSION['id_entity_code'];

        $this->capitulo= $_POST['capitulo'];
        $this->grupo= $_POST['grupo'];
        $this->numero= $_POST['numero'];

        $this->if_titulo= $_POST['if_titulo'];
        $this->politica=  $_POST['politica'];
    }

    private function setPeso_objetivos() {
        $this->error= null;
        $obj_objetivo= new Tobjetivo($this->clink);

        $obj_objetivo->SetInicio($this->inicio);
        $obj_objetivo->SetFin($this->fin);
        $obj_objetivo->SetYear($this->year);

        $obj_objetivo->SetIdPolitica($this->id);
        $obj_objetivo->set_id_politica_code($this->id_code);

        $obj= new Tobjetivo($this->clink);
        $result= $obj->listar();

        $array_ids= array();
        while ($row= $this->clink->fetch_array($result)) {
            if ($array_ids[$row['_id']])
                continue;
            $array_ids[$row['_id']]= $row['_id'];

            $obj_objetivo->SetId($row['_id']);
            $obj_objetivo->SetIdObjetivo($row['_id']);
            $obj_objetivo->set_id_objetivo_code($row['_id_code']);
            $obj_objetivo->SetIfObjetivoSup(false);

            $obj_objetivo->SetYear($this->year);
            $obj_objetivo->SetIdProceso($this->id_proceso);
            $obj_objetivo->set_id_proceso_code($this->id_proceso_code);

            $value= $_POST['select_obji'.$row['_id']];
            $_value= $_POST['init_obji'.$row['_id']];

            if ((int)$value > 0 && (int)$value != (int)$_value) {
                $obj_objetivo->SetPeso($value);
                $obj_objetivo->expand_period_ref($this->id);

            } else {
                if ((int)$_value > 0 && empty($value))
                    $obj_objetivo->delete_period_ref($this->id);
            }
        }

        $this->clink->free_result($result);
        unset($obj_objetivo);
    }

    public function apply() {
        $obj= new Tpolitica($this->clink);

        if (!empty($this->id)) {
            $obj->SetIdPolitica($this->id);
            $error= $obj->Set();
            $this->id_code= $obj->get_id_code();
        }

        $obj->action= $this->action;

        if ($this->action == 'add') {
            $obj->SetIdProceso($this->id_proceso);
            $obj->set_id_proceso_code($this->id_proceso_code);
        }

        $this->capitulo= !empty($this->capitulo) ? $this->capitulo : null;
        $this->grupo= !empty($this->grupo) ? $this->grupo : null;

        $obj->SetCapitulo($this->capitulo);
        $obj->SetGrupo($this->grupo);
        $obj->SetNumero($this->numero);

        $obj->SetIfTitulo($this->if_titulo);

        $flag= $this->if_titulo ? true : false;
        $obj->SetNombre(trim($this->politica), $flag);
		$obj->SetObservacion(trim($this->observacion));

        $obj->SetYear($this->year);
        $obj->SetInicio($this->inicio);
        $obj->SetFin($this->fin);


        if ($this->action == 'add') {
            $if_inner= !is_null($this->model) ? $this->model->if_inner : 1;
            $error= $obj->add($if_inner);

            if (is_null($error)) {
                $this->id= $obj->GetIdPolitica();
                $this->id_code= $obj->get_id_politica_code();
                $this->id_politica_code= $this->id_code;
            }
        }

        if ($this->action == 'update') {
            $error= $obj->update();
        }

        if (is_null($error) && ($this->action == 'add' || $this->action == 'update') && !$this->if_titulo)
            $error= $this->setPeso_objetivos();

        if ($this->action == 'delete') {
            $observacion= "No. ".$obj->GetNumero(). " ".$obj->GetNombre();

            $error= $obj->eliminar($this->_radio_date);

            if (is_null($error)) {
                $this->obj_code->SetObservacion($observacion);
                $this->obj_code->reg_delete('tpoliticas','id',$this->id);
            }
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $obj->Set();
        }

        if (!is_null($this->model))
            if ($this->action != 'edit') {
                return $error;
            }  else {
                return $obj;
            }

        $url_page= "../php/politica.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($obj);

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                ?>

                    self.location.href='<?php next_page();?>';
                <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit') $this->action= 'update';
                ?>
                    self.location='../form/fpolitica.php?action=<?= $this->action?>#<?= $this->id; ?>';

                <?php } } else {
                    $obj->error= $error;
                    $_SESSION['obj']= serialize($obj);
                ?>
                self.location.href='<?php prev_page($error);?>';
            <?php
            }
    }   }
}
?>
<?php if (is_null($using_remote_functions)) { ?>
            </div>
        </body>
    </html>

    <script type="text/javascript">
        $(document).ready(function() {
            setInterval('setChronometer()',1);

            $('#body-log table').mouseover(function() {
                _moveScroll= false;
            });
            $('#body-log table').mouseout(function() {
                _moveScroll= true;
            });
            <?php
            $interface= new TpoliticaInterface($clink);
            $interface->apply();
            ?>
        });
    </script>
<?php } ?>