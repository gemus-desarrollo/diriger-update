<?php
/**
 * Created by Visual Studio Code.
 * User: mustelier
 * Date: 11/28/2015
 * Time: 8:19 a.m.
 */

session_start();
require_once "setup.ini.php";
require_once "class/config.class.php";

require_once "config.inc.php";
require_once "interface.class.php";
require_once "class/connect.class.php";
require_once "class/proyecto.class.php";
require_once "class/tarea.class.php";
require_once "class/regtarea.class.php";


class TGanttXML extends Tproyecto {

    public $xml;
    public $arrData;
    public $arrLinks;

    public $init_arrData;
    public $init_arrLinks;

    function TGanttXML($clink= null) {
        $this->clink= $clink;
        Tproyecto::__construct($this->clink);

        $this->className= 'TGanttXML';
    }

    function Set($xmlString) {
        $this->xml= simplexml_load_string($xmlString);
        $this->processXML($this->xml);

        $this->initData();
    }


    function objectsIntoArray($arrObjData, $arrSkipIndices = array()) {
        $arrData = array();

        // if input is object, convert into array
        if (is_object($arrObjData)) {
            $arrObjData = get_object_vars($arrObjData);
        }

        if (is_array($arrObjData)) {
            foreach ($arrObjData as $index => $value) {
                if (is_object($value) || is_array($value)) {
                    $value = $this->objectsIntoArray($value, $arrSkipIndices); // recursive call
                }

                if (in_array($index, $arrSkipIndices)) {
                    continue;
                }

                $arrData[$index] = $value;
            }
        }

        return $arrData;
    }


    function processXML($node) {
        $child= null;

        foreach ($node->children() as $child) {
            if ($child->getName() == 'data') 
                processXML($node);

            if ($child->getName() == 'task') {
                $arrXml = $this->objectsIntoArray($child);
                $this->arrData[]= $arrXml['@attributes'];
            }

            if ($child->getName() == 'coll_options') {
                if ($child->for= 'links') 
                    $this->processXML($child);
            }

            if ($child->getName() == 'item') {
                $arrXml = $this->objectsIntoArray($child);
                $this->arrLinks[]= $arrXml['@attributes'];
            }
    }   }


    private function initData() {
        $obj_task= new Tregtarea($this->clink);

        $obj_task->SetYear($this->year);
        $obj_task->SetMonth($this->month);
        $obj_task->SetIdProyecto($this->id_proyecto);
        $obj_task->SetIdProceso($this->id_proceso);

        $result= $obj_task->listar(false);

// fijando los data (task)
        while ($row= $this->clink->fetch_array($result)) {
            $obj_task->SetIdTarea($row['_id']);
            $array= $obj_task->listar_reg($row['_id'], true);

            $pComp= !is_null($array[0]['valor']) ? round($array[0]['valor']/100, 2) : null;
            $duration= s_datediff('d', date_create($row['fecha_inicio_plan']), date_create($row['fecha_fin_plan']));
            $pStart = str_replace("/", "-", odbc2date($row['fecha_inicio_plan']));
            $pEnd = str_replace("/", "-", odbc2date($row['fecha_inicio_plan']));
            $parent= !empty($row['id_tarea_grupo']) ? $row['id_tarea_grupo'] : null;

            $array= array("id"=>$row['_id'], "text"=>$row['tarea'], "start_date"=>$pStart, "end_date"=>$pEnd, "duration"=>$duration,
                "progress"=>$pComp, "users"=>$row['responsable'], "parent"=>$parent);

            $this->init_arrData[]= $array;
        }

 // fijando los links
        $i= 0;
        $this->clink->data_seek($result);
        while ($row= $this->clink->fetch_array($result)) {
            $array_depend= $obj_task->GetDependencies($row['_id'], 'source');
            $cant= $obj_task->GetCantidad();
            if (empty($cant)) 
                continue;

            foreach ($array_depend as $task) {
                $array= array("id"=>++$i, "source"=>$task['id'], "target"=>$row['_id'], "type"=>$task['tipo_depend']);
                $this->init_arrLinks[]= $array;
            }
        }

/* agregando el campo flag */
        $arrItem= array();
        $this->copy_array($this->init_arrLinks, $arrItem, 'delete');
        foreach ($arrItem as $array) 
            $this->init_arrLinks[]= $array;

        unset($arrItem); $arrItem= array();
        $this->copy_array($this->arrLinks, $arrItem, 'add');
        foreach ($arrItem as $array) 
            $this->arrLinks[]= $array;

        unset($arrItem); $arrItem= array();
        $this->copy_array($this->arrData, $arrItem, 'ok');
        foreach ($arrItem as $array) 
            $this->arrData[]= $array;

        unset($arrItem); $arrItem= array();
        $this->copy_array($this->init_arrData, $arrItem, 'ok');
        foreach ($arrItem as $array) 
            $this->init_arrData[]= $array;

        $this->inspect_links();
        $this->inspect_data();
    }

