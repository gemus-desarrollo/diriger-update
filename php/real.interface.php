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
require_once "class/indicador.class.php";
require_once "class/registro.class.php";

require_once "class/matrix.class.php";
require_once "class/resume.class.php";

require_once "class/peso_calculo.class.php";

class Treal_interface extends Tregistro {
    public $obj_matrix;

    public function __construct($clink= null) {
        Tregistro::__construct($clink);

        $this->action = $_POST['exect'];
        $this->menu = $_POST['menu'];
        $this->signal = !empty($_GET['signal']) ? $_GET['signal'] : $_POST['signal'];
        $this->id_escenario = !empty($_GET['escenario']) ? $_GET['escenario'] : $_POST['escenario'];
        $this->id_tablero = !empty($_GET['id_tablero']) ? $_GET['id_tablero'] : null;

        $this->clink = $clink;
    }

    public function apply() {
        $this->year = (int)$_POST['_year'];
        $this->day = (int)$_POST['_day'];
        $this->month = (int)$_POST['_month'];

        $cant = !empty($_POST['cant']) ? (int)$_POST['cant'] : 0;

        $this->periodicidad = (string)$_POST['periodicidad'];
        $this->cumulative = (bool)$_POST['cumulative'];
        $this->formulated = (bool)$_POST['formulated'];
        $this->trend = (int)$_POST['trend'];

        $this->id_usuario = $_SESSION['id_usuario'];

        $obj_peso = new Tpeso_calculo($this->clink);
        $obj_peso->SetYear($this->year);
        $obj_peso->SetMonth($this->month);
        $obj_peso->SetDay($this->day);
        $obj_peso->set_cronos($this->cronos);

        $obj_peso->set_matrix();
        $obj_peso->if_real= true;

        $error = null;
        $_error = null;

        for ($i = 0; $i < $cant; $i++) {
            $this->id_code = $_POST["indicador_code_" . $i];
            $this->id_indicador = $_POST["indicador_" . $i];
            $this->id_indicador_code = $this->id_code;
            $this->value = !is_null($_POST['real_' . $i]) && strlen($_POST['real_' . $i]) > 0 ? $_POST['real_' . $i] : null;
            $this->observacion_real = !is_null($_POST['observacion_' . $i]) ? nl2br(trim($_POST['observacion_' . $i])) : null;
            $this->chk_cumulative= $_POST['chk_cumulative_' . $i];

            if ((is_null($this->value) || !is_numeric($this->value))
                    && (is_null($this->observacion_real) || strlen($this->observacion_real) <= 0))
                continue;

            $this->Set();
            $nombre= $this->GetNombre();

            if (!$this->chk_cumulative)
                $result= $this->update_real();
            else 
                $result= $this->update_cumulative_in_period();

            if (is_null($result))
                continue;

            $obj_peso->SetYearMonth($this->year, $this->month);
            $obj_peso->SetDay($this->day);

            $obj_peso->init_calcular();
            $obj_peso->SetIdIndicador($this->id_indicador);

            if ($result > 0) {
                $obj_peso->SetObservacion($this->observacion_real);
                $obj_peso->set_calcular_indicador($this->id_indicador, false, $this->value);
            }
            if ($result == -1)
                $_error.= " $nombre ---> $this->day/$this->month/$this->year ";
        }

        $obj_peso->close_matrix();

        $url_page= "../php/real.interface.php";
        $url_page.= "?id=$this->id&signal=$this->signal&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_proceso=$this->id_proceso&id_perspectiva=$this->id_perspectiva";
        $url_page.= "&id_inductor=$this->id_inductor&menu=$this->menu";
        $action= "&action=$this->action&exect=$this->action";

        add_page($url_page . $action, $this->action, 'i');

        if (!empty($_error)) {
            $error = "Ha ocurrido un error. Es posible que ha este indicador no se le haya asignado una unidad de medida, ";
            $error .= "o no se le haya definido la periodicidad. ".$_error;
        ?>
            <script language='javascript' type="text/javascript" charset="utf-8">
                self.location.href = '<?php prev_page($error) ?>';
            </script>

        <?php
        } else {
        ?>
            <script language='javascript' type="text/javascript" charset="utf-8">
                self.location.href = '<?php next_page($error); ?>';
            </script>
        <?php
        }
    }

}

$interface = new Treal_interface($clink);
$interface->apply();
?>
