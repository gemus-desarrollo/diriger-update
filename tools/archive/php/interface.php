<?php

/*
 * Copyright 2017
 * PhD. Geraudis Mustelier Portuondo
 * Este software esta protegido por la Ley de Derecho de Autor
 * Numero de Registro: 0415-01-2016
 */

session_start();
include_once "../../../php/setup.ini.php";
include_once "../../../php/class/config.class.php";
$_SESSION['debug']= 'no';

require_once "../php/setup.inc.php";
require_once "base.interface.php";
?>

<?php
global $using_remote_functions;
$ajax_win= !is_null($_GET['ajax_win']) ? $_GET['ajax_win'] : false;
if (is_null($using_remote_functions) && !$ajax_win)
    include "../../../php/_header.interface.inc";
?>

<?php
class Tinterface extends Tbase_interface {
    public $filename;
    public $url;
    private $codigo;

    public function __construct($clink) {
        Tbase_interface::__construct($clink);
        $this->clink= $clink;
    }

    private function set_event() {
        $obj_event= null;

        if (($this->action == 'add' || $this->action == 'update') && !empty($this->fecha_fin_plan)) {
            $this->id_evento= !empty($_POST['id_evento']) ? $_POST['id_evento'] : 0;

            $this->obj_event= new Tevento($this->clink);
            $this->obj_event->SetYear($this->year);

            if ($this->action == 'update' && !empty($this->id_evento)) {
                $this->obj_event->SetIdEvento($this->id_evento);
                $this->obj_event->Set();
                $this->id_evento= $this->obj_event->GetIdEvento();
                $this->id_evento_code= $this->obj_event->get_id_evento_code();
            }

            $this->indicaciones= textparse($this->indicaciones, false, true);
            $this->obj_event->SetNombre($this->indicaciones, false);

            $this->obj_event->SetIdResponsable($this->id_responsable);
            $this->obj_event->SetFechaInicioPlan(date('Y-m-d', strtotime($this->fecha_fin_plan)).' 08:30:00');
            $this->obj_event->SetFechaFinPlan($this->fecha_fin_plan);
            $this->obj_event->SetDescripcion("Indicación generada desde la Oficina de Gestión de Archivos");
            $this->obj_event->SetSendMail($this->sendmail);

            $this->obj_event->SetIdArchivo($this->id_archivo);
            $this->obj_event->set_id_archivo_code($this->id_archivo_code);

            $this->obj_event->toshow= _EVENTO_INDIVIDUAL;
            $this->obj_event->SetIfEmpresarial(_EVENTO_INDIVIDUAL);
            $this->obj_event->set_toshow_plan(_EVENTO_INDIVIDUAL);

            if ($this->action == 'add') {
                $this->obj_event->SetIdProceso($_SESSION['id_entity']);
                $this->obj_event->set_id_proceso_code($_SESSION['id_entity_code']);
            }

            if ($this->action == 'add' || ($this->action == 'update' && empty($this->id_evento))) {
                $this->error= $this->obj_event->add();

                if (is_null($this->error)) {
                    $this->id_evento= $this->obj_event->GetIdEvento();
                    $this->id_evento_code= $this->obj_event->get_id_evento_code();

                    $obj_prs= new Tproceso_item($this->clink);
                    $obj_prs->SetYear($this->year);

                    $obj_prs->SetIdProceso($this->id_proceso);
                    $obj_prs->set_id_proceso_code($this->id_proceso_code);
                    $obj_prs->SetIdArchivo($this->id_archivo);
                    $obj_prs->set_id_archivo_code($this->id_archivo_code);
                    $obj_prs->SetIdEvento($this->id_evento);
                    $obj_prs->set_id_evento_code($this->id_evento_code);

                    $obj_prs->toshow= _EVENTO_INDIVIDUAL;
                    $obj_prs->SetIfEmpresarial(_EVENTO_INDIVIDUAL);
                    $obj_prs->SetIdResponsable($this->id_responsable);

                    $obj_prs->setEvento('add');
                }
            } else {
                $this->error= $this->obj_event->update();
            }
        }

        if (($this->action == 'add' || $this->action == 'update') && !empty($this->id_evento)) {
            if (!$this->if_output) {
                $this->setUsuarios_event();
                $this->setGrupos_event();

                $this->setReg_event();
            } else {
                $this->setPersonas_event();
            }
        }
    }

