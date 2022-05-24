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

require_once "class/proceso.class.php";
require_once "class/proceso_item.class.php";
require_once "class/peso.class.php";

require_once "class/base_lista.class.php";
require_once "class/tipo_lista.class.php";
require_once "class/lista.class.php";
require_once "class/lista_requisito.class.php";

require_once "class/code.class.php";
?>

<?php
class TListaBaseInterface extends Tbase {
    public $menu;
    protected $obj;

    protected $componente;
    protected $id_capitulo;
    protected $id_subcapitulo;

    protected $id_tipo_lista;
    protected $id_tipo_lista_code;

    protected $id_lista;
    protected $id_lista_code;

    protected $id_requisito;
    protected $id_requisito_code;


    public function __construct($clink) {
        $this->clink= $clink;

        $this->id= !empty($_GET['id']) ? $_GET['id'] : $_POST['id'];
        $this->action= !empty($_GET['action']) ? $_GET['action'] : $_POST['exect'];
        $this->menu= !empty($_GET['menu']) ? $_GET['menu'] : $_POST['menu'];
        $this->signal= !empty($_GET['signal']) ? $_GET['signal'] : $_POST['signal'];
        $this->year= !empty($_GET['year']) ? $_GET['year'] : $_POST['year'];

        $this->id_proceso= !empty($_POST['id_proceso']) ? $_POST['id_proceso'] :  $_GET['id_proceso'];
        if (empty($this->id_proceso)) 
            $this->id_proceso= $_SESSION['id_entity'];

        $this->componente= !empty($_POST['componente']) ? $_POST['componente'] :  $_GET['componente'];
        if (empty($this->componente)) 
            $this->componente= 0;
        $this->id_capitulo= !empty($_POST['id_capitulo']) ? $_POST['id_capitulo'] :  $_GET['id_capitulo'];
        if (empty($this->id_capitulo)) 
            $this->id_capitulo= 0;
        $this->id_Ssubcapitulo= !empty($_POST['id_subcapitulo']) ? $_POST['id_subcapitulo'] :  $_GET['id_subcapitulo'];
        if (empty($this->id_subcapitulo)) 
            $this->id_subcapitulo= 0;

        $this->id_auditoria= !empty($_POST['id_auditoria']) ? $_POST['id_auditoria'] : $_GET['id_auditoria'];
        $this->id_auditoria_code= !empty($_POST['id_auditoria_code']) ? $_POST['id_auditoria_code'] : $_GET['id_auditoria_code'];
        $this->id_auditoria_code= !empty($this->id_auditoria_code) ? $this->id_auditoria_code : get_code_from_table("tauditorias", $this->id_auditoria);

        $this->id_lista= !empty($_POST['id_lista']) ? $_POST['id_lista'] : $_GET['id_lista'];
        $this->id_lista_code= !empty($_POST['id_lista_code']) ? $_POST['id_lista_code'] : $_GET['id_lista_code'];
        $this->id_lista_code= !empty($this->id_lista_code) ? $this->id_lista_code : get_code_from_table("tlistas", $this->id_lista);

        $this->if_jefe= !empty($_POST['if_jefe']) ? $_POST['if_jefe'] : $_GET['if_jefe'];

        $this->obj_code= new Tcode($this->clink);
    }

    protected function setProcesos() {
        $tobj= new Tproceso_item($this->clink);
        $result= $tobj->listar();

        $tobj->SetIdLista($this->id_lista);
        $tobj->set_id_lista_code($this->id_lista_code);
        $tobj->SetIdRequisito($this->id_requisito);
        $tobj->set_id_requisito_code($this->id_requisito_code);

        while ($row= $this->clink->fetch_array($result)) {
            $value= $_POST['multiselect-prs_'.$row['_id']];

            $tobj->SetIdProceso($row['_id']);
            $tobj->set_id_proceso_code($row['_id_code']);

            if (!empty($value)) {
                for ($year= $this->inicio; $year <= $this->fin; $year++) {
                    if ($this->action == 'update' && $year < $this->year)
                        continue;
                    $tobj->SetYear($year);
                    empty($this->id_requisito) ? $tobj->setLista('add') : $tobj->setRequisito('add');
                }

            } else {
                if (!empty($_POST['multiselect-prs_init_'.$row['_id']])) {
                    for ($year= $this->inicio; $year <= $this->fin; $year++) {
                        if ($this->action == 'update' && $year < $this->year)
                            continue;
                        $tobj->SetYear($year);
                        empty($this->id_requisito) ? $tobj->setLista('delete') : $tobj->setRequisito('delete');

                        $this->obj_code->reg_delete('tproceso_listas', 'id_proceso_code', $row['_id_code'], 
                                        empty($this->id_requisito) ? 'id_lista_code' : 'id_requisito_code', 
                                        $this->id_code, 'year', $year);
        }   }   }   }

        $tobj->SetIdProceso($this->id_proceso);
        $tobj->set_id_proceso_code($this->id_proceso_code);

        for ($year= $this->inicio; $year <= $this->fin; $year++) {
            if ($this->action == 'update' && $year < $this->year) 
                continue;

            $tobj->SetYear($year);
            $tobj->setProyecto('add');
        }

        unset($tobj);
    }    
}
?>
