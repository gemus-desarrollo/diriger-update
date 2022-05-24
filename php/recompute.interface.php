<?php
/**
 * @author Geraudis Mustelier
 * Date: 3/7/2016
 * Time: 8:42 a.m. *
 * @copyright 2012
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "class/base.class.php";
require_once "class/connect.class.php";

require_once "class/programa.class.php";
require_once "class/politica.class.php";
require_once "class/objetivo.class.php";
require_once "class/objetivo_ci.class.php";
require_once "class/perspectiva.class.php";
require_once "class/inductor.class.php";

require_once "class/peso.class.php";

require_once "class/code.class.php";
require_once "base.interface.php";


class TrecomputeInterface extends TbaseInterface {
    private $item_recompute;
    private $obj_peso;
    protected $id_tablero;

    public function __construct($clink= null) {
        $this->clink= $clink;
        TbaseInterface::__construct($clink);

        $this->item_recompute= $_GET['item_recompute'];
        $this->id_tablero= !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : null;

        $this->obj_peso= new Tpeso($this->clink);
        $this->obj_peso->SetYear($this->year);
        $this->obj_peso->SetMonth($this->month);
        $this->obj_peso->SetDay($this->day);
    }

    private function proceso() {
        $obj= new Tproceso($this->clink);
        $obj->SetYear($this->year);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            if (!empty($this->id_tablero) && $this->id_tablero != $row['id'])
                continue;
            if (empty($this->id_tablero) && empty($_POST['page_proceso_'.$row['_id']]))
                continue;
            $this->obj_peso->set_calcular_year_proceso($row['id'], $this->year);
        }
    }

    private function perspectiva() {
        $obj= new Tperspectiva($this->clink);
        $obj->SetYear($this->year);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            if (empty($_POST['page_perspectiva_'.$row['_id']]))
                continue;
            $this->obj_peso->set_calcular_year_perspectiva($row['_id'], $this->year);
        }
    }

    private function inductor() {
        $obj= new Tinductor($this->clink);
        $obj->SetYear($this->year);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            if (empty($_POST['page_inductor_'.$row['_id']]))
                continue;
            $this->obj_peso->set_calcular_year_inductor($row['_id'], $this->year);
        }
    }

    private function programa() {
        $obj= new Tprograma($this->clink);
        $obj->SetYear($this->year);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            if (empty($_POST['page_programa_'.$row['_id']]))
                continue;
            $this->obj_peso->set_calcular_year_programa($row['_id'], $this->year);
        }
    }

    private function objetivo() {
        $obj= new Tobjetivo($this->clink);
        $obj->SetYear($this->year);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            if (empty($_POST['page_objetivo_'.$row['_id']]))
                continue;
            $this->obj_peso->set_calcular_year_objetivo($row['_id'], $this->year);
        }
    }

    private function politica() {
        $obj= new Tpolitica($this->clink);
        $obj->SetYear($this->year);
        $result= $obj->listar();

        while ($row= $this->clink->fetch_array($result)) {
            if (empty($_POST['page_politica_'.$row['_id']]))
                continue;
            $this->obj_peso->set_calcular_year_politica($row['_id'], $this->year);
        }
    }

    public function apply() {
        $this->obj_peso= new Tpeso($this->clink);
        $this->obj_peso->SetIdProceso($this->id_proceso);
        $this->obj_peso->SetYear($this->year);
        $this->obj_peso->SetMonth($this->month);
        $this->obj_peso->SetDay($this->day);

        switch ($this->item_recompute) {
            case 'proceso':
                $this->proceso();
                break;
            case 'politica':
                $this->politica();
                $this->objetivo();
                $this->inductor();
                break;
            case 'objetivo':
            case 'objetivo_sup' :
                $this->objetivo();
                $this->inductor();
                break;
            case 'inductor' :
                $this->inductor();
                break;
            case 'empresa':
                $this->politica();
                $this->objetivo();
                $this->inductor();
            case 'perspectiva':
                $this->perspectiva();
                $this->inductor();
                break;
            case 'programa':
                $this->programa();
                $this->inductor();
                break;
            default:
                $this->inductor();
        }

        $url_page= "../php/recompute.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&month=$this->month";
        $url_page.= "&day=$this->day&id_proceso=$this->id_proceso&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');
        ?>
        <script language='javascript' type="text/javascript" charset="utf-8">self.location.href='<?php prev_page();?>'</script>
        <?php
    }
}

$interface= new TrecomputeInterface($clink);
$interface->apply();
?>