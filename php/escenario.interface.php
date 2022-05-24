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

require_once "class/code.class.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win) include "_header.interface.inc";
?>

<?php
class Tinterface extends Tbase {
    public $menu;
    private $obj;

    public function Tinterface($clink) {
        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->action= !empty($_POST['exect']) ? $_POST['exect'] : $_GET['action'];
        $this->signal= !empty($_POST['signal']) ? $_POST['signal'] : $_GET['signal'];
        $this->menu= !empty($_POST['menu']) ? $_POST['menu'] : $_GET['menu'];

        $this->clink= $clink;
        $this->obj= new Tescenario($this->clink);
        $this->obj_code= new Tcode($this->clink);
    }

    private function set_image_file($signal) {
        if (!$_POST[$signal.'_mapa-trash']) {
            if (!empty($_FILES[$signal.'_mapa-upload']['name'])) {
                $fileName= $_FILES[$signal.'_mapa-upload']['name'];
                $tmpName=  $_FILES[$signal.'_mapa-upload']['tmp_name'];
                $fileSize= $_FILES[$signal.'_mapa-upload']['size'];
                $fileType= $_FILES[$signal.'_mapa-upload']['type'];

                $datos= getimagesize($tmpName);

                $fp= fopen($tmpName, 'rb');
                $image= fread($fp, $fileSize);
                $image= $_SESSION["_DB_SYSTEM"] == "mysql" ? addslashes($image) : bin2hex($image);
                fclose($fp);

                if (!get_magic_quotes_gpc()) $fileName = addslashes($fileName);

                $param['name']= $fileName;
                $param['size']= $fileSize;
                $param['type']= $fileType;
                $param['dim']= $datos[3];

                $this->obj->SetMapa($signal, $image, $param);
        }   }

        if ($_POST[$signal.'_mapa-trash']) {
            $this->obj->SetMapa($signal, 0, 0);
        }
    }

    public function apply() {
        if (!empty($this->id)) {
            $this->obj->Set();
            $this->id_code= $this->obj->get_id_code();
        }

        $this->obj->action= $this->action;
        $this->obj->SetIdEscenario($this->id);

        $this->id_proceso= $_POST['proceso'];
        $this->id_proceso_code= $_POST['proceso_code_'.$this->id_proceso];

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetMision(trim($_POST['mision']));
            $this->obj->SetVision(trim($_POST['vision']));
            $this->obj->SetInicio($_POST['inicio']);
            $this->obj->SetFin($_POST['fin']);
            $this->obj->SetDescripcion(trim($_POST['descripcion']));

             $this->obj->set_observacion('org',trim($_POST['org_observacion']));
             $this->obj->set_observacion('proc',trim($_POST['proc_observacion']));

             $this->obj->SetIdProceso($this->id_proceso);
             $this->obj->set_id_proceso_code($this->id_proceso_code);
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->set_image_file('strat');
            $this->set_image_file('proc');
            $this->set_image_file('org');
        }

        if ($this->action == 'add') {
            $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdEscenario();
                $this->id_escenario= $this->id;
                $this->obj_code->SetId($this->id);
                $this->obj_code->set_code('tescenarios','id','id_code');

                $this->id_code= $this->obj_code->get_id_code();
                $this->obj->set_id_code($this->id_code);
                $this->obj->set_id_escenario_code($this->id_code);
            }
        }

        if ($this->action == 'delete')	{
            $error= $this->obj->eliminar();

            if (is_null($error)) $this->obj_code->reg_delete('tescenarios','id_code',$this->id_code);
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();
        }

        if ($this->action == 'update') {
            $error= $this->obj->update();

            if (is_null($error)) {
                if ($this->id == $_SESSION['current_id_escenario']) {
                    $_SESSION['inicio']= $this->obj->GetInicio(); $_SESSION['fin']= $this->obj->GetFin();
                }
            }
        }

        $url_page= "../php/escenario.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_escenario=$this->id_escenario&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($this->obj);

                if ($this->action == 'add' && $this->id == 1) {
                ?>
                    alert('Este es el primer escenario que se crea en el sistema. Usted deber&aacute; salir y autenticase nuevamente, para comenzar a trabajar en el nuevo escenario.');
                    parent.location='<?= _SERVER_DIRIGER?>index.php';
                <?php
                }

                if (($this->action == 'add' || $this->action == 'update') || $this->action == 'delete') {
                ?>
                    self.location.href='<?php next_page();?>';
                <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                   if ($this->action == 'edit') $this->action= 'update';
                ?>
                    self.location='../form/fescenario.php?action=<?= $this->action?>&signal=<?= $this->signal?>';

            <?php } } else {
                $this->obj->error= $error;
                $_SESSION['obj']= serialize($this->obj);
                ?>
                self.location.href='<?php prev_page($error);?>';
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
