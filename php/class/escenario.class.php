<?php
/**
 * @author Geraudis Mustelier
 * @copyright 2012
 */

include_once "../config.inc.php";

include_once "unidad.class.php";

class Tescenario extends Tunidad {
    protected $mapa, $param;
    protected $mapa_strat, $param_strat;
    protected $mapa_org, $param_org, $observacion_org;
    protected $mapa_proc, $param_proc, $observacion_proc;

    protected $mision, $vision;

    public function SetMision($id) {
        $this->mision = $id;
    }
    public function SetVision($id) {
        $this->vision = $id;
    }
    public function GetMision() {
        return $this->mision;
    }
    public function GetVision() {
        return $this->vision;
    }

    public function __construct($clink= null) {
        Tunidad::__construct($clink);
        $this->clink= $clink;
    	$this->mapa= NULL;
    }

    public function set_observacion($case, $observacion) {
        switch ($case) {
            case 'org':
                $this->observacion_org = $observacion;
                break;
            case 'proc':
                $this->observacion_proc = $observacion;
                break;
        }
    }

    public function get_observacion($case) {
        switch($case) {
            case 'org':
                return $this->observacion_org;
            case 'proc':
                return $this->observacion_proc;
        }
    }

    public function SetMapa($case, $id, $param) {
         switch ($case) {
            case 'strat':
                $this->mapa_strat = $id;
                $this->param_strat = $param;
                break;
            case 'org':
                $this->mapa_org = $id;
                $this->param_org = $param;
                break;
            case 'proc':
                $this->mapa_proc = $id;
                $this->param_proc = $param;
                break;
        }
    }

    public function GetImage($case) {
        switch ($case) {
            case 'strat':
                return $this->mapa_strat;
            case 'org':
                return $this->mapa_org;
            case 'proc':
                return $this->mapa_proc;
        }
    }

    public function GetParam($case) {
        switch ($case) {
            case 'strat':
                $param = $this->param_strat;
                break;
            case 'org':
                $param = $this->param_org;
                break;
            case 'proc':
                $param = $this->param_proc;
                break;
        }

        if (empty($param))
            return null;

        list($name,$type,$size,$dim)= preg_split("[:]",$param);
        $xparam['name']= $name;
        $xparam['type']= $type;
        $xparam['size']= $size;
        $xparam['dim']= $dim;

        return $xparam;
    }

    public function GetDim($case, $param= null, $ratio=1) {
        if (empty($param)) {
            switch($case) {
                case 'strat':
                    $param = $this->param_strat;
                    break;
                case 'org':
                    $param = $this->param_org;
                    break;
                case 'proc':
                    $param = $this->param_proc;
                    break;
            }
        }

        if (empty($param))
            return null;

        list($name,$type,$size,$dim)= preg_split("[:]",$param);
        list($x1,$x2,$x3,$x4)= preg_split('[\"]',$dim);
        $dim= 'width='.$x2*$ratio.'px  height='.$x4*$ratio.'px';

        return $dim;
     }

    public function Set($id = null) {
        if (!empty($id)) $this->id_escenario = $id;

        $sql = "select * from tescenarios where id = $this->id_escenario ";
        $result = $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row = $this->clink->fetch_array($result);

            $this->id= $row['id'];
            $this->id_escenario= $this->id;
            $this->id_code = $row['id_code'];
            $this->id_escenario_code = $this->id_code;

            $this->id_proceso = $row['id_proceso'];
            $this->id_proceso_code = $row['id_proceso_code'];

            $this->mapa_strat = $row['mapa'];
            $this->param_strat = $row['mapa_param'];

            $this->mapa_org = $row['org_mapa'];
            $this->param_org = $row['org_param'];
            $this->observacion_org = stripslashes($row['org_observaciones']);

            $this->mapa_proc = $row['proc_mapa'];
            $this->param_proc = $row['proc_param'];
            $this->observacion_proc = stripslashes($row['proc_observaciones']);

            $this->inicio = $row['inicio'];
            $this->fin = $row['fin'];
            $this->mision = stripslashes($row['mision']);
            $this->vision = stripslashes($row['vision']);

            $this->descripcion = stripslashes($row['descripcion']);
        }

