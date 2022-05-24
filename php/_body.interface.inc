
<script language="javascript" type="text/javascript">
    function add_tarea() {
        var id_proceso = $('#proceso').val();
        var id_proyecto= 0;
        var id_programa= 0;
        var id_responsable= 0;
 
        if (id_proceso == 0) {
            alert("Aun no ha elegido el proceso en el que se define <?= $text_title ?>.");
            return;
        }
        var year = $('#year').val();
        var id = $('#id').val();
        var fecha_origen= $('#fecha_inicio').val();
        var fecha_termino= $('#fecha_fin').val();

        var url = '&action=add&id_proceso=' + id_proceso + '&year=' + year;
        url += '&signal=f<?= $this->signal ?>&id_<?= $this->signal ?>=' + id;
        url += '&fecha_origen='+encodeURIComponent(fecha_origen)+'&fecha_termino='+encodeURIComponent(fecha_termino);

        if ($('#menu').val() == 'proyecto' || $('#menu').val() == 'tablero') {
            id_proyecto = $('#id_proyecto').val();
            id_programa = $('#id_programa').val();
            id_responsable= $('#responsable').val();
        }
        
        url+= "&id_proyecto="+id_proyecto+'&id_programa='+id_programa+'&id_responsable='+id_responsable;
        self.location.href = '../form/ftarea.php?version=' + url;
    }
    
    function editar_tarea(id) {
        var id_riesgo = $('#id_riesgo').val();
        var id_nota = $('#id_nota').val();
        var id_proyecto = $('#id_proyecto').val();
        var year= $('#year').val();
        var url = null;

        url= '../php/tarea.interface.php?version=&action=edit&signal=f<?=$this->signal?>&menu=tarea';
        url+= '&id_riesgo='+id_riesgo+'&id_nota='+id_nota+'&id_proyecto='+id_proyecto+'&id_tarea='+id+'&id='+id;
        url+= '&year='+year;
        self.location.href = url;
    }      
</script>

<input type="hidden" name="menu" id="menu" value="<?=$this->menu?>" />
<input type="hidden" id="id" name="id" value="<?=!empty($this->id) ? $this->id : 0?>" />

<input type="hidden" id="id_riesgo" name="id_riesgo" value="<?=!empty($this->id_riesgo) ? $this->id_riesgo : 0?>" />
<input type="hidden" name="id_nota" id="id_nota" value="<?=!empty($this->id_nota) ? $this->id_nota : 0 ?>" />
<input type="hidden" name="id_proyecto" id="id_proyecto" value="<?=!empty($this->id_proyecto) ? $this->id_proyecto : 0?>" />
<input type="hidden" name="id_programa" id="id_programa" value="<?=!empty($this->id_programa) ? $this->id_programa : 0?>" />
<input type="hidden" name="responsable" id="responsable" value="<?=$this->id_responsable?>" />

<input type="hidden" name="fecha_inicio" id="fecha_inicio" value="<?=$this->fecha_inicio?>" />
<input type="hidden" name="fecha_fin" id="fecha_fin" value="<?=$this->fecha_fin?>" />

<input type="hidden" name="proceso" id="proceso" value="<?=$this->id_proceso?>" />

<input type="hidden" name="year" id="year" value="<?= $this->year ?>" />