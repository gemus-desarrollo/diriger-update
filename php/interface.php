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

require_once "class/perspectiva.class.php";
require_once "class/programa.class.php";
require_once "class/unidad.class.php";
require_once "class/tipo_evento.class.php";

require_once "class/tipo_lista.class.php";

require_once "class/badger.class.php";

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

    private $obj;
    public $menu;

    protected $_componente;
    protected $id_lista,
            $id_lista_code;
    protected $capitulo, 
            $_capitulo,
            $subcapitulo,
            $_subcapitulo;        
    protected $id_capitulo,
            $id_capitulo_code,
            $_id_capitulo;
    protected $id_subcapitulo,
            $id_subcapitulo_code,
            $_id_subcapitulo;
    protected $id_subcapitulo0,
            $_id_subcapitulo0;
    protected $id_subcapitulo1,
            $_id_subcapitulo1;

    protected $empresarial;

    public function __construct($clink) {
        $this->clink = $clink;
        Tbase::__construct($clink);

        $this->id = !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->action = !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->menu = !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];
        $this->signal = !empty($_GET['signal']) ? $_GET['signal'] : $_POST['signal'];
        $this->year = !empty($_GET['year']) ? $_GET['year'] : $_POST['year'];
        $this->month = !empty($_GET['month']) ? $_GET['month'] : $_POST['month'];
        $this->day = !empty($_GET['day']) ? $_GET['day'] : $_POST['day'];

        $this->inicio= !empty($_GET['inicio']) ? $_GET['inicio'] : $_POST['inicio'];
        $this->fin= !empty($_GET['fin']) ? $_GET['fin'] : $_POST['fin'];

        $this->id_escenario = !empty($_GET['id_escenario']) ? $_GET['id_escenario'] : $_POST['id_escenario'];
        $this->id_escenario_code = $_POST['id_escenario_code_' . $this->id_escenario];
        $this->error = !empty($_GET['error']) ? $_GET['error'] : null;

        $this->obj_code= new Tcode($this->clink);
    }

    private function update_cascade_lista() {
        $obj= new Ttipo_lista($this->clink);
        $obj->SetYear($this->year);
        $obj->SetIdLista($this->id_lista);
        $obj->SetIdTipo_lista($this->id);
        $obj->Set();

        if ($this->_componente != $this->componente)
            $obj->update_componente_down();

        if (!empty($this->_id_capitulo) && $this->_id_capitulo != $this->id_capitulo)
            $obj->update_capitulo_down($this->_id_capitulo); 
    }

    private function update_cascade_evento() {
        $obj= new Ttipo_evento($this->clink);
        $obj->SetYear($this->year);
        $obj->SetIdProceso($_SESSION['id_entity']);
        $obj->SetIdTipo_evento($this->id);
        $obj->Set();
        
        if ($this->empresarial != $this->_empresarial)
            $obj->update_empresarial_down();

        if (!empty($this->_id_subcapitulo) && $this->_id_subcapitulo != $this->id_subcapitulo)
            $obj->update_subcapitulo_down($this->_id_subcapitulo);
    }

    private function _set_tipo_lista() {
        $this->_componente= $this->obj->GetComponente();
        $this->_id_capitulo= $this->obj->GetIdCapitulo();
        $this->_capitulo= $this->obj->GetCapitulo();
        $this->_subcapitulo= $this->obj->GetSubCapitulo();

        $this->obj->setNumero($_POST['numero']);

        $this->id_capitulo= !empty($_POST['id_capitulo']) ? $_POST['id_capitulo'] : null;
        $this->id_capitulo_code= !empty($id_capitulo) ? get_code_from_table('ttipo_listas', $this->id_capitulo) : null;
        $this->obj->SetIdCapitulo($this->id_capitulo);
        $this->obj->set_id_capitulo_code($this->id_capitulo_code);

        $this->capitulo= !empty($_POST['capitulo']) ? $_POST['capitulo'] : null;
        $this->obj->SetCapitulo($this->capitulo);

        $this->subcapitulo= !empty($_POST['subcapitulo']) ? $_POST['subcapitulo'] : null;
        $this->obj->SetSubcapitulo($this->subcapitulo);
        
        $this->inicio= !empty($this->inicio) && $this->inicio <= $this->year ? $this->inicio : $this->year;
        $this->obj->SetInicio(!empty($_POST['inicio']) && $_POST['inicio'] <= $this->year ? $_POST['inicio'] : $this->year);
        $this->fin= !empty($this->fin) ? $this->fin : $this->year;
        $this->obj->SetFin($this->fin);

        $this->id_lista= $_POST['id_lista'];

        if (!empty($this->id_lista)) {
            $this->obj->SetIdLista($this->id_lista);
            $this->id_lista_code= get_code_from_table('tlistas', $this->id_lista);
            $this->obj->set_id_lista_code($this->id_lista_code);
        }

        $this->componente= $_POST['componente'];
        $this->obj->SetComponente($this->componente);        
    }

    private function _set_tipo_evento() {
        $this->_empresarial= $this->obj->GetIfEmpresarial();
        $this->_id_subcapitulo= $this->obj->GetIdSubcapitulo();

        $this->obj->setNumero($_POST['numero']);

        $this->id_subcapitulo= !empty($_POST['subcapitulo']) ? $_POST['subcapitulo'] : null;
        $this->id_subcapitulo_code= !empty($this->id_subcapitulo) ? get_code_from_table('ttipo_eventos', $this->id_subcapitulo) : null;
        $this->obj->SetIdSubcapitulo($this->id_subcapitulo);
        $this->obj->set_id_subcapitulo_code($this->id_subcapitulo_code);

        $this->id_subcapitulo0= !empty($_POST['subcapitulo0']) ? $_POST['subcapitulo0'] : null;
        $this->obj->SetSubcapitulo0($this->id_subcapitulo0);
        $this->id_subcapitulo1= !empty($_POST['subcapitulo1']) ? $_POST['subcapitulo1'] : null;
        $this->obj->SetSubcapitulo1($this->id_subcapitulo1);

        $this->empresarial= $_POST['empresarial'];
        $this->obj->SetIfEmpresarial($this->empresarial);

        $this->obj->SetInicio($_POST['inicio']);
        $this->obj->SetFin($_POST['fin']);
    }

    public function apply() {
        $this->obj = null;

        switch ($this->menu) {
            case('unidad'):
                $this->obj = new Tunidad($this->clink);
                break;
            case('tipo_evento'):
                $this->obj = new Ttipo_evento($this->clink);
                break;
            case('tipo_lista'):
                $this->obj = new Ttipo_lista($this->clink);
                break;
        }

        if (!empty($this->id)) {
            switch ($this->menu) {
                case('unidad'):
                    $this->obj->SetIdUnidad($this->id);
                    break;
                case('tipo_evento'):
                    $this->obj->SetIdTipo_evento($this->id);
                    break;
                case('tipo_lista'):
                    $this->obj->SetIdTipo_lista($this->id);
                    break;
            }

            $error = $this->obj->Set();
            $this->id_code = $this->obj->get_id_code();
        }

        $this->obj->action = $this->action;

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetYear($this->year);
            $this->obj->SetInicio($this->inicio);
            $this->obj->SetFin($this->fin);
            $this->obj->SetNombre(trim($_POST['nombre']), false);
            $this->obj->SetDescripcion(trim($_POST['descripcion']));            
        }

        if ($this->menu == 'unidad') {
            $this->obj->SetDecimal($_POST['decimal']);
        }

        if ($this->menu == 'tipo_evento') {
            $this->_set_tipo_evento();
        }

        if ($this->menu == 'tipo_lista') {
            $this->_set_tipo_lista();
        }

        $this->id_proceso = $_POST['id_proceso'];
        $this->obj->SetIdProceso($this->id_proceso);
        $this->id_proceso_code= get_code_from_table("tprocesos", $this->id_proceso, $this->clink);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->obj->SetIdEscenario($_POST['id_escenario_' . $this->id_proceso]);
        $this->obj->set_id_escenario_code($_POST['id_escenario_code_' . $this->id_proceso]);

        if ($this->action == 'add') {
            $error = $this->obj->add();
        }

        if ($this->action == 'update') {
            $this->error = $this->obj->update();

            if ($this->menu == 'tipo_lista')
                $this->update_cascade_lista();

            if ($this->menu == 'tipo_evento')
                $this->update_cascade_evento(); 
        }

        if ($this->action == 'delete') {
            $radio_date = !is_null($_POST['_radio_date']) ? $_POST['_radio_date'] : $_GET['_radio_date'];
            $error = $this->obj->eliminar($radio_date);
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error = $this->obj->Set();
        }

        $error = !empty($this->error) ? $this->error . " " . $error : $error;

        $url_page = "../php/interface.php";
        $url_page .= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year";
        $url_page .= "&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj'] = serialize($this->obj);

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
                $this->obj->error = $error;
                $_SESSION['obj'] = serialize($this->obj);

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