        return $this->error;
    }

    public function add() {
        $observacion_org = setNULL_str($this->observacion_org);
        $observacion_proc = setNULL_str($this->observacion_proc);
        $descripcion = setNULL_str($this->descripcion);
        $mision = setNULL_str($this->mision);
        $vision = setNULL_str($this->vision);

        $sql = "insert into tescenarios (id_proceso, id_proceso_code, descripcion, inicio, fin, mapa, mapa_param, org_mapa, ";
        $sql .= "org_param, proc_mapa, proc_param, mision, vision, cronos, situs, org_observaciones, proc_observaciones) ";
        $sql .= "values ($this->id_proceso, '$this->id_proceso_code', $descripcion, $this->inicio, $this->fin, ";

        if ($this->mapa_strat)
            $param_strat = "'{$this->param_strat['name']}:{$this->param_strat['type']}:{$this->param_strat['size']}:{$this->param_strat['dim']}'";
        else
            $param_strat= setNULL_str($this->param_strat);
        $mapa_strat= $this->mapa_strat ? decodeBlob2pg($this->mapa_strat) : 'NULL';

        if ($this->mapa_org)
            $param_org = "'{$this->param_org['name']}:{$this->param_org['type']}:{$this->param_org['size']}:{$this->param_org['dim']}'";
        else
            $param_org= setNULL_str($this->param_org);
        $mapa_org= $this->mapa_org ? decodeBlob2pg($this->mapa_org) : 'NULL';

        if ($this->mapa_proc)
            $param_proc = "'{$this->param_proc['name']}:{$this->param_proc['type']}:{$this->param_proc['size']}:{$this->param_proc['dim']}'";
        else
            $param_proc= setNULL_str($this->param_proc);
        $mapa_proc= $this->mapa_proc ? decodeBlob2pg($this->mapa_proc) : 'NULL';

        $sql.= "$mapa_strat, $param_strat, $mapa_org, $param_org, $mapa_proc, $param_proc, ";
        $sql.= "$mision, $vision, '$this->cronos', '$this->location', $observacion_org, $observacion_proc) ";

        $result = $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id = $this->clink->inserted_id("tescenarios");
            $this->id_escenario = $this->id;
        }

        return $this->error;
    }

    public function update() {
        $observacion_org = setNULL_str($this->observacion_org);
        $observacion_proc = setNULL_str($this->observacion_proc);
        $descripcion = setNULL_str($this->descripcion);
        $mision = setNULL_str($this->mision);
        $vision = setNULL_str($this->vision);

        $sql = "update tescenarios set inicio= $this->inicio, fin= $this->fin, descripcion= $descripcion, ";
        $sql .= "mision= $mision, vision= $vision, proc_observaciones= $observacion_proc, ";
        $sql .= "org_observaciones= $observacion_org, cronos= '$this->cronos', situs= '$this->location' ";

        if (!is_null($this->mapa_strat)) {
            if ($this->param_strat)
                $param_strat = "'{$this->param_strat['name']}:{$this->param_strat['type']}:{$this->param_strat['size']}:{$this->param_strat['dim']}'";
            else
                $param_strat= setNULL_empty($this->param_strat);

            $mapa_strat= !empty($this->mapa_strat) ? decodeBlob2pg($this->mapa_strat) : 'NULL';

            $sql .= ", mapa= $mapa_strat, mapa_param= $param_strat ";
        }

        if (!is_null($this->mapa_proc)) {
            if ($this->param_proc)
                $param_proc = "'{$this->param_proc['name']}:{$this->param_proc['type']}:{$this->param_proc['size']}:{$this->param_proc['dim']}'";
            else
                $param_proc= setNULL_empty($this->param_proc);

            $mapa_proc= !empty($this->mapa_proc) ? decodeBlob2pg($this->mapa_proc) : 'NULL';

            $sql .= ", proc_mapa= $mapa_proc, proc_param= $param_proc ";
        }

        if (!is_null($this->mapa_org)) {
            if ($this->param_org)
                $param_org = "'{$this->param_org['name']}:{$this->param_org['type']}:{$this->param_org['size']}:{$this->param_org['dim']}'";
            else
                $param_org= setNULL_empty($this->param_org);

            $mapa_org= !empty($this->mapa_org) ? decodeBlob2pg($this->mapa_org) : 'NULL';

            $sql .= ", org_mapa= $mapa_org, org_param= $param_org ";
        }

        $sql .= "where id = $this->id_escenario ";

        $result = $this->do_sql_show_error('update', $sql);
        return $this->error;
    }

    public function listar() {
        $sql = "select tescenarios.*, tescenarios.id as _id, tescenarios.inicio as _inicio, tescenarios.fin as _fin, nombre as proceso, ";
        $sql.= "tipo from tescenarios, tprocesos where tescenarios.id_proceso = tprocesos.id ";
        $sql.= "and tescenarios.id_proceso = $this->id_proceso ";
        if (!empty($this->year))
            $sql .= "and (tescenarios.inicio <= $this->year and tescenarios.fin >= $this->year) ";
        $sql .= "order by tescenarios.inicio";

        $result = $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function eliminar() {
        $sql = "delete from tescenarios where id = $this->id_escenario";
        $result = $this->do_sql_show_error('eliminar', $sql);
        return $this->error;
    }

    public function SetEscenario($year, &$id_proceso, $search = _LOCAL) {
        $id_proceso = !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $local = _NO_LOCAL;
        $local_id = $_SESSION['local_proceso_id'];

        $sql = "select tescenarios.*, tescenarios.id as _id_escenario, tescenarios.id_code as _id_escenario_code, ";
        $sql .= "t1.id as _id, t1.id_code as _id_code from tescenarios, tprocesos as t1 ";
        $sql .= "where (tescenarios.id_proceso = t1.id and t1.id = $id_proceso) ";
        if ($search == _NO_LOCAL) {
            $sql .= "and ((t1.conectado = $local or (t1.conectado != $local and t1.id = $local_id)) ";
            $sql .= "or (t1.conectado != $local and t1.id != $local_id)) ";
        }
        $sql .= "and ($year >= tescenarios.inicio and $year <= tescenarios.fin) ";
        $sql .= "union ";
        $sql .= "select tescenarios.*, tescenarios.id as _id_escenario, tescenarios.id_code as _id_escenario_code, ";
        $sql .= "t1.id as _id, t1.id_code as _id_code from tescenarios, tprocesos as t1, tprocesos as t2 ";
        $sql .= "where ((tescenarios.id_proceso = t1.id and t1.id = t2.id_proceso) and t2.id = $id_proceso) ";
        if ($search == _NO_LOCAL) {
            $sql .= "and ((t1.conectado = $local or (t1.conectado != $local and t1.id = $local_id)) ";
            $sql .= "or (t1.conectado != $local and t1.id != $local_id)) ";
        }
        $sql .= "and ($year >= tescenarios.inicio and $year <= tescenarios.fin) ";

        $result = $this->do_sql_show_error('SetEscenario', $sql);

        $this->id_escenario = null;
        $this->id_escenario_code = null;
        $id_prs_sup = null;
        $i = 0;
        $array = null;

        while ($row = $this->clink->fetch_array($result)) {
            if (!empty($row['_id_escenario'])) {
                $this->id_escenario = $row['_id_escenario'];
                $this->id_escenario_code = $row['_id_escenario_code'];
                $this->id_code = $this->id_escenario_code;

                $array = array('id' => $this->id_escenario, 'id_code' => $this->id_escenario_code, 'inicio' => $row['inicio'], 'fin' => $row['fin']);
                return $array;
            }

            ++$i;
            $id_prs_sup = $row['_id'];
            if (is_null($id_prs_sup))
                return null;
        }

        if (!is_null($id_prs_sup))
            $array = $this->SetEscenario($year, $id_prs_sup, $search);

        return $array;
    }

    public function get_init_fin_year() {
        $sql= "select min(inicio) as _inicio, max(fin) as _fin from tescenarios ";
        $sql.= "where id_proceso = {$_SESSION['local_proceso_id']}";
        $result = $this->do_sql_show_error('get_init_fin_year', $sql);
        $row= $this->clink->fetch_array($result);

        $this->inicio= $row['_inicio'];
        $this->fin= $row['_fin'];
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "code.class.php";
if (!class_exists('Tproceso'))
    include_once "proceso.class.php";
