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
require_once "class/peso.class.php";

require_once "class/auditoria.class.php";
require_once "class/nota.class.php";
require_once "class/lista.class.php";
require_once "class/lista_requisito.class.php";
require_once "class/code.class.php";

require_once "lista_base.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !empty($_GET['ajax_win']) ? true : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "_header.interface.inc";
?>

<?php
class Tinterface extends TListaBaseInterface {
    private $obj_audit;
    private $obj_nota;
    private $origen;
    protected $id_tipo_auditoria,
            $id_tipo_auditoria_code;
    private $print; 
    private $chk_apply;
    private $init_row_temporary;

    private $array_requisitos;

    public function __construct($clink) {
        $this->clink= $clink;
        TListaBaseInterface::__construct($this->clink);

        $this->obj= new Tlista_requisito($this->clink);
        $this->obj_audit= new Tauditoria($this->clink);
        $this->obj_nota= new Tnota($this->clink);

        $this->cronos= date('Y-m-d H:i:s');
        $this->print= !empty($_GET['print']) ? $_GET['print'] : 0;
        $this->init_row_temporary= !empty($_POST['init_row_temporary']) ? $_POST['init_row_temporary'] : 0;
    }

    public function apply() {
        if (!empty($this->id)) {
            $this->obj->SetIdRequisito($this->id);
            $error= $this->obj->Set();

            $this->id_lista= $this->obj->GetIdLista();
            $this->id_lista_code= $this->obj->get_id_lista_code();

            $this->id_requisito= $this->id;
            $this->id_code= $this->obj->get_id_code();
            $this->id_requisito_code= $this->id_code;
        }

        $this->obj->SetYear($this->year);

        if (!empty($this->id_proceso)) {
            $this->obj->SetIdProceso($this->id_proceso);
            $this->id_proceso_code= get_code_from_table('tprocesos', $this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);
        }

        $this->obj->action= $this->action;

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetComponente($this->componente);
            $this->obj->SetIdCapitulo($this->id_capitulo);

            $this->obj->SetIdLista($this->id_lista);
            if (!empty($this->id_lista))
                $this->id_lista_code= get_code_from_table('tlistas', $this->id_lista);
            $this->obj->set_id_lista_code($this->id_lista_code);

            $this->id_tipo_lista = !empty($_POST['subcapitulo']) ? $_POST['subcapitulo'] : $_POST['capitulo'];
            $this->obj->SetIdTipo_lista($this->id_tipo_lista ? $this->id_tipo_lista : null);
            $this->id_tipo_lista_code= !empty($this->id_tipo_lista) ? get_code_from_table('ttipo_listas', $this->id_tipo_lista) : null;
            $this->obj->set_id_tipo_lista_code($this->id_tipo_lista ? $this->id_tipo_lista_code : null);

            $this->obj->SetNumero($_POST['numero']);
            $this->obj->SetNombre(trim($_POST['nombre']), false);
            $this->obj->SetEvidencia(trim($_POST['evidencia']));
            $this->obj->SetIndicacion(trim($_POST['indicacion']));

            $this->obj->SetPeso($_POST['peso']);

            $this->inicio= $_POST['inicio'];
            $this->fin= $_POST['fin'];
            $this->obj->SetInicio($this->inicio);
            $this->obj->SetFin($this->fin);
        }

        if ($this->action == 'add') {
            $error= $this->obj->add();

            if (is_null($error)) {
                $this->id= $this->obj->GetIdRequisito();
                $this->id_requisito= $this->id;
                $this->id_code= $this->obj->get_id_code();
                $this->id_requisito_code= $this->id_code;
            }
        }

        if ($this->action == 'update') {
            $error= $this->obj->update();
        }

