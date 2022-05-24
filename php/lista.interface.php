<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "lista_base.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win) 
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TListaBaseInterface {
    protected $_inicio,
              $_fin;

    public function __construct($clink) {
        $this->clink= $clink;
        TListaBaseInterface::__construct($this->clink);
    }

    public function apply() {
        $error= null;
        $this->obj= new Tlista($this->clink);

        if (!empty($this->id)) {
            $this->obj->SetIdLista($this->id);
            $error= $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();

            $this->_inicio= $this->obj->GetInicio();
            $this->_fin= $this->obj->GetFin();
        }

        $this->obj->action= $this->action;

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetYear($this->year);

            if (!empty($this->id_proceso)) {
                $this->obj->SetIdProceso($this->id_proceso);
                $this->id_proceso_code= get_code_from_table('tprocesos', $this->id_proceso);
                $this->obj->set_id_proceso_code($this->id_proceso_code);
            }

            $this->obj->SetNumero($_POST['numero']);
            $this->obj->SetNombre(trim($_POST['nombre']), false);
            $this->obj->SetDescripcion(trim($_POST['descripcion']));

            $this->inicio= $_POST['inicio'];
            $this->fin= $_POST['fin'];
            $this->obj->SetInicio($this->inicio);
            $this->obj->SetFin($this->fin);
        }

        if ($this->action == 'add') {
            $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdLista();
                $this->id_lista= $this->id;
                $this->id_code= $this->obj->get_id_code();
                $this->id_lista_code= $this->id_code;
            }
        }

        if ($this->action == 'update') {
            $error= $this->obj->update();

            if (is_null($error) && ($this->inicio != $this->_inicio || $this->fin != $this->_fin))
                $this->obj->extend_year();
        }

        if (is_null($error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->id_lista= $this->id;
            $this->id_lista_code= $this->id_code;
            $this->id_requisito= null;
            $this->id_requisito_code= null;

            $this->setProcesos();
        }

        if ($this->action == 'delete')	{
            $radio_date= !is_null($_POST['_radio_date']) ? $_POST['_radio_date'] : $_GET['_radio_date'];
            $error= $this->obj->eliminar($radio_date);

            if (is_null($error) && $radio_date == 2)
                $this->obj_code->reg_delete('tlistas', 'id_code', $this->id_code);
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();
        }

        $url_page= "../php/lista.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month";
        $url_page.= "&day=$this->day&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($this->obj);

                if ($this->action == 'delete' || $this->action == 'update') {
                ?>
                    self.location.href='<?php next_page();?>';
                <?php
                }

                if (($this->action == 'edit' || $this->action == 'add') || $this->action == 'list') {
                    if ($this->action == 'edit' || $this->action == 'add') 
                        $this->action= 'update';
                ?>
                    self.location='../form/flista.php?action=<?= $this->action?>#<?= $this->id; ?>';
                <?php } ?>
            <?php 
            } else {
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