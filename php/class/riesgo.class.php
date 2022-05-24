<?php
/**
 * @author Geraudis Mustelier Portuondo
 * @copyright 2012
 */

if (!class_exists('Tregister_nota'))
    include_once "register_nota.class.php";

class Triesgo extends Tregister_nota {
    private $nivel;
    private $frecuencia;
    private $frecuencia_memo;
    private $impacto;
    private $impacto_memo;
    private $deteccion;
    private $deteccion_memo;

    private $ifestrategico;
    private $sst, $ma, $ext, $econ, $reg, $info, $calidad;

    private $if_triesgos;

    public function SetIfEstrategico($id = NULL) {
        $this->ifestrategico = (empty($id)) ? 0 : 1;
    }
    public function GetIfEstrategico() {
        return $this->ifestrategico;
    }
    public function SetIfExterno($id = null) {
        $this->ext = (empty($id)) ? 0 : $id;
    }
    public function GetIfEXterno() {
        return $this->ext;
    }
    public function SetIfMedioambiental($id = true) {
        $this->ma = (empty($id)) ? 0 : 1;
    }
    public function GetIfMedioAmbiental() {
        return $this->ma;
    }
    public function SetIfSST($id = true) {
        $this->sst = (empty($id)) ? 0 : 1;
    }
    public function GetIfSST() {
        return $this->sst;
    }
    public function SetIfRegulatorio($id = true) {
        $this->reg = (empty($id)) ? 0 : 1;
    }
    public function GetIfRegulatorio() {
        return $this->reg;
    }
    public function SetIfInformatico($id = true) {
        $this->info = (empty($id)) ? 0 : 1;
    }
    public function GetIfInformatico() {
        return $this->info;
    }
    public function SetIfCalidad($id = true) {
        $this->calidad = (empty($id)) ? 0 : 1;
    }
    public function GetIfCalidad() {
        return $this->calidad;
    }
    public function SetIfEconomico($id = NULL) {
        $this->econ = (empty($id)) ? 0 : 1;
    }
    public function GetIfEconomico() {
        return $this->econ;
    }
    public function setFrecuencia($id) {
        $this->frecuencia = $id;
    }
    public function setImpacto($id) {
        $this->impacto = $id;
    }
    public function setDeteccion($id) {
        $this->deteccion = $id;
    }
    public function setFrecuencia_memo($id) {
        $this->frecuencia_memo = $id;
    }
    public function setImpacto_memo($id) {
        $this->impacto_memo = $id;
    }
    public function setDeteccion_memo($id) {
        $this->deteccion_memo = $id;
    }
    public function getFrecuencia() {
        return $this->frecuencia;
    }
    public function getImpacto() {
        return $this->impacto;
    }
    public function getDeteccion() {
        return $this->deteccion;
    }
    public function getFrecuencia_memo() {
        return $this->frecuencia_memo;
    }
    public function getImpacto_memo() {
        return $this->impacto_memo;
    }
    public function getDeteccion_memo() {
        return $this->deteccion_memo;
    }
    public function SetFecha($id) {
        $this->fecha = $id;
    }
    public function GetFecha() {
        return $this->fecha;
    }

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tregister_nota::__construct($clink);

        $this->ext= NULL;
        $this->sst= NULL;
        $this->ma= NULL;
        $this->econ= NULL;
        $this->reg= NULL;
        $this->info= null;

        $this->if_triesgos= false;