    /**
     * elimnar las conexiones
     */
    private function copy_array(&$arrOrigen, &$arrNew, $flag= 'update') {
        if (is_null($arrOrigen)) 
            return false;

        if (is_array($arrOrigen)) {
            reset($arrOrigen);

            foreach ($arrOrigen as $index => $value) {
                if (is_array($value)) {
                    $new= array();
                    $this->copy_array($value, $new, $flag);
                    $arrNew[]= $new;
                } else {
                    $arrNew[$index]= $value;
                }
            }

            $arrNew['flag']= $flag;
        }
    }


    private function inspect_links() {
        reset($this->arrLinks);
        reset($this->init_arrLinks);
        $i= 0;
        $n= 0;

        foreach ($this->init_arrLinks as $init) {
            foreach ($this->arrLinks as $new) {
                if (($init['source'] == $new['source'] && $init['target'] == $new['target']) && $init['type'] == $new['type']) {
                    $this->init_arrLinks[$i]['flag']= 'ok';
                    $this->arrLinks[$n]['flag']= 'ok';
                }

                if (($init['source'] == $new['source'] && $init['target'] == $new['target']) && $init['type'] != $new['type']) {
                    $this->init_arrLinks[$i]['flag']= 'update';
                    $this->arrLinks[$n]['flag']= 'update';
                }
                ++$n;
            }
            ++$i;
        }
    }

    public function set_links() {
        $obj_task= new Tregtarea($this->clink);
        $obj_task->set_cronos($this->cronos);

        reset($this->arrLinks);
        reset($this->init_arrLinks);

        foreach ($this->init_arrLinks as $array) {
            $obj_task->SetId($array['target']);
            $obj_task->set_id_code(null);

            if ($array['flag'] == 'ok') 
                continue;
            if ($array['flag'] == 'delete') 
                $obj_task->setDependencies($array['source'], null, $array['type'], 'delete');
        }

        foreach ($this->arrLinks as $array) {
            $obj_task->SetId($array['target']);
            $obj_task->set_id_code(null);

            if ($array['flag'] == 'ok') 
                continue;
            if ($array['flag'] == 'add') 
                $obj_task->setDependencies($array['source'], null, $array['type'], 'add');
            if ($array['flag'] == 'update') 
                $obj_task->setDependencies($array['source'], null, $array['type'], 'update');
        }
    }


    private function inspect_data() {
        reset($this->arrLinks);
        reset($this->init_arrLinks);
        $i= 0;
        $n= 0;

        foreach ($this->init_arrData as $init) {
            reset($this->arrData);
            foreach ($this->arrData as $new) {
                if (strtotime($init['start_date']) != strtotime(date2odbc($new['start_date']))
                    || strtotime($init['end_date']) != strtotime(date2odbc($new['end_date']))) {

                    if ($init['progress'] == $new['progress']) {
                        $this->init_arrData[$i]['flag']= 'update';
                        $this->arrData[$n]['flag']= 'update';
                    } else {
                        $this->init_arrData[$i]['flag']= 'seted';
                        $this->arrData[$n]['flag']= 'seted';
                    }
                } else {
                    if ($init['progress'] != $new['progress']) {
                        $this->init_arrData[$i]['flag']= 'register';
                        $this->arrData[$n]['flag']= 'register';
                    }
                }

                ++$n;
            }

            ++$i;
        }
    }

