<?php
/**
 * Created by Visual Studio Code.
 * User: muste
 * Date: 20/01/15
 * Time: 13:58
 */

include_once "unidad.class.php";

define("_YEAR_FIELD_NO", 0);
define("_YEAR_FIELD_YEAR", 1);
define("_YEAR_FIELD_INTERVAL", 2);

class Treference extends Tunidad {
    private $upward;
    public  $flag_field_prs;

    public function __construct($clink= null) {
        Tunidad::__construct($clink);
        $this->clink= $clink;

        $this->upward= '>=';
    }

    public function empty_tref_indicadores($id_inductor= null, $id_indicador= null, $year= null, $upward= null) {
        if (!is_null($upward))
            $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_inductor= !empty($id_inductor) ? $id_inductor : $this->id_inductor;
        $this->id_indicador= !empty($id_indicador) ? $id_indicador : $this->id_indicador;

        $sql= "delete from tref_indicadores where 1 ";
        if (!empty($this->id_indicador))
            $sql.= "and id_indicador = $this->id_indicador ";
        if (!empty($this->id_inductor))
            $sql.= "and id_inductor = $this->id_inductor ";
        if (!empty($this->year))
            $sql.= "and year $this->upward $year ";
        $this->do_sql_show_error('empty_tref_indicadores', $sql);
        return $this->error;
    }

    public function empty_tindicador_criterio($id_indicador= null, $id_perspectiva= null, $year= null, $upward= null) {
        if (!is_null($upward)) 
            $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_perspectiva= !empty($id_perspectiva) ? $id_perspectiva : $this->id_perspectiva;
        $this->id_indicador= !empty($id_indicador) ? $id_indicador : $this->id_indicador;

        $sql= "delete from tindicador_criterio where 1 ";
        if (!empty($this->id_indicador))
            $sql.= "and id_indicador = $this->id_indicador ";
        if (!empty($this->id_perspectiva))
            $sql.= "and id_perspectiva = $this->id_perspectiva ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($this->year))
            $sql.= "and year $this->upward $this->year ";
        $this->do_sql_show_error('empty_tindicador_criterio', $sql);
        return $this->error;
    }

    public function empty_tref_programas($id_programa= null, $id_indicador= null, $year= null, $upward= null) {
        if (!is_null($upward)) 
            $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_programa= !empty($id_programa) ? $id_programa : $this->id_programa;
        $this->id_indicador= !empty($id_indicador) ? $id_indicador : $this->id_indicador;

        $sql= "delete from tref_programas where 1 ";
        if (!empty($this->id_indicador))
            $sql.= "and id_indicador = $this->id_indicador ";
        if (!empty($this->id_programa))
            $sql.= "and id_programa = $this->id_programa ";
        if (!empty($this->year))
            $sql.= "and year $this->upward $year ";
        $this->do_sql_show_error('empty_tref_programas', $sql);

        $cant= $this->clink->affected_rows();
        if ($cant > 0 && !empty($this->id_indicador)) 
            $this->set_calcular_year_programa($this->id_programa, $year, $this->upward);

        if (!empty($this->id_programa) && empty($this->id_indicador)) {
            $sql= "delete from tproceso_proyectos where id_programa = $this->id_programa ";
            if (!empty($this->year))
                $sql.= "and year $this->upward $year ";
            $this->do_sql_show_error('empty_tref_programas', $sql);
        }

        return $this->error;
    }

    public function empty_set_null_perspectiva($id_perspectiva= null, $id_indicador= null, $year= null, $upward= null) {
        if (!is_null($upward)) 
            $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_perspectiva= !empty($id_perspectiva) ? $id_perspectiva : $this->id_perspectiva;
        $this->id_indicador= !empty($id_indicador) ? $id_indicador : $this->id_indicador;

        $sql= "update tindicador_criterio set id_perspectiva= NULL, id_perspectiva_code= NULL where 1 ";
        if (!empty($this->id_indicador))
            $sql.= "and id_indicador = $this->id_indicador ";
        if (!empty($this->id_perspectiva))
            $sql.= "and id_perspectiva = $this->id_perspectiva ";
        if (!empty($this->id_proceso))
            $sql.= "and id_proceso = $this->id_proceso ";
        if (!empty($this->year))
            $sql.= "and year $this->upward $year ";
        $this->do_sql_show_error('empty_set_null_perspectiva', $sql);

        $cant= $this->clink->affected_rows();
        if ($cant > 0 && !empty($this->id_indicador)) 
            $this->set_calcular_year_perspectiva($this->id_perspectiva, $year, $this->upward);

        return $this->error;
    }