        $this->className= 'Triesgo';
    }

    public function Set($id_riesgo= null) {
        $id_riesgo= empty($id_riesgo) ? $this->id_riesgo : $id_riesgo;

        $sql= "select triesgos.*, treg_riesgo.frecuencia, treg_riesgo.impacto, treg_riesgo.deteccion, treg_riesgo.estado, ";
        $sql.= "treg_riesgo.cronos as _cronos from triesgos, treg_riesgo where triesgos.id = treg_riesgo.id_riesgo ";
        $sql.= "and triesgos.id = $id_riesgo order by treg_riesgo.cronos asc ";

        $result= $this->do_sql_show_error('Set', $sql);

        if ($result) {
            $row= $this->clink->fetch_array($result);

            $this->id= $this->id_riesgo;
            $this->id_code= $row['id_code'];
            $this->id_riesgo_code= $this->id_code;

            $this->nombre= stripslashes($row['nombre']);
            $this->descripcion= stripslashes($row['descripcion']);
            $this->observacion= stripslashes($row['observacion']);

            $this->id_proceso= $row['id_proceso'];
            $this->id_proceso_code= $row['id_proceso_code'];

            $this->estado= $row['estado'];
            $this->frecuencia= $row['frecuencia'];
            $this->frecuencia_memo= $row['frecuencia_memo'];
            $this->impacto= $row['impacto'];

            $this->impacto_memo= $row['impacto_memo'];
            $this->deteccion= $row['deteccion'];
            $this->deteccion_memo= $row['deteccion_memo'];

            $this->fecha_inicio_plan= $row['fecha_inicio_plan'];
            $this->fecha_fin_plan= $row['fecha_fin_plan'];

            $this->ifestrategico= boolean($row['estrategico']);
            $this->lugar= $row['lugar'];

            $this->ext= $row['ext'];
            $this->ma= boolean($row['ma']);
            $this->sst= boolean($row['sst']);
            $this->econ= boolean($row['econ']);
            $this->reg= boolean($row['reg']);
            $this->info= boolean($row['info']);
            $this->calidad= boolean($row['calidad']);

            $this->id_responsable= $row['id_usuario'];

            $this->value= $row['valor'];
            $this->origen_data= $row['origen_data'];
            $this->kronos= $row['_cronos'];

            $this->copyto= $row['copyto'];
        }

        return $this->error;
    }

    public function add() {
        $frecuencia_memo= setNULL_str($this->frecuencia_memo);
        $impacto_memo= setNULL_str($this->impacto_memo);
        $deteccion_memo= setNULL_str($this->deteccion_memo);
        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);

        $ma= boolean2pg($this->ma);
        $sst= boolean2pg($this->sst);
        $ext= setNULL($this->ext);
        $econ= boolean2pg($this->econ);
        $reg= boolean2pg($this->reg);
        $info= boolean2pg($this->info);
        $calidad= boolean2pg($this->calidad);
        $ifestrategico= boolean2pg($this->ifestrategico);

        $value= setNULL($this->value);

        $sql= "insert into triesgos (nombre, descripcion, id_usuario, frecuencia_memo, impacto_memo, deteccion_memo, lugar,";
        $sql.= "estrategico, fecha_inicio_plan, fecha_fin_plan, id_proceso, id_proceso_code, ";
        $sql.= "ma, sst, ext, econ, reg, info, calidad, valor, cronos, situs) values ($nombre, $descripcion, ";
        $sql.= "$this->id_responsable, $frecuencia_memo, $impacto_memo, $deteccion_memo, '$this->lugar', $ifestrategico, ";
        $sql.= "'$this->fecha_inicio_plan', '$this->fecha_fin_plan', $this->id_proceso, '$this->id_proceso_code', ";
        $sql.= "$ma, $sst, $ext, $econ, $reg, $info, $calidad, $value, '$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('add', $sql);

        if ($result) {
            $this->id_riesgo= $this->clink->inserted_id("triesgos");
            $this->id= $this->id_riesgo;

            $this->obj_code->SetId($this->id);
            $this->obj_code->set_code('triesgos','id','id_code');

            $this->id_code= $this->obj_code->get_id_code();
            $this->id_riesgo_code= $this->id_code;

            $this->observacion= $this->descripcion;
        }

    	return $this->error;
    }

    public function update($id= null) {
        $id= !empty($id) ? $id : $this->id_riesgo;

        $frecuencia_memo= setNULL_str($this->frecuencia_memo);
        $impacto_memo= setNULL_str($this->impacto_memo);
        $deteccion_memo= setNULL_str($this->deteccion_memo);

        $descripcion= setNULL_str($this->descripcion);
        $nombre= setNULL_str($this->nombre);

        $ma= boolean2pg($this->ma);
        $sst= boolean2pg($this->sst);
        $ext= setNULL($this->ext);
        $econ= boolean2pg($this->econ);
        $reg= boolean2pg($this->reg);
        $info= boolean2pg($this->info);
        $calidad= boolean2pg($this->calidad);

        $ifestrategico= boolean2pg($this->ifestrategico);

        $value= setNULL($this->value);

        $sql= "update triesgos set nombre= $nombre, descripcion= $descripcion, fecha_fin_plan= '$this->fecha_fin_plan', ";
        $sql.= "frecuencia_memo= $frecuencia_memo, impacto_memo= $impacto_memo, fecha_inicio_plan= '$this->fecha_inicio_plan', ";
        $sql.= "id_proceso_code= '$this->id_proceso_code', deteccion_memo= $deteccion_memo, lugar= '$this->lugar', ";
        $sql.= "id_proceso= $this->id_proceso, estrategico= $ifestrategico, cronos= '$this->cronos', id_usuario= $this->id_responsable, ";
        $sql.= "situs= '$this->location', ma= $ma, sst= $sst, ext= $ext, econ= $econ, reg= $reg, info= $info, ";
        $sql.= "calidad= $calidad, valor= $value where id = $id ";

        $result= $this->do_sql_show_error('update', $sql);
        if ($result)
            $this->observacion= $this->descripcion;
    	return $this->error;
    }

    public function set_estado($id_proceso= null, $id_proceso_code= null) {
        $observacion= setNULL_str($this->observacion);
        $fecha= setNULL_str(!is_null($this->fecha) ? $this->fecha : $this->cronos);
        $impacto= setNULL($this->impacto);
        $frecuencia= setNULL($this->frecuencia);
        $deteccion= setNULL($this->deteccion);
        
        $id_proceso= !empty($id_proceso) ? $id_proceso : $this->id_proceso;
        $id_proceso_code= !empty($id_proceso_code) ? $id_proceso_code : get_code_from_table("tprocesos", $id_proceso);

        $sql= "insert into treg_riesgo (id_riesgo,id_riesgo_code, id_proceso, id_proceso_code, id_usuario, estado, ";
        $sql.= "frecuencia, impacto, deteccion, reg_fecha, observacion, cronos, situs) values ($this->id_riesgo, ";
        $sql.= "'$this->id_riesgo_code', $id_proceso, '$id_proceso_code', $this->id_usuario, $this->estado, ";
        $sql.= "$frecuencia, $impacto, $deteccion, $fecha, $observacion, ";
        $sql.= "'$this->cronos', '$this->location')";

        $result= $this->do_sql_show_error('set_estado', $sql);
        return $this->error;
    }

    public function getRiesgo_reg($id_riesgo= null, $flag= false, $order= 'desc') {
        $flag= !is_null($flag) ? $flag : false;

        if (!empty($this->month)) {
            $time= new TTime();
            $lastday= $time->longmonth($this->month, $this->year);
            $date= $this->year.'-'.str_pad($this->month,2,'0',STR_PAD_LEFT).'-'.$lastday;
            unset($time);
        } else {
            if ($this->year == date('Y') || $this->year > date('Y')) 
                $date= "{$this->year}-12-31";
            if ($this->year < date('Y')) 
                $date= date('Y-m-d');
        }

        $id_riesgo= !empty($id_riesgo) ? $id_riesgo : $this->id_riesgo;
        if (empty($id_riesgo)) 
            return null;

        $sql= "select treg_riesgo.* from treg_riesgo where id_riesgo = $id_riesgo and ".date2pg("treg_riesgo.reg_fecha")." <= '$date' ";
        if (!empty($this->id_usuario))
            $sql.= "and id_usuario = $this->id_usuario ";
        if (!empty($this->estado))
            $sql.= "and estado = $this->estado ";
        if (!empty($this->id_proceso))
            $sql.= "and treg_riesgo.id_proceso = $this->id_proceso ";
        $sql.= "order by reg_fecha $order, treg_riesgo.cronos $order limit 1";

        $result= $this->do_sql_show_error('getRiesgo_reg', $sql);

        if ($flag) 
            return $result;
        $array= NULL;

       if (!empty($this->cant)) {
            $row = $this->clink->fetch_array($result);
        } else {
            if (!empty($this->id_proceso)) {
                $sql= "select triesgos.*, triesgos.cronos as reg_fecha from triesgos, tproceso_riesgos ";
                $sql.= "where triesgos.id = $id_riesgo and triesgos.id = tproceso_riesgos.id_riesgo ";
                $sql.= "and tproceso_riesgos.id_proceso = $this->id_proceso ";

                $result= $this->do_sql_show_error('getRiesgo_reg', $sql);
                $row = $this->clink->fetch_array($result);
            }
        }

        if (!empty($this->cant)) {
            $nivel = $this->getNivel($row['frecuencia'], $row['impacto']);
            $deteccion= !empty($row['deteccion']) ? (int)$row['deteccion'] : 1;
            $prioridad = (int)$row['frecuencia'] * (int)$row['impacto'] * $deteccion;

            $array = array('id' => $row['id_riesgo'], 'id_code' => $row['id_riesgo_code'], 'frecuencia' => $row['frecuencia'],
                'impacto' => $row['impacto'], 'deteccion' => $row['deteccion'], 'nivel' => $nivel, 'prioridad' => $prioridad,
                'estado' => $row['estado'], 'origen_data' => $row['origen_data'], 'id_proceso' => $row['id_proceso'],
                'observacion' => $row['observacion'], 'id_usuario' => $row['id_usuario'], 'cronos' => $row['cronos'],
                'reg_fecha' => $row['reg_fecha']);
        }

        return $array;
    }

    public function getAvance($id_riesgo, $list= false) {
        $date= null;
        if (!empty($this->month)) {
            $time= new TTime();
            $lastday= $time->longmonth($this->month, $this->year);
            $date= $this->year.'-'.str_pad($this->month,2,'0',STR_PAD_LEFT).'-'.$lastday;
            unset($time);
        } else {
            if (!empty($this->year)) 
                $date= "{$this->year}-12-31";
        }

        $sql= "select distinct treg_riesgo.*, ";
        $sql.= "tusuarios.nombre as responsable from treg_riesgo, tusuarios where id_riesgo = $id_riesgo ";
        $sql.= "and estado is not null and treg_riesgo.id_usuario = tusuarios.id ";
        if (!empty($date))
            $sql.= "and date(reg_fecha) <= '$date' ";
        if (!empty($this->id_proceso))
            $sql.= "and treg_riesgo.id_proceso = $this->id_proceso ";
        $sql.= "order by treg_riesgo.cronos desc ";

        $result= $this->do_sql_show_error('getAvance', $sql);
        if ($list) 
            return $result;

        $row= $this->clink->fetch_array($result);
        $valor= $row['valor'];
        if (empty($valor)) 
            $valor= 0;

        return $valor;
    }

    public function listar($fix_proceso= false, $use_tmp_triesgos= false) {
        global $string_procesos_down_entity;

        $fix_proceso= !is_null($fix_proceso) ? $fix_proceso : false;
        $use_tmp_triesgos= !is_null($use_tmp_triesgos) ? $use_tmp_triesgos : false;
        $triesgos= $use_tmp_triesgos && $this->if_triesgos ? "_triesgos" : "triesgos";

        if (!empty($this->month)) {
            $time= new TTime();
            $lastday= $time->longmonth($this->month, $this->year);
            $fecha_inicio= $this->year.'-01-01';
            $fecha_fin= $this->year.'-'.str_pad($this->month,2,'0',STR_PAD_LEFT).'-'.$lastday;
            unset($time);
        } else {
            $fecha_inicio= $this->year.'-01-01';
            $fecha_fin= $this->year.'-12-31';
        }

        $true= $_SESSION['_DB_SYSTEM'] == "mysql" ? "1" : "'1'";

        $sql= "select distinct $triesgos.*, $triesgos.id as _id, $triesgos.id_code as _id_code, $triesgos.id_proceso as _id_proceso, ";
        $sql.= "$triesgos.origen_data as _origen_data, tprocesos.tipo as _tipo from $triesgos, tproceso_riesgos, tprocesos ";
        $sql.= "where $triesgos.id = tproceso_riesgos.id_riesgo and tproceso_riesgos.id_proceso = tprocesos.id ";

        if (!empty($this->id_proceso)) {
            $sql.= "and tproceso_riesgos.id_proceso = $this->id_proceso ";
            if ($fix_proceso)
                $sql.= "and $triesgos.id_proceso = $this->id_proceso ";            
        } else {
            $sql.= "and tproceso_riesgos.id_proceso in ($string_procesos_down_entity) ";
        }

        $sql.= "and (";
        $or= null;
        if (!empty($this->ifestrategico)){
            $sql .= $or . "estrategico = $true";
            $or = " or ";
        }
        if (!empty($this->ma)){
            $sql .= $or . "ma = $true";
            $or = " or ";
        }
        if (!empty($this->sst)){
            $sql .= $or . "sst = $true";
            $or = " or ";
        }
        if (!empty($this->ext)){
            $sql .= $or . "ext = $this->ext";
            $or = " or ";
        }
        if (!empty($this->econ)){
            $sql .= $or . "econ = $true";
            $or = " or ";
        }
        if (!empty($this->reg)){
            $sql .= $or . "reg = $true";
            $or = " or ";
        }
        if (!empty($this->info)){
            $sql .= $or . "info = $true";
            $or = " or ";
        }
        if (!empty($this->calidad)){
            $sql .= $or . "calidad = $true";
            $or = " or ";
        }
        if (is_null($or))
            $sql.= "tprocesos.id_code is not null";
        $sql.= ") and (".date2pg("fecha_inicio_plan")." >= '$fecha_inicio' and ".date2pg("fecha_inicio_plan")." <= '$fecha_fin') ";
        $sql.= "order by _tipo asc ";

        $result= $this->do_sql_show_error('listar', $sql);
        return $result;
    }

    public function compute_status($id= null) {
        $this->id_riesgo= !empty($id) ? $id : $this->id_riesgo;        
        $this->reg_fecha= $this->year < date('Y') ? "{$this->year}-12-31" : date('Y-m-d');
        $this->get_array_reg_tareas();

        $i= 0;
        $j= 0;
        $max_cronos= '2000-01-01 00:00:00';
        $ncumplimiento= null;
        $n_outdate= null;
        foreach ($this->array_tareas as $id_tarea => $array) {
            ++$i;
            $rowcmp= $this->get_tarea_reg($id_tarea, date('Y', strtotime($array['fecha_inicio'])), date('Y', strtotime($array['fecha_fin'])));
            $ncumplimiento+= $rowcmp[0];
            $n_outdate+= $rowcmp[1];
            $max_cronos= $rowcmp[2];
            
            if ($rowcmp[3]['cumplimiento'] == _COMPLETADO) 
                ++$j;
        }

        $array= null;
        $this->estado= _IDENTIFICADO;
        if ($i > 0) {
            if ($i == $j) {
                if ($this->frecuencia >= _POCO_PROBABLE) 
                    $this->frecuencia-= 1;
                $this->deteccion= _CASI_CIERTO;
                $this->impacto= _INSIGNIFICANTE;
                $this->estado= $n_outdate > 0 ? _GESTIONANDOSE : _MITIGADO;
                $array= array('frecuencia'=>$this->frecuencia, 'impacto'=>$this->impacto, 'deteccion'=>$this->deteccion,
                            'estado'=>$this->estado, 'cronos'=>$max_cronos);
                
            } elseif ($ncumplimiento > 0) {
                $this->estado= _GESTIONANDOSE;
                $array= array('frecuencia'=>null, 'impacto'=>null, 'deteccion'=>null, 'estado'=>$this->estado,
                            'cronos'=>$max_cronos);
            }
        }
        return $array;
    }

    public function listar_and_ranking($flag= false, $create_tmp_triesgos= false, $automatic_risk= false, $last_reg= true) {
        $flag= !is_null($flag) ? $flag : false;
        $create_tmp_triesgos= !is_null($create_tmp_triesgos) ? $create_tmp_triesgos : false;
        $automatic_risk= !is_null($automatic_risk) ? $automatic_risk : false;
        $last_reg= !is_null($last_reg) ? $last_reg : true;

        $result= $this->listar($flag);
        if (empty($this->cant))
            return null;

        $i= 0;
        $array= null;
        $this->id_usuario= null;
        $this->estado= null;

        $id_riesgo= $this->id_riesgo;
        $id_riesgo_code= $this->id_riesgo_code;

        while ($row= $this->clink->fetch_array($result)) {
            $this->estado= null;
            $this->id_usuario= null;
            $this->id_riesgo= null;
            $this->id_riesgo_code= null;

           $_array= $this->getRiesgo_reg($row['_id'], false, $last_reg ? 'desc' : 'asc');

            if (is_null($_array)) {
                $this->frecuencia= $row['frecuencia'];
                $this->impacto= $row['impacto'];
                $this->deteccion= $row['deteccion'];
                $this->estado= $row['estado'];
                $this->nivel= $this->getNivel($row['frecuencia'], $row['impacto']);
            } else {
                $this->frecuencia= $_array['frecuencia'];
                $this->impacto= $_array['impacto'];
                $this->deteccion= $_array['deteccion'];
                $this->estado= $_array['estado'];
                $this->nivel= $this->getNivel($_array['frecuencia'], $_array['impacto']);
            }

            if ($last_reg) {
                $_arrayR= $this->compute_status($row['_id']);

                if ((!is_null($_arrayR) && !is_null($_array)) 
                    && (strtotime($_arrayR['cronos']) >= strtotime($_array['cronos']) 
                        || (strtotime($_arrayR['cronos']) < strtotime($_array['cronos']) 
                            && ($_array['id_usuario'] != _USER_SYSTEM && strtotime($row['cronos']) == strtotime($_array['cronos']))))) {
                    
                    if (!empty($_arrayR['frecuencia']))
                        $_array['frecuencia']= $_arrayR['frecuencia'];
                    if (!empty($_arrayR['impacto']))
                        $_array['impacto']= $_arrayR['impacto'];
                    if (!empty($_arrayR['deteccion']))
                        $_array['deteccion']= $_arrayR['deteccion'];
                    if (!empty($_arrayR['estado']))
                        $_array['estado']= $_arrayR['estado'];
                    $_array['nivel']= $this->getNivel($_array['frecuencia'], $_array['impacto']);

                    $this->frecuencia= $_array['frecuencia'];
                    $this->impacto= $_array['impacto'];
                    $this->deteccion= $_array['deteccion'];
                    $this->estado= $_array['estado'];
                    $this->nivel= $_array['nivel'];

                    $this->id_riesgo= $row['_id'];
                    $this->id_riesgo_code= $row['_id_code'];
                    $this->id_usuario= _USER_SYSTEM;
                    $this->fecha= $this->cronos;
                    $this->observacion= "Las variables ESTADO y PROBABILIDAD DE OCURRENCIA asignadas automaticamente por el sistema.";
                    
                    
                    $this->set_estado();

                    $this->frecuencia= null;
                    $this->impacto= null;
                    $this->deteccion= null;
                    $this->estado= null;
                    $this->nivel= null;

                    $this->id_usuario= null;
                    $this->fecha= null;
                }
            }
            
           $array[$i++]= $_array;
        }

        $this->id_riesgo= $id_riesgo;
        $this->id_riesgo_code= $id_riesgo_code;

        $num= $i;
        $j= 0;
        $ranking= null;
        $p= null;
        $pmax= null;
        $nmax= null;

        for ($k= 0; $k < $num; ++$k) {
            $null= true;

            for ($i= 0; $i < $num; ++$i) {
                if (is_null($array[$i]))
                    continue;
                
                if ($array[$i]['estado'] == _MITIGADO && $array[$i]['id_usuario'] == _USER_SYSTEM) {
                    $array[$i]['nivel']=  _NIVEL_RIESGO_BAJO;
                }    
                if ($null) {
                    $pmax= $array[$i]['prioridad'];
                    $nmax= $array[$i]['nivel'];
                    $p= $i;
                    $null= false;
                    continue;
                }
                if ($array[$i]['prioridad'] > $pmax || ($array[$i]['prioridad'] == $pmax && $array[$i]['nivel'] > $nmax)) {
                    $pmax= $array[$i]['prioridad'];
                    $nmax= $array[$i]['nivel'];
                    $p= $i;
                }
            }

            $ranking[$j++]= $array[$p];
            $array[$p]= null;
        }

        $this->cant= $j;

        if (!$create_tmp_triesgos) 
            return $ranking;

        foreach ($ranking as $reg)
            $this->create_tmp_riesgos($reg['id']);

        reset($ranking);
        return $ranking;
    }

    private function _create_tmp_triesgos() {
        $sql= "drop table if exists ".stringSQL("_triesgos");
        $this->do_sql_show_error('_create_tmp_triesgos', $sql);
     //
        $sql= " CREATE TEMPORARY TABLE ".stringSQL("_triesgos")." ( ";
        $sql.= " id ".field2pg("INTEGER(11)").", ";
        $sql.= " id_code ".field2pg("CHAR(12)").", ";
        $sql.= " estrategico ".field2pg("TINYINT(1)")." NOT NULL DEFAULT '0', ";
        $sql.= " econ ".field2pg("TINYINT(1)").", ";
        $sql.= " valor ".field2pg("FLOAT(9,3)").", ";
        $sql.= " ext ".field2pg("TINYINT(2)").", ";
        $sql.= " ma ".field2pg("TINYINT(1)").", ";
        $sql.= " sst ".field2pg("TINYINT(1)").", ";
        $sql.= " reg ".field2pg("TINYINT(1)").", ";
        $sql.= " info ".field2pg("TINYINT(1)").", ";
        $sql.= " calidad ".field2pg("TINYINT(1)").", ";
        $sql.= " id_proceso ".field2pg("INTEGER(11)").", ";
        $sql.= " id_proceso_code ".field2pg("CHAR(12)").", ";
        $sql.= " origen_data ".field2pg("TEXT").", ";
        $sql.= " fecha_inicio_plan ".field2pg("DATETIME").", ";
        $sql.= " fecha_fin_plan ".field2pg("DATETIME")." ";
        $sql.= " ) ";

        $this->do_sql_show_error('_create_tmp_triesgos', $sql);
        $this->if_triesgos= true;
    }

    public function create_tmp_riesgos($id) {
        if (!$this->if_triesgos) 
            $this->_create_tmp_triesgos();

        $sql= "insert into _triesgos ";
        $sql.= $_SESSION["_DB_SYSTEM"] == "mysql" ? "() " : null;
        $sql.= "select id, id_code, estrategico, econ, valor, ext, ma, sst, reg, info, calidad, id_proceso, id_proceso_code, ";
        $sql.= "origen_data, fecha_inicio_plan, fecha_fin_plan from triesgos where id= $id";

        $this->do_sql_show_error('create_tmp_riesgos', $sql);
    }

    public function list_riesgos_in_process($id_proceso) {
        if (isset($this->array_riesgos)) 
            unset($this->array_riesgos);
        $this->array_riesgos= null;

        $triesgos= $this->if_triesgos ? "_triesgos" : "triesgos";

        $sql= "select distinct $triesgos.* from $triesgos, tproceso_riesgos where $triesgos.id = tproceso_riesgos.id_riesgo ";
        $sql.= "and tproceso_riesgos.id_proceso = $id_proceso";
        $result= $this->do_sql_show_error('list_riesgos_in_process', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result)) {
            $array= array('id'=>$row['id'], 'id_code'=>$row['id_code']);
            $this->array_riesgos[$row['id']]= $array;
            ++$i;
        }
        return $i;
    }

    public function eliminar() {
        $array_tareas= array();
        $sql= "select triesgo_tareas.id_tarea as _id, id_proyecto from triesgo_tareas, ttareas ";
        $sql.= "where triesgo_tareas.id_tarea = ttareas.id and id_riesgo = $this->id_riesgo";
        $result= $this->do_sql_show_error('eliminar', $sql);

        $i= 0;
        while ($row= $this->clink->fetch_array($result))
            $array_tareas[$i++]= array('id'=>$row['_id'], 'id_proyecto'=>$row['id_proyeco']);

        $sql= "delete from triesgos where id = $this->id_riesgo ";
        $this->do_sql_show_error('eliminar', $sql);

        if ($i == 0) 
            return;

        foreach ($array_tareas as $tarea) {
            if (!empty($tarea['id_proyecto']))
                continue;

            $sql= "select * from triesgo_tareas where id_tarea = {$tarea['id']}";
            $result= $this->do_sql_show_error('eliminar', $sql);

            if (empty($result->num_rows)) {
                $sql= "delete from ttareas where id = {$tarea['id']}";
                $this->do_sql_show_error('eliminar', $sql);
            }
        }
    }

    public function riesgo_reg_clean() {
        $sql= "delete from treg_riesgo where id_riesgo = $this->id_riesgo";
        $this->do_sql_show_error('riesgo_reg_clean', $sql);
    }

    public function getNivel($freq= NULL, $impact= NULL) {
        if (empty($freq)) 
            $freq= $this->frecuencia;
        if (empty($impact)) 
            $impact= $this->impacto;

        $nivel[1][1]= 1; $nivel[1][2]= 1; $nivel[1][3]= 2; $nivel[1][4]= 3; $nivel[1][5]= 4;
        $nivel[2][1]= 1; $nivel[2][2]= 2; $nivel[2][3]= 3; $nivel[2][4]= 4; $nivel[2][5]= 5;
        $nivel[3][1]= 2; $nivel[3][2]= 3; $nivel[3][3]= 4; $nivel[3][4]= 5; $nivel[3][5]= 6;
        $nivel[4][1]= 3; $nivel[4][2]= 4; $nivel[4][3]= 5; $nivel[4][4]= 6; $nivel[4][5]= 7;
        $nivel[5][1]= 4; $nivel[5][2]= 5; $nivel[5][3]= 6; $nivel[5][4]= 7; $nivel[5][5]= 7;

        $this->nivel= $nivel[$freq][$impact];
        return $this->nivel;
    }

    public function GetPrioridad() {
        $deteccion= !empty($this->deteccion) ? $this->deteccion : _MODERADO;
        $prioridad= $this->frecuencia*$this->impacto*$deteccion;
        return $prioridad;
    }

    private function set_copyto($id, $year, $id_code) {
        $this->copyto= "$year($id_code)-";
        $sql= "update triesgos set copyto= '$this->copyto' where id = $id ";
        $this->do_sql_show_error('set_copyto', $sql);
    }

    public function if_exists_copyto($year, $copyto= null) {
        $copyto= !empty($copyto) ? $copyto : $this->copyto;
        $id_code= $this->get_ifcopyto($year, $copyto);
        if (empty($id_code))
            return null;

        $sql= "select * from triesgos where id_code = '$id_code'";
        $result= $this->do_sql_show_error('if_exists_copyto', $sql);
        if (empty($this->cant) || $this->cant == -1)
            return null;

        $row= $this->clink->fetch_array($result);
        return array('id'=>$row['id'], 'id_code'=>$row['id_code']);
    }

    public function this_copy($id_proceso=null, $id_proceso_code= null, $tipo= null, $radio_prs= null, 
                                                                        $to_year= null, $array_id= null) {
        $plus_year= empty($to_year) ? 1 : ($to_year - $this->year);
        if ($plus_year <= 0)
            $plus_year= 1;
        $to_year= !empty($to_year) ? $to_year : ($this->year + $plus_year);

        $id_proceso= !empty($id_proceso) ? $id_proceso : $_SESSION['id_entity'];
        $id_proceso_code= !empty($id_proceso_code) ? $id_proceso_code : get_code_from_table('tprocesos', $id_proceso);

        $this->id_proceso= $id_proceso;
        $this->id_proceso_code= $id_proceso_code;

        $tipo= !empty($tipo) ? $tipo : $_SESSION['entity_tipo'];

        $obj= $this->_this_copy();

        $obj->SetLink($this->clink);
        $obj->obj_code->SetLink($this->clink);
        $obj->SetId(null);
        $obj->SetIdRiesgo(null);
        $obj->set_id_code(null);
        $obj->set_id_riesgo_code(null);

        $obj->SetOrigenData(null);
        $obj->SetIdUsuario($_SESSION['id_usuario']);

        $obj->set_cronos($this->cronos);
        $obj->SetFechaInicioReal(null);
        $obj->SetFechaFinReal(null);

        $obj->SetIdProceso($id_proceso);
        $obj->set_id_proceso_code($id_proceso_code);

        $this->id_proceso= $radio_prs == 2 ? null : $id_proceso;
        $this->get_array_reg_tareas($radio_prs == 2 ? null : $id_proceso);
        $this->get_array_reg_procesos($id_proceso, $tipo, $radio_prs);
        $this->get_array_reg_objs($to_year);
        $this->listar_causas(true);

        $this->id_proceso= $id_proceso;

        $fecha= add_date($this->fecha_inicio_plan, 0, 0, $plus_year);
        $obj->SetFechaInicioPlan($fecha);
        $obj->SetFechaFinPlan(add_date($this->fecha_fin_plan, 0, 0, $plus_year));

/* adicionar el nuevo riesgo */
        if (is_null($array_id)) {
            $error= $obj->add();
            if (!is_null($error))
                return null;
            $id= $obj->GetId();
            $id_code= $obj->get_id_code();

            $this->set_copyto($this->id, $to_year, $id_code);
        } 
        else {
            $id= $array_id['id'];
            $id_code= $array_id['id_code'];

            $obj->SetIdRiesgo($id);
            $obj->set_id_riesgo_code($id_code);

            $error= $obj->update($id);
        }

        $this->obj_code->SetId($id);
        $this->obj_code->set_code('triesgos','id','id_code');
        $id_code= $this->obj_code->get_id_code();
        $obj->set_id_code($id_code);
        $obj->set_id_riesgo_code($id_code);

        $obj->setEstado(_IDENTIFICADO);
        $obj->SetFecha(date('Y-m-d', strtotime($this->cronos)));

        foreach ($this->array_procesos as $prs) {
            $obj->SetIdProceso($prs['id']);
            $obj->set_id_proceso_code($prs['id_code']);
            $obj->set_estado();
        }
        reset($this->array_procesos);

        $this->id_proceso= $id_proceso;
        $this->id_proceso_code= $id_proceso_code;
        $this->tipo= $tipo;

        $this->_copy_reg($id, $id_code, $radio_prs, $to_year);
        $this->_copy_items($obj);

        $array= array('id'=>$id, 'id_code'=>$id_code);
        return $array;
    }

    private function get_array_reg_tareas($id_proceso= null) {
        $this->inicio= $this->year;
        $this->fin= $this->year;
        $this->listar_tareas($this->id_riesgo, $id_proceso, true);
    }

    private function _copy_items(Triesgo &$obj) {
 /* asignando estado de tareas al riesgo */
        reset($this->array_reg_tareas);
        foreach ($this->array_reg_tareas as $tarea) {
            $obj->SetIdTarea($tarea['id']);
            $obj->set_id_tarea_code($tarea['id_code']);
            $obj->add_tarea();
        }

/* asignando causas al riesgo */
        reset($this->array_causas);
        foreach ($this->array_causas as $causa) {
            $obj->SetFecha($causa['fecha']);
            $obj->SetIdUsuario($causa['id_usuario']);
            $obj->SetDescripcion($causa['descripcion']);
            $obj->SetOrigenData($causa['origen_data']);

            $obj->add_causa();
        }
    }

    private function _copy_reg($id_riesgo= null, $id_riesgo_code= null, $radio_prs= null, $to_year= null) {
        $id_riesgo= !empty($id_riesgo) ? $id_riesgo : $this->id_riesgo;
        $id_riesgo_code= !empty($id_riesgo_code) ? $id_riesgo_code : $this->id_riesgo_code;

        reset($this->array_tareas);
        reset($this->array_procesos);
        reset($this->array_inductores);

        $this->array_reg_tareas= array();

        /* copia de las tareas */
        foreach ($this->array_tareas as $tarea) {
            if (isset($obj_task)) unset($obj_task);
            $obj_task= new Ttarea($this->clink);

            $obj_task->className= 'Triesgo';
            $obj_task->SetIdTarea($tarea['id']);
            $obj_task->Set();

            $obj_task->SetYear($this->year);
            $array= $obj_task->if_exists_copyto($to_year);

            $array= $obj_task->this_copy($this->id_proceso, $this->id_proceso_code, $this->tipo, $radio_prs, $to_year, $array);
            $this->array_reg_tareas[]= $array;
        }

        /* copia tproceso_riesgos */
        foreach ($this->array_procesos as $array) {
            $id_proceso= $array['id'];
            $id_proceso_code= setNULL_str($array['id_code']);

            $sql= "insert into tproceso_riesgos (id_riesgo, id_riesgo_code, id_proceso, id_proceso_code, year, cronos, situs) ";
            $sql.= "values ($id_riesgo, '$id_riesgo_code', $id_proceso, $id_proceso_code, $to_year, '$this->cronos', '$this->location') ";
            $result= $this->do_sql_show_error('_copy_reg', $sql);
        }

        /* copia tinductor_riesgos */
        foreach ($this->array_inductores as $array) {
            $id_inductor= $array['id'];
            $id_inductor_code= setNULL_str($array['id_code']);
            $peso= setNULL($array['peso']);

            $sql= "insert into tinductor_riesgos (id_riesgo, id_riesgo_code, id_inductor, id_inductor_code, ";
            $sql.= "peso, cronos, situs) values ($id_riesgo, '$id_riesgo_code', $id_inductor, $id_inductor_code, ";
            $sql.= "$peso, '$this->cronos', '$this->location')";
            $result= $this->do_sql_show_error('_copy_reg', $sql);
        }
    }

    public function if_empty_riesgo_nota() {
        $sql= "select from tproceso_riesgos where 1 ";
        if (!empty($this->id_riesgo))
            $sql.= "and id_riesgo = $this->id_riesgo ";
        if (!empty($this->id_nota))
            $sql.= "and id_nota = $this->id_nota ";
        if (!empty($this->id_requisito))
            $sql.= "and id_requisito = $this->id_requisito ";
        $result= $this->do_sql_show_error('if_empty_riesgo_nota', $sql);  
        return $this->cant > 0 ? true : false;  
    }
}

/*
 * Clases adjuntas o necesarias
 */
include_once "time.class.php";
if (!class_exists('Tregister_planning'))
    include_once "register_planning.class.php";