    private function add_document() {
        if ($_FILES['file_doc-upload']["name"]) {
            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);

            $this->obj->SetIdArchivo($this->id_archivo);
            $this->obj->set_id_archivo_code($this->id_archivo_code);

            $this->error= $this->obj->upload();

            if (is_null($this->error)) {
                $this->url= $this->obj->url;
                $this->filename= $this->obj->filename;

                $this->error= $this->obj->add_upload();
            }

            if (is_null($this->error)) {
                $this->id_documento= $this->obj->GetIdDocumento();
                $this->id_documento_code= $this->obj->get_id_documento_code();

                $this->obj->SetIdDocumento($this->id_documento);
                $this->obj->set_id_documento_code($this->id_documento_code);
                $this->error= $this->obj->update();
            }

            if (is_null($this->error)) {
                $this->setUsuarios_doc();
                $this->setGrupos_doc();
            }

        } else {
            if (!empty($_POST['id_documento']) && empty($_POST['file_doc-upload-init'])) {
                $this->error= $this->obj->eliminar_by_id($_POST['id_documento']);

                if (is_null($this->error)) {
                    $this->obj->SetIdDocumento(null);
                    $this->obj->set_id_documento_code(null);
                    $this->obj->update();
        }   }   }
    }

    private function set_serie() {
        $obj_prs= new Tproceso($this->clink);
        $obj_prs->get_codigo_archive_array();
        $array_codigo_archives= $obj_prs->array_codigo_archives;

        $obj_serie = new Tserie($this->clink);
        $obj_serie->SetYear($this->year);
        $obj_serie->SetIdProceso($this->id_proceso);
        $obj_serie->set_id_proceso_code($this->id_proceso_code);
        $obj_serie->SetIdUsuario($_SESSION['id_usuario']);

        $obj_serie->SetSerie($this->if_output ? "RS" : "RE");
        $this->numero= $obj_serie->GetNumber();
        $this->error= $obj_serie->error;

        $this->codigo= $this->if_output ? "RS" : "RE";
        $this->codigo.= "-".str_pad($this->numero, 6, "0", STR_PAD_LEFT);
        $this->codigo.= "-{$this->year}";
        if (!empty($array_codigo_archives[$this->id_proceso]))
            $this->codigo.= "-{$array_codigo_archives[$this->id_proceso]}";
    }

    public function apply() {
        $numero_init= null;

        if (!empty($this->id)) {
            $this->obj->Set($this->id);

            $this->id_code = $this->obj->get_id_code();
            $this->id_archivo = $this->id;
            $this->id_archivo_code = $this->id_code;
            $this->id_evento= $this->obj->GetIdEvento();
            $this->id_evento_code= $this->obj->get_id_evento_code();
            $numero_init= $this->obj->GetNumero();
            $this->id_responsable_init= $this->obj->GetIdResponsable();
        }

        $this->obj->set_cronos($this->cronos);
        $this->obj->action = $this->action;
        $this->obj->SetIdUsuario($_SESSION['id_usuario']);
        $this->obj->SetYear($this->year);

        if ($this->action == 'add' || $this->action == 'update') {
            $this->obj->SetNumero(trim($_POST['numero']));
            $this->obj->SetTipo($_POST['tipo']);

            $this->fecha_entrega= time2odbc($_POST['fecha_entrega'].' '.$_POST['hora_entrega']);
            $this->obj->SetFechaOrigen(date2odbc($_POST['fecha_origen']));
            $this->obj->SetFechaEntrega($this->fecha_entrega);

            $this->obj->SetKeywords(strtolower(trim($_POST['keywords'])));
            $this->descripcion= trim($_POST['descripcion']);
            $this->obj->SetDescripcion($this->descripcion);

            $this->indicaciones= trim($_POST['indicaciones']);
            $this->obj->SetIndicaciones($this->indicaciones);

            $this->fecha_fin_plan= !empty($_POST['fecha_fin_plan']) ? time2odbc($_POST['fecha_fin_plan'].' '.$_POST['hora_fin_plan']) : null;
            $this->obj->SetFechaFinPlan($this->fecha_fin_plan);
            $this->fecha_inicio_plan= !empty($_POST['fecha_fin_plan']) ? time2odbc($_POST['fecha_fin_plan'].' 00:00:00') : null;
            $this->obj->SetFechaInicioPlan($this->fecha_inicio_plan);

            $this->obj->SetPrioridad($_POST['prioridad']);
            $this->obj->SetClase($_POST['clase']);

            $this->id_responsable= !empty($_POST['responsable']) ? $_POST['responsable'] : _USER_SYSTEM;
            $this->obj->SetIdResponsable($this->id_responsable);

            $this->obj->SetAntecedentes(trim($_POST['antecedentes']));

            $this->obj->SetIfOutput($this->if_output);

            $this->obj->SetIdProceso($this->id_proceso);
            $this->obj->set_id_proceso_code($this->id_proceso_code);

            $cant_personas= (int)$_POST['cant_personas'];
            $cant_multiselect= (int)$_POST['cant_multiselect-users'];

            $this->anonymous= 0;
            if ($this->if_output) {
                if (empty($cant_personas) && empty($cant_multiselect))
                    $this->if_anonymous= _IF_ANONYMOUS_SENDER_TARGET;
                elseif (empty($cant_personas))
                    $this->if_anonymous= _IF_ANONYMOUS_TARGET;
                elseif (empty($cant_multiselect))
                    $this->if_anonymous= _IF_ANONYMOUS_SENDER;
            } else {
                if (empty($cant_personas) && empty($cant_multiselect))
                    $this->if_anonymous= _IF_ANONYMOUS_SENDER_TARGET;
                elseif (empty($cant_personas))
                    $this->if_anonymous= _IF_ANONYMOUS_SENDER;
                elseif (empty($cant_multiselect))
                    $this->if_anonymous= _IF_ANONYMOUS_TARGET;
            }

            $this->obj->SetIfAnonymous($this->if_anonymous);
            $this->obj->SetIfImmediate($this->if_immediate);
            $this->obj->SetSendMail($this->sendmail);
            $this->obj->toshow= $this->toshow;
        }

        if ($this->action == 'add') {
            $this->error= $this->obj->add();

            if (is_null($this->error)) {
                $this->id_archivo= $this->obj->GetIdArchivo();
                $this->id_archivo_code= $this->obj->get_id_archivo_code();

                $this->set_serie();
                $this->obj->SetCodigo($this->codigo);
            }
        }

        if (is_null($this->error) && ($this->action == 'add' || !empty($this->numero))) {
            $this->obj->SetNumero($this->numero);
            $this->obj->SetCodigo($this->codigo);
            $this->error= $this->obj->update();
        }

        if ($this->action == 'update') {
            $this->error= $this->obj->update();
        }

        $this->obj_ref->set_cronos($this->cronos);
        $this->obj_ref->action = $this->action;

        if (is_null($this->error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->obj_ref->if_sender= $this->if_output ? false : true;
            $this->setPersonas();

            $this->obj_ref->if_sender= $this->if_output ? true : false;
            $this->setUsuarios();
            $this->setGrupos();
        }

        if (is_null($this->error) && ($this->action == 'add' || $this->action == 'update')) {
            if (!empty($this->indicaciones) && !empty($this->fecha_fin_plan))
                $this->setReg();
        }

        /** agregar evento */
        $this->id_responsable= !empty($_POST['responsable']) ? $_POST['responsable'] : _USER_SYSTEM;

        $toshow= false;
        if ($this->toshow && (!empty($this->fecha_fin_plan) && !empty($this->id_responsable)))
            $toshow= true;

        if ($toshow && (!$this->if_output || ($this->if_output && $this->n_user_persona))) {
            $this->set_event();
        }

        /** Para adjuntar el documento  */
        if (is_null($this->error) && ($this->action == 'add' || $this->action == 'update')) {
            $this->add_document();
        }

        if (($this->action == 'add' || $this->action == 'update') && !empty($this->id_evento) && is_null($this->error)) {
            $this->obj->SetIdEvento($this->id_evento);
            $this->obj->set_id_evento_code($this->id_evento_code);

            $this->obj->update();
        }

        if (($this->action == 'add' && $this->sendmail) && is_null($this->error)) {
            $this->sendMail();
        }

        if ($this->action == 'edit') {
            $this->obj->Set();
        }

        $url_page = "../php/interface.php";
        $url_page .= "?id=$this->id&signal=$this->signal&action=$this->action&year=$this->year&id_mcpo=$id_mcpo&id_prov=$id_prov";
        $url_page .= "&id_person=$this->id_person&id_proceso=$this->id_proceso&id_perspectiva=$this->id_perspectiva";
        $url_page .= "&exect=$this->action&menu=$this->menu";

        add_page($url_page, $this->action, 'i');

        if ($_SESSION['debug'] == 'no' || empty($_SESSION['debug'])) {
            $url= "&if_output=$this->if_output&id_organismo=$this->id_organismo&keywords=". urlencode($this->keywords);
            $url.= "&date_init=".urlencode($this->date_init)."&date_end=".urlencode($this->date_end)."&id_proceso=$this->id_proceso";

            if (is_null($this->error)) {
                $_SESSION['obj'] = serialize($this->obj);

                if (($this->action == 'add' && $numero_init == (int)$this->numero) || ($this->action == 'update' || $this->action == 'delete')) {
                    $action= $this->action == 'list' ? 'list' : 'edit';
            ?>
                    self.location.href = '../form/lrecord.php?action=<?=$action?><?=$url?>';
                <?php
                }

                if (($this->action == 'add' && $numero_init != (int)$this->numero) || ($this->action == 'edit' || $this->action == 'list')) {
                    if ($this->action == 'edit' || $this->action == 'add')
                        $this->action = 'update';
                    ?>
                        self.location.href = '../form/frecord.php?action=<?=$this->action?>&id=<?=$this->id_archivo?>&id_proceso=<?=$this->id_proceso?>';
                    <?php
                }
            } else {
                $this->obj->error = $this->error;
                $_SESSION['obj'] = serialize($this->obj);
                ?>
                    self.location.href = '<?php prev_page($this->error); ?>';
                <?php
        }   }
    }

}
?>

<?php if (is_null($using_remote_functions)) { ?>
    <?php if (!$ajax_win) { ?>
                </div>
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
        $interface = new Tinterface($clink);
        $interface->apply();
        ?>

       <?php if (!$ajax_win) { ?>
        });
        <?php } ?>
    </script>
<?php } ?>