    public function set_data() {
        $obj_task= new Tregtarea($this->clink);
        $obj_task->set_cronos($this->cronos);

        reset($this->arrData);
        reset($this->init_arrData);

        foreach ($this->arrData as $array) {
            if ($array['flag' == 'update'] || $array['flag'] == 'seted') {
                $obj_task->Set($array['id']);

                $time= date('H:i:s', strtotime($obj_task->GetFechaInicioPlan()));
                $date= date2odbc($array['start_date']).' '.$time;
                $obj_task->SetFechaInicioPlan($date);

                $time= date('H:i:s', strtotime($obj_task->GetFechaFinPlan()));
                $date= date2odbc($array['end_date']).' '.$time;
                $obj_task->SetFechaFinPlan($date);

                $obj_task->update();
            }

            if ($array['flag'] == 'register' || $array['flag'] == 'seted') {
                if ($array['flag'] == 'register') $obj_task->Set($array['id']);

                $obj_task->SetFecha($this->cronos);
                $obj_task->SetIdUsuario($_SESSION['id_usuario']);
                $obj_task->SetCumplimiento($array['progress']*100);
                $obj_task->SetObservacion(null);
                $obj_task->SetPlanning(0);

                $obj_task->add_cump_to_task();
            }
        }
    }
}


class Tinterface extends TplanningInterface {
    protected  $xmlString;


    public function __construct($clink= null) {
        $this->id_proyecto= $_GET['id_proyecto'];

      //  $xmlString= "<data><task id='1' parent='' start_date='15-04-2013 00:00' duration='18' open='true' progress='0.92' end_date='03-05-2013 00:00'><![CDATA[Project #2]]></task><task id='2' parent='1' start_date='02-04-2013 00:00' duration='8' open='false' progress='0.83' end_date='10-04-2013 00:00'><![CDATA[Task #1]]></task><task id='3' parent='1' start_date='11-04-2013 00:00' duration='8' open='false' progress='0.6' end_date='19-04-2013 00:00'><![CDATA[Task #2]]></task><task id='4' parent='1' start_date='07-04-2013 00:00' duration='9' open='true' progress='0.71' end_date='16-04-2013 00:00'><![CDATA[bProject #2]]></task><task id='5' parent='4' start_date='09-04-2013 00:00' duration='5' open='false' progress='0.2' end_date='14-04-2013 00:00'><![CDATA[btask #1]]></task><task id='6' parent='4' start_date='13-04-2013 00:00' duration='3' open='false' progress='0.85' end_date='16-04-2013 00:00'><![CDATA[bTask #2]]></task><coll_options for='links'><item id='1' source='1' target='2' type='3' /><item id='2' source='2' target='3' type='0' /><item id='4' source='1' target='4' type='2' /><item id='6' source='1' target='5' type='1' /><item id='7' source='6' target='4' type='2' /></coll_options></data>";
        $xmlString= $_GET['xmlString'];
        if (empty($xmlString) || !strlen($xmlString)) 
            die("xml no valido");
        $this->xmlString= $xmlString;

        $this->clink= $clink;
        TplanningInterface::__construct($clink);
    }


    public function apply() {
        $obj= new TGanttXML($this->clink);
        $obj->set_cronos($this->cronos);

        $obj->SetYear($this->year);
        $obj->SetMonth($this->month);
        $obj->SetIdProceso($this->id_proceso);
        $obj->SetIdProceso($this->id_proyecto);

        $obj->Set($this->xmlString);

        //echo $ganttXML->xml->asXML();
/*
        echo "<br><br>";
        print_r($obj->arrData) ;echo "<br><br>";
        print_r($obj->arrLinks); echo "<br><br>";
        print_r($obj->init_arrData); echo "<br><br>";
        print_r($obj->init_arrLinks); echo "<br><br>";
*/
        $obj->set_data();
        $obj->set_links();

        $url_page= "../html/gantt.php?id_proyecto=$this->id_proyecto&signal=proyecto&action=$this->action&menu=tablero";
        $url_page.= "&exect=$this->action&id_proceso=$this->id_proceso&year=$this->year&month=$this->month&day=$this->day";
        $url_page.= "&id_programa=$this->id_programa";


        unset($_SESSION['obj']);

        if (is_null($error)) {
        ?>
            <script language='javascript' type="text/javascript" charset="utf-8">//self.location.href='<?php echo $url_page;?>'</script>
        <?php
        }

    }
}

$interface= new Tinterface($clink);
$interface->apply();
?>