<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";


require_once "class/base.class.php";
require_once "class/connect.class.php";
require_once "class/usuario.class.php";
?>

<?php
$ajax_win= false;
include "_header.interface.inc";
?>

<?php
class Tclave_interface extends Tusuario {

    public $menu;

    public function __construct($clink= null) {
        Tusuario::__construct($clink);

        $this->id = $_GET['id'];
        if (empty($this->id))
            $this->id = $_POST['id'];

        $this->action = $_POST['exect'];
        $this->menu = $_POST['menu'];

        $this->clink = $clink;
    }

    public function apply() {
        $this->action = $this->action;
        $this->SetIdUsuario($_POST['id']);

        $error = $this->Set();

        if (is_null($error) && (md5(clean_string(trim($_POST['pwd']))) != $this->GetClave())) {
            $error = "Clave actual incorrecta. Debe escribirla correctamente.";
        }

        if (is_null($error) && (md5(clean_string(trim($_POST['pwd']))) == $this->GetClave())) {
            $this->SetClave(clean_string(trim($_POST['clave'])));
            $this->update_clave();
        }

        if (is_null($error)) {
            $this->redirect = 'ok';
            ?>

            alert('Los cambios tomarán efecto la próxima vez que el usuario acceda al sistema.', function(ok) {
                if (ok) 
                    self.location = '../html/background.php?csfr_token=<?=$_SESSION['csfr_token']?>&';
            });
        <?php
        } else {
            if (!is_null($error))
                $this->redirect = 'fail';

            $obj = new Tbase();
            $obj->error = $error;
            $_SESSION['obj'] = serialize($obj);
            ?>

                self.location = '../form/<?php echo 'f' . $this->menu . '.php?action=update' ?>';
            <?php
        }
    }
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
        $interface = new Tclave_interface($clink);
        $interface->apply();
        ?>
    <?php if (!$ajax_win) { ?> }); <?php } ?>
</script>
