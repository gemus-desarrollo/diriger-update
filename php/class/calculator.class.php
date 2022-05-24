<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 4/14/15
 * Time: 4:14 p.m.
 */


define('PI','3.14159');
define('E', '2.71828');

if (!class_exists('Tregistro'))
    include_once "registro.class.php";

class Tcalculator extends Tregistro {
    public $fix_cumulative;
    public $use_real;
    public $array_values;
    public $contents;

    private $array_inds;

    public function __construct($clink= null) {
        Tregistro::__construct($clink);
        $this->clink= $clink;

        $this->fix_cumulative= false;
        $this->use_real= false;
        $this->array_values= array();

        $this->_init();
    }

    public function _init() {
        $this->null_real_found= null;
        $this->updated= true;
        $this->valid_period= true;

        $this->null_plan_found= null;
        $this->updated_plan= true;

        $this->not_null_plan_found= null;
        $this->not_null_acumulado_plan_found= null;
        $this->not_null_real_found= null;
        $this->not_null_acumulado_real_found= null;
    }

    public function Set() {
        $value= null;
        $accumulated= null;

        $this->value= null;
        $this->acumulado_real= null;
        $this->plan= null;
        $this->acumulado_plan= null;
        $this->acumulado_plan_cot= null;

        $date= $this->year."-".str_pad($this->month, 2, '0', STR_PAD_LEFT)."-".str_pad($this->day, 2, '0' ,STR_PAD_LEFT);
        $this->id_usuario= _USER_SYSTEM;

        $value= $this->compute($this->use_real);

        if (is_null($value))
            return null;

        if ($this->use_real) {
            $this->row_real['valor']= $value[0];
            $this->row_real['acumulado_real']= $this->cumulative ? $value[1] : null;
            $this->row_real['id_usuario']= _USER_SYSTEM;

            $this->row_real['fecha']= $date;
            $this->row_real['observacion']= null;
            $this->row_real['cronos']= $this->cronos;

            $this->row_real['updated']= $this->obj_calc->updated;
            $this->row_real['valid_period']= $this->obj_calc->valid_period;

        } else {
            $this->row_plan['plan']= $this->trend != 3 ? $value[0] : null;
            $this->row_plan['plan_cot']= $this->trend == 3 ? $value[0] : null;
            $this->row_plan['acumulado_plan']= $this->trend != 3 && $this->cumulative ? $value[1] : null;
            $this->row_plan['acumulado_plan_cot']= $this->trend == 3 && $this->cumulative ? $value[1] : null;

            $this->row_plan['id_usuario']= _USER_SYSTEM;
            $this->row_plan['cronos']= $this->cronos;

            $this->row_plan['observacion']= null;
            $this->row_plan['reg_date']= $date;

            $this->row_plan['updated_plan']= $this->obj_calc->updated_plan;
        }

        return $value;
    }

    public function convert2code_str($calculo) {
        $obj_ind= new Tindicador($this->clink);
        $formula= null;

        $n= preg_match_all('/\_[A-Z]{2}[0-9]{10}/i', $calculo, $array_code);
        if ($n == 0)
            return null;

        $formula= $calculo;
        foreach ($array_code[0] as $item) {
            $id_code= substr($item, 1);
            $obj_ind->Set(null, $id_code);
            $nombre= $obj_ind->GetNombre();
            $formula= preg_replace("/_$id_code/", "'$nombre'", $formula);
        }
        return $formula;
    }

    public function validar_bucle($id_code, $calculo) {
        if (isset($this->array_inds)) unset($this->array_inds);
        if (!empty($id_code))
            $this->array_inds[$id_code]= $id_code;

        $n= preg_match_all('/\_[A-Z]{2}[0-9]{10}/i', $calculo, $array_code);
        if ($n == 0)
            return null;

        foreach ($array_code[0] as $item) {
            $id_code= substr($item, 1);
            $_result= $this->_validar_code($id_code);
            if ($_result)
                return $_result;
        }

        return null;
    }

    public function _validar_code($id_code) {
        $sql= "select * from tformulas where id_indicador_ref_code = '$id_code'";
        $result= $this->do_sql_show_error('_validar_code', $sql);

        while ($row= $this->clink->fetch_array($result)) {
            if (array_key_exists($row['id_indicador_code'], (array)$this->array_inds))
                return "Hay un indicador en la formula, presente al menos mas de una vez. No se admite este tipo de fórmula";
            else {
                $this->array_inds[$row['id_indicador_code']]= $row['id_indicador_code'];
                $_result= $this->_validar_code($row['id_indicador_code']);
                if ($_result)
                    return $_result;
        }   }
        return null;
    }

