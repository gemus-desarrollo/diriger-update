<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 7/02/15
 * Time: 18:51
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "interface.class.php";
require_once "class/connect.class.php";
require_once "class/time.class.php";
require_once "class/proceso.class.php";
require_once "class/proceso_item.class.php";
require_once "class/tarea.class.php";

require_once "class/nota.class.php";
require_once "class/riesgo.class.php";

require_once "class/code.class.php";


class Tinterface extends TbaseInterface {
    protected $id_nota;
    protected  $id_nota_code;
    protected $id_causa;
    protected $id_causa_code;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TbaseInterface::__construct($clink);

        $this->id_nota= !empty($_GET['id_nota']) ? $_GET['id_nota'] : null;
        $this->id_riesgo= !empty($_GET['id_riesgo']) ? $_GET['id_riesgo'] : null;
        $this->id_causa= !empty($_GET['id_causa']) ? $_GET['id_causa'] : null;
    }

    protected function set_estado() {
        $obj_prs= new Tproceso_item($this->clink);
        $obj_prs->SetIdRiesgo($this->id_riesgo);
        $obj_prs->SetIdNota($this->id_nota);
        $obj_prs->GetProcesosRiesgo();

        if (empty($obj_prs->GetCantidad()))
            return null;

        $this->obj->SetIdUsuario($_SESSION['id_usuario']);
        $this->obj->setEstado(_GESTIONANDOSE);
        $this->obj->SetObservacion("Análisis de causa");
        $this->obj->SetFecha($this->cronos);

        foreach ($obj_prs->array_procesos as $id => $prs)
            $this->obj->set_estado($prs['id'], $prs['id_code']);
    }

    public function apply() {
        $this->obj= $this->id_nota ? new Tnota($this->clink) : new Triesgo($this->clink);
        $this->obj->SetIdNota($this->id_nota);
        $this->obj->SetIdRiesgo($this->id_riesgo);

        $this->obj->Set();
        $this->id_nota_code= $this->obj->get_id_nota_code();
        $this->id_riesgo_code= $this->obj->get_id_riesgo_code();

        if (!empty($this->id_causa)) {
            $this->obj->set_causa($this->id_causa);
            $this->id_causa_code= $this->obj->get_id_causa_code();
        }

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetIdUsuario($_SESSION['id_usuario']);
            $this->obj->SetFecha(date2odbc(urldecode($_GET['fecha_reg'])));
            $this->obj->SetDescripcion(urldecode($_GET['descripcion']));
        }

        if ($this->action == 'add')
            $this->obj->add_causa();

        if ($this->action == 'update')
            $this->obj->update_causa();

        if ($this->action == 'add' || $this->action == 'update')
            $this->set_estado();

        if ($this->action == 'delete')
            $this->obj->eliminar_causa();

        if (is_null($this->error)) {
?>
            <script language='javascript' type="text/javascript" charset="utf-8">
                refresh_ajax_causas();
            </script>

        <?php  } else { ?>
            <script language='javascript' type="text/javascript" charset="utf-8">
                alert("<?=$this->error?>");
                cerrar();
            </script>
<?php
        }
    }
}

$interface= new Tinterface($clink);
$interface->apply();

?>