        if (is_null($error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->id_requisito= $this->id;
            $this->id_requisito_code= $this->id_code;
            
            $this->setProcesos();
        }

        if ($this->action == 'delete')	{
            $radio_date= !is_null($_POST['_radio_date']) ? $_POST['_radio_date'] : $_GET['_radio_date'];
            $error= $this->obj->eliminar($radio_date);

            if (is_null($error) && $radio_date == 2)
                $this->obj_code->reg_delete('tlista_requisitos', 'id_code', $this->id_code);
        }

        if ($this->action == 'edit' || $this->action == 'list') {
            $error= $this->obj->Set();
        }

        $url_page_init= "../php/lista_requisito.interface.php?id=$this->id&signal=$this->signal&";
        $url_page= "action=$this->action&year=$this->year&componente=$this->componente";
        $url_page.= "&id_lista=$this->id_lista&id_tipo_lista=$this->id_tipo_lista&id_proceso=$this->id_proceso&inicio=$this->inicio";
        $url_page.= "fin=$this->fin&exect=$this->action&menu=$this->menu&if_jefe=$this->if_jefe&id_capitulo=$this->id_capitulo";
        $url_page.= "&id_subcapitulo=$this->id_subcapitulo&init_row_temporary=$this->init_row_temporary";

        add_page($url_page_init.$url_page, $this->action, 'i');

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj'] = serialize($this->obj);

                if ($this->action == 'add') {
                ?>
                    self.location.href = '../form/flista_requisito.php?<?=$url_page?>';
                <?php
                }
                if ($this->action == 'update' || $this->action == 'delete') {
                ?>
                    self.location.href = '<?php next_page(null, null, "&init_row_temporary=$this->init_row_temporary"); ?>';
                <?php
                }

                if ($this->action == 'edit' || $this->action == 'list') {
                    if ($this->action == 'edit')
                        $this->action= 'update';
                    ?>
                    self.location = '../form/flista_requisito.php?action=<?= $this->action ?>#<?= $this->id; ?>';
                <?php
                }
            } else {
                $this->obj->error = $error;
                $_SESSION['obj'] = serialize($this->obj);
                ?>
                self.location.href = '<?php prev_page($error); ?>';
                <?php
            }
        }
    }

    public function register() {
        if (!empty($this->id)) {
            $this->obj->SetIdRequisito($this->id);
            $error= $this->obj->Set();

            $this->id_requisito= $this->id;
            $this->id_code= $this->obj->get_id_code();
            $this->id_requisito_code= $this->id_code;
        }

        $this->id_lista= $_POST['id_lista'];
        $this->obj->SetIdLista($this->id_lista);
        if (!empty($this->id_lista))
            $this->id_lista_code= get_code_from_table('tlistas', $this->id_lista);
        $this->obj->set_id_lista_code($this->id_lista_code);

        $this->obj->SetIdProceso($this->id_proceso);
        $this->id_proceso_code= get_code_from_table('tprocesos', $this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->id_auditoria= $_POST['id_auditoria'];
        if (!empty($this->id_auditoria)) {
            $this->obj->SetIdAuditoria($this->id_auditoria);
            $this->id_auditoria_code= get_code_from_table('tauditorias', $this->id_auditoria);
            $this->obj->set_id_auditoria_code($this->id_auditoria_code);
        }

        $this->obj->SetCumplimiento($_POST['cumplimiento']);
        $this->obj->SetFecha(date2odbc($_POST['fecha']));
        $this->obj->SetObservacion(trim($_POST['observacion']));
        $this->obj->SetIdResponsable($_POST['responsable']);
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);

        $this->obj->setLista_reg();

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($error)) {
                $_SESSION['obj']= serialize($this->obj);
            ?>
                    cerrar('');

            <?php } else {
                $this->obj->error= $error;
                $_SESSION['obj']= serialize($this->obj);
            ?>
                cerrar("<?=$error?>");
            <?php
            }
        }
    }

    private function _set_nota($value) {
        $this->obj_nota->SetIdNota(null);
        $this->obj_nota->set_id_nota_code(null);

        $this->obj_nota->SetIdRequisito($this->id_requisito);
        $this->obj_nota->set_id_requisito_code($this->id_requisito_code);

        $this->obj_nota->SetIdAuditoria($this->id_auditoria);
        $this->obj_nota->set_id_auditoria_code($this->id_auditoria_code);

        $this->obj_nota->SetIdTipo_auditoria($this->id_tipo_auditoria);
        $this->obj_nota->set_id_tipo_auditoria_code($this->id_tipo_auditoria_code);

        $this->obj_nota->SetIdLista($this->id_lista);
        $this->obj_nota->set_id_lista_code($this->id_lista_code);

        $this->obj_nota->SetIdProceso($this->id_proceso);
        $this->obj_nota->set_id_proceso_code($this->id_proceso_code);

        $this->obj_nota->SetCumplimiento($value);
        $this->obj_nota->SetDescripcion($this->nombre);
        $this->obj_nota->SetIdUsuario($_SESSION['id_usuario']);

        $this->obj_nota->SetObservacion("Registrado el hallazgo a partir de Lista de Chequeo");
        $this->obj_nota->SetFecha($this->cronos);
        $this->obj_nota->setEstado(_IDENTIFICADO);

        $this->obj_nota->SetOrigen($this->origen); 
    }

    private function add_nota() {
        if ($this->cumplimiento != _NO_SE_CUMPLE)
            return null;

        $obj= new Tlista_requisito($this->clink);
        $obj->SetIdRequisito($this->id_requisito);
        $obj->Set();
        $this->nombre= "NO SE CUMPLE: ".$obj->GetNombre();

        $this->obj_nota->SetDescripcion($this->nombre);
        $this->obj_nota->SetTipo(_NO_CONFORMIDAD);
        $this->obj_nota->SetObservacion("Creada por incumplimiento de requisito en Lista de Chequeo");
        $this->obj_nota->SetCumplimiento($this->cumplimiento);

        $this->obj_nota->SetFechaInicioReal($this->cronos);
        $fecha_fin= "{$this->year}-12-01 23:59:00";
        $this->obj_nota->SetFechaFinPlan($fecha_fin);

        $this->obj_nota->SetIdUsuario($_SESSION['id_usuario']);
        $this->obj_nota->SetIdResponsable($_SESSION['id_usuario']);

        $error= $this->obj_nota->add();
        
        if (is_null($error)) {
            $this->id_nota= $this->obj_nota->GetId();
            $this->id_nota_code= $this->obj_nota->get_id_code();

            $tobj = new Tproceso_item($this->clink);
            $tobj->SetIdProceso($this->id_proceso);
            $tobj->set_id_proceso_code($this->id_proceso_code);
            $tobj->SetYear($this->year);

            $tobj->SetIdNota($this->id_nota);
            $tobj->set_id_nota_code($this->id_nota_code);

            $tobj->SetIdRequisito($this->id_requisito);
            $tobj->set_id_requisito_code($this->id_requisito_code);

            $error= $tobj->setRiesgo('add');
        }

        $this->error= $error;
        return $error;
    }

    private function get_nota() {
        $this->id_nota= $this->array_requisitos[$this->id_requisito]['id_nota'];
        $this->id_nota_code= $this->array_requisitos[$this->id_requisito]['id_nota_code'];

        if (empty($this->id_nota))
            return null;
        
        $obj_nota= new Tnota($this->clink);
        $obj_nota->SetYear($this->year);
        $obj_nota->SetIdNota($this->id_nota);
        $obj_nota->Set();
        $obj_nota->SetInicio(date('Y', strtotime($obj_nota->GetFechaInicioReal())));
        $obj_nota->SetFin(date('Y', strtotime($obj_nota->GetFechaFinPlan())));

        $result= $obj_nota->compute_status();

        if (!$result)
            return false;
        if ($result && !$result['ntareas'])
            return false;

        return true;
    }   

    private function delete_nota() {
        $this->get_nota();

        $this->obj_nota->SetIdNota($this->id_nota);
        $error= $this->obj_nota->eliminar();

        if (is_null($error)) {
            $this->obj_code->reg_delete('tnotas', 'id_code', $this->id_nota_code);
            $this->id_nota= null;
            $this->id_nota_code= null;

            $this->obj_nota->SetIdNota(null);
            $this->obj_nota->set_id_nota_code(null);
        }

        $this->error= $error;
        return $error;
    }

    private function set_all_requistos($array_requisitos_action) {
        $this->obj->get_requsitos_array();
        $this->array_requisitos= array_merge_overwrite($this->obj->array_requisitos, $this->array_requisitos);

        $this->obj_nota->SetFecha(date('Y-m-d H:i:s'));

        foreach ($this->array_requisitos as $id_requsito => $row) {
            if (!$row['chk_apply'] && $array_requisitos_action[$row['id_requisito']]['action'] != _ELIMINADO)
                continue; 

            $error= null;
            $this->id_nota= $row['id_nota'];
            $this->id_nota_code= $row['id_nota_code'];
            
            $value= $row['cumplimiento'];
            $this->nombre= $row['nombre'];
            $this->cumplimiento= $value;

            $this->id_requisito= $row['id_requisito'];
            $this->id_requisito_code= $row['id_requisito_code'];

            $this->obj_nota->SetIdProceso($this->id_proceso);
            $this->obj_nota->set_id_proceso_code($this->id_proceso_code);

            $this->obj_nota->SetIdRequisito($this->id_requisito);
            $this->obj_nota->set_id_requisito_code($this->id_requisito_code);
            
            $action= false;
            if (empty($this->id_nota) && $value == _NO_SE_CUMPLE) {
                $error= $this->add_nota();
                $this->obj_nota->setEstado(_IDENTIFICADO);
                $this->obj_nota->SetObservacion("Gestionandose la NO CONFORMIDAD");
                $action= true;
            }

            if (!empty($this->id_nota) && ($value == _NO_PROCEDE || $value == _SE_CUMPLE)) {
                $fixed= $this->get_nota();
                if (!$fixed) {
                    $error= $this->delete_nota();
                    $this->obj_nota->setEstado(_ELIMINADO);
                    $this->obj_nota->SetObservacion("Eliminada la NO CONFORMIDAD");
                    $action= true;
                } 
            }

            if (is_null($error) && $action) {
                $this->obj_nota->SetChkApply(false);
                $this->obj_nota->set_estado();                
            }
        }
    }

    private function set_requisito($_value, $value) {
        $this->_set_nota($value);

        $this->id_nota= null;
        $this->id_nota_code= null;
        $this->obj_nota->SetIdNota(null);
        $this->obj_nota->set_id_nota_code(null);

        $fixed= $this->get_nota();
        $action= null;

        if ($_value != _NO_SE_CUMPLE && $value == _NO_SE_CUMPLE) {
            $action= _IDENTIFICADO;
            $this->obj_nota->setEstado(_IDENTIFICADO);
            $this->obj_nota->SetObservacion("Registrada la NO CONFORMIDAD");
        }
        if ($_value == _NO_SE_CUMPLE && ($value == _NO_PROCEDE || ($value == _SE_CUMPLE && !$fixed && !empty($this->id_nota)))) {
            $action= _ELIMINADO;
            $this->obj_nota->setEstado(_ELIMINADO);
            $this->obj_nota->SetObservacion("Eliminada la NO CONFORMIDAD");    
        }
        if (($_value == _NO_SE_CUMPLE && $value == _SE_CUMPLE) && $fixed) {
            $action= _CERRADA;
            $this->obj_nota->setEstado(_CERRADA);
            $this->obj_nota->SetObservacion("Cerrada la NO CONFORMIDAD"); 
        }
        if (($_value != _SE_CUMPLE && $value == _SE_CUMPLE) && empty($this->id_nota)) {
            $action= _ELIMINADO;
            $this->obj_nota->setEstado(_ELIMINADO);
            $this->obj_nota->SetObservacion("Se cumple el requsito");            
        }
        if ($_value == _NO_SE_CUMPLE && $value == _EN_PROCESO) {
            $action= _GESTIONANDOSE;
            $this->obj_nota->setEstado(_GESTIONANDOSE);
            $this->obj_nota->SetObservacion("Gestionandose la NO CONFORMIDAD");
        }
        if (($_value == _NO_SE_CUMPLE && ($value == _NO_PROCEDE || $value == _SE_CUMPLE)) && $fixed) {
            $action= _GESTIONANDOSE;
            $this->obj_nota->setEstado(_GESTIONANDOSE);
            $this->obj_nota->SetObservacion("Gestionandose la NO CONFORMIDAD en espera del cumplimiento de las tareas.");            
        }
        if (!empty($_value) && empty($value)) {
            $action= _ELIMINADO;
            $this->obj_nota->setEstado(_ELIMINADO);
            $this->obj_nota->SetObservacion("No procede el REQUISITO");  
        }
        
        if ($action) {
            $this->obj_nota->SetIdNota($this->id_nota);
            $this->obj_nota->set_id_nota_code($this->id_nota_code);

            $this->obj_nota->SetChkApply(true);
            $this->obj_nota->set_estado();
        }

        return $action;
    }

    public function apply_list() {
        $this->obj_audit->SetId($this->id_auditoria);
        $this->obj_audit->Set($this->id_auditoria);

        $this->id_auditoria_code= $this->obj_audit->get_id_code();
        $this->origen= $this->obj_audit->GetOrigen();
        $this->id_tipo_auditoria= $this->obj_audit->GetIdTipo_auditoria();
        $this->id_tipo_auditoria_code= $this->obj_audit->get_id_tipo_auditoria_code();

        $this->obj->SetIdAuditoria($this->id_auditoria);
        $this->obj->set_id_auditoria_code($this->id_auditoria_code);

        $this->obj->SetIdLista($this->id_lista);
        $this->obj->set_id_lista_code($this->id_lista_code);

        $this->obj->SetYear($this->year);

        $this->obj->SetIdProceso($this->id_proceso);
        $this->id_proceso_code= get_code_from_table("tprocesos", $this->id_proceso);
        $this->obj->set_id_proceso_code($this->id_proceso_code);

        $this->obj_nota->SetIdAuditoria($this->id_auditoria);
        $this->obj_nota->set_id_auditoria_code($this->id_auditoria_code);

        $this->obj_nota->SetIdProceso($this->id_proceso);
        $this->obj_nota->set_id_proceso_code($this->id_proceso_code);

        $result= $this->obj->listar(null, null, true);

        $this->chk_apply= $this->print != _APLICAR_LISTA_TABLERO_NOTAS ? true :  false;
        $cant_requisitos= $this->obj->get_requsitos_array();
        $this->array_requisitos= array_merge_overwrite($this->obj->array_requisitos, $this->array_requisitos);

        $array_requisitos_action= array();

        while ($row= $this->clink->fetch_array($result)) {
            $_value= $_POST['id_requisito_init_'.$row['_id']];
            if (is_null($_value))
                continue;

            $value= $_POST['cumplimiento_'.$row['_id']];

            if (!is_null($_value) && ($_value == $value))
                continue;
            
            $this->nombre= $row['nombre'];
            $this->cumplimiento= $value;

            $this->id_requisito= $row['_id'];
            $this->id_requisito_code= $_POST['id_requisito_code_'.$row['_id']];

            $this->obj_nota->SetIdRequisito($this->id_requisito);
            $this->obj_nota->set_id_requisito_code($this->id_requisito_code);

            $array_requisitos_action[$row['_id']]['action']= $this->set_requisito($_value, $value);
        }

        if ($this->print == _APLICAR_LISTA_TABLERO_NOTAS) {
            $this->set_all_requistos($array_requisitos_action);
        }

        $url_page_init= "../php/lista_requisito.interface.php?id=$this->id";
        $url_page= "&signal=$this->signal&action=$this->action&year=$this->year&componente=$this->componente";
        $url_page.= "&id_lista=$this->id_lista&id_tipo_lista=$this->id_tipo_lista&id_auditoria=$this->id_auditoria";
        $url_page.= "&id_proceso=$this->id_proceso&inicio=$this->iniciofin=$this->fin&exect=$this->action";
        $url_page.= "&menu=$this->menu&if_jefe=$this->if_jefe&id_capitulo=$this->id_capitulo&id_subcapitulo=$this->id_subcapitulo";

        add_page($url_page_init.$url_page, $this->action, 'i');

        $form_page= "../form/flista_requisito_status.php?";
        $form_page.= $url_page;

        unset($_SESSION['obj']);

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            if (is_null($this->error)) {
                $_SESSION['obj']= serialize($this->obj);
            ?>
                <?php if ($this->print == _GUADAR_LISTA_IMPRIMIR) { ?>
                    var url= "../print/lista_requisito_status.php?<?=$url_page?>";
                    prnpage = window.open(url, "IMPRIMIENDO GRAFICO DEL ESTADO DE LOS REQUISTOS",
                        "width=900,height=600,toolbar=no,location=no, scrollbars=yes");
                <?php } ?>
                <?php 
                if ($this->print == _GUARDAR_LISTA_LISTA_REQUISTOS || $this->print == _GUADAR_LISTA_IMPRIMIR) { ?>
                    self.location.href = '<?= $form_page."&init_row_temporary=$this->init_row_temporary" ?>';
                <?php } ?>
                <?php if ($this->print == _APLICAR_LISTA_TABLERO_NOTAS || $this->print == _GUARDAR_LISTA_TABLERO_NOTAS) { ?>
                    self.location.href = '<?php next_page(null, null, "&init_row_temporary=$this->init_row_temporary"); ?>';
                <?php } ?>

            <?php } else {
                $this->obj->error= $this->error;
                $_SESSION['obj']= serialize($this->obj);
            ?>
                self.location.href = '<?php prev_page($this->error); ?>';
            <?php
            }
        }
    }
}
?>

<?php if (!$ajax_win) { ?>
    </body>
</html>
<?php } else { ?>
    </div>
<?php } ?>

<script type="text/javascript">
    <?php if (!$ajax_win) { ?>
    $(document).ready(function() {
        setInterval('setChronometer()',1);

        $('#body-log table').mouseover(function() {
            _moveScroll= false;
        });
        $('#body-log table').mouseout(function() {
            _moveScroll= true;
        });
    <?php } ?>

        <?php
        $menu= !empty($_POST['menu']) ? $_POST['menu'] : $_GET['menu'];
        if (empty($menu))
            $menu= 'frequisito';

        $interface= new Tinterface($clink);
        if ($menu == 'frequisito')
            $interface->apply();
        if ($menu == 'fregister')
            $interface->register();
        if ($menu == 'flista_register')
            $interface->apply_list();
        ?>

    <?php if (!$ajax_win) { ?>
    });
    <?php } ?>
</script>