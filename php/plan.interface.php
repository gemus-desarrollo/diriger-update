<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */


session_start();
include_once "setup.ini.php";
include_once "class/config.class.php";

include_once "config.inc.php";
include_once "class/base.class.php";
if (!$clink)
    include_once _ROOT_DIRIGER_DIR."php/class/connect.class.php";

include_once "class/indicador.class.php";
include_once "class/peso_calculo.class.php";

include_once "class/registro.class.php";

include_once "class/matrix.class.php";
include_once "class/resume.class.php";
?>

<?php
global $using_remote_functions;
if (is_null($using_remote_functions))
    include "_header.interface.inc";
?>

<?php
class TplanInterface extends Tregistro {
    private $model;

    public function __construct($clink) {
        Tregistro::__construct($clink);

        $this->clink= $clink;
        $this->id_usuario= $_SESSION['id_usuario'];
        $this->year= $_POST['_year'];
        $this->id_escenario= $_POST['id_escenario'];
        $this->id_indicador= $_POST['id'];
        $this->id_indicador_code=  $_POST['id_code'];
        $this->criterio= $_POST['criterio'];
        $this->periodicidad= $_POST['periodicidad'];
        $this->cumulative= $_POST['cumulative'];
        $this->formulated= $_POST['formulated'];
        $this->trend= $_POST['trend'];
    }

    public function apply() {
        global $periodicidad_value;
        $pmonth= $periodicidad_value[$this->periodicidad];

        $error= null;
        $_error= null;

        $this->Set();

        $obj_peso= new Tpeso_calculo($this->clink);
        $obj_peso->set_cronos($this->cronos);
        $obj_peso->SetYear($this->year);
        $obj_peso->SetIdIndicador($this->id_indicador);
        $obj_peso->set_id_indicador_code($this->id_indicador_code);

        $obj_peso->set_matrix();
        $obj_peso->if_real= false;

        for ($i= $pmonth; $i <= 12; $i+=$pmonth) {
            for ($j=1; $j <= 31; $j++) {
                $this->month= (int)$i;
                $this->day= (int)$j;

                if (is_null($this->model)) {
                    $this->plan= $_POST["plan_".$this->month."_".$this->day];
                    $this->plan_cot= $_POST["plan_cot_".$this->month."_".$this->day];
                    $this->observacion_plan= nl2br(trim($_POST['observacion_'.$this->month."_".$this->day]));
                } else {
                    $this->plan= $this->model->array_values['plan'][$this->year][$this->month][$this->day];
                    $this->plan_cot= $this->model->array_values['plan_cot'][$this->year][$this->month][$this->day];
                    $this->observacion_plan= $this->model->array_values['observacion'][$this->year][$this->month][$this->day];
                }
                if (((is_null($this->plan) || !is_numeric($this->plan)) && (is_null($this->plan_cot) || !is_numeric($this->plan_cot)))
                    && (is_null($this->observacion_plan) || strlen($this->observacion_plan) <= 0))
                    continue;

                $cant= $this->update_plan($this->day, $this->month, $this->year);
                if (is_null($cant))
                    continue;

                $obj_peso->SetYearMonth($this->year, $this->month);
                $obj_peso->SetDay($this->day);

                $obj_peso->init_calcular();
                if ($cant > 0) {
                    $obj_peso->SetObservacion($this->observacion_plan);
                    $obj_peso->set_calcular_indicador($this->id_indicador, false);
                }
                if ($cant == -1)
                    $_error.= " $this->day/$this->month/$this->year;  ";
            }
        }

        $obj_peso->close_matrix();

        if (!is_null($this->model))
            return null;

        $url_page= "../php/plan.interface.php";
        $url_page.= "?id=$this->id&year=$this->year&month=$this->month&day=$this->day";
        $action= "&action=$this->action&exect=$this->action";

        add_page($url_page.$action,$this->action,'i');

        if (!empty($_error)) {
            $error= "Ha ocurrido un error. Es posible que ha este indicador no se le haya asignado una unidad de medida, ";
            $error.= "o no se le haya definido la periodicidad. ".$_error;
    ?>
            self.location.href='<?php prev_page($error) ?>';

    <?php
        } else {
    ?>
            self.location.href='<?php next_page($error);?>';
    <?php
        }
    }
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
            $interface= new TplanInterface($clink);
            $interface->apply();
            ?>
        });
    </script>
<?php } ?>