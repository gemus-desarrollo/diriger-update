<?php
/**
 * Description of mirror_evento
 *
 * @author mustelier
 */

include_once "evento.class.php";

/**
 * Class Tcopy_evento
 * Gestionar los eventos a partir de las auditorias
 */
class Tmirror_evento extends Tevento {

    public function __construct($clink= null) {
        $this->clink= $clink;
        Tevento::__construct($this->clink);
    }

    public function set_evento_object(Tauditoria &$obj) {
        global $Ttipo_auditoria_array;
        global $Ttipo_nota_origen_array;

        $this->nombre= $Ttipo_nota_origen_array[$obj->GetOrigen()]." ".$Ttipo_auditoria_array[$obj->GetTipo_auditoria()];
        $this->numero= $obj->GetNumero();
        $this->numero_plus= $obj->GetNumero_plus();

        $this->descripcion= "Alcance: ".$obj->GetLugar().'\\n'.'Objetivos: '.$obj->GetObjetivo();
        $this->lugar= $obj->GetLugar();

        $this->id_usuario= $obj->GetIdUsuario();
        $this->id_responsable= $obj->GetIdResponsable();

        $this->id_proceso= $obj->GetIdProceso();
        $this->id_proceso_code= $obj->get_id_proceso_code();

        $this->funcionario= $obj->GetJefe_equipo();
        $this->funcionario= !empty($this->funcionario) ? $this->funcionario.' (Jefe del equipo)' : null;

        $this->id_responsable_2= $obj->get_id_responsable_2();
        $this->responsable_2_reg_date= $obj->get_responsable_2_reg_date();

        $this->fecha_inicio_plan= $obj->GetFechaInicioPlan();
        $this->fecha_fin_plan= $obj->GetFechaFinPlan();

        $this->toshow= $obj->toshow;
        $this->empresarial= $obj->GetIfEmpresarial();
        $this->id_tipo_evento= $obj->GetIdTipo_evento();
        $this->id_tipo_evento_code= $obj->get_id_tipo_evento_code();

        $this->SetCarga($obj->GetCarga());
        $this->SetPeriodicidad($obj->GetPeriodicidad());
        $this->SetDayWeek($obj->GetDayWeek());

        $this->SetSendMail($obj->GetSendMail());
        $this->SetToworkplan($obj->GetToworkplan());

        $this->saturday= $obj->saturday;
        $this->sunday= $obj->sunday;
        $this->freeday= $obj->freeday;

        $this->id_auditoria= $obj->GetId();
        $this->id_auditoria_code= $obj->get_id_code();

        $this->cronos= $obj->get_cronos();
        $this->location= $obj->location;
    }

    public function set_array_eventos($obj) {
        $i= 0;
        foreach ($this->array_eventos as $array) {
            $this->array_eventos[$i]= $obj->array_eventos[$i];
            ++$i;
        }
    }

    public function set_incio_fin($inicio, $fin) {
        $this->fecha_inicio_plan= $inicio;
        $this->fecha_inicio_plan= $fin;
    }
}

