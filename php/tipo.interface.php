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

require_once "class/base_tipo.class.php";
require_once "class/tipo_reunion.class.php";
require_once "class/tipo_auditoria.class.php";

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends Tbase {

    public $menu;

    public function Tinterface($clink) {
        $this->clink = $clink;

        $this->id = !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->action = !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->menu = !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];
        $this->signal = !empty($_GET['signal']) ? $_GET['signal'] : $_POST['signal'];
        $this->year = !empty($_GET['year']) ? $_GET['year'] : $_POST['year'];

        $this->inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $_POST['inicio'];
        $this->fin= !empty($_GET['fin']) ? $_GET['fin'] : $_POST['fin'];

        $this->numero = !empty($_GET['numero']) ? $_GET['numero'] : $_POST['numero'];
        $this->error = !empty($_GET['error']) ? $_GET['error'] : null;

        $this->obj_code= new Tcode($this->clink);
    }

    public function apply() {
        $obj = null;

        switch ($this->menu) {
            case('tipo_auditoria'):
                $obj = new Ttipo_auditoria($this->clink);
                break;
            case('tipo_reunion'):
                $obj = new Ttipo_reunion($this->clink);
                break;
        }

        if (!empty($this->id)) {
            switch ($this->menu) {
                case('tipo_auditoria'):
                    $obj->SetIdTipo_auditoria($this->id);
                    break;
                case('tipo_reunion'):
                    $obj->SetIdTipo_reunion($this->id);
                    break;
            }
            $obj->SetId($this->id);

            $error = $obj->Set();
            $this->id_code = $obj->get_id_code();
        }

        $this->id_proceso = $_POST['id_proceso'];
        $obj->SetIdProceso($this->id_proceso);
        $this->id_proceso_code= get_code_from_table("tprocesos", $this->id_proceso, $this->clink);
        $obj->set_id_proceso_code($this->id_proceso_code);

        if ($this->action == 'add' || $this->action == 'update') {
            $obj->action = $this->action;
            $obj->SetYear($this->year);
            $obj->SetInicio($this->inicio);
            $obj->SetFin($this->fin);
            $obj->SetNumero($_POST['numero']);
            $obj->SetNombre(trim($_POST['nombre']), $this->menu == 'tipo_auditoria' ? true : false);
            $obj->SetDescripcion(trim($_POST['descripcion']));
        }

        if ($this->action == 'add') {
            $error = $obj->add();
        }

        if ($this->action == 'update') {
            $this->error = $obj->update();
        }

        if ($this->action == 'delete') {
            $error = $obj->eliminar();
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error = $obj->Set();
        }

        $error = !empty($this->error) ? $this->error . " " . $error : $error;

        $url_page = "../php/tipo.interface.php";
        $url_page .= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year";
        $url_page .= "&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj'] = serialize($obj);

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                    ?>
                    self.location.href = '<?php next_page(); ?>';
                    <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit')
                        $this->action = 'update';
                    ?>
                        self.location = '../form/<?= "f" . $this->menu ?>.php?action=<?= $this->action ?>#<?= $this->id ?>';
                <?php
                }
            } else {
                $obj->error = $error;
                $_SESSION['obj'] = serialize($obj);

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit')
                        $this->action = 'update';
                    ?>
                        self.location.href = '../form/<?= "f" . $this->menu ?>.php?action=<?= $this->action ?>&signal=<?= "$this->signal#$this->id"; ?>&error=<?php $error ?>';
                <?php } ?>
                    self.location.href = '<?php prev_page($error); ?>';
                <?php
            }
    }    }

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
        <?php } ?>

            <?php
            $interface = new Tinterface($clink);
            $interface->apply();
            ?>

        <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>