    public function empty_tobjetivo_inductores($id_objetivo= null, $id_inductor= null, $year= null, $upward= null) {
        if (!is_null($upward)) $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_inductor= !empty($id_inductor) ? $id_inductor : $this->id_inductor;
        $this->id_objetivo= !empty($id_objetivo) ? $id_objetivo : $this->id_objetivo;

        $sql= "delete from tobjetivo_inductores where 1 ";
        if (!empty($this->id_objetivo))
            $sql.= "and id_objetivo = $this->id_objetivo ";
        if (!empty($this->id_inductor))
            $sql.= "and id_inductor = $this->id_inductor ";
        if (!empty($this->year))
            $sql.= "and year $this->upward $this->year ";
        $this->do_sql_show_error('empty_tobjetivo_inductores', $sql);
        $cant= $this->clink->affected_rows();

        if ($cant > 0 && !empty($this->id_objetivo)) 
            $this->set_calcular_year_objetivo($this->id_objetivo, $year, $this->upward);

        return $this->error;
    }

    public function empty_tinductor_eventos($id_inductor= null, $id_evento= null, $year= null, $upward= null) {
        if (!is_null($upward)) 
            $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_inductor= !empty($id_inductor) ? $id_inductor : $this->id_inductor;
        $this->id_evento= !empty($id_evento) ? $id_evento : $this->id_evento;

        $sql= "delete from tinductor_eventos using ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "tinductor_eventos, " : "";
        $sql.= "teventos where tinductor_eventos.id_evento = teventos.id ";
        if (!empty($this->id_evento))
            $sql.= "and id_evento = $this->id_objetivo ";
        if (!empty($this->id_inductor))
            $sql.= "and id_inductor = $this->id_inductor ";
        if (!empty($this->year))
            $sql.= "and (YEAR(fecha_inicio_plan) $this->upward $this->year and YEAR(fecha_fin_plan) $this->upward $this->year) ";
        $this->do_sql_show_error('empty_tinductor_eventos', $sql);
        return $this->error;
    }

    public function empty_tinductor_riesgos($id_inductor= null, $id_riesgo= null, $year= null, $upward= null) {
        if (!is_null($upward)) 
            $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_inductor= !empty($id_inductor) ? $id_inductor : $this->id_inductor;
        $this->id_riesgo= !empty($id_riesgo) ? $id_riesgo : $this->id_riesgo;

        $sql= "delete from tinductor_riesgos using ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "tinductor_riesgos, " : "";
        $sql.= "triesgos where tinductor_riesgos.id_riesgo = triesgos.id ";
        if (!empty($this->id_riesgo))
            $sql.= "and id_riesgo = $this->id_riesgo ";
        if (!empty($this->id_inductor))
            $sql.= "and id_inductor = $this->id_inductor ";
        if (!empty($this->year))
            $sql.= "and (YEAR(fecha_inicio_plan) $this->upward $this->year and YEAR(fecha_fin_plan) $this->upward $this->year) ";
        $this->do_sql_show_error('empty_tinductor_riesgos', $sql);
        return $this->error;
    }

    public function empty_tpolitica_objetivos($id_politica= null, $id_objetivo= null, $year= null, $upward= null) {
        if (!is_null($upward)) 
            $this->upward= $upward ? '>=' : '<=';

        $this->year= !empty($year) ? $year : $this->year;
        $this->id_politica= !empty($id_politica) ? $id_politica : $this->id_politica;
        $this->id_objetivo= !empty($id_objetivo) ? $id_objetivo : $this->id_objetivo;

        $sql= "delete from tpolitica_objetivos where 1 ";
        if (!empty($this->id_objetivo) && !$this->if_objsup)
            $sql.= "and id_objetivo = $this->id_objetivo ";
        if (!empty($this->id_objetivo) && $this->if_objsup)
            $sql.= "and id_objetivo_sup = $this->id_objetivo ";
        if (!empty($this->id_politica))
            $sql.= "and id_politica = $this->id_politica ";
        if (!empty($this->year))
            $sql.= "and year $this->upward $this->year ";
        $this->do_sql_show_error('empty_tpolitica_objetivos', $sql);

        $cant= $this->clink->affected_rows();
        if ($cant > 0 && !empty($this->id_objetivo))
            $this->set_calcular_year_objetivo($this->id_politica, $year, $this->upward);

        return $this->error;
    }

