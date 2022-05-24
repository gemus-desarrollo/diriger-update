
drop view _tmp_view_evento_toshow;
create view _tmp_view_evento_toshow as select distinct id_evento, toshow from teventos where id_evento is not null and toshow > 0;

select teventos.id, teventos.toshow, fecha_inicio_plan, id_proceso_code from teventos, _tmp_view_evento_toshow where (teventos.toshow = 0 or teventos.toshow is null) and teventos.id = _tmp_view_evento_toshow.id_evento;

/* reparar */
create view _tmp_view_evento_toshow as select distinct id_evento, toshow from teventos where id_evento is not null and toshow > 0;

update teventos, _tmp_view_evento_toshow set teventos.toshow= _tmp_view_evento_toshow.toshow 
where (teventos.toshow = 0 or teventos.toshow is null) and teventos.id = _tmp_view_evento_toshow.id_evento;

drop view _tmp_view_evento_toshow;