    public function error_formulated($calculo) {
        $error= null;

        if (substr_count($calculo, "(") != substr_count($calculo, ")"))
            $error= "Posible error en los operadores de la formula. Por favor, revise";
        if (substr_count($calculo, "'") % 2 != 0)
            $error= "Error en el uso de las comillas. Revise la formula";

        return $error;
    }

    private function IND_($id_code) {
        $year= $this->year;
        $month= $this->month;
        $day= $this->day;

        $value= null;
        if (!is_null($this->array_values[$id_code][$year][$month][$day])) {
            if ($this->use_real) {
                if ($this->array_values[$id_code]['cumulative'] && $this->fix_cumulative)
                    $value= $this->array_values[$id_code][$year][$month][$day]['acumulado_real'];
                else
                    $value= $this->array_values[$id_code][$year][$month][$day]['real'];
            } else {
                if ($this->array_values[$id_code]['cumulative'] && $this->fix_cumulative) {
                    if ($this->array_values[$id_code]['trend'] != 3)
                        $value= $this->array_values[$id_code][$year][$month][$day]['acumulado_plan'];
                    else
                        $value= $this->array_values[$id_code][$year][$month][$day]['acumulado_plan_cot'];
                } else {
                    if ($this->array_values[$id_code]['trend'] != 3)
                        $value= $this->array_values[$id_code][$year][$month][$day]['plan'];
                    else
                        $value= $this->array_values[$id_code][$year][$month][$day]['plan_cot'];
            }   }

            if (!$this->use_real && !is_null($value))
                if (!$this->fix_cumulative)
                    $this->not_null_plan_found= true;
                else
                    $this->not_null_acumulado_plan_found= true;

            if ($this->use_real && !is_null($value))
                if (!$this->fix_cumulative)
                    $this->not_null_real_found= true;
                else
                    $this->not_null_acumulado_real_found= true;

            return !is_null($value) ? $value : 0;
        }

        $id= $this->obj_code->get_id_from_id_code($id_code, 'tindicadores');

        $cell= new Tcell($this->clink);
        $cell->recompute= (int)$this->recompute;

        $cell->fix_year= true;
        $cell->fix_interval= true;
        $cell->compute_traze= false;

        $cell->SetYear($year);
        $cell->SetMonth($month);
        $cell->SetDay($day);
        $cell->SetIndicador($id);

        $this->array_values[$id_code]['cumulative']= $cell->GetIfCumulative();
        $this->array_values[$id_code]['formulated']= $cell->GetIfFormulated();
        $this->array_values[$id_code]['trend']= $cell->GetTrend();

        $cell->get_register();

        if (!$cell->updated && $this->use_real)
            $this->updated= false;
        if (!$cell->updated_plan && !$this->use_real)
            $this->updated_plan= false;
        if (!$cell->valid_period && $this->use_real)
            $this->valid_period= false;

        $real= $cell->GetReal();
        $acumulado_real= $cell->GetAcumuladoReal();
        $plan= $cell->GetPlan();
        $acumulado_plan= $cell->GetAcumuladoPlan();
        $plan_cot= $cell->GetPlan_cot();
        $acumulado_plan_cot= $cell->GetAcumuladoPlan_cot();
        /*
        $real= !is_null($cell->GetReal()) ? $cell->GetReal() : 0;
        $acumulado_real= !is_null($cell->GetAcumuladoReal()) ? $cell->GetAcumuladoReal() : 0;
        $plan= !is_null($cell->GetPlan()) ? $cell->GetPlan() : 0;
        $acumulado_plan= !is_null($cell->GetAcumuladoPlan()) ? $cell->GetAcumuladoPlan() : 0;
        $plan_cot= !is_null($cell->GetPlan_cot()) ? $cell->GetPlan_cot() : 0;
        $acumulado_plan_cot= !is_null($cell->GetAcumuladoPlan_cot()) ? $cell->GetAcumuladoPlan_cot() : 0;
        */
        $array= array('real'=>$real, 'acumulado_real'=>$acumulado_real, 'plan'=>$plan, 'acumulado_plan'=>$acumulado_plan,
                    'plan_cot'=>$plan_cot, 'acumulado_plan_cot'=>$acumulado_plan_cot, 'updated'=>$this->updated,
                    'updated_plan'=>$this->updated_plan, 'valid_period'=>$this->valid_period);

        $this->array_values[$id_code][$year][$month][$day]= $array;

        if (is_null($cell->GetPlan()) && is_null($cell->GetAcumuladoPlan()) && is_null($cell->GetAcumuladoPlan_cot())) {
            if (!$this->null_plan_found)
                $this->null_plan_found= array();
            $this->null_plan_found[$id]= $id;
        }
        if (is_null($cell->GetReal()) && is_null($cell->GetAcumuladoReal())) {
            if (!$this->null_real_found)
                $this->null_real_found= array();
            $this->null_real_found[$id]= $id;
        }

        return $this->IND_($id_code);
    }