    //<---- FIJAR EL CALCULO EN LASS TABAS DE REGISTRO ---------------------------------------------------------------->
    protected  function read_peso($table, $plus, $fix_date= _YEAR_FIELD_NO, $chain_cascade_prs= null) {
        $this->cant= 0;

        $plus= is_null($plus) ? "1" : $plus;

        $sql= "select distinct * from $table where $plus ";
        if ($this->flag_field_prs && empty($chain_cascade_prs)) {
            if (!empty($this->id_proceso))
                $sql.= "and id_proceso = $this->id_proceso ";
            else if (!empty($this->flag_id_proceso))
                $sql.= "and id_proceso = $this->flag_id_proceso ";
        }
        if ($this->flag_field_prs && is_string($chain_cascade_prs)) {
            if (!empty($this->id_proceso))
                $sql.= "and id_proceso in (".$chain_cascade_prs.") ";
        }
        $sql.= "and peso > 0 ";
        if ($fix_date == _YEAR_FIELD_YEAR)
            $sql.= "and year = $this->year ";
        if ($fix_date == _YEAR_FIELD_INTERVAL)
            $sql.= "and (inicio <= $this->year and fin >= $this->year) ";

        $result= $this->do_sql_show_error('read_peso', $sql);
        $this->flag_field_prs= false;
        return $result;
    }

    public function set_to_calcular_year($table, $year, $plus= null, $upward= '=') {
        $sql= "update $table set calcular = true where year $upward $year ";
        if (!is_null($plus))
            $sql.= "and $plus ";
        $this->do_sql_show_error('set_to_calcular_year', $sql);
    }

    public function set_calcular_year_politica($id, $year, $upward= '=') {
        $this->set_to_calcular_year('treg_politica', $year, "id_politica = $id", $upward);
    }

    public function set_calcular_year_objetivo($id, $year, $upward= '=') {
        $result= $this->read_peso("tpolitica_objetivos", "id_objetivo = $id");

        if ($this->cant > 0) {
            while ($row= $this->clink->fetch_array($result)) {
                if (!empty($row['id_politica']))
                    $this->set_calcular_year_politica($row['id_politica'], $year, $upward);
                if (!empty($row['id_objetivo_sup']))
                    $this->set_calcular_year_objetivo($row['id_objetivo_sup'], $year, $upward);
            }
        }

        $this->set_to_calcular_year('treg_objetivo', $year, "id_objetivo = $id", $upward);
    }

    public function set_calcular_year_programa($id, $year, $upward= '=') {
        $this->set_to_calcular_year('treg_programa', $year, "id_programa = $id", $upward);
    }

    public function set_calcular_year_inductor($id, $year, $upward= '=') {
        $plus= "id_inductor = $id and month = $this->month";
        $this->set_to_calcular_year('treg_inductor', $year, $plus, $upward);
        
        $result= $this->read_peso("tobjetivo_inductores", "id_inductor = $id");
        if ($this->cant > 0) {
            while ($row= $this->clink->fetch_array($result)) {
                $this->set_calcular_year_objetivo($row['id_objetivo'], $year, $upward);
            }
        }

        $this->set_to_calcular_year('treg_inductor', $year, "id_inductor = $id", $upward);
    }

    public function set_calcular_year_perspectiva($id, $year, $upward= '=') {
        $plus= "id_perspectiva = $id and month = $this->month and id_proceso = $this->id_proceso";
        $this->set_to_calcular_year('treg_perspectiva', $year, $plus, $upward);
    }

    public function set_calcular_year_proceso($id, $year, $upward= '=') {
        $this->set_to_calcular_year('treg_proceso', $year, "id_proceso= $id", $upward);
    }

    public function set_calcular_year_indicador($id, $year, $upward= '=') {
        $this->flag_id_proceso= null;
        $this->flag_id_proceso_code= null;
        $fix_year= true;

        //programa
        $result= $this->read_peso("tref_programas", "id_indicador = $id", $fix_year);
        while ($row= $this->clink->fetch_array($result)) {
            $this->set_calcular_year_programa($row['id_programa'], $year, $upward);
        }
        //inductores
        $result= $this->read_peso("tref_indicadores", "id_indicador = $id");
        while ($row= $this->clink->fetch_array($result)) {
            $this->set_calcular_year_inductor($row['id_inductor'], $year, $upward);
        }
        //perspectiva
        $result= $this->read_peso("tindicador_criterio", "id_indicador = $id");
        while ($row= $this->clink->fetch_array($result)) {
            $this->set_calcular_year_perspectiva($row['id_perspectiva'], $year, $upward);
        }
        //proceso
        $result= $this->read_peso("tproceso_indicadores", "id_indicador = $id");
        while ($row= $this->clink->fetch_array($result)) {
            $this->set_calcular_year_proceso($row['id_proceso'], $year, $upward);
        }
    }
}