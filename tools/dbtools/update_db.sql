
/*************************************************************/
-- beginscript:2017-01-10
/*************************************************************/
alter table ttareas add column ifassure tinyint(1) default null;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-01-17
/*************************************************************/
alter table _config add column off_mail_server tinyint(1) default 0;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-01-29
/*************************************************************/
ALTER TABLE ttematicas DROP INDEX tematica_numero_index;
CREATE UNIQUE INDEX tematica_numero_index ON ttematicas (numero, id_evento_code, id_proceso_code, id_tematica_code, fecha_inicio_plan);

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-02-14
/*************************************************************/
ALTER TABLE ttematicas DROP FOREIGN KEY ttematicas_fk4;
ALTER TABLE ttematicas ADD CONSTRAINT ttematicas_fk4 FOREIGN KEY (id_tematica) REFERENCES ttematicas (id) ON DELETE CASCADE ON UPDATE CASCADE;

CREATE TABLE tusuario_documentos (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_documento int(11) DEFAULT NULL,
  id_documento_code char(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  id_grupo int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY documento_index (id_documento_code,id_usuario,id_grupo),
  KEY id_documento (id_documento),
  KEY id_usuario (id_usuario),
  KEY id_grupo (id_grupo),
  CONSTRAINT tusuario_documentos_fk2 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tusuario_documentos_fk FOREIGN KEY (id_documento) REFERENCES tdocumentos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tusuario_documentos_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-02-27
/*************************************************************/
alter table texcel_celdas change id id int(11) unsigned not null auto_increment, add primary key (id);

ALTER TABLE texcel DROP FOREIGN KEY texcel_fk2;
ALTER TABLE texcel
  ADD CONSTRAINT texcel_fk2 FOREIGN KEY (id_plantilla) REFERENCES texcel_plantillas (id) ON DELETE CASCADE ON UPDATE CASCADE;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-03-16
/*************************************************************/
alter table _config change column time_synchro time_synchro mediumint(9) DEFAULT NULL AFTER type_synchro;
update _config set time_synchro= null where time_synchro = 86400;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-04-01
/*************************************************************/
update tnotas, tauditorias set tnotas.id_auditoria_code = tauditorias.id_code where tnotas.id_auditoria = tauditorias.id;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-04-10
/*************************************************************/
alter table tsystem add column inicio datetime; 
alter table tsystem add column fin datetime;
update tsystem set fin= cronos;
alter table tsystem change column cronos cronos datetime DEFAULT NULL AFTER fecha;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-04-12
/*************************************************************/
update tsystem set cronos= inicio, fin= inicio where cronos= '0000-00-00 00:00:00';
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-05-02
/*************************************************************/
update tusuario_procesos, tprocesos set tusuario_procesos.id_proceso_code = tprocesos.id_code where tusuario_procesos.id_proceso = tprocesos.id;
update tusuarios, tprocesos set tusuarios.id_proceso_code = tprocesos.id_code where tusuarios.id_proceso = tprocesos.id;

alter table treg_riesgo change column deteccion smallint(6) after impacto default null;

update tusuarios set nombre= 'SUPERUSUARIO', cargo= 'USUARIO ADMINITRATIVO DEL SISTEMA' where id = 1;

alter table ttipo_eventos change column numero numero varchar(9) default null after nombre;

alter table ttipo_eventos add column subcapitulo0 smallint(2) default null;

alter table ttipo_eventos add column id_subcapitulo int(11) default null;
ALTER TABLE ttipo_eventos ADD CONSTRAINT ttipo_eventos_fk FOREIGN KEY (id_subcapitulo) REFERENCES ttipo_eventos (id) ON DELETE CASCADE ON UPDATE CASCADE;
alter table ttipo_eventos add column subcapitulo1 smallint(2) default null;
  
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-05-19
/*************************************************************/
alter table _config add column smtp_auth tinyint(1) default 1;
update tdebates, ttematicas set tdebates.id_tematica_code= ttematicas.id_code where tdebates.id_tematica = ttematicas.id; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-05-25
/*************************************************************/
alter table ttipo_eventos add column indice integer(11);
alter table _config add column smtp_auth_tls tinyint(1) default 1;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-06-09
/*************************************************************/
alter table treg_real add column reg_date datetime default null;

update treg_real, tregistro set reg_date= reg_date_real where treg_real.id_indicador = tregistro.id_indicador 
and treg_real.year = tregistro.year and treg_real.month = tregistro.month and treg_real.day = tregistro.day;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-06-12
/*************************************************************/
update treg_evento set compute= 1 where cumplimiento in (2,3,8);

update triesgos set reg= 1 where reg = 0 and ext = 0 and ma = 0 and sst = 0 and info = 0 
and calidad = 0 and econ = 0 and estrategico = 0;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-06-27
/*************************************************************/
alter table triesgos drop index riesgo_lugar_index;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-07-06
/*************************************************************/
CREATE TABLE talerts (
  id_usuario int(11) DEFAULT NULL,
  id_evento int(11) DEFAULT NULL,
  nombre text,
  lugar text,
  id_responsable int(11) DEFAULT NULL,
  funcionario varchar(180) DEFAULT NULL,
  fecha_inicio_plan datetime DEFAULT NULL,
  alarm datetime DEFAULT NULL,
  sound tinyint(1) DEFAULT '1',
  active tinyint(1) DEFAULT '1',
  cronos datetime DEFAULT NULL,
  UNIQUE KEY index_evento_alert (id_usuario,id_evento),
  KEY id_usuario (id_usuario),
  KEY id_evento (id_evento),
  CONSTRAINT talerts_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT talerts_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT talerts_fk2 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-07-18
/*************************************************************/
CREATE TABLE tref_archivos (
  id int(11) NOT NULL AUTO_INCREMENT,
  nombre varchar(120) DEFAULT NULL,
  noIdentidad char(11) CHARACTER SET utf8 DEFAULT NULL,
  cargo varchar(120) DEFAULT NULL,
  organismo varchar(5) DEFAULT NULL,
  email varchar(180) DEFAULT NULL,
  telefono varchar(20) DEFAULT NULL,
  movil varchar(20) DEFAULT NULL,
  provincia char(2) DEFAULT NULL,
  municipio char(3) DEFAULT NULL,
  direccion text,
  id_evento int(11) DEFAULT NULL,
  id_evento_code char(10) DEFAULT NULL,  
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY tref_archivos_pkey (id_usuario),
  CONSTRAINT tref_archivos_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE,
  CONSTRAINT tref_archivos_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

CREATE TABLE tarchivos (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  numero mediumint(9) DEFAULT NULL,
  year smallint(6) DEFAULT NULL,
  tipo char(2) DEFAULT NULL,
  descripcion text,
  keywords text,
  indicaciones text,
  antecedentes text,
  id_documento int(11) DEFAULT NULL,
  id_remitente int(11) DEFAULT NULL,
  id_destinatario int(11) DEFAULT NULL,
  fecha_origen date DEFAULT NULL,
  fecha_entrega date DEFAULT NULL,
  noIdentidad varchar(11) DEFAULT NULL,
  nombre varchar(120) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_documento (id_documento),
  KEY id_remitente (id_remitente),
  KEY id_destinatario (id_destinatario),
  KEY id_usuario (id_usuario),
  CONSTRAINT tarchivos_fk FOREIGN KEY (id_documento) REFERENCES tdocumentos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tarchivos_fk1 FOREIGN KEY (id_remitente) REFERENCES tref_archivos (id) ON UPDATE CASCADE,
  CONSTRAINT tarchivos_fk2 FOREIGN KEY (id_destinatario) REFERENCES tref_archivos (id) ON UPDATE CASCADE,
  CONSTRAINT tarchivos_fk3 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-09-21
/*************************************************************/
ALTER TABLE tdebates DROP INDEX tdebates_index;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-09-29
/*************************************************************/
ALTER TABLE tnotas DROP INDEX proceso;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-10-03
/*************************************************************/
/* paso 1 */
ALTER TABLE tref_archivos DROP FOREIGN KEY tref_archivos_fk1;
ALTER TABLE tref_archivos DROP COLUMN id_evento;
ALTER TABLE tref_archivos DROP COLUMN id_evento_code;

ALTER TABLE tref_archivos ADD COLUMN id_responsable INTEGER(11);
ALTER TABLE tref_archivos 
	ADD CONSTRAINT tref_archivos_fk1 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE tref_archivos ADD COLUMN id_proceso INTEGER(11);
ALTER TABLE tref_archivos ADD COLUMN id_proceso_code CHAR(10);
ALTER TABLE tref_archivos 
	ADD CONSTRAINT tref_archivos_fk2 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON UPDATE CASCADE ON DELETE SET NULL;

/* paso 2 */
ALTER TABLE tarchivos ADD COLUMN if_anonymous TINYINT(4);
ALTER TABLE tarchivos ADD COLUMN id_evento INTEGER(11);
ALTER TABLE tarchivos ADD COLUMN id_evento_code CHAR(10);
ALTER TABLE tarchivos ADD COLUMN fecha_fin_plan DATETIME;
ALTER TABLE tarchivos ADD COLUMN id_documento_code CHAR(10);

ALTER TABLE tarchivos ADD CONSTRAINT tarchivos_fk4 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON UPDATE CASCADE ON DELETE RESTRICT; 

/* paso 3*/
ALTER TABLE tarchivos DROP COLUMN noIdentidad;
ALTER TABLE tarchivos DROP COLUMN nombre;
ALTER TABLE tarchivos ADD COLUMN if_output TINYINT(1);

ALTER TABLE tref_archivos ADD UNIQUE INDEX tref_archivos_noIdentidad_index (noIdentidad);
ALTER TABLE tref_archivos ADD UNIQUE INDEX tref_archivos_usuario_index (id_usuario);

/* paso 4 */
ALTER TABLE tdocumentos ADD COLUMN id_archivo INTEGER(11);
ALTER TABLE tdocumentos ADD COLUMN id_archivo_code CHAR(10);
ALTER TABLE tdocumentos ADD CONSTRAINT tdocumentos_fk6 FOREIGN KEY (id_archivo) REFERENCES tarchivos (id) ON UPDATE CASCADE ON DELETE CASCADE; 

/* paso 5 */
 ALTER TABLE tarchivos ADD COLUMN id_responsable INTEGER(11);
 ALTER TABLE tarchivos ADD CONSTRAINT tarchivos_fk5 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON UPDATE CASCADE ON DELETE RESTRICT;
 
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-10-04
/*************************************************************/
/* paso 1 */
ALTER TABLE teventos ADD COLUMN id_archivo INTEGER(11);
ALTER TABLE teventos ADD COLUMN id_archivo_code CHAR(10);
ALTER TABLE teventos ADD CONSTRAINT teventos_fk10 FOREIGN KEY (id_archivo) REFERENCES tarchivos (id) ON UPDATE CASCADE ON DELETE CASCADE;

/* paso 2 */
ALTER TABLE tarchivos ADD COLUMN toshow TINYINT(2);
ALTER TABLE tarchivos ADD COLUMN sendmail TINYINT(1);
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-10-07
/*************************************************************/
/* paso1 */
CREATE TABLE tpersonas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  nombre varchar(180) DEFAULT NULL,
  cargo varchar(180) DEFAULT NULL,
  organismo varchar(8) DEFAULT NULL,
  noIdentidad char(11) DEFAULT NULL,
  provincia char(3) DEFAULT NULL,
  municipio char(3) DEFAULT NULL,
  telefono varchar(14) DEFAULT NULL,
  movil varchar(14) DEFAULT NULL,
  email varchar(120) DEFAULT NULL,
  lugar varchar(180) DEFAULT NULL,
  direccion text,
  id_responsable int(11) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL, 
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY noIdentidad (noIdentidad),
  KEY id_responsable (id_responsable),
  KEY id_usuario (id_usuario),
  CONSTRAINT tpersonas_fk FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON UPDATE CASCADE,
  CONSTRAINT tpersonas_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE,
  CONSTRAINT tpersonas_fk2 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* paso 2 */
ALTER TABLE tarchivos DROP FOREIGN KEY tarchivos_fk1;
ALTER TABLE tarchivos DROP FOREIGN KEY tarchivos_fk2;
ALTER TABLE tarchivos DROP COLUMN id_remitente;
ALTER TABLE tarchivos DROP COLUMN id_destinatario;

DROP TABLE tref_archivos;

/* paso 3 */
CREATE TABLE tarchivo_personas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_archivo int(11) DEFAULT NULL,
  id_archivo_code char(10) DEFAULT NULL,
  id_persona int(11) DEFAULT NULL,
  id_persona_code char(10) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  id_grupo int(11) DEFAULT NULL,
  if_output tinyint(1) DEFAULT NULL,
  if_anonymous tinyint(4) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY tarchivo_personas_idx (id_archivo_code,id_persona_code),
  UNIQUE KEY tarchivo_personas_idx1 (id_archivo_code,id_usuario,id_grupo),
  KEY id_archivo (id_archivo),
  KEY id_persona (id_persona),
  KEY id_usuario (id_usuario),
  KEY id_grupo (id_grupo),
  CONSTRAINT tarchivo_personas_fk FOREIGN KEY (id_archivo) REFERENCES tarchivos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tarchivo_personas_fk1 FOREIGN KEY (id_persona) REFERENCES tpersonas (id) ON UPDATE CASCADE,
  CONSTRAINT tarchivo_personas_fk2 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE,
  CONSTRAINT tarchivo_personas_fk3 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-10-09
/*************************************************************/
/* paso 1 */
CREATE TABLE treg_archivo (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_archivo int(11) DEFAULT NULL,
  id_archivo_code char(10) DEFAULT NULL,
  observacion text,
  cumplimiento tinyint(4) DEFAULT NULL,
  reg_fecha datetime DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_archivo (id_archivo),
  KEY id_usuario (id_usuario),
  CONSTRAINT treg_archivo_fk FOREIGN KEY (id_archivo) REFERENCES tarchivos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT treg_archivo_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* paso 2 */
ALTER TABLE tarchivo_personas CHANGE COLUMN if_output if_sender TINYINT(1) DEFAULT NULL;

/* paso 3 */
ALTER TABLE tusuarios ADD COLUMN acc_archive TINYINT(1) DEFAULT NULL;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-10-11
/*************************************************************/

ALTER TABLE tarchivos CHANGE COLUMN fecha_entrega fecha_entrega DATETIME;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-10-21
/*************************************************************/

ALTER TABLE treg_archivo ADD COLUMN id_responsable INTEGER(11);
ALTER TABLE treg_archivo ADD CONSTRAINT treg_archivo_fk2 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON UPDATE CASCADE;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-11-03
/*************************************************************/
/* paso 1 */
ALTER TABLE tarchivos ADD COLUMN if_anonymous TINYINT(4);

/* paso 2 */
ALTER TABLE tarchivos ADD COLUMN if_immediate TINYINT(1);
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-11-19
/*************************************************************/
ALTER TABLE tproceso_criterio CHANGE COLUMN _blue _aqua FLOAT(5,2) DEFAULT 105.0;
ALTER TABLE tproceso_criterio CHANGE COLUMN _dark _blue FLOAT(5,2) DEFAULT 110.0;

ALTER TABLE tindicador_criterio CHANGE COLUMN _blue _aqua FLOAT(5,2) DEFAULT 105.0;
ALTER TABLE tindicador_criterio CHANGE COLUMN _dark _blue FLOAT(5,2) DEFAULT 110.0;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-11-21
/*************************************************************/
ALTER TABLE _config ADD COLUMN incoming_mail_server VARCHAR(80) DEFAULT NULL;
ALTER TABLE _config ADD COLUMN outgoing_mail_server VARCHAR(80) DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-11-28
/*************************************************************/
ALTER TABLE _config DROP COLUMN fullusermail;
ALTER TABLE _config DROP COLUMN outgoing_port;
ALTER TABLE _config DROP COLUMN outgoing_ssl;
ALTER TABLE _config DROP COLUMN incoming_protocol;
ALTER TABLE _config DROP COLUMN incoming_port;
ALTER TABLE _config DROP COLUMN incoming_ssl;
ALTER TABLE _config DROP COLUMN smtp_auth;
ALTER TABLE _config DROP COLUMN smtp_auth_tls;
ALTER TABLE _config DROP COLUMN ldap_login;
ALTER TABLE _config DROP COLUMN mail_use_ldap;
ALTER TABLE _config DROP COLUMN mail_method;
ALTER TABLE _config DROP COLUMN incoming_mail_server;
ALTER TABLE _config DROP COLUMN outgoing_mail_server;
ALTER TABLE _config DROP COLUMN off_mail_server;
/*************************************************************************/
-- endscript
/*************************************************************************/
/*************************************************************/
-- beginscript:2017-12-07
/*************************************************************/
ALTER TABLE ttipo_eventos DROP INDEX tipo_evento_numero_index;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-12-07
/*************************************************************/
ALTER TABLE ttipo_eventos ADD COLUMN year MEDIUMINT(9) DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2017-12-24
/*************************************************************/
ALTER TABLE tusuarios CHANGE COLUMN user_ldap user_ldap varchar(128) DEFAULT NULL;
update tusuarios set user_ldap= null;
CREATE UNIQUE INDEX user_ldap ON tusuarios (user_ldap);

ALTER TABLE tplanes CHANGE COLUMN tipo tipo TINYINT(2) DEFAULT 1;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-01-09
/*************************************************************/
  SET FOREIGN_KEY_CHECKS=0;
--
-- Constraints for table tsubordinados
--
ALTER TABLE tsubordinados
  ADD CONSTRAINT tsubordinados_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tsubordinados 
 ADD CONSTRAINT tsubordinados_fk1 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tsubordinados 
 ADD CONSTRAINT tsubordinados_fk2 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tsystem
--
ALTER TABLE tsystem
  ADD CONSTRAINT tsystem_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table ttareas
--
ALTER TABLE ttareas
  ADD CONSTRAINT ttareas_fk FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON UPDATE CASCADE;
ALTER TABLE ttareas  
  ADD CONSTRAINT ttareas_fk1 FOREIGN KEY (id_proyecto) REFERENCES tproyectos (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE ttareas  
  ADD CONSTRAINT ttareas_fk2 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE;
ALTER TABLE ttareas  
  ADD CONSTRAINT ttareas_fk3 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON UPDATE CASCADE;
ALTER TABLE ttareas  
  ADD CONSTRAINT ttareas_fk4 FOREIGN KEY (id_responsable_2) REFERENCES tusuarios (id) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table ttarea_tarea
--
ALTER TABLE ttarea_tarea
  ADD CONSTRAINT ttarea_tarea_fk FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ttarea_tarea  
  ADD CONSTRAINT ttarea_tarea_fk1 FOREIGN KEY (id_depend) REFERENCES ttareas (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table ttematicas
--
ALTER TABLE ttematicas
  ADD CONSTRAINT ttematicas_fk FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk1 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk2 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk3 FOREIGN KEY (id_responsable_eval) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk4 FOREIGN KEY (id_tematica) REFERENCES ttematicas (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk5 FOREIGN KEY (id_evento_accords) REFERENCES teventos (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table ttipo_eventos
--
ALTER TABLE ttipo_eventos
  ADD CONSTRAINT ttipo_eventos_fk FOREIGN KEY (id_subcapitulo) REFERENCES ttipo_eventos (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tusuarios
--
ALTER TABLE tusuarios
  ADD CONSTRAINT tusuarios_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table tusuario_documentos
--
ALTER TABLE tusuario_documentos
  ADD CONSTRAINT tusuario_documentos_fk FOREIGN KEY (id_documento) REFERENCES tdocumentos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_documentos  
  ADD CONSTRAINT tusuario_documentos_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_documentos  
  ADD CONSTRAINT tusuario_documentos_fk2 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tusuario_eventos
--
ALTER TABLE tusuario_eventos
  ADD CONSTRAINT tusuario_eventos_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_eventos  
  ADD CONSTRAINT tusuario_eventos_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_eventos  
  ADD CONSTRAINT tusuario_eventos_fk2 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_eventos  
  ADD CONSTRAINT tusuario_eventos_fk3 FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_eventos  
  ADD CONSTRAINT tusuario_eventos_fk4 FOREIGN KEY (id_auditoria) REFERENCES tauditorias (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tusuario_grupos
--
ALTER TABLE tusuario_grupos
  ADD CONSTRAINT tusuario_grupos_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_grupos  
  ADD CONSTRAINT tusuario_grupos_fk1 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tusuario_procesos
--
ALTER TABLE tusuario_procesos
  ADD CONSTRAINT tusuario_procesos_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_procesos  
  ADD CONSTRAINT tusuario_procesos_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_procesos  
  ADD CONSTRAINT tusuario_procesos_fk2 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tusuario_proyectos
--
ALTER TABLE tusuario_proyectos
  ADD CONSTRAINT tusuario_proyectos_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_proyectos  
  ADD CONSTRAINT tusuario_proyectos_fk1 FOREIGN KEY (id_proyecto) REFERENCES tproyectos (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_proyectos  
  ADD CONSTRAINT tusuario_proyectos_fk2 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tusuario_tableros
--
ALTER TABLE tusuario_tableros
  ADD CONSTRAINT tusuario_tableros_fk FOREIGN KEY (id_tablero) REFERENCES ttableros (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_tableros  
  ADD CONSTRAINT tusuario_tableros_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE tusuario_tableros  
  ADD CONSTRAINT tusuario_tableros_fk2 FOREIGN KEY (id_grupo) REFERENCES tgrupos (id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table _config_synchro
--
ALTER TABLE _config_synchro
  ADD CONSTRAINT _config_synchro_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE;
 
  SET FOREIGN_KEY_CHECKS=1; 
 /*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-01-16
/*************************************************************/

ALTER TABLE ttipo_eventos ADD COLUMN year MEDIUMINT(9) DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-01-20
/*************************************************************/
/* paso 1 */
ALTER TABLE tprocesos ADD COLUMN local_archive TINYINT(1) DEFAULT NULL;

/* paso 2 */
CREATE TABLE tseries (
  id int(11) NOT NULL AUTO_INCREMENT,
  serie varchar(20) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  year smallint(6) DEFAULT NULL,
  numero int(11) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY tseries_idx (id_proceso_code,serie),
  KEY id_proceso (id_proceso),
  KEY id_usuario (id_usuario),
  CONSTRAINT tseries_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON UPDATE CASCADE,
  CONSTRAINT tseries_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* paso 3 */
ALTER TABLE tarchivos ADD COLUMN id_proceso INTEGER(11) DEFAULT NULL;
ALTER TABLE tarchivos ADD COLUMN id_proceso_code CHAR(10) DEFAULT NULL;

ALTER TABLE tarchivos
  ADD CONSTRAINT tarchivos_fk6 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE RESTRICT ON UPDATE CASCADE;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-01-24
/*************************************************************/

ALTER TABLE treg_riesgo CHANGE COLUMN observacion observacion LONGTEXT DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-01-28
/*************************************************************/

CREATE TABLE ttipo_listas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  nombre text DEFAULT NULL,
  numero smallint(6) DEFAULT NULL,
  descripcion text DEFAULT NULL,
  componente smallint(6) DEFAULT NULL,
  year mediumint(9) DEFAULT NULL,
  indice int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE tlistas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  numero mediumint(9) DEFAULT NULL,
  descripcion longtext DEFAULT NULL,
  componente smallint(6) DEFAULT NULL,
  id_tipo_lista int(11) DEFAULT NULL,
  id_tipo_lista_code char(10) DEFAULT NULL,
  inicio mediumint(9) DEFAULT NULL,
  fin mediumint(9) DEFAULT NULL,
  if_send_down tinyint(1) DEFAULT NULL,
  peso tinyint(4) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
   KEY id_tipo_lista (id_tipo_lista),
  CONSTRAINT tlistas_fk FOREIGN KEY (id_tipo_lista) REFERENCES ttipo_listas (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


CREATE TABLE tproceso_listas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  year smallint(6) DEFAULT NULL,
  peso tinyint(4) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY tproceso_listas_idx (id_lista_code,id_proceso_code,year),
  KEY id_lista (id_lista),
  KEY id_proceso (id_proceso),
  CONSTRAINT tproceso_listas_fk FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tproceso_listas_fk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;


CREATE TABLE treg_lista (
  id int(11) NOT NULL AUTO_INCREMENT,
  ambiente smallint(6) DEFAULT NULL,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  id_auditoria int(11) DEFAULT NULL,
  id_auditoria_code char(10) DEFAULT NULL,
  cumplimiento tinyint(4) DEFAULT NULL,
  observacion text DEFAULT NULL,
  reg_fecha date DEFAULT NULL,
  id_responsable int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_lista (id_lista),
  KEY id_proceso (id_proceso),
  KEY id_auditoria (id_auditoria),
  CONSTRAINT treg_lista_fk FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT treg_lista_fk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT treg_lista_fk2 FOREIGN KEY (id_auditoria) REFERENCES tauditorias (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-01-29
/*************************************************************/
ALTER TABLE teventos ADD COLUMN numero_plus VARCHAR(10) DEFAULT NULL; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-02-02
/*************************************************************/
ALTER TABLE tdebates CHANGE COLUMN observacion observacion LONGTEXT NOT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-02-05
/*************************************************************/
ALTER TABLE tindicador_criterio DROP INDEX indicador_index;
CREATE UNIQUE INDEX indicador_index ON tindicador_criterio (id_indicador_code, id_proceso_code, year);
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-02-07
/*************************************************************/
UPDATE tusuarios, tescenarios SET tusuarios.id_proceso= tescenarios.id_proceso, tusuarios.id_proceso_code= tescenarios.id_proceso_code 
WHERE tusuarios.id_proceso IS NULL; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-02-09
/*************************************************************/
ALTER TABLE tauditorias ADD COLUMN numero MEDIUMINT DEFAULT NULL;
ALTER TABLE tauditorias ADD COLUMN numero_plus VARCHAR(10) DEFAULT NULL;

UPDATE tauditorias, teventos SET  tauditorias.numero= teventos.numero, tauditorias.numero_plus= teventos.numero_plus 
WHERE tauditorias.id = teventos.id_auditoria;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-02-17
/*************************************************************/

ALTER TABLE ttematicas DROP FOREIGN KEY ttematicas_fk;
ALTER TABLE ttematicas DROP FOREIGN KEY ttematicas_fk1;
ALTER TABLE ttematicas DROP FOREIGN KEY ttematicas_fk2;
ALTER TABLE ttematicas DROP FOREIGN KEY ttematicas_fk3;

ALTER TABLE ttematicas DROP INDEX ttematicas_fk;
ALTER TABLE ttematicas DROP INDEX ttematicas_fk1;
ALTER TABLE ttematicas DROP INDEX ttematicas_fk2;
ALTER TABLE ttematicas DROP INDEX ttematicas_fk3;

ALTER TABLE ttematicas
  ADD CONSTRAINT ttematicas_fk FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk1 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk2 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE ttematicas  
  ADD CONSTRAINT ttematicas_fk3 FOREIGN KEY (id_responsable_eval) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;


ALTER TABLE tdebates DROP FOREIGN KEY tdebates_fk;
ALTER TABLE tdebates DROP FOREIGN KEY tdebates_fk1;

ALTER TABLE tdebates DROP INDEX tdebates_fk;
ALTER TABLE tdebates DROP INDEX tdebates_fk1;

ALTER TABLE tdebates
  ADD CONSTRAINT tdebates_fk FOREIGN KEY (id_tematica) REFERENCES ttematicas (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE tdebates  
  ADD CONSTRAINT tdebates_fk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE RESTRICT ON UPDATE CASCADE;

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-02-20
/*************************************************************/
ALTER TABLE tprocesos ADD COLUMN inicio MEDIUMINT DEFAULT NULL;
ALTER TABLE tprocesos ADD COLUMN fin MEDIUMINT DEFAULT NULL;
UPDATE tprocesos SET inicio= 2009, fin= 2030;

ALTER TABLE tasistencias DROP FOREIGN KEY tasistencias_fk;
ALTER TABLE tasistencias DROP FOREIGN KEY tasistencias_fk1;

ALTER TABLE tasistencias
  ADD CONSTRAINT tasistencias_fk FOREIGN KEY (id_usuario) REFERENCES tusuarios(id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE tasistencias
  ADD CONSTRAINT tasistencias_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) ON DELETE RESTRICT ON UPDATE CASCADE;
  
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-03-01
/*************************************************************/
ALTER TABLE tdeletes ADD COLUMN observacion TEXT DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-03-23
/*************************************************************/
ALTER TABLE teventos ADD COLUMN if_send TINYINT(1) DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-03-25
/*************************************************************/
/* paso 1 */
ALTER TABLE ttipo_eventos ADD COLUMN id_code CHAR(10) DEFAULT NULL;

ALTER TABLE ttipo_eventos ADD COLUMN id_proceso INTEGER(11) DEFAULT NULL;
ALTER TABLE ttipo_eventos ADD COLUMN id_proceso_code CHAR(10) DEFAULT NULL;

ALTER TABLE ttipo_eventos ADD COLUMN id_subcapitulo_code CHAR(10) DEFAULT NULL;

ALTER TABLE ttipo_eventos
  ADD CONSTRAINT ttipo_eventos_fk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos(id) ON DELETE RESTRICT ON UPDATE CASCADE;

/* paso 2 */
ALTER TABLE teventos ADD COLUMN id_tipo_evento_code CHAR(10) DEFAULT NULL; 

/*************************************************************************/
-- endscript
/*************************************************************************/

/*************************************************************/
-- beginscript:2018-04-01
/*************************************************************/
/* paso 1*/
update tsystem set action= 'purge' where action = 'purgue';

/* paso 2 */
SET FOREIGN_KEY_CHECKS=0;

delete from tpoliticas;
truncate tpoliticas;

ALTER TABLE tpoliticas CHANGE COLUMN grupo grupo MEDIUMINT(9) DEFAULT NULL;
ALTER TABLE tpoliticas CHANGE COLUMN capitulo capitulo MEDIUMINT(9) DEFAULT NULL;

REPLACE INTO tpoliticas VALUES (1, 'XX00000001', 1, 1, 'I MODELO DE GESTION ECONOMICA ', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (2, 'XX00000002', 0, 1, 'La planificación socialista seguirá siendo la vía principal para la dirección de la economía y continuará su transformación, garantizará los equilibrios macroeconómicos fundamentales y los objetivos y metas para el Desarrollo Económico y 6 Social a largo plazo. Se reconoce la existencia objetiva de las relaciones del mercado, influyendo sobre el mismo y considerando sus características.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (3, 'XX00000003', 0, 2, 'El Modelo Económico y Social Cubano de Desarrollo Socialista reconoce la propiedad socialista de todo el pueblo sobre los medios de producción fundamentales, como la forma principal en la economía nacional. Además, reconoce, entre otras, la propiedad cooperativa, mixta y la privada de personas naturales o jurídicas cubanas o totalmente extranjeras. Todas interactúan de conjunto.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (4, 'XX00000004', 0, 3, 'En las formas de gestión no estatales no se permitirá la concentración de la propiedad y la riqueza en personas jurídicas o naturales, lo que se regulará.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (5, 'XX00000005', 0, 4, 'El sistema de dirección de la economía abarca el sistema empresarial estatal, la actividad presupuestada, las diferentes modalidades de las asociaciones económicas internacionales y demás formas de propiedad y gestión, con el objetivo de garantizar el carácter integral del sistema de planificación.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (6, 'XX00000006', 0, 5, 'Continuar fortaleciendo el papel del contrato como instrumento esencial de la gestión económica, elevando la exigencia en su cumplimiento en las relaciones entre los actores económicos.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (7, 'XX00000007', 0, 6, 'Exigir la actuación ética de los jefes, los trabajadores y las entidades, así como fortalecer el sistema de control interno. El control externo se basará, principalmente, en mecanismos\r\neconómico-financieros, sin excluir los administrativos, haciéndolo más racional.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (8, 'XX00000008', 0, 7, 'Continuar fortaleciendo la contabilidad para que constituya una herramienta en la toma de decisiones y garantice la fiabilidad de la información financiera y estadística, oportuna y razonablemente.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (13, 'XX00000013', 1, 1, 'GENERALES', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (15, 'XX00000015', 1, 2, 'ESFERA EMPRESARIAL', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (16, 'XX00000016', 0, 8, 'Continuar otorgando gradualmente a las direcciones de las entidades y del sistema empresarial nuevas facultades, definiendo con precisión sus límites sobre la base del rigor en el diseño y aplicación de su sistema de control interno, así como mostrando en su gestión administrativa orden, disciplina y exigencia. Evaluar de manera sistemática los resultados de la aplicación y su impacto.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (17, 'XX00000017', 0, 9, 'Las empresas deciden y administran su capital de trabajo e inversiones hasta el límite previsto en el plan; sus finanzas internas no podrán ser intervenidas por instancias ajenas a las mismas; ello solo podrá ser realizado mediante los procedimientos legalmente establecidos.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (18, 'XX00000018', 0, 10, 'Avanzar en el perfeccionamiento del sistema empresarial, a partir de otorgarle nuevas facultades para su funcionamiento, a fin de lograr empresas con mayor autonomía y competitividad. Elaborar la norma jurídica que regule integralmente la actividad empresarial.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (19, 'XX00000019', 0, 11, 'Las empresas y cooperativas que muestren sostenidamente en sus balances financieros pérdidas, capital de trabajo insuficiente, que no puedan honrar con sus activos las obligaciones contraídas o que obtengan resultados negativos en auditorías financieras, se podrán transformar o serán sometidas a un proceso de liquidación, cumpliendo con lo que se establezca.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (20, 'XX00000020', 0, 12, 'Continuar avanzando en la implantación del principio de que los ingresos de los trabajadores y sus jefes en el sistema de entidades de carácter empresarial, estén en correspondencia con los resultados que se obtengan.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (21, 'XX00000021', 0, 13, 'Las empresas y las cooperativas pagarán a los consejos de la administración municipal donde operan sus establecimientos, un tributo territorial, definido centralmente, teniendo en cuenta las particularidades de cada municipio, para contribuir a su desarrollo, que constituya fuente para financiar gastos corrientes y de capital.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (22, 'XX00000022', 0, 14, 'Priorizar y continuar avanzando en el logro del ciclo completo de producción mediante los encadenamientos productivos entre organizaciones que desarrollan actividades productivas, de servicios y de ciencia, tecnología e innovación, incluidas las universidades, que garanticen el desarrollo rápido y eficaz de nuevos productos y servicios, con estándares de calidad apropiados, que incorporen los resultados de la investigación científica e innovación tecnológica, e integren la gestión de comercialización interna y externa.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (28, 'XX00000028', 1, 3, 'LAS COOPERATIVAS', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (29, 'XX00000029', 0, 15, 'Avanzar en el experimento de las cooperativas no agropecuarias, priorizando aquellas actividades que ofrezcan soluciones al desarrollo de la localidad, e iniciar el proceso de constitución de cooperativas de segundo grado.', 2012, 2018, 1, 28, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (30, 'XX00000030', 0, 16, 'La norma jurídica sobre cooperativas regulará todos los tipos de cooperativas y deberá ratificar que como propiedad colectiva, no serán vendidas, ni trasmitida su posesión a otras cooperativas, a formas de gestión no estatales o a personas naturales. Proponer la creación de la instancia de Gobierno que conduzca la actividad.', 2012, 2018, 1, 28, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (39, 'XX00000039', 1, 5, 'TERRITORIOS', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (40, 'XX00000040', 0, 17, 'Impulsar el desarrollo de los territorios a partir de la estrategia del país, de modo que se fortalezcan los municipios como instancia fundamental, con la autonomía necesaria, sustentables, con una sólida base económico-productiva, y se reduzcan las principales desproporciones entre estos, aprovechando sus potencialidades. Elaborar el marco jurídico correspondiente.', 2012, 2018, 1, 39, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (43, 'XX00000043', 1, 2, 'II POLÍTICAS MACROECONÓMICAS', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (44, 'XX00000044', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (45, 'XX00000045', 0, 18, 'Garantizar los equilibrios macroeconómicos fundamentales y con ello lograr un entorno macroeconómico fiscal, monetario y financiero estable y sostenible que permita asignar eficientemente los recursos en función de las prioridades nacionales y del crecimiento económico sostenido.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (46, 'XX00000046', 0, 19, 'Consolidar las funciones dinerarias del peso cubano, con el objetivo de fortalecer su papel y preponderancia en el sistema monetario y financiero del país.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (47, 'XX00000047', 0, 20, 'Consolidar el marco regulatorio e institucional y el resto de las condiciones que permitan avanzar en el funcionamiento ordenado y eficiente de los mercados en función de incentivar la eficiencia, la competitividad y el fortalecimiento del papel de los precios.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (48, 'XX00000048', 0, 21, 'Consolidar un sistema financiero eficiente, solvente y diversificado, que asegure la sostenibilidad financiera del proceso de transformación estructural previsto en el Plan Nacional de Desarrollo Económico y Social.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (49, 'XX00000049', 0, 22, 'Incrementar gradualmente el poder adquisitivo de los ingresos provenientes del trabajo, manteniendo los equilibrios macroeconómicos fundamentales y el nivel de prioridad que requiere la recapitalización de la economía.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (50, 'XX00000050', 0, 23, 'Alcanzar una dinámica de crecimiento del PIB, y en consecuencia de la riqueza del país, que asegure un nivel de desarrollo sostenible, que conduzca al mejoramiento del bienestar de la población, con equidad y justicia social.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (51, 'XX00000051', 0, 24, 'Alcanzar mayores niveles de productividad y eficiencia en todos los sectores de la economía a partir de elevar el impacto de la ciencia, la tecnología y la innovación en el desarrollo económico y social, así como de la adopción de nuevos patrones de utilización de los factores productivos, modelos gerenciales y de organización de la producción.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (52, 'XX00000052', 1, 2, 'POLÍTICA MONETARIA', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (53, 'XX00000053', 0, 28, 'La planificación monetaria a corto, mediano y largo plazos deberá lograr el equilibrio monetario interno y externo, de manera integral.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (54, 'XX00000054', 0, 29, 'Regular la cantidad de dinero en circulación, a partir de lo establecido en el plan, con el fin de contribuir a lograr la estabilidad cambiaria, poder adquisitivo de la moneda y, con ello, el crecimiento ordenado de la economía.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (55, 'XX00000055', 0, 30, 'Establecer reglas adecuadas de emisión monetaria y utilizar oportunamente las herramientas analíticas para su medición y control.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (56, 'XX00000056', 0, 31, 'Fortalecer la utilización de los instrumentos de Política Monetaria para administrar  desequilibrios coyunturales, contribuir al ordenamiento monetario del país y al cumplimiento de las metas establecidas en el plan.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (57, 'XX00000057', 0, 32, 'Estructurar un sistema de tasas de interés más racional y fundamentado, así como establecer los mecanismos que permitan que la tasa de interés se constituya en un instrumento relevante del Sistema de Dirección de la Economía.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (58, 'XX00000058', 0, 33, 'La correspondencia entre el crecimiento de la cantidad de dinero en poder de la población y de la capacidad de absorción del Estado, así como la posibilidad de conducir esta relación de forma planificada, continuará siendo el instrumento clave para lograr la estabilidad monetaria y cambiaria en dicho sector, condición necesaria para avanzar en el restablecimiento del funcionamiento de la ley de distribución socialista, “de cada cual según su capacidad, a cada cual según su trabajo”.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (59, 'XX00000059', 0, 34, 'Dinamizar el crédito como mecanismo de impulso a la actividad económica del país y el fortalecimiento del mercado interno.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (60, 'XX00000060', 0, 35, 'Incrementar y diversificar las ofertas de crédito a la población en la medida que las condiciones del país lo permitan.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (61, 'XX00000061', 0, 36, 'Incrementar y diversificar las ofertas de productos bancarios a la población para estimular el ahorro y el acceso a los servicios financieros.\r\nIncrementar y diversificar las ofertas de productos bancarios a la población para estimular el ahorro y el acceso a los servicios financieros.\r\n', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (62, 'XX00000062', 0, 37, 'Perfeccionar los servicios bancarios necesarios al sector que opera bajo formas de gestión no estatales, para contribuir a su adecuado funcionamiento, en particular los dirigidos al desarrollo del sector agropecuario.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (63, 'XX00000063', 0, 38, 'Consolidar los mecanismos de regulación y supervisión del sistema financiero en función de los riesgos crecientes de esta actividad en el actual entorno económico.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (64, 'XX00000064', 0, 39, 'Avanzar en el desarrollo del sistema de pago y de los sistemas financieros, a fin de establecer una infraestructura de pagos eficiente y transparente. Intensificar el desarrollo de la bancarización en función del logro de estos objetivos.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (65, 'XX00000065', 1, 4, 'POLÍTICA FISCAL', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (66, 'XX00000066', 0, 42, 'La Política Fiscal deberá contribuir al incremento sostenido de la eficiencia de la economía y de los ingresos al Presupuesto del Estado, con el propósito de respaldar el gasto público en los niveles planificados y mantener un adecuado equilibrio financiero, tomando en cuenta las particularidades de nuestro modelo económico.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (67, 'XX00000067', 0, 43, 'Se ratifica el papel del Sistema Tributario como elemento redistribuidor del ingreso, basado en los principios de generalidad y equidad de la carga tributaria, a la vez que contribuya a la aplicación de las políticas encaminadas al perfeccionamiento del modelo económico. Tener en cuenta las características de los territorios.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (68, 'XX00000068', 0, 44, 'Perfeccionar los mecanismos que garanticen que la demanda de financiamiento del Presupuesto del Estado resulte congruente con el equilibrio financiero y que la magnitud de la deuda pública que se asuma a partir del déficit presupuestario esté acotada a la capacidad de la economía de generar ingresos futuros que permitan su amortización.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (69, 'XX00000069', 0, 45, 'Desarrollar el mercado de deuda pública a fin de incrementar la efectividad en el financiamiento del déficit fiscal.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (70, 'XX00000070', 0, 46, 'Perfeccionar y ampliar los mecanismos para la inversión financiera del Presupuesto del Estado en el sector productivo, garantizando que sea rentable.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (71, 'XX00000071', 0, 47, 'Perfeccionar y ampliar los fondos presupuestarios para el apoyo financiero a las actividades que se requieran fomentar en interés del desarrollo económico y social del país.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (72, 'XX00000072', 0, 48, 'Continuar avanzando en la aplicación de estímulos fiscales que promuevan el desarrollo ordenado de las formas de gestión no estatales.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (73, 'XX00000073', 0, 49, 'Perfeccionar la aplicación de estímulos fiscales que promuevan producciones nacionales en sectores claves de la economía, especialmente a los fondos exportables y a los que sustituyen importaciones, al desarrollo local y la protección del medio ambiente.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (74, 'XX00000074', 0, 50, 'Actualizar el papel del Sistema Arancelario dentro del modelo económico, priorizando los regímenes arancelarios preferenciales y las bonificaciones que se consideren convenientes otorgar, bajo el principio de que los fondos exportables y las producciones que sustituyan importaciones deben ser rentables.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (75, 'XX00000075', 0, 51, 'Fomentar la cultura tributaria y la responsabilidad social de la población, entidades y formas de gestión no estatales del país, en el cumplimiento cabal de las obligaciones tributarias, para desarrollar el valor cívico de contribución al sostenimiento de los gastos sociales y altos niveles de disciplina fiscal.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (76, 'XX00000076', 1, 5, 'POLÍTICA DE PRECIOS', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (77, 'XX00000077', 0, 55, 'Establecer un Sistema de Precios que permita medir correctamente los hechos económicos, estimule la producción, la eficiencia, el incremento de las exportaciones y la sustitución de importaciones, así como trasladar las señales del mercado a los productores.', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (78, 'XX00000078', 0, 56, 'Mantener centralizados los precios mayoristas y minoristas de un grupo de productos y servicios esenciales que permitan respaldar las políticas sociales y las necesidades básicas de la población.', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (79, 'XX00000079', 0, 57, 'Garantizar por parte del Estado métodos efectivos de control directo e indirecto, de precios mayoristas y minoristas. Lograr que los precios minoristas sean continuidad de los mayoristas y aseguren la correspondencia con la calidad.', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (80, 'XX00000080', 0, 58, 'Continuar avanzando en el cumplimiento del principio de subsidiar personas y no productos, así como en la eliminación de subsidios. Se podrán mantener algunos niveles de estos, para garantizar determinados productos o servicios de uso masivo que lo requieran.', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (81, 'XX00000081', 0, 59, 'Los precios mayoristas deben constituirse en el vehículo principal para la asignación de recursos en la economía, minimizando el uso de mecanismos administrativos.', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (82, 'XX00000082', 0, 60, 'Los precios minoristas que se forman al amparo de las regulaciones estatales, deben ser continuidad de los mayoristase incluir los márgenes comerciales y los tributos que correspondan.', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (83, 'XX00000083', 1, 3, 'III. POLÍTICA ECONÓMICA EXTERNA', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (84, 'XX00000084', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (85, 'XX00000085', 0, 62, 'Consolidar la credibilidad del país en sus relaciones económicas internacionales, mediante el estricto  cumplimiento de los compromisos contraídos.', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (86, 'XX00000086', 0, 63, 'Continuar prestando la máxima atención a la selección y al control de los cuadros, funcionarios y empresarios que intervienen en las relaciones económicas externas, de manera especial, a la conducta ética acorde con los principios de la Revolución y la preparación técnica, en aspectos económicos, financieros, y jurídicos, entre otros.', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (87, 'XX00000087', 0, 64, 'Aplicar el principio de quien decide no negocia en toda la actividad que desarrolle el país en el plano de las relaciones económicas internacionales.', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (88, 'XX00000088', 0, 65, 'Promover, siempre que se justifique económicamente y resulte conveniente, el establecimiento de empresas y alian zas en el exterior, que propicien el mejor posicionamiento de los intereses de Cuba en los mercados externos.', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (89, 'XX00000089', 1, 2, 'COMERCIO EXTERIOR', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (90, 'XX00000090', 0, 66, 'Garantizar la aplicación integral de las políticas Comercial, Fiscal, Crediticia, Arancelaria, Laboral y otras; así como consolidar los mecanismos de protección de precios de los productos que se cotizan en bolsa y que Cuba comercializa.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (91, 'XX00000091', 0, 67, 'Elevar la eficiencia en la gestión de las empresas vinculadas al comercio exterior para incrementar y consolidar los ingresos por concepto de exportaciones de bienes y servicios; crear una real vocación exportadora a todos los niveles, fundamentar con estudios de mercado las decisiones más importantes y estratégicas; continuar la flexibilización de la participación de las entidades nacionales en el comercio exterior.\r\n', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (92, 'XX00000092', 0, 68, 'Diversificar los destinos de los bienes y servicios exportables, con preferencia en los de mayor valor agregado y contenido tecnológico, además de mantener la prioridad y atención a los principales socios del país, y lograr mayor estabilidad en la obtención de ingresos.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (93, 'XX00000093', 0, 69, 'Continuar desarrollando la exportación de servicios, en particular los profesionales, que priorice la venta de proyectos o soluciones tecnológicas, y contemple el análisis flexible de la contratación de la fuerza de trabajo individual.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (94, 'XX00000094', 0, 70, 'Acelerar el desarrollo de los Servicios Médicos y de Salud Cubanos y continuar ampliando los mercados para su exportación.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (95, 'XX00000095', 0, 71, 'Continuar diversificando los mercados de exportación de langostas y camarones, incorporando mayor valor agregado al producto.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (96, 'XX00000096', 0, 72, 'Trabajar para garantizar, por las empresas y entidades vinculadas a la exportación, que todos los bienes y servicios destinados a los mercados internacionales respondan a los más altos estándares de calidad.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (97, 'XX00000097', 0, 73, 'Incrementar la eficiencia en la gestión importadora del país, haciendo énfasis en la disponibilidad oportuna de las importaciones, su racionalidad, el uso eficaz del poder de compra y el desarrollo del mercado mayorista.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (98, 'XX00000098', 0, 74, 'Promover acuerdos internacionales de cooperación y complementación en el sector industrial que favorezcan las exportaciones de mayor valor agregado y la sustitución de importaciones, con un mejor aprovechamiento de las capacidades\r\nnacionales.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (99, 'XX00000099', 0, 75, 'Establecer los mecanismos para canalizar las demandas de importación que surjan de las formas de propiedad y gestión no estatales, así como viabilizar la realización de potenciales fondos exportables.', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (106, 'XX00000106', 1, 3, 'DEUDA Y CRÉDITO', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (107, 'XX00000107', 0, 76, 'Continuar el proceso de reordenamiento de la deuda externa, aplicando estrategias de pago flexibles, de modo que se garantice  estrictamente el cumplimiento de los compromisos, para contribuir al desempeño creciente y sostenido de la economía, así como al acceso a nuevos financiamientos.', 2012, 2018, 83, 106, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (108, 'XX00000108', 0, 77, 'Garantizar que los compromisos que se adquieran en el reordenamiento de las deudas se cumplan estrictamente. ', 2012, 2018, 83, 106, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (111, 'XX00000111', 1, 4, 'INVERSIÓN EXTRANJERA', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (112, 'XX00000112', 0, 78, 'Incrementar la participación del capital extranjero como una fuente importante para el desarrollo del país. Considerarlo en determinados sectores y actividades económicos como un elemento fundamental.', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (113, 'XX00000113', 0, 79, 'Favorecer, en el proceso de promoción de inversiones, la diversificación de la participación de diferentes países.', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (114, 'XX00000114', 0, 80, 'Ampliar y mantener actualizada una cartera de proyectos de oportunidades de inversión extranjera, en correspondencia\r\ncon las actividades, sectores priorizados y los territorios.', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (115, 'XX00000115', 0, 81, 'Consolidar la Zona Especial de Desarrollo Mariel y promover la creación de nuevas, de acuerdo con el desarrollo de\r\nla economía.', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (124, 'XX00000124', 1, 5, 'COOPERACIÓN', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (125, 'XX00000125', 0, 82, 'Consolidar el proceso de incorporación al Plan de la Economía Nacional y el Presupuesto del Estado, de las acciones de cooperación internacional que Cuba recibe y ofrece, que demanden recursos materiales y financieros adicionales.', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (126, 'XX00000126', 0, 83, 'Culminar la implementación del marco legal y regulatorio para la cooperación económica y científico-técnica que Cuba recibe y ofrece.', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (127, 'XX00000127', 0, 84, 'Continuar desarrollando la solidaridad internacional a través de la cooperación que Cuba ofrece; considerando, en la medida que sea posible, la compensación, al menos, de sus costos.', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (128, 'XX00000128', 0, 85, 'Promover la cooperación económica que se recibe del exterior, destinada a la atracción de recursos financieros y tecnología, de acuerdo con las prioridades que se establezcan en el Plan Nacional de Desarrollo Económico y Social hasta 2030. Potenciar la vía multilateral, en especial con instituciones del Sistema de las Naciones Unidas.', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (131, 'XX00000131', 1, 6, 'INTEGRACIÓN ECONÓMICA', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (132, 'XX00000132', 0, 86, 'Dar prioridad a la participación en la Alianza Bolivariana para los Pueblos de Nuestra América (ALBA) y trabajar con celeridad e intensamente en la coordinación, cooperación y complementación económica a corto, mediano y largo plazos, para el logro y profundización de los objetivos económicos, sociales y políticos que promueve.', 2012, 2018, 83, 131, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (133, 'XX00000133', 0, 87, 'Continuar la participación activa en la integración económica con América Latina y el Caribe, como objetivo estratégico, y mantener la participación en los esquemas regionales de integración comercial en que Cuba logró articularse: Asociación Latinoamericana de Integración (Aladi), Comunidad del Caribe (Caricom), Asociación de Estados del Caribe (AEC), Petrocaribe y otros; y continuar fortaleciendo la unidad entre sus miembros.', 2012, 2018, 83, 131, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (134, 'XX00000134', 1, 4, 'IV. POLITICA INVERSIONISTA', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (135, 'XX00000135', 0, 88, 'Las inversiones fundamentales a realizar responderán a la estrategia de desarrollo del país a corto, mediano y largo \r\nplazos, erradicando la espontaneidad, la improvisación, la superficialidad, el incumplimiento de los planes, la falta de profundidad\r\nen los estudios de factibilidad, la inmovilización de recursos y la carencia de integralidad al emprender una inversión.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (136, 'XX00000136', 0, 89, 'Continuar orientando las inversiones hacia la esfera productiva y de los servicios, así como a la infraestructura necesaria\r\npara el desarrollo sostenible, garantizando su aseguramiento oportuno, para generar beneficios a corto plazo. Se priorizarán las actividades de mantenimiento constructivo y tecnológico en todas las esferas de la economía.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (137, 'XX00000137', 0, 90, 'Elevar la exigencia y el control a los inversionistas para que jerarquicen la atención integral y garanticen la calidad del\r\nproceso inversionista e incentivar el acortamiento de plazos, el ahorro de recursos y presupuesto en las inversiones.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (138, 'XX00000138', 0, 91, 'Se elevará la calidad y la jerarquía de los planes generales de ordenamiento territorial y urbano a nivel nacional, provincial\r\ny municipal, su integración con las proyecciones a mediano y largo plazos de la economía y con el Plan de Inversiones, garantizando la profundidad y agilidad en los plazos de respuesta en los procesos obligados de consulta.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (139, 'XX00000139', 0, 92, 'Continuar el proceso de descentralización del Plan de Inversiones y cambio en su concepción, otorgándoles facultades de aprobación de las inversiones a los organismos de la Administración Central del Estado, a los consejos de la administración, al Sistema Empresarial y unidades presupuestadas.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (140, 'XX00000140', 0, 93, 'Las inversiones que se aprueben, como política, demostrarán que son capaces de recuperarse con sus propios resultados y deberán realizarse con créditos externos preferiblemente a mediano y largo plazos o capital propio, cuyo reembolso se efectuará a partir de los recursos generados por la propia inversión.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (141, 'XX00000141', 0, 94, 'Se continuarán asimilando e incorporando nuevas técnicas de dirección del proceso inversionista y también de entidades proyectistas y constructoras en asociaciones económicas internacionales. Valorar, siempre que sea necesario, la participación de constructores y proyectistas extranjeros para garantizar la ejecución de inversiones cuya complejidad e importancia lo requieran.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (142, 'XX00000142', 0, 95, 'Generalizar la licitación de los servicios de diseno y construcción entre entidades cubanas. Elaborar las regulaciones para ello.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (143, 'XX00000143', 0, 96, 'Las inversiones de infraestructura como norma se desarrollarán con financiamiento a largo plazo y la inversión extranjera.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (144, 'XX00000144', 0, 97, 'Implementar acciones que permitan el completamiento y preparación de la fuerza de trabajo para continuar avanzando en el restablecimiento de la disciplina territorial y urbana. Simplificar y agilizar los trámites de la población para la obtención de la documentación requerida en los procesos de construcción, remodelación y rehabilitación de viviendas y locales.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (148, 'XX00000148', 1, 5, 'V. POLÍTICA DE CIENCIA, TECNOLOGÍA, INNOVACIÓN Y MEDIO AMBIENTE', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (149, 'XX00000149', 0, 98, 'Situar en primer plano el papel de la ciencia, la tecnología y la innovación en todas las instancias, con una visión que\r\nasegure lograr a corto y mediano plazos los objetivos del Plan Nacional de Desarrollo Económico y Social.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (150, 'XX00000150', 0, 99, 'Continuar desarrollando el marco jurídico y regulatorio que propicie la introducción sistemática y acelerada de los resultados\r\nde la ciencia, la innovación y la tecnología en los procesos productivos y de servicios, y el cumplimiento de las normas\r\nde responsabilidad social y medioambiental establecidas.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (151, 'XX00000151', 0, 100, 'Continuar reordenando las entidades de ciencia, tecnología e innovación que están en función de la producción y los servicios hacia su transformación en empresas, pasando a formar parte de estas o de las organizaciones superiores de dirección empresarial, en todos los casos que resulte posible.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (152, 'XX00000152', 0, 101, 'Implementar las políticas de los sistemas de ciencia, tecnología, innovación y medio ambiente facilitando la interacción en sus ámbitos respectivos, incrementando su impacto en todas las esferas de la economía y la sociedad a corto, mediano y largo plazos. Asegurar el respaldo económico-financiero de cada sistema en correspondencia con la naturaleza y objetivos de sus actividades.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (153, 'XX00000153', 0, 102, 'Sostener y desarrollar los resultados alcanzados en el campo de la biotecnología, la producción médico-farmacéutica, las ciencias básicas, las ciencias naturales, las ciencias agropecuarias, los estudios y el empleo de las fuentes renovables de energía, las tecnologías sociales y educativas, la transferencia tecnológica industrial, la producción de equipos de tecnología avanzada, la nanotecnología y los servicios científicos y tecnológicos de alto valor agregado.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (154, 'XX00000154', 0, 103, 'Continuar fomentando el desarrollo de investigaciones sociales y humanísticas sobre los asuntos prioritarios de la vida de la sociedad, así como perfeccionando los métodos de introducción de sus resultados en la toma de decisiones a los diferentes niveles.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (155, 'XX00000155', 0, 104, 'Prestar mayor atención en la formación y capacitación continuas del personal técnico y cuadros calificados que respondan y se anticipen al desarrollo científico-tecnológico en las principales áreas de la producción y los servicios, así como a la prevención y mitigación de impactos sociales y medioambientales.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (156, 'XX00000156', 0, 105, 'Actualizar las vías existentes y definir e impulsar otras para estimular la creatividad de los colectivos laborales de base y fortalecer su participación en la solución de los problemas tecnológicos de la producción y los servicios y la promoción de formas productivas ambientalmente sostenibles.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (157, 'XX00000157', 0, 106, 'Asegurar la estabilidad, el completamiento y rejuvenecimiento del potencial científico-tecnológico de los sistemas de ciencia, tecnología, innovación y medio ambiente para retomar su crecimiento selectivo, escalonado, proporcionado y sostenible. Perfeccionar los diferentes mecanismos de estimulación.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (158, 'XX00000158', 0, 107, 'Acelerar la implantación de las directivas y de los programas de ciencia, tecnología e innovación, dirigidos al enfrentamiento\r\ndel cambio climático, por todos los organismos y entidades, integrando todo ello a las políticas territoriales y sectoriales, con prioridad en los sectores agropecuario, hidráulico y de la salud. Elevar la información y capacitación que contribuya a objetivizar la percepción de riesgo a escala de toda la sociedad.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (159, 'XX00000159', 0, 108, 'Avanzar gradualmente, según lo permitan las posibilidades económicas, en el proceso de informatización de la sociedad, el desarrollo de la infraestructura de telecomunicaciones y la industria de aplicaciones y servicios informáticos. Sustentarlo en un sistema de ciberseguridad que proteja nuestra soberanía tecnológica y asegure el enfrentamiento al uso ilegal de las tecnologías de la información y la comunicación. Instrumentar mecanismos de colaboración internacional en este campo.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (160, 'XX00000160', 1, 6, 'VI. POLÍTICA SOCIAL', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (161, 'XX00000161', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (162, 'XX00000162', 0, 113, 'Impulsar el desarrollo integral y pleno de los seres humanos. Continuar consolidando las conquistas de la Revolución, tales como el acceso a la atención médica, la educación, la cultura, el deporte, la recreación, la justicia, la tranquilidad ciudadana, la seguridad social y la protección mediante la Asistencia Social a las personas que lo necesiten. Promover y reafirmar la adopción de los valores, prácticas y actitudes que deben distinguir a nuestra sociedad.', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (163, 'XX00000163', 0, 114, 'Aunar los esfuerzos de las instituciones educativas, culturales, organizaciones políticas, de masas, las formas asociativas\r\nsin ánimo de lucro y de los medios de comunicación masiva, en todas sus expresiones y de aquellos factores que influyen en la comunidad y en la familia, para cultivar en la sociedad el conocimiento de nuestra historia, cultura e identidad, y al propio tiempo la capacidad para asumir una posición crítica y descolonizada ante los productos de la industria cultural hegemónica capitalista.', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (164, 'XX00000164', 0, 115, 'Dar continuidad al perfeccionamiento de la educación, la salud, la cultura y el deporte, para lo cual resulta imprescindible\r\nreducir o eliminar gastos excesivos en la esfera social, así como generar nuevas fuentes de ingreso y evaluar todas las actividades que puedan pasar del sector presupuestado al sistema empresarial.', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (165, 'XX00000165', 1, 7, 'DINAMICA DEMOGRAFICA', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (166, 'XX00000166', 0, 116, 'Garantizar la implantación gradual de la política para atender los elevados niveles de envejecimiento de la población. Estimular la fecundidad con el fin de acercarse al remplazo poblacional en una perspectiva mediata. Continuar estudiando este tema con integralidad.', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (167, 'XX00000167', 1, 2, 'EDUCACIÓN', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (168, 'XX00000168', 0, 117, 'Continuar avanzando en la elevación de la calidad y el rigor del proceso docente-educativo, así como en el fortalecimiento del papel del profesor frente al alumno; incrementar la eficiencia del ciclo escolar, jerarquizar la superación permanente, el enaltecimiento y atención del personal docente, el mejoramiento de las condiciones de trabajo y el perfeccionamiento del papel de la familia en la educación de niños, adolescentes y jóvenes.', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (169, 'XX00000169', 0, 118, 'Formar con calidad y rigor el personal docente que se precisa en cada provincia y municipio para dar respuesta a las necesidades de los centros educativos de los diferentes niveles de enseñanza.', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (170, 'XX00000170', 0, 119, 'Avanzar en la informatización del sistema de educación. Desarrollar los servicios en el uso de la red telemática y la tecnología educativa de forma racional, así como la generación de contenidos digitales y audiovisuales.', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (171, 'XX00000171', 0, 120, 'Ajustar la capacidad de la red escolar y el personal docente en la educación primaria, y ampliar las capacidades de los círculos infantiles en correspondencia con el desarrollo económico, sociodemográfico y los lugares de residencia. Brindar especial atención al Plan Turquino.', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (172, 'XX00000172', 0, 121, 'Lograr que las matrículas en las diferentes especialidades y carreras estén en correspondencia con el desarrollo de la economía y la sociedad, incrementar la matrícula en carreras agropecuarias, pedagógicas, tecnológicas y de ciencias básicas afines. Garantizar de conjunto con las entidades de la producción y los servicios, las organizaciones políticas, estudiantiles y de masas y con la articipación de la familia, la formación vocacional y orientación profesional, desde la primaria. Continuar potenciando el reconocimiento a la labor de\r\nlos técnicos medios y obreros calificados.', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (173, 'XX00000173', 0, 122, 'Consolidar el cumplimiento de la responsabilidad de los organismos, entidades, consejos de la administración y otros actores económicos, en la formación y desarrollo de la fuerza de trabajo calificada. Actualizar los programas de formación e investigación de las universidades en función de las necesidades del desarrollo, la actualización del Modelo Económico y Social y de las nuevas tecnologías.', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (177, 'XX00000177', 1, 3, 'SALUD', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (178, 'XX00000178', 0, 123, 'Elevar la calidad del servicio que se brinda, el cumplimiento de la ética médica, lograr la satisfacción de la población, así como el mejoramiento de las condiciones de trabajo y la atención al personal de la salud. Garantizar la utilización eficiente de los recursos, el ahorro y la eliminación de gastos innecesarios.', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (179, 'XX00000179', 0, 124, 'Fortalecer las acciones de salud con la participación intersectorial y comunitaria en la promoción y prevención para el mejoramiento del estilo de vida, que contribuyan a incrementar los niveles de salud de la población.', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (180, 'XX00000180', 0, 125, 'Garantizar la sostenibilidad de las acciones interdisciplinarias, sectoriales, intersectoriales y comunitarias dirigidas al mejoramiento de las condiciones higiénico-epidemiológicas que determinan las enfermedades transmisibles que más impactan en el cuadro de salud, y afectan el medio ambiente, con énfasis en las enfermedades de transmisión hídrica, por alimentos y por vectores.', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (181, 'XX00000181', 0, 126, 'Dar continuidad al proceso de reorganización, compactación y regionalización de los servicios de salud, con la calidad necesaria, incluyendo la atención de urgencias y el transporte sanitario, a partir de las necesidades de cada provincia y municipio. Garantizar que el propio Sistema de Salud facilite que cada paciente reciba la atención correspondiente.', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (182, 'XX00000182', 0, 127, 'Consolidar la ensenanza y el empleo del método clínico y epidemiológico y el estudio del entorno social en el abordaje de los problemas de salud de la población, de manera que contribuyan al uso racional y eficiente de los recursos para el diagnóstico y tratamiento de las enfermedades.\r\n', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (183, 'XX00000183', 0, 128, 'Consolidar la implantación del Programa Nacional de Medicamentos y la eficiencia de los servicios farmacéuticos.', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (184, 'XX00000184', 0, 129, 'Asegurar el cumplimiento del Plan de Acciones para garantizar el desarrollo y consolidación de la Medicina Natural y Tradicional.', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (185, 'XX00000185', 1, 4, 'DEPORTE', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (186, 'XX00000186', 0, 131, 'Priorizar el perfeccionamiento integral del sistema deportivo cubano, atemperado a las condiciones tanto nacionales como internacionales. Continuar promoviendo el desarrollo de la cultura física y lograr la práctica masiva del deporte que contribuya a elevar la calidad de vida de la población, teniendo a la escuela como eslabón fundamental. Mantener resultados satisfactorios en los eventos internacionales.', 2012, 2018, 160, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (187, 'XX00000187', 0, 132, 'Elevar la calidad y el rigor en la formación de atletas y docentes, desde la escuela-combinado deportivo y centro de alto rendimiento; desarrollar la participación de estos en eventos en el país y en el exterior en todas las categorías; sustentar su preparación en la educación en valores y en los avances de la ciencia y la innovación tecnológica. Continuar mejorando la infraestructura de la red de instalaciones deportivas.', 2012, 2018, 160, 185, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (188, 'XX00000188', 1, 5, 'CULTURA', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (189, 'XX00000189', 0, 133, 'Fortalecer el papel de la cultura en los nuevos escenarios a partir de continuar fomentando la defensa de la identidad, así como la creación artística y literaria y la capacidad para apreciar el arte: promover la lectura, enriquecer la vida cultural de la población y potenciar el trabajo comunitario, como vías para satisfacer las necesidades espirituales y defender los valores de nuestro socialismo.', 2012, 2018, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (190, 'XX00000190', 0, 134, 'Garantizar la defensa del patrimonio cultural, material e inmaterial de la nación cubana.', 2012, 2018, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (191, 'XX00000191', 1, 6, 'SEGURIDAD SOCIAL', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (192, 'XX00000192', 0, 137, 'Disminuir la participación relativa del Presupuesto del Estado en el financiamiento de la seguridad social, la que continuará\r\ncreciendo a partir del incremento del número de personas jubiladas, por lo que es necesario seguir extendiendo la contribución de los trabajadores del sector estatal y la aplicación de regímenes especiales de contribución en el sector no estatal.', 2012, 2018, 160, 191, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (193, 'XX00000193', 0, 138, 'Garantizar que la protección de la asistencia social la reciban las personas que realmente la necesitan, estén impedidas para el trabajo y no cuenten con familiares que brinden apoyo. Continuar consolidando y perfeccionando el Sistema de Prevención, Asistencia y Trabajo Social.', 2012, 2018, 160, 191, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (194, 'XX00000194', 1, 7, 'EMPLEO Y SALARIOS', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (195, 'XX00000195', 0, 139, 'Rescatar el papel del trabajo y los ingresos que por él se obtienen como vía principal para generar productos y servicios de calidad e incremento de la producción y la productividad, y lograr la satisfacción de las necesidades fundamentales de los trabajadores y su familia.', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (196, 'XX00000196', 0, 140, 'Favorecer la incorporación al empleo de las personas en condiciones de trabajar, como forma de contribuir a los fines de la sociedad y a la satisfacción de sus necesidades.', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (197, 'XX00000197', 0, 141, 'Ampliar el trabajo en el sector no estatal, como una alternativa más de empleo, en dependencia de las nuevas formas organizativas de la producción y los servicios que se establezcan.', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (198, 'XX00000198', 0, 142, 'El incremento de los ingresos en el sector empresarial será según la creación de la riqueza y las posibilidades económico- financieras de las empresas, promoviendo la evaluación sistemática de sus resultados de conjunto con el movimiento sindical. En el presupuestado se hará gradualmente, en correspondencia con las prioridades que se establezcan y las posibilidades de la economía.', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (199, 'XX00000199', 0, 143, 'Proyectar la formación de fuerza de trabajo calificada en correspondencia con el Plan Nacional de Desarrollo Económico y Social, a mediano y largo plazos.', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (201, 'XX00000201', 1, 8, 'GRATUIDADES Y SUBSIDIOS', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (202, 'XX00000202', 0, 144, 'Continuar, en correspondencia con la situación económica del país y los ingresos de las personas, el proceso de eliminación gradual de gratuidades indebidas y subsidios excesivos bajo el principio de subsidiar a las personas necesitadas y no productos.', 2012, 2018, 160, 201, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (203, 'XX00000203', 0, 145, 'Dar continuidad a la eliminación ordenada y gradual de los productos de la libreta de abastecimiento, como forma de distribución normada, igualitaria y a precios subsidiados.', 2012, 2018, 160, 201, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (204, 'XX00000204', 0, 146, 'Mantener la alimentación que se brinda en la esfera de los servicios sociales, dando prioridad a las instituciones de salud y centros educacionales que lo requieran. Perfeccionar las vías para proteger a la población vulnerable o de riesgo en la alimentación.\r\n', 2012, 2018, 160, 201, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (206, 'XX00000206', 1, 7, 'VII POLÍTICA AGROINDUSTRIAL ', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (207, 'XX00000207', 0, 147, 'Lograr que la producción agroindustrial contribuya al desarrollo de la economía del país y se exprese en un aumento de su participación en el Producto Interno Bruto, con una mayor oferta de alimentos con destino al consumo interno, la disminución de importaciones y el incremento de las exportaciones. Disminuir la alta dependencia de financiamiento que hoy se cubre con los ingresos de otros sectores.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (208, 'XX00000208', 0, 148, 'Continuar transformando el modelo de gestión, en correspondencia con la mayor presencia de formas productivas no estatales, en el que la empresa estatal agropecuaria se constituya en el gestor principal del desarrollo tecnológico y de las estrategias de producción y comercialización. Utilizar de manera efectiva las relaciones monetario-mercantiles y consolidar la autonomía otorgada a los productores, para incrementar la eficiencia y la competitividad.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (209, 'XX00000209', 0, 149, 'Lograr que los productores agropecuarios cuenten con un programa de desarrollo, en correspondencia con la estrategia del país. Introducir de forma gradual las cooperativas de servicios en la actividad agroindustrial.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (210, 'XX00000210', 0, 150, 'Garantizar el servicio bancario especializado al sector agroindustrial, que tenga en cuenta los ciclos de producción y el nivel de riesgos. Fortalecer y ampliar la actividad de seguros agropecuarios, propiciando una mayor eficacia en su aplicación.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (211, 'XX00000211', 0, 151, 'Continuar la transformación del sistema de comercialización de insumos, equipamientos y servicios, que garantice el acceso directo de los productores al mercado, según su eficiencia y capacidad financiera, asegurando la disponibilidad y oportunidad de los recursos con una adecuada correspondencia entre la calidad y los precios.\r\n', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (212, 'XX00000212', 0, 152, 'Continuar la transformación de la comercialización de productos agropecuarios que se está experimentando, evaluando los resultados alcanzados y adoptando las medidas necesarias para superar las dificultades que se presenten, en particular priorizar el pago a los productores en los plazos establecidos; perfeccionar e integrar todos los elementos del sistema —producción, acopio y comercialización—, para contribuir a mejorar la oferta y la satisfacción de la población, en cuanto a precios, calidad y estabilidad. Desarrollar progresivamente la oferta de servicios complementarios.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (213, 'XX00000213', 0, 153, 'Perfeccionar la acción reguladora del Estado y los procedimientos en la formación del precio de acopio de los productos agropecuarios, para estimular a los productores primarios. Se tendrá en cuenta el comportamiento de los precios en el mercado internacional.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (214, 'XX00000214', 0, 154, 'Desarrollar una política integral que estimule la incorporación, permanencia y estabilidad de la fuerza laboral en el campo, en especial de jóvenes y mujeres, así como la recuperación y desarrollo de las comunidades agrícolas, para que simultáneamente con la introducción de las nuevas tecnologías en la agricultura, garanticen el incremento de la producción agropecuaria.\r\n', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (215, 'XX00000215', 0, 155, 'Disenar y aplicar servicios de asistencia técnica, capacitación y extensión agraria, para asimilar eficientemente las nuevas tecnologías que contribuyan a una mejor organización de la fuerza laboral, aseguren el aumento de la productividad y tengan en cuenta las transformaciones ocurridas y proyectadas en el sector.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (216, 'XX00000216', 0, 156, 'Desarrollar una agricultura sostenible empleando una gestión integrada de ciencia, tecnología y medio ambiente, aprovechando\r\ny fortaleciendo las capacidades disponibles en el país, además que reconozca las diversas escalas productivas. ', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (217, 'XX00000217', 0, 157, 'Priorizar la conservación, protección y mejoramiento de los recursos naturales, entre ellos, el suelo, el agua y los recursos\r\nzoo y fitogenéticos. Recuperar la producción de semillas de calidad, la genética animal y vegetal; así como el empleo de productos biológicos nacionales.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (218, 'XX00000218', 0, 158, 'Sostener y desarrollar investigaciones integrales para proteger, conservar y rehabilitar el medio ambiente, evaluar impactos económicos y sociales de eventos extremos, y adecuar la política ambiental a las proyecciones del entorno económico y social. Ejecutar programas para la conservación, rehabilitación y uso racional de recursos naturales. Fomentar los procesos de educación ambiental, considerando todos los actores de la sociedad.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (219, 'XX00000219', 0, 159, 'Asegurar un efectivo y sistemático control estatal sobre la tenencia y el uso de la tierra, que contribuya a su explotación\r\neficiente y al incremento sostenido de las producciones. Continuar la entrega de tierras en usufructo y la reducción de las áreas ociosas.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (220, 'XX00000220', 0, 160, 'Continuar priorizando la producción de alimentos que puedan ser obtenidos eficientemente en el país. Los recursos e inversiones bajo el principio de encadenamientos productivos, necesarios para ello, deberán destinarse a donde existan mejores condiciones para su empleo más efectivo.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (221, 'XX00000221', 0, 161, 'Continuar la reorganización y el desarrollo de las actividades de riego, drenaje, abasto de agua a los animales y los servicios de maquinaria agropecuaria con el objetivo de lograr el uso racional del agua, de la infraestructura hidráulica y de los equipos agropecuarios, contribuir al incremento de la productividad y al ahorro de fuerza de trabajo, combinando el uso de la tracción animal con tecnologías de avanzada. Garantizar los servicios de mantenimiento y reparaciones.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (222, 'XX00000222', 0, 162, 'Organizar la producción en los polos productivos agropecuarios encargados de abastecer las grandes ciudades y la industria alimentaria, lograr una efectiva sustitución de importaciones e incrementar las exportaciones, aplicando un enfoque de cadena productiva de todos los eslabones que se articulan en torno al complejo agroindustrial, con independencia a la organización empresarial a la que se vinculen.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (223, 'XX00000223', 0, 163, 'En la organización de la producción agropecuaria, destinada fundamentalmente al consumo interno, deberá predominar un enfoque territorial, integrándose con las mini-industrias, las que además podrán vincularse a la industria, con el objetivo de lograr una mayor eficiencia, aumentar la calidad y presentación; ahorrar transporte y gastos de distribución.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (224, 'XX00000224', 0, 164, 'Desarrollar la política ganadera, priorizando las especies vacuna, porcina y avícola. La ganadería vacuna debe sustentarse en el aprovechamiento del fondo de tierras, la recuperación de la infraestructura, los pastos y los forrajes, así como el mejoramiento genético de los rebaños y la elevación de los rendimientos, para incrementar la producción de leche y carne, haciendo un uso eficiente de la mecanización. Perfeccionar el control de la masa, asegurar el servicio veterinario, la producción de medicamentos y la biotecnología reproductiva. Desarrollar el ganado menor en las regiones del país con condiciones favorables para ello.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (225, 'XX00000225', 0, 165, 'Incrementar la producción de viandas y hortalizas con una adecuada estructura de cultivos, sobre la base de aumentar los rendimientos y lograr una mejor utilización del balance de áreas de cultivos varios. 166. Asegurar el cumplimiento de los programas de producción de arroz, frijol, maíz y otros granos que garanticen el incremento productivo, para contribuir a la reducción gradual de las importaciones de estos productos y aumentar el consumo.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (226, 'XX00000226', 0, 166, 'Asegurar el cumplimiento de los programas de produccion de arroz, frijol, maiz y otros granos que garanticen el incremento productivo, para \r\r\ncontribuir a la reducción gradual de las importaciones de estos productos y aumentar el consumo.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (227, 'XX00000227', 0, 167, 'Impulsar el desarrollo de las actividades tabacalera, cafetalera, apícola, del cacao y otros rubros, para contribuir a la recuperación gradual de \r\r\nlas exportaciones. En la producción tabacalera explotar al máximo las posibilidades del mercado externo.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (228, 'XX00000228', 0, 168, 'Reanimar la agroindustria citrícola. Continuar el incremento y diversificación de la producción de frutales, asegurar el acopio y \r\r\ncomercialización eficiente de las frutas frescas e industrializadas en los mercados nacional e internacional.\r\n', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (229, 'XX00000229', 0, 169, ' Desarrollar un programa integral de mantenimiento, conservación y fomento de plantaciones forestales que priorice la protección de las cuencas \r\r\nhidrográficas, en particular las presas, las franjas hidrorreguladoras, las montañas y las costas; así como incrementar las plantaciones en el llano y \r\r\nla premontaña, aumentar la producción de madera y otros productos del bosque.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (230, 'XX00000230', 0, 170, 'Continuar desarrollando el programa de autoabastecimiento alimentario municipal, apoyándose en la agricultura urbana y suburbana,\r\naprovechando los recursos locales y la tracción animal.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (231, 'XX00000231', 0, 171, 'Desarrollar la industria alimentaria y de bebidas, incluyendo la actividad local, en función de lograr un mayor aprovechamiento de las materias primas, la diversificación de la producción y el incremento de la oferta al mercado interno y de las exportaciones.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (232, 'XX00000232', 0, 172, 'Aplicar los sistemas de gestión de la calidad en correspondencia con las normas establecidas y las exigencias de los clientes, para asegurar, \r\r\nentre otros objetivos, la inocuidad de los alimentos.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (233, 'XX00000233', 0, 173, 'La agroindustria de la cana de azúcar, como sector estratégico deberá continuar incrementando su eficiencia agrícola e industrial, así como \r\r\naumentar la producción de caña, modernizar el equipamiento y mejorar el aprovechamiento de la capacidad de molida.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (234, 'XX00000234', 0, 174, 'Aumentar de forma gradual la producción de azúcar, diversificar las producciones teniendo en cuenta las exigencias del mercado internacional e \r\r\ninterno, y avanzar en la creación, recuperación y explotación de las plantas de derivados, priorizando las destinadas a la obtención de alcohol, \r\r\nalimento animal y los bioproductos. Continuar incrementando la entrega de electricidad al Sistema lectroenergético Nacional.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (235, 'XX00000235', 0, 175, 'Incrementar la eficiencia de las pesquerías cumpliendo las regulaciones pesqueras. Modernizar las embarcaciones y emplear artes de pesca \r\r\nselectivas que garanticen la calidad de las capturas y la preservación del medio marino y costero. Incrementar los ingresos por exportaciones, \r\r\nfundamentalmente en el camarón de cultivo.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (236, 'XX00000236', 0, 176, 'Desarrollar la acuicultura aplicando técnicas modernas de cultivo, con elevada disciplina tecnológica y mejora constante de la genética. \r\r\nReanimar la industria pesquera e incrementar la oferta, variedad y calidad de productos al mercado interno.', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (243, 'XX00000378', 0, 243, 'Priorizar programas multisectoriales para garantizar el aprovechamiento del agua con inversiones asociadas a fuentes subutilizadas, la hidrometría, el mejoramiento de los sistemas de riego, la introducción de tecnologías eficientes y la automatización de los sistemas de operación y control, que permita el incremento del área agrícola bajo riego.', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (245, 'XX00000245', 1, 8, 'VIII. POLÍTICA INDUSTRIAL Y ENERGÉTICA POLÍTICA INDUSTRIAL', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (246, 'XX00000246', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 245, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (247, 'XX00000247', 0, 177, 'Definir una política tecnológica que contribuya a reorientar el desarrollo industrial, y que comprenda el control de las tecnologías existentes \r\r\nen el país, a fin de promover su modernización sistemática. Observando los principios de la Política medio ambiental del país.', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (248, 'XX00000248', 0, 178, 'Desarrollar la industria, priorizando los sectores que dinamizan la economía o contribuyen a su transformación estructural, avanzando en la \r\r\nmodernización, desarrollo tecnológico y elevando su respuesta a las demandas de la economía.', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (249, 'XX00000249', 0, 179, 'Prestar atención prioritaria al impacto ambiental asociado al desarrollo industrial existente y proyectado, en particular, en las ramas de la \r\r\nquímica; la industria del petróleo y la minería, en especial el níquel; el cemento y otros materiales de construcción; así como en los territorios \r\r\nmás afectados, incluyendo el fortalecimiento de los sistemas de control y monitoreo.', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (250, 'XX00000250', 0, 180, 'Intensificar el proceso de reestructuración y redimensionamiento del plantel industrial, concentrando la industria en capacidades eficientes, con un empleo racional de instalaciones, equipos y fuerza de trabajo.\r\n', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (251, 'XX00000251', 0, 181, 'Priorizar la reactivación del mantenimiento industrial, incluyendo la producción y recuperación de partes, piezas de repuesto y herramentales.\r\n', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (252, 'XX00000252', 0, 182, 'Intensificar las acciones de control de la generación de los desechos peligrosos y el manejo integral de los mismos. ', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (253, 'XX00000253', 1, 2, 'LINEAMIENTOS PARA LAS PRINCIPALES RAMAS', 2012, 2018, 245, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (254, 'XX00000254', 0, 183, 'Consolidar la industria farmacéutica y biotecnológica como una de las actividades de mayor capacidad exportadora de la economía, diversificar \r\r\nproductos y mercados e incorporar nuevos productos al mercado nacional para sustituir importaciones. Desarrollar la industria de suplementos dietéticos \r\r\ny medicamentos naturales.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (255, 'XX00000255', 0, 184, 'Mejorar la posición de la industria del níquel en los mercados, mediante el incremento y diversificación de la producción, elevación de la calidad de sus productos y reducción de los costos, logrando una mejor utilización de los recursos minerales.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (256, 'XX00000256', 0, 185, 'Ejecutar con celeridad los proyectos en marcha para la exploración de pequenos yacimientos de minerales, en particular para la producción de oro, \r\r\ncobre, cromo, plomo y zinc. Priorizar las inversiones para la explotación de yacimientos de plata.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (257, 'XX00000257', 0, 186, 'Desarrollar la industria electrónica y la automática, diversificando sus producciones y elevando su capacidad tecnológica, con vistas a \r\r\npotenciar la sustitución de importaciones, incrementar las exportaciones y los servicios.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (258, 'XX00000258', 0, 187, 'Desarrollar las producciones químicas, priorizando la industria transformativa del plástico, las producciones de cloro, sal, fertilizantes y \r\r\nneumáticos. Fortalecer las capacidades de recape en el país. Avanzar en los estudios que posibiliten un mayor empleo de las producciones mineras \r\r\nnacionales a partir de rocas y minerales industriales.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (259, 'XX00000259', 0, 188, 'Desarrollar las industrias productoras de envases y embalajes. Priorizar la producción de envases demandados por las actividades exportadoras y el \r\r\ndesarrollo agroalimentario.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (260, 'XX00000260', 0, 189, 'Recuperar e incrementar la producción de materiales para la construcción que aseguren los programas inversionistas priorizados del país \r\r\n(turismo, viviendas, industriales, entre otros), la expansión de las exportaciones y la venta a la población. Desarrollar producciones con mayor valor \r\r\nagregado y calidad. Lograr incrementos significativos en los niveles y diversidad de las producciones locales de materiales de construcción y divulgar \r\r\nsus normas de empleo.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (261, 'XX00000261', 0, 190, 'Desarrollar la metalurgia ferrosa, priorizando la ampliación de capacidades, la reducción de los consumos energéticos y la diversificación de \r\r\nla producción de laminados y de metales conformados, elevando su calidad.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (262, 'XX00000262', 0, 191, 'Promover la intensificación del reciclaje y el aumento del valor agregado de los productos recuperados. Priorizar el aprovechamiento\r\ndel potencial de los residuos sólidos urbanos.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (263, 'XX00000263', 0, 192, 'Desarrollar la industria metal-mecánica y de bienes de capital, a partir de la reorganización productiva de las capacidades existentes, la \r\r\nrecuperación y modernización de máquinas herramientas y equipos, y la realización de inversiones en nuevos procesos de mayor nivel tecnológico.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (264, 'XX00000264', 0, 193, 'Elevar la competitividad de la industria ligera potenciando los encadenamientos productivos, el diseno y asegurar la gestión de la calidad. \r\r\nConcluir el proceso de reordenamiento y reestructuración del sistema empresarial, incluyendo el paso a nuevas formas de gestión.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (265, 'XX00000265', 0, 194, 'Perfeccionar el modelo de gestión de la industria local, flexibilizando su operación para posibilitar el desarrollo de producciones\r\nartesanales y la fabricación de bienes de consumo en pequeñas series o a la medida, así como la prestación de servicios de reparación y \r\r\nmantenimiento. Ello incluye la apertura de mayores espacios para actividades no estatales. Prestar atención a los talleres especiales donde laboran \r\r\npersonas con limitaciones.', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (273, 'XX00000273', 1, 3, 'POLITICA ENERGÉTICA', 2012, 2018, 245, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (274, 'XX00000274', 0, 195, 'Elevar la producción nacional de crudo y gas acompanante, desarrollando los yacimientos conocidos e incorporando la recuperación mejorada. \r\r\nAcelerar los estudios geológicos encaminados a poder contar con nuevos yacimientos, incluidos los trabajos de exploración en la Zona Económica \r\r\nExclusiva del Golfo de México.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (275, 'XX00000275', 0, 196, 'Elevar la eficiencia y el rendimiento del sistema de refinación en Cuba, que permita incrementar los volúmenes de productos de mayor valor \r\r\nagregado.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (276, 'XX00000276', 0, 197, 'Elevar la eficiencia en la generación eléctrica, dedicando la atención y recursos necesarios al mantenimiento de las plantas en operación, y lograr altos índices de disponibilidad en las plantas térmicas y en las instalaciones de generación con grupos electrógenos.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (277, 'XX00000277', 0, 198, 'Ejecutar el programa de construcción, montaje y puesta en marcha de nuevas capacidades de generación térmica y prestar atención priorizada al \r\r\ncompletamiento de las capacidades de generación en los ciclos combinados de Boca de Jaruco y Varadero.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (278, 'XX00000278', 0, 199, 'Mantener una política activa en el acomodo de la carga eléctrica, que disminuya la demanda máxima y reduzca su impacto sobre las capacidades de \r\r\ngeneración.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (279, 'XX00000279', 0, 200, 'Proseguir el programa de rehabilitación y modernización de redes y subestaciones eléctricas, de eliminación de zonas de bajo voltaje, logrando \r\r\nlos ahorros planificados por disminución de las pérdidas en la distribución y transmisión de energía eléctrica. Avanzar en el Programa aprobado de \r\r\nelectrificación en zonas aisladas del Sistema Electroenergético Nacional, en correspondencia con las necesidades y posibilidades del país, utilizando las fuentes más económicas.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (280, 'XX00000280', 0, 201, 'Fomentar la cogeneración y trigeneración en todas las actividades con posibilidades.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (281, 'XX00000281', 0, 202, 'Acelerar el cumplimiento del Programa aprobado hasta 2030, para el desarrollo de las fuentes renovables y el uso eficiente de la energía.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (282, 'XX00000282', 0, 203, 'Se priorizará la identificación permanente del potencial de ahorro en el sector estatal y privado, así como la ejecución de acciones para su \r\r\ncaptación.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (283, 'XX00000283', 0, 204, 'Concebir las nuevas inversiones, el mantenimiento constructivo y las reparaciones capitalizables con soluciones para el uso eficiente de la \r\r\nenergía, instrumentando adecuadamente los procedimientos de supervisión.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (284, 'XX00000284', 0, 205, 'Perfeccionar el trabajo de planificación y control del uso de los portadores energéticos, ampliando los elementos de medición y la calidad de \r\r\nlos indicadores de eficiencia e índices de consumo establecidos.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (285, 'XX00000285', 0, 206, 'Proyectar el sistema educativo y los medios de comunicación masiva en función de profundizar en la calidad e integralidad de la política \r\r\nenfocada al ahorro y al uso eficiente y sostenible de la energía.', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (289, 'XX00000289', 1, 9, 'IX. POLÍTICA PARA EL TURISMO', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (290, 'XX00000290', 0, 207, 'La actividad turística deberá tener un crecimiento acelerado que garantice la sostenibilidad y dinamice la economía, incrementando de manera \r\r\nsostenida los ingresos y las utilidades, diversificando los mercados emisores y segmentos de clientes, y maximizando el ingreso medio por turista.', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (291, 'XX00000291', 0, 208, 'Continuar incrementando la competitividad de Cuba en los mercados turísticos, diversificando las ofertas, potenciando la capacitación de los \r\r\nrecursos humanos y la elevación de la calidad de los servicios con una adecuada relación “calidad-precio”.', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (292, 'XX00000292', 0, 209, 'Perfeccionar las formas de comercialización, utilizando las tecnologías más avanzadas de la información y las comunicaciones,\r\ny potenciando la comunicación promocional.', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (293, 'XX00000293', 0, 210, 'La actividad no estatal en alojamiento, gastronomía y otros servicios, se continuará desarrollando como oferta turística\r\ncomplementaria a la estatal.\r\n', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (294, 'XX00000294', 0, 211, 'Consolidar el mercado interno, creando y diversificando ofertas que posibiliten el mayor aprovechamiento de las infraestructuras, así como otras ofertas que faciliten a los cubanos residentes en el país, viajar al exterior como turistas.', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (295, 'XX00000295', 0, 212, 'Continuar incrementando la participación de la industria y los servicios del país en los recursos que se utilizan en la operación e inversión turística. La participación de la industria nacional deberá desarrollarse con financiamiento a largo plazo.', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (296, 'XX00000296', 0, 213, 'Continuar priorizando la reparación, el mantenimiento, renovación y actualización de la infraestructura turística y de apoyo. Aplicar polí\r\r\nticas que garanticen la sostenibilidad de su desarrollo, implementando medidas para disminuir el índice de consumo de agua y de portadores energéticos \r\r\ne incrementar la utilización de fuentes de energía renovable y el reciclaje de los desechos que se generan en la prestación de los servicios turí\r\r\nsticos, en armonía con el medio ambiente. ', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (297, 'XX00000297', 0, 214, 'Velar porque las expresiones artísticas vinculadas a las actividades turísticas respondan fielmente a la política cultural trazada por la \r\r\nRevolución cubana. ', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (304, 'XX00000304', 1, 10, 'X. POLÍTICA PARA EL TRANSPORTE', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (305, 'XX00000305', 0, 215, 'Continuar la recuperación, modernización, reposición y reordenamiento del transporte automotor tanto estatal como no estatal, fomentando el \r\r\ndesarrollo de los servicios técnicos y el incremento de la seguridad vial, con una mayor participación de la industria nacional en la fabricación de \r\r\npiezas de repuesto y medios de transporte. Garantizar el cumplimiento con efectividad y eficacia del plan estratégico nacional de seguridad vial.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (306, 'XX00000306', 0, 216, 'Perfeccionar la organización y el control de los servicios que prestan los porteadores privados, facilitándole el acceso a piezas y accesorios, \r\r\ncombustibles y otros recursos, en correspondencia con las posibilidades de la economía, de modo que se favorezca la legalidad, seguridad y calidad de \r\r\neste servicio.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (307, 'XX00000307', 0, 217, 'Garantizar la utilización de los esquemas y medios más eficientes para cada tipo de transportación, perfeccionando el Balance de Cargas, y \r\r\nlogrando un adecuado funcionamiento de la cadena puerto-transporte-economía interna, aprovechando las ventajas comparativas del ferrocarril, del \r\r\ncabotaje, de las empresas especializadas y de la contenerización, logrando la integración multimodal.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (308, 'XX00000308', 0, 218, 'Impulsar el programa de recuperación y desarrollo del ferrocarril dentro del proceso inversionista del país. Considerar fuentes de financiamiento \r\r\na largo plazo. Culminar el perfeccionamiento del sistema, con énfasis en el rescate de la disciplina en el funcionamiento de la actividad ferroviaria.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (309, 'XX00000309', 0, 219, 'Desarrollar la flota mercante nacional y los astilleros, como forma de propiciar el incremento en la recaudación de divisas y el ahorro por \r\r\nconcepto de flete.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (310, 'XX00000310', 0, 220, 'Elevar la eficiencia de las operaciones marítimo-portuarias, a partir de la organización de sistemas de trabajo que permitan alcanzar ritmos \r\r\nsuperiores en la manipulación de mercancías, y una mayor eficiencia en la atención a los cruceros, incluyendo la modernización y el mantenimiento \r\r\noportuno de la infraestructura portuaria y su equipamiento, el sistema de seguridad marítima, así como el dragado de los principales puertos del paí\r\r\ns.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (311, 'XX00000311', 0, 221, 'Fomentar el diseno de formas organizativas estatales y no estatales en las transportaciones de pasajeros y carga, así como en otros servicios \r\r\nvinculados con la actividad, en correspondencia con las características de cada territorio.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (312, 'XX00000312', 0, 222, 'Continuar la modernización y ampliación de la flota aérea cubana de pasajeros y de carga, así como de la infraestructura aeroportuaria con el \r\r\nobjetivo de asegurar el crecimiento del turismo y la demanda nacional.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (313, 'XX00000313', 0, 223, 'Incrementar los niveles de satisfacción de la demanda de transportación de pasajeros, con estabilidad y calidad, en un ambiente de integración \r\r\nmultimodal con la participación de las diferentes formas de gestión, que facilite la movilidad de una población que envejece, en función de sus \r\r\nnecesidades y las de la economía.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (314, 'XX00000314', 0, 224, 'Implementar nuevas formas de cobro en el transporte urbano y rural de pasajeros en función de minimizar la evasión del pago y el desvío de la \r\r\nrecaudación.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (315, 'XX00000315', 0, 225, 'Potenciar la recuperación, el mantenimiento y el desarrollo de la infraestructura vial automotor incluyendo su señalización.', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (323, 'XX00000323', 1, 11, 'XI. POLÍTICA PARA LAS CONSTRUCCIONES, VIVIENDAS Y RECURSOS HIDRAULICOS', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (324, 'XX00000324', 1, 1, 'CONSTRUCCIONES', 2012, 2018, 323, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (325, 'XX00000325', 0, 226, 'Continuar perfeccionando la elaboración del Balance de los Recursos Constructivos del país sobre la base de una mayor coordinación con el \r\r\nproceso de planificación de la economía, la preparación de las organizaciones, la descentralización de facultades y un mayor control, incorporando \r\r\nlas nuevas formas no estatales de gestión.', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (326, 'XX00000326', 0, 227, 'Elevar la eficiencia en las construcciones empleando sistemas de pago por resultados y calidad, más efectivos, aumentando el rendimiento del \r\r\nequipamiento tecnológico y no tecnológico, introduciendo nuevas tecnologías en la construcción y adoptando nuevas formas organizativas, tanto \r\r\nestatales como no estatales.', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (327, 'XX00000327', 0, 228, 'Incrementar la creación de empresas especializadas de alcance nacional en las funciones de proyectos y construcción para programas priorizados y \r\r\notros sectores de la economía que lo requieran.', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (328, 'XX00000328', 0, 229, 'Aplicar la actualización de los precios de las construcciones en correspondencia con la política de los precios aprobada y asegurar su ulterior \r\r\nperfeccionamiento.', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (330, 'XX00000330', 1, 2, 'VIVIENDAS', 2012, 2018, 323, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (331, 'XX00000331', 0, 230, 'Mantener la atención prioritaria a las acciones constructivas de conservación y rehabilitación de viviendas. Recuperar viviendas que hoy se \r\r\nemplean en funciones administrativas o estatales, así como inmuebles que pueden asumir funciones habitacionales.', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (332, 'XX00000332', 0, 231, 'Mantener la atención prioritaria al aseguramiento del programa de viviendas a nivel municipal, incrementando la producción local y la \r\r\ncomercialización de materiales de la construcción empleando las materias primas y tecnologías disponibles, que permitan incrementar la participación \r\r\npopular, mejorar la calidad y disminuir los costos de los productos.', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (333, 'XX00000333', 0, 232, 'Se adoptarán las acciones que correspondan para priorizar la construcción, conservación y rehabilitación de viviendas en el campo, teniendo en \r\r\ncuenta la necesidad de mejorar las condiciones de vida, las particularidades que hacen más compleja esta actividad en la zona rural y la política para \r\r\natender los elevados niveles de envejecimiento de la población, con el objetivo de contribuir al completamiento y estabilidad de la fuerza de trabajo \r\r\nen el sector agroalimentario.', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (334, 'XX00000334', 0, 233, 'Establecer el Programa Nacional de la Vivienda de forma integral, que abarque las directivas principales de la construcción, las formas de \r\r\ngestión para la producción, incluyendo la no estatal y por esfuerzo propio, la rehabilitación de viviendas y las  urbanizaciones, definiendo las \r\r\nprioridades para resolver el déficit habitacional, teniendo en cuenta un mayor aprovechamiento del suelo y el uso de tecnologías más eficientes.\r\n', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (335, 'XX00000335', 0, 234, 'Actualizar, ordenar y agilizar los trámites para la remodelación, rehabilitación, construcción, arrendamiento de viviendas y transferencia de \r\r\npropiedad.', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (336, 'XX00000336', 0, 235, 'Adecuar la legislación sobre la vivienda al modelo de desarrollo económico y social, asegurando la racionalidad y sustentabilidad de la solución \r\r\nal problema habitacional, manteniendo los principios sociales logrados por la Revolución y diversificando las formas para su acceso y financiamiento.', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (339, 'XX00000339', 1, 3, 'RECURSOS HIDRAULICOS', 2012, 2018, 323, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (340, 'XX00000340', 0, 236, 'Consolidar el balance de agua como instrumento de planificación e instrumentar la evaluación de la productividad del agua para medir la \r\r\neficiencia en el consumo.', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (341, 'XX00000341', 0, 237, 'Continuará desarrollándose el programa hidráulico con inversiones de largo alcance para enfrentar el impacto del cambio climático y \r\r\nmaterializar las medidas de adaptación: la reutilización del agua; la captación de la lluvia; la desalinización del agua de mar y la sostenibilidad \r\r\nde todos los servicios asociados, que permita alcanzar y superar los objetivos de desarrollo sostenible.', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (342, 'XX00000342', 0, 238, 'Se priorizará y ampliará el programa de rehabilitación de acueductos y alcantarillados con la utilización de nuevas tecnologías en \r\r\ncorrespondencia con las capacidades financieras y constructivas, con el objetivo de garantizar la cantidad y calidad del agua, disminuir las pérdidas, \r\r\nincrementar su reciclaje, reducir el consumo energético y los servicios asociados a los sistemas de aprovechamiento, acueducto y alcantarillado.', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (343, 'XX00000343', 0, 239, 'Implementar el reordenamiento de los acueductos y alcantarillados, las tarifas del servicio y regular de manera obligatoria la medición del caudal \r\r\ny el cobro a los usuarios, con el objetivo de propiciar el uso racional del agua, reducir el derroche y la disminución gradual del subsidio.', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (344, 'XX00000344', 1, 12, 'XII. POLÍTICA PARA EL COMERCIO ', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (345, 'XX00000345', 0, 244, 'Continuar la reestructuración del comercio mayorista y minorista, en función de las condiciones en que operará la economía.', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (346, 'XX00000346', 0, 245, 'Incrementar y estabilizar la oferta de bienes y servicios a la población y su calidad, incluyendo la oferta de equipos eficientes energéticamente \r\r\ny la prestación de los servicios de postventa, que satisfagan la demanda de los distintos segmentos del mercado, en lo fundamental, a partir de la \r\r\ndistribución del ingreso con arreglo al trabajo.', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (347, 'XX00000347', 0, 246, 'Elevar la eficacia de los servicios de reparación y mantenimiento de los equipos eléctricos de cocción y otros equipos electrodomésticos, con \r\r\nvistas a lograr su adecuado funcionamiento.\r\n', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (348, 'XX00000348', 0, 247, 'Avanzar en la venta liberada de gas licuado de petróleo y de otras tecnologías, como opción adicional y a precios no subsidiados.', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (349, 'XX00000349', 0, 248, 'Continuar perfeccionando el sistema de abastecimiento del país, aumentando la participación de los productores nacionales. Definir las formas de \r\r\ngestión mayorista que den respuesta a todos los actores de la economía de acuerdo con las posibilidades del país.', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (350, 'XX00000350', 0, 249, 'Se desarrollarán mercados de aprovisionamiento que vendan a precios mayoristas y brinden los servicios de alquiler de medios y equipos, sin \r\r\nsubsidio, al sistema empresarial, al presupuestado y a las formas de gestión no estatal.', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (351, 'XX00000351', 0, 250, 'Ejercer un efectivo control sobre la gestión de compras y de inventarios, para minimizar la inmovilización de recursos y las pérdidas en la \r\r\neconomía. ', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (352, 'XX00000352', 0, 251, 'Trabajar para desarrollar un plan logístico nacional que garantice la gestión integrada de las cadenas de suministro existentes en el país.', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (353, 'XX00000353', 0, 252, 'Continuar la introducción gradual, donde se considere necesario, de formas no estatales de gestión en el comercio.', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (354, 'XX00000354', 0, 13, 'XIII. PERFECCIONAMIENTO DE SISTEMAS Y ÓRGANOS DE DIRECCIÓN', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (357, 'XX00000357', 0, 25, 'Lograr la disminución progresiva de los niveles de los subsidios y otras transferencias que se otorgan por el Estado y contribuya a mejorar, en lo posible, la oferta de productos y servicios esenciales para la población.', 2014, 2021, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (358, 'XX00000358', 0, 26, 'Una relación adecuada entre el componente importado de la producción nacional y la capacidad de generar ingresos en divisas de la economía.', 2014, 2021, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (360, 'XX00000360', 0, 40, 'Concluir el proceso de unificación monetaria y cambiaria como un paso decisivo en el ordenamiento monetario del país.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (361, 'XX00000361', 0, 41, 'Avanzar en la creación de mecanismos más eficientes en el acceso a las divisas para los diferentes actores económicos, que contribuyan a facilitar el funcionamiento de la economía.', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (362, 'XX00000362', 0, 52, 'Actualizar los instrumentos jurídicos a fin de propiciar un mayor ordenamiento de las finanzas públicas en el país.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (363, 'XX00000363', 0, 53, 'Perfeccionar el proceso de planificación y elevar el control sobre la utilización de los recursos financieros del Presupuesto del Estado, tanto en \r\r\nlos ingresos como en los gastos.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (364, 'XX00000364', 0, 54, 'Perfeccionar la gestión en el cobro de los tributos y fortalecer el control fiscal. Para ello se debe consolidar el fortalecimiento de la ONAT, \r\r\nasí como continuar el proceso de simplificación del pago de los tributos sin deteriorar la carga tributaria diseñada para los diferentes sectores de \r\r\ncontribuyentes.', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (365, 'XX00000365', 1, 6, 'SEGUROS', 2014, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (366, 'XX00000366', 0, 61, 'Potenciar el uso del seguro, en sus diferentes modalidades, como mecanismo de protección financiera de las personas y del sector productivo, abarcando todas las formas de gestión. Desarrollar los seguros de vida como complemento de la seguridad social', 2014, 2018, 43, 365, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (367, 'XX00000367', 0, 109, 'Culminar el perfeccionamiento del sistema de normalización, metrología y aseguramiento de la calidad, en correspondencia con los objetivos \r\r\npriorizados del Plan Nacional de Desarrollo Económico y Social, alcanzando a todos los actores económicos del país.', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (371, 'XX00000371', 0, 130, 'Garantizar la formación, desarrollo y estabilidad de los especialistas médicos para dar respuesta a las necesidades del país, incluido el desarrollo de la atención de pacientes extranjeros en Cuba, y a las que se generen por los compromisos internacionales.', 2014, 2020, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (372, 'XX00000372', 0, 135, 'Continuar elevando la calidad y rigor de la enseñanza artística profesional, a partir del mejoramiento de las condiciones de las instituciones educacionales y la elevación de la preparación del personal docente.', 2014, 2020, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (373, 'XX00000373', 0, 136, 'Implantar la política sobre la transformación del cine cubano y el Icaic encaminada a fomentar la creación cinematográfica y audiovisual.', 2014, 2020, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (374, 'XX00000374', 0, 27, 'Perfeccionar y fortalecer la utilización de los indicadores macroeconómicos en el Sistema de Dirección de la Economía, como elemento fundamental para la elaboración y control del plan de la economía.', 2014, 2020, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (375, 'XX00000375', 0, 240, 'Garantizar el acceso sistemático del abasto de agua a la población, de acuerdo con las posibilidades de la economía, con la potabilidad y calidad requeridas a partir de la materialización de inversiones para dar respuesta a las necesidades del consumo de la población.', 2014, 2020, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (376, 'XX00000376', 0, 241, 'Perfeccionar la gestión integrada del agua en la cuenca hidrográfica como unidad de gestión territorial, con prioridad en las estrategias preventivas para la reducción de la generación de residuales y emisiones en la fuente de origen, que contribuya a asegurar la cantidad y calidad del agua.', 2014, 2020, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (377, 'XX00000377', 0, 242, 'Modernizar la red de monitoreo del ciclo hidrológico y la calidad del agua que contribuya al fortalecimiento del sistema de alerta temprana para la mitigación y enfrentamiento a los eventos extremos del clima y afectaciones epidemiológicas, implementando un programa multisectorial para la erradicación paulatina de las fuentes contaminantes categorizadas como principales, que afectan las aguas\r\nterrestres.', 2014, 2020, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (379, 'XX00000379', 1, 3, 'XIII. PERFECCIONAMIENTO DE SISTEMAS Y ÓRGANOS DE DIRECCIÓN', 2014, 2020, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (381, 'XX00000381', 1, 1, 'LINEAMIENTOS GENERALES', 2014, 2020, 379, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (382, 'XX00000382', 0, 253, 'Continuar los cambios estructurales, funcionales, organizativos y económicos del sistema empresarial, las unidades presupuestadas y la administración estatal en general, de forma programada, con orden y disciplina, sobre la base de la política aprobada.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (383, 'XX00000383', 0, 254, 'Perfeccionar y garantizar un programa de capacitación de directivos, ejecutores directos y trabajadores para la implantación de las políticas que se aprueben, comprobando el dominio de lo que se regule y exigir su cumplimiento. Informar a los trabajadores y escuchar sus opiniones.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (384, 'XX00000384', 0, 255, 'La separación de las funciones estatales y empresariales continuará realizándose mediante un proceso paulatino y ordenado, estableciendo las normas que aseguren alcanzar las metas propuestas.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (386, 'XX00000386', 0, 256, 'Las unidades presupuestadas cumplen funciones estatales y de Gobierno, así como de otras características como la prestación de servicios de salud, educación y otros. No se crearán para prestar servicios productivos ni para la producción de bienes. Se les definen misión, funciones, obligaciones y atribuciones.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (387, 'XX00000387', 0, 257, 'Continuar reduciendo la cantidad de unidades presupuestadas hasta el número mínimo que garantice el cumplimiento de las funciones asignadas, donde primen los criterios de máximo ahorro del Presupuesto del Estado en recursos materiales, humanos y financieros, garantizando un servicio eficiente y de calidad.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (388, 'XX00000388', 0, 258, 'Las unidades presupuestadas que puedan financiar sus gastos con sus ingresos y generar un excedente, pasarán a ser unidades autofinanciadas, sin dejar de cumplir las funciones y atribuciones asignadas, o podrán adoptar, previa aprobación, la forma de empresa.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (389, 'XX00000389', 0, 259, 'A las unidades presupuestadas que solo logren cubrir una parte de sus gastos con sus ingresos, se les aprobará la parte de los gastos que se financiará por el Presupuesto del Estado, mediante un tratamiento especial.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (390, 'XX00000390', 0, 260, 'Continuar el perfeccionamiento del sistema de dirección y gestión de las unidades presupuestadas, adecuándolo a sus características funcionales, organizativas y económicas, simplificando su contabilidad.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (391, 'XX00000391', 0, 261, 'Los consejos de la administración provinciales y municipales cumplirán funciones estatales y no intervendrán directamente en la gestión empresarial, en correspondencia con ello se consolidarán y generalizarán las experiencias obtenidas en la separación de funciones estatales y empresariales en el experimento que se realiza.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (392, 'XX00000392', 0, 262, 'Las funciones estatales que ejercen los órganos de dirección en provincias y municipios y su relación con las que desarrollan los organismos de la Administración Central del Estado, serán reguladas dejando definidos los límites de sus competencias, vínculos, reglamentos de trabajo y las metodologías de actuación que se aplicarán en correspondencia con el experimento que se realiza.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (393, 'XX00000393', 0, 263, 'Perfeccionar la protección al consumidor adoptando medidas que coadyuven a asegurar sus derechos por quienes producen, comercializan y prestan servicios en general.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (394, 'XX00000394', 0, 264, 'Implantar la Política de Comunicación Social del Estado y el Gobierno cubanos. Realizar las transformaciones funcionales y estructurales que demande su aplicación. Priorizar en sus tareas iniciales el diseño de una estrategia de comunicación para la implementación de los lineamientos económicos y sociales del país, que contribuya a potenciar el optimismo y la confianza en el futuro.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (395, 'XX00000395', 0, 265, 'Realizar el perfeccionamiento del funcionamiento, estructura y composición de los órganos superiores de Dirección del Estado y el Gobierno que se exija.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (396, 'XX00000396', 0, 266, 'Culminar el perfeccionamiento del funcionamiento, estructura y composición de los OACE y entidades nacionales, estableciendo la base jurídico-organizativa que se requiera. Desarrollar sistemáticamente este proceso.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (397, 'XX00000397', 0, 267, 'Continuar el perfeccionamiento de los órganos del Poder Popular como vía para consolidar nuestra democracia socialista.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (398, 'XX00000398', 0, 268, 'Consolidar y perfeccionar el Sistema de Planificación de Objetivos y Actividades del Gobierno.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (399, 'XX00000399', 0, 269, 'Perfeccionar el Sistema de Trabajo con los Cuadros del Estado y del Gobierno, incluida su base reglamentaria, y avanzar con calidad en la aplicación de los procesos que lo integran; prestando la debida atención y exigencia por los jefes, comisiones y órganos de cuadros a: la selección y promoción de los cuadros, su atención y estimulación, la reserva, el rigor en la evaluación, la ética, la disciplina, así como a la preparación y superación.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (400, 'XX00000400', 0, 270, 'Fortalecer el control interno y externo ejercido por los órganos del Estado, los organismos, las entidades, así como el control social sobre la gestión administrativa; promover y exigir la transparencia de la gestión pública y la protección de los derechos ciudadanos. Consolidar las acciones de prevención y enfrentamiento a las ilegalidades, la corrupción, el delito e indisciplinas sociales.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (401, 'XX00000401', 0, 271, 'Avanzar en la creación del Sistema de Información del Gobierno, asegurando el más alto grado de informatización que las posibilidades económicas permitan.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (402, 'XX00000402', 0, 272, 'Transformar el sistema de registros públicos y de los servicios y trámites, a partir de las normas aprobadas y las experiencias adquiridas mediante los experimentos realizados.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (403, 'XX00000403', 0, 273, 'Perfeccionar la División Político-Administrativa a fin de que esta facilite conformar un modelo de municipio con una sólida base económico-productiva, la autonomía necesaria y sustentabilidad.', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (404, 'XX00000404', 1, 1, 'LINEAMIENTOS GENERALES', 2014, 2020, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (405, 'XX00000405', 0, 110, 'Fortalecer las capacidades de prospección y vigilancia tecnológica y la política de protección de la propiedad industrial en Cuba y en los principales mercados externos.', 2014, 2020, 148, 404, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (406, 'XX00000406', 0, 111, 'Potenciar la organización y el desarrollo de capacidades de servicios profesionales de diseño, su integración a los sistemas institucional y empresarial del país.', 2014, 2020, 148, 404, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
REPLACE INTO tpoliticas VALUES (407, 'XX00000407', 0, 112, 'Potenciar el papel de la inversión extranjera directa en la introducción en el país de tecnologías de avanzada a nivel internacional y promover la creación de estructuras dinamizadoras (parques científicos y tecnológicos, incubadoras de empresas, zonas especiales de desarrollo y otras).', 2014, 2020, 148, 404, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-04-07
/**********************************************/
/* paso 1 */
ALTER TABLE tauditorias ADD COLUMN id_tipo_evento_code CHAR(10) DEFAULT NULL;
UPDATE tauditorias, ttipo_eventos SET id_tipo_evento_code = ttipo_eventos.id_code WHERE id_tipo_evento = ttipo_eventos.id; 

/* paso 2 */
update treg_evento set outlook= null;
ALTER TABLE tinductores CHANGE COLUMN descripcion descripcion LONGTEXT DEFAULT NULL;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-04-14
/**********************************************/
ALTER TABLE teventos CHANGE COLUMN descripcion descripcion LONGTEXT DEFAULT NULL;
ALTER TABLE treg_evento CHANGE COLUMN observacion observacion LONGTEXT DEFAULT NULL;

ALTER TABLE tinductores CHANGE COLUMN descripcion descripcion LONGTEXT DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-04-17
/**********************************************/
ALTER TABLE _config_synchro ADD COLUMN mcrypt_key VARCHAR (128) DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-04-19
/**********************************************/
ALTER TABLE texcel CHANGE COLUMN fichero filename VARCHAR(120) DEFAULT NULL;
ALTER TABLE texcel_celdas CHANGE COLUMN resulttoshow toshow TINYINT(2) DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-06-06
/**********************************************/
ALTER TABLE _config_synchro ADD COLUMN mcrypt BOOLEAN DEFAULT NULL;
UPDATE _config_synchro, tprocesos SET _config_synchro.id_proceso = tprocesos.id WHERE _config_synchro.id_proceso_code = tprocesos.id_code;
/*************************************************************************/
-- endscript
/*************************************************************************/
/**********************************************/
-- beginscript:2018-06-18
/**********************************************/
delete from tusuario_documentos where id_documento is null;
update teventos, tauditorias set teventos.id_auditoria = tauditorias.id where teventos.id_auditoria_code = tauditorias.id_code;
update teventos set carga= null, periodicidad= null where date(fecha_inicio_plan) = date(fecha_fin_plan);
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-06-21
/**********************************************/
ALTER TABLE tnotas CHANGE COLUMN descripcion descripcion LONGTEXT DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-06-22
/**********************************************/
/* paso 1 */
DROP TABLE tlistas;
CREATE TABLE tlistas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  nombre text DEFAULT NULL,
  descripcion longtext DEFAULT NULL,
  inicio mediumint(9) DEFAULT NULL,
  fin mediumint(9) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_proceso (id_proceso),
  CONSTRAINT tlistas_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/* paso 2 */
DROP TABLE tlista_requisitos;
CREATE TABLE tlista_requisitos (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  numero mediumint(9) DEFAULT NULL,
  componente tinyint(4) DEFAULT NULL,
  nombre text DEFAULT NULL,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) DEFAULT NULL,
  id_tipo_lista int(11) DEFAULT NULL,
  id_tipo_lista_code char(10) DEFAULT NULL,
  peso tinyint(4) DEFAULT NULL,
  inicio mediumint(9) DEFAULT NULL,
  fin mediumint(9) DEFAULT NULL,
  evidencia longtext DEFAULT NULL,
  indicacion longtext DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_lista (id_lista),
  KEY id_usuario (id_usuario),
  KEY id_tipo_lista (id_tipo_lista),
  CONSTRAINT tlista_requisito_fk FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tlista_requisito_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT tlista_requisito_fk2 FOREIGN KEY (id_tipo_lista) REFERENCES ttipo_listas (id) ON DELETE SET NULL
) ENGINE=InnoDB;

/* paso 3 */
DROP TABLE ttipo_listas;
CREATE TABLE ttipo_listas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  nombre text DEFAULT NULL,
  numero varchar(9) DEFAULT NULL,
  descripcion text DEFAULT NULL,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) DEFAULT NULL,
  componente smallint(6) DEFAULT NULL,
  year mediumint(9) DEFAULT NULL,
  capitulo smallint(6) DEFAULT NULL,
  subcapitulo int(11) DEFAULT NULL,
  id_capitulo int(11) DEFAULT NULL,
  id_capitulo_code char(10) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  indice int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_proceso (id_proceso),
  KEY id_capitulo (id_capitulo),
  KEY id_lista (id_lista),
  CONSTRAINT id_lista FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT ttipo_listas_fk FOREIGN KEY (id_capitulo) REFERENCES ttipo_listas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT ttipo_listas_fk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB;

/* paso 4 */
DROP TABLE treg_lista;
CREATE TABLE treg_lista (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) DEFAULT NULL,
  id_requisito int(11) DEFAULT NULL,
  id_requisito_code char(10) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  id_auditoria int(11) DEFAULT NULL,
  id_auditoria_code char(10) DEFAULT NULL,
  cumplimiento tinyint(4) DEFAULT NULL,
  observacion longtext DEFAULT NULL,
  valor float(9,3) DEFAULT NULL,
  calcular tinyint(1) DEFAULT NULL,
  reg_fecha date DEFAULT NULL,
  id_responsable int(11) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_lista (id_lista),
  KEY id_proceso (id_proceso),
  KEY id_auditoria (id_auditoria),
  KEY id_requisito (id_requisito),
  CONSTRAINT treg_lista_fk FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT treg_lista_fk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT treg_lista_fk2 FOREIGN KEY (id_auditoria) REFERENCES tauditorias (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT treg_lista_fk3 FOREIGN KEY (id_requisito) REFERENCES tlista_requisitos (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-07-23
/**********************************************/
/* paso 1 */
ALTER TABLE tsincronizacion ADD COLUMN mcrypt BOOLEAN DEFAULT NULL;

/* paso 2 */
ALTER TABLE tauditorias ADD COLUMN numero MEDIUMINT(9) AFTER situs; 
ALTER TABLE tauditorias ADD COLUMN numero_plus VARCHAR(10) AFTER numero; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-08-05
/**********************************************/
update ttipo_eventos set year= 2030 where year is null;
update teventos, ttipo_eventos set teventos.id_tipo_evento_code= ttipo_eventos.id_code where teventos.id_tipo_evento= ttipo_eventos.id;
update tauditorias, ttipo_eventos set tauditorias.id_tipo_evento_code= ttipo_eventos.id_code where tauditorias.id_tipo_evento= ttipo_eventos.id;
update ttipo_eventos set id_subcapitulo_code= null where id_subcapitulo is null;
update ttipo_eventos, ttipo_eventos as t2 set ttipo_eventos.id_subcapitulo_code= t2.id_code where ttipo_eventos.id_subcapitulo = t2.id;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-08-06
/**********************************************/
delete from tsincronizacion where year(cronos) >= 2018;
delete from tsystem where action like '%Lote' and year(cronos) >= 2018;

alter table _config drop column grouprows;
alter table _config drop column delay;
alter table _config drop column inactive;
alter table _config drop column daysbackup;
alter table _config drop column hoursoldier;
alter table _config drop column onlypost;
alter table _config drop column summaryextend;
alter table _config drop column breaktime;
alter table _config drop column freeassign;
alter table _config drop column hourcolum;
alter table _config drop column datecolum;
alter table _config drop column placecolum;
alter table _config drop column observcolum;
alter table _config drop column hourcolum_y;
alter table _config drop column placecolum_y;
alter table _config drop column observcolum_y;
alter table _config drop column monthstack;
alter table _config drop column type_synchro;
alter table _config drop column time_synchro;
alter table _config drop column hoursoldier;
alter table _config drop column onlypost;
alter table _config drop column summaryextend;
alter table _config drop column breaktime;
alter table _config drop column freeassign;
alter table _config drop column riskseeprocess;
alter table _config drop column riskseeactivity;
alter table _config drop column riskseedescription;
alter table _config drop column riskseetype1;
alter table _config drop column riskseedetection;
alter table _config drop column riskseestate;
alter table _config drop column riskseeobserv;
alter table _config drop column seemonthplan;
alter table _config drop column seeanualplan;
alter table _config drop column monthpurgue;
alter table _config drop column timepurgue;
alter table _config drop column timesynchro;
alter table _config drop column automatic_risk;
alter table _config drop column automatic_note;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-08-23
/**********************************************/
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM tpoliticas;
TRUNCATE table tpoliticas;

/* parte 2 */
INSERT INTO tpoliticas (id, id_code, titulo, numero, nombre, inicio, fin, capitulo, grupo, observacion, if_inner, id_proceso, id_proceso_code, cronos, cronos_syn, situs) VALUES
(1, 'XX00000001', 1, 1, 'I MODELO DE GESTIÓN ECONOMICA ', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(2, 'XX00000002', 0, 1, 'Continuar la actualización del Sistema de Dirección Planificada del Desarrollo Económico y Social, que abarca a los actores de todas las formas de propiedad y gestión, incrementando la eficiencia y eficacia. Garantizar el carácter integral del sistema y la interrelación de los diferentes actores', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(3, 'XX00000003', 0, 2, 'La planificación socialista seguirá siendo la vía principal para la dirección de la economía, con énfasis en garantizar los equilibrios macroeconómicos fundamentales y los objetivos y metas para el desarrollo a largo plazo. Se reconoce la existencia objetiva de las relaciones de mercado, sobre el cual el Estado ejerce regulación e influencia, considerando sus características', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(4, 'XX00000004', 0, 3, 'El Modelo Económico y Social Cubano consolida la propiedad socialista de todo el pueblo sobre los medios de producción fundamentales como la forma principal en la economía nacional. Además, reconoce en las actividades que se autoricen la propiedad cooperativa, la mixta, la privada de personas naturales o jurídicas cubanas o totalmente extranjeras, de organizaciones políticas, de masas, sociales y otras entidades de la sociedad civil. Todas funcionan e interactúan en beneficio de la economía y están sujetas al marco regulatorio y de control definido por el Estado', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(5, 'XX00000005', 0, 4, 'En las formas de gestión no estatales no se permitirá la concentración de la propiedad y la riqueza material y financiera en personas naturales o jurídicas no estatales. Continuar la actualización de las regulaciones para evitar que se contraponga a los principios de nuestro socialismo', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(6, 'XX00000006', 0, 5, 'Continuar fortaleciendo el papel del contrato como instrumento esencial de la gestión económica, elevando la exigencia en su cumplimiento en las relaciones entre los actores económicos', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(7, 'XX00000007', 0, 6, 'Exigir la actuación ética de los jefes, los trabajadores y las entidades, así como fortalecer el sistema de control interno y avanzar en la aplicación de métodos participativos en la dirección y en el control, que impliquen a todos los trabajadores. El control externo se basará, principalmente, en mecanismos económico-financieros, sin excluir los administrativos, haciendo estos más racionales en sus objetivos y propósitos.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(8, 'XX00000008', 0, 7, 'Continuar fortaleciendo la contabilidad para que constituya una herramienta en la toma de decisiones y garantice la fiabilidad de la información financiera y estadística, oportuna y razonablemente.', 2012, 2018, 1, 13, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(13, 'XX00000013', 1, 1, 'GENERALES', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(15, 'XX00000015', 1, 2, 'ESFERA EMPRESARIAL', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(16, 'XX00000016', 0, 8, 'Las empresas deciden y administran su capital de trabajo e inversiones hasta el límite previsto en el plan; sus finanzas internas no podrán ser intervenidas por instancias ajenas a estas; ello solo podrá ser realizado mediante los procedimientos legalmente establecidos.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(17, 'XX00000017', 0, 9, 'Avanzar en el perfeccionamiento del sistema empresarial, otorgando gradualmente a las direcciones de las entidades nuevas facultades, definiendo con precisión sus límites, con la finalidad de lograr empresas con mayor autonomía, efectividad y competitividad, sobre la base del rigor en el diseáo y aplicación de su sistema de control interno; mostrando en su gestión administrativa orden, disciplina y exigencia. Evaluar de manera sistemática los resultados de la aplicación y su impacto. Elaborar el régimen jurídico que regule integralmente la actividad empresarial.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(18, 'XX00000018', 0, 10, 'Las empresas y cooperativas que muestren sostenidamente en sus balances financieros pérdidas, capital de trabajo insuficiente, que no puedan honrar con sus activos las obligaciones contraídas o que obtengan resultados negativos en auditorías financieras, se podrán\r\n5\r\ntransformar o serán sometidas a un proceso de liquidación, cumpliendo con lo que se establezca.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(19, 'XX00000019', 0, 11, 'Continuar avanzando en la implantación del principio de que los ingresos de los trabajadores y sus jefes en el sistema de entidades de carácter empresarial, estén en correspondencia con los resultados que se obtengan.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(20, 'XX00000020', 0, 12, 'Las empresas y las cooperativas pagarán a los consejos de la administración municipal donde operan sus establecimientos, un tributo territorial, definido centralmente, teniendo en cuenta las particularidades de cada municipio, para contribuir a su desarrollo y constituyen fuente para financiar gastos corrientes y de capital.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(21, 'XX00000021', 0, 13, 'Priorizar y continuar avanzando en el logro del ciclo completo de producción mediante los encadenamientos productivos entre organizaciones que desarrollan actividades productivas, de servicios y de ciencia, tecnología e innovación, incluidas las universidades, que garanticen el desarrollo rápido y eficaz de nuevos productos y servicios, con estándares de calidad apropiados, que incorporen los resultados de la investigación científica e innovación tecnológica, e integren la gestión de comercialización interna y externa.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(22, 'XX00000022', 0, 14, 'Avanzar en la participación activa y consciente de todos los colectivos laborales en el proceso de conformación de la propuesta, ejecución y control de los planes de sus organizaciones, enmarcados en las políticas y directivas aprobadas por el Gobierno, según lo acordado en los convenios colectivos de trabajo.', 2012, 2018, 1, 15, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(28, 'XX00000028', 1, 3, 'LAS COOPERATIVAS', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(29, 'XX00000029', 0, 15, 'Avanzar en el experimento de las cooperativas no agropecuarias, priorizando aquellas actividades que ofrezcan soluciones al desarrollo de la localidad.', 2012, 2018, 1, 28, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(30, 'XX00000030', 0, 16, 'La norma jurídica sobre cooperativas regulará todos los tipos de cooperativas y deberá ratificar que como propiedad colectiva, no serán vendidas ni trasmitidas su posesión a otras cooperativas, a formas de gestión no estatales o a personas naturales. Proponer la creación de la instancia de Gobierno que conduzca la actividad.', 2012, 2018, 1, 28, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(39, 'XX00000039', 1, 5, 'TERRITORIOS', 2012, 2018, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(40, 'XX00000040', 0, 17, 'Impulsar el desarrollo de los territorios a partir de la estrategia del país, de modo que se fortalezcan los municipios como instancia fundamental, con la autonomía necesaria, sustentables, con una sólida base económico-productiva, y se reduzcan las principales desproporciones entre estos, aprovechando sus potencialidades. Elaborar el marco jurídico correspondiente.', 2012, 2018, 1, 39, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(43, 'XX00000043', 1, 2, 'II POLÍTICAS MACROECONÓMICAS', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(44, 'XX00000044', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(45, 'XX00000045', 0, 18, 'Garantizar los equilibrios macroeconómicos fundamentales y con ello lograr un entorno macroeconómico fiscal, monetario y financiero estable y sostenible que permita asignar eficientemente los recursos en función de las prioridades nacionales y del crecimiento económico sostenido.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(46, 'XX00000046', 0, 19, 'Consolidar las funciones dinerarias del peso cubano, con el objetivo de fortalecer su papel y preponderancia en el sistema monetario y financiero del país.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(47, 'XX00000047', 0, 20, 'Consolidar el marco regulatorio e institucional y el resto de las condiciones que permitan avanzar en el funcionamiento ordenado y eficiente de los mercados en función de incentivar la eficiencia, la competitividad y el fortalecimiento del papel de los precios.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(48, 'XX00000048', 0, 21, 'Consolidar un sistema financiero eficiente, solvente y diversificado, que asegure la sostenibilidad financiera del proceso de transformación\r\n7\r\nestructural previsto en el Plan Nacional de Desarrollo Económico y Social.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(49, 'XX00000049', 0, 22, 'Incrementar gradualmente el poder adquisitivo de los ingresos provenientes del trabajo, manteniendo los equilibrios macroeconómicos fundamentales y el nivel de prioridad que requiere la recapitalización de la economía.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(50, 'XX00000050', 0, 23, 'Alcanzar una dinámica de crecimiento del Producto Interno Bruto (PIB) y, en consecuencia de la riqueza del país, que asegure un nivel de desarrollo sostenible, que conduzca al mejoramiento del bienestar de la población, con equidad y justicia social.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(51, 'XX00000051', 0, 24, 'Alcanzar mayores niveles de productividad y eficiencia en todos los sectores de la economía a partir de elevar el impacto de la ciencia, la tecnología y la innovación en el desarrollo económico y social, así como de la adopción de nuevos patrones de utilización de los factores productivos, modelos gerenciales y de organización de la producción.', 2012, 2018, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(52, 'XX00000052', 1, 2, 'POLÍTICA MONETARIA', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(53, 'XX00000053', 0, 28, 'La planificación monetaria a corto, mediano y largo plazos deberá lograr, de manera integral, el equilibrio monetario interno y externo', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(54, 'XX00000054', 0, 29, 'Regular la cantidad de dinero en circulación, a partir de lo establecido en el plan, con el fin de contribuir al logro de la estabilidad cambiaria, del poder adquisitivo de la moneda y, con ello, el crecimiento ordenado de la economía', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(55, 'XX00000055', 0, 30, 'Establecer reglas adecuadas de emisión monetaria y utilizar oportunamente las herramientas analíticas para su medición y control', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(56, 'XX00000056', 0, 31, 'Fortalecer la utilización de los instrumentos de Política Monetaria para administrar desequilibrios coyunturales, contribuir al ordenamiento monetario del país y al cumplimiento de las metas establecidas en el plan', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(57, 'XX00000057', 0, 32, 'Estructurar un sistema de tasas de interés más racional y fundamentado, así como establecer los mecanismos que permitan que la tasa de interés se constituya en un instrumento relevante del Sistema de Dirección de la Economía', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(58, 'XX00000058', 0, 33, 'La correspondencia entre el crecimiento de la cantidad de dinero en poder de la población y de la capacidad de absorción del Estado, así como la posibilidad de conducir esta relación de forma planificada, continuará siendo el instrumento clave para lograr la estabilidad monetaria y cambiaria en dicho sector, condición necesaria para avanzar en el restablecimiento del funcionamiento de la ley de distribución socialista, de cada cual según su capacidad, a cada cual según su trabajo', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(59, 'XX00000059', 0, 34, 'Dinamizar el crédito como mecanismo de impulso a la actividad económica del país y el fortalecimiento del mercado interno', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(60, 'XX00000060', 0, 35, 'Incrementar y diversificar las ofertas de crédito a la población en la medida que las condiciones del país lo permitan', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(61, 'XX00000061', 0, 36, 'Incrementar y diversificar las ofertas de productos bancarios a la población para estimular el ahorro y el acceso a los servicios financieros', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(62, 'XX00000062', 0, 37, 'Perfeccionar los servicios bancarios necesarios al sector que opera bajo formas de gestión no estatales, para contribuir a su adecuado funcionamiento, en particular los dirigidos al desarrollo del sector agropecuario', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(63, 'XX00000063', 0, 38, 'Consolidar los mecanismos de regulación y supervisión del sistema financiero en función de los riesgos crecientes de esta actividad en el actual entorno económico', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(64, 'XX00000064', 0, 39, 'Avanzar en el desarrollo del sistema de pago y de los sistemas financieros, a fin de establecer una eficiente y transparente infraestructura de pagos. Intensificar el desarrollo de la bancarización en función del logro de estos objetivos', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(65, 'XX00000065', 1, 4, 'POLÍTICA FISCAL', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(66, 'XX00000066', 0, 42, 'La Política Fiscal deberá contribuir al incremento sostenido de la eficiencia de la economía y de los ingresos al Presupuesto del Estado, con el propósito de respaldar el gasto público en los niveles planificados y mantener un adecuado equilibrio financiero, tomando en cuenta las particularidades de nuestro modelo económico', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(67, 'XX00000067', 0, 43, 'Se ratifica el papel del Sistema Tributario como elemento redistribuidor del ingreso, basado en los principios de generalidad y equidad de la carga tributaria, a la vez que contribuya a la aplicación de las políticas\r\n10\r\nencaminadas al perfeccionamiento del modelo económico. Tener en cuenta las características de los territorios', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(68, 'XX00000068', 0, 44, 'Perfeccionar los mecanismos que garanticen que la demanda de financiamiento del Presupuesto del Estado resulte congruente con el equilibrio financiero y que la magnitud de la deuda pública que se asuma a partir del déficit presupuestario esté acotada a la capacidad de la economía de generar ingresos futuros que permitan su amortización', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(69, 'XX00000069', 0, 45, 'Desarrollar el mercado de deuda pública a fin de incrementar la efectividad en el financiamiento del déficit fiscal', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(70, 'XX00000070', 0, 46, 'Perfeccionar y ampliar los mecanismos para la inversión financiera del Presupuesto del Estado en el sector productivo, garantizando que sea rentable', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(71, 'XX00000071', 0, 47, 'Perfeccionar y ampliar los fondos presupuestarios para el apoyo financiero a las actividades que se requieran fomentar en interés del desarrollo económico y social del país', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(72, 'XX00000072', 0, 48, 'Continuar avanzando en la aplicación de estímulos fiscales que promuevan el desarrollo ordenado de las formas de gestión no estatales', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(73, 'XX00000073', 0, 49, 'Perfeccionar la aplicación de estímulos fiscales que promuevan producciones nacionales en sectores claves de la economía, especialmente a los fondos exportables y a los que sustituyen importaciones, al desarrollo local y la protección del medio ambiente', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(74, 'XX00000074', 0, 50, 'Actualizar el papel del Sistema Arancelario dentro del modelo económico, priorizando los regímenes arancelarios preferenciales y las bonificaciones que se consideren convenientes otorgar, bajo el principio de que los fondos exportables y las producciones que sustituyan importaciones deben ser rentables', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(75, 'XX00000075', 0, 51, 'Fomentar la cultura tributaria y la responsabilidad social de la población, entidades y formas de gestión no estatales del país, en el cumplimiento\r\n11\r\ncabal de las obligaciones tributarias, para desarrollar el valor cívico de contribución al sostenimiento de los gastos sociales y altos niveles de disciplina fiscal', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(76, 'XX00000076', 1, 5, 'POLÍTICA DE PRECIOS', 2012, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(77, 'XX00000077', 0, 55, 'Establecer un Sistema de Precios que permita medir correctamente los hechos económicos, estimule la producción, la eficiencia, el incremento de las exportaciones y la sustitución de importaciones, así como trasladar las seáales del mercado a los productores', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(78, 'XX00000078', 0, 56, 'Mantener centralizados los precios mayoristas y minoristas de un grupo de productos y servicios esenciales que permitan respaldar las políticas sociales y las necesidades básicas de la población', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(79, 'XX00000079', 0, 57, 'Garantizar, por parte del Estado, métodos efectivos de regulación y control directo e indirecto de precios mayoristas y minoristas', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(80, 'XX00000080', 0, 58, 'Los precios minoristas que se forman al amparo de las regulaciones estatales, deben ser continuidad de los mayoristas e incluir los márgenes comerciales y los tributos que correspondan', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(81, 'XX00000081', 0, 59, 'Continuar avanzando en el cumplimiento del principio de subsidiar personas y no productos, así como en la eliminación de subsidios. Se podrán mantener algunos niveles de estos, para garantizar determinados productos o servicios de uso masivo que lo requieran', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(82, 'XX00000082', 0, 60, 'Los precios mayoristas deben constituirse en el vehículo principal para la asignación de recursos en la economía, minimizando el uso de mecanismos administrativos', 2012, 2018, 43, 76, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(83, 'XX00000083', 1, 3, 'III. POLÍTICA ECONÓMICA EXTERNA', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(84, 'XX00000084', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(85, 'XX00000085', 0, 62, 'LINEAMIENTOS GENERALES\r\nConsolidar la credibilidad del país en sus relaciones económicas internacionales mediante el estricto cumplimiento de los compromisos contraídos', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(86, 'XX00000086', 0, 63, 'Continuar prestando la máxima atención a la selección y al control de los cuadros, funcionarios y empresarios que intervienen en las relaciones económicas externas, de manera especial, a la conducta ética acorde con los principios de la Revolución y la preparación técnica, en aspectos económicos, financieros, y jurídicos, entre otros\r\n62. Consolidar la credibilidad del país en sus relaciones económicas internacionales mediante el estricto cumplimiento de los compromisos contraídos', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(87, 'XX00000087', 0, 64, 'Aplicar el principio de quien decide no negocia en toda la actividad que desarrolle el país en el plano de las relaciones económicas internacionales', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(88, 'XX00000088', 0, 65, 'Promover, siempre que se justifique económicamente y resulte conveniente, el establecimiento de empresas y alianzas en el exterior,\r\n13\r\nque propicien el mejor posicionamiento de los intereses de Cuba en los mercados externos', 2012, 2018, 83, 84, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(89, 'XX00000089', 1, 2, 'COMERCIO EXTERIOR', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(90, 'XX00000090', 0, 66, 'Garantizar la aplicación integral de las políticas comercial, fiscal, crediticia, arancelaria, laboral y otras. Consolidar los mecanismos de protección de precios de los productos que se cotizan en bolsa y que Cuba comercializa', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(91, 'XX00000091', 0, 67, 'Elevar la eficiencia en la gestión de las empresas vinculadas al comercio exterior para incrementar y consolidar los ingresos por concepto de exportaciones de bienes y servicios; crear una real vocación exportadora a todos los niveles de dirección, en especial en el sector empresarial; fundamentar con estudios de mercado las decisiones más importantes y estratégicas; continuar la flexibilización de la participación de las entidades nacionales en el comercio exterior', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(92, 'XX00000092', 0, 68, 'Diversificar los destinos de los bienes y servicios exportables, con preferencia en los de mayor valor agregado y contenido tecnológico, además de mantener la prioridad y atención a los principales socios del país, y lograr mayor estabilidad en la obtención de ingresos', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(93, 'XX00000093', 0, 69, 'Continuar desarrollando la exportación de servicios, en particular los profesionales, que priorice la venta de proyectos o soluciones tecnológicas, y contemple el análisis flexible de la contratación de la fuerza de trabajo individual', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(94, 'XX00000094', 0, 70, 'Acelerar el desarrollo de los servicios médicos y de salud cubanos y continuar ampliando los mercados para su exportación', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(95, 'XX00000095', 0, 71, 'Continuar diversificando los mercados de exportación de langostas y camarones, e incorporar mayor valor agregado al producto', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(96, 'XX00000096', 0, 72, 'Trabajar para garantizar, por las empresas y entidades vinculadas a la exportación, que todos los bienes y servicios destinados a los mercados internacionales respondan a los más altos estándares de calidad', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(97, 'XX00000097', 0, 73, 'Incrementar la eficiencia en la gestión importadora del país, haciendo énfasis en la disponibilidad oportuna de las importaciones, su racionalidad, el uso eficaz del poder de compra y el desarrollo del mercado mayorista', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(98, 'XX00000098', 0, 74, 'Promover acuerdos internacionales de cooperación y complementación en el sector industrial que favorezcan las exportaciones de mayor valor agregado y la sustitución de importaciones, con un mejor aprovechamiento de las capacidades nacionales', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(99, 'XX00000099', 0, 75, 'Establecer los mecanismos para canalizar las demandas de importación que surjan de las formas de propiedad y gestión no estatales, así como viabilizar la realización de potenciales fondos exportables', 2012, 2018, 83, 89, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(106, 'XX00000106', 1, 3, 'DEUDA Y CRÉDITO', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(107, 'XX00000107', 0, 76, 'Continuar el proceso de reordenamiento de la deuda externa, aplicando estrategias de pago flexibles, de modo que se garantice estrictamente el cumplimiento de los compromisos, para contribuir al desempeáo creciente y sostenido de la economía, así como al acceso a nuevos financiamientos', 2012, 2018, 83, 106, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(108, 'XX00000108', 0, 77, 'Asegurar un adecuado balance en la toma de créditos y su estructura, el pago de las deudas reordenadas, la deuda corriente y el cumplimiento del plan', 2012, 2018, 83, 106, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(111, 'XX00000111', 1, 4, 'INVERSIÓN EXTRANJERA', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(112, 'XX00000112', 0, 78, 'Incrementar la participación de la inversión extranjera directa como una fuente importante para el desarrollo del país. Considerarla en determinados sectores y actividades económicas como un elemento fundamental', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(113, 'XX00000113', 0, 79, 'Favorecer, en el proceso de promoción de inversiones, la diversificación de la participación de diferentes países', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(114, 'XX00000114', 0, 80, 'Priorizar la ampliación y actualización sistemática de la cartera de proyectos de oportunidades de inversión extranjera, en correspondencia con las actividades, sectores priorizados y los territorios', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(115, 'XX00000115', 0, 81, 'Consolidar la Zona Especial de Desarrollo Mariel y promover la creación de nuevas, de acuerdo con el desarrollo de la economía', 2012, 2018, 83, 111, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(124, 'XX00000124', 1, 5, 'COOPERACIÓN', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(125, 'XX00000125', 0, 82, 'Consolidar el proceso de incorporación al Plan de la Economía Nacional y el Presupuesto del Estado, de las acciones de cooperación internacional que Cuba recibe y ofrece, que demanden recursos materiales y financieros adicionales', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(126, 'XX00000126', 0, 83, 'Culminar la implementación del marco legal y regulatorio para la cooperación económica y científico-técnica que Cuba recibe y ofrece', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(127, 'XX00000127', 0, 84, 'Continuar desarrollando la solidaridad internacional a través de la cooperación que Cuba ofrece; considerar, en la medida que sea posible, la compensación, al menos, de sus costos', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(128, 'XX00000128', 0, 85, 'Promover la cooperación económica que se recibe del exterior, destinada a la atracción de recursos financieros y tecnología, de acuerdo con las prioridades que se establezcan en el Plan Nacional de Desarrollo Económico y Social hasta 2030. Potenciar la vía multilateral, en especial con instituciones del Sistema de las Naciones Unidas', 2012, 2018, 83, 124, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(131, 'XX00000131', 1, 6, 'INTEGRACIÓN ECONÓMICA', 2012, 2018, 83, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(132, 'XX00000132', 0, 86, 'Dar prioridad a la participación en la Alianza Bolivariana para los Pueblos de Nuestra América (ALBA) y trabajar con celeridad e intensamente en la coordinación, cooperación y complementación económica a corto, mediano y largo plazos, para el logro y la profundización de los objetivos económicos, sociales y políticos que promueve', 2012, 2018, 83, 131, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(133, 'XX00000133', 0, 87, 'Continuar la participación activa en la integración económica con América Latina y el Caribe, como objetivo estratégico, y mantener la participación en los esquemas regionales de integración comercial en que Cuba logró articularse: Asociación Latinoamericana de Integración (Aladi), Comunidad del Caribe (Caricom), Asociación de Estados del Caribe (AEC), Petrocaribe y otros. Continuar fortaleciendo la unidad entre sus miembros', 2012, 2018, 83, 131, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(134, 'XX00000134', 1, 4, 'IV. POLITICA INVERSIONISTA', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(135, 'XX00000135', 0, 88, 'Las inversiones fundamentales a realizar responderán a la estrategia de desarrollo del país a corto, mediano y largo plazos, erradicando la espontaneidad, la improvisación, la superficialidad, el incumplimiento de los planes, la falta de profundidad en los estudios de factibilidad, la inmovilización de recursos y la carencia de integralidad al emprender una inversión', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(136, 'XX00000136', 0, 89, 'Continuar orientando las inversiones hacia la esfera productiva y de los servicios, así como a la infraestructura necesaria para el desarrollo sostenible, garantizando su aseguramiento oportuno, para generar beneficios a corto plazo. Se priorizarán las actividades de mantenimiento constructivo y tecnológico en todas las esferas de la economía.', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(137, 'XX00000137', 0, 90, 'Elevar la exigencia y el control a los inversionistas para que jerarquicen la atención integral y garanticen la calidad del proceso inversionista e incentivar el acortamiento de plazos, el ahorro de recursos y presupuesto en las inversiones', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(138, 'XX00000138', 0, 91, 'Se elevará la calidad y la jerarquía de los planes generales de ordenamiento territorial y urbano a nivel nacional, provincial y municipal, su integración con las proyecciones a mediano y largo plazos de la economía y con el Plan de Inversiones, garantizando la profundidad y\r\n17\r\nagilidad en los plazos de respuesta en los procesos obligados de consulta', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(139, 'XX00000139', 0, 92, 'Continuar el proceso de descentralización del Plan de Inversiones y cambio en su concepción, otorgándoles facultades de aprobación de las inversiones a los organismos de la Administración Central del Estado, a los consejos de la administración, al sistema empresarial y unidades presupuestadas', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(140, 'XX00000140', 0, 93, 'Las inversiones que se aprueben, como política, demostrarán que son capaces de recuperarse con sus propios resultados y deberán realizarse con créditos externos preferiblemente a mediano y largo plazos o capital propio, cuyo reembolso se efectuará a partir de los recursos generados por la propia inversión', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(141, 'XX00000141', 0, 94, 'Se continuarán asimilando e incorporando nuevas técnicas de dirección del proceso inversionista y también de entidades proyectistas y constructoras en asociaciones económicas internacionales. Valorar, siempre que sea imprescindible, la participación de constructores y proyectistas extranjeros para garantizar la ejecución de inversiones cuya complejidad e importancia lo requieran', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(142, 'XX00000142', 0, 95, 'Generalizar la licitación de los servicios de diseáo y construcción entre entidades cubanas. Elaborar las regulaciones para ello', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(143, 'XX00000143', 0, 96, 'Las inversiones de infraestructura, como norma, se desarrollarán con financiamiento a largo plazo y la inversión extranjera', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(144, 'XX00000144', 0, 97, 'Implementar acciones que permitan el completamiento y preparación de la fuerza de trabajo para continuar avanzando en el restablecimiento de la disciplina territorial y urbana. Simplificar y agilizar los trámites de la población para la obtención de la documentación requerida en los procesos de construcción, remodelación y rehabilitación de viviendas y locales', 2012, 2018, 134, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(148, 'XX00000148', 1, 5, 'V. POLÍTICA DE CIENCIA, TECNOLOGÍA, INNOVACIÓN Y MEDIO AMBIENTE', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(149, 'XX00000149', 0, 98, 'Situar en primer plano el papel de la ciencia, la tecnología y la innovación en todas las instancias, con una visión que asegure lograr a corto y mediano plazos los objetivos del Plan Nacional de Desarrollo Económico y Social', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(150, 'XX00000150', 0, 99, 'Continuar desarrollando el marco jurídico y regulatorio que propicie la introducción sistemática y acelerada de los resultados de la ciencia, la innovación y la tecnología en los procesos productivos y de servicios, y el cumplimiento de las normas de responsabilidad social y medioambiental establecidas', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(151, 'XX00000151', 0, 100, 'Continuar reordenando las entidades de ciencia, tecnología e innovación que están en función de la producción y los servicios hacia su transformación en empresas, pasando a formar parte de estas o de las organizaciones superiores de dirección empresarial, en todos los casos que resulte posible y conveniente', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(152, 'XX00000152', 0, 101, 'Implementar las políticas de los sistemas de ciencia, tecnología, innovación y medio ambiente, facilitando la interacción en sus ámbitos respectivos, e incrementar su impacto en todas las esferas de la economía y la sociedad a corto, mediano y largo plazos. Asegurar el respaldo económico-financiero de cada sistema en correspondencia con la naturaleza y objetivos de sus actividades', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(153, 'XX00000153', 0, 102, 'Sostener y desarrollar los resultados alcanzados en el campo de la biotecnología, la producción médico-farmacéutica, las ciencias básicas, las ciencias naturales, las ciencias agropecuarias, los estudios y el empleo de las fuentes renovables de energía, las tecnologías sociales y educativas, la transferencia tecnológica industrial, la producción de equipos de tecnología avanzada, la nanotecnología y los servicios científicos y tecnológicos de alto valor agregado', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(154, 'XX00000154', 0, 103, 'Continuar fomentando el desarrollo de investigaciones sociales y humanísticas sobre los asuntos prioritarios de la vida de la sociedad, así como perfeccionar los métodos de introducción de sus resultados en la toma de decisiones en los diferentes niveles, por los organismos, entidades e instituciones', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(155, 'XX00000155', 0, 104, 'Prestar mayor atención a la formación y capacitación continuas del personal técnico y cuadros calificados que respondan y se anticipen, con responsabilidad social, al desarrollo científico-tecnológico en las principales áreas de la producción y los servicios, así como a la prevención y mitigación de impactos sociales y medioambientales', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(156, 'XX00000156', 0, 105, 'Actualizar las vías existentes y definir e impulsar otras para estimular la creatividad de los colectivos laborales de base y fortalecer su participación en la solución de los problemas tecnológicos de la producción y los servicios, así como la promoción de formas productivas ambientalmente sostenibles', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(157, 'XX00000157', 0, 106, 'Asegurar la estabilidad, el completamiento y rejuvenecimiento del potencial científico-tecnológico de los sistemas de ciencia, tecnología, innovación y medio ambiente, para retomar su crecimiento selectivo, escalonado, proporcionado y sostenible. Perfeccionar los diferentes mecanismos de estimulación', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(158, 'XX00000158', 0, 107, 'Acelerar la implantación de las directivas y de los programas de ciencia, tecnología e innovación, dirigidos al enfrentamiento del cambio climático, por todos los organismos y entidades, integrando todo ello a las políticas territoriales y sectoriales, con prioridad en los sectores agropecuario, hidráulico y de la salud. Incrementar la información y capacitación que contribuyan a objetivar la percepción de riesgo a escala de toda la sociedad', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(159, 'XX00000159', 0, 108, 'Avanzar gradualmente, según lo permitan las posibilidades económicas, en el proceso de informatización de la sociedad, el desarrollo de la infraestructura de telecomunicaciones y la industria de aplicaciones y servicios informáticos. Sustentar este avance en unsistema de ciberseguridad que proteja nuestra soberanía tecnológica y asegure el enfrentamiento al uso ilegal de las tecnologías de la información y la comunicación. Instrumentar mecanismos de colaboración internacional en este campo', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(160, 'XX00000160', 1, 6, 'VI. POLÍTICA SOCIAL', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(161, 'XX00000161', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(162, 'XX00000162', 0, 113, 'Potenciar el papel de la inversión extranjera directa en la introducción en el país de tecnologías de avanzada a nivel internacional y promover la creación de estructuras dinamizadoras (empresas de alta tecnología, parques científicos y tecnológicos, incubadoras de empresas, zonas especiales de desarrollo y otras)', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(163, 'XX00000163', 0, 114, 'Definir y crear la categoría de empresas de alta tecnología con estímulos fiscales y tributarios, para promover las empresas que basan su economía en el uso de la ciencia y la innovación tecnológica', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(164, 'XX00000164', 0, 115, 'Promover y propiciar la interacción entre los sectores empresarial, presupuestado, académico, el sistema educativo y formativo, y las entidades de ciencia, tecnología e innovación, incentivando que los resultados científicos y tecnológicos se apliquen y generalicen en la producción y los servicios', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(165, 'XX00000165', 1, 7, 'DINAMICA DEMOGRAFICA', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(166, 'XX00000166', 0, 116, 'Impulsar el desarrollo integral y pleno de los seres humanos. Continuar consolidando las conquistas de la Revolución, tales como el acceso a la atención médica, la educación, la cultura, el deporte, la recreación, la justicia, la tranquilidad ciudadana, la seguridad social y la protección mediante la asistencia social a las personas que lo necesiten. Promover y reafirmar la adopción de los valores, prácticas y actitudes que deben distinguir a nuestra sociedad', 2012, 2018, 160, 161, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(167, 'XX00000167', 1, 2, 'EDUCACIÓN', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(168, 'XX00000168', 0, 117, 'Aunar los esfuerzos de las instituciones educativas, culturales, organizaciones políticas, de masas, las formas asociativas sin ánimo de lucro y de los medios de comunicación masiva, en todas sus expresiones y de aquellos factores que influyen en la comunidad y en la familia, para cultivar en la sociedad el conocimiento de nuestra historia, cultura e identidad, y al propio tiempo la capacidad para asumir una posición crítica y descolonizada ante los productos de la industria cultural hegemónica capitalista', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(169, 'XX00000169', 0, 118, 'Dar continuidad al perfeccionamiento de la educación, la salud, la cultura y el deporte, para lo cual resulta imprescindible reducir o eliminar gastos excesivos en la esfera social, así como generar nuevas fuentes de ingreso y evaluar todas las actividades que puedan pasar del sector presupuestado al sistema empresarial', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(170, 'XX00000170', 0, 119, 'Garantizar la implantación gradual de la política para atender los elevados niveles de envejecimiento de la población. Estimular la fecundidad con el fin de acercarse al remplazo poblacional en una perspectiva mediata. Continuar estudiando este tema con integralidad', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(171, 'XX00000171', 0, 120, 'Establecer el nivel educacional mínimo con carácter obligatorio; continuar avanzando en la elevación de la calidad y el rigor del proceso docente-educativo, así como en el fortalecimiento del papel del profesor frente al alumno; incrementar la eficiencia del ciclo escolar, jerarquizar la superación permanente, el enaltecimiento y atención al personal docente, el mejoramiento de las condiciones de trabajo y el perfeccionamiento del papel de la familia en la educación de los niáos, adolescentes y jóvenes', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(172, 'XX00000172', 0, 121, 'Formar con calidad y rigor el personal docente que se precisa en cada provincia y municipio para dar respuesta a las necesidades de los centros educativos de los diferentes niveles de enseáanza', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(173, 'XX00000173', 0, 122, 'Avanzar en la informatización del sistema de educación. Desarrollar, de forma racional, los servicios en el uso de la red telemática y la tecnología educativa, así como la generación de contenidos digitales y audiovisuales', 2012, 2018, 160, 167, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(177, 'XX00000177', 1, 3, 'SALUD', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(178, 'XX00000178', 0, 123, 'Ajustar la capacidad de la red escolar y el personal docente en la educación primaria, y ampliar las capacidades de los círculos infantiles en correspondencia con el desarrollo económico, sociodemográfico y los lugares de residencia. Brindar especial atención al Plan Turquino', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(179, 'XX00000179', 0, 124, 'Lograr que las matrículas en las diferentes especialidades y carreras estén en correspondencia con el desarrollo de la economía y la sociedad; incrementar la matrícula en carreras agropecuarias, pedagógicas, tecnológicas y de ciencias básicas afines. Garantizar de conjunto con las entidades de la producción y los servicios, las organizaciones políticas, estudiantiles y de masas y con la participación de la familia, la formación vocacional y orientación profesional desde la primaria. Continuar potenciando el reconocimiento a la labor de los técnicos medios y obreros calificados', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(180, 'XX00000180', 0, 125, 'Consolidar el cumplimiento de la responsabilidad de los organismos, entidades, consejos de la administración y otros actores económicos, en la formación y desarrollo de la fuerza de trabajo calificada. Actualizar los programas de formación e investigación de las universidades en función de las necesidades del desarrollo, de las nuevas tecnologías y de la actualización del Modelo Económico y Social', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(181, 'XX00000181', 0, 126, 'Elevar la calidad del servicio que se brinda, el cumplimiento de la ética médica, lograr la satisfacción de la población, así como el mejoramiento de las condiciones de trabajo y la atención al personal de la salud. Garantizar la utilización eficiente de los recursos, el ahorro y la eliminación de gastos innecesarios', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(182, 'XX00000182', 0, 127, 'Fortalecer las acciones de salud con la participación intersectorial y comunitaria en la promoción y prevención para el mejoramiento del estilo de vida, que contribuyan a incrementar los niveles de salud de la población', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(183, 'XX00000183', 0, 128, 'Garantizar la sostenibilidad de las acciones interdisciplinarias, sectoriales, intersectoriales y comunitarias dirigidas al mejoramiento de las condiciones higiénico-epidemiológicas que determinan las enfermedades transmisibles que más impactan en el cuadro de salud y afectan el medio ambiente, con énfasis en las enfermedades de transmisión hídrica, por alimentos y por vectores', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(184, 'XX00000184', 0, 129, 'Dar continuidad al proceso de reorganización, compactación y regionalización de los servicios de salud, con la calidad necesaria, incluyendo la atención de urgencias y el transporte sanitario, a partir de las necesidades de cada provincia y municipio. Garantizar que el propio Sistema de Salud facilite que cada paciente reciba la atención correspondiente', 2012, 2018, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(185, 'XX00000185', 1, 4, 'DEPORTE', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(186, 'XX00000186', 0, 131, 'Consolidar la implantación del Programa Nacional de Medicamentos y la eficiencia de los servicios farmacéuticos', 2012, 2018, 160, 185, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(187, 'XX00000187', 0, 132, 'Asegurar el cumplimiento del Plan de Acciones para garantizar el desarrollo y consolidación de la Medicina Natural y Tradicional', 2012, 2018, 160, 185, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(188, 'XX00000188', 1, 5, 'CULTURA', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(189, 'XX00000189', 0, 133, 'Garantizar la formación, desarrollo y estabilidad de los especialistas médicos para dar respuesta a las necesidades del país, incluido el desarrollo de la atención de pacientes extranjeros en Cuba, y a las que se generen por los compromisos internacionales', 2012, 2018, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(190, 'XX00000190', 0, 134, 'Priorizar el perfeccionamiento integral del sistema deportivo cubano, atemperado a las condiciones tanto nacionales como internacionales. Continuar promoviendo el desarrollo de la cultura física y lograr la práctica masiva del deporte que contribuya a elevar la calidad de vida de la población, teniendo a la escuela como eslabón fundamental. Mantener resultados satisfactorios en los eventos internacionales', 2012, 2018, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(191, 'XX00000191', 1, 6, 'SEGURIDAD SOCIAL', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(192, 'XX00000192', 0, 137, 'Garantizar la defensa y salvaguarda del patrimonio cultural, material e inmaterial de la nación cubana', 2012, 2018, 160, 191, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(193, 'XX00000193', 0, 138, 'Continuar elevando la calidad y rigor de la enseáanza artística profesional, a partir del mejoramiento de las condiciones de las instituciones educacionales y la elevación de la preparación del personal docente', 2012, 2018, 160, 191, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(194, 'XX00000194', 1, 7, 'EMPLEO Y SALARIOS', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(195, 'XX00000195', 0, 139, 'Diseáar la política sobre el cine cubano encaminada a fomentar la creación cinematográfica y audiovisual', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(196, 'XX00000196', 0, 140, 'Disminuir la participación relativa del Presupuesto del Estado en el financiamiento de la seguridad social, la que continuará creciendo a partir del incremento del número de personas jubiladas, por lo que es necesario seguir extendiendo la contribución de los trabajadores del sector estatal y la aplicación de regímenes especiales de contribución en el sector no estatal', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(197, 'XX00000197', 0, 141, 'Garantizar que la protección de la asistencia social la reciban las personas que realmente la necesitan, estén impedidas para el trabajo y no cuenten con familiares que brinden apoyo. Continuar consolidando y perfeccionando el Sistema de Prevención, Asistencia y Trabajo Social', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, 'XX');
INSERT INTO tpoliticas (id, id_code, titulo, numero, nombre, inicio, fin, capitulo, grupo, observacion, if_inner, id_proceso, id_proceso_code, cronos, cronos_syn, situs) VALUES
(198, 'XX00000198', 0, 142, 'Rescatar el papel del trabajo y los ingresos que por él se obtienen como vía principal para generar productos y servicios de calidad e incremento de la producción y la productividad, y lograr la satisfacción de las necesidades fundamentales de los trabajadores y su familia', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(199, 'XX00000199', 0, 143, 'Proyectar la formación de fuerza de trabajo calificada en correspondencia con el Plan Nacional de Desarrollo Económico y Social, a mediano y largo plazos.', 2012, 2018, 160, 194, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(201, 'XX00000201', 1, 8, 'GRATUIDADES Y SUBSIDIOS', 2012, 2018, 160, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(202, 'XX00000202', 0, 144, 'Ampliar el trabajo en el sector no estatal, como una alternativa más de empleo, en dependencia de las nuevas formas organizativas de la producción y los servicios que se establezcan', 2012, 2018, 160, 201, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(203, 'XX00000203', 0, 145, 'El incremento de los ingresos en el sector empresarial será según la creación de la riqueza y las posibilidades económico-financieras de las empresas, promoviendo la evaluación sistemática de sus resultados de conjunto con el movimiento sindical. En el sector presupuestado se hará gradualmente, en correspondencia con las prioridades que se establezcan y las posibilidades de la economía', 2012, 2018, 160, 201, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(204, 'XX00000204', 0, 146, 'Proyectar la formación de fuerza de trabajo calificada en correspondencia con el Plan Nacional de Desarrollo Económico y Social, a mediano y largo plazos', 2012, 2018, 160, 201, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(206, 'XX00000206', 1, 7, 'VII POLÍTICA AGROINDUSTRIAL ', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(207, 'XX00000207', 0, 147, 'Continuar, en correspondencia con la situación económica del país y los ingresos de las personas, el proceso de eliminación gradual de gratuidades indebidas y subsidios excesivos, bajo el principio de subsidiar a las personas necesitadas y no a productos', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(208, 'XX00000208', 0, 148, 'Dar continuidad a la eliminación ordenada y gradual de los productos de la libreta de abastecimiento, como forma de distribución normada, igualitaria y a precios subsidiados', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(209, 'XX00000209', 0, 149, 'Mantener la alimentación que se brinda en la esfera de los servicios sociales, dando prioridad a las instituciones de salud y centros educacionales que lo requieran. Perfeccionar las vías para proteger a la población vulnerable o de riesgo en la alimentación', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(210, 'XX00000210', 0, 150, 'Lograr que la producción agroindustrial contribuya al desarrollo de la economía del país y se exprese en un aumento de su participación en el Producto Interno Bruto, con una mayor oferta de alimentos con destino al consumo interno, la disminución de importaciones y el incremento de las exportaciones. Disminuir la alta dependencia de financiamiento que hoy se cubre con los ingresos de otros sectores', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(211, 'XX00000211', 0, 151, 'Continuar transformando el modelo de gestión, en correspondencia con la mayor presencia de formas productivas no estatales, en el que la empresa estatal agropecuaria se constituya en el gestor principal del desarrollo tecnológico y de las estrategias de producción y comercialización. Utilizar de manera efectiva las relaciones monetario-mercantiles y consolidar la autonomía otorgada a los productores, para incrementar la eficiencia y la competitividad', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(212, 'XX00000212', 0, 152, 'Lograr que los productores agropecuarios cuenten con un programa de desarrollo en correspondencia con la estrategia del país. Introducir de forma gradual las cooperativas de servicios en la actividad agroindustrial', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(213, 'XX00000213', 0, 153, 'Garantizar el servicio bancario especializado al sector agroindustrial, que tenga en cuenta los ciclos de producción y el nivel de riesgos. Fortalecer y ampliar la actividad de seguros agropecuarios, propiciando una mayor eficacia en su aplicación', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(214, 'XX00000214', 0, 154, 'Continuar la transformación del sistema de comercialización de insumos, equipamientos y servicios, que garantice el acceso directo de los productores al mercado, según su eficiencia y capacidad financiera, asegurando la disponibilidad y oportunidad de los recursos con una adecuada correspondencia entre la calidad y los precios', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(215, 'XX00000215', 0, 155, 'Continuar la transformación de la comercialización de productos agropecuarios, evaluar sus resultados y adoptar las medidas\r\n28\r\nnecesarias para superar las dificultades. Priorizar el pago a los productores en los plazos establecidos; perfeccionar e integrar todos los elementos del sistema para contribuir a mejorar la oferta y la satisfacción de la población, en cuanto a precios, calidad y estabilidad. Desarrollar progresivamente la oferta de servicios complementarios', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(216, 'XX00000216', 0, 156, 'Perfeccionar la acción reguladora del Estado y los procedimientos en la formación del precio de acopio de los productos agropecuarios, para estimular a los productores primarios. Se tendrá en cuenta el comportamiento de los precios en el mercado internacional', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(217, 'XX00000217', 0, 157, 'Desarrollar una política integral que estimule la incorporación, permanencia y estabilidad de la fuerza laboral en el campo, en especial de jóvenes y mujeres, para que simultáneamente con la introducción de las nuevas tecnologías en la agricultura, garanticen el incremento de la producción agropecuaria. Avanzar de modo integral en la recuperación y desarrollo de las comunidades rurales, considerando las complejidades de las zonas montaáosas y costeras', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(218, 'XX00000218', 0, 158, 'Diseáar y aplicar servicios de asistencia técnica, capacitación y extensión agraria, para asimilar eficientemente las nuevas tecnologías que contribuyan a una mejor organización de la fuerza laboral, aseguren el aumento de la productividad y tengan en cuenta las transformaciones ocurridas y proyectadas en el sector', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(219, 'XX00000219', 0, 159, 'Desarrollar una agricultura sostenible, empleando una gestión integrada de ciencia, tecnología y medio ambiente, aprovechando y fortaleciendo las capacidades disponibles en el país, además que reconozca las diversas escalas productivas', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(220, 'XX00000220', 0, 160, 'Priorizar la conservación, protección y mejoramiento de los recursos naturales, entre ellos, el suelo, el agua y los recursos zoo y fitogenéticos. Recuperar la producción de semillas de calidad, la genética animal y vegetal; así como el empleo de productos biológicos nacionales', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(221, 'XX00000221', 0, 161, 'Sostener y desarrollar investigaciones integrales para proteger, conservar y rehabilitar el medio ambiente, evaluar impactos económicos y sociales de eventos extremos, y adecuar la política ambiental a las proyecciones del entorno económico y social. Ejecutar programas para la conservación, rehabilitación y uso racional de recursos naturales. Fomentar los procesos de educación ambiental, considerando todos los actores de la sociedad', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(222, 'XX00000222', 0, 162, 'Asegurar un efectivo y sistemático control estatal sobre la tenencia y el uso de la tierra, para contribuir a su explotación eficiente y al incremento sostenido de las producciones. Continuar la entrega de tierras en usufructo y la reducción de las áreas ociosas', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(223, 'XX00000223', 0, 163, 'Continuar priorizando la producción de alimentos que puedan ser obtenidos eficientemente en el país. Los recursos e inversiones bajo el principio de encadenamientos productivos, necesarios para ello, deberán destinarse a donde existan mejores condiciones para su empleo más efectivo', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(224, 'XX00000224', 0, 164, 'Continuar la reorganización y el desarrollo de las actividades de riego, drenaje, abasto de agua a los animales y los servicios de maquinaria agropecuaria con el objetivo de lograr el uso racional del agua, de la infraestructura hidráulica y de los equipos agropecuarios, contribuir al incremento de la productividad y al ahorro de fuerza de trabajo, combinando el uso de la tracción animal con tecnologías de avanzada. Garantizar los servicios de mantenimiento y reparaciones', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(225, 'XX00000225', 0, 165, 'Organizar la producción en los polos productivos agropecuarios encargados de abastecer las grandes ciudades y la industria alimentaria, lograr una efectiva sustitución de importaciones e incrementar las exportaciones, aplicando un enfoque de cadena productiva de todos los eslabones que se articulan en torno al complejo agroindustrial, con independencia de la organización empresarial a la que se vinculen', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(226, 'XX00000226', 0, 166, 'En la organización de la producción agropecuaria, destinada fundamentalmente al consumo interno, deberá predominar un enfoque territorial, integrándose con las minindustrias, las que además podrán vincularse a la industria, con el objetivo de lograr una mayor eficiencia, aumentar la calidad y mejorar la presentación; ahorrar transporte y gastos de distribución', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(227, 'XX00000227', 0, 167, 'Desarrollar la política ganadera, priorizando las especies vacuna, porcina y avícola. La ganadería vacuna debe sustentarse en el aprovechamiento del fondo de tierras, la recuperación de la infraestructura, los pastos y los forrajes, así como el mejoramiento genético de los rebaáos y la elevación de los rendimientos, para incrementar la producción de leche y carne, haciendo un uso eficiente de la mecanización. Perfeccionar el control de la masa, asegurar el servicio veterinario, la producción de medicamentos y la biotecnología reproductiva. Desarrollar el ganado menor en las regiones del país con condiciones favorables para ello', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(228, 'XX00000228', 0, 168, 'Incrementar la producción de viandas y hortalizas con una adecuada estructura de cultivos, sobre la base de aumentar los rendimientos y lograr una mejor utilización del balance de áreas de cultivos varios', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(229, 'XX00000229', 0, 169, 'Asegurar el cumplimiento de los programas de producción de arroz, frijol, maíz y otros granos que garanticen el incremento productivo, para contribuir a la reducción gradual de las importaciones de estos productos y aumentar el consumo', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(230, 'XX00000230', 0, 170, 'Impulsar el desarrollo de las actividades tabacalera, cafetalera, apícola, del cacao y otros rubros, para contribuir a la recuperación gradual de las exportaciones. En la producción tabacalera explotar al máximo las posibilidades del mercado externo', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(231, 'XX00000231', 0, 171, 'Reanimar la agroindustria citrícola. Continuar el incremento y diversificación de la producción de frutales, asegurar el acopio y comercialización eficientes de las frutas frescas e industrializadas en los mercados nacional e internacional', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(232, 'XX00000232', 0, 172, 'Desarrollar un programa integral de mantenimiento, conservación y fomento de plantaciones forestales que priorice la protección de las cuencas hidrográficas, en particular las presas, las franjas hidrorreguladoras, las montaáas y las costas; así como incrementar las plantaciones en el llano y la premontaáa, aumentar la producción de madera y otros productos del bosque', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(233, 'XX00000233', 0, 173, 'Continuar desarrollando el autoabastecimiento alimentario municipal, apoyándose en el Programa Nacional de agricultura urbana, suburbana y familiar, aprovechando los recursos locales y la tracción animal', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(234, 'XX00000234', 0, 174, 'Desarrollar la industria alimentaria y de bebidas, incluyendo la actividad local, en función de lograr un mayor aprovechamiento de las materias primas, la diversificación de la producción y el incremento de la oferta al mercado interno y de las exportaciones', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(235, 'XX00000235', 0, 175, 'Aplicar los sistemas de gestión de la calidad en correspondencia con las normas establecidas y las exigencias de los clientes, para asegurar, entre otros objetivos, la inocuidad de los alimentos', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(236, 'XX00000236', 0, 176, 'La agroindustria de la caáa de azúcar, como sector estratégico, deberá continuar incrementando su eficiencia agrícola e industrial, así como aumentar la producción de caáa, modernizar el equipamiento y mejorar el aprovechamiento de la capacidad de molida', 2012, 2018, 206, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(243, 'XX00000243', 0, 243, 'Perfeccionar la gestión integrada del agua en la cuenca hidrográfica como unidad de gestión territorial, con prioridad en las estrategias preventivas para la reducción de la generación de residuales y emisiones en la fuente de origen, que contribuya a asegurar la cantidad y calidad del agua', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(245, 'XX00000245', 1, 8, 'VIII. POLÍTICA INDUSTRIAL Y ENERGÉTICA POLÍTICA INDUSTRIAL', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(246, 'XX00000246', 1, 1, 'LINEAMIENTOS GENERALES', 2012, 2018, 245, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(247, 'XX00000247', 0, 177, 'Aumentar de forma gradual la producción de azúcar, diversificar las producciones teniendo en cuenta las exigencias del mercado internacional e interno, y avanzar en la creación, recuperación y explotación de las plantas de derivados, priorizando las destinadas a la obtención de alcohol, alimento animal y los bioproductos. Continuar incrementando la entrega de electricidad al Sistema Electroenergético Nacional', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(248, 'XX00000248', 0, 178, 'Incrementar la eficiencia de las pesquerías cumpliendo las regulaciones pesqueras. Modernizar las embarcaciones y emplear artes de pesca selectivas que garanticen la calidad de las capturas y la preservación\r\n32\r\ndel medio marino y costero. Incrementar los ingresos por exportaciones, fundamentalmente en el camarón de cultivo', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(249, 'XX00000249', 0, 179, 'Desarrollar la acuicultura aplicando técnicas modernas de cultivo, con elevada disciplina tecnológica y mejora constante de la genética. Reanimar la industria pesquera e incrementar la oferta, variedad y calidad de productos al mercado interno', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(250, 'XX00000250', 0, 180, 'Definir una política tecnológica que contribuya a reorientar el desarrollo industrial, que comprenda el control de las tecnologías existentes en el país, a fin de promover su modernización sistemática, observando los principios de la Política medioambiental del país', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(251, 'XX00000251', 0, 181, 'Desarrollar la industria, priorizando los sectores que dinamizan la economía o contribuyen a su transformación estructural, avanzando en la modernización, desarrollo tecnológico y elevando su respuesta a las demandas de la economía', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(252, 'XX00000252', 0, 182, 'Prestar atención prioritaria al impacto ambiental asociado al desarrollo industrial existente y proyectado, en particular, en las ramas de la química; la industria del petróleo y la minería, en especial el níquel; el cemento y otros materiales de construcción; así como en los territorios más afectados, incluyendo el fortalecimiento de los sistemas de control y monitoreo', 2012, 2018, 245, 246, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(253, 'XX00000253', 1, 2, 'LINEAMIENTOS PARA LAS PRINCIPALES RAMAS', 2012, 2018, 245, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(254, 'XX00000254', 0, 183, 'Intensificar el proceso de reestructuración y redimensionamiento del plantel industrial, concentrando la industria en capacidades eficientes, con un empleo racional de instalaciones, equipos y fuerza de trabajo', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(255, 'XX00000255', 0, 184, 'Priorizar la reactivación del mantenimiento industrial, incluyendo la producción y recuperación de partes, piezas de repuesto y herramentales', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(256, 'XX00000256', 0, 185, 'Consolidar la industria farmacéutica y biotecnológica como una de las actividades de mayor capacidad exportadora de la economía, diversificar productos y mercados e incorporar nuevos productos al mercado nacional para sustituir importaciones. Desarrollar la industria de suplementos dietéticos y medicamentos naturales', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(257, 'XX00000257', 0, 186, 'Mejorar la posición de la industria del níquel en los mercados mediante el incremento y diversificación de la producción, elevación de la calidad de sus productos y reducción de los costos, logrando una mejor utilización de los recursos minerales', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(258, 'XX00000258', 0, 187, 'Ejecutar con celeridad los proyectos en marcha para la exploración de pequeáos yacimientos de minerales, en particular para la producción de oro, cobre, cromo, plomo y zinc. Priorizar las inversiones para la explotación de yacimientos de plata', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(259, 'XX00000259', 0, 188, 'Desarrollar la industria electrónica y la automática, diversificando sus producciones y elevando su capacidad tecnológica, con vistas a potenciar la sustitución de importaciones, incrementar las exportaciones y los servicios', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(260, 'XX00000260', 0, 189, 'Desarrollar las producciones químicas, priorizando la industria transformativa del plástico, las producciones de cloro, sal, fertilizantes y neumáticos. Fortalecer las capacidades de recape en el país. Avanzar en los estudios que posibiliten un mayor empleo de las producciones mineras nacionales a partir de rocas y minerales industriales', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(261, 'XX00000261', 0, 190, 'Desarrollar las industrias productoras de envases y embalajes. Priorizar la producción de envases demandados por las actividades exportadoras y el desarrollo agroalimentario', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(262, 'XX00000262', 0, 191, 'Recuperar e incrementar la producción de materiales para la construcción que aseguren los programas inversionistas priorizados del país (turismo, viviendas, industriales, entre otros), la expansión de las exportaciones y la venta a la población. Desarrollar producciones con mayor valor agregado y calidad. Lograr incrementos significativos en los niveles y diversidad de las producciones locales de materiales de construcción y divulgar sus normas de empleo', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(263, 'XX00000263', 0, 192, 'Desarrollar la metalurgia ferrosa, priorizando la ampliación de capacidades, la reducción de los consumos energéticos y la diversificación de la producción de laminados y de metales conformados, elevando su calidad', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(264, 'XX00000264', 0, 193, 'Promover la intensificación del reciclaje y el aumento del valor agregado de los productos recuperados. Priorizar el aprovechamiento del potencial de los residuos sólidos urbanos', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(265, 'XX00000265', 0, 194, 'Desarrollar la industria metal-mecánica y de bienes de capital, a partir de la reorganización productiva de las capacidades existentes, la recuperación y modernización de máquinas herramientas y equipos, y la realización de inversiones en nuevos procesos de mayor nivel tecnológico', 2012, 2018, 245, 253, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(273, 'XX00000273', 1, 3, 'POLITICA ENERGÉTICA', 2012, 2018, 245, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(274, 'XX00000274', 0, 195, 'Elevar la competitividad de la industria ligera, potenciando los encadenamientos productivos, el diseáo y asegurar la gestión de la calidad. Concluir el proceso de reordenamiento y reestructuración del sistema empresarial, incluyendo el paso a nuevas formas de gestión', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(275, 'XX00000275', 0, 196, 'Perfeccionar el modelo de gestión de la industria local, flexibilizando su operación para posibilitar el desarrollo de producciones artesanales y la fabricación de bienes de consumo en pequeáas series o a la medida, así como la prestación de servicios de reparación y mantenimiento. Ello incluye la apertura de mayores espacios para actividades no estatales. Prestar atención a los talleres especiales donde laboran personas con limitaciones', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(276, 'XX00000276', 0, 197, 'Elevar la producción nacional de crudo y gas acompaáante, desarrollando los yacimientos conocidos e incorporando la recuperación mejorada. Acelerar los estudios geológicos encaminados a poder contar con nuevos yacimientos, incluidos los trabajos de exploración en la Zona Económica Exclusiva del Golfo de México', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(277, 'XX00000277', 0, 198, 'Elevar la eficiencia y el rendimiento del sistema de refinación en Cuba, que permita incrementar los volúmenes de productos de mayor valor agregado', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(278, 'XX00000278', 0, 199, 'Elevar la eficiencia en la generación eléctrica, dedicando la atención y recursos necesarios al mantenimiento de las plantas en operación, y lograr altos índices de disponibilidad en las plantas térmicas y en las instalaciones de generación con grupos electrógenos', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(279, 'XX00000279', 0, 200, 'Ejecutar el programa de construcción, montaje y puesta en marcha de nuevas capacidades de generación térmica y prestar atención priorizada al completamiento de las capacidades de generación en los ciclos combinados de Boca de Jaruco y Varadero', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(280, 'XX00000280', 0, 201, 'Mantener una política activa en el acomodo de la carga eléctrica, que disminuya la demanda máxima y reduzca su impacto sobre las capacidades de generación', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(281, 'XX00000281', 0, 202, 'Proseguir el programa de rehabilitación y modernización de redes y subestaciones eléctricas, de eliminación de zonas de bajo voltaje, para lograr los ahorros planificados por disminución de las pérdidas en la distribución y transmisión de energía eléctrica. Avanzar en el Programa aprobado de electrificación en zonas aisladas del Sistema Electroenergético Nacional, en correspondencia con las necesidades y posibilidades del país, utilizando las fuentes más económicas', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(282, 'XX00000282', 0, 203, 'Fomentar la cogeneración y trigeneración en todas las actividades con posibilidades', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(283, 'XX00000283', 0, 204, 'Acelerar el cumplimiento del Programa aprobado hasta 2030 para el desarrollo de las fuentes renovables y el uso eficiente de la energía', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(284, 'XX00000284', 0, 205, 'Se priorizará la identificación permanente del potencial de ahorro en el sector estatal y privado, así como la ejecución de acciones para su captación', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(285, 'XX00000285', 0, 206, 'Concebir las nuevas inversiones, el mantenimiento constructivo y las reparaciones capitalizables con soluciones para el uso eficiente de la energía, instrumentando adecuadamente los procedimientos de supervisión', 2012, 2018, 245, 273, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(289, 'XX00000289', 1, 9, 'IX. POLÍTICA PARA EL TURISMO', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(290, 'XX00000290', 0, 207, 'Perfeccionar el trabajo de planificación y control del uso de los portadores energéticos, ampliando los elementos de medición y la calidad de los indicadores de eficiencia e índices de consumo establecidos', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(291, 'XX00000291', 0, 208, 'Proyectar el sistema educativo y los medios de comunicación masiva en función de profundizar en la calidad e integralidad de la política enfocada al ahorro y al uso eficiente y sostenible de la energía', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(292, 'XX00000292', 0, 209, 'La actividad turística deberá tener un crecimiento acelerado que garantice la sostenibilidad y dinamice la economía, incrementando de manera sostenida los ingresos y las utilidades, diversificando los mercados emisores y segmentos de clientes, y maximizando el ingreso medio por turista', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(293, 'XX00000293', 0, 210, 'Continuar incrementando la competitividad de Cuba en los mercados turísticos, diversificando las ofertas, potenciando la capacitación de los recursos humanos y la elevación de la calidad de los servicios con una adecuada relación calidad-precio', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(294, 'XX00000294', 0, 211, 'Perfeccionar las formas de comercialización, utilizando las tecnologías más avanzadas de la información y la comunicación, y potenciando la comunicación promocional', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(295, 'XX00000295', 0, 212, 'La actividad no estatal en alojamiento, gastronomía y otros servicios se continuará desarrollando como oferta turística complementaria a la estatal', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(296, 'XX00000296', 0, 213, 'Consolidar el mercado interno, creando y diversificando ofertas que posibiliten el mayor aprovechamiento de las infraestructuras, así como otras ofertas que faciliten a los cubanos residentes en el país viajar al exterior como turistas', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(297, 'XX00000297', 0, 214, 'Continuar incrementando la participación de la industria y los servicios del país en los recursos que se utilizan en la operación e inversión turística. La participación de la industria nacional deberá desarrollarse con financiamiento a largo plazo', 2012, 2018, 289, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(304, 'XX00000304', 1, 10, 'X. POLÍTICA PARA EL TRANSPORTE', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(305, 'XX00000305', 0, 215, 'Continuar priorizando la reparación, el mantenimiento, renovación y actualización de la infraestructura turística y de apoyo. Aplicar políticas que garanticen la sostenibilidad de su desarrollo, e implementar medidas para disminuir el índice de consumo de agua y de portadores energéticos e incrementar la utilización de fuentes de energía renovable y el reciclaje de los desechos que se generan en la prestación de los servicios turísticos, en armonía con el medio ambiente', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(306, 'XX00000306', 0, 216, 'Velar porque las expresiones artísticas vinculadas a las actividades turísticas respondan fielmente a la política cultural trazada por la Revolución cubana', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(307, 'XX00000307', 0, 217, 'Continuar la recuperación, modernización, reposición y reordenamiento del transporte automotor tanto estatal como no estatal, fomentando el desarrollo de los servicios técnicos y el incremento de la seguridad vial,\r\n38\r\ncon una mayor participación de la industria nacional en la fabricación de piezas de repuesto y medios de transporte. Garantizar el cumplimiento con efectividad y eficacia del plan estratégico nacional de seguridad vial', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(308, 'XX00000308', 0, 218, 'Perfeccionar la organización y el control de los servicios que prestan los porteadores privados. Facilitarles, en correspondencia con las posibilidades de la economía, el acceso a piezas y accesorios, combustibles y otros recursos, de modo que se favorezca la legalidad, seguridad y calidad de este servicio', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(309, 'XX00000309', 0, 219, 'Garantizar la utilización de los esquemas y medios más eficientes para cada tipo de transportación. Perfeccionar el balance de cargas y lograr un adecuado funcionamiento de la cadena puerto-transporte-economía interna, aprovechando las ventajas comparativas del ferrocarril, del cabotaje, de las empresas especializadas y del empleo de contenedores, para lograr la integración multimodal', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(310, 'XX00000310', 0, 220, 'Impulsar el programa de recuperación y desarrollo del ferrocarril dentro del proceso inversionista del país. Considerar fuentes de financiamiento a largo plazo. Culminar el perfeccionamiento del sistema, con énfasis en el rescate de la disciplina en el funcionamiento de la actividad ferroviaria', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(311, 'XX00000311', 0, 221, 'Desarrollar la flota mercante nacional y los astilleros, como forma de propiciar el incremento en la recaudación de divisas y el ahorro por concepto de flete', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(312, 'XX00000312', 0, 222, 'Elevar la eficiencia de las operaciones marítimo-portuarias a partir de la organización de sistemas de trabajo que permitan alcanzar ritmos superiores en la manipulación de mercancías, y una mayor eficiencia en la atención a los cruceros, incluyendo la modernización y el mantenimiento oportuno de la infraestructura portuaria y su equipamiento, el sistema de seguridad marítima, así como el dragado de los principales puertos del país', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(313, 'XX00000313', 0, 223, 'Fomentar el diseáo de formas organizativas estatales y no estatales en las transportaciones de pasajeros y carga, así como en otros servicios vinculados con la actividad, en correspondencia con las características de cada territorio', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(314, 'XX00000314', 0, 224, 'Continuar la modernización y ampliación de la flota aérea cubana de pasajeros y de carga, fundamentalmente de corto alcance, de la infraestructura aeroportuaria, así como lograr mayor eficiencia en los servicios que se prestan, con el objetivo de asegurar el crecimiento del turismo y la demanda nacional', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(315, 'XX00000315', 0, 225, 'Incrementar los niveles de satisfacción de la demanda de transportación de pasajeros, con estabilidad y calidad, en un ambiente de integración multimodal con la participación de las diferentes formas de gestión, que facilite la movilidad de una población que envejece, en función de sus necesidades y las de la economía. Prestar especial atención a las zonas de difícil acceso', 2012, 2018, 304, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(323, 'XX00000323', 1, 11, 'XI. POLÍTICA PARA LAS CONSTRUCCIONES, VIVIENDAS Y RECURSOS HIDRAULICOS', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(324, 'XX00000324', 1, 1, 'CONSTRUCCIONES', 2012, 2018, 323, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(325, 'XX00000325', 0, 226, 'Implementar nuevas formas de cobro en el transporte urbano y rural de pasajeros en función de minimizar la evasión del pago y el desvío de la recaudación', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(326, 'XX00000326', 0, 227, 'Potenciar la recuperación, el mantenimiento y el desarrollo de la infraestructura vial automotor, incluyendo su seáalización', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(327, 'XX00000327', 0, 228, 'Continuar perfeccionando la elaboración del balance de los recursos constructivos del país sobre la base de una mayor coordinación con el proceso de planificación de la economía, la preparación de las organizaciones, la descentralización de facultades y un mayor control, incorporando las nuevas formas no estatales de gestión', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(328, 'XX00000328', 0, 229, 'Elevar la eficiencia en las construcciones empleando sistemas de pago por resultados y calidad más efectivos, aumentando el rendimiento del equipamiento tecnológico y no tecnológico, introduciendo nuevas tecnologías en la construcción y adoptando nuevas formas organizativas, tanto estatales como no estatales', 2012, 2018, 323, 324, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(330, 'XX00000330', 1, 2, 'VIVIENDAS', 2012, 2018, 323, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(331, 'XX00000331', 0, 230, 'Incrementar la creación de empresas especializadas de alcance nacional en las funciones de proyectos y construcción para programas priorizados y otros sectores de la economía que lo requieran', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(332, 'XX00000332', 0, 231, 'Aplicar la actualización de los precios de las construcciones en correspondencia con la política de precios aprobada y asegurar su ulterior perfeccionamiento', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(333, 'XX00000333', 0, 232, 'Mantener la atención prioritaria a las acciones constructivas de conservación y rehabilitación de viviendas. Recuperar viviendas que hoy se emplean en funciones administrativas o estatales, así como inmuebles que pueden asumir funciones habitacionales', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(334, 'XX00000334', 0, 233, 'Mantener la atención prioritaria al aseguramiento del programa de viviendas a nivel municipal, incrementando la producción local y la comercialización de materiales de la construcción, empleando las materias primas y tecnologías disponibles, que permitan aumentar la participación popular, mejorar la calidad y disminuir los costos de los productos', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(335, 'XX00000335', 0, 234, 'Se adoptarán las acciones que correspondan para priorizar la construcción, conservación y rehabilitación de viviendas en el campo, teniendo en cuenta la necesidad de mejorar las condiciones de vida, las particularidades que hacen más compleja esta actividad en la zona rural y estimular la natalidad con el objetivo de contribuir al completamiento y estabilidad de la fuerza de trabajo en el sector agroalimentario', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(336, 'XX00000336', 0, 235, 'Establecer el Programa Nacional de la Vivienda de forma integral, que abarque las directivas principales de la construcción, las formas de gestión para la producción, incluidas la no estatal y por esfuerzo propio, la rehabilitación de viviendas y las urbanizaciones. Definir las prioridades para resolver el déficit habitacional, teniendo en cuenta un mayor aprovechamiento del suelo y el uso de tecnologías más eficientes', 2012, 2018, 323, 330, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(339, 'XX00000339', 1, 3, 'RECURSOS HIDRAULICOS', 2012, 2018, 323, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(340, 'XX00000340', 0, 236, 'Actualizar, ordenar y agilizar los trámites para la remodelación, rehabilitación, construcción, arrendamiento de viviendas y transferencia de propiedad', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(341, 'XX00000341', 0, 237, 'Adecuar la legislación sobre la vivienda al modelo de desarrollo económico y social, asegurando la racionalidad y sustentabilidad de la solución al problema habitacional, manteniendo los principios sociales logrados por la Revolución y diversificando las formas para su acceso y financiamiento', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(342, 'XX00000342', 0, 238, 'Consolidar el balance de agua como herramienta de planificación e instrumentar la evaluación de la productividad del agua para medir la eficiencia en el consumo', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(343, 'XX00000343', 0, 239, 'Continuará desarrollándose el programa hidráulico con inversiones de largo alcance para enfrentar el impacto del cambio climático y materializar las medidas de adaptación: la reutilización del agua; la captación de la lluvia; la desalinización del agua de mar y la sostenibilidad de todos los servicios asociados, que permita alcanzar y superar los objetivos de desarrollo sostenible', 2012, 2018, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(344, 'XX00000344', 1, 12, 'XII. POLÍTICA PARA EL COMERCIO ', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(345, 'XX00000345', 0, 244, 'Modernizar la red de monitoreo del ciclo hidrológico y la calidad del agua, que contribuya al fortalecimiento del sistema de alerta temprana para la mitigación y enfrentamiento a los eventos extremos del clima y afectaciones epidemiológicas, implementando un programa multisectorial para la erradicación paulatina de las fuentes contaminantes categorizadas como principales, que afectan las aguas terrestres', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(346, 'XX00000346', 0, 245, 'Priorizar programas multisectoriales para garantizar el aprovechamiento del agua con inversiones asociadas a fuentes subutilizadas, la hidrometría, el mejoramiento de los sistemas de riego, la introducción de tecnologías eficientes y la automatización de los sistemas de operación y control, que permitan el incremento del área agrícola bajo riego', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(347, 'XX00000347', 0, 246, 'Continuar la reestructuración del comercio mayorista y el minorista, en función de las condiciones en que operará la economía', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(348, 'XX00000348', 0, 247, 'Incrementar y estabilizar la oferta de bienes y servicios a la población y su calidad, incluyendo la oferta de equipos eficientes energéticamente y la prestación de los servicios de posventa, que satisfagan la demanda de los distintos segmentos del mercado, en lo fundamental, a partir de la distribución del ingreso con arreglo al trabajo', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(349, 'XX00000349', 0, 248, 'Elevar la eficacia de los servicios de reparación y mantenimiento de los equipos eléctricos de cocción y otros equipos electrodomésticos, con vistas a lograr su adecuado funcionamiento', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(350, 'XX00000350', 0, 249, 'Avanzar en la venta liberada de gas licuado de petróleo y de otras tecnologías, como opción adicional y a precios no subsidiados', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(351, 'XX00000351', 0, 250, 'Continuar perfeccionando el sistema de abastecimiento del país, aumentando la participación de los productores nacionales. Definir las formas de gestión mayorista que den respuesta a todos los actores de la economía de acuerdo con las posibilidades del país', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(352, 'XX00000352', 0, 251, 'Se desarrollarán mercados de aprovisionamiento que vendan a precios mayoristas y brinden los servicios de alquiler de medios y equipos, sin subsidio, al sistema empresarial, al presupuestado y a las formas de gestión no estatal', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(353, 'XX00000353', 0, 252, 'Ejercer un efectivo control sobre la gestión de compras y de inventarios, para minimizar la inmovilización de recursos y las pérdidas en la economía', 2012, 2018, 344, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(354, 'XX00000354', 0, 13, 'XIII. PERFECCIONAMIENTO DE SISTEMAS Y ÓRGANOS DE DIRECCIÓN', 2012, 2018, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(357, 'XX00000357', 0, 25, 'Lograr la disminución progresiva de los niveles de los subsidios y otras transferencias que se otorgan por el Estado y contribuya a mejorar, en lo posible, la oferta de productos y servicios esenciales para la población.', 2014, 2021, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(358, 'XX00000358', 0, 26, 'Lograr una relación adecuada entre el componente importado de la producción nacional y la capacidad de la economía de generar ingresos en divisas', 2014, 2021, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(360, 'XX00000360', 0, 40, 'Concluir el proceso de unificación monetaria y cambiaria como un paso decisivo en el ordenamiento monetario del país', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(361, 'XX00000361', 0, 41, 'Avanzar en la creación de mecanismos más eficientes para el acceso a las divisas de los diferentes actores económicos, que contribuyan a facilitar el funcionamiento de la economía', 2012, 2018, 43, 52, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(362, 'XX00000362', 0, 52, 'Actualizar los instrumentos jurídicos a fin de propiciar un mayor ordenamiento de las finanzas públicas en el país', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(363, 'XX00000363', 0, 53, 'Perfeccionar el proceso de planificación y elevar el control sobre la utilización de los recursos financieros del Presupuesto del Estado, tanto en los ingresos como en los gastos', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(364, 'XX00000364', 0, 54, 'Perfeccionar la gestión en el cobro de los tributos y fortalecer el control fiscal. Para ello se debe consolidar el fortalecimiento de la Oficina Nacional de Administración Tributaria (ONAT), así como continuar el proceso de simplificación del pago de los tributos sin deteriorar la carga tributaria diseáada para los diferentes sectores de contribuyentes', 2012, 2018, 43, 65, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(365, 'XX00000365', 1, 6, 'SEGUROS', 2014, 2018, 43, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(366, 'XX00000366', 0, 61, 'Potenciar el uso del seguro, en sus diferentes modalidades, como mecanismo de protección financiera de las personas y del sector productivo, abarcando todas las formas de gestión. Desarrollar los seguros de vida como complemento de la seguridad social', 2014, 2018, 43, 365, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(367, 'XX00000367', 0, 109, 'Culminar el perfeccionamiento del sistema de normalización, metrología, calidad y acreditación, en correspondencia con los objetivos priorizados del Plan Nacional de Desarrollo Económico y Social, alcanzando a todos los actores económicos del país', 2012, 2018, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(371, 'XX00000371', 0, 130, 'Consolidar la enseáanza y el empleo del método clínico y epidemiológico y el estudio del entorno social en el abordaje de los\r\n24\r\nproblemas de salud de la población, de manera que contribuyan al uso racional y eficiente de los recursos para el diagnóstico y tratamiento de las enfermedades', 2014, 2020, 160, 177, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(372, 'XX00000372', 0, 135, 'Elevar la calidad y el rigor en la formación de atletas y docentes, desde la escuela-combinado deportivo y centro de alto rendimiento; desarrollar la participación de estos en eventos en el país y en el exterior en todas las categorías; sustentar su preparación en la educación en valores y en los avances de la ciencia y la innovación tecnológica. Continuar mejorando la infraestructura de la red de instalaciones deportivas', 2014, 2020, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(373, 'XX00000373', 0, 136, 'Fortalecer el papel de la cultura en los nuevos escenarios a partir de continuar fomentando la defensa de la identidad, así como la creación artística y literaria y la capacidad para apreciar el arte: promover la lectura, enriquecer la vida cultural de la población y potenciar el trabajo\r\n25\r\ncomunitario, como vías para satisfacer las necesidades espirituales, de recreación y defender los valores de nuestro socialismo', 2014, 2020, 160, 188, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(374, 'XX00000374', 0, 27, 'Perfeccionar y fortalecer la utilización de los indicadores macroeconómicos en el Sistema de Dirección de la Economía, como elemento fundamental para la elaboración y control del plan de la economía', 2014, 2020, 43, 44, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(375, 'XX00000375', 0, 240, 'Se priorizará y ampliará el programa de rehabilitación de acueductos y alcantarillados con la utilización de nuevas tecnologías en correspondencia con las capacidades financieras y constructivas, con el objetivo de garantizar la cantidad y calidad del agua, disminuir las pérdidas, incrementar su reciclaje, reducir el consumo energético y los\r\n42\r\nservicios asociados a los sistemas de aprovechamiento, acueducto y alcantarillado', 2014, 2020, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(376, 'XX00000376', 0, 241, 'Implementar el reordenamiento de los acueductos y alcantarillados, las tarifas del servicio, incluyendo el alcantarillado y regular de manera obligatoria la medición del caudal y el cobro a los usuarios, con el objetivo de propiciar el uso racional del agua, reducir el derroche y la disminución gradual del subsidio', 2014, 2020, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(377, 'XX00000377', 0, 242, 'Garantizar el acceso sistemático del abasto de agua a la población, de acuerdo con las posibilidades de la economía, con la potabilidad y calidad requeridas, a partir de la materialización de inversiones para dar respuesta a las necesidades del consumo de la población', 2014, 2020, 323, 339, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(379, 'XX00000379', 1, 3, 'XIII. PERFECCIONAMIENTO DE SISTEMAS Y ÓRGANOS DE DIRECCIÓN', 2014, 2020, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(381, 'XX00000381', 1, 1, 'LINEAMIENTOS GENERALES', 2014, 2020, 379, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(382, 'XX00000382', 0, 253, 'Trabajar para desarrollar un plan logístico nacional que garantice la gestión integrada de las cadenas de suministro existentes en el país', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(383, 'XX00000383', 0, 254, 'Continuar la introducción gradual, donde se considere necesario, de formas no estatales de gestión en el comercio', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(384, 'XX00000384', 0, 255, 'Perfeccionar y garantizar un programa de capacitación de directivos, ejecutores directos y trabajadores para la implantación de las políticas que se aprueben, comprobando el dominio de lo que se regule y exigir su cumplimiento. Informar a los trabajadores y escuchar sus opiniones', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(386, 'XX00000386', 0, 256, 'La separación de las funciones estatales y empresariales continuará realizándose mediante un proceso paulatino y ordenado. Establecer las normas que aseguren alcanzar las metas propuestas', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX');
INSERT INTO tpoliticas (id, id_code, titulo, numero, nombre, inicio, fin, capitulo, grupo, observacion, if_inner, id_proceso, id_proceso_code, cronos, cronos_syn, situs) VALUES
(387, 'XX00000387', 0, 257, 'Las unidades presupuestadas cumplen funciones estatales y de Gobierno, así como de otras características como la prestación de servicios de salud, educación y otros. No se crearán para prestar servicios productivos ni para la producción de bienes. Se les definen misión, funciones, obligaciones y atribuciones', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(388, 'XX00000388', 0, 258, 'Continuar reduciendo la cantidad de unidades presupuestadas hasta el número mínimo que garantice el cumplimiento de las funciones asignadas, donde primen los criterios de máximo ahorro del Presupuesto del Estado en recursos materiales, humanos y financieros, garantizando un servicio eficiente y de calidad', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(389, 'XX00000389', 0, 259, 'Las unidades presupuestadas que puedan financiar sus gastos con sus ingresos y generar un excedente pasarán a ser unidades autofinanciadas, sin dejar de cumplir las funciones y atribuciones asignadas o podrán adoptar, previa aprobación, la forma de empresa', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(390, 'XX00000390', 0, 260, 'A las unidades presupuestadas que solo logren cubrir una parte de sus gastos con sus ingresos, se les aprobará la parte de los gastos que se financiará por el Presupuesto del Estado, mediante un tratamiento especial', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(391, 'XX00000391', 0, 261, 'Continuar el perfeccionamiento del sistema de dirección y gestión de las unidades presupuestadas, adecuándolo a sus características funcionales, organizativas y económicas, simplificando su contabilidad', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(392, 'XX00000392', 0, 262, 'Los consejos de la administración provinciales y municipales cumplirán funciones estatales y no intervendrán directamente en la gestión empresarial. En correspondencia con ello se consolidarán y generalizarán las experiencias obtenidas en la separación de funciones estatales y empresariales en el experimento que se realiza', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(393, 'XX00000393', 0, 263, 'Las funciones estatales que ejercen los órganos de dirección en provincias y municipios y su relación con las que desarrollan los organismos de la Administración Central del Estado serán reguladas, dejando definidos los límites de sus competencias, vínculos, reglamentos de trabajo y las metodologías de actuación que se aplicarán en correspondencia con el experimento que se realiza', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(394, 'XX00000394', 0, 264, 'Perfeccionar la protección al consumidor adoptando medidas que coadyuven a asegurar sus derechos por quienes producen, comercializan y prestan servicios en general', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(395, 'XX00000395', 0, 265, 'Implantar la Política de Comunicación Social del Estado y el Gobierno, realizando las transformaciones funcionales y estructurales requeridas. Lograr que ejerza su verdadero papel en los sistemas de dirección en la sociedad, organismos, organizaciones y demás entidades, propiciando con oportunidad y transparencia la participación organizada de los trabajadores y ciudadanos. Priorizar en sus tareas iniciales el diseáo de una estrategia de comunicación que acompaáe la actualización del modelo económico y social, y contribuya a mantener las principales fortalezas con las que se cuenta para el desarrollo de un socialismo próspero y sostenible', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(396, 'XX00000396', 0, 266, 'Realizar el perfeccionamiento del funcionamiento, estructura y composición de los órganos superiores de Dirección del Estado y el Gobierno que se exija', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(397, 'XX00000397', 0, 267, 'Culminar el perfeccionamiento de los organismos de la Administración Central del Estado (OACE) y entidades nacionales, con énfasis en su funcionamiento, estructura y composición, estableciendo la base jurídico-organizativa que se requiera. Desarrollar sistemáticamente este proceso con el objetivo de lograr una Administración Pública más ligera, ágil y eficiente', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(398, 'XX00000398', 0, 268, 'Continuar el perfeccionamiento de los órganos del Poder Popular como vía para consolidar nuestra democracia socialista', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(399, 'XX00000399', 0, 269, 'Consolidar y perfeccionar el Sistema de Planificación de Objetivos y Actividades del Gobierno', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(400, 'XX00000400', 0, 270, 'Perfeccionar el Sistema de Trabajo con los Cuadros del Estado y del Gobierno, incluida su base reglamentaria, y avanzar con calidad en la aplicación de los procesos que lo integran; prestando la debida atención y exigencia por los jefes, comisiones y órganos de cuadros a: la selección y promoción de los cuadros, su atención y estimulación, la reserva, el rigor en la evaluación, la ética, la disciplina, así como a la preparación y superación', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(401, 'XX00000401', 0, 271, 'Fortalecer el control interno y el externo ejercido por los órganos del Estado, los organismos, las entidades, así como el control social, incluyendo el popular, sobre la gestión administrativa; promover y exigir la transparencia de la gestión pública y la protección de los derechos ciudadanos. Consolidar las acciones de prevención y enfrentamiento a las ilegalidades, la corrupción, el delito e indisciplinas sociales', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(402, 'XX00000402', 0, 272, 'Avanzar en la creación del Sistema de Información del Gobierno; asegurar el más alto grado de informatización que las posibilidades económicas permitan', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(403, 'XX00000403', 0, 273, 'Transformar el sistema de registros públicos y de los servicios y trámites a partir de las normas aprobadas y las experiencias adquiridas mediante los experimentos realizados', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(404, 'XX00000404', 1, 1, 'LINEAMIENTOS GENERALES', 2014, 2020, 148, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(405, 'XX00000405', 0, 110, 'Fortalecer las capacidades de prospección y vigilancia tecnológica, así como la política de protección de la propiedad industrial en Cuba y en los principales mercados externos', 2014, 2020, 148, 404, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(406, 'XX00000406', 0, 111, 'Potenciar la organización y el desarrollo de capacidades de servicios profesionales de diseáo, su integración a los sistemas institucional y empresarial del país', 2014, 2020, 148, 404, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(407, 'XX00000407', 0, 112, 'Intensificar las acciones de control de la generación de los desechos peligrosos y su manejo integral hasta su disposición final', 2014, 2020, 148, 404, NULL, NULL, NULL, NULL, NULL, NULL, 'XX'),
(409, 'XX00000409', 0, 274, 'Continuar el perfeccionamiento del sistema de justicia en todos sus ámbitos y de sus órganos, organismos y organizaciones que lo integran o le tributan, consolidando la seguridad jurídica, la protección de los derechos ciudadanos, la institucionalidad, la disciplina social y el orden interior', 2014, 2020, 379, 381, NULL, NULL, NULL, NULL, NULL, NULL, 'XX');
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-09-08
/**********************************************/
/* paso 2 */
alter table tprocesos add column codigo_archive varchar(10) default null;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-09-09
/**********************************************/
/* paso 1 */
DROP TABLE tlistas;
CREATE TABLE tlistas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  nombre text DEFAULT NULL,
  descripcion longtext DEFAULT NULL,
  inicio mediumint(9) DEFAULT NULL,
  fin mediumint(9) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_proceso (id_proceso),
  CONSTRAINT tlistas_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/* paso 2 */
DROP TABLE tlista_requisitos;
CREATE TABLE tlista_requisitos (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  numero mediumint(9) DEFAULT NULL,
  componente tinyint(4) DEFAULT NULL,
  nombre text DEFAULT NULL,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) DEFAULT NULL,
  id_tipo_lista int(11) DEFAULT NULL,
  id_tipo_lista_code char(10) DEFAULT NULL,
  peso tinyint(4) DEFAULT NULL,
  inicio mediumint(9) DEFAULT NULL,
  fin mediumint(9) DEFAULT NULL,
  evidencia longtext DEFAULT NULL,
  indicacion longtext DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_lista (id_lista),
  KEY id_usuario (id_usuario),
  KEY id_tipo_lista (id_tipo_lista),
  CONSTRAINT tlista_requisito_fk FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tlista_requisito_fk1 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT tlista_requisito_fk2 FOREIGN KEY (id_tipo_lista) REFERENCES ttipo_listas (id) ON DELETE SET NULL
) ENGINE=InnoDB;

/* paso 3 */
DROP TABLE ttipo_listas;
CREATE TABLE ttipo_listas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  nombre text DEFAULT NULL,
  numero varchar(9) DEFAULT NULL,
  descripcion text DEFAULT NULL,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) DEFAULT NULL,
  componente smallint(6) DEFAULT NULL,
  year mediumint(9) DEFAULT NULL,
  capitulo smallint(6) DEFAULT NULL,
  subcapitulo int(11) DEFAULT NULL,
  id_capitulo int(11) DEFAULT NULL,
  id_capitulo_code char(10) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,
  indice int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY id_proceso (id_proceso),
  KEY id_capitulo (id_capitulo),
  KEY id_lista (id_lista),
  CONSTRAINT id_lista FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT ttipo_listas_fk FOREIGN KEY (id_capitulo) REFERENCES ttipo_listas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT ttipo_listas_fk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB;;

/* paso 4 */
DROP TABLE treg_lista;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-09-13
/**********************************************/
/* paso 1 */
CREATE TABLE tlista_auditorias (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_lista int(11) DEFAULT NULL,
  id_lista_code char(10) COLLATE utf8_spanish_ci NOT NULL,
  id_auditoria int(11) DEFAULT NULL,
  id_auditoria_code char(10) COLLATE utf8_spanish_ci NOT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY lista_auditoria_index (id_lista_code,id_auditoria_code),
  KEY id_lista (id_lista),
  KEY id_auditoria (id_auditoria),
  CONSTRAINT tlista_auditorias_fk FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tlista_auditorias_fk1 FOREIGN KEY (id_auditoria) REFERENCES tauditorias (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/* paso 2 */
ALTER TABLE tnotas ADD COLUMN id_lista INT(11) DEFAULT NULL AFTER id_auditoria_code;
ALTER TABLE tnotas ADD COLUMN id_lista_code CHAR(10) DEFAULT NULL AFTER id_lista;
ALTER TABLE tnotas ADD COLUMN id_requisito INT(11) DEFAULT NULL AFTER id_lista_code;
ALTER TABLE tnotas ADD COLUMN id_requisito_code CHAR(10) DEFAULT NULL AFTER id_requisito;
ALTER TABLE tnotas ADD COLUMN cumplimiento TINYINT(2) DEFAULT NULL AFTER id_requisito_code;

ALTER TABLE tnotas
  ADD CONSTRAINT tnotas_fk4 FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE tnotas
  ADD CONSTRAINT tnotas_fk5 FOREIGN KEY (id_requisito) REFERENCES tlista_requisitos (id) ON DELETE CASCADE ON UPDATE CASCADE;

/* paso 3*/
ALTER TABLE treg_nota ADD COLUMN id_auditoria INT(11) DEFAULT NULL AFTER id_nota_code;
ALTER TABLE treg_nota ADD COLUMN id_auditoria_code CHAR(10) DEFAULT NULL AFTER id_auditoria;
ALTER TABLE treg_nota ADD COLUMN id_lista INT(11) DEFAULT NULL AFTER id_auditoria_code;
ALTER TABLE treg_nota ADD COLUMN id_lista_code CHAR(10) DEFAULT NULL AFTER id_lista;
ALTER TABLE treg_nota ADD COLUMN id_requisito INT(11) DEFAULT NULL AFTER id_lista_code;
ALTER TABLE treg_nota ADD COLUMN id_requisito_code CHAR(10) DEFAULT NULL AFTER id_requisito;
ALTER TABLE treg_nota ADD COLUMN cumplimiento TINYINT(2) DEFAULT NULL AFTER id_requisito_code;
ALTER TABLE treg_nota ADD COLUMN calcular TINYINT(1) DEFAULT 1 AFTER cumplimiento;

ALTER TABLE treg_nota
  ADD CONSTRAINT treg_nota_fk2 FOREIGN KEY (id_auditoria) REFERENCES tauditorias (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE treg_nota
  ADD CONSTRAINT treg_nota_fk3 FOREIGN KEY (id_lista) REFERENCES tlistas (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE treg_nota
  ADD CONSTRAINT treg_nota_fk4 FOREIGN KEY (id_requisito) REFERENCES tlista_requisitos (id) ON DELETE CASCADE ON UPDATE CASCADE;

/* paso 4 */
DROP TABLE treg_lista;  

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-09-23
/**********************************************/
/* paso 1 */
ALTER TABLE tusuarios CHANGE COLUMN global_user global_user BOOLEAN DEFAULT NULL;
UPDATE tusuarios SET global_user= NULL; 

/* paso 2 */
TRUNCATE TABLE tsincronizacion;
delete from tsystem where action like '%Lote' or action = 'purge';

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-10-02
/**********************************************/
ALTER TABLE triesgos CHANGE COLUMN valor valor DOUBLE(15,3) DEFAULT NULL;

ALTER TABLE tsystem ADD COLUMN observacion TEXT DEFAULT NULL;

/* paso 2 */
ALTER TABLE tnota_causas ADD COLUMN id_riesgo INTEGER(11) DEFAULT NULL AFTER id_nota_code;
ALTER TABLE tnota_causas ADD COLUMN id_riesgo_code CHAR(10) DEFAULT NULL AFTER id_riesgo;
ALTER TABLE tnota_causas
  ADD CONSTRAINT tnota_causas_fk2 FOREIGN KEY (id_riesgo) REFERENCES triesgos (id) ON DELETE CASCADE ON UPDATE CASCADE;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-10-10
/**********************************************/
/* paso 1 */
delete from tlista_requisitos;
delete from tlistas;
delete from ttipo_listas;

truncate table tlista_requisitos;
truncate table tlistas;
truncate table ttipo_listas;

/* Volcado de datos para la tabla tlistas */

INSERT INTO tlistas (id, id_code, nombre, descripcion, inicio, fin, id_proceso, id_proceso_code, cronos, cronos_syn, situs) VALUES
(1, 'XX00000001', 'Guía de Autocontrol', 'es la lista no.1 de prueba', 2018, 2019, 1, 'XX00000001', '2018-09-17 10:34:18', NULL, 'XX');

/* Volcado de datos para la tabla tlista_requisitos */

INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES
(3, 'XX00000003', 1, 1, 'Se corresponden con la misión, las prioridades del país y los recursos disponibles.', 1, 'XX00000001', 78, 'XX00000078', 3, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:12:04', NULL, 'XX'),
(4, 'XX00000004', 2, 1, 'Son puntualizados y ajustados anualmente.', 1, 'XX00000001', 78, 'XX00000078', 6, 2018, 2019, NULL, NULL, 2, '2018-09-28 15:41:34', NULL, 'XX'),
(5, 'XX00000005', 3, 1, 'Los trabajadores conocen los objetivos de trabajo con sus indicadores.', 1, 'XX00000001', 78, 'XX00000078', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:12:18', NULL, 'XX'),
(6, 'XX00000006', 4, 1, 'Se realizan evaluaciones y análisis periódicos sobre su cumplimiento y se toman las medidas correctivas que correspondan.', 1, 'XX00000001', 78, 'XX00000078', 6, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:12:33', NULL, 'XX'),
(7, 'XX00000007', 5, 1, 'El jefe de la entidad dirige este proceso y el órgano colegiado participa activamente.', 1, 'XX00000001', 78, 'XX00000078', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:12:44', NULL, 'XX'),
(8, 'XX00000008', 73, 3, 'Se garantiza la división de funciones y la contrapartida en las tareas y responsabilidades esenciales, relativas al tratamiento, autorización, registro y revisión de las transacciones y hechos, en correspondencia con el contenido y función de cada cargo.', 1, 'XX00000001', 9, 'XX00000009', 0, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:51:51', NULL, 'XX'),
(9, 'XX00000009', 2, 1, 'Se ajusta a lo establecido en la Instrucción No. 1 del Presidente de los Consejos de Estado y de Ministros para la planificación de los objetivos y actividades.', 1, 'XX00000001', 1, 'XX00000001', 5, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:13:22', NULL, 'XX'),
(10, 'XX00000010', 74, 3, 'Se incrementan las acciones de supervisión y control, en los casos que no es posible la división de tareas y responsabilidades.', 1, 'XX00000001', 9, 'XX00000009', 6, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:52:20', NULL, 'XX'),
(11, 'XX00000011', 1, 1, 'Si se encuentra instalado el sistema.', 1, 'XX00000001', 48, 'XX00000048', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 15:43:57', NULL, 'XX'),
(12, 'XX00000012', 1, 3, 'Realizado el levantamiento de relaciones de familiaridad y en este se encuentran identificadas las relaciones que afectan la contrapartida.', 1, 'XX00000001', 60, 'XX00000060', 5, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:52:50', NULL, 'XX'),
(13, 'XX00000013', 2, 1, 'Si se está explotando el sistema.', 1, 'XX00000001', 48, 'XX00000048', 6, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:15:08', NULL, 'XX'),
(14, 'XX00000014', 2, 3, 'Está elaborado y se cumple el plan de acción para dar solución a las relaciones de familiaridad cuando se afecta la contrapartida.', 1, 'XX00000001', 60, 'XX00000060', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:53:30', NULL, 'XX'),
(15, 'XX00000015', 4, 1, 'El plan anual de actividades asegura la correspondencia entre los objetivos de trabajo, las actividades y los recursos aprobados en el plan económico de la entidad y con el nivel a que se subordina.', 1, 'XX00000001', 1, 'XX00000001', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:17:43', NULL, 'XX'),
(16, 'XX00000016', 6, 3, 'Comprobadas las responsabilidades por áreas y los niveles de autorización definidas en el Reglamento Orgánico y en el Manual de Funcionamiento, según corresponda.', 1, 'XX00000001', 60, 'XX00000060', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:53:58', NULL, 'XX'),
(17, 'XX00000017', 7, 3, 'Están definidas las firmas autorizadas para las diferentes transacciones y operaciones de la entidad.', 1, 'XX00000001', 60, 'XX00000060', 0, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:54:26', NULL, 'XX'),
(18, 'XX00000018', 1, 1, 'La misión de la entidad.', 1, 'XX00000001', 49, 'XX00000049', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:18:02', NULL, 'XX'),
(19, 'XX00000019', 2, 1, 'Los objetivos de trabajo.', 1, 'XX00000001', 49, 'XX00000049', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:18:17', NULL, 'XX'),
(20, 'XX00000020', 78, 3, 'Las transacciones, operaciones y hechos cuentan con un soporte documental demostrativo, fiable y que garantice la trazabilidad de la misma.', 1, 'XX00000001', 10, 'XX00000010', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:54:50', NULL, 'XX'),
(21, 'XX00000021', 3, 1, 'El objeto social, encargo estatal o función estatal de la entidad, notificado por el MEP o por el órgano u organismo que la crea, según corresponda.', 1, 'XX00000001', 49, 'XX00000049', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:18:43', NULL, 'XX'),
(22, 'XX00000022', 4, 1, 'Directivas del Plan Económico anual diseñadas por la empresa y aprobadas por el nivel correspondiente, y sus indicadores.', 1, 'XX00000001', 49, 'XX00000049', 0, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:19:06', NULL, 'XX'),
(23, 'XX00000023', 5, 1, 'Razonabilidad de las cifras comprometidas.', 1, 'XX00000001', 49, 'XX00000049', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:19:21', NULL, 'XX'),
(24, 'XX00000024', 6, 1, 'Las funciones definidas de cada área y puesto de trabajo.', 1, 'XX00000001', 49, 'XX00000049', 0, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:19:34', NULL, 'XX'),
(25, 'XX00000025', 7, 1, 'Las actividades a realizar en cada proceso o subproceso.', 1, 'XX00000001', 49, 'XX00000049', 3, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:19:50', NULL, 'XX'),
(26, 'XX00000026', 8, 1, 'Los riesgos más relevantes que ponen en peligro el cumplimiento de los objetivos y la misión de la entidad (Plan de Prevención de Riegos).', 1, 'XX00000001', 49, 'XX00000049', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:20:02', NULL, 'XX'),
(27, 'XX00000027', 9, 1, 'Las tareas de consulta y discusión del Plan económico, el Presupuesto y el sistema de pagos a los trabajadores en todas sus etapas o procesos, y su correspondencia con la proyección estratégica aprobada por el máximo órgano colegiado de dirección.', 1, 'XX00000001', 49, 'XX00000049', 0, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:20:14', NULL, 'XX'),
(31, 'XX00000031', 6, 1, 'La alta dirección y su órgano colegiado intervienen directamente en el proceso de elaboración y aprobación del plan.', 1, 'XX00000001', 1, 'XX00000001', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:20:29', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(35, 'XX00000035', 6, 1, 'La alta dirección y su órgano colegiado intervienen directamente en el proceso de elaboración y aprobación del plan.', 1, 'XX00000001', 1, 'XX00000001', 6, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:20:56', NULL, 'XX'),
(36, 'XX00000036', 7, 1, 'Se analiza periódicamente el cumplimiento del plan de actividades anual y se adoptan las medidas que correspondan.', 1, 'XX00000001', 1, 'XX00000001', 3, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:21:14', NULL, 'XX'),
(37, 'XX00000037', 8, 1, 'Elaborado el plan de trabajo mensual de la entidad, direcciones y departamentos, sobre la base de lo aprobado en el plan anual de actividades de cada nivel de dirección, puntualizando las actividades que hayan sufrido cambios y las nuevas, como resultado del proceso de dirección, teniendo en cuenta también que en el cumplimiento del mismo se incluyan las acciones de control y seguimiento a realizar por la propia entidad para solucionar las deficiencias o limitaciones que se detecten, lo que debe incidir en la actualización de los planes de Prevención de Riesgos.', 1, 'XX00000001', 1, 'XX00000001', 5, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:21:27', NULL, 'XX'),
(38, 'XX00000038', 8, 1, 'Se incluye el cumplimiento de los acuerdos, mandatos y acciones que generen los órganos de dirección del nivel superior y su propio nivel.', 1, 'XX00000001', 1, 'XX00000001', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:21:56', NULL, 'XX'),
(39, 'XX00000039', 9, 1, 'Cada cuadro, funcionario y especialista elabora su plan de trabajo individual, teniendo presente el plan de trabajo mensual del nivel de dirección a que se subordina, el aseguramiento de los objetivos y tareas que responda a su responsabilidad y a las misiones asignadas. El jefe inmediato superior revisa, aprueba y analiza el cumplimiento del plan aprobado.', 1, 'XX00000001', 1, 'XX00000001', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:22:16', NULL, 'XX'),
(40, 'XX00000040', 10, 1, 'Se analizan los recursos que son necesarios para garantizar las nuevas tareas que se incluyen en el plan, de dónde se obtienen los recursos y qué otras tareas se modifican como resultado de ello.', 1, 'XX00000001', 1, 'XX00000001', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:22:39', NULL, 'XX'),
(41, 'XX00000041', 11, 1, 'Se informa a los niveles que correspondan, el cumplimiento de los planes de trabajo.', 1, 'XX00000001', 1, 'XX00000001', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:22:51', NULL, 'XX'),
(42, 'XX00000042', 12, 1, 'Se encuentra firmado por los cuadros el Código de Ética de los Cuadros del Estado Cubano.', 1, 'XX00000001', 2, 'XX00000002', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:23:07', NULL, 'XX'),
(43, 'XX00000043', 13, 1, 'Se evalúan en las rendiciones de cuenta y en las evaluaciones la observancia de los preceptos éticos.', 1, 'XX00000001', 1, 'XX00000001', 0, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:23:24', NULL, 'XX'),
(44, 'XX00000044', 14, 1, 'Identificados por los trabajadores los valores éticos de la entidad.', 1, 'XX00000001', 2, 'XX00000002', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:24:43', NULL, 'XX'),
(45, 'XX00000045', 15, 1, 'Cuenta la entidad con un Código de ética específico para la actividad.', 1, 'XX00000001', 2, 'XX00000002', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:24:09', NULL, 'XX'),
(46, 'XX00000046', 16, 1, 'Se conoce por los trabajadores y se aplica el Reglamento Disciplinario aprobado.', 1, 'XX00000001', 2, 'XX00000002', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:24:56', NULL, 'XX'),
(47, 'XX00000047', 17, 1, 'Se cumple el Convenio Colectivo de Trabajo elaborado conjuntamente entre la administración y la organización sindical, habiendo sido discutido y aprobado por los trabajadores, el que debe mantener su vigencia por un periodo máximo de tres años.', 1, 'XX00000001', 2, 'XX00000002', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:25:06', NULL, 'XX'),
(48, 'XX00000048', 18, 1, 'Conformado y actualizado un registro consecutivo anual de las medidas disciplinarias en la entidad.', 1, 'XX00000001', 2, 'XX00000002', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:25:17', NULL, 'XX'),
(49, 'XX00000049', 19, 1, 'Existe evidencia de la preparación general de los cuadros y reservas, su vinculación con los demás trabajadores, para lograr una cultura de responsabilidad administrativa.', 1, 'XX00000001', 2, 'XX00000002', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:25:28', NULL, 'XX'),
(50, 'XX00000050', 20, 1, 'Creado el comité de expertos el cual se ratifica o renueva cada dos años y se conservan las actas de las reuniones, así como las recomendaciones emitidas en cada caso y cualquier otra información o documentación probatoria del asunto en cuestión.', 1, 'XX00000001', 3, 'XX00000003', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:25:39', NULL, 'XX'),
(51, 'XX00000051', 21, 1, 'Se utilizan en las entidades que lo requieran las buenas prácticas para definir perfiles de competencia para cada cargo establecido según las normas cubanas.', 1, 'XX00000001', 3, 'XX00000003', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:25:49', NULL, 'XX'),
(52, 'XX00000052', 22, 1, 'Cada trabajador conoce sus tareas o funciones establecidas en el calificador de cargos y en los contenidos específicos de trabajo, y se refleja su cumplimiento en las evaluaciones de desempeño.', 1, 'XX00000001', 3, 'XX00000003', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:25:59', NULL, 'XX'),
(53, 'XX00000053', 23, 1, 'Elaborado y actualizado un registro de la plantilla de personal y el registro de trabajadores, de acuerdo con la legislación vigente del MTSS.', 1, 'XX00000001', 3, 'XX00000003', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:26:14', NULL, 'XX'),
(54, 'XX00000054', 24, 1, 'El plan anual de capacitación se confecciona a partir de lo establecido en la legislación vigente del MTSS, considerando además la integración del diagnóstico o determinación de las necesidades de preparación y el plan individual de capacitación.', 1, 'XX00000001', 3, 'XX00000003', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:26:25', NULL, 'XX'),
(55, 'XX00000055', 25, 1, 'Se cuenta con la disposición que aprueba la constitución de la entidad y su objeto social o encargo estatal, según proceda.', 1, 'XX00000001', 4, 'XX00000004', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:26:41', NULL, 'XX'),
(56, 'XX00000056', 26, 1, 'Poseen los certificados de inscripción en los registros públicos correspondientes según la actividad que realiza la entidad (Registros de la Oficina Nacional de Estadística e Información y la Oficina Nacional de Administración Tributaria, entre otros).', 1, 'XX00000001', 4, 'XX00000004', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:26:52', NULL, 'XX'),
(57, 'XX00000057', 27, 1, 'Se cuenta con la documentación que aprueba la plantilla de cargos, así como con el organigrama de la entidad, los que se corresponden con la estructura organizativa de la entidad y sus necesidades.', 1, 'XX00000001', 4, 'XX00000004', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:27:03', NULL, 'XX'),
(58, 'XX00000058', 28, 1, 'Identificados los procesos, actividades y sus responsables, a partir de las funciones de la entidad, para dar cumplimiento a los objetivos trazados.', 1, 'XX00000001', 4, 'XX00000004', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:27:15', NULL, 'XX'),
(59, 'XX00000059', 1, 1, 'El manual de procedimientos, donde se relacionan los procedimientos a seguir en cada uno de los procesos fundamentales.', 1, 'XX00000001', 50, 'XX00000050', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:27:31', NULL, 'XX'),
(60, 'XX00000060', 2, 1, 'Según corresponda: El manual de funcionamiento interno, donde se establecen las funciones y relaciones entre las áreas y puestos de trabajo de acuerdo con los procesos y actividades que se desarrollan para el cumplimiento de los objetivos de trabajo de la entidad, así como la autoridad y responsabilidad de los distintos puestos de trabajo, encontrándose aprobado por la máxima dirección; o', 1, 'XX00000001', 50, 'XX00000050', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:27:47', NULL, 'XX'),
(61, 'XX00000061', 3, 1, 'El Reglamento Orgánico, según corresponda.', 1, 'XX00000001', 50, 'XX00000050', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:27:59', NULL, 'XX'),
(62, 'XX00000062', 30, 1, 'Se aplica el sistema de información del Gobierno, conforme a lo establecido en la legislación vigente.', 1, 'XX00000001', 4, 'XX00000004', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:28:14', NULL, 'XX'),
(63, 'XX00000063', 31, 1, 'Se aplican las normas obligatorias emitidas por la Oficina Nacional de Normalización para los procesos que lo requieran.', 1, 'XX00000001', 4, 'XX00000004', 6, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:28:27', NULL, 'XX'),
(64, 'XX00000064', 32, 1, 'Cuentan con asesoramiento jurídico y se le da seguimiento a los dictámenes sobre aspectos legales de la gestión que desarrolla la entidad.', 1, 'XX00000001', 4, 'XX00000004', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:28:52', NULL, 'XX'),
(65, 'XX00000065', 1, 1, 'Objeto del contrato.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:29:06', NULL, 'XX'),
(66, 'XX00000066', 2, 1, 'Objeto de las prestaciones derivadas del contrato.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:37:04', NULL, 'XX'),
(67, 'XX00000067', 3, 1, 'Plazos para el cumplimiento de las obligaciones.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:29:32', NULL, 'XX'),
(68, 'XX00000068', 4, 1, 'Términos o reglas internacionales.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:29:45', NULL, 'XX'),
(69, 'XX00000069', 5, 1, 'Precios y tarifas.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:29:56', NULL, 'XX'),
(70, 'XX00000070', 7, 1, 'Efectos de la falta de pago.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:31:00', NULL, 'XX'),
(71, 'XX00000071', 8, 1, 'Concurrencia y parámetros de calidad.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:31:13', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES
(72, 'XX00000072', 9, 1, 'Plazos de la garantía comercial, en correspondencia con la naturaleza de la prestación que constituye el objeto del contrato, o en su caso, de acuerdo con las normas vigentes.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:31:28', NULL, 'XX'),
(73, 'XX00000073', 10, 1, 'Cuando proceda, la relación de la documentación técnica y comercial a entregar.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:31:39', NULL, 'XX'),
(74, 'XX00000074', 11, 1, 'Cuando corresponda, la parte que debe obtener el seguro en virtud de los términos del contrato y de los riesgos contra los cuales se establece.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:31:56', NULL, 'XX'),
(75, 'XX00000075', 12, 1, 'Reclamaciones por incumplimiento de determinadas obligaciones, como soluciones alternativas para el cumplimiento.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:32:08', NULL, 'XX'),
(76, 'XX00000076', 13, 1, 'Formas de aviso ante la eventual posibilidad de un incumplimiento en su ejecución.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:32:23', NULL, 'XX'),
(77, 'XX00000077', 14, 1, 'Solución de controversias, donde se especifique el órgano judicial o arbitral ante el que se resolverán las controversias.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:32:37', NULL, 'XX'),
(78, 'XX00000078', 15, 1, 'Modificación y terminación del contrato.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:32:51', NULL, 'XX'),
(79, 'XX00000079', 16, 1, 'Vigencia del contrato.', 1, 'XX00000001', 51, 'XX00000051', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:33:04', NULL, 'XX'),
(80, 'XX00000080', 62, 2, 'Se identifican y analizan los riesgos que puedan afectar el cumplimiento de los objetivos y metas de la organización, sean externos e internos, clasificados por procesos, actividades y operaciones de cada área, con la participación de los trabajadores.', 1, 'XX00000001', 6, 'XX00000006', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:38:58', NULL, 'XX'),
(81, 'XX00000081', 1, 2, 'Sistema de pagos.', 1, 'XX00000001', 79, 'XX00000079', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:40:59', NULL, 'XX'),
(82, 'XX00000082', 2, 2, 'Relaciones contractuales pactadas con personas naturales.', 1, 'XX00000001', 79, 'XX00000079', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:41:20', NULL, 'XX'),
(83, 'XX00000083', 3, 2, 'Formas no estatales de gestión.', 1, 'XX00000001', 79, 'XX00000079', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:41:57', NULL, 'XX'),
(84, 'XX00000084', 4, 2, 'Arrendamiento de locales.', 1, 'XX00000001', 79, 'XX00000079', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:42:18', NULL, 'XX'),
(85, 'XX00000085', 5, 2, 'Formas de subsidio a las personas, no a productos.', 1, 'XX00000001', 79, 'XX00000079', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:42:40', NULL, 'XX'),
(86, 'XX00000086', 6, 2, 'Otros.', 1, 'XX00000001', 79, 'XX00000079', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:42:58', NULL, 'XX'),
(87, 'XX00000087', 64, 2, 'Se identifican y analizan los riesgos generados por situaciones excepcionales (desastres naturales, situaciones de guerra, etc.)', 1, 'XX00000001', 6, 'XX00000006', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:43:30', NULL, 'XX'),
(88, 'XX00000088', 65, 2, 'Una vez identificados los riesgos, éstos se vinculan con las causas y condiciones que lo generan y los objetivos de control. En relación con ellos, se analizan los procedimientos y actividades de control más convenientes.', 1, 'XX00000001', 6, 'XX00000006', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:43:58', NULL, 'XX'),
(89, 'XX00000089', 66, 2, 'Se conservan las actas de las reuniones por áreas con los trabajadores para la determinación de los objetivos de control y fueron antecedidas de un trabajo de información y preparación de los trabajadores.', 1, 'XX00000001', 7, 'XX00000007', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:44:25', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(90, 'XX00000090', 67, 2, 'Elaborado el Plan de Prevención de Riesgos de la entidad, el que debe proporcionar una seguridad razonable al logro de los objetivos institucionales y una adecuada rendición de cuentas, a partir del análisis de los riesgos más relevantes contenidos en los respectivos planes de Prevención de Riesgos de las áreas y considerando el autocontrol como una de las medidas.', 1, 'XX00000001', 8, 'XX00000008', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:45:12', NULL, 'XX'),
(91, 'XX00000091', 68, 2, 'Se consideran en el Plan de Prevención de Riesgos, los riesgos más relevantes relacionados con la seguridad informática, la seguridad y protección física, la protección de la Información Oficial en la entidad y la actuación ética e incumplimiento de las normas vigentes establecidas a partir de la política migratoria.', 1, 'XX00000001', 8, 'XX00000008', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:45:52', NULL, 'XX'),
(92, 'XX00000092', 69, 2, 'Las acciones y medidas contenidas en el Plan de Prevención de Riesgos no constituyen deberes funcionales de los cargos o desarrollo de actividades de control declaradas en los procedimientos de trabajo y documentos normativos de la entidad, sino consisten en comprobar que la función de controlar que no ocurra un riesgo y de aplicar actividades de control, se haya cumplido.', 1, 'XX00000001', 8, 'XX00000008', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:46:15', NULL, 'XX'),
(93, 'XX00000093', 69, 2, 'Las acciones y medidas contenidas en el Plan de Prevención de Riesgos no constituyen deberes funcionales de los cargos o desarrollo de actividades de control declaradas en los procedimientos de trabajo y documentos normativos de la entidad, sino consisten en comprobar que la función de controlar que no ocurra un riesgo y de aplicar actividades de control, se haya cumplido.', 1, 'XX00000001', 8, 'XX00000008', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:47:43', NULL, 'XX'),
(94, 'XX00000094', 70, 2, 'Aprobado el Plan de Prevención de Riesgos por parte del órgano colegiado de dirección y los trabajadores, dejando evidencia documental mediante acta de la reunión.', 1, 'XX00000001', 8, 'XX00000008', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:47:59', NULL, 'XX'),
(95, 'XX00000095', 65, 2, 'Existe evidencia de la evaluación y actualización sistemática del Plan de Prevención de Riesgos a partir del análisis de las causas y condiciones y las vulnerabilidades identificadas por diferentes acciones de control y hechos extraordinarios ocurridos.', 1, 'XX00000001', 7, 'XX00000007', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:44:54', NULL, 'XX'),
(96, 'XX00000096', 1, 2, 'Pérdidas por el impacto del cambio en la política cambiaria y la unificación monetaria.', 1, 'XX00000001', 80, 'XX00000080', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:48:35', NULL, 'XX'),
(97, 'XX00000097', 2, 2, 'Pérdidas por variación de precios.', 1, 'XX00000001', 80, 'XX00000080', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:49:00', NULL, 'XX'),
(98, 'XX00000098', 3, 2, 'Pérdidas por variación de la tasa de interés.', 1, 'XX00000001', 80, 'XX00000080', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:49:57', NULL, 'XX'),
(99, 'XX00000099', 4, 2, 'Otros.', 1, 'XX00000001', 80, 'XX00000080', 4, 2018, 2019, NULL, NULL, 2, '2018-09-28 16:50:15', NULL, 'XX'),
(100, 'XX00000100', 1, 4, 'Implementado un sistema para la gestión de la información que garantice:', 1, 'XX00000001', NULL, 'XX00000006', 4, 2018, 2019, NULL, NULL, 2, '2018-09-17 12:05:30', NULL, 'XX'),
(101, 'XX00000101', 1, 4, 'La elaboración del diagrama del flujo de la información de la entidad, definiendo el emisor, receptor y canales de comunicación.', 1, 'XX00000001', NULL, 'XX00000006', 4, 2018, 2019, NULL, NULL, 2, '2018-09-17 12:05:49', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(102, 'XX00000102', 1, 4, 'Tener bien definido la frecuencia, formato, almacenamiento y soporte de los documentos y bases de datos relevantes.', 1, 'XX00000001', NULL, 'XX00000006', 4, 2018, 2019, NULL, NULL, 2, '2018-09-17 13:04:14', NULL, 'XX'),
(103, 'XX00000103', 1, 4, 'Clasificada la información oficial de la entidad, identificando su contenido, períodos de conservación y otros aspectos específicos.', 1, 'XX00000001', NULL, 'XX00000006', 4, 2018, 2019, NULL, NULL, 2, '2018-09-17 13:04:45', NULL, 'XX'),
(104, 'XX00000104', 1, 4, 'Determinar los accesos a la información.', 1, 'XX00000001', NULL, 'XX00000006', 6, 2018, 2019, NULL, NULL, 2, '2018-09-17 13:05:02', NULL, 'XX'),
(105, 'XX00000105', 79, 3, 'Se comprueba el comportamiento de los Proyectos de Colaboración y Donativos.', 1, 'XX00000001', 10, 'XX00000010', 0, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:55:12', NULL, 'XX'),
(106, 'XX00000106', 80, 3, 'Se cuenta con la documentación que complemente el cumplimiento de las directivas, y de la sustentación del Plan anual, en el Sector Empresarial.', 1, 'XX00000001', 10, 'XX00000010', 6, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:55:39', NULL, 'XX'),
(107, 'XX00000107', 1, 3, 'Áreas de responsabilidad y centros de costo definidos.', 1, 'XX00000001', 61, 'XX00000061', 2, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:56:01', NULL, 'XX'),
(108, 'XX00000108', 2, 3, 'Métodos de cálculo del costo empleado.', 1, 'XX00000001', 61, 'XX00000061', 3, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:56:21', NULL, 'XX'),
(109, 'XX00000109', 3, 3, 'Análisis de las desviaciones del costo y su aplicación en la toma de decisiones.', 1, 'XX00000001', 61, 'XX00000061', 1, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:57:06', NULL, 'XX'),
(110, 'XX00000110', 4, 3, 'Fichas actualizadas de costo y precio.', 1, 'XX00000001', 61, 'XX00000061', 3, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:57:26', NULL, 'XX'),
(111, 'XX00000111', 1, 3, 'Razones de liquidez: general, inmediata y ácida.', 1, 'XX00000001', 62, 'XX00000062', 3, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:58:00', NULL, 'XX'),
(112, 'XX00000112', 2, 3, 'Razones de actividad: ciclo de cobros, ciclo de pagos, ciclo de efectivo y ciclo de inventarios.', 1, 'XX00000001', 62, 'XX00000062', 0, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:58:20', NULL, 'XX'),
(113, 'XX00000113', 3, 3, 'Razones de endeudamiento: razón de endeudamiento, deuda-activos, deuda-patrimonio, calidad de la deuda y cobertura de los intereses.', 1, 'XX00000001', 62, 'XX00000062', 4, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:58:39', NULL, 'XX'),
(114, 'XX00000114', 4, 3, 'Razones de rentabilidad: margen de utilidad, rentabilidad financiera y rentabilidad económica.', 1, 'XX00000001', 62, 'XX00000062', 0, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:58:57', NULL, 'XX'),
(115, 'XX00000115', 5, 3, 'Administración financiera del inventario.', 1, 'XX00000001', 62, 'XX00000062', 0, 2018, 2019, 'evidencias', 'indicaciones', 2, '2018-09-28 16:59:36', NULL, 'XX'),
(116, 'XX00000116', 6, 1, 'Pago: forma, medio, plazo, tasas de interés, lugar y cualquier otra condición del pago.', 1, 'XX00000001', 51, 'XX00000051', 6, 2018, 2019, NULL, NULL, 12, '2018-09-28 16:30:47', NULL, 'XX'),
(117, 'XX00000117', 6, 3, 'La estructura del inventario y sus respectivos ciclos deben corresponderse con las necesidades de la entidad.', 1, 'XX00000001', 62, 'XX00000062', 0, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:00:25', NULL, 'XX'),
(118, 'XX00000118', 7, 3, 'Tratamiento adecuado según la legislación vigente a los inventarios ociosos y de lenta rotación.', 1, 'XX00000001', 62, 'XX00000062', 0, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:00:48', NULL, 'XX'),
(119, 'XX00000119', 1, 3, 'Conocimiento de los instrumentos de cobros y pagos que puede emplear y que su selección sea adecuada.', 1, 'XX00000001', 62, 'XX00000062', 3, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:01:15', NULL, 'XX'),
(120, 'XX00000120', 9, 3, 'Análisis de antigÁ¼edad de las cuentas por cobrar y pagar que intervienen en el proceso de cobros y pagos, y que sus saldos estén conciliados y documentados.', 1, 'XX00000001', 62, 'XX00000062', 3, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:01:39', NULL, 'XX'),
(121, 'XX00000121', 10, 3, 'Financiamiento de las inversiones.', 1, 'XX00000001', 62, 'XX00000062', 3, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:01:58', NULL, 'XX'),
(122, 'XX00000122', 11, 3, 'Tratamiento financiero a las pérdidas.', 1, 'XX00000001', 62, 'XX00000062', 2, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:02:15', NULL, 'XX'),
(123, 'XX00000123', 1, 3, 'Financiamiento de las Organizaciones Superiores de Dirección.', 1, 'XX00000001', 62, 'XX00000062', 3, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:02:33', NULL, 'XX'),
(124, 'XX00000124', 13, 3, 'Asignaciones presupuestarias o subsidios por diferentes conceptos.', 1, 'XX00000001', 62, 'XX00000062', 5, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:02:54', NULL, 'XX'),
(125, 'XX00000125', 14, 3, 'Tributos e impuestos.', 1, 'XX00000001', 62, 'XX00000062', 2, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:03:15', NULL, 'XX'),
(126, 'XX00000126', 15, 3, 'Daños y perjuicios económicos causados al patrimonio de la entidad o al Presupuesto del Estado.', 1, 'XX00000001', 62, 'XX00000062', 2, 2018, 2019, NULL, NULL, 12, '2018-09-28 17:03:33', NULL, 'XX'),
(127, 'XX00000127', 1, 3, 'El efectivo y valores equivalentes se mantienen en un lugar apropiado que ofrezca garantía contra robos, incendios, etc. y el cajero tiene firmada el Acta de Responsabilidad Material por la custodia del efectivo y otros bienes valores depositados en la caja de seguridad.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, 'evidencias', 'indicaciones', 12, '2018-10-06 15:17:31', NULL, 'XX'),
(128, 'XX00000128', 2, 3, 'Se cumple lo establecido con relación a la tenencia y custodia de la combinación de la caja de seguridad.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, 'evidencias', 'indicaciones', 12, '2018-10-06 15:19:40', NULL, 'XX'),
(129, 'XX00000129', 3, 3, 'Son verificados el importe de los ingresos cobrados en efectivo y se corresponden con la suma de los documentos justificantes.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, 'evidencias', 'indicaciones', 12, '2018-10-06 15:20:45', NULL, 'XX'),
(130, 'XX00000130', 4, 3, 'Se controlan como está establecido los modelos de Recibo de Efectivo que se encuentran en poder del cajero.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:21:34', NULL, 'XX'),
(131, 'XX00000131', 5, 3, 'Se controla que los cobros en efectivo procedentes de ingresos, no se utilicen para efectuar pagos.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:22:03', NULL, 'XX'),
(132, 'XX00000132', 1, 4, 'La elaboración del diagrama del flujo de la información de la entidad, definiendo el emisor, receptor y canales de comunicación.', 1, 'XX00000001', 81, 'XX00000081', 6, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:23:10', NULL, 'XX'),
(133, 'XX00000133', 6, 3, 'Se realizan arqueos al efectivo en caja de forma sorpresiva y al término de cada mes.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:23:12', NULL, 'XX'),
(134, 'XX00000134', 2, 4, 'Tener bien definido la frecuencia, formato, almacenamiento y soporte de los documentos y bases de datos relevantes.', 1, 'XX00000001', 41, 'XX00000041', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:23:34', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(135, 'XX00000135', 7, 3, 'La suma del efectivo en caja más los documentos pagados y no reembolsados coinciden con el fondo autorizado.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:23:51', NULL, 'XX'),
(136, 'XX00000136', 3, 4, 'Clasificada la información oficial de la entidad, identificando su contenido, períodos de conservación y otros aspectos específicos.', 1, 'XX00000001', 81, 'XX00000081', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:23:59', NULL, 'XX'),
(137, 'XX00000137', 4, 4, 'Determinar los accesos a la información.', 1, 'XX00000001', 81, 'XX00000081', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:24:18', NULL, 'XX'),
(138, 'XX00000138', 8, 3, 'Se deposita en el banco, el día hábil siguiente, el efectivo recaudado; de no ser así, existe un documento por las personas facultadas que aprueba otro término.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:24:26', NULL, 'XX'),
(139, 'XX00000139', 1, 4, 'Desarrollar cohesión, armonía e implicación de todos los trabajadores.', 1, 'XX00000001', 76, 'XX00000076', 2, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:24:43', NULL, 'XX'),
(140, 'XX00000140', 1, 4, 'Fortalecer identidad e imagen interna y externa que de respuesta a políticas institucionales.', 1, 'XX00000001', 76, 'XX00000076', 3, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:25:02', NULL, 'XX'),
(141, 'XX00000141', 3, 4, 'Incrementar economía, eficiencia y eficacia de los recursos, potenciar el sentido de pertenencia y desarrollar valores éticos y de la cultura organizacional.', 1, 'XX00000001', 76, 'XX00000076', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:25:24', NULL, 'XX'),
(142, 'XX00000142', 116, 4, 'El sistema para la gestión de la información logra que la comunicación descendente, facilite que los trabajadores conozcan y entiendan los principios y metas de la organización.', 1, 'XX00000001', 41, 'XX00000041', 3, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:25:50', NULL, 'XX'),
(143, 'XX00000143', 117, 4, 'El sistema para la gestión de la información logra que la comunicación ascendente, permita la mejora continua de la organización al retroalimentarse con la opinión de los trabajadores.', 1, 'XX00000001', 41, 'XX00000041', 5, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:26:08', NULL, 'XX'),
(144, 'XX00000144', 118, 4, 'El sistema para la gestión de la información logra que la comunicación horizontal, garantice la ágil y rápida respuesta de los problemas que se presentan en los diferentes procesos y fortalece el trabajo en grupo y el desarrollo de la inteligencia colectiva.', 1, 'XX00000001', 41, 'XX00000041', 2, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:26:27', NULL, 'XX'),
(145, 'XX00000145', 119, 4, 'Se informa y analiza con los trabajadores periódicamente, el comportamiento de la gestión de la entidad, después de aplicadas las facultades otorgadas al sector empresarial a partir de las directivas aprobadas para el plan.', 1, 'XX00000001', 41, 'XX00000041', 5, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:26:45', NULL, 'XX'),
(146, 'XX00000146', 9, 3, 'Se controlan los vales para pagos menores y sus justificantes, así como los modelos de depósitos de efectivo, según la legislación vigente.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:27:26', NULL, 'XX'),
(147, 'XX00000147', 120, 4, 'Están definidos los responsables de la información y comunicación en la entidad.', 1, 'XX00000001', 42, 'XX00000042', 3, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:27:27', NULL, 'XX'),
(148, 'XX00000148', 10, 3, 'Tienen establecido el registro para el control de los cheques emitidos, cargados por el banco, caducados y cancelados.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:27:59', NULL, 'XX'),
(149, 'XX00000149', 121, 4, 'Se aplican las políticas establecidas para garantizar la calidad de la información relevante, su organización y conservación, que permita ser auditada.', 1, 'XX00000001', 42, 'XX00000042', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:28:12', NULL, 'XX'),
(150, 'XX00000150', 122, 4, 'Existe una adecuada disciplina informativa que garantice el cumplimiento de lo establecido para el sistema informativo y el intercambio entre sus integrantes.', 1, 'XX00000001', 42, 'XX00000042', 6, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:28:30', NULL, 'XX'),
(151, 'XX00000151', 11, 3, 'Se revisan los documentos que dan origen a los cheques antes de firmarlos.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:28:38', NULL, 'XX'),
(152, 'XX00000152', 123, 4, 'Aplica su entidad las buenas prácticas para el tratamiento de la evidencia documental prevista en la legislación archivística cubana y las normas del sistema de gestión documental, que permita de forma transparente y responsable la rendición de cuenta de los cuadros y funcionarios.', 1, 'XX00000001', 43, 'XX00000043', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:30:42', NULL, 'XX'),
(153, 'XX00000153', 12, 3, 'Se controla que las personas autorizadas a firmar cheques no contabilicen dichas operaciones.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:29:11', NULL, 'XX'),
(154, 'XX00000154', 124, 4, 'Los cuadros y funcionarios informan de forma integral acerca de la probidad de su gestión y toma de decisiones.', 1, 'XX00000001', 43, 'XX00000043', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:30:18', NULL, 'XX'),
(155, 'XX00000155', 13, 3, 'Existencia de registro de firmas autorizadas para el desarrollo de las diferentes operaciones en la caja.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:29:41', NULL, 'XX'),
(156, 'XX00000156', 125, 4, 'Existe un cronograma con las fechas de las rendiciones de cuenta.', 1, 'XX00000001', 43, 'XX00000043', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:29:51', NULL, 'XX'),
(157, 'XX00000157', 14, 3, 'Se controla el pago de los servicios a cuentapropistas, tanto por persona jurídica cubana de cualquier organismo y unidades presupuestadas de acuerdo con la legislación vigente.', 1, 'XX00000001', 63, 'XX00000063', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:30:17', NULL, 'XX'),
(158, 'XX00000158', 1, 3, 'Existe control sobre los cheques emitidos, cargados por el banco, caducados y cancelados.', 1, 'XX00000001', 64, 'XX00000064', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:30:54', NULL, 'XX'),
(159, 'XX00000159', 1, 4, 'a) Parte de un examen valorativo sobre la ejecución del Presupuesto y el cumplimiento del Plan de la Economía, así como el desempeño y conducta ética de los directivos y funcionarios que rinden cuentas ante su órgano de dirección, colectivo laboral o sus instancias superiores', 1, 'XX00000001', 77, 'XX00000077', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:31:19', NULL, 'XX'),
(160, 'XX00000160', 2, 3, 'Al menos una persona de los que firman los cheques, tiene que revisar los documentos que dan origen a la emisión de éstos, antes de firmarlos.', 1, 'XX00000001', 64, 'XX00000064', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:31:28', NULL, 'XX'),
(161, 'XX00000161', 2, 4, 'b) Contiene información clara, oportuna y adecuada sobre los principales indicadores que determinan de forma integral los resultados de las áreas o actividades técnicas, comerciales, económicas y administrativas, que permitan medir el impacto de la gestión para la entidad y el país.', 1, 'XX00000001', 77, 'XX00000077', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:31:47', NULL, 'XX'),
(162, 'XX00000162', 3, 3, 'Cuando se concluye el expediente de pago, también la persona que inició la operación debe revisar los documentos es decir, cuando se procede al archivo de la documentación (oferta, factura, informe de recepción, certificación del servicio recibido, conciliación de saldo u otros).', 1, 'XX00000001', 64, 'XX00000064', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:32:18', NULL, 'XX'),
(163, 'XX00000163', 127, 5, 'El sistema de control interno implementado se corresponde con los principios y características que se refrendan en la Resolución No.60/2011 de la Contraloría General de la República.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:36:52', NULL, 'XX'),
(164, 'XX00000164', 4, 3, 'Las operaciones de las cuentas bancarias se concilian periódicamente y existe evidencia de las conciliaciones de todas las cuentas de Efectivo en Banco, mensualmente.', 1, 'XX00000001', 64, 'XX00000064', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:32:53', NULL, 'XX'),
(165, 'XX00000165', 128, 5, 'Adecuada la Guía de Autocontrol General a las condiciones y características de la entidad.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:37:24', NULL, 'XX'),
(166, 'XX00000166', 129, 5, 'Analizados los resultados de las acciones de control interna y externas con los trabajadores y se elaboró del plan de medidas correspondiente.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:37:43', NULL, 'XX'),
(167, 'XX00000167', 5, 3, 'Se revisan las operaciones y justificantes correspondientes a cobros automáticos.', 1, 'XX00000001', 64, 'XX00000064', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:33:37', NULL, 'XX'),
(169, 'XX00000169', 6, 3, 'Los pagos efectuados deben corresponderse con los conceptos y los montos aprobados en los presupuestos correspondientes.', 1, 'XX00000001', 64, 'XX00000064', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:34:12', NULL, 'XX'),
(170, 'XX00000170', 131, 5, 'Realizan los trabajadores el control permanente sobre las actividades que ellos mismos llevan a cabo.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:38:54', NULL, 'XX'),
(171, 'XX00000171', 132, 5, 'Se controla la aplicación del Sistema de Control Interno en las unidades subordinadas.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:39:19', NULL, 'XX'),
(172, 'XX00000172', 7, 3, 'Los funcionarios autorizados para firmar cheques no pueden contabilizar estas operaciones.', 1, 'XX00000001', 64, 'XX00000064', 4, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:34:38', NULL, 'XX'),
(173, 'XX00000173', 133, 5, 'Se realizan Inspecciones Estatales por los organismos rectores de las actividades, dejando los señalamientos y el plan de medidas.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:39:48', NULL, 'XX'),
(174, 'XX00000174', 134, 5, 'Conformado el expediente de las acciones de control de acuerdo con la legislación vigente.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:39:59', NULL, 'XX'),
(175, 'XX00000175', 135, 5, 'Existen auditores internos en la entidad y han elaborado un plan de auditoría interna que se cumple.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:40:11', NULL, 'XX'),
(176, 'XX00000176', 1, 3, 'La persona del almacén que cuenta, mide y pesa todos los productos recibidos no tiene acceso al documento del suministrador.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:35:21', NULL, 'XX'),
(177, 'XX00000177', 136, 5, 'Se logra medir el impacto sobre el control y cumplimiento de las tareas, constatando mayor calidad, motivación y mejores resultados de trabajo, producto del cambio en la mentalidad de los directivos en cuanto al desarrollo de sus funcionen a partir de la implementación y actualización del modelo de gestión económica aprobado en los Lineamientos de la Política Económica y Social del Partido y la Revolución.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:40:29', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(178, 'XX00000178', 1, 3, 'Los submayores de inventario del área contable deben estar al día.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:35:51', NULL, 'XX'),
(179, 'XX00000179', 137, 5, 'Se encuentra constituido por resolución el Comité de Prevención y Control y cumple, presidido por el jefe máximo de la entidad, su función asesora, velando por el adecuado funcionamiento del Sistema de Control Interno y su mejoramiento continuo.', 1, 'XX00000001', 45, 'XX00000045', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:35:56', NULL, 'XX'),
(180, 'XX00000180', 3, 3, 'El almacén informa las existencias de cada producto en todos los modelos de entradas y salidas, después de anotados estos movimientos, dichas existencias tienen que cotejarse diariamente con los submayores de inventario, localizándose inmediatamente las diferencias detectadas.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:38:21', NULL, 'XX'),
(181, 'XX00000181', 130, 5, 'Se realizan periódicamente autoevaluaciones del sistema de control interno y se deja evidencia documental de su análisis con los trabajadores.', 1, 'XX00000001', 44, 'XX00000044', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:38:37', NULL, 'XX'),
(182, 'XX00000182', 1, 3, 'Es obligatorio elaborar un plan anual y efectuar conteos físicos periódicos de los productos almacenados, y cuando estos chequeos arrojen sistemáticamente diferencias, se realiza un inventario general anual y se depuran las mismas conforme a la legislación vigente.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:38:50', NULL, 'XX'),
(183, 'XX00000183', 5, 3, 'El personal de los almacenes tiene que tener firmadas actas de responsabilidad material por la custodia de los bienes materiales y en caso de faltantes o pérdidas, aplicárseles dicha responsabilidad, de acuerdo con lo establecido.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:39:24', NULL, 'XX'),
(184, 'XX00000184', 6, 3, 'Cuando se detecten faltantes o sobrantes de bienes materiales se elaboran los expedientes correspondientes y se contabilizan inmediatamente, tramitándose y aprobándose dentro de los términos establecidos.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:40:02', NULL, 'XX'),
(185, 'XX00000185', 7, 3, 'Las producciones terminadas y las producidas para insumo remitidas a los almacenes tienen que estar amparadas por el documento justificativo de la entrega de éstas.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:40:32', NULL, 'XX'),
(186, 'XX00000186', 8, 3, 'Se controla a través de las tarjetas de estiba y de los submayores de inventario, los materiales y los equipos por instalar destinados al proceso inversionista, así como los productos recibidos o remitidos en consignación y en depósito.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:40:54', NULL, 'XX'),
(187, 'XX00000187', 138, 5, 'La composición, permanencia y periodicidad de las reuniones del Comité de Prevención y Control están definidas por la máxima autoridad, mediante evidencia documental, así como el cronograma de reuniones y de los temas tratados, acuerdos adoptados y su seguimiento en las sesiones de trabajo. Se conservan las actas y acuerdos como evidencia de los análisis realizados.', 1, 'XX00000001', 45, 'XX00000045', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:40:55', NULL, 'XX'),
(188, 'XX00000188', 139, 5, 'Se analizan con la rigurosidad requerida los casos de indisciplinas, ilegalidades y presuntos hechos delictivos y de corrupción. Se aplican las medidas disciplinarias pertinentes.', 1, 'XX00000001', 45, 'XX00000045', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:41:10', NULL, 'XX'),
(189, 'XX00000189', 9, 3, 'En caso de detectarse diferencias físicas entre lo facturado por ventas de productos y lo recibido como pagos por los clientes, deben elaborarse los expedientes de faltantes correspondientes.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:41:22', NULL, 'XX'),
(190, 'XX00000190', 140, 5, 'Los hechos o conductas que pueden ser constitutivas de delitos, se dan a conocer a las autoridades correspondientes, independientemente de la medida disciplinaria que se decida imponérsele al infractor.', 1, 'XX00000001', 45, 'XX00000045', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:41:24', NULL, 'XX'),
(191, 'XX00000191', 10, 3, 'Debe existir un control eficaz de los útiles y herramientas en uso; en caso de detectarse faltantes o sobrantes de estos bienes se elaboran los expedientes correspondientes, se contabilizan correctamente y se aplica la responsabilidad material.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:41:52', NULL, 'XX'),
(192, 'XX00000192', 11, 3, 'El personal del almacén no tiene acceso a los registros contables ni a los Submayores de control de inventarios.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:42:23', NULL, 'XX'),
(193, 'XX00000193', 113, 3, 'Existen y se cumplen los procedimientos escritos de cómo aplicar los indicadores de rendimiento y de desempeño.', 1, 'XX00000001', 40, 'XX00000040', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:42:44', NULL, 'XX'),
(194, 'XX00000194', 12, 3, 'El área contable tiene que revisar los precios y cálculos de los productos recepcionados.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:42:52', NULL, 'XX'),
(195, 'XX00000195', 112, 3, 'Están establecidos indicadores cualitativos y cuantitativos para medir el desempeño del personal.', 1, 'XX00000001', 40, 'XX00000040', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:43:08', NULL, 'XX'),
(196, 'XX00000196', 13, 3, 'Los procedimientos seguidos con los inventarios de lento movimiento y ociosos cumplen con lo establecido en la legislación vigente.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:43:19', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES
(197, 'XX00000197', 14, 3, 'Se cuenta con la aprobación emitida por el nivel correspondiente, para el destino final económicamente útil de los inventarios de lento movimiento y los ociosos.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:43:41', NULL, 'XX'),
(198, 'XX00000198', 111, 3, 'Al producirse un incidente o violación, se reporta la información oportunamente a la Oficina de Seguridad para las Redes Informáticas (OSRI) y a la instancia superior de la entidad, de acuerdo con la importancia de la misma.', 1, 'XX00000001', 39, 'XX00000039', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:43:52', NULL, 'XX'),
(199, 'XX00000199', 15, 3, 'Se cuenta con el procedimiento emitido por los jefes o presidentes de su nivel superior para las entidades que aprobaron venta de inventarios de lento movimiento y ociosos.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:44:07', NULL, 'XX'),
(200, 'XX00000200', 1, 3, 'Protección contra virus y otros programas dañinos.', 1, 'XX00000001', 75, 'XX00000075', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:44:29', NULL, 'XX'),
(201, 'XX00000201', 16, 3, 'Una vez vendidos los inventarios de lento movimiento y ociosos por precios menores a los registrados en libro, se realiza el ajuste de la diferencia de valores de los inventarios vendidos, como pérdida que afectan los resultados económico-financieros del período; esta pérdida puede ser regulada con la liberación de la provisión a tales fines, si existe.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:44:38', NULL, 'XX'),
(202, 'XX00000202', 2, 3, 'Obtención de copias de resguardo.', 1, 'XX00000001', 75, 'XX00000075', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:44:46', NULL, 'XX'),
(203, 'XX00000203', 17, 3, 'El procedimiento que la entidad tiene establecido para la formación de precios de productos de lento movimiento y ociosos, para la venta mayorista y minorista, fue analizado y aprobado en el Consejo de Dirección, así como validado por su nivel superior.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:45:02', NULL, 'XX'),
(204, 'XX00000204', 3, 3, 'Verificación periódica de la seguridad de la red, para detectar posibles vulnerabilidades.', 1, 'XX00000001', 75, 'XX00000075', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:45:02', NULL, 'XX'),
(205, 'XX00000205', 4, 3, 'Eliminar la adición de algún equipo o la introducción de cualquier tipo de software en una red, sin la autorización de la dirección de la entidad.', 1, 'XX00000001', 75, 'XX00000075', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:45:22', NULL, 'XX'),
(206, 'XX00000206', 18, 3, 'Los procedimientos seguidos con los inventarios de la reserva material y movilizativa cumplen con lo establecido en la legislación vigente.', 1, 'XX00000001', 65, 'XX00000065', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:45:31', NULL, 'XX'),
(207, 'XX00000207', 5, 3, 'Asegurar la integridad, confidencialidad y oportunidad de la información, de acuerdo con los servicios que se reciben y se ofertan.', 1, 'XX00000001', 75, 'XX00000075', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:45:39', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(208, 'XX00000208', 6, 3, 'Garantizar que tanto para la asignación o para el retiro de los identificadores de usuarios en los sistemas, el jefe inmediato del usuario, notifica la solicitud de otorgamiento o retiro de permisos de acceso a quienes corresponda, definiendo los derechos y privilegios, y dejando la evidencia documental.', 1, 'XX00000001', 75, 'XX00000075', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:45:58', NULL, 'XX'),
(209, 'XX00000209', 7, 3, 'Salvar y analizar las trazas de los diferentes servicios, especificando quién la realiza y con qué frecuencia y permitiendo que sean auditables.', 1, 'XX00000001', 75, 'XX00000075', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:46:15', NULL, 'XX'),
(210, 'XX00000210', 86, 3, 'Se controla en las entidades encargadas de las actividades comerciales y de prestación de servicios en el mercado interno que los organismos y entidades estatales no adquieran mercancía en el comercio minorista o reciban servicios destinados a la población, excepto en los casos de expendio de combustible de los servicentros.', 1, 'XX00000001', 10, 'XX00000010', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:46:25', NULL, 'XX'),
(211, 'XX00000211', 107, 3, 'Verificar que los sistemas contable â€“ financieros utilizados por la entidad cuenten con certificados actualizados emitido por la entidad autorizada.', 1, 'XX00000001', 39, 'XX00000039', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:46:44', NULL, 'XX'),
(212, 'XX00000212', 87, 3, 'En las empresas mayoristas está diseñada la estrategia para el adecuado tratamiento a los inventarios de lento movimiento y ociosos, estableciéndose medidas organizativas y de control sobre los mismos y realizándose gestiones de venta, empleando técnicas como: promoción en sus almacenes, en ferias u otros espacios comerciales y oferta a través de sus agentes de ventas.', 1, 'XX00000001', 10, 'XX00000010', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:46:53', NULL, 'XX'),
(213, 'XX00000213', 108, 3, 'Se informa y analizan las vulnerabilidades encontradas en los sistemas contable â€“ financieros certificados con los propietarios del mismo.', 1, 'XX00000001', 39, 'XX00000039', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:47:11', NULL, 'XX'),
(214, 'XX00000214', 88, 3, 'En las empresas comercializadoras minoristas se garantiza la publicidad e información a la población de cada producto que se oferta, de forma adecuada.', 1, 'XX00000001', 10, 'XX00000010', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:47:31', NULL, 'XX'),
(215, 'XX00000215', 109, 3, 'Se realizan inspecciones sorpresivas para detectar entre otros aspectos:', 1, 'XX00000001', 39, 'XX00000039', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:47:35', NULL, 'XX'),
(216, 'XX00000216', 1, 3, 'La caja registradora tiene habilitada la fecha y la hora actualizada, y aparecen los datos del cajero y el código o el nombre de la tienda; así como cuenta con una impresión legible de los registros.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:48:33', NULL, 'XX'),
(217, 'XX00000217', 2, 3, 'Funciona el display de la caja registradora y está dirigido al cliente; así como el POS funciona y se encuentra ubicado en el área de venta, de ser necesario cuentan con scanner para el proceso de venta.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:49:00', NULL, 'XX'),
(218, 'XX00000218', 3, 3, 'Existe una adecuada limitación en el acceso y posesión de las llaves de la caja registradora en la tienda.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:49:39', NULL, 'XX'),
(219, 'XX00000219', 1, 3, 'Las extracciones o préstamos no autorizados de bienes informáticos.', 1, 'XX00000001', 82, 'XX00000082', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:49:45', NULL, 'XX'),
(220, 'XX00000220', 2, 3, 'El control y uso inadecuado de los servicios informáticos y telefónicos.', 1, 'XX00000001', 82, 'XX00000082', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:50:06', NULL, 'XX'),
(221, 'XX00000221', 4, 3, 'El acta de constitución de los fondos está actualizada. Se encuentra actualizada y firmada por cada uno de los cajeros el Acta de Responsabilidad Material para la custodia del fondo para cambio y el efectivo recaudado.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:50:09', NULL, 'XX'),
(222, 'XX00000222', 110, 3, 'Se analizan las causas de compras de sistemas que no estén siendo explotados en la entidad.', 1, 'XX00000001', 39, 'XX00000039', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:50:29', NULL, 'XX'),
(223, 'XX00000223', 5, 3, 'Poseen vales de venta en el área de la caja registradora y son controlados, esa área se mantiene sin efectivo fuera y posee moneda fraccionaria para cambio.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:50:42', NULL, 'XX'),
(224, 'XX00000224', 105, 3, 'Cumplen las políticas, normas y procedimientos escritos para la planificación, ejecución, evaluación y control del uso de las tecnologías de información para el logro de los objetivos de la entidad.', 1, 'XX00000001', 39, 'XX00000039', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:50:57', NULL, 'XX'),
(225, 'XX00000225', 101, 3, 'Definidos los cargos que tienen tareas clave y se garantiza la continuidad de las mismas durante períodos de ausencias del personal, al contar con personal preparado para la sustitución.', 1, 'XX00000001', 38, 'XX00000038', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:51:18', NULL, 'XX'),
(226, 'XX00000226', 6, 3, 'Se controlan al dorso del modelo de Liquidación de Cajero los billetes de alta denominación.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:51:23', NULL, 'XX'),
(227, 'XX00000227', 102, 3, 'Existe un plan de rotación del personal que tiene a cargo las tareas con mayor probabilidad de comisión de irregularidades.', 1, 'XX00000001', 38, 'XX00000038', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:51:40', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(228, 'XX00000228', 7, 3, 'El efectivo en tienda es controlado, siendo depositado en el banco con la frecuencia legalmente establecida.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:51:50', NULL, 'XX'),
(229, 'XX00000229', 103, 3, 'Existe evidencia documental de la rotación sistemática del personal en dichas tareas.', 1, 'XX00000001', 38, 'XX00000038', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:52:10', NULL, 'XX'),
(230, 'XX00000230', 8, 3, 'Al realizar la declaración del efectivo total y valores en caja recaudado al final del día, coincide con los importes que emite la caja registradora.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:52:15', NULL, 'XX'),
(231, 'XX00000231', 104, 3, 'En el caso de contar con pocos trabajadores y dificultarse el cumplimiento de esta norma, se aumenta la periodicidad de las acciones de supervisión y control.', 1, 'XX00000001', 38, 'XX00000038', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:52:28', NULL, 'XX'),
(232, 'XX00000232', 9, 3, 'Se confecciona correctamente la Liquidación de Cajero en el modelo correspondiente.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:52:40', NULL, 'XX'),
(233, 'XX00000233', 98, 3, 'Se cumplen los procedimientos de seguridad realizados para proteger y conservar los recursos y registros que constituyen evidencia de los actos administrativos.', 1, 'XX00000001', 37, 'XX00000037', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:52:48', NULL, 'XX'),
(234, 'XX00000234', 10, 3, 'Los códigos y precios de los productos coinciden con los registrados en la caja registradora y son los correctos de acuerdo con el Libro Oficial de Precios.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:53:07', NULL, 'XX'),
(235, 'XX00000235', 11, 3, 'Los precios son visibles al cliente y se entrega comprobante de venta al cliente, correctamente confeccionados.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:53:35', NULL, 'XX'),
(236, 'XX00000236', 12, 3, 'Existe información al cliente sobre la rebaja de precios y los productos que sufren rebaja estando en la tienda, tienen el precio anterior tachado y reetiquetado con el precio actual de forma separada.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:54:27', NULL, 'XX'),
(237, 'XX00000237', 13, 3, 'La tienda posee control actualizado de los productos perecederos y de aquellos lotes de los productos que lo requieren.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:54:51', NULL, 'XX'),
(238, 'XX00000238', 99, 3, 'Se revisa que se cumplan  los niveles de acceso a las áreas y dependencias.', 1, 'XX00000001', 37, 'XX00000037', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:55:02', NULL, 'XX'),
(239, 'XX00000239', 14, 3, 'Se retiran del área de venta los productos perecederos vencidos y se aplica en la tienda el procedimiento establecido para los productos perecederos próximos a la fecha de vencimiento.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:55:20', NULL, 'XX'),
(240, 'XX00000240', 100, 3, 'Las personas autorizadas para acceder a los recursos, activos, registros y comprobantes; rinden cuenta de su custodia y utilización.', 1, 'XX00000001', 37, 'XX00000037', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:55:31', NULL, 'XX'),
(241, 'XX00000241', 15, 3, 'Los submayores y/o listados de existencia de la tienda están actualizados.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:56:03', NULL, 'XX'),
(242, 'XX00000242', 16, 3, 'La tienda oferta mercancía de acuerdo con la caracterización establecida en el Certificado Comercial.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:57:01', NULL, 'XX'),
(243, 'XX00000243', 17, 3, 'La mercancía de la tienda está debidamente etiquetada y los medios para etiquetar están custodiados por el personal autorizado y se encuentran en lugar adecuado, toda la mercancía en tienda pertenece a la entidad.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:57:45', NULL, 'XX'),
(244, 'XX00000244', 18, 3, 'Se encuentra actualizado y visible al cliente el número de teléfono para atender las quejas.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:58:28', NULL, 'XX'),
(245, 'XX00000245', 19, 3, 'Existen condiciones adecuadas para mantener, sin afectación de la calidad, los productos que se ofertan.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 15:59:30', NULL, 'XX'),
(246, 'XX00000246', 20, 3, 'Los resultados de la muestra física del inventario se corresponden con lo controlado en el submayor.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:00:02', NULL, 'XX'),
(247, 'XX00000247', 21, 3, 'Se confecciona correctamente el expediente de merma y el modelo de propuesta de mercancía a declarar como merma, donde debe aparecer claramente la causa o el defecto de la mercancía.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:00:36', NULL, 'XX'),
(248, 'XX00000248', 22, 3, 'Los expedientes de mermas están acompañados por el acta que recoja el análisis en el Consejo de Dirección de las causas y condiciones que originaron las mermas y las medidas adoptadas.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:01:13', NULL, 'XX'),
(249, 'XX00000249', 23, 3, 'Se lleva un registro y se controla el destino final de las devoluciones.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:01:48', NULL, 'XX'),
(250, 'XX00000250', 24, 3, 'Los productos con pérdidas de atributos se encuentran separados en áreas diferenciadas y señalizadas.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:02:21', NULL, 'XX'),
(251, 'XX00000251', 24, 3, 'Se corresponden los destinos de la merma con los aprobados por el nivel superior.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:02:55', NULL, 'XX'),
(252, 'XX00000252', 25, 3, 'El Acta de Declaración y detalle de las mercancías propuestas a destrucción están confeccionadas correctamente y firmadas.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:03:23', NULL, 'XX'),
(253, 'XX00000253', 27, 3, 'Se comprueba que a la mercancía en merma le fue aplicado el procedimiento establecido para los casos de pérdida de atributos, retirándolos de la venta de acuerdo al tiempo establecido.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:04:14', NULL, 'XX'),
(254, 'XX00000254', 28, 3, 'La tienda posee los medios para probarle al cliente el correcto funcionamiento de la mercancía que por sus características lo requiera.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:04:48', NULL, 'XX'),
(255, 'XX00000255', 29, 3, 'Los equipos de medición (pesaje) están certificados y actualizados para su uso y existe equipo de comprobación para el peso de los productos envasados.', 1, 'XX00000001', 66, 'XX00000066', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:05:18', NULL, 'XX'),
(256, 'XX00000256', 1, 3, 'Cada responsable de área tiene firmada un Acta de Responsabilidad Material de los activos fijos bajo su custodia.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:06:02', NULL, 'XX'),
(257, 'XX00000257', 2, 3, 'En el área contable se debe contar con la información mínima indispensable de estos bienes para su correcta identificación, verificándose la suma de sus valores con el saldo de la cuenta control', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:06:34', NULL, 'XX'),
(258, 'XX00000258', 3, 3, 'Los modelos de control por áreas de los activos fijos tangibles deben encontrarse actualizados, en éstas y en el área contable, y en los mismos debe llevarse el control del número de serie en los casos de los equipos de transporte, eléctricos y electrónicos.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:07:01', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(259, 'XX00000259', 4, 3, 'Se elaboran inmediatamente a su ocurrencia los modelos de movimientos de estos bienes, por las altas, bajas, traslados, enviados a reparar, ventas, etc.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:07:38', NULL, 'XX'),
(260, 'XX00000260', 5, 3, 'De existir algún activo fijo, que sea de propiedad personal, la existencia de documento de autorización debidamente aprobado para su uso en la entidad.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:08:10', NULL, 'XX'),
(261, 'XX00000261', 6, 3, 'Es preciso elaborar el plan anual y efectuar chequeos periódicos y sistemáticos de los bienes, y en caso de detectarse faltantes o sobrantes, elaborarse los expedientes correspondientes, contabilizarse éstos correctamente y aplicarse en el caso de faltantes, la responsabilidad material.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:08:40', NULL, 'XX'),
(262, 'XX00000262', 7, 3, 'Los valores de los activos fijos tangibles se deprecian mensualmente de acuerdo con las regulaciones vigentes y en base a las tasas establecidas para su reposición.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:09:07', NULL, 'XX'),
(263, 'XX00000263', 8, 3, 'No se depreciarán activos fijos tangibles que estén en desuso por rotura, enviados a reparar u otras causas.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:10:11', NULL, 'XX'),
(264, 'XX00000264', 9, 3, 'Cuando proceda, dicha depreciación debe aportarse al Presupuesto del Estado correctamente y en el plazo fijado.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:10:42', NULL, 'XX'),
(265, 'XX00000265', 10, 3, 'Cuando se sustituya el responsable de un área debe efectuarse el chequeo de todos los activos fijos tangibles bajo su custodia, a fin de fijar la responsabilidad material correctamente.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:11:31', NULL, 'XX'),
(266, 'XX00000266', 1, 3, 'La entidad debe establecer y aplicar un sistema que le permita conocer los costos de sus producciones por áreas y procesos y determinar las desviaciones desglosadas por conceptos, al compararse con las Fichas de Costo confeccionadas.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:11:48', NULL, 'XX'),
(267, 'XX00000267', 2, 3, 'Es imprescindible la actualización de las Fichas de Costo por producciones. Contaran con submayores de proceso por cada actividad de producción o servicios y se verificará la coincidencia del saldo de los submayores de proceso con el Balance.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:12:05', NULL, 'XX'),
(268, 'XX00000268', 11, 3, 'Las bajas, ventas y traslados de estos bienes deben estar aprobadas por los funcionarios autorizados.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:12:09', NULL, 'XX'),
(269, 'XX00000269', 3, 3, 'Deben realizarse análisis periódicos y sistemáticos de las informaciones de costo y de las causas de las desviaciones determinadas y analizarse éstas en el Consejo de Dirección.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:12:29', NULL, 'XX'),
(270, 'XX00000270', 12, 3, 'El proceso de baja para los equipos pesados, de construcción y tractores se realiza según las indicaciones de los organismos correspondientes.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:12:34', NULL, 'XX'),
(271, 'XX00000271', 4, 3, 'Es conveniente elaborar presupuestos de gastos por áreas de responsabilidad y compararse éstos con los gastos reales incurridos en las mismas, analizándose las causas de las desviaciones detectadas.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:13:00', NULL, 'XX'),
(272, 'XX00000272', 13, 3, 'Se de alta a los activos fijos tangibles al concluir las inversiones y se analicen los gastos que no se transfieren para diferirlos.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:13:03', NULL, 'XX'),
(273, 'XX00000273', 5, 3, 'Los gastos deben registrarse al incurrirse y analizarse por los elementos (conceptos) de gastos establecidos por las entidades.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:13:17', NULL, 'XX'),
(274, 'XX00000274', 14, 3, 'Se deprecian mensualmente los activos fijos tangibles en las entidades del sistema empresarial y presupuestado de acuerdo con las regulaciones vigentes y se aplican las tasas establecidas.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:13:29', NULL, 'XX'),
(275, 'XX00000275', 6, 3, 'En las unidades presupuestadas deben mantenerse actualizados los registros de los gastos presupuestarios devengados, analizados por grupos presupuestarios, epígrafes y partidas.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:13:35', NULL, 'XX'),
(276, 'XX00000276', 7, 3, 'Los precios de los productos y servicios prestados facturados, deben establecerse, de proceder, en base a las Fichas de Costo elaboradas y tanto éstos como los de las mercancías vendidas deben haber sido aprobados por el nivel correspondiente.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:14:01', NULL, 'XX'),
(277, 'XX00000277', 8, 3, 'Los gastos indirectos de producción deben trasladarse a los costos directos de cada producto elaborado o servicio prestado, mensualmente.', 1, 'XX00000001', 74, 'XX00000074', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:14:18', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(278, 'XX00000278', 15, 3, 'Se controlan los activos fijos intangibles en submayores habilitados al efecto y se amortizan mensualmente, en base a las tasas establecidas por cada organismo.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:14:36', NULL, 'XX'),
(279, 'XX00000279', 9, 3, 'Se definen los métodos para la determinación de las unidades equivalentes y para el tratamiento de los residuos, subproductos, productos intermedios y defectuosos.', 1, 'XX00000001', 10, 'XX00000010', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:14:40', NULL, 'XX'),
(280, 'XX00000280', 1, 3, 'En los casos de faltantes, pérdidas o sobrantes de activos fijos tangibles, bienes materiales o recursos monetarios: se determinan éstos en unidades físicas y en valor en aquellos casos en que solamente proceda legalmente; el monto de la depreciación acumulada de los activos fijos tangibles; las causas y condiciones que les dieron lugar, investigaciones o comprobaciones realizadas; y la denuncia en caso de faltantes, ante el órgano estatal competente.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:15:05', NULL, 'XX'),
(281, 'XX00000281', 16, 3, 'En el caso de las OSDE, las empresas y las sociedades mercantiles, se destinan los recursos de la depreciación y amortización de los activos fijos tangibles e intangibles, así como la amortización de los gastos diferidos a largo plazo, para financiar las inversiones, el reequipamiento, la modernización y otros destinos, según los intereses que determinen para su desarrollo y la ampliación de sus actividades, a partir del plan de inversiones aprobado en el año.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:15:07', NULL, 'XX'),
(282, 'XX00000282', 2, 3, 'En los casos de mercancías por faltantes de origen en las operaciones de Comercio Exterior, se cuenta con: la factura del proveedor; la certificación de conocimiento de embarque; el certificado de supervisión de la mercancía en origen por una tercera entidad; la declaración de mercancía; el certificado del pesaje por la Empresa de Servicios de Certificación y Pesaje de las Cargas; el certificado de supervisión de la mercancía en destino por una tercera entidad; y la reclamación al proveedor o al seguro.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:15:22', NULL, 'XX'),
(283, 'XX00000283', 17, 3, 'Cuando un activo fijo tangible es dado de baja y como consecuencia de su desmantelamiento se decide por la autoridad competente venderlo como chatarra a otra entidad, se afectará la cuenta de Inversión Estatal por el valor inicial del activo, menos el monto de depreciación acumulada.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:15:35', NULL, 'XX'),
(284, 'XX00000284', 3, 3, 'En los casos de cancelaciones de cuentas por cobrar y por pagar, se cuenta con: el nombre del o de los clientes o suministradores; las causas por las que no se efectuó el cobro o pago; la constancia de las gestiones realizadas para el cobro; la certificación del suministrador de la no existencia del adeudo.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:15:38', NULL, 'XX'),
(285, 'XX00000285', 4, 3, 'En los casos de cancelaciones de cuentas por cobrar y por pagar debido a faltantes de mercancías en la entrega que son transportadas por un tercero: tiene constancia de las gestiones realizadas con éste y su resultado. De ser imputable al transportista, siempre que lo haya firmado, se reconoce la cuenta por cobrar a éste y se cancela el faltante.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:15:56', NULL, 'XX'),
(286, 'XX00000286', 18, 3, 'La entidad conforma y custodia un expediente con los documentos siguientes: autorización de baja del activo con destino a chatarra emitida por el nivel de autorización correspondiente; movimiento de activo fijo tangible con todas las características de dicho activo y la firma de los niveles de autorización establecidos; dictamen técnico de la entidad competente si se trata de aparatos y equipos técnicos especiales; documento de baja emitido por la Oficina del Registro de Vehículos correspondiente que certifique la entrega de la chapa y la circulación, en el caso de medios y equipos de transporte; documento primario emitido por la Empresa de Recuperación de Materias Primas que recibe la Chatarra o de la entidad autorizada por los órganos estatales a centralizar la entrega de chatarra.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:16:03', NULL, 'XX'),
(287, 'XX00000287', 5, 3, 'En los casos de cancelaciones de cuentas por cobrar y por pagar debido a faltantes de mercancías en la entrega, por las transportaciones realizadas con medios propios que no son responsabilidad del transportista: se tramita el expediente de faltante de inventario.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:16:21', NULL, 'XX'),
(288, 'XX00000288', 19, 3, 'Se comprueba el cumplimiento de las obligaciones establecidas por la legislación vigente para el uso de los vehículos estatales, en las que se incluyen las relacionadas con el Registro de Vehículos del Ministerio del Interior.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:16:31', NULL, 'XX'),
(289, 'XX00000289', 6, 3, 'En los casos de cancelaciones de cuentas por cobrar y por pagar debido a faltantes de mercancías en la entrega, que son faltantes de origen y responsabilidad del proveedor: se procede a la reclamación y se reconoce la cuenta por cobrar contra la cuenta faltante, cerrando el expediente. Lo mismo se hace cuando son mercancías cubiertas por el seguro.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:16:37', NULL, 'XX'),
(290, 'XX00000290', 7, 3, 'En el caso de consumo material o gastos no registrados en el año, se cuenta con: los documentos que amparan los gastos; las causas por las que no se efectuó el registro en su oportunidad; y el importe total.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:16:53', NULL, 'XX'),
(291, 'XX00000291', 20, 3, 'Actualizado el Registro de Equipos de la entidad, el cual coincide con el registro de activos fijos tangibles de la entidad.', 1, 'XX00000001', 67, 'XX00000067', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:16:56', NULL, 'XX'),
(292, 'XX00000292', 1, 3, 'Se separan las funciones entre la persona que controla el tiempo laborado, la que confecciona la nómina, la que paga y la que registra.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:17:18', NULL, 'XX'),
(293, 'XX00000293', 8, 3, 'El expediente destinado para los ajustes, realizados a consecuencia de las rebajas de precios minoristas, por pérdidas de calidad en los productos agropecuarios, cuenta con: las actas detalladas y certificadas por los funcionarios e inspectores autorizados para aprobar las citadas rebajas; y el importe total.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:17:19', NULL, 'XX'),
(294, 'XX00000294', 2, 3, 'Se procede a revisar y aprobar las nóminas antes de la extracción del efectivo para su pago por los diferentes sistemas de pagos y contra la plantilla cubierta (personal fijo y contratado) en cada área de responsabilidad.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:17:45', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(295, 'XX00000295', 9, 3, 'El expediente destinado para los ajustes, realizados a consecuencia de las rebajas de precios minoristas, por pérdidas de calidad en los productos agropecuarios, cuenta con: las actas detalladas y certificadas por los funcionarios e inspectores autorizados para aprobar las citadas rebajas; y el importe total.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:17:48', NULL, 'XX'),
(296, 'XX00000296', 10, 3, 'Se analizan las causas que generan las mermas y deterioros en exceso a las normas técnicas; el importe de cada producto y el monto total de la afectación', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:18:08', NULL, 'XX'),
(297, 'XX00000297', 3, 3, 'Se efectúa la liquidación de las nóminas pagadas antes del término de cinco días y se reintegran al banco los salarios no pagados.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:18:09', NULL, 'XX'),
(298, 'XX00000298', 11, 3, 'Las cancelaciones por adeudos con el órgano estatal o el Presupuesto del Estado, que no son tramitadas oportunamente, cuentan con: el documento primario que genera el adeudo; el importe total; y la certificación del Ministerio de Finanzas y Precios o del órgano estatal que autorice la cancelación del adeudo.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:18:26', NULL, 'XX'),
(299, 'XX00000299', 4, 3, 'Se revisa, por el área de Contabilidad, que los salarios reintegrados coinciden con el importe reflejado en las nóminas en los espacios que aparecen no firmados, y si en el renglón no firmado por el trabajador se consigna la palabra REINTEGRO, el número y la fecha.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:18:37', NULL, 'XX'),
(300, 'XX00000300', 12, 3, 'Los errores contables de años anteriores, cuentan con la evidencia documental que sustente el error; el importe total; y el informe de causas y condiciones que fundamenten los errores detectados.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:18:57', NULL, 'XX'),
(301, 'XX00000301', 5, 3, 'Se aportan al Presupuesto del Estado los salarios no pagados al término de los 180 días.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:18:58', NULL, 'XX'),
(302, 'XX00000302', 13, 3, 'Cuando se detectan diferencias en los procesos de actualización o depuración de la contabilidad, se posee la evidencia documental que sustente el error; el importe total; y el informe de las causas y condiciones que fundamente los errores detectados.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:19:13', NULL, 'XX'),
(303, 'XX00000303', 6, 3, 'Si están establecidos controles eficientes para la forma de pago mediante tarjetas prepagadas y por la entrega del efectivo para pago y liquidación de nóminas.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:19:20', NULL, 'XX'),
(304, 'XX00000304', 14, 3, 'Las inspecciones realizadas aplican la norma establecida para realizar informes de faltantes y sobrantes.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:19:28', NULL, 'XX'),
(305, 'XX00000305', 7, 3, 'Se controla el número del cheque de extracción del efectivo para pago de la nómina, cuyo importe debe concordar con el total de los salarios, vacaciones y subsidios a pagar a los trabajadores.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:19:43', NULL, 'XX'),
(306, 'XX00000306', 15, 3, 'Las pérdidas ocasionadas por situaciones excepcionales, cubiertas o no por pólizas de seguro, tienen: el informe de las tasaciones certificadas por la entidad competente; y el importe total.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:19:49', NULL, 'XX'),
(307, 'XX00000307', 16, 3, 'En el caso de detectarse un presunto hecho delictivo, se procede a la realización de la denuncia policial teniendo en cuenta lo que establece la legislación vigente.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:20:08', NULL, 'XX'),
(308, 'XX00000308', 8, 3, 'Se consigna la fecha y número del reintegro en el caso de los salarios indebidos y no reclamados en el espacio Recibí Conforme.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:20:24', NULL, 'XX'),
(309, 'XX00000309', 17, 3, 'Los expedientes confeccionados por concepto de faltantes o sobrantes de bienes materiales deben contener toda la documentación y estar organizados de conformidad con la legislación vigente.', 1, 'XX00000001', 73, 'XX00000073', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:20:27', NULL, 'XX'),
(310, 'XX00000310', 9, 3, 'Se garantiza la actualización del modelo SC-4-08 Registro de salario y tiempo de servicio.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:20:49', NULL, 'XX'),
(311, 'XX00000311', 1, 3, 'Se cuenta con un Programa de Ahorro de Portadores Energético, donde existen medidas relacionadas con los combustibles y los lubricantes, y se analiza en el Consejo de Dirección el cumplimiento de ese programa, quedando evidencia en las actas levantadas.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:21:10', NULL, 'XX'),
(312, 'XX00000312', 10, 3, 'Se comprueba la actualización de los submayores de vacaciones y el cuadre de la suma de sus saldos con el de la cuenta control correspondiente, no debiendo acumularse tiempo en exceso al autorizado por el Ministerio de Trabajo y Seguridad Social.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:21:31', NULL, 'XX'),
(313, 'XX00000313', 2, 3, 'Todos los equipos poseen sus normas de consumo específicas y se analizan con periodicidad en el Consejo de Dirección y sobre la base de pruebas de consumo.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:21:37', NULL, 'XX'),
(314, 'XX00000314', 3, 3, 'Existe evidencia documental de las acciones de control y supervisión que realizan a la adquisición de combustibles.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:21:55', NULL, 'XX'),
(315, 'XX00000315', 11, 3, 'Se controlan según lo establecido en la legislación vigente los pagos por concepto de estipendio alimentario y de estimulación.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:22:11', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(316, 'XX00000316', 4, 3, 'Existe correspondencia entre los datos del modelo 5073-03 â€œBalance de consumo de portadores energéticos y los registros primarios de combustible y hay contrato firmado entre las partes en caso de haber equipos de transporte que no forman parte de los activos fijos tangibles de la entidad.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:22:24', NULL, 'XX'),
(317, 'XX00000317', 12, 3, 'En el diseño del sistema de pago por rendimiento aprobado anualmente por el jefe máximo de la entidad facultada, se han tenido en cuenta los aspectos señalados en la legislación vigente.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:22:41', NULL, 'XX'),
(318, 'XX00000318', 5, 3, 'Están establecidas normas de consumo para todos los equipos y la periodicidad en que se revisan, a fin de mantenerlas actualizadas, utilizándose éstas para el control del consumo de combustible a los medios de transporte.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:22:42', NULL, 'XX'),
(319, 'XX00000319', 6, 3, 'Hay correspondencia entre la información contenida en las Cartas Porte y las Hojas de Ruta, referido a: horario de recepción y entrega de las cargas, las distancias recorridas y el combustible consumido.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:22:57', NULL, 'XX'),
(320, 'XX00000320', 13, 3, 'Se certifican los indicadores y el pago de salario se realiza en correspondencia con los niveles de cumplimiento, no deteriorándose el gasto de salario por peso de valor agregado bruto planificado para el período.', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:23:13', NULL, 'XX'),
(321, 'XX00000321', 7, 3, 'En caso de que exista algún vehículo con el odómetro roto, se verifica que se esté trabajando con la Tabla de Distancia de recorridos.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:23:18', NULL, 'XX'),
(322, 'XX00000322', 8, 3, 'Se entrega combustible solamente a vehículos que se encuentren funcionando y las tarjetas magnéticas se custodian en la caja de seguridad o en el área que se decida por la entidad.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:23:33', NULL, 'XX'),
(323, 'XX00000323', 14, 3, 'Si se han efectuado pagos a los trabajadores sin respaldo productivo, se cuantifican los daños económicos ocasionados a la entidad. En el caso que proceda, analizar con profundad las causas y/o condiciones', 1, 'XX00000001', 68, 'XX00000068', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:23:37', NULL, 'XX'),
(324, 'XX00000324', 9, 3, 'En las entidades que excepcionalmente posean pipas legalizadas por la Comisión Provincial de Reordenamiento (o el Consejo Energético Provincial) se verificará la existencia de un documento para cada extracción de combustible, firmado y acuñado por el director con todos los requisitos que se especifican en la Instrucción No. 1/2010 del Ministerio de Economía y Planificación.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:23:53', NULL, 'XX'),
(325, 'XX00000325', 1, 3, 'Si las inversiones puestas en explotación, cuentan con el Certificado de habitable utilizable.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:23:56', NULL, 'XX'),
(326, 'XX00000326', 10, 3, 'Existen los procedimientos para la recepción y servicio del combustible físico.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:24:14', NULL, 'XX'),
(327, 'XX00000327', 2, 3, 'Si toda obra que se construya o se repare, que realizan entidades que no tengan en su objeto social autorización para estas operaciones, estén oficializadas mediante la licencia otorgada por el Registro de Constructores.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:24:25', NULL, 'XX'),
(328, 'XX00000328', 11, 3, 'Se realiza un arqueo de la caja para conocer las tarjetas que se encuentran guardadas y las que están en uso, comparándolas con el inventario del cajero y el reporte histórico emitido por Fincimex de las tarjetas activas de la entidad.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:24:31', NULL, 'XX'),
(329, 'XX00000329', 3, 3, 'Si toda la producción realizada según las certificaciones de avances de obra por el inversionista, fue registrada en la cuenta correspondiente.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:24:47', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(330, 'XX00000330', 12, 3, 'Existen documentos en la caja que muestran que al extraerse las tarjetas para ser cargadas estén definidos el número de las tarjetas, el tipo de combustible, las cantidades a cargar, los saldos iníciales que pueda tener la tarjeta cuando va a ser cargada, las firmas de quien entrega y recibe al extraerse y las firmas de quien entrega y recibe al ingresarse en la caja . (Documento de cuatro firmas)', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:24:51', NULL, 'XX'),
(331, 'XX00000331', 13, 3, 'Para el acto de entrega y liquidación de las tarjetas prepagadas a los usuarios para el consumo, existe un documento donde esté definido el traspaso de responsabilidad del usuario y el responsable de la custodia de las tarjetas. Debe estar definido el organismo y la entidad, el nombre del usuario, la chapa del vehículo, el tipo de combustible, el número de la tarjeta de combustible, el saldo al inicio en importe, el consumo en importe, el saldo final en importe, la firma de quien recibe y de quien entrega, la fecha de entrega y de liquidación, las cantidades autorizadas a consumir y las firma de quien recibe y entrega.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:25:10', NULL, 'XX'),
(332, 'XX00000332', 4, 3, 'La entidad posee el Plan de Inversiones para cada año, el cual forma parte del Plan de la Economía Nacional, a partir del cual se planifica el proceso inversionista, y cuenta con dos fases fundamentales: â€œPlan de preparación de las inversiones y â€œPlan de ejecución.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:25:12', NULL, 'XX'),
(333, 'XX00000333', 14, 3, 'Los comprobantes de consumo entregados por los servicentros deben contener la chapa del vehículo que servició y la firma del conductor como se exige (solamente puede ser un vehículo por comprobante), pudiendo exigir la entidad cualquier otro dato.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:25:26', NULL, 'XX'),
(334, 'XX00000334', 15, 3, 'Existe registro contable para cada una de las tarjetas en uso y que el mismo contenga todos los datos obligatorios establecidos por el Sistema Nacional de Contabilidad (SNC).Este registro no se puede encontrar en la caja o en poder de otra persona responsabilizada con la entrega y liquidación de las tarjetas, ya que no existe contrapartida en dicha transacción.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:25:44', NULL, 'XX'),
(335, 'XX00000335', 5, 3, 'Se cuenta con la Licencia de Obra y la Compatibilización con los intereses de la Defensa.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:25:56', NULL, 'XX'),
(336, 'XX00000336', 6, 3, 'Los sujetos principales de la inversión cumplen los preceptos relacionados en la legislación vigente.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:26:36', NULL, 'XX'),
(337, 'XX00000337', 16, 3, 'Hay documento que autorice la compensación y autorización del combustible a vehículos privados.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:26:56', NULL, 'XX'),
(338, 'XX00000338', 7, 3, 'La inversión objeto de revisión cumple con las indicaciones relacionadas con el plan de inversiones y está definido en aquellos casos que se encuentren en preparación, si es de las que están consideradas de mayor importancia desde el punto de vista económico, social y ambiental del país.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:27:01', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(339, 'XX00000339', 17, 3, 'Se controlan las Hojas de Ruta y se analiza el kilometraje.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:27:15', NULL, 'XX'),
(340, 'XX00000340', 8, 3, 'Los tipos de contrato que se emplean en el proceso inversionista son aquellos que se norman en la legislación vigente. No obstante, las partes pueden formalizar otros contratos dentro de los límites autorizados por las normas imperativas y el orden público.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:27:29', NULL, 'XX'),
(341, 'XX00000341', 18, 3, 'En los casos donde se utiliza Sistema de Posicionamiento Global (GPS) en los vehículos, comprobar la correspondencia con las Hojas de Ruta, la distancia recorrida, el combustible consumido y en los casos que proceda, verificar la carga física real con respecto a la reflejada en los documentos.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:27:32', NULL, 'XX'),
(342, 'XX00000342', 19, 3, 'Se entregan los comprobantes que avalen el consumo total de combustible al final de cada mes y estos están firmados al dorso por el chofer del vehículo y contiene el número de chapa del auto que fue serviciado.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:27:48', NULL, 'XX'),
(343, 'XX00000343', 9, 3, 'Se emplea como método de selección de la contraparte contractual el procedimiento negociado o la licitación. Excepcionalmente, y por decisión de una instancia superior al sujeto que interviene en la inversión, se puede emplear la adjudicación directa, siempre y cuando se conozca y asegure de forma comprobada la calidad técnica, el precio competitivo y la confiabilidad de un proveedor de productos o servicios reconocidos.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:27:54', NULL, 'XX'),
(344, 'XX00000344', 20, 3, 'Se archiva el REPORTE DE COMBUSTIBLE HABILITADO y KILÓMETROS RECORRIDOS por su número de orden consecutivo.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:28:05', NULL, 'XX'),
(345, 'XX00000345', 10, 3, 'La licitación puede ser abierta (cuando se invita a presentar ofertas a un número indeterminado de posibles oferentes) o restringida (cuando se convoca individualmente a determinadas personas para que presente ofertas).', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:28:22', NULL, 'XX'),
(346, 'XX00000346', 21, 3, 'En el libro de control de REPORTE DE COMBUSTIBLE HABILITADO y KILÓMETROS RECORRIDOS emitidos, se anota el número consecutivo, la fecha de entrega, el nombre de la persona a quien se le entrega y el número de chapa del vehículo administrativo.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:28:22', NULL, 'XX'),
(347, 'XX00000347', 22, 3, 'Existe un contrato que ampare la utilización del grupo electrógeno, el cual está firmado por la entidad montadora y la inversionista.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:28:41', NULL, 'XX'),
(348, 'XX00000348', 11, 3, 'Se elabora la documentación de inversiones por parte del inversionista y de los terceros que contrate, teniendo en cuenta la legislación vigente.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:28:50', NULL, 'XX'),
(349, 'XX00000349', 12, 3, 'Se elabora el estudio de factibilidad técnico â€“ económica según las normas establecidas por el Ministerio de Economía y Planificación, que reglamenta su alcance y contenido de acuerdo con las características de las inversiones.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:29:17', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES
(350, 'XX00000350', 23, 3, 'Se emite con frecuencia mensual un ejemplar único del REPORTE DE COMBUSTIBLE HABILITADO y KILÓMETROS RECORRIDOS para cada vehículo administrativo.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:29:26', NULL, 'XX'),
(351, 'XX00000351', 13, 3, 'En casos muy excepcionales, en dependencia de las características y poca complejidad de la inversión, el Ministerio de Economía y Planificación puede decidir que alguna inversión no nominal pueda no necesitar de estudios de factibilidad, por lo que se inscriben en el plan con los estudios previos.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:29:38', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(352, 'XX00000352', 24, 3, 'Los asientos en el REPORTE DE COMBUSTIBLE HABILITADO y KILÓMETROS RECORRIDOS se realizan con bolígrafo, en los espacios expresamente habilitados al efecto, por las personas autorizadas, quienes cuidarán que las anotaciones sean legibles, sin borrones ni tachaduras y que éstas respondan con exactitud a los datos reales, exigidos en los escaques correspondientes.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:29:43', NULL, 'XX'),
(353, 'XX00000353', 25, 3, 'Mensualmente por parte de la oficina, base o piquera se efectúan los análisis de los kilómetros recorridos, el combustible consumido, el índice de consumo y los mantenimientos realizados al vehículo administrativo durante el mes, comparándolos con los datos planificados y analizando las desviaciones que se produzcan en cada caso.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:30:02', NULL, 'XX'),
(354, 'XX00000354', 14, 3, 'El estudio de factibilidad técnico â€“ económica resume los principales aspectos técnicos, económicos, financieros y ambientales que caracterizan la inversión propuesta y que fundamentan la necesidad y viabilidad de su ejecución. Se basa en la documentación técnica a nivel de Ingeniería Básica.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:30:05', NULL, 'XX'),
(355, 'XX00000355', 26, 3, 'El libro de control para el registro de las personas autorizadas a habilitar combustible por vehículo administrativo, debe incluir el nombre de la persona, el cargo y la chapa del vehículo administrativo para el cual está autorizado.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:30:20', NULL, 'XX'),
(356, 'XX00000356', 15, 3, 'Cuentan con la evidencia de las consultas realizadas al Sistema de Planificación Física por los órganos, organismos o entidades que se encuentran relacionados con la pre-inversión.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:30:33', NULL, 'XX'),
(357, 'XX00000357', 27, 3, 'Los REPORTES DE COMBUSTIBLE HABILITADO y KILÓMETROS RECORRIDOS para cada uno de los vehículos administrativos de la entidad, tienen llenos los escaques desde el 1 hasta el 11 por el personal de transporte autorizado.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:30:36', NULL, 'XX'),
(358, 'XX00000358', 28, 3, 'Los datos referidos al kilometraje y combustible estimado en tanque de las casillas 5 y 6, coincidan con los datos del cierre del mes anterior que se muestran en las casillas 18 y 19.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:30:57', NULL, 'XX'),
(359, 'XX00000359', 29, 3, 'Las firmas en el REPORTE DE COMBUSTIBLE HABILITADO y KILÓMETROS RECORRIDOS coinciden con las declaradas en el registro de las personas autorizadas a habilitar combustible por vehículo administrativo.', 1, 'XX00000001', 72, 'XX00000072', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:31:14', NULL, 'XX'),
(360, 'XX00000360', 1, 3, 'Es preciso conciliar periódicamente los importes recibidos y pendientes de pago según controles contables, con los de los suministradores, dejando evidencia documental de esas conciliaciones.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:31:34', NULL, 'XX'),
(361, 'XX00000361', 16, 3, 'Se ejerce el control de autor por el proyectista en la fase de ejecución y el control y supervisión técnica por el inversionista, para garantizar el cumplimiento por parte del ejecutor de los requerimientos establecidos en la Ingeniería Básica y en el Proyecto ejecutivo.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:31:36', NULL, 'XX'),
(362, 'XX00000362', 2, 3, 'Se elaboran expedientes de pago por proveedores contentivos de cada factura, su correspondiente informe de recepción (cuando proceda) y el cheque o referencia del pago, cancelándose las facturas con el cuño de â€œPagado.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:31:52', NULL, 'XX'),
(363, 'XX00000363', 17, 3, 'El inversionista realiza las inspecciones técnicas en sus obras y en la ejecución de otros contratos que suscriban para la inversión.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:32:02', NULL, 'XX'),
(364, 'XX00000364', 3, 3, 'Es preciso mantener al día los submayores de Cuentas por Pagar a suministradores, los de Cuentas por Pagar Diversas y los de Cobros Anticipados y no presentar saldos envejecidos.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:32:12', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(365, 'XX00000365', 4, 3, 'Se liquidan en tiempo los préstamos bancarios recibidos.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:32:29', NULL, 'XX'),
(366, 'XX00000366', 18, 3, 'Se contrata, de ser necesario, para la ejecución de la inspección técnica, a un tercero ajeno al proceso inversionista.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:32:34', NULL, 'XX'),
(367, 'XX00000367', 5, 3, 'Las Cuentas por Pagar a proveedores, las diversas y los Cobros Anticipados deben desglosarse por cada factura recibida y cada pago efectuado; así como por edades y analizarse por el Consejo de Dirección.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:32:47', NULL, 'XX'),
(368, 'XX00000368', 19, 3, 'El supervisor técnico supervisa la realización de los trabajos de construcción y montaje en el grado necesario para verificar la realización de éstos y tiene la obligación de presentarse en la obra en los actos de entrega y recepción de los trabajos, la ejecución de las pruebas prescriptas y en el replanteo de las construcciones importantes e inspecciones de los elementos que van a ser cubiertos por otros, así como en otros eventos que se acuerden con el inversionista.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:33:05', NULL, 'XX'),
(369, 'XX00000369', 6, 3, 'Las devoluciones y reclamaciones efectuadas a suministradores deben controlarse para garantizar que los pagos se realicen por lo realmente recibido.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:33:06', NULL, 'XX'),
(370, 'XX00000370', 20, 3, 'Las características y el alcance del Control de Autor son acordadas entre el inversionista y el proyectista en el contrato, teniendo derecho a exigir al inversionista la paralización total o parcial de una obra cuando ésta a su juicio ofrezca peligro, así como cuando se ejecute con violación de las condiciones técnicas establecidas en la documentación técnica.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:33:29', NULL, 'XX'),
(371, 'XX00000371', 7, 3, 'Mensualmente debe verificarse que la suma de los saldos de todos los Submayores de las cuentas por pagar coincidan con los de las cuentas de control correspondientes (Incluyendo los de Depósitos Recibidos).', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:33:36', NULL, 'XX'),
(372, 'XX00000372', 21, 3, 'Se realizan revisiones periódicas del Cronograma de Ejecución de la inversión.', 1, 'XX00000001', 69, 'XX00000069', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:33:53', NULL, 'XX'),
(373, 'XX00000373', 8, 3, 'Los Efectos por Pagar deben registrarse correctamente, analizándose sus fechas de vencimiento para efectuar sus pagos correctamente.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:33:58', NULL, 'XX'),
(374, 'XX00000374', 9, 3, 'Se informa al banco trimestralmente, las cuentas por pagar en pesos cubanos vencidas, por más de seis meses.', 1, 'XX00000001', 71, 'XX00000071', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:34:16', NULL, 'XX'),
(375, 'XX00000375', 1, 3, 'Los modelos en blanco de facturas y órdenes de compras o de servicio deben controlarse en el área de contabilidad por persona ajena a la que los confecciona, estar prenumerados y controlarse por dicha área las numeraciones de los emitidos y de los no utilizados.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:34:49', NULL, 'XX'),
(376, 'XX00000376', 8, 3, 'Deben elaborarse expedientes por la cancelación de las Cuentas por Cobrar y aprobarse y registrarse correctamente.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:34:55', NULL, 'XX'),
(377, 'XX00000377', 9, 3, 'Las Cuentas por Cobrar a clientes, las diversas y los Pagos Anticipados tienen que analizarse por clientes, así como por cada factura y cobro realizado y por edades, así como ser analizados por el Consejo de Dirección.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:35:10', NULL, 'XX');
INSERT INTO tlista_requisitos (id, id_code, numero, componente, nombre, id_lista, id_lista_code, id_tipo_lista, id_tipo_lista_code, peso, inicio, fin, evidencia, indicacion, id_usuario, cronos, cronos_syn, situs) VALUES 
(378, 'XX00000378', 2, 3, 'Es preciso que exista separación de funciones entre el empleado del almacén que efectúe la entrega de productos o mercancías, el que confecciona la facturación y el que contabilice la operación, así como del que efectúe el cobro.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:35:12', NULL, 'XX'),
(379, 'XX00000379', 10, 3, 'La provisión para cuentas incobrables debe estar autorizada y operarse correctamente.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:35:33', NULL, 'XX'),
(380, 'XX00000380', 3, 3, 'Se concilian periódicamente las facturas emitidas y los cobros efectuados según los datos contables con los de los clientes, cuidando que se cumpla el principio de división de funciones.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:35:34', NULL, 'XX'),
(381, 'XX00000381', 11, 3, 'Debe existir un registro para el control de los efectos por cobrar.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:35:46', NULL, 'XX'),
(382, 'XX00000382', 4, 3, 'El registro contable de las facturas debe efectuarse en orden numérico, manteniéndose actualizados los submayores de los clientes y no presentar saldos envejecidos.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:35:57', NULL, 'XX'),
(383, 'XX00000383', 12, 3, 'Los efectos por cobrar pendientes y los descontados deben controlarse contablemente por sus vencimientos.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:36:02', NULL, 'XX'),
(384, 'XX00000384', 5, 3, 'El registro contable de las facturas debe efectuarse en orden numérico, manteniéndose actualizados los submayores de los clientes y no presentar saldos envejecidos.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:36:16', NULL, 'XX'),
(385, 'XX00000385', 13, 3, 'Los saldos mostrados en las cuentas por cobrar en litigio y en proceso judicial estén debidamente sustentados por la documentación establecida en la legislación vigente.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:36:18', NULL, 'XX'),
(386, 'XX00000386', 14, 3, 'Se habilitan expedientes de cobros por clientes contentivos de cada factura emitida y del cheque cobrado; así como de las reclamaciones aceptadas, debe mostrar por cada cliente o suministrador, según sea el caso, una relación de los documentos pendientes de cobro o de pago analizados por el rango de edades predefinido. En caso de que la fecha de la factura y la del plazo dado al cliente para su cobro coincidan, se toma la fecha de la factura, de no coincidir se toma la fecha del plazo al cliente.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:36:32', NULL, 'XX'),
(387, 'XX00000387', 6, 3, 'Se mantienen actualizados los submayores de Cuentas por Cobrar Diversas y no presentan saldos envejecidos.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:36:47', NULL, 'XX'),
(388, 'XX00000388', 7, 3, 'No deben existir saldos por pagos anticipados, fuera de los términos pactados para su liquidación y en caso de corresponder a importes que permanezcan en poder de los proveedores, trasladarlos a la cuenta de depósitos y fianzas.', 1, 'XX00000001', 70, 'XX00000070', 0, 2018, 2019, NULL, NULL, 12, '2018-10-06 16:37:10', NULL, 'XX');

/* Volcado de datos para la tabla ttipo_lista */

INSERT INTO ttipo_listas (id, id_code, nombre, numero, descripcion, id_lista, id_lista_code, componente, year, capitulo, subcapitulo, id_capitulo, id_capitulo_code, id_proceso, id_proceso_code, indice, cronos, cronos_syn, situs) VALUES 
(1, 'XX00000001', 'Planeación, planes de trabajo anual, mensual e individual', '1.1', NULL, 1, 'XX00000001', 1, 2018, 1, NULL, NULL, NULL, 1, 'XX00000001', 1001000, '2018-09-28 14:40:42', NULL, 'XX'),
(2, 'XX00000002', 'Integridad y valores éticos', '1.2', NULL, 1, 'XX00000001', 1, 2018, 2, NULL, NULL, NULL, 1, 'XX00000001', 1002000, '2018-09-28 14:40:51', NULL, 'XX'),
(3, 'XX00000003', 'Idoneidad demostrada', '1.3', NULL, 1, 'XX00000001', 1, 2018, 3, NULL, NULL, NULL, 1, 'XX00000001', 1003000, '2018-09-28 14:40:59', NULL, 'XX'),
(4, 'XX00000004', 'Estructura organizativa y asignación de autoridad y responsabilidad', '1.4', NULL, 1, 'XX00000001', 1, 2018, 4, NULL, NULL, NULL, 1, 'XX00000001', 1004000, '2018-09-28 14:41:05', NULL, 'XX'),
(6, 'XX00000006', 'Identificación del riesgo y detección del cambio', '2.1', NULL, 1, 'XX00000001', 2, 2018, 1, NULL, NULL, NULL, 1, 'XX00000001', 2001000, '2018-09-28 14:41:21', NULL, 'XX'),
(7, 'XX00000007', 'Determinación de los objetivos de control', '2.2', NULL, 1, 'XX00000001', 2, 2018, 2, NULL, NULL, NULL, 1, 'XX00000001', 2002000, '2018-09-28 14:41:28', NULL, 'XX'),
(8, 'XX00000008', 'Prevención de riesgos', '2.3', NULL, 1, 'XX00000001', 2, 2018, 3, NULL, NULL, NULL, 1, 'XX00000001', 2003000, '2018-09-28 14:41:36', NULL, 'XX'),
(9, 'XX00000009', 'Coordinación entre áreas, separación de tareas y responsabilidades y niveles de autorización', '3.1', NULL, 1, 'XX00000001', 3, 2018, 1, NULL, NULL, NULL, 1, 'XX00000001', 3001000, '2018-09-28 14:42:08', NULL, 'XX'),
(10, 'XX00000010', 'Documentación, registro oportuno y adecuado de las transacciones y hechos', '3.2', NULL, 1, 'XX00000001', 3, 2018, 2, NULL, NULL, NULL, 1, 'XX00000001', 3002000, '2018-09-28 14:42:16', NULL, 'XX'),
(25, 'XX00000025', 'Subsistema Efectivo en Caja', '3.3', NULL, 1, 'XX00000001', 3, 2018, 3, NULL, NULL, NULL, 1, 'XX00000001', 3003000, '2018-09-28 14:58:09', NULL, 'XX'),
(26, 'XX00000026', 'Subsistema Efectivo en Banco', '3.4', NULL, 1, 'XX00000001', 3, 2018, 4, NULL, NULL, NULL, 1, 'XX00000001', 3004000, '2018-09-28 14:58:25', NULL, 'XX'),
(27, 'XX00000027', 'Subsistema de Inventarios', '3.5', NULL, 1, 'XX00000001', 3, 2018, 5, NULL, NULL, NULL, 1, 'XX00000001', 3005000, '2018-09-28 14:58:58', NULL, 'XX'),
(28, 'XX00000028', 'Tiendas en divisas y Moneda nacional del Comercio y la Gastronomía.', '3.6', NULL, 1, 'XX00000001', 3, 2018, 6, NULL, NULL, NULL, 1, 'XX00000001', 3006000, '2018-09-28 14:59:10', NULL, 'XX'),
(29, 'XX00000029', 'Subsistema Activo Fijos', '3.7', NULL, 1, 'XX00000001', 3, 2018, 7, NULL, NULL, NULL, 1, 'XX00000001', 3007000, '2018-09-28 14:59:23', NULL, 'XX'),
(30, 'XX00000030', 'Subsistema Nómina', '3.8', NULL, 1, 'XX00000001', 3, 2018, 8, NULL, NULL, NULL, 1, 'XX00000001', 3008000, '2018-09-28 14:59:55', NULL, 'XX'),
(31, 'XX00000031', 'Inversiones', '3.9', NULL, 1, 'XX00000001', 3, 2018, 9, NULL, NULL, NULL, 1, 'XX00000001', 3009000, '2018-09-28 15:00:25', NULL, 'XX'),
(32, 'XX00000032', 'Subsistema Cuentas por cobrar', '3.10', NULL, 1, 'XX00000001', 3, 2018, 10, NULL, NULL, NULL, 1, 'XX00000001', 3010000, '2018-09-28 15:00:50', NULL, 'XX'),
(33, 'XX00000033', 'Subsistema Cuentas por Pagar', '3.11', NULL, 1, 'XX00000001', 3, 2018, 11, NULL, NULL, NULL, 1, 'XX00000001', 3011000, '2018-09-28 15:01:04', NULL, 'XX'),
(34, 'XX00000034', 'Combustible y Otros Portadores Energéticos', '3.12', NULL, 1, 'XX00000001', 3, 2018, 12, NULL, NULL, NULL, 1, 'XX00000001', 3012000, '2018-09-28 15:01:16', NULL, 'XX'),
(35, 'XX00000035', 'Faltantes, Pérdidas y Sobrantes', '3.13', NULL, 1, 'XX00000001', 3, 2018, 13, NULL, NULL, NULL, 1, 'XX00000001', 3013000, '2018-09-28 15:01:56', NULL, 'XX'),
(36, 'XX00000036', 'Subsistema de Costo', '3.14', NULL, 1, 'XX00000001', 3, 2018, 14, NULL, NULL, NULL, 1, 'XX00000001', 3014000, '2018-09-28 15:02:43', NULL, 'XX');
INSERT INTO ttipo_listas (id, id_code, nombre, numero, descripcion, id_lista, id_lista_code, componente, year, capitulo, subcapitulo, id_capitulo, id_capitulo_code, id_proceso, id_proceso_code, indice, cronos, cronos_syn, situs) VALUES 
(37, 'XX00000037', 'Acceso restringido a los recursos, activos y registros', '3.15', NULL, 1, 'XX00000001', 3, 2018, 15, NULL, NULL, NULL, 1, 'XX00000001', 3015000, '2018-09-28 15:02:55', NULL, 'XX'),
(38, 'XX00000038', 'Rotación del personal en las tareas claves', '3.16', NULL, 1, 'XX00000001', 3, 2018, 16, NULL, NULL, NULL, 1, 'XX00000001', 3016000, '2018-09-28 15:03:07', NULL, 'XX'),
(39, 'XX00000039', 'Control de las tecnologías de la información y las comunicaciones', '3.17', NULL, 1, 'XX00000001', 3, 2018, 17, NULL, NULL, NULL, 1, 'XX00000001', 3017000, '2018-09-28 15:03:16', NULL, 'XX'),
(40, 'XX00000040', 'Indicadores de rendimiento y de desempeño', '3.18', NULL, 1, 'XX00000001', 3, 2018, 18, NULL, NULL, NULL, 1, 'XX00000001', 3018000, '2018-09-28 15:03:34', NULL, 'XX'),
(41, 'XX00000041', 'Sistema de información, flujo y canales de comunicación', '4.1', NULL, 1, 'XX00000001', 4, 2018, 1, NULL, NULL, NULL, 1, 'XX00000001', 4001000, '2018-09-28 15:04:50', NULL, 'XX'),
(42, 'XX00000042', 'Contenido, calidad y responsabilidad', '4.2', NULL, 1, 'XX00000001', 4, 2018, 2, NULL, NULL, NULL, 1, 'XX00000001', 4002000, '2018-09-28 15:05:04', NULL, 'XX'),
(43, 'XX00000043', 'Rendición de cuentas', '4.3', NULL, 1, 'XX00000001', 4, 2018, 3, NULL, NULL, NULL, 1, 'XX00000001', 4003000, '2018-09-28 15:05:35', NULL, 'XX'),
(44, 'XX00000044', 'Evaluación y determinación de la eficacia del sistema de control interno', '5.1', NULL, 1, 'XX00000001', 5, 2018, 1, NULL, NULL, NULL, 1, 'XX00000001', 5001000, '2018-09-28 15:06:02', NULL, 'XX'),
(45, 'XX00000045', 'Comité de prevención y control', '5.2', NULL, 1, 'XX00000001', 5, 2018, 2, NULL, NULL, NULL, 1, 'XX00000001', 5002000, '2018-09-28 15:06:21', NULL, 'XX'),
(48, 'XX00000048', 'Sobre el sistema automatizado SIPAC (Sistema de planificación de actividades), según proceda su implementación:', '1.1.3', NULL, 1, 'XX00000001', 1, 2018, 1, 3, 1, 'XX00000001', 1, 'XX00000001', 1001030, '2018-09-28 15:15:44', NULL, 'XX'),
(49, 'XX00000049', 'Para la elaboración del plan anual de actividades, según corresponda, se debe tener en cuenta entre otros aspectos los siguientes:', '1.1.5', NULL, 1, 'XX00000001', 1, 2018, 1, 5, 1, 'XX00000001', 1, 'XX00000001', 1001050, '2018-09-28 15:15:56', NULL, 'XX'),
(50, 'XX00000050', 'Elaborado y aprobado por la máxima dirección', '1.4.29', NULL, 1, 'XX00000001', 1, 2018, 4, 29, 4, 'XX00000004', 1, 'XX00000001', 1004290, '2018-09-28 15:16:43', NULL, 'XX'),
(51, 'XX00000051', 'Se controla que exista una correcta contratación económica entre los principales suministradores y clientes, las cuales pueden ser entidades estatales y no estatales, sobre los productos y servicios que se definan por la entidad, y se logra el adecuado proceso de conciliación entre ellos; delimitando entre otros, los siguientes aspectos:', '1.4.33', NULL, 1, 'XX00000001', 1, 2018, 4, 33, 4, 'XX00000004', 1, 'XX00000001', 1004330, '2018-09-28 15:16:59', NULL, 'XX'),
(52, 'XX00000052', 'En caso de que los procesos de contratación de los productos y servicios se realicen con trabajadores por cuenta propia, u otra forma de gestión no estatal, se debe cumplir lo siguiente:', '1.4.34', NULL, 1, 'XX00000001', 1, 2018, 4, 34, 4, 'XX00000004', 1, 'XX00000001', 1004340, '2018-09-28 15:17:16', NULL, 'XX'),
(53, 'XX00000053', 'En los casos que proceda, se controla y exige la tramitación ágil y oportuna de las demandas judiciales para el reconocimiento de deudas entre las partes del contrato, considerando lo siguiente:', '1.4.35', NULL, 1, 'XX00000001', 1, 2018, 4, 35, 4, 'XX00000004', 1, 'XX00000001', 1004350, '2018-09-28 15:17:31', NULL, 'XX');
INSERT INTO ttipo_listas (id, id_code, nombre, numero, descripcion, id_lista, id_lista_code, componente, year, capitulo, subcapitulo, id_capitulo, id_capitulo_code, id_proceso, id_proceso_code, indice, cronos, cronos_syn, situs) VALUES 
(54, 'XX00000054', 'Se elaboran y controlan los diferentes tipos de contratos derivados de las relaciones de trabajo, monetarias y mercantiles, pactados con personas naturales y jurídicas, según lo establecido en las normas vigentes, teniendo en cuenta:', '1.4.36', NULL, 1, 'XX00000001', 1, 2018, 4, 36, 4, 'XX00000004', 1, 'XX00000001', 1004360, '2018-09-28 15:17:47', NULL, 'XX'),
(55, 'XX00000055', 'La estructura y organización de las áreas económicas y contable se corresponden con la misión de su organización y el volumen de las operaciones para garantizar los procesos de revisión sobre:', '1.4.43', NULL, 1, 'XX00000001', 1, 2018, 4, 43, 4, 'XX00000004', 1, 'XX00000001', 1004430, '2018-09-28 15:17:59', NULL, 'XX'),
(56, 'XX00000056', 'SISTEMA EMPRESARIAL', '1.5', NULL, 1, 'XX00000001', 1, 2018, 5, NULL, NULL, NULL, 1, 'XX00000001', 1005000, '2018-09-28 15:22:37', NULL, 'XX'),
(57, 'XX00000057', 'Políticas y prácticas en la gestión de los recursos humanos', '1.6', NULL, 1, 'XX00000001', 1, 2018, 6, NULL, NULL, NULL, 1, 'XX00000001', 1006000, '2018-09-28 15:22:47', NULL, 'XX'),
(58, 'XX00000058', 'Se cuenta con la documentación inherente a la razonabilidad de las cifras comprometidas y el cumplimiento del encargo estatal:', '1.5.45', NULL, 1, 'XX00000001', 1, 2018, 5, 45, 56, 'XX00000056', 1, 'XX00000001', 1005450, '2018-09-28 15:23:08', NULL, 'XX'),
(59, 'XX00000059', 'Para la evaluación de la propuesta de distribución de utilidades por parte del máximo órgano colegiado de dirección, la entidad entrega la documentación siguiente:', '1.5.54', NULL, 1, 'XX00000001', 1, 2018, 5, 54, 56, 'XX00000056', 1, 'XX00000001', 1005540, '2018-09-28 15:23:28', NULL, 'XX'),
(60, 'XX00000060', 'Relaciones de familiaridad:', '3.1.75', NULL, 1, 'XX00000001', 3, 2018, 1, 75, 9, 'XX00000009', 1, 'XX00000001', 3001750, '2018-09-28 15:25:28', NULL, 'XX'),
(61, 'XX00000061', 'El sistema costo implantado se corresponde con el tipo de actividad que realiza la entidad e incluye:', '3.2.81', NULL, 1, 'XX00000001', 3, 2018, 2, 81, 10, 'XX00000010', 1, 'XX00000001', 3002810, '2018-09-28 15:25:46', NULL, 'XX'),
(62, 'XX00000062', 'Utilizadas como herramientas de dirección los resultados obtenidos en el cálculo de las razones financieras con la situación real de la entidad considerando de proceder las siguientes:', '3.2.82', NULL, 1, 'XX00000001', 3, 2018, 2, 82, 10, 'XX00000010', 1, 'XX00000001', 3002820, '2018-09-28 15:26:03', NULL, 'XX'),
(63, 'XX00000063', 'Se comprueba la efectividad del control interno en el Subsistema Efectivo en Caja, teniendo en cuenta lo siguiente:', '3.2.83', NULL, 1, 'XX00000001', 3, 2018, 2, 83, 10, 'XX00000010', 1, 'XX00000001', 3002830, '2018-09-28 15:26:19', NULL, 'XX'),
(64, 'XX00000064', 'Se comprueba la efectividad del control interno en el Subsistema Efectivo en Banco, teniendo en cuenta lo siguiente:', '3.2.84', NULL, 1, 'XX00000001', 3, 2018, 2, 84, 10, 'XX00000010', 1, 'XX00000001', 3002840, '2018-09-28 15:26:36', NULL, 'XX'),
(65, 'XX00000065', 'Se comprueba la efectividad del control interno en el Subsistema de Inventarios y el cumplimiento de la política de gestión de inventarios establecida en el país, teniendo en cuenta lo siguiente:', '3.2.85', NULL, 1, 'XX00000001', 3, 2018, 2, 85, 10, 'XX00000010', 1, 'XX00000001', 3002850, '2018-09-28 15:26:52', NULL, 'XX'),
(66, 'XX00000066', 'Se comprueba la efectividad del control interno en tiendas en divisas y en moneda nacional del Comercio y la Gastronomía (debe cumplir además con los aspectos señalados en los subsistemas de efectivo en caja y de inventarios).', '3.2.89', NULL, 1, 'XX00000001', 3, 2018, 2, 89, 10, 'XX00000010', 1, 'XX00000001', 3002890, '2018-09-28 15:27:09', NULL, 'XX'),
(67, 'XX00000067', 'Se comprueba la efectividad del control interno en el Subsistema Activo Fijos, teniendo en cuenta lo siguiente:', '3.2.90', NULL, 1, 'XX00000001', 3, 2018, 2, 90, 10, 'XX00000010', 1, 'XX00000001', 3002900, '2018-09-28 15:27:24', NULL, 'XX'),
(68, 'XX00000068', 'Se comprueba la efectividad del control interno en el Subsistema Nómina, teniendo en cuentan lo siguiente:', '3.2.91', NULL, 1, 'XX00000001', 3, 2018, 2, 91, 10, 'XX00000010', 1, 'XX00000001', 3002910, '2018-09-28 15:27:43', NULL, 'XX'),
(69, 'XX00000069', 'Se comprueba la efectividad del control interno en el tema de Inversiones, teniendo en cuenta de proceder lo siguiente:', '3.2.92', NULL, 1, 'XX00000001', 3, 2018, 2, 92, 10, 'XX00000010', 1, 'XX00000001', 3002920, '2018-09-28 15:27:57', NULL, 'XX'),
(70, 'XX00000070', 'Se comprueba la efectividad del control interno en el Subsistema Cuentas por cobrar, teniendo en cuenta lo siguiente:', '3.2.93', NULL, 1, 'XX00000001', 3, 2018, 2, 93, 10, 'XX00000010', 1, 'XX00000001', 3002930, '2018-09-28 15:28:12', NULL, 'XX'),
(71, 'XX00000071', 'Se comprueba la efectividad del control interno en el Subsistema Cuentas por Pagar, teniendo en cuenta lo siguiente:', '3.2.94', NULL, 1, 'XX00000001', 3, 2018, 2, 94, 10, 'XX00000010', 1, 'XX00000001', 3002940, '2018-09-28 15:28:27', NULL, 'XX'),
(72, 'XX00000072', 'Se comprueba la efectividad del control interno en el tema Combustible y Otros Portadores Energéticos, considerando lo siguiente:', '3.2.95', NULL, 1, 'XX00000001', 3, 2018, 2, 95, 10, 'XX00000010', 1, 'XX00000001', 3002950, '2018-09-28 15:28:40', NULL, 'XX'),
(73, 'XX00000073', 'Se comprueba la efectividad del control interno en el tema Faltantes, Pérdidas y Sobrantes, teniendo en cuenta lo siguiente:', '3.2.96', NULL, 1, 'XX00000001', 3, 2018, 2, 96, 10, 'XX00000010', 1, 'XX00000001', 3002960, '2018-09-28 15:28:54', NULL, 'XX'),
(74, 'XX00000074', 'Se comprueba la efectividad del control interno en el Subsistema de Costo, teniendo en cuenta de proceder lo siguiente:', '3.2.97', NULL, 1, 'XX00000001', 3, 2018, 2, 97, 10, 'XX00000010', 1, 'XX00000001', 3002970, '2018-09-28 15:29:07', NULL, 'XX'),
(75, 'XX00000075', 'Comprobado el cumplimiento del Plan de Seguridad Informática, el cual contiene entre otros aspectos, procedimientos para:', '3.17.106', NULL, 1, 'XX00000001', 3, 2018, 17, 106, 39, 'XX00000039', 1, 'XX00000001', 3018060, '2018-09-28 15:30:33', NULL, 'XX');
INSERT INTO ttipo_listas (id, id_code, nombre, numero, descripcion, id_lista, id_lista_code, componente, year, capitulo, subcapitulo, id_capitulo, id_capitulo_code, id_proceso, id_proceso_code, indice, cronos, cronos_syn, situs) VALUES 
(76, 'XX00000076', 'Verificar que la entidad cuenta y cumple con el programa de comunicación institucional que defina el contenido informativo, su origen, destino y periodicidad, y posibilite en lo fundamental con su gestión:', '4.1.115', NULL, 1, 'XX00000001', 4, 2018, 1, 115, 41, 'XX00000041', 1, 'XX00000001', 4002150, '2018-09-28 15:31:19', NULL, 'XX'),
(77, 'XX00000077', 'Para el buen desarrollo de la rendición de cuentas se tienen en cuenta los siguientes aspectos:', '4.3.126', NULL, 1, 'XX00000001', 4, 2018, 3, 126, 43, 'XX00000043', 1, 'XX00000001', 4004260, '2018-09-28 15:31:53', NULL, 'XX'),
(78, 'XX00000078', 'Definidos los objetivos de trabajo de la entidad a mediano y largo plazo.', '1.1.1', NULL, 1, 'XX00000001', 1, 2018, 1, 1, 1, 'XX00000001', 1, 'XX00000001', 1001010, '2018-09-28 15:41:08', NULL, 'XX'),
(79, 'XX00000079', 'Cuentan con algún procedimiento, que permita identificar y analizar los riesgos generados por la actualización del modelo económico cubano, que traen consigo cambios jurídicos y estructurales tales como:', '2.1.63', NULL, 1, 'XX00000001', 2, 2018, 1, 63, 6, 'XX00000006', 1, 'XX00000001', 2001630, '2018-09-28 16:39:57', NULL, 'XX'),
(80, 'XX00000080', 'De existir riesgos financieros en su entidad, estos son administrados para la toma de decisiones, considerando lo siguiente:', '2.3.72', NULL, 1, 'XX00000001', 2, 2018, 3, 72, 8, 'XX00000008', 1, 'XX00000001', 2003720, '2018-09-28 16:40:22', NULL, 'XX'),
(81, 'XX00000081', 'Implementado un sistema para la gestión de la información que garantice:', '4.1.114', NULL, 1, 'XX00000001', 4, 2018, 1, 114, 41, 'XX00000041', 1, 'XX00000001', 4002140, '2018-10-06 15:22:38', NULL, 'XX'),
(82, 'XX00000082', 'Se realizan inspecciones sorpresivas para detectar entre otros aspectos:', '3.17.109', NULL, 1, 'XX00000001', 3, 2018, 17, 109, 39, 'XX00000039', 1, 'XX00000001', 3018090, '2018-10-06 15:49:22', NULL, 'XX');

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-10-11
/**********************************************/
/* paso 1 */
delete from tproceso_listas;
truncate table tproceso_listas;

INSERT INTO tproceso_listas (id, id_lista, id_lista_code, id_proceso, id_proceso_code, id_usuario, year, peso, cronos, cronos_syn, situs) VALUES 
(1, 1, 'XX00000001', 1, 'XX00000001', 1, 2018, 6, '2018-09-17 10:34:18', NULL, 'XX');

/**********************************************/
-- beginscript:2018-10-12
/**********************************************/
/* paso 2 */
UPDATE tlista_requisitos SET id_usuario = 1;
UPDATE tproceso_listas SET id_usuario = 1;

UPDATE tlistas, tprocesos SET tlistas.id_proceso_code= tprocesos.id_code where tlistas.id_proceso = tprocesos.id;
UPDATE tproceso_listas, tprocesos SET tproceso_listas.id_proceso_code= tprocesos.id_code where tproceso_listas.id_proceso = tprocesos.id;
UPDATE ttipo_listas, tprocesos SET ttipo_listas.id_proceso_code= tprocesos.id_code where ttipo_listas.id_proceso = tprocesos.id;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-10-18
/**********************************************/
/* paso 1*/
UPDATE  tarchivos SET tipo = NULL;
ALTER TABLE tarchivos CHANGE tipo tipo TINYINT(2) DEFAULT NULL;
ALTER TABLE tarchivos ADD COLUMN prioridad TINYINT(2) DEFAULT NULL;
ALTER TABLE tarchivos ADD COLUMN clase TINYINT(2) DEFAULT NULL;

/* paso 2 */
ALTER TABLE tusuarios ADD COLUMN nivel_archive2 BOOLEAN DEFAULT NULL;
ALTER TABLE tusuarios ADD COLUMN nivel_archive3 BOOLEAN DEFAULT NULL;

/* paso 3*/
ALTER TABLE tarchivos ADD COLUMN activo BOOLEAN DEFAULT true;
ALTER TABLE tpersonas ADD COLUMN activo BOOLEAN DEFAULT true;
ALTER TABLE tarchivo_personas ADD COLUMN activo BOOLEAN DEFAULT true;

/* paso 4 */
ALTER TABLE tindicador_criterio ADD COLUMN _dark FLOAT (5,2) NOT NULL DEFAULT 115.0 AFTER _blue;
ALTER TABLE tindicador_criterio CHANGE COLUMN _dark _dark FLOAT (5,2) NOT NULL DEFAULT 115.0;
UPDATE tindicador_criterio, tindicadores SET tindicador_criterio.id_indicador_code= tindicadores.id_code WHERE tindicador_criterio.id_indicador= tindicadores.id;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-10-30
/**********************************************/
/* paso 1*/
drop view if exists tmp_view_teventos;
create view tmp_view_teventos as select id, ifmeeting from teventos where id_evento is null and ifmeeting is not null;
update teventos, tmp_view_teventos set teventos.ifmeeting= tmp_view_teventos.ifmeeting where teventos.id_evento= tmp_view_teventos.id;
drop view tmp_view_teventos;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-10-31
/**********************************************/
ALTER TABLE tref_documentos ADD COLUMN id_requisito INT(11) DEFAULT NULL AFTER id_nota_code;
ALTER TABLE tref_documentos ADD COLUMN id_requisito_code CHAR(10) DEFAULT NULL AFTER id_requisito;

ALTER TABLE tref_documentos 
  ADD CONSTRAINT tref_documentos_fk6 FOREIGN KEY (id_requisito) REFERENCES tlista_requisitos (id) ON DELETE CASCADE ON UPDATE CASCADE;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-11-14
/**********************************************/
ALTER TABLE tseries DROP INDEX tseries_idx;
CREATE UNIQUE INDEX tseries_index ON tseries (serie, id_proceso_code, year);

ALTER TABLE tarchivo_personas DROP INDEX tarchivo_personas_idx;
CREATE UNIQUE INDEX tarchivo_personas_index ON tarchivo_personas (id_archivo_code, id_persona_code, if_sender);

ALTER TABLE tarchivo_personas DROP INDEX tarchivo_personas_idx1;
CREATE UNIQUE INDEX tarchivo_personas_index1 ON tarchivo_personas (id_archivo_code, id_usuario, id_grupo, if_sender);
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-11-23
/**********************************************/
/* paso 1 */
ALTER TABLE tusuario_eventos ADD COLUMN id_tematica INT(11) DEFAULT NULL AFTER id_auditoria_code;
ALTER TABLE tusuario_eventos ADD COLUMN id_tematica_code CHAR(10) DEFAULT NULL AFTER id_tematica;

ALTER TABLE tusuario_eventos 
  ADD CONSTRAINT tusuario_eventos_fk5 FOREIGN KEY (id_tematica) REFERENCES ttematicas (id) ON DELETE CASCADE ON UPDATE CASCADE;

/* paso 2 */
ALTER TABLE tasistencias ADD COLUMN id_code CHAR(10) DEFAULT NULL AFTER id;

ALTER TABLE tdebates ADD COLUMN id_asistencia INTEGER(11) DEFAULT NULL AFTER id_usuario;
ALTER TABLE tdebates ADD COLUMN id_asistencia_code CHAR(11) DEFAULT NULL AFTER id_asistencia;

ALTER TABLE tdebates 
  ADD CONSTRAINT tdebates_fk4 FOREIGN KEY (id_asistencia) REFERENCES tasistencias (id) ON DELETE RESTRICT ON UPDATE CASCADE;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-11-24
/**********************************************/
/* paso 1 */
create view view_tasistencias as 
select distinct tasistencias.id, tasistencias.id_code, tasistencias.id_evento, tasistencias.id_usuario, tdebates.id_tematica, 
tdebates.id_responsable from tasistencias, tdebates where tasistencias.id_usuario = tdebates.id_responsable;

/* paso 2 */
create view view_debates as
select view_tasistencias.* from view_tasistencias, ttematicas, tdebates where (view_tasistencias.id_evento = ttematicas.id_evento)
and (view_tasistencias.id_tematica = ttematicas.id and ttematicas.id = tdebates.id_tematica);

/* paso 3 */
update tdebates, view_debates 
set tdebates.id_asistencia = view_debates.id, tdebates.id_asistencia_code = view_debates.id_code 
where view_debates.id_tematica = tdebates.id_tematica and view_debates.id_usuario = tdebates.id_responsable;

/* paso 4 */
drop view view_debates;
drop view view_tasistencias;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-12-03
/**********************************************/
/* paso 1 */
ALTER TABLE tdebates DROP FOREIGN KEY tdebates_fk4;
ALTER TABLE tdebates DROP INDEX tdebates_fk4;

ALTER TABLE tdebates
  ADD CONSTRAINT tdebates_fk4 FOREIGN KEY (id_asistencia) REFERENCES tasistencias (id) ON DELETE RESTRICT ON UPDATE CASCADE;

 /* paso 2 */
ALTER TABLE tasistencias ADD COLUMN id_code CHAR(10) DEFAULT NULL AFTER id;
delete from tasistencias where cronos > '2018-11-23 00:00:00'and tasistencias.id not in (select id_asistencia from tdebates);
UPDATE tasistencias, teventos SET tasistencias.id_evento_code= teventos.id_code WHERE tasistencias.id_evento = teventos.id;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-12-14
/**********************************************/
/* paso 1 */
ALTER TABLE tdebates DROP FOREIGN KEY tdebates_fk4;
ALTER TABLE tdebates DROP INDEX tdebates_fk4;

ALTER TABLE tdebates
  ADD CONSTRAINT tdebates_fk4 FOREIGN KEY (id_asistencia) REFERENCES tasistencias (id) ON DELETE RESTRICT ON UPDATE CASCADE;

 /* paso 2 */
ALTER TABLE tasistencias ADD COLUMN id_code CHAR(10) DEFAULT NULL AFTER id;
UPDATE tasistencias, teventos SET tasistencias.id_evento_code= teventos.id_code WHERE tasistencias.id_evento = teventos.id;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-12-15
/**********************************************/
/* paso 1 */
ALTER TABLE tsubordinados ADD COLUMN cronos DATETIME DEFAULT NULL;
update tsubordinados, tusuarios set tsubordinados.cronos= tusuarios.cronos where id_responsable = tusuarios.id;

/* paso 2 */
ALTER TABLE tgrupos ADD COLUMN cronos DATETIME DEFAULT NULL;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-12-16
/**********************************************/
ALTER TABLE tusuario_grupos ADD COLUMN cronos DATETIME DEFAULT NULL;
update tusuario_grupos, tgrupos set tusuario_grupos.cronos= tgrupos.cronos where id_grupo = tgrupos.id;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-12-17
/**********************************************/
ALTER TABLE tsincronizacion ADD COLUMN date_cutoff DATETIME AFTER observacion;
ALTER TABLE tsincronizacion ADD COLUMN date_cutover DATETIME AFTER date_cutoff;
ALTER TABLE tsincronizacion ADD COLUMN cronos_cut DATETIME AFTER date_cutover;
ALTER TABLE tsincronizacion ADD COLUMN steep_current INT AFTER cronos_cut;
ALTER TABLE tsincronizacion ADD COLUMN finalized BOOLEAN AFTER steep_current;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2018-12-21
/**********************************************/
/* paso 1 */
update tsincronizacion set finalized= 1;
ALTER TABLE tsincronizacion CHANGE COLUMN finalized finalized BOOLEAN DEFAULT NULL;

/* paso 2 */
ALTER TABLE _config_synchro ADD COLUMN conectado BOOLEAN DEFAULT NULL;
update _config_synchro, tprocesos set _config_synchro.conectado= tprocesos.conectado where _config_synchro.id_proceso = tprocesos.id;

/* paso 3 */
update tasistencias, tusuarios set tasistencias.nombre= tusuarios.nombre, tasistencias.cargo= tusuarios.cargo 
where tasistencias.id_usuario = tusuarios.id;
update tusuario_eventos set indirect = NULL where indirect = 0;

/* paso 4 */
update treg_riesgo set reg_fecha = date(cronos) where year(reg_fecha) = 2019 and year(cronos) = 2018; 

update tproceso_eventos, teventos set tproceso_eventos.id_tarea = teventos.id_tarea, tproceso_eventos.id_tarea_code = teventos.id_tarea_code 
where tproceso_eventos.id_tarea is null and (tproceso_eventos.id_evento = teventos.id and teventos.id_tarea is not null);

/*************************************************************************/
-- endscript
/*************************************************************************/


/**********************************************/
-- beginscript:2019-02-03
/**********************************************/
/* paso 1 */
ALTER TABLE tunidades CHANGE COLUMN nombre nombre VARCHAR(80) DEFAULT NULL;

ALTER TABLE ttareas DROP INDEX tarea_nombre_index;

/* paso 2 */
ALTER TABLE teventos ADD COLUMN indice INTEGER(11) DEFAULT NULL;
ALTER TABLE teventos ADD COLUMN indice_plus INTEGER(11) DEFAULT NULL;
ALTER TABLE teventos ADD COLUMN tidx BOOLEAN DEFAULT NULL;

ALTER TABLE tauditorias ADD COLUMN indice INTEGER(11) DEFAULT NULL;
ALTER TABLE tauditorias ADD COLUMN indice_plus INTEGER(11) DEFAULT NULL;
ALTER TABLE tauditorias ADD COLUMN tidx BOOLEAN DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-02-05
/**********************************************/
/* paso 3 */
ALTER TABLE tusuarios ADD COLUMN id_proceso_jefe INTEGER(11) DEFAULT NULL;
UPDATE tusuarios, tprocesos SET tusuarios.id_proceso_jefe= tprocesos.id WHERE tusuarios.id = tprocesos.id_responsable;
ALTER TABLE tusuarios
  ADD CONSTRAINT tusuarios_fk1 FOREIGN KEY (id_proceso_jefe) REFERENCES tprocesos (id) ON DELETE SET NULL ON UPDATE CASCADE;
  
/* pso 4 */
CREATE VIEW view_usuario_proceso_grupos AS 
select distinct tusuario_grupos.id_usuario, tusuario_grupos.id_grupo, tusuario_procesos.id_proceso 
from tusuario_procesos, tusuario_grupos 
where tusuario_procesos.id_grupo is not null and tusuario_grupos.id_grupo = tusuario_procesos.id_grupo;

CREATE VIEW view_usuario_grupos AS 
select distinct tusuarios.*, id_grupo from tusuarios, tusuario_grupos 
where tusuarios.id = tusuario_grupos.id_usuario;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-03-15
/**********************************************/
ALTER TABLE tusuarios ADD COLUMN nivel_archive4 BOOLEAN DEFAULT NULL AFTER nivel_archive3;
UPDATE teventos, tprocesos SET teventos.id_proceso_code = tprocesos.id_code where teventos.id_proceso = tprocesos.id;

update teventos set toshow = null where id not in (select distinct id_evento from treg_evento);
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-03-25
/**********************************************/
drop table treg_evento;
drop table tusuario_eventos;
drop table tproceso_eventos;

delete from tsincronizacion where cronos >= '2018-12-01 00:00'; 
delete from tsystem where cronos >= '2018-12-01 00:00' and action like '%Lote%'; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-03-27
/**********************************************/
/* paso 1 */
CREATE TABLE torganismos (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) DEFAULT NULL,
  nombre char(180) DEFAULT NULL,
  codigo varchar(10) DEFAULT NULL,
  descripcion text DEFAULT NULL,
  activo tinyint(1) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY index_organismo_nombre (nombre),
  UNIQUE KEY index_organismo_codigo (codigo)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

/* paso 2 */
INSERT INTO torganismos (id, id_code, nombre, codigo, descripcion, activo, cronos, cronos_syn, situs) VALUES 
(1, 'XX00000001', 'Ministerio de las Comunicaciones', 'MINCONS', NULL, 1, '2019-03-31 12:15:26', NULL, 'XX'),
(2, 'XX00000002', 'Ministerio de Economia y Planificación', 'MEP', NULL, 1, '2019-03-31 12:21:11', NULL, 'XX'),
(3, 'XX00000003', 'Ministerio de Agricultura', 'MINAGRI', NULL, 1, '2019-03-31 12:25:55', NULL, 'XX'),
(4, 'XX00000004', 'Ministerio de Ciencia, Tecnología y Medio Ambiente', 'CITMA', NULL, 1, '2019-03-31 12:28:39', NULL, 'XX'),
(5, 'XX00000005', 'Ministerio de Comercio Exterior y la Inversión Extranjera', 'MINCEX', NULL, 1, '2019-03-31 12:33:41', NULL, 'XX'),
(6, 'XX00000006', 'Ministerio de Comercio Interior', 'MINCIN', NULL, 1, '2019-03-31 12:34:56', NULL, 'XX'),
(7, 'XX00000007', 'Ministerio de la Construcción', 'MCONS', NULL, 1, '2019-03-31 12:35:16', NULL, 'XX'),
(8, 'XX00000008', 'Ministerio de Educación', 'MINED', NULL, 1, '2019-03-31 12:35:41', NULL, 'XX'),
(9, 'XX00000009', 'Ministerio de Educación Superior', 'MES', NULL, 1, '2019-03-31 12:35:53', NULL, 'XX'),
(10, 'XX00000010', 'Ministerio de Energía y Minas', 'MEM', NULL, 1, '2019-03-31 12:36:05', NULL, 'XX'),
(11, 'XX00000011', 'Ministerio de Finanzas y Precios', 'MFP', NULL, 1, '2019-03-31 12:36:18', NULL, 'XX'),
(12, 'XX00000012', 'Ministerio de las Fuerzas Armadas Revolucionarias', 'MINFAR', NULL, 1, '2019-03-31 12:36:34', NULL, 'XX'),
(13, 'XX00000013', 'Ministerio de Industrias', 'MINDUS', NULL, 1, '2019-03-31 12:36:48', NULL, 'XX'),
(14, 'XX00000014', 'Ministerio de la Industria Alimentaria', 'MINAL', NULL, 1, '2019-03-31 12:37:06', NULL, 'XX'),
(15, 'XX00000015', 'Ministerio del Interior', 'MININ', NULL, 1, '2019-03-31 12:37:32', NULL, 'XX'),
(16, 'XX00000016', 'Ministerio del Interiorde Justicia', 'MINJUS', NULL, 1, '2019-03-31 12:37:48', NULL, 'XX'),
(17, 'XX00000017', 'Ministerio de Relaciones Exteriores', 'MINREX', NULL, 1, '2019-03-31 12:38:04', NULL, 'XX'),
(18, 'XX00000018', 'Ministerio de Salud Pública', 'MINSAP', NULL, 1, '2019-03-31 12:38:21', NULL, 'XX'),
(19, 'XX00000019', 'Ministerio de Trabajo y Seguridad Social', 'MTSS', NULL, 1, '2019-03-31 12:38:32', NULL, 'XX'),
(20, 'XX00000020', 'Ministerio de Transporte', 'MITRANS', NULL, 1, '2019-03-31 12:38:44', NULL, 'XX'),
(21, 'XX00000021', 'Ministerio de Turismo', 'MINTUR', NULL, 1, '2019-03-31 12:38:56', NULL, 'XX'),
(22, 'XX00000022', 'Instituto Cubano de Radio y Televisión', 'ICRT', NULL, 1, '2019-03-31 12:39:06', NULL, 'XX'),
(23, 'XX00000023', 'Instituto Nacional de Deportes,Educación Física y Recreación', 'INDER', NULL, 1, '2019-03-31 12:39:17', NULL, 'XX'),
(24, 'XX00000024', 'Instituto Nacional de Recursos Hidráulicos', 'INRH', NULL, 1, '2019-03-31 12:39:28', NULL, 'XX'),
(25, 'XX00000025', 'Banco Central de Cuba', 'BCC', NULL, 1, '2019-03-31 12:39:38', NULL, 'XX');

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-03-29
/**********************************************/
/* paso 1 */
ALTER TABLE tpersonas ADD COLUMN id_organismo INT(11) DEFAULT NULL AFTER organismo;
ALTER TABLE tpersonas ADD COLUMN id_organismo_code CHAR(10) DEFAULT NULL AFTER id_organismo;

update tpersonas, torganismos set id_organismo = torganismos.id, id_organismo_code = torganismos.id_code 
where CONVERT(tpersonas.organismo USING utf8) = CONVERT(torganismos.codigo USING utf8);

ALTER TABLE tpersonas DROP COLUMN organismo;

/* paso 2 */
ALTER TABLE tproceso_eventos_2017 ADD COLUMN origen_data TEXT DEFAULT NULL AFTER id_responsable;
ALTER TABLE tproceso_eventos_2018 ADD COLUMN origen_data TEXT DEFAULT NULL AFTER id_responsable;
ALTER TABLE tproceso_eventos_2019 ADD COLUMN origen_data TEXT DEFAULT NULL AFTER id_responsable;
ALTER TABLE tproceso_eventos_2020 ADD COLUMN origen_data TEXT DEFAULT NULL AFTER id_responsable;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-04-02
/**********************************************/

INSERT INTO torganismos (id, id_code, nombre, codigo, descripcion, activo, cronos, cronos_syn, situs) VALUES 
(1, 'XX00000001', 'Ministerio de las Comunicaciones', 'MINCONS', NULL, 1, '2019-03-31 12:15:26', NULL, 'XX'),
(2, 'XX00000002', 'Ministerio de Economia y Planificación', 'MEP', NULL, 1, '2019-03-31 12:21:11', NULL, 'XX'),
(3, 'XX00000003', 'Ministerio de Agricultura', 'MINAGRI', NULL, 1, '2019-03-31 12:25:55', NULL, 'XX'),
(4, 'XX00000004', 'Ministerio de Ciencia, Tecnología y Medio Ambiente', 'CITMA', NULL, 1, '2019-03-31 12:28:39', NULL, 'XX'),
(5, 'XX00000005', 'Ministerio de Comercio Exterior y la Inversión Extranjera', 'MINCEX', NULL, 1, '2019-03-31 12:33:41', NULL, 'XX'),
(6, 'XX00000006', 'Ministerio de Comercio Interior', 'MINCIN', NULL, 1, '2019-03-31 12:34:56', NULL, 'XX'),
(7, 'XX00000007', 'Ministerio de la Construcción', 'MCONS', NULL, 1, '2019-03-31 12:35:16', NULL, 'XX'),
(8, 'XX00000008', 'Ministerio de Educación', 'MINED', NULL, 1, '2019-03-31 12:35:41', NULL, 'XX'),
(9, 'XX00000009', 'Ministerio de Educación Superior', 'MES', NULL, 1, '2019-03-31 12:35:53', NULL, 'XX'),
(10, 'XX00000010', 'Ministerio de Energía y Minas', 'MEM', NULL, 1, '2019-03-31 12:36:05', NULL, 'XX'),
(11, 'XX00000011', 'Ministerio de Finanzas y Precios', 'MFP', NULL, 1, '2019-03-31 12:36:18', NULL, 'XX'),
(12, 'XX00000012', 'Ministerio de las Fuerzas Armadas Revolucionarias', 'MINFAR', NULL, 1, '2019-03-31 12:36:34', NULL, 'XX'),
(13, 'XX00000013', 'Ministerio de Industrias', 'MINDUS', NULL, 1, '2019-03-31 12:36:48', NULL, 'XX'),
(14, 'XX00000014', 'Ministerio de la Industria Alimentaria', 'MINAL', NULL, 1, '2019-03-31 12:37:06', NULL, 'XX'),
(15, 'XX00000015', 'Ministerio del Interior', 'MININ', NULL, 1, '2019-03-31 12:37:32', NULL, 'XX'),
(16, 'XX00000016', 'Ministerio del Interiorde Justicia', 'MINJUS', NULL, 1, '2019-03-31 12:37:48', NULL, 'XX'),
(17, 'XX00000017', 'Ministerio de Relaciones Exteriores', 'MINREX', NULL, 1, '2019-03-31 12:38:04', NULL, 'XX'),
(18, 'XX00000018', 'Ministerio de Salud Pública', 'MINSAP', NULL, 1, '2019-03-31 12:38:21', NULL, 'XX'),
(19, 'XX00000019', 'Ministerio de Trabajo y Seguridad Social', 'MTSS', NULL, 1, '2019-03-31 12:38:32', NULL, 'XX'),
(20, 'XX00000020', 'Ministerio de Transporte', 'MITRANS', NULL, 1, '2019-03-31 12:38:44', NULL, 'XX'),
(21, 'XX00000021', 'Ministerio de Turismo', 'MINTUR', NULL, 1, '2019-03-31 12:38:56', NULL, 'XX'),
(22, 'XX00000022', 'Instituto Cubano de Radio y Televisión', 'ICRT', NULL, 1, '2019-03-31 12:39:06', NULL, 'XX'),
(23, 'XX00000023', 'Instituto Nacional de Deportes,Educación Física y Recreación', 'INDER', NULL, 1, '2019-03-31 12:39:17', NULL, 'XX'),
(24, 'XX00000024', 'Instituto Nacional de Recursos Hidráulicos', 'INRH', NULL, 1, '2019-03-31 12:39:28', NULL, 'XX'),
(25, 'XX00000025', 'Banco Central de Cuba', 'BCC', NULL, 1, '2019-03-31 12:39:38', NULL, 'XX');

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-04-04
/**********************************************/
ALTER TABLE tproceso_eventos_2020 ADD COLUMN origen_data TEXT DEFAULT NULL AFTER id_responsable;
ALTER TABLE tsincronizacion ADD COLUMN mcrypt BOOLEAN DEFAULT NULL;
delete from tsincronizacion where cronos > '2019-01-01 00:00:00';
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-04-06
/**********************************************/
alter table ttipo_eventos change id_code id_code char(10) after id;
alter table ttipo_eventos change id_subcapitulo_code id_subcapitulo_code char(10) after id_subcapitulo;
alter table ttipo_eventos change cronos cronos datetime after id_proceso_code;
alter table ttipo_eventos change cronos_syn cronos_syn datetime after cronos;
alter table ttipo_eventos change situs situs char(2) after cronos_syn; 

update ttipo_eventos set id_subcapitulo_code= null where id_subcapitulo is null;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-04-15
/**********************************************/
alter table tnotas drop column fecha_fin_real;
alter table tnotas drop column estado;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-04-26
/**********************************************/
/* paso 1 */
alter table tnotas change id_usuario id_usuario integer(11) default null;

/* paso 2 */
update teventos, tprocesos set teventos.id_proceso = tprocesos.id 
where convert (teventos.id_proceso_code using utf8) = convert (tprocesos.id_code using utf8);

update tproceso_eventos_2019, tprocesos set tproceso_eventos_2019.id_proceso = tprocesos.id 
where convert (tproceso_eventos_2019.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tproceso_eventos_2018, tprocesos set tproceso_eventos_2018.id_proceso = tprocesos.id 
where convert (tproceso_eventos_2018.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tproceso_eventos_2020, tprocesos set tproceso_eventos_2020.id_proceso = tprocesos.id 
where convert (tproceso_eventos_2020.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tasistencias, tprocesos set tasistencias.id_proceso = tprocesos.id 
where convert (tasistencias.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tauditorias, tprocesos set tauditorias.id_proceso = tprocesos.id 
where convert (tauditorias.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tdebates, tprocesos set tdebates.id_proceso = tprocesos.id 
where convert (tdebates.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tdocumentos, tprocesos set tdocumentos.id_proceso = tprocesos.id 
where convert (tdocumentos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tescenarios, tprocesos set tescenarios.id_proceso = tprocesos.id 
where convert (tescenarios.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tindicadores, tprocesos set tindicadores.id_proceso = tprocesos.id 
where convert (tindicadores.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tindicador_criterio, tprocesos set tindicador_criterio.id_proceso = tprocesos.id 
where convert (tindicador_criterio.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tinductores, tprocesos set tinductores.id_proceso = tprocesos.id 
where convert (tinductores.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tnotas, tprocesos set tnotas.id_proceso = tprocesos.id 
where convert (tnotas.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tobjetivos, tprocesos set tobjetivos.id_proceso = tprocesos.id 
where convert (tobjetivos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tperspectivas, tprocesos set tperspectivas.id_proceso = tprocesos.id 
where convert (tperspectivas.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tplanes, tprocesos set tplanes.id_proceso = tprocesos.id 
where convert (tplanes.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tpolitica_objetivos, tprocesos set tpolitica_objetivos.id_proceso = tprocesos.id 
where convert (tpolitica_objetivos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tproceso_criterio, tprocesos set tproceso_criterio.id_proceso = tprocesos.id 
where convert (tproceso_criterio.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tproceso_indicadores, tprocesos set tproceso_indicadores.id_proceso = tprocesos.id 
where convert (tproceso_indicadores.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tproceso_objetivos, tprocesos set tproceso_objetivos.id_proceso = tprocesos.id 
where convert (tproceso_objetivos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tproceso_riesgos, tprocesos set tproceso_riesgos.id_proceso = tprocesos.id 
where convert (tproceso_riesgos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tprogramas, tprocesos set tprogramas.id_proceso = tprocesos.id 
where convert (tprogramas.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update treg_objetivo, tprocesos set treg_objetivo.id_proceso = tprocesos.id 
where convert (treg_objetivo.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update treg_perspectiva, tprocesos set treg_perspectiva.id_proceso = tprocesos.id 
where convert (treg_perspectiva.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update treg_plantrab, tprocesos set treg_plantrab.id_proceso = tprocesos.id 
where convert (treg_plantrab.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update treg_proceso, tprocesos set treg_proceso.id_proceso = tprocesos.id 
where convert (treg_proceso.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update triesgos, tprocesos set triesgos.id_proceso = tprocesos.id 
where convert (triesgos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update treg_riesgo, tprocesos set treg_riesgo.id_proceso = tprocesos.id 
where convert (treg_riesgo.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update ttareas, tprocesos set ttareas.id_proceso = tprocesos.id 
where convert (ttareas.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update ttematicas, tprocesos set ttematicas.id_proceso = tprocesos.id 
where convert (ttematicas.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update ttipo_eventos, tprocesos set ttipo_eventos.id_proceso = tprocesos.id 
where convert (ttipo_eventos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tusuarios, tprocesos set tusuarios.id_proceso = tprocesos.id 
where convert (tusuarios.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

update tusuario_procesos, tprocesos set tusuario_procesos.id_proceso = tprocesos.id 
where convert (tusuario_procesos.id_proceso_code using utf8) = convert(tprocesos.id_code using utf8);

/* paso 3 */
drop table treg_evento_0;
drop table tproceso_eventos_0;
drop table tusuario_eventos_0;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-04-29
/**********************************************/
ALTER TABLE treg_evento_2017 ADD COLUMN hide_synchro BOOLEAN DEFAULT NULL AFTER user_check;
ALTER TABLE treg_evento_2018 ADD COLUMN hide_synchro BOOLEAN DEFAULT NULL AFTER user_check;
ALTER TABLE treg_evento_2019 ADD COLUMN hide_synchro BOOLEAN DEFAULT NULL AFTER user_check;
ALTER TABLE treg_evento_2020 ADD COLUMN hide_synchro BOOLEAN DEFAULT NULL AFTER user_check;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-05-04
/**********************************************/
ALTER TABLE tobjetivos CHANGE COLUMN estrategia estrategia LONGTEXT DEFAULT NULL; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-05-17
/**********************************************/
ALTER TABLE tplanes ADD COLUMN if_numering TINYINT(2) DEFAULT NULL AFTER efectivas; 
ALTER TABLE tauditorias CHANGE COLUMN lugar lugar TEXT DEFAULT NULL;

ALTER TABLE tdeletes ADD COLUMN cronos_syn DATETIME DEFAULT NULL;
ALTER TABLE tdeletes CHANGE COLUMN observacion observacion TEXT DEFAULT NULL AFTER id_usuario;
ALTER TABLE tdeletes ADD COLUMN origen_data TEXT DEFAULT NULL AFTER id_usuario;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-06-01
/**********************************************/
/* paso1 */
ALTER TABLE tdeletes ADD COLUMN cronos_syn DATETIME DEFAULT NULL AFTER cronos;

/* paso2 */
update tdeletes set campo1= 'id_code' where tabla = 'teventos' and campo1 = 'id_evento_code' and campo2 is null;
update tdeletes set campo1= 'id_code' where tabla = 'ttareas' and campo1 = 'id_tarea_code' and campo2 is null;
update tdeletes set campo1= 'id_code' where tabla = 'tauditorias' and campo1 = 'id_auditoria_code' and campo2 is null;
update tdeletes set campo1= 'id_code' where tabla = 'ttematicas' and campo1 = 'id_tematica_code' and campo2 is null;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-07-16
/**********************************************/
ALTER TABLE tsincronizacion ADD COLUMN tb_filter varchar(120) DEFAULT NULL;
update tdeletes set tabla= 'tproceso_riesgos' where tabla = 'tproceso_notas';
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-07-17
/**********************************************/
/* paso 1*/
ALTER TABLE tsincronizacion ADD COLUMN tb_filter varchar(120) DEFAULT NULL;
update tdeletes set campo1= 'id_code' where tabla= 'teventos' and campo1 = 'id_evento_code' and campo2 is null and campo3 is null;

/* paso 2 */
delete from tdeletes where (campo1 is not null and valor1 is null) or (campo2 is not null and valor2 is null) or (campo3 is not null and valor3 is null);

/* paso 3 */
/*
-- update treg_evento_2019, teventos set treg_evento_2019.id_evento= teventos.id where convert(teventos.id_code using utf8) = convert(treg_evento_2019.id_evento_code using utf8);
*/
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-08-06
/**********************************************/

update tarchivos set clase=clase+1;

ALTER TABLE tusuarios CHANGE COLUMN nombre nombre VARCHAR(80) DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-08-09
/**********************************************/
alter table treg_plan add column calcular tinyint(1) default false after observacion;
alter table treg_real add column calcular tinyint(1) default false after observacion;
alter table treg_real change column reg_date reg_date datetime after calcular;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-10-07
/**********************************************/

update ttematicas, treg_evento_2019 set ttematicas.id_responsable_eval = treg_evento_2019.id_responsable, ttematicas.evaluado = treg_evento_2019.cronos, ttematicas.evaluacion= treg_evento_2019.observacion where id_evento_accords is not null and (id_evento_accords = treg_evento_2019.id_evento and ttematicas.id_responsable = treg_evento_2019.id_usuario);
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-10-28
/**********************************************/
/* paso 1*/
update ttematicas set evaluado= fecha_inicio_plan where year(evaluado) = 1969;
update ttematicas set fecha_inicio_plan= cronos where year(fecha_inicio_plan) = 1969;
update ttematicas set evaluado= fecha_inicio_plan where year(evaluado) = 1969;
update teventos set fecha_inicio_plan= cronos where year(fecha_inicio_plan) = 1969;
update teventos set fecha_fin_plan= cronos where year(fecha_fin_plan) = 1969;

/* paso 2*/
update tdeletes set tabla= 'tusuario_eventos' where tabla = 'usuario_eventos';
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-11-06
/**********************************************/

/* paso 1*/
ALTER TABLE tref_documentos ADD COLUMN id_indicador INTEGER(11) DEFAULT NULL AFTER id_requisito_code;
ALTER TABLE tref_documentos ADD COLUMN id_indicador_code CHAR(11) DEFAULT NULL AFTER id_indicador;

ALTER TABLE tref_documentos
  ADD CONSTRAINT tref_documentos_fk7 FOREIGN KEY (id_indicador) REFERENCES tindicadores (id) ON DELETE RESTRICT ON UPDATE CASCADE;
  
/* paso 2 */
ALTER TABLE tdocumentos ADD COLUMN year MEDIUMINT DEFAULT NULL AFTER id_archivo_code;
ALTER TABLE tdocumentos ADD COLUMN month SMALLINT DEFAULT NULL AFTER year;

update tdocumentos set year= year(cronos) where year is null;
  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-11-19
/**********************************************/
ALTER TABLE tdeletes CHANGE COLUMN id_usuario id_responsable INTEGER(11) DEFAULT NULL;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-11-27
/**********************************************/

ALTER TABLE tinductor_eventos DROP COLUMN id_escenario;
ALTER TABLE tinductor_eventos DROP INDEX id_evento_index;
ALTER TABLE tinductor_eventos DROP COLUMN id_escenario_code;
CREATE UNIQUE INDEX id_evento_index ON tinductor_eventos (id_inductor_code, id_evento_code);

ALTER TABLE tinductor_riesgos DROP COLUMN id_escenario;
ALTER TABLE tinductor_riesgos DROP INDEX id_riesgo_index;
ALTER TABLE tinductor_riesgos DROP COLUMN id_escenario_code;
CREATE UNIQUE INDEX id_riesgo_index ON tinductor_riesgos (id_inductor_code, id_riesgo_code);

ALTER TABLE tinductores DROP COLUMN id_escenario;
ALTER TABLE tinductores DROP COLUMN id_escenario_code;

ALTER TABLE tobjetivo_inductores DROP COLUMN id_escenario;
ALTER TABLE tobjetivo_inductores DROP INDEX id_objetivo_inductor_index;
ALTER TABLE tobjetivo_inductores DROP COLUMN id_escenario_code;
CREATE UNIQUE INDEX id_objetivo_inductor_index ON tobjetivo_inductores (id_inductor_code, id_objetivo_code, year);

ALTER TABLE tobjetivos DROP FOREIGN KEY tobjetivos_fk1;
ALTER TABLE tobjetivos DROP COLUMN id_escenario;
ALTER TABLE tobjetivos DROP COLUMN id_escenario_code;

ALTER TABLE tperspectivas DROP FOREIGN KEY tperspectivas_fk;
ALTER TABLE tperspectivas DROP COLUMN id_escenario;
ALTER TABLE tperspectivas DROP INDEX perspectiva_proceso_index;
ALTER TABLE tperspectivas DROP COLUMN id_escenario_code;
CREATE UNIQUE INDEX perspectiva_proceso_index ON tperspectivas (nombre, id_proceso_code);

ALTER TABLE tproceso_indicadores DROP COLUMN id_escenario;
ALTER TABLE tproceso_indicadores DROP INDEX indicador_proceso_year_index;
ALTER TABLE tproceso_indicadores DROP COLUMN id_escenario_code;
CREATE UNIQUE INDEX indicador_proceso_year_index ON tproceso_indicadores (id_indicador_code, id_proceso_code, year);

ALTER TABLE tprogramas DROP COLUMN id_escenario;
ALTER TABLE tprogramas DROP COLUMN id_escenario_code;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-11-29
/**********************************************/

ALTER TABLE ttematicas DROP FOREIGN KEY ttematicas_fk1;
ALTER TABLE ttematicas CHANGE COLUMN id_responsable id_asistencia_resp INTEGER(11) DEFAULT NULL AFTER observacion;
ALTER TABLE ttematicas ADD COLUMN id_asistencia_resp_code CHAR(10) DEFAULT NULL AFTER id_asistencia_resp;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-12-20
/**********************************************/

/*paso 1 */
ALTER TABLE tsincronizacion ADD COLUMN date_tdeletes DATETIME;

ALTER TABLE tsincronizacion ADD COLUMN date_treg_tarea DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_treg_evento DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_tproceso_eventos DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_tusuario_eventos DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_tinductor_eventos DATETIME;

ALTER TABLE tsincronizacion ADD COLUMN date_treg_objetivo DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_treg_inductor DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_treg_perspectiva DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_treg_real DATETIME;
ALTER TABLE tsincronizacion ADD COLUMN date_treg_plan DATETIME;

/* paso 2*/
UPDATE tsincronizacion SET date_tdeletes= cronos_cut, date_treg_tarea= cronos_cut, date_treg_evento= cronos_cut, date_tproceso_eventos= cronos_cut, date_tusuario_eventos= cronos_cut, date_treg_objetivo= cronos_cut, 
date_treg_inductor= cronos_cut, date_treg_perspectiva= cronos_cut, date_treg_real= cronos_cut, 
date_treg_plan= cronos_cut, date_tinductor_eventos= cronos_cut;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-12-21
/**********************************************/
ALTER TABLE tprocesos ADD COLUMN protocolo VARCHAR(10) DEFAULT NULL AFTER email;
update tprocesos SET ip= null;
ALTER TABLE tprocesos CHANGE COLUMN ip puerto INT2 DEFAULT NULL AFTER url;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2019-12-27
/**********************************************/
ALTER TABLE tindicadores ADD COLUMN id_proyecto integer(11) DEFAULT NULL AFTER id_proceso_code;
ALTER TABLE tindicadores ADD COLUMN id_proyecto_code char(10) DEFAULT NULL AFTER id_proyecto;

ALTER TABLE tindicadores
  ADD CONSTRAINT tindicadores_fk3 FOREIGN KEY (id_proyecto) REFERENCES tproyectos (id) ON DELETE SET NULL ON UPDATE CASCADE;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-01-07
/**********************************************/
TRUNCATE TABLE ttarea_tarea;
ALTER TABLE ttarea_tarea DROP COLUMN tipo;
ALTER TABLE ttarea_tarea ADD COLUMN tipo char(2) DEFAULT NULL AFTER id_depend_code;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-01-31
/**********************************************/

/* paso 1*/
ALTER TABLE ttipo_eventos CHANGE year fin MEDIUMINT;
ALTER TABLE ttipo_eventos ADD COLUMN inicio MEDIUMINT AFTER indice;
update ttipo_eventos set inicio= year(cronos);

/* paso 2*/
CREATE TABLE ttipo_auditorias (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  nombre varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  descripcion text COLLATE utf8_spanish_ci DEFAULT NULL,
  numero mediumint(9) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,  
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY id_code (id_code),
  UNIQUE KEY ttipo_auditorias_nombre_index (id_proceso_code,nombre),
  KEY id_proceso (id_proceso),
  CONSTRAINT ttipo_auditorias_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE ttipo_reuniones (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_code char(10) COLLATE utf8_spanish_ci DEFAULT NULL,
  nombre varchar(255) COLLATE utf8_spanish_ci DEFAULT NULL,
  descripcion text COLLATE utf8_spanish_ci DEFAULT NULL,
  numero mediumint(9) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(10) DEFAULT NULL,  
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) COLLATE utf8_spanish_ci DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY id_code (id_code),
  UNIQUE KEY ttipo_reuniones_nombre_index (nombre,id_proceso_code),
  KEY id_proceso (id_proceso),
  CONSTRAINT ttipo_reuniones_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/* paso 3 */
ALTER TABLE tauditorias ADD COLUMN id_tipo_auditoria INTEGER AFTER tipo;
ALTER TABLE tauditorias ADD COLUMN id_tipo_auditoria_code CHAR(10) AFTER id_tipo_auditoria;

UPDATE tauditorias SET id_tipo_auditoria= tipo;
ALTER TABLE tauditorias DROP COLUMN tipo;

/* paso 4 */
ALTER TABLE teventos ADD COLUMN id_tipo_reunion INTEGER AFTER ifmeeting;
ALTER TABLE teventos ADD COLUMN id_tipo_reunion_code CHAR(10) AFTER id_tipo_reunion;

UPDATE teventos SET id_tipo_reunion= ifmeeting;
ALTER TABLE teventos DROP COLUMN ifmeeting;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-02-01
/**********************************************/
/* paso 1*/
UPDATE teventos, ttipo_reuniones SET teventos.id_tipo_reunion_code = ttipo_reuniones.id_code 
where teventos.id_tipo_reunion = ttipo_reuniones.id;
UPDATE tauditorias, ttipo_auditorias set id_tipo_auditoria_code= ttipo_auditorias.id_code where id_tipo_auditoria = ttipo_auditorias.id;

/* paso 2*/
ALTER TABLE tunidades ADD COLUMN id_code CHAR(10) AFTER id;
ALTER TABLE tunidades ADD COLUMN id_proceso INTEGER AFTER descripcion;
ALTER TABLE tunidades ADD COLUMN id_proceso_code CHAR(10) AFTER id_proceso;

ALTER TABLE tunidades
  ADD CONSTRAINT tunidades_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE tindicadores ADD COLUMN id_unidad INTEGER AFTER unidad;
ALTER TABLE tindicadores ADD COLUMN id_unidad_code CHAR(10) AFTER id_unidad;

UPDATE tindicadores SET id_unidad= unidad;
UPDATE tindicadores, tunidades set id_unidad_code= tunidades.id_code where id_unidad = tunidades.id;
 
ALTER TABLE tindicadores DROP COLUMN unidad;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-02-12
/**********************************************/

CREATE TABLE tcola (
  id int(11) NOT NULL AUTO_INCREMENT,
  cronos datetime DEFAULT NULL,
  action varchar(20) DEFAULT NULL,
  protocol varchar(5) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  origen char(2) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-02-25
/**********************************************/
/* p[aso 1*/
ALTER TABLE tproceso_riesgos ADD COLUMN id_requisito INTEGER(11) AFTER id_nota_code;
ALTER TABLE tproceso_riesgos ADD COLUMN id_requisito_code CHAR(10) AFTER id_requisito;

/*paso 2*/
ALTER TABLE tnotas ADD COLUMN tipo SMALLINT AFTER id_tipo_auditoria_code;
UPDATE tnotas SET tipo= id_tipo_auditoria;
ALTER TABLE tnotas DROP COLUMN id_tipo_auditoria;
ALTER TABLE tnotas DROP COLUMN id_tipo_auditoria_code;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-02-26
/**********************************************/
DROP TABLE tcola;

CREATE TABLE tcola (
  id int(11) NOT NULL AUTO_INCREMENT,
  cronos datetime DEFAULT NULL,
  action varchar(20) DEFAULT NULL,
  protocol varchar(5) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  origen char(2) DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-03-10
/**********************************************/
UPDATE teventos, ttipo_reuniones set id_tipo_reunion_code= teventos.id_code where id_tipo_reunion = ttipo_reuniones.id;
UPDATE tauditorias, ttipo_auditorias set id_tipo_auditoria_code= ttipo_auditorias.id_code where id_tipo_auditoria = ttipo_auditorias.id;
UPDATE tindicadores, tunidades set id_unidad_code= tunidades.id_code where id_unidad = tunidades.id;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-03-16
/**********************************************/
/* paso 1*/
UPDATE tusuarios SET nivel= 6 WHERE id = 1; 
ALTER TABLE tgrupos ADD COLUMN id_entity INTEGER AFTER nombre;
ALTER TABLE tgrupos ADD COLUMN id_entity_code CHAR(10) AFTER id_entity;

UPDATE tgrupos, tprocesos SET tgrupos.id_entity= tprocesos.id, tgrupos.id_entity_code= tprocesos.id_code WHERE tprocesos.id = 1; 

ALTER TABLE tgrupos
  ADD CONSTRAINT tgrupos_fk FOREIGN KEY (id_entity) REFERENCES tprocesos (id) ON DELETE RESTRICT ON UPDATE CASCADE;
  
/* paso 2 */
ALTER TABLE tprocesos ADD COLUMN if_entity BOOLEAN AFTER tipo;
ALTER TABLE tprocesos ADD COLUMN id_entity INTEGER AFTER if_entity;
ALTER TABLE tprocesos ADD COLUMN id_entity_code CHAR(10) AFTER id_entity;

UPDATE tprocesos SET if_entity= true WHERE id= 1;

ALTER TABLE tprocesos
  ADD CONSTRAINT tprocesos_fk2 FOREIGN KEY (id_entity) REFERENCES tprocesos (id) ON DELETE RESTRICT ON UPDATE CASCADE;
  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-03-26
/**********************************************/
ALTER TABLE ttareas ADD COLUMN toshow TINYINT(4) AFTER ifgrupo;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-04-05
/**********************************************/
delete from tsincronizacion where cronos >= '2020-01-01 00:00';
delete from tsystem where action like '%Lote%' and cronos >= '2020-01-01';
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-05-15
/**********************************************/
delete from tsystem where inicio = fin and action like '%Lote%';
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-06-01
/**********************************************/
ALTER TABLE tarchivos ADD COLUMN codigo VARCHAR(20) AFTER numero;
ALTER TABLE tarchivos CHANGE COLUMN antecedentes antecedentes VARCHAR(20);
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-06-07
/**********************************************/
ALTER TABLE ttableros ADD COLUMN id_entity INTEGER(11);
ALTER TABLE ttableros ADD COLUMN id_entity_code char(12) AFTER id_entity;

ALTER TABLE tusuarios ADD COLUMN id_proceso_jefe_code CHAR(12) AFTER id_proceso_jefe;
update tusuarios, tprocesos set id_proceso_jefe_code= tprocesos.id_code where tusuarios.id_proceso_jefe = tprocesos.id;

ALTER TABLE tgrupos DROP INDEX nombre;
CREATE UNIQUE INDEX grupo_nombre_index ON tgrupos (nombre, id_entity_code);

ALTER TABLE ttableros DROP INDEX nombre_2;
ALTER TABLE ttableros DROP INDEX tablero_nombre_index;
CREATE UNIQUE INDEX nombre_index ON ttableros (nombre, id_entity_code);

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-06-17
/**********************************************/
/* paso 1 */
ALTER TABLE teventos DROP INDEX nombre_index;
CREATE UNIQUE INDEX teventos_nombre_index ON teventos (nombre(255), lugar(255), fecha_inicio_plan, fecha_fin_plan, situs);

ALTER TABLE tinductor_eventos DROP INDEX id_evento_index;
CREATE UNIQUE INDEX tinductor_eventos_evento_index ON tinductor_eventos (id_inductor_code, id_evento_code);

ALTER TABLE teventos CHANGE COLUMN id_tipo_reunion id_tipo_reunion INTEGER(11);
/* paso 2*/
update treg_nota, tprocesos set treg_nota.id_proceso_code= tprocesos.id_code where treg_nota.id_proceso = tprocesos.id and LENGTH(treg_nota.id_proceso_code) < 12;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-07-11
/**********************************************/
/* paso 1 */


/* paso 2 */
update tproceso_eventos_2017 set toshow = 0 where toshow is null and (id_auditoria is not null or id_tarea is not null);
update tproceso_eventos_2018 set toshow = 0 where toshow is null and (id_auditoria is not null or id_tarea is not null);
update tproceso_eventos_2019 set toshow = 0 where toshow is null and (id_auditoria is not null or id_tarea is not null);
update tproceso_eventos_2020 set toshow = 0 where toshow is null and (id_auditoria is not null or id_tarea is not null);
update tproceso_eventos_2021 set toshow = 0 where toshow is null and (id_auditoria is not null or id_tarea is not null);

/* paso 3 */
ALTER TABLE tproceso_eventos_2017 CHANGE COLUMN evaluado aprobado DATETIME;
ALTER TABLE tproceso_eventos_2017 CHANGE COLUMN evaluacion observacion LONGTEXT;
ALTER TABLE tproceso_eventos_2017 CHANGE COLUMN id_responsable_eval id_responsable_aprb INTEGER(11);

ALTER TABLE tproceso_eventos_2018 CHANGE COLUMN evaluado aprobado DATETIME;
ALTER TABLE tproceso_eventos_2018 CHANGE COLUMN evaluacion observacion LONGTEXT;
ALTER TABLE tproceso_eventos_2018 CHANGE COLUMN id_responsable_eval id_responsable_aprb INTEGER(11);

ALTER TABLE tproceso_eventos_2019 CHANGE COLUMN evaluado aprobado DATETIME;
ALTER TABLE tproceso_eventos_2019 CHANGE COLUMN evaluacion observacion LONGTEXT;
ALTER TABLE tproceso_eventos_2019 CHANGE COLUMN id_responsable_eval id_responsable_aprb INTEGER(11);

ALTER TABLE tproceso_eventos_2020 CHANGE COLUMN evaluado aprobado DATETIME;
ALTER TABLE tproceso_eventos_2020 CHANGE COLUMN evaluacion observacion LONGTEXT;
ALTER TABLE tproceso_eventos_2020 CHANGE COLUMN id_responsable_eval id_responsable_aprb INTEGER(11);

ALTER TABLE tproceso_eventos_2021 CHANGE COLUMN evaluado aprobado DATETIME;
ALTER TABLE tproceso_eventos_2021 CHANGE COLUMN evaluacion observacion LONGTEXT;
ALTER TABLE tproceso_eventos_2021 CHANGE COLUMN id_responsable_eval id_responsable_aprb INTEGER(11);

/* paso 4 */
ALTER TABLE tproceso_eventos_2017 ADD COLUMN id_usuario INTEGER(11) AFTER id_responsable_aprb; 
ALTER TABLE tproceso_eventos_2018 ADD COLUMN id_usuario INTEGER(11) AFTER id_responsable_aprb; 
ALTER TABLE tproceso_eventos_2019 ADD COLUMN id_usuario INTEGER(11) AFTER id_responsable_aprb; 
ALTER TABLE tproceso_eventos_2020 ADD COLUMN id_usuario INTEGER(11) AFTER id_responsable_aprb; 
ALTER TABLE tproceso_eventos_2021 ADD COLUMN id_usuario INTEGER(11) AFTER id_responsable_aprb; 

/* paso 5 */
ALTER TABLE tproceso_eventos_2017
  ADD CONSTRAINT tproceso_eventos_2017_fk6 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2018
  ADD CONSTRAINT tproceso_eventos_2018_fk6 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2019
  ADD CONSTRAINT tproceso_eventos_2019_fk6 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2020
  ADD CONSTRAINT tproceso_eventos_2020_fk6 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2021
  ADD CONSTRAINT tproceso_eventos_2021_fk6 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-07-19
/**********************************************/
/* paso 1 */
ALTER TABLE tunidades DROP FOREIGN KEY tunidades_fk;
ALTER TABLE tunidades DROP COLUMN id_proceso;
ALTER TABLE tunidades DROP COLUMN id_proceso_code; 

/* paso 2 */
ALTER TABLE tproceso_eventos_2017 ADD COLUMN empresarial TINYINT(4) AFTER toshow; 
ALTER TABLE tproceso_eventos_2018 ADD COLUMN empresarial TINYINT(4) AFTER toshow; 
ALTER TABLE tproceso_eventos_2019 ADD COLUMN empresarial TINYINT(4) AFTER toshow; 
ALTER TABLE tproceso_eventos_2020 ADD COLUMN empresarial TINYINT(4) AFTER toshow; 
ALTER TABLE tproceso_eventos_2021 ADD COLUMN empresarial TINYINT(4) AFTER toshow;

ALTER TABLE tproceso_eventos_2017 ADD COLUMN id_tipo_evento INTEGER(11) AFTER empresarial; 
ALTER TABLE tproceso_eventos_2018 ADD COLUMN id_tipo_evento INTEGER(11) AFTER empresarial; 
ALTER TABLE tproceso_eventos_2019 ADD COLUMN id_tipo_evento INTEGER(11) AFTER empresarial; 
ALTER TABLE tproceso_eventos_2020 ADD COLUMN id_tipo_evento INTEGER(11) AFTER empresarial; 
ALTER TABLE tproceso_eventos_2021 ADD COLUMN id_tipo_evento INTEGER(11) AFTER empresarial;

ALTER TABLE tproceso_eventos_2017 ADD COLUMN id_tipo_evento_code CHAR(12) AFTER id_tipo_evento;
ALTER TABLE tproceso_eventos_2018 ADD COLUMN id_tipo_evento_code CHAR(12) AFTER id_tipo_evento;
ALTER TABLE tproceso_eventos_2019 ADD COLUMN id_tipo_evento_code CHAR(12) AFTER id_tipo_evento;
ALTER TABLE tproceso_eventos_2020 ADD COLUMN id_tipo_evento_code CHAR(12) AFTER id_tipo_evento;
ALTER TABLE tproceso_eventos_2021 ADD COLUMN id_tipo_evento_code CHAR(12) AFTER id_tipo_evento;

ALTER TABLE tproceso_eventos_2017 ADD COLUMN indice INTEGER(11) AFTER id_usuario;
ALTER TABLE tproceso_eventos_2018 ADD COLUMN indice INTEGER(11) AFTER id_usuario;
ALTER TABLE tproceso_eventos_2019 ADD COLUMN indice INTEGER(11) AFTER id_usuario;
ALTER TABLE tproceso_eventos_2020 ADD COLUMN indice INTEGER(11) AFTER id_usuario;
ALTER TABLE tproceso_eventos_2021 ADD COLUMN indice INTEGER(11) AFTER id_usuario;

ALTER TABLE tproceso_eventos_2017 ADD COLUMN indice_plus INTEGER(11) AFTER indice;
ALTER TABLE tproceso_eventos_2018 ADD COLUMN indice_plus INTEGER(11) AFTER indice;
ALTER TABLE tproceso_eventos_2019 ADD COLUMN indice_plus INTEGER(11) AFTER indice;
ALTER TABLE tproceso_eventos_2020 ADD COLUMN indice_plus INTEGER(11) AFTER indice;
ALTER TABLE tproceso_eventos_2021 ADD COLUMN indice_plus INTEGER(11) AFTER indice;

ALTER TABLE tproceso_eventos_2017
  ADD CONSTRAINT tproceso_eventos_2017_fk7 FOREIGN KEY (id_tipo_evento) REFERENCES ttipo_eventos (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2018
  ADD CONSTRAINT tproceso_eventos_2018_fk7 FOREIGN KEY (id_tipo_evento) REFERENCES ttipo_eventos (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2019
  ADD CONSTRAINT tproceso_eventos_2019_fk7 FOREIGN KEY (id_tipo_evento) REFERENCES ttipo_eventos (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2020
  ADD CONSTRAINT tproceso_eventos_2020_fk7 FOREIGN KEY (id_tipo_evento) REFERENCES ttipo_eventos (id) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE tproceso_eventos_2021
  ADD CONSTRAINT tproceso_eventos_2021_fk7 FOREIGN KEY (id_tipo_evento) REFERENCES ttipo_eventos (id) ON DELETE SET NULL ON UPDATE CASCADE;

/* paso 3 */
UPDATE tproceso_eventos_2017, tauditorias SET tproceso_eventos_2017.id_responsable= tauditorias.id_responsable, tproceso_eventos_2017.empresarial= tauditorias.empresarial, 
tproceso_eventos_2017.id_tipo_evento= tauditorias.id_tipo_evento, tproceso_eventos_2017.id_tipo_evento_code= tauditorias.id_tipo_evento_code, tproceso_eventos_2017.indice= tauditorias.indice, 
tproceso_eventos_2017.indice_plus= tauditorias.indice_plus where tproceso_eventos_2017.id_auditoria= tauditorias.id;

UPDATE tproceso_eventos_2018, tauditorias SET tproceso_eventos_2018.id_responsable= tauditorias.id_responsable, tproceso_eventos_2018.empresarial= tauditorias.empresarial, 
tproceso_eventos_2018.id_tipo_evento= tauditorias.id_tipo_evento, tproceso_eventos_2018.id_tipo_evento_code= tauditorias.id_tipo_evento_code, tproceso_eventos_2018.indice= tauditorias.indice, 
tproceso_eventos_2018.indice_plus= tauditorias.indice_plus where tproceso_eventos_2018.id_auditoria= tauditorias.id;

UPDATE tproceso_eventos_2019, tauditorias SET tproceso_eventos_2019.id_responsable= tauditorias.id_responsable, tproceso_eventos_2019.empresarial= tauditorias.empresarial, 
tproceso_eventos_2019.id_tipo_evento= tauditorias.id_tipo_evento, tproceso_eventos_2019.id_tipo_evento_code= tauditorias.id_tipo_evento_code, tproceso_eventos_2019.indice= tauditorias.indice, 
tproceso_eventos_2019.indice_plus= tauditorias.indice_plus where tproceso_eventos_2019.id_auditoria= tauditorias.id;

UPDATE tproceso_eventos_2020, tauditorias SET tproceso_eventos_2020.id_responsable= tauditorias.id_responsable, tproceso_eventos_2020.empresarial= tauditorias.empresarial, 
tproceso_eventos_2020.id_tipo_evento= tauditorias.id_tipo_evento, tproceso_eventos_2020.id_tipo_evento_code= tauditorias.id_tipo_evento_code, tproceso_eventos_2020.indice= tauditorias.indice, 
tproceso_eventos_2020.indice_plus= tauditorias.indice_plus where tproceso_eventos_2020.id_auditoria= tauditorias.id;

UPDATE tproceso_eventos_2021, tauditorias SET tproceso_eventos_2021.id_responsable= tauditorias.id_responsable, tproceso_eventos_2021.empresarial= tauditorias.empresarial, 
tproceso_eventos_2021.id_tipo_evento= tauditorias.id_tipo_evento, tproceso_eventos_2021.id_tipo_evento_code= tauditorias.id_tipo_evento_code, tproceso_eventos_2021.indice= tauditorias.indice, 
tproceso_eventos_2021.indice_plus= tauditorias.indice_plus where tproceso_eventos_2021.id_auditoria= tauditorias.id; 
  
/* paso 4 */
UPDATE tproceso_eventos_2017, teventos SET tproceso_eventos_2017.id_responsable= teventos.id_responsable, tproceso_eventos_2017.empresarial= teventos.empresarial, 
tproceso_eventos_2017.id_tipo_evento= teventos.id_tipo_evento, tproceso_eventos_2017.id_tipo_evento_code= teventos.id_tipo_evento_code, tproceso_eventos_2017.indice= teventos.indice, 
tproceso_eventos_2017.indice_plus= teventos.indice_plus where tproceso_eventos_2017.id_evento= teventos.id;

UPDATE tproceso_eventos_2018, teventos SET tproceso_eventos_2018.id_responsable= teventos.id_responsable, tproceso_eventos_2018.empresarial= teventos.empresarial, 
tproceso_eventos_2018.id_tipo_evento= teventos.id_tipo_evento, tproceso_eventos_2018.id_tipo_evento_code= teventos.id_tipo_evento_code, tproceso_eventos_2018.indice= teventos.indice, 
tproceso_eventos_2018.indice_plus= teventos.indice_plus where tproceso_eventos_2018.id_evento= teventos.id;

UPDATE tproceso_eventos_2019, teventos SET tproceso_eventos_2019.id_responsable= teventos.id_responsable, tproceso_eventos_2019.empresarial= teventos.empresarial, 
tproceso_eventos_2019.id_tipo_evento= teventos.id_tipo_evento, tproceso_eventos_2019.id_tipo_evento_code= teventos.id_tipo_evento_code, tproceso_eventos_2019.indice= teventos.indice, 
tproceso_eventos_2019.indice_plus= teventos.indice_plus where tproceso_eventos_2019.id_evento= teventos.id;

UPDATE tproceso_eventos_2020, teventos SET tproceso_eventos_2020.id_responsable= teventos.id_responsable, tproceso_eventos_2020.empresarial= teventos.empresarial, 
tproceso_eventos_2020.id_tipo_evento= teventos.id_tipo_evento, tproceso_eventos_2020.id_tipo_evento_code= teventos.id_tipo_evento_code, tproceso_eventos_2020.indice= teventos.indice, 
tproceso_eventos_2020.indice_plus= teventos.indice_plus where tproceso_eventos_2020.id_evento= teventos.id;

UPDATE tproceso_eventos_2021, teventos SET tproceso_eventos_2021.id_responsable= teventos.id_responsable, tproceso_eventos_2021.empresarial= teventos.empresarial, 
tproceso_eventos_2021.id_tipo_evento= teventos.id_tipo_evento, tproceso_eventos_2021.id_tipo_evento_code= teventos.id_tipo_evento_code, tproceso_eventos_2021.indice= teventos.indice, 
tproceso_eventos_2021.indice_plus= teventos.indice_plus where tproceso_eventos_2021.id_evento= teventos.id;
  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-07-20
/**********************************************/
/* paso 1 */
ALTER TABLE _config ADD COLUMN n_entity integer(11) DEFAULT NULL AFTER email_app;
ALTER TABLE _config ADD COLUMN n_usuarios integer(11) DEFAULT NULL AFTER n_entity;

/* paso 2*/
update ttipo_eventos as t1, ttipo_eventos as t2 set t1.id_subcapitulo= t2.id where t1.id_subcapitulo_code = t2.id_code;

/* paso 3 */
ALTER TABLE teventos DROP INDEX teventos_nombre_index;
CREATE UNIQUE INDEX teventos_nombre_index ON teventos (nombre(255), lugar(255), fecha_inicio_plan, fecha_fin_plan, id_proceso_code, situs);

ALTER TABLE tauditorias DROP INDEX auditoria_index;
CREATE UNIQUE INDEX tauditorias_nombre_index ON tauditorias (id_tipo_auditoria, origen, lugar(255), fecha_inicio_plan, fecha_fin_plan, id_proceso_code, situs);

ALTER TABLE tproceso_eventos_2017 ADD COLUMN rechazado DATETIME AFTER id_responsable_aprb;
ALTER TABLE tproceso_eventos_2018 ADD COLUMN rechazado DATETIME AFTER id_responsable_aprb;
ALTER TABLE tproceso_eventos_2019 ADD COLUMN rechazado DATETIME AFTER id_responsable_aprb;
ALTER TABLE tproceso_eventos_2020 ADD COLUMN rechazado DATETIME AFTER id_responsable_aprb;
ALTER TABLE tproceso_eventos_2021 ADD COLUMN rechazado DATETIME AFTER id_responsable_aprb;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-08-10
/**********************************************/
/* paso 1 */
update tprocesos set id_entity= null, id_entity_code= null where if_entity= true;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-08-13
/**********************************************/
/* paso 1 */
ALTER TABLE ttematicas DROP FOREIGN KEY ttematicas_fk2;
ALTER TABLE ttematicas DROP COLUMN id_proceso;
ALTER TABLE ttematicas DROP COLUMN id_proceso_code;

ALTER TABLE ttematicas ADD COLUMN copyto VARCHAR(100) AFTER evaluacion;
ALTER TABLE ttematicas ADD COLUMN id_copyfrom INTEGER(11) AFTER copyto;
ALTER TABLE ttematicas ADD COLUMN id_copyfrom_code CHAR(12) AFTER id_copyfrom;

/* paso 2*/
ALTER TABLE ttematicas DROP INDEX tematica_numero_index;
CREATE UNIQUE INDEX tematica_numero_index ON ttematicas (numero, ifaccords, id_evento_code, fecha_inicio_plan);
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-08-25
/**********************************************/
update tproceso_eventos_2019, teventos set tproceso_eventos_2019.id_responsable = teventos.id_responsable 
where tproceso_eventos_2019.id_responsable = 1 and tproceso_eventos_2019.id_evento = teventos.id;

update tproceso_eventos_2020, teventos set tproceso_eventos_2020.id_responsable = teventos.id_responsable 
where tproceso_eventos_2020.id_responsable = 1 and tproceso_eventos_2020.id_evento = teventos.id;

update tproceso_eventos_2021, teventos set tproceso_eventos_2021.id_responsable = teventos.id_responsable 
where tproceso_eventos_2021.id_responsable = 1 and tproceso_eventos_2021.id_evento = teventos.id;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-09-02
/**********************************************/
update treg_nota, tprocesos set treg_nota.id_proceso_code = tprocesos.id_code where treg_nota.id_proceso = tprocesos.id;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-10-09
/**********************************************/
update ttipo_auditorias, tprocesos set ttipo_auditorias.id_proceso_code= tprocesos.id_code where ttipo_auditorias.id_proceso = tprocesos.id;
update tusuarios set nombre= 'ADMINISTRADOR' where id = 1;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-10-21
/**********************************************/
update tdocumentos set month= null where month = 0;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-10-26
/**********************************************/
ALTER TABLE tplanes ADD COLUMN efectivas_cumplidas MEDIUMINT(4) DEFAULT NULL AFTER reprogramadas;
ALTER TABLE tplanes ADD COLUMN efectivas_incumplidas MEDIUMINT(4) DEFAULT NULL AFTER efectivas_cumplidas;

ALTER TABLE treg_plantrab ADD COLUMN efectivas_cumplidas MEDIUMINT(4) DEFAULT NULL AFTER reprogramadas;
ALTER TABLE treg_plantrab ADD COLUMN efectivas_incumplidas MEDIUMINT(4) DEFAULT NULL AFTER efectivas_cumplidas;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2020-12-13
/**********************************************/
/* paso 1*/
ALTER TABLE tunidades ADD COLUMN id_proceso INTEGER AFTER descripcion;
ALTER TABLE tunidades ADD COLUMN id_proceso_code CHAR(12) AFTER id_proceso;

ALTER TABLE tunidades
  ADD CONSTRAINT tunidades_fk FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE SET NULL ON UPDATE CASCADE;
  
update tunidades, tprocesos set tunidades.id_proceso= tprocesos.id, tunidades.id_proceso_code= tprocesos.id_code 
where convert(tunidades.situs using utf8) = convert(tprocesos.codigo using utf8);

/* paso 2*/
ALTER TABLE triesgos MODIFY COLUMN ext TINYINT(2) DEFAULT NULL; 
/*************************************************************************/
-- endscript
/*************************************************************************/  

/**********************************************/
-- beginscript:2021-01-23
/**********************************************/
CREATE TABLE ttrazas (
  id int(11) NOT NULL AUTO_INCREMENT,
  action varchar(30),
  id_usuario int(11) DEFAULT NULL,
  descripcion text,
  observacion text,
  id_proceso int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY ttrazas_frk (id_usuario),
  KEY ttrazas_frk1 (id_proceso),
  CONSTRAINT ttrazas_frk FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON UPDATE CASCADE,
  CONSTRAINT ttrazas_frk1 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-02-17
/**********************************************/
/* paso 1*/
CREATE TABLE tkanban_columns (
  id int(11) NOT NULL AUTO_INCREMENT,
  fixed tinyint(2) DEFAULT NULL,
  nombre varchar(255) DEFAULT NULL,
  numero smallint(255) DEFAULT NULL,
  class varchar(80) DEFAULT NULL,
  descripcion text DEFAULT NULL,
  id_proyecto int(11) DEFAULT NULL,
  id_proyecto_code char(12) DEFAULT NULL,
  id_responsable int(11) DEFAULT NULL,
  active tinyint(1) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY tkanban_columns_nombre (nombre,id_proyecto_code),
  KEY tkanban_columns_fk (id_proyecto),
  KEY tkanban_columns_fk1 (id_responsable),
  CONSTRAINT tkanban_columns_fk FOREIGN KEY (id_proyecto) REFERENCES tproyectos (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tkanban_columns_fk1 FOREIGN KEY (id_responsable) REFERENCES tusuarios (id) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tkanban_column_tareas (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_kanban_column int(11) DEFAULT NULL,
  id_tarea int(11) DEFAULT NULL,
  id_tarea_code char(12) DEFAULT NULL,
  numero smallint(6) DEFAULT NULL,
  active tinyint(1) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  PRIMARY KEY (id),
  KEY tkanban_columns_tareas_fk (id_kanban_column),
  KEY tkanban_columns_tarea_fk1 (id_tarea),
  CONSTRAINT tkanban_columns_tarea_fk1 FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT tkanban_columns_tareas_fk FOREIGN KEY (id_kanban_column) REFERENCES tkanban_columns (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/* paso 2*/
update tproceso_eventos_2021, teventos set tproceso_eventos_2021.id_evento_code= teventos.id_code 
where tproceso_eventos_2021.id_evento = teventos.id;


/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-03-02
/**********************************************/
alter table tusuarios change column freeassign freeassign TINYINT(2);

/*************************************************************************/
-- endscript
/*************************************************************************/
/**********************************************/
-- beginscript:2021-03-04
/**********************************************/
ALTER TABLE treg_tarea ADD COLUMN cumplimiento TINYINT(2) DEFAULT NULL AFTER valor;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-03-25
/**********************************************/
update tprocesos as t1, tprocesos as t2 set t1.id_entity = t2.id, t1.id_entity_code = t2.id_code 
where t1.id_entity is not null and t1.id_proceso = t2.id and t2.if_entity = true;  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-03-31
/**********************************************/

ALTER TABLE tproceso_eventos_2022 CHANGE COLUMN evaluado aprobado DATETIME;
ALTER TABLE tproceso_eventos_2022 CHANGE COLUMN evaluacion observacion LONGTEXT;
ALTER TABLE tproceso_eventos_2022 CHANGE COLUMN id_responsable_eval id_responsable_aprb INTEGER(11);

ALTER TABLE tproceso_eventos_2022 ADD COLUMN id_usuario INTEGER(11) AFTER id_responsable_aprb; 

ALTER TABLE tproceso_eventos_2022
  ADD CONSTRAINT tproceso_eventos_2022_fk6 FOREIGN KEY (id_usuario) REFERENCES tusuarios (id) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE tproceso_eventos_2022 ADD COLUMN empresarial TINYINT(4) AFTER toshow;
ALTER TABLE tproceso_eventos_2022 ADD COLUMN id_tipo_evento INTEGER(11) AFTER empresarial;
ALTER TABLE tproceso_eventos_2022 ADD COLUMN id_tipo_evento_code CHAR(12) AFTER id_tipo_evento;
ALTER TABLE tproceso_eventos_2022 ADD COLUMN indice INTEGER(11) AFTER id_usuario;
ALTER TABLE tproceso_eventos_2022 ADD COLUMN indice_plus INTEGER(11) AFTER indice;

ALTER TABLE tproceso_eventos_2022
  ADD CONSTRAINT tproceso_eventos_2022_fk7 FOREIGN KEY (id_tipo_evento) REFERENCES ttipo_eventos (id) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE tproceso_eventos_2022, tauditorias SET tproceso_eventos_2022.id_responsable= tauditorias.id_responsable, tproceso_eventos_2022.empresarial= tauditorias.empresarial, 
tproceso_eventos_2022.id_tipo_evento= tauditorias.id_tipo_evento, tproceso_eventos_2022.id_tipo_evento_code= tauditorias.id_tipo_evento_code, 
tproceso_eventos_2022.indice= tauditorias.indice, tproceso_eventos_2022.indice_plus= tauditorias.indice_plus where tproceso_eventos_2022.id_auditoria= tauditorias.id; 
UPDATE tproceso_eventos_2022, teventos SET tproceso_eventos_2022.id_responsable= teventos.id_responsable, tproceso_eventos_2022.empresarial= teventos.empresarial, 
tproceso_eventos_2022.id_tipo_evento= teventos.id_tipo_evento, tproceso_eventos_2022.id_tipo_evento_code= teventos.id_tipo_evento_code, tproceso_eventos_2022.indice= teventos.indice, 
tproceso_eventos_2022.indice_plus= teventos.indice_plus where tproceso_eventos_2022.id_evento= teventos.id;
ALTER TABLE tproceso_eventos_2022 ADD COLUMN rechazado DATETIME AFTER id_responsable_aprb;    

update tproceso_eventos_2022, teventos set tproceso_eventos_2022.id_responsable = teventos.id_responsable 
where tproceso_eventos_2022.id_responsable = 1 and tproceso_eventos_2022.id_evento = teventos.id;

update tproceso_eventos_2022, teventos set tproceso_eventos_2022.id_evento_code= teventos.id_code 
where tproceso_eventos_2022.id_evento = teventos.id;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-04-01
/**********************************************/
ALTER TABLE tinductor_eventos CHANGE id_evento id_evento INTEGER(11) DEFAULT NULL;
ALTER TABLE tinductor_eventos ADD COLUMN id_tarea INTEGER(11) AFTER id_evento_code; 
ALTER TABLE tinductor_eventos ADD COLUMN id_tarea_code CHAR(12) AFTER id_tarea; 

ALTER TABLE tinductor_eventos DROP INDEX tinductor_eventos_evento_index;
CREATE UNIQUE INDEX tinductor_eventos_index ON tinductor_eventos (id_inductor_code, id_evento_code, id_tarea_code);

ALTER TABLE tinductor_eventos
  ADD CONSTRAINT tinductor_eventos_fk2 FOREIGN KEY (id_tarea) REFERENCES tusuarios (id) ON DELETE SET NULL ON UPDATE CASCADE;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-04-09
/**********************************************/
ALTER TABLE ttipo_listas CHANGE COLUMN year inicio MEDIUMINT(9) AFTER componente;
ALTER TABLE ttipo_listas ADD COLUMN fin MEDIUMINT(9) AFTER inicio;
UPDATE ttipo_listas SET fin= 2022 WHERE inicio < 2022;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-04-14
/**********************************************/
ALTER TABLE tkanban_column_tareas ADD COLUMN observacion TEXT AFTER active;
update tkanban_columns set class= concat('bg-',class);
update tkanban_columns set class= 'bg-info' where class = 'bg-info,good';

ALTER TABLE tkanban_column_tareas DROP FOREIGN KEY tkanban_columns_tareas_fk;
ALTER TABLE tkanban_column_tareas DROP INDEX tkanban_columns_tareas_fk;
ALTER TABLE tkanban_column_tareas 
  ADD CONSTRAINT tkanban_column_tareas_fk FOREIGN KEY (id_kanban_column) REFERENCES tkanban_columns (id) ON DELETE RESTRICT ON UPDATE CASCADE;
  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-04-26
/**********************************************/
/* paso 1 */
ALTER TABLE tinductor_eventos CHANGE id_evento id_evento INTEGER(11) DEFAULT NULL;
ALTER TABLE tinductor_eventos ADD COLUMN id_tarea INTEGER(11) AFTER id_evento_code; 
ALTER TABLE tinductor_eventos ADD COLUMN id_tarea_code CHAR(12) AFTER id_tarea; 

ALTER TABLE tinductor_eventos DROP INDEX tinductor_eventos_evento_index;
CREATE UNIQUE INDEX tinductor_eventos_index ON tinductor_eventos (id_inductor_code, id_evento_code, id_tarea_code);

ALTER TABLE tinductor_eventos DROP FOREIGN KEY tinductor_eventos_fk2;

ALTER TABLE tinductor_eventos
  ADD CONSTRAINT tinductor_eventos_fk2 FOREIGN KEY (id_tarea) REFERENCES tusuarios (id) ON DELETE CASCADE ON UPDATE CASCADE;
  
UPDATE tinductor_eventos, teventos SET tinductor_eventos.id_tarea= teventos.id_tarea, tinductor_eventos.id_tarea_code= teventos.id_tarea_code 
WHERE tinductor_eventos.id_evento = teventos.id;

/* paso 2*/
ALTER TABLE tref_documentos ADD COLUMN id_tarea INTEGER(11) AFTER id_evento_code; 
ALTER TABLE tref_documentos ADD COLUMN id_tarea_code CHAR(12) AFTER id_tarea; 

ALTER TABLE tref_documentos
  ADD CONSTRAINT tref_documentos_fk8 FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON DELETE SET NULL ON UPDATE CASCADE;

update tref_documentos, teventos SET tref_documentos.id_tarea = teventos.id_tarea, tref_documentos.id_tarea_code = teventos.id_tarea_code  
where tref_documentos.id_evento = teventos.id;
update tref_documentos, ttareas SET tref_documentos.id_tarea = ttareas.id, tref_documentos.id_tarea_code = ttareas.id_code  
where tref_documentos.id_proyecto = ttareas.id_proyecto;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-05-01
/**********************************************/
update ttipo_reuniones, tprocesos set ttipo_reuniones.id_proceso_code= tprocesos.id_code where ttipo_reuniones.id_proceso= tprocesos.id; 
update teventos, ttipo_reuniones set teventos.id_tipo_reunion_code= ttipo_reuniones.id_code where teventos.id_tipo_reunion= ttipo_reuniones.id; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-05-06
/**********************************************/
ALTER TABLE teventos
  ADD CONSTRAINT teventos_fk10 FOREIGN KEY (id_tipo_reunion) REFERENCES ttipo_reuniones (id) ON DELETE RESTRICT ON UPDATE CASCADE;
  
update tprocesos as t1, tprocesos as t2 set t1.id_proceso_code= t2.id_code where t1.id_proceso = t2.id 
and (t1.id_proceso is not null and t1.id_proceso_code is null);
  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-06-11
/**********************************************/
ALTER TABLE teventos
  ADD CONSTRAINT teventos_fk10 FOREIGN KEY (id_tipo_reunion) REFERENCES ttipo_reuniones (id) ON DELETE RESTRICT ON UPDATE CASCADE;
  
update tprocesos as t1, tprocesos as t2 set t1.id_proceso_code= t2.id_code where t1.id_proceso = t2.id;
update tusuarios, tprocesos set tusuarios.id_proceso_code= tprocesos.id_code where tusuarios.id_proceso = tprocesos.id;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-06-14
/**********************************************/
delete t1 from treg_evento_2021 as t1, treg_evento_2021 as t2 where t1.id != t2.id 
and (t1.id_evento = t2.id_evento and t1.cronos = t2.cronos and t1.id_usuario = t2.id_usuario and t1.id_responsable = t2.id_responsable)
and (t1.rechazado is not null and t2.rechazado is null) and t2.toshow = true and t1.observacion like 'Tarea reprogramada en fecha%';
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-06-30
/**********************************************/
ALTER TABLE ttareas
  ADD CONSTRAINT ttareas_fk5 FOREIGN KEY (id_proceso) REFERENCES tprocesos (id) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE ttareas DROP KEY id_proceso, ADD KEY ttareas_fk5 (id_proceso);

ALTER TABLE ttareas DROP KEY id_proyecto, ADD KEY ttareas_fk1 (id_proyecto);
ALTER TABLE ttareas DROP KEY id_grupo_tarea, ADD KEY ttareas_fk (id_tarea);
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-10-04
/**********************************************/
ALTER TABLE tproceso_listas ADD COLUMN id_requisito INTEGER(11) AFTER id_lista_code;
ALTER TABLE tproceso_listas ADD COLUMN id_requisito_code char(12) AFTER id_requisito;

ALTER TABLE tproceso_listas
  ADD CONSTRAINT tproceso_listas_fk2 FOREIGN KEY (id_requisito) REFERENCES tlista_requisitos (id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE tproceso_listas DROP INDEX tproceso_listas_idx;
CREATE UNIQUE INDEX tproceso_listas_idx ON tproceso_listas (id_lista_code, id_requisito_code, id_proceso_code, year);  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-10-20
/**********************************************/
ALTER TABLE tnotas CHANGE COLUMN origen origen smallint DEFAULT NULL;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-10-23
/**********************************************/
ALTER TABLE tinductor_eventos DROP FOREIGN KEY tinductor_eventos_fk2;
ALTER TABLE tinductor_eventos 
  ADD CONSTRAINT tinductor_eventos_fk2 FOREIGN KEY (id_tarea) REFERENCES ttareas (id) ON DELETE CASCADE ON UPDATE CASCADE; 
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-10-31
/**********************************************/
update teventos set id_secretary= null where id_tipo_reunion is null;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-11-04
/**********************************************/
/* paso 1 */
CREATE TABLE treg_proyecto (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_proyecto int(11) DEFAULT NULL,
  id_proyecto_code char(12) DEFAULT NULL,
  id_proceso int(11) DEFAULT NULL,
  id_proceso_code char(12) DEFAULT NULL,
  year mediumint(9) unsigned NOT NULL,
  month smallint(6) unsigned NOT NULL,
  valor float(9,3) DEFAULT NULL,
  calcular tinyint(1) NOT NULL DEFAULT 0,
  observacion text DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  origen_data text DEFAULT NULL,
  reg_fecha datetime DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY treg_proyecto_proyecto_index (id_proyecto_code,year,month,reg_fecha,observacion(1)),
  KEY id_proyecto (id_proyecto),
  CONSTRAINT treg_proyecto_fk FOREIGN KEY (id_proyecto) REFERENCES tproyectos (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/* paso 2 */
ALTER TABLE tkanban_columns ADD COLUMN id_code char(12) AFTER id;
ALTER TABLE tkanban_column_tareas ADD COLUMN id_kanban_column_code char(12) AFTER id_kanban_column;

ALTER TABLE treg_tarea ADD COLUMN id_kanban_column INTEGER(11) AFTER reg_fecha;
ALTER TABLE treg_tarea ADD COLUMN id_kanban_column_code CHAR(12) AFTER id_kanban_column;
ALTER TABLE treg_tarea 
  ADD CONSTRAINT treg_tarea_fk2 FOREIGN KEY (id_kanban_column) REFERENCES tkanban_columns (id) ON DELETE SET NULL ON UPDATE CASCADE; 

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-11-14
/**********************************************/
ALTER TABLE ttematicas CHANGE COLUMN observacion descripcion TEXT AFTER ifaccords;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-11-20
/**********************************************/
ALTER TABLE ttarea_tarea DROP INDEX depend;
CREATE UNIQUE INDEX ttarea_tarea_depend_index ON ttarea_tarea (id_tarea_code, id_depend_code, tipo);  
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2021-12-26
/**********************************************/
/* paso 1 */
ALTER TABLE tindicadores ADD COLUMN chk_cumulative tinyint(1) AFTER cumulative;
update tindicadores set chk_cumulative= true; 
update treg_real set chk_cumulative= true;

ALTER TABLE treg_real ADD COLUMN chk_cumulative tinyint(1) AFTER acumulado_real;
ALTER TABLE treg_real ADD COLUMN acumulado_corte double(15,3) AFTER valor;

ALTER TABLE treg_real CHANGE COLUMN acumulado_real acumulado_real double(15,3);
ALTER TABLE treg_plan CHANGE COLUMN acumulado_plan acumulado_plan double(15,3);
ALTER TABLE treg_plan CHANGE COLUMN acumulado_plan_cot acumulado_plan_cot double(15,3);

ALTER TABLE tregistro CHANGE COLUMN acumulado_real acumulado_real double(15,3);
ALTER TABLE tregistro CHANGE COLUMN acumulado_plan acumulado_plan double(15,3);

/* paso 2 */
update tobjetivos, tprocesos set tobjetivos.id_proceso_code = tprocesos.id_code 
where tobjetivos.id_proceso = tprocesos.id;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2022-01-07
/**********************************************/
alter table torganismos add column use_anual_plan bool after codigo;
update torganismos set use_anual_plan= true;

CREATE TABLE torganismo_eventos (
  id int(11) NOT NULL AUTO_INCREMENT,
  id_organismo int(11) DEFAULT NULL,
  id_organismo_code char(12) DEFAULT NULL,
  id_evento int(11) DEFAULT NULL,
  id_evento_code char(12) DEFAULT NULL,
  id_usuario int(11) DEFAULT NULL,
  cronos datetime DEFAULT NULL,
  cronos_syn datetime DEFAULT NULL,
  situs char(2) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY torganismo_eventos_fk (id_organismo),
  KEY torganismo_evento_fk1 (id_evento),
  UNIQUE KEY torganismo_eventos_index (id_organismo_code,id_evento_code) USING BTREE,
  CONSTRAINT torganismo_evento_fk1 FOREIGN KEY (id_evento) REFERENCES teventos (id) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT torganismo_eventos_fk FOREIGN KEY (id_organismo) REFERENCES torganismos (id) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2022-01-22
/**********************************************/
ALTER TABLE treg_nota ADD COLUMN chk_apply BOOLEAN AFTER cumplimiento;
update treg_nota set chk_apply= true;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2022-01-30
/**********************************************/
ALTER TABLE tkanban_columns ADD COLUMN cronos_syn datetime AFTER cronos;
ALTER TABLE tkanban_columns ADD COLUMN situs char(2) AFTER cronos_syn;
ALTER TABLE tkanban_column_tareas ADD COLUMN cronos_syn datetime AFTER cronos;
ALTER TABLE tkanban_column_tareas ADD COLUMN situs char(2) AFTER cronos_syn;

update tkanban_columns, tprocesos SET tkanban_columns.situs= tprocesos.codigo where tprocesos.id = 1;
update tkanban_column_tareas, tprocesos SET tkanban_column_tareas.situs= tprocesos.codigo where tprocesos.id = 1;

delete from tsystem where action= 'exportLote' and year(inicio) = 2022;
truncate table tcola;
delete from tsincronizacion where year(cronos) = 2022;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2022-02-16
/**********************************************/
ALTER TABLE tinductores CHANGE COLUMN nombre nombre longtext;
/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2022-02-20
/**********************************************/
ALTER TABLE treg_programa CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_programa DROP INDEX id_programa_index;
CREATE UNIQUE INDEX treg_programa_index ON treg_programa (id_programa_code, id_proceso_code, year, month, reg_fecha, observacion);

ALTER TABLE treg_objetivo CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_objetivo DROP INDEX id_objetivo_index;
CREATE UNIQUE INDEX treg_objetivo_index ON treg_objetivo (id_objetivo_code, id_proceso_code, year, month, reg_fecha, observacion);

ALTER TABLE treg_inductor CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_inductor DROP INDEX id_inductor_index;
CREATE UNIQUE INDEX treg_inductor_index ON treg_inductor (id_inductor_code, year, month, reg_fecha, observacion);

ALTER TABLE treg_nota CHANGE observacion observacion LONGTEXT;

ALTER TABLE treg_perspectiva CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_perspectiva DROP INDEX id_perspectiva_index;
CREATE UNIQUE INDEX treg_perspectiva_index ON treg_perspectiva (id_perspectiva_code, id_proceso_code, year, month, reg_fecha, observacion);

ALTER TABLE treg_plan CHANGE observacion observacion LONGTEXT;

ALTER TABLE treg_politica CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_politica DROP INDEX id_politica_index;
CREATE UNIQUE INDEX treg_politica_index ON treg_politica (id_politica_code, id_proceso_code, year, month, reg_fecha, observacion);

ALTER TABLE treg_proceso CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_proceso DROP INDEX reg_proceso_year_index;
CREATE UNIQUE INDEX treg_proceso_index ON treg_proceso (id_proceso_code, year, month, reg_fecha, observacion);

ALTER TABLE treg_proyecto CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_proyecto DROP INDEX treg_proyecto_proyecto_index;
CREATE UNIQUE INDEX treg_proyecto_index ON treg_proyecto (id_proyecto_code, year, month, reg_fecha, observacion);

ALTER TABLE treg_real CHANGE observacion observacion LONGTEXT;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2022-02-27
/**********************************************/
ALTER TABLE treg_objetivo CHANGE observacion observacion LONGTEXT;
ALTER TABLE treg_objetivo DROP INDEX id_objetivo_index;
CREATE UNIQUE INDEX treg_objetivo_index ON treg_objetivo (id_objetivo_code, id_proceso_code, year, month, reg_fecha, observacion);

ALTER TABLE tinductores ADD COLUMN peso SMALLINT(6) AFTER id_proceso_code;

ALTER TABLE tlista_requisitos ADD COLUMN indice INTEGER AFTER indicacion;

update tlista_requisitos, ttipo_listas set tlista_requisitos.indice= ttipo_listas.indice 
  where tlista_requisitos.id_tipo_lista = ttipo_listas.id;

update tlista_requisitos set indice= componente*pow(10,6) where id_tipo_lista is null;

/*************************************************************************/
-- endscript
/*************************************************************************/

/**********************************************/
-- beginscript:2022-04-08
/**********************************************/
update tkanban_column_tareas, tkanban_columns set tkanban_column_tareas.id_kanban_column_code = tkanban_columns.id_code 
  where tkanban_column_tareas.id_kanban_column = tkanban_columns.id;
/*************************************************************************/
-- endscript
/*************************************************************************/