    private function purgehtml() {
        $contents= $this->contents;

        $n= preg_match_all('/[\+,\-,\*,\/]{2}|[\(][\+,\-,\*,\/]|[\+,\-,\*,\/][\)]/i', $contents);
        if ($n)
            return "false;";

        $n= preg_match_all('/\_[A-Z]{2}[0-9]{10}/i', $contents, $array_code);
        if ($n == 0)
            return "false;";

        foreach ($array_code[0] as $code)
            $contents= str_replace($code, "\$this->IND_('".substr($code, 1)."')", $contents);

        $contents= preg_replace('/SIN\(/i', 'cSIN(', $contents);
        $contents= preg_replace('/COS\(/i', 'cCOS(', $contents);
        $contents= preg_replace('/TAN\(/i', 'cTAN(', $contents);
        $contents= preg_replace('/ASIN\(/i', 'cASIN(', $contents);
        $contents= preg_replace('/ACOS\(/i', 'cACOS(', $contents);
        $contents= preg_replace('/ATAN\(/i', 'cEXP(', $contents);
        $contents= preg_replace('/EXP\(/i', 'cEXP(', $contents);
        $contents= preg_replace('/LOG\(/i', 'cLOG(', $contents);
        $contents= preg_replace('/SQRT\(/i', 'cSQRT(', $contents);
        return $contents.";";
    }

    public function compute($use_real= true) {
        $this->use_real= is_null($use_real) ? true : $use_real;

        $result= true;
        $result_cumulative= true;
        $value= null;
        $cumulative= null;

        if (is_null($this->contents))
            return null;

        $contents= $this->purgehtml();

        try {
            if ($this->use_real)
                $this->not_null_real_found= false;
            else
                $this->not_null_plan_found= false;
            $this->fix_cumulative= false;
            $result= eval("\$value=$contents;");
        } catch (Exception $e) {
            if ($this->use_real) {
          ?>
            <script language="javascript" type="text/javascript">
                <?php if ($this->null_real_found || $this->null_plan_found) { ?>
                    text= "Hay indicadores que no tienen valores definidos en la fecha de referencia ";
                    alert(text);
                <?php } ?>
                text= "Hay un error en la fórmula de cálculo del indicador <strong><?=$this->nombre?></strong>,
                text+= "o realmente no es un indicador que se cálcule por el sistema a partir de otros indicadores primarios.";
                alert(text);
            </script>
          <?php
           }
           $result= false;
        }

        if (is_null($result) && $this->cumulative) {

            try {
                if ($this->use_real)
                    $this->not_null_acumulado_real_found= false;
                else
                    $this->not_null_acumulado_plan_found= false;

                $this->fix_cumulative= true;
                $result_cumulative= eval("\$cumulative=$contents;");
            } catch (Exception $e) {
                if ($this->use_real) {
              ?>
                <script language="javascript" type="text/javascript">
                <?php if ($this->null_real_found || $this->null_plan_found) { ?>
                    text= "Hay indicadores que no tienen valores definidos en la fecha de referencia ";
                    alert(text);
                <?php } ?>
                    text= "Hay un error en la fórmula de cálculo del indicador <strong><?=$this->nombre?></strong>,
                    text+= "o realmente no es un indicador que se cálcule por el sistema a partir de otros indicadores primarios.";
                    alert(text);
                </script>
              <?php
               }
               $result_cumulative= false;
            }
        }

        if ($result === false || ($use_real && $value === false))
            $value= null;
        if ($value === false)
            $value= null;
        if ($result_cumulative === false || ($use_real && $cumulative === false))
            $cumulative= null;
        if ($cumulative === false)
            $cumulative= null;

        return is_null($value) && is_null($cumulative) ? null : array($value, $cumulative);
    }
}



function cSIN($x){
    $c= sin($x);
    return $c;
}
function cCOS($x) {
    $c= cos($x);
    return $c;
}
function cTAN($x){
    $c= tan($x);
    return $c;
}
function cASIN($x){
    $c= asin($x);
    return $c;
}
function cACOS($x){
    $c= acos($x);
    return $c;
}
function cATAN($x){
    $c= atan($x);
    return $c;
}
// agregar "c" a loa funcion
function cSQRT($x,$y){
    $c= pow($x,1/$y);
    return $c;
}
function SQRT2($x){
    $c= sqrt($x);
    return $c;
}
function POT2($x){
    $c= pow($x,2);
    return $c;
}
function POT($x,$y){
    $c= pow($x,$y);
    return $c;
}
// agregar "c" a loa funcion
function cEXP($x){
    $c= exp($x);
    return $c;
}
// agregar "c" a loa funcion
function cLOG($x){
    $c=  log($x) / log(10);
    return $c;
}
function LN($x){
    $c= log($x);
    return $c;
}
function ALOG($x){
    $c= pow(10,$x);
    return $c;
}
function ALN($x){
    $c= pow(E,$x);
    return $c;
}
function FACTORIAL($x){
    if ($x == 0) return 1;
    $c= FACTORIAL($x-1);
    return $c;
}
function INV($x) {
    $c= 1/$x;
    return $c;
}
function SIGNO($x) {
    $c= (-1)*$x;
    return $c;
}
function PERCENT($x,$y) {
    $c= ($x*$y)/100;
    return $c;
}
function cMAX($x1,$x2,$x3=null,$x4=null,$x5=null,$x6=null,$x7=null,$x8=null,$x9=null,$x10=null,
            $x11=null,$x12=null,$x13=null,$x14=null,$x15=null,$x16=null,$x17=null,$x18=null,$x19=null,$x20=null,
            $x21=null,$x22=null,$x23=null,$x24=null,$x25=null,$x26=null,$x27=null,$x28=null,$x29=null,$x30=null,
            $x31=null,$x32=null,$x33=null,$x34=null,$x35=null,$x36=null,$x37=null,$x38=null,$x39=null,$x40=null,
            $x41=null,$x42=null,$x43=null,$x44=null,$x45=null,$x46=null,$x47=null,$x48=null,$x49=null,$x50=null,
            $x51=null,$x52=null,$x53=null,$x54=null,$x55=null,$x56=null,$x57=null,$x58=null,$x59=null,$x60=null) {
    $c= max($x1,$x2,$x3,$x4,$x5,$x6,$x7,$x8,$x9,$x10,
            $x11,$x12,$x13,$x14,$x15,$x16,$x17,$x18,$x19,$x20,
            $x21,$x22,$x23,$x24,$x25,$x26,$x27,$x28,$x29,$x30,
            $x31,$x32,$x33,$x34,$x35,$x36,$x37,$x38,$x39,$x40,
            $x41,$x42,$x43,$x44,$x45,$x46,$x47,$x48,$x49,$x50,
            $x51,$x52,$x53,$x54,$x55,$x56,$x57,$x58,$x59,$x60);
    return $c;
}
function cMIN($x1,$x2,$x3=null,$x4=null,$x5=null,$x6=null,$x7=null,$x8=null,$x9=null,$x10=null,
            $x11=null,$x12=null,$x13=null,$x14=null,$x15=null,$x16=null,$x17=null,$x18=null,$x19=null,$x20=null,
            $x21=null,$x22=null,$x23=null,$x24=null,$x25=null,$x26=null,$x27=null,$x28=null,$x29=null,$x30=null,
            $x31=null,$x32=null,$x33=null,$x34=null,$x35=null,$x36=null,$x37=null,$x38=null,$x39=null,$x40=null,
            $x41=null,$x42=null,$x43=null,$x44=null,$x45=null,$x46=null,$x47=null,$x48=null,$x49=null,$x50=null,
            $x51=null,$x52=null,$x53=null,$x54=null,$x55=null,$x56=null,$x57=null,$x58=null,$x59=null,$x60=null) {
    $c= min(setNAN($x1),setNAN($x2),setNAN($x3),setNAN($x4),setNAN($x5),setNAN($x6),setNAN($x7),setNAN($x8),setNAN($x9),setNAN($x10),
            setNAN($x11),setNAN($x12),setNAN($x13),setNAN($x14),setNAN($x15),setNAN($x16),setNAN($x17),setNAN($x18),setNAN($x19),setNAN($x20),
            setNAN($x21),setNAN($x22),setNAN($x23),setNAN($x24),setNAN($x25),setNAN($x26),setNAN($x27),setNAN($x28),setNAN($x29),setNAN($x30),
            setNAN($x31),setNAN($x32),setNAN($x33),setNAN($x34),setNAN($x35),setNAN($x36),setNAN($x37),setNAN($x38),setNAN($x39),setNAN($x40),
            setNAN($x41),setNAN($x42),setNAN($x43),setNAN($x44),setNAN($x45),setNAN($x46),setNAN($x47),setNAN($x48),setNAN($x49),setNAN($x50),
            setNAN($x51),setNAN($x52),setNAN($x53),setNAN($x54),setNAN($x55),setNAN($x56),setNAN($x57),setNAN($x58),setNAN($x59),setNAN($x60));
    return $c;